<?php
defined('BASEPATH') OR exit('No direct script access allowed');


require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;


class Student extends REST_Controller {


    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('setting_model');
        $this->load->model('student_model');
        $this->load->library('authorization_token');
        $this->load->library('hemis_rest_auth');
        $this->load->library('hemis_client');
        $this->load->helper('file_upload');
        $this->load->helper('hemis_student');
        $this->load->helper('nepali_calendar');
    }


    /**
     * Create Student API
     * @method: POST
     * @url: api/Student/Create (also api/student/create)
     * @param: multipart/form-data with all student fields
     * @return: JSON response with success/error
     */
    public function create_post() {
        // Validate token
        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();

        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => 'error',
                'message' => 'Unauthorized Access',
                'details' => $is_valid_token['message'] ?? 'Token validation failed'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }


        // Process form data
        $this->load->library('form_validation');
       
        // Set validation rules
        $this->form_validation->set_rules('firstName', 'First Name', 'required');
        $this->form_validation->set_rules('lastName', 'Last Name', 'required');
        $this->form_validation->set_rules('gender', 'Gender', 'required|in_list[male,female,other]');
        $this->form_validation->set_rules('admissionYearId', 'Admission Year ID', 'required|numeric');
        $this->form_validation->set_rules('campusId', 'Campus ID', 'required|numeric');
        $this->form_validation->set_rules('nepaliName', 'Nepali Name', 'required');
        $this->form_validation->set_rules('doBBS', 'Date of Birth (BS)', 'required');
        $this->form_validation->set_rules('doBAD', 'Date of Birth (AD)', 'required');
        $this->form_validation->set_rules('ethnicity', 'Ethnicity', 'required');
        $this->form_validation->set_rules('nationality', 'Nationality', 'required');
        $this->form_validation->set_rules('disabilityStatus', 'Disability Status', 'required');
        // HEMIS showstopper fields — must exist locally or outbound push will always fail precheck.
        $this->form_validation->set_rules('batchId', 'Batch ID', 'required|numeric');
        $this->form_validation->set_rules('pProvince', 'Permanent Province', 'required');
        $this->form_validation->set_rules('pDistrict', 'Permanent District', 'required');
        $this->form_validation->set_rules('pLocalLevel', 'Permanent Local Level', 'required');
        $this->form_validation->set_rules('pWardNo', 'Permanent Ward No', 'required|numeric');
        $this->form_validation->set_rules('fatherName', 'Father Name', 'required');
        $this->form_validation->set_rules('guardianName', 'Guardian Name', 'required');
        $this->form_validation->set_rules('programId', 'Program ID', 'required|numeric');
        $this->form_validation->set_rules('programName', 'Program Name', 'required');
        $this->form_validation->set_rules('levelName', 'Level Name', 'required');
        $this->form_validation->set_rules('facultyName', 'Faculty Name', 'required');
        $this->form_validation->set_rules('complitionYear', 'Completion Year', 'required|numeric|exact_length[4]');
        $this->form_validation->set_rules('dateOfEnrollment', 'Date of Enrollment', 'required');
        $this->form_validation->set_rules('fiscalYearId', 'Fiscal Year ID', 'required|numeric');
        $this->form_validation->set_rules('year', 'Year', 'required');
        $this->form_validation->set_rules('programType', 'Program Type', 'required');
        $this->form_validation->set_rules('religion', 'Religion', 'required');
        // Deterministic address anchoring (UGC combinedCode).
        $this->form_validation->set_rules('pCombinedCode', 'Permanent Combined Code', 'required');


        // Run validation
        if ($this->form_validation->run() == FALSE) {
            return $this->response([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


        try {
            // --- Showstopper guardrails: validate cache-backed lookups ---
            $batchId = (int) $this->input->post('batchId');
            $fyId = (int) $this->input->post('fiscalYearId');
            $ugcProgramId = (int) $this->input->post('programId');

            if (!$this->db->table_exists('ugc_batches') || !$this->db->table_exists('ugc_fiscal_years')) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'UGC metadata not ready',
                    'details' => 'Missing ugc_batches / ugc_fiscal_years tables. Run metadata refresh first.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $batchRow = $this->db->select('id, is_active')->from('ugc_batches')->where('id', $batchId)->limit(1)->get()->row_array();
            if (empty($batchRow)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid batchId',
                    'details' => 'batchId must exist in ugc_batches cache.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if (isset($batchRow['is_active']) && (int) $batchRow['is_active'] === 0) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Inactive batchId',
                    'details' => 'Selected batch is not active in ugc_batches cache.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $fyRow = $this->db->select('id')->from('ugc_fiscal_years')->where('id', $fyId)->limit(1)->get()->row_array();
            if (empty($fyRow)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid fiscalYearId',
                    'details' => 'fiscalYearId must exist in ugc_fiscal_years cache.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            // Program cache is strongly recommended; if present, validate.
            if ($this->db->table_exists('ugc_programs')) {
                $pRow = $this->db->select('program_id')->from('ugc_programs')->where('program_id', $ugcProgramId)->limit(1)->get()->row_array();
                if (empty($pRow)) {
                    return $this->response([
                        'status' => 'error',
                        'message' => 'Invalid programId',
                        'details' => 'programId must exist in ugc_programs cache.',
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }

            // --- combinedCode strategy: resolve province/district/local level names from ugc_addresses ---
            if (!$this->db->table_exists('ugc_addresses')) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'UGC address cache not ready',
                    'details' => 'Missing ugc_addresses table. Run metadata refresh first.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $pCode = trim((string) $this->input->post('pCombinedCode'));
            $pAddr = $this->db->select('combined_code, province, district, local_level')
                ->from('ugc_addresses')
                ->where('combined_code', $pCode)
                ->limit(1)->get()->row_array();
            if (empty($pAddr)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid pCombinedCode',
                    'details' => 'pCombinedCode must exist in ugc_addresses cache.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $isSame = !empty($this->input->post('isSameAsPermanent'));
            $tCode = $isSame ? $pCode : trim((string) $this->input->post('tCombinedCode'));
            if (!$isSame && $tCode === '') {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Validation Error',
                    'errors' => ['tCombinedCode' => 'Temporary Combined Code is required when isSameAsPermanent is false.'],
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $tAddr = $this->db->select('combined_code, province, district, local_level')
                ->from('ugc_addresses')
                ->where('combined_code', $tCode)
                ->limit(1)->get()->row_array();
            if (empty($tAddr)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid tCombinedCode',
                    'details' => 'tCombinedCode must exist in ugc_addresses cache.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            // Normalize dates: store BS + AD separately (no placeholder conversion).
            $dob_bs = trim((string) $this->input->post('doBBS'));
            $dob_ad = trim((string) $this->input->post('doBAD'));
            // Normalize common formats
            if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $dob_bs)) {
                $dob_bs = str_replace('-', '/', $dob_bs);
            }
            if (preg_match('/^\\d{4}\\/\\d{2}\\/\\d{2}$/', $dob_ad)) {
                $dob_ad = str_replace('/', '-', $dob_ad);
            }

            // Also set the casing that Hemis_client precheck expects during immediate sync.
            $_POST['batchId'] = (string) $batchId;
            $_POST['FiscalYearId'] = (string) $fyId;
            $_POST['doBBS'] = $dob_bs;
            $_POST['doBAD'] = $dob_ad;

            // Prepare student data for database
            $student_data = [
                'firstname' => $this->input->post('firstName'),
                'middlename' => $this->input->post('middleName'),
                'lastname' => $this->input->post('lastName'),
                'mobileno' => $this->input->post('phoneNumber'),
                'gender' => $this->input->post('gender'),
                'email' => $this->input->post('email'),
                'admissionYearId' => $this->input->post('admissionYearId'),  // Store original ID for reference
                'admission_year' => date('Y'), // Assuming current year if not convertible
                'has_scolorship' => $this->input->post('hasScholarship') ? 1 : 0,
                'is_same_as_permanent' => $this->input->post('isSameAsPermanent') ? 1 : 0,
                'roll_no' => $this->input->post('rollNo'),
                'school_id' => $this->input->post('campusId'),
                'first_name_np' => $this->extract_nepali_name_part($this->input->post('nepaliName'), 'first'),
                'middle_name_np' => $this->extract_nepali_name_part($this->input->post('nepaliName'), 'middle'),
                'last_name_np' => $this->extract_nepali_name_part($this->input->post('nepaliName'), 'last'),
                // HEMIS dual-date contract
                'dob_bs' => $dob_bs,
                'dob_ad' => $dob_ad,
                'ethnicity' => $this->input->post('ethnicity'),
                'nationality' => $this->input->post('nationality'),
                'disability_status' => $this->input->post('disabilityStatus'),
                'disability_type' => $this->input->post('disabilityType'),
                'citizenship' => $this->input->post('citizenshipNo'),
                'citizenship_issue_dist' => $this->input->post('citizenIssueDist'),
                'permanent_province' => $this->input->post('pProvince'),
                'permanent_district' => $this->input->post('pDistrict'),
                'permanent_local_level' => $this->input->post('pLocalLevel'),
                'permanent_ward_no' => $this->input->post('pWardNo'),
                'ugc_p_combined_code' => $pCode,
                'ugc_p_province' => $pAddr['province'] ?? null,
                'ugc_p_district' => $pAddr['district'] ?? null,
                'ugc_p_local_level' => $pAddr['local_level'] ?? null,
                'p_block_no' => $this->input->post('pBlockNo'),
                'permanent_house_no' => $this->input->post('pHouseNo'),
                'p_locality' => $this->input->post('pLocality'),
                'temporary_province' => $this->input->post('isSameAsPermanent') ? $this->input->post('pProvince') : $this->input->post('tProvince'),
                'temporary_district' => $this->input->post('isSameAsPermanent') ? $this->input->post('pDistrict') : $this->input->post('tDistrict'),
                'temporary_local_level' => $this->input->post('isSameAsPermanent') ? $this->input->post('pLocalLevel') : $this->input->post('tLocalLevel'),
                'temporary_ward_no' => $this->input->post('isSameAsPermanent') ? $this->input->post('pWardNo') : $this->input->post('tWardNo'),
                'temporary_house_no' => $this->input->post('isSameAsPermanent') ? $this->input->post('pHouseNo') : $this->input->post('tHouseNo'),
                'ugc_t_combined_code' => $tCode,
                'ugc_t_province' => $tAddr['province'] ?? null,
                'ugc_t_district' => $tAddr['district'] ?? null,
                'ugc_t_local_level' => $tAddr['local_level'] ?? null,
                't_block_no' => $this->input->post('isSameAsPermanent') ? $this->input->post('pBlockNo') : $this->input->post('tBlockNo'),
                't_locality' => $this->input->post('isSameAsPermanent') ? $this->input->post('pLocality') : $this->input->post('tLocality'),
                'father_name' => $this->input->post('fatherName'),
                'father_occupation' => $this->input->post('fOccupation'),
                'father_phone' => $this->input->post('fatherPhoneNo'),
                'father_email' => $this->input->post('fatherEmail'),
                'mother_name' => $this->input->post('motherName'),
                'mother_occupation' => $this->input->post('mOccupation'),
                'mother_phone' => $this->input->post('motherPhoneNo'),
                'mother_email' => $this->input->post('motherEmail'),
                'guardian_name' => $this->input->post('guardianName'),
                'guardian_occupation' => $this->input->post('guardianOccupation'),
                'guardian_phone' => $this->input->post('guardianPhone'),
                'guardian_address' => $this->input->post('gAddress'),
                'guardian_email' => $this->input->post('gEmail'),
                'program_id' => $this->input->post('programId'),
                'ugc_program_id' => $ugcProgramId,
                'complition_year' => $this->input->post('complitionYear'),
                'admission_date' => $this->input->post('dateOfEnrollment'),
                'fiscal_year_id' => $this->input->post('fiscalYearId'),
                'ugc_fiscal_year_id' => $fyId,
                'batch_id' => $batchId,
                'ugc_batch_id' => $batchId,
                'edg' => $this->input->post('edg') ? 1 : 0,
                'semister_or_year_no' => $this->input->post('year'),
                'type' => $this->input->post('type'),
                'is_mukta_kamaiya' => $this->input->post('isMuktaKamaiya') ? 1 : 0,
                'is_from_martyr_family' => $this->input->post('isFromMartyrFamily') ? 1 : 0,
                'entrance_score' => $this->input->post('entranceScore'),
                'entrance_symbol' => $this->input->post('entranceSymbol'),
                'reg_no_manual' => $this->input->post('regNoManual'),
                'religion' => $this->input->post('religion'),
                'roll_no_manual' => $this->input->post('rollNoManual'),
                'university_regd_no' => $this->input->post('universityRegdNo'),
                'medium' => $this->input->post('medium'),
                'shift' => $this->input->post('shift'),
                'is_active' => 'yes',
                'is_verified' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d'),
                'guardian_is' => $this->input->post('guardianIs') ? $this->input->post('guardianIs') : 'father',
                'academic_program_duration' => $this->input->post('academicProgramDuration')
                    ? $this->input->post('academicProgramDuration') : '4',
            ];

            if ($this->input->post('parentId') !== null && $this->input->post('parentId') !== '') {
                $student_data['parent_id'] = (int) $this->input->post('parentId');
            }
            if ($this->input->post('classId') !== null && $this->input->post('classId') !== '') {
                $student_data['class_id'] = (int) $this->input->post('classId');
            }
            if ($this->input->post('sectionId') !== null && $this->input->post('sectionId') !== '') {
                $student_data['section_id'] = (int) $this->input->post('sectionId');
            }

            // Handle file uploads
            $upload_config = [
                'upload_path' => './uploads/students/',
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size' => 2048, // 2MB
                'encrypt_name' => TRUE
            ];


            $this->load->library('upload', $upload_config);


            // Process citizenship front
            if (!empty($_FILES['citizenshipFront']['name'])) {
                $_FILES['file']['name'] = $_FILES['citizenshipFront']['name'];
                $_FILES['file']['type'] = $_FILES['citizenshipFront']['type'];
                $_FILES['file']['tmp_name'] = $_FILES['citizenshipFront']['tmp_name'];
                $_FILES['file']['error'] = $_FILES['citizenshipFront']['error'];
                $_FILES['file']['size'] = $_FILES['citizenshipFront']['size'];
               
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $student_data['citizenship_front'] = 'uploads/students/' . $upload_data['file_name'];
                }
            }


            // Process citizenship back
            if (!empty($_FILES['citizenshipBack']['name'])) {
                $_FILES['file']['name'] = $_FILES['citizenshipBack']['name'];
                $_FILES['file']['type'] = $_FILES['citizenshipBack']['type'];
                $_FILES['file']['tmp_name'] = $_FILES['citizenshipBack']['tmp_name'];
                $_FILES['file']['error'] = $_FILES['citizenshipBack']['error'];
                $_FILES['file']['size'] = $_FILES['citizenshipBack']['size'];
               
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $student_data['citizenship_back'] = 'uploads/students/' . $upload_data['file_name'];
                }
            }


            // Process NID pic
            if (!empty($_FILES['nidPic']['name'])) {
                $_FILES['file']['name'] = $_FILES['nidPic']['name'];
                $_FILES['file']['type'] = $_FILES['nidPic']['type'];
                $_FILES['file']['tmp_name'] = $_FILES['nidPic']['tmp_name'];
                $_FILES['file']['error'] = $_FILES['nidPic']['error'];
                $_FILES['file']['size'] = $_FILES['nidPic']['size'];
               
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $student_data['nid_pic'] = 'uploads/students/' . $upload_data['file_name'];
                }
            }


            // Process PP size photo
            if (!empty($_FILES['ppSizePhoto']['name'])) {
                $_FILES['file']['name'] = $_FILES['ppSizePhoto']['name'];
                $_FILES['file']['type'] = $_FILES['ppSizePhoto']['type'];
                $_FILES['file']['tmp_name'] = $_FILES['ppSizePhoto']['tmp_name'];
                $_FILES['file']['error'] = $_FILES['ppSizePhoto']['error'];
                $_FILES['file']['size'] = $_FILES['ppSizePhoto']['size'];
               
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $student_data['pp_size_photo'] = 'uploads/students/' . $upload_data['file_name'];
                }
            }


            // Process digital signature
            if (!empty($_FILES['digitalSignature']['name'])) {
                $_FILES['file']['name'] = $_FILES['digitalSignature']['name'];
                $_FILES['file']['type'] = $_FILES['digitalSignature']['type'];
                $_FILES['file']['tmp_name'] = $_FILES['digitalSignature']['tmp_name'];
                $_FILES['file']['error'] = $_FILES['digitalSignature']['error'];
                $_FILES['file']['size'] = $_FILES['digitalSignature']['size'];
               
                $this->upload->initialize($upload_config);
                if ($this->upload->do_upload('file')) {
                    $upload_data = $this->upload->data();
                    $student_data['digital_signature'] = 'uploads/students/' . $upload_data['file_name'];
                }
            }


            // Create student record
            $student_id = $this->student_model->create_student($student_data);


            if ($student_id) {
                $this->load->helper('hemis_sync_post_from_db');
                $freshRow = $this->student_model->get_student_by_id((int) $student_id);
                $hemisMultipart = is_array($freshRow) ? hemis_student_db_row_to_create_post($freshRow) : null;
                $hemis_sync = $this->hemis_client->sync_student_after_local_create((int) $student_id, $hemisMultipart);

                // Generate response data
                $response_data = [
                    'id' => $student_id,
                    'firstName' => $student_data['firstname'],
                    'middleName' => $student_data['middlename'],
                    'lastName' => $student_data['lastname'],
                    'phoneNumber' => $student_data['mobileno'],
                    'gender' => $student_data['gender'],
                    'email' => $student_data['email'],
                    'admissionYearId' => $this->input->post('admissionYearId'),
                    'hasScholarship' => (bool)$student_data['has_scolorship'],
                    'isSameAsPermanent' => (bool)$student_data['is_same_as_permanent'],
                    'rollNo' => $student_data['roll_no'],
                    'campusId' => $student_data['school_id'],
                    'nepaliName' => $this->input->post('nepaliName'),
                    'doBBS' => $this->input->post('doBBS'),
                    'doBAD' => $this->input->post('doBAD'),
                    'ethnicity' => $student_data['ethnicity'],
                    'nationality' => $student_data['nationality'],
                    'disabilityStatus' => $student_data['disability_status'],
                    'disabilityType' => $student_data['disability_type'],
                    'citizenshipNo' => $student_data['citizenship'],
                    'citizenIssueDist' => $student_data['citizenship_issue_dist'],
                    'citizenshipFront' => $student_data['citizenship_front'] ?? null,
                    'citizenshipBack' => $student_data['citizenship_back'] ?? null,
                    'nidno' => $this->input->post('nidno'),
                    'nidPic' => $student_data['nid_pic'] ?? null,
                    'ppSizePhoto' => $student_data['pp_size_photo'] ?? null,
                    'pProvince' => $student_data['permanent_province'],
                    'pDistrict' => $student_data['permanent_district'],
                    'pLocalLevel' => $student_data['permanent_local_level'],
                    'pWardNo' => $student_data['permanent_ward_no'],
                    'pBlockNo' => $student_data['p_block_no'],
                    'pHouseNo' => $student_data['permanent_house_no'],
                    'pLocality' => $student_data['p_locality'],
                    'tProvince' => $student_data['temporary_province'],
                    'tDistrict' => $student_data['temporary_district'],
                    'tLocalLevel' => $student_data['temporary_local_level'],
                    'tWardNo' => $student_data['temporary_ward_no'],
                    'tBlockNo' => $student_data['t_block_no'],
                    'tHouseNo' => $student_data['temporary_house_no'],
                    'tLocality' => $student_data['t_locality'],
                    'fatherName' => $student_data['father_name'],
                    'fOccupation' => $student_data['father_occupation'],
                    'fatherPhoneNo' => $student_data['father_phone'],
                    'fatherEmail' => $student_data['father_email'],
                    'motherName' => $student_data['mother_name'],
                    'mOccupation' => $student_data['mother_occupation'],
                    'motherPhoneNo' => $student_data['mother_phone'],
                    'motherEmail' => $student_data['mother_email'],
                    'guardianName' => $student_data['guardian_name'],
                    'guardianOccupation' => $student_data['guardian_occupation'],
                    'guardianPhone' => $student_data['guardian_phone'],
                    'gAddress' => $student_data['guardian_address'],
                    'gEmail' => $student_data['guardian_email'],
                    'programId' => $student_data['program_id'],
                    'programName' => $this->input->post('programName'),
                    'levelName' => $this->input->post('levelName'),
                    'facultyName' => $this->input->post('facultyName'),
                    'complitionYear' => $student_data['complition_year'],
                    'dateOfEnrollment' => $student_data['admission_date'],
                    'fiscalYearId' => $student_data['fiscal_year_id'],
                    'edg' => (bool)$student_data['edg'],
                    'semester' => $this->input->post('semester'),
                    'year' => $student_data['semister_or_year_no'],
                    'programType' => $this->input->post('programType'),
                    'section' => $this->input->post('section'),
                    'type' => $student_data['type'],
                    'isMuktaKamaiya' => (bool)$student_data['is_mukta_kamaiya'],
                    'isFromMartyrFamily' => (bool)$student_data['is_from_martyr_family'],
                    'entranceScore' => $student_data['entrance_score'],
                    'entranceSymbol' => $student_data['entrance_symbol'],
                    'digitalSignature' => $student_data['digital_signature'] ?? null,
                    'regNoManual' => $student_data['reg_no_manual'],
                    'religion' => $student_data['religion'],
                    'rollNoManual' => $student_data['roll_no_manual'],
                    'universityRegdNo' => $student_data['university_regd_no'],
                    'medium' => $student_data['medium'],
                    'shift' => $student_data['shift'],
                    'hemisStudentId' => !empty($hemis_sync['hemis_student_id']) ? (int) $hemis_sync['hemis_student_id'] : null,
                    'hemisSync' => $hemis_sync,
                ];


                return $this->response([
                    'status' => 'success',
                    'message' => 'Student created successfully',
                    'data' => $response_data
                ], REST_Controller::HTTP_CREATED);
            } else {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Failed to create student record'
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            return $this->response([
                'status' => 'error',
                'message' => 'An error occurred while creating student',
                'error' => $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Helper function to extract parts from Nepali name
     */
    private function extract_nepali_name_part($full_name, $part = 'first') {
        if (empty($full_name)) {
            return '';
        }
       
        $parts = explode(' ', $full_name);
       
        if ($part === 'first' && !empty($parts)) {
            return $parts[0];
        } elseif ($part === 'middle' && count($parts) > 2) {
            $middle_parts = array_slice($parts, 1, count($parts) - 2);
            return implode(' ', $middle_parts);
        } elseif ($part === 'last' && count($parts) > 1) {
            return end($parts);
        }
       
        return '';
    }
   
    /**
     * Helper function to convert Nepali date to AD
     * This is a placeholder - you'll need to implement proper date conversion
     */
    private function convert_nepali_date_to_ad($nepali_date) {
        // This is a placeholder - you would need a proper Nepali to AD date converter
        // For now, just return the input doBAD from the request or today's date
        $ad_date = $this->input->post('doBAD');
        if (!$ad_date) {
            $ad_date = date('Y-m-d');
        }
        return $ad_date;
    }

    /**
     * HEMIS §5.8.4 — GET /api/Student/{studentId} (campus: local primary key).
     * Route: api/Student/{id} GET → view_get
     */
    public function view_get($id = null)
    {
        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();
        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => 'error',
                'message' => 'Unauthorized Access',
                'details' => $is_valid_token['message'] ?? 'Token validation failed',
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        if ($id === null || $id === '' || !ctype_digit((string) $id)) {
            return $this->response([
                'status' => 'error',
                'message' => 'Student ID is required',
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->db->where('id', (int) $id);
        $student = $this->db->get('students')->row();
        if (empty($student)) {
            return $this->response([
                'status' => 'error',
                'message' => 'Student not found',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $item = $this->build_hemis_student_list_item($student);
        if ($item === null) {
            return $this->response([
                'status' => 'error',
                'message' => 'Unable to format student',
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $row = hemis_student_row_to_array($student);
        $hid = hemis_student_field($row, 'hemis_student_id');
        $item['hemisStudentId'] = ($hid !== null && $hid !== '') ? (int) $hid : null;

        return $this->response([
            'status' => 'Success',
            'data'   => $item,
        ], REST_Controller::HTTP_OK);
    }

    /**
     * Shared HEMIS-shaped fields for GetAllStudent and Get-by-id (PDF §5.8.3 / §5.8.4 style).
     *
     * @param object $student Row from students
     * @return array|null
     */
    private function build_hemis_student_list_item($student)
    {
        if (empty($student)) {
            return null;
        }
        $row = hemis_student_row_to_array($student);

        return [
            'id'               => (int) hemis_student_field($row, 'id'),
            'firstName'        => hemis_student_field($row, 'firstname'),
            'lastName'         => hemis_student_field($row, 'lastname'),
            'phoneNumber'      => hemis_student_field($row, 'mobileno'),
            'gender'           => hemis_student_field($row, 'gender'),
            'email'            => hemis_student_field($row, 'email'),
            'admissionYearId'  => hemis_resolve_admission_year_id($row),
            'rollNo'           => hemis_student_field($row, 'roll_no'),
            'collegeName'      => $this->get_college_name(hemis_student_field($row, 'school_id')),
            'collegeId'        => hemis_student_field($row, 'school_id'),
            'nepaliName'       => trim(
                (hemis_student_field($row, 'first_name_np') ?: '') . ' '
                . (hemis_student_field($row, 'middle_name_np') ? hemis_student_field($row, 'middle_name_np') . ' ' : '')
                . (hemis_student_field($row, 'last_name_np') ?: '')
            ),
            // HEMIS contract: doBBS must be BS yyyy/mm/dd. Prefer stored dob_bs; fallback to derived.
            'doBBS'            => (hemis_student_field($row, 'dob_bs') ?: convert_ad_to_bs(hemis_student_field($row, 'dob_ad') ?: hemis_student_field($row, 'dob'))),
            'ethnicity'        => hemis_resolve_ethnicity_value($row),
            'nationality'      => hemis_resolve_nationality_value($row),
            'pProvince'        => hemis_student_field($row, 'permanent_province'),
            'pDistrict'        => hemis_student_field($row, 'permanent_district'),
            'tProvince'        => hemis_student_field($row, 'temporary_province'),
            'tDistrict'        => hemis_student_field($row, 'temporary_district'),
            'guardianName'     => hemis_student_field($row, 'guardian_name'),
            'guardianPhone'    => hemis_student_field($row, 'guardian_phone'),
            'programId'        => (int) hemis_student_field($row, 'program_id'),
            'programName'      => $this->get_program_name(hemis_student_field($row, 'program_id')),
            'levelName'        => $this->get_level_name(hemis_student_field($row, 'class_id')),
            'facultyName'      => $this->get_faculty_name(hemis_student_field($row, 'program_id')),
            'fiscalYear'       => $this->get_fiscal_year(),
            'admissionYear'    => hemis_student_field($row, 'admission_year'),
            'dateOfEnrollment' => hemis_student_field($row, 'admission_date'),
            'isVerified'       => (hemis_student_field($row, 'is_active') == 'yes'),
            'semester'         => $this->get_semester(hemis_student_field($row, 'section_id')),
            'isActive'         => (hemis_student_field($row, 'is_active') == 'yes'),
        ];
    }

   
    public function GetAllStudent_get() {
        // Debug: Log the headers and request info
        $headers = $this->input->request_headers();
        log_message('debug', 'API Headers: ' . json_encode($headers));
        log_message('debug', 'Request URI: ' . $this->uri->uri_string());
        log_message('debug', 'Server Environment: ' . ENVIRONMENT);


        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();
        log_message('debug', 'Token validation result: ' . json_encode($is_valid_token));

        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => 'error',
                'message' => 'Unauthorized Access',
                'details' => $is_valid_token['message'] ?? 'Token validation failed'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }


        // Debug: Log the query parameters
        $is_verified = $this->get('isVerified');
        $uni_id = $this->get('UniId');
        log_message('debug', 'Query params: isVerified=' . $is_verified . ', UniId=' . $uni_id);


        // Build filter array
        $filters = [];
       
        // Add filters if parameters are provided
        if ($is_verified !== null) {
            $filters['is_active'] = ($is_verified === 'true' || $is_verified === '1') ? 'yes' : 'no';
        }
       
        if ($uni_id !== null) {
            $filters['school_id'] = $uni_id;
        }


        try {
            // Test database connection
            $test_query = $this->db->query("SELECT COUNT(*) as count FROM students");
            $test_result = $test_query->row();
            log_message('debug', 'Total students in database: ' . $test_result->count);
           
            // Get the query that would be executed by the model
            $this->db->reset_query();
            $this->db->from('students');
            foreach ($filters as $key => $value) {
                $this->db->where($key, $value);
            }
            $query_string = $this->db->get_compiled_select();
            log_message('debug', 'Query to be executed: ' . $query_string);
           
            // Get students data from model
            $students = $this->student_model->get_all_students($filters);
           
            // Debug: Log number of students found
            log_message('debug', 'Students found: ' . count($students));
           
            // Debug: Log the first student data to check structure
            if (!empty($students)) {
                log_message('debug', 'First student data: ' . json_encode($students[0]));
            }


            // Format response data (shared builder with view_get)
            $formatted_students = [];
            foreach ($students as $student) {
                $item = $this->build_hemis_student_list_item($student);
                if ($item !== null) {
                    $formatted_students[] = $item;
                }
            }


            // Return response
            return $this->response([
                'status' => 'Success',
                'data' => $formatted_students,
                'debug' => [
                    'environment' => ENVIRONMENT,
                    'filters_applied' => $filters,
                    'record_count' => count($students)
                ]
            ], REST_Controller::HTTP_OK);
           
        } catch (Exception $e) {
            log_message('error', 'Error in GetAllStudent_get: ' . $e->getMessage());
            log_message('error', 'Error trace: ' . $e->getTraceAsString());
            return $this->response([
                'status' => 'error',
                'message' => 'An error occurred while retrieving student data',
                'details' => $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function Update_patch($id) {
        $is_valid_token = $this->hemis_rest_auth->validate_campus_bearer_array();

        if (!$is_valid_token['status']) {
            return $this->response([
                'status' => 'error',
                'message' => 'Unauthorized Access',
                'details' => $is_valid_token['message'] ?? 'Token validation failed'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

        // Check if student exists
        $existing_student = $this->student_model->get_student_by_id($id);
        if (!$existing_student) {
            return $this->response([
                'status' => 'error',
                'message' => 'Student not found'
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if (!empty($this->request->body) && is_array($this->request->body)) {
            foreach ($this->request->body as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        $this->load->library('form_validation');
        if (!empty($this->request->body) && is_array($this->request->body)) {
            $this->form_validation->set_data($this->request->body);
        }

        // Process form data
        
        // Set validation rules for required fields only
        $this->form_validation->set_rules('firstName', 'First Name', 'required');
        $this->form_validation->set_rules('lastName', 'Last Name', 'required');
        $this->form_validation->set_rules('gender', 'Gender', 'required|in_list[male,female,other]');
        $this->form_validation->set_rules('admissionYearId', 'Admission Year ID', 'required|numeric');
        $this->form_validation->set_rules('campusId', 'Campus ID', 'required|numeric');
        $this->form_validation->set_rules('nepaliName', 'Nepali Name', 'required');
        $this->form_validation->set_rules('doBBS', 'Date of Birth (BS)', 'required');
        $this->form_validation->set_rules('doBAD', 'Date of Birth (AD)', 'required');
        $this->form_validation->set_rules('ethnicity', 'Ethnicity', 'required');
        $this->form_validation->set_rules('nationality', 'Nationality', 'required');
        $this->form_validation->set_rules('disabilityStatus', 'Disability Status', 'required');
        $this->form_validation->set_rules('batchId', 'Batch ID', 'required|numeric');
        $this->form_validation->set_rules('pProvince', 'Permanent Province', 'required');
        $this->form_validation->set_rules('pDistrict', 'Permanent District', 'required');
        $this->form_validation->set_rules('pLocalLevel', 'Permanent Local Level', 'required');
        $this->form_validation->set_rules('pWardNo', 'Permanent Ward No', 'required|numeric');
        $this->form_validation->set_rules('pCombinedCode', 'Permanent Combined Code', 'required');
        $this->form_validation->set_rules('fatherName', 'Father Name', 'required');
        $this->form_validation->set_rules('guardianName', 'Guardian Name', 'required');
        $this->form_validation->set_rules('programId', 'Program ID', 'required|numeric');
        $this->form_validation->set_rules('programName', 'Program Name', 'required');
        $this->form_validation->set_rules('levelName', 'Level Name', 'required');
        $this->form_validation->set_rules('facultyName', 'Faculty Name', 'required');
        $this->form_validation->set_rules('complitionYear', 'Completion Year', 'required|numeric|exact_length[4]');
        $this->form_validation->set_rules('dateOfEnrollment', 'Date of Enrollment', 'required');
        $this->form_validation->set_rules('fiscalYearId', 'Fiscal Year ID', 'required|numeric');
        $this->form_validation->set_rules('year', 'Year', 'required');
        $this->form_validation->set_rules('religion', 'Religion', 'required');
    
        // Run validation
        if ($this->form_validation->run() == FALSE) {
            return $this->response([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $this->form_validation->error_array()
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    
        try {
            $batchId = (int) $this->input->post('batchId');
            $fyId = (int) $this->input->post('fiscalYearId');
            $ugcProgramId = (int) $this->input->post('programId');

            // Validate cache-backed batch/fiscal-year/program for update too.
            if (!$this->db->table_exists('ugc_batches') || !$this->db->table_exists('ugc_fiscal_years') || !$this->db->table_exists('ugc_addresses')) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'UGC metadata not ready',
                    'details' => 'Missing ugc_* cache tables. Run metadata refresh first.',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $batchRow = $this->db->select('id, is_active')->from('ugc_batches')->where('id', $batchId)->limit(1)->get()->row_array();
            if (empty($batchRow) || (isset($batchRow['is_active']) && (int) $batchRow['is_active'] === 0)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid batchId',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $fyRow = $this->db->select('id')->from('ugc_fiscal_years')->where('id', $fyId)->limit(1)->get()->row_array();
            if (empty($fyRow)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid fiscalYearId',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            if ($this->db->table_exists('ugc_programs')) {
                $pRow = $this->db->select('program_id')->from('ugc_programs')->where('program_id', $ugcProgramId)->limit(1)->get()->row_array();
                if (empty($pRow)) {
                    return $this->response([
                        'status' => 'error',
                        'message' => 'Invalid programId',
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }

            $pCode = trim((string) $this->input->post('pCombinedCode'));
            $pAddr = $this->db->select('combined_code, province, district, local_level')
                ->from('ugc_addresses')->where('combined_code', $pCode)->limit(1)->get()->row_array();
            if (empty($pAddr)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid pCombinedCode',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $isSame = !empty($this->input->post('isSameAsPermanent'));
            $tCode = $isSame ? $pCode : trim((string) $this->input->post('tCombinedCode'));
            if (!$isSame && $tCode === '') {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Validation Error',
                    'errors' => ['tCombinedCode' => 'Temporary Combined Code is required when isSameAsPermanent is false.'],
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            $tAddr = $this->db->select('combined_code, province, district, local_level')
                ->from('ugc_addresses')->where('combined_code', $tCode)->limit(1)->get()->row_array();
            if (empty($tAddr)) {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Invalid tCombinedCode',
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $dob_bs = trim((string) $this->input->post('doBBS'));
            $dob_ad = trim((string) $this->input->post('doBAD'));
            if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $dob_bs)) {
                $dob_bs = str_replace('-', '/', $dob_bs);
            }
            if (preg_match('/^\\d{4}\\/\\d{2}\\/\\d{2}$/', $dob_ad)) {
                $dob_ad = str_replace('/', '-', $dob_ad);
            }
            $_POST['batchId'] = (string) $batchId;
            $_POST['FiscalYearId'] = (string) $fyId;
            $_POST['doBBS'] = $dob_bs;
            $_POST['doBAD'] = $dob_ad;

            // Prepare student data for database update
            $student_data = [
                'firstname' => $this->input->post('firstName'),
                'middlename' => $this->input->post('middleName'),
                'lastname' => $this->input->post('lastName'),
                'mobileno' => $this->input->post('phoneNumber'),
                'gender' => $this->input->post('gender'),
                'email' => $this->input->post('email'),
                'admissionYearId' => $this->input->post('admissionYearId'),
                // Don't update admission_year to current year, use the value from the request or keep the existing value
                'admission_year' => $this->input->post('admissionYear') ?? $existing_student['admission_year'],
                'has_scolorship' => $this->input->post('hasScholarship') ? 1 : 0,
                'is_same_as_permanent' => $this->input->post('isSameAsPermanent') ? 1 : 0,
                'roll_no' => $this->input->post('rollNo'),
                'school_id' => $this->input->post('campusId'),
                'first_name_np' => $this->extract_nepali_name_part($this->input->post('nepaliName'), 'first'),
                'middle_name_np' => $this->extract_nepali_name_part($this->input->post('nepaliName'), 'middle'),
                'last_name_np' => $this->extract_nepali_name_part($this->input->post('nepaliName'), 'last'),
                'dob_bs' => $dob_bs,
                'dob_ad' => $dob_ad,
                'ethnicity' => $this->input->post('ethnicity'),
                'nationality' => $this->input->post('nationality'),
                'disability_status' => $this->input->post('disabilityStatus'),
                'disability_type' => $this->input->post('disabilityType'),
                'citizenship' => $this->input->post('citizenshipNo'),
                'citizenship_issue_dist' => $this->input->post('citizenIssueDist'),
                'permanent_province' => $this->input->post('pProvince'),
                'permanent_district' => $this->input->post('pDistrict'),
                'permanent_local_level' => $this->input->post('pLocalLevel'),
                'permanent_ward_no' => $this->input->post('pWardNo'),
                'ugc_p_combined_code' => $pCode,
                'ugc_p_province' => $pAddr['province'] ?? null,
                'ugc_p_district' => $pAddr['district'] ?? null,
                'ugc_p_local_level' => $pAddr['local_level'] ?? null,
                'p_block_no' => $this->input->post('pBlockNo'),
                'permanent_house_no' => $this->input->post('pHouseNo'),
                'p_locality' => $this->input->post('pLocality'),
                'father_name' => $this->input->post('fatherName'),
                'father_occupation' => $this->input->post('fOccupation'),
                'father_phone' => $this->input->post('fatherPhoneNo'),
                'father_email' => $this->input->post('fatherEmail'),
                'mother_name' => $this->input->post('motherName'),
                'mother_occupation' => $this->input->post('mOccupation'),
                'mother_phone' => $this->input->post('motherPhoneNo'),
                'mother_email' => $this->input->post('motherEmail'),
                'guardian_name' => $this->input->post('guardianName'),
                'guardian_occupation' => $this->input->post('guardianOccupation'),
                'guardian_phone' => $this->input->post('guardianPhone'),
                'guardian_address' => $this->input->post('gAddress'),
                'guardian_email' => $this->input->post('gEmail'),
                'program_id' => $this->input->post('programId'),
                'ugc_program_id' => $ugcProgramId,
                'complition_year' => $this->input->post('complitionYear'),
                'admission_date' => $this->input->post('dateOfEnrollment'),
                'fiscal_year_id' => $this->input->post('fiscalYearId'),
                'ugc_fiscal_year_id' => $fyId,
                'batch_id' => $batchId,
                'ugc_batch_id' => $batchId,
                'edg' => $this->input->post('edg') ? 1 : 0,
                'semister_or_year_no' => $this->input->post('year'),
                'type' => $this->input->post('type'),
                'is_mukta_kamaiya' => $this->input->post('isMuktaKamaiya') ? 1 : 0,
                'is_from_martyr_family' => $this->input->post('isFromMartyrFamily') ? 1 : 0,
                'entrance_score' => $this->input->post('entranceScore'),
                'entrance_symbol' => $this->input->post('entranceSymbol'),
                'reg_no_manual' => $this->input->post('regNoManual'),
                'religion' => $this->input->post('religion'),
                'roll_no_manual' => $this->input->post('rollNoManual'),
                'university_regd_no' => $this->input->post('universityRegdNo'),
                'medium' => $this->input->post('medium'),
                'shift' => $this->input->post('shift'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
    
            // Handle temporary address fields based on isSameAsPermanent flag
            if ($this->input->post('isSameAsPermanent')) {
                $student_data['temporary_province'] = $this->input->post('pProvince');
                $student_data['temporary_district'] = $this->input->post('pDistrict');
                $student_data['temporary_local_level'] = $this->input->post('pLocalLevel');
                $student_data['temporary_ward_no'] = $this->input->post('pWardNo');
                $student_data['temporary_house_no'] = $this->input->post('pHouseNo');
                $student_data['t_block_no'] = $this->input->post('pBlockNo');
                $student_data['t_locality'] = $this->input->post('pLocality');
                $student_data['ugc_t_combined_code'] = $pCode;
                $student_data['ugc_t_province'] = $pAddr['province'] ?? null;
                $student_data['ugc_t_district'] = $pAddr['district'] ?? null;
                $student_data['ugc_t_local_level'] = $pAddr['local_level'] ?? null;
            } else {
                $student_data['temporary_province'] = $this->input->post('tProvince');
                $student_data['temporary_district'] = $this->input->post('tDistrict');
                $student_data['temporary_local_level'] = $this->input->post('tLocalLevel');
                $student_data['temporary_ward_no'] = $this->input->post('tWardNo');
                $student_data['temporary_house_no'] = $this->input->post('tHouseNo');
                $student_data['t_block_no'] = $this->input->post('tBlockNo');
                $student_data['t_locality'] = $this->input->post('tLocality');
                $student_data['ugc_t_combined_code'] = $tCode;
                $student_data['ugc_t_province'] = $tAddr['province'] ?? null;
                $student_data['ugc_t_district'] = $tAddr['district'] ?? null;
                $student_data['ugc_t_local_level'] = $tAddr['local_level'] ?? null;
            }
    
            // Handle file uploads
            $upload_config = [
                'upload_path' => './uploads/students/',
                'allowed_types' => 'jpg|jpeg|png|pdf',
                'max_size' => 2048, // 2MB
                'encrypt_name' => TRUE
            ];
    
            $this->load->library('upload', $upload_config);
    
            // Process uploads only if files are provided
            $upload_fields = [
                'citizenshipFront' => 'citizenship_front',
                'citizenshipBack' => 'citizenship_back',
                'nidPic' => 'nid_pic',
                'ppSizePhoto' => 'pp_size_photo',
                'digitalSignature' => 'digital_signature'
            ];
    
            foreach ($upload_fields as $field => $db_field) {
                if (!empty($_FILES[$field]['name'])) {
                    // Reset configuration for each upload
                    $this->upload->initialize($upload_config);
                    
                    $_FILES['file']['name'] = $_FILES[$field]['name'];
                    $_FILES['file']['type'] = $_FILES[$field]['type'];
                    $_FILES['file']['tmp_name'] = $_FILES[$field]['tmp_name'];
                    $_FILES['file']['error'] = $_FILES[$field]['error'];
                    $_FILES['file']['size'] = $_FILES[$field]['size'];
                    
                    if ($this->upload->do_upload('file')) {
                        $upload_data = $this->upload->data();
                        $student_data[$db_field] = 'uploads/students/' . $upload_data['file_name'];
                        
                        // Delete old file if exists
                        if (!empty($existing_student[$db_field]) && file_exists($existing_student[$db_field])) {
                            unlink($existing_student[$db_field]);
                        }
                    } else {
                        // Log upload errors but continue with other fields
                        log_message('error', 'Failed to upload ' . $field . ': ' . $this->upload->display_errors('', ''));
                    }
                }
            }
    
            // Update student record
            $update_result = $this->student_model->update_student($id, $student_data);
    
            if ($update_result) {
                // Build the SAME cleaned DB-snapshot payload as create, so PATCH does not
                // forward raw form fields (empty rollNo/phones, leading NBSP, etc.) that
                // can crash the UGC PATCH handler (HTTP 500).
                $this->load->helper('hemis_sync_post_from_db');
                $freshRow = $this->student_model->get_student_by_id((int) $id);
                $hemisMultipart = is_array($freshRow) ? hemis_student_db_row_to_create_post($freshRow) : null;
                $hemis_sync = $this->hemis_client->sync_student_after_local_update($id, $hemisMultipart);
                return $this->response([
                    'status' => 'Success',
                    'message' => 'Student record updated successfully.',
                    'hemisSync' => $hemis_sync,
                ], REST_Controller::HTTP_OK);
            } else {
                return $this->response([
                    'status' => 'error',
                    'message' => 'Failed to update student record'
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            log_message('error', 'Error updating student ' . $id . ': ' . $e->getMessage());
            return $this->response([
                'status' => 'error',
                'message' => 'An error occurred while updating student',
                'error' => $e->getMessage()
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function get_college_name($school_id) {
        if (!$school_id) {
            return null;
        }
        $set = $this->setting_model->get();
        if (is_array($set) && !empty($set['name'])) {
            return $set['name'];
        }
        if (is_object($set) && isset($set->name)) {
            return $set->name;
        }
        return null;
    }


    private function get_program_name($program_id) {
        if (!$program_id) {
            return null;
        }
        $this->load->model('program_model');
        $program = $this->program_model->get_program_by_id($program_id);
        if (!is_array($program)) {
            return 'Unknown';
        }
        return isset($program['title']) ? $program['title'] : 'Unknown';
    }


    /**
     * Academic level / year label for HEMIS. In this college CMS, `classes` stores the
     * degree year or level row (not “Grade 1”), joined via `student_session.class_id`.
     */
    private function get_level_name($class_id) {
        if (!$class_id) {
            return null;
        }
        $this->load->model('class_model');
        $class = $this->class_model->getAll((int) $class_id);
        if (is_array($class)) {
            if (!empty($class['level_name'])) {
                return $class['level_name'];
            }
            if (!empty($class['class'])) {
                return $class['class'];
            }
        }
        return 'Unknown';
    }


    private function get_faculty_name($program_id) {
        if (!$program_id) {
            return null;
        }
        $this->load->model('program_model');
        $program = $this->program_model->get_program_by_id($program_id);
        if (!is_array($program) || empty($program['faculty_id'])) {
            return null;
        }
        if (!$this->db->table_exists('faculty')) {
            return null;
        }
        $q = $this->db->select('name')->from('faculty')->where('id', (int) $program['faculty_id'])->limit(1)->get();
        if ($q->num_rows() === 0) {
            return null;
        }
        $row = $q->row_array();
        return isset($row['name']) ? $row['name'] : null;
    }


    private function get_fiscal_year() {
        $current_year = date('Y');
        $month = date('n');
       
        if ($month >= 7) {
            return $current_year . '/' . ($current_year + 1);
        } else {
            return ($current_year - 1) . '/' . $current_year;
        }
    }


    /**
     * Semester or term cohort label for HEMIS. In this college CMS, `sections` represent
     * semester/year buckets (e.g. Semester 1) linked via `student_session.section_id`.
     */
    private function get_semester($section_id) {
        if (!$section_id) {
            return null;
        }
        $this->load->model('section_model');
        $section = $this->section_model->get((int) $section_id);
        if (is_array($section) && !empty($section['section'])) {
            return $section['section'];
        }
        return null;
    }
}





