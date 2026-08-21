<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Temporary maintenance: backfill UGC/HEMIS report fields (ethnicity, citizenship, nationality,
 * disability) and normalize gender labels for campus reports.
 *
 * URL: admin/data_sanitization (class Data_sanitization).
 * Remove or restrict this controller in production once data is clean.
 */
class Data_sanitization extends Admin_Controller
{
    /** Default text when ethnicity / nationality / citizenship is missing (HEMIS-style “unknown” bucket). */
    const DEFAULT_UNKNOWN = 'Unknown';

    /** Default ethnicity bucket when aligning with “General” category wording. */
    const DEFAULT_GENERAL = 'General';

    /** Stored gender values must stay compatible with Customlib::getGender() (Male / Female) plus Other. */
    const GENDER_MALE = 'Male';

    const GENDER_FEMALE = 'Female';

    const GENDER_OTHER = 'Other';

    /**
     * HEMIS-style gender codes → DB labels used by this application.
     *
     * @var array<string,string>
     */
    protected $gender_code_map = array(
        '1' => self::GENDER_MALE,
        '2' => self::GENDER_FEMALE,
        '3' => self::GENDER_OTHER,
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'admin/data_sanitization/index');

        $data['title'] = 'Data sanitization (UGC / HEMIS reports)';
        $data['stats'] = $this->build_stats();
        $data['gender_legend'] = $this->gender_code_map;
        $data['default_unknown'] = self::DEFAULT_UNKNOWN;
        $data['default_general'] = self::DEFAULT_GENERAL;
        $data['can_apply'] = $this->rbac->hasPrivilege('superadmin', 'can_edit');
        $data['flash'] = $this->session->flashdata('sanitization_msg');

        $this->load->view('layout/header', $data);
        $this->load->view('admin/data_sanitization/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * POST: apply batch fixes (superadmin only).
     */
    public function apply()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_edit')) {
            access_denied();
        }

        if ($this->input->method() !== 'post') {
            redirect('admin/data_sanitization');
        }

        $fill_unknown = $this->input->post('fill_unknown') ? true : false;
        $fill_general_ethnicity = $this->input->post('fill_general_ethnicity') ? true : false;
        $normalize_gender = $this->input->post('normalize_gender') ? true : false;
        $confirm = $this->input->post('confirm');

        if ($confirm !== 'yes') {
            $this->session->set_flashdata('sanitization_msg', array(
                'type' => 'warning',
                'text' => 'Confirmation was not checked. No changes were made.',
            ));
            redirect('admin/data_sanitization');
        }

        $result = array(
            'unknown_fields' => array('updated_rows' => 0),
            'general_ethnicity' => array('updated_rows' => 0),
            'gender' => array('updated_rows' => 0),
        );

        if ($fill_unknown) {
            $result['unknown_fields'] = $this->apply_unknown_defaults();
        }

        if ($fill_general_ethnicity) {
            $result['general_ethnicity'] = $this->apply_general_ethnicity();
        }

        if ($normalize_gender) {
            $result['gender'] = $this->apply_gender_normalization();
        }

        $this->session->set_flashdata('sanitization_msg', array(
            'type' => 'success',
            'text' => 'Sanitization finished.',
            'detail' => $result,
        ));
        redirect('admin/data_sanitization');
    }

    /**
     * Counts for dashboard.
     *
     * @return array
     */
    protected function build_stats()
    {
        $t = $this->db->dbprefix('students');

        $q = function ($col) use ($t) {
            $expr = str_replace('col', $col, "(col IS NULL OR TRIM(col) = '')");
            return (int) $this->db->query("SELECT COUNT(*) AS c FROM {$t} WHERE {$expr}")->row()->c;
        };

        return array(
            'total_students' => (int) $this->db->count_all('students'),
            'missing_ethnicity' => $q('ethnicity'),
            'missing_nationality' => $q('nationality'),
            'missing_citizenship' => $q('citizenship'),
            'missing_disability_status' => $q('disability_status'),
            'gender_needs_review' => $this->count_gender_needs_normalization(),
        );
    }

    /**
     * Rows where stored gender differs from the canonical HEMIS-aligned label.
     *
     * @return int
     */
    protected function count_gender_needs_normalization()
    {
        $rows = $this->db->select('id, gender')->from('students')->get()->result_array();
        $n = 0;
        foreach ($rows as $row) {
            $resolved = $this->resolve_gender(isset($row['gender']) ? $row['gender'] : '');
            if ((string) $row['gender'] !== $resolved) {
                $n++;
            }
        }
        return $n;
    }

    /**
     * Set empty string / NULL fields to Unknown (and disability_type cleared when status filled).
     *
     * @return array{updated_rows:int}
     */
    protected function apply_unknown_defaults()
    {
        $default = self::DEFAULT_UNKNOWN;
        $detail = array();

        $this->db->where("(ethnicity IS NULL OR TRIM(ethnicity) = '')", null, false);
        $this->db->update('students', array('ethnicity' => $default));
        $detail['ethnicity'] = (int) $this->db->affected_rows();

        $this->db->where("(nationality IS NULL OR TRIM(nationality) = '')", null, false);
        $this->db->update('students', array('nationality' => $default));
        $detail['nationality'] = (int) $this->db->affected_rows();

        $this->db->where("(citizenship IS NULL OR TRIM(citizenship) = '')", null, false);
        $this->db->update('students', array('citizenship' => $default));
        $detail['citizenship'] = (int) $this->db->affected_rows();

        $this->db->where("(disability_status IS NULL OR TRIM(disability_status) = '')", null, false);
        $this->db->update('students', array(
            'disability_status' => $default,
            'disability_type'   => '',
        ));
        $detail['disability_status'] = (int) $this->db->affected_rows();

        return array(
            'updated_rows' => array_sum($detail),
            'by_field'     => $detail,
        );
    }

    /**
     * Move ethnicity from "Unknown" → "General" (HEMIS-style broad category). Run after fill_unknown if desired.
     *
     * @return array{updated_rows:int}
     */
    protected function apply_general_ethnicity()
    {
        $this->db->where('ethnicity', self::DEFAULT_UNKNOWN);
        $this->db->update('students', array('ethnicity' => self::DEFAULT_GENERAL));
        return array('updated_rows' => (int) $this->db->affected_rows());
    }

    /**
     * Normalize gender to Male / Female / Other; map HEMIS codes 1/2/3; fill empty with Other.
     *
     * @return array{updated_rows:int}
     */
    protected function apply_gender_normalization()
    {
        $rows = $this->db->select('id, gender')->from('students')->get()->result_array();
        $updated = 0;

        foreach ($rows as $row) {
            $new = $this->resolve_gender(isset($row['gender']) ? $row['gender'] : '');
            if ((string) $row['gender'] === $new) {
                continue;
            }
            $this->db->where('id', (int) $row['id']);
            $this->db->update('students', array('gender' => $new));
            if ($this->db->affected_rows() > 0) {
                $updated++;
            }
        }

        return array('updated_rows' => $updated);
    }

    /**
     * Canonical gender label for DB + reports (Male / Female / Other). HEMIS codes 1/2/3 are accepted.
     *
     * @param string|null $raw
     * @return string
     */
    protected function resolve_gender($raw)
    {
        $g = is_string($raw) ? trim($raw) : '';
        if ($g === '') {
            return self::GENDER_OTHER;
        }

        if (isset($this->gender_code_map[$g])) {
            return $this->gender_code_map[$g];
        }

        $lower = strtolower($g);
        if ($lower === 'male' || $lower === 'm') {
            return self::GENDER_MALE;
        }
        if ($lower === 'female' || $lower === 'f') {
            return self::GENDER_FEMALE;
        }
        if ($lower === 'other' || $lower === 'o') {
            return self::GENDER_OTHER;
        }

        if ($g === self::GENDER_MALE || $g === self::GENDER_FEMALE || $g === self::GENDER_OTHER) {
            return $g;
        }

        return self::GENDER_OTHER;
    }
}
