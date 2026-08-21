<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cron extends MY_Controller
{

    protected $cron_key;

    /**
     * This is default constructor of the class
     */
    public function __construct($key = "")
    {
        parent::__construct();
        $setting_result = $this->setting_model->getSetting();
        $this->cron_key = $setting_result->cron_secret_key;
        $this->load->model('feereminder_model');
        $this->load->model('calendar_model');
        $this->load->model('customfield_model');
        $this->load->model('class_section_time_model');
        $this->load->model('stuattendence_model');
        $this->load->model('student_model');
        $this->load->model('staff_model');
        $this->load->model('auth_model');
        $this->load->library('customlib');
        $this->load->helper('custom');
        if ($this->customlib->getTimeZone()) {
            date_default_timezone_set($this->customlib->getTimeZone());
        } else {
            return date_default_timezone_set('UTC');
        }
    }

    public function index($key = '')
    {

        if ($key != "" && $this->cron_key == $key) {

            $this->autobackup($key);
            $this->feereminder($key);
            $this->eventreminder($key);
            $this->schedulesmsemails($key);
        } else {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }
    }

    /**
     * Prune expired API bearer tokens (tokens table). Call from scheduler, same key as index.
     * Example: /cron/tokens_prune/YOUR_CRON_SECRET
     */
    public function tokens_prune($key = '')
    {
        if ($key === '' || $this->cron_key != $key) {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }
        $n = $this->auth_model->prune_expired_tokens(90);
        echo 'OK tokens pruned (affected rows this request: ' . $n . ')';
    }

    /**
     * Clear old hemis_sync_error strings (students table). Same cron key as tokens_prune.
     * Example: /cron/hemis_errors_prune/YOUR_CRON_SECRET
     */
    public function hemis_errors_prune($key = '')
    {
        if ($key === '' || $this->cron_key != $key) {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }
        $n = $this->auth_model->prune_stale_hemis_sync_errors(30);
        echo 'OK hemis_sync_error pruned (rows updated: ' . $n . '). Requires hemis_sync_error_at column; see application/sql/hemis_sync_error_at.sql';
    }

    /**
     * One-time production readiness for HEMIS (gender + program codes + optional cleanup).
     * Example: /cron/hemis_go_live/YOUR_CRON_SECRET?mode=report
     * Modes: report (default), fix_errors, fix_gender, fix_all
     */
    public function hemis_go_live($key = '')
    {
        if ($key === '' || $this->cron_key != $key) {
            echo 'Invalid Key or Direct access is not allowed';
            return;
        }
        $this->load->config('ugc_program_codes');
        $official = $this->config->item('ugc_official_program_codes');
        if (!is_array($official)) {
            $official = array();
        }
        $mode = isset($_GET['mode']) ? strtolower(trim((string) $_GET['mode'])) : 'report';

        $report = array();
        $report['mode'] = $mode;
        $report['cleared_dev_hemis_errors'] = 0;
        $report['gender_rows_normalized'] = 0;

        if ($mode === 'fix_errors') {
            $report['cleared_dev_hemis_errors'] = $this->auth_model->hemis_clear_leftover_dev_hemis_errors();
            $report = array_merge($report, $this->auth_model->hemis_go_live_readiness($official));
        } elseif ($mode === 'fix_gender') {
            $report['gender_rows_normalized'] = $this->auth_model->hemis_normalize_student_gender_to_ugc_int_strings();
            $report = array_merge($report, $this->auth_model->hemis_go_live_readiness($official));
        } elseif ($mode === 'fix_all') {
            $report['cleared_dev_hemis_errors'] = $this->auth_model->hemis_clear_leftover_dev_hemis_errors();
            $report['gender_rows_normalized'] = $this->auth_model->hemis_normalize_student_gender_to_ugc_int_strings();
            $report = array_merge($report, $this->auth_model->hemis_go_live_readiness($official));
        } else {
            $report = array_merge($report, $this->auth_model->hemis_go_live_readiness($official));
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }


    public function student_attendance($key=""){

           if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }

          $time=date('H:i:s');
          $date=date('Y-m-d');
          $class_sections=$this->class_section_time_model->getAttendanceNotSubmittedByTime($date,$time);
         
          if(!empty($class_sections)){
            $attendance_data=array();
                foreach ($class_sections as $class_key => $class_value) {
                    $attendance_data[]=[
                                        'student_session_id'=>$class_value->student_session_id,
                                        'date'=>date('Y-m-d'),
                                        'attendence_type_id'=>4,
                                      ];
             
                }
            $this->stuattendence_model->batch_insert($attendance_data);
          }

    }



    public function autobackup($key = '')
    {

        if ($key != "") {
            if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }

            $this->load->dbutil();
            $version  = $this->customlib->getAppVersion();
            $filename = "db_ver_" . $version . '_' . date("Y-m-d_H-i-s") . ".sql";
            $prefs    = array(
                'ignore'     => array(),
                'format'     => 'txt',
                'filename'   => 'mybackup.sql',
                'add_drop'   => true,
                'add_insert' => true,
                'newline'    => "\n",
            );
            $backup = $this->dbutil->backup($prefs);
            $this->load->helper('file');
            write_file('./backup/database_backup/' . $filename, $backup);
        }
    }

    public function feereminder($key = "")
    {
        $setting_result = $this->setting_model->getSetting();
        if ($key != "") {
            if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }
       
            $this->load->library('mailsmsconf');
            $feereminder   = $this->feereminder_model->get(null, 1);

            $reminter_type = array();
            $studentList   = array();

            if (!empty($feereminder)) {
                foreach ($feereminder as $feereminder_key => $feereminder_value) {
                    if ($feereminder_value->reminder_type == "before") {

                        $date               = date('Y-m-d', strtotime('+' . $feereminder_value->day . ' days'));
                        $fees_type_reminder = $this->feegrouptype_model->getFeeTypeDueDateReminder($date);


                        if (!empty($fees_type_reminder)) {

                            foreach ($fees_type_reminder as $reminder_key => $reminder_value) {

                                $students = $this->feegrouptype_model->getFeeTypeStudents($reminder_value->fee_session_group_id, $reminder_value->id);

                                foreach ($students as $student_key => $student_value) {
                                    $students[$student_key]->{'fee_category'}       = "fees";       
                                    $students[$student_key]->{'fee_group_name'}       = $reminder_value->fee_group_name;
                                    $students[$student_key]->{'due_date'}       = $date;
                                    $students[$student_key]->{'fee_type'}       = $reminder_value->type;
                                    $students[$student_key]->{'fee_code'}       = $reminder_value->code;
                                    $students[$student_key]->{'fee_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'due_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($student_value->amount_detail);
                                    if (json_last_error() == JSON_ERROR_NONE) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {
                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $students[$student_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $students[$student_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };
                                    $students[$student_key]->{'student_name'} = $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $studentList[]                            = $student_value;
                                }
                            }
                        }
                        $dt="2022-09-09";
                       $transport_fees= $this->studentfeemaster_model->getTransportFeesByDueDate($dt, $dt);
                     


                           if (!empty($transport_fees)) {

                            foreach ($transport_fees as $reminder_key => $reminder_value) {

                                    $transport_fees[$reminder_key]->{'fee_category'}       ="transport";
                                    $transport_fees[$reminder_key]->{'fee_group_name'}   = "Transport";
                                    $transport_fees[$reminder_key]->{'due_date'}       = $date;
                                    $transport_fees[$reminder_key]->{'fee_type'}       = $reminder_value->month;
                                    $transport_fees[$reminder_key]->{'fee_code'}       = "-";
                                    $transport_fees[$reminder_key]->{'fee_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'due_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($reminder_value->amount_detail);
                                    if (isJSON($reminder_value->amount_detail)) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {
                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $transport_fees[$reminder_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };
                                    $transport_fees[$reminder_key]->{'student_name'} = $this->customlib->getFullName($reminder_value->firstname, $reminder_value->middlename, $reminder_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $studentList[]                            = $reminder_value;
                               
                            }
                        }



                    } else if ($feereminder_value->reminder_type == "after") {

                        $date               = date('Y-m-d', strtotime('-' . $feereminder_value->day . ' days'));
                        $fees_type_reminder = $this->feegrouptype_model->getFeeTypeDueDateReminder($date);

                        if (!empty($fees_type_reminder)) {
                            foreach ($fees_type_reminder as $reminder_key => $reminder_value) {

                                $students = $this->feegrouptype_model->getFeeTypeStudents($reminder_value->fee_session_group_id, $reminder_value->id);

                                foreach ($students as $student_key => $student_value) {
                                    $students[$student_key]->{'fee_category'}       = "fees";
                                    $students[$student_key]->{'due_date'}       = $date;
                                    $students[$student_key]->{'fee_group_name'}       = $reminder_value->fee_group_name;
                                    $students[$student_key]->{'fee_type'}       = $reminder_value->type;
                                    $students[$student_key]->{'fee_code'}       = $reminder_value->code;
                                    $students[$student_key]->{'fee_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'due_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($student_value->amount_detail);
                                    if (json_last_error() == JSON_ERROR_NONE) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {

                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $students[$student_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $students[$student_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };

                                    $students[$student_key]->{'student_name'} = $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $students[$student_key]->{'school_name'}  = $this->customlib->getSchoolName();
                                    $studentList[]                            = $student_value;
                                }
                            }
                        }



                                   $dt="2022-09-09";
                       $transport_fees= $this->studentfeemaster_model->getTransportFeesByDueDate($dt, $dt);
                     


                           if (!empty($transport_fees)) {

                            foreach ($transport_fees as $reminder_key => $reminder_value) {

                                    $transport_fees[$reminder_key]->{'fee_category'}       ="transport";
                                    $transport_fees[$reminder_key]->{'fee_group_name'}   = "Transport";
                                    $transport_fees[$reminder_key]->{'due_date'}       = $date;
                                    $transport_fees[$reminder_key]->{'fee_type'}       = $reminder_value->month;
                                    $transport_fees[$reminder_key]->{'fee_code'}       = "-";
                                    $transport_fees[$reminder_key]->{'fee_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'due_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($reminder_value->amount_detail);
                                    if (isJSON($reminder_value->amount_detail)) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {
                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $transport_fees[$reminder_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };
                                    $transport_fees[$reminder_key]->{'student_name'} = $this->customlib->getFullName($reminder_value->firstname, $reminder_value->middlename, $reminder_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $studentList[]                            = $reminder_value;
                               
                            }
                        }


                    }
                }

                if (!empty($studentList)) {
                    foreach ($studentList as $eachStudent_key => $eachStudent_value) {
                        if ($eachStudent_value->due_amount <= 0) {
                            unset($studentList[$eachStudent_key]);
                        }
                    }
                }

                if (!empty($studentList)) {
                    foreach ($studentList as $eachStudent_key => $eachStudent_value) {

                        $this->mailsmsconf->mailsms('fees_reminder', $eachStudent_value);
                    
                    }
                }
            }
        }
    }

    public function eventreminder($key = "")
    {
        $setting_result = $this->setting_model->getSetting();

        if ($key != "") {
            if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }
            $this->load->library('mailsmsconf');

            $event_reminder = array();

            if ($setting_result->event_reminder == "enabled") {

                $date = date('Y-m-d', strtotime('+' . $setting_result->calendar_event_reminder . ' days'));

                $event_reminder = $this->calendar_model->geteventreminder($date);

                if (!empty($event_reminder)) {
                    foreach ($event_reminder as $event_reminder_key => $event_reminder_value) {

                        if ($event_reminder_value['event_type'] == 'private') {
                            $event_email = $this->staff_model->getstaffemail($event_reminder_value['event_for']);
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        } else if ($event_reminder_value['event_type'] == 'sameforall') {
                            $event_email = $this->staff_model->getEmployee($event_reminder_value['event_for'], 1);
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        } else if ($event_reminder_value['event_type'] == 'public') {
                            $event_email = $this->calendar_model->getstaffandstudentemail();
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        } else if ($event_reminder_value['event_type'] == 'protected') {
                            $event_email = $this->staff_model->searchFullText("", 1);
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        }

                        if (!empty($event_reminder)) {
                            foreach ($event_reminder as $event_reminder_value) {
                                $this->mailsmsconf->sendEailEventReminder($event_reminder_value);
                            }
                        }
                    }
                }
            }

        }
    }

    public function schedulesmsemails($key = "")
    {
        $this->load->library('mailer');
        $this->load->model('messages_model');
        $this->load->library('smsgateway');
        $userdata     = $this->messages_model->get_scheduledata(date('Y-m-d H:i'));
        
        $current_date = date('Y-m-d H:i:s');
        foreach ($userdata as $key => $value) {
               
            $user_list = json_decode($value['user_list'], true);

            if ($value['schedule_date_time'] <= $current_date) {
                $attachments = $this->messages_model->get_message_attachment($value['id']);

                
 
                foreach ($user_list as $user_listkey => $user_listvalue) {
                    
                    if($user_listvalue['role']=='student'){
                        
                        $user_student   =   $this->student_model->getstudentdetailbyid($user_listvalue['user_id']);
                        $email  =   $user_student['email'];
                        $phone  =   $user_student['mobileno'];
                        
                    }elseif($user_listvalue['role']=='parent'){
                        
                        $user_parent   =   $this->student_model->getstudentdetailbyid($user_listvalue['user_id']);
                        $email  =   $user_parent['guardian_email'];
                        $phone  =   $user_parent['guardian_phone'];
                        
                    }elseif($user_listvalue['role']=='staff'){
                        
                        $user_staff   =   $this->staff_model->getProfile($user_listvalue['user_id']);
                        $email  =   $user_staff['email'];
                        $phone  =   $user_staff['contact_no'];
                        
                    }                 
                    
                    if (!empty($email) && $value['send_mail'] == 1) {
                        $this->mailer->compose_mail($email, $value['title'], $value['message'], $attachments);
                    }

                    if (!empty($phone) && $value['send_sms'] == 1) {
                        $this->smsgateway->sendSMS($phone, $value['message'], $value['title']);
                    }

                }
                
                $insert['id']   = $value['id'];
                $insert['sent'] = 1;
                $this->messages_model->add($insert);

            }
        }
    }

    /**
     * Retry HEMIS outbound sync for students with a stored hemis_sync_error (uses cron_secret_key).
     * Call: {base_url}cron/hemis_students/{cron_secret_key}
     */
    public function hemis_students($key = '')
    {
        if ($key === '' || $this->cron_key !== $key) {
            echo 'Invalid Key or Direct access is not allowed';
            return;
        }
        if (!$this->db->table_exists('students') || !$this->db->field_exists('hemis_sync_error', 'students')) {
            echo 'HEMIS columns missing on students table';
            return;
        }
        $this->load->library('hemis_client');
        $this->db->where('hemis_sync_error IS NOT NULL', null, false);
        $this->db->where('hemis_sync_error !=', '');
        $this->db->limit(40);
        $rows = $this->db->get('students')->result_array();
        $n = 0;
        foreach ($rows as $r) {
            if (empty($r['id'])) {
                continue;
            }
            $sid = (int) $r['id'];
            if (!empty($r['hemis_student_id'])) {
                $this->hemis_client->sync_student_update_from_database_snapshot($sid);
            } else {
                $this->hemis_client->sync_student_after_database_snapshot($sid);
            }
            $n++;
        }
        echo 'HEMIS student sync attempts: ' . $n;
    }

    /**
     * Retry HEMIS outbound sync for one student (uses cron_secret_key).
     * Call: {base_url}cron/hemis_student_one/{cron_secret_key}/{students.id}
     */
    public function hemis_student_one($key = '', $id = 0)
    {
        if ($key === '' || $this->cron_key !== $key) {
            echo 'Invalid Key or Direct access is not allowed';
            return;
        }
        $id = (int) $id;
        if ($id < 1) {
            echo 'Missing student id';
            return;
        }
        $this->load->library('hemis_client');
        $row = $this->student_model->get_student_by_id($id);
        if (!$row) {
            echo 'Student not found';
            return;
        }
        if (!empty($row['hemis_student_id'])) {
            $out = $this->hemis_client->sync_student_update_from_database_snapshot($id);
        } else {
            $out = $this->hemis_client->sync_student_after_database_snapshot($id);
        }
        echo json_encode($out);
    }

}
