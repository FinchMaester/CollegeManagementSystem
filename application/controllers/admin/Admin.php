<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');  
}

class Admin extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model("classteacher_model");
        $this->load->model("Staff_model");
        $this->load->library('Enc_lib');
        $this->load->model('program_model');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        
    }

    public function unauthorized()
    {
        $data = array();
        
        // Debug information (only show if in development or if explicitly requested)
        $data['debug_info'] = array();
        if (ENVIRONMENT === 'development' || $this->input->get('debug') === '1') {
            $admin = $this->session->userdata('admin');
            $roles = $this->customlib->getStaffRole();
            $logged_user_role = json_decode($roles);
            
            $data['debug_info'] = array(
                'role_name' => isset($logged_user_role->name) ? $logged_user_role->name : 'Unknown',
                'role_id' => isset($admin['roles']) ? current($admin['roles']) : 'Unknown',
                'admin_session' => !empty($admin) ? 'Present' : 'Missing',
                'current_url' => $this->uri->uri_string(),
                'controller' => $this->router->fetch_class(),
                'method' => $this->router->fetch_method()
            );
        }
        
        $this->load->view('layout/header', $data);
        $this->load->view('unauthorized', $data);
        $this->load->view('layout/footer', $data);
        
    }

    public function getDebugInfo()
    {
        $debug_data = array(
            'status' => 'success',
            'role_name' => 'Unknown',
            'role_id' => 'Unknown',
            'is_super_admin' => false,
            'admin_session' => 'Missing',
            'controller' => 'Unknown',
            'method' => 'Unknown',
            'permission_category' => 'Unknown',
            'permission_required' => 'Unknown',
            'role_permissions' => null,
            'permission_check_result' => null,
            'debug_messages' => array()
        );

        try {
            // Get role information
            $roles = $this->customlib->getStaffRole();
            if ($roles) {
                $logged_user_role = json_decode($roles);
                $debug_data['role_name'] = isset($logged_user_role->name) ? $logged_user_role->name : 'Unknown';
                $debug_data['is_super_admin'] = (isset($logged_user_role->name) && $logged_user_role->name == 'Super Admin');
            }

            // Get admin session
            $admin = $this->session->userdata('admin');
            if (!empty($admin)) {
                $debug_data['admin_session'] = 'Present';
                if (isset($admin['roles'])) {
                    $roles_array = $admin['roles'];
                    $role_key = key($roles_array);
                    $debug_data['role_id'] = $roles_array[$role_key];
                }
            } else {
                $debug_data['debug_messages'][] = 'WARNING: Admin session data is missing!';
            }

            // Get current controller and method from referrer or session
            $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $debug_data['referrer'] = $referrer;
            
            // Try to extract controller/method from referrer
            if ($referrer) {
                $parsed_url = parse_url($referrer);
                if (isset($parsed_url['path'])) {
                    $path_parts = explode('/', trim($parsed_url['path'], '/'));
                    if (count($path_parts) >= 2) {
                        $debug_data['controller'] = $path_parts[0];
                        $debug_data['method'] = $path_parts[1];
                    }
                }
            }

            // Check what permission might be needed based on common patterns
            $common_permissions = array(
                'exam_schedule' => 'can_view',
                'exam_group' => 'can_view',
                'exam_result' => 'can_view',
                'collect_fees' => 'can_view',
                'fees_discount' => 'can_view'
            );

            // Try to determine permission category from controller
            if ($debug_data['controller'] !== 'Unknown') {
                $controller_to_category = array(
                    'exam_schedule' => 'exam_schedule',
                    'examgroup' => 'exam_group',
                    'examresult' => 'exam_result',
                    'studentfee' => 'collect_fees',
                    'feediscount' => 'fees_discount'
                ); 
                
                foreach ($controller_to_category as $ctrl => $cat) {
                    if (stripos($debug_data['controller'], $ctrl) !== false) {
                        $debug_data['permission_category'] = $cat;
                        $debug_data['permission_required'] = isset($common_permissions[$cat]) ? $common_permissions[$cat] : 'can_view';
                        break;
                    }
                }
            }

            // Get RBAC debug from session first (most recent check)
            $rbac_debug = $this->session->userdata('rbac_debug');
            if ($rbac_debug && isset($rbac_debug['category'])) {
                $debug_data['permission_category'] = $rbac_debug['category'];
                $debug_data['permission_required'] = $rbac_debug['permission'];
            }
            
            // Also check for any stored permission category from session
            if ($debug_data['permission_category'] === 'Unknown' && isset($rbac_debug['category'])) {
                $debug_data['permission_category'] = $rbac_debug['category'];
            }
            
            // Check ALL recent RBAC checks from session (not just the last one)
            // Sometimes multiple checks happen and we need to see the one that failed
            $all_rbac_checks = $this->session->userdata('rbac_debug_history');
            if (!$all_rbac_checks) {
                $all_rbac_checks = array();
            }
            if ($rbac_debug) {
                $all_rbac_checks[] = $rbac_debug;
                // Keep only last 10 checks
                if (count($all_rbac_checks) > 10) {
                    $all_rbac_checks = array_slice($all_rbac_checks, -10);
                }
                $this->session->set_userdata('rbac_debug_history', $all_rbac_checks);
            }
            $debug_data['all_recent_rbac_checks'] = $all_rbac_checks;
            
            // ALWAYS check for "superadmin" specifically since we know it's failing
            // Check if superadmin was in any recent check
            $superadmin_found_in_history = false;
            foreach ($all_rbac_checks as $check) {
                if (isset($check['category']) && strtolower($check['category']) === 'superadmin') {
                    $superadmin_found_in_history = true;
                    $debug_data['superadmin_check_found'] = true;
                    $debug_data['superadmin_check_details'] = $check;
                    // Override permission category to check superadmin
                    $debug_data['permission_category'] = 'superadmin';
                    $debug_data['permission_required'] = isset($check['permission']) ? $check['permission'] : 'can_view';
                    break;
                }
            }
            
            // Even if not in history, check superadmin anyway if we're debugging
            if (!$superadmin_found_in_history) {
                $debug_data['superadmin_manual_check'] = true;
                // Check superadmin permission category exists
                $this->db->select('id, name, short_code');
                $this->db->from('permission_category');
                $this->db->where('short_code', 'superadmin');
                $superadmin_cat_query = $this->db->get();
                $debug_data['superadmin_category_exists'] = $superadmin_cat_query->num_rows() > 0;
                if ($superadmin_cat_query->num_rows() > 0) {
                    $debug_data['superadmin_category_data'] = $superadmin_cat_query->row_array();
                }
                
                // Check if role has superadmin permission
                if ($debug_data['role_id'] !== 'Unknown') {
                    $this->db->select('roles_permissions.*');
                    $this->db->from('roles_permissions');
                    $this->db->join('permission_category', 'permission_category.id=roles_permissions.perm_cat_id');
                    $this->db->where('roles_permissions.role_id', $debug_data['role_id']);
                    $this->db->where('permission_category.short_code', 'superadmin');
                    $superadmin_perm_query = $this->db->get();
                    $debug_data['superadmin_permission_exists'] = $superadmin_perm_query->num_rows() > 0;
                    if ($superadmin_perm_query->num_rows() > 0) {
                        $debug_data['superadmin_permission_data'] = $superadmin_perm_query->row_array();
                    }
                }
            }

            // Always check for "superadmin" specifically if it was in any recent check
            $superadmin_check_needed = false;
            foreach ($all_rbac_checks as $check) {
                if (isset($check['category']) && strtolower($check['category']) === 'superadmin') {
                    $superadmin_check_needed = true;
                    if ($debug_data['permission_category'] === 'Unknown') {
                        $debug_data['permission_category'] = 'superadmin';
                        $debug_data['permission_required'] = isset($check['permission']) ? $check['permission'] : 'can_view';
                    }
                    break;
                }
            }
            
            // If we have role_id and permission_category, check the permission
            if ($debug_data['role_id'] !== 'Unknown' && $debug_data['permission_category'] !== 'Unknown') {
                // First, check if permission_category exists in database (exact match)
                $this->db->select('id, name, short_code');
                $this->db->from('permission_category');
                $this->db->where('short_code', trim($debug_data['permission_category']));
                $category_query = $this->db->get();
                
                $debug_data['database_checks'] = array();
                $debug_data['database_checks']['permission_category_exists'] = $category_query->num_rows() > 0;
                $debug_data['database_checks']['searched_category'] = trim($debug_data['permission_category']);
                
                if ($category_query->num_rows() > 0) {
                    $debug_data['database_checks']['permission_category_data'] = $category_query->row_array();
                } else {
                    // Try case-insensitive search
                    $this->db->select('id, name, short_code');
                    $this->db->from('permission_category');
                    $this->db->where('LOWER(short_code)', strtolower(trim($debug_data['permission_category'])));
                    $case_insensitive_query = $this->db->get();
                    
                    if ($case_insensitive_query->num_rows() > 0) {
                        $debug_data['database_checks']['case_insensitive_match'] = true;
                        $debug_data['database_checks']['permission_category_data'] = $case_insensitive_query->row_array();
                        $debug_data['debug_messages'][] = '⚠️ CASE SENSITIVITY ISSUE: Found category with different case: "' . $case_insensitive_query->row()->short_code . '"';
                    } else {
                        // Check all permission categories to see what's available
                        $all_categories = $this->db->select('id, name, short_code')->from('permission_category')->order_by('short_code')->get()->result_array();
                        $debug_data['database_checks']['all_permission_categories'] = $all_categories;
                        $debug_data['debug_messages'][] = 'Permission category "' . $debug_data['permission_category'] . '" does not exist in permission_category table!';
                        
                        // Check for similar categories (fuzzy match)
                        $similar = array();
                        foreach ($all_categories as $cat) {
                            similar_text(strtolower($cat['short_code']), strtolower(trim($debug_data['permission_category'])), $percent);
                            if ($percent > 70) {
                                $similar[] = array('short_code' => $cat['short_code'], 'name' => $cat['name'], 'similarity' => round($percent, 2));
                            }
                        }
                        if (!empty($similar)) {
                            $debug_data['database_checks']['similar_categories'] = $similar;
                            $debug_data['debug_messages'][] = 'Found similar permission categories (might be a typo):';
                        }
                    }
                }
                
                // Check if role_permissions record exists
                $this->db->select('roles_permissions.*');
                $this->db->from('roles_permissions');
                $this->db->join('permission_category', 'permission_category.id=roles_permissions.perm_cat_id');
                $this->db->where('roles_permissions.role_id', $debug_data['role_id']);
                $this->db->where('permission_category.short_code', trim($debug_data['permission_category']));
                $role_perm_query = $this->db->get();
                
                $debug_data['database_checks']['role_permission_exists'] = $role_perm_query->num_rows() > 0;
                $debug_data['database_checks']['last_query'] = $this->db->last_query();
                
                if ($role_perm_query->num_rows() > 0) {
                    $role_perm = $role_perm_query->row_array();
                    $debug_data['permission_check_result'] = array(
                        'found' => true,
                        'value' => isset($role_perm[$debug_data['permission_required']]) ? $role_perm[$debug_data['permission_required']] : 'Not set',
                        'available_keys' => array_keys($role_perm)
                    );
                    $debug_data['role_permissions'] = $role_perm;
                } else {
                    // Check what permissions this role actually has
                    $this->db->select('roles_permissions.*, permission_category.short_code, permission_category.name');
                    $this->db->from('roles_permissions');
                    $this->db->join('permission_category', 'permission_category.id=roles_permissions.perm_cat_id');
                    $this->db->where('roles_permissions.role_id', $debug_data['role_id']);
                    $all_role_perms = $this->db->get()->result_array();
                    $debug_data['database_checks']['all_role_permissions'] = $all_role_perms;
                    
                    $debug_data['permission_check_result'] = array(
                        'found' => false,
                        'value' => null,
                        'available_keys' => array(),
                        'message' => 'Permission category "' . $debug_data['permission_category'] . '" not found for role ID ' . $debug_data['role_id']
                    );
                    $debug_data['debug_messages'][] = 'Permission category "' . $debug_data['permission_category'] . '" not found in roles_permissions table for this role.';
                }
            } else {
                if ($debug_data['role_id'] === 'Unknown') {
                    $debug_data['debug_messages'][] = 'Cannot check permissions: Role ID is unknown.';
                }
                if ($debug_data['permission_category'] === 'Unknown') {
                    $debug_data['debug_messages'][] = 'Cannot check permissions: Permission category could not be determined from URL.';
                }
            }

            // Get all permissions for this role for reference
            if ($debug_data['role_id'] !== 'Unknown') {
                $all_permissions = $this->rolepermission_model->getPermissionByRole($debug_data['role_id']);
                if ($all_permissions) {
                    $debug_data['all_role_permissions'] = array();
                    foreach ($all_permissions as $perm) {
                        $debug_data['all_role_permissions'][] = array(
                            'category' => isset($perm->permission_category_code) ? $perm->permission_category_code : 'Unknown',
                            'category_name' => isset($perm->permission_category_name) ? $perm->permission_category_name : 'Unknown'
                        );
                    }
                }
            }

            // Get RBAC debug info from session if available
            $rbac_debug = $this->session->userdata('rbac_debug');
            if ($rbac_debug) {
                $debug_data['rbac_debug'] = $rbac_debug;
                $debug_data['debug_messages'][] = 'RBAC debug info found in session from last permission check.';
            }

        } catch (Exception $e) {
            $debug_data['status'] = 'error';
            $debug_data['error'] = $e->getMessage();
            $debug_data['debug_messages'][] = 'Exception: ' . $e->getMessage();
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($debug_data));
    }

    public function dashboard()
    {
       
        $role            = $this->customlib->getStaffRole();
        $role_id         = json_decode($role)->id;
        $data['role_id'] = $role_id;

        $staffid       = $this->customlib->getStaffID();
        $notifications = $this->notification_model->getUnreadStaffNotification($staffid, $role_id);
        $data['total_faculty'] = $this->section_model->getFacultyCount();
        $data['total_programs'] = $this->program_model->getProgramCount();
        $data['total_graduates'] = $this->studentsession_model->getGraduatesCount();
        $data['total_dropouts'] = $this->studentsession_model->getDropoutsCount();
        $data['total_male'] = $this->student_model->getCountByGender('male');
        $data['total_female'] = $this->student_model->getCountByGender('female');
        $data['total_staff'] = $this->staff_model->getTotalStaff();
        $data['enrollment_status_data'] = $this->student_model->getEnrollmentStatusByYear(5);

        $ratio_data = $this->student_model->getStudentTeacherRatioData();
        $data['ratio_data'] = $ratio_data;

        $data['total_teachers'] = $ratio_data['total_teachers'];
        $data['student_teacher_ratio'] = $ratio_data['ratio'];

        $data['gender_program_data'] = $this->student_model->getStudentCountByGenderAndProgram();
        
        $data['notifications'] = $notifications;
        $input                 = $this->setting_model->getCurrentSessionName();

        // Fee/expense charts use Gregorian (AD) dates. Session labels may be BS (e.g. 2082-83).
        $session_parts = explode('-', (string) $input);
        $session_start = isset($session_parts[0]) ? (int) $session_parts[0] : (int) date('Y');
        $session_end_raw = isset($session_parts[1]) ? trim((string) $session_parts[1]) : '';
        if ($session_start >= 2070) {
            // Approx BS → AD for chart date windows (same +57 convention used elsewhere).
            $Current_year = $session_start - 57;
            if (strlen($session_end_raw) === 2) {
                $Next_year = $Current_year + 1;
            } elseif (ctype_digit($session_end_raw) && (int) $session_end_raw >= 2070) {
                $Next_year = (int) $session_end_raw - 57;
            } else {
                $Next_year = $Current_year + 1;
            }
        } else {
            $Current_year = $session_start > 0 ? $session_start : (int) date('Y');
            if (strlen($session_end_raw) == 2) {
                $Next_year = (int) (substr((string) $Current_year, 0, 2) . $session_end_raw);
            } elseif ($session_end_raw !== '') {
                $Next_year = (int) $session_end_raw;
            } else {
                $Next_year = $Current_year + 1;
            }
        }
        $data['mysqlVersion'] = $this->setting_model->getMysqlVersion();
        $data['sqlMode']      = $this->setting_model->getSqlMode();
        //========================== Current Attendence ==========================
        $current_date       = date('Y-m-d');
        $data['title']      = 'Dashboard';
        $Current_start_date = date('01');
        $Current_date       = date('d');
        $Current_month      = date('m');
        $month_collection   = 0;
        $month_expense      = 0;
        $total_students     = 0;
        $total_teachers     = 0;
        $ar                 = $this->startmonthandend();
        $year_str_month     = $Current_year . '-' . $ar[0] . '-01';
        $year_end_month     = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));
        $getDepositeAmount  = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
        //======================Current Month Collection ==============================
        $first_day_this_month     = date('Y-m-01');
        $current_month_collection = $this->studentfeemaster_model->getDepositAmountBetweenDate($first_day_this_month, $current_date);
        $month_collection         = $this->whatever($current_month_collection, $first_day_this_month, $current_date);
        // $expense                  = $this->expense_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
        // if (!empty($expense)) {
        // $month_expense = $month_expense + $expense->amount;
        // }
        // $data['month_expense']    = $month_expense;

        $data['month_collection'] = $month_collection;

        $tot_students = $this->studentsession_model->getTotalStudentBySession();
        if (!empty($tot_students)) {
            $total_students = $tot_students->total_student;
        }

        $data['total_students'] = $total_students;
        $tot_roles              = $this->role_model->get();
        foreach ($tot_roles as $key => $value) {

            $count_roles[$value["name"]] = $this->role_model->count_roles($value["id"]);

        }
        $data["roles"] = $count_roles;

        $data["designations"] = $this->getStaffDesignationCounts();

        //======================== get collection by month ==========================
        $start_month = strtotime($year_str_month);
        $start       = strtotime($year_str_month);
        $end         = strtotime($year_end_month);
        $coll_month  = array();
        $s           = array();
        $total_month = array();
        while ($start_month <= $end) {
            $total_month[] = $this->lang->line(strtolower(date('F', $start_month)));
            $month_start   = date('Y-m-d', $start_month);
            $month_end     = date("Y-m-t", $start_month);
            $return        = $this->whatever($getDepositeAmount, $month_start, $month_end);
            if ($return) {
                $s[] = convertBaseAmountCurrencyFormat($return);
            } else {
                $s[] = "0.00";
            }

            $start_month = strtotime("+1 month", $start_month);
        }
        //======================== getexpense by month ==============================
        $ex                  = array();
        $start_session_month = strtotime($year_str_month);
        while ($start_session_month <= $end) {

            $month_start = date('Y-m-d', $start_session_month);
            $month_end   = date("Y-m-t", $start_session_month);

            $expense_monthly = $this->expense_model->getTotalExpenseBwdate($month_start, $month_end);

            if (!empty($expense_monthly)) {
                $amt  = 0;
                $ex[] = $amt + convertBaseAmountCurrencyFormat($expense_monthly->amount);
            }

            $start_session_month = strtotime("+1 month", $start_session_month);
        }

        $data['yearly_collection'] = $s;
        $data['yearly_expense']    = $ex;
        $data['total_month']       = $total_month;

        //======================= current month collection /expense ===================
        // hardcoded '01' for first day
        $startdate       = date('m/01/Y');
        $enddate         = date('m/t/Y');
        $start           = strtotime($startdate);
        $end             = strtotime($enddate);
        $currentdate     = $start;
        $month_days      = array();
        $days_collection = array();
        while ($currentdate <= $end) {
            $cur_date          = date('Y-m-d', $currentdate);
            $month_days[]      = date('d', $currentdate);
            $coll_amt          = $this->whatever($getDepositeAmount, $cur_date, $cur_date);
            $days_collection[] = convertBaseAmountCurrencyFormat($coll_amt);
            $currentdate       = strtotime('+1 day', $currentdate);
        }
        $data['current_month_days'] = $month_days;
        $data['days_collection']    = $days_collection;

        //======================= current month /expense ==============================
        // hardcoded '01' for first day

        $startdate    = date('m/01/Y');
        $enddate      = date('m/t/Y');
        $start        = strtotime($startdate);
        $end          = strtotime($enddate);
        $currentdate  = $start;
        $days_expense = array();
        while ($currentdate <= $end) {
            $cur_date       = date('Y-m-d', $currentdate);
            $month_days[]   = date('d', $currentdate);
            $currentdate    = strtotime('+1 day', $currentdate);
            $ct             = $this->getExpensebyday($cur_date);
            $days_expense[] = convertBaseAmountCurrencyFormat($ct);
        }

        $data['days_expense']        = $days_expense;
        $student_fee_history         = $this->studentfee_model->getTodayStudentFees();
        $data['student_fee_history'] = $student_fee_history;

        $event_colors         = array("#03a9f4", "#c53da9", "#757575", "#8e24aa", "#d81b60", "#7cb342", "#fb8c00", "#fb3b3b");
        $data["event_colors"] = $event_colors;
        $userdata             = $this->customlib->getUserData();
        $data["role"]         = $userdata["user_type"];
        $start_date           = date('Y-m-01');
        $end_date             = date('Y-m-t');
        $current_month        = date('F');

        $student_due_fee       = $this->studentfeemaster_model->getFeesAwaiting($start_date, $end_date);
        $student_transport_fee = $this->studentfeemaster_model->getTransportFeesByDueDate($start_date, $end_date);

        $data['fees_awaiting'] = $student_due_fee;

        $total_fess    = 0;
        $total_paid    = 0;
        $total_unpaid  = 0;
        $total_partial = 0;

        if (!empty($student_transport_fee)) {

            foreach ($student_transport_fee as $transport_fees_key => $transport_fees_value) {

                $amount_to_be_taken = 0;
                if ($transport_fees_value->fees > 0) {
                    $amount_to_be_taken = $transport_fees_value->fees;
                }

                if ($amount_to_be_taken > 0) {
                    $total_fess++;

                    if (is_string($transport_fees_value->amount_detail) && is_array(json_decode($transport_fees_value->amount_detail, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                        $amount_paid_details = (json_decode($transport_fees_value->amount_detail));
                        $amt_                = 0;
                        foreach ($amount_paid_details as $amount_paid_detail_key => $amount_paid_detail_value) {
                            $amt_ = $amt_ + $amount_paid_detail_value->amount;
                        }

                        if (($amt_ + $amount_paid_detail_value->amount_discount) >= $amount_to_be_taken) {
                            $total_paid++;
                        } elseif (($amt_ + $amount_paid_detail_value->amount_discount) < $amount_to_be_taken) {
                            $total_partial++;
                        }
                    } else {
                        $total_unpaid++;
                    }

                }
            }
        }

        if (!empty($data['fees_awaiting'])) {

            foreach ($data['fees_awaiting'] as $awaiting_key => $awaiting_value) {

                $amount_to_be_taken = 0;
                if ($awaiting_value->is_system) {
                    if ($awaiting_value->amount > 0) {
                        $amount_to_be_taken = $awaiting_value->amount;
                    }
                } elseif ($awaiting_value->is_system == 0) {
                    if ($awaiting_value->fee_amount > 0) {
                        $amount_to_be_taken = $awaiting_value->fee_amount;
                    }
                }

                if ($amount_to_be_taken > 0) {
                    $total_fess++;

                    if (is_string($awaiting_value->amount_detail) && is_array(json_decode($awaiting_value->amount_detail, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                        $amount_paid_details = (json_decode($awaiting_value->amount_detail));
                        $amt_                = 0;
                        foreach ($amount_paid_details as $amount_paid_detail_key => $amount_paid_detail_value) {
                            $amt_ = $amt_ + $amount_paid_detail_value->amount;
                        }

                        if (($amt_ + $amount_paid_detail_value->amount_discount) >= $amount_to_be_taken) {
                            $total_paid++;
                        } elseif (($amt_ + $amount_paid_detail_value->amount_discount) < $amount_to_be_taken) {
                            $total_partial++;
                        }
                    } else {
                        $total_unpaid++;
                    }

                }
            }
        }

        $incomegraph = $this->income_model->getIncomeHeadsData($start_date, $end_date);
        foreach ($incomegraph as $key => $value) {
            $incomegraph[$key]['total'] = convertBaseAmountCurrencyFormat($value['total']);
        }
        $data['incomegraph'] = $incomegraph;

        $expensegraph = $this->expense_model->getExpenseHeadData($start_date, $end_date);
        foreach ($expensegraph as $key => $value) {
            $expensegraph[$key]['total'] = convertBaseAmountCurrencyFormat($value['total']);
            if (!empty($value['total'])) {
                $month_expense = $month_expense + convertBaseAmountCurrencyFormat($value['total']);
            }
        }
        $data['expensegraph']  = $expensegraph;
        $data['month_expense'] = $month_expense;

        $enquiry       = $this->admin_model->getAllEnquiryCount($start_date, $end_date);
        $total_counter = $total_paid + $total_unpaid + $total_partial;

        $data['fees_overview'] = array(
            'total_unpaid'     => $total_unpaid,
            'unpaid_progress'  => ($total_counter > 0) ? (($total_unpaid * 100) / $total_counter) : 0,
            'total_paid'       => $total_paid,
            'paid_progress'    => ($total_counter > 0) ? (($total_paid * 100) / $total_counter) : 0,
            'total_partial'    => $total_partial,
            'partial_progress' => ($total_counter > 0) ? (($total_partial * 100) / $total_counter) : 0,
        );

        $total_enquiry = $enquiry['total'];

        if ($total_enquiry > 0) {

            $data['enquiry_overview'] = array(
                'won'              => $enquiry['complete'],
                'won_progress'     => ($enquiry['complete'] * 100) / $total_enquiry,
                'active'           => $enquiry['active'],
                'active_progress'  => ($enquiry['active'] * 100) / $total_enquiry,
                'passive'          => $enquiry['passive'],
                'passive_progress' => ($enquiry['passive'] * 100) / $total_enquiry,
                'dead'             => $enquiry['dead'],
                'dead_progress'    => ($enquiry['dead'] * 100) / $total_enquiry,
                'lost'             => $enquiry['lost'],
                'lost_progress'    => ($enquiry['lost'] * 100) / $total_enquiry,
            );

        } else {

            $data['enquiry_overview'] = array(
                'won'              => 0,
                'won_progress'     => 0,
                'active'           => 0,
                'active_progress'  => 0,
                'passive'          => 0,
                'passive_progress' => 0,
                'dead'             => 0,
                'dead_progress'    => 0,
                'lost'             => 0,
                'lost_progress'    => 0,
            );

        }

        $data['total_paid'] = $total_paid;
        $data['total_fees'] = $total_fess;
        if ($total_fess > 0) {
            $data['fessprogressbar'] = ($total_paid * 100) / $total_fess;
        } else {
            $data['fessprogressbar'] = 0;
        }

        $data['total_enquiry']  = $total_enquiry  = $enquiry['total'];
        $data['total_complete'] = $complete_enquiry = $enquiry['complete'];
        if ($total_enquiry > 0) {
            $data['fenquiryprogressbar'] = ($complete_enquiry * 100) / $total_enquiry;
        } else {
            $data['fenquiryprogressbar'] = 0;
        }

        $bookoverview      = $this->book_model->bookoverview($start_date, $end_date);
        $bookduereport     = $this->bookissue_model->dueforreturn($start_date, $end_date);
        $forreturndata     = $this->bookissue_model->forreturn($start_date, $end_date);
        $dueforreturn      = $bookduereport[0]['total'];
        $forreturn         = $forreturndata[0]['total'];
        $total_qty         = $bookoverview[0]['qty'];
        $total_issued      = $bookoverview[0]['total_issue'];
        $availble          = '0';
        $availble_progress = 0;
        $issued_progress   = 0;

        if ($total_qty > 0) {
            $availble          = $total_qty - $total_issued;
            $availble_progress = ($availble * 100) / $total_qty;
            $issued_progress   = ($total_issued * 100) / $total_qty;
        }

        $data['book_overview'] = array(
            'total'             => $total_qty,
            'total_progress'    => 100,
            'availble'          => $availble,
            'availble_progress' => round($availble_progress, 2),
            'total_issued'      => $total_issued,
            'issued_progress'   => round($issued_progress, 2),
            'dueforreturn'      => $dueforreturn,
            'forreturn'         => $forreturn,
        );

        $Attendence                   = $this->stuattendence_model->getTodayDayAttendance($total_students);
        $data['attendence_data']      = $Attendence;
        $Staffattendence              = $this->Staff_model->getTodayDayAttendance();
        $data['Staffattendence_data'] = $Staffattendence;
        $getTotalStaff                = $this->Staff_model->getTotalStaff();
        $data['getTotalStaff_data']   = $getTotalStaff;
        if ($getTotalStaff > 0) {$percentTotalStaff_data = ($Staffattendence * 100) / ($getTotalStaff);} else { $percentTotalStaff_data = '0';}
        $data['percentTotalStaff_data'] = $percentTotalStaff_data;
        $data['sch_setting']            = $this->sch_setting_detail;

        if ($data['sch_setting']->attendence_type == 0) {
            $data['std_graphclass'] = "col-lg-3 col-md-6 col-sm-6";
        } else {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }


    private function getStaffDesignationCounts()
{
    $designations = $this->db->select('id, designation')
                            ->from('staff_designation')
                            ->where('is_active', '1')
                            ->get()
                            ->result_array();
    
    $designation_counts = array();
    
    foreach ($designations as $designation) {
        $count = $this->db->select('COUNT(*) as count')
                         ->from('staff')
                         ->where('designation', $designation['id'])
                         ->where('is_active', 1)
                         ->get()
                         ->row();
        
        $designation_counts[$designation['designation']] = $count ? $count->count : 0;
    }
    
    return $designation_counts;
}

    public function getUserImage()
    {
        $id     = $this->session->userdata["admin"]["id"];
        $result = $this->staff_model->get($id);
    }

    public function getSession()
    {
        if (!$this->rbac->hasPrivilege('quick_session_change', 'can_view')) {
            access_denied();
        }
        $session             = $this->session_model->getAllSession();
        $data                = array();
        $session_array       = $this->session->has_userdata('session_array');
        $data['sessionData'] = array('session_id' => 0);
        if ($session_array) {
            $data['sessionData'] = $this->session->userdata('session_array');
        } else {
            $setting             = $this->setting_model->get();
            $data['sessionData'] = array('session_id' => $setting[0]['session_id']);
        }
        $data['sessionList'] = $session;
        $this->load->view('admin/partial/_session', $data);
    }

    public function updateSession()
    {
        $session       = $this->input->post('popup_session');
        $session_array = $this->session->has_userdata('session_array');
        if ($session_array) {
            $this->session->unset_userdata('session_array');
        }
        $session       = $this->session_model->get($session);
        $session_array = array('session_id' => $session['id'], 'session' => $session['session']);
        $this->session->set_userdata('session_array', $session_array);
        echo json_encode(array('status' => 1, 'message' => $this->lang->line('session_changed_successfully')));
    }

    public function updatePurchaseCode()
    {
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('envato_market_purchase_code', $this->lang->line('purchase_code'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'email'                       => form_error('email'),
                'envato_market_purchase_code' => form_error('envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->app_update();
        }
    }

    public function backup()
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/backup');
        $this->session->set_userdata('inner_menu', 'admin/backup');
        $data['title'] = $this->lang->line('backup_history');
        if ($this->input->server('REQUEST_METHOD') == "POST") {
            if ($this->input->post('backup') == "upload") {
                $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
                if ($this->form_validation->run() == false) {

                } else {
                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $fileInfo  = pathinfo($_FILES["file"]["name"]);
                        $file_name = "db-" . date("Y-m-d_H-i-s") . ".sql";
                        move_uploaded_file($_FILES["file"]["tmp_name"], "./backup/temp_uploaded/" . $file_name);
                        $folder_name  = 'temp_uploaded';
                        $path         = './backup/';
                        $filePath     = $path . $folder_name . '/' . $file_name;
                        $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                        $db           = (array) get_instance()->db;
                        $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                        $sql   = '';
                        $error = '';

                        if (file_exists($filePath)) {
                            $lines = file($filePath);

                            foreach ($lines as $line) {

                                // Ignoring comments from the SQL script
                                if (substr($line, 0, 2) == '--' || $line == '') {
                                    continue;
                                }

                                $sql .= $line;

                                if (substr(trim($line), -1, 1) == ';') {
                                    $result = mysqli_query($conn, $sql);
                                    if (!$result) {
                                        $error .= mysqli_error($conn) . "\n";
                                    }
                                    $sql = '';
                                }
                            }
                            $msg = $this->lang->line('restored_message');
                        } // end if file exists

                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                        redirect('admin/admin/backup');
                    }
                }
            }
            if ($this->input->post('backup') == "backup") {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                $this->load->helper('download');
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
                redirect('admin/admin/backup');
                force_download($filename, $backup);
                $this->session->set_flashdata('feedback', $this->lang->line('success_message_for_client_to_see'));
                redirect('admin/admin/backup');
            } else if ($this->input->post('backup') == "restore") {
                $folder_name  = 'database_backup';
                $file_name    = $this->input->post('filename');
                $path         = './backup/';
                $filePath     = $path . $folder_name . '/' . $file_name;
                $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                $db           = (array) get_instance()->db;
                $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                $sql   = '';
                $error = '';

                if (file_exists($filePath)) {
                    $lines = file($filePath);

                    foreach ($lines as $line) {

                        // Ignoring comments from the SQL script
                        if (substr($line, 0, 2) == '--' || $line == '') {
                            continue;
                        }

                        $sql .= $line;

                        if (substr(trim($line), -1, 1) == ';') {
                            $result = mysqli_query($conn, $sql);
                            if (!$result) {
                                $error .= mysqli_error($conn) . "\n";
                            }
                            $sql = '';
                        }
                    }
                    $msg = $this->lang->line('restored_message');
                } // end if file exists
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $msg . '</div>');
                redirect('admin/admin/backup');
            }
        }
        $dir    = "./backup/database_backup/";
        $result = array();
        $cdir   = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
        $data['dbfileList']  = $result;
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/backup', $data);
        $this->load->view('layout/footer', $data);
    }

    public function changepass()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'changepass/index');
        $data['title'] = 'Change Password';
        $this->form_validation->set_rules('current_pass', $this->lang->line("current_password"), 'trim|required|xss_clean');
        $this->form_validation->set_rules('new_pass', $this->lang->line("new_password"), 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', $this->lang->line("confirm_password"), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $sessionData            = $this->session->userdata('admin');
            $this->data['id']       = $sessionData['id'];
            $this->data['username'] = $sessionData['username'];
            $this->load->view('layout/header', $data);
            $this->load->view('admin/change_password', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sessionData = $this->session->userdata('admin');
            $userdata    = $this->customlib->getUserData();
            $data_array  = array(
                'current_pass' => $this->input->post('current_pass'),
                'new_pass'     => md5($this->input->post('new_pass')),
                'user_id'      => $sessionData['id'],
                'user_email'   => $sessionData['email'],
                'user_name'    => $sessionData['username'],
            );
            $newdata = array(
                'id'       => $sessionData['id'],
                'password' => $this->enc_lib->passHashEnc($this->input->post('new_pass')),
            );
            $check  = $this->enc_lib->passHashDyc($this->input->post('current_pass'), $userdata["password"]);
            $query1 = $this->admin_model->checkOldPass($data_array);

            if ($query1) {

                if ($check) {
                    $query2 = $this->admin_model->saveNewPass($newdata);
                    if ($query2) {
                        $data['error_message'] = "<div class='alert alert-success'>" . $this->lang->line("password_changed_successfully") . "</div>";
                        $this->load->view('layout/header', $data);
                        $this->load->view('admin/change_password', $data);
                        $this->load->view('layout/footer', $data);
                    }
                } else {
                    $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("invalid_current_password") . "</div>";
                    $this->load->view('layout/header', $data);
                    $this->load->view('admin/change_password', $data);
                    $this->load->view('layout/footer', $data);
                }
            } else {

                $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("invalid_current_password") . "</div>";
                $this->load->view('layout/header', $data);
                $this->load->view('admin/change_password', $data);
                $this->load->view('layout/footer', $data);
            }
        }
    }

    public function pdf_report()
    {
        $data        = array();
        $html        = $this->load->view('reports/students_detail', $data, true);
        $pdfFilePath = "output_pdf_name.pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function downloadbackup($file)
    {
        $this->load->helper('download');
        $filepath = "./backup/database_backup/" . $file;
        $data     = file_get_contents($filepath);
        $name     = $file;
        force_download($name, $data);
    }

    public function dropbackup($file)
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_delete')) {
            access_denied();
        }
        unlink('./backup/database_backup/' . $file);
        redirect('admin/admin/backup');
    }

    public function search()
    {
        $data['title']           = 'Search';
        $search_text             = $this->input->post('search_text1');
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['search_text']     = trim($this->input->post('search_text1'));
        $userdata                = $this->customlib->getUserData();
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $carray                  = array();
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();

        // if (!empty($data["classlist"])) {
        //     foreach ($data["classlist"] as $ckey => $cvalue) {

        //         $carray[] = $cvalue["id"];
        //     }
        // }

        // $resultlist = $this->student_model->searchusersbyFullText($search_text, $carray);
        // $data['resultlist'] = $resultlist;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/search', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getCollectionbymonth()
    {
        $result = $this->admin_model->getMonthlyCollection();
        return $result;
    }

    public function getCollectionbyday($date)
    {
        $result = $this->admin_model->getCollectionbyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebyday($date)
    {
        $result = $this->admin_model->getExpensebyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebymonth()
    {
        $result = $this->admin_model->getMonthlyExpense();
        return $result;
    }

    public function whatever($feecollection_array, $start_month_date, $end_month_date)
    {
        $return_amount = 0;
        $st_date       = strtotime($start_month_date);
        $ed_date       = strtotime($end_month_date);
        if (!empty($feecollection_array)) {
            while ($st_date <= $ed_date) {
                $date = date('Y-m-d', $st_date);
                foreach ($feecollection_array as $key => $value) {

                    if ($value['date'] == $date) {

                        $return_amount = $return_amount + $value['amount'] + $value['amount_fine'];
                    }
                }
                $st_date = $st_date + 86400;
            }
        } else {

        }

        return $return_amount;
    }

    public function startmonthandend()
    {
        $startmonth = $this->setting_model->getStartMonth();
        if ($startmonth == 1) {
            $endmonth = 12;
        } else {
            $endmonth = $startmonth - 1;
        }
        return array($startmonth, $endmonth);
    }

    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('sql');
            $temp        = explode(".", $_FILES["file"]["name"]);
            $extension   = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["file"]["type"] != 'application/octet-stream') {
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_type_not_allowed"));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_upload', $this->lang->line("extension_not_allowed"));
                return false;
            }
            if ($_FILES["file"]["size"] > 102400000) {
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_size_shoud_be_less_than") . ' 100 MB');
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line("the_file_field_is_required"));
            return false;
        }
    }

    public function generate_key($length = 12)
    {
        $str        = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max        = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function addCronsecretkey($id)
    {
        $key  = $this->generate_key(25);
        $data = array('cron_secret_key' => $key);
        $this->setting_model->add_cronsecretkey($data, $id);
        redirect('admin/admin/backup');
    }

    public function updateandappCode()
    {
        $this->form_validation->set_rules('app-email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->andapp_update();
        }
    }

    public function filetype()
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_view')) {
            access_denied();
        }
        
        $data          = array();
        $data['title'] = 'File Type List';
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/filetype');
        $data['filetype'] = $this->filetype_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/filetype', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addfiletype()
    {
        $this->form_validation->set_rules('file_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('image_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('file_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('file_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'file_extension'  => form_error('file_extension'),
                'file_mime'       => form_error('file_mime'),
                'image_extension' => form_error('image_extension'),
                'image_mime'      => form_error('image_mime'),
                'image_size'      => form_error('image_size'),
                'file_size'       => form_error('file_size'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $insert_array = array(
                'file_extension'  => $this->input->post('file_extension'),
                'file_mime'       => $this->input->post('file_mime'),
                'image_extension' => $this->input->post('image_extension'),
                'image_mime'      => $this->input->post('image_mime'),
                'file_size'       => $this->input->post('file_size'),
                'image_size'      => $this->input->post('image_size'),
            );

            $inserted_id = $this->filetype_model->add($insert_array);

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function validate_extension($extension)
    {
        if (preg_match('/^([A-Za-z0-9]+)(,\s[A-Za-z0-9]+)*$/', $extension)) {
            return true;
        } else {
            $this->form_validation->set_message('validate_extension', 'The %s field must be like jpg, jpeg');
            return false;
        }
    }

    public function validate_mime($mime)
    {
        if (preg_match('/^([A-Za-z0-9-.+\/]+)(,\s[A-Za-z0-9-.+\/]+)*$/', $mime)) {
            return true;
        } else {
            $this->form_validation->set_message('validate_mime', 'The %s field must be like audio/mp4, video/mp4');
            return false;
        }
    }

    public function updateaddon()
    {
        $this->form_validation->set_rules('app-email', $this->lang->line('email'), 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', $this->lang->line('purchase_code'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );

            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->addon_update();
        }
    }

    public function searchvalidation()
    {
        $search_text1 = $this->input->post('search_text1');
        $params       = array('search_text1' => $search_text1);
        $array        = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function search_text()
    {
        // Accept both search_text and search_text1 for compatibility
        $search_text1 = $this->input->post('search_text') ? $this->input->post('search_text') : $this->input->post('search_text1');
        $params       = array('search_text' => $search_text1);
        $array        = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function dtstudentlist()
    {
        // Accept both search_text and search_text1 for compatibility
        $search_text = $this->input->post('search_text') ? $this->input->post('search_text') : $this->input->post('search_text1');
        $sch_setting     = $this->sch_setting_detail;
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $classlist       = $this->class_model->get();
        $classlist       = $classlist;
        $carray          = array();
        if (!empty($classlist)) {
            foreach ($classlist as $ckey => $cvalue) {
                $carray[] = $cvalue["id"];
            }
        }
        $search=$this->input->post('search');

      


        $resultlist      = $this->student_model->searchFullText($search_text, $carray);
        $start           = $this->input->post('start');
        $length          = $this->input->post('length');
     
        $resultlist_view = $this->student_model->getSearchFullView($search_text, $start, $length,$search, $carray);

        $data = array(
            'resultlist'      => $resultlist_view,
            'sch_setting'     => $this->sch_setting_detail,
            'adm_auto_insert' => $this->sch_setting_detail->adm_auto_insert,
            'currency_symbol' => $this->customlib->getSchoolCurrencyFormat(),
        );

        $resultlist_view = $this->load->view('admin/resultlist_view', $data, true);

        $fields   = $this->customfield_model->get_custom_fields('students', 1);
        $students = json_decode($resultlist);
        $dt_data  = array();
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {

                $editbtn    = '';
                $deletebtn  = '';
                $viewbtn    = '';
                $collectbtn = "";
                $viewbtn    = "<a href='" . base_url() . "student/view/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('show') . "'><i class='fa fa-reorder'></i></a>";

                if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "student/edit/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) {

                    $collectbtn = "<a href='" . base_url() . "studentfee/addfee/" . $student->student_session_id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('add_fees') . "'><span >" . $currency_symbol . "</a>";
                }

                $row   = array();
                $row[] = $student->admission_no;
                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";
                $row[] = $student->class . "(" . $student->section . ")";
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }

                $row[] = $this->customlib->dateformat($student->dob);

                $row[] = $this->lang->line(strtolower($student->gender));
                if ($sch_setting->category) {
                    $row[] = $student->category;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno;
                }

                foreach ($fields as $fields_key => $fields_value) {

                    $custom_name   = $fields_value->name;
                    $display_field = $student->$custom_name;
                    if ($fields_value->type == "link") {
                        $display_field = "<a href=" . $student->$custom_name . " target='_blank'>" . $student->$custom_name . "</a>";

                    }
                    $row[] = $display_field;

                }
                $row[] = $viewbtn . '' . $editbtn . '' . $collectbtn;

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($students->draw),
            "recordsTotal"    => intval($students->recordsTotal),
            "recordsFiltered" => intval($students->recordsFiltered),
            "data"            => $dt_data,
            "resultlist_view" => $resultlist_view,
        );
        echo json_encode($json_data);

    }

    private function getStudentsByProgramAndGender()
{
    try {
        // Check if both tables exist
        if ($this->db->table_exists('students') && $this->db->table_exists('programs')) {
            // Verify column names
            if ($this->db->field_exists('gender', 'students') && 
                $this->db->field_exists('program_id', 'students') && 
                $this->db->field_exists('program_name', 'programs') && 
                $this->db->field_exists('id', 'programs')) {
                
                $this->db->select('programs.program_name, 
                                   SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) as male_count,
                                   SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) as female_count');
                $this->db->from('students');
                $this->db->join('programs', 'programs.id = students.program_id', 'left');
                
                // Only add the is_active condition if the column exists
                if ($this->db->field_exists('is_active', 'students')) {
                    $this->db->where('students.is_active', 'yes');
                }
                
                $this->db->group_by('students.program_id');
                $query = $this->db->get();
                
                if ($query && $query->num_rows() > 0) {
                    return $query->result_array();
                }
            }
        }
        
        // Return empty array if any checks fail
        return [];
    } catch (Exception $e) {
        log_message('error', 'Error in getStudentsByProgramAndGender: ' . $e->getMessage());
        return [];
    }
}
}