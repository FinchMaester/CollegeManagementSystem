<?php

class Studentresult_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

/**
 * Get promoted students who need result marking
 */
public function getPromotedStudents($section_id, $program_id, $class_id, $session_id) {
    $this->db->select('
        students.id,
        students.admission_no,
        students.firstname,
        students.middlename,
        students.lastname,
        students.father_name,
        students.dob,
        student_session.is_pass,
        student_session.is_fail,
        student_session.session_id,
        student_session.class_id,
        student_session.section_id,
        student_session.program_id
    ');
    
    $this->db->from('students');
    $this->db->join('student_session', 'student_session.student_id = students.id');
    
    $this->db->where('student_session.section_id', $section_id);
    $this->db->where('student_session.program_id', $program_id);
    $this->db->where('student_session.class_id', $class_id);
    $this->db->where('student_session.session_id', $session_id);
    $this->db->where('student_session.is_active', 'no'); // Only promoted students
    $this->db->where('students.is_active', 'yes'); // Only active students
    
    $this->db->order_by('students.admission_no', 'ASC');
    
    $query = $this->db->get();
    return $query->result_array();
}

/**
 * Save student results (pass/fail) for their previous session
 */
public function saveStudentResults($results_data) {
    $this->db->trans_start();
    
    try {
        foreach ($results_data as $result) {
            $student_id = $result['student_id'];
            $section_id = $result['section_id'];
            $program_id = $result['program_id'];
            $class_id = $result['class_id'];
            $session_id = $result['session_id'];
            $result_status = $result['result_status'];
            
            // Prepare update data based on result status
            $update_data = array(
                'updated_at' => date('Y-m-d')
            );
            
            if ($result_status == 'pass') {
                $update_data['is_pass'] = 1;
                $update_data['is_fail'] = 0;
            } else {
                $update_data['is_pass'] = 0;
                $update_data['is_fail'] = 1;
            }
            
            // Update the student_session record
            $this->db->where('student_id', $student_id);
            $this->db->where('section_id', $section_id);
            $this->db->where('program_id', $program_id);
            $this->db->where('class_id', $class_id);
            $this->db->where('session_id', $session_id);
            $this->db->where('is_active', 'no'); // Only update promoted students
            
            $this->db->update('student_session', $update_data);
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        $this->db->trans_rollback();
        log_message('error', 'Error saving student results: ' . $e->getMessage());
        return false;
    }
}

    /**
     * Log student result activity for audit trail
     */
    private function logStudentResultActivity($student_id, $session_id, $class_id, $result_status) {
        // Get current user ID
        $current_user_id = $this->session->userdata('admin_id');
        
        // Get session and class names for logging
        $session_name = $this->getSessionName($session_id);
        $class_name = $this->getClassName($class_id);
        
        $log_data = array(
            'user_id' => $current_user_id,
            'student_id' => $student_id,
            'action' => 'result_marked',
            'description' => "Student result marked as {$result_status} for {$class_name} in {$session_name}",
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s')
        );
        
    }

    /**
     * Get session name by ID
     */
    private function getSessionName($session_id) {
        $this->db->select('session');
        $this->db->from('sessions');
        $this->db->where('id', $session_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row()->session;
        }
        return 'Unknown Session';
    }

    /**
     * Get class name by ID
     */
    private function getClassName($class_id) {
        $this->db->select('class');
        $this->db->from('classes');
        $this->db->where('id', $class_id);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->row()->class;
        }
        return 'Unknown Class';
    }

    /**
     * Get student result statistics
     */
    public function getResultStatistics($section_id, $program_id, $class_id, $session_id) {
        $this->db->select('
            COUNT(*) as total_students,
            SUM(CASE WHEN is_pass = 1 THEN 1 ELSE 0 END) as passed_students,
            SUM(CASE WHEN is_fail = 1 THEN 1 ELSE 0 END) as failed_students,
            SUM(CASE WHEN is_pass = 0 AND is_fail = 0 THEN 1 ELSE 0 END) as pending_results
        ');
        
        $this->db->from('student_session');
        $this->db->where('section_id', $section_id);
        $this->db->where('program_id', $program_id);
        $this->db->where('class_id', $class_id);
        $this->db->where('session_id', $session_id);
        $this->db->where('is_active', 'no');
        
        $query = $this->db->get();
        return $query->row_array();
    }
}