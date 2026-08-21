<?php

/**
 * 
 */
class Designation extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('file');
        $this->config->load("payroll");
        $this->load->model('designation_model');
        $this->load->model('staff_model');
    }

    function designation() {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/designation/designation');
        $designation = $this->designation_model->get();
        $data["title"] = $this->lang->line('add_designation');
        $data["designation"] = $designation;
        
        // Add staff_type options
        $data["staff_types"] = array(
            '' => 'Select Staff Type',
            'Teaching' => 'Teaching',
            'Non Teaching' => 'Non Teaching'
        );
        
        // Add designation options for JavaScript
        $data["designation_options"] = json_encode(array(
            'Teaching' => array(
                "Professor",
                "Associate Professor", 
                "Assistant Professor",
                "Teaching Assistant",
                "Guest Lecturer",
                "Campus Chief",
                "Head of Department",
                "Researcher"
            ),
            'Non Teaching' => array(
                "Campus Administration Chief",
                "Section Officer",
                "Office Assistant", 
                "Account Officer",
                "Accountant",
                "Information Officer",
                "Chief Librarian",
                "Library Assistant",
                "Technical Assistant",
                "Office Helper",
                "Driver",
                "Security Guard"
            )
        ));
        
        $this->form_validation->set_rules('staff_type', 'Staff Type', 'required');
        $this->form_validation->set_rules('selected_designations[]', 'Designation', 'required');
        
        if ($this->form_validation->run()) {
            $staff_type = $this->input->post("staff_type");
            $selected_designations = $this->input->post("selected_designations");
            $designationid = $this->input->post("designationid");
            
            if (empty($designationid)) {
                if (!$this->rbac->hasPrivilege('designation', 'can_add')) {
                    access_denied();
                }
            } else {
                if (!$this->rbac->hasPrivilege('designation', 'can_edit')) {
                    access_denied();
                }
            }

            // Process multiple designations
            if (!empty($selected_designations)) {
                // If editing, first delete all existing designations for this staff type
                if (!empty($designationid)) {
                    $current_record = $this->designation_model->get($designationid);
                    if ($current_record) {
                        // Delete all designations for this staff type
                        $this->designation_model->deleteByStaffType($current_record['staff_type']);
                    }
                }

                // Insert new/updated designations
                foreach ($selected_designations as $designation_name) {
                    $data = array(
                        'designation' => $designation_name,
                        'staff_type' => $staff_type,
                        'is_active' => 'yes'
                    );
                    $this->designation_model->addDesignation($data);
                }
            }
            
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            redirect("admin/designation/designation");
        } else {
            $this->load->view("layout/header");
            $this->load->view("admin/staff/designation", $data);
            $this->load->view("layout/footer");
        }
    }

    function designationedit($id) {
        $result = $this->designation_model->get($id);
        $data["title"] = $this->lang->line('edit_designation');
        $data["result"] = $result;

        // Get all designations for the same staff type
        $existing_designations = $this->designation_model->getByStaffType($result['staff_type']);
        $data["existing_designations"] = array_column($existing_designations, 'designation');

        // Add staff_type options for edit
        $data["staff_types"] = array(
            '' => 'Select Staff Type',
            'Teaching' => 'Teaching',
            'Non Teaching' => 'Non Teaching'
        );

        // Add designation options for JavaScript
        $data["designation_options"] = json_encode(array(
            'Teaching' => array(
                "Professor",
                "Associate Professor",
                "Assistant Professor", 
                "Teaching Assistant",
                "Guest Lecturer",
                "Campus Chief",
                "Head of Department",
                "Researcher"
            ),
            'Non Teaching' => array(
                "Campus Administration Chief",
                "Section Officer",
                "Office Assistant",
                "Account Officer",
                "Accountant",
                "Information Officer",
                "Chief Librarian",
                "Library Assistant",
                "Technical Assistant",
                "Office Helper",
                "Driver",
                "Security Guard"
            )
        ));

        $designation = $this->designation_model->get();
        $data["designation"] = $designation;
        $this->load->view("layout/header");
        $this->load->view("admin/staff/designation", $data);
        $this->load->view("layout/footer");
    }

    function designationdelete($id) {
        $this->designation_model->deleteDesignation($id);
        redirect('admin/designation/designation');
    }
}

?>