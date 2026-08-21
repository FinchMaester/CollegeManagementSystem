<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staffattendance extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
        $this->config->load("mailsms");
        $this->config->load("payroll");
        $this->load->library('mailsmsconf');
        $this->config_attendance = $this->config->item('attendence');
        $this->staff_attendance  = $this->config->item('staffattendance');
        $this->load->model("staffattendancemodel");
        $this->load->model("staff_model");
        $this->load->model("payroll_model"); 
    }

    public function index()
    {
        if (!($this->rbac->hasPrivilege('staff_attendance', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staffattendance');
        $data['title']        = 'Staff Attendance List';
        $data['title_list']   = 'Staff Attendance List';
        $user_type            = $this->staff_model->getStaffRole();
        $data['sch_setting'] = $this->setting_model->getSetting();
        $data['classlist']    = $user_type;
        $data['class_id']     = "";
        $data['section_id']   = "";
        $data['date']         = "";
        $user_type_id         = $this->input->post('user_id');
        $data["user_type_id"] = $user_type_id;
        if (!(isset($user_type_id))) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/staffattendance/staffattendancelist', $data);
            $this->load->view('layout/footer', $data);
        } else {

            $user_type            = $this->input->post('user_id');
            $date                 = $this->input->post('date');
            $user_list            = $this->staffattendancemodel->get();
            $data['userlist']     = $user_list;
            $data['class_id']     = $user_list;
            $data['user_type_id'] = $user_type_id;
            $data['section_id']   = "";
            $data['date']         = $date;
            $search               = $this->input->post('search');
            $holiday              = $this->input->post('holiday');
            $this->session->set_flashdata('msg', '');
            if ($search == "saveattendence") {
                $user_type_ary = $this->input->post('student_session');
                $absent_student_list = array();
                
                // Get the Nepali date from input (already in YYYY-MM-DD format)
                $nepali_date = $this->input->post('date');
                
                foreach ($user_type_ary as $key => $value) {
                    $checkForUpdate = $this->input->post('attendendence_id' . $value);                    
                    if ($checkForUpdate != 0 && !empty($checkForUpdate)) {
                        if (isset($holiday)) {
                            $arr = array(
                                'id' => $checkForUpdate,
                                'staff_id' => $value,
                                'staff_attendance_type_id' => 5,
                                'remark' => $this->input->post("remark" . $value),
                                'date' => $nepali_date, // Store Nepali date directly
                            );
                        } else {
                            $arr = array(
                                'id' => $checkForUpdate,
                                'staff_id' => $value,
                                'staff_attendance_type_id' => $this->input->post('attendencetype' . $value),
                                'remark' => $this->input->post("remark" . $value),
                                'date' => $nepali_date, // Store Nepali date directly
                            );
                        }
            
                        $insert_id = $this->staffattendancemodel->add($arr);
                    } else {
                        if (isset($holiday)) {
                            $arr = array(
                                'staff_id' => $value,
                                'staff_attendance_type_id' => 5,
                                'date' => $nepali_date, // Store Nepali date directly
                                'remark' => '',
                            );
                        } else {
                            $arr = array(
                                'staff_id' => $value,
                                'staff_attendance_type_id' => $this->input->post('attendencetype' . $value),
                                'date' => $nepali_date, // Store Nepali date directly
                                'remark' => $this->input->post("remark" . $value),
                            );
                        }
                        $insert_id = $this->staffattendancemodel->add($arr);
                        
                        $absent_config = $this->config_attendance['absent'];
                        if ($arr['staff_attendance_type_id'] == $absent_config) {
                            $absent_student_list[] = $value;
                        }
                    }
                }

                $absent_config = $this->config_attendance['absent'];
                if (!empty($absent_student_list)) {

                    $this->mailsmsconf->mailsms('absent_attendence', $absent_student_list, $date);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                redirect('admin/staffattendance/index');
            }

            $attendencetypes             = $this->attendencetype_model->getStaffAttendanceType();
            $data['attendencetypeslist'] = $attendencetypes;        
            
            $resultlist = $this->staffattendancemodel->searchAttendenceUserType($user_type, $this->input->post('date'));
            $data['resultlist']          = $resultlist;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/staffattendance/staffattendancelist', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    

    public function monthAttendance($st_month, $no_of_months, $emp)
    {
        $this->load->model("payroll_model");
        $record = array();
        $r     = array();
        $month = date('m', strtotime($st_month));
        $year  = date('Y', strtotime($st_month));
        foreach ($this->staff_attendance as $att_key => $att_value) {
            $s = $this->payroll_model->count_attendance_obj($month, $year, $emp, $att_value);
            $r[$att_key] = $s;
        }

        $record[$emp] = $r;
        return $record;
    }

    public function profileattendance()
    {
        $data["monthlist"]     = $this->customlib->getNepaliMonthDropdown();
        $data['yearlist']      = $this->customlib->getNepaliYearDropdown();
        $staffRole             = $this->staff_model->getStaffRole();
        $data["role"]          = $staffRole;
        $data["role_selected"] = "";
        $j                     = 0;
        for ($i = 1; $i <= 31; $i++) {

            $att_date = sprintf("%02d", $i);
            $attendence_array[] = $att_date;
            foreach ($monthlist as $key => $value) {
                $datemonth       = date("m", strtotime($value));
                $att_dates       = date("Y") . "-" . $datemonth . "-" . sprintf("%02d", $i);
                $date_array[]    = $att_dates;
                $res[$att_dates] = $this->staffattendancemodel->searchStaffattendance($att_dates, $staff_id = 8);
            }

            $j++;
        }

        $data["resultlist"]       = $res;
        $data["attendence_array"] = $attendence_array;
        $data["date_array"]       = $date_array;
        $this->load->view("layout/header");
        $this->load->view("admin/staff/staffattendance", $data);
        $this->load->view("layout/footer");
    }
 
    public function markHoliday()
    {
        if (!$this->input->is_ajax_request()) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }
    
        // Always return JSON header
        $this->output->set_content_type('application/json');
    
        $role = $this->input->post('role');
        $date = $this->input->post('date');
    
        if (empty($role) || $role == 'select' || empty($date)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please select role and date',
                'debug' => [
                    'role' => $role,
                    'date' => $date
                ]
            ]);
            return;
        }
    
        try {
            // Fetch staff list by role and date
            $resultlist = $this->staffattendancemodel->searchAttendenceUserType($role, $date);
    
            if (empty($resultlist)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No staff found for this role',
                    'debug' => [
                        'role' => $role,
                        'date' => $date,
                        'query' => $this->db->last_query()
                    ]
                ]);
                return;
            }
    
            $success_count = 0;
            $error_count = 0;
            $errors = [];
            $holiday_type_id = 5; // holiday attendance type
    
            foreach ($resultlist as $staff) {
                try {
                    $staff_id = $staff['staff_id'];
    
                    // Check if record exists
                    $existing = $this->staffattendancemodel->checkAttendanceExists($staff_id, $date);
    
                    $data = [
                        'staff_id' => $staff_id,
                        'date' => $date,
                        'staff_attendance_type_id' => $holiday_type_id,
                        'remark' => 'Marked as Holiday',
                        'biometric_attendence' => 0,
                        'is_active' => 0
                    ];
    
                    // If record exists, add the ID for update
                    if (!empty($existing->id)) {
                        $data['id'] = $existing->id;
                    }
    
                    // Use your existing add method (handles both insert and update)
                    $result = $this->staffattendancemodel->add($data);
    
                    // ✅ SUCCESS: if result is not FALSE, consider it success
                    // (handles: insert_id, true, 1, 0 rows affected but executed, etc.)
                    if ($result !== false) {
                        $success_count++;
                    } else {
                        $error_count++;
                        $errors[] = [
                            'staff_id' => $staff_id,
                            'staff_name' => $staff['name'] . ' ' . $staff['surname'],
                            'error' => 'Failed to save attendance record',
                            'data' => $data
                        ];
                    }
    
                } catch (Exception $e) {
                    $error_count++;
                    $errors[] = [
                        'staff_id' => isset($staff_id) ? $staff_id : null,
                        'staff_name' => isset($staff['name']) ? $staff['name'] . ' ' . $staff['surname'] : 'Unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ];
                }
            }
    
            // ✅ RESPONSE LOGIC
            $total_staff = count($resultlist);
    
            if ($success_count > 0 && $error_count === 0) {
                $status = 'success';
                $message = "Holiday marked successfully for all {$total_staff} staff members.";
            } elseif ($success_count > 0 && $error_count > 0) {
                $status = 'partial';
                $message = "Holiday marked for {$success_count} staff, but failed for {$error_count} staff.";
            } else {
                $status = 'error';
                $message = "Failed to mark holiday for all staff members.";
            }
    
            echo json_encode([
                'status' => $status,
                'message' => $message,
                'success_count' => $success_count,
                'error_count' => $error_count,
                'total_staff' => $total_staff,
                'errors' => $errors,
                'debug' => [
                    'role' => $role,
                    'date' => $date
                ]
            ]);
    
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Exception occurred: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'debug' => [
                    'role' => $role,
                    'date' => $date
                ]
            ]);
        }
    }
    
}
