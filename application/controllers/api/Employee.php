<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Employee API Controller
 * 
 * This controller handles API endpoints for employee/staff data.
 * 
 * @package         CodeIgniter
 * @subpackage      REST API
 * @category        Controller
 */

// This is needed to use REST_Controller
require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Employee extends REST_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load required models
        $this->load->database();
        $this->load->model('staff_model');
        $this->load->model('setting_model');
        $this->load->library('authorization_token');
        $this->load->library('hemis_rest_auth');
        $this->load->library('hemis_client');
        
        // Explicitly configure allowed methods
        $this->methods['index_get']['limit'] = 500; // Allow GET requests
        $this->methods['index_post']['limit'] = 100; // Allow POST requests
        $this->methods['index_patch']['limit'] = 100;

        $this->load->config('api_cors', true);
        $origin = $this->config->item('allow_origin', 'api_cors') ?: '*';
        $methods = $this->config->item('allow_methods', 'api_cors') ?: 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
        $hdrs = $this->config->item('allow_headers', 'api_cors') ?: 'Content-Type, Authorization, X-Requested-With';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: ' . $methods);
        header('Access-Control-Allow-Headers: ' . $hdrs);
        
        // Better handling of OPTIONS requests - let the controller method handle it
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $this->output->set_status_header(200);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array('status' => 'success')));
            $this->output->_display();
            exit;
        }
        
        // Log request method
        log_message('debug', 'Employee API accessed with method: ' . $_SERVER['REQUEST_METHOD']);
    }

    /**
     * Alias for HEMIS-style route api/Employee/GetAllEmployeees (typo preserved in UGC doc).
     */
    public function getAllEmployees_get()
    {
        return $this->index_get();
    }

    /**
     * Get All Employees API
     * 
     * @method: GET
     * @url: api/employee
     * @return: JSON with list of employees
     */
    public function index_get() {
        // Log API access
        log_message('debug', 'Employee API accessed via GET method');
        
        // Get request headers for token validation
        $headers = $this->input->request_headers();
        log_message('debug', 'API Headers: ' . json_encode($headers));
        
        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();
        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => false,
                'message' => 'Unauthorized Access',
                'data' => [],
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        try {
            // Get staff data from model
            $staff_list = $this->staff_model->get_all();
            log_message('debug', 'Staff found: ' . count($staff_list));
            
            // Format response data according to API documentation
            $formatted_staff = [];
            foreach ($staff_list as $staff) {
                // Map database fields to API response fields
                $formatted_staff[] = [
                    'id' => (int)$staff['id'],
                    'userId' => null,
                    'user' => null,
                    'firstName' => $staff['name'],
                    'middleName' => $staff['middle_name'],
                    'lastName' => $staff['surname'],
                    'phoneNumber' => $staff['contact_no'],
                    'gender' => strtolower($staff['gender']),
                    'email' => $staff['email'],
                    'ethnicity' => $staff['caste'],
                    'campusId' => (int)$staff['institution_id'],
                    'universityId' => null,
                    'teachingFacultyName' => $staff['teaching_faculty_name'] ?? 'undefined',
                    'employeeType' => $staff['employee_type'] ?? 'Other',
                    'citizenshipNo' => $this->extract_citizenship_no($staff['citizenship']),
                    'orgId' => $staff['org_id'] ? (int)$staff['org_id'] : null,
                    'dateOFBirth' => $this->format_date($staff['dob']),
                    'dateOFBirthAd' => null,
                    'employeePositionId' => $staff['employee_position_id'] ? (int)$staff['employee_position_id'] : null,
                    'postName' => $staff['post_name'],
                    'positionType' => $staff['position_type'] ?? 'Other',
                    'salutation' => $staff['salutation'] ?? 'Mr.',
                    'ctzIssueDistrictCode' => $staff['ctz_issue_district_code'],
                    'nidNo' => $staff['nid_no'],
                    'citizenshipFront' => $staff['citizenship_front'],
                    'citizenshipBack' => $staff['citizenship_back'],
                    'nidPage' => $staff['nid_page'],
                    'ppSizePhoto' => $staff['pp_size_photo'],
                    'pProvince' => isset($staff['ugc_p_province']) ? $staff['ugc_p_province'] : '',
                    'pDistrict' => isset($staff['ugc_p_district']) ? $staff['ugc_p_district'] : '',
                    'pLocalLevel' => isset($staff['ugc_p_local_level']) ? $staff['ugc_p_local_level'] : '',
                    'pWardNo' => $staff['permanent_ward_no'],
                    'pTole' => $staff['permanent_tole'],
                    'pHouseNo' => $staff['permanent_house_no'],
                    'pAddress' => $staff['permanent_address'],
                    'fiscalYearId' => null,
                    'tProvince' => isset($staff['ugc_t_province']) ? $staff['ugc_t_province'] : '',
                    'tDistrict' => isset($staff['ugc_t_district']) ? $staff['ugc_t_district'] : '',
                    'tLocalLevel' => isset($staff['ugc_t_local_level']) ? $staff['ugc_t_local_level'] : '',
                    'tWardNo' => $staff['temporary_ward_no'],
                    'tHouseNo' => $staff['temporary_house_no'],
                    'tTole' => $staff['temporary_tole'],
                    'tAddress' => $staff['local_address'],
                    'group' => $staff['group_name'] ?? 'undefined',
                    'subGroup' => $staff['sub_group'],
                    'position' => $staff['position'] ?? 'Nonteaching',
                    'joiningType' => $staff['contract_type'] ?? 'permanent',
                    'joiningDate' => $staff['date_of_joining'] ? $this->format_nepali_date($staff['date_of_joining']) : null,
                    'workPhone' => $staff['work_phone'],
                    'workEmail' => $staff['work_email'],
                    'referenceLetter' => $staff['reference_letter'],
                    'joiningLetter' => $staff['joining_letter'],
                    'otherLetter' => $staff['other_letter'],
                    'nonTeachingType' => $staff['non_teaching_type'],
                    'institutionId' => $staff['institution_id'] ? (int)$staff['institution_id'] : null,
                    'graduatedFrom' => $this->extract_institution_name($staff['previous_institution_name']),
                    'facultyName' => $staff['faculty_name'] ?? 'undefined',
                    'levelName' => $staff['level_name'],
                    'programName' => $staff['program_name'],
                    'enrolledYear' => $staff['enrolled_year'],
                    'passedYear' => $staff['passed_year'],
                    'certificateCopy' => $staff['certificate_copy'],
                    'transCriptCopy' => $staff['transcript_copy'],
                    'marksheetCopy' => $staff['marksheet_copy'],
                    'otherImage' => $staff['other_image'],
                    'collegeName' => $staff['college_name'],
                    'campusName' => $staff['campus_name'],
                    'bankAc' => $staff['bank_account_no'],
                    'bankName' => $staff['bank_name'],
                    'bankBranch' => $staff['bank_branch'],
                    'panNo' => $staff['pan_no'],
                    'departmentId' => $staff['department'] ? (int)$staff['department'] : null,
                    'sectionId' => $staff['section_id'] ? (int)$staff['section_id'] : null,
                    'isSameAsPermanent' => (bool)$staff['is_same_as_permanent'],
                    'religion' => $staff['religion']
                ];
            }
            
            // Return the entire array directly
            return $this->response($formatted_staff, REST_Controller::HTTP_OK);
            
        } catch (Exception $e) {
            log_message('error', 'Error in Employee index_get: ' . $e->getMessage());
            return $this->response([
                'status' => false,
                'message' => 'An error occurred while retrieving employee data',
                'error_details' => $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST method for Employee
     * 
     * This method now properly handles both:
     * 1. Retrieving all employees (when called without data)
     * 2. Creating a new employee (when called with employee data)
     * 
     * @method: POST
     * @url: api/employee
     * @return: JSON with operation status or employee data array
     */

    public function index_post() {
        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();
        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => false,
                'message' => 'Unauthorized Access',
                'data' => [],
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        $in = $this->input->post();
        if (!is_array($in)) {
            $in = array();
        }

        // Map HEMIS-style API fields -> local staff table columns.
        // Do not pass through unknown keys, as staff_model::create_employee() inserts directly.
        $data = array(
            'name'            => $in['firstName'] ?? null,
            'middle_name'     => $in['middleName'] ?? null,
            'surname'         => $in['lastName'] ?? null,
            'contact_no'      => $in['phoneNumber'] ?? null,
            'gender'          => isset($in['gender']) ? ucfirst(strtolower((string) $in['gender'])) : null,
            'email'           => $in['email'] ?? null,
            'caste'           => $in['ethnicity'] ?? null,
            'institution_id'  => isset($in['campusId']) ? (int) $in['campusId'] : null,
            'post_name'       => $in['postName'] ?? null,
            'joining_date'    => $in['joiningDate'] ?? null,
            'spouse_name'     => $in['spouseName'] ?? null,
            'dependent_info'  => $in['dependentInfo'] ?? null,
            'is_active'       => 1,
        );

        // Remove nulls to let DB defaults apply.
        $data = array_filter($data, static function ($v) {
            return $v !== null;
        });

        // staff.employee_id is NOT NULL + UNIQUE in this schema; generate if caller didn't provide one.
        if (empty($data['employee_id'])) {
            $data['employee_id'] = 'QA-' . date('YmdHis') . '-' . substr((string) mt_rand(), 0, 6);
        }

        try {
            $new_employee_id = $this->staff_model->create_employee($data);

            if (!$new_employee_id) {
                return $this->response([
                    'status' => false,
                    'message' => 'Failed to create employee.',
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }

            // --- Multipart document capture (local) ---
            // Store file paths so Hemis_client can stream them later as CURLFile.
            $upload_base = './uploads/staff_documents/' . (int) $new_employee_id . '/';
            if (!is_dir($upload_base)) {
                @mkdir($upload_base, 0777, true);
            }
            $upload_config = array(
                'upload_path'   => $upload_base,
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size'      => 4096,
                'encrypt_name'  => true,
            );
            $this->load->library('upload', $upload_config);

            $file_map = array(
                'citizenshipFront' => 'citizenship_front',
                'citizenshipBack'  => 'citizenship_back',
                'nidPage'          => 'nid_page',
                'ppSizePhoto'      => 'pp_size_photo',
                'digitalSignature' => 'digital_signature',
            );

            $file_updates = array();
            foreach ($file_map as $field => $col) {
                if (empty($_FILES[$field]['name'])) {
                    continue;
                }
                $_FILES['file']['name']     = $_FILES[$field]['name'];
                $_FILES['file']['type']     = $_FILES[$field]['type'];
                $_FILES['file']['tmp_name'] = $_FILES[$field]['tmp_name'];
                $_FILES['file']['error']    = $_FILES[$field]['error'];
                $_FILES['file']['size']     = $_FILES[$field]['size'];
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $ud = $this->upload->data();
                    // Store as web-relative path to keep behavior consistent with Student module.
                    $file_updates[$col] = 'uploads/staff_documents/' . (int) $new_employee_id . '/' . $ud['file_name'];
                } else {
                    log_message('error', 'Employee doc upload failed: ' . $field . ' :: ' . $this->upload->display_errors('', ''));
                }
            }
            if (!empty($file_updates)) {
                $this->staff_model->update_employee((int) $new_employee_id, $file_updates);
            }

            $staff_row = $this->staff_model->get((int) $new_employee_id);
            // Use hardened sync: multipart + DB-file streaming + "no fake PAN/bank" precheck.
            $hemisSync = $this->hemis_client->sync_employee_after_local_create((int) $new_employee_id);

            return $this->response([
                'status' => true,
                'message' => 'Employee created successfully.',
                'id' => $new_employee_id,
                'hemisSync' => $hemisSync,
            ], REST_Controller::HTTP_OK);

        } catch (Exception $e) {
            return $this->response([
                'status' => false,
                'message' => 'Failed to create employee.',
                'error' => $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PATCH api/Employee/{id} — partial update (JSON body keys = staff columns or API field names per your mapping).
     */
    public function index_patch($id = null) {
        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();
        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => false,
                'message' => 'Unauthorized Access',
                'data' => [],
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if (empty($id)) {
            return $this->response([
                'status' => false,
                'message' => 'Employee ID is required',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $input = json_decode($this->input->raw_input_stream, true);
        if (empty($input) || !is_array($input)) {
            return $this->response([
                'status' => false,
                'message' => 'JSON body is required',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        try {
            $ok = $this->staff_model->update_employee((int) $id, $input);
            if (!$ok) {
                return $this->response([
                    'status' => false,
                    'message' => 'Employee update failed.',
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Accept optional multipart docs on PATCH as well.
            $upload_base = './uploads/staff_documents/' . (int) $id . '/';
            if (!is_dir($upload_base)) {
                @mkdir($upload_base, 0777, true);
            }
            $upload_config = array(
                'upload_path'   => $upload_base,
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size'      => 4096,
                'encrypt_name'  => true,
            );
            $this->load->library('upload', $upload_config);
            $file_map = array(
                'citizenshipFront' => 'citizenship_front',
                'citizenshipBack'  => 'citizenship_back',
                'nidPage'          => 'nid_page',
                'ppSizePhoto'      => 'pp_size_photo',
                'digitalSignature' => 'digital_signature',
            );
            $file_updates = array();
            foreach ($file_map as $field => $col) {
                if (empty($_FILES[$field]['name'])) {
                    continue;
                }
                $_FILES['file']['name']     = $_FILES[$field]['name'];
                $_FILES['file']['type']     = $_FILES[$field]['type'];
                $_FILES['file']['tmp_name'] = $_FILES[$field]['tmp_name'];
                $_FILES['file']['error']    = $_FILES[$field]['error'];
                $_FILES['file']['size']     = $_FILES[$field]['size'];
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $ud = $this->upload->data();
                    $file_updates[$col] = 'uploads/staff_documents/' . (int) $id . '/' . $ud['file_name'];
                } else {
                    log_message('error', 'Employee doc upload failed: ' . $field . ' :: ' . $this->upload->display_errors('', ''));
                }
            }
            if (!empty($file_updates)) {
                $this->staff_model->update_employee((int) $id, $file_updates);
            }

            $hemisSync = $this->hemis_client->sync_employee_after_local_update((int) $id);

            return $this->response([
                'status' => true,
                'message' => 'Employee updated successfully.',
                'id' => (int) $id,
                'hemisSync' => $hemisSync,
            ], REST_Controller::HTTP_OK);
        } catch (Exception $e) {
            return $this->response([
                'status' => false,
                'message' => $e->getMessage(),
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle OPTIONS requests for CORS preflight
     */
    public function index_options() {
        // Just return 200 OK for OPTIONS requests
        $this->response(NULL, REST_Controller::HTTP_OK);
    }

    /**
     * Extract citizenship number from JSON field
     * 
     * @param string $citizenship_json JSON string containing citizenship info
     * @return string|null The citizenship number or null
     */
    private function extract_citizenship_no($citizenship_json) {
        if (empty($citizenship_json)) {
            return null;
        }
        
        try {
            $citizenship_data = json_decode($citizenship_json, true);
            if (is_array($citizenship_data) && !empty($citizenship_data)) {
                // Return the first citizenship number found
                if (isset($citizenship_data[0]['number'])) {
                    return $citizenship_data[0]['number'];
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error parsing citizenship JSON: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Extract institution name from JSON field
     * 
     * @param string $institution_json JSON string containing institution info
     * @return string|null The institution name or null
     */
    private function extract_institution_name($institution_json) {
        if (empty($institution_json)) {
            return null;
        }
        
        try {
            $institution_data = json_decode($institution_json, true);
            if (is_array($institution_data) && !empty($institution_data)) {
                // Return the first institution name found
                if (isset($institution_data[0])) {
                    return $institution_data[0];
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Error parsing institution JSON: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Format date to API format (m/d/Y)
     * 
     * @param string $date_str Date string from database
     * @return string|null Formatted date or null
     */
    private function format_date($date_str) {
        if (empty($date_str)) {
            return null;
        }
        
        try {
            $date = new DateTime($date_str);
            return $date->format('n/j/Y');
        } catch (Exception $e) {
            log_message('error', 'Error formatting date: ' . $e->getMessage());
            return $date_str; // Return original if parsing fails
        }
    }
    
    /**
     * Format date to Nepali format (YYYY/MM/DD)
     * 
     * @param string $date_str Date string from database
     * @return string|null Formatted date or null
     */
    private function format_nepali_date($date_str) {
        if (empty($date_str)) {
            return null;
        }
        
        try {
            $date = new DateTime($date_str);
            return $date->format('Y/m/d');
            
            // For actual AD to BS conversion, you'll need to either:
            // 1. Use a Nepali date conversion library
            // 2. Implement the conversion logic here
            // 3. Call an existing function if available in your system
            
        } catch (Exception $e) {
            log_message('error', 'Error formatting Nepali date: ' . $e->getMessage());
            return $date_str; // Return original if parsing fails
        }
    }
}