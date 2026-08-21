<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Diagnose extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model("student_model");
        $this->load->model("session_model");
        $this->load->model("class_model");
        $this->load->model("section_model");
        $this->load->model("program_model");
        $this->load->helper('debug');
    }
    
    public function student_data()
    {
        if (!$this->rbac->hasPrivilege('promote_student', 'can_view')) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'System');
        $this->session->set_userdata('sub_menu', 'diagnose/student_data');
        
        $data['title'] = 'Diagnose Student Data';
        
        // Get current session
        $current_session = $this->setting_model->getCurrentSession();
        $data['current_session'] = $current_session;
        
        // Get classes, sections, and programs
        $data['classes'] = $this->class_model->get();
        $data['sections'] = $this->section_model->get();
        $data['programs'] = $this->program_model->get();
        
        // Check if we have student data
        $student_count = $this->db->count_all('students');
        $data['student_count'] = $student_count;
        
        // Check student_session data
        $student_session_count = $this->db->where('session_id', $current_session)->count_all_results('student_session');
        $data['student_session_count'] = $student_session_count;
        
        // Get distribution of students by class
        $this->db->select('classes.class, count(*) as count');
        $this->db->from('student_session');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->where('student_session.session_id', $current_session);
        $this->db->group_by('student_session.class_id');
        $class_distribution = $this->db->get()->result_array();
        $data['class_distribution'] = $class_distribution;
        
        // Get distribution by status flags
        $this->db->select('count(*) as active_count');
        $this->db->from('student_session');
        $this->db->where('session_id', $current_session);
        $this->db->where('is_active', 'yes');
        $active_count = $this->db->get()->row()->active_count;
        $data['active_count'] = $active_count;
        
        $this->db->select('count(*) as leave_count');
        $this->db->from('student_session');
        $this->db->where('session_id', $current_session);
        $this->db->where('is_leave', 1);
        $leave_count = $this->db->get()->row()->leave_count;
        $data['leave_count'] = $leave_count;
        
        $this->db->select('count(*) as graduated_count');
        $this->db->from('student_session');
        $this->db->where('session_id', $current_session);
        $this->db->where('is_graduated', 1);
        $graduated_count = $this->db->get()->row()->graduated_count;
        $data['graduated_count'] = $graduated_count;
        
        // Check if is_graduated column exists in student_session table
        $db_columns = $this->db->list_fields('student_session');
        $data['has_graduated_column'] = in_array('is_graduated', $db_columns);
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/diagnose/student_data', $data);
        $this->load->view('layout/footer', $data);
    }
    
    public function check_class($class_id, $section_id, $program_id)
    {
        if (!$this->rbac->hasPrivilege('promote_student', 'can_view')) {
            access_denied();
        }
        
        // Get current session
        $current_session = $this->setting_model->getCurrentSession();
        
        // Get students in this class/section/program
        $this->db->select('students.id, students.firstname, students.lastname, students.admission_no');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->where('student_session.session_id', $current_session);
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where('student_session.program_id', $program_id);
        $this->db->where('students.is_active', 'yes');
        $students = $this->db->get()->result_array();
        
        echo json_encode([
            'count' => count($students),
            'students' => $students,
            'query' => $this->db->last_query()
        ]);
    }
}