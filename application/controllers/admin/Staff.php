<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staff extends Admin_Controller
{

    public $sch_setting_detail = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->config->load("payroll");
        $this->config->load("app-config");
        $this->load->library('Enc_lib');
        $this->load->library('mailsmsconf');
        $this->load->model("staff_model");
        $this->load->library('encoding_lib');
        $this->load->model("leaverequest_model");
        $this->load->model("state_model"); // Use state_model instead of province_model
        $this->contract_type      = $this->config->item('contracttype');
        $this->marital_status     = $this->config->item('marital_status');
        $this->staff_attendance   = $this->config->item('staffattendance');
        $this->payroll_status     = $this->config->item('payroll_status');
        $this->payment_mode       = $this->config->item('payment_mode');
        $this->status             = $this->config->item('status');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    protected function ensure_staff_hemis_columns()
    {
        // Add missing columns needed for HEMIS employee sync inputs.
        // Safe to call multiple times.
        if (!$this->db->table_exists('staff')) {
            return;
        }
        if (!$this->db->field_exists('citizenship_no', 'staff')) {
            $this->db->query("ALTER TABLE `staff` ADD COLUMN `citizenship_no` VARCHAR(64) NULL AFTER `nid_no`");
        }
        if (!$this->db->field_exists('graduated_from', 'staff')) {
            $this->_staff_safe_add_column('graduated_from', 'TEXT NULL');
        }
        if (!$this->db->field_exists('ugc_fiscal_year_id', 'staff')) {
            if ($this->db->field_exists('hemis_employee_id', 'staff')) {
                $this->db->query("ALTER TABLE `staff` ADD COLUMN `ugc_fiscal_year_id` INT NULL AFTER `hemis_employee_id`");
            } else {
                $this->db->query("ALTER TABLE `staff` ADD COLUMN `ugc_fiscal_year_id` INT NULL");
            }
        }
        if (!$this->db->field_exists('fiscal_year_id', 'staff')) {
            if ($this->db->field_exists('ugc_fiscal_year_id', 'staff')) {
                $this->db->query("ALTER TABLE `staff` ADD COLUMN `fiscal_year_id` INT NULL AFTER `ugc_fiscal_year_id`");
            } else {
                $this->db->query("ALTER TABLE `staff` ADD COLUMN `fiscal_year_id` INT NULL");
            }
        }
        $ugc_cols = array(
            'ugc_p_province'       => 'TEXT NULL',
            'ugc_p_district'       => 'TEXT NULL',
            'ugc_p_local_level'    => 'TEXT NULL',
            'ugc_p_combined_code'  => 'VARCHAR(20) NULL',
            'ugc_t_province'       => 'TEXT NULL',
            'ugc_t_district'       => 'TEXT NULL',
            'ugc_t_local_level'    => 'TEXT NULL',
            'ugc_t_combined_code'  => 'VARCHAR(20) NULL',
        );
        foreach ($ugc_cols as $col => $def) {
            if (!$this->db->field_exists($col, 'staff')) {
                $this->_staff_safe_add_column($col, $def);
            }
        }
    }

    /**
     * ALTER ADD COLUMN without fatal error when staff row is near MySQL size limit.
     */
    protected function _staff_safe_add_column($col, $definition)
    {
        $col = preg_replace('/[^a-z0-9_]/i', '', (string) $col);
        if ($col === '' || !$this->db->table_exists('staff') || $this->db->field_exists($col, 'staff')) {
            return false;
        }
        try {
            $this->db->query("ALTER TABLE `staff` ADD COLUMN `{$col}` {$definition}");
            return true;
        } catch (Throwable $e) {
            log_message('error', 'Staff ensure_staff_hemis_columns: could not add ' . $col . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Persist UGC/HEMIS address hidden fields from staff create/edit POST.
     */
    protected function merge_staff_ugc_address_post(array $data)
    {
        if (!$this->db->table_exists('staff')) {
            return $data;
        }
        $pairs = array(
            'ugc_p_province'      => 'ugc_p_province_name',
            'ugc_p_district'      => 'ugc_p_district_name',
            'ugc_p_local_level'   => 'ugc_p_local_level_name',
            'ugc_p_combined_code' => 'ugc_p_combined_code',
            'ugc_t_province'      => 'ugc_t_province_name',
            'ugc_t_district'      => 'ugc_t_district_name',
            'ugc_t_local_level'   => 'ugc_t_local_level_name',
            'ugc_t_combined_code' => 'ugc_t_combined_code',
        );
        foreach ($pairs as $col => $post_key) {
            if (!$this->db->field_exists($col, 'staff')) {
                continue;
            }
            $v = trim((string) $this->input->post($post_key));
            $data[$col] = ($v !== '') ? $v : null;
        }
        return $data;
    }

    /**
     * Map department dropdown (UGC ids) to ugc_department_id / ugc_section_id + staff.department.
     * Teaching vs non-teaching is derived from Employee Position (HEMIS), not a separate Staff Type field.
     */
    protected function merge_staff_hemis_org_unit_from_post(array $data)
    {
        $pid = isset($data['employee_position_id']) ? (int) $data['employee_position_id'] : (int) $this->input->post('employee_position_id');
        $sel = (int) $this->input->post('department');

        $this->load->model('ugc_metadata_model');
        $pos = ($pid > 0) ? $this->ugc_metadata_model->get_employee_position_by_id($pid) : null;
        $isTeaching = is_array($pos) ? $this->ugc_metadata_model->is_teaching_position_row($pos) : false;
        $data['non_teaching_type'] = $isTeaching ? 0 : 1;

        if ($sel < 1 && is_array($pos)) {
            $org = $this->ugc_metadata_model->parse_position_row_org_ids($pos);
            if ($isTeaching && $org['departmentId'] > 0) {
                $sel = $org['departmentId'];
            } elseif (!$isTeaching && $org['sectionId'] > 0) {
                $sel = $org['sectionId'];
            }
        }

        if ($sel < 1) {
            return $data;
        }

        $data['department'] = $sel;
        if ($this->db->field_exists('ugc_department_id', 'staff')) {
            $data['ugc_department_id'] = $isTeaching ? $sel : null;
        }
        if ($this->db->field_exists('ugc_section_id', 'staff')) {
            $data['ugc_section_id'] = $isTeaching ? null : $sel;
        }
        return $data;
    }

    /**
     * Merge repeatable HEMIS qualification rows from POST into staff row (scalars + JSON arrays).
     */
    protected function merge_staff_hemis_qualification_from_post(array $data)
    {
        $this->load->helper('staff_hemis_qualification');

        $graduated = staff_hemis_filter_post_array($this->input->post('graduated_from'));
        $levels = staff_hemis_filter_post_array($this->input->post('level_name'));
        $programs = staff_hemis_filter_post_array($this->input->post('program_name'));
        $boards = staff_hemis_filter_post_array($this->input->post('previous_board_university_college'));
        $regs = staff_hemis_filter_post_array($this->input->post('previous_registration_no'));
        $faculties = staff_hemis_filter_post_array($this->input->post('faculty_name'));
        $enrolled = staff_hemis_filter_post_array($this->input->post('enrolled_year'));
        $passed = staff_hemis_filter_post_array($this->input->post('passed_year'));

        $data['graduated_from'] = !empty($graduated) ? $graduated[0] : '';
        $data['level_name'] = !empty($levels) ? $levels[0] : '';
        $data['program_name'] = !empty($programs) ? $programs[0] : '';
        $data['faculty_name'] = !empty($faculties) ? $faculties[0] : '';
        $data['teaching_faculty_name'] = trim((string) $this->input->post('teaching_faculty_name'));
        $data['enrolled_year'] = !empty($enrolled) ? $enrolled[0] : '';
        $data['passed_year'] = !empty($passed) ? $passed[0] : '';

        $data['previous_board_university_college'] = !empty($boards) ? json_encode($boards) : '[]';
        $data['previous_registration_no'] = !empty($regs) ? json_encode($regs) : '[]';
        $data['previous_level_of_study'] = !empty($levels) ? json_encode($levels) : '[]';
        $data['previous_institution_name'] = !empty($graduated) ? json_encode($graduated) : '[]';

        return $data;
    }

    /**
     * @param string $ignored
     * @return bool
     */
    public function _graduated_from_required($ignored)
    {
        $this->load->helper('staff_hemis_qualification');
        if (staff_hemis_first_non_empty_scalar($this->input->post('graduated_from')) === '') {
            $this->form_validation->set_message('_graduated_from_required', 'Graduated From (HEMIS) is required on the first qualification row.');
            return false;
        }
        return true;
    }

    /**
     * Keep staff.hemis_employee_id and ugc_sync_states in sync after HEMIS employee API calls.
     *
     * @param int   $staff_id
     * @param array $hemisOut
     * @return void
     */
    protected function _record_staff_ugc_employee_sync($staff_id, array $hemisOut)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id < 1) {
            return;
        }

        $this->staff_model->apply_hemis_employee_sync_result($staff_id, $hemisOut);

        $staff = $this->staff_model->get($staff_id);
        $hid = (is_array($staff) && !empty($staff['hemis_employee_id'])) ? (int) $staff['hemis_employee_id'] : 0;
        if ($hid > 0) {
            if (empty($hemisOut['hemis_employee_id'])) {
                $hemisOut['hemis_employee_id'] = $hid;
            }
            $hemisOut['synced'] = true;
        }

        $http = null;
        if (isset($hemisOut['http_code'])) {
            $http = (int) $hemisOut['http_code'];
        } elseif (isset($hemisOut['status_code'])) {
            $http = (int) $hemisOut['status_code'];
        }

        $synced = !empty($hemisOut['synced']) || $hid > 0;
        $blocked = isset($hemisOut['sync_pending']) && $hemisOut['sync_pending'] === 'UGC_DEV_BLOCKER';
        $status = $synced ? 'synced' : ($blocked ? 'blocked' : (!empty($hemisOut['error']) ? 'error' : 'pending'));

        $this->load->model('ugc_sync_state_model');
        $this->ugc_sync_state_model->upsert('Employee', $staff_id, array(
            'status'         => $status,
            'ugc_record_id'  => $hid > 0 ? (string) $hid : null,
            'last_synced_at' => $synced ? date('Y-m-d H:i:s') : null,
            'last_http_code' => $http,
            'last_error'     => ($status === 'error' && !empty($hemisOut['error']))
                ? substr((string) $hemisOut['error'], 0, 500)
                : null,
        ));
    }

    /**
     * Resolve local staff_designation id from HEMIS post name (Employee Position).
     */
    protected function resolve_staff_designation_id_from_post_name($post_name)
    {
        $post_name = trim((string) $post_name);
        if ($post_name === '') {
            return null;
        }
        $designation_id = $this->staff_model->getDesignationIdByName($post_name);
        if (!empty($designation_id)) {
            return $designation_id;
        }
        return $this->staff_model->addDesignation(array(
            'designation' => $post_name,
            'is_active'   => 1,
        ));
    }

    /**
     * Whether permanent and temporary address are the same (checkbox or UGC hidden fields).
     */
    protected function _staff_is_same_as_permanent_from_post()
    {
        if ($this->input->post('same_as_permanent') == '1' || $this->input->post('is_same_as_permanent') == '1') {
            return 1;
        }
        return 0;
    }

    /**
     * Resolve UGC combined_code from province / district / local level labels.
     */
    protected function _staff_resolve_ugc_combined_code($province, $district, $localLevel)
    {
        $province = trim((string) $province);
        $district = trim((string) $district);
        $localLevel = trim((string) $localLevel);
        if ($province === '' || $district === '' || $localLevel === '' || !$this->db->table_exists('ugc_addresses')) {
            return null;
        }
        $row = $this->db->select('combined_code')
            ->from('ugc_addresses')
            ->where('province', $province)
            ->where('district', $district)
            ->where('local_level', $localLevel)
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($row['combined_code']) ? (string) $row['combined_code'] : null;
    }

    /**
     * Build ugc_p_* / ugc_t_* array from CSV import row (legacy column names hold UGC text labels).
     */
    protected function _staff_ugc_address_from_import_row(array $row)
    {
        $enc = function ($key) use ($row) {
            return isset($row[$key]) && trim((string) $row[$key]) !== ''
                ? trim((string) $this->encoding_lib->toUTF8($row[$key]))
                : '';
        };
        $pProv = $enc('permanent_province');
        $pDist = $enc('permanent_district');
        $pLoc  = $enc('permanent_local_level');
        $tProv = $enc('temporary_province');
        $tDist = $enc('temporary_district');
        $tLoc  = $enc('temporary_local_level');

        $is_same = 0;
        if ($tProv !== '' && $tProv === $pProv && $tDist === $pDist && $tLoc === $pLoc) {
            $is_same = 1;
        }

        $out = array(
            'ugc_p_province'      => $pProv !== '' ? $pProv : null,
            'ugc_p_district'      => $pDist !== '' ? $pDist : null,
            'ugc_p_local_level'   => $pLoc !== '' ? $pLoc : null,
            'ugc_p_combined_code' => $this->_staff_resolve_ugc_combined_code($pProv, $pDist, $pLoc),
            'is_same_as_permanent' => $is_same,
        );

        if ($is_same) {
            $out['ugc_t_province']      = $out['ugc_p_province'];
            $out['ugc_t_district']      = $out['ugc_p_district'];
            $out['ugc_t_local_level']   = $out['ugc_p_local_level'];
            $out['ugc_t_combined_code'] = $out['ugc_p_combined_code'];
        } else {
            $out['ugc_t_province']      = $tProv !== '' ? $tProv : null;
            $out['ugc_t_district']      = $tDist !== '' ? $tDist : null;
            $out['ugc_t_local_level']   = $tLoc !== '' ? $tLoc : null;
            $out['ugc_t_combined_code'] = $this->_staff_resolve_ugc_combined_code($tProv, $tDist, $tLoc);
        }

        return $out;
    }

    /**
     * GET admin/staff/ugc_addresses — official UGC address list (same source as students).
     */
    public function ugc_addresses()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_view') && !$this->rbac->hasPrivilege('staff', 'can_add') && !$this->rbac->hasPrivilege('staff', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        $live = (int) $this->input->get('live') === 1;
        $this->load->model('ugc_metadata_model');
        $r = null;

        $cached = $this->ugc_metadata_model->get_addresses_cached();
        if (!empty($cached) && !$live) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $cached, 'source' => 'cache')));
            return;
        }

        if ($live || empty($cached)) {
            $this->load->library('hemis_client');
            $r = $this->hemis_client->hemis_get_address_all();
            if (!empty($r['ok']) && is_array($r['data']) && !empty($r['data'])) {
                $this->ugc_metadata_model->upsert_addresses($r['data']);
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 1,
                    'data'   => $r['data'],
                    'source' => 'ugc',
                    'mode'   => isset($r['mode']) ? $r['mode'] : null,
                )));
                return;
            }
        }

        if (!empty($cached)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'data' => $cached, 'source' => 'cache')));
            return;
        }

        $msg = 'UGC address list is empty. Refresh metadata from Super Admin or check HEMIS API settings.';
        if (!empty($r['error'])) {
            $msg = $r['error'];
        }
        $this->output->set_status_header(empty($cached) ? 502 : 200);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 0, 'message' => $msg, 'data' => array())));
    }

    /**
     * Map HEMIS EmployeePositions.type → staff.employee_type (POST employeeType).
     */
    protected function hemis_employee_type_from_position_type($type)
    {
        $type = trim((string) $type);
        if ($type === '') {
            return '';
        }
        if (strcasecmp($type, 'Teaching') === 0) {
            return 'teaching';
        }
        if (in_array($type, array('Administrative', 'Technical', 'Other'), true)) {
            return $type;
        }
        return $type;
    }

    /**
     * When employee_position_id is set, fill post_name, employee_type, position (Teaching/Nonteaching).
     */
    protected function merge_hemis_employee_position_fields(array $data)
    {
        $pid = isset($data['employee_position_id']) ? (int) $data['employee_position_id'] : 0;
        if ($pid < 1) {
            if ($pid === 0) {
                $data['employee_position_id'] = null;
            }
            return $data;
        }

        $data['employee_position_id'] = $pid;

        $this->load->model('ugc_metadata_model');
        $pos = $this->ugc_metadata_model->get_employee_position_by_id($pid);
        if (!is_array($pos) || empty($pos)) {
            return $data;
        }

        $postName = isset($pos['postName']) ? trim((string) $pos['postName']) : '';
        if ($postName === '' && isset($pos['post_name'])) {
            $postName = trim((string) $pos['post_name']);
        }
        if ($postName !== '') {
            $data['post_name'] = $postName;
        }

        $type = isset($pos['type']) ? trim((string) $pos['type']) : '';
        if ($type !== '') {
            $data['employee_type'] = $this->hemis_employee_type_from_position_type($type);
        }

        $category = isset($pos['category']) ? trim((string) $pos['category']) : '';
        if ($category !== '') {
            $data['position'] = $category;
        }

        if (empty(trim((string) ($data['position_type'] ?? '')))) {
            $data['position_type'] = 'Other';
        }

        $data['non_teaching_type'] = $this->ugc_metadata_model->is_teaching_position_row($pos) ? 0 : 1;

        $org = $this->ugc_metadata_model->parse_position_row_org_ids($pos);
        if ($org['departmentId'] > 0 && $this->db->field_exists('ugc_department_id', 'staff')) {
            $data['ugc_department_id'] = $org['departmentId'];
            $data['department'] = $org['departmentId'];
        }
        if ($org['sectionId'] > 0 && $this->db->field_exists('ugc_section_id', 'staff')) {
            $data['ugc_section_id'] = $org['sectionId'];
            if (empty($data['department'])) {
                $data['department'] = $org['sectionId'];
            }
        }

        return $data;
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_view')) {
            access_denied();
        }

        $data['title']  = $this->lang->line('staff_list');
        $data['fields'] = $this->customfield_model->get_custom_fields('staff', 1);
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff');
        $search      = $this->input->post('search');
        $search_text = trim((string)$this->input->post('search_text', true));
        
        // default: active staff list
        $data['resultlist'] = $this->staff_model->searchFullText('', 1);
        
        $staffRole       = $this->staff_model->getStaffRole();
        $data['role']    = $staffRole;
        $data['role_id'] = '';
        
        if ($search === 'search_filter') {
            $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
            if ($this->form_validation->run() == false) {
                $data['resultlist'] = array();
            } else {
                $data['searchby']    = 'filter';
                $role                = (int)$this->input->post('role');
                $data['employee_id'] = $this->input->post('empid');
                $data['role_id']     = $role;
                $data['search_text'] = $search_text;
                $data['resultlist']  = $this->staff_model->getEmployee($role, 1);
            }
        } elseif ($search === 'search_full') {
            $data['searchby']    = 'text';
            $data['search_text'] = $search_text;
            $data['resultlist']  = $this->staff_model->searchFullText($search_text, 1);
            $data['title']       = $this->lang->line('search_details') . ': ' . $search_text;
        }

        // UGC Sync state badges (Employee module)
        $this->load->model('ugc_sync_state_model');
        $ids = array();
        if (!empty($data['resultlist']) && is_array($data['resultlist'])) {
            foreach ($data['resultlist'] as $r) {
                if (is_array($r) && isset($r['id'])) {
                    $ids[] = (int) $r['id'];
                }
            }
        }
        $data['ugc_employee_sync_map'] = $this->ugc_sync_state_model->map_states('Employee', $ids);

        $this->load->view('layout/header');
        $this->load->view('admin/staff/staffsearch', $data);
        $this->load->view('layout/footer');
    }

    public function disablestafflist()
    {
        if (!$this->rbac->hasPrivilege('disable_staff', 'can_view')) {
            access_denied();
        }

        if (isset($_POST['role']) && $_POST['role'] != '') {
            $data['search_role'] = $_POST['role'];
        } else {
            $data['search_role'] = "";
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff/disablestafflist');
        $data['title'] = 'Staff Search';
        $staffRole     = $this->staff_model->getStaffRole();

        $data["role"]       = $staffRole;
        $search      = $this->input->post('search');
        $search_text = trim((string)$this->input->post('search_text', true));
        
        // default: disabled staff list
        $data['resultlist'] = $this->staff_model->searchFullText('', 0);
        
        if ($search === 'search_filter') {
            $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
            if ($this->form_validation->run() == false) {
                $data['resultlist'] = array();
            } else {
                $data['searchby']    = 'filter';
                $role                = (int)$this->input->post('role');
                $data['employee_id'] = $this->input->post('empid');
                $data['search_text'] = $search_text;
                $data['resultlist']  = $this->staff_model->getEmployee($role, 0);
            }
        } elseif ($search === 'search_full') {
            $data['searchby']    = 'text';
            $data['search_text'] = $search_text;
            $data['resultlist']  = $this->staff_model->searchFullText($search_text, 0);
            $data['title']       = 'Search Details: ' . $search_text;
                
        }

        // UGC Sync state badges (Employee module)
        $this->load->model('ugc_sync_state_model');
        $ids = array();
        if (!empty($data['resultlist']) && is_array($data['resultlist'])) {
            foreach ($data['resultlist'] as $r) {
                if (is_array($r) && isset($r['id'])) {
                    $ids[] = (int) $r['id'];
                }
            }
        }
        $data['ugc_employee_sync_map'] = $this->ugc_sync_state_model->map_states('Employee', $ids);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/disablestaff', $data);
        $this->load->view('layout/footer', $data);
    }

    public function profile($id)
    {
        $data['enable_disable'] = 1;
        if ($this->customlib->getStaffID() == $id) {
            $data['enable_disable'] = 0;
        } else if (!$this->rbac->hasPrivilege('staff', 'can_view')) {
            access_denied();
        }
    
        $this->load->model("staffattendancemodel");
        $this->load->model("setting_model");
        $data["id"]      = $id;
        $data['title']   = 'Staff Details';
        $staff_info      = $this->staff_model->getProfile($id);
       
        $userdata        = $this->customlib->getUserData();
        $userid          = $userdata['id'];
        $timeline_status = '';
        if ($userid == $id) {
            $timeline_status = 'yes';
        }
        $timeline_list         = $this->timeline_model->getStaffTimeline($id, $timeline_status);
        $data["timeline_list"] = $timeline_list;
        $staff_payroll         = $this->staff_model->getStaffPayroll($id);
        $staff_leaves          = $this->leaverequest_model->staff_leave_request($id);
    
        $alloted_leavetype           = $this->staff_model->allotedLeaveType($id);
        $data['sch_setting']         = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $this->load->model("payroll_model");
        $salary = $this->payroll_model->getSalaryDetails($id);
    
        $data['currency_symbol'] = $this->customlib->getSchoolCurrencyFormat();
        
        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;
    
        $i            = 0;
        $leaveDetail  = array();
        $count_leaves = array(); // <-- added: avoid undefined variable notice
        foreach ($alloted_leavetype as $key => $value) {
            $count_leaves[]                   = $this->leaverequest_model->countLeavesData($id, $value["leave_type_id"]);
            $leaveDetail[$i]['type']          = $value["type"];
            $leaveDetail[$i]['alloted_leave'] = $value["alloted_leave"];
            $leaveDetail[$i]['approve_leave'] = $count_leaves[$i]['approve_leave'];
            $i++;
        }
        $data["leavedetails"]  = $leaveDetail;
        $data["staff_leaves"]  = $staff_leaves;
        $data['staff_doc_id']  = $id;
        $data['staff']         = $staff_info;
        $data['staff_payroll'] = $staff_payroll;
        $data['salary']        = $salary;

        // UGC sync state for this staff (Employee module)
        $this->load->model('ugc_sync_state_model');
        $data['ugc_employee_sync_state'] = $this->ugc_sync_state_model->get_state('Employee', (int) $id);
    
        // Keep Nepali month/year lists for the UI
        $data["monthlist"] = $this->customlib->getNepaliMonthDropdown();
        $data['yearlist']  = $this->customlib->getNepaliYearDropdown();
    
        $session_current       = $this->setting_model->getCurrentSessionName();
        $startMonth            = $this->setting_model->getStartMonth();
        $centenary             = substr($session_current, 0, 2); //2017-18 to 2017
        $year_first_substring  = substr($session_current, 2, 2); //2017-18 to 2017
        $year_second_substring = substr($session_current, 5, 2); //2017-18 to 18
        $month_number          = date("m", strtotime($startMonth));
        $data['rate_canview']  = 0;
    
        if ($id != '1') {
            $staff_rating = $this->staff_model->staff_ratingById($id);
    
            if ($staff_rating['total'] >= 3) {
                $data['rate'] = ($staff_rating['rate'] / $staff_rating['total']);
                $data['rate_canview'] = 1;
            }
            $data['reviews'] = $staff_rating['total'];
        }
    
        $data['reviews_comment'] = $this->staff_model->staff_ratingById($id);
    
        $year = date("Y");
    
        $staff_list              = $this->staff_model->user_reviewlist($id);
        $data['user_reviewlist'] = $staff_list;
    
        $attendence_count = array();
        $attendencetypes  = $this->attendencetype_model->getStaffAttendanceType();
        foreach ($attendencetypes as $att_key => $att_value) {
            $attendence_count[$att_value['type']] = array();
        }
    
        // --- FIX: drive the attendance loop with ENGLISH months so strtotime() works
        $monthlist = $this->customlib->getMonthDropdown(); // e.g., ["January"=>"January", ...]
        $date_array = array(); // <-- init
        $res        = array(); // <-- init
    
        foreach ($monthlist as $key => $value) {
            // $key like "January", "February", ...
            $datemonth       = date("m", strtotime($key));
            $date_each_month = date('Y-' . $datemonth . '-01');
    
            $date_start = 1;
            $date_end   = (int)date('t', strtotime($date_each_month));
    
            for ($n = $date_start; $n <= $date_end; $n++) {
                $att_dates        = $year . "-" . $datemonth . "-" . sprintf("%02d", $n);
                $date_array[]     = $att_dates;
                $staff_attendence = $this->staffattendancemodel->searchStaffattendance($att_dates, $id, false);
    
                if (!empty($staff_attendence)) {
                    if ($staff_attendence['att_type'] != "") {
                        $attendence_count[$staff_attendence['att_type']][] = 1;
                    }
                } else {
                    // no attendance record for this day
                }
                $res[$att_dates] = $staff_attendence;
            }
        }
    
        $session       = $this->setting_model->getCurrentSessionName();
        $session_start = explode("-", $session);
        $start_year    = $session_start[0];
        $date          = $start_year . "-" . $startMonth;
        $newdate       = date("Y-m-d", strtotime($date . "+1 month"));
    
        $data["countAttendance"]  = $attendence_count;
        $data["resultlist"]       = $res;
        $data["attendence_array"] = range(1, 31);
        $data["date_array"]       = $date_array;
        $data["payroll_status"]   = $this->payroll_status;
        $data["payment_mode"]     = $this->payment_mode;
        $data["contract_type"]    = $this->contract_type;
        $data["status"]           = $this->status;
        $roles                    = $this->role_model->get();
        $data["roles"]            = $roles;
        $stafflist                = $this->staff_model->get();
        $data['stafflist']        = $stafflist;
    
        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffprofile', $data);
        $this->load->view('layout/footer', $data);
    }
    

    public function countAttendance($year, $emp)
    {

        $record = array();

        foreach ($this->staff_attendance as $att_key => $att_value) {

            $s           = $this->staff_model->count_attendance($year, $emp, $att_value);
            $r[$att_key] = $s;
        }

        $record[$year] = $r;

        return $record;
    }

    public function getSession()
    {
        $session             = $this->session_model->getAllSession();
        $data                = array();
        $session_array       = $this->session->has_userdata('session_array');
        $data['sessionData'] = array('session_id' => 0);
        if ($session_array) {
            $data['sessionData'] = $this->session->userdata('session_array');
        } else {
            $setting             = $this->setting_model->get();
            $data['sessionData'] = array('session_id' => $setting[0]['session_id']);
        }
        $data['sessionList'] = $session;

        return $data;
    }

    public function getSessionMonthDropdown()
    {
        $startMonth = $this->setting_model->getStartMonth();
        $array      = array();
        for ($m = $startMonth; $m <= $startMonth + 11; $m++) {
            $month         = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
            $array[$month] = $month;
        }
        return $array;
    }

    public function download($staff_id, $doc)
    {
        $stafflist = $this->staff_model->getProfile($staff_id);
        $this->media_storage->filedownload($stafflist[$doc], "./uploads/staff_documents/$staff_id");
    }

    public function doc_delete($id, $doc)
    {
        $this->staff_model->doc_delete($id, $doc);
        $this->session->set_flashdata('msg', '<i class="fa fa-check-square-o" aria-hidden="true"></i>' . $this->lang->line('delete_message') . '');
        redirect('admin/staff/profile/' . $id);
    }

    public function ajax_attendance()
    {
        $this->load->model("staffattendancemodel");
        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;

        $id           = $this->input->post("id");
        $year         = $this->input->post("year");
        $data["year"] = $year;
        if (!empty($year)) {

            $data["monthlist"] = $this->customlib->getNepaliMonthDropdown();
            $data['yearlist']  = $this->customlib->getNepaliYearDropdown();
            $session_current   = $this->setting_model->getCurrentSessionName();
            $startMonth        = $this->setting_model->getStartMonth();

            foreach ($data["monthlist"] as $key => $value) {
                $datemonth       = date("m", strtotime($key));
                $date_each_month = date('Y-' . $datemonth . '-01');
                $date_end        = date('t', strtotime($date_each_month));
                for ($n = 1; $n <= $date_end; $n++) {
                    $att_date           = sprintf("%02d", $n);
                    $attendence_array[] = $att_date;
                    $datemonth          = date("m", strtotime($key));
                    $att_dates          = $year . "-" . $datemonth . "-" . sprintf("%02d", $n);

                    $date_array[]    = $att_dates;
                    $res[$att_dates] = $this->staffattendancemodel->searchStaffattendance($att_dates, $id);
                }
            }
            $date    = $year . "-" . $startMonth;
            $newdate = date("Y-m-d", strtotime($date . "+1 month"));

            $countAttendance = $this->countAttendance($year, $id);

            $data["countAttendance"] = $countAttendance;

            $data["id"]               = $id;
            $data["resultlist"]       = $res;
            $data["attendence_array"] = $attendence_array;
            $data["date_array"]       = $date_array;

            $page = $this->load->view("admin/staff/ajaxattendance", $data, true);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(array(
                    'status'          => 1,
                    'countAttendance' => $countAttendance[$year],
                    'page'            => $page,
                )));
        }
    }

    public function getDesignationsByType() {
        $staff_type = $this->input->post('staff_type');
        $designations = $this->staff_model->getDesignationsByType($staff_type);
        echo json_encode($designations);
    }

    public function create()
    {
        $this->ensure_staff_hemis_columns();
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staff');
       
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
       
        // Load email helper for staff notifications
        $this->load->library('email_service');
       
        // Fetch other data for dropdowns and fields
        $roles = $this->role_model->get();
        $data["roles"] = $roles;
        $genderList = $this->customlib->getGender();
        $data['genderList'] = $genderList;
        $payscaleList = $this->staff_model->getPayroll();
        $leavetypeList = $this->staff_model->getLeaveType();
        $data["leavetypeList"] = $leavetypeList;
        $data["payscaleList"] = $payscaleList;
        $designation = $this->staff_model->getStaffDesignation();
        $data["designation"] = $designation;
        $department = $this->staff_model->getDepartment();
        $data["department"] = $department;
        $this->load->model('ugc_metadata_model');
        $this->ugc_metadata_model->ensure_tables();
        $data['ugc_departments'] = $this->ugc_metadata_model->get_departments_cached();
        $data['ugc_sections'] = $this->ugc_metadata_model->get_sections_cached();
        $marital_status = $this->marital_status;
        $data["marital_status"] = $marital_status;
        $data['title'] = 'Add Staff';
        $data["contract_type"] = $this->contract_type;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        
        $positions = $this->ugc_metadata_model->get_employee_positions_for_staff_form();
        $data['ugc_employee_positions'] = is_array($positions) ? $positions : array();
        if (empty($data['ugc_employee_positions'])) {
            $seeded = $this->ugc_metadata_model->seed_employee_positions_if_empty();
            if ($seeded > 0) {
                $positions = $this->ugc_metadata_model->get_employee_positions_for_staff_form();
                $data['ugc_employee_positions'] = is_array($positions) ? $positions : array();
            }
        }
       
        // Get custom fields for the staff
        $custom_fields = $this->customfield_model->getByBelong('staff');
        $data['custom_fields'] = $custom_fields;
       
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation']) {
                $custom_fields_id = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[staff][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }
   
        // Set form validation rules
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('firstnameNp', $this->lang->line('first_name_np'), 'trim|xss_clean');
        $this->form_validation->set_rules('middlenameNp', $this->lang->line('middle_name_np'), 'trim|xss_clean');
        $this->form_validation->set_rules('lastnameNp', $this->lang->line('last_name_np'), 'trim|xss_clean');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob_bs', $this->lang->line('date_of_birth_bs'), 'trim|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', $this->lang->line('email'), array('required', 'valid_email', array('check_exists', array($this->staff_model, 'valid_email_id'))));
        $this->form_validation->set_rules('permanent_ward_no', $this->lang->line('ward'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('department', $this->lang->line('department'), 'trim|callback__ugc_dept_or_section_required');
        $this->form_validation->set_rules('contact_no', $this->lang->line('phone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('religion', $this->lang->line('religion'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('salutation', $this->lang->line('salutation'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('employee_position_id', 'Employee Position (HEMIS)', 'trim|required|integer|greater_than[0]');
        $this->form_validation->set_rules('graduated_from', 'Graduated From (HEMIS)', 'callback__graduated_from_required');
        $this->form_validation->set_rules('other_document_file', 'Additional Documents', 'callback_handle_additional_upload');


        $this->form_validation->set_rules('citizenship[]', $this->lang->line('citizenship'), 'callback_validate_file_upload[citizenship]');
        $this->form_validation->set_rules('nationalId[]', $this->lang->line('national_id'), 'callback_validate_file_upload[nationalId]');
       
        if (!$this->sch_setting_detail->staffid_auto_insert) {
            $this->form_validation->set_rules('employee_id', $this->lang->line('staff_id'), 'callback_username_check');
        }
       
        if ($this->form_validation->run() == true) {
            try {
                $this->db->trans_start(); // Start transaction for all database operations
               
                // Generate random password
                $password = $this->role->get_random_password($chars_min = 8, $chars_max = 12, $use_upper_case = true, $include_numbers = true, $include_special_chars = false);
               
                $plain_password = $password;
                   
                // Prepare staff data from form submission
                $employee_id = $this->input->post('employee_id');
               
                 // Designation follows HEMIS Post Name (Employee Position)
            $posted_designation_name = trim((string) $this->input->post('post_name'));
            if ($posted_designation_name === '') {
                $posted_designation_name = trim((string) $this->input->post('designation'));
            }
            $designation_id = null;
            
            if (!empty($posted_designation_name)) {
                $designation_id = $this->resolve_staff_designation_id_from_post_name($posted_designation_name);
            }
            
            log_message('debug', 'Create Method - Posted Designation Name: ' . $posted_designation_name . ' | Final Designation ID: ' . ($designation_id ?? 'NULL'));

            if (empty($employee_id)) {
                $employee_id = $this->staff_model->generate_employee_id();
            }
               
                // Format date values
              
                $dob = $this->input->post('dob');
                $dob = date('Y-m-d', strtotime($dob));

             
               
                $dob_bs = $this->input->post('dob_bs');
                $date_of_joining = $this->input->post('date_of_joining');
                if (empty($date_of_joining)) {
                    $date_of_joining = null;
                }
               
                // is_same_as_permanent from checkbox / hidden field
                $is_same_as_permanent = $this->_staff_is_same_as_permanent_from_post();
               
                // Main staff data array
                $staff_data = array(
                    'employee_id' => $employee_id,
                    'lang_id' => 1,
                    'currency_id' => 0,
                    'user_id' => (int) $this->input->post('role'),
                    'department' => $this->input->post('department'),
                     'designation' => $designation_id, // Use the processed designation ID
                    'qualification' => $this->input->post('qualification') ? $this->input->post('qualification') : '',
                    'work_exp' => $this->input->post('work_exp') ? $this->input->post('work_exp') : '',
                    'name' => $this->input->post('name'),
                    'firstnameNp' => $this->input->post('firstnameNp') !== null && $this->input->post('firstnameNp') !== ''
                        ? $this->input->post('firstnameNp')
                        : ($this->input->post('first_name_np') !== null && $this->input->post('first_name_np') !== '' ? $this->input->post('first_name_np') : ''),
                    'middle_name' => $this->input->post('middle_name') ? $this->input->post('middle_name') : '',
                    'middlenameNp' => $this->input->post('middlenameNp') !== null && $this->input->post('middlenameNp') !== ''
                        ? $this->input->post('middlenameNp')
                        : ($this->input->post('middle_name_np') !== null && $this->input->post('middle_name_np') !== '' ? $this->input->post('middle_name_np') : ''),
                    'surname' => $this->input->post('surname') ? $this->input->post('surname') : '',
                    'lastnameNp' => $this->input->post('lastnameNp') !== null && $this->input->post('lastnameNp') !== ''
                        ? $this->input->post('lastnameNp')
                        : ($this->input->post('last_name_np') !== null && $this->input->post('last_name_np') !== '' ? $this->input->post('last_name_np') : ''),
                    'father_name' => $this->input->post('father_name') ? $this->input->post('father_name') : '',
                    'mother_name' => $this->input->post('mother_name') ? $this->input->post('mother_name') : '',
                    'contact_no' => $this->input->post('contact_no') ? $this->input->post('contact_no') : '',
                    'emergency_contact_no' => $this->input->post('emergency_contact_no') ? $this->input->post('emergency_contact_no') : '',
                    'email' => $this->input->post('email'),
                    'dob_bs' => $dob_bs,
                    'dob' => $dob,
                    'marital_status' => $this->input->post('marital_status') ? $this->input->post('marital_status') : '',
                    'spouse_name' => $this->input->post('spouse_name') !== null && $this->input->post('spouse_name') !== '' ? $this->input->post('spouse_name') : '',
                    'dependent_info' => $this->input->post('dependent_info') !== null && $this->input->post('dependent_info') !== '' ? $this->input->post('dependent_info') : null,
                    'date_of_joining' => $date_of_joining,
                    'permanent_ward_no' => $this->input->post('permanent_ward_no'),
                    'permanent_house_no' => $this->input->post('permanent_house_no') ? $this->input->post('permanent_house_no') : '',
                    'local_address' => $this->input->post('local_address') ? $this->input->post('local_address') : '',
                    'permanent_address' => $this->input->post('permanent_address') ? $this->input->post('permanent_address') : '',
                    'permanent_tole' => $this->input->post('permanent_tole') ? $this->input->post('permanent_tole') : '',
                    'is_same_as_permanent' => $is_same_as_permanent,
                    'temporary_ward_no' => $is_same_as_permanent ? $this->input->post('permanent_ward_no') : ($this->input->post('temporary_ward_no') ? $this->input->post('temporary_ward_no') : null),
                    'temporary_tole' => $is_same_as_permanent ? $this->input->post('permanent_tole') : ($this->input->post('temporary_tole') ? $this->input->post('temporary_tole') : null),
                    'gender' => $this->input->post('gender'),
                    'account_title' => $this->input->post('account_title') ? $this->input->post('account_title') : '',
                    'bank_account_no' => $this->input->post('bank_account_no') ? $this->input->post('bank_account_no') : '',
                    'bank_name' => $this->input->post('bank_name') ? $this->input->post('bank_name') : '',
                    'ifsc_code' => $this->input->post('ifsc_code') ? $this->input->post('ifsc_code') : '',
                    'bank_branch' => $this->input->post('bank_branch') ? $this->input->post('bank_branch') : '',
                    'payscale' => $this->input->post('payscale') ? $this->input->post('payscale') : '',
                    'religion' => $this->input->post('religion') ? $this->input->post('religion') : '',
                    'caste' => $this->input->post('caste') ? $this->input->post('caste') : '',
                    'basic_salary' => $this->input->post('basic_salary') ? $this->input->post('basic_salary') : null,
                    'epf_no' => $this->input->post('epf_no') ? $this->input->post('epf_no') : '',
                    'contract_type' => $this->input->post('contract_type') ? $this->input->post('contract_type') : '',
                    'shift' => $this->input->post('shift') ? $this->input->post('shift') : '',
                    'location' => $this->input->post('location') ? $this->input->post('location') : '',
                    'facebook' => $this->input->post('facebook') ? $this->input->post('facebook') : '',
                    'twitter' => $this->input->post('twitter') ? $this->input->post('twitter') : '',
                    'linkedin' => $this->input->post('linkedin') ? $this->input->post('linkedin') : '',
                    'instagram' => $this->input->post('instagram') ? $this->input->post('instagram') : '',
                    'password' => $this->enc_lib->passHashEnc($password),
                    'is_active' => 1,
                    'verification_code' => md5(rand(0, 1000)),
                   
                    // New fields from the database schema
                    'salutation' => $this->input->post('salutation') ? $this->input->post('salutation') : '',
                    'pan_no' => $this->input->post('pan_no') ? $this->input->post('pan_no') : '',
                    'employee_type' => $this->input->post('employee_type') ? $this->input->post('employee_type') : '',
                    'employee_position_id' => $this->input->post('employee_position_id') ? $this->input->post('employee_position_id') : null,
                    'post_name' => $this->input->post('post_name') ? $this->input->post('post_name') : '',
                    'position_type' => $this->input->post('position_type') ? $this->input->post('position_type') : '',
                    'ctz_issue_district_code' => $this->input->post('ctz_issue_district_code') ? $this->input->post('ctz_issue_district_code') : '',
                    'nid_no' => $this->input->post('nid_no') ? $this->input->post('nid_no') : '',
                    'citizenship_no' => $this->input->post('citizenship_no') ? $this->input->post('citizenship_no') : '',
                    'non_teaching_type' => null,
                    'institution_id' => $this->input->post('institution_id') ? $this->input->post('institution_id') : null,
                    'college_name' => $this->input->post('college_name') ? $this->input->post('college_name') : '',
                    'campus_name' => $this->input->post('campus_name') ? $this->input->post('campus_name') : '',
                    'section_id' => $this->input->post('section_id') ? $this->input->post('section_id') : null,
                    'group_name' => $this->input->post('group_name') ? $this->input->post('group_name') : '',
                    'sub_group' => $this->input->post('sub_group') ? $this->input->post('sub_group') : '',
                    'position' => $this->input->post('position') ? $this->input->post('position') : '',
                    'work_phone' => $this->input->post('work_phone') ? $this->input->post('work_phone') : '',
                    'work_email' => $this->input->post('work_email') ? $this->input->post('work_email') : '',
                    'org_id' => $this->input->post('org_id') ? $this->input->post('org_id') : null,
                    'note' => '',
                    'image' => '',
                    'resume' => '',
                    'joining_letter' => '',
                    'resignation_letter' => '',
                );

                $staff_data = $this->merge_hemis_employee_position_fields($staff_data);
                $staff_data = $this->merge_staff_hemis_org_unit_from_post($staff_data);
                $staff_data = $this->merge_staff_hemis_qualification_from_post($staff_data);
                $staff_data = $this->merge_staff_ugc_address_post($staff_data);
                $staff_data = $this->staff_model->apply_staff_row_defaults($staff_data);

                if (empty($staff_data['designation']) && !empty($staff_data['post_name'])) {
                    $staff_data['designation'] = $this->resolve_staff_designation_id_from_post_name($staff_data['post_name']);
                } elseif (empty($staff_data['designation']) && $designation_id) {
                    $staff_data['designation'] = $designation_id;
                }
               
                // Handle file upload for photo
                if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                    $fileInfo = pathinfo($_FILES["file"]["name"]);
                    $img_name = $employee_id . '.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/staff_images/" . $img_name);
                    $staff_data['image'] = $img_name;
                }
               
                // Handle citizenship document front and back
                if (isset($_FILES["citizenship_front"]) && !empty($_FILES['citizenship_front']['name'])) {
                    $fileInfo = pathinfo($_FILES["citizenship_front"]["name"]);
                    $citizenship_front_doc = $employee_id . '_citizenship_front.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["citizenship_front"]["tmp_name"], "./uploads/staff_documents/" . $citizenship_front_doc);
                    $staff_data['citizenship_front'] = $citizenship_front_doc;
                }
               
                if (isset($_FILES["citizenship_back"]) && !empty($_FILES['citizenship_back']['name'])) {
                    $fileInfo = pathinfo($_FILES["citizenship_back"]["name"]);
                    $citizenship_back_doc = $employee_id . '_citizenship_back.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["citizenship_back"]["tmp_name"], "./uploads/staff_documents/" . $citizenship_back_doc);
                    $staff_data['citizenship_back'] = $citizenship_back_doc;
                }
               
                // Handle multiple citizenship documents (JSON field)
                $citizenship_files = array();
                if (isset($_FILES["citizenship"]) && !empty($_FILES['citizenship']['name'][0])) {
                    $file_count = count($_FILES['citizenship']['name']);
                   
                    for ($i = 0; $i < $file_count; $i++) {
                        if (!empty($_FILES['citizenship']['name'][$i])) {
                            $fileInfo = pathinfo($_FILES['citizenship']['name'][$i]);
                            $citizenship_doc = $employee_id . '_citizenship_' . time() . '_' . $i . '.' . $fileInfo['extension'];
                           
                            if (move_uploaded_file(
                                $_FILES['citizenship']['tmp_name'][$i],
                                "./uploads/staff_documents/" . $citizenship_doc
                            )) {
                                $citizenship_files[] = $citizenship_doc;
                            } else {
                                log_message('error', 'Failed to move citizenship file: ' . $_FILES['citizenship']['name'][$i]);
                            }
                        }
                    }
                   
                    if (!empty($citizenship_files)) {
                        $staff_data['citizenship'] = json_encode($citizenship_files);
                    }
                }
               
                $national_id_files = array();
                if (isset($_FILES["nationalId"]) && is_array($_FILES['nationalId']['name']) && !empty($_FILES['nationalId']['name'][0])) {
                    $file_count = count($_FILES['nationalId']['name']);
                   
                    // Create upload directory if it doesn't exist
                    $upload_dir = "./uploads/staff_documents/";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                   
                    for ($i = 0; $i < $file_count; $i++) {
                        if (!empty($_FILES['nationalId']['name'][$i]) && $_FILES['nationalId']['error'][$i] === UPLOAD_ERR_OK) {
                            $fileInfo = pathinfo($_FILES['nationalId']['name'][$i]);
                            $extension = strtolower($fileInfo['extension']);
                           
                            // Generate unique filename
                            $national_id_doc = $employee_id . '_nationalId_' . time() . '_' . $i . '.' . $extension;
                            $target_path = $upload_dir . $national_id_doc;
                           
                            // Verify file type
                            $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
                            if (!in_array($extension, $allowed_types)) {
                                log_message('error', 'Invalid file type for national ID: ' . $extension);
                                continue;
                            }
                           
                            // Verify file size (max 2MB)
                            if ($_FILES['nationalId']['size'][$i] > 2097152) {
                                log_message('error', 'File too large for national ID: ' . $_FILES['nationalId']['name'][$i]);
                                continue;
                            }
                           
                            // Move uploaded file
                            if (move_uploaded_file($_FILES['nationalId']['tmp_name'][$i], $target_path)) {
                                $national_id_files[] = $national_id_doc;
                                log_message('debug', 'Successfully uploaded national ID file: ' . $national_id_doc);
                            } else {
                                $error = error_get_last();
                                log_message('error', 'Failed to move national ID file: ' . $_FILES['nationalId']['name'][$i] . ' - Error: ' . ($error ? $error['message'] : 'Unknown error'));
                            }
                        } else if ($_FILES['nationalId']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                            log_message('error', 'Upload error for national ID file ' . $i . ': ' . $_FILES['nationalId']['error'][$i]);
                        }
                    }
                   
                    // Store the files in JSON format
                    if (!empty($national_id_files)) {
                        $staff_data['nationalId'] = json_encode($national_id_files);
                        log_message('debug', 'National ID files stored: ' . json_encode($national_id_files));
                    } else {
                        $staff_data['nationalId'] = null;
                        log_message('debug', 'No valid national ID files were uploaded');
                    }
                } else {
                    $staff_data['nationalId'] = null;
                    log_message('debug', 'No national ID files were provided');
                }
               
                // Handle National ID page
                if (isset($_FILES["nid_page"]) && !empty($_FILES['nid_page']['name'])) {
                    $fileInfo = pathinfo($_FILES["nid_page"]["name"]);
                    $nid_page_doc = $employee_id . '_nid_page.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["nid_page"]["tmp_name"], "./uploads/staff_documents/" . $nid_page_doc);
                    $staff_data['nid_page'] = $nid_page_doc;
                }
               
                // Handle passport size photo
                if (isset($_FILES["pp_size_photo"]) && !empty($_FILES['pp_size_photo']['name'])) {
                    $fileInfo = pathinfo($_FILES["pp_size_photo"]["name"]);
                    $pp_photo = $employee_id . '_pp_photo.' . $fileInfo['extension'];
                    move_uploaded_file($_FILES["pp_size_photo"]["tmp_name"], "./uploads/staff_documents/" . $pp_photo);
                    $staff_data['pp_size_photo'] = $pp_photo;
                }
               
                // Handle academic documents
                $academic_files = array(
                    'certificate_copy' => 'certificate_copy',
                    'transcript_copy' => 'transcript_copy',
                    'marksheet_copy' => 'marksheet_copy'
                );
               
                foreach ($academic_files as $key => $doc_field) {
                    if (isset($_FILES[$key]) && !empty($_FILES[$key]['name'])) {
                        $fileInfo = pathinfo($_FILES[$key]["name"]);
                        $doc_name = $employee_id . '_' . $doc_field . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES[$key]["tmp_name"], "./uploads/staff_documents/" . $doc_name);
                        $staff_data[$doc_field] = $doc_name;
                    }
                }
               
                // Handle additional documents
                $additional_files = array(
                    'reference_letter' => 'reference_letter',
                    'other_letter' => 'other_letter',
                    'other_image' => 'other_image'
                );
               
                foreach ($additional_files as $key => $doc_field) {
                    if (isset($_FILES[$key]) && !empty($_FILES[$key]['name'])) {
                        $fileInfo = pathinfo($_FILES[$key]["name"]);
                        $doc_name = $employee_id . '_' . $doc_field . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES[$key]["tmp_name"], "./uploads/staff_documents/" . $doc_name);
                        $staff_data[$doc_field] = $doc_name;
                    }
                }
               
                // Process other documents
                $document_files = array(
                    'first_doc' => 'resume',
                    'second_doc' => 'joining_letter',
                    'third_doc' => 'resignation_letter',
                    'fourth_doc' => 'other_document_file'
                );
               
                foreach ($document_files as $key => $doc_field) {
                    if (isset($_FILES[$key]) && !empty($_FILES[$key]['name'])) {
                        $fileInfo = pathinfo($_FILES[$key]["name"]);
                        $doc_name = $employee_id . '_' . $doc_field . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES[$key]["tmp_name"], "./uploads/staff_documents/" . $doc_name);
                        $staff_data[$doc_field] = $doc_name;
                    }
                }
               
                // If the form has the "other documents" title
                if ($this->input->post('fourth_title')) {
                    $staff_data['other_document_name'] = $this->input->post('fourth_title');
                }
   
                $other_document_names = $this->input->post('other_document_name');
                $other_document_files_uploaded = array();
                $other_document_names_array = array();
   
                if (!empty($other_document_names) && is_array($other_document_names)) {
                    foreach ($other_document_names as $index => $document_name) {
                        if (!empty($document_name) && isset($_FILES['other_document_file']['name'][$index]) && !empty($_FILES['other_document_file']['name'][$index])) {
                            // Check if file was uploaded without errors
                            if ($_FILES['other_document_file']['error'][$index] === 0) {
                                $fileInfo = pathinfo($_FILES['other_document_file']['name'][$index]);
                                $doc_name = $employee_id . '_additional_' . $index . '.' . $fileInfo['extension'];
                               
                                if (move_uploaded_file($_FILES['other_document_file']['tmp_name'][$index], "./uploads/staff_documents/" . $doc_name)) {
                                    $other_document_files_uploaded[] = $doc_name;
                                    $other_document_names_array[] = $document_name;
                                }
                            }
                        }
                    }
                }
   
                // Update the staff_data array to include the additional documents
                if (!empty($other_document_files_uploaded)) {
                    $staff_data['other_document_name'] = json_encode($other_document_names_array);
                    $staff_data['other_document_file'] = json_encode($other_document_files_uploaded);
                } else {
                    $staff_data['other_document_name'] = null;
                    $staff_data['other_document_file'] = null;
                }
               
                // Debug: Log the staff data before insertion
                log_message('debug', 'Staff data before insertion: ' . print_r($staff_data, true));
               
                // Insert staff data and get the inserted ID
                $insert_id = $this->staff_model->add($staff_data);
               
                if (!$insert_id) {
                    throw new Exception($this->staff_model->describe_insert_failure($staff_data));
                }
               
                // Insert role data
                $role_data = array(
                    'staff_id' => $insert_id,
                    'role_id' => $this->input->post('role'),
                    'is_active' => 1
                );
                $this->staff_model->add_staff_role($role_data);
               
                // Handle custom fields if any
                if (!empty($custom_fields)) {
                    $custom_field_post = $this->input->post("custom_fields[staff]");
                    if (!empty($custom_field_post)) {
                        foreach ($custom_field_post as $key => $value) {
                            $check_field_type = $this->input->post("custom_fields[staff][" . $key . "]");
                            $field_value = is_array($check_field_type) ? implode(",", $value) : $value;
                            $custom_field_array = array(
                                'belong_table_id' => $insert_id,
                                'custom_field_id' => $key,
                                'field_value' => $field_value,
                            );
                            $this->customfield_model->insertRecord($custom_field_array);
                        }
                    }
                }
               
                // Commit the transaction
                $this->db->trans_complete();
               
                if ($this->db->trans_status() === FALSE) {
                    // Transaction failed, roll back
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line('error_occurred') . '</div>');
                    log_message('error', 'Transaction failed during staff creation');
                } else {
                    // Transaction successful
                    $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
                    log_message('info', 'Staff created successfully with ID: ' . $insert_id);

                    // Outbound HEMIS sync: multipart POST + DB files (recommended path).
                    $this->load->library('hemis_client');
                    $hemis_sync = $this->hemis_client->sync_employee_after_local_create((int) $insert_id);
                    log_message('error', 'RAW HEMIS employee create response: ' . json_encode($hemis_sync));
                    log_message('info', 'HEMIS employee sync after web create: ' . json_encode($hemis_sync));

                    if (!empty($hemis_sync['status_code']) && (int) $hemis_sync['status_code'] === 409
                        && $this->db->field_exists('hemis_sync_error', 'staff')) {
                        $payload409 = array();
                        $row409 = $this->staff_model->get((int) $insert_id);
                        if (is_array($row409)) {
                            $payload409 = $this->hemis_client->map_staff_row_to_hemis_employee_payload($row409);
                        }
                        log_message('error', 'HEMIS employee POST 409 duplicate for staff id: '
                            . $insert_id
                            . ' - employee may already exist at UGC level. '
                            . 'Payload citizenshipNo: ' . (!empty($payload409['citizenshipNo'])
                                ? $payload409['citizenshipNo'] : 'null'));
                        if (empty($hemis_sync['error'])) {
                            $hemis_sync['error'] = 'Duplicate detected at UGC level (409). Resolve manually or contact UGC.';
                        }
                    }

                    $this->_record_staff_ugc_employee_sync((int) $insert_id, is_array($hemis_sync) ? $hemis_sync : array());
                   
                    // Get staff email and name from the form
                    $staff_email = $this->input->post('email');
                    $staff_name = $this->input->post('name');
                   
                    // Send credentials email to the staff member
                    $email_result = $this->email_service->send_staff_credentials($staff_email, $staff_name, $employee_id, $plain_password);
                   
                    if ($email_result['status']) {
                        log_message('info', 'Staff credentials email sent successfully to: ' . $staff_email);
                        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . ' Email with login credentials has been sent to the staff member.</div>');
                    } else {
                        log_message('error', 'Failed to send staff credentials email: ' . $email_result['message']);
                        $this->session->set_flashdata('msg', '<div class="alert alert-warning">' . $this->lang->line('success_message') . ' However, there was an issue sending the email with login credentials.</div>');
                    }
                }
                   
                    redirect('admin/staff');
               
            } catch (Exception $e) {
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                }
                log_message('error', 'Staff Creation Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
                $error_text = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                $error_text = str_replace(' | ', '<br>', $error_text);
                $this->session->set_flashdata('msg', '<div class="alert alert-danger"><strong>Error saving staff</strong><br>' . $error_text . '</div>');
                redirect('admin/staff/create');
            }
           
        } else {
            // Form validation failed - display the form with errors
            $this->load->view('layout/header', $data);
            $this->load->view('admin/staff/staffcreate', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    /**
     * Get cached UGC departments for staff forms (no Super Admin required).
     * GET /admin/staff/ugc_departments_get
     */
    public function ugc_departments_get()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_view') && !$this->rbac->hasPrivilege('staff', 'can_add') && !$this->rbac->hasPrivilege('staff', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            echo json_encode(array('status' => 0, 'message' => 'Permission denied', 'data' => array()));
            return;
        }

        $this->load->model('ugc_metadata_model');
        $this->ugc_metadata_model->ensure_tables();
        $rows = $this->ugc_metadata_model->get_departments_cached();
        $this->output->set_content_type('application/json');
        echo json_encode(array('status' => 1, 'data' => $rows));
    }

    /**
     * Get cached UGC sections for staff forms (no Super Admin required).
     * GET /admin/staff/ugc_sections_get
     */
    public function ugc_sections_get()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_view') && !$this->rbac->hasPrivilege('staff', 'can_add') && !$this->rbac->hasPrivilege('staff', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            echo json_encode(array('status' => 0, 'message' => 'Permission denied', 'data' => array()));
            return;
        }

        $this->load->model('ugc_metadata_model');
        $this->ugc_metadata_model->ensure_tables();
        $rows = $this->ugc_metadata_model->get_sections_cached();
        $this->output->set_content_type('application/json');
        echo json_encode(array('status' => 1, 'data' => $rows));
    }

    public function _ugc_department_exists($ugc_department_id)
    {
        $id = (int) $ugc_department_id;
        if ($id < 1) {
            return true;
        }
        if (!$this->db->table_exists('ugc_departments')) {
            $this->form_validation->set_message('_ugc_department_exists', 'UGC departments cache is not ready. Please refresh metadata and try again.');
            return false;
        }
        $row = $this->db->select('id')->from('ugc_departments')->where('id', $id)->get()->row_array();
        if (empty($row)) {
            $this->form_validation->set_message('_ugc_department_exists', 'Selected UGC Department no longer exists in local metadata cache. Please refresh metadata and re-select.');
            return false;
        }
        return true;
    }

    public function _ugc_section_exists($ugc_section_id)
    {
        $id = (int) $ugc_section_id;
        if ($id < 1) {
            return true;
        }
        if (!$this->db->table_exists('ugc_sections')) {
            $this->form_validation->set_message('_ugc_section_exists', 'UGC sections cache is not ready. Please refresh metadata and try again.');
            return false;
        }
        $row = $this->db->select('id')->from('ugc_sections')->where('id', $id)->get()->row_array();
        if (empty($row)) {
            $this->form_validation->set_message('_ugc_section_exists', 'Selected UGC Section no longer exists in local metadata cache. Please refresh metadata and re-select.');
            return false;
        }
        return true;
    }

    public function _ugc_dept_or_section_required($department)
    {
        $pid = (int) $this->input->post('employee_position_id');
        $sel = (int) $department;
        if ($pid < 1) {
            return true;
        }

        $this->load->model('ugc_metadata_model');
        $pos = $this->ugc_metadata_model->get_employee_position_by_id($pid);
        if (!is_array($pos) || empty($pos)) {
            return true;
        }

        $isTeaching = $this->ugc_metadata_model->is_teaching_position_row($pos);
        $org = $this->ugc_metadata_model->parse_position_row_org_ids($pos);
        if ($isTeaching && $org['departmentId'] > 0) {
            return true;
        }
        if (!$isTeaching && $org['sectionId'] > 0) {
            return true;
        }

        if ($isTeaching) {
            if (!$this->db->table_exists('ugc_departments') || (int) $this->db->count_all('ugc_departments') < 1) {
                return true;
            }
            if ($sel < 1) {
                $this->form_validation->set_message('_ugc_dept_or_section_required', 'UGC Department (HEMIS departmentId) is required for this teaching position.');
                return false;
            }
        } else {
            if (!$this->db->table_exists('ugc_sections') || (int) $this->db->count_all('ugc_sections') < 1) {
                return true;
            }
            if ($sel < 1) {
                $this->form_validation->set_message('_ugc_dept_or_section_required', 'UGC Section (HEMIS sectionId) is required for this non-teaching position.');
                return false;
            }
        }
        return true;
    }





    public function validate_file_upload($files, $field) {
        if (isset($_FILES[$field]['name']) && is_array($_FILES[$field]['name'])) {
            $file_count = count($_FILES[$field]['name']);
           
            for ($i = 0; $i < $file_count; $i++) {
                if (!empty($_FILES[$field]['name'][$i]) && $_FILES[$field]['error'][$i] === UPLOAD_ERR_OK) {
                    // Check file extension
                    $fileInfo = pathinfo($_FILES[$field]['name'][$i]);
                    $extension = strtolower($fileInfo['extension']);
                    $allowedExts = array('jpg', 'jpeg', 'png', 'pdf');
                   
                    if (!in_array($extension, $allowedExts)) {
                        $this->form_validation->set_message('validate_file_upload', 'Invalid file format for ' . $field . '. Only JPG, JPEG, PNG, PDF are allowed.');
                        return false;
                    }
                   
                    // Check file size (2MB max)
                    if ($_FILES[$field]['size'][$i] > 2097152) {
                        $this->form_validation->set_message('validate_file_upload', 'File size should be less than 2MB for ' . $field);
                        return false;
                    }
                }
            }
        }
        return true;
    }









    public function handle_upload()
{
    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
        $allowedExts = array('jpg', 'jpeg', 'png');
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);
        if ($_FILES["file"]["error"] > 0) {
            $error .= "Error: " . $_FILES["file"]["error"] . "<br />";
        }
        if (($_FILES["file"]["size"] > 2097152)) {
            $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than_2mb'));
            return FALSE;
        }
        if (!in_array(strtolower($extension), $allowedExts)) {
            $this->form_validation->set_message('handle_upload', $this->lang->line('invalid_file_format'));
            return FALSE;
        }
    }
    return TRUE;
}
    public function handle_first_upload()
    {
        $file_validate = $this->config->item('file_validate');
        $result        = $this->filetype_model->get();
        if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {

            $file_type = $_FILES["first_doc"]['type'];
            $file_size = $_FILES["first_doc"]["size"];
            $file_name = $_FILES["first_doc"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES['first_doc']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_first_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_first_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_first_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($file_validate['upload_size'] / 1048576, 2) . " MB");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_second_upload()
    {
        $file_validate = $this->config->item('file_validate');
        $result        = $this->filetype_model->get();
        if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {

            $file_type         = $_FILES["second_doc"]['type'];
            $file_size         = $_FILES["second_doc"]["size"];
            $file_name         = $_FILES["second_doc"]["name"];
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES['second_doc']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_second_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_second_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }

            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_second_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($file_validate['upload_size'] / 1048576, 2) . " MB");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_third_upload()
    {
        $file_validate = $this->config->item('file_validate');
        $result        = $this->filetype_model->get();
        if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {

            $file_type = $_FILES["third_doc"]['type'];
            $file_size = $_FILES["third_doc"]["size"];
            $file_name = $_FILES["third_doc"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES['third_doc']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_third_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_third_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_third_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($file_validate['upload_size'] / 1048576, 2) . " MB");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_fourth_upload()
    {
        $file_validate = $this->config->item('file_validate');
        $result        = $this->filetype_model->get();
        if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {

            $file_type = $_FILES["fourth_doc"]['type'];
            $file_size = $_FILES["fourth_doc"]["size"];
            $file_name = $_FILES["fourth_doc"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES['fourth_doc']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_fourth_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_fourth_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_fourth_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($file_validate['upload_size'] / 1048576, 2) . " MB");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_additional_upload()
    {
        $file_validate = $this->config->item('file_validate');
        $result = $this->filetype_model->get();
        
        if (isset($_FILES["other_document_file"]) && !empty($_FILES['other_document_file']['name'])) {
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            
            // Handle multiple files
            foreach ($_FILES['other_document_file']['name'] as $index => $filename) {
                if (!empty($filename)) {
                    // Skip if there's an upload error
                    if ($_FILES["other_document_file"]['error'][$index] !== 0) {
                        continue;
                    }
                    
                    $file_type = $_FILES["other_document_file"]['type'][$index];
                    $file_size = $_FILES["other_document_file"]["size"][$index];
                    $file_name = $_FILES["other_document_file"]["name"][$index];
                    $tmp_name = $_FILES["other_document_file"]["tmp_name"][$index];
                    
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mtype = finfo_file($finfo, $tmp_name);
                    finfo_close($finfo);
                    
                    if (!in_array($mtype, $allowed_mime_type)) {
                        $this->form_validation->set_message('handle_additional_upload', $this->lang->line('file_type_not_allowed') . ' for document ' . ($index + 1));
                        return false;
                    }
                    
                    if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                        $this->form_validation->set_message('handle_additional_upload', $this->lang->line('extension_not_allowed') . ' for document ' . ($index + 1));
                        return false;
                    }
                    
                    if ($file_size > $result->file_size) {
                        $this->form_validation->set_message('handle_additional_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($file_validate['upload_size'] / 1048576, 2) . " MB for document " . ($index + 1));
                        return false;
                    }
                }
            }
        }
        return true;
    }
    

    public function username_check($str)
    {
        if (empty($str)) {
            $this->form_validation->set_message('username_check', $this->lang->line('staff_id_field_is_required'));
            return false;
        } else {
            $result = $this->staff_model->valid_employee_id($str);
            if ($result == false) {
                return false;
            }
            return true;
        }
    }

    public function edit($id)
    {
        if (!$this->session->userdata('hemis_cols_checked')) {
            $this->ensure_staff_hemis_columns();
            $this->session->set_userdata('hemis_cols_checked', TRUE);
        }
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff');
        $data['title'] = 'Edit Staff';
        $data['id'] = $id;
        $data['roles'] = $this->role_model->get();
        $data['genderList'] = $this->customlib->getGender();
        $data['marital_status'] = $this->customlib->getMaritalStatus();
        $data['contract_type'] = $this->contract_type;
        $this->load->model('ugc_metadata_model');
        $this->ugc_metadata_model->ensure_tables();
        $data['department'] = $this->department_model->get();
        $data['ugc_departments'] = $this->ugc_metadata_model->get_departments_cached();
        $data['ugc_sections'] = $this->ugc_metadata_model->get_sections_cached();
        $data['designation'] = $this->designation_model->get();
        $data['staff'] = $this->staff_model->get($id);
        $data['staff_doc'] = $this->staff_model->getStaffDocuments($id);
        $data['sch_setting'] = $this->setting_model->getSetting();

        $positions = $this->ugc_metadata_model->get_employee_positions_for_staff_form();
        $data['ugc_employee_positions'] = is_array($positions) ? $positions : array();
        if (empty($data['ugc_employee_positions'])) {
            $seeded = $this->ugc_metadata_model->seed_employee_positions_if_empty();
            if ($seeded > 0) {
                $positions = $this->ugc_metadata_model->get_employee_positions_for_staff_form();
                $data['ugc_employee_positions'] = is_array($positions) ? $positions : array();
            }
        }
        $data['leave_types_list'] = $this->staff_model->getLeaveType();
        $alloc_rows = $this->staff_model->allotedLeaveType($id);
        $data['leave_alloc_by_type'] = array();
        foreach ($alloc_rows as $row) {
            $data['leave_alloc_by_type'][(int) $row['leave_type_id']] = $row['alloted_leave'];
        }
        if ($this->input->method() === 'post' && $this->input->post('leave_alloc') && is_array($this->input->post('leave_alloc'))) {
            $posted = $this->input->post('leave_alloc');
            foreach ($data['leave_types_list'] as $lt_row) {
                $tid = (int) $lt_row['id'];
                if (array_key_exists($tid, $posted) || array_key_exists((string) $tid, $posted)) {
                    $v = isset($posted[$tid]) ? $posted[$tid] : $posted[(string) $tid];
                    $data['leave_alloc_by_type'][$tid] = $v;
                }
            }
        }
        
        // Fixed form validation rules to match the actual form field names
        $this->form_validation->set_rules('name', $this->lang->line('first_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('middle_name', $this->lang->line('middle_name'), 'trim|xss_clean'); 
        $this->form_validation->set_rules('surname', $this->lang->line('last_name'), 'trim|xss_clean'); 
        $this->form_validation->set_rules('firstnameNp', $this->lang->line('first_name_np'), 'trim|xss_clean'); 
        $this->form_validation->set_rules('middlenameNp', $this->lang->line('middle_name_np'), 'trim|xss_clean'); 
        $this->form_validation->set_rules('lastnameNp', $this->lang->line('last_name_np'), 'trim|xss_clean'); 
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|required|xss_clean|valid_email|callback_check_email_exists[' . $id . ']');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob_bs', $this->lang->line('date_of_birth_bs'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|xss_clean');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('department', $this->lang->line('department'), 'trim|callback__ugc_dept_or_section_required');
        $this->form_validation->set_rules('contact_no', $this->lang->line('phone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('religion', $this->lang->line('religion'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('permanent_ward_no', $this->lang->line('ward'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('employee_position_id', 'Employee Position (HEMIS)', 'trim|required|integer|greater_than[0]');
        $this->form_validation->set_rules('graduated_from', 'Graduated From (HEMIS)', 'callback__graduated_from_required');

        $this->form_validation->set_rules('ctz_issue_district_code', $this->lang->line('ctz_issue_district_code'), 'trim|xss_clean');
        $this->form_validation->set_rules('nid_no', $this->lang->line('nid_no'), 'trim|xss_clean');
        $this->form_validation->set_rules('salutation', $this->lang->line('salutation'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('pan_no', $this->lang->line('pan_no'), 'trim|xss_clean');
        $this->form_validation->set_rules('work_phone', $this->lang->line('work_phone'), 'trim|xss_clean');
        $this->form_validation->set_rules('work_email', $this->lang->line('work_email'), 'trim|xss_clean|valid_email');
    
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/staff/staffedit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $same_as_permanent_post = $this->_staff_is_same_as_permanent_from_post();

            $update_data = array(
                'id' => $id,
                'employee_id' => $this->input->post('employee_id'),
                'lang_id' => $this->input->post('lang_id') ? $this->input->post('lang_id') : 0,
                'currency_id' => $this->input->post('currency_id') ? $this->input->post('currency_id') : 0,
                'department' => $this->input->post('department'),
                'designation' => null,
                'qualification' => $this->input->post('qualification'),
                'work_exp' => $this->input->post('work_exp'),
                'name' => $this->input->post('name'),
                'middle_name' => $this->input->post('middle_name'),  
                'surname' => $this->input->post('surname'),
                'firstnameNp' => $this->input->post('firstnameNp'),
                'middlenameNp' => $this->input->post('middlenameNp'),  
                'lastnameNp' => $this->input->post('lastnameNp'),
                'father_name' => $this->input->post('father_name'),
                'mother_name' => $this->input->post('mother_name'),
                'email' => $this->input->post('email'),
                'gender' => $this->input->post('gender'),
                'dob_bs' => $this->input->post('dob_bs'), 
                'dob' => $this->input->post('dob') ? date('Y-m-d', strtotime($this->input->post('dob'))) : null,
                'date_of_joining' => $this->input->post('date_of_joining'),
                'date_of_leaving' => $this->input->post('date_of_leaving') ? date('Y-m-d', strtotime($this->input->post('date_of_leaving'))) : null,
                'contact_no' => $this->input->post('contact_no'),
                'emergency_contact_no' => $this->input->post('emergency_contact_no'),
                'marital_status' => $this->input->post('marital_status'),
                'spouse_name' => $this->input->post('spouse_name') !== null && $this->input->post('spouse_name') !== '' ? $this->input->post('spouse_name') : '',
                'dependent_info' => $this->input->post('dependent_info') !== null && $this->input->post('dependent_info') !== '' ? $this->input->post('dependent_info') : null,
                'religion' => $this->input->post('religion'),
                'caste' => $this->input->post('caste'),
                'local_address' => $same_as_permanent_post
                    ? $this->input->post('permanent_address') : $this->input->post('local_address'),
                'permanent_address' => $this->input->post('permanent_address'),
                'note' => $this->input->post('note'),
                'account_title' => $this->input->post('account_title'),
                'bank_account_no' => $this->input->post('bank_account_no'),
                'bank_name' => $this->input->post('bank_name'),
                'ifsc_code' => $this->input->post('ifsc_code'),
                'bank_branch' => $this->input->post('bank_branch'),
                'payscale' => $this->input->post('payscale'),
                'basic_salary' => $this->input->post('basic_salary'),
                'epf_no' => $this->input->post('epf_no'),
                'contract_type' => $this->input->post('contract_type'),
                'shift' => $this->input->post('shift'),
                'location' => $this->input->post('location'),
                'facebook' => $this->input->post('facebook'),
                'twitter' => $this->input->post('twitter'),
                'linkedin' => $this->input->post('linkedin'),
                'instagram' => $this->input->post('instagram'),
                'permanent_ward_no' => $this->input->post('permanent_ward_no'),
                'permanent_house_no' => $this->input->post('permanent_house_no'),
                'permanent_tole' => $this->input->post('permanent_tole'),
                'temporary_ward_no' => $same_as_permanent_post
                    ? $this->input->post('permanent_ward_no') : $this->input->post('temporary_ward_no'),
                'temporary_tole' => $same_as_permanent_post
                    ? $this->input->post('permanent_tole') : $this->input->post('temporary_tole'),
                'is_same_as_permanent' => $same_as_permanent_post ? 1 : 0,
                'employee_type' => $this->input->post('employee_type'),
                'org_id' => $this->input->post('org_id') ? $this->input->post('org_id') : null,
                'employee_position_id' => $this->input->post('employee_position_id'),
                'post_name' => $this->input->post('post_name'),
                'position_type' => $this->input->post('position_type'),
                'salutation' => $this->input->post('salutation'),
                'ctz_issue_district_code' => $this->input->post('ctz_issue_district_code'),
                'nid_no' => $this->input->post('nid_no'),
                'citizenship_no' => $this->input->post('citizenship_no'),
                'pan_no' => $this->input->post('pan_no'),
                'non_teaching_type' => null,
                'institution_id' => $this->input->post('institution_id') ? $this->input->post('institution_id') : null,
                'college_name' => $this->input->post('college_name') ? $this->input->post('college_name') : '',
                'campus_name' => $this->input->post('campus_name') ? $this->input->post('campus_name') : '',
                'section_id' => $this->input->post('section_id') ? $this->input->post('section_id') : null,
                'group_name' => $this->input->post('group_name') ? $this->input->post('group_name') : '',
                'sub_group' => $this->input->post('sub_group') ? $this->input->post('sub_group') : '',
                'position' => $this->input->post('position') ? $this->input->post('position') : '',
                'work_phone' => $this->input->post('work_phone'),
                'work_email' => $this->input->post('work_email'),
                'disable_at' => $this->input->post('disable_at') ? date('Y-m-d', strtotime($this->input->post('disable_at'))) : null,
            );

            $update_data = $this->merge_hemis_employee_position_fields($update_data);
            $update_data = $this->merge_staff_hemis_org_unit_from_post($update_data);
            $update_data = $this->merge_staff_hemis_qualification_from_post($update_data);
            $update_data = $this->merge_staff_ugc_address_post($update_data);

            if (!empty($update_data['post_name'])) {
                $update_data['designation'] = $this->resolve_staff_designation_id_from_post_name($update_data['post_name']);
            }
    
            // Handle file uploads
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $fileInfo = pathinfo($_FILES["file"]["name"]);
                $img_name = $id . '.' . $fileInfo['extension'];
                $this->upload->initialize($this->set_upload_options($img_name));
                if ($this->upload->do_upload('file')) {
                    $update_data['image'] = $img_name;
                } else {
                    log_message('error', 'Staff image upload failed: ' . $this->upload->display_errors());
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->upload->display_errors() . '</div>');
                }
            }
    
            // Handle Citizenship document (multiple files allowed)
            if (!empty($_FILES['citizenship']['name'][0])) {
                $citizenship_files = [];
                $upload_path = "./uploads/staff_documents/" . $id . "/";
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
    
                foreach ($_FILES['citizenship']['name'] as $i => $name) {
                    if (!empty($name)) {
                        $fileInfo = pathinfo($name);
                        $filename = $id . '_citizenship_' . time() . "_$i." . $fileInfo['extension'];
                        move_uploaded_file($_FILES['citizenship']['tmp_name'][$i], $upload_path . $filename);
                        $citizenship_files[] = $filename;
                    }
                }
    
                $update_data['citizenship'] = json_encode($citizenship_files);
            } elseif ($this->input->post('old_citizenship')) {
                $update_data['citizenship'] = $this->input->post('old_citizenship');
            }
    
            // Handle National ID document (multiple files allowed)
            if (!empty($_FILES['nationalId']['name'][0])) {
                $nationalId_files = [];
                $upload_path = "./uploads/staff_documents/" . $id . "/";
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
    
                foreach ($_FILES['nationalId']['name'] as $i => $name) {
                    if (!empty($name)) {
                        $fileInfo = pathinfo($name);
                        $filename = $id . '_nationalId_' . time() . "_$i." . $fileInfo['extension'];
                        move_uploaded_file($_FILES['nationalId']['tmp_name'][$i], $upload_path . $filename);
                        $nationalId_files[] = $filename;
                    }
                }
    
                $update_data['nationalId'] = json_encode($nationalId_files);
            } elseif ($this->input->post('old_nationalId')) {
                $update_data['nationalId'] = $this->input->post('old_nationalId');
            }
            
    
            // Handle Resume document (first_doc)
            if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                $fileInfo = pathinfo($_FILES["first_doc"]["name"]);
                $resume_doc = $id . '_resume.' . $fileInfo['extension'];
                $upload_path = "./uploads/staff_documents/" . $id . "/";
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                move_uploaded_file($_FILES["first_doc"]["tmp_name"], $upload_path . $resume_doc);
                $update_data['resume'] = $resume_doc;
            } else if ($this->input->post('old_first_doc')) {
                $update_data['resume'] = $this->input->post('old_first_doc');
            }
    
            // Handle Joining Letter document (second_doc)
            if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
                $fileInfo = pathinfo($_FILES["second_doc"]["name"]);
                $joining_letter_doc = $id . '_joining_letter.' . $fileInfo['extension'];
                $upload_path = "./uploads/staff_documents/" . $id . "/";
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                move_uploaded_file($_FILES["second_doc"]["tmp_name"], $upload_path . $joining_letter_doc);
                $update_data['joining_letter'] = $joining_letter_doc;
            } else if ($this->input->post('old_second_doc')) {
                $update_data['joining_letter'] = $this->input->post('old_second_doc');
            }
    
            // Handle Resignation Letter document (third_doc)
            if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {
                $fileInfo = pathinfo($_FILES["third_doc"]["name"]);
                $resignation_letter_doc = $id . '_resignation_letter.' . $fileInfo['extension'];
                $upload_path = "./uploads/staff_documents/" . $id . "/";
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                move_uploaded_file($_FILES["third_doc"]["tmp_name"], $upload_path . $resignation_letter_doc);
                $update_data['resignation_letter'] = $resignation_letter_doc;
            } else if ($this->input->post('old_third_doc')) {
                $update_data['resignation_letter'] = $this->input->post('old_third_doc');
            }
    
            // Handle dynamic other documents
            $other_doc_names = [];
            $other_doc_files = [];
    
            if (isset($_POST['other_document_name'])) {
                foreach ($_POST['other_document_name'] as $index => $doc_name) {
                    $old_file = $_POST['old_other_doc_file'][$index] ?? '';
                    $upload_path = "./uploads/staff_documents/" . $id . "/";
    
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0777, true);
                    }
    
                    $filename = '';
    
                    if (!empty($_FILES['other_document_file']['name'][$index])) {
                        $fileInfo = pathinfo($_FILES['other_document_file']['name'][$index]);
                        $clean_name = preg_replace('/\s+/', '_', strtolower($doc_name));
                        $filename = $id . '_' . $clean_name . '_' . time() . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES['other_document_file']['tmp_name'][$index], $upload_path . $filename);
                    } elseif (!empty($old_file)) {
                        $filename = $old_file;
                    }
    
                    if (!empty($doc_name) && !empty($filename)) {
                        $other_doc_names[] = $doc_name;
                        $other_doc_files[] = $filename;
                    }
                }
    
                // Save correctly formatted JSON to update_data
                $update_data['other_document_name'] = json_encode($other_doc_names);
                $update_data['other_document_file'] = json_encode($other_doc_files);
            }
    
            // Update staff data
            log_message('error', 'Update Data: ' . print_r($update_data, true));
    
            $update_result = $this->staff_model->update($update_data);
    
            if ($update_result) {
                $role_data = [
                    'staff_id' => $id,
                    'role_id' => $this->input->post('role'),
                    'is_active' => 1
                ];
                $this->staff_model->update_staff_role($role_data);
                $this->staff_model->sync_staff_leave_allocations($id, $this->input->post('leave_alloc'));

                $this->load->library('hemis_client');
                $staff_row = $this->staff_model->get((int) $id);
                $payload = is_array($staff_row) ? $this->hemis_client->map_staff_row_to_hemis_employee_payload($staff_row) : array();
                log_message('error', 'HEMIS PATCH will use employee id: '
                    . (!empty($staff_row['hemis_employee_id'])
                        ? 'UGC_' . $staff_row['hemis_employee_id']
                        : 'LOCAL_' . $id)
                    . ' for staff id: ' . $id);
                $hemis_sync = $this->hemis_client->sync_employee_patch((int) $id, $payload);
                log_message('info', 'HEMIS employee sync after web edit: ' . json_encode($hemis_sync));

                $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line('update_failed') . '</div>');
            }
    
            redirect('admin/staff');
        }
    }

    // TEMPORARY: remove before production
    public function hemis_test()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }
        $this->load->model('hemis_integration_model');
        $this->load->library('hemis_client');
        $cfg = $this->hemis_integration_model->get_effective_config();
        if (!is_array($cfg)) {
            $cfg = array();
        }
        $errors = array();
        $tokenStr = isset($cfg['remote_bearer_token']) ? trim((string) $cfg['remote_bearer_token']) : '';

        $pos = $this->hemis_client->hemis_get_employee_positions();
        $posList = (!empty($pos['ok']) && isset($pos['data']) && is_array($pos['data'])) ? $pos['data'] : array();
        $posHttp = isset($pos['http_code']) ? (int) $pos['http_code'] : null;
        $posSample = isset($posList[0]) ? $posList[0] : null;
        $posSuccess = !empty($pos['ok']) && $posHttp !== null && $posHttp >= 200 && $posHttp < 300;

        $all = $this->hemis_client->diagnostic_http_get_list_sample('/api/Employee/GetAllEmployeees');
        $allSuccess = empty($all['error']) && (int) $all['http_code'] >= 200 && (int) $all['http_code'] < 300;

        $payload = array(
            'positions_endpoint_probe' => $this->hemis_client->hemis_test_positions_endpoint(),
            'config' => array(
                'sync_enabled'     => !empty($cfg['sync_enabled']),
                'sync_employee'    => !empty($cfg['sync_employee']),
                'mock_mode'        => !empty($cfg['mock_mode']),
                'remote_base_url'  => isset($cfg['remote_base_url']) ? (string) $cfg['remote_base_url'] : '',
                'token_set'        => ($tokenStr !== ''),
            ),
            'employee_positions_test' => array(
                'success'   => $posSuccess,
                'http_code' => $posHttp,
                'count'     => count($posList),
                'sample'    => $posSample,
            ),
            'get_all_employees_test' => array(
                'success'   => $allSuccess,
                'http_code' => isset($all['http_code']) ? (int) $all['http_code'] : 0,
                'count'     => isset($all['count']) ? (int) $all['count'] : 0,
                'sample'    => isset($all['sample']) ? $all['sample'] : null,
            ),
            'errors' => $errors,
        );
        if (!$payload['config']['sync_enabled']) {
            $payload['errors'][] = 'sync_enabled is false';
        }
        if (!$payload['config']['sync_employee']) {
            $payload['errors'][] = 'sync_employee is false';
        }
        if ($payload['config']['mock_mode']) {
            $payload['errors'][] = 'mock_mode is true (live API tests expect false)';
        }
        if ($all['error']) {
            $payload['errors'][] = 'GetAllEmployees: ' . $all['error'];
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    // TEMPORARY: remove before production
    public function reseed_positions()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }
        $this->load->model('ugc_metadata_model');
        $this->ugc_metadata_model->truncate_employee_positions_cache();
        $count = $this->ugc_metadata_model->seed_employee_positions_if_empty();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'success' => $count > 0,
                'seeded'  => $count,
                'message' => $count > 0
                    ? 'Positions reseeded: ' . $count . ' rows'
                    : 'Seed failed - check logs',
            )));
    }

    // TEMPORARY: remove before production
    public function hemis_test_create_employee()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }
        $this->load->model('hemis_integration_model');
        $this->load->library('hemis_client');
        $cfg = $this->hemis_integration_model->get_effective_config();
        if (!is_array($cfg)) {
            $cfg = array();
        }
        $campusId = isset($cfg['remote_college_id']) ? (int) $cfg['remote_college_id'] : 0;

        $positionsResult = $this->hemis_client->hemis_get_employee_positions();
        $positions = array();
        if (!empty($positionsResult['ok']) && isset($positionsResult['data']) && is_array($positionsResult['data'])) {
            $positions = $positionsResult['data'];
        }

        if (empty($positions) || !is_array($positions)) {
            $this->load->model('ugc_metadata_model');
            $this->ugc_metadata_model->ensure_tables();
            $this->ugc_metadata_model->seed_employee_positions_if_empty();
            $positions = $this->ugc_metadata_model->get_employee_positions_from_db_only();
        }

        $firstPositionId = 0;
        $firstPostName   = 'Peon';

        if (!empty($positions) && is_array($positions)) {
            $first = reset($positions);
            if (is_array($first)) {
                if (!empty($first['id'])) {
                    $firstPositionId = (int) $first['id'];
                } elseif (!empty($first['ugc_id'])) {
                    $firstPositionId = (int) $first['ugc_id'];
                }
                if (!empty($first['postName'])) {
                    $firstPostName = $first['postName'];
                } elseif (!empty($first['post_name'])) {
                    $firstPostName = $first['post_name'];
                }
            } elseif (is_object($first)) {
                if (!empty($first->id)) {
                    $firstPositionId = (int) $first->id;
                } elseif (!empty($first->ugc_id)) {
                    $firstPositionId = (int) $first->ugc_id;
                }
                if (!empty($first->postName)) {
                    $firstPostName = $first->postName;
                } elseif (!empty($first->post_name)) {
                    $firstPostName = $first->post_name;
                }
            }
        }

        if ($firstPositionId === 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'error'   => 'Cannot test employee create: no EmployeePositions available from API or local cache.',
                    'hint'    => 'Visit admin/staff/reseed_positions then retry, or seed ugc_employee_positions manually.',
                )));
        }

        $payload = array(
            'firstName'             => 'HEMIS',
            'middleName'            => 'TEST',
            'lastName'              => 'EMPLOYEE',
            'phoneNumber'           => '9800000001',
            'gender'                => 'male',
            'email'                 => 'hemistest@test.com',
            'ethnicity'             => 'Brahman',
            'campusId'              => $campusId,
            'teachingFacultyName'   => 'General',
            'employeeType'          => 'Other',
            'citizenshipNo'         => 'TEST-' . time(),
            'dateOFBirth'           => '2050/01/01',
            'dateOFBirthAd'         => '1993-04-14',
            'employeePositionId'    => $firstPositionId,
            'postName'              => $firstPostName,
            'positionType'          => 'Other',
            'salutation'            => 'Mr.',
            'pProvince'             => 'Bagmati',
            'pDistrict'             => 'Kathmandu',
            'pLocalLevel'           => 'Kathmandu Metropolitan City',
            'pWardNo'               => 1,
            'tProvince'             => 'Bagmati',
            'tDistrict'             => 'Kathmandu',
            'tLocalLevel'           => 'Kathmandu Metropolitan City',
            'tWardNo'               => 1,
            'position'              => 'Nonteaching',
            'joiningType'           => 'permanent',
            'joiningDate'           => '2080/01/01',
            'institutionId'         => $campusId,
            'graduatedFrom'         => 'Test School',
            'facultyName'           => 'General',
            'programName'           => 'TSLC',
            'bankAc'                => '00000000001',
            'bankName'              => 'Test Bank',
            'bankBranch'            => 'Test Branch',
            'panNo'                 => '999999999',
            'departmentId'          => null,
            'sectionId'             => null,
            'isSameAsPermanent'     => true,
            'religion'              => 'Hinduism',
            'fiscalYearId'          => null,
        );

        $result = $this->hemis_client->diagnostic_employee_multipart_post($payload);
        $out = array(
            'payload_sent'      => $payload,
            'http_code'         => $result['http_code'],
            'raw_response'      => isset($result['raw_response']) ? $result['raw_response'] : '',
            'parsed_hemis_id'   => isset($result['parsed_hemis_id']) ? $result['parsed_hemis_id'] : null,
            'success'           => !empty($result['success']),
            'error'             => isset($result['error']) ? $result['error'] : null,
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    // TEMPORARY: remove before production
    public function hemis_test_patch_employee($hemis_id = null)
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }
        if ($hemis_id === null || $hemis_id === '') {
            $hemis_id = $this->uri->segment(4);
        }
        $hemis_id = (int) $hemis_id;
        $this->load->model('hemis_integration_model');
        $this->load->library('hemis_client');
        $cfg = $this->hemis_integration_model->get_effective_config();
        if (!is_array($cfg)) {
            $cfg = array();
        }
        $campusId = isset($cfg['remote_college_id']) ? (int) $cfg['remote_college_id'] : 0;

        $pos = $this->hemis_client->hemis_get_employee_positions();
        $posList = (!empty($pos['ok']) && isset($pos['data']) && is_array($pos['data'])) ? $pos['data'] : array();
        $firstPid = (isset($posList[0]['id']) && (int) $posList[0]['id'] > 0) ? (int) $posList[0]['id'] : 0;

        $patchPayload = array(
            'id'                    => $hemis_id,
            'firstName'             => 'HEMIS',
            'middleName'            => 'UPDATED',
            'lastName'              => 'EMPLOYEE',
            'phoneNumber'           => '9800000002',
            'gender'                => 'male',
            'email'                 => 'hemistest@test.com',
            'ethnicity'             => 'Brahman',
            'campusId'              => $campusId,
            'employeeType'          => 'Other',
            'citizenshipNo'         => 'TEST-PATCH',
            'dateOFBirth'           => '2050/01/01',
            'dateOFBirthAd'         => '1993-04-14',
            'employeePositionId'    => $firstPid > 0 ? $firstPid : 1,
            'postName'              => 'Peon',
            'positionType'          => 'Other',
            'salutation'            => 'Mr.',
            'pProvince'             => 'Bagmati',
            'pDistrict'             => 'Kathmandu',
            'pLocalLevel'           => 'Kathmandu Metropolitan City',
            'pWardNo'               => 1,
            'position'              => 'Nonteaching',
            'joiningType'           => 'permanent',
            'joiningDate'           => '2080/01/01',
            'institutionId'         => $campusId,
            'isSameAsPermanent'     => true,
            'religion'              => 'Hinduism',
        );

        $result = $this->hemis_client->diagnostic_employee_multipart_patch($hemis_id, $patchPayload);
        $out = array(
            'patch_url'     => '/api/Employee/' . $hemis_id,
            'http_code'     => $result['http_code'],
            'raw_response'  => isset($result['raw_response']) ? $result['raw_response'] : '',
            'success'       => !empty($result['success']),
            'error'         => isset($result['error']) ? $result['error'] : null,
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($out));
    }

    private function getOrCreateDesignation($designation_name) {
        // First try to get existing designation ID
        $designation_id = $this->staff_model->getDesignationIdByName($designation_name);
        
        if ($designation_id) {
            return $designation_id;
        }
        
        // If not found, create new designation
        $designation_data = [
            'designation' => $designation_name,
            'is_active' => 1
        ];
        
        return $this->staff_model->addDesignation($designation_data);
    }

    public function check_email_exists($email, $id)
    {

       if (!empty($id)) {
       $this->db->where('id !=', $id);
    }
       $this->db->where('email', $email);
       $query = $this->db->get('staff');

       if ($query->num_rows() > 0) {
       $this->form_validation->set_message('check_email_exists', 'The {field} already exists.');
       return FALSE;
    }
       return TRUE;
    }


      public function check_employeeid_exists($employee_id, $id)
     {
      
          if (!empty($id)) {
          $this->db->where('id !=', $id);
       }
          $this->db->where('employee_id', $employee_id);
          $query = $this->db->get('staff');

          if ($query->num_rows() > 0) {
          $this->form_validation->set_message('check_employeeid_exists', 'The {field} already exists.');
          return FALSE;
       }
          return TRUE;
       }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_delete')) {
            access_denied();
        }

        $a           = 0;
        $sessionData = $this->session->userdata('admin');
        $userdata    = $this->customlib->getUserData();
        $staff       = $this->staff_model->get($id);

        if ($staff['id'] == $userdata['id']) {
            $a = 1;
        } else if ($staff["role_id"] == 7) {
            $a = 1;
        }

        if ($a == 1) {
            access_denied();
        }
        $data['title'] = 'Staff List';
        $this->staff_model->remove($id);
        redirect('admin/staff');
    }

    public function disablestaff($id)
    {
        if (!$this->rbac->hasPrivilege('disable_staff', 'can_view')) {
            access_denied();
        }

        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'date' => form_error('date'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');

            echo json_encode($array);

        } else {

            $a           = 0;
            $sessionData = $this->session->userdata('admin');
            $userdata    = $this->customlib->getUserData();
            $staff       = $this->staff_model->get($id);
            if ($staff["role_id"] == 7) {
                $a = 0;
                if ($userdata["email"] == $staff["email"]) {
                    $a = 1;
                }
            } else {
                $a = 1;
            }

            if ($a != 1) {
                access_denied();
            }

            if (isset($_POST['date'])) {
                $data = array('id' => $id, 'disable_at' => date('Y-m-d', $this->customlib->datetostrtotime($_POST['date'])), 'is_active' => 0);
            } else {
                $data = array('id' => $id, 'is_active' => 0);
            }

            $this->staff_model->disablestaff($data);

            if (isset($_POST['date'])) {
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
                echo json_encode($array);
            } else {
                redirect('admin/staff/profile/' . $id);
            }
        }
    }

    public function enablestaff($id)
    {
        $a           = 0;
        $sessionData = $this->session->userdata('admin');
        $userdata    = $this->customlib->getUserData();
        $staff       = $this->staff_model->get($id);
        if ($staff["role_id"] == 7) {
            $a = 0;
            if ($userdata["email"] == $staff["email"]) {
                $a = 1;
            }
        } else {
            $a = 1;
        }

        if ($a != 1) {
            access_denied();
        }
        $this->staff_model->enablestaff($id);
        redirect('admin/staff/profile/' . $id);
    }

    public function staffLeaveSummary()
    {
        $resultdata         = $this->staff_model->getLeaveSummary();
        $data["resultdata"] = $resultdata;
        $this->load->view("layout/header");
        $this->load->view("admin/staff/staff_leave_summary", $data);
        $this->load->view("layout/footer");
    }

    public function getEmployeeByRole()
    {
        $role = $this->input->post("role");
        $data = $this->staff_model->getEmployee($role);
        echo json_encode($data);
    }

    public function dateDifference($date_1, $date_2, $differenceFormat = '%a')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval  = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat) + 1;
    }

    public function permission($id)
    {
        $data['title']          = 'Add Role';
        $data['id']             = $id;
        $staff                  = $this->staff_model->get($id);
        $data['staff']          = $staff;
        $userpermission         = $this->userpermission_model->getUserPermission($id);
        $data['userpermission'] = $userpermission;

        if ($this->input->server('REQUEST_METHOD') == "POST") {
            $staff_id   = $this->input->post('staff_id');
            $prev_array = $this->input->post('prev_array');
            if (!isset($prev_array)) {
                $prev_array = array();
            }
            $module_perm  = $this->input->post('module_perm');
            $delete_array = array_diff($prev_array, $module_perm);
            $insert_diff  = array_diff($module_perm, $prev_array);
            $insert_array = array();
            if (!empty($insert_diff)) {

                foreach ($insert_diff as $key => $value) {
                    $insert_array[] = array(
                        'staff_id'      => $staff_id,
                        'permission_id' => $value,
                    );
                }
            }

            $this->userpermission_model->getInsertBatch($insert_array, $staff_id, $delete_array);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/staff');
        }

        $this->load->view('layout/header');
        $this->load->view('admin/staff/permission', $data);
        $this->load->view('layout/footer');
    }

    public function leaverequest()
    {
        if (!$this->rbac->hasPrivilege('apply_leave', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staff/leaverequest');
        $userdata                   = $this->customlib->getUserData();
        $leave_request              = $this->leaverequest_model->user_leave_request($userdata["id"]);
        $data["leave_request"]      = $leave_request;
        $LeaveTypes                 = $this->leaverequest_model->allotedLeaveType($userdata["id"]);
        $data["staff_id"]           = $userdata["id"];
        $data["leavetype"]          = $LeaveTypes;
        $staffRole                  = $this->staff_model->getStaffRole();
        $data["staffrole"]          = $staffRole;
        $data["status"]             = $this->status;
        $data["superadmin_visible"] = $this->customlib->superadmin_visible();
        $data["user_role"]          = $userdata['role_id'];
        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/leaverequest", $data);
        $this->load->view("layout/footer", $data);
    }

    public function change_password($id)
    {
        $sessionData = $this->session->userdata('admin');
        $userdata    = $this->customlib->getUserData();
        $this->form_validation->set_rules('new_pass', $this->lang->line('new_password'), 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', $this->lang->line('confirm_password'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

            $msg = array(
                'new_pass'     => form_error('new_pass'),
                'confirm_pass' => form_error('confirm_pass'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            if (!empty($id)) {
                $newdata = array(
                    'id'       => $id,
                    'password' => $this->enc_lib->passHashEnc($this->input->post('new_pass')),
                );

                $query2 = $this->admin_model->saveNewPass($newdata);
                if ($query2) {
                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('password_changed_successfully'));
                } else {
                    $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('password_not_changed'));
                }
            } else {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('password_not_changed'));
            }
        }

        echo json_encode($array);
    }

    public function import()
    {
        $data['field'] = array(
            "employee_id"                  => "employee_id",
            "name"                         => "first_name",
            "surname"                      => "last_name",
            "firstnameNp"                  => "firstnameNp",
            "middlenameNp"                 => "middlenameNp",
            "lastnameNp"                   => "lastnameNp",
            "father_name"                  => "father_name",
            "mother_name"                  => "mother_name",
            "email"                        => "email",
            "gender"                       => "gender",
            "dob"                          => "date_of_birth",
            "dob_bs"                       => "date_of_birth_bs",
            "date_of_joining"              => "date_of_joining",
            "date_of_leaving"              => "date_of_leaving",
            "contact_no"                   => "phone",
            "emergency_contact_no"         => "emergency_contact_number",
            "marital_status"               => "marital_status",
            "qualification"                => "qualification",
            "work_exp"                     => "work_experience",
            "note"                         => "note",
            "account_title"                => "account_title",
            "bank_account_no"              => "bank_account_no",
            "bank_name"                    => "bank_name",
            "ifsc_code"                    => "ifsc_code",
            "bank_branch"                  => "bank_branch",
            "payscale"                     => "payscale",
            "basic_salary"                 => "basic_salary",
            "epf_no"                       => "epf_no",
            "contract_type"                => "contract_type",
            "shift"                        => "shift",
            // Address (CSV columns permanent_* / temporary_* = UGC province/district/local level labels)
            "local_address"                => "current_address",
            "permanent_address"            => "permanent_address",
            "permanent_province"           => "permanent_province",
            "permanent_district"           => "permanent_district",
            "permanent_local_level"        => "permanent_local_level",
            "permanent_ward_no"            => "permanent_ward_no",
            "permanent_house_no"           => "permanent_house_no",
            "temporary_district"           => "temporary_district",
            "temporary_province"           => "temporary_province",
            "temporary_local_level"        => "temporary_local_level",
            "temporary_ward_no"            => "temporary_ward_no",
            "previous_level_of_study"      => "previous_level_of_study",
            "previous_board_university_college" => "previous_board_university_college",
            "previous_registration_no"     => "previous_registration_no",
            "previous_institution_name"    => "previous_institution_name",
            "citizenship"                  => "citizenship",
            "nationalId"                   => "nationalId",
            "religion"                     => "religion",
            "caste"                        => "caste"
        );
    
        $roles               = $this->role_model->get();
        $data["roles"]       = $roles;
        $designation         = $this->staff_model->getStaffDesignation();
        $data["designation"] = $designation;
        $department          = $this->staff_model->getDepartment();
        $data["department"]  = $department;
    
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_csv_upload');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'required');
    
        if ($this->form_validation->run() == false) {
            $this->load->view("layout/header", $data);
            $this->load->view("admin/staff/import/import", $data);
            $this->load->view("layout/footer", $data);
        } else {
            $rowcount = 0;
            $totalRows = 0;
            $import_failures = array();
            $staff_csv_processed = false;

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $staff_csv_processed = true;
                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);
    
                    if (!empty($result)) {
                        foreach ($result as $r_key => $r_value) {
                            // Skip header row if it exists
                            if ($r_key == 0 && isset($result[$r_key]['employee_id']) && strtolower(trim((string) $result[$r_key]['employee_id'])) === 'employee_id') {
                                continue;
                            }
                            $totalRows++;

                            $file_row    = $r_key + 1;
                            $emp_label   = isset($result[$r_key]['employee_id']) ? trim((string) $result[$r_key]['employee_id']) : '';
                            $name_label  = isset($result[$r_key]['name']) ? trim((string) $result[$r_key]['name']) : '';
                            $email_label = isset($result[$r_key]['email']) ? trim((string) $result[$r_key]['email']) : '';

                            $check_exists      = $this->staff_model->import_check_data_exists($result[$r_key]['name'], $result[$r_key]['employee_id']);
                            $check_emailexists = $this->staff_model->import_check_email_exists($result[$r_key]['name'], $result[$r_key]['email']);

                            if ($check_exists == 0 && $check_emailexists == 0) {
                                $ugc_addr = $this->_staff_ugc_address_from_import_row($result[$r_key]);
    
                                // Normalize JSON-capable education history fields
                                $normalizeJson = function($raw) {
                                    if (!isset($raw) || $raw === '') {
                                        return '[]';
                                    }
                                    // Accept already valid JSON arrays/strings
                                    $decoded = json_decode($raw, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        // Ensure it's encoded back to a compact JSON string
                                        return json_encode($decoded);
                                    }
                                    // Fallback: wrap as array of single string
                                    return json_encode(array($raw));
                                };

                                $prev_level_json = $normalizeJson(isset($result[$r_key]['previous_level_of_study']) ? $this->encoding_lib->toUTF8($result[$r_key]['previous_level_of_study']) : '');
                                $prev_board_json = $normalizeJson(isset($result[$r_key]['previous_board_university_college']) ? $this->encoding_lib->toUTF8($result[$r_key]['previous_board_university_college']) : '');
                                $prev_regno_json = $normalizeJson(isset($result[$r_key]['previous_registration_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['previous_registration_no']) : '');
                                $prev_inst_json  = $normalizeJson(isset($result[$r_key]['previous_institution_name']) ? $this->encoding_lib->toUTF8($result[$r_key]['previous_institution_name']) : '');

                                // Normalize documents stored in JSON columns as JSON strings
                                $citizenship_json = null;
                                if (isset($result[$r_key]['citizenship']) && $result[$r_key]['citizenship'] !== '') {
                                    $citizenship_json = json_encode($this->encoding_lib->toUTF8($result[$r_key]['citizenship']));
                                }
                                $nationalId_json = null;
                                if (isset($result[$r_key]['nationalId']) && $result[$r_key]['nationalId'] !== '') {
                                    $nationalId_json = json_encode($this->encoding_lib->toUTF8($result[$r_key]['nationalId']));
                                }

                                // Basic fields - preserve encoding for all fields
                                $staff_data = array(
                                    'employee_id'          => isset($result[$r_key]['employee_id']) ? $this->encoding_lib->toUTF8($result[$r_key]['employee_id']) : '',
                                    'lang_id'              => 1, // Default value
                                    'currency_id'          => 0, // Default value
                                    'name'                 => isset($result[$r_key]['name']) ? $this->encoding_lib->toUTF8($result[$r_key]['name']) : '',
                                    'surname'              => isset($result[$r_key]['surname']) ? $this->encoding_lib->toUTF8($result[$r_key]['surname']) : '',
                                    'firstnameNp'          => isset($result[$r_key]['firstnameNp']) ? $this->encoding_lib->toUTF8($result[$r_key]['firstnameNp']) : '',
                                    'middlenameNp'         => isset($result[$r_key]['middlenameNp']) ? $this->encoding_lib->toUTF8($result[$r_key]['middlenameNp']) : null,
                                    'lastnameNp'           => isset($result[$r_key]['lastnameNp']) ? $this->encoding_lib->toUTF8($result[$r_key]['lastnameNp']) : '',
                                    'father_name'          => isset($result[$r_key]['father_name']) ? $this->encoding_lib->toUTF8($result[$r_key]['father_name']) : '',
                                    'mother_name'          => isset($result[$r_key]['mother_name']) ? $this->encoding_lib->toUTF8($result[$r_key]['mother_name']) : '',
                                    'contact_no'           => isset($result[$r_key]['contact_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['contact_no']) : '',
                                    'emergency_contact_no' => isset($result[$r_key]['emergency_contact_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['emergency_contact_no']) : '',
                                    'email'                => isset($result[$r_key]['email']) ? $this->encoding_lib->toUTF8($result[$r_key]['email']) : '',
                                    'dob'                  => isset($result[$r_key]['dob']) ? $this->encoding_lib->toUTF8($result[$r_key]['dob']) : null,
                                    'dob_bs'               => isset($result[$r_key]['dob_bs']) ? $this->encoding_lib->toUTF8($result[$r_key]['dob_bs']) : null,
                                    'marital_status'       => isset($result[$r_key]['marital_status']) ? $this->encoding_lib->toUTF8($result[$r_key]['marital_status']) : '',
                                    'date_of_joining'      => isset($result[$r_key]['date_of_joining']) ? $this->encoding_lib->toUTF8($result[$r_key]['date_of_joining']) : null,
                                    'date_of_leaving'      => isset($result[$r_key]['date_of_leaving']) && !empty($result[$r_key]['date_of_leaving']) ? $this->encoding_lib->toUTF8($result[$r_key]['date_of_leaving']) : null,
                                    'gender'               => isset($result[$r_key]['gender']) ? $this->encoding_lib->toUTF8($result[$r_key]['gender']) : '',
                                    'qualification'        => isset($result[$r_key]['qualification']) ? $this->encoding_lib->toUTF8($result[$r_key]['qualification']) : '',
                                    'work_exp'             => isset($result[$r_key]['work_exp']) ? $this->encoding_lib->toUTF8($result[$r_key]['work_exp']) : '',
                                    'note'                 => isset($result[$r_key]['note']) ? $this->encoding_lib->toUTF8($result[$r_key]['note']) : '',
                                   
                                    // Address (UGC/HEMIS text + ward; CSV columns permanent_* / temporary_* are UGC labels)
                                    'local_address'        => isset($result[$r_key]['local_address']) ? $this->encoding_lib->toUTF8($result[$r_key]['local_address']) : '',
                                    'permanent_address'    => isset($result[$r_key]['permanent_address']) ? $this->encoding_lib->toUTF8($result[$r_key]['permanent_address']) : '',
                                    'permanent_ward_no'      => isset($result[$r_key]['permanent_ward_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['permanent_ward_no']) : '',
                                    'permanent_house_no'     => isset($result[$r_key]['permanent_house_no']) && !empty($result[$r_key]['permanent_house_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['permanent_house_no']) : null,
                                    'temporary_ward_no'      => isset($result[$r_key]['temporary_ward_no']) && !empty($result[$r_key]['temporary_ward_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['temporary_ward_no']) : null,
                                    'is_same_as_permanent'   => $ugc_addr['is_same_as_permanent'],
                                    'ugc_p_province'         => $ugc_addr['ugc_p_province'],
                                    'ugc_p_district'         => $ugc_addr['ugc_p_district'],
                                    'ugc_p_local_level'      => $ugc_addr['ugc_p_local_level'],
                                    'ugc_p_combined_code'    => $ugc_addr['ugc_p_combined_code'],
                                    'ugc_t_province'         => $ugc_addr['ugc_t_province'],
                                    'ugc_t_district'         => $ugc_addr['ugc_t_district'],
                                    'ugc_t_local_level'      => $ugc_addr['ugc_t_local_level'],
                                    'ugc_t_combined_code'    => $ugc_addr['ugc_t_combined_code'],
                                   
                                    // Banking info
                                    'account_title'        => isset($result[$r_key]['account_title']) ? $this->encoding_lib->toUTF8($result[$r_key]['account_title']) : '',
                                    'bank_account_no'      => isset($result[$r_key]['bank_account_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['bank_account_no']) : '',
                                    'bank_name'            => isset($result[$r_key]['bank_name']) ? $this->encoding_lib->toUTF8($result[$r_key]['bank_name']) : '',
                                    'ifsc_code'            => isset($result[$r_key]['ifsc_code']) ? $this->encoding_lib->toUTF8($result[$r_key]['ifsc_code']) : '',
                                    'bank_branch'          => isset($result[$r_key]['bank_branch']) ? $this->encoding_lib->toUTF8($result[$r_key]['bank_branch']) : '',
                                   
                                    // Salary info
                                    'payscale'             => isset($result[$r_key]['payscale']) ? $this->encoding_lib->toUTF8($result[$r_key]['payscale']) : '',
                                    'basic_salary'         => isset($result[$r_key]['basic_salary']) && !empty($result[$r_key]['basic_salary']) ? (int)$result[$r_key]['basic_salary'] : null,
                                    'epf_no'               => isset($result[$r_key]['epf_no']) ? $this->encoding_lib->toUTF8($result[$r_key]['epf_no']) : '',
                                   
                                    // Work info
                                    'contract_type'        => isset($result[$r_key]['contract_type']) ? $this->encoding_lib->toUTF8($result[$r_key]['contract_type']) : '',
                                    'shift'                => isset($result[$r_key]['shift']) ? $this->encoding_lib->toUTF8($result[$r_key]['shift']) : '',
                                    'location'             => '', // Default empty value
                                   
                                    // Social media - default empty values
                                    'facebook'             => '',
                                    'twitter'              => '',
                                    'linkedin'             => '',
                                    'instagram'            => '',
                                   
                                    // Education history - ensured valid JSON strings
                                    'previous_level_of_study'      => $prev_level_json,
                                    'previous_board_university_college' => $prev_board_json,
                                    'previous_registration_no'     => $prev_regno_json,
                                    'previous_institution_name'    => $prev_inst_json,
                                   
                                    // ID documents - store as valid JSON strings
                                    'citizenship'           => $citizenship_json,
                                    'nationalId'            => $nationalId_json,
                                   
                                    // Religion and caste
                                    'religion'              => isset($result[$r_key]['religion']) ? $this->encoding_lib->toUTF8($result[$r_key]['religion']) : '',
                                    'caste'                 => isset($result[$r_key]['caste']) ? $this->encoding_lib->toUTF8($result[$r_key]['caste']) : '',
                                   
                                    // Documents - default empty values
                                    'resume'               => '',
                                    'joining_letter'       => '',
                                    'resignation_letter'   => '',
                                    'other_document_name'  => null,
                                    'other_document_file'  => null,
                                    'image'                => '', // Added missing image field
                                   
                                    // System fields
                                    'user_id'              => $this->input->post('role'),
                                    'designation'          => $this->input->post('designation'),
                                    'department'           => $this->input->post('department'),
                                    'is_active'            => 1,
                                    'verification_code'    => '' // Added missing verification_code field
                                );
    
                                $password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
    
                                $staff_data['password'] = $this->enc_lib->passHashEnc($password);
    
                                $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => 0);
    
                                $insert_id = $this->staff_model->batchInsert($staff_data, $role_array);
                                $staff_id  = $insert_id;
                                if ($staff_id) {
                                    $teacher_login_detail = array('id' => $staff_id, 'credential_for' => 'staff', 'username' => $staff_data['email'], 'password' => $password, 'contact_no' => $staff_data['contact_no'], 'email' => $staff_data['email']);
                                    $this->mailsmsconf->mailsms('login_credential', $teacher_login_detail);
                                    $rowcount++;
                                } else {
                                    $db_err = $this->db->error();
                                    $fail_reason = (isset($db_err['message']) && trim((string) $db_err['message']) !== '')
                                        ? $db_err['message']
                                        : $this->lang->line('staff_import_failed_save');
                                    $import_failures[] = array(
                                        'row' => $file_row,
                                        'employee_id' => $emp_label,
                                        'name' => $name_label,
                                        'email' => $email_label,
                                        'reason' => $fail_reason,
                                    );
                                }
                            } else {
                                if ($check_exists != 0) {
                                    $import_failures[] = array(
                                        'row' => $file_row,
                                        'employee_id' => $emp_label,
                                        'name' => $name_label,
                                        'email' => $email_label,
                                        'reason' => $this->lang->line('staff_import_duplicate_employee_id'),
                                    );
                                } elseif ($check_emailexists != 0) {
                                    $import_failures[] = array(
                                        'row' => $file_row,
                                        'employee_id' => $emp_label,
                                        'name' => $name_label,
                                        'email' => $email_label,
                                        'reason' => $this->lang->line('staff_import_duplicate_email'),
                                    );
                                }
                            }
                        } ///Result loop
                    } //Not empty
    
                    $successMessage = ($totalRows > 0 && $rowcount === $totalRows)
                        ? 'All records imported successfully.'
                        : ('Imported ' . $rowcount . ' record(s) successfully.');
                    $array = array('status' => 'success', 'error' => '', 'message' => $successMessage);
                } else {
                    $msg = array(
                        'e' => $this->lang->line('the_file_field_is_required'),
                    );
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                }
            } else {
                $msg = array(
                    'e' => $this->lang->line('the_file_field_is_required'),
                );
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }

            if ($staff_csv_processed) {
                $this->session->set_flashdata('staff_import_result', array(
                    'success_count' => $rowcount,
                    'total_rows' => $totalRows,
                    'failures' => $import_failures,
                ));
            } else {
                if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('please_upload_csv_file_only') . '</div>');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('the_file_field_is_required') . '</div>');
                }
            }
            redirect('admin/staff/import');
        }
    }
    
        public function handle_csv_upload()
        {
            $error = "";
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $allowedExts = array('csv');
                $mimes       = array('text/csv',
                    'text/plain',
                    'application/csv',
                    'text/comma-separated-values',
                    'application/excel',
                    'application/vnd.ms-excel',
                    'application/vnd.msexcel',
                    'text/anytext',
                    'application/octet-stream',
                    'application/txt');
                $temp      = explode(".", $_FILES["file"]["name"]);
                $extension = end($temp);
                if ($_FILES["file"]["error"] > 0) {
                    $error .= "Error opening the file<br />";
                }
                if (!in_array($_FILES['file']['type'], $mimes)) {
                    $error .= "Error opening the file<br />";
                    $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if (!in_array($extension, $allowedExts)) {
                    $error .= "Error opening the file<br />";
                    $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($error == "") {
                    return true;
                }
            } else {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
                return false;
            }
        }
    
    
        public function exportformat()
        {
            $this->load->helper('download');
            $filepath = "./backend/import/staff_csvfile.csv";
    
    
            $data = file_get_contents($filepath);
            if (substr($data, 0, 3) !== "\xEF\xBB\xBF") {
                $data = "\xEF\xBB\xBF" . $data;
            }
           
            $name = 'staff_csvfile.csv';
            force_download($name, $data);
        }
    
    

    public function rating()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/rating');
        $this->load->view('layout/header');
        $staff_list         = $this->staff_model->getrat();
        $data['resultlist'] = $staff_list;
        $this->load->view('admin/staff/rating', $data);
        $this->load->view('layout/footer');
    }

    public function ratingapr($id)
    {
        $approve['status'] = '1';
        $this->staff_model->ratingapr($id, $approve);
        redirect('admin/staff/rating');
    }

    public function delete_rateing($id)
    {
        $this->staff_model->rating_remove($id);
        redirect('admin/staff/rating');
    }

    public function getDesignations()
    {
        $staff_type = $this->input->post('staff_type');
        
        // Get designations from database based on staff type
        $this->db->select('id, name');
        $this->db->from('staff_designation');
        $this->db->where('staff_type', $staff_type);
        $query = $this->db->get();
        
        $designations = $query->result_array();
        
        echo json_encode($designations);
    }

    // Add this helper for staff image upload
    private function set_upload_options($file_name)
    {
        $config = array();
        $config['upload_path']   = './uploads/staff_images/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size']      = 2048; // 2MB
        $config['overwrite']     = true;
        $config['file_name']     = $file_name;
        return $config;
    }

}