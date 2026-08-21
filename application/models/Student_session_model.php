<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Student_session_model
 *
 * This model handles operations related to student sessions
 */
class Student_session_model extends CI_Model {
   
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
   
    /**
     * Get student current session
     *
     * @param int $student_id
     * @return array|null
     */
    public function get_student_current_session($student_id) {
        // Get current session ID
        $current_session = $this->session_model->get_current_session();
        $session_id = $current_session['id'];
       
        $this->db->select('*');
        $this->db->from('student_session');
        $this->db->where('student_id', $student_id);
        $this->db->where('session_id', $session_id);
        $this->db->where('is_active', 'yes');
        $query = $this->db->get();
       
        return $query->num_rows() > 0 ? $query->row_array() : null;
    }
   
    /**
     * Get all sessions for a student
     *
     * @param int $student_id
     * @return array
     */
    public function get_student_sessions($student_id) {
        $this->db->select('student_session.*, sessions.session, classes.class, sections.section');
        $this->db->from('student_session');
        $this->db->join('sessions', 'sessions.id = student_session.session_id', 'left');
        $this->db->join('classes', 'classes.id = student_session.class_id', 'left');
        $this->db->join('sections', 'sections.id = student_session.section_id', 'left');
        $this->db->where('student_session.student_id', $student_id);
        $this->db->order_by('student_session.id', 'DESC');
        $query = $this->db->get();
       
        return $query->result_array();
    }
   
    /**
     * Add a new student session
     *
     * @param array $data
     * @return int The inserted ID
     */
    public function add_student_session($data) {
        $this->db->insert('student_session', $data);
        return $this->db->insert_id();
    }
   
    /**
     * Update student session
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_student_session($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('student_session', $data);
    }
   
    /**
     * Set all previous sessions as inactive
     *
     * @param int $student_id
     * @param int $current_session_id
     * @return bool
     */
    public function deactivate_previous_sessions($student_id, $current_session_id) {
        $this->db->where('student_id', $student_id);
        $this->db->where('session_id !=', $current_session_id);
        return $this->db->update('student_session', ['is_active' => 'no']);
    }


   
}

