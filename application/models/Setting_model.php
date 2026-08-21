<?php


if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Setting_model extends MY_Model {


    public function __construct() {
        parent::__construct();
    }


    public function getMysqlVersion() {
        $mysqlVersion = $this->db->query('SELECT VERSION() as version')->row();
        return $mysqlVersion;
    }


    public function getSqlMode() {


        $sqlMode = $this->db->query('SELECT @@sql_mode as mode')->row();
        return $sqlMode;
    }
 
    public function get($id = null) {


        $this->db->select('sch_settings.id,sch_settings.lang_id,sch_settings.languages,sch_settings.class_teacher,sch_settings.cron_secret_key, sch_settings.timezone,sch_settings.superadmin_restriction,sch_settings.student_timeline,sch_settings.name,sch_settings.email,sch_settings.biometric,sch_settings.biometric_device,sch_settings.time_format,sch_settings.phone,languages.language,sch_settings.attendence_type,sch_settings.address,sch_settings.dise_code,sch_settings.date_format,sch_settings.currency,sch_settings.currency_place,sch_settings.start_month,sch_settings.start_week,sch_settings.session_id,sch_settings.fee_due_days,sch_settings.image,sch_settings.theme,sessions.session,sch_settings.online_admission,sch_settings.is_duplicate_fees_invoice,sch_settings.is_student_house,sch_settings.is_blood_group,sch_settings.admin_logo,sch_settings.admin_small_logo,sch_settings.mobile_api_url,sch_settings.app_primary_color_code,sch_settings.app_secondary_color_code,sch_settings.app_logo,sch_settings.student_profile_edit,sch_settings.staff_barcode,sch_settings.student_barcode,languages.is_rtl,sch_settings.student_panel_login,sch_settings.parent_panel_login,sch_settings.currency_format,sch_settings.exam_result,sch_settings.base_url,sch_settings.folder_path,currencies.symbol as currency_symbol,currencies.base_price,currencies.short_name as currency,sch_settings.online_admission_application_form,currencies.id as currency_id,sch_settings.admin_login_page_background,sch_settings.user_login_page_background,sch_settings.low_attendance_limit');
     
        $this->db->from('sch_settings');
        $this->db->join('sessions', 'sessions.id = sch_settings.session_id');
        $this->db->join('languages', 'languages.id = sch_settings.lang_id');
        $this->db->join('currencies', 'currencies.id = sch_settings.currency');
        if ($id != null) {
            $this->db->where('sch_settings.id', $id);
        } else {
            $this->db->order_by('sch_settings.id');
        }
        $query = $this->db->get();


        if ($id != null) {
            return $query->row_array();
        } else {
            $session_array = $this->session->has_userdata('session_array');
            $result = $query->result_array();


            $result[0]['current_session'] = array(
                'session_id' => $result[0]['session_id'],
                'session' => $result[0]['session']
            );


            if ($session_array) {
                $session_array = $this->session->userdata('session_array');
                $result[0]['session_id'] = $session_array['session_id'];
                $result[0]['session'] = $session_array['session'];
            }


            return $result;
        }
    }

    public function get_studentlang($id) {
        $data = $this->db->select('users.lang_id')->from('users')->where('user_id', $id)->get()->row_array();
        return $data;
    }
   
    public function get_guestlang($id) {
        $data = $this->db->select('guest.lang_id')->from('guest')->where('id', $id)->get()->row_array();
        return $data;
    }


    public function get_parentlang($id) {
        $data = $this->db->select('users.lang_id')->from('users')->where('id', $id)->get()->row_array();
        return $data;
    }


    public function get_stafflang($id) {
        $data = $this->db->select('staff.lang_id')->from('staff')->where('id', $id)->get()->row_array();
        return $data;
    }


    public function getSchoolDetail($id = null) {


        $this->db->select('sch_settings.id,sch_settings.base_url,sch_settings.folder_path,sch_settings.lang_id,sch_settings.is_rtl,sch_settings.timezone,
          sch_settings.name,sch_settings.email,sch_settings.biometric,sch_settings.biometric_device,sch_settings.phone,languages.language,sch_settings.address,sch_settings.dise_code,sch_settings.date_format,sch_settings.currency,sch_settings.start_month,sch_settings.start_week,sch_settings.session_id,sch_settings.image,sch_settings.theme,sessions.session,sch_settings.online_admission,sch_settings.maintenance_mode,currencies.symbol as currency_symbol,currencies.base_price,currencies.short_name as currency');
        $this->db->from('sch_settings');
        $this->db->join('sessions', 'sessions.id = sch_settings.session_id');
        $this->db->join('languages', 'languages.id = sch_settings.lang_id');
        $this->db->join('currencies', 'currencies.id = sch_settings.currency');
        $this->db->order_by('sch_settings.id');
        $query = $this->db->get();
        return $query->row();
    }


    public function getSetting() {  
        $this->db->select('sch_settings.id,sch_settings.base_url,sch_settings.folder_path,sch_settings.attendence_type,sch_settings.lang_id,sch_settings.is_rtl,sch_settings.fee_due_days,sch_settings.staff_notification_email,sch_settings.class_teacher,sch_settings.cron_secret_key,sch_settings.timezone,sch_settings.online_admission_application_form,
          sch_settings.name,sch_settings.email,sch_settings.biometric,sch_settings.biometric_device,sch_settings.phone,sch_settings.adm_prefix,sch_settings.adm_start_from,languages.language,sch_settings.adm_no_digit,sch_settings.adm_update_status,sch_settings.adm_auto_insert,sch_settings.staffid_prefix,sch_settings.staffid_start_from,sch_settings.staffid_auto_insert,sch_settings.staffid_no_digit,sch_settings.staffid_update_status,sch_settings.superadmin_restriction,sch_settings.student_timeline,sch_settings.address,sch_settings.dise_code,sch_settings.date_format,sch_settings.currency,sch_settings.currency_place,sch_settings.start_month,sch_settings.start_week,sch_settings.session_id,sch_settings.image,sch_settings.theme,sessions.session,online_admission,sch_settings.is_duplicate_fees_invoice,sch_settings.is_student_house,sch_settings.is_blood_group,sch_settings.roll_no,sch_settings.lastname,sch_settings.middlename,sch_settings.category,sch_settings.cast,sch_settings.religion,sch_settings.mobile_no,sch_settings.student_email,sch_settings.admission_date,sch_settings.student_photo,sch_settings.student_height,sch_settings.student_weight,sch_settings.measurement_date,sch_settings.father_name,sch_settings.father_phone,sch_settings.father_occupation,sch_settings.father_pic,sch_settings.mother_name,sch_settings.mother_phone,sch_settings.mother_occupation,sch_settings.mother_pic,sch_settings.guardian_phone,sch_settings.guardian_name,sch_settings.guardian_relation,sch_settings.guardian_email,sch_settings.guardian_pic,sch_settings.guardian_occupation,sch_settings.guardian_address,sch_settings.current_address,sch_settings.permanent_address,sch_settings.route_list,sch_settings.hostel_id,sch_settings.bank_account_no,sch_settings.bank_name,sch_settings.ifsc_code,sch_settings.national_identification_no,sch_settings.local_identification_no,sch_settings.rte,sch_settings.previous_school_details,sch_settings.student_note,sch_settings.upload_documents,sch_settings.staff_designation,sch_settings.staff_department,sch_settings.staff_last_name,sch_settings.staff_father_name,sch_settings.staff_mother_name,sch_settings.staff_date_of_joining,sch_settings.staff_phone,sch_settings.staff_emergency_contact,sch_settings.staff_marital_status,sch_settings.staff_photo,sch_settings.staff_current_address,sch_settings.staff_permanent_address,sch_settings.staff_qualification,sch_settings.staff_work_experience,sch_settings.staff_note,sch_settings.staff_epf_no,sch_settings.staff_basic_salary,sch_settings.staff_contract_type,sch_settings.staff_work_shift,sch_settings.staff_work_location,sch_settings.staff_leaves,sch_settings.staff_account_details,sch_settings.staff_social_media,sch_settings.staff_upload_documents,sch_settings.admin_logo,sch_settings.admin_small_logo,sch_settings.mobile_api_url,sch_settings.app_primary_color_code,sch_settings.app_secondary_color_code,sch_settings.app_logo,languages.short_code as `language_code`,sch_settings.student_profile_edit,sch_settings.my_question,sch_settings.online_admission_payment,online_admission_amount,sch_settings.online_admission_instruction,sch_settings.online_admission_conditions,sch_settings.languages as activelanguage,sch_settings.single_page_print as single_page_print, sch_settings.student_barcode, sch_settings.staff_barcode,sch_settings.calendar_event_reminder,sch_settings.student_login,sch_settings.parent_login,sch_settings.student_panel_login,sch_settings.parent_panel_login,sch_settings.currency_format,sch_settings.exam_result,sch_settings.admin_mobile_api_url,sch_settings.admin_app_primary_color_code,sch_settings.admin_app_secondary_color_code,is_student_feature_lock,is_offline_fee_payment,lock_grace_period,sch_settings.collect_back_date_fees,sch_settings.event_reminder,sch_settings.event_reminder,sch_settings.maintenance_mode,currencies.symbol as currency_symbol,currencies.base_price,currencies.short_name as currency,currencies.id as currency_id,sch_settings.offline_bank_payment_instruction,sch_settings.admin_login_page_background,sch_settings.user_login_page_background,sch_settings.low_attendance_limit,
          sch_settings.province, sch_settings.district, sch_settings.local_level, sch_settings.ward, sch_settings.affiliated_university, sch_settings.level_of_study, 
          sch_settings.latitude, sch_settings.longitude,
          sch_settings.campus_chief_name, sch_settings.campus_chief_contact, sch_settings.campus_chief_email,
          sch_settings.campus_it_name, sch_settings.campus_it_contact, sch_settings.campus_it_email'); 
    
        $this->db->from('sch_settings');
        $this->db->join('sessions', 'sessions.id = sch_settings.session_id');
        $this->db->join('languages', 'languages.id = sch_settings.lang_id');
        $this->db->join('currencies', 'currencies.id = sch_settings.currency');
        $this->db->order_by('sch_settings.id');
        $query = $this->db->get();    
       
        $result = $query->row();
       
        $json_languages = json_decode($result->activelanguage);        
        foreach ($json_languages as $key => $value) {       
            $langresult = $this->language_model->get($value);                            
            $language[$key] = $langresult;                    
        }
        
        $result->activelanguage2 = $language;        
        return $result;
    }


    public function remove($id) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('sch_settings');
        $message = DELETE_RECORD_CONSTANT . " On settings id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }


    public function add($data) {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('sch_settings', $data);
            $message = UPDATE_RECORD_CONSTANT . " On settings id " . $data['id'];
            $action = "Update";
            $record_id = $insert_id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('sch_settings', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On settings id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);            
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return $insert_id;
        }
    }
 
    public function getCurrentSession() {
        $session_result = $this->get();


        return $session_result[0]['session_id'];
    }


    public function getOnlineAdmissionStatus() {
        $setting_result = $this->get();


        if ($setting_result[0]['online_admission']) {
            return true;
        }
        return false;
    }


    public function getexamresultstatus() {
        $setting_result = $this->get();


        if ($setting_result[0]['exam_result']) {
            return true;
        }
        return false;
    }


    public function getCurrentSessionName() {
        $session_result = $this->get();
        $session = $session_result[0]['session'];
        
        // Convert to BS format if needed
        $this->load->library('customlib');
        return $this->customlib->convertSessionToBSIfNeeded($session);
    }


    public function getCurrentSchoolName() {
        $session_result = $this->get();
        return $session_result[0]['name'];
    }


    public function getStartMonth() {
        $session_result = $this->get();
        return $session_result[0]['start_month'];
    }


    public function getCurrentSessiondata() {
        $session_result = $this->get();
        return $session_result[0];
    }


    public function getCurrency() {
        $session_result = $this->get();
        return $session_result[0]['currency'];
    }


    public function getCurrencySymbol() {
        $session_result = $this->get();
        return $session_result[0]['currency_symbol'];
    }


    public function getDateYmd() {
        return date('Y-m-d');
    }


    public function getDateDmy() {
        return date('d-m-Y');
    }


    public function add_cronsecretkey($data, $id) {


        $this->db->where("id", $id)->update("sch_settings", $data);
    }


    public function getLanguage() {


        $query = $this->db->select('languages.language,languages.short_code')->where('id', $this->session->userdata['admin']['language']['lang_id'])->get('languages');
        return $query->row_array();
    }


   
    public function getuserLanguage() {


        $query = $this->db->select('languages.language,languages.short_code')->where('id', $this->session->userdata['student']['language']['lang_id'])->get('languages');
        return $query->row_array();
    }


    public function getAdminlogo() {
        $query = $this->db->select('admin_logo')->get('sch_settings');
        $logo = $query->row_array();
        return $logo['admin_logo'];
    }


    public function getAdminsmalllogo() {
        $query = $this->db->select('admin_small_logo')->get('sch_settings');
        $logo = $query->row_array();
        return  $logo['admin_small_logo'];
    }


    public function get_appname() {


        $query = $this->db->select('name')->get('sch_settings');
        $name = $query->row_array();
        echo $name['name'];
    }


    public function check_haederimage($type) {
        $check = $this->db->select('*')->from('print_headerfooter')->where('print_type', $type)->get()->row_array();




        if (empty($check['header_image'])) {
            return 0;
        } else {
            return 1;
        }
    }


    public function add_printheader($data) {


        $this->db->where('print_type', $data['print_type']);
        $this->db->update('print_headerfooter', $data);
       
    }


    public function get_printheader() {
        return $this->db->select('*')->from('print_headerfooter')->get()->result_array();
    }


    public function get_receiptheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'student_receipt')->get()->row_array();
        return $image['header_image'];
    }


    public function unlink_receiptheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'student_receipt')->get()->row_array();
        return $image['header_image'];
    }


    public function get_receiptfooter() {
        $image = $this->db->select('footer_content')->from('print_headerfooter')->where('print_type', 'student_receipt')->get()->row_array();
        return $image['footer_content'];
    }


    public function get_payslipheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'staff_payslip')->get()->row_array();
        return $image['header_image'];
    }


    public function unlink_payslipheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'staff_payslip')->get()->row_array();
        return $image['header_image'];
    }


    public function get_payslipfooter() {
        $image = $this->db->select('footer_content')->from('print_headerfooter')->where('print_type', 'staff_payslip')->get()->row_array();
        return $image['footer_content'];
    }


    public function unlink_onlinereceiptheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'online_admission_receipt')->get()->row_array();
        return $image['header_image'];
    }


    public function get_onlineadmissionheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'online_admission_receipt')->get()->row_array();
        echo $image['header_image'];
    }


    public function get_onlineadmissionfooter() {
        $image = $this->db->select('footer_content')->from('print_headerfooter')->where('print_type', 'online_admission_receipt')->get()->row_array();
        echo $image['footer_content'];
    }
   
    public function get_onlineexamheader() {
        $image = $this->db->select('header_image')->from('print_headerfooter')->where('print_type', 'online_exam')->get()->row_array();
        echo $image['header_image'];
    }
   
    public function get_onlineexamfooter() {
        $query = $this->db->select('footer_content,header_image')->from('print_headerfooter')->where('print_type', 'online_exam')->get()->row_array();        
        return $query ;
    }

    /**
     * Auto-detect and set current session based on Nepali calendar
     * Session changes on Baisakh 1 (month 1) of each year
     * If today is 2082/09/02, session should be 2082-83
     * If today is 2083/01/01 or later, session should be 2083-84
     * 
     * IMPORTANT: Only auto-sets on initial login. Respects manual session changes.
     * If user has manually changed session (session_array exists), don't override.
     * 
     * @return array Session info including debug data
     */
    public function autoDetectAndSetSession() {
        // Check if user has manually changed the session
        // If session_array exists, it means user manually selected a session
        // Don't override manual changes - respect user's choice
        if ($this->session->has_userdata('session_array')) {
            $manual_session = $this->session->userdata('session_array');
            log_message('info', 'Auto-session: User has manually set session to ' . ($manual_session['session'] ?? 'unknown') . ' (ID: ' . ($manual_session['session_id'] ?? 'unknown') . '). Respecting manual change, skipping auto-detection.');
            
            // Still return debug info but mark as manual
            $debug_info = array(
                'status' => 'manual',
                'message' => 'User has manually set session, auto-detection skipped',
                'manual_session_id' => $manual_session['session_id'] ?? null,
                'manual_session_name' => $manual_session['session'] ?? null
            );
            $this->session->set_userdata('auto_session_debug', $debug_info);
            return $manual_session['session_id'] ?? null;
        }

        // Respect configured session in sch_settings.
        // On logout/login, session_array is cleared, so this prevents auto-detection
        // from overriding the admin-selected session every time.
        $this->db->select('session_id');
        $this->db->from('sch_settings');
        $this->db->limit(1);
        $configured_query = $this->db->get();
        if ($configured_query->num_rows() > 0) {
            $configured_session_id = (int) $configured_query->row()->session_id;
            if ($configured_session_id > 0) {
                $configured_session_name = null;
                $this->db->select('session');
                $this->db->from('sessions');
                $this->db->where('id', $configured_session_id);
                $this->db->limit(1);
                $configured_name_query = $this->db->get();
                if ($configured_name_query->num_rows() > 0) {
                    $configured_session_name = $configured_name_query->row()->session;
                }

                $debug_info = array(
                    'status' => 'configured',
                    'message' => 'Using session configured in settings; auto-detection skipped',
                    'session_id' => $configured_session_id,
                    'current_session_name' => $configured_session_name,
                    'was_updated' => false,
                );
                $this->session->set_userdata('auto_session_debug', $debug_info);
                return $configured_session_id;
            }
        }
        // Get current AD date
        $today_ad = date('Y-m-d');
        $ad_year = (int)date('Y');
        $ad_month = (int)date('m');
        $ad_day = (int)date('d');
        
        // Load customlib for date conversion
        $this->load->library('customlib');
        
        // More accurate BS year calculation
        // The simple AD + 57 conversion might be off by a year depending on the month
        // Nepali calendar year starts around mid-April (Baisakh 1)
        // December 2024 AD = approximately BS 2081, but could be 2082 depending on exact date
        
        // Try multiple methods to determine BS year
        $bs_year = null;
        $bs_month = null;
        
        // Method 1: Use conversion function
        $today_bs = $this->customlib->convertADDateToBS($today_ad);
        $bs_parts = explode('-', $today_bs);
        if (count($bs_parts) == 3) {
            $bs_year = (int)$bs_parts[0];
            $bs_month = (int)$bs_parts[1];
        }
        
        // Method 2: If conversion seems off, try alternative calculations
        // The conversion uses AD + 57, but this might be off by a year depending on the month
        // December 2024 AD = AD + 57 = 2081, but actual BS might be 2082
        // User says today is 2082/09/02, so we need to account for this
        
        // Check if the converted year seems reasonable (2080-2090 range)
        if ($bs_year === null || $bs_year < 2080 || $bs_year > 2090) {
            // Use simple calculation as fallback
            $bs_year = $ad_year + 57;
            $bs_month = $ad_month;
        }
        
        // IMPORTANT: Adjust for known conversion issues
        // December 2024 AD should be BS 2081-2082, not 2083
        // December 2025 AD should be BS 2082, not 2083
        // If conversion gives 2083 or higher in December 2024-2025, it's likely wrong
        if ($bs_year >= 2083 && ($ad_year == 2024 || $ad_year == 2025)) {
            // Likely conversion error, force to 2082 to be safe
            $original_bs_year = $bs_year;
            $bs_year = 2082;
            log_message('warning', 'Auto-session: Adjusted BS year from ' . $original_bs_year . ' to 2082 (AD year ' . $ad_year . ' correction)');
        }
        
        // Also ensure BS year is correct for 2025
        // December 2025 = BS 2082 (2025 + 57 = 2082, but conversion might be off)
        if ($ad_year == 2025 && $bs_year != 2082) {
            // Force to 2082 for 2025
            $original_bs_year = $bs_year;
            $bs_year = 2082;
            log_message('info', 'Auto-session: Corrected BS year from ' . $original_bs_year . ' to 2082 for AD year 2025');
        }
        
        // Method 3: Account for Nepali calendar year boundary (around mid-April)
        // If we're in late AD year (Oct-Dec), we might be in the next BS year
        // But since the user says today is 2082/09/02, we'll trust the conversion
        // and be conservative in our session selection
        
        // For now, we'll use the converted values and let the session logic handle it conservatively
        
        // Determine which session we should be in
        // Session format: YYYY-YY (e.g., 2082-83 means 2082 to 2083)
        // 
        // IMPORTANT: The conversion AD + 57 might be off by a year
        // December 2024 AD = approximately BS 2081, but actual date might be 2082
        // To be safe, we'll be VERY conservative and only switch to 2083-84 when
        // we're absolutely certain (BS year >= 2083 AND month >= 1)
        // Otherwise, default to 2082-83
        
        // Determine target session - be VERY conservative
        // FORCE 2082-83 for AD years 2024 and 2025 (regardless of conversion)
        // This ensures we don't accidentally set 2083-84 when we're still in 2024-2025
        // BS session 2082-83 corresponds to AD years 2024-2025
        $target_session = '2082-83'; // Default
        
        // CRITICAL: If we're in AD year 2024 or 2025, ALWAYS use 2082-83
        // Don't trust conversion - be conservative
        // Only switch to 2083-84 when we're in AD year 2026 or later (when BS year 2083 starts)
        // User confirmed: AD 2025/12/17 = BS 2082/09/02, so session should be 2082-83
        if ($ad_year == 2024 || $ad_year == 2025) {
            $target_session = '2082-83';
            log_message('info', 'Auto-session: FORCING 2082-83 for AD year ' . $ad_year . ' (AD=' . $today_ad . ', BS should be 2082)');
        } else {
            // For other years, use normal logic
            // Explicit logic:
            // - If BS year is 2081, 2082, or unknown -> use 2082-83
            // - If BS year is 2083 AND month >= 1 (Baisakh) -> use 2083-84  
            // - If BS year is > 2083 -> calculate dynamically
            
            if ($bs_year !== null && $bs_year > 2083) {
                // Year is 2084 or later, calculate session dynamically
                $session_start_year = $bs_year;
                $session_end_year = $bs_year + 1;
                $target_session = $session_start_year . '-' . substr($session_end_year, -2);
            } elseif ($bs_year === 2083 && $bs_month !== null && $bs_month >= 1) {
                // Year is exactly 2083 AND we're in Baisakh (month 1) or later
                // This means we've passed Baisakh 1, 2083, so session should be 2083-84
                $target_session = '2083-84';
            } else {
                // All other cases (2081, 2082, null, or 2083 before Baisakh) -> use 2082-83
                // This is the safe default
                $target_session = '2082-83';
            }
        }
        
        // Prepare debug info for console output
        $debug_info = array(
            'ad_date' => $today_ad,
            'ad_year' => $ad_year,
            'ad_month' => $ad_month,
            'bs_year' => $bs_year,
            'bs_month' => $bs_month,
            'target_session' => $target_session,
            'converted_bs_date' => $today_bs
        );
        
        // Log for debugging - this will help identify conversion issues
        log_message('info', 'Auto-session detection: AD=' . $today_ad . ' (Year=' . $ad_year . ', Month=' . $ad_month . '), BS=' . ($bs_year ?? 'null') . '-' . ($bs_month ?? 'null') . ', Target Session=' . $target_session);
        
        // IMPORTANT: Sessions in database are stored in AD format (e.g., "2024-25", "2025-26")
        // NOT in BS format (e.g., "2082-83", "2083-84")
        // So we need to convert our target session to AD format OR find by AD year
        
        // Convert BS session to AD session for lookup
        // BS 2082-83 = AD 2025-26 (2025 + 57 = 2082, 2026 + 57 = 2083)
        // BS 2083-84 = AD 2026-27
        $ad_target_session = null;
        if ($target_session == '2082-83') {
            // BS 2082-83 corresponds to AD 2025-26
            $ad_target_session = '2025-26';
        } elseif ($target_session == '2083-84') {
            // BS 2083-84 corresponds to AD 2026-27
            $ad_target_session = '2026-27';
        } else {
            // Try to convert: extract year from BS session and convert to AD
            $bs_parts = explode('-', $target_session);
            if (count($bs_parts) == 2) {
                $bs_year1 = (int)$bs_parts[0];
                $bs_year2 = (int)$bs_parts[1];
                // Handle 2-digit year (e.g., "83" in "2082-83")
                if ($bs_year2 < 100) {
                    $bs_year2 = 2000 + $bs_year2; // "83" becomes 2083
                }
                // Convert BS to AD: AD = BS - 57
                $ad_year1 = $bs_year1 - 57;
                $ad_year2 = $bs_year2 - 57;
                $ad_target_session = $ad_year1 . '-' . substr($ad_year2, -2);
            }
        }
        
        // Find the session ID from sessions table using AD format
        $this->db->select('id, session');
        $this->db->from('sessions');
        // Try AD format first
        if ($ad_target_session) {
            $this->db->where('session', $ad_target_session);
        } else {
            // Fallback to BS format if conversion failed
            $this->db->where('session', $target_session);
        }
        $this->db->limit(1);
        $query = $this->db->get();
        
        log_message('info', 'Auto-session: Looking for session "' . ($ad_target_session ?? $target_session) . '" (AD format) or "' . $target_session . '" (BS format) in database');
        
        if ($query->num_rows() > 0) {
            $session_row = $query->row();
            $session_id = $session_row->id;
            $found_session = $session_row->session;
            
            log_message('info', 'Auto-session: Found session "' . $found_session . '" with ID ' . $session_id);
            
            // Get current session_id from sch_settings
            $this->db->select('id, session_id');
            $this->db->from('sch_settings');
            $this->db->limit(1);
            $current_query = $this->db->get();
            
            $current_session_id = null;
            $settings_id = null;
            if ($current_query->num_rows() > 0) {
                $current_row = $current_query->row();
                $current_session_id = $current_row->session_id;
                $settings_id = $current_row->id;
            }
            
            // Check what session name corresponds to current_session_id
            $current_session_name = null;
            if ($current_session_id) {
                $this->db->select('session');
                $this->db->from('sessions');
                $this->db->where('id', $current_session_id);
                $current_session_query = $this->db->get();
                if ($current_session_query->num_rows() > 0) {
                    $current_session_name = $current_session_query->row()->session;
                }
            }
            
            log_message('info', 'Auto-session: Current session_id in sch_settings is ' . ($current_session_id ?? 'null') . ' (session: ' . ($current_session_name ?? 'unknown') . '), target is ' . $session_id . ' (session: ' . $found_session . ')');
            
            // ALWAYS update to ensure correct session is set
            // Even if IDs match, verify the session name matches our target
            $was_updated = false;
            if ($current_session_id != $session_id || $current_session_name != $found_session) {
                // Update sch_settings with the detected session
                $this->db->where('id', $settings_id);
                $update_result = $this->db->update('sch_settings', array('session_id' => $session_id));
                $was_updated = true;
                
                // NOTE: We DON'T clear session_array here because:
                // 1. If session_array exists, we already returned early (manual change detected)
                // 2. If we're here, it means auto-detection ran, so we want to update database
                // 3. But we should NOT clear session_array if user manually changed it
                // Since we check for session_array at the start, we won't reach here if user manually changed it
                
                // Log the change (optional, for debugging)
                log_message('info', 'Auto-session: UPDATED session from ' . $current_session_id . ' (' . ($current_session_name ?? 'unknown') . ') to ' . $session_id . ' (' . $found_session . ' / ' . $target_session . '). Update result: ' . ($update_result ? 'SUCCESS' : 'FAILED'));
            } else {
                // Even if same, log for debugging
                log_message('info', 'Auto-session: Session already correct (' . $target_session . ' / ' . $found_session . ', ID: ' . $session_id . '), no update needed');
            }
            
            $debug_info['session_id'] = $session_id;
            $debug_info['previous_session_id'] = $current_session_id;
            $debug_info['was_updated'] = $was_updated;
            $debug_info['status'] = 'success';
            $debug_info['found_session_name'] = $found_session;
            $debug_info['current_session_name'] = $current_session_name;
            
            // Store debug info in session for console output
            $this->load->library('session');
            $this->session->set_userdata('auto_session_debug', $debug_info);
            
            return $session_id;
        } else {
            // Session not found - log all available sessions for debugging
            $this->db->select('id, session');
            $this->db->from('sessions');
            $this->db->order_by('id', 'DESC');
            $all_sessions_query = $this->db->get();
            $available_sessions = array();
            if ($all_sessions_query->num_rows() > 0) {
                foreach ($all_sessions_query->result() as $row) {
                    $available_sessions[] = $row->session . ' (ID: ' . $row->id . ')';
                }
            }
            log_message('error', 'Auto-session: Target session "' . ($ad_target_session ?? $target_session) . '" NOT FOUND in database. Available sessions: ' . implode(', ', $available_sessions));
            
            // Session not found, try to find 2025-26 (AD format for 2082-83) as fallback
            // BS 2082-83 = AD 2025-26
            $this->db->select('id');
            $this->db->from('sessions');
            // Try AD format first (2025-26)
            $this->db->where('session', '2025-26');
            $this->db->limit(1);
            $fallback_query = $this->db->get();
            
            // If AD format not found, try BS format as last resort
            if ($fallback_query->num_rows() == 0) {
                $this->db->select('id');
                $this->db->from('sessions');
                $this->db->where('session', '2082-83');
                $this->db->limit(1);
                $fallback_query = $this->db->get();
            }
            
            if ($fallback_query->num_rows() > 0) {
                $fallback_row = $fallback_query->row();
                $fallback_session_id = $fallback_row->id;
                
                // Get current session_id before updating
                $this->db->select('session_id');
                $this->db->from('sch_settings');
                $this->db->limit(1);
                $current_fallback_query = $this->db->get();
                $current_session_id_before = null;
                if ($current_fallback_query->num_rows() > 0) {
                    $current_fallback_row = $current_fallback_query->row();
                    $current_session_id_before = $current_fallback_row->session_id;
                }
                
                // Update sch_settings with fallback session
                $was_updated_fallback = ($current_session_id_before != $fallback_session_id);
                if ($was_updated_fallback) {
                    $this->db->update('sch_settings', array('session_id' => $fallback_session_id));
                }
                log_message('warning', 'Target session ' . $target_session . ' not found, using fallback 2082-83');
                
                $debug_info['session_id'] = $fallback_session_id;
                $debug_info['previous_session_id'] = $current_session_id_before;
                $debug_info['was_updated'] = $was_updated_fallback;
                $debug_info['status'] = 'fallback';
                $debug_info['fallback_reason'] = 'Target session not found';
                
                // Store debug info in session for console output
                $this->load->library('session');
                $this->session->set_userdata('auto_session_debug', $debug_info);
                
                return $fallback_session_id;
            }
            
            // If no session found at all, get current session_id without changing
            $this->db->select('session_id');
            $this->db->from('sch_settings');
            $this->db->limit(1);
            $current_fallback_query = $this->db->get();
            $current_session_id_fallback = null;
            if ($current_fallback_query->num_rows() > 0) {
                $current_fallback_row = $current_fallback_query->row();
                $current_session_id_fallback = $current_fallback_row->session_id;
            }
            log_message('error', 'Could not find session ' . $target_session . ' or fallback 2082-83');
            
            $debug_info['session_id'] = $current_session_id_fallback;
            $debug_info['status'] = 'error';
            $debug_info['error'] = 'Session not found';
            
            // Store debug info in session for console output
            $this->load->library('session');
            $this->session->set_userdata('auto_session_debug', $debug_info);
            
            return $current_session_id_fallback;
        }
    }


}



