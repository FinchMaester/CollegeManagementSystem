<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Classes extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('program_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('class', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'classes/index');
        $data['title']      = 'Add Class';
        $data['title_list'] = 'Class List';
   
        $this->form_validation->set_rules('class', $this->lang->line('academic_level'), 'required|trim|xss_clean|callback_check_class_unique');
        $this->form_validation->set_rules('sections[]', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('degree', $this->lang->line('degree_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_programs', $this->lang->line('programs'), 'trim|required|xss_clean');
   
        if ($this->form_validation->run() == false) {
            // Load view with errors
            $vehicle_result = $this->section_model->get();
            $data['vehiclelist'] = $vehicle_result;
            $vehroute_result = $this->classsection_model->getByID();
            $data['vehroutelist'] = $vehroute_result;
           
            // Get all programs for initial load
            $data['all_programs'] = $this->program_model->getAllActivePrograms();
           
            $this->load->view('layout/header', $data);
            $this->load->view('class/classList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // DEBUG: Log all POST data
            log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
           
            $class = $this->input->post('class');
            $degree = $this->input->post('degree');
            $sections = $this->input->post('sections');
            $section_programs_raw = $this->input->post('section_programs');
           
            // DEBUG: Log the raw section_programs data
            log_message('debug', 'Raw section_programs: ' . $section_programs_raw);
           
            $class_array = array(
                'class' => $this->input->post('class'),
                'degree' => $this->input->post('degree'),
                'is_active' => 'yes',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d')
            );
           
            // Parse the section_programs string
            $section_programs = $this->parseSectionPrograms($section_programs_raw);
           
            // DEBUG: Log parsed data
            log_message('debug', 'Parsed section_programs: ' . print_r($section_programs, true));
           
            // Add the class and sections with programs
            $result = $this->addClassWithSectionsAndPrograms($class_array, $sections, $section_programs);
           
            if ($result) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error creating class. Please try again.</div>');
            }
            redirect('classes');
        }
    }

public function check_class_unique($class) {
    $class_id = $this->input->post('pre_class_id'); // For edit case
    $degree = $this->input->post('degree'); // Get the degree
    
    if (empty($degree)) {
        // If degree is not provided, use old validation (backward compatibility)
        $this->form_validation->set_message('check_class_unique', 'Degree is required');
        return false;
    }
    
    if ($class_id) {
        // Check if class name exists for other records with the same degree
        $this->db->where('class', $class);
        $this->db->where('degree', $degree);
        $this->db->where('id !=', $class_id);
        $query = $this->db->get('classes');
        
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('check_class_unique', 'This academic level name already exists for the selected degree');
            return false;
        }
    } else {
        // For new records - check if class name exists with the same degree
        $this->db->where('class', $class);
        $this->db->where('degree', $degree);
        $query = $this->db->get('classes');
        
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('check_class_unique', 'This academic level name already exists for the selected degree');
            return false;
        }
    }
    
    return true;
}
    /**
     * Parse section_programs string into array format
     * Input format: "section_id:program_id1,program_id2|section_id2:program_id3"
     * Output format: [section_id => [program_id1, program_id2], section_id2 => [program_id3]]
     */
    private function parseSectionPrograms($section_programs_string)
    {
        $parsed = array();
       
        log_message('debug', 'Parsing section_programs: ' . $section_programs_string);
       
        if (empty($section_programs_string)) {
            log_message('debug', 'Section programs string is empty');
            return $parsed;
        }
       
        // Split by | to get each section's programs
        $section_groups = explode('|', $section_programs_string);
        log_message('debug', 'Section groups: ' . print_r($section_groups, true));
       
        foreach ($section_groups as $group) {
            $group = trim($group);
            if (!empty($group) && strpos($group, ':') !== false) {
                $parts = explode(':', $group, 2);
                $section_id = trim($parts[0]);
                $program_ids_string = trim($parts[1]);
               
                if (!empty($program_ids_string)) {
                    $program_ids = explode(',', $program_ids_string);
                    $program_ids = array_map('trim', $program_ids);
                    $program_ids = array_filter($program_ids); // Remove empty values
                   
                    if (!empty($program_ids)) {
                        $parsed[$section_id] = $program_ids;
                    }
                }
            }
        }
       
        log_message('debug', 'Final parsed result: ' . print_r($parsed, true));
        return $parsed;
    }
   
    /**
     * Add class with sections and programs - Updated for integer program_id
     */
    private function addClassWithSectionsAndPrograms($class_array, $sections, $section_programs)
    {
        // Start transaction
        $this->db->trans_start();
        
        try {
            log_message('debug', 'Starting class creation transaction');
            
            // First, add the class
            $this->db->insert('classes', $class_array);
            $class_id = $this->db->insert_id();
            
            log_message('debug', 'Class inserted with ID: ' . $class_id);
            
            if (!$class_id) {
                throw new Exception('Failed to insert class');
            }
            
            // Now add class_sections - one record for each section-program combination
            foreach ($sections as $section_id) {
                log_message('debug', 'Processing section: ' . $section_id);
                
                // Get program IDs for this section
                $program_ids = isset($section_programs[$section_id]) ? $section_programs[$section_id] : array();
                
                log_message('debug', 'Programs for section ' . $section_id . ': ' . print_r($program_ids, true));
                
                // Create separate records for each program
                if (!empty($program_ids)) {
                    foreach ($program_ids as $program_id) {
                        $class_section_data = array(
                            'class_id' => $class_id,
                            'section_id' => $section_id,
                            'program_id' => intval($program_id), // Store as integer
                            'is_active' => 'yes',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        );
                        
                        log_message('debug', 'Inserting class_section: ' . print_r($class_section_data, true));
                        
                        $this->db->insert('class_sections', $class_section_data);
                        
                        if ($this->db->affected_rows() == 0) {
                            log_message('error', 'Failed to insert class_section record');
                            throw new Exception('Failed to insert class_section record');
                        }
                    }
                } else {
                    // If no programs selected for this section, create record with NULL program_id
                    $class_section_data = array(
                        'class_id' => $class_id,
                        'section_id' => $section_id,
                        'program_id' => NULL,
                        'is_active' => 'yes',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    
                    log_message('debug', 'Inserting class_section without program: ' . print_r($class_section_data, true));
                    
                    $this->db->insert('class_sections', $class_section_data);
                    
                    if ($this->db->affected_rows() == 0) {
                        log_message('error', 'Failed to insert class_section record');
                        throw new Exception('Failed to insert class_section record');
                    }
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Transaction failed');
                return false;
            }
            
            log_message('debug', 'Transaction completed successfully');
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Exception in addClassWithSectionsAndPrograms: ' . $e->getMessage());
            $this->db->trans_rollback();
            return false;
        }
    }
    
    /**
     * Update class with sections and programs - Updated for integer program_id
     */
    private function updateClassWithSectionsAndPrograms($class_id, $class_array, $sections, $section_programs)
    {
        // Start transaction
        $this->db->trans_start();
        
        try {
            // Update the class
            $this->db->where('id', $class_id);
            $this->db->update('classes', $class_array);
            
            // Delete existing class_sections for this class
            $this->db->where('class_id', $class_id);
            $this->db->delete('class_sections');
            
            // Add new class_sections - one record for each section-program combination
            foreach ($sections as $section_id) {
                // Get program IDs for this section
                $program_ids = isset($section_programs[$section_id]) ? $section_programs[$section_id] : array();
                
                // Create separate records for each program
                if (!empty($program_ids)) {
                    foreach ($program_ids as $program_id) {
                        $class_section_data = array(
                            'class_id' => $class_id,
                            'section_id' => $section_id,
                            'program_id' => intval($program_id), // Store as integer
                            'is_active' => 'yes',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        );
                        
                        $this->db->insert('class_sections', $class_section_data);
                    }
                } else {
                    // If no programs selected for this section, create record with NULL program_id
                    $class_section_data = array(
                        'class_id' => $class_id,
                        'section_id' => $section_id,
                        'program_id' => NULL,
                        'is_active' => 'yes',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    
                    $this->db->insert('class_sections', $class_section_data);
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return false;
        }
    }
   
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('class', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->class_model->remove($id);

        $student_delete = $this->student_model->getUndefinedStudent();
        if (!empty($student_delete)) {
            $delete_student_array = array();
            foreach ($student_delete as $student_key => $student_value) {
                $delete_student_array[] = $student_value->id;
            }
            $this->student_model->bulkdelete($delete_student_array);
        }
     
        redirect('classes');
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('class', 'can_edit')) {
            access_denied();
        }
    
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'classes/index');
        $data['title'] = 'Edit Class';
        $data['id'] = $id;
        $vehroute = $this->classsection_model->getByID($id);
        $data['vehroute'] = $vehroute;
        $data['title_list'] = 'Class List';
    
        // Updated validation rules
        $this->form_validation->set_rules('class', $this->lang->line('academic_level'), 'required|trim|xss_clean|callback_check_class_unique');
        $this->form_validation->set_rules('sections[]', $this->lang->line('sections'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('degree', $this->lang->line('degree_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_programs', $this->lang->line('programs'), 'trim|required|xss_clean');
    
        if ($this->form_validation->run() == false) {
            // Load view with errors
            $vehicle_result = $this->section_model->get();
            $data['vehiclelist'] = $vehicle_result;
            $routeList = $this->route_model->get();
            $data['routelist'] = $routeList;
            $vehroute_result = $this->classsection_model->getByID();
            $data['vehroutelist'] = $vehroute_result;
    
            // Get all programs for editing
            $data['all_programs'] = $this->program_model->getAllActivePrograms();
            
            // Get existing section-program relationships - THIS IS THE KEY FIX
            $data['existing_section_programs'] = $this->classsection_model->getExistingSectionPrograms($id);
            
            // Debug: Log the existing relationships
            log_message('debug', 'Existing section programs for class ' . $id . ': ' . print_r($data['existing_section_programs'], true));
    
            $this->load->view('layout/header', $data);
            $this->load->view('class/classEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // Handle form submission and saving
            $sections = $this->input->post('sections');
            $section_programs_raw = $this->input->post('section_programs');
            $class_id = $this->input->post('pre_class_id');
    
            // Parse section_programs
            $section_programs = $this->parseSectionPrograms($section_programs_raw);
    
            // Update class info
            $class_array = array(
                'class' => $this->input->post('class'),
                'degree' => $this->input->post('degree'),
                'updated_at' => date('Y-m-d H:i:s')
            );
    
            $result = $this->updateClassWithSectionsAndPrograms($class_id, $class_array, $sections, $section_programs);
    
            if ($result) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error updating class. Please try again.</div>');
            }
            redirect('classes/index');
        }
    }
   
    public function get_section($id)
    {
        $data['sections'] = $this->class_model->get_section($id);
        $this->load->view('class/_section_list', $data);
    }
   
    public function get_programs_by_sections()
    {
        header('Content-Type: application/json');
        
        $section_ids = $this->input->post('section_ids');
        
        log_message('debug', 'get_programs_by_sections called with section_ids: ' . print_r($section_ids, true));
        
        if (!empty($section_ids) && is_array($section_ids)) {
            try {
                // Clean and validate section IDs
                $section_ids = array_map('intval', array_filter($section_ids));
                
                if (empty($section_ids)) {
                    echo json_encode(array('error' => 'Invalid section IDs'));
                    return;
                }
                
                // Main query to get programs for selected sections (removed is_active logic)
                $this->db->select('p.id, p.title, p.code, sp.section_id, s.section as section_name');
                $this->db->from('section_programs sp');
                $this->db->join('programs p', 'p.id = sp.program_id', 'inner');
                $this->db->join('sections s', 's.id = sp.section_id', 'inner');
                $this->db->where_in('sp.section_id', $section_ids);
                $this->db->order_by('sp.section_id ASC, p.title ASC');
                
                $query = $this->db->get();
                
                if ($query === FALSE) {
                    $db_error = $this->db->error();
                    log_message('error', 'Database query failed: ' . print_r($db_error, true));
                    echo json_encode(array('error' => 'Database query failed: ' . $db_error['message']));
                    return;
                }
                
                $programs = $query->result_array();
                
                // DEBUG: Log the programs returned
                log_message('debug', 'Programs returned from query: ' . print_r($programs, true));
                
                // Return programs regardless of count
                echo json_encode(array(
                    'success' => true, 
                    'data' => $programs,
                    'message' => count($programs) > 0 ? 'Programs loaded successfully' : 'No programs found for selected sections'
                ));
                
            } catch (Exception $e) {
                log_message('error', 'Exception in get_programs_by_sections: ' . $e->getMessage());
                echo json_encode(array('error' => 'An error occurred while fetching programs: ' . $e->getMessage()));
            }
        } else {
            log_message('debug', 'No section_ids provided or invalid format. Received: ' . print_r($this->input->post(), true));
            echo json_encode(array('error' => 'No section IDs provided'));
        }
    }
   
    /**
     * Get programs for a specific section via AJAX
     */
    public function get_section_programs()
    {
        $section_id = $this->input->post('section_id');
       
        if (!empty($section_id)) {
            // Get programs associated with this section from section_programs table
            $this->db->select('p.id, p.title, p.code');
            $this->db->from('section_programs sp');
            $this->db->join('programs p', 'p.id = sp.program_id');
            $this->db->where('sp.section_id', $section_id);
            $this->db->where('p.is_active', 'yes');
            $query = $this->db->get();
           
            echo json_encode($query->result_array());
        } else {
            echo json_encode(array());
        }
    }

    public function getBySectionAndProgram() {
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');

        $this->load->model('class_model');
        $classes = $this->class_model->getBySectionAndProgram($section_id, $program_id);

        echo json_encode($classes);
    }
}