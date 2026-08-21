<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/**
 * StudentUpgrade API Controller
 * 
 * This controller handles the API endpoints for upgrading students from one semester/year to the next
 */
class StudentUpgrade extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('setting_model');
        $this->load->library('hemis_client');
        $this->load->model('student_model');
        $this->load->model('session_model');
        $this->load->model('program_model');
        $this->load->model('student_session_model');
        $this->load->model('batch_model');
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
    }
    
    /**
     * Upgrade students API endpoint
     * POST: /api/StudentUpgrade
     */
    public function index_post() {
        // Get request body
        $request_data = $this->request->body;
        
        // If not in correct format, try getting from input
        if (empty($request_data)) {
            $request_data = json_decode($this->input->raw_input_stream, true);
        }
        
        // Validate request data
        if (empty($request_data) || !is_array($request_data)) {
            $this->response([
                'status' => false,
                'message' => 'Invalid request data. Expected array of student upgrade information.'
            ], REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        // Get current session ID (you might need to adjust this based on your application logic)
        $current_session = $this->session_model->get_current_session();
        $session_id = $current_session['id'];
        
        // Process each student upgrade
        $response_data = [];
        $errors = [];
        
        foreach ($request_data as $upgrade_data) {
            // Validate required fields
            if (!isset($upgrade_data['studentId']) || empty($upgrade_data['programId']) || 
                (empty($upgrade_data['year']) && empty($upgrade_data['semester']))) {
                $errors[] = "Missing required fields for studentId: " . ($upgrade_data['studentId'] ?? 'unknown');
                continue;
            }
            
            $student_id = $upgrade_data['studentId'];
            $program_id = $upgrade_data['programId'];
            
            // Get student details
            $student = $this->student_model->get_student_by_id($student_id);
            if (empty($student)) {
                $errors[] = "Student not found with ID: $student_id";
                continue;
            }
            
            // Get program details
            $program = $this->program_model->get_program_by_id($program_id);
            if (empty($program)) {
                $errors[] = "Program not found with ID: $program_id";
                continue;
            }
            
            // Get student's current session
            $current_student_session = $this->student_session_model->get_student_current_session($student_id);
            
            // Determine the class_id and section_id based on the year/semester
            // This might need adjustment based on your school's class and section structure
            $class_id = $this->_get_class_id_by_year_semester($program_id, $upgrade_data['year'], $upgrade_data['semester']);
            if ($class_id < 1 && !empty($current_student_session['class_id'])) {
                $class_id = (int) $current_student_session['class_id'];
            }
            $section_id = $current_student_session ? $current_student_session['section_id'] : 0;
            
            // Get batch details (optional table in some deployments)
            $batch_id = $upgrade_data['batchId'];
            $batch = null;
            if ($this->db->table_exists('batches')) {
                $batch = $this->batch_model->get_batch_by_id($batch_id);
            }
            if (!is_array($batch)) {
                $batch = array(
                    'name'        => (string) date('Y'),
                    'name_nepali' => '',
                );
            }
            
            // Prepare data for student_session table
            $student_session_data = [
                'session_id' => $session_id,
                'student_id' => $student_id,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'program_id' => $program_id,
                'is_active' => 'yes',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d')
            ];
            
            // If current student has hostel room, transport route, etc., copy those to the new session
            if ($current_student_session) {
                $student_session_data['hostel_room_id'] = $current_student_session['hostel_room_id'] ?? null;
                $student_session_data['vehroute_id'] = $current_student_session['vehroute_id'] ?? null;
                $student_session_data['route_pickup_point_id'] = $current_student_session['route_pickup_point_id'] ?? null;
                $student_session_data['transport_fees'] = $current_student_session['transport_fees'] ?? 0.00;
                $student_session_data['fees_discount'] = $current_student_session['fees_discount'] ?? 0.00;
            }
            
            // Save to database
            $inserted_id = $this->student_session_model->add_student_session($student_session_data);
            
            if ($inserted_id) {
                // If the student was successfully upgraded, update the student's details if necessary
                // For example, update the student's year/semester in the students table if you store this information there
                $student_update_data = [
                    'updated_at' => date('Y-m-d')
                ];
                
                if (!empty($upgrade_data['year'])) {
                    $student_update_data['semister_or_year_no'] = $upgrade_data['year'];
                }
                
                $this->student_model->update_student($student_id, $student_update_data);
                
                // Prepare response in the format specified in the documentation
                $response_item = [
                    'id' => $inserted_id,
                    'programId' => $program_id,
                    'programName' => $program['name'] ?? '',
                    'programType' => $program['type'] ?? 'annual',
                    'levelName' => $program['level'] ?? 'Bachelor',
                    'facultyName' => $program['faculty'] ?? 'Education',
                    'studentId' => $student_id,
                    'student' => [
                        'id' => $student_id,
                        'userId' => $student['user_id'] ?? null,
                        'programId' => $program_id,
                        'firstName' => $student['firstname'] ?? '',
                        'middleName' => $student['middlename'] ?? null,
                        'lastName' => $student['lastname'] ?? '',
                        'phoneNumber' => $student['mobileno'] ?? '',
                        'gender' => $student['gender'] ?? '',
                        'email' => $student['email'] ?? null,
                        'rollNo' => $student['roll_no'] ?? null,
                        'universityId' => null,
                        'campusId' => $student['school_id'] ?? null,
                        'nepaliName' => $student['first_name_np'] . ' ' . $student['last_name_np'],
                        'doBBS' => $student['dob'] ?? '',
                        'doBAD' => $student['dob_ad'] ?? '0001-01-01T00:00:00',
                        'ethnicity' => $student['ethnicity'] ?? '',
                        'nationality' => $student['nationality'] ?? 'nepali',
                        'disabilityStatus' => $student['disability_status'] ?? 'able',
                        'disabilityType' => $student['disability_type'] ?? null,
                        'citizenshipNo' => $student['citizenship'] ?? null,
                        'citizenIssueDist' => $student['citizenship_issue_dist'] ?? null,
                        'citizenshipFront' => $student['citizenship_front'] ?? null,
                        'citizenshipBack' => $student['citizenship_back'] ?? null,
                        'nidno' => $student['national_id'] ?? null,
                        'nidPic' => $student['nid_pic'] ?? null,
                        'ppSizePhoto' => $student['pp_size_photo'] ?? null,
                        'pProvince' => $student['permanent_province'] ?? '',
                        'pDistrict' => $student['permanent_district'] ?? '',
                        'pLocalLevel' => $student['permanent_local_level'] ?? '',
                        'pWardNo' => $student['permanent_ward_no'] ?? 0,
                        'pBlockNo' => $student['p_block_no'] ?? 0,
                        'pHouseNo' => $student['permanent_house_no'] ?? null,
                        'pLocality' => $student['p_locality'] ?? null,
                        'tProvince' => $student['temporary_province'] ?? '',
                        'tDistrict' => $student['temporary_district'] ?? '',
                        'tLocalLevel' => $student['temporary_local_level'] ?? '',
                        'tWardNo' => $student['temporary_ward_no'] ?? 0,
                        'tBlockNo' => $student['t_block_no'] ?? 0,
                        'tHouseNo' => $student['temporary_house_no'] ?? null,
                        'tLocality' => $student['t_locality'] ?? null,
                        'fatherName' => $student['father_name'] ?? null,
                        'fOccupation' => $student['father_occupation'] ?? null,
                        'fatherPhoneNo' => $student['father_phone'] ?? null,
                        'fatherEmail' => $student['father_email'] ?? null,
                        'motherName' => $student['mother_name'] ?? null,
                        'mOccupation' => $student['mother_occupation'] ?? null,
                        'motherPhoneNo' => $student['mother_phone'] ?? null,
                        'motherEmail' => $student['mother_email'] ?? null,
                        'guardianName' => $student['guardian_name'] ?? '',
                        'guardianOccupation' => $student['guardian_occupation'] ?? null,
                        'guardianPhone' => $student['guardian_phone'] ?? '',
                        'gAddress' => $student['guardian_address'] ?? '',
                        'gEmail' => $student['guardian_email'] ?? null,
                        'type' => $student['type'] ?? null,
                        'semisterOrYearNo' => $upgrade_data['year'] ?? $upgrade_data['semester'] ?? null,
                        'admissionYear' => $student['admission_year'] ?? 0,
                        'complitionYear' => $student['complition_year'] ?? null,
                        'dateOfEnrollment' => $student['admission_date'] ?? date('Y-m-d'),
                        'fiscalYearId' => $student['ugc_fiscal_year_id'] ?? $student['fiscal_year_id'] ?? 0,
                        'institutionId' => $student['institution_id'] ?? null,
                        'isVerified' => $student['is_verified'] ?? true,
                        'programfee' => $student['program_fee'] ?? null,
                        'paidAmount' => $student['paid_amount'] ?? null,
                        'scholarshipAmt' => $student['scholarship_amt'] ?? null,
                        'receiptImage' => $student['receipt_image'] ?? null,
                        'edg' => $student['edg'] ?? false,
                        'semester' => $upgrade_data['semester'] ?? null,
                        'year' => $upgrade_data['year'] ?? null,
                        'programType' => $program['type'] ?? null,
                        'section' => null,
                        'batchNameNepali' => $student['batch_name_nepali'] ?? null,
                        'batchName' => $student['batch_name'] ?? null,
                        'batchId' => $student['ugc_batch_id'] ?? $student['batch_id'] ?? 0,
                        'fiscalYearName' => $student['fiscal_year'] ?? '',
                        'religion' => $student['religion'] ?? 'Hinduism',
                        'universityRegdNo' => $student['university_regd_no'] ?? null,
                        'isRegNoManual' => $student['is_reg_no_manual'] ?? true,
                        'isRollNoManual' => $student['is_roll_no_manual'] ?? true,
                        'medium' => $student['medium'] ?? null,
                        'shift' => $student['shift'] ?? null,
                        'rollNoManual' => $student['roll_no_manual'] ?? null
                    ],
                    'year' => $upgrade_data['year'] ?? '',
                    'semester' => $upgrade_data['semester'] ?? '',
                    'batch' => $batch['name'] ?? date('Y'),
                    'batchNepali' => $batch['name_nepali'] ?? date('Y'),
                    'batchId' => $batch_id,
                    'fiscalYear' => $student['fiscal_year'] ?? ''
                ];
                
                $response_data[] = $response_item;
            } else {
                $errors[] = "Failed to upgrade student with ID: $student_id";
            }
        }
        
        // Return response
        if (!empty($response_data)) {
            $hemis_sync = $this->hemis_client->sync_student_upgrade_batch($request_data);
            $this->response(array(
                'status'   => 'success',
                'data'     => $response_data,
                'hemisSync' => $hemis_sync,
            ), REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Failed to upgrade students',
                'errors' => $errors
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * Get class ID based on year/semester
     * 
     * @param int $program_id
     * @param string $year
     * @param string $semester
     * @return int
     */
    private function _get_class_id_by_year_semester($program_id, $year, $semester) {
        $this->load->model('class_model');
        if (method_exists($this->class_model, 'get_class_by_year_semester')) {
            $class = $this->class_model->get_class_by_year_semester($program_id, $year, $semester);
            if (!empty($class['id'])) {
                return (int) $class['id'];
            }
        }
        return 0;
    }
}