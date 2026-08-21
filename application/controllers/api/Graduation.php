<?php
defined('BASEPATH') or exit('No direct script access allowed');


require_once APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;


/**
 * API Controller for Graduated Students
 */
class Graduation extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        try {
            $this->load->database();
            $this->load->model('setting_model');
            $this->load->model('student_model');
            $this->load->model('session_model');
            $this->load->library('hemis_client');
            $this->load->library('upload');
            $this->load->library('hemis_rest_auth');
            $this->hemis_rest_auth->guard_controller($this);
        } catch (Exception $e) {
            log_message('error', 'Constructor error: ' . $e->getMessage());
            $this->response([
                'status' => false,
                'message' => 'Initialization error: ' . $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function index_get()
    {
        try {
            $graduatedStudents = $this->getGraduatedStudents();
           
            if (!empty($graduatedStudents)) {
                $this->response([
                    'status' => true,
                    'message' => 'Graduated students retrieved successfully',
                    'data' => $graduatedStudents
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'No graduated students found',
                    'data' => []
                ], REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            log_message('error', 'Get graduated students error: ' . $e->getMessage());
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function view_get($id)
    {
        try {
            if (empty($id)) {
                $this->response([
                    'status' => false,
                    'message' => 'Student ID is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }


            $graduatedStudent = $this->getGraduatedStudentById($id);
           
            if (!empty($graduatedStudent)) {
                $this->response([
                    'status' => true,
                    'message' => 'Graduated student details retrieved successfully',
                    'data' => $graduatedStudent
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Graduated student not found',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            log_message('error', 'Get student by ID error: ' . $e->getMessage());
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Generate Graduation Application for existing students
     */
    public function GenerateGraduationApplication_post()
    {
        try {
            // Log the incoming request for debugging
            log_message('info', 'GenerateGraduationApplication_post called');
            log_message('info', 'POST data: ' . json_encode($this->input->post()));
            log_message('info', 'FILES data: ' . json_encode($_FILES));
           
            // Get POST data - handle both form-data and JSON
            $postData = $this->input->post();
            if (empty($postData)) {
                $postData = json_decode(file_get_contents('php://input'), true);
            }
           
            // Basic validation
            if (empty($postData)) {
                log_message('error', 'No POST data received');
                $this->response([
                    'status' => false,
                    'message' => 'No data received',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }


            // Validate required fields
            $studentId = isset($postData['StudentID']) ? trim($postData['StudentID']) : null;
            if (empty($studentId)) {
                $this->response([
                    'status' => false,
                    'message' => 'StudentID is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }


            // Check if student exists
            $existingStudent = $this->checkExistingStudent($studentId);
            if (!$existingStudent) {
                $this->response([
                    'status' => false,
                    'message' => 'Student not found in the system',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }


            // Process file uploads (make it optional)
            $uploadedFiles = [];
            try {
                $uploadedFiles = $this->handleFileUploads();
            } catch (Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
                // Continue without files if upload fails
            }


            // Update student graduation status
            $graduationData = $this->prepareGraduationData($postData, $uploadedFiles);
            $result = $this->updateStudentGraduation($studentId, $graduationData);


            if ($result) {
                $this->load->library('hemis_client');
                $hemisSync = $this->hemis_client->sync_graduation_generate_application((int) $studentId);
                $this->response([
                    'status' => true,
                    'message' => 'Graduation application generated successfully',
                    'data' => ['graduation_id' => $result],
                    'hemisSync' => $hemisSync,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to generate graduation application',
                    'data' => []
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }


        } catch (Exception $e) {
            log_message('error', 'GenerateGraduationApplication_post error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            $this->response([
                'status' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Generate Graduation Application for students not in enrolled list
     */
    public function GenerateGraduationApplicationForStudent_post()
    {
        try {
            log_message('info', 'GenerateGraduationApplicationForStudent_post called');
           
            // Get POST data - handle both form-data and JSON
            $postData = $this->input->post();
            if (empty($postData)) {
                $postData = json_decode(file_get_contents('php://input'), true);
            }
           
            if (empty($postData)) {
                $this->response([
                    'status' => false,
                    'message' => 'No data received',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }


            // Validate required fields
            $requiredFields = ['ApplicantNameEng', 'ProgramMgmtId'];
            foreach ($requiredFields as $field) {
                if (empty(trim($postData[$field] ?? ''))) {
                    $this->response([
                        'status' => false,
                        'message' => $field . ' is required',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }
            }


            // Process file uploads (make it optional)
            $uploadedFiles = [];
            try {
                $uploadedFiles = $this->handleFileUploads();
            } catch (Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
            }


            // Create new student record for graduation
            $studentData = $this->prepareStudentData($postData, $uploadedFiles);
            $result = $this->createGraduatedStudent($studentData);


            if ($result) {
                $hemisSync = $this->hemis_client->sync_graduation_for_student((int) $result, $postData, $uploadedFiles);
                $this->response([
                    'status' => true,
                    'message' => 'Graduation application created successfully for external student',
                    'data' => ['student_id' => $result],
                    'hemisSync' => $hemisSync,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to create graduation application',
                    'data' => []
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }


        } catch (Exception $e) {
            log_message('error', 'GenerateGraduationApplicationForStudent_post error: ' . $e->getMessage());
            $this->response([
                'status' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update graduated student / application — PUT /api/Graduation/{studentId}
     * Body: JSON with same optional fields as GenerateGraduationApplication (StudentRegNo, CampusRolNo, …).
     */
    public function index_put($id = null)
    {
        try {
            if (empty($id)) {
                $this->response([
                    'status' => false,
                    'message' => 'Student ID is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $existing = $this->getGraduatedStudentById($id);
            if (empty($existing)) {
                $this->response([
                    'status' => false,
                    'message' => 'Graduated student not found',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
                return;
            }

            $postData = json_decode($this->input->raw_input_stream, true);
            if (!is_array($postData)) {
                $postData = array();
            }

            $uploadedFiles = array();
            if (!empty($_FILES)) {
                try {
                    $uploadedFiles = $this->handleFileUploads();
                } catch (Exception $e) {
                    log_message('error', 'Graduation PUT upload: ' . $e->getMessage());
                }
            }

            if (empty($postData) && empty($uploadedFiles)) {
                $this->response([
                    'status' => false,
                    'message' => 'No update data or files provided',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $graduationData = $this->prepareGraduationData($postData, $uploadedFiles);
            $result = $this->updateStudentGraduation($id, $graduationData);

            if (!$result) {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to update graduation record',
                    'data' => []
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                return;
            }

            $updated = $this->getGraduatedStudentById($id);
            $hemisSync = $this->hemis_client->sync_graduation_put((int) $id, $updated);

            $this->response([
                'status' => true,
                'message' => 'Graduation record updated successfully',
                'data' => $updated,
                'hemisSync' => $hemisSync,
            ], REST_Controller::HTTP_OK);
        } catch (Exception $e) {
            log_message('error', 'Graduation index_put error: ' . $e->getMessage());
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    private function checkExistingStudent($studentId)
    {
        try {
            $this->db->select('students.id, students.is_active');
            $this->db->from('students');
            $this->db->where('students.id', $studentId);
           
            $query = $this->db->get();
            $student = $query->row_array();
           
            log_message('info', 'Student check result: ' . json_encode($student));
           
            // Return true if student exists and is active
            return $student && $student['is_active'] == 'yes';
        } catch (Exception $e) {
            log_message('error', 'checkExistingStudent error: ' . $e->getMessage());
            return false;
        }
    }


    private function handleFileUploads()
    {
        $uploadedFiles = [];
       
        if (empty($_FILES)) {
            return $uploadedFiles;
        }


        $uploadFields = [
            'UploadSignature' => 'digital_signature',
            'UploadPPSizePhoto' => 'pp_size_photo',
            'UploadReceipt' => 'receipt_image',
            'UploadTranscript' => 'transcript_image',
            'UploadOtherDoc' => 'other_document'
        ];


        // Create upload directory if it doesn't exist
        $uploadPath = './uploads/graduation/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }


        foreach ($uploadFields as $inputName => $dbField) {
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0) {
                try {
                    // Configure upload
                    $config = [
                        'upload_path' => $uploadPath,
                        'allowed_types' => 'gif|jpg|jpeg|png|pdf|doc|docx',
                        'max_size' => 2048, // 2MB
                        'file_name' => time() . '_' . $_FILES[$inputName]['name']
                    ];
                   
                    $this->upload->initialize($config);
                   
                    if ($this->upload->do_upload($inputName)) {
                        $uploadData = $this->upload->data();
                        $uploadedFiles[$dbField] = $uploadData['file_name'];
                        log_message('info', 'File uploaded successfully: ' . $uploadData['file_name']);
                    } else {
                        log_message('error', 'File upload failed for ' . $inputName . ': ' . $this->upload->display_errors());
                    }
                } catch (Exception $e) {
                    log_message('error', 'File upload exception for ' . $inputName . ': ' . $e->getMessage());
                }
            }
        }
       
        return $uploadedFiles;
    }


    private function prepareGraduationData($postData, $uploadedFiles)
    {
        $data = [
            'university_regd_no' => isset($postData['StudentRegNo']) ? trim($postData['StudentRegNo']) : '',
            'roll_no' => isset($postData['CampusRolNo']) ? trim($postData['CampusRolNo']) : '',
            'entrance_score' => isset($postData['GPA']) ? floatval($postData['GPA']) : 0,
            'father_name' => isset($postData['FatherName']) ? trim($postData['FatherName']) : '',
            'mother_name' => isset($postData['MotherName']) ? trim($postData['MotherName']) : '',
            'mobileno' => isset($postData['ContactNo']) ? trim($postData['ContactNo']) : '',
            'note' => isset($postData['Remarks']) ? trim($postData['Remarks']) : '',
            'complition_year' => isset($postData['PassedYear']) ? intval($postData['PassedYear']) : date('Y'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Add uploaded files to data
        foreach ($uploadedFiles as $field => $filename) {
            $data[$field] = $filename;
        }


        return $data;
    }


    private function prepareStudentData($postData, $uploadedFiles)
    {
        // Parse names
        $applicantNameEng = isset($postData['ApplicantNameEng']) ? trim($postData['ApplicantNameEng']) : '';
        $namePartsEng = explode(' ', $applicantNameEng);
       
        $applicantNameNep = isset($postData['ApplicantNameNep']) ? trim($postData['ApplicantNameNep']) : '';
        $namePartsNep = explode(' ', $applicantNameNep);


        $data = [
            'firstname' => isset($namePartsEng[0]) ? $namePartsEng[0] : '',
            'middlename' => (count($namePartsEng) > 2) ? $namePartsEng[1] : '',
            'lastname' => (count($namePartsEng) > 1) ? end($namePartsEng) : '',
            'first_name_np' => isset($namePartsNep[0]) ? $namePartsNep[0] : '',
            'middle_name_np' => (count($namePartsNep) > 2) ? $namePartsNep[1] : '',
            'last_name_np' => (count($namePartsNep) > 1) ? end($namePartsNep) : '',
            'email' => isset($postData['Email']) ? trim($postData['Email']) : '',
            'university_regd_no' => isset($postData['StudentRegNo']) ? trim($postData['StudentRegNo']) : '',
            'roll_no' => isset($postData['CampusRolNo']) ? trim($postData['CampusRolNo']) : '',
            'dob' => isset($postData['DoBEng']) && !empty($postData['DoBEng']) ? $postData['DoBEng'] : null,
            'fiscal_year_id' => isset($postData['FiscalYearID']) ? intval($postData['FiscalYearID']) : 0,
            'gender' => isset($postData['Gender']) ? trim($postData['Gender']) : '',
            'father_name' => isset($postData['FatherName']) ? trim($postData['FatherName']) : '',
            'mother_name' => isset($postData['MotherName']) ? trim($postData['MotherName']) : '',
            'mobileno' => isset($postData['ContactNo']) ? trim($postData['ContactNo']) : '',
            'note' => isset($postData['Remarks']) ? trim($postData['Remarks']) : '',
            'entrance_score' => isset($postData['GPA']) ? floatval($postData['GPA']) : 0,
            'program_id' => isset($postData['ProgramMgmtId']) ? intval($postData['ProgramMgmtId']) : 0,
            'complition_year' => isset($postData['PassedYear']) ? intval($postData['PassedYear']) : date('Y'),
            'admission_year' => isset($postData['EnrolledYear']) ? intval($postData['EnrolledYear']) : date('Y'),
            'is_active' => 'yes',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Add uploaded files to data
        foreach ($uploadedFiles as $field => $filename) {
            $data[$field] = $filename;
        }


        return $data;
    }


    private function updateStudentGraduation($studentId, $data)
    {
        try {
            // Start transaction
            $this->db->trans_start();
           
            // Update student record
            $this->db->where('id', $studentId);
            $updateResult = $this->db->update('students', $data);
           
            if (!$updateResult) {
                log_message('error', 'Failed to update student record. DB Error: ' . $this->db->error()['message']);
                $this->db->trans_rollback();
                return false;
            }


            // Check if student_session record exists
            $this->db->where('student_id', $studentId);
            $sessionQuery = $this->db->get('student_session');
           
            if ($sessionQuery->num_rows() > 0) {
                // Update existing session
                $this->db->where('student_id', $studentId);
                $sessionResult = $this->db->update('student_session', [
                    'is_graduated' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Insert new session
                $sessionResult = $this->db->insert('student_session', [
                    'student_id' => $studentId,
                    'is_graduated' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
           
            if (!$sessionResult) {
                log_message('error', 'Failed to update/insert student_session record. DB Error: ' . $this->db->error()['message']);
                $this->db->trans_rollback();
                return false;
            }
           
            // Complete transaction
            $this->db->trans_complete();
           
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Transaction failed');
                return false;
            }
           
            return $studentId;
        } catch (Exception $e) {
            log_message('error', 'updateStudentGraduation error: ' . $e->getMessage());
            $this->db->trans_rollback();
            return false;
        }
    }


    private function createGraduatedStudent($data)
    {
        try {
            // Start transaction
            $this->db->trans_start();
           
            // Insert student record
            $insertResult = $this->db->insert('students', $data);
            $studentId = $this->db->insert_id();
           
            if (!$insertResult || !$studentId) {
                log_message('error', 'Failed to insert student record. DB Error: ' . $this->db->error()['message']);
                $this->db->trans_rollback();
                return false;
            }
           
            // Insert student_session record
            $sessionResult = $this->db->insert('student_session', [
                'student_id' => $studentId,
                'is_graduated' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
           
            if (!$sessionResult) {
                log_message('error', 'Failed to insert student_session record. DB Error: ' . $this->db->error()['message']);
                $this->db->trans_rollback();
                return false;
            }
           
            // Complete transaction
            $this->db->trans_complete();
           
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Create student transaction failed');
                return false;
            }
           
            return $studentId;
        } catch (Exception $e) {
            log_message('error', 'createGraduatedStudent error: ' . $e->getMessage());
            $this->db->trans_rollback();
            return false;
        }
    }


    private function getGraduatedStudents()
    {
        try {
            $this->db->select('
                students.id as studentID,
                students.firstname,
                students.middlename,
                students.lastname,
                students.first_name_np,
                students.middle_name_np,
                students.last_name_np,
                students.email,
                students.roll_no as campusRolNo,
                students.university_regd_no as studentRegNo,
                students.dob as doBEng,
                students.fiscal_year_id as fiscalYearID,
                students.gender,
                students.father_name as fatherName,
                students.mother_name as motherName,
                students.mobileno as contactNo,
                students.digital_signature as uploadSignature,
                students.pp_size_photo as uploadPPSizePhoto,
                students.receipt_image as uploadReceipt,
                students.note as remarks,
                students.entrance_score as gpa,
                students.program_id as programMgmtId,
                student_session.is_graduated,
                students.complition_year as passedYear,
                students.admission_year as enrolledYear
            ');
            $this->db->from('students');
            $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
            $this->db->where('student_session.is_graduated', 1);
            $this->db->where('students.is_active', 'yes');
           
            $query = $this->db->get();
            $result = $query->result_array();


            $formattedResult = [];
            foreach ($result as $student) {
                $applicantNameEng = trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']);
                $applicantNameNep = trim($student['first_name_np'] . ' ' . $student['middle_name_np'] . ' ' . $student['last_name_np']);


                $programInfo = $this->getProgramInfo($student['programMgmtId']);
                $doBNepali = $this->convertToNepaliDate($student['doBEng']);
               
                $formattedResult[] = [
                    'id' => (int)$student['studentID'],
                    'fiscalYearID' => (int)$student['fiscalYearID'],
                    'studentID' => (int)$student['studentID'],
                    'universityName' => $programInfo['universityName'],
                    'campusId' => $programInfo['campusId'],
                    'campusName' => $programInfo['campusName'],
                    'facultyName' => $programInfo['facultyName'],
                    'levelName' => $programInfo['levelName'],
                    'programType' => $programInfo['programType'],
                    'programName' => $programInfo['programName'],
                    'applicantNameEng' => $applicantNameEng,
                    'applicantNameNep' => $applicantNameNep,
                    'doBNepali' => $doBNepali,
                    'doBEng' => $student['doBEng'],
                    'email' => $student['email'],
                    'universityIssueNo' => '',
                    'studentRegNo' => $student['studentRegNo'],
                    'symbolNoUniversity' => '',
                    'campusRolNo' => $student['campusRolNo'],
                    'enrolledYear' => (int)$student['enrolledYear'],
                    'passedYear' => (int)$student['passedYear'],
                    'division' => '',
                    'gpa' => (float)$student['gpa'],
                    'fatherName' => $student['fatherName'],
                    'motherName' => $student['motherName'],
                    'contactNo' => $student['contactNo'],
                    'uploadSignature' => $student['uploadSignature'],
                    'uploadPPSizePhoto' => $student['uploadPPSizePhoto'],
                    'uploadReceipt' => $student['uploadReceipt'],
                    'uploadTranscript' => '',
                    'uploadOtherDoc' => '',
                    'remarks' => $student['remarks'],
                    'programMgmtId' => (int)$student['programMgmtId'],
                    'gender' => $student['gender']
                ];
            }
           
            return $formattedResult;
        } catch (Exception $e) {
            log_message('error', 'getGraduatedStudents error: ' . $e->getMessage());
            return [];
        }
    }


    private function getGraduatedStudentById($id)
    {
        try {
            $this->db->select('
                students.id as studentID,
                students.firstname,
                students.middlename,
                students.lastname,
                students.first_name_np,
                students.middle_name_np,
                students.last_name_np,
                students.email,
                students.roll_no as campusRolNo,
                students.university_regd_no as studentRegNo,
                students.dob as doBEng,
                students.fiscal_year_id as fiscalYearID,
                students.gender,
                students.father_name as fatherName,
                students.mother_name as motherName,
                students.mobileno as contactNo,
                students.digital_signature as uploadSignature,
                students.pp_size_photo as uploadPPSizePhoto,
                students.receipt_image as uploadReceipt,
                students.note as remarks,
                students.entrance_score as gpa,
                students.program_id as programMgmtId,
                student_session.is_graduated,
                students.complition_year as passedYear,
                students.admission_year as enrolledYear
            ');
            $this->db->from('students');
            $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
            $this->db->where('student_session.is_graduated', 1);
            $this->db->where('students.is_active', 'yes');
            $this->db->where('students.id', $id);
           
            $query = $this->db->get();
            $student = $query->row_array();
           
            if (empty($student)) {
                return [];
            }
           
            $applicantNameEng = trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']);
            $applicantNameNep = trim($student['first_name_np'] . ' ' . $student['middle_name_np'] . ' ' . $student['last_name_np']);


            $programInfo = $this->getProgramInfo($student['programMgmtId']);
            $doBNepali = $this->convertToNepaliDate($student['doBEng']);
           
            return [
                'id' => (int)$student['studentID'],
                'fiscalYearID' => (int)$student['fiscalYearID'],
                'studentID' => (int)$student['studentID'],
                'universityName' => $programInfo['universityName'],
                'campusId' => $programInfo['campusId'],
                'campusName' => $programInfo['campusName'],
                'facultyName' => $programInfo['facultyName'],
                'levelName' => $programInfo['levelName'],
                'programType' => $programInfo['programType'],
                'programName' => $programInfo['programName'],
                'applicantNameEng' => $applicantNameEng,
                'applicantNameNep' => $applicantNameNep,
                'doBNepali' => $doBNepali,
                'doBEng' => $student['doBEng'],
                'email' => $student['email'],
                'universityIssueNo' => '',
                'studentRegNo' => $student['studentRegNo'],
                'symbolNoUniversity' => '',
                'campusRolNo' => $student['campusRolNo'],
                'enrolledYear' => (int)$student['enrolledYear'],
                'passedYear' => (int)$student['passedYear'],
                'division' => '',
                'gpa' => (float)$student['gpa'],
                'fatherName' => $student['fatherName'],
                'motherName' => $student['motherName'],
                'contactNo' => $student['contactNo'],
                'uploadSignature' => $student['uploadSignature'],
                'uploadPPSizePhoto' => $student['uploadPPSizePhoto'],
                'uploadReceipt' => $student['uploadReceipt'],
                'uploadTranscript' => '',
                'uploadOtherDoc' => '',
                'remarks' => $student['remarks'],
                'programMgmtId' => (int)$student['programMgmtId'],
                'gender' => $student['gender']
            ];
        } catch (Exception $e) {
            log_message('error', 'getGraduatedStudentById error: ' . $e->getMessage());
            return [];
        }
    }


    private function getProgramInfo($programId)
    {
        try {
            // Simple query first - adjust table names as needed
            $this->db->select('*');
            $this->db->from('programs');
            $this->db->where('id', $programId);
           
            $query = $this->db->get();
            $result = $query->row_array();
           
            if ($result) {
                return [
                    'programId' => $result['id'],
                    'programName' => isset($result['program_name']) ? $result['program_name'] : '',
                    'programType' => isset($result['program_type']) ? $result['program_type'] : '',
                    'campusId' => 0,
                    'campusName' => '',
                    'facultyName' => '',
                    'levelName' => '',
                    'universityName' => ''
                ];
            }
        } catch (Exception $e) {
            log_message('error', 'getProgramInfo error: ' . $e->getMessage());
        }
       
        return [
            'programId' => 0,
            'programName' => '',
            'programType' => '',
            'campusId' => 0,
            'campusName' => '',
            'facultyName' => '',
            'levelName' => '',
            'universityName' => ''
        ];
    }


    private function convertToNepaliDate($englishDate)
    {
        if (empty($englishDate)) {
            return date('Y-m-d');
        }
       
        // Simple return for now - implement actual conversion if needed
        return $englishDate;
    }
}

