<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Session_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($id = null) {
        $this->db->select()->from('sessions');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        
        if ($id != null) {
            $result = $query->row_array();
            // Convert session to BS format if needed
            if (!empty($result) && isset($result['session'])) {
                $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
            }
            return $result;
        } else {
            $results = $query->result_array();
            // Convert all sessions to BS format
            foreach ($results as &$result) {
                if (isset($result['session'])) {
                    $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
                }
            }
            return $results;
        }
        $this->current_session = $this->setting_model->getCurrentSession();
    }
    
    /**
     * Convert session to BS format if it's in AD format
     * @param string $session Session string (e.g., "2024-25" or "2081-82")
     * @return string BS formatted session
     */
    private function convertSessionToBSIfNeeded($session) {
        if (empty($session)) {
            return $session;
        }
        
        // AGGRESSIVE CONVERSION: Check if session is already in BS format
        // BS years typically range from 2070-2100 (current BS year is around 2081-2082)
        $parts = explode('-', $session);
        if (count($parts) == 2) {
            $year1 = intval($parts[0]);
            
            // If year is in valid BS range (2070-2100), return as-is
            if ($year1 >= 2070 && $year1 <= 2100) {
                return $session;
            }
            
            // FORCE CONVERSION for anything outside valid range
            if (!isset($this->customlib)) {
                $this->load->library('customlib');
            }
            return $this->customlib->convertSessionToBS($session);
        }
        
        // Invalid format, try to convert anyway
        if (!isset($this->customlib)) {
            $this->load->library('customlib');
        }
        return $this->customlib->convertSessionToBS($session);
    }

    public function getAllSession() {
        $sql = "SELECT sessions.*, IFNULL(sch_settings.session_id, 0) as `active` FROM `sessions` LEFT JOIN sch_settings ON sessions.id=sch_settings.session_id";
        $query = $this->db->query($sql);
        $results = $query->result_array();

        if (!isset($this->customlib)) {
            $this->load->library('customlib');
        }

        $fiscal_by_bs_year = $this->getFiscalYearEnglishMapByBsYear();

        foreach ($results as &$result) {
            $stored = isset($result['session']) ? (string) $result['session'] : '';
            $bs = $this->convertSessionToBSIfNeeded($stored);
            $ad = $this->customlib->convertSessionToADIfNeeded($bs);
            $bs_start = 0;
            $parts = explode('-', $bs);
            if (!empty($parts[0])) {
                $bs_start = (int) $parts[0];
            }

            $result['session'] = $bs; // BS / translated display (existing behaviour)
            $result['session_bs'] = $bs;
            $result['session_ad'] = $ad;
            $result['fiscal_year_nepali'] = '';
            $result['fiscal_year_english'] = '';
            if ($bs_start > 0 && isset($fiscal_by_bs_year[$bs_start])) {
                $result['fiscal_year_nepali'] = $fiscal_by_bs_year[$bs_start]['year_nepali'];
                $result['fiscal_year_english'] = $fiscal_by_bs_year[$bs_start]['year_english'];
            }
        }
        unset($result);

        return $results;
    }

    /**
     * Map BS start year => UGC fiscal year nepali/english labels.
     *
     * @return array
     */
    private function getFiscalYearEnglishMapByBsYear()
    {
        $map = array();
        if (!$this->db->table_exists('ugc_fiscal_years')) {
            return $map;
        }

        $rows = $this->db->select('year_nepali, year_english')
            ->from('ugc_fiscal_years')
            ->order_by('active_fiscal_year', 'DESC')
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();

        foreach ($rows as $row) {
            $np = isset($row['year_nepali']) ? (string) $row['year_nepali'] : '';
            // Formats like 2082/083 or 2082/83
            if (!preg_match('/^(\d{4})/', $np, $m)) {
                continue;
            }
            $bs_year = (int) $m[1];
            if ($bs_year < 1 || isset($map[$bs_year])) {
                continue; // keep first (prefer active / latest)
            }
            $map[$bs_year] = array(
                'year_nepali'  => $np,
                'year_english' => isset($row['year_english']) ? (string) $row['year_english'] : '',
            );
        }

        return $map;
    }

    public function getPreSession($session_id) {
        $sql = "select * from sessions where id in (select max(id) from sessions where id < $session_id)";

        $query = $this->db->query($sql);
        return $query->row();
    }

    public function getStudentAcademicSession($student_id = null) {
        $this->db->select('sessions.*')->from('sessions');
        $this->db->join('student_session', 'sessions.id = student_session.session_id');
        $this->db->where('student_session.student_id', $student_id);
        $this->db->group_by('student_session.session_id');
        $this->db->order_by('sessions.id');
        $query = $this->db->get();
        $results = $query->result_array();
        
        // Convert all sessions to BS format
        foreach ($results as &$result) {
            if (isset($result['session'])) {
                $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
            }
        }
        
        return $results;
    }

    public function remove($id) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('sessions');
        $message = DELETE_RECORD_CONSTANT . " On sessions id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('sessions', $data);
            $message = UPDATE_RECORD_CONSTANT . " On sessions id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        } else {
            $this->db->insert('sessions', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On sessions id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        }
    }

    public function getDropoutStudents()
    {
        $this->db->select('student_session.*, students.firstname, students.middlename, students.lastname, 
                          students.first_name_np, students.email, students.roll_no, students.batch_name,
                          students.dis_reason, students.disable_at, students.dis_note');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id', 'inner');
        $this->db->where('student_session.is_leave', 1);
        $this->db->where('students.is_active', 'yes');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get dropout student by ID
     * 
     * @param int $student_id
     * @return array
     */
    public function getDropoutStudentById($student_id)
    {
        $this->db->select('student_session.*, students.firstname, students.middlename, students.lastname, 
                          students.first_name_np, students.email, students.roll_no, students.batch_name,
                          students.dis_reason, students.disable_at, students.dis_note');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id', 'inner');
        $this->db->where('student_session.is_leave', 1);
        $this->db->where('students.is_active', 'yes');
        $this->db->where('students.id', $student_id);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Check if a student is a dropout
     * 
     * @param int $student_id
     * @return bool
     */
    public function isDropout($student_id)
    {
        $this->db->where('student_id', $student_id);
        $this->db->where('is_leave', 1);
        $query = $this->db->get('student_session');
        
        return ($query->num_rows() > 0);
    }


    public function getGraduatedStudents()
    {
        $this->db->select('student_session.*, students.firstname, students.middlename, students.lastname, 
                          students.first_name_np, students.middle_name_np, students.last_name_np,
                          students.email, students.roll_no, students.university_regd_no,
                          students.dob, students.fiscal_year_id, students.gender,
                          students.father_name, students.mother_name, students.mobileno,
                          students.digital_signature, students.pp_size_photo, students.receipt_image,
                          students.note, students.entrance_score, students.program_id,
                          students.complition_year, students.admission_year');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id', 'inner');
        $this->db->where('student_session.is_graduated', 1);
        $this->db->where('students.is_active', 'yes');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get graduated student by ID
     * 
     * @param int $student_id
     * @return array
     */
    public function getGraduatedStudentById($student_id)
    {
        $this->db->select('student_session.*, students.firstname, students.middlename, students.lastname, 
                          students.first_name_np, students.middle_name_np, students.last_name_np,
                          students.email, students.roll_no, students.university_regd_no,
                          students.dob, students.fiscal_year_id, students.gender,
                          students.father_name, students.mother_name, students.mobileno,
                          students.digital_signature, students.pp_size_photo, students.receipt_image,
                          students.note, students.entrance_score, students.program_id,
                          students.complition_year, students.admission_year');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id', 'inner');
        $this->db->where('student_session.is_graduated', 1);
        $this->db->where('students.is_active', 'yes');
        $this->db->where('students.id', $student_id);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Check if a student is graduated
     * 
     * @param int $student_id
     * @return bool
     */
    public function isGraduated($student_id)
    {
        $this->db->where('student_id', $student_id);
        $this->db->where('is_graduated', 1);
        $query = $this->db->get('student_session');
        
        return ($query->num_rows() > 0);
    }

    public function get_current_session() {
        // Assuming you have a 'sessions' table with a 'is_current' column to identify the current session
        $this->db->select('*');
        $this->db->from('sessions');
        $this->db->where('is_active', 'yes');
        $this->db->limit(1);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            // Convert session to BS format
            if (isset($result['session'])) {
                $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
            }
            return $result;
        } else {
            // If no current session is found, return the latest session
            $this->db->select('*');
            $this->db->from('sessions');
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get();
            
            if ($query->num_rows() > 0) {
                $result = $query->row_array();
                // Convert session to BS format
                if (isset($result['session'])) {
                    $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
                }
                return $result;
            }
            
            return ['id' => 0];
        }
    }
    
    /**
     * Get all sessions
     * 
     * @return array
     */
    public function get_all_sessions() {
        $this->db->select('*');
        $this->db->from('sessions');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $results = $query->result_array();
        
        // Convert all sessions to BS format
        foreach ($results as &$result) {
            if (isset($result['session'])) {
                $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
            }
        }
        
        return $results;
    }
    
    /**
     * Get session by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function get_session_by_id($id) {
        $this->db->select('*');
        $this->db->from('sessions');
        $this->db->where('id', $id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            // Convert session to BS format
            if (isset($result['session'])) {
                $result['session'] = $this->convertSessionToBSIfNeeded($result['session']);
            }
            return $result;
        }
        
        return null;
    }
}
