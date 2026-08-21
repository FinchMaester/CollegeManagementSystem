<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Generateidcard extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('Customlib');
        $this->load->library('media_storage');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function search()
    {
        if (!$this->rbac->hasPrivilege('generate_id_card', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generateidcard');
    
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting'] = $this->sch_setting_detail;
        $idcardlist = $this->Generateidcard_model->getstudentidcard();
        $data['idcardlist'] = $idcardlist;
        
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/certificate/generateidcard', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $section_id = $this->input->post('section_id');
            $program_id = $this->input->post('program_id');
            $class_id = $this->input->post('class_id');
            $id_card = $this->input->post('id_card');
            $search = $this->input->post('search');
            
            if (isset($search)) {
                $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('id_card', $this->lang->line('id_card_template'), 'trim|required|xss_clean');
                
                if ($this->form_validation->run() == false) {
                    $data['section_id'] = $section_id;
                    $data['program_id'] = $program_id;
                    $data['class_id'] = $class_id;
                    $data['id_card'] = $id_card;
                } else {
                    $data['searchby'] = "filter";
                    $data['section_id'] = $section_id;
                    $data['program_id'] = $program_id;
                    $data['class_id'] = $class_id;
                    $data['id_card'] = $id_card;
                    
                    $idcardResult = $this->Generateidcard_model->getidcardbyid($id_card);
                    $data['idcardResult'] = $idcardResult;
                    
                    // Get students with program filtering
                    $resultlist = $this->student_model->searchByClassSection($class_id, $section_id);
                    
                    // Additional filtering by program if needed
                    if (!empty($program_id)) {
                        $resultlist = array_filter($resultlist, function($student) use ($program_id) {
                            return isset($student['program_id']) && $student['program_id'] == $program_id;
                        });
                    }
                    
                    $data['resultlist'] = $resultlist;
                    
                    if (empty($resultlist)) {
                        $this->session->set_flashdata('msg', '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><i class="fa fa-warning"></i> ' . $this->lang->line('no_record_found') . '</div>');
                    }
                }
            }
    
            $this->load->view('layout/header', $data);
            $this->load->view('admin/certificate/generateidcard', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function generate($student, $class, $idcard)
    {
        $idcardlist         = $this->Generateidcard_model->getidcardbyid($idcard);
        $data['idcardlist'] = $idcardlist;
        $resultlist         = $this->student_model->searchByClassStudent($class, $student);
        $data['resultlist'] = $resultlist;

        $this->load->view('admin/certificate/studentidcard', $data);
    }

    public function generatemultiple()
    {
        // Set proper content type for JSON response
        $this->output->set_content_type('application/json');
        
        try {
            // Log the incoming request for debugging
            log_message('debug', 'Generate multiple ID cards called');
            log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
            
            // Get POST data with validation
            $studentid = $this->input->post('data');
            $idcard = $this->input->post('id_card');
            $class = $this->input->post('class_id');
            
            // Validate JSON data
            if (empty($studentid)) {
                throw new Exception('No student data received');
            }
            
            $student_array = json_decode($studentid, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data: ' . json_last_error_msg());
            }
            
            // Validate input data
            if (empty($student_array) || !is_array($student_array)) {
                throw new Exception('No students selected or invalid data format');
            }
            
            if (empty($idcard) || !is_numeric($idcard)) {
                throw new Exception('ID card template is required and must be valid');
            }
            
            if (empty($class) || !is_numeric($class)) {
                throw new Exception('Class is required and must be valid');
            }
            
            // Initialize arrays
            $data = array();
            $std_arr = array();
            
            // Get school settings with error handling
            $school_settings = $this->setting_model->get();
            if (empty($school_settings)) {
                log_message('error', 'Failed to load school settings');
                $school_settings = array(); // Use empty array as fallback
            }
            $data['sch_setting'] = $school_settings;
            
            // Get ID card template with validation
            $id_card_data = $this->Generateidcard_model->getidcardbyid($idcard);
            if (empty($id_card_data)) {
                throw new Exception('ID card template not found or invalid');
            }
            $data['id_card'] = $id_card_data;
            
            // Extract and validate student IDs
            foreach ($student_array as $key => $value) {
                if (is_array($value) && isset($value['student_id']) && !empty($value['student_id'])) {
                    $student_id = intval($value['student_id']);
                    if ($student_id > 0) {
                        $std_arr[] = $student_id;
                    }
                } elseif (is_object($value) && isset($value->student_id) && !empty($value->student_id)) {
                    $student_id = intval($value->student_id);
                    if ($student_id > 0) {
                        $std_arr[] = $student_id;
                    }
                }
            }
            
            if (empty($std_arr)) {
                throw new Exception('No valid student IDs found in the selection');
            }
            
            log_message('debug', 'Processing ' . count($std_arr) . ' student IDs: ' . implode(', ', $std_arr));
            
            // Get students data with error handling
            $students = $this->student_model->getStudentsByArray($std_arr);
            if (empty($students)) {
                throw new Exception('No student records found for the selected IDs');
            }
            
            log_message('debug', 'Found ' . count($students) . ' student records');
            
            // Process and validate students data
            $valid_students = array();
            foreach ($students as $key => $student) {
                try {
                    // Ensure student is valid array/object
                    if (is_array($student) && isset($student['admission_no'])) {
                        // Add missing fields with defaults
                        $student['mother_name'] = $student['mother_name'] ?? '';
                        $student['current_address'] = $student['current_address'] ?? '';
                        $student['blood_group'] = $student['blood_group'] ?? '';
                        $student['program_name'] = $student['program_name'] ?? '';
                        
                        // Generate barcode if admission number exists
                        if (!empty($student['admission_no'])) {
                            try {
                                $student['barcode'] = $this->customlib->generatebarcode($student['admission_no']);
                            } catch (Exception $e) {
                                log_message('error', 'Failed to generate barcode for admission: ' . $student['admission_no'] . ' - ' . $e->getMessage());
                                $student['barcode'] = '';
                            }
                        } else {
                            $student['barcode'] = '';
                        }
                        
                        // Convert array to object for view compatibility
                        $valid_students[] = (object) $student;
                        
                    } else {
                        log_message('warning', 'Invalid student data at index ' . $key . ': ' . print_r($student, true));
                    }
                    
                } catch (Exception $e) {
                    log_message('error', 'Error processing student at index ' . $key . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            if (empty($valid_students)) {
                throw new Exception('No valid student data could be processed');
            }
            
            log_message('debug', 'Successfully processed ' . count($valid_students) . ' students');
            
            // Prepare data for view
            $data['students'] = $valid_students;
            $data['sch_settingdata'] = $this->sch_setting_detail;
            
            // Generate the ID cards HTML
            $id_cards_html = $this->load->view('admin/certificate/generatemultiple', $data, true);
            
            if (empty($id_cards_html)) {
                throw new Exception('Failed to generate ID cards HTML template');
            }
            
            // Log success
            log_message('debug', 'Successfully generated ID cards HTML (' . strlen($id_cards_html) . ' characters)');
            
            // Return success response
            $response = array(
                'status' => 1,
                'page' => $id_cards_html,
                'message' => 'ID cards generated successfully',
                'student_count' => count($valid_students)
            );
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            // Log the error
            log_message('error', 'Error in generatemultiple: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // Return error response
            $response = array(
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
                'debug_info' => array(
                    'post_data' => $this->input->post(),
                    'student_ids' => isset($std_arr) ? $std_arr : array(),
                    'students_found' => isset($students) ? count($students) : 0
                )
            );
            
            echo json_encode($response);
        }
    }

    /**
     * Helper method for debugging - can be removed in production
     */
    public function test_connection()
    {
        $this->output->set_content_type('application/json');
        echo json_encode(array(
            'status' => 'ok',
            'message' => 'Controller is accessible',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->server('REQUEST_METHOD'),
            'post_data' => $this->input->post()
        ));
    }
}