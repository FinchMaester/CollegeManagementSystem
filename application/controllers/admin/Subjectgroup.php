<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subjectgroup extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("program_model");
        $this->load->model("program_model");
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('subject_group', 'can_view')) {
            access_denied();
        }

        $json_array = array();
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'subjectgroup/index');
        $data['title'] = 'Add Class';
        $data['title_list'] = 'Class List';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $data['section_array'] = $json_array;
        $program_list = $this->program_model->getAllActivePrograms();
        $data['programlist'] = $program_list;

        $this->form_validation->set_rules(
            'name', $this->lang->line('name'), array(
                'required',
                array('class_exists', array($this->subjectgroup_model, 'class_exists')),
            )
        );

        $this->form_validation->set_rules('subject[]', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('selected_faculties[]', $this->lang->line('faculty'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['section_array'] = $this->input->post('sections');
        } else {
            $name = $this->input->post('name');
            $session = $this->setting_model->getCurrentSession();
            $class_array = array(
                'name' => $this->input->post('name'),
                'session_id' => $session,
                'description' => $this->input->post('description'),
            );
            $subject = $this->input->post('subject');
            
            $faculty_ids = $this->input->post('selected_faculties');
            $program_ids_by_faculty = $this->input->post('program_ids');
            $class_ids_by_faculty = $this->input->post('class_ids');
            
            $sections = array();
            if (!empty($faculty_ids)) {
                foreach ($faculty_ids as $faculty_id) {
                    if (isset($program_ids_by_faculty[$faculty_id]) && isset($class_ids_by_faculty[$faculty_id])) {
                        $sections[] = array(
                            'faculty_id' => $faculty_id,
                            'program_id' => $program_ids_by_faculty[$faculty_id],
                            'class_id' => $class_ids_by_faculty[$faculty_id]
                        );
                    }
                }
            }

            $this->subjectgroup_model->add($class_array, $subject, $sections);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/subjectgroup');
        }
        
        $subject_list = $this->subject_model->get();
        $data['subjectlist'] = $subject_list;
        $subjectgroupList = $this->subjectgroup_model->getByID();
        $data['subjectgroupList'] = $subjectgroupList;
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/subjectgroup/subjectgroupList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('subject_group', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->subjectgroup_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/subjectgroup');
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('subject_group', 'can_edit')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'subjectgroup/index');
        $json_array = array();
        $old_sections = array();
        $old_subjects = array();
        $data['title'] = 'Edit Class';
        $data['id'] = $id;
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        $subject_list = $this->subject_model->get();
        $data['subjectlist'] = $subject_list;
        $subjectgroupList = $this->subjectgroup_model->getByID();
        $data['subjectgroupList'] = $subjectgroupList;
        $subjectgroup = $this->subjectgroup_model->getByID($id);
        $program_list = $this->program_model->getAllActivePrograms();
        $data['programlist'] = $program_list;

        $assigned_sections_data = array();
        if (!empty($subjectgroup[0]->sections)) {
            foreach ($subjectgroup[0]->sections as $value) {
                $assigned_sections_data[$value->section_id] = array(
                    'program_id' => $value->program_id,
                    'class_id' => $value->class_id
                );
                // Populate old_sections for comparison during form submission
                $old_sections[] = ['faculty_id' => $value->section_id, 'program_id' => $value->program_id, 'class_id' => $value->class_id];
            }
        }
        if (!empty($subjectgroup[0]->group_subject)) {
            foreach ($subjectgroup[0]->group_subject as $key => $value) {
                $old_subjects[] = $value->subject_id;
            }
        }

        $data['subjectgroup'] = $subjectgroup;
        $data['assigned_sections_data'] = $assigned_sections_data; // Pass this to the view
        
        $this->form_validation->set_rules(
            'name', $this->lang->line('name'), array(
                'required',
                array('class_exists', array($this->subjectgroup_model, 'class_exists')),
            )
        );

        $this->form_validation->set_rules('selected_faculties[]', $this->lang->line('faculty'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject[]', $this->lang->line('subject'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            if ($this->input->server('REQUEST_METHOD') == "POST") {
                $data['section_array'] = $this->input->post('sections');
            }

            $this->load->view('layout/header', $data);
            $this->load->view('admin/subjectgroup/subjectgroupEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            log_message('debug', 'Subjectgroup Controller: edit() - Raw POST Data: ' . print_r($this->input->post(), true));

            $class_array = array(
                'id' => $this->input->post('id'),
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
            );
            $subject = $this->input->post('subject');
            
            $faculty_ids = $this->input->post('selected_faculties');
            $program_ids_by_faculty = $this->input->post('program_ids');
            $class_ids_by_faculty = $this->input->post('class_ids');
            
            $sections = array();
            if (!empty($faculty_ids)) {
                foreach ($faculty_ids as $faculty_id) {
                    if (isset($program_ids_by_faculty[$faculty_id]) && isset($class_ids_by_faculty[$faculty_id])) {
                        $sections[] = array(
                            'faculty_id' => $faculty_id,
                            'program_id' => $program_ids_by_faculty[$faculty_id],
                            'class_id' => $class_ids_by_faculty[$faculty_id]
                        );
                    }
                }
            }

            log_message('debug', 'Subjectgroup Controller: edit() - Old Sections Array: ' . print_r($old_sections, true));
            log_message('debug', 'Subjectgroup Controller: edit() - New Sections Array: ' . print_r($sections, true));
            
            $add_sections = [];
            $delete_sections = [];

            foreach ($sections as $section_data) {
                $found = false;
                foreach ($old_sections as $old_section_data) {
                    if ($section_data['faculty_id'] == $old_section_data['faculty_id'] &&
                        $section_data['program_id'] == $old_section_data['program_id'] &&
                        $section_data['class_id'] == $old_section_data['class_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $add_sections[] = $section_data;
                }
            }

            foreach ($old_sections as $old_section_data) {
                $found = false;
                foreach ($sections as $section_data) {
                    if ($old_section_data['faculty_id'] == $section_data['faculty_id'] &&
                        $old_section_data['program_id'] == $section_data['program_id'] &&
                        $old_section_data['class_id'] == $section_data['class_id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $delete_sections[] = $old_section_data;
                }
            }

            log_message('debug', 'Subjectgroup Controller: edit() - Sections to Delete: ' . print_r($delete_sections, true));
            log_message('debug', 'Subjectgroup Controller: edit() - Sections to Add: ' . print_r($add_sections, true));

            $delete_subjects = array_diff($old_subjects, $subject);
            $add_subjects = array_diff($subject, $old_subjects);
            
            log_message('debug', 'Subjectgroup Controller: edit() - Subjects to Delete: ' . print_r($delete_subjects, true));
            log_message('debug', 'Subjectgroup Controller: edit() - Subjects to Add: ' . print_r($add_subjects, true));

            $this->subjectgroup_model->edit($class_array, $delete_sections, $add_sections, $delete_subjects, $add_subjects);
            redirect('admin/subjectgroup');
        }
    }

    public function addsubjectgroup()
    {
        $this->form_validation->set_rules('subject_group_id', $this->lang->line('fee_group'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'subject_group_id' => form_error('subject_group_id'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $student_session_id     = $this->input->post('student_session_id');
            $subject_group_id       = $this->input->post('subject_group_id');
            $student_sesssion_array = isset($student_session_id) ? $student_session_id : array();
            $student_ids            = $this->input->post('student_ids');
            $delete_student         = array_diff($student_ids, $student_sesssion_array);

            $preserve_record = array();
            if (!empty($student_sesssion_array)) {
                foreach ($student_sesssion_array as $key => $value) {

                    $insert_array = array(
                        'student_session_id' => $value,
                        'subject_group_id'   => $subject_group_id,
                    );
                    $inserted_id       = $this->studentsubjectgroup_model->add($insert_array);
                    $preserve_record[] = $inserted_id;
                }
            }

            if (!empty($delete_student)) {
                $this->studentsubjectgroup_model->delete($subject_group_id, $delete_student);
            }

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function getGroupByClassandSection()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $session_id = $this->input->post('session_id');
        if(!isset($session_id)){
            $session_id=NULL;
        }
        $data       = $this->subjectgroup_model->getGroupByClassandSection($class_id, $section_id,$session_id);
        echo json_encode($data);
    }

    public function getSubjectByClassandSectionDate()
    {
        try {
            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $date       = $this->input->post('date');
            
            // Date is optional - convert if provided and not empty
            $date_ad = null;
            if ($date && trim($date) !== '' && $date !== null) {
                // Only try to convert if date is not empty
                $date_timestamp = $this->customlib->datetostrtotime($date);
                if ($date_timestamp && $date_timestamp !== false && $date_timestamp !== '') {
                    $date_ad = date('Y-m-d', $date_timestamp);
                }
            }
            
            // Use the new method that fetches from subject groups
            // Subjects are fetched based on Faculty (section_id), Program, and Level (class_id)
            // Date is only used to filter timetable entries (optional)
            $data = $this->subjectgroup_model->getSubjectsByClassSectionWithTimetable($class_id, $section_id, $date_ad);
            echo json_encode($data);
        } catch (Exception $e) {
            log_message('error', 'getSubjectByClassandSectionDate error: ' . $e->getMessage());
            echo json_encode(array('error' => $e->getMessage()));
        }
    }

    public function getAllSubjectByClassandSection()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $data       = $this->subjectgroup_model->getAllsubjectByClassSection($class_id, $section_id);
        echo json_encode($data);
    }

    public function getSubjectByClassandSection()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $data       = $this->subjecttimetable_model->getSubjectByClassandSection($class_id, $section_id);
        echo json_encode($data);
    }

    public function getGroupsubjects()
    {
        $subject_group_id = $this->input->post('subject_group_id');
         $session_id = $this->input->post('session_id');
        if(!isset($session_id)){
            $session_id=NULL;
        }
        $data             = $this->subjectgroup_model->getGroupsubjects($subject_group_id,$session_id);      
        echo json_encode($data);
    }

    public function getProgramsByClassSection()
    {
        $class_id = $this->input->post('class_id');
        $faculty_ids = $this->input->post('faculty_ids');
        
        if (empty($class_id) || empty($faculty_ids)) {
            echo json_encode(array());
            return;
        }
        
        // Debug logging
        error_log('getProgramsByClassSection called');
        error_log('Class ID: ' . $class_id);
        error_log('Faculty IDs: ' . print_r($faculty_ids, true));
        
        $this->load->model('program_model');
        $programs = $this->program_model->getProgramsByMultipleFaculties($class_id, $faculty_ids);
        
        echo json_encode($programs);
    }

    public function getProgramsByFaculty() {
        try {
            // Get input parameters
            $class_id = $this->input->post('class_id');
            $faculty_id = $this->input->post('faculty_id');
            
            // Debug logging
            error_log('getProgramsByFaculty controller called');
            error_log('class_id: ' . print_r($class_id, true));
            error_log('faculty_id: ' . print_r($faculty_id, true));
            
            // Validate input
            if (empty($class_id) || empty($faculty_id)) {
                throw new Exception('Missing required parameters');
            }
            
            // Load model if not already loaded
            if (!isset($this->program_model)) {
                $this->load->model('program_model');
            }
            
            // Get programs
            $programs = $this->program_model->getProgramsByFaculty($class_id, $faculty_id);
            
            // Debug logging
            error_log('Programs returned: ' . print_r($programs, true));
            
            // Send response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($programs));
                
        } catch (Exception $e) {
            error_log('Error in getProgramsByFaculty: ' . $e->getMessage());
            
            // Send error response
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => $e->getMessage()]));
        }
    }

}