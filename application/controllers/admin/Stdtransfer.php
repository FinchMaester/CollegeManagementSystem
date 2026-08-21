<?php




if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}




class Stdtransfer extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("classteacher_model");
        $this->load->model("program_model");
        $this->load->model("class_model");
        $this->load->model("studentresult_model");
        $this->load->helper('hemis_sync_post_from_db');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }




    public function index()
    {
        if (!$this->rbac->hasPrivilege('promote_student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'stdtransfer/index');
        $data['title']           = 'Exam Schedule';
        $class                   = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist']       = $class;
        $userdata                = $this->customlib->getUserData();
        $data['sch_setting']     = $this->sch_setting_detail;
        $session_result          = $this->session_model->get();
        $data['sessionlist']     = $session_result;
        $final_year_class        = $this->class_model->getFinalYearClass();
        $data['final_year_class'] = $final_year_class;
       
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|xss_clean');
       
        // Graduation = final academic level by name/degree (same rules as UI), not max class id.
        $posted_class_id = (int) $this->input->post('class_id');
        $is_graduation = $posted_class_id > 0
            && $this->class_model->isGraduationEligibleClassId($posted_class_id);
        $data['is_graduation'] = $is_graduation;

        if (!$is_graduation) {
            // Promote flow needs destination class/section/program + session
            $this->form_validation->set_rules('class_promote_id', $this->lang->line('class'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('section_promote_id', $this->lang->line('section'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('program_promote_id', $this->lang->line('program'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        } else {
            // Graduation still needs a target session for the graduate action
            $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        }

        if ($this->form_validation->run() == true) {
            try {
                $class = $this->input->post('class_id');
                $section = $this->input->post('section_id');
                $program = $this->input->post('program_id');
            
                log_message('debug', 'Form validation passed with: class=' . $class . ', section=' . $section . ', program=' . $program);
            
                $data['class_post'] = $class;
                $data['section_post'] = $section;
                $data['program_post'] = $program;
            
                if ($is_graduation) {
                    // Get students for graduation
                    log_message('debug', 'Calling getStudentsForGraduation with class=' . $class . ', section=' . $section . ', program=' . $program);
                    $resultlist = $this->student_model->getStudentsForGraduation($class, $section, $program);
                    log_message('debug', 'Found ' . count($resultlist) . ' students for graduation');
                
                    // Check if any records were found
                    if (empty($resultlist)) {
                        log_message('debug', 'No records found for graduation. SQL: ' . $this->db->last_query());
                    }
                } else {
                    // Get students for promotion
                    $session = $this->input->post('session_id');
                    $class_promote = $this->input->post('class_promote_id');
                    $section_promote = $this->input->post('section_promote_id');
                    $program_promote = $this->input->post('program_promote_id');

                
                    $data['class_promoted_post'] = $class_promote;
                    $data['section_promoted_post'] = $section_promote;
                    $data['program_promoted_post'] = $program_promote;
                    $data['session_promoted_post'] = $session;
                
                    $resultlist = $this->student_model->searchNonPromotedStudents(
                        $class, $section, $session,
                        $class_promote, $section_promote,
                        $program, $program_promote
                    );
                
                    log_message('debug', 'Found ' . count($resultlist) . ' non-promoted students');
                
                    // Check if any records were found
                    if (empty($resultlist)) {
                        log_message('debug', 'No records found for promotion. SQL: ' . $this->db->last_query());
                    }
                }
            
                $data['resultlist'] = $resultlist;
            
            } catch (Exception $e) {
                log_message('error', 'Error in searching students: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'An error occurred while searching for students');
                $data['resultlist'] = array();
            }
        }
       
        $this->load->view('layout/header', $data);
        $this->load->view('admin/stdtransfer/stdtransfer', $data);
        $this->load->view('layout/footer', $data);
    }




    public function promote()
    {
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('class_promote_id', $this->lang->line('class'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('section_promote_id', $this->lang->line('section'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('program_promote_id', $this->lang->line('program'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_list[]', $this->lang->line('student'), 'required|trim|xss_clean');
       
        if ($this->form_validation->run() == false) {
            $errors = array(
                'session_id'         => form_error('session_id'),
                'class_promote_id'   => form_error('class_promote_id'),
                'section_promote_id' => form_error('section_promote_id'),
                'program_promote_id' => form_error('program_promote_id'),
                'student_list'       => form_error('student_list[]'),
            );
            echo json_encode(array('status' => 'fail', 'msg' => $errors));
        } else {
            $student_list    = $this->input->post('student_list');
            $current_session = $this->setting_model->getCurrentSession();
    
            $class_post   = $this->input->post('class_post');
            $section_post = $this->input->post('section_post');
            $program_post = $this->input->post('program_post');
    
            $hemis_upgrade_payload = array();
            $hemis_upgrade_local_ids = array();
            $hemis_upgrade_skipped = array();
            $hemis_dropout_queue = array();
            $hemis_dropout_results = array();
            $hemis_dropout_skipped = array();

            if (!empty($student_list) && isset($student_list)) {
                foreach ($student_list as $key => $value) {
                    $student_id     = $value;
                    $result         = "pass";
                    $session_status = $this->input->post('next_working_' . $value);
                   
                    if ($result == "pass" && $session_status == "countinue") {
                        $promoted_class   = $this->input->post('class_promote_id');
                        $promoted_section = $this->input->post('section_promote_id');
                        $promoted_program = $this->input->post('program_promote_id');
                        $promoted_session = $this->input->post('session_id');
                       
                        // Update old record - set is_active to 'no' and mark as passed
                        $this->db->where('student_id', $student_id);
                        $this->db->where('session_id', $current_session);
                        $this->db->where('class_id', $class_post);
                        $this->db->where('section_id', $section_post);
                        $this->db->where('program_id', $program_post);
                        $this->db->update('student_session', array(
                            'is_pass' => 0,
                            'is_fail' => 0,
                            'is_active' => 'no'  // Set old record as inactive
                        ));
                        
                        // Create new record for promoted session (placement SoT + year-scoped HEMIS fields)
                        $data_new = array(
                            'student_id'     => $student_id,
                            'class_id'       => $promoted_class,
                            'section_id'     => $promoted_section,
                            'program_id'     => $promoted_program,
                            'session_id'     => $promoted_session,
                            'transport_fees' => 0,
                            'fees_discount'  => 0,
                            'is_pass'        => 0,  
                            'is_fail'        => 0,  
                            'is_alumni'      => 0,
                            'is_active'      => 'yes'  // New record is active
                        );
                        if ($this->db->field_exists('ugc_fiscal_year_id', 'student_session')) {
                            $fy = 0;
                            if ($this->db->field_exists('ugc_fiscal_year_id', 'sessions')) {
                                $sessFy = $this->db->select('ugc_fiscal_year_id')->from('sessions')->where('id', (int) $promoted_session)->limit(1)->get()->row_array();
                                $fy = !empty($sessFy['ugc_fiscal_year_id']) ? (int) $sessFy['ugc_fiscal_year_id'] : 0;
                            }
                            if ($fy < 1 && $this->db->table_exists('ugc_fiscal_years')) {
                                $activeFy = $this->db->select('id')->from('ugc_fiscal_years')->where('active_fiscal_year', 1)->order_by('id', 'DESC')->limit(1)->get()->row_array();
                                $fy = !empty($activeFy['id']) ? (int) $activeFy['id'] : 0;
                            }
                            if ($fy > 0) {
                                $data_new['ugc_fiscal_year_id'] = $fy;
                            }
                        }
                       
                        $this->student_model->add_student_session($data_new);

                        // UGC year/semester from destination class + student's ugc_program_id (create-path rules).
                        $upgradeBuild = hemis_student_build_upgrade_row_after_promote(
                            (int) $student_id,
                            (int) $promoted_class
                        );

                        if (!empty($upgradeBuild['ok']) && !empty($upgradeBuild['semister_or_year_no'])
                            && $this->db->field_exists('semister_or_year_no', 'student_session')) {
                            $this->db->where('student_id', (int) $student_id)
                                ->where('session_id', (int) $promoted_session)
                                ->update('student_session', array(
                                    'semister_or_year_no' => $upgradeBuild['semister_or_year_no'],
                                ));
                            $data_new['semister_or_year_no'] = $upgradeBuild['semister_or_year_no'];
                        }
                       
                        // Legacy students.* placement cache only (SoT is student_session)
                        $student_update_data = array(
                            'class_id'   => $promoted_class,
                            'section_id' => $promoted_section,
                            'program_id' => $promoted_program
                        );
                        if (!empty($upgradeBuild['ok']) && !empty($upgradeBuild['semister_or_year_no'])
                            && $this->db->field_exists('semister_or_year_no', 'students')) {
                            $student_update_data['semister_or_year_no'] = $upgradeBuild['semister_or_year_no'];
                        }
                        if (!empty($data_new['ugc_fiscal_year_id'])) {
                            if ($this->db->field_exists('ugc_fiscal_year_id', 'students')) {
                                $student_update_data['ugc_fiscal_year_id'] = (int) $data_new['ugc_fiscal_year_id'];
                            }
                            if ($this->db->field_exists('fiscal_year_id', 'students')) {
                                $student_update_data['fiscal_year_id'] = (int) $data_new['ugc_fiscal_year_id'];
                            }
                        }
                        $this->student_model->update($student_id, $student_update_data);

                        if (!empty($upgradeBuild['ok']) && !empty($upgradeBuild['row'])) {
                            $hemis_upgrade_payload[] = $upgradeBuild['row'];
                            $hemis_upgrade_local_ids[] = (int) $student_id;
                        } else {
                            $hemis_upgrade_skipped[] = array(
                                'student_id' => (int) $student_id,
                                'reason'     => isset($upgradeBuild['skip_reason'])
                                    ? $upgradeBuild['skip_reason']
                                    : 'Skipped HEMIS StudentUpgrade.',
                            );
                        }
                       
                    } elseif ($result == "fail" && $session_status == "countinue") {
                        $promoted_session = $this->input->post('session_id');
                        $class_post       = $this->input->post('class_post');
                        $section_post     = $this->input->post('section_post');
                        $program_post     = $this->input->post('program_post');
                       
                        // Update old record - set is_active to 'no' and mark as failed
                        $this->db->where('student_id', $student_id);
                        $this->db->where('session_id', $current_session);
                        $this->db->where('class_id', $class_post);
                        $this->db->where('section_id', $section_post);
                        $this->db->where('program_id', $program_post);
                        $this->db->update('student_session', array(
                            'is_pass' => 0,
                            'is_fail' => 1,
                            'is_active' => 'no'  // Set old record as inactive
                        ));
                        
                        // Create new record for the same class (student repeats)
                        $data_new = array(
                            'student_id'     => $student_id,
                            'class_id'       => $class_post,     
                            'section_id'     => $section_post,    
                            'program_id'     => $program_post,    
                            'session_id'     => $promoted_session,
                            'transport_fees' => 0,
                            'fees_discount'  => 0,
                            'is_pass'        => 0, 
                            'is_fail'        => 0,  
                            'is_alumni'      => 0,
                            'is_active'      => 'yes'  // New record is active
                        );
                        $this->student_model->add_student_session($data_new);
                       
                    } elseif ($session_status == "leave" || $session_status == "dropout") {
                        // Dropout on Promote page (legacy radio value "leave" still accepted).
                        // Do NOT mark alumni — that is for graduation only.
                        $dropout_reason = trim((string) $this->input->post('dropout_reason'));
                        if ($dropout_reason === '') {
                            $dropout_reason = 'Dropped out via Promote Students';
                        }
                        $dropout_remarks = trim((string) $this->input->post('dropout_remarks'));
                        if ($dropout_remarks === '') {
                            $dropout_remarks = $dropout_reason;
                        }
                        $dropout_date = trim((string) $this->input->post('dropout_date'));
                        if ($dropout_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dropout_date)) {
                            $dropout_date = date('Y-m-d');
                        }

                        $this->db->where('student_id', $student_id);
                        $this->db->where('session_id', $current_session);
                        $this->db->where('class_id', $class_post);
                        $this->db->where('section_id', $section_post);
                        $this->db->where('program_id', $program_post);
                        $this->db->update('student_session', array(
                            'is_leave'   => 1,
                            'is_alumni'  => 0,
                            'is_active'  => 'no',
                            'updated_at' => date('Y-m-d'),
                        ));

                        $student_disable = array(
                            'is_active'  => 'no',
                            'dis_reason' => substr($dropout_reason, 0, 255),
                            'dis_note'   => $dropout_remarks,
                            'disable_at' => $dropout_date,
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        $this->student_model->disableStudent((int) $student_id, $student_disable);

                        $this->db->where('user_id', (int) $student_id);
                        $this->db->where('role', 'student');
                        $this->db->update('users', array('is_active' => 'no'));

                        $hemis_dropout_queue[] = array(
                            'student_id' => (int) $student_id,
                            'input'      => array(
                                'studentId'   => (int) $student_id,
                                'reason'      => $dropout_reason,
                                'dateOfEntry' => $dropout_date,
                                'remarks'     => $dropout_remarks,
                            ),
                        );
                    }
                }
            }

            $hemis_sync = array(
                'synced'  => false,
                'mode'    => 'disabled',
                'error'   => null,
                'queued'  => count($hemis_upgrade_payload),
                'skipped' => $hemis_upgrade_skipped,
                'dropout' => array(
                    'queued'  => count($hemis_dropout_queue),
                    'synced'  => 0,
                    'failed'  => 0,
                    'skipped' => array(),
                    'error'   => null,
                ),
            );

            if (!empty($hemis_upgrade_payload) || !empty($hemis_dropout_queue)) {
                $this->load->library('hemis_client');
            }

            if (!empty($hemis_upgrade_payload)) {
                $hemis_out = $this->hemis_client->sync_student_upgrade_batch($hemis_upgrade_payload);
                if (is_array($hemis_out)) {
                    $hemis_sync = array_merge($hemis_sync, $hemis_out);
                    $hemis_sync['queued'] = count($hemis_upgrade_payload);
                    $hemis_sync['skipped'] = $hemis_upgrade_skipped;
                    if (!isset($hemis_sync['dropout'])) {
                        $hemis_sync['dropout'] = array(
                            'queued' => count($hemis_dropout_queue),
                            'synced' => 0,
                            'failed' => 0,
                            'skipped' => array(),
                            'error' => null,
                        );
                    }
                }

                $errMsg = (!empty($hemis_sync['error'])) ? trim((string) $hemis_sync['error']) : '';
                if ($errMsg !== '' && $this->db->field_exists('hemis_sync_error', 'students')) {
                    foreach ($hemis_upgrade_local_ids as $lid) {
                        $fields = array('hemis_sync_error' => substr($errMsg, 0, 500));
                        if ($this->db->field_exists('hemis_sync_error_at', 'students')) {
                            $fields['hemis_sync_error_at'] = date('Y-m-d H:i:s');
                        }
                        $this->student_model->update_hemis_fields((int) $lid, $fields);
                    }
                } elseif (!empty($hemis_sync['synced']) && $this->db->field_exists('hemis_sync_error', 'students')) {
                    foreach ($hemis_upgrade_local_ids as $lid) {
                        $fields = array(
                            'hemis_sync_error' => null,
                            'hemis_sync_at'    => date('Y-m-d H:i:s'),
                        );
                        if ($this->db->field_exists('hemis_sync_error_at', 'students')) {
                            $fields['hemis_sync_error_at'] = null;
                        }
                        $this->student_model->update_hemis_fields((int) $lid, $fields);
                    }
                }
            }

            if (!empty($hemis_dropout_queue)) {
                $dropSynced = 0;
                $dropFailed = 0;
                $dropErrors = array();
                foreach ($hemis_dropout_queue as $item) {
                    $lid = (int) $item['student_id'];
                    $row = $this->student_model->get_student_by_id($lid);
                    if (empty($row['hemis_student_id']) || (int) $row['hemis_student_id'] < 1) {
                        $hemis_dropout_skipped[] = array(
                            'student_id' => $lid,
                            'reason'     => 'Missing hemis_student_id — local dropout saved only.',
                        );
                        continue;
                    }
                    $dout = $this->hemis_client->sync_dropout_post($lid, $item['input']);
                    $hemis_dropout_results[] = array('student_id' => $lid, 'result' => $dout);
                    if (!empty($dout['synced'])) {
                        $dropSynced++;
                    } elseif (!empty($dout['error'])) {
                        $dropFailed++;
                        $dropErrors[] = $dout['error'];
                    }
                }
                $hemis_sync['dropout'] = array(
                    'queued'  => count($hemis_dropout_queue),
                    'synced'  => $dropSynced,
                    'failed'  => $dropFailed,
                    'skipped' => $hemis_dropout_skipped,
                    'error'   => !empty($dropErrors) ? $dropErrors[0] : null,
                );
            }

            echo json_encode(array(
                'status'     => 'success',
                'msg'        => '',
                'hemis_sync' => $hemis_sync,
            ));
        }
    }

    public function graduate()
    {

    $this->form_validation->set_rules('class_post', $this->lang->line('class'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('section_post', $this->lang->line('section'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('program_post', $this->lang->line('program'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('student_list[]', $this->lang->line('student'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean');
   
    if ($this->form_validation->run() == false) {
        $errors = array(
            'class_post'    => form_error('class_post'),
            'section_post'  => form_error('section_post'),
            'program_post'  => form_error('program_post'),
            'student_list'  => form_error('student_list[]'),
            'session_id'    => form_error('session_id'),
        );
        echo json_encode(array('status' => 'fail', 'msg' => $errors));
    } else {
        $student_list         = $this->input->post('student_list');
        $graduation_session   = $this->input->post('session_id');
        $class_post          = $this->input->post('class_post');
        $section_post        = $this->input->post('section_post');
        $program_post        = $this->input->post('program_post');
        $current_session     = $this->setting_model->getCurrentSession();
        $graduated_local_ids = array();
       
        if (!empty($student_list) && isset($student_list)) {
            $this->db->trans_start();
            
            foreach ($student_list as $key => $value) {
                $student_id = $value;
                
                $current_record = $this->db->get_where('student_session', array(
                    'student_id' => $student_id,
                    'session_id' => $current_session,
                    'class_id' => $class_post,
                    'section_id' => $section_post,
                    'program_id' => $program_post,
                    'is_active' => 'yes'
                ))->row();
            
               
                if ($current_record) {
                    $this->db->where('student_id', $student_id);
                    $this->db->where('session_id', $current_session);
                    $this->db->where('class_id', $class_post);
                    $this->db->where('section_id', $section_post);
                    $this->db->where('program_id', $program_post);
                    $this->db->where('is_active', 'yes');
                    $update_result = $this->db->update('student_session', array(
                        'is_pass' => 0,
                        'is_fail' => 0,
                        'is_active' => 'no',
                        'is_alumni' => 0, 
                        'updated_at' => date('Y-m-d')
                    ));

                    $graduation_data = array(
                        'student_id'     => $student_id,
                        'session_id'     => $graduation_session,
                        'class_id'       => $class_post,
                        'section_id'     => $section_post,
                        'program_id'     => $program_post,
                        'transport_fees' => 0,
                        'fees_discount'  => 0,
                        'is_leave'       => 0,
                        'is_graduated'   => 1,
                        'is_active'      => 'yes',  
                        'is_alumni'      => 1,
                        'is_pass'        => 1,   
                        'is_fail'        => 0,
                        'updated_at'     => date('Y-m-d')
                    );
                    if ($this->db->field_exists('ugc_fiscal_year_id', 'student_session')
                        && $this->db->field_exists('ugc_fiscal_year_id', 'sessions')) {
                        $sessFy = $this->db->select('ugc_fiscal_year_id')->from('sessions')
                            ->where('id', (int) $graduation_session)->limit(1)->get()->row_array();
                        if (!empty($sessFy['ugc_fiscal_year_id'])) {
                            $graduation_data['ugc_fiscal_year_id'] = (int) $sessFy['ugc_fiscal_year_id'];
                        }
                    }

                    $insert_result = $this->student_model->add_student_session($graduation_data);
                    
                    $alumni_data = array(
                        'student_id' => $student_id,
                        'is_alumni'  => 1,
                    );
                    
                    $alumni_result = $this->student_model->alumni_student_status(['student_id' => $student_id]);

                    if ($this->db->field_exists('complition_year', 'students')) {
                        $passed_year = (int) date('Y');
                        $this->student_model->update((int) $student_id, array(
                            'complition_year' => $passed_year,
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ));
                    }

                    $graduated_local_ids[] = (int) $student_id;
                    
                } else {
                    log_message('error', 'No active record found for student ' . $student_id . ' in current session');
                }
            }

            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array('status' => 'fail', 'msg' => 'Database error occurred during graduation'));
            } else {
                $hemis_sync = array(
                    'synced'  => 0,
                    'failed'  => 0,
                    'queued'  => count($graduated_local_ids),
                    'skipped' => array(),
                    'error'   => null,
                    'mode'    => 'disabled',
                );

                if (!empty($graduated_local_ids)) {
                    $this->load->library('hemis_client');
                    $errors = array();
                    foreach ($graduated_local_ids as $lid) {
                        $gout = $this->hemis_client->sync_graduation_generate_application_from_db($lid, array(
                            'Remarks'    => 'Campus graduate via Promote Students',
                            'PassedYear' => (string) date('Y'),
                        ));
                        if (!empty($gout['skipped'])) {
                            $hemis_sync['skipped'][] = array(
                                'student_id' => $lid,
                                'reason'     => isset($gout['skip_reason']) ? $gout['skip_reason'] : 'Skipped',
                            );
                            continue;
                        }
                        if (!empty($gout['mode'])) {
                            $hemis_sync['mode'] = $gout['mode'];
                        }
                        if (!empty($gout['synced'])) {
                            $hemis_sync['synced']++;
                        } elseif (!empty($gout['error'])) {
                            $hemis_sync['failed']++;
                            $errors[] = $gout['error'];
                        }
                    }
                    if (!empty($errors)) {
                        $hemis_sync['error'] = $errors[0];
                    }
                }

                echo json_encode(array(
                    'status'     => 'success',
                    'msg'        => 'Students have been successfully graduated!',
                    'hemis_sync' => $hemis_sync,
                ));
            }
        } else {
            echo json_encode(array('status' => 'fail', 'msg' => array('student_list' => 'No students selected')));
        }
    }
}

public function student_results() {
    
    $data['title'] = 'Student Results';
    $data['title_list'] = 'Mark Student Results';
    
    // Get all sessions for dropdown
    $data['sessionlist'] = $this->session_model->get();
    
    $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
    $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|xss_clean');
    $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
    $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
    
    if ($this->form_validation->run() == FALSE) {
        $this->load->view('layout/header', $data);
        $this->load->view('admin/stdtransfer/student_results', $data);
        $this->load->view('layout/footer', $data);
    } else {
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');
        $class_id = $this->input->post('class_id');
        $session_id = $this->input->post('session_id');
        
        // Get promoted students for the selected criteria
        $data['resultlist'] = $this->studentresult_model->getPromotedStudents($section_id, $program_id, $class_id, $session_id);
        $data['section_post'] = $section_id;
        $data['program_post'] = $program_id;
        $data['class_post'] = $class_id;
        $data['session_post'] = $session_id;
        $data['sessionlist'] = $this->session_model->get();
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/stdtransfer/student_results', $data);
        $this->load->view('layout/footer', $data);
    }
}

/**
 * Save student results (pass/fail)
 */
public function save_student_results() {

    $student_list = $this->input->post('student_list');
    $section_id = $this->input->post('section_post');
    $program_id = $this->input->post('program_post');
    $class_id = $this->input->post('class_post');
    $session_id = $this->input->post('session_post');
    
    if (empty($student_list)) {
        $array = array('status' => 'fail', 'msg' => array($this->lang->line('no_students_selected')));
        echo json_encode($array);
        return;
    }
    
    $results_data = array();
    foreach ($student_list as $student_id) {
        $result_status = $this->input->post('result_' . $student_id);
        
        $results_data[] = array(
            'student_id' => $student_id,
            'section_id' => $section_id,
            'program_id' => $program_id,
            'class_id' => $class_id,
            'session_id' => $session_id,
            'result_status' => $result_status // 'pass' or 'fail'
        );
    }
    
    $result = $this->studentresult_model->saveStudentResults($results_data);
    
    if ($result) {
        $array = array('status' => 'success', 'msg' => $this->lang->line('student_results_saved_successfully'));
    } else {
        $array = array('status' => 'fail', 'msg' => array($this->lang->line('something_went_wrong')));
    }
    
    echo json_encode($array);
}

public function getPromotedStudentsForResults() {
    $section_id = $this->input->post('section_id');
    $program_id = $this->input->post('program_id');
    $class_id = $this->input->post('class_id');
    $session_id = $this->input->post('session_id');

    $this->load->model('studentresult_model');
    $students = $this->studentresult_model->getPromotedStudents($section_id, $program_id, $class_id, $session_id);

    echo json_encode(array('status' => 'success', 'data' => $students));
}

    /**
     * Preview promote / graduate history and allow guarded revert.
     */
    public function history()
    {
        if (!$this->rbac->hasPrivilege('promote_student', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'stdtransfer/index');

        $data['title']       = 'Promote / Graduate History';
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['sessionlist'] = $this->session_model->get();
        $data['classlist']   = $this->class_model->get('', $classteacher = 'yes');
        $data['action_type'] = $this->input->get_post('action_type') ?: 'promote';
        if (!in_array($data['action_type'], array('promote', 'graduate'), true)) {
            $data['action_type'] = 'promote';
        }

        $data['filters'] = array(
            'session_id'  => (int) $this->input->get_post('session_id'),
            'section_id'  => (int) $this->input->get_post('section_id'),
            'program_id'  => (int) $this->input->get_post('program_id'),
            'class_id'    => (int) $this->input->get_post('class_id'),
            'search_text' => trim((string) $this->input->get_post('search_text')),
        );

        $data['resultlist'] = array();
        $data['searched']   = false;

        if ($this->input->server('REQUEST_METHOD') === 'POST' || $this->input->get('search') === '1') {
            $data['searched'] = true;
            if ($data['action_type'] === 'graduate') {
                $data['resultlist'] = $this->student_model->getGraduateHistory($data['filters']);
            } else {
                $data['resultlist'] = $this->student_model->getPromoteHistory($data['filters']);
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/stdtransfer/history', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX: revert promote or graduate with confirmation token.
     */
    public function revert()
    {
        if (!$this->rbac->hasPrivilege('promote_student', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'msg' => 'Access denied'));
            return;
        }

        $action_type = $this->input->post('action_type');
        $confirm     = strtoupper(trim((string) $this->input->post('confirm_text')));
        $student_id  = (int) $this->input->post('student_id');
        $from_id     = (int) $this->input->post('from_session_row_id');
        $to_id       = (int) $this->input->post('to_session_row_id');
        $allow_fees  = $this->input->post('allow_with_fees') === '1' || $this->input->post('allow_with_fees') === 'true';

        if ($confirm !== 'REVERT') {
            echo json_encode(array(
                'status' => 'fail',
                'msg'    => 'Type REVERT in the confirmation box to continue.',
            ));
            return;
        }

        if ($action_type === 'promote') {
            $result = $this->student_model->revertPromotion($student_id, $from_id, $to_id, $allow_fees);
        } elseif ($action_type === 'graduate') {
            $result = $this->student_model->revertGraduation($student_id, $to_id, $from_id, $allow_fees);
        } else {
            echo json_encode(array('status' => 'fail', 'msg' => 'Unknown action type.'));
            return;
        }

        if (!empty($result['ok'])) {
            $msg = $result['message'];
            if (!empty($result['warnings'])) {
                $msg .= ' ' . implode(' ', $result['warnings']);
            }
            if (!empty($result['hemis_note'])) {
                $msg .= ' ' . $result['hemis_note'];
            }
            echo json_encode(array('status' => 'success', 'msg' => $msg));
            return;
        }

        echo json_encode(array(
            'status'            => 'fail',
            'msg'               => isset($result['message']) ? $result['message'] : 'Revert failed.',
            'fee_deposit_count' => isset($result['fee_deposit_count']) ? $result['fee_deposit_count'] : 0,
            'needs_fee_confirm' => !empty($result['fee_deposit_count']),
        ));
    }

    /**
     * AJAX: bulk revert selected promote/graduate rows.
     */
    public function revert_bulk()
    {
        if (!$this->rbac->hasPrivilege('promote_student', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'msg' => 'Access denied'));
            return;
        }

        $action_type = $this->input->post('action_type');
        $confirm     = strtoupper(trim((string) $this->input->post('confirm_text')));
        $allow_fees  = $this->input->post('allow_with_fees') === '1' || $this->input->post('allow_with_fees') === 'true';
        $items_raw   = $this->input->post('items');

        if ($confirm !== 'REVERT') {
            echo json_encode(array(
                'status' => 'fail',
                'msg'    => 'Type REVERT in the confirmation box to continue.',
            ));
            return;
        }

        if (!in_array($action_type, array('promote', 'graduate'), true)) {
            echo json_encode(array('status' => 'fail', 'msg' => 'Unknown action type.'));
            return;
        }

        $items = array();
        if (is_string($items_raw) && $items_raw !== '') {
            $decoded = json_decode($items_raw, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        } elseif (is_array($items_raw)) {
            $items = $items_raw;
        }

        if (empty($items)) {
            echo json_encode(array('status' => 'fail', 'msg' => 'No students selected.'));
            return;
        }

        // Cap batch size to avoid long-running requests.
        if (count($items) > 200) {
            echo json_encode(array('status' => 'fail', 'msg' => 'Please select at most 200 records at a time.'));
            return;
        }

        $success = 0;
        $failed  = 0;
        $skipped_fees = 0;
        $errors  = array();
        $warnings_all = array();

        foreach ($items as $item) {
            $student_id = isset($item['student_id']) ? (int) $item['student_id'] : 0;
            $from_id    = isset($item['from_session_row_id']) ? (int) $item['from_session_row_id'] : 0;
            $to_id      = isset($item['to_session_row_id']) ? (int) $item['to_session_row_id'] : 0;
            $label      = isset($item['label']) ? (string) $item['label'] : ('Student #' . $student_id);

            if ($student_id < 1 || $to_id < 1) {
                $failed++;
                $errors[] = $label . ': invalid identifiers.';
                continue;
            }

            if ($action_type === 'promote') {
                $result = $this->student_model->revertPromotion($student_id, $from_id, $to_id, $allow_fees);
            } else {
                $result = $this->student_model->revertGraduation($student_id, $to_id, $from_id, $allow_fees);
            }

            if (!empty($result['ok'])) {
                $success++;
                if (!empty($result['warnings'])) {
                    $warnings_all[] = $label . ': ' . implode(' ', $result['warnings']);
                }
            } else {
                $failed++;
                $msg = isset($result['message']) ? $result['message'] : 'Revert failed.';
                if (!empty($result['fee_deposit_count'])) {
                    $skipped_fees++;
                }
                $errors[] = $label . ': ' . $msg;
            }
        }

        $status = ($success > 0 && $failed === 0) ? 'success' : (($success > 0) ? 'partial' : 'fail');
        $msg = $success . ' reverted successfully';
        if ($failed > 0) {
            $msg .= ', ' . $failed . ' failed';
        }
        if ($skipped_fees > 0 && !$allow_fees) {
            $msg .= '. ' . $skipped_fees . ' need fee confirmation — enable “Allow with fee deposits” and try again for those.';
        }
        if ($action_type === 'promote' && $success > 0) {
            $msg .= ' If HEMIS StudentUpgrade was synced, correct HEMIS manually for reverted students.';
        }

        echo json_encode(array(
            'status'            => $status,
            'msg'               => $msg,
            'success_count'     => $success,
            'failed_count'      => $failed,
            'skipped_fees'      => $skipped_fees,
            'needs_fee_confirm' => ($skipped_fees > 0 && !$allow_fees),
            'errors'            => array_slice($errors, 0, 15),
            'warnings'          => array_slice($warnings_all, 0, 10),
        ));
    }
}