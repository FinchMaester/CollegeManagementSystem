<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Programs extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('program_model');
        $this->load->model('section_model');
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'programs/index');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('program', 'can_view')) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'programs/index');
        $data['title'] = 'Program List';
    
        // Get all programs with their sections
        $program_result = $this->program_model->getAllWithSections();
        $data['programlist'] = $program_result;
        
        // Get all sections for the dropdown
        $data['sections'] = $this->section_model->get();
    
        // Form validation rules
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('code', $this->lang->line('code'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('duration', $this->lang->line('duration'), 'trim|required|xss_clean|numeric');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('program/programCreate', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // Prepare program data
            $program_data = array(
                'title' => $this->input->post('title'),
                'code' => $this->input->post('code'),
                'duration' => $this->input->post('duration'),
                'session_type' => $this->input->post('session_type'),
                'total_session' => $this->input->post('total_session'),
                'description' => $this->input->post('description'),
                'is_active' => $this->input->post('is_active') ? 1 : 0
            );
            
            // Get section_id for the pivot table
            $section_id = $this->input->post('section_id');
            
            // Add program and create section relationship
            $program_id = $this->program_model->addWithSection($program_data, $section_id);
            
            if ($program_id) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error creating program</div>');
            }
            
            redirect('programs/index');
        }
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('program', 'can_view')) {
            access_denied();
        }
        $data['title'] = 'Program Details';
        $program = $this->program_model->get($id);
        $data['program'] = $program;
        $this->load->view('layout/header', $data);
        $this->load->view('program/programShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('program', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Program List';
        $this->program_model->remove($id);
        redirect('programs/index');
    }
    
    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('program', 'can_edit')) {
            access_denied();
        }
        
        $data['title'] = 'Edit Program';
        $data['id'] = $id;
        
        // Get program data
        $program = $this->program_model->get($id);
        $data['program'] = $program;
        
        // Get all programs for listing
        $program_result = $this->program_model->getAllWithSections();
        $data['programlist'] = $program_result;
        
        // Get all sections
        $data['sections'] = $this->section_model->get();
        
        // Get current program's section - FIX: Get the section_id, not the entire section array
        $program_sections = $this->program_model->getProgramSections($id);
        $data['current_section_id'] = !empty($program_sections) ? $program_sections[0]['id'] : null;
        
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('code', $this->lang->line('code'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('duration', $this->lang->line('duration'), 'trim|required|xss_clean|numeric');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('program/programEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $program_data = array(
                'id' => $id,
                'title' => $this->input->post('title'),
                'code' => $this->input->post('code'),
                'duration' => $this->input->post('duration'),
                'session_type' => $this->input->post('session_type'),
                'total_session' => $this->input->post('total_session'),
                'description' => $this->input->post('description'),
                'is_active' => $this->input->post('is_active') ? 1 : 0
            );
            
            $section_id = $this->input->post('section_id');
            
            // Update program and section relationship
            $success = $this->program_model->updateWithSection($program_data, $section_id);
            
            if ($success) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error updating program</div>');
            }
            
            redirect('programs/index');
        }
    }

public function getProgramsByClassSection($class_id, $section_id) {
    $this->db->select('programs.*');
    $this->db->from('programs');
    $this->db->join('program_sections', 'programs.id = program_sections.program_id', 'inner');
    $this->db->where('program_sections.class_id', $class_id);
    $this->db->where('program_sections.section_id', $section_id);
    $this->db->order_by('programs.program_name', 'asc');
    $query = $this->db->get();
    
    $programs = array();
    if ($query->num_rows() > 0) {
        foreach ($query->result() as $row) {
            $programs[] = array(
                'program_id' => $row->id,
                'program' => $row->program_name
            );
        }
    }
    
    return $programs;
}
public function get_gender_distribution() {
    $programs = $this->db->get('programs')->result_array();
    
    $program_names = array();
    $male_counts = array();
    $female_counts = array();
    
    foreach ($programs as $program) {
        $program_names[] = $program['program_name'];
        
        // Count male students
        $this->db->where('program_id', $program['id']);
        $this->db->where('gender', 'Male');
        $male_counts[] = $this->db->count_all_results('students');
        
        // Count female students
        $this->db->where('program_id', $program['id']);
        $this->db->where('gender', 'Female');
        $female_counts[] = $this->db->count_all_results('students');
    }
    
    return array(
        'program_names' => $program_names,
        'male_counts' => $male_counts,
        'female_counts' => $female_counts
    );
}

public function getBySection() {
    $section_id = $this->input->post('section_id'); 
    
    $this->load->model('program_model');
    $programs = $this->program_model->getProgramsBySection($section_id);
    
    echo json_encode($programs);
}

}
