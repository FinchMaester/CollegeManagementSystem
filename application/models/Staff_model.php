<?php
class Staff_model extends MY_Model
{
    /** @var bool Ensure staff HEMIS columns once per HTTP request (avoids crashes before Staff controller runs). */
    protected static $staff_hemis_schema_ensured = false;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('customlib');
        $this->superadmin_visible = $this->customlib->superadmin_visible();
        $getStaffRole = $this->customlib->getStaffRole();
        if (isset($getStaffRole)) {
            $this->staffrole = json_decode($getStaffRole);
        }
        $this->load->database();
        $this->_ensure_staff_hemis_schema_columns();
    }

    /**
     * Append-only ALTERs so Staff_model::get() SELECT matches DB on installs without migrations run.
     * UGC address strings use TEXT (not VARCHAR) — staff row is near MySQL 65535 byte limit.
     */
    protected function _ensure_staff_hemis_schema_columns()
    {
        if (self::$staff_hemis_schema_ensured) {
            return;
        }
        self::$staff_hemis_schema_ensured = true;

        if (!$this->db->table_exists('staff')) {
            return;
        }

        if (!$this->db->field_exists('citizenship_no', 'staff')) {
            $this->_staff_schema_add_column('citizenship_no', "VARCHAR(64) NULL");
        }
        if (!$this->db->field_exists('graduated_from', 'staff')) {
            $this->_staff_schema_add_column('graduated_from', 'TEXT NULL');
        }
        if (!$this->db->field_exists('hemis_employee_id', 'staff')) {
            $this->_staff_schema_add_column('hemis_employee_id', 'INT NULL');
        }
        if (!$this->db->field_exists('hemis_sync_at', 'staff')) {
            $this->_staff_schema_add_column('hemis_sync_at', 'DATETIME NULL');
        }
        if (!$this->db->field_exists('hemis_sync_error', 'staff')) {
            $this->_staff_schema_add_column('hemis_sync_error', 'VARCHAR(500) NULL');
        }
        if (!$this->db->field_exists('hemis_sync_error_at', 'staff')) {
            $this->_staff_schema_add_column('hemis_sync_error_at', 'DATETIME NULL');
        }
        if (!$this->db->field_exists('ugc_department_id', 'staff')) {
            $this->_staff_schema_add_column('ugc_department_id', 'INT NULL');
        }
        if (!$this->db->field_exists('ugc_section_id', 'staff')) {
            $this->_staff_schema_add_column('ugc_section_id', 'INT NULL');
        }
        if (!$this->db->field_exists('ugc_fiscal_year_id', 'staff')) {
            $this->_staff_schema_add_column('ugc_fiscal_year_id', 'INT NULL');
        }
        if (!$this->db->field_exists('fiscal_year_id', 'staff')) {
            $this->_staff_schema_add_column('fiscal_year_id', 'INT NULL');
        }

        $ugc_addr_cols = array(
            'ugc_p_province'      => 'TEXT NULL',
            'ugc_p_district'      => 'TEXT NULL',
            'ugc_p_local_level'   => 'TEXT NULL',
            'ugc_p_combined_code' => 'VARCHAR(20) NULL',
            'ugc_t_province'      => 'TEXT NULL',
            'ugc_t_district'      => 'TEXT NULL',
            'ugc_t_local_level'   => 'TEXT NULL',
            'ugc_t_combined_code' => 'VARCHAR(20) NULL',
        );
        foreach ($ugc_addr_cols as $col => $def) {
            if (!$this->db->field_exists($col, 'staff')) {
                $this->_staff_schema_add_column($col, $def);
            }
        }
    }

    /**
     * Run ALTER ADD COLUMN without breaking the app when row-size or duplicate errors occur.
     */
    protected function _staff_schema_add_column($col, $definition)
    {
        $col = preg_replace('/[^a-z0-9_]/i', '', (string) $col);
        if ($col === '' || !$this->db->table_exists('staff') || $this->db->field_exists($col, 'staff')) {
            return false;
        }
        try {
            $this->db->query("ALTER TABLE `staff` ADD COLUMN `{$col}` {$definition}");
            return true;
        } catch (Throwable $e) {
            log_message('error', 'Staff schema: could not add column ' . $col . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Comma-prefixed SELECT fragments for optional staff columns (skip if ALTER failed).
     */
    protected function _staff_optional_select_columns(array $columns)
    {
        $parts = array();
        foreach ($columns as $col) {
            if ($this->db->field_exists($col, 'staff')) {
                $parts[] = 'staff.' . $col;
            }
        }
        return empty($parts) ? '' : (",\n        " . implode(",\n        ", $parts));
    }








    public function get($id = null)
    {
        $optional_hemis = $this->_staff_optional_select_columns(array(
            'citizenship_no', 'graduated_from', 'ugc_department_id', 'ugc_section_id',
            'ugc_p_province', 'ugc_p_district', 'ugc_p_local_level', 'ugc_p_combined_code',
            'ugc_t_province', 'ugc_t_district', 'ugc_t_local_level', 'ugc_t_combined_code',
            'hemis_employee_id', 'ugc_fiscal_year_id', 'fiscal_year_id',
        ));

        $this->db->select('
        staff.id,
        staff.employee_id,
        staff.lang_id,
        staff.currency_id,
        staff.department,
        staff.designation,
        staff.qualification,
        staff.work_exp,
        staff.name,
        staff.firstnameNp,
        staff.surname,
        staff.middle_name,
        staff.middlenameNp,
        staff.lastnameNp,
        staff.father_name,
        staff.mother_name,
        staff.contact_no,
        staff.emergency_contact_no,
        staff.email,
        staff.dob,
        staff.dob_bs,
        staff.marital_status,
        staff.date_of_joining,
        staff.date_of_leaving,
        staff.local_address,
        staff.permanent_address,
        staff.note,
        staff.image,
        staff.password,
        staff.gender,
        staff.account_title,
        staff.bank_account_no,
        staff.bank_name,
        staff.ifsc_code,
        staff.bank_branch,
        staff.payscale,
        staff.basic_salary,
        staff.epf_no,
        staff.contract_type,
        staff.shift,
        staff.location,
        staff.facebook,
        staff.twitter,
        staff.linkedin,
        staff.instagram,
        staff.resume,
        staff.joining_letter,
        staff.resignation_letter,
        staff.other_document_name,
        staff.other_document_file,
        staff.permanent_ward_no,
        staff.permanent_house_no,
        staff.permanent_tole,
        staff.temporary_ward_no,
        staff.temporary_tole,
        staff.previous_level_of_study,
        staff.previous_board_university_college,
        staff.previous_registration_no,
        staff.previous_institution_name,
        staff.citizenship,
        staff.nationalId,
        staff.religion,
        staff.caste,
        staff.user_id,
        staff.is_active,
        staff.verification_code,
        staff.disable_at,
        staff.non_teaching_type,
        staff.teaching_faculty_name,
        staff.employee_type,
        staff.org_id,
        staff.employee_position_id,
        staff.post_name,
        staff.position_type,
        staff.salutation,
        staff.ctz_issue_district_code,
        staff.nid_no,
        staff.citizenship_front,
        staff.citizenship_back,
        staff.nid_page,
        staff.pp_size_photo,
        staff.reference_letter,
        staff.other_letter,
        staff.other_image,
        staff.institution_id,
        staff.faculty_name,
        staff.level_name,
        staff.program_name,
        staff.enrolled_year,
        staff.passed_year,
        staff.certificate_copy,
        staff.transcript_copy,
        staff.marksheet_copy,
        staff.college_name,
        staff.campus_name,
        staff.pan_no,
        staff.section_id' . $optional_hemis . ',
        staff.is_same_as_permanent,
        staff.group_name,
        staff.sub_group,
        staff.position,
        staff.work_phone,
        staff.work_email,
        staff_designation.designation as designation_name,
        languages.language,
        languages.is_rtl,
        roles.name as user_type,
        roles.id as role_id
        ')
        ->from('staff')
        ->join('staff_roles', 'staff_roles.staff_id = staff.id', 'left')
        ->join('roles', 'staff_roles.role_id = roles.id', 'left')
        ->join('staff_designation', 'staff_designation.id = staff.designation', 'left')
        ->join('languages', 'languages.id = staff.lang_id', 'left');
   
        if ($this->session->has_userdata('admin')) {
            if ($this->staffrole->id != 7) {
                if ($this->superadmin_visible == 'disabled') {
                    $this->db->where("roles.id !=", 7);            
                }
            }
        }
   
        if ($id != null) {
            $this->db->where('staff.id', $id);
        } else {
            $this->db->where('staff.is_active', 1);
            $this->db->order_by('staff.id');
        }
   
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }
   
































    public function getrat()
    {
































        $this->db->select('staff.id,staff.employee_id,CONCAT_WS(" ",staff.name,staff.surname,"(",staff.employee_id,")") as name,roles.name as user_type,roles.id as role_id,staff_rating.rate,staff_rating.status,staff_rating.comment,staff_rating.id as rate_id,CONCAT_WS(" ",students.firstname,students.middlename,students.lastname,"(",students.admission_no,")") as student_name')->from('staff')->join("staff_roles", "staff_roles.staff_id = staff.id", "left")->join("roles", "staff_roles.role_id = roles.id", "left")->join("staff_rating", "staff_rating.staff_id = staff.id", "inner")->join("users", "users.id=staff_rating.user_id", "left")->join("students", "students.id=users.user_id", "left");
        $this->db->where('staff.is_active', 1);
        $this->db->where_not_in('roles.id', 7);
































        $this->db->order_by('staff.id');
        $query = $this->db->get();
        return $query->result_array();
































    }
































    public function getTodayDayAttendance()
    {
































        $date = date('Y-m-d');
































        $this->db->select('staff_id');
        $this->db->from("staff_attendance");
        $this->db->where('date = ', $date);
        $this->db->where("(staff_attendance_type_id='1' OR staff_attendance_type_id='2' OR staff_attendance_type_id='4')");
        $this->db->group_by('staff_attendance.staff_id');
        $query = $this->db->get();
        $q     = $query->result_array();
        return count($q);
    }
































    public function getTotalStaff()
    {
        $this->db->select('staff.id');
        $this->db->from("staff");
        $this->db->where("staff.is_active", 1);
        $this->db->where("staff.designation IS NOT NULL");
        $query = $this->db->get();
        $q = $query->result_array();
        return count($q);
    }



    public function user_reviewlist($id)
    {
        $this->db->select('staff_rating.rate,staff_rating.comment,staff_rating.role,students.firstname as firstname,students.middlename, students.lastname as lastname,students.guardian_name')->from('staff_rating')->join("users", "users.id = staff_rating.user_id", "inner")->join("staff", "staff_rating.staff_id = staff.id", "inner")->join("students", "students.id = staff_rating.user_id", "left");
        $this->db->where('staff.is_active', 1);
        $this->db->where('staff_rating.staff_id', $id);
        $this->db->where('staff_rating.status', 1);
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function getAll($id = null, $is_active = null)
    {
































        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");
































        if ($id != null) {
            $this->db->where('staff.id', $id);
        } else {
            if ($is_active != null) {
































                $this->db->where('staff.is_active', $is_active);
            }
            $this->db->order_by('staff.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }
































    public function getAll_users($id = null, $is_active = null)
    {
































        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");
































        if ($id != null) {
            $this->db->where('staff.id', $id);
        } else {
            if ($is_active != null) {
































                $this->db->where('staff.is_active', $is_active);
            }
            $this->db->where('roles.id!=', 7);
            $this->db->order_by('staff.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }
































    public function getBirthDayStaff($dob, $is_active = 1, $email = false, $contact_no = false)
    {
































        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");
































        $this->db->where('staff.is_active', $is_active);
        $this->db->where("DATE_FORMAT(staff.dob,'%m-%d') = DATE_FORMAT('" . $dob . "','%m-%d')");
        if ($email) {
            $this->db->where('staff.email !=', "");
        }
        if ($contact_no) {
            $this->db->where('staff.contact_no !=', "");
        }
        $this->db->order_by('staff.id');
































        $query = $this->db->get();
































        return $query->result_array();
    }
































    /** @var string Last MySQL error from staff insert/update (for admin diagnostics). */
    protected $last_staff_db_error = '';

    /** @var array Column keys removed before insert (server schema behind code). */
    protected $last_staff_dropped_columns = array();

    /** @var int Last insert_id from staff insert attempt. */
    protected $last_staff_insert_id = 0;

    /**
     * @return string
     */
    public function get_last_staff_db_error()
    {
        return $this->last_staff_db_error;
    }

    /**
     * @return array
     */
    public function get_last_staff_dropped_columns()
    {
        return $this->last_staff_dropped_columns;
    }

    /**
     * Human-readable failure summary for staff create UI / logs.
     *
     * @param array $source_data Original payload before column filter (optional).
     * @return string
     */
    public function describe_insert_failure(array $source_data = array())
    {
        $parts = array();

        if ($this->last_staff_db_error !== '') {
            $parts[] = 'Database: ' . $this->last_staff_db_error;
        } else {
            $err = $this->db->error();
            $msg = isset($err['message']) ? trim((string) $err['message']) : '';
            $code = isset($err['code']) ? (int) $err['code'] : 0;
            if ($msg !== '' && $code !== 0) {
                $parts[] = 'Database: ' . $msg . ' (#' . $code . ')';
            }
        }

        if ($this->last_staff_insert_id > 0) {
            $parts[] = 'insert_id=' . $this->last_staff_insert_id . ' (row may have been created before a later step failed)';
        } else {
            $parts[] = 'insert_id=0 (staff row was not created)';
        }

        $parts[] = 'transaction=' . ($this->db->trans_status() ? 'ok' : 'failed');

        $last_sql = trim((string) $this->db->last_query());
        if ($last_sql !== '') {
            if (strlen($last_sql) > 500) {
                $last_sql = substr($last_sql, 0, 500) . '…';
            }
            $parts[] = 'Last SQL: ' . $last_sql;
        }

        if (!empty($this->last_staff_dropped_columns)) {
            $parts[] = 'Columns not on server DB (skipped): ' . implode(', ', $this->last_staff_dropped_columns);
        }

        if (!empty($source_data) && $this->db->table_exists('staff')) {
            $missing = $this->find_staff_not_null_gaps($source_data);
            if (!empty($missing)) {
                $parts[] = 'Likely missing required columns: ' . implode(', ', $missing);
            }
        }

        if (empty($parts)) {
            return 'Failed to insert staff data (no database error message was returned).';
        }

        return 'Failed to insert staff data. ' . implode(' | ', $parts);
    }

    /**
     * @param array $data
     * @return array
     */
    public function find_staff_not_null_gaps(array $data)
    {
        if (!$this->db->table_exists('staff')) {
            return array();
        }

        $gaps = array();
        $fields = $this->db->field_data('staff');
        foreach ($fields as $field) {
            $name = $field->name;
            if ($name === 'id') {
                continue;
            }
            if (isset($field->primary_key) && (int) $field->primary_key === 1) {
                continue;
            }
            $nullable = true;
            if (isset($field->nullable)) {
                $nullable = ($field->nullable === true || $field->nullable === 'YES' || $field->nullable === 1);
            }
            if ($nullable) {
                continue;
            }
            if (!array_key_exists($name, $data)) {
                $gaps[] = $name;
                continue;
            }
            if ($data[$name] === null) {
                $gaps[] = $name . ' (null)';
            }
        }

        return $gaps;
    }

    /**
     * @param string $context
     * @return void
     */
    protected function _staff_capture_db_error($context = '')
    {
        $err = $this->db->error();
        $msg = isset($err['message']) ? trim((string) $err['message']) : '';
        $code = isset($err['code']) ? (int) $err['code'] : 0;
        if ($msg === '' || $code === 0) {
            return;
        }
        $line = ($context !== '' ? $context . ': ' : '') . $msg . ' (#' . $code . ')';
        $this->last_staff_db_error = ($this->last_staff_db_error === '')
            ? $line
            : $this->last_staff_db_error . '; ' . $line;
    }

    /**
     * Drop keys that are not real staff table columns (server DB may lag behind code).
     *
     * @param array $data
     * @return array
     */
    public function filter_row_to_existing_columns(array $data)
    {
        if (!$this->db->table_exists('staff')) {
            $this->last_staff_dropped_columns = array();
            return $data;
        }
        static $field_map = null;
        if ($field_map === null) {
            $field_map = array_flip($this->db->list_fields('staff'));
        }
        $out = array();
        $dropped = array();
        foreach ($data as $key => $value) {
            if (isset($field_map[$key])) {
                $out[$key] = $value;
            } else {
                $dropped[] = $key;
            }
        }
        $this->last_staff_dropped_columns = $dropped;
        return $out;
    }

    /**
     * Fill missing NOT NULL staff columns (server strict mode / legacy schema).
     *
     * @param array $data
     * @return array
     */
    public function apply_staff_row_defaults(array $data)
    {
        if (!$this->db->table_exists('staff')) {
            return $data;
        }

        $scalar_defaults = array(
            'lang_id'                 => 0,
            'currency_id'             => 0,
            'user_id'                 => 0,
            'note'                    => '',
            'image'                   => '',
            'resume'                  => '',
            'joining_letter'          => '',
            'resignation_letter'      => '',
            'permanent_district'      => '',
            'permanent_province'      => '',
            'permanent_local_level'   => '',
        );
        foreach ($scalar_defaults as $col => $default) {
            if ($this->db->field_exists($col, 'staff') && !array_key_exists($col, $data)) {
                $data[$col] = $default;
            }
        }

        $json_defaults = array(
            'previous_level_of_study',
            'previous_board_university_college',
            'previous_registration_no',
            'previous_institution_name',
        );
        foreach ($json_defaults as $col) {
            if (!$this->db->field_exists($col, 'staff')) {
                continue;
            }
            if (!array_key_exists($col, $data) || $data[$col] === null || $data[$col] === '') {
                $data[$col] = '[]';
            }
        }

        return $data;
    }

    public function add($data)
    {
        $this->last_staff_db_error = '';
        $this->last_staff_dropped_columns = array();
        $this->last_staff_insert_id = 0;

        $source_data = $data;
        $data = $this->filter_row_to_existing_columns($data);
        $data = $this->apply_staff_row_defaults($data);

        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $pending_audit = null;
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('staff', $data);
            $this->_staff_capture_db_error('staff update');
            $message = UPDATE_RECORD_CONSTANT . " On staff id " . $data['id'];
            $action = "Update";
            $record_id = $id = $data['id'];
            $pending_audit = array('message' => $message, 'record_id' => $record_id, 'action' => $action);
        } else {
            $this->db->insert('staff', $data);
            $id = (int) $this->db->insert_id();
            $this->last_staff_insert_id = $id;
            $this->_staff_capture_db_error('staff insert');
            $message = INSERT_RECORD_CONSTANT . " On staff id " . $id;
            $action = "Insert";
            $record_id = $id;
            if ($id > 0) {
                $pending_audit = array('message' => $message, 'record_id' => $record_id, 'action' => $action);
            }
        }
        //======================Code End==============================
































        $this->db->trans_complete(); # Completing transaction
        /*Optional*/
































        if ($pending_audit !== null && $this->db->trans_status() !== false) {
            $this->log($pending_audit['message'], $pending_audit['record_id'], $pending_audit['action']);
            $this->_staff_capture_db_error('audit log');
        }

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            $this->_staff_capture_db_error('transaction');
            if ($this->last_staff_db_error === '') {
                $this->last_staff_db_error = 'Database transaction failed after staff insert.';
            }
            log_message('error', 'Staff add() transaction failed: ' . $this->describe_insert_failure($data));
            return false;
        }

        if (!isset($source_data['id']) && (int) $id < 1) {
            if ($this->last_staff_db_error === '') {
                $this->last_staff_db_error = 'insert_id is 0 — staff row was not created.';
            }
            log_message('error', 'Staff add() no insert_id: ' . $this->describe_insert_failure($data));
            return false;
        }

        return $id;
    }
































    public function update($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $data['id']);
        $query = $this->db->update('staff', $data);
        $message = UPDATE_RECORD_CONSTANT . " On staff id " . $data['id'];
        $action = "Update";
        $record_id = $data['id'];
        $this->log($message, $record_id, $action);
        //======================Code End==============================


        $this->db->trans_complete(); # Completing transaction
        /*Optional*/




        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $query;
        }
    }
































































































   
































    public function getByVerificationCode($ver_code)
    {
        $condition = "verification_code =" . "'" . $ver_code . "'";
        $this->db->select('*');
        $this->db->from('staff');
        $this->db->where($condition);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }
































    public function batchInsert($data, $roles = array(), $leave_array = array(), $data_setting = array())
    {
































        $this->db->trans_start();
        $this->db->trans_strict(false);
































        $this->db->insert('staff', $data);
        $staff_id          = $this->db->insert_id();
        $roles['staff_id'] = $staff_id;
        $this->db->insert_batch('staff_roles', array($roles));
        if (!empty($data_setting)) {
            if ($data_setting['staffid_auto_insert']) {
                if ($data_setting['staffid_update_status'] == 0) {
                    $data_setting['staffid_update_status'] = 1;
                    $this->setting_model->add($data_setting);
                }
            }
        }
































        if (!empty($leave_array)) {
            foreach ($leave_array as $key => $value) {
                $leave_array[$key]['staff_id'] = $staff_id;
            }
































            $this->db->insert_batch('staff_leave_details', $leave_array);
        }
        $this->db->trans_complete();
































        if ($this->db->trans_status() === false) {
































            $this->db->trans_rollback();
            return false;
        } else {
































            $this->db->trans_commit();
            return $staff_id;
        }
    }
































    public function adddoc($data)
    {
































        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('staff_documents', $data);
        } else {
            $this->db->insert('staff_documents', $data);
            return $this->db->insert_id();
        }
    }
































    public function remove($id)
    {
































        $this->db->trans_start();
        $this->db->trans_strict(false);
        $sql   = "DELETE FROM custom_field_values WHERE id IN (select * from (SELECT t2.id as `id` FROM `custom_fields` INNER JOIN custom_field_values as t2 on t2.custom_field_id=custom_fields.id WHERE custom_fields.belong_to='staff' and t2.belong_table_id IN (" . $id . ")) as m2)";
        $query = $this->db->query($sql);
































        $this->db->where('id', $id);
        $this->db->delete('staff');
































        $this->db->trans_complete();
































        if ($this->db->trans_status() === false) {
































            $this->db->trans_rollback();
            return false;
        } else {
































            $this->db->trans_commit();
            return $staff_id;
        }
































    }
































    public function add_staff_leave_details($data2)
    {
































        if (isset($data2['id'])) {
            $update_id = $data2['id'];
            unset($data2['id']);
            $this->db->where('id', $update_id);
            $this->db->update('staff_leave_details', $data2);
        } else {
            $this->db->insert('staff_leave_details', $data2);
            return $this->db->insert_id();
        }
    }

    /**
     * Sync staff_leave_details from POST leave_alloc[leave_type_id] = days.
     * Blank removes the row; non-negative numbers insert or update.
     *
     * @param int   $staff_id
     * @param array|null $allocations_post
     */
    public function sync_staff_leave_allocations($staff_id, $allocations_post)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id < 1) {
            return;
        }
        if (!is_array($allocations_post)) {
            $allocations_post = array();
        }
        $existing_rows = $this->db->get_where('staff_leave_details', array('staff_id' => $staff_id))->result_array();
        $by_type       = array();
        foreach ($existing_rows as $row) {
            $by_type[(int) $row['leave_type_id']] = $row;
        }
        $all_types = $this->getLeaveType();
        if (empty($all_types)) {
            return;
        }
        foreach ($all_types as $lt) {
            $tid = (int) $lt['id'];
            $raw = isset($allocations_post[$tid]) ? trim((string) $allocations_post[$tid]) : '';
            if ($raw === '') {
                if (isset($by_type[$tid])) {
                    $this->db->where('id', (int) $by_type[$tid]['id'])->delete('staff_leave_details');
                }
                continue;
            }
            if (!is_numeric($raw)) {
                continue;
            }
            $days = (float) $raw;
            if ($days < 0) {
                continue;
            }
            $data = array(
                'staff_id'      => $staff_id,
                'leave_type_id' => $tid,
                'alloted_leave' => $days,
            );
            if (isset($by_type[$tid])) {
                $data['id'] = (int) $by_type[$tid]['id'];
                $this->add_staff_leave_details($data);
            } else {
                $this->add_staff_leave_details($data);
            }
        }
    }
































    public function getPayroll($id = null)
    {
































        $this->db->select()->from('staff_payroll');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }
































    public function getLeaveType($id = null)
    {
































        $this->db->select()->from('leave_types');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('is_active', 'yes');
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }
































   
   
    public function check_data_exists($name, $id, $staff_id)
    {
































        if ($staff_id != 0) {
            $data  = array('id != ' => $staff_id, 'employee_id' => $id);
            $query = $this->db->where($data)->get('staff');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
































            $this->db->where('employee_id', $id);
            $query = $this->db->get('staff');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
































    public function import_check_data_exists($name, $id)
    {
        $this->db->where('employee_id', $id);
        $query = $this->db->get('staff');
        if ($query->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }
































    public function import_check_email_exists($name, $email)
    {
        $this->db->where('email', $email);
        $query = $this->db->get('staff');
        if ($query->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }
































    public function valid_email_id($email) {
        $id = $this->input->post('id'); // Get current staff ID from form
       
        $this->db->where('email', $email);
        if ($id) {
            $this->db->where('id !=', $id); // Exclude current staff record
        }
        $query = $this->db->get('staff');
       
        if ($query->num_rows() > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
   
    /**
     * Check if employee ID is valid (for form validation)
     *
     * @param string $employee_id
     * @return bool TRUE if valid, FALSE otherwise
     */
    public function valid_employee_id($employee_id) {
        $id = $this->input->post('id'); // Get current staff ID from form
       
        $this->db->where('employee_id', $employee_id);
        if ($id) {
            $this->db->where('id !=', $id); // Exclude current staff record
        }
       
        $query = $this->db->get('staff');
       
        return $query->num_rows() === 0; // TRUE if unique, FALSE if duplicate
    }
































    public function check_email_exists($email, $id, $staff_id)
    {
































        if ($staff_id != 0) {
            $data  = array('id != ' => $staff_id, 'email' => $email);
            $query = $this->db->where($data)->get('staff');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
































            $this->db->where('email', $email);
            $query = $this->db->get('staff');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
































    public function getStaffRole($id = null)
    {
        $this->db->select('roles.id,roles.name as type')->from('roles');
































        if ($id != null) {
            $this->db->where('id', $id);
        } else {                      
            if ($this->superadmin_visible == 'disabled' && $this->staffrole->id != 7) {
                $this->db->where("roles.id !=", 7);            
            }
            $this->db->order_by('id');
        }
        $this->db->where("is_active", "yes");
        $query = $this->db->get();
        if ($id != null) {
            $result = $query->row_array();
        } else {
            $result = $query->result_array();
        }
        return $result;
    }
































    public function count_leave($month, $year, $staff_id)
    {
































        $query1 = $this->db->select('sum(leave_days) as tl')->where(array('month(date)' => $month, 'year(date)' => $year, 'staff_id' => $staff_id, 'status' => 'approve'))->get("staff_leave_request");
        return $query1->row_array();
    }
































    public function alloted_leave($staff_id)
    {
































        $query2 = $this->db->select('sum(alloted_leave) as alloted_leave')->where(array('staff_id' => $staff_id))->get("staff_leave_details");
































        return $query2->result_array();
    }
































    public function allotedLeaveType($id)
    {
































        $query = $this->db->select('staff_leave_details.*,leave_types.type')->where(array('staff_id' => $id))->join("leave_types", "staff_leave_details.leave_type_id = leave_types.id")->get("staff_leave_details");
































        return $query->result_array();
    }
































    public function getAllotedLeave($staff_id)
    {
































        $query = $this->db->select('*')->join("leave_types", "staff_leave_details.leave_type_id = leave_types.id")->where("staff_id", $staff_id)->get("staff_leave_details");
































        return $query->result_array();
    }
































    public function getEmployee($role, $active = 1, $class_id = null)
    {
        $i = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('staff', 1);
































        $field_k_array = array();
        $join_array = "";
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_k_array, '`table_custom_' . $i . '`.`field_value` as `' . $custom_fields_value->name . '`');
                $this->db->join('custom_field_values as ' . $tb_counter, 'staff.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }
     
        $field_var = count($field_k_array) > 0 ? "," . implode(',', $field_k_array) : "";        
         
        if ($this->staffrole->id != 7) {        
            if ($this->superadmin_visible == 'disabled') {
                $this->db->where("roles.id !=", 7);            
            }
        }
































        $this->db->select("staff.*,staff_designation.designation,department.department_name as department,roles.name as user_type,roles.id as role_id" . $field_var)
            ->from('staff')
            ->join('staff_designation', "staff_designation.id = staff.designation", "left")
            ->join('staff_roles', "staff_roles.staff_id = staff.id", "left")
            ->join('roles', "roles.id = staff_roles.role_id", "left")
            ->join('department', "department.id = staff.department", "left");      
































        if ($class_id != "") {
            $this->db->join('class_teacher', 'staff.id=class_teacher.staff_id', 'left');
            $this->db->or_where('class_teacher.class_id', $student_current_class->class_id);
        }
        $this->db->where("staff.is_active", $active);  
        if ($role != "") {
            $this->db->where("roles.id", $role);
        }  
        $query = $this->db->get();
































        return $query->result_array();
    }
   
   
   
    public function getEmployeeByRoleID($role, $active = 1)
    {
































        $query = $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role")->join('staff_designation', "staff_designation.id = staff.designation", "left")->join('staff_roles', "staff_roles.staff_id = staff.id", "left")->join('roles', "roles.id = staff_roles.role_id", "left")->join('department', "department.id = staff.department", "left")->where("staff.is_active", $active)->where("roles.id", $role)->get("staff");
































        return $query->result_array();
    }
































    public function getStaffDesignation()
    {
































        $query = $this->db->select('*')->where("is_active", "yes")->get("staff_designation");
        return $query->result_array();
    }
































    public function getDepartment()
    {
        $query = $this->db->select('*')->where("is_active", "yes")->get('department');
        return $query->result_array();
    }
































    public function getLeaveRecord($id)
    {
































        $query = $this->db->select('leave_types.type,leave_types.id as lid,roles.id as staff_role,staff.name,staff.surname,staff.id as staff_id,roles.name as user_type,staff.employee_id,staff_leave_request.*')->join("leave_types", "leave_types.id = staff_leave_request.leave_type_id")->join("staff", "staff.id = staff_leave_request.staff_id")->join("staff_roles", "staff.id = staff_roles.staff_id")->join("roles", "staff_roles.role_id = roles.id")->where("staff_leave_request.id", $id)->get("staff_leave_request");        
       
        $result =  $query->row();        
        $applied_by  =   $this->staff_model->get($result->applied_by) ;            
           
        if(!empty($applied_by['employee_id'])){
            $result->applied_by =  $applied_by['name'].' '.$applied_by['surname'].' ('. $applied_by['employee_id'] .')';
        }else{
            $result->applied_by = '';
        }
       
        return $result;
       
    }
































    public function add_staff_role($data)
{
    $this->db->insert('staff_roles', $data);
    return $this->db->insert_id();
}
































    public function getStaffId($empid)
    {
































        $data  = array('employee_id' => $empid);
        $query = $this->db->select('id')->where($data)->get("staff");
        return $query->row_array();
    }
 
    public function getProfile($id)
    {
        $this->db->select('staff.*,staff_designation.designation as designation,staff_roles.role_id, department.department_name as department,roles.name as user_type,
            staff.ugc_p_province as permanent_province_name,
            staff.ugc_p_district as permanent_district_name,
            staff.ugc_p_local_level as permanent_local_level_name,
            staff.ugc_t_province as temporary_province_name,
            staff.ugc_t_district as temporary_district_name,
            staff.ugc_t_local_level as temporary_local_level_name')
            ->from('staff')
            ->join("staff_designation", "staff_designation.id = staff.designation", "left")
            ->join("department", "department.id = staff.department", "left")
            ->join("staff_roles", "staff_roles.staff_id = staff.id", "left")
            ->join("roles", "roles.id = staff_roles.role_id", "left")
            ->where("staff.id", $id);
       
        $query = $this->db->get();
        return $query->row_array();
    }
































    public function get_staff_name($id) {
































        $filter_get_student_name = $this->db->select('CONCAT_WS(" ",name,surname,"(",employee_id,")") as name')->from('staff')->where('staff.id', $id)->get()->row_array();
        return $filter_get_student_name['name'];
    }
































    public function searchFullText($searchterm, $active)
    {
        $i             = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('staff', 1);
































        $field_k_array = array();
        $join_array    = "";
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_k_array, '`table_custom_' . $i . '`.`field_value` as `' . $custom_fields_value->name . '`');              
                $join_array .= " LEFT JOIN `custom_field_values` as `" . $tb_counter . "` ON `staff`.`id` = `" . $tb_counter . "`.`belong_table_id` AND `" . $tb_counter . "`.`custom_field_id` = " . $custom_fields_value->id;
































                $i++;
            }
        }
       
        $condition = '';              
        if($this->staffrole->id != 7){
            if ($this->superadmin_visible == 'disabled' ) {
                $condition = "and roles.id != 7 "  ;            
            }
        }
































        $field_var = count($field_k_array) > 0 ? "," . implode(',', $field_k_array) : "";
































        $searchterm = $searchterm ? $searchterm : '';
        $query = "SELECT `staff`.*, `staff_designation`.`designation` as `designation`, `department`.`department_name` as `department`,roles.id as role_id,`roles`.`name` as user_type " . $field_var . "  FROM `staff` " . $join_array . " LEFT JOIN `staff_designation` ON `staff_designation`.`id` = `staff`.`designation` LEFT JOIN `staff_roles` ON `staff_roles`.`staff_id` = `staff`.`id` LEFT JOIN `roles` ON `staff_roles`.`role_id` = `roles`.`id` LEFT JOIN `department` ON `department`.`id` = `staff`.`department` WHERE  `staff`.`is_active` = '$active' and (CONCAT(`staff`.`name`,' ',`staff`.`surname`) LIKE '%".$this->db->escape_like_str($searchterm)."%' ESCAPE '!' OR `staff`.`surname` LIKE '%".$this->db->escape_like_str($searchterm)."%' ESCAPE '!' OR `staff`.`employee_id` LIKE '%".$this->db->escape_like_str($searchterm)."%' ESCAPE '!' OR `staff`.`local_address` LIKE '%".$this->db->escape_like_str($searchterm)."%' ESCAPE '!'  OR `staff`.`contact_no` LIKE '%".$this->db->escape_like_str($searchterm)."%' ESCAPE '!' OR `staff`.`email` LIKE '%".$this->db->escape_like_str($searchterm)."%' ESCAPE '!' OR `roles`.`name` LIKE '%".$this->db->escape_like_str($searchterm)."' ESCAPE '!') $condition ";
































        $query = $this->db->query($query);
































        return $query->result_array();
    }
































    public function searchByEmployeeId($employee_id)
    {
































        $this->db->select('*');
        $this->db->from('staff');
        $this->db->like('staff.employee_id', $employee_id);
        $this->db->like('staff.is_active', 1);
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function getStaffDoc($id)
    {
































        $this->db->select('*');
        $this->db->from('staff_documents');
        $this->db->where('staff_id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }
































    public function count_attendance($year, $staff_id, $att_type)
    {
        $query = $this->db->select('count(*) as attendence')->where(array('staff_id' => $staff_id, 'year(date)' => $year, 'staff_attendance_type_id' => $att_type))->get("staff_attendance");
































        return $query->row()->attendence;
    }
































    public function getStaffPayroll($id)
    {
        $this->db->select('*');
        $this->db->from('staff_payslip');
        $this->db->where('staff_id', $id);
        $query = $this->db->get();
        return $query->result_array();
    }
































      public function doc_delete($id, $doc)
    {
































        $staff = $this->get($id);
        $file_name  = "";
































        if ($doc == "resume") {
            $file_name = $staff['resume'];
            $data      = array('resume' => '');
        } else if ($doc == "joining_letter") {
            $file_name = $staff['joining_letter'];
            $data      = array('joining_letter' => '');
        } else if ($doc == "resignation_letter") {
            $file_name = $staff['resignation_letter'];
            $data      = array('resignation_letter' => '');
        } else if ($doc == "other_document_file") {
            $file_name = $staff['other_document_file'];
            $data      = array('other_document_name' => '', 'other_document_file' => '');
        }
     
        if($file_name != ""){
































        $this->media_storage->filedelete($file_name,  "./uploads/staff_documents/" . $id);
        }
     
        $this->db->where('id', $id)->update("staff", $data);
    }
































































    public function getLeaveDetails($id)
    {
































        $query = $this->db->select('staff_leave_details.alloted_leave,staff_leave_details.id as altid,leave_types.type,leave_types.id')->join("leave_types", "staff_leave_details.leave_type_id = leave_types.id", "inner")->where("staff_leave_details.staff_id", $id)->get("staff_leave_details");
































        return $query->result_array();
    }
































    public function disablestaff($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        $query = $this->db->where("id", $data['id'])->update("staff", $data);
        $message   = UPDATE_RECORD_CONSTANT . " On  disable Staff id " . $data['id'];
        $action    = "Update";
        $record_id = $insert_id = $data['id'];
        $this->log($message, $record_id, $action);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
































    }
































    public function enablestaff($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        $data = array('is_active' => 1, 'disable_at' => '');
        $query = $this->db->where("id", $id)->update("staff", $data);
        $message   = UPDATE_RECORD_CONSTANT . " On  Enable Staff id " . $id;
        $action    = "Update";
        $record_id = $insert_id = $id;
        $this->log($message, $record_id, $action);
































        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
    }




    public function getByEmail($email)
    {
        $this->db->select('staff.*,languages.language,languages.id as language_id,languages.is_rtl,IFNULL(currencies.name,0) as currency_name,IFNULL(currencies.symbol,0) as symbol,IFNULL(currencies.base_price,0) as base_price ,IFNULL(currencies.id,0) as `currency`');
        $this->db->from('staff');
        $this->db->join('languages', 'languages.id=staff.lang_id', 'left');
        $this->db->join('currencies', 'currencies.id=staff.currency_id', 'left');
        $this->db->where('email', $email);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }




    public function checkLogin($data)
    {








        $record = $this->getByEmail($data['email']);
        if ($record) {
            $pass_verify = $this->enc_lib->passHashDyc($data['password'], $record->password);
            if ($pass_verify) {
                $roles = $this->staffroles_model->getStaffRoles($record->id);
                $record->roles = array($roles[0]->name => $roles[0]->role_id);
                return $record;
            }
        }
        return false;
    }
































    public function getStaffbyrole($id)
    {
        $this->db->select('staff.*,staff_designation.designation as designation,staff_roles.role_id, department.department_name as department,roles.name as user_type');
        $this->db->join("staff_designation", "staff_designation.id = staff.designation", "left");
        $this->db->join("department", "department.id = staff.department", "left");
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");
        $this->db->where("staff_roles.role_id", $id);
        $this->db->where("staff.is_active", "1");
        $this->db->from('staff');
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function searchNameLike($searchterm)
    {
        $this->db->select('staff.*')->from('staff');
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");
        $this->db->group_start();
        $this->db->like('staff.name', $searchterm);
        $this->db->group_end();
        $this->db->where("staff.is_active", "1");
        $this->db->order_by('staff.id');        
































                 
        if ($this->superadmin_visible == 'disabled' && $this->staffrole->id != 7) {
                $this->db->where("roles.id !=", 7);          
        }
        $this->db->limit(15);
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function update_role($role_data)
    {
        $this->db->where("staff_id", $role_data["staff_id"])->update("staff_roles", $role_data);
    }
































    public function check_staffid_exists($employee_id)
    {
        $this->db->where(array('employee_id' => $employee_id));
        $query = $this->db->get('staff');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
































    }
































    public function lastRecord()
    {
        $last_row = $this->db->select('*')->order_by('id', "desc")->limit(1)->get('staff')->row();
        return $last_row;
    }
































    public function ratingapr($id, $approve)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where("id", $id)->update("staff_rating", $approve);
        $message   = UPDATE_RECORD_CONSTANT . " On staff rating id " . $id;
        $action    = "Update";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
































        $this->db->trans_complete(); # Completing transaction
        /*Optional*/
































        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
































        } else {
            //return $return_value;
        }
    }
































    public function rating_remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('staff_rating');
        $message   = DELETE_RECORD_CONSTANT . " On staff rating id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /*Optional*/
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }
































    public function get_RatedStaffByUser($user_id)
    {
        $this->db->select('staff_rating.staff_id')->from('staff_rating');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function all_rating()
    {
        $this->db->select('sum(`rate`) as rate, count(*) as total,staff_id')->from('staff_rating');
        $this->db->where('status', '1');
        $this->db->group_by('staff_id');
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function get_ratingbyuser($user_id, $role)
    {
        $this->db->select('*')->from('staff_rating');
        $this->db->where('user_id', $user_id);
        $this->db->where('role', $role);
        $query = $this->db->get();
        return $query->result_array();
    }
































    public function staff_ratingById($id)
    {
        $this->db->select('sum(`rate`) as rate, count(*) as total')->from('staff_rating');
        $this->db->where('staff_id', $id);
        $this->db->where('status', 1);
        $query = $this->db->get();
        return $query->row_array();
    }
































    public function get_StaffNameById($id)
    {
        return $this->db->select("CONCAT_WS(' ',name,surname) as name,employee_id,id")->from('staff')->where('id', $id)->get()->row_array();
    }
































    public function staff_report($condition)
{      
    $rolescondition = '';
   
    if ($this->session->has_userdata('admin')) {
        if($this->staffrole->id != 7){
            if ($this->superadmin_visible == 'disabled' ) {
                $rolescondition = " and roles.id != 7 ";
            }
        }
    }
   
    $i = 1;
    $custom_fields = $this->customfield_model->get_custom_fields('staff', 1);
















    $field_k_array = array();
    $join_array = "";
    if (!empty($custom_fields)) {
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            $tb_counter = "table_custom_" . $i;
            array_push($field_k_array, '`table_custom_' . $i . '`.`field_value` as `' . $custom_fields_value->name . '`');
            $join_array .= " LEFT JOIN `custom_field_values` as `" . $tb_counter . "` ON `staff`.`id` = `" . $tb_counter . "`.`belong_table_id` AND `" . $tb_counter . "`.`custom_field_id` = " . $custom_fields_value->id;
            $i++;
        }
    }
 
    $field_var = count($field_k_array) > 0 ? "," . implode(',', $field_k_array) : "";
















    $query = "SELECT `staff`.*, `staff_designation`.`designation` as `designation`, `department`.`department_name` as `department`,`roles`.`name` as user_type " . $field_var . ", GROUP_CONCAT(leave_type_id,'@',alloted_leave) as leaves  
              FROM `staff` " . $join_array . "
              LEFT JOIN `staff_designation` ON `staff_designation`.`id` = `staff`.`designation`
              LEFT JOIN `staff_roles` ON `staff_roles`.`staff_id` = `staff`.`id`
              LEFT JOIN `roles` ON `staff_roles`.`role_id` = `roles`.`id`
              LEFT JOIN `department` ON `department`.`id` = `staff`.`department`
              LEFT JOIN staff_leave_details ON staff_leave_details.staff_id=staff.id
              WHERE 1 " . $condition . $rolescondition . "
              GROUP BY staff.id";
















    $result = $this->db->query($query);
   
    if ($result === FALSE) {
     
        log_message('error', "Database error in staff_report: " . $this->db->error()['message']);
        return array();
    }
   
    return $result->result_array();
}
















public function get_staff_by_ethnicity_gender()
{
    $rolescondition = '';
   
    if ($this->session->has_userdata('admin')) {
        if($this->staffrole->id != 7) {
            if ($this->superadmin_visible == 'disabled') {
                $rolescondition = " and roles.id != 7 ";
            }
        }
    }
   
    $query = "SELECT staff.caste as ethnicity, staff.gender, COUNT(*) as total
              FROM `staff`
              LEFT JOIN `staff_roles` ON `staff_roles`.`staff_id` = `staff`.`id`
              LEFT JOIN `roles` ON `staff_roles`.`role_id` = `roles`.`id`
              WHERE staff.is_active = 1 " . $rolescondition . "
              GROUP BY staff.caste, staff.gender";
             
    $result = $this->db->query($query);
   
    if ($result === FALSE) {
        log_message('error', "Database error in get_staff_by_ethnicity_gender: " . $this->db->error()['message']);
        return array();
    }
   
    return $result->result_array();
}
















public function get_staff_by_position_jobtype_gender()
{
    $rolescondition = '';
   
    if ($this->session->has_userdata('admin')) {
        if($this->staffrole->id != 7) {
            if ($this->superadmin_visible == 'disabled') {
                $rolescondition = " and roles.id != 7 ";
            }
        }
    }
   
    $query = "SELECT
                `roles`.`name` as position,
                staff.contract_type as job_type,
                staff.gender,
                COUNT(*) as total
              FROM `staff`
              LEFT JOIN `staff_roles` ON `staff_roles`.`staff_id` = `staff`.`id`
              LEFT JOIN `roles` ON `staff_roles`.`role_id` = `roles`.`id`
              WHERE staff.is_active = 1 " . $rolescondition . "
              GROUP BY `roles`.`name`, staff.contract_type, staff.gender";
             
    $result = $this->db->query($query);
   
    if ($result === FALSE) {
        log_message('error', "Database error in get_staff_by_position_jobtype_gender: " . $this->db->error()['message']);
        return array();
    }
   
    return $result->result_array();
}
































    public function inventry_staff()
    {        
        if ($this->superadmin_visible == 'disabled' && $this->staffrole->id != 7) {
                $this->db->where("staff_roles.role_id !=", 7);            
        }
       
        $this->db->select("CONCAT_WS(' ',staff.name,staff.surname) as name,staff.employee_id,roles.id as role_id,staff.id");
        $this->db->from('staff');
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");
        $this->db->where('staff.is_active', 1);
        $query = $this->db->get();
        return $query->result_array();
    }
   
    public function getstaffemail($id = null)
    {
        $this->db->select('staff.*,languages.language,roles.name as user_type,roles.id as role_id')->from('staff')->join("staff_roles", "staff_roles.staff_id = staff.id", "left")->join("roles", "staff_roles.role_id = roles.id", "left")->join("languages", "languages.id = staff.lang_id", "left");
        $this->db->where('staff.id', $id);
        $query = $this->db->get();
        return $query->result_array();
       
    }
































    public function getemployeeidbystaffid($id){
        $this->db->select('staff.employee_id,email,contact_no')->from('staff')->where('staff.id', $id);
         $query = $this->db->get();
        $result =  $query->row_array();
        return $result['employee_id'];
    }
































public function getStaffByRoleCount($role_id) {
    $this->db->select('COUNT(*) as count');
    $this->db->from('staff');
    $this->db->join('staff_roles', 'staff_roles.staff_id = staff.id');
    $this->db->where('staff_roles.role_id', $role_id);
    $this->db->where('staff.is_active', 1);
    $query = $this->db->get();
    return $query->row()->count;
   
}































public function get_all() {
    $this->db->select('s.*,
                      setting.name as college_name,
                      dept.department_name as department_name,
                      desig.designation as designation_name')
            ->from('staff as s')
            ->join('sch_settings as setting', 'setting.id = s.institution_id', 'left')
            ->join('staff_designation as desig', 'desig.id = s.designation', 'left')
            ->join('department as dept', 'dept.id = s.department', 'left')
            ->where('s.is_active', 1);
           
    $query = $this->db->get();
   
    $result = $query->result_array();
    log_message('debug', 'Staff query: ' . $this->db->last_query());
   
    return $result;
}
 
/**
 * Get staff member by ID
 *
 * @param int $id Staff ID
 * @return array|null Array containing staff details or null if not found
 */
public function get_by_id($id) {
    $this->db->select('s.*,
                      setting.name as college_name,
                      dept.department_name as department_name,
                      desig.designation as designation_name')
            ->from('staff as s')
            ->join('sch_settings as setting', 'setting.id = s.institution_id', 'left')
            ->join('staff_designation as desig', 'desig.id = s.designation', 'left')
            ->join('department as dept', 'dept.id = s.department', 'left')
            ->where('s.id', $id)
            ->where('s.is_active', 1);
           
    $query = $this->db->get();
   
    if ($query->num_rows() > 0) {
        return $query->row_array();
    }
   
    return null;
}
















/**
 * Get staff members by department
 *
 * @param int $department_id Department ID
 * @return array Array of staff members
 */
public function get_by_department($department_id) {
    $this->db->select('*')
            ->from('staff')
            ->where('department', $department_id)
            ->where('is_active', 1);
           
    $query = $this->db->get();
   
    return $query->result_array();
}
















/**
 * Get staff members by designation
 *
 * @param int $designation_id Designation ID
 * @return array Array of staff members
 */
public function get_by_designation($designation_id) {
    $this->db->select('*')
            ->from('staff')
            ->where('designation', $designation_id)
            ->where('is_active', 1);
           
    $query = $this->db->get();
   
    return $query->result_array();
}
















/**
 * Get staff members by institution
 *
 * @param int $institution_id Institution ID
 * @return array Array of staff members
 */
public function get_by_institution($institution_id) {
    $this->db->select('*')
            ->from('staff')
            ->where('institution_id', $institution_id)
            ->where('is_active', 1);
           
    $query = $this->db->get();
   
    return $query->result_array();
}
















/**
 * Search staff by name, email or phone
 *
 * @param string $search_term Search term
 * @return array Array of matching staff members
 */
public function search($search_term) {
    $this->db->select('*')
            ->from('staff')
            ->group_start()
                ->like('name', $search_term)
                ->or_like('surname', $search_term)
                ->or_like('email', $search_term)
                ->or_like('contact_no', $search_term)
            ->group_end()
            ->where('is_active', 1);
           
    $query = $this->db->get();
   
    return $query->result_array();
}
















/**
 * Persist HEMIS employee sync result on staff row (central id + sync metadata).
 *
 * @param int   $staff_id
 * @param array $hemisOut Hemis_client sync_* return array
 * @return bool True when staff row was updated
 */
public function apply_hemis_employee_sync_result($staff_id, array $hemisOut)
{
    $staff_id = (int) $staff_id;
    if ($staff_id < 1 || !$this->db->table_exists('staff')) {
        return false;
    }

    $synced = !empty($hemisOut['synced']);
    $code = 0;
    if (isset($hemisOut['status_code'])) {
        $code = (int) $hemisOut['status_code'];
    } elseif (isset($hemisOut['http_code'])) {
        $code = (int) $hemisOut['http_code'];
    }
    $hid = isset($hemisOut['hemis_employee_id']) ? (int) $hemisOut['hemis_employee_id'] : 0;

    $upd = array('id' => $staff_id);

    if ($synced && $hid > 0) {
        $upd['hemis_employee_id'] = $hid;
        if ($this->db->field_exists('hemis_sync_at', 'staff')) {
            $upd['hemis_sync_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('hemis_sync_error', 'staff')) {
            $upd['hemis_sync_error'] = null;
        }
        if ($this->db->field_exists('hemis_sync_error_at', 'staff')) {
            $upd['hemis_sync_error_at'] = null;
        }
        $this->update($upd);
        return true;
    }

    if ($synced && $code >= 200 && $code < 300) {
        if ($this->db->field_exists('hemis_sync_at', 'staff')) {
            $upd['hemis_sync_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('hemis_sync_error', 'staff')) {
            $upd['hemis_sync_error'] = null;
        }
        if ($this->db->field_exists('hemis_sync_error_at', 'staff')) {
            $upd['hemis_sync_error_at'] = null;
        }
        if (count($upd) > 1) {
            $this->update($upd);
            return true;
        }
    }

    if (!$synced && !empty($hemisOut['error']) && $this->db->field_exists('hemis_sync_error', 'staff')) {
        $upd['hemis_sync_error'] = substr((string) $hemisOut['error'], 0, 500);
        if ($this->db->field_exists('hemis_sync_error_at', 'staff')) {
            $upd['hemis_sync_error_at'] = date('Y-m-d H:i:s');
        }
        $this->update($upd);
    }

    return false;
}

/**
 * Create new employee record
 *
 * @param array $data Employee data
 * @return int|boolean ID of new employee or false on failure
 */
public function create_employee($data) {
    $this->db->trans_start();
    $this->db->insert('staff', $data);
    $insert_id = $this->db->insert_id();
    $this->db->trans_complete();
   
    if ($this->db->trans_status() === FALSE) {
        log_message('error', 'Failed to insert employee data');
        return false;
    }
   
    return $insert_id;
}
















/**
 * Update employee record
 *
 * @param int $id Employee ID
 * @param array $data Updated employee data
 * @return boolean True on success, false on failure
 */
public function update_employee($id, $data) {
    $this->db->trans_start();
    $this->db->where('id', $id);
    $this->db->update('staff', $data);
    $this->db->trans_complete();
   
    if ($this->db->trans_status() === FALSE) {
        log_message('error', 'Failed to update employee data');
        return false;
    }

    return true;
}








/**
 * Delete employee (soft delete by setting is_active = 0)
 *
 * @param int $id Employee ID
 * @return boolean True on success, false on failure
 */
public function delete_employee($id) {
    $this->db->trans_start();
    $this->db->where('id', $id);
    $this->db->update('staff', ['is_active' => 0]);
    $this->db->trans_complete();
   
    if ($this->db->trans_status() === FALSE) {
        log_message('error', 'Failed to delete employee');
        return false;
    }
   
    return true;
}




public function getStaffDocuments($staff_id) {
    $this->db->select('citizenship, nationalId, resume, joining_letter, resignation_letter, other_document_name, other_document_file');
    $this->db->where('id', $staff_id);
    $query = $this->db->get('staff');




    $documents = [
        'citizenship' => null,
        'national_id' => null,
        0 => ['document' => null], // Resume
        1 => ['document' => null], // Joining Letter
        2 => ['document' => null], // Resignation Letter
        3 => ['document_details' => []], // Dynamic Other Documents
    ];




    if ($query && $query->num_rows() > 0) {
        $row = $query->row_array();




        // Helper to return correct file path
        $getCorrectPath = function($filename) use ($staff_id) {
            if (empty($filename)) return null;




            $staff_folder_path = FCPATH . 'uploads/staff_documents/' . $staff_id . '/' . $filename;
            if (file_exists($staff_folder_path)) {
                return $staff_id . '/' . $filename;
            }




            $main_folder_path = FCPATH . 'uploads/staff_documents/' . $filename;
            if (file_exists($main_folder_path)) {
                return $filename;
            }




            return $filename; // fallback
        };




        // Static fields
        if (!empty($row['citizenship'])) {
            $documents['citizenship'] = $getCorrectPath($row['citizenship']);
        }




        if (!empty($row['nationalId'])) {
            $documents['national_id'] = $getCorrectPath($row['nationalId']);
        }




        if (!empty($row['resume'])) {
            $documents[0]['document'] = $getCorrectPath($row['resume']);
        }




        if (!empty($row['joining_letter'])) {
            $documents[1]['document'] = $getCorrectPath($row['joining_letter']);
        }




        if (!empty($row['resignation_letter'])) {
            $documents[2]['document'] = $getCorrectPath($row['resignation_letter']);
        }




        // Dynamic documents
        $other_doc_files = !empty($row['other_document_file']) ? json_decode($row['other_document_file'], true) : null;
        $other_doc_names = !empty($row['other_document_name']) ? json_decode($row['other_document_name'], true) : null;




        if (!empty($other_doc_files) && is_array($other_doc_files)) {
            $document_details = [];
            foreach ($other_doc_files as $index => $file_name) {
                if (!empty($file_name)) {
                    $document_details[] = [
                        'file' => $getCorrectPath($file_name),
                        'name' => isset($other_doc_names[$index]) ? $other_doc_names[$index] : 'Additional Document ' . ($index + 1),
                    ];
                }
            }
            $documents[3]['document_details'] = $document_details;
        }
    }




    return $documents;
}




public function updateOtherDocuments($staff_id, $other_doc_names = [], $other_doc_files = []) {
    // Convert arrays to JSON strings
    $data = [
        'other_document_name' => json_encode($other_doc_names),
        'other_document_file' => json_encode($other_doc_files)
    ];




    // Update staff table where id = $staff_id
    $this->db->where('id', $staff_id);
    return $this->db->update('staff', $data);
}












public function update_staff_role($data)
{
    $this->db->where('staff_id', $data['staff_id']);
    $this->db->update('staff_roles', $data);
    return ($this->db->affected_rows() > 0);
}




public function addDesignation($data) {
    $this->db->insert('staff_designation', $data);
    return $this->db->insert_id();
}








public function getDesignationIdByName($designation_name) {
    $this->db->select('id');
    $this->db->from('staff_designation');
    $this->db->where('designation', $designation_name);
    $this->db->where('is_active', 1);
    $query = $this->db->get();
   
    if ($query->num_rows() > 0) {
        $result = $query->row();
        return $result->id;
    }
   
    // If not found, create new designation
    $designation_data = [
        'designation' => $designation_name,
        'is_active' => 1
    ];
   
    return $this->addDesignation($designation_data);
}


public function getDesignationsByType($staff_type) {
    $db_type = ($staff_type == 'non_teaching') ? 'Non Teaching' : 'Teaching';
   
    $this->db->select('designation');
    $this->db->from('staff_designation');
    $this->db->where('staff_type', $db_type);
    $this->db->where('is_active', 'yes');
    $this->db->order_by('designation', 'asc');
    $query = $this->db->get();
    return $query->result();
}

// public function getEmployeeByRole($role)
// {
//     $this->db->where('type', $role);
//     $query = $this->db->get('staff');
//     return $query->result_array();
// }


   
}