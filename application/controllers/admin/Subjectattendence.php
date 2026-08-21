<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subjectattendence extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->config->load("mailsms");
        $this->load->library('mailsmsconf');
        $this->config_attendance = $this->config->item('attendence');
        $this->load->model("classteacher_model");
    }    

    public function reportbydate()
    {
        $this->session->set_userdata('top_menu', 'Attendance');
        $this->session->set_userdata('sub_menu', 'subjectattendence/reportbydate');
        $data                = array();
        $data['sch_setting'] = $this->setting_model->getSetting();
        $data['attendance_report'] = null;
        $data['resultlist']        = null;
        
        // Load sections (faculties) for dropdown
        $this->load->model('section_model');
        $sections = $this->section_model->getAll();
        $data['sectionlist'] = $sections;
        
        $this->form_validation->set_rules('section_id', $this->lang->line('faculty'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('academic_level'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {
            $section_id                  = $this->input->post('section_id');
            $program_id                 = $this->input->post('program_id');
            $class_id                   = $this->input->post('class_id');
            $date                       = $this->input->post('date');
            
            // Convert date and get day - handle empty dates
            $date_timestamp = null;
            if ($date && !empty($date) && trim($date) !== '') {
                $date_timestamp = $this->customlib->datetostrtotime($date);
                if (!$date_timestamp || $date_timestamp === false || $date_timestamp === '') {
                    // Try to manually convert BS date
                    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
                        $year = intval($matches[1]);
                        if ($year >= 2070 && $year <= 2100) {
                            $ad_year = $year - 57;
                            $ad_date = sprintf('%04d-%02d-%02d', $ad_year, $matches[2], $matches[3]);
                            $date_timestamp = strtotime($ad_date);
                        }
                    }
                }
            }
            
            if ($date_timestamp && $date_timestamp !== false && $date_timestamp !== '') {
                $day = date('l', $date_timestamp);
                $date_ad = date('Y-m-d', $date_timestamp);
            } else {
                $day = null;
                $date_ad = null;
            }
            
            // Debug: Log what we're passing to the model
            $data['controller_debug'] = array(
                'class_id' => $class_id,
                'section_id' => $section_id,
                'program_id' => $program_id,
                'day' => $day,
                'date_raw' => $date,
                'date_ad' => $date_ad,
                'date_timestamp' => isset($date_timestamp) ? $date_timestamp : null
            );
            
            // Don't filter by subject - show all subjects for the date
            $resultlist = $this->studentsubjectattendence_model->searchByStudentsAttendanceByDate($class_id, $section_id, $day, $date_ad, '');
            $attendencetypes = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;
            $data['attendance_report'] = is_array($resultlist) ? $resultlist : null;
            $data['resultlist']        = is_string($resultlist) ? $resultlist : null;
            
            // Pass debug info to view
            $data['db_debug_info'] = isset($this->studentsubjectattendence_model->db_debug_info) ? $this->studentsubjectattendence_model->db_debug_info : array();
            $data['debug_info'] = array(
                'class_id' => $class_id,
                'section_id' => $section_id,
                'program_id' => $program_id,
                'date_raw' => $date,
                'date_ad' => $date_ad,
                'day' => $day,
                'resultlist_empty' => empty($resultlist) ? 'YES' : 'NO'
            );
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/subjectattendence/reportbydate', $data);
        $this->load->view('layout/footer', $data);
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'Attendance');
        $this->session->set_userdata('sub_menu', 'subjectattendence/index');
        $data['title']      = 'Add Fees Type';
        $data['title_list'] = 'Fees Type List';
        $data['sch_setting'] = $this->setting_model->getSetting();
        
        // Load sections (faculties) for dropdown
        $this->load->model('section_model');
        $sections = $this->section_model->getAll();
        $data['sectionlist'] = $sections;
        
        $data['class_id']    = "";
        $data['section_id']  = "";
        $data['program_id']  = "";
        $data['date']        = "";
        
        // Handle both POST and GET (for redirect after save)
        $class_id = $this->input->post('class_id') ?: $this->input->get('class_id');
        $section_id = $this->input->post('section_id') ?: $this->input->get('section_id');
        $program_id = $this->input->post('program_id') ?: $this->input->get('program_id');
        $date = $this->input->post('date') ?: $this->input->get('date');
        $subject_timetable_id = $this->input->post('subject_timetable_id') ?: $this->input->get('subject_timetable_id');
        
        $this->form_validation->set_rules('section_id', $this->lang->line('faculty'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('academic_level'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_timetable_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        
        // Set values for view (for both validation failure and success cases)
        $data['class_id']             = $class_id ?: "";
        $data['section_id']           = $section_id ?: "";
        $data['program_id']           = $program_id ?: "";
        $data['subject_timetable_id'] = $subject_timetable_id ?: "";
        $data['date']                 = $date ?: "";
        
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subjectattendence/attendenceList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $search                       = $this->input->post('search');
            $holiday                      = $this->input->post('holiday');
            
            if ($search == "saveattendence") {
                // Attendance rows must reference subject_timetable.id, never subjects.id
                $st_id = (int) $subject_timetable_id;
                $session_id = (int) $this->setting_model->getCurrentSession();
                if ($st_id <= 0) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('error_message') . ' (invalid subject period)</div>');
                    redirect('admin/subjectattendence/index?section_id=' . $section_id . '&program_id=' . $program_id . '&class_id=' . $class_id . '&date=' . urlencode($date) . '&subject_timetable_id=' . $subject_timetable_id);
                    return;
                }
                $st_ok = $this->db->where('id', $st_id)
                    ->where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('session_id', $session_id)
                    ->count_all_results('subject_timetable');
                if ($st_ok < 1) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('error_message') . ' (subject period does not match class/section/session)</div>');
                    redirect('admin/subjectattendence/index?section_id=' . $section_id . '&program_id=' . $program_id . '&class_id=' . $class_id . '&date=' . urlencode($date) . '&subject_timetable_id=');
                    return;
                }

                $session_ary         = $this->input->post('student_session');
                $absent_student_list = array();
                $insert_student_list = array();
                $update_student_list = array();
                
                // Convert date once instead of multiple times in the loop
                $date_converted = date('Y-m-d', $this->customlib->datetostrtotime($date));
                $absent_config = $this->config_attendance['absent'];
                
                foreach ($session_ary as $key => $value) {
                    $checkForUpdate = $this->input->post('attendance_id' . $value);
                    if ($checkForUpdate != 0) {
                        if (isset($holiday)) {
                            $arr = array(
                                'id'                   => $checkForUpdate,
                                'student_session_id'   => $value,
                                'attendence_type_id'   => 5,
                                'remark'               => $this->input->post("remark" . $value),
                                'subject_timetable_id' => $subject_timetable_id,
                                'date'                 => $date_converted,
                            );
                        } else {
                            $arr = array(
                                'id'                   => $checkForUpdate,
                                'student_session_id'   => $value,
                                'attendence_type_id'   => $this->input->post('attendencetype' . $value),
                                'remark'               => $this->input->post("remark" . $value),
                                'subject_timetable_id' => $subject_timetable_id,
                                'date'                 => $date_converted,
                            );
                        }
                        $update_student_list[] = $arr;
                    } else {
                        if (isset($holiday)) {
                            $arr = array(
                                'student_session_id'   => $value,
                                'attendence_type_id'   => 5,
                                'remark'               => $this->input->post("remark" . $value),
                                'subject_timetable_id' => $subject_timetable_id,
                                'date'                 => $date_converted,
                            );
                        } else {
                            $attendence_type_id = $this->input->post('attendencetype' . $value);
                            $arr = array(
                                'student_session_id'   => $value,
                                'attendence_type_id'   => $attendence_type_id,
                                'remark'               => $this->input->post("remark" . $value),
                                'subject_timetable_id' => $subject_timetable_id,
                                'date'                 => $date_converted,
                            );
                        }
                        $insert_student_list[] = $arr;
                        if ($arr['attendence_type_id'] == $absent_config) {
                            $absent_student_list[] = $value;
                        }
                    }
                }
                
                // Save attendance first
                $save_result = $this->studentsubjectattendence_model->add($insert_student_list, $update_student_list);
                
                if ($save_result) {
                    // Set success message first
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                    
                    // Prepare redirect URL
                    $redirect_url = 'admin/subjectattendence/index?section_id=' . $section_id . '&program_id=' . $program_id . '&class_id=' . $class_id . '&date=' . urlencode($date) . '&subject_timetable_id=' . $subject_timetable_id;
                    
                    // Send emails/SMS for absent students in background (non-blocking)
                    if (!empty($absent_student_list)) {
                        // Check if email/SMS is enabled before sending
                        $send_for_config = $this->config->item('mailsms');
                        if (isset($send_for_config['absent_attendence'])) {
                            $chk_mail_sms = $this->customlib->sendMailSMS($send_for_config['absent_attendence']);
                            
                            // Only send if email/SMS is actually enabled and configured
                            if ($chk_mail_sms && ($chk_mail_sms['mail'] || $chk_mail_sms['sms'] || $chk_mail_sms['notification'])) {
                                // Store data needed for background processing BEFORE redirect
                                $timetable = $this->subjecttimetable_model->get($subject_timetable_id);
                                
                                // Use fastcgi_finish_request to send response immediately, then send emails/SMS in background
                                if (function_exists('fastcgi_finish_request')) {
                                    // Send redirect header manually (CodeIgniter's redirect() exits, so we can't use it)
                                    $full_redirect_url = site_url($redirect_url);
                                    
                                    // Clear any existing output
                                    if (ob_get_level() > 0) {
                                        while (ob_get_level() > 0) {
                                            @ob_end_clean();
                                        }
                                    }
                                    
                                    // Send redirect headers
                                    header('Location: ' . $full_redirect_url, true, 302);
                                    header('Connection: close');
                                    header('Content-Length: 0');
                                    
                                    // Flush all output
                                    @flush();
                                    
                                    // Close connection to browser - user gets redirect immediately
                                    fastcgi_finish_request();
                                    
                                    // Now send emails/SMS in background (won't block user or cause timeout)
                                    try {
                                        // Increase timeout for background process (server allows this)
                                        set_time_limit(120);
                                        ignore_user_abort(true);
                                        $this->mailsmsconf->mailsms('absent_attendence', $absent_student_list, $date, $timetable);
                                    } catch (Exception $e) {
                                        // Log error but don't fail - user already got success
                                        log_message('error', 'Background email/SMS sending failed: ' . $e->getMessage());
                                    }
                                    
                                    // Exit after background processing
                                    exit;
                                } else {
                                    // Fallback for servers without fastcgi_finish_request
                                    // Skip email/SMS to prevent timeout - it's better to skip than cause 500 error
                                    log_message('info', 'Email/SMS skipped for absent students - fastcgi_finish_request not available. To prevent server timeout.');
                                    // Continue to redirect normally
                                }
                            }
                        }
                    }
                    
                    // Redirect with all search parameters to show the list with success message
                    // (Only reaches here if fastcgi_finish_request was not used)
                    redirect($redirect_url);
                } else {
                    // Save failed
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('error_message') . '</div>');
                    redirect('admin/subjectattendence/index?section_id=' . $section_id . '&program_id=' . $program_id . '&class_id=' . $class_id . '&date=' . urlencode($date) . '&subject_timetable_id=' . $subject_timetable_id);
                }
            }
            
            $attendencetypes             = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;

            $resultlist = $this->studentsubjectattendence_model->searchAttendenceClassSection($class_id, $section_id, $subject_timetable_id, date('Y-m-d', $this->customlib->datetostrtotime($date)));

            $data['resultlist'] = $resultlist;

            $this->load->view('layout/header', $data);
            $this->load->view('admin/subjectattendence/attendenceList', $data);
            $this->load->view('layout/footer', $data);
        }
    }

}
