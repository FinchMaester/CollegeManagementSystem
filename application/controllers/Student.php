<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Student extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('program_model', 'student_edit_field_model', 'state_model', 'district_model', 'municipality_model'));
        $this->load->library('media_storage');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /**
     * Store optional HEMIS documents into students.* path columns.
     * Uses Media_storage::fileupload() and saves relative "uploads/..." paths.
     *
     * @param array $student_data
     * @return array Updated student_data
     */
    protected function _merge_hemis_student_doc_uploads_into_student_data(array $student_data)
    {
        $upload_dir = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_documents';
        $rel_prefix = 'uploads/student_documents/';

        $map = array(
            'citizenship_front' => 'citizenship_front',
            'citizenship_back'  => 'citizenship_back',
            'nid_pic'           => 'nid_pic',
            'pp_size_photo'     => 'pp_size_photo',
            'digital_signature' => 'digital_signature',
            'receipt_image'     => 'receipt_image',
        );

        foreach ($map as $input => $col) {
            $fn = $this->media_storage->fileupload($input, $upload_dir);
            if ($fn) {
                $student_data[$col] = $rel_prefix . $fn;
            }
        }
        return $student_data;
    }

    /**
     * Merge the "extended" student profile fields that the create/edit forms post but the
     * controller previously dropped on the floor (so they never reached the students row).
     *
     * Covers plain text/number columns, boolean checkboxes, and the repeatable previous-study
     * inputs (posted as parallel arrays, stored as a single column). Each column is only written
     * when it actually exists in the schema AND the field was present in this submission, so a
     * disabled form section can never null out previously saved data.
     *
     * @param array $data students insert/update payload
     * @return array
     */
    protected function _merge_extended_student_fields_into_student_data(array $data)
    {
        $scalar_fields = array(
            'nationality', 'disability_status', 'disability_type',
            'program_fee', 'scholarship_amt', 'paid_amount',
            'entrance_score', 'entrance_symbol', 'medium', 'shift', 'type',
            'semister_or_year_no', 'father_email', 'mother_email',
        );
        foreach ($scalar_fields as $col) {
            if (!array_key_exists($col, $_POST) || !$this->db->field_exists($col, 'students')) {
                continue;
            }
            $value = trim((string) $this->input->post($col));
            $data[$col] = ($value === '') ? null : $value;
        }

        $boolean_fields = array('is_verified', 'has_scolorship', 'is_mukta_kamaiya', 'is_from_martyr_family');
        foreach ($boolean_fields as $col) {
            if (!array_key_exists($col, $_POST) || !$this->db->field_exists($col, 'students')) {
                continue;
            }
            $data[$col] = ((int) $this->input->post($col) === 1) ? 1 : 0;
        }

        // Previous study section posts repeatable rows (name="...[]"). These columns carry a
        // json_valid() CHECK constraint (students_chk_1..4) and the edit view json_decode()s them,
        // so they MUST be stored as a JSON array — a plain string violates the constraint and makes
        // the entire UPDATE fail. Preserve row order; store NULL when nothing meaningful was entered.
        $previous_json_fields = array(
            'previous_level_of_study',
            'previous_board_university_college',
            'previous_registration_no',
            'previous_institution_name',
        );
        foreach ($previous_json_fields as $col) {
            if (!array_key_exists($col, $_POST) || !$this->db->field_exists($col, 'students')) {
                continue;
            }
            $raw = $this->input->post($col);
            if (!is_array($raw)) {
                $raw = (trim((string) $raw) === '') ? array() : array($raw);
            }
            $values = array();
            $has_value = false;
            foreach ($raw as $item) {
                $item = trim((string) $item);
                $values[] = $item;
                if ($item !== '') {
                    $has_value = true;
                }
            }
            $data[$col] = $has_value ? json_encode(array_values($values)) : null;
        }

        return $data;
    }

    /**
     * Merge student profile / parent photo uploads into the students payload.
     * Files land in uploads/student_images/ (same convention as the online-admission flow).
     * A column is only set when a new file was actually uploaded, so editing without re-uploading
     * keeps the existing photo.
     *
     * @param array $data students insert/update payload
     * @return array
     */
    protected function _merge_student_photo_uploads_into_student_data(array $data)
    {
        $upload_dir = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_images';
        $rel_prefix = 'uploads/student_images/';

        $map = array(
            'file'         => 'image', // student profile photo (input name="file")
            'father_pic'   => 'father_pic',
            'mother_pic'   => 'mother_pic',
            'guardian_pic' => 'guardian_pic',
        );
        foreach ($map as $input => $col) {
            if (!$this->db->field_exists($col, 'students')) {
                continue;
            }
            $fn = $this->media_storage->fileupload($input, $upload_dir);
            if ($fn) {
                $data[$col] = $rel_prefix . $fn;
            }
        }
        return $data;
    }

    /**
     * Upload one file from an array-style file input ($_FILES[$input][...][$index]).
     * Mirrors Media_storage::fileupload naming ("<ts>-<rand>!<original>") so existing
     * download/view helpers keep working.
     *
     * @param string $input    file input name (e.g. "additional_doc")
     * @param int    $index    array index within the input
     * @param string $abs_dir  absolute target directory
     * @return string|false stored filename, or false when nothing was uploaded
     */
    protected function _upload_array_file($input, $index, $abs_dir)
    {
        if (!isset($_FILES[$input]['name'][$index])) {
            return false;
        }
        $orig = $_FILES[$input]['name'][$index];
        $err  = isset($_FILES[$input]['error'][$index]) ? $_FILES[$input]['error'][$index] : UPLOAD_ERR_NO_FILE;
        if ($orig === '' || $err !== UPLOAD_ERR_OK) {
            return false;
        }
        if (!is_dir($abs_dir) && !mkdir($abs_dir, 0755, true)) {
            log_message('error', 'Failed to create directory: ' . $abs_dir);
            return false;
        }
        $newfilename = round(microtime(true)) . '-' . rand() . time() . '!' . $orig;
        $destination = rtrim($abs_dir, "/\\") . DIRECTORY_SEPARATOR . $newfilename;
        if (move_uploaded_file($_FILES[$input]['tmp_name'][$index], $destination)) {
            return $newfilename;
        }
        log_message('error', 'Failed to move uploaded file to: ' . $destination);
        return false;
    }

    /**
     * Persist the "Upload Documents" section into student_doc for both the create form
     * (additional_title[]/additional_doc[]) and the edit form (first/second/fourth/fifth_* fixed
     * slots plus dynamic_*[] rows). New uploads insert or replace; titles update in place; rows
     * with no new file keep their existing document. Deletion is handled separately (removeDocument).
     *
     * @param int $student_id
     * @return void
     */
    protected function _save_student_documents($student_id)
    {
        $student_id = (int) $student_id;
        if ($student_id < 1) {
            return;
        }

        $abs_dir = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_documents';
        $rel_prefix = 'uploads/student_documents/';

        // Edit form: fixed slots (note the view skips "third"). [title field, id field, old field].
        $fixed_slots = array(
            'first_doc'  => array('first_title', 'first_doc_id', 'first_doc_old'),
            'second_doc' => array('second_title', 'second_doc_id', 'second_doc_old'),
            'fourth_doc' => array('fourth_title', 'fourth_doc_id', 'fourth_doc_old'),
            'fifth_doc'  => array('fifth_title', 'fifth_doc_id', 'fifth_doc_old'),
        );
        foreach ($fixed_slots as $file_input => $meta) {
            list($title_field, $id_field, $old_field) = $meta;
            if (!array_key_exists($title_field, $_POST) && !isset($_FILES[$file_input])) {
                continue;
            }
            $title  = trim((string) $this->input->post($title_field));
            $doc_id = (int) $this->input->post($id_field);
            $new_fn = $this->media_storage->fileupload($file_input, $abs_dir);

            if ($doc_id > 0) {
                $update = array('title' => $title);
                if ($new_fn) {
                    $update['doc'] = $rel_prefix . $new_fn;
                }
                $this->student_model->updatedoc($doc_id, $update);
            } elseif ($new_fn) {
                $this->student_model->adddoc(array(
                    'student_id' => $student_id,
                    'title'      => $title,
                    'doc'        => $rel_prefix . $new_fn,
                ));
            }
        }

        // Edit form: dynamic extra rows.
        $dynamic_titles = $this->input->post('dynamic_title');
        if (is_array($dynamic_titles)) {
            $dynamic_ids = $this->input->post('dynamic_doc_id');
            $dynamic_ids = is_array($dynamic_ids) ? $dynamic_ids : array();
            foreach ($dynamic_titles as $i => $title) {
                $title  = trim((string) $title);
                $doc_id = isset($dynamic_ids[$i]) ? (int) $dynamic_ids[$i] : 0;
                $new_fn = $this->_upload_array_file('dynamic_doc', $i, $abs_dir);
                if ($doc_id > 0) {
                    $update = array('title' => $title);
                    if ($new_fn) {
                        $update['doc'] = $rel_prefix . $new_fn;
                    }
                    $this->student_model->updatedoc($doc_id, $update);
                } elseif ($new_fn) {
                    $this->student_model->adddoc(array(
                        'student_id' => $student_id,
                        'title'      => $title,
                        'doc'        => $rel_prefix . $new_fn,
                    ));
                }
            }
        }

        // Create form: additional document rows.
        $additional_titles = $this->input->post('additional_title');
        if (is_array($additional_titles)) {
            foreach ($additional_titles as $i => $title) {
                $title  = trim((string) $title);
                $new_fn = $this->_upload_array_file('additional_doc', $i, $abs_dir);
                if ($new_fn) {
                    $this->student_model->adddoc(array(
                        'student_id' => $student_id,
                        'title'      => $title,
                        'doc'        => $rel_prefix . $new_fn,
                    ));
                }
            }
        }
    }

    /**
     * Auto label (First/Second/…) from Class + UGC program_type. Source of truth for semister_or_year_no.
     *
     * @param int $class_id
     * @param int $ugc_program_id
     * @return string
     */
    protected function _derive_semister_or_year_no($class_id, $ugc_program_id = 0)
    {
        $this->load->helper('hemis_sync_post_from_db');
        if (!function_exists('hemis_student_derive_semister_or_year_no')) {
            return '';
        }
        return (string) hemis_student_derive_semister_or_year_no((int) $class_id, (int) $ugc_program_id);
    }

    /**
     * Resolve UGC fiscal year id for a campus session (sessions.ugc_fiscal_year_id → POST → active FY).
     *
     * @param int $session_id
     * @return int
     */
    protected function _resolve_ugc_fiscal_year_id_for_session($session_id = 0)
    {
        $session_id = (int) $session_id;
        if ($session_id > 0 && $this->db->field_exists('ugc_fiscal_year_id', 'sessions')) {
            $row = $this->db->select('ugc_fiscal_year_id')->from('sessions')->where('id', $session_id)->limit(1)->get()->row_array();
            if (!empty($row['ugc_fiscal_year_id'])) {
                return (int) $row['ugc_fiscal_year_id'];
            }
        }
        $posted = (int) $this->input->post('ugc_fiscal_year_id');
        if ($posted > 0) {
            return $posted;
        }
        if ($this->db->table_exists('ugc_fiscal_years')) {
            $fy = $this->db->select('id')->from('ugc_fiscal_years')->where('active_fiscal_year', 1)->order_by('id', 'DESC')->limit(1)->get()->row_array();
            if (!empty($fy['id'])) {
                return (int) $fy['id'];
            }
        }
        return 0;
    }

    /**
     * Build student_session write payload (placement SoT + year-scoped HEMIS fields).
     * students.class_id/section_id/program_id remain a compatibility cache only.
     *
     * @param int   $student_id
     * @param int   $session_id
     * @param array $extra
     * @return array
     */
    protected function _build_student_session_payload($student_id, $session_id, array $extra = array())
    {
        $session_id = (int) $session_id;
        $fy = $this->_resolve_ugc_fiscal_year_id_for_session($session_id);
        $class_id = (int) $this->input->post('class_id');
        $ugc_program_id = (int) $this->input->post('ugc_program_id');
        $semister = $this->_derive_semister_or_year_no($class_id, $ugc_program_id);
        $payload = array(
            'student_id' => (int) $student_id,
            'session_id' => $session_id,
            'class_id'   => $class_id,
            'section_id' => (int) $this->input->post('section_id'),
            'program_id' => (int) $this->input->post('program_id'),
            'is_active'  => 'yes',
        );
        if ($this->db->field_exists('ugc_fiscal_year_id', 'student_session') && $fy > 0) {
            $payload['ugc_fiscal_year_id'] = $fy;
        }
        if ($this->db->field_exists('semister_or_year_no', 'student_session') && $semister !== '') {
            $payload['semister_or_year_no'] = $semister;
        }
        return array_merge($payload, $extra);
    }

    /**
     * Store students.ugc_fiscal_year_id from POST (with active/latest FY fallback when empty),
     * and mirror the same value into students.fiscal_year_id when that column exists.
     *
     * Graduation / legacy integrations read fiscal_year_id; HEMIS outbound helpers fall back to it
     * when ugc_fiscal_year_id is null — keeping both in sync avoids NULL fiscal_year_id rows.
     *
     * @param array $payload Reference to insert/update payload for students.
     */
    protected function _merge_ugc_fiscal_year_columns_into_student_payload(array &$payload)
    {
        if (!$this->db->field_exists('ugc_fiscal_year_id', 'students')) {
            return;
        }

        $ugc_fiscal_year_id = (int) $this->input->post('ugc_fiscal_year_id');

        if ($ugc_fiscal_year_id < 1 && $this->db->table_exists('ugc_fiscal_years')) {
            $fy = $this->db->select('id')
                ->from('ugc_fiscal_years')
                ->where('active_fiscal_year', 1)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($fy['id'])) {
                $ugc_fiscal_year_id = (int) $fy['id'];
            } else {
                $fy2 = $this->db->select('id')
                    ->from('ugc_fiscal_years')
                    ->order_by('idx', 'DESC')
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row_array();
                if (!empty($fy2['id'])) {
                    $ugc_fiscal_year_id = (int) $fy2['id'];
                }
            }
        }

        $resolved = $ugc_fiscal_year_id > 0 ? $ugc_fiscal_year_id : null;
        $payload['ugc_fiscal_year_id'] = $resolved;

        if ($this->db->field_exists('fiscal_year_id', 'students')) {
            $payload['fiscal_year_id'] = $resolved;
        }
    }

    /**
     * HTML fragment for flash when local save succeeded but outbound HEMIS sync reported an error.
     * (StudentController never surfaced sync status before; failures still land in students.hemis_sync_error.)
     *
     * @param array $out Return array from Hemis_client::sync_student_* methods.
     */
    protected function _hemis_failed_sync_notice_html(array $out)
    {
        if (!empty($out['synced']) || empty($out['error'])) {
            return '';
        }
        return '<div class="alert alert-warning text-left" style="margin-top:10px"><strong>HEMIS / UGC sync:</strong> '
            . html_escape((string) $out['error'])
            . ' The student record was saved locally; see students.hemis_sync_error or application logs.</div>';
    }

    /**
     * Block outbound HEMIS sync when permanent/temporary combined codes cannot be resolved (matches helper + remote API rules).
     *
     * @param int $student_id
     * @return string|null human-readable reason, or null when sync may proceed
     */
    protected function _student_hemis_combined_gate_message($student_id)
    {
        $student_id = (int) $student_id;
        if ($student_id < 1) {
            return 'HEMIS / UGC sync skipped: invalid student id.';
        }
        $this->load->helper('hemis_sync_post_from_db');
        $this->load->model('student_model');
        $row = $this->student_model->get_student_by_id($student_id);
        if (!is_array($row)) {
            return 'HEMIS / UGC sync skipped: student record could not be loaded.';
        }
        $probe = hemis_student_db_row_to_create_post($row);
        $pc = isset($probe['pCombinedCode']) ? trim((string) $probe['pCombinedCode']) : '';
        $sameRaw = isset($probe['isSameAsPermanent']) ? $probe['isSameAsPermanent'] : '';
        $same = (strtolower(trim((string) $sameRaw)) === 'true' || $sameRaw === true || $sameRaw === 1 || $sameRaw === '1');
        $tc = isset($probe['tCombinedCode']) ? trim((string) $probe['tCombinedCode']) : '';
        if ($pc === '') {
            return 'HEMIS / UGC sync skipped: permanent municipality combined code (pCombinedCode) is missing or could not be uniquely resolved from ugc_addresses. Select province, district, and municipality on the form.';
        }
        if (!$same && $tc === '') {
            return 'HEMIS / UGC sync skipped: temporary municipality combined code (tCombinedCode) is required when the address is not the same as permanent.';
        }

        return null;
    }

    /**
     * Persist gate failure on students.hemis_sync_error and return a Hemis_client-shaped result.
     *
     * @param int    $student_id
     * @param string $gateMsg
     * @return array
     */
    protected function _student_hemis_skip_sync_outcome($student_id, $gateMsg)
    {
        $student_id = (int) $student_id;
        $maxErr = 1000;
        if ($this->db->field_exists('hemis_sync_error', 'students')) {
            $col = $this->db->query("SHOW COLUMNS FROM `students` LIKE 'hemis_sync_error'")->row_array();
            if (!empty($col['Type']) && preg_match('/varchar\((\d+)\)/i', (string) $col['Type'], $mm)) {
                $maxErr = max(1, (int) $mm[1]);
            }
        }
        $upd = array('hemis_sync_error' => substr($gateMsg, 0, $maxErr));
        if ($this->db->field_exists('hemis_sync_error_at', 'students')) {
            $upd['hemis_sync_error_at'] = date('Y-m-d H:i:s');
        }
        $this->load->model('student_model');
        $this->student_model->update_hemis_fields($student_id, $upd);

        return array(
            'synced'    => false,
            'error'     => $gateMsg,
            'mode'      => 'local_only',
            'http_code' => 0,
        );
    }

    public function search()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        $data['title'] = 'Student Search';
        
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['fields'] = $this->customfield_model->get_custom_fields('students', 1);
        $filters = $this->session->userdata('student_search_filters');
        $data['restore_student_search'] = is_array($filters) ? $filters : null;

        $this->load->model('hemis_integration_model');
        $hemisCfg = $this->hemis_integration_model->get_effective_config();
        $data['hemis_mock_mode'] = !empty($hemisCfg['mock_mode']);
        
        $this->load->view('layout/header', $data);
        $this->load->view('student/studentSearch', $data);
        $this->load->view('layout/footer', $data);
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/create');

        $data['title'] = 'Add Student';
        $data['title_list'] = 'Student Admission';
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = isset($this->sch_setting_detail->adm_auto_insert) ? (int) $this->sch_setting_detail->adm_auto_insert : 0;
        $data['genderList'] = $this->customlib->getGender();
        $data['classlist'] = $this->class_model->get();
        $data['categorylist'] = $this->category_model->get();
        $data['houses'] = $this->houselist_model->get();
        $data['hostelList'] = $this->hostel_model->get();
        $current_session = $this->setting_model->getCurrentSession();
        $this->load->model('transportfee_model');
        $data['transport_fees'] = $this->transportfee_model->getSessionFees($current_session);
        $data['vehroutelist'] = $this->vehroute_model->getRouteVehiclesList();
        $data['feesessiongroup_model'] = $this->feesessiongroup_model->getFeesByGroup();
        $data['provinces'] = $this->state_model->getAll();
        $data['fields'] = $this->customfield_model->get_custom_fields('students', 1);
        $data['bloodgroup'] = $this->config->item('bloodgroup');
        $data['count'] = 0;
        // Option lists for religion/caste dropdowns (same as student edit).
        $data['religions'] = array();
        $data['castes']    = array();
        $data['ethnicities'] = $this->_ethnicity_option_list();
        $religion_rows = $this->db->select('religion')->from('students')->where('religion IS NOT NULL', null, false)->where('religion !=', '')->group_by('religion')->order_by('religion', 'ASC')->get()->result_array();
        foreach ($religion_rows as $row) {
            $value = trim((string) $row['religion']);
            if ($value !== '') {
                $data['religions'][$value] = $value;
            }
        }
        $caste_rows = $this->db->select('cast')->from('students')->where('cast IS NOT NULL', null, false)->where('cast !=', '')->group_by('cast')->order_by('cast', 'ASC')->get()->result_array();
        foreach ($caste_rows as $row) {
            $value = trim((string) $row['cast']);
            if ($value !== '') {
                $data['castes'][$value] = $value;
            }
        }

        if (strtoupper($this->input->method(true)) === 'POST') {
            $is_ajax = $this->input->is_ajax_request();

            if (!$data['adm_auto_insert']) {
                $this->form_validation->set_rules('admission_no', $this->lang->line('admission_no'), 'trim|required');
            }
            $this->form_validation->set_rules('firstname', $this->lang->line('first_name'), 'trim|required');
            $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|is_natural_no_zero');
            // HEMIS showstopper fields: do not allow local save without UGC-synced lookups.
            $this->form_validation->set_rules('ugc_program_id', 'UGC Program (HEMIS)', 'trim|required|is_natural_no_zero|callback__ugc_program_exists');
            $this->form_validation->set_rules('ugc_batch_id', 'UGC Batch (HEMIS)', 'trim|required|is_natural_no_zero|callback__ugc_batch_exists');
            $this->form_validation->set_rules('ugc_fiscal_year_id', 'UGC Fiscal Year (HEMIS)', 'trim|required|is_natural_no_zero|callback__ugc_fiscal_year_exists');
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required');
            $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required');
            $this->form_validation->set_rules('academic_program_duration', $this->lang->line('academic_program_duration'), 'trim|required');
            $this->form_validation->set_rules('first_name_np', $this->lang->line('first_name_np'), 'trim|required');
            // HEMIS-required fields (prevent local save that will always fail sync).
            $this->form_validation->set_rules('permanent_ward_no', $this->lang->line('permanent_ward_no'), 'trim|required|is_natural_no_zero');
            // Ethnicity is not present in the default theme; if the form doesn't post it, block save with clear error.
            $this->form_validation->set_rules('ethnicity', 'Ethnicity (HEMIS)', 'trim|required');
            if ($this->db->field_exists('admissionYearId', 'students')) {
                $this->form_validation->set_rules('admissionYearId', 'Admission Year ID (HEMIS)', 'trim|required|is_natural_no_zero');
            }
            if (!empty($this->sch_setting_detail->lastname)) {
                $this->form_validation->set_rules('lastname', $this->lang->line('last_name'), 'trim|required');
                $this->form_validation->set_rules('last_name_np', $this->lang->line('last_name_np'), 'trim|required');
            }

            if ($this->form_validation->run()) {
                $admission_no = trim((string) $this->input->post('admission_no'));
                if ($admission_no === '') {
                    $admission_no = 'AUTO_' . time();
                }

                $student_data = array(
                    'admission_no'      => $admission_no,
                    'firstname'         => $this->input->post('firstname'),
                    'middlename'        => $this->input->post('middlename'),
                    'lastname'          => $this->input->post('lastname'),
                    'first_name_np'     => $this->input->post('first_name_np'),
                    'middle_name_np'    => $this->input->post('middle_name_np'),
                    'last_name_np'      => $this->input->post('last_name_np'),
                    'gender'            => $this->input->post('gender'),
                    'mobileno'          => $this->input->post('mobileno'),
                    'email'             => $this->input->post('email'),
                    'admission_year'    => $this->_resolve_admission_year_from_post(),
                    'roll_no'           => trim((string) $this->input->post('roll_no')) !== ''
                        ? trim((string) $this->input->post('roll_no')) : null,
                    'academic_program_duration' => $this->input->post('academic_program_duration'),
                    'admission_date'    => $this->customlib->dateFormatToYYYYMMDD($this->input->post('admission_date')),
                    'dob'               => $this->customlib->dateFormatToYYYYMMDD($this->input->post('dob')),
                    'dob_ad'            => $this->customlib->dateFormatToYYYYMMDD($this->input->post('dob_ad')),
                    'category_id'       => $this->input->post('category_id'),
                    'current_address'   => $this->input->post('current_address'),
                    'permanent_address' => $this->input->post('permanent_address'),
                    'father_name'       => $this->input->post('father_name'),
                    'father_phone'      => $this->input->post('father_phone'),
                    'mother_name'       => $this->input->post('mother_name'),
                    'mother_phone'      => $this->input->post('mother_phone'),
                    'guardian_name'     => $this->input->post('guardian_name'),
                    'guardian_phone'    => $this->input->post('guardian_phone'),
                    'guardian_relation' => $this->input->post('guardian_relation'),
                    'guardian_email'    => $this->input->post('guardian_email'),
                    'guardian_is'       => $this->input->post('guardian_is'),
                    'guardian_occupation' => $this->input->post('guardian_occupation'),
                    'guardian_address'  => $this->input->post('guardian_address'),
                    'father_occupation' => $this->input->post('father_occupation'),
                    'mother_occupation' => $this->input->post('mother_occupation'),
                    'temporary_province'      => $this->input->post('temporary_province'),
                    'temporary_district'      => $this->input->post('temporary_district'),
                    'temporary_local_level'   => $this->input->post('temporary_local_level'),
                    'temporary_ward_no'       => $this->input->post('temporary_ward_no'),
                    'temporary_house_no'      => $this->input->post('temporary_house_no'),
                    'permanent_province'      => $this->input->post('permanent_province'),
                    'permanent_district'      => $this->input->post('permanent_district'),
                    'permanent_local_level'   => $this->input->post('permanent_local_level'),
                    'permanent_ward_no'       => $this->input->post('permanent_ward_no'),
                    'permanent_house_no'      => $this->input->post('permanent_house_no'),
                    'bank_account_no'         => $this->input->post('bank_account_no'),
                    'bank_name'               => $this->input->post('bank_name'),
                    'adhar_no'                => $this->input->post('adhar_no'),
                    'samagra_id'              => $this->input->post('samagra_id'),
                    'rte'                     => $this->input->post('rte'),
                    'note'              => $this->input->post('note'),
                    'is_active'         => 'yes',
                    'religion'          => $this->input->post('religion'),
                    'edj'               => $this->input->post('edj'),
                    'blood_group'       => $this->input->post('blood_group'),
                    'school_house_id'   => ($this->input->post('house') !== '' && (int) $this->input->post('house') > 0)
                        ? (int) $this->input->post('house') : null,
                    'height'            => $this->input->post('height'),
                    'weight'            => $this->input->post('weight'),
                );
                // Mirror class/program placement on students row as legacy cache only.
                // Source of truth for placement is student_session (see add_student_session below).
                if ($this->db->field_exists('program_id', 'students')) {
                    $student_data['program_id'] = (int) $this->input->post('program_id');
                }
                if ($this->db->field_exists('class_id', 'students')) {
                    $student_data['class_id'] = (int) $this->input->post('class_id');
                }
                if ($this->db->field_exists('section_id', 'students')) {
                    $student_data['section_id'] = (int) $this->input->post('section_id');
                }
                if ($this->db->field_exists('ifsc_code', 'students')) {
                    $student_data['ifsc_code'] = $this->input->post('ifsc_code');
                }
                // HEMIS ethnicity is the source of truth; legacy `cast` is mirrored for old reports.
                $ethnicity_val = trim((string) $this->input->post('ethnicity'));
                if ($this->db->field_exists('ethnicity', 'students')) {
                    $student_data['ethnicity'] = $ethnicity_val !== '' ? $ethnicity_val : null;
                }
                if ($this->db->field_exists('cast', 'students')) {
                    $student_data['cast'] = $ethnicity_val !== '' ? $ethnicity_val : ($this->input->post('cast') ?: null);
                }

                // Optional HEMIS documents (citizenship/NID/photo/signature/receipt).
                $student_data = $this->_merge_hemis_student_doc_uploads_into_student_data($student_data);
                // Extended profile fields (nationality, disability, fees, previous study, flags, etc.).
                $student_data = $this->_merge_extended_student_fields_into_student_data($student_data);
                // Academic year/semester label is derived from Class + UGC program_type (not free-text).
                if ($this->db->field_exists('semister_or_year_no', 'students')) {
                    $derivedSy = $this->_derive_semister_or_year_no(
                        (int) $this->input->post('class_id'),
                        (int) $this->input->post('ugc_program_id')
                    );
                    if ($derivedSy !== '') {
                        $student_data['semister_or_year_no'] = $derivedSy;
                    }
                }
                // Student / parent profile photos.
                $student_data = $this->_merge_student_photo_uploads_into_student_data($student_data);

                // Store UGC Program FK if column exists (added by Ugc_metadata_model lazily).
                if ($this->db->field_exists('ugc_program_id', 'students')) {
                    $ugc_program_id = (int) $this->input->post('ugc_program_id');
                    $student_data['ugc_program_id'] = $ugc_program_id > 0 ? $ugc_program_id : null;
                }
                if ($this->db->field_exists('ugc_batch_id', 'students')) {
                    $ugc_batch_id = (int) $this->input->post('ugc_batch_id');
                    $student_data['ugc_batch_id'] = $ugc_batch_id > 0 ? $ugc_batch_id : null;
                    // Legacy campus API readers still use batch_id — keep mirrored from UGC batch.
                    if ($this->db->field_exists('batch_id', 'students') && $ugc_batch_id > 0) {
                        $student_data['batch_id'] = $ugc_batch_id;
                    }
                }
                $this->_merge_ugc_fiscal_year_columns_into_student_payload($student_data);
                if ($this->db->field_exists('batch_name', 'students')) {
                    $student_data['batch_name'] = $this->input->post('batch_name') ?: null;
                }
                if ($this->db->field_exists('batch_name_nepali', 'students')) {
                    $student_data['batch_name_nepali'] = $this->input->post('batch_name_nepali') ?: null;
                }
                // HEMIS admissionYearId (numeric). If column exists, store whatever operator provided.
                if ($this->db->field_exists('admissionYearId', 'students')) {
                    $aid = (int) $this->input->post('admissionYearId');
                    $student_data['admissionYearId'] = $aid > 0 ? $aid : null;
                }
                if ($this->db->field_exists('complition_year', 'students')) {
                    $cy = trim((string) $this->input->post('complition_year'));
                    $student_data['complition_year'] = ($cy !== '' && ctype_digit($cy) && (int) $cy > 0) ? (int) $cy : null;
                }
                if ($this->db->field_exists('citizenship', 'students')) {
                    $c = trim((string) $this->input->post('citizenship'));
                    $student_data['citizenship'] = $c !== '' ? $c : null;
                }
                if ($this->db->field_exists('citizenship_issue_dist', 'students')) {
                    $cid = trim((string) $this->input->post('citizenship_issue_dist'));
                    $student_data['citizenship_issue_dist'] = $cid !== '' ? $cid : null;
                }
                if ($this->db->field_exists('ugc_t_province', 'students')) {
                    $student_data['ugc_t_province'] = $this->input->post('ugc_t_province_name') ?: null;
                    $student_data['ugc_t_district'] = $this->input->post('ugc_t_district_name') ?: null;
                    $student_data['ugc_t_local_level'] = $this->input->post('ugc_t_local_level_name') ?: null;
                    $student_data['ugc_p_province'] = $this->input->post('ugc_p_province_name') ?: null;
                    $student_data['ugc_p_district'] = $this->input->post('ugc_p_district_name') ?: null;
                    $student_data['ugc_p_local_level'] = $this->input->post('ugc_p_local_level_name') ?: null;
                }
                // Deterministic address anchor (combinedCode).
                if ($this->db->field_exists('ugc_p_combined_code', 'students')) {
                    $student_data['ugc_p_combined_code'] = $this->input->post('ugc_p_combined_code') ?: null;
                }
                if ($this->db->field_exists('ugc_t_combined_code', 'students')) {
                    $student_data['ugc_t_combined_code'] = $this->input->post('ugc_t_combined_code') ?: null;
                }

                $measure_date = $this->input->post('measure_date');
                if (!empty($measure_date)) {
                    $student_data['measurement_date'] = $this->customlib->dateFormatToYYYYMMDD($measure_date);
                }

                $student_id = $this->student_model->add($student_data, array('adm_auto_insert' => $data['adm_auto_insert']));
                if ($student_id) {
                    $current_session_id = (int) $this->setting_model->getCurrentSession();
                    $this->student_model->add_student_session(
                        $this->_build_student_session_payload((int) $student_id, $current_session_id)
                    );
                    // Persist any uploaded documents (Upload Documents section).
                    $this->_save_student_documents((int) $student_id);
                    $this->load->library('hemis_client');
                    $gateMsg = $this->_student_hemis_combined_gate_message((int) $student_id);
                    if ($gateMsg !== null) {
                        $hemis_out = $this->_student_hemis_skip_sync_outcome((int) $student_id, $gateMsg);
                    } else {
                        $hemis_out = $this->hemis_client->sync_student_after_database_snapshot((int) $student_id);
                    }
                    log_message('info', 'HEMIS real-time sync after UI student admission: students.id=' . (int) $student_id
                        . ' synced=' . (!empty($hemis_out['synced']) ? '1' : '0'));
                    if ($is_ajax) {
                        $fresh_row = $this->student_model->get_student_by_id((int) $student_id);
                        $persisted_hemis_err = '';
                        if (is_array($fresh_row) && $this->db->field_exists('hemis_sync_error', 'students')) {
                            $persisted_hemis_err = isset($fresh_row['hemis_sync_error'])
                                ? trim((string) $fresh_row['hemis_sync_error']) : '';
                        }
                        $this->output
                            ->set_content_type('application/json')
                            ->set_output(json_encode(array(
                                'success'    => true,
                                'message'    => $this->lang->line('success_message'),
                                'student_id' => (int) $student_id,
                                'hemis_sync' => $hemis_out,
                                'hemis_sync_error' => ($persisted_hemis_err !== '') ? $persisted_hemis_err : null,
                            )));
                        return;
                    }
                    $msg = '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>';
                    $msg .= $this->_hemis_failed_sync_notice_html(is_array($hemis_out) ? $hemis_out : array());
                    $this->session->set_flashdata('msg', $msg);
                    redirect('student/create');
                    return;
                }

                $fail = 'Unable to save student admission. Please check admission number and try again.';
                if ($is_ajax) {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(array(
                            'success' => false,
                            'message' => $fail,
                        )));
                    return;
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $fail . '</div>');
            } elseif ($is_ajax) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => false,
                        'message' => $this->lang->line('fields_values_required'),
                        'errors'  => $this->form_validation->error_array(),
                    )));
                return;
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('student/studentCreate', $data);
        $this->load->view('layout/footer', $data);
    }

    public function _ugc_program_exists($ugc_program_id)
    {
        $id = (int) $ugc_program_id;
        if ($id < 1) {
            return true;
        }
        if (!$this->db->table_exists('ugc_programs')) {
            $this->form_validation->set_message('_ugc_program_exists', 'UGC programs cache is not ready. Please refresh metadata and try again.');
            return false;
        }
        $row = $this->db->select('program_id')->from('ugc_programs')->where('program_id', $id)->get()->row_array();
        if (empty($row)) {
            $this->form_validation->set_message('_ugc_program_exists', 'Selected UGC Program no longer exists in local metadata cache. Please refresh metadata and re-select.');
            return false;
        }
        return true;
    }

    /**
     * Ethnicity dropdown/datalist options (HEMIS). Merges curated defaults with DB values.
     *
     * @return array value => label
     */
    protected function _ethnicity_option_list()
    {
        $defaults = array(
            'Brahmin', 'Kshetri', 'Chhetri', 'Tharu', 'Dalit', 'Newar', 'Magar',
            'Gurung', 'Rai', 'Limbu', 'Tamang', 'Sherpa', 'Janjati', 'Madhesi', 'Muslim', 'Other',
        );
        $out = array();
        foreach ($defaults as $v) {
            $out[$v] = $v;
        }
        if ($this->db->field_exists('ethnicity', 'students')) {
            $rows = $this->db->select('ethnicity')
                ->from('students')
                ->where('ethnicity IS NOT NULL', null, false)
                ->where('ethnicity !=', '')
                ->group_by('ethnicity')
                ->order_by('ethnicity', 'ASC')
                ->get()->result_array();
            foreach ($rows as $row) {
                $value = trim((string) $row['ethnicity']);
                if ($value !== '') {
                    $out[$value] = $value;
                }
            }
        }
        if ($this->db->field_exists('cast', 'students')) {
            $rows = $this->db->select('cast')
                ->from('students')
                ->where('cast IS NOT NULL', null, false)
                ->where('cast !=', '')
                ->group_by('cast')
                ->order_by('cast', 'ASC')
                ->get()->result_array();
            foreach ($rows as $row) {
                $value = trim((string) $row['cast']);
                if ($value !== '') {
                    $out[$value] = $value;
                }
            }
        }
        asort($out, SORT_NATURAL | SORT_FLAG_CASE);
        return $out;
    }

    public function _ugc_batch_exists($ugc_batch_id)
    {
        $id = (int) $ugc_batch_id;
        if ($id < 1) {
            return true;
        }
        if (!$this->db->table_exists('ugc_batches')) {
            $this->form_validation->set_message('_ugc_batch_exists', 'UGC batches cache is not ready. Please refresh metadata and try again.');
            return false;
        }
        $row = $this->db->select('id')->from('ugc_batches')->where('id', $id)->get()->row_array();
        if (empty($row)) {
            $this->form_validation->set_message('_ugc_batch_exists', 'Selected UGC Batch no longer exists in local metadata cache. Please refresh metadata and re-select.');
            return false;
        }
        return true;
    }

    /**
     * Derive students.admission_year (B.S. batch label) from UGC Batch metadata.
     * UGC Batch is the source of truth; the column is kept for legacy reports/API.
     *
     * @param int    $ugc_batch_id
     * @param string $batch_name_nepali
     * @param string $posted_admission_year
     * @param string $existing_value Prior admission_year on edit
     * @return string
     */
    private function _resolve_admission_year_from_batch($ugc_batch_id, $batch_name_nepali = '', $posted_admission_year = '', $existing_value = '')
    {
        $nep = trim((string) $batch_name_nepali);
        if (preg_match('/^\d{4}$/', $nep)) {
            return $nep;
        }

        $ugc_batch_id = (int) $ugc_batch_id;
        if ($ugc_batch_id > 0 && $this->db->table_exists('ugc_batches')) {
            $row = $this->db->select('batch_nepali')
                ->from('ugc_batches')
                ->where('id', $ugc_batch_id)
                ->get()
                ->row_array();
            if (!empty($row['batch_nepali'])) {
                $from_batch = trim((string) $row['batch_nepali']);
                if (preg_match('/^\d{4}$/', $from_batch)) {
                    return $from_batch;
                }
                if ($from_batch !== '') {
                    return $from_batch;
                }
            }
        }

        $posted = trim((string) $posted_admission_year);
        if ($posted !== '') {
            return $posted;
        }

        $existing_value = trim((string) $existing_value);
        return $existing_value !== '' ? $existing_value : '0000';
    }

    /**
     * @param string $existing_value Prior admission_year on edit
     * @return string
     */
    private function _resolve_admission_year_from_post($existing_value = '')
    {
        return $this->_resolve_admission_year_from_batch(
            (int) $this->input->post('ugc_batch_id'),
            (string) $this->input->post('batch_name_nepali'),
            (string) $this->input->post('admission_year'),
            (string) $existing_value
        );
    }

    public function _ugc_fiscal_year_exists($ugc_fiscal_year_id)
    {
        $id = (int) $ugc_fiscal_year_id;
        if ($id < 1) {
            return true;
        }
        if (!$this->db->table_exists('ugc_fiscal_years')) {
            $this->form_validation->set_message('_ugc_fiscal_year_exists', 'UGC fiscal years cache is not ready. Please refresh metadata and try again.');
            return false;
        }
        $row = $this->db->select('id')->from('ugc_fiscal_years')->where('id', $id)->get()->row_array();
        if (empty($row)) {
            $this->form_validation->set_message('_ugc_fiscal_year_exists', 'Selected UGC Fiscal Year no longer exists in local metadata cache. Please refresh metadata and re-select.');
            return false;
        }
        return true;
    }

    /**
     * AJAX: fetch UGC programs from central HEMIS (ProgramMgmt/GetCollegePrograms).
     * Used by student create/edit to populate the Program dropdown from official metadata.
     */
    public function ugc_programs()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add') && !$this->rbac->hasPrivilege('student', 'can_edit') && !$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $live = (int) $this->input->get('live') === 1;
        $this->load->model('ugc_metadata_model');

        if (!$live) {
            $cached = $this->ugc_metadata_model->get_programs_cached();
            if (!empty($cached)) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $cached, 'source' => 'cache')));
                return;
            }
        }

        $this->load->library('hemis_client');
        $r = $this->hemis_client->hemis_get_college_programs();

        if (!is_array($r) || empty($r['ok'])) {
            $msg = is_array($r) && !empty($r['error']) ? $r['error'] : 'UGC program pull failed';
            $this->output->set_status_header(502);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => $msg, 'data' => array())));
            return;
        }

        $data = isset($r['data']) && is_array($r['data']) ? $r['data'] : array();
        if (!empty($data)) {
            $this->ugc_metadata_model->upsert_programs($data);
        }

        $message = null;
        if ($live && empty($data)) {
            // Common situation: campus has no programs assigned on HEMIS side, or token is scoped to a campus with no programs.
            $campusId = null;
            if (isset($this->hemis_client->cfg) && is_array($this->hemis_client->cfg)) {
                if (!empty($this->hemis_client->cfg['remote_college_id'])) {
                    $campusId = (int) $this->hemis_client->cfg['remote_college_id'];
                } elseif (!empty($this->hemis_client->cfg['college_id'])) {
                    $campusId = (int) $this->hemis_client->cfg['college_id'];
                }
            }
            $message = 'HEMIS returned 200 OK but no programs. This usually means no programs are assigned to your campus'
                . ($campusId ? (' (campusId=' . $campusId . ')') : '')
                . '. Please ask HEMIS/UGC to assign programs to the campus for this token.';
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => 1,
            'data'   => $data,
            'mode'   => $r['mode'] ?? null,
            'source' => 'ugc',
            'message'=> $message,
        )));
    }

    /**
     * AJAX: suggest campus Faculty (section) + Program from a UGC program id.
     * Uses hemis.php ugc_program_campus_map first, then fuzzy name match.
     */
    public function resolve_campus_from_ugc_program()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add') && !$this->rbac->hasPrivilege('student', 'can_edit') && !$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $ugc_program_id = (int) $this->input->get_post('ugc_program_id');
        if ($ugc_program_id < 1) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status' => 0,
                'message' => 'ugc_program_id required',
                'data' => null,
            )));
            return;
        }

        $this->config->load('hemis', true);
        $map = $this->config->item('ugc_program_campus_map', 'hemis');
        if (!is_array($map)) {
            $map = array();
        }

        $ugc = null;
        if ($this->db->table_exists('ugc_programs')) {
            $ugc = $this->db->from('ugc_programs')->where('program_id', $ugc_program_id)->limit(1)->get()->row_array();
        }

        $section_id = 0;
        $program_id = 0;
        $matched_by = null;
        $hint = '';

        if (isset($map[$ugc_program_id]) && is_array($map[$ugc_program_id])) {
            $section_id = (int) (isset($map[$ugc_program_id]['section_id']) ? $map[$ugc_program_id]['section_id'] : 0);
            $program_id = (int) (isset($map[$ugc_program_id]['program_id']) ? $map[$ugc_program_id]['program_id'] : 0);
            $matched_by = 'config';
        }

        if (($section_id < 1 || $program_id < 1) && is_array($ugc)) {
            $facName = trim((string) (isset($ugc['faculty_name']) ? $ugc['faculty_name'] : ''));
            $progName = trim((string) (isset($ugc['program_name']) ? $ugc['program_name'] : ''));
            $shortName = trim((string) (isset($ugc['short_name']) ? $ugc['short_name'] : ''));

            if ($section_id < 1 && $facName !== '') {
                $sections = $this->db->select('id, section')->from('sections')->get()->result_array();
                $facNorm = $this->_norm_placement_name($facName);
                foreach ($sections as $sec) {
                    $secNorm = $this->_norm_placement_name(isset($sec['section']) ? $sec['section'] : '');
                    if ($secNorm === '' || $facNorm === '') {
                        continue;
                    }
                    if ($secNorm === $facNorm || strpos($secNorm, $facNorm) !== false || strpos($facNorm, $secNorm) !== false) {
                        $section_id = (int) $sec['id'];
                        break;
                    }
                    // "Education" ↔ "Faculty of Education"
                    if (strpos($secNorm, $facNorm) !== false || preg_match('/\b' . preg_quote($facNorm, '/') . '\b/', $secNorm)) {
                        $section_id = (int) $sec['id'];
                        break;
                    }
                }
            }

            if ($program_id < 1 && ($progName !== '' || $shortName !== '')) {
                $programs = $this->db->select('id, title')->from('programs')->get()->result_array();
                $candidates = array();
                foreach ($programs as $pr) {
                    $titleNorm = $this->_norm_placement_name(isset($pr['title']) ? $pr['title'] : '');
                    $pn = $this->_norm_placement_name($progName);
                    $sn = $this->_norm_placement_name($shortName);
                    if ($titleNorm === '') {
                        continue;
                    }
                    $score = 0;
                    if ($pn !== '' && $titleNorm === $pn) {
                        $score = 100;
                    } elseif ($sn !== '' && (strpos($titleNorm, $sn) !== false || strpos($sn, $titleNorm) !== false)) {
                        $score = 80;
                    } elseif ($pn !== '' && (strpos($titleNorm, $pn) !== false || strpos($pn, $titleNorm) !== false)) {
                        $score = 60;
                    } else {
                        // token overlap (bachelor education vs bachelors of education)
                        $t1 = array_filter(explode(' ', $titleNorm));
                        $t2 = array_filter(explode(' ', $pn !== '' ? $pn : $sn));
                        $overlap = count(array_intersect($t1, $t2));
                        if ($overlap >= 2) {
                            $score = 40 + $overlap;
                        }
                    }
                    if ($score > 0) {
                        $candidates[] = array('id' => (int) $pr['id'], 'score' => $score, 'title' => $pr['title']);
                    }
                }
                usort($candidates, function ($a, $b) {
                    return $b['score'] - $a['score'];
                });
                if (!empty($candidates) && (count($candidates) === 1 || $candidates[0]['score'] >= 60)) {
                    $program_id = (int) $candidates[0]['id'];
                    if ($matched_by === null) {
                        $matched_by = 'fuzzy';
                    }
                }
            }
            if ($matched_by === null && ($section_id > 0 || $program_id > 0)) {
                $matched_by = 'fuzzy';
            }
        }

        if ($section_id > 0 && $program_id > 0) {
            $secLabel = '';
            $progLabel = '';
            $srow = $this->db->select('section')->from('sections')->where('id', $section_id)->get()->row_array();
            $prow = $this->db->select('title')->from('programs')->where('id', $program_id)->get()->row_array();
            if ($srow) {
                $secLabel = $srow['section'];
            }
            if ($prow) {
                $progLabel = $prow['title'];
            }
            $hint = 'Auto-selected campus Faculty/Program from UGC Program'
                . ($secLabel || $progLabel ? (': ' . trim($secLabel . ' / ' . $progLabel, ' /')) : '');
        } elseif ($section_id > 0) {
            $hint = 'Auto-selected campus Faculty from UGC; pick Program manually.';
        } else {
            $hint = 'No campus Faculty/Program mapping for this UGC Program — select manually.';
        }

        $duration = is_array($ugc) && !empty($ugc['duration']) ? (string) $ugc['duration'] : '';
        $faculty_name = is_array($ugc) && !empty($ugc['faculty_name']) ? (string) $ugc['faculty_name'] : '';
        $level_name = is_array($ugc) && !empty($ugc['level_name']) ? (string) $ugc['level_name'] : '';
        $program_type = is_array($ugc) && !empty($ugc['program_type']) ? (string) $ugc['program_type'] : '';

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => 1,
            'hint'   => $hint,
            'data'   => array(
                'section_id'    => $section_id > 0 ? $section_id : null,
                'program_id'    => $program_id > 0 ? $program_id : null,
                'matched_by'    => $matched_by,
                'duration'      => $duration,
                'faculty_name'  => $faculty_name,
                'level_name'    => $level_name,
                'program_type'  => $program_type,
                'ugc_program_id'=> $ugc_program_id,
            ),
        )));
    }

    /**
     * Normalize program/faculty labels for fuzzy campus↔UGC matching.
     *
     * @param string $s
     * @return string
     */
    protected function _norm_placement_name($s)
    {
        $s = strtolower(trim((string) $s));
        $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
        $s = preg_replace('/\b(of|the|in|and|faculty|bachelors|bachelor|masters|master)\b/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    public function ugc_batches()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add') && !$this->rbac->hasPrivilege('student', 'can_edit') && !$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $live = (int) $this->input->get('live') === 1;
        $this->load->model('ugc_metadata_model');

        if (!$live) {
            $cached = $this->ugc_metadata_model->get_batches_cached();
            if (!empty($cached)) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $cached, 'source' => 'cache')));
                return;
            }
        }

        $this->load->library('hemis_client');
        $r = $this->hemis_client->hemis_get_batch();
        if (empty($r['ok']) || !is_array($r['data'])) {
            $msg = !empty($r['error']) ? $r['error'] : 'UGC batch pull failed';
            $this->output->set_status_header(502);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => $msg, 'data' => array())));
            return;
        }
        $this->ugc_metadata_model->upsert_batches($r['data']);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $r['data'], 'source' => 'ugc', 'mode' => $r['mode'] ?? null)));
    }

    public function ugc_fiscal_years()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add') && !$this->rbac->hasPrivilege('student', 'can_edit') && !$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $live = (int) $this->input->get('live') === 1;
        $this->load->model('ugc_metadata_model');

        if (!$live) {
            $cached = $this->ugc_metadata_model->get_fiscal_years_cached();
            if (!empty($cached)) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $cached, 'source' => 'cache')));
                return;
            }
        }

        $this->load->library('hemis_client');
        $r = $this->hemis_client->hemis_get_fiscal_year();
        if (empty($r['ok']) || !is_array($r['data'])) {
            $msg = !empty($r['error']) ? $r['error'] : 'UGC fiscal year pull failed';
            $this->output->set_status_header(502);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => $msg, 'data' => array())));
            return;
        }
        $this->ugc_metadata_model->upsert_fiscal_years($r['data']);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $r['data'], 'source' => 'ugc', 'mode' => $r['mode'] ?? null)));
    }

    /**
     * AJAX: derive Academic year/semester label from Class + UGC program_type.
     */
    public function derive_semister_or_year_no()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add') && !$this->rbac->hasPrivilege('student', 'can_edit') && !$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }
        $class_id = (int) $this->input->get_post('class_id');
        $ugc_program_id = (int) $this->input->get_post('ugc_program_id');
        $label = $this->_derive_semister_or_year_no($class_id, $ugc_program_id);
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => 1,
            'semister_or_year_no' => $label,
            'class_id' => $class_id,
            'ugc_program_id' => $ugc_program_id,
        )));
    }

    public function ugc_addresses()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add') && !$this->rbac->hasPrivilege('student', 'can_edit') && !$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $live = (int) $this->input->get('live') === 1;
        $this->load->model('ugc_metadata_model');

        if (!$live) {
            $cached = $this->ugc_metadata_model->get_addresses_cached();
            if (!empty($cached)) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $cached, 'source' => 'cache')));
                return;
            }
        }

        $this->load->library('hemis_client');
        $r = $this->hemis_client->hemis_get_address_all();
        if (empty($r['ok']) || !is_array($r['data'])) {
            $msg = !empty($r['error']) ? $r['error'] : 'UGC address pull failed';
            $this->output->set_status_header(502);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => $msg, 'data' => array())));
            return;
        }
        $this->ugc_metadata_model->upsert_addresses($r['data']);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $r['data'], 'source' => 'ugc', 'mode' => $r['mode'] ?? null)));
    }

    public function searchvalidation()
    {
        $search_text = $this->input->post('search_text');
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');
        $class_id = $this->input->post('class_id');
        
        $params = array(
            'search_text' => $search_text,
            'section_id' => $section_id,
            'program_id' => $program_id,
            'class_id' => $class_id
        );

        $this->session->set_userdata('student_search_filters', $params);
        
        $array = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        
        $data['title'] = 'Student Details';
        $data['id'] = $id;
        
        // Check if student exists
        $this->db->where('id', $id);
        $student_exists = $this->db->get('students')->row_array();
        
        if (empty($student_exists)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('student_not_found') . '</div>');
            redirect('student/search');
        }
        
        // Try to get student with session
        $student = $this->student_model->get($id);
        
        // If student exists but no session record, try to get without session restriction
        if (empty($student)) {
            // Get student without session restriction
            $this->db->select('pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.transport_fees,students.app_key,students.parent_app_key,student_session.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,vehicles.vehicle_model,vehicles.manufacture_year,vehicles.driver_licence,vehicles.vehicle_photo,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,student_session.program_id AS program_id,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno,students.email,students.state,students.city,students.pincode,students.note,students.religion,students.cast,school_houses.house_name,students.dob,students.dob_ad,students.current_address,students.previous_school,students.guardian_is,students.parent_id,students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code,students.guardian_name,students.father_pic,students.height,students.weight,students.measurement_date,students.mother_pic,students.guardian_pic,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,students.created_at,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.rte,students.guardian_email,users.username,users.password,users.id as user_id,students.dis_reason,students.dis_note,students.disable_at');
            $this->db->select('students.first_name_np, students.middle_name_np, students.last_name_np');
            $this->db->select('students.permanent_district, students.permanent_province, students.permanent_local_level, students.permanent_ward_no, students.permanent_house_no');
            $this->db->select('students.temporary_district, students.temporary_province, students.temporary_local_level, students.temporary_ward_no, students.temporary_house_no');
            $this->db->select('students.ethnicity, students.edj, students.national_id, students.citizenship, students.citizenship_issue_dist, students.complition_year, students.admissionYearId');
            $this->db->select('students.admission_year, students.academic_program_duration');
            $this->db->select('students.previous_level_of_study, students.previous_board_university_college, students.previous_registration_no, students.previous_institution_name, students.previous_study_records_attachment');
            $this->db->select('students.school_id');
            $this->db->select('students.disability_status, students.disability_type, students.program_fee, students.scholarship_amt, students.paid_amount, students.batch_name, students.batch_name_nepali, students.entrance_score, students.entrance_symbol, students.medium, students.shift, students.type, students.nationality, students.semister_or_year_no, students.father_email, students.mother_email, students.is_verified, students.has_scolorship, students.is_mukta_kamaiya, students.is_from_martyr_family');
            $this->db->from('students');
            $this->db->join('student_session', 'student_session.student_id = students.id', 'left');
            $this->db->join('classes', 'student_session.class_id = classes.id', 'left');
            $this->db->join('sections', 'sections.id = student_session.section_id', 'left');
            $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
            $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
            $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
            $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
            $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
            $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
            $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
            $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
            $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
            $this->db->join('users', 'users.user_id = students.id AND users.role = "student"', 'left');
            $this->db->where('students.id', $id);
            $this->db->order_by('student_session.session_id', 'desc');
            $this->db->limit(1);
            $query = $this->db->get();
            $student = $query->row_array();
            
            // If still empty, create a minimal student array
            if (empty($student)) {
                $student = $student_exists;
                $student['student_session_id'] = null;
                $student['class_id'] = null;
                $student['section_id'] = null;
                $student['program_id'] = null;
            }
        }
        
        $data['student'] = $student;
        
        // Get student session info
        $studentSession = array();
        if (isset($student['student_session_id']) && !empty($student['student_session_id'])) {
            $this->db->select('student_session.*, sessions.session');
            $this->db->from('student_session');
            $this->db->join('sessions', 'sessions.id = student_session.session_id', 'left');
            $this->db->where('student_session.id', $student['student_session_id']);
            $query = $this->db->get();
            $studentSession = $query->row_array();
        }
        $data['studentSession'] = $studentSession;
        
        // Get disable reason if student is inactive
        $reason_data = array();
        $is_active = isset($student['is_active']) ? $student['is_active'] : 'yes';
        if ($is_active == 'no' && isset($student['dis_reason']) && !empty($student['dis_reason'])) {
            $reason_data = $this->disable_reason_model->get($student['dis_reason']);
        }
        $data['reason_data'] = !empty($reason_data) ? $reason_data : array();
        
        // Get fees information
        $data['fees'] = array();
        if (isset($student['student_session_id']) && !empty($student['student_session_id'])) {
            $this->load->model('studentfeemaster_model');
            $data['fees'] = $this->studentfeemaster_model->getStudentFees($student['student_session_id']);
        }
        
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['currency_symbol'] = $this->customlib->getSchoolCurrencyFormat();
        $data['reason'] = $this->disable_reason_model->get();

        // Session name for display (e.g. "2024-2025")
        $data['session'] = $this->setting_model->getCurrentSessionName();

        // Category list for profile (category dropdown/display)
        $data['category_list'] = $this->category_model->get();

        // Attendance types for attendance report section (Present, Absent, etc.)
        $data['attendencetypeslist'] = $this->attendencetype_model->get();

        // Sidebar: class sections and students in same class (for section tabs)
        $data['class_section'] = array();
        $data['studentlistbysection'] = array();
        $class_id = isset($student['class_id']) ? $student['class_id'] : null;
        if (!empty($class_id)) {
            $data['class_section'] = $this->student_model->getClassSection($class_id);
            $session_id = $this->setting_model->getCurrentSession();
            $data['studentlistbysection'] = $this->student_model->getStudentClassSection($class_id, $session_id);
        }

        // Attendance report: session year start and resultlist (date => attendance key)
        $data['session_year_start'] = date('Y-m-01');
        $data['resultlist'] = array();
        $student_session_id = isset($student['student_session_id']) ? $student['student_session_id'] : null;
        if (!empty($student_session_id)) {
            $session_name = $this->setting_model->getCurrentSessionName();
            $start_month = $this->setting_model->getStartMonth();
            $end_month = ($start_month == 1) ? 12 : $start_month - 1;
            if (preg_match('/^(\d{4})-(\d{2,4})$/', $session_name, $m)) {
                $start_year = $m[1];
                $next_year = (strlen($m[2]) == 2) ? substr($start_year, 0, 2) . $m[2] : $m[2];
                $data['session_year_start'] = date('Y-m-01', strtotime($start_year . '-' . sprintf('%02d', $start_month) . '-01'));
                $session_year_end = date('Y-m-t', strtotime($next_year . '-' . sprintf('%02d', $end_month) . '-01'));
                $attendences = $this->stuattendence_model->student_attendence_bw_date($data['session_year_start'], $session_year_end, $student_session_id);
                $by_date = array();
                if (!empty($attendences)) {
                    foreach ($attendences as $att) {
                        $by_date[$att->date] = array('key' => isset($att->key) ? $att->key : '');
                    }
                }
                $monthlist = $this->customlib->getMonthNoDropdown($start_month);
                if (!empty($monthlist)) {
                    $st = $start_year;
                    foreach ($monthlist as $key => $value) {
                        $datemonth = $key;
                        if ($datemonth < $start_month) {
                            $st = $next_year;
                        }
                        $date_each_month = $st . '-' . sprintf('%02d', $datemonth) . '-01';
                        $date_end = (int) date('t', strtotime($date_each_month));
                        for ($n = 1; $n <= $date_end; $n++) {
                            $att_dates = $st . '-' . sprintf('%02d', $datemonth) . '-' . sprintf('%02d', $n);
                            $data['resultlist'][$att_dates] = isset($by_date[$att_dates]) ? $by_date[$att_dates] : array('key' => '');
                        }
                    }
                }
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('student/studentShow', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Disable student: set students.is_active = 'no' and users.is_active = 'no' so login is blocked.
     * Called via AJAX from student show page disable modal.
     */
    public function disable_reason()
    {
        if (!$this->rbac->hasPrivilege('disable_student', 'can_view') && !$this->rbac->hasPrivilege('disable_student', 'can_add') && !$this->rbac->hasPrivilege('user_status', 'can_view')) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('unauthorized'))));
            return;
        }
        $this->form_validation->set_rules('student_id', $this->lang->line('student'), 'trim|required|integer');
        $this->form_validation->set_rules('reason', $this->lang->line('disable_reason'), 'trim|required|integer');
        $this->form_validation->set_rules('disable_date', $this->lang->line('date'), 'trim|required');
        if ($this->form_validation->run() === false) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => $this->form_validation->error_array()));
            return;
        }
        $student_id = (int) $this->input->post('student_id');
        $reason_id  = (int) $this->input->post('reason');
        $disable_date_raw = $this->input->post('disable_date');
        $note        = $this->input->post('note');
        $disable_at  = $this->customlib->dateFormatToYYYYMMDD($disable_date_raw);
        if (!$disable_at) {
            $disable_at = date('Y-m-d');
        }
        $student_exists = $this->db->where('id', $student_id)->get('students')->row_array();
        if (empty($student_exists)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('student_not_found'))));
            return;
        }
        $data = array(
            'is_active'  => 'no',
            'dis_reason' => $reason_id,
            'dis_note'   => $note ? $note : null,
            'disable_at' => $disable_at,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        $this->student_model->disableStudent($student_id, $data);
        // Block student login by syncing users.is_active
        $this->db->where('user_id', $student_id);
        $this->db->where('role', 'student');
        $this->db->update('users', array('is_active' => 'no'));

        // Align with DropOut: mark current-session enrollment as leave (dashboard dropouts).
        $current_session = (int) $this->setting_model->getCurrentSession();
        if ($current_session > 0) {
            $this->db->where('student_id', $student_id);
            $this->db->where('session_id', $current_session);
            $this->db->update('student_session', array(
                'is_leave'   => 1,
                'is_active'  => 'no',
                'updated_at' => date('Y-m-d'),
            ));
        }

        $reason_text = '';
        $reason_row = $this->disable_reason_model->get($reason_id);
        if (!empty($reason_row)) {
            $reason_text = is_array($reason_row)
                ? (isset($reason_row['reason']) ? $reason_row['reason'] : '')
                : (isset($reason_row->reason) ? $reason_row->reason : '');
        }
        if ($reason_text === '') {
            $reason_text = 'Disabled by admin';
        }

        $this->load->library('hemis_client');
        $hemisSync = $this->hemis_client->sync_dropout_post($student_id, array(
            'studentId'   => $student_id,
            'reason'      => $reason_text,
            'dateOfEntry' => $disable_at,
            'remarks'     => $note ? (string) $note : $reason_text,
        ));

        header('Content-Type: application/json');
        echo json_encode(array(
            'status'     => 'success',
            'message'    => $this->lang->line('success_message'),
            'hemis_sync' => $hemisSync,
        ));
    }

    /**
     * AJAX: returns login user details for a student (used by enable() button).
     * Endpoint called from `student/studentShow.php` as:
     * - POST `student/getUserLoginDetails` with `student_id`
     */
    public function getUserLoginDetails()
    {
        if (!$this->rbac->hasPrivilege('disable_student', 'can_view') && !$this->rbac->hasPrivilege('disable_student', 'can_add') && !$this->rbac->hasPrivilege('user_status', 'can_view')) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('unauthorized'))));
            return;
        }

        $student_id = (int) $this->input->post('student_id');
        if ($student_id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('student_not_found'))));
            return;
        }

        $data = $this->user_model->getUserLoginDetails($student_id);

        header('Content-Type: application/json');
        echo json_encode(array('status' => 'success', 'data' => $data));
    }

    /**
     * AJAX: enable a disabled student.
     * Endpoint called from `student/studentShow.php` as:
     * - POST/GET `student/enablestudent/{student_id}`
     */
    public function enablestudent($student_id = null)
    {
        if (!$this->rbac->hasPrivilege('disable_student', 'can_view') && !$this->rbac->hasPrivilege('disable_student', 'can_add') && !$this->rbac->hasPrivilege('user_status', 'can_view')) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('unauthorized'))));
            return;
        }

        $student_id = (int) ($student_id ?? $this->input->post('student_id'));
        if ($student_id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('student_not_found'))));
            return;
        }

        $student_exists = $this->db->where('id', $student_id)->get('students')->row_array();
        if (empty($student_exists)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'fail', 'error' => array('e' => $this->lang->line('student_not_found'))));
            return;
        }

        $data = array(
            'is_active'  => 'yes',
            'dis_reason' => null,
            'dis_note'   => null,
            'disable_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        // Update student row
        $this->student_model->disableStudent($student_id, $data);

        // Re-enable login if a user record exists
        $this->db->where('user_id', $student_id);
        $this->db->where('role', 'student');
        $this->db->update('users', array('is_active' => 'yes'));

        header('Content-Type: application/json');
        echo json_encode(array('status' => 'success', 'message' => $this->lang->line('success_message')));
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('student', 'can_edit')) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        
        $data['title'] = 'Edit Student';
        $data['id'] = $id;
        
        // Check if student exists
        $this->db->where('id', $id);
        $student_exists = $this->db->get('students')->row_array();
        
        if (empty($student_exists)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('student_not_found') . '</div>');
            redirect('student/search');
        }
        
        // Try to get student with session
        $student = $this->student_model->get($id);
        
        // If student exists but no session record, try to get without session restriction
        if (empty($student)) {
            // Get student without session restriction
            $this->db->select('pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.transport_fees,students.app_key,students.parent_app_key,student_session.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,vehicles.vehicle_model,vehicles.manufacture_year,vehicles.driver_licence,vehicles.vehicle_photo,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,student_session.program_id,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno,students.email,students.state,students.city,students.pincode,students.note,students.religion,students.cast,school_houses.house_name,students.dob,students.dob_ad,students.current_address,students.previous_school,students.guardian_is,students.parent_id,students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code,students.guardian_name,students.father_pic,students.height,students.weight,students.measurement_date,students.mother_pic,students.guardian_pic,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,students.created_at,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.rte,students.guardian_email,users.username,users.password,users.id as user_id,students.dis_reason,students.dis_note,students.disable_at');
            $this->db->select('students.first_name_np, students.middle_name_np, students.last_name_np');
            $this->db->select('students.permanent_district, students.permanent_province, students.permanent_local_level, students.permanent_ward_no, students.permanent_house_no');
            $this->db->select('students.temporary_district, students.temporary_province, students.temporary_local_level, students.temporary_ward_no, students.temporary_house_no');
            $this->db->select('students.ethnicity, students.edj, students.national_id, students.citizenship, students.citizenship_issue_dist, students.complition_year, students.admissionYearId');
            $this->db->select('students.admission_year, students.academic_program_duration');
            $this->db->select('students.previous_level_of_study, students.previous_board_university_college, students.previous_registration_no, students.previous_institution_name, students.previous_study_records_attachment');
            $this->db->select('students.school_id');
            $this->db->select('students.disability_status, students.disability_type, students.program_fee, students.scholarship_amt, students.paid_amount, students.batch_name, students.batch_name_nepali, students.entrance_score, students.entrance_symbol, students.medium, students.shift, students.type, students.nationality, students.semister_or_year_no, students.father_email, students.mother_email, students.is_verified, students.has_scolorship, students.is_mukta_kamaiya, students.is_from_martyr_family');
            $this->db->select('students.pp_size_photo, students.citizenship_front, students.citizenship_back, students.nid_pic, students.digital_signature, students.receipt_image');
            $this->db->select('students.ugc_program_id, students.ugc_batch_id, students.ugc_fiscal_year_id, students.ugc_t_province, students.ugc_t_district, students.ugc_t_local_level, students.ugc_p_province, students.ugc_p_district, students.ugc_p_local_level, students.ugc_p_combined_code, students.ugc_t_combined_code');
            $this->db->from('students');
            $this->db->join('student_session', 'student_session.student_id = students.id', 'left');
            $this->db->join('classes', 'student_session.class_id = classes.id', 'left');
            $this->db->join('sections', 'sections.id = student_session.section_id', 'left');
            $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
            $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
            $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
            $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
            $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
            $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
            $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
            $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
            $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
            $this->db->join('users', 'users.user_id = students.id AND users.role = "student"', 'left');
            $this->db->where('students.id', $id);
            $this->db->order_by('student_session.session_id', 'desc');
            $this->db->limit(1);
            $query = $this->db->get();
            $student = $query->row_array();
            
            // If still empty, create a minimal student array
            if (empty($student)) {
                $student = $student_exists;
                $student['student_session_id'] = null;
                $student['class_id'] = null;
                $student['section_id'] = null;
                $student['program_id'] = null;
            }
        }
        
        // Ensure all required fields exist with default values
        $student['admission_no'] = isset($student['admission_no']) ? $student['admission_no'] : '';
        $student['roll_no'] = isset($student['roll_no']) ? $student['roll_no'] : '';
        $student['firstname'] = isset($student['firstname']) ? $student['firstname'] : '';
        $student['middlename'] = isset($student['middlename']) ? $student['middlename'] : '';
        $student['lastname'] = isset($student['lastname']) ? $student['lastname'] : '';
        $student['admission_date'] = isset($student['admission_date']) ? $student['admission_date'] : '';
        $student['admission_year'] = isset($student['admission_year']) ? $student['admission_year'] : '';
        $student['id'] = isset($student['id']) ? $student['id'] : $id;
        $student['student_session_id'] = isset($student['student_session_id']) ? $student['student_session_id'] : '';
        $student['first_name_np'] = isset($student['first_name_np']) ? $student['first_name_np'] : '';
        $student['program_id'] = isset($student['program_id']) ? $student['program_id'] : '';
        $student['class_id'] = isset($student['class_id']) ? $student['class_id'] : '';
        $student['section_id'] = isset($student['section_id']) ? $student['section_id'] : '';
        $student['category_id'] = isset($student['category_id']) ? $student['category_id'] : '';
        $student['religion'] = isset($student['religion']) ? $student['religion'] : '';
        $student['cast'] = isset($student['cast']) ? $student['cast'] : '';
        $student['dob'] = isset($student['dob']) ? $student['dob'] : '';
        $student['dob_ad'] = isset($student['dob_ad']) ? $student['dob_ad'] : '';

        // Remember where the user opened this screen from (search, view, etc.) so after save we can go "back" there.
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $ref = $this->input->server('HTTP_REFERER');
            if (!empty($ref) && strpos($ref, base_url()) === 0 && stripos($ref, '/student/edit/') === false) {
                $this->session->set_userdata('student_edit_return_url', $ref);
            }
        }

        if (strtoupper($this->input->method(true)) === 'POST') {
            $this->form_validation->set_rules('firstname', $this->lang->line('first_name'), 'trim|required');
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required');
            $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required');
            $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required');
            // HEMIS showstopper fields: do not allow local update without UGC-synced lookups.
            $this->form_validation->set_rules('ugc_program_id', 'UGC Program (HEMIS)', 'trim|required|is_natural_no_zero|callback__ugc_program_exists');
            $this->form_validation->set_rules('ugc_batch_id', 'UGC Batch (HEMIS)', 'trim|required|is_natural_no_zero|callback__ugc_batch_exists');
            $this->form_validation->set_rules('ugc_fiscal_year_id', 'UGC Fiscal Year (HEMIS)', 'trim|required|is_natural_no_zero|callback__ugc_fiscal_year_exists');
            $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required');
            // HEMIS-required fields (prevent local save that will always fail sync).
            $this->form_validation->set_rules('permanent_ward_no', $this->lang->line('permanent_ward_no'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('ethnicity', 'Ethnicity (HEMIS)', 'trim|required');
            if ($this->db->field_exists('admissionYearId', 'students')) {
                $this->form_validation->set_rules('admissionYearId', 'Admission Year ID (HEMIS)', 'trim|required|is_natural_no_zero');
            }

            if ($this->form_validation->run()) {
                $student_update = array(
                    'id'                => $id,
                    'admission_no'      => $this->input->post('admission_no'),
                    'roll_no'           => $this->input->post('roll_no'),
                    'firstname'         => $this->input->post('firstname'),
                    'middlename'        => $this->input->post('middlename'),
                    'lastname'          => $this->input->post('lastname'),
                    'first_name_np'     => $this->input->post('first_name_np'),
                    'middle_name_np'    => $this->input->post('middle_name_np'),
                    'last_name_np'      => $this->input->post('last_name_np'),
                    'admission_year'    => $this->_resolve_admission_year_from_post(isset($student['admission_year']) ? $student['admission_year'] : ''),
                    'academic_program_duration' => $this->input->post('academic_program_duration'),
                    'gender'            => $this->input->post('gender'),
                    'mobileno'          => $this->input->post('mobileno'),
                    'email'             => $this->input->post('email'),
                    'category_id'       => $this->input->post('category_id'),
                    'religion'          => $this->input->post('religion'),
                    'edj'               => $this->input->post('edj'),
                    'blood_group'       => $this->input->post('blood_group'),
                    'height'            => $this->input->post('height'),
                    'weight'            => $this->input->post('weight'),
                    'temporary_province'      => $this->input->post('temporary_province'),
                    'temporary_district'      => $this->input->post('temporary_district'),
                    'temporary_local_level'   => $this->input->post('temporary_local_level'),
                    'temporary_ward_no'       => $this->input->post('temporary_ward_no'),
                    'temporary_house_no'      => $this->input->post('temporary_house_no'),
                    'permanent_province'      => $this->input->post('permanent_province'),
                    'permanent_district'      => $this->input->post('permanent_district'),
                    'permanent_local_level'   => $this->input->post('permanent_local_level'),
                    'permanent_ward_no'       => $this->input->post('permanent_ward_no'),
                    'permanent_house_no'      => $this->input->post('permanent_house_no'),
                    'current_address'         => $this->input->post('current_address'),
                    'permanent_address'       => $this->input->post('permanent_address'),
                    'father_name'             => $this->input->post('father_name'),
                    'father_phone'            => $this->input->post('father_phone'),
                    'father_occupation'       => $this->input->post('father_occupation'),
                    'mother_name'             => $this->input->post('mother_name'),
                    'mother_phone'            => $this->input->post('mother_phone'),
                    'mother_occupation'       => $this->input->post('mother_occupation'),
                    'guardian_is'             => $this->input->post('guardian_is'),
                    'guardian_name'           => $this->input->post('guardian_name'),
                    'guardian_relation'       => $this->input->post('guardian_relation'),
                    'guardian_phone'          => $this->input->post('guardian_phone'),
                    'guardian_occupation'     => $this->input->post('guardian_occupation'),
                    'guardian_email'          => $this->input->post('guardian_email'),
                    'guardian_address'        => $this->input->post('guardian_address'),
                    'bank_account_no'         => $this->input->post('bank_account_no'),
                    'bank_name'               => $this->input->post('bank_name'),
                    'adhar_no'                => $this->input->post('adhar_no'),
                    'samagra_id'              => $this->input->post('samagra_id'),
                    'rte'                     => $this->input->post('rte'),
                    'note'                    => $this->input->post('note'),
                );
                    // HEMIS ethnicity is SoT; mirror into legacy cast for older reports.
                    $ethnicity_val = trim((string) $this->input->post('ethnicity'));
                    if ($this->db->field_exists('ethnicity', 'students')) {
                        $student_update['ethnicity'] = $ethnicity_val !== '' ? $ethnicity_val : null;
                    }
                    if ($this->db->field_exists('cast', 'students')) {
                        $student_update['cast'] = $ethnicity_val !== '' ? $ethnicity_val : ($this->input->post('cast') ?: null);
                    }

                    // Optional HEMIS documents (citizenship/NID/photo/signature/receipt).
                    $student_update = $this->_merge_hemis_student_doc_uploads_into_student_data($student_update);
                    // Extended profile fields (nationality, disability, fees, previous study, flags, etc.).
                    $student_update = $this->_merge_extended_student_fields_into_student_data($student_update);
                    if ($this->db->field_exists('semister_or_year_no', 'students')) {
                        $derivedSy = $this->_derive_semister_or_year_no(
                            (int) $this->input->post('class_id'),
                            (int) $this->input->post('ugc_program_id')
                        );
                        if ($derivedSy !== '') {
                            $student_update['semister_or_year_no'] = $derivedSy;
                        }
                    }
                    // Student / parent profile photos.
                    $student_update = $this->_merge_student_photo_uploads_into_student_data($student_update);

                if ($this->db->field_exists('ugc_program_id', 'students')) {
                    $ugc_program_id = (int) $this->input->post('ugc_program_id');
                    $student_update['ugc_program_id'] = $ugc_program_id > 0 ? $ugc_program_id : null;
                }
                if ($this->db->field_exists('ugc_batch_id', 'students')) {
                    $ugc_batch_id = (int) $this->input->post('ugc_batch_id');
                    $student_update['ugc_batch_id'] = $ugc_batch_id > 0 ? $ugc_batch_id : null;
                    if ($this->db->field_exists('batch_id', 'students') && $ugc_batch_id > 0) {
                        $student_update['batch_id'] = $ugc_batch_id;
                    }
                }
                $this->_merge_ugc_fiscal_year_columns_into_student_payload($student_update);
                if ($this->db->field_exists('batch_name', 'students')) {
                    $student_update['batch_name'] = $this->input->post('batch_name') ?: null;
                }
                if ($this->db->field_exists('batch_name_nepali', 'students')) {
                    $student_update['batch_name_nepali'] = $this->input->post('batch_name_nepali') ?: null;
                }
                if ($this->db->field_exists('admissionYearId', 'students')) {
                    $aid = (int) $this->input->post('admissionYearId');
                    $student_update['admissionYearId'] = $aid > 0 ? $aid : null;
                }
                if ($this->db->field_exists('complition_year', 'students')) {
                    $cy = trim((string) $this->input->post('complition_year'));
                    $student_update['complition_year'] = ($cy !== '' && ctype_digit($cy) && (int) $cy > 0) ? (int) $cy : null;
                }
                if ($this->db->field_exists('citizenship', 'students')) {
                    $c = trim((string) $this->input->post('citizenship'));
                    $student_update['citizenship'] = $c !== '' ? $c : null;
                }
                if ($this->db->field_exists('citizenship_issue_dist', 'students')) {
                    $cid = trim((string) $this->input->post('citizenship_issue_dist'));
                    $student_update['citizenship_issue_dist'] = $cid !== '' ? $cid : null;
                }
                if ($this->db->field_exists('ugc_t_province', 'students')) {
                    $student_update['ugc_t_province'] = $this->input->post('ugc_t_province_name') ?: null;
                    $student_update['ugc_t_district'] = $this->input->post('ugc_t_district_name') ?: null;
                    $student_update['ugc_t_local_level'] = $this->input->post('ugc_t_local_level_name') ?: null;
                    $student_update['ugc_p_province'] = $this->input->post('ugc_p_province_name') ?: null;
                    $student_update['ugc_p_district'] = $this->input->post('ugc_p_district_name') ?: null;
                    $student_update['ugc_p_local_level'] = $this->input->post('ugc_p_local_level_name') ?: null;
                }
                // Deterministic address anchor (combinedCode).
                if ($this->db->field_exists('ugc_p_combined_code', 'students')) {
                    $student_update['ugc_p_combined_code'] = $this->input->post('ugc_p_combined_code') ?: null;
                }
                if ($this->db->field_exists('ugc_t_combined_code', 'students')) {
                    $student_update['ugc_t_combined_code'] = $this->input->post('ugc_t_combined_code') ?: null;
                }

                // DOB: `dob` column = Nepali BS calendar (Y-m-d), `dob_ad` = English AD (Y-m-d).
                // Only overwrite when the operator posted a non-empty value so we do not null out dates
                // if a field was omitted, failed validation elsewhere, or a datepicker did not POST.
                $dob_bs = trim((string) $this->input->post('dob'));
                $dob_ad = trim((string) $this->input->post('dob_ad'));
                $admission_date_raw = trim((string) $this->input->post('admission_date'));
                if ($dob_bs !== '') {
                    $normDob = $this->customlib->dateFormatToYYYYMMDD($dob_bs);
                    if ($normDob !== null && $normDob !== '') {
                        $student_update['dob'] = $normDob;
                    }
                }
                if ($dob_ad !== '') {
                    $normAd = $this->customlib->dateFormatToYYYYMMDD($dob_ad);
                    if ($normAd !== null && $normAd !== '') {
                        $student_update['dob_ad'] = $normAd;
                    }
                }
                if ($admission_date_raw !== '') {
                    $normAdm = $this->customlib->dateFormatToYYYYMMDD($admission_date_raw);
                    if ($normAdm !== null && $normAdm !== '') {
                        $student_update['admission_date'] = $normAdm;
                    }
                }

                $measure_date = trim((string) $this->input->post('measure_date'));
                if ($measure_date !== '' && $this->db->field_exists('measurement_date', 'students')) {
                    $normMeas = $this->customlib->dateFormatToYYYYMMDD($measure_date);
                    if ($normMeas !== null && $normMeas !== '') {
                        $student_update['measurement_date'] = $normMeas;
                    }
                }

                $save_ok = (bool) $this->student_model->add($student_update);

                // Persist any uploaded / updated documents (Upload Documents section).
                $this->_save_student_documents((int) $id);

                // Keep student_session as placement SoT; students.* is compatibility cache only.
                $current_session_id = (int) $this->setting_model->getCurrentSession();
                $session_update = $this->_build_student_session_payload((int) $id, $current_session_id);
                unset($session_update['student_id'], $session_update['session_id']); // don't overwrite keys incorrectly on update-by-id
                // Re-include student/session when inserting fallback path needs them — handled below.

                $this->student_model->sync_students_placement_cache((int) $id, array(
                    'class_id' => (int) $this->input->post('class_id'),
                    'section_id' => (int) $this->input->post('section_id'),
                    'program_id' => (int) $this->input->post('program_id'),
                    'semister_or_year_no' => $this->_derive_semister_or_year_no(
                        (int) $this->input->post('class_id'),
                        (int) $this->input->post('ugc_program_id')
                    ),
                    'ugc_fiscal_year_id' => $this->_resolve_ugc_fiscal_year_id_for_session($current_session_id),
                ));
                if ($this->db->field_exists('ugc_batch_id', 'students') && $this->db->field_exists('batch_id', 'students')) {
                    $ub = (int) $this->input->post('ugc_batch_id');
                    if ($ub > 0) {
                        $this->db->where('id', $id)->update('students', array('batch_id' => $ub));
                    }
                }

                $student_session_id = $this->input->post('student_session_id');
                if (!empty($student_session_id)) {
                    $this->db->where('id', $student_session_id)->update('student_session', $session_update);
                } else {
                    $full = $this->_build_student_session_payload((int) $id, $current_session_id);
                    $this->student_model->add_student_session($full);
                    $student_session_row = $this->db->select('id')
                        ->from('student_session')
                        ->where('student_id', $id)
                        ->where('session_id', $current_session_id)
                        ->get()
                        ->row();
                    $student_session_id = !empty($student_session_row) ? (int) $student_session_row->id : 0;
                }

                // Persist fee-group selections from "Fees Details" section.
                if (!empty($student_session_id)) {
                    $selected_fee_groups = $this->input->post('fee_session_group_id');
                    $selected_fee_groups = is_array($selected_fee_groups) ? $selected_fee_groups : array();
                    $selected_fee_groups = array_values(array_unique(array_filter(array_map('intval', $selected_fee_groups), function ($value) {
                        return $value > 0;
                    })));

                    $existing_fee_rows = $this->studentfeemaster_model->getStudentFees($student_session_id);
                    $existing_fee_groups = array();
                    if (!empty($existing_fee_rows)) {
                        foreach ($existing_fee_rows as $existing_fee_row) {
                            $fee_group_id = isset($existing_fee_row->fee_session_group_id) ? (int) $existing_fee_row->fee_session_group_id : 0;
                            if ($fee_group_id > 0) {
                                $existing_fee_groups[] = $fee_group_id;
                            }
                        }
                    }
                    $existing_fee_groups = array_values(array_unique($existing_fee_groups));

                    $fees_to_add = array_values(array_diff($selected_fee_groups, $existing_fee_groups));
                    $fees_to_delete = array_values(array_diff($existing_fee_groups, $selected_fee_groups));
                    $this->studentfeemaster_model->assign_bulk_fees($fees_to_add, $student_session_id, $fees_to_delete);
                }

                if ($save_ok) {
                    $this->load->library('hemis_client');
                    $gateMsg = $this->_student_hemis_combined_gate_message((int) $id);
                    if ($gateMsg !== null) {
                        $hemis_out = $this->_student_hemis_skip_sync_outcome((int) $id, $gateMsg);
                    } else {
                        $hemis_out = $this->hemis_client->sync_student_update_from_database_snapshot((int) $id);
                    }
                    log_message('info', 'HEMIS real-time sync after UI student edit: students.id=' . (int) $id
                        . ' synced=' . (!empty($hemis_out['synced']) ? '1' : '0'));
                    $msg = '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>';
                    $msg .= $this->_hemis_failed_sync_notice_html(is_array($hemis_out) ? $hemis_out : array());
                    $this->session->set_flashdata('msg', $msg);
                    $return = $this->session->userdata('student_edit_return_url');
                    $this->session->unset_userdata('student_edit_return_url');
                    if (!empty($return) && strpos($return, base_url()) === 0) {
                        redirect($return);
                    }
                    redirect('student/search');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Unable to save student details. Please try again.</div>');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            }
        }
        
        $data['student'] = $student;
        
        // Load necessary data for the form
        $genderList = $this->customlib->getGender();
        $data["month"] = $this->customlib->getMonthDropdown();
        $data["day"] = $this->customlib->getDayDropdown();
        $data["year"] = $this->customlib->getYearDropdown();
        $data['genderList'] = $genderList;
        $session = $this->session_model->get();
        $data['sessionlist'] = $session;
        $vehroute_result = $this->vehroute_model->get();
        $data['vehroutelist'] = $vehroute_result;
        $category = $this->category_model->get();
        $data['categorylist'] = $category;
        $data["bloodgroup"] = $this->config->item('bloodgroup');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = isset($this->sch_setting_detail->adm_auto_insert) ? $this->sch_setting_detail->adm_auto_insert : 0;
        $data['sch_setting_detail'] = $this->sch_setting_detail;
        $data['fields'] = $this->customfield_model->get_custom_fields('students', 1);
        $data['inserted_fields'] = $this->student_edit_field_model->get();
        $data['classlist'] = $this->class_model->get();
        if (!empty($student['student_session_id'])) {
            $data['feesessiongroup_model'] = $this->feesessiongroup_model->getFeesByGroupByStudent((int) $student['student_session_id']);
        } else {
            $data['feesessiongroup_model'] = $this->feesessiongroup_model->getFeesByGroup();
        }
        $data['student_docs'] = $this->student_model->getStudentDocuments($id);
        $data['siblings'] = array();
        $data['siblings_counts'] = 0;
        if (!empty($student['parent_id'])) {
            $siblings = $this->student_model->getMySiblings($student['parent_id'], $student['id']);
            $data['siblings'] = is_array($siblings) ? $siblings : array();
            $data['siblings_counts'] = count($data['siblings']);
        }
        $data['hostelList'] = $this->hostel_model->get();
        $data['houses'] = $this->houselist_model->get();
        $data['provinces'] = $this->state_model->getAll();
        $data['temporary_districts'] = array();
        $data['temporary_municipalities'] = array();
        $data['permanent_districts'] = array();
        $data['permanent_municipalities'] = array();
        if (!empty($student['temporary_province'])) {
            $data['temporary_districts'] = $this->district_model->getByProvinceId($student['temporary_province']);
        }
        if (!empty($student['temporary_district'])) {
            $data['temporary_municipalities'] = $this->municipality_model->getByDistrictId($student['temporary_district']);
        }
        if (!empty($student['permanent_province'])) {
            $data['permanent_districts'] = $this->district_model->getByProvinceId($student['permanent_province']);
        }
        if (!empty($student['permanent_district'])) {
            $data['permanent_municipalities'] = $this->municipality_model->getByDistrictId($student['permanent_district']);
        }
        // Provide option lists expected by the edit view.
        $data['religions'] = array();
        $data['castes'] = array();
        $data['ethnicities'] = $this->_ethnicity_option_list();
        $religion_rows = $this->db->select('religion')->from('students')->where('religion IS NOT NULL', null, false)->where('religion !=', '')->group_by('religion')->order_by('religion', 'ASC')->get()->result_array();
        foreach ($religion_rows as $row) {
            $value = trim((string) $row['religion']);
            if ($value !== '') {
                $data['religions'][$value] = $value;
            }
        }
        $caste_rows = $this->db->select('cast')->from('students')->where('cast IS NOT NULL', null, false)->where('cast !=', '')->group_by('cast')->order_by('cast', 'ASC')->get()->result_array();
        foreach ($caste_rows as $row) {
            $value = trim((string) $row['cast']);
            if ($value !== '') {
                $data['castes'][$value] = $value;
            }
        }
        
        // Set form values for initial load
        if (!$this->input->post()) {
            // Only set these values if form hasn't been posted (i.e., on initial load)
            $_POST['section_id'] = isset($student['section_id']) ? $student['section_id'] : '';
            $_POST['program_id'] = isset($student['program_id']) ? $student['program_id'] : '';  
            $_POST['class_id'] = isset($student['class_id']) ? $student['class_id'] : '';
            $_POST['category_id'] = isset($student['category_id']) ? $student['category_id'] : '';
            $_POST['religion'] = isset($student['religion']) ? $student['religion'] : '';
            $_POST['cast'] = isset($student['cast']) ? $student['cast'] : '';
            $_POST['dob'] = isset($student['dob']) ? $student['dob'] : '';
            $_POST['admission_year'] = isset($student['admission_year']) ? $student['admission_year'] : '';
            
            // Set all other form values too for consistency
            foreach ($student as $key => $value) {
                if (!isset($_POST[$key])) {
                    $_POST[$key] = $value;
                }
            }
        }
        
        $this->load->view('layout/header', $data);
        $this->load->view('student/studentEdit', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getDistrictsByProvince()
    {
        $province_id = $this->input->post('province_id');
        $districts = array();
        if (!empty($province_id)) {
            $districts = $this->district_model->getByProvinceId($province_id);
        }
        header('Content-Type: application/json');
        echo json_encode($districts);
    }

    public function getMunicipalitiesByDistrict()
    {
        $district_id = $this->input->post('district_id');
        $municipalities = array();
        if (!empty($district_id)) {
            $municipalities = $this->municipality_model->getByDistrictId($district_id);
        }
        header('Content-Type: application/json');
        echo json_encode($municipalities);
    }

    public function getByClassAndSection()
    {
        $class_id = $this->input->get_post('class_id');
        $section_id = $this->input->get_post('section_id');
        $program_id = $this->input->get_post('program_id');

        $students = $this->student_model->searchByClassSectionWithSession(
            !empty($class_id) ? $class_id : null,
            !empty($section_id) ? $section_id : null,
            null,
            !empty($program_id) ? $program_id : null
        );

        $result = array();
        if (!empty($students)) {
            foreach ($students as $student) {
                $result[] = array(
                    'id' => isset($student['id']) ? $student['id'] : '',
                    'student_session_id' => isset($student['student_session_id']) ? $student['student_session_id'] : '',
                    'class_id' => isset($student['class_id']) ? $student['class_id'] : '',
                    'section_id' => isset($student['section_id']) ? $student['section_id'] : '',
                    'admission_no' => isset($student['admission_no']) ? $student['admission_no'] : '',
                    'roll_no' => isset($student['roll_no']) ? $student['roll_no'] : '',
                    'firstname' => isset($student['firstname']) ? $student['firstname'] : '',
                    'middlename' => isset($student['middlename']) ? $student['middlename'] : '',
                    'lastname' => isset($student['lastname']) ? $student['lastname'] : '',
                    'full_name' => $this->customlib->getFullName(
                        isset($student['firstname']) ? $student['firstname'] : '',
                        isset($student['middlename']) ? $student['middlename'] : '',
                        isset($student['lastname']) ? $student['lastname'] : '',
                        $this->sch_setting_detail->middlename,
                        $this->sch_setting_detail->lastname
                    ),
                );
            }
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function getByClassAndSectionExcludeMe()
    {
        $class_id = $this->input->get_post('class_id');
        $section_id = $this->input->get_post('section_id');
        $current_student_id = (int) $this->input->get_post('current_student_id');

        $students = $this->student_model->searchByClassSectionWithSession(
            !empty($class_id) ? $class_id : null,
            !empty($section_id) ? $section_id : null
        );

        $result = array();
        if (!empty($students)) {
            foreach ($students as $student) {
                $student_id = isset($student['id']) ? (int) $student['id'] : 0;
                if (!empty($current_student_id) && $student_id === $current_student_id) {
                    continue;
                }
                $result[] = array(
                    'id' => $student_id,
                    'student_session_id' => isset($student['student_session_id']) ? $student['student_session_id'] : '',
                    'class_id' => isset($student['class_id']) ? $student['class_id'] : '',
                    'section_id' => isset($student['section_id']) ? $student['section_id'] : '',
                    'admission_no' => isset($student['admission_no']) ? $student['admission_no'] : '',
                    'roll_no' => isset($student['roll_no']) ? $student['roll_no'] : '',
                    'firstname' => isset($student['firstname']) ? $student['firstname'] : '',
                    'middlename' => isset($student['middlename']) ? $student['middlename'] : '',
                    'lastname' => isset($student['lastname']) ? $student['lastname'] : '',
                    'full_name' => $this->customlib->getFullName(
                        isset($student['firstname']) ? $student['firstname'] : '',
                        isset($student['middlename']) ? $student['middlename'] : '',
                        isset($student['lastname']) ? $student['lastname'] : '',
                        $this->sch_setting_detail->middlename,
                        $this->sch_setting_detail->lastname
                    ),
                );
            }
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function getStudentRecordByID()
    {
        $student_id = (int) $this->input->get_post('student_id');
        $student = array();

        if (!empty($student_id)) {
            $student = $this->db->where('id', $student_id)->get('students')->row_array();
        }

        if (empty($student)) {
            header('Content-Type: application/json');
            echo json_encode(array());
            exit;
        }

        $student['full_name'] = $this->customlib->getFullName(
            isset($student['firstname']) ? $student['firstname'] : '',
            isset($student['middlename']) ? $student['middlename'] : '',
            isset($student['lastname']) ? $student['lastname'] : '',
            $this->sch_setting_detail->middlename,
            $this->sch_setting_detail->lastname
        );

        header('Content-Type: application/json');
        echo json_encode($student);
        exit;
    }

    public function dtstudentlist()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }
        
        // Get search parameters
        $search_text = $this->input->post('search_text') ? $this->input->post('search_text') : '';
        $section_id = $this->input->post('section_id') ? $this->input->post('section_id') : '';
        $program_id = $this->input->post('program_id') ? $this->input->post('program_id') : '';
        $class_id = $this->input->post('class_id') ? $this->input->post('class_id') : '';
        
        $sch_setting = $this->sch_setting_detail;
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $classlist = $this->class_model->get();
        $carray = array();
        if (!empty($classlist)) {
            foreach ($classlist as $ckey => $cvalue) {
                $carray[] = $cvalue["id"];
            }
        }
        
        // Get student data using datatables
        $userdata = $this->customlib->getUserData();
        $class_section_array = $this->customlib->get_myClassSection();
        
        // Use the datatables method from student model
        $resultlist = $this->student_model->getDatatableByFullTextSearch($search_text, $section_id, $program_id, $class_id);
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        
        // Get detail view for the tab
        $datatable_search = $this->input->post('search');
        $resultlist_view = $this->student_model->getSearchFullView($search_text, $start, $length, $datatable_search, $carray, $section_id, $program_id, $class_id);

        // _searchDetailView expects $students->data as objects (same shape as DataTables JSON).
        $detail_rows = array();
        if (!empty($resultlist_view) && is_array($resultlist_view)) {
            foreach ($resultlist_view as $row) {
                $detail_rows[] = is_object($row) ? $row : (object) $row;
            }
        }
        $data = array(
            'students' => (object) array('data' => $detail_rows),
            'resultlist' => $resultlist_view,
            'sch_setting' => $this->sch_setting_detail,
            'adm_auto_insert' => $this->sch_setting_detail->adm_auto_insert,
            'currency_symbol' => $this->customlib->getSchoolCurrencyFormat(),
        );
        
        // Load the detail view
        $student_detail_view = $this->load->view('student/_searchDetailView', $data, true);
        
        $fields = $this->customfield_model->get_custom_fields('students', 1);
        // If model JSON encoding fails or PHP emitted warnings, decoding can fail and
        // would break DataTables with "Invalid JSON response". Guard and fallback.
        $students = json_decode($resultlist);
        if (!$students || !is_object($students)) {
            $students = (object) array(
                'draw' => (int) $this->input->post('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
            );
        }
        $dt_data = array();
        
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {
                $editbtn = '';
                $viewbtn = '';
                $collectbtn = "";
                
                $viewbtn = "<a href='" . base_url() . "student/view/" . $student->id . "' class='btn btn-default btn-xs' data-toggle='tooltip' title='" . $this->lang->line('show') . "'><i class='fa fa-reorder'></i></a>";
                
                if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "student/edit/" . $student->id . "' class='btn btn-default btn-xs' data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                
                if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) {
                    $collectbtn = "<a href='" . base_url() . "studentfee/addfee/" . (isset($student->student_session_id) ? $student->student_session_id : $student->id) . "' class='btn btn-default btn-xs' data-toggle='tooltip' title='" . $this->lang->line('add_fees') . "'><span>" . $currency_symbol . "</span></a>";
                }
                
                $row = array();
                $row[] = isset($student->admission_no) ? $student->admission_no : '';
                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName(
                    isset($student->firstname) ? $student->firstname : '',
                    isset($student->middlename) ? $student->middlename : '',
                    isset($student->lastname) ? $student->lastname : '',
                    $sch_setting->middlename,
                    $sch_setting->lastname
                ) . "</a>";
                
                // Section
                $row[] = isset($student->section) ? $student->section : '';
                
                // Program
                $row[] = isset($student->program_title) ? $student->program_title : (isset($student->program) ? $student->program : '');
                
                // Class (Academic Level)
                $row[] = isset($student->class) ? $student->class : '';
                
                // Gender
                $row[] = isset($student->gender) ? $this->lang->line(strtolower($student->gender)) : '';
                
                // Mobile Number
                $row[] = isset($student->mobileno) ? $student->mobileno : '';
                
                // Religion
                $row[] = isset($student->religion_name) ? $student->religion_name : (isset($student->religion) ? $student->religion : '');
                
                // Caste
                $row[] = isset($student->caste_name) ? $student->caste_name : (isset($student->cast) ? $student->cast : '');
                
                // Category
                $row[] = isset($student->category) ? $student->category : '';

                // HEMIS / UGC sync status (error takes precedence over synced)
                $hemis_err = isset($student->hemis_sync_error) ? trim((string) $student->hemis_sync_error) : '';
                $hemis_sid = isset($student->hemis_student_id) ? trim((string) $student->hemis_student_id) : '';
                if ($hemis_err !== '') {
                    $err_title = htmlspecialchars(preg_replace('/\s+/', ' ', $hemis_err), ENT_QUOTES, 'UTF-8');
                    $retry = '';
                    if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                        $retry = ' <button type="button" class="btn btn-xs btn-default hemis-retry-sync" data-student-id="' . (int) $student->id . '" title="Retry HEMIS sync"><i class="fa fa-refresh"></i></button>';
                    }
                    $row[] = '<span class="label label-danger hemis-badge-cell" title="' . $err_title . '">Error</span>' . $retry;
                } elseif ($hemis_sid !== '') {
                    $row[] = '<span class="label label-success">Synced</span>';
                } else {
                    $row[] = '<span class="text-muted">&mdash;</span>';
                }

                // HEMIS Id (separate column)
                $row[] = ($hemis_sid !== '' && (int) $hemis_sid > 0)
                    ? '<span class="hemis-id-cell">' . htmlspecialchars($hemis_sid, ENT_QUOTES, 'UTF-8') . '</span>'
                    : '<span class="text-muted">&mdash;</span>';
                
                // Status
                $status = isset($student->is_active) && $student->is_active == 'yes' ? 
                    '<span class="label label-success">' . $this->lang->line('active') . '</span>' : 
                    '<span class="label label-danger">' . $this->lang->line('inactive') . '</span>';
                $row[] = $status;
                
                // Action buttons
                $row[] = $viewbtn . ' ' . $editbtn . ' ' . $collectbtn;
                
                $dt_data[] = $row;
            }
        }
        
        $json_data = array(
            "draw" => isset($students->draw) ? intval($students->draw) : intval($this->input->post('draw')),
            "recordsTotal" => isset($students->recordsTotal) ? intval($students->recordsTotal) : 0,
            "recordsFiltered" => isset($students->recordsFiltered) ? intval($students->recordsFiltered) : 0,
            "data" => $dt_data,
            "student_detail_view" => $student_detail_view
        );
        
        header('Content-Type: application/json');
        echo json_encode($json_data);
        exit;
    }

    /**
     * AJAX: retry outbound HEMIS sync for one student (uses DB snapshot; no full page reload).
     *
     * @param int $id students.id
     */
    public function manual_sync($id = null)
    {
        if (!$this->rbac->hasPrivilege('student', 'can_edit')) {
            access_denied();
        }
        $id = (int) $id;
        header('Content-Type: application/json; charset=utf-8');
        if ($id < 1) {
            echo json_encode(array('success' => false, 'message' => 'Invalid student id'));
            return;
        }
        $this->load->library('hemis_client');
        $row = $this->student_model->get_student_by_id($id);
        if (!$row) {
            echo json_encode(array('success' => false, 'message' => 'Student not found'));
            return;
        }
        $gateMsg = $this->_student_hemis_combined_gate_message($id);
        if ($gateMsg !== null) {
            $out = $this->_student_hemis_skip_sync_outcome($id, $gateMsg);
        } else {
            $hid = isset($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : 0;
            if ($hid > 0) {
                $out = $this->hemis_client->sync_student_update_from_database_snapshot($id);
            } else {
                $out = $this->hemis_client->sync_student_after_database_snapshot($id);
            }
        }
        $fresh = $this->student_model->get_student_by_id($id);
        $err = isset($fresh['hemis_sync_error']) ? trim((string) $fresh['hemis_sync_error']) : '';
        $hsid = isset($fresh['hemis_student_id']) ? trim((string) $fresh['hemis_student_id']) : '';
        $synced = !empty($out['synced']);
        $serverBusy = !empty($out['server_busy'])
            || ($err !== '' && stripos($err, 'Server Busy:') === 0);
        echo json_encode(array(
            'success'       => $synced && $err === '',
            'synced'        => $synced,
            'message'       => !empty($out['error']) ? (string) $out['error'] : ($synced ? 'Synced' : 'Sync finished'),
            'hemis_student_id' => ($hsid !== '' && (int) $hsid > 0) ? (int) $hsid : null,
            'hemis_sync_error' => ($err !== '') ? $err : null,
            'mode'          => isset($out['mode']) ? $out['mode'] : null,
            'server_busy'   => $serverBusy,
            'http_code'     => isset($out['http_code']) ? (int) $out['http_code'] : null,
        ));
    }

    /**
     * AJAX: list student IDs eligible for outbound HEMIS sync (for queued client-side bulk sync).
     */
    public function hemis_sync_queue_ids()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_edit')) {
            access_denied();
        }
        header('Content-Type: application/json; charset=utf-8');
        $ids = $this->student_model->getStudentIdsForBulkHemisSync(10000);
        echo json_encode(array(
            'success' => true,
            'total'   => count($ids),
            'ids'     => $ids,
        ));
    }

    /**
     * AJAX: batch HEMIS sync (up to 50 students: missing hemis id or sync error).
     */
    public function bulk_hemis_sync()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_edit')) {
            access_denied();
        }
        header('Content-Type: application/json; charset=utf-8');
        $this->load->library('hemis_client');
        $ids = $this->student_model->getStudentIdsForBulkHemisSync(50);
        $results = array();
        $ok = 0;
        $fail = 0;
        foreach ($ids as $sid) {
            $row = $this->student_model->get_student_by_id($sid);
            if (!$row) {
                continue;
            }
            $gateMsg = $this->_student_hemis_combined_gate_message($sid);
            if ($gateMsg !== null) {
                $out = $this->_student_hemis_skip_sync_outcome($sid, $gateMsg);
            } else {
                $hid = isset($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : 0;
                if ($hid > 0) {
                    $out = $this->hemis_client->sync_student_update_from_database_snapshot($sid);
                } else {
                    $out = $this->hemis_client->sync_student_after_database_snapshot($sid);
                }
            }
            $fresh = $this->student_model->get_student_by_id($sid);
            $err = isset($fresh['hemis_sync_error']) ? trim((string) $fresh['hemis_sync_error']) : '';
            $good = !empty($out['synced']) && $err === '';
            if ($good) {
                $ok++;
            } else {
                $fail++;
            }
            $results[] = array(
                'id'               => $sid,
                'synced'           => !empty($out['synced']),
                'hemis_student_id' => isset($fresh['hemis_student_id']) ? $fresh['hemis_student_id'] : null,
                'error'            => $err !== '' ? $err : (isset($out['error']) ? $out['error'] : null),
            );
        }
        echo json_encode(array(
            'success'  => true,
            'attempted' => count($ids),
            'succeeded' => $ok,
            'failed'    => $fail,
            'results'   => $results,
        ));
    }

    /**
     * Student CSV import: POST parses CSV, creates students + student_session for selected class.
     */
    public function import()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/import');
        $data['title'] = $this->lang->line('import_student');

        if (strtoupper($this->input->method(true)) === 'POST') {
            $this->load->library('form_validation');
            $this->load->library('encoding_lib');

            $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|is_natural_no_zero');
            $this->form_validation->set_rules('file', $this->lang->line('select_csv_file'), 'callback_handle_student_csv_upload');

            if ($this->form_validation->run() === false) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . validation_errors() . '</div>');
                redirect('student/import');
                return;
            }

            $class_id   = (int) $this->input->post('class_id');
            $section_id = (int) $this->input->post('section_id');
            $program_id = (int) $this->input->post('program_id');

            $rowcount = 0;
            $totalRows = 0;
            $import_failures = array();
            $student_csv_processed = false;

            if (isset($_FILES['file']['name']) && $_FILES['file']['name'] !== '' && strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) === 'csv') {
                $student_csv_processed = true;
                $this->load->library('CSVReader');
                $result = $this->csvreader->parse_file($_FILES['file']['tmp_name']);

                if (!empty($result)) {
                    $this->db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
                    $allowed = array_flip($this->db->list_fields('students'));
                    $adm_auto = isset($this->sch_setting_detail->adm_auto_insert) ? (int) $this->sch_setting_detail->adm_auto_insert : 0;
                    $current_session = $this->setting_model->getCurrentSession();
                    $this->load->library('hemis_client');

                    foreach ($result as $r_key => $r_value) {
                        if ($r_key == 0 && isset($r_value['admission_no']) && strtolower(trim((string) $r_value['admission_no'])) === 'admission_no') {
                            continue;
                        }
                        $totalRows++;
                        $file_row = $r_key + 1;
                        $row = $r_value;

                        $admission_label = isset($row['admission_no']) ? trim((string) $row['admission_no']) : '';
                        $email_label = isset($row['email']) ? trim((string) $row['email']) : '';
                        $name_label = trim(
                            (isset($row['firstname']) ? $row['firstname'] : '') . ' '
                            . (isset($row['middlename']) ? $row['middlename'] : '') . ' '
                            . (isset($row['lastname']) ? $row['lastname'] : '')
                        );

                        $prep = $this->_student_import_prepare_row($row, $allowed);
                        $nepali_err = $this->_student_import_detect_corrupted_nepali($row, $prep);
                        if ($nepali_err !== null) {
                            $import_failures[] = array(
                                'row' => $file_row,
                                'admission_no' => $admission_label,
                                'name' => $name_label,
                                'email' => $email_label,
                                'reason' => $nepali_err,
                            );
                            continue;
                        }
                        if (!empty($prep['error'])) {
                            $import_failures[] = array(
                                'row' => $file_row,
                                'admission_no' => $admission_label,
                                'name' => $name_label,
                                'email' => $email_label,
                                'reason' => $prep['error'],
                            );
                            continue;
                        }

                        $student_data = $prep['student_data'];

                        if (empty($student_data['admission_no'])) {
                            if ($adm_auto) {
                                $student_data['admission_no'] = 'IMPORT_' . date('YmdHis') . '_' . $file_row;
                            } else {
                                $import_failures[] = array(
                                    'row' => $file_row,
                                    'admission_no' => $admission_label,
                                    'name' => $name_label,
                                    'email' => $email_label,
                                    'reason' => sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('admission_no')),
                                );
                                continue;
                            }
                        }

                        if ($this->student_model->import_admission_no_exists($student_data['admission_no'])) {
                            $import_failures[] = array(
                                'row' => $file_row,
                                'admission_no' => $student_data['admission_no'],
                                'name' => $name_label,
                                'email' => $email_label,
                                'reason' => $this->lang->line('student_import_duplicate_admission_no'),
                            );
                            continue;
                        }

                        if (!empty($student_data['email']) && $this->student_model->import_student_email_exists($student_data['email'])) {
                            $import_failures[] = array(
                                'row' => $file_row,
                                'admission_no' => $student_data['admission_no'],
                                'name' => $name_label,
                                'email' => $student_data['email'],
                                'reason' => $this->lang->line('student_import_duplicate_email'),
                            );
                            continue;
                        }

                        $student_id = $this->student_model->add($student_data, array('adm_auto_insert' => $adm_auto));
                        if (!$student_id) {
                            $db_err = $this->db->error();
                            $fail_reason = (isset($db_err['message']) && trim((string) $db_err['message']) !== '')
                                ? $db_err['message']
                                : $this->lang->line('staff_import_failed_save');
                            $import_failures[] = array(
                                'row' => $file_row,
                                'admission_no' => $student_data['admission_no'],
                                'name' => $name_label,
                                'email' => isset($student_data['email']) ? $student_data['email'] : '',
                                'reason' => $fail_reason,
                            );
                            continue;
                        }

                        $sess_id = $this->student_model->add_student_session(array(
                            'student_id' => $student_id,
                            'session_id' => $current_session,
                            'class_id'   => $class_id,
                            'section_id' => $section_id,
                            'program_id' => $program_id,
                            'is_active'  => 'yes',
                        ));

                        if (!$sess_id) {
                            $this->student_model->remove($student_id);
                            $db_err = $this->db->error();
                            $fail_reason = (isset($db_err['message']) && trim((string) $db_err['message']) !== '')
                                ? $db_err['message']
                                : $this->lang->line('staff_import_failed_save');
                            $import_failures[] = array(
                                'row' => $file_row,
                                'admission_no' => $student_data['admission_no'],
                                'name' => $name_label,
                                'email' => isset($student_data['email']) ? $student_data['email'] : '',
                                'reason' => $fail_reason,
                            );
                            continue;
                        }

                        $gateMsg = $this->_student_hemis_combined_gate_message((int) $student_id);
                        if ($gateMsg !== null) {
                            $this->_student_hemis_skip_sync_outcome((int) $student_id, $gateMsg);
                        } else {
                            $this->hemis_client->sync_student_after_database_snapshot((int) $student_id);
                        }

                        $rowcount++;
                    }
                }

                $this->session->set_flashdata('staff_import_result', array(
                    'success_count' => $rowcount,
                    'total_rows'    => $totalRows,
                    'failures'      => $import_failures,
                    'mode'          => 'student',
                ));
            } elseif ($student_csv_processed === false) {
                if (isset($_FILES['file']['name']) && $_FILES['file']['name'] !== '') {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('please_upload_csv_file_only') . '</div>');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('the_file_field_is_required') . '</div>');
                }
            }

            redirect('student/import');
            return;
        }

        $data['fields'] = $this->customfield_model->get_custom_fields('students', 1);
        $data['import_hemis_checklist'] = $this->_student_import_hemis_checklist();
        $data['import_schema'] = $this->_student_import_column_schema();
        $this->load->view('layout/header', $data);
        $this->load->view('student/import', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * JSON: full cached UGC address list for import page cascade (exact spellings).
     */
    public function import_ugc_addresses()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }
        $this->load->model('ugc_metadata_model');
        $rows = $this->ugc_metadata_model->get_addresses_cached();
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => 1,
            'count'  => count($rows),
            'data'   => $rows,
        )));
    }

    /**
     * JSON: search ugc_addresses for CSV address columns (combined_code lookup).
     */
    public function import_lookup_address()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }
        if (!$this->db->table_exists('ugc_addresses')) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'UGC address cache empty. Refresh metadata first.', 'data' => array())));
            return;
        }

        $district = trim((string) $this->input->get('district'));
        $q = trim((string) $this->input->get('q'));
        $limit = (int) $this->input->get('limit');
        if ($limit < 1 || $limit > 100) {
            $limit = 40;
        }

        $this->db->select('combined_code, province, district, local_level')
            ->from('ugc_addresses');
        if ($district !== '') {
            $this->db->where('district', $district);
        }
        if ($q !== '') {
            $esc = $this->db->escape_like_str($q);
            $this->db->group_start();
            $this->db->like('local_level', $esc, 'both');
            $this->db->or_like('combined_code', $esc, 'both');
            $this->db->or_like('province', $esc, 'both');
            $this->db->or_like('district', $esc, 'both');
            $this->db->group_end();
        }
        $rows = $this->db->order_by('district', 'ASC')
            ->order_by('local_level', 'ASC')
            ->limit($limit)
            ->get()
            ->result_array();

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status' => 1,
            'data'   => $rows,
        )));
    }

    /**
     * JSON: HEMIS import preflight status (cache counts, ID reference, admission year map).
     */
    public function import_hemis_preflight()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'    => 1,
            'checklist' => $this->_student_import_hemis_checklist(),
        )));
    }

    /**
     * Pull UGC metadata from HEMIS (programs, batches, fiscal years, addresses) for import operators.
     */
    public function import_refresh_metadata()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')
            && !$this->rbac->hasPrivilege('student', 'can_add')
            && !$this->rbac->hasPrivilege('student', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $this->load->model('ugc_metadata_model');
        $this->load->library('hemis_client');
        $this->ugc_metadata_model->ensure_tables();

        $pull = array();

        $p = $this->hemis_client->hemis_get_college_programs();
        if (!empty($p['ok']) && is_array($p['data']) && !empty($p['data'])) {
            $this->ugc_metadata_model->upsert_programs($p['data']);
        }
        $pull['programs'] = array(
            'ok'    => !empty($p['ok']),
            'count' => is_array($p['data'] ?? null) ? count($p['data']) : 0,
            'error' => !empty($p['error']) ? (string) $p['error'] : null,
        );

        $b = $this->hemis_client->hemis_get_batch();
        if (!empty($b['ok']) && is_array($b['data']) && !empty($b['data'])) {
            $this->ugc_metadata_model->upsert_batches($b['data']);
        }
        $pull['batches'] = array(
            'ok'    => !empty($b['ok']),
            'count' => is_array($b['data'] ?? null) ? count($b['data']) : 0,
            'error' => !empty($b['error']) ? (string) $b['error'] : null,
        );

        $fy = $this->hemis_client->hemis_get_fiscal_year();
        if (!empty($fy['ok']) && is_array($fy['data']) && !empty($fy['data'])) {
            $this->ugc_metadata_model->upsert_fiscal_years($fy['data']);
        }
        $pull['fiscal_years'] = array(
            'ok'    => !empty($fy['ok']),
            'count' => is_array($fy['data'] ?? null) ? count($fy['data']) : 0,
            'error' => !empty($fy['error']) ? (string) $fy['error'] : null,
        );

        $a = $this->hemis_client->hemis_get_address_all();
        if (!empty($a['ok']) && is_array($a['data']) && !empty($a['data'])) {
            $this->ugc_metadata_model->upsert_addresses($a['data']);
        }
        $pull['addresses'] = array(
            'ok'    => !empty($a['ok']),
            'count' => is_array($a['data'] ?? null) ? count($a['data']) : 0,
            'error' => !empty($a['error']) ? (string) $a['error'] : null,
        );

        $checklist = $this->_student_import_hemis_checklist();
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'    => 1,
            'message'   => $this->lang->line('student_import_metadata_refreshed'),
            'pull'      => $pull,
            'checklist' => $checklist,
        )));
    }

    /**
     * Build HEMIS pre-import checklist payload for the import UI.
     *
     * @return array
     */
    protected function _student_import_hemis_checklist()
    {
        $cache_items = array(
            array('key' => 'ugc_programs', 'label' => 'UGC Programs', 'table' => 'ugc_programs', 'min' => 1),
            array('key' => 'ugc_batches', 'label' => 'UGC Batches', 'table' => 'ugc_batches', 'min' => 1),
            array('key' => 'ugc_fiscal_years', 'label' => 'UGC Fiscal Years', 'table' => 'ugc_fiscal_years', 'min' => 1),
            array('key' => 'ugc_addresses', 'label' => 'UGC Addresses', 'table' => 'ugc_addresses', 'min' => 1),
        );

        $items = array();
        $ready = true;
        foreach ($cache_items as $def) {
            $count = 0;
            if ($this->db->table_exists($def['table'])) {
                $count = (int) $this->db->count_all($def['table']);
            }
            $ok = $count >= (int) $def['min'];
            if (!$ok) {
                $ready = false;
            }
            $items[] = array(
                'key'   => $def['key'],
                'label' => $def['label'],
                'count' => $count,
                'ok'    => $ok,
            );
        }

        $hemis_cfg = $this->config->item('hemis');
        $admission_year_map = (is_array($hemis_cfg) && isset($hemis_cfg['admission_year_id_map']) && is_array($hemis_cfg['admission_year_id_map']))
            ? $hemis_cfg['admission_year_id_map']
            : array();

        $admission_years_used = array();
        if ($this->db->field_exists('admissionYearId', 'students')) {
            $this->db->select('admission_year, admissionYearId, COUNT(*) AS student_count', false);
            $this->db->from('students');
            $this->db->where('admissionYearId IS NOT NULL', null, false);
            $this->db->where('admissionYearId >', 0);
            $this->db->group_by(array('admission_year', 'admissionYearId'));
            $this->db->order_by('admission_year', 'DESC');
            $this->db->limit(25);
            $q = $this->db->get();
            if ($q) {
                $admission_years_used = $q->result_array();
            }
        }

        $programs_ref = array();
        if ($this->db->table_exists('ugc_programs')) {
            $programs_ref = $this->db->select('program_id AS id, program_name AS name')
                ->from('ugc_programs')
                ->order_by('program_name', 'ASC')
                ->limit(30)
                ->get()
                ->result_array();
        }

        $batches_ref = array();
        if ($this->db->table_exists('ugc_batches')) {
            $batches_ref = $this->db->select('id, name, batch_nepali')
                ->from('ugc_batches')
                ->order_by('idx', 'DESC')
                ->order_by('id', 'DESC')
                ->limit(30)
                ->get()
                ->result_array();
        }

        $fiscal_ref = array();
        if ($this->db->table_exists('ugc_fiscal_years')) {
            $fiscal_ref = $this->db->select('id, year_nepali, year_english, active_fiscal_year')
                ->from('ugc_fiscal_years')
                ->order_by('idx', 'DESC')
                ->order_by('id', 'DESC')
                ->limit(30)
                ->get()
                ->result_array();
        }

        $categories_ref = array();
        if ($this->db->table_exists('categories')) {
            $categories_ref = $this->db->select('id, category AS name')
                ->from('categories')
                ->order_by('category', 'ASC')
                ->limit(50)
                ->get()
                ->result_array();
        }

        $districts_ref = array();
        if ($this->db->table_exists('ugc_addresses')) {
            $districts_ref = $this->db->select('DISTINCT district', false)
                ->from('ugc_addresses')
                ->where('district IS NOT NULL', null, false)
                ->where('district !=', '')
                ->order_by('district', 'ASC')
                ->limit(120)
                ->get()
                ->result_array();
        }

        return array(
            'ready'                  => $ready,
            'items'                  => $items,
            'admission_year_id_map'  => $admission_year_map,
            'has_admission_year_map' => !empty($admission_year_map),
            'admission_years_used'   => $admission_years_used,
            'programs_ref'           => $programs_ref,
            'batches_ref'            => $batches_ref,
            'fiscal_ref'             => $fiscal_ref,
            'categories_ref'         => $categories_ref,
            'districts_ref'          => $districts_ref,
            'hemis_settings_url'     => site_url('hemisintegration'),
        );
    }

    /**
     * Canonical student import CSV columns (UI + sample download). UGC address only — no duplicate local province columns.
     *
     * @return array
     */
    protected function _student_import_column_schema()
    {
        $groups = array(
            array(
                'id'    => 'hemis',
                'label' => 'HEMIS / UGC (required for sync)',
                'columns' => array(
                    array('col' => 'admission_no', 'label' => 'Admission No', 'req' => true),
                    array('col' => 'ugc_program_id', 'label' => 'UGC Program ID', 'req' => true),
                    array('col' => 'ugc_batch_id', 'label' => 'UGC Batch ID', 'req' => true),
                    array('col' => 'ugc_fiscal_year_id', 'label' => 'UGC Fiscal Year ID', 'req' => true),
                    array('col' => 'roll_no', 'label' => 'Roll No', 'req' => false),
                    array('col' => 'admission_date', 'label' => 'Admission Date', 'req' => false),
                    array('col' => 'academic_program_duration', 'label' => 'Program Duration', 'req' => false),
                    array('col' => 'complition_year', 'label' => 'Completion Year', 'req' => false),
                ),
            ),
            array(
                'id'    => 'student',
                'label' => 'Student',
                'columns' => array(
                    array('col' => 'firstname', 'label' => 'First Name', 'req' => true),
                    array('col' => 'middlename', 'label' => 'Middle Name', 'req' => false),
                    array('col' => 'lastname', 'label' => 'Last Name', 'req' => false),
                    array('col' => 'firstname_np', 'label' => 'First Name (NP)', 'req' => false),
                    array('col' => 'middlename_np', 'label' => 'Middle Name (NP)', 'req' => false),
                    array('col' => 'lastname_np', 'label' => 'Last Name (NP)', 'req' => false),
                    array('col' => 'mobileno', 'label' => 'Mobile', 'req' => false),
                    array('col' => 'email', 'label' => 'Email', 'req' => false),
                    array('col' => 'dob_bs', 'label' => 'DOB (B.S.)', 'req' => true),
                    array('col' => 'dob', 'label' => 'DOB (A.D.)', 'req' => false),
                    array('col' => 'gender', 'label' => 'Gender', 'req' => true),
                    array('col' => 'religion', 'label' => 'Religion', 'req' => false),
                    array('col' => 'cast', 'label' => 'Caste', 'req' => false),
                    array('col' => 'ethnicity', 'label' => 'Ethnicity', 'req' => true),
                    array('col' => 'edj', 'label' => 'EDJ', 'req' => false),
                    array('col' => 'national_id', 'label' => 'National ID', 'req' => false),
                ),
            ),
            array(
                'id'    => 'address',
                'label' => 'Address (UGC labels — not local states/districts tables)',
                'columns' => array(
                    array('col' => 'ugc_p_province', 'label' => 'Permanent Province (UGC)', 'req' => false),
                    array('col' => 'ugc_p_district', 'label' => 'Permanent District (UGC)', 'req' => false),
                    array('col' => 'ugc_p_local_level', 'label' => 'Permanent Local Level (UGC)', 'req' => false),
                    array('col' => 'ugc_p_combined_code', 'label' => 'Permanent Combined Code', 'req' => false),
                    array('col' => 'permanent_ward_no', 'label' => 'Permanent Ward No', 'req' => true),
                    array('col' => 'permanent_house_no', 'label' => 'Permanent House No', 'req' => false),
                    array('col' => 'permanent_address', 'label' => 'Permanent Address (text)', 'req' => false),
                    array('col' => 'ugc_t_province', 'label' => 'Temporary Province (UGC)', 'req' => false),
                    array('col' => 'ugc_t_district', 'label' => 'Temporary District (UGC)', 'req' => false),
                    array('col' => 'ugc_t_local_level', 'label' => 'Temporary Local Level (UGC)', 'req' => false),
                    array('col' => 'ugc_t_combined_code', 'label' => 'Temporary Combined Code', 'req' => false),
                    array('col' => 'temporary_ward_no', 'label' => 'Temporary Ward No', 'req' => false),
                    array('col' => 'temporary_house_no', 'label' => 'Temporary House No', 'req' => false),
                    array('col' => 'current_address', 'label' => 'Current Address (text)', 'req' => false),
                ),
            ),
            array(
                'id'    => 'guardian',
                'label' => 'Guardian',
                'columns' => array(
                    array('col' => 'guardian_name', 'label' => 'Guardian Name', 'req' => true),
                    array('col' => 'guardian_relation', 'label' => 'Guardian Relation', 'req' => false),
                    array('col' => 'guardian_phone', 'label' => 'Guardian Phone', 'req' => true),
                    array('col' => 'guardian_occupation', 'label' => 'Guardian Occupation', 'req' => false),
                    array('col' => 'guardian_address', 'label' => 'Guardian Address', 'req' => false),
                    array('col' => 'guardian_email', 'label' => 'Guardian Email', 'req' => false),
                    array('col' => 'father_name', 'label' => 'Father Name', 'req' => false),
                    array('col' => 'mother_name', 'label' => 'Mother Name', 'req' => false),
                ),
            ),
            array(
                'id'    => 'optional',
                'label' => 'Optional',
                'columns' => array(
                    array('col' => 'citizenship', 'label' => 'Citizenship', 'req' => false),
                    array('col' => 'citizenship_issue_dist', 'label' => 'Citizenship Issued District', 'req' => false),
                    array('col' => 'nationality', 'label' => 'Nationality', 'req' => false),
                    array('col' => 'disability_status', 'label' => 'Disability Status', 'req' => false),
                    array('col' => 'disability_type', 'label' => 'Disability Type', 'req' => false),
                    array('col' => 'category_id', 'label' => 'Category ID', 'req' => false),
                    array('col' => 'father_email', 'label' => 'Father Email', 'req' => false),
                    array('col' => 'mother_email', 'label' => 'Mother Email', 'req' => false),
                    array('col' => 'program_fee', 'label' => 'Program Fee', 'req' => false),
                    array('col' => 'scholarship_amt', 'label' => 'Scholarship Amount', 'req' => false),
                    array('col' => 'paid_amount', 'label' => 'Paid Amount', 'req' => false),
                    array('col' => 'entrance_score', 'label' => 'Entrance Score', 'req' => false),
                    array('col' => 'entrance_symbol', 'label' => 'Entrance Symbol', 'req' => false),
                    array('col' => 'medium', 'label' => 'Medium', 'req' => false),
                    array('col' => 'shift', 'label' => 'Shift', 'req' => false),
                    array('col' => 'type', 'label' => 'Type', 'req' => false),
                    array('col' => 'semister_or_year_no', 'label' => 'Semester/Year', 'req' => false),
                    array('col' => 'has_scolorship', 'label' => 'Has Scholarship (yes/no)', 'req' => false),
                    array('col' => 'is_verified', 'label' => 'Is Verified (yes/no)', 'req' => false),
                    array('col' => 'is_mukta_kamaiya', 'label' => 'Mukta Kamaiya (yes/no)', 'req' => false),
                    array('col' => 'is_from_martyr_family', 'label' => 'Martyr Family (yes/no)', 'req' => false),
                    array('col' => 'is_active', 'label' => 'Is Active (yes/no)', 'req' => false),
                ),
            ),
        );

        $sample_rows = array(
            array(
                'admission_no' => '1050878781',
                'ugc_program_id' => '33', 'ugc_batch_id' => '22', 'ugc_fiscal_year_id' => '4',
                'roll_no' => '1', 'admission_date' => '2025-02-02', 'academic_program_duration' => '4', 'complition_year' => '2085',
                'firstname' => 'Ram', 'middlename' => '', 'lastname' => 'Singh',
                'firstname_np' => 'राम', 'middlename_np' => '', 'lastname_np' => 'सिंह',
                'mobileno' => '9841234567', 'email' => 'ram7zxz@example.com',
                'dob_bs' => '2050-12-12', 'dob' => '1994-03-25', 'gender' => 'Male',
                'religion' => 'Hindu', 'cast' => 'Brahmin', 'ethnicity' => 'Brahmin', 'edj' => 'No', 'national_id' => 'NP12345678',
                'ugc_p_province' => 'Bagmati Province', 'ugc_p_district' => 'Kathmandu', 'ugc_p_local_level' => 'Shankharapur Municipality', 'ugc_p_combined_code' => '30601',
                'permanent_ward_no' => '5', 'permanent_house_no' => '10', 'permanent_address' => '123 Main St',
                'ugc_t_province' => '', 'ugc_t_district' => '', 'ugc_t_local_level' => '', 'ugc_t_combined_code' => '',
                'temporary_ward_no' => '', 'temporary_house_no' => '', 'current_address' => '',
                'guardian_name' => 'Hari Singh', 'guardian_relation' => 'Father', 'guardian_phone' => '9841122334',
                'guardian_occupation' => 'Businessman', 'guardian_address' => '123 Main St', 'guardian_email' => 'hari@example.com',
                'father_name' => 'Hari Singh', 'mother_name' => 'Sita Devi',
                'citizenship' => '1234567/78/9012', 'citizenship_issue_dist' => 'Kathmandu', 'nationality' => 'Nepali',
                'category_id' => '2', 'is_active' => 'yes',
            ),
            array(
                'admission_no' => '787810502',
                'ugc_program_id' => '33', 'ugc_batch_id' => '22', 'ugc_fiscal_year_id' => '4',
                'roll_no' => '2', 'admission_date' => '2025-02-03', 'academic_program_duration' => '4', 'complition_year' => '2085',
                'firstname' => 'Sita', 'middlename' => '', 'lastname' => 'Sharma',
                'firstname_np' => 'सिता', 'middlename_np' => '', 'lastname_np' => 'शर्मा',
                'mobileno' => '9851234567', 'email' => 'sitax89z@example.com',
                'dob_bs' => '2050-12-12', 'dob' => '1994-03-25', 'gender' => 'Female',
                'religion' => 'Hindu', 'cast' => 'Chhetri', 'ethnicity' => 'Chhetri', 'edj' => 'No', 'national_id' => 'NP23456789',
                'ugc_p_province' => 'Gandaki Province', 'ugc_p_district' => 'Kaski', 'ugc_p_local_level' => 'Madi Rural Municipality', 'ugc_p_combined_code' => '40501',
                'permanent_ward_no' => '3', 'permanent_house_no' => '12', 'permanent_address' => '456 Park Rd',
                'ugc_t_province' => 'Bagmati Province', 'ugc_t_district' => 'Lalitpur', 'ugc_t_local_level' => 'Lalitpur Metropolitan City', 'ugc_t_combined_code' => '',
                'temporary_ward_no' => '5', 'temporary_house_no' => '8', 'current_address' => '789 Temple St',
                'guardian_name' => 'Krishna Sharma', 'guardian_relation' => 'Father', 'guardian_phone' => '9851122334',
                'guardian_occupation' => 'Teacher', 'guardian_address' => '456 Park Rd', 'guardian_email' => 'krishna@example.com',
                'father_name' => 'Krishna Sharma', 'mother_name' => 'Radha Sharma',
                'citizenship' => '2345678/78/9012', 'nationality' => 'Nepali', 'category_id' => '3', 'has_scolorship' => 'yes', 'is_active' => 'yes',
            ),
        );

        $all_columns = array();
        foreach ($groups as $g) {
            foreach ($g['columns'] as $c) {
                $all_columns[] = $c['col'];
            }
        }

        return array(
            'groups'       => $groups,
            'all_columns'  => $all_columns,
            'sample_rows'  => $sample_rows,
        );
    }

    /**
     * Build UTF-8 CSV string for sample download from canonical schema.
     *
     * @return string
     */
    protected function _student_import_build_sample_csv()
    {
        $path = FCPATH . 'backend/import/import_student_sample_file.csv';
        if (is_readable($path)) {
            $data = file_get_contents($path);
            if ($data !== false && $data !== '') {
                if (substr($data, 0, 3) !== "\xEF\xBB\xBF") {
                    $data = "\xEF\xBB\xBF" . $data;
                }

                return $data;
            }
        }

        $schema = $this->_student_import_column_schema();
        $cols = $schema['all_columns'];
        $lines = array(implode(',', $cols));
        foreach ($schema['sample_rows'] as $row) {
            $cells = array();
            foreach ($cols as $col) {
                $v = isset($row[$col]) ? (string) $row[$col] : '';
                if (strpos($v, ',') !== false || strpos($v, '"') !== false || strpos($v, "\n") !== false) {
                    $v = '"' . str_replace('"', '""', $v) . '"';
                }
                $cells[] = $v;
            }
            $lines[] = implode(',', $cells);
        }

        return "\xEF\xBB\xBF" . implode("\n", $lines) . "\n";
    }

    public function handle_student_csv_upload()
    {
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array(
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt',
            );
            $temp      = explode('.', $_FILES['file']['name']);
            $extension = strtolower(end($temp));
            if ($_FILES['file']['error'] > 0) {
                $this->form_validation->set_message('handle_student_csv_upload', $this->lang->line('please_select_file'));
                return false;
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $this->form_validation->set_message('handle_student_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_student_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        }
        $this->form_validation->set_message('handle_student_csv_upload', $this->lang->line('please_select_file'));
        return false;
    }

    /**
     * Map CSV header (lowercased by CSVReader) to students table column name.
     *
     * @param string $csvKey
     * @param array  $allowed  flip(list_fields('students'))
     * @param array  $key_map
     * @return string|null
     */
    /**
     * Detect CSV rows where Nepali text was lost before upload (Excel non-UTF-8 export shows as ?).
     *
     * @param array $row Raw CSV row
     * @param array $prep Result from _student_import_prepare_row
     * @return string|null Error message or null
     */
    private function _student_import_detect_corrupted_nepali(array $row, array $prep)
    {
        if (!empty($prep['error'])) {
            return null;
        }
        $student = isset($prep['student_data']) ? $prep['student_data'] : array();
        $checks = array(
            array('cols' => array('firstname_np', 'firstname_', 'first_name_np'), 'db' => 'first_name_np', 'label' => 'First name (Nepali)'),
            array('cols' => array('lastname_np', 'lastname_', 'last_name_np'), 'db' => 'last_name_np', 'label' => 'Last name (Nepali)'),
            array('cols' => array('middlename_np', 'middlename_', 'middlenam', 'middlenan', 'middle_name_np'), 'db' => 'middle_name_np', 'label' => 'Middle name (Nepali)'),
        );
        foreach ($checks as $def) {
            $raw = '';
            foreach ($def['cols'] as $col) {
                $key = strtolower($col);
                if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                    $raw = trim((string) $row[$key]);
                    break;
                }
            }
            if ($raw === '') {
                continue;
            }
            if ($this->_student_import_text_looks_corrupted($raw)) {
                return $this->lang->line('student_import_nepali_encoding_error');
            }
        }

        return null;
    }

    /**
     * True only when CSV text is clearly broken (Excel ANSI export), not valid Unicode Nepali.
     *
     * @param string $text
     * @return bool
     */
    private function _student_import_text_looks_corrupted($text)
    {
        $text = trim((string) $text);
        if ($text === '') {
            return false;
        }

        // Excel plain CSV: every non-ASCII letter became ?
        if (preg_match('/^[\?？\x{FFFD}]+$/u', $text)) {
            return true;
        }

        // Nepali / Devanagari Unicode block — always accept
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            return false;
        }

        // Other valid Unicode letters (Romanized Nepali, etc.)
        if (preg_match('/[\p{L}\p{M}]/u', $text)) {
            return false;
        }

        // Plain ASCII / numbers are fine
        if (preg_match('/^[\x20-\x7E]+$/', $text)) {
            return false;
        }

        // UTF-16 cell leaked through: try conversion before rejecting
        if (strpos($text, "\x00") !== false) {
            $converted = @mb_convert_encoding($text, 'UTF-8', 'UTF-16LE');
            if ($converted !== false && !$this->_student_import_text_looks_corrupted($converted)) {
                return false;
            }
        }

        return !mb_check_encoding($text, 'UTF-8');
    }

    private function _student_import_csv_column_to_db($csvKey, array $allowed, array $key_map)
    {
        $csvKey = strtolower(trim((string) $csvKey));
        if ($csvKey === '') {
            return null;
        }
        if (isset($key_map[$csvKey])) {
            $mapped = $key_map[$csvKey];
            if (isset($allowed[$mapped])) {
                return $mapped;
            }
        }
        if (isset($allowed[$csvKey])) {
            return $csvKey;
        }
        foreach ($allowed as $field => $_) {
            if (strtolower((string) $field) === $csvKey) {
                return $field;
            }
        }

        return null;
    }

    private function _student_import_prepare_row(array $row, array $allowed)
    {
        $skip = array(
            'permanent_province', 'permanent_district', 'permanent_local_level',
            'temporary_province', 'temporary_district', 'temporary_local_level',
            'ugc_p_province', 'ugc_p_district', 'ugc_p_local_level', 'ugc_p_combined_code',
            'ugc_t_province', 'ugc_t_district', 'ugc_t_local_level', 'ugc_t_combined_code',
            'dob_bs',
        );
        $key_map = array(
            'firstname_np'      => 'first_name_np',
            'firstname_'        => 'first_name_np',
            'middlename_np'     => 'middle_name_np',
            'middlename_'       => 'middle_name_np',
            'middlenam'         => 'middle_name_np',
            'middlenan'         => 'middle_name_np',
            'lastname_np'       => 'last_name_np',
            'lastname_'         => 'last_name_np',
            'admissionyearid'   => 'admissionYearId',
        );

        $out = array();
        foreach ($row as $k => $val) {
            $k = strtolower(trim((string) $k));
            if (in_array($k, $skip, true)) {
                continue;
            }
            $dbk = $this->_student_import_csv_column_to_db($k, $allowed, $key_map);
            if ($dbk === null) {
                continue;
            }
            $out[$dbk] = $this->encoding_lib->toUTF8(trim((string) $val));
        }

        $this->_student_import_apply_dates($out, $row, $allowed);

        if (empty($out['firstname'])) {
            return array('error' => sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('first_name')));
        }
        if (empty($out['dob'])) {
            return array('error' => sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('date_of_birth') . ' (B.S.)'));
        }
        if (empty($out['gender'])) {
            return array('error' => sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('gender')));
        }
        if (empty($out['guardian_name'])) {
            return array('error' => sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('guardian_name')));
        }
        if (empty($out['guardian_phone'])) {
            return array('error' => sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('guardian_phone')));
        }

        if (!empty($out['admission_date'])) {
            $out['admission_date'] = $this->customlib->dateFormatToYYYYMMDD($out['admission_date']);
        }

        $hemis_err = $this->_student_import_merge_hemis_row($out, $row, $allowed);
        if ($hemis_err !== null) {
            return array('error' => $hemis_err);
        }

        $this->_student_import_apply_legacy_address_ids($out, $allowed);

        if (isset($out['category_id']) && $out['category_id'] !== '') {
            $out['category_id'] = (int) $out['category_id'];
        }

        $ia = isset($out['is_active']) ? strtolower(trim((string) $out['is_active'])) : 'yes';
        $out['is_active'] = ($ia === 'no') ? 'no' : 'yes';

        $out = array_intersect_key($out, $allowed);
        unset($out['id']);

        return array('student_data' => $out);
    }

    /**
     * Map CSV dob_bs / dob columns to students.dob (B.S.) and students.dob_ad (A.D.).
     */
    private function _student_import_apply_dates(array &$out, array $row, array $allowed)
    {
        $dob_bs_raw = isset($row['dob_bs']) ? trim((string) $row['dob_bs']) : '';
        $dob_ad_raw = isset($row['dob']) ? trim((string) $row['dob']) : '';

        if ($dob_bs_raw === '' && isset($out['dob']) && $out['dob'] !== '') {
            $dob_bs_raw = $out['dob'];
            $dob_ad_raw = '';
        }

        if ($dob_bs_raw !== '' && isset($allowed['dob'])) {
            $norm_bs = $this->customlib->dateFormatToYYYYMMDD($dob_bs_raw);
            $out['dob'] = ($norm_bs !== null && $norm_bs !== '') ? $norm_bs : $dob_bs_raw;
        }
        if (isset($allowed['dob_bs']) && $dob_bs_raw !== '') {
            $out['dob_bs'] = $dob_bs_raw;
        }
        if ($dob_ad_raw !== '' && isset($allowed['dob_ad'])) {
            $norm_ad = $this->customlib->dateFormatToYYYYMMDD($dob_ad_raw);
            $out['dob_ad'] = ($norm_ad !== null && $norm_ad !== '') ? $norm_ad : $dob_ad_raw;
        }
    }

    /**
     * HEMIS / UGC field normalization for CSV import (parity with student create/edit).
     *
     * @return string|null Error message or null on success
     */
    private function _student_import_merge_hemis_row(array &$out, array $row, array $allowed)
    {
        $this->load->helper('hemis_student');
        $this->load->helper('hemis_sync_post_from_db');

        foreach (array('ugc_program_id', 'ugc_batch_id', 'ugc_fiscal_year_id', 'admissionYearId', 'complition_year', 'permanent_ward_no') as $intCol) {
            if (isset($out[$intCol]) && $out[$intCol] !== '') {
                $out[$intCol] = (int) $out[$intCol];
            }
        }

        if (isset($allowed['admissionYearId'])) {
            $aid = isset($out['admissionYearId']) ? (int) $out['admissionYearId'] : 0;
            if ($aid < 1) {
                $resolve_row = array_merge($row, $out);
                $resolved = hemis_resolve_admission_year_id($resolve_row);
                if ($resolved !== null && (int) $resolved > 0) {
                    $aid = (int) $resolved;
                    $out['admissionYearId'] = $aid;
                }
            }
        }

        if (!empty($out['ugc_batch_id']) && $this->db->table_exists('ugc_batches')) {
            $batch_row = $this->db->select('name, batch_nepali')
                ->from('ugc_batches')
                ->where('id', (int) $out['ugc_batch_id'])
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($batch_row)) {
                if (isset($allowed['batch_name']) && empty($out['batch_name'])) {
                    $out['batch_name'] = $batch_row['name'];
                }
                if (isset($allowed['batch_name_nepali']) && empty($out['batch_name_nepali'])) {
                    $out['batch_name_nepali'] = $batch_row['batch_nepali'];
                }
            }
            $bid = (int) $out['ugc_batch_id'];
            if (isset($allowed['admissionYearId']) && (empty($out['admissionYearId']) || (int) $out['admissionYearId'] < 1)) {
                $out['admissionYearId'] = $bid;
            }
            if (isset($allowed['batch_id']) && (empty($out['batch_id']) || (int) $out['batch_id'] < 1)) {
                $out['batch_id'] = $bid;
            }
            if (isset($allowed['admission_year'])) {
                $posted_ay = isset($out['admission_year']) ? $out['admission_year'] : (isset($row['admission_year']) ? $row['admission_year'] : '');
                $out['admission_year'] = $this->_resolve_admission_year_from_batch(
                    $bid,
                    isset($out['batch_name_nepali']) ? $out['batch_name_nepali'] : '',
                    $posted_ay,
                    ''
                );
            }
        }

        if (isset($allowed['ugc_fiscal_year_id'])) {
            $fy = isset($out['ugc_fiscal_year_id']) ? (int) $out['ugc_fiscal_year_id'] : 0;
            if ($fy < 1 && $this->db->table_exists('ugc_fiscal_years')) {
                $active = $this->db->select('id')
                    ->from('ugc_fiscal_years')
                    ->where('active_fiscal_year', 1)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row_array();
                if (!empty($active['id'])) {
                    $fy = (int) $active['id'];
                } else {
                    $latest = $this->db->select('id')
                        ->from('ugc_fiscal_years')
                        ->order_by('idx', 'DESC')
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row_array();
                    if (!empty($latest['id'])) {
                        $fy = (int) $latest['id'];
                    }
                }
            }
            if ($fy > 0) {
                $out['ugc_fiscal_year_id'] = $fy;
                if (isset($allowed['fiscal_year_id'])) {
                    $out['fiscal_year_id'] = $fy;
                }
            }
        }

        $this->_student_import_resolve_ugc_address($out, $row, 'p', $allowed);
        $this->_student_import_resolve_ugc_address($out, $row, 't', $allowed);

        foreach (array('has_scolorship', 'is_verified', 'is_mukta_kamaiya', 'is_from_martyr_family') as $flag) {
            if (!isset($out[$flag]) || !isset($allowed[$flag])) {
                continue;
            }
            $v = strtolower(trim((string) $out[$flag]));
            $out[$flag] = in_array($v, array('1', 'yes', 'true', 'y'), true) ? 1 : 0;
        }

        if (isset($allowed['ethnicity']) && empty($out['ethnicity'])) {
            $cast = isset($out['cast']) ? trim((string) $out['cast']) : '';
            if ($cast !== '') {
                $out['ethnicity'] = $cast;
            }
        }

        if (isset($allowed['ethnicity']) && empty($out['ethnicity'])) {
            return sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('ethnicity'));
        }
        if (isset($allowed['permanent_ward_no']) && empty($out['permanent_ward_no'])) {
            return sprintf($this->lang->line('student_import_missing_required'), $this->lang->line('permanent_ward_no'));
        }
        if (isset($allowed['admissionYearId']) && empty($out['admissionYearId'])) {
            return sprintf($this->lang->line('student_import_missing_required'), 'UGC Batch ID (HEMIS)');
        }
        if (isset($allowed['ugc_program_id'])) {
            $pid = isset($out['ugc_program_id']) ? (int) $out['ugc_program_id'] : 0;
            if ($pid < 1) {
                return sprintf($this->lang->line('student_import_missing_required'), 'UGC Program ID (HEMIS)');
            }
            if (!$this->_student_import_ugc_row_exists('ugc_programs', 'program_id', $pid)) {
                return sprintf($this->lang->line('student_import_invalid_ugc_id'), 'UGC Program', $pid);
            }
        }
        if (isset($allowed['ugc_batch_id'])) {
            $bid = isset($out['ugc_batch_id']) ? (int) $out['ugc_batch_id'] : 0;
            if ($bid < 1) {
                return sprintf($this->lang->line('student_import_missing_required'), 'UGC Batch ID (HEMIS)');
            }
            if (!$this->_student_import_ugc_row_exists('ugc_batches', 'id', $bid)) {
                return sprintf($this->lang->line('student_import_invalid_ugc_id'), 'UGC Batch', $bid);
            }
        }
        if (isset($allowed['ugc_fiscal_year_id'])) {
            $fid = isset($out['ugc_fiscal_year_id']) ? (int) $out['ugc_fiscal_year_id'] : 0;
            if ($fid < 1) {
                return sprintf($this->lang->line('student_import_missing_required'), 'UGC Fiscal Year ID (HEMIS)');
            }
            if (!$this->_student_import_ugc_row_exists('ugc_fiscal_years', 'id', $fid)) {
                return sprintf($this->lang->line('student_import_invalid_ugc_id'), 'UGC Fiscal Year', $fid);
            }
        }

        return null;
    }

    /**
     * Resolve UGC wire address labels + combined_code from CSV ugc_* columns only.
     */
    private function _student_import_resolve_ugc_address(array &$out, array $row, $which, array $allowed)
    {
        $which = ($which === 't') ? 't' : 'p';
        $prefix = 'ugc_' . $which . '_';

        $prov_col = $prefix . 'province';
        if (!isset($allowed[$prov_col])) {
            return;
        }

        $province = isset($row[$prov_col]) ? trim((string) $row[$prov_col]) : '';
        $district = isset($row[$prefix . 'district']) ? trim((string) $row[$prefix . 'district']) : '';
        $local_level = isset($row[$prefix . 'local_level']) ? trim((string) $row[$prefix . 'local_level']) : '';
        $combined_code = isset($row[$prefix . 'combined_code']) ? trim((string) $row[$prefix . 'combined_code']) : '';

        if ($combined_code !== '' && $this->db->table_exists('ugc_addresses')) {
            $addr = $this->db->select('province, district, local_level, combined_code')
                ->from('ugc_addresses')
                ->where('combined_code', $combined_code)
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($addr)) {
                $province = trim((string) $addr['province']);
                $district = trim((string) $addr['district']);
                $local_level = trim((string) $addr['local_level']);
            }
        } elseif ($district !== '' && $local_level !== '') {
            hemis_student_try_align_address_labels_with_ugc_addresses($this, $province, $district, $local_level, $combined_code);
            if ($combined_code === '') {
                $combined_code = hemis_student_lookup_unique_combined_code_best_effort($this, $province, $district, $local_level);
            }
        }

        hemis_student_normalize_ugc_address_wire_triple($province, $district, $local_level);

        if ($province !== '') {
            $out[$prov_col] = $province;
        }
        if ($district !== '') {
            $out[$prefix . 'district'] = $district;
        }
        if ($local_level !== '') {
            $out[$prefix . 'local_level'] = $local_level;
        }
        if ($combined_code !== '' && isset($allowed[$prefix . 'combined_code'])) {
            $out[$prefix . 'combined_code'] = $combined_code;
        }
    }

    private function _student_import_ugc_row_exists($table, $id_column, $id)
    {
        $id = (int) $id;
        if ($id < 1 || !$this->db->table_exists($table)) {
            return false;
        }

        return $this->db->where($id_column, $id)->count_all_results($table) > 0;
    }

    /**
     * Best-effort map UGC address labels to legacy states/districts/municipalities IDs (not required in CSV).
     */
    private function _student_import_apply_legacy_address_ids(array &$out, array $allowed)
    {
        $p_province = isset($out['ugc_p_province']) ? trim((string) $out['ugc_p_province']) : '';
        $p_district = isset($out['ugc_p_district']) ? trim((string) $out['ugc_p_district']) : '';
        $p_local = isset($out['ugc_p_local_level']) ? trim((string) $out['ugc_p_local_level']) : '';

        $pid = $this->_import_get_province_id($this->_import_normalize_local_province_name($p_province));
        $did = $this->_import_get_district_id($p_district, $pid);
        $lid = $this->_import_get_municipality_id($p_local, $did);

        if (isset($allowed['permanent_province'])) {
            $out['permanent_province'] = ($pid === null) ? 0 : (int) $pid;
        }
        if (isset($allowed['permanent_district'])) {
            $out['permanent_district'] = ($did === null) ? 0 : (int) $did;
        }
        if (isset($allowed['permanent_local_level'])) {
            $out['permanent_local_level'] = ($lid === null) ? 0 : (int) $lid;
        }

        $t_district = isset($out['ugc_t_district']) ? trim((string) $out['ugc_t_district']) : '';
        $t_local = isset($out['ugc_t_local_level']) ? trim((string) $out['ugc_t_local_level']) : '';
        $t_province = isset($out['ugc_t_province']) ? trim((string) $out['ugc_t_province']) : '';

        $is_same = 0;
        if ($t_district === '' && $t_local === '' && $t_province === '') {
            $is_same = 1;
            if (isset($allowed['ugc_t_province']) && $p_province !== '') {
                $out['ugc_t_province'] = $p_province;
            }
            if (isset($allowed['ugc_t_district']) && $p_district !== '') {
                $out['ugc_t_district'] = $p_district;
            }
            if (isset($allowed['ugc_t_local_level']) && $p_local !== '') {
                $out['ugc_t_local_level'] = $p_local;
            }
            if (isset($allowed['ugc_t_combined_code']) && !empty($out['ugc_p_combined_code'])) {
                $out['ugc_t_combined_code'] = $out['ugc_p_combined_code'];
            }
        }

        if ($is_same) {
            if (isset($allowed['temporary_province'])) {
                $out['temporary_province'] = isset($out['permanent_province']) ? (int) $out['permanent_province'] : 0;
            }
            if (isset($allowed['temporary_district'])) {
                $out['temporary_district'] = isset($out['permanent_district']) ? (int) $out['permanent_district'] : 0;
            }
            if (isset($allowed['temporary_local_level'])) {
                $out['temporary_local_level'] = isset($out['permanent_local_level']) ? (int) $out['permanent_local_level'] : 0;
            }
        } else {
            $tpid = $this->_import_get_province_id($this->_import_normalize_local_province_name($t_province));
            $tdid = $this->_import_get_district_id($t_district, $tpid);
            $tlid = $this->_import_get_municipality_id($t_local, $tdid);
            if (isset($allowed['temporary_province'])) {
                $out['temporary_province'] = ($tpid === null) ? 0 : (int) $tpid;
            }
            if (isset($allowed['temporary_district'])) {
                $out['temporary_district'] = ($tdid === null) ? 0 : (int) $tdid;
            }
            if (isset($allowed['temporary_local_level'])) {
                $out['temporary_local_level'] = ($tlid === null) ? 0 : (int) $tlid;
            }
        }

        if (isset($allowed['is_same_as_permanent'])) {
            $out['is_same_as_permanent'] = $is_same;
        }
    }

    /**
     * Strip " Province" suffix so local states.name lookup can match UGC wire labels.
     *
     * @param string $province
     * @return string
     */
    private function _import_normalize_local_province_name($province)
    {
        $p = trim((string) $province);
        if ($p === '') {
            return '';
        }
        if (preg_match('/^(.+?)\s+province$/iu', $p, $m)) {
            return trim($m[1]);
        }

        return $p;
    }

    private function _import_get_province_id($province_name)
    {
        if ($province_name === '') {
            return null;
        }
        $this->db->where('name', $province_name);
        $this->db->or_where('name_np', $province_name);
        $q = $this->db->get('states');
        return ($q->num_rows() > 0) ? (int) $q->row()->id : null;
    }

    private function _import_get_district_id($district_name, $province_id = null)
    {
        if ($district_name === '') {
            return null;
        }
        $this->db->where('name', $district_name);
        $this->db->or_where('name_np', $district_name);
        if ($province_id) {
            $this->db->where('province_id', (int) $province_id);
        }
        $q = $this->db->get('districts');
        return ($q->num_rows() > 0) ? (int) $q->row()->id : null;
    }

    private function _import_get_municipality_id($municipality_name, $district_id = null)
    {
        if ($municipality_name === '') {
            return null;
        }
        $this->db->where('name', $municipality_name);
        $this->db->or_where('name_np', $municipality_name);
        if ($district_id) {
            $this->db->where('district_id', (int) $district_id);
        }
        $q = $this->db->get('municipalities');
        return ($q->num_rows() > 0) ? (int) $q->row()->id : null;
    }

    /**
     * JSON: students for bulk Symbol / Registration / Roll modal (student/search).
     */
    public function list_for_bulk_ids()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => 0,
                'message' => 'Permission denied',
            )));
            return;
        }

        $section_id = (int) $this->input->post('section_id');
        $program_id = (int) $this->input->post('program_id');
        $class_id   = (int) $this->input->post('class_id');
        if ($section_id < 1 || $program_id < 1 || $class_id < 1) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => 0,
                'message' => 'Section, Program and Class are required.',
            )));
            return;
        }

        $students = $this->student_model->get_students_for_bulk_ids($section_id, $program_id, $class_id);
        $used     = $this->student_model->get_bulk_ids_used_values($section_id, $program_id, $class_id);

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'   => 1,
            'students' => $students,
            'used'     => $used,
        )));
    }

    /**
     * JSON: save bulk Symbol / Registration / Roll updates from student/search modal.
     */
    public function bulk_update_symbol_reg()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => 0,
                'message' => 'Permission denied',
            )));
            return;
        }

        $raw   = $this->input->post('items');
        $items = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($items)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => 0,
                'message' => 'Invalid payload.',
            )));
            return;
        }

        $result = $this->student_model->bulk_update_symbol_reg_roll($items);
        if (!empty($result['errors'])) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status'  => 0,
                'updated' => (int) $result['updated'],
                'errors'  => $result['errors'],
            )));
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'status'  => 1,
            'updated' => (int) $result['updated'],
        )));
    }

    /**
     * Download sample CSV for student import (linked from student/import).
     */
    public function exportformat()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')) {
            access_denied();
        }
        $this->load->helper('download');
        $data = $this->_student_import_build_sample_csv();
        force_download('import_student_sample.csv', $data);
    }

    /**
     * Bulk delete search + list. Form posts here with search_filter.
     */
    public function bulkdelete()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_delete') && !$this->rbac->hasPrivilege('student', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/bulkdelete');
        $data['title'] = $this->lang->line('bulk_delete');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['fields'] = $this->customfield_model->get_custom_fields('students', 1);

        if ($this->input->post('search') === 'search_filter') {
            $class_id   = (int) $this->input->post('class_id');
            $section_id = (int) $this->input->post('section_id');
            if ($class_id > 0 && $section_id > 0) {
                $data['resultlist'] = $this->student_model->getStudentByClassSectionID($class_id, $section_id);
            } else {
                $data['resultlist'] = array();
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('student/bulkdelete', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX bulk delete — must return JSON and include CSRF in form serialize.
     */
    public function ajax_delete()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_delete') && !$this->rbac->hasPrivilege('student', 'can_edit')) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 0, 'error' => array($this->lang->line('permission_denied')), 'message' => ''));
            return;
        }
        $students = $this->input->post('student');
        if (empty($students) || !is_array($students)) {
            $array = array(
                'status'  => 0,
                'error'   => array($this->lang->line('atleast_one_student_should_be_select')),
                'message' => '',
            );
        } else {
            $this->student_model->bulkdelete($students);
            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('delete_message'));
        }
        header('Content-Type: application/json');
        echo json_encode($array);
    }

    /**
     * Multi-class student: search by section/program/class and show students with their sessions.
     */
    public function multiclass()
    {
        if (!$this->rbac->hasPrivilege('multi_class_student', 'can_view') && !$this->rbac->hasPrivilege('multi_class_student', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/multiclass');
        $data['title'] = $this->lang->line('multi_class_student');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['students'] = array();

        if (strtoupper($this->input->method(true)) === 'POST') {
            $class_id   = (int) $this->input->post('class_id');
            $section_id = (int) $this->input->post('section_id');
            if ($class_id > 0 && $section_id > 0) {
                $data['students'] = $this->student_model->getStudentsWithSessionsForMulticlass($class_id, $section_id);
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('student/multiclass', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Save multi-class updates (AJAX). Expects student_id, row_count[], student_session_id_N, section_id_N, program_id_N, class_id_N.
     */
    public function savemulticlass()
    {
        if (!$this->rbac->hasPrivilege('multi_class_student', 'can_edit')) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 0, 'message' => $this->lang->line('permission_denied')));
            return;
        }
        $student_id = (int) $this->input->post('student_id');
        $row_count  = $this->input->post('row_count');
        if (!$student_id || empty($row_count) || !is_array($row_count)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 0, 'message' => $this->lang->line('error_occurred_please_try_again')));
            return;
        }
        $current_session = $this->setting_model->getCurrentSession();
        foreach ($row_count as $count) {
            $n = (int) $count;
            if ($n < 1) continue;
            $sess_id   = (int) $this->input->post('student_session_id_' . $n);
            $section_id = (int) $this->input->post('section_id_' . $n);
            $program_id = (int) $this->input->post('program_id_' . $n);
            $class_id   = (int) $this->input->post('class_id_' . $n);
            if ($class_id < 1) continue;
            if ($sess_id > 0) {
                $this->db->where('id', $sess_id);
                $this->db->update('student_session', array('class_id' => $class_id, 'section_id' => $section_id, 'program_id' => $program_id));
            } else {
                $this->student_model->add_student_session(array(
                    'student_id'  => $student_id,
                    'session_id'  => $current_session,
                    'class_id'    => $class_id,
                    'section_id'  => $section_id,
                    'program_id'  => $program_id,
                    'is_active'   => 'yes',
                ));
            }
        }
        header('Content-Type: application/json');
        echo json_encode(array('status' => 1, 'message' => $this->lang->line('success_message')));
    }

    /**
     * Disabled Students: single page at student/disablestudents.
     * GET = show criteria form. Search is done via AJAX and results shown in the same page table.
     */
    public function disablestudents()
    {
        if (!$this->rbac->hasPrivilege('disable_student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/disablestudents');
        $data['title'] = $this->lang->line('disable_student_list');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['disable_reason'] = $this->_get_disable_reason_keyed_by_id();
        $data['resultlist'] = array();

        $this->load->view('layout/header', $data);
        $this->load->view('student/disablestudents', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX: returns only the results HTML (table + details) for the disabled students page.
     * POST params: search (search_filter|search_full), and for filter: section_id, program_id, class_id; for full: search_text.
     */
    public function disablestudents_results_ajax()
    {
        if (!$this->rbac->hasPrivilege('disable_student', 'can_view')) {
            $this->output->set_status_header(403);
            return;
        }
        if (strtoupper($this->input->method(true)) !== 'POST') {
            $this->output->set_status_header(405);
            return;
        }

        $resultlist = array();
        $search_type = $this->input->post('search');
        if ($search_type === 'search_filter') {
            $section_id = (int) $this->input->post('section_id');
            $program_id = (int) $this->input->post('program_id');
            $class_id   = (int) $this->input->post('class_id');
            if ($class_id > 0 && $section_id > 0) {
                $resultlist = $this->student_model->disablestudentByClassSection($class_id, $section_id);
            }
        } elseif ($search_type === 'search_full') {
            $search_text = $this->input->post('search_text');
            if (!empty(trim($search_text))) {
                $resultlist = $this->student_model->disablestudentFullText(trim($search_text));
            }
        }

        $data = array(
            'resultlist'    => $resultlist,
            'disable_reason' => $this->_get_disable_reason_keyed_by_id(),
            'sch_setting'   => $this->sch_setting_detail,
        );
        $html = $this->load->view('student/disablestudents_results', $data, true);
        $this->output->set_content_type('text/html')->set_output($html);
    }

    /**
     * Return disable reasons keyed by id so view can use array_key_exists($reason_id, $disable_reason).
     */
    private function _get_disable_reason_keyed_by_id()
    {
        $list = $this->disable_reason_model->get();
        $keyed = array();
        if (!empty($list)) {
            foreach ($list as $row) {
                $keyed[(int) $row['id']] = $row;
            }
        }
        return $keyed;
    }

    /**
     * Legacy URL: redirect to the single disabled-students page.
     */
    public function disablestudentslist()
    {
        redirect('student/disablestudents');
    }

    /**
     * AJAX: student list for Class & Section report (reports/classsectionreport).
     * POST: cls_section_id (class_sections.id); optional program_id (ignored — row is scoped by cls_section_id).
     */
    public function getStudentByClassSection()
    {
        if (!$this->rbac->hasPrivilege('student_report', 'can_view')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array('page' => '<div class="alert alert-danger">' . $this->lang->line('access_denied') . '</div>'));
            return;
        }

        $cls_section_id = (int) $this->input->post('cls_section_id');
        if ($cls_section_id < 1) {
            $this->output->set_content_type('application/json');
            echo json_encode(array('page' => '<div class="alert alert-danger">' . $this->lang->line('error_occurred_please_try_again') . '</div>'));
            return;
        }

        $student_list = $this->student_model->getStudentBy_class_section_id($cls_section_id);
        $data['student_list'] = $student_list ? $student_list : array();
        $data['sch_setting']  = $this->sch_setting_detail;
        $data['fields']       = $this->customfield_model->get_custom_fields('students', 1);
        $page                 = $this->load->view('homework/_getStudentByClassSection', $data, true);
        $this->output->set_content_type('application/json');
        echo json_encode(array('page' => $page));
    }

    /**
     * Dashboard widget: graduated students list.
     */
    public function graduates()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view') && !$this->rbac->hasPrivilege('graduates_count_widget', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        $data['title']       = $this->lang->line('graduates');
        $data['resultlist']  = $this->student_model->getGraduatedStudents();
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['show_session'] = true;
        $this->load->view('layout/header', $data);
        $this->load->view('student/dashboard_student_list', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Dashboard widget: dropout / leave students list.
     */
    public function dropouts()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view') && !$this->rbac->hasPrivilege('dropouts_count_widget', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        $data['title']       = $this->lang->line('dropouts');
        $data['resultlist']  = $this->student_model->getDropoutStudents();
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['show_session'] = true;
        $this->load->view('layout/header', $data);
        $this->load->view('student/dashboard_student_list', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Dashboard widget: male students (current session, active enrolled).
     */
    public function male()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view') && !$this->rbac->hasPrivilege('gender_count_widget', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        $data['title']       = $this->lang->line('male') . ' ' . $this->lang->line('student');
        $data['resultlist']  = $this->student_model->getActiveStudentsByGender('male');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['show_session'] = false;
        $this->load->view('layout/header', $data);
        $this->load->view('student/dashboard_student_list', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Dashboard widget: female students (current session, active enrolled).
     */
    public function female()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view') && !$this->rbac->hasPrivilege('gender_count_widget', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        $data['title']       = $this->lang->line('female') . ' ' . $this->lang->line('student');
        $data['resultlist']  = $this->student_model->getActiveStudentsByGender('female');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['show_session'] = false;
        $this->load->view('layout/header', $data);
        $this->load->view('student/dashboard_student_list', $data);
        $this->load->view('layout/footer', $data);
    }
}
