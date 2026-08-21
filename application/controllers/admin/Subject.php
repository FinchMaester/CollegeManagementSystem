<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subject extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'Academics/subject');
        $data['title']         = 'Add subject';
        $subject_result        = $this->subject_model->get();
        $data['subjectlist']   = $subject_result;
        $data['subject_types'] = $this->customlib->subjectType();
        $this->form_validation->set_rules('name', $this->lang->line('subject_name'), 'trim|required|xss_clean|callback__check_name_exists');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        if ($this->input->post('code')) {
            $this->form_validation->set_rules('code', $this->lang->line('code'), 'trim|required|callback__check_code_exists');
        }
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subject/subjectList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'type' => $this->input->post('type'),
                'is_academic' => $this->input->post('is_academic') !== null ? (int)$this->input->post('is_academic') : 1, // Default to academic if not specified
            );
            $this->subject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/subject/index');
        }
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_view')) {
            access_denied();
        }
        $data['title']   = 'Subject List';
        $subject         = $this->subject_model->get($id);
        $data['subject'] = $subject;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/subject/subjectShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Subject List';
        $this->subject_model->remove($id);
        redirect('admin/subject/index');
    }

    public function _check_name_exists()
    {
        $name = $this->input->post('name');
        $type = $this->input->post('type');
        $id   = $this->input->post('id');

        $data['name'] = ($name !== null) ? $this->security->xss_clean($name) : '';
        $data['type'] = ($type !== null) ? $this->security->xss_clean($type) : '';
        // When editing, pass current id to exclude from duplicate check (avoid cleaning null)
        $data['exclude_id'] = ($id !== null && $id !== '') ? $this->security->xss_clean($id) : '';
        if ($this->subject_model->check_data_exists($data)) {
            $this->form_validation->set_message('_check_name_exists', $this->lang->line('name_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function _check_code_exists()
    {
        $code = $this->input->post('code');
        $type = $this->input->post('type');
        $id   = $this->input->post('id');

        $data['code'] = ($code !== null) ? $this->security->xss_clean($code) : '';
        $data['type'] = ($type !== null) ? $this->security->xss_clean($type) : '';
        // When editing, pass current id to exclude from duplicate check
        $data['exclude_id'] = ($id !== null && $id !== '') ? $this->security->xss_clean($id) : '';
        if ($this->subject_model->check_code_exists($data)) {
            $this->form_validation->set_message('_check_code_exists', $this->lang->line('code_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_edit')) {
            access_denied();
        }
        $subject_result        = $this->subject_model->get();
        $data['subjectlist']   = $subject_result;
        $data['title']         = 'Edit Subject';
        $data['id']            = $id;
        $subject               = $this->subject_model->get($id);
        $data['subject']       = $subject;
        $data['subject_types'] = $this->customlib->subjectType();
        
        // Check if subject is non-academic
        $is_non_academic = (isset($subject['is_academic']) && $subject['is_academic'] == 0);
        
        // Apply same uniqueness validation as create, scoped by type and excluding current id
        $this->form_validation->set_rules('name', $this->lang->line('subject'), 'trim|required|xss_clean|callback__check_name_exists');
        if ($this->input->post('code')) {
            $this->form_validation->set_rules('code', $this->lang->line('code'), 'trim|required|callback__check_code_exists');
        }
        // Only require type for academic subjects
        if (!$is_non_academic) {
            $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        }
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subject/subjectEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // Get current subject to check if it's non-academic
            $current_subject = $this->subject_model->get($id);
            $is_academic = $this->input->post('is_academic') !== null ? (int)$this->input->post('is_academic') : (isset($current_subject['is_academic']) ? (int)$current_subject['is_academic'] : 1);
            
            $data = array(
                'id'   => $id,
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'is_academic' => $is_academic
            );
            
            // Only set type for academic subjects
            if ($is_academic == 1) {
                $data['type'] = $this->input->post('type');
            } else {
                // For non-academic subjects, keep type as empty string
                $data['type'] = '';
            }
            
            $this->subject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/subject/index');
        }
    }

    /**
     * Create Non-Academic Subject (via modal or direct call)
     * Route: POST admin/subject/create_nonacademic
     */
    public function create_nonacademic()
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_add')) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 'error', 'message' => 'Access denied'));
                return;
            }
            access_denied();
        }

        $this->form_validation->set_rules('name', 'Subject Name', 'trim|required|xss_clean');
        if ($this->input->post('code')) {
            $this->form_validation->set_rules('code', 'Subject Code', 'trim|xss_clean');
        }

        if ($this->form_validation->run() == false) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 'error', 'message' => validation_errors()));
                return;
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid input. Please try again.</div>');
            redirect('admin/subject');
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'is_academic' => 0, // Non-academic
                'type' => '' // Empty string for non-academic subjects (type column doesn't allow NULL)
            );

            $result = $this->subject_model->add($data);
            
            if ($this->input->is_ajax_request()) {
                if ($result === 'duplicate') {
                    echo json_encode(array('status' => 'error', 'message' => 'Duplicate entry! Subject with same name, code, and type already exists.'));
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Non-Academic subject added successfully.</div>');
                    echo json_encode(array('status' => 'success', 'message' => 'Non-Academic subject added successfully.'));
                }
                return;
            }
            
            if ($result === 'duplicate') {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Duplicate entry! Subject with same name, code, and type already exists.</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Non-Academic subject added successfully.</div>');
            }
            redirect('admin/subject');
        }
    }

    public function getSubjctByClassandSection()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $date       = $this->teachersubject_model->getSubjectByClsandSection($class_id, $section_id);
        echo json_encode($data);
    }

}
