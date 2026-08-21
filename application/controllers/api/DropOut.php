<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class DropOut extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('setting_model');
        $this->load->model('student_model');
        $this->load->library('hemis_client');
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
    }
    
    /**
     * Get all dropout students
     * 
     * @return void
     */
    public function index_get()
    {
        try {
            $dropoutStudents = $this->getDropoutStudents();
            
            if (!empty($dropoutStudents)) {
                $this->response([
                    'status' => true,
                    'message' => 'Dropout students retrieved successfully',
                    'data' => $dropoutStudents
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'No dropout students found',
                    'data' => []
                ], self::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Get a specific dropout student by ID
     * 
     * @param int $id Student ID
     * @return void
     */
    public function view_get($id)
    {
        try {
            // Validate if ID is provided
            if (empty($id)) {
                $this->response([
                    'status' => false,
                    'message' => 'Student ID is required',
                    'data' => []
                ], self::HTTP_BAD_REQUEST);
                return;
            }

            $dropoutStudent = $this->getDropoutStudentById($id);
            
            if (!empty($dropoutStudent)) {
                $this->response([
                    'status' => true,
                    'message' => 'Dropout student details retrieved successfully',
                    'data' => $dropoutStudent
                ], self::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Dropout student not found',
                    'data' => []
                ], self::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Update dropout student information
     * 
     * @param int $id Student ID
     * @return void
     */
    public function index_put($id)
    {
        try {
            // Validate if ID is provided
            if (empty($id)) {
                $this->response([
                    'status' => false,
                    'message' => 'Student ID is required in URL',
                    'data' => []
                ], self::HTTP_BAD_REQUEST);
                return;
            }

            // Get JSON input
            $input = json_decode($this->input->raw_input_stream, true);
            
            // Validate input
            $validation = $this->validateDropoutInput($input);
            if (!$validation['valid']) {
                $this->response([
                    'status' => false,
                    'message' => $validation['message'],
                    'data' => []
                ], self::HTTP_BAD_REQUEST);
                return;
            }

            $studentId = $input['studentId'];
            $reason = $input['reason'];
            $dateOfEntry = $input['dateOfEntry'];
            $remarks = $input['remarks'];

            // Validate that URL ID matches body studentId
            if ($id != $studentId) {
                $this->response([
                    'status' => false,
                    'message' => 'Student ID in URL must match studentId in request body',
                    'data' => []
                ], self::HTTP_BAD_REQUEST);
                return;
            }

            // Check if student exists and is already a dropout
            $existingDropout = $this->getDropoutStudentById($studentId);
            if (empty($existingDropout)) {
                $this->response([
                    'status' => false,
                    'message' => 'Dropout student not found. Student must be marked as dropout first.',
                    'data' => []
                ], self::HTTP_NOT_FOUND);
                return;
            }

            // Start transaction
            $this->db->trans_start();

            // Update student table
            $studentData = [
                'dis_reason' => $reason,
                'disable_at' => $dateOfEntry,
                'dis_note' => $remarks,
                'updated_at' => date('Y-m-d')
            ];

            $this->db->where('id', $studentId);
            $this->db->where('is_active', 'no'); // Ensure we only update dropout students
            $this->db->update('students', $studentData);

            // Update student_session table timestamp
            $sessionData = [
                'updated_at' => date('Y-m-d')
            ];

            $this->db->where('student_id', $studentId);
            $this->db->where('is_leave', 1); // Ensure we only update dropout sessions
            $this->db->update('student_session', $sessionData);

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to update dropout student information',
                    'data' => []
                ], self::HTTP_INTERNAL_ERROR);
                return;
            }

            // Check if any rows were affected
            if ($this->db->affected_rows() == 0) {
                $this->response([
                    'status' => false,
                    'message' => 'No changes were made to the dropout student information',
                    'data' => []
                ], self::HTTP_NOT_MODIFIED);
                return;
            }

            // Get updated student data
            $updatedStudent = $this->getDropoutStudentById($studentId);

            $hemisSync = $this->hemis_client->sync_dropout_put((int) $studentId, $input);

            $this->response([
                'status' => true,
                'message' => 'Dropout student information updated successfully',
                'data' => $updatedStudent,
                'hemisSync' => $hemisSync,
            ], self::HTTP_OK);

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->trans_rollback();
            
            $this->response([
                'status' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
                'data' => []
            ], self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Mark a student as dropout
     * 
     * @return void
     */
    public function index_post()
    {
        try {
            // Get JSON input
            $input = json_decode($this->input->raw_input_stream, true);
            
            // Validate input
            $validation = $this->validateDropoutInput($input);
            if (!$validation['valid']) {
                $this->response([
                    'status' => false,
                    'message' => $validation['message'],
                    'data' => []
                ], self::HTTP_BAD_REQUEST);
                return;
            }

            $studentId = $input['studentId'];
            $reason = $input['reason'];
            $dateOfEntry = $input['dateOfEntry'];
            $remarks = $input['remarks'];

            // Check if student exists and is active
            $student = $this->getActiveStudentById($studentId);
            if (empty($student)) {
                $this->response([
                    'status' => false,
                    'message' => 'Student not found or already inactive',
                    'data' => []
                ], self::HTTP_NOT_FOUND);
                return;
            }

            // Check if student is already marked as dropout
            if ($this->isStudentAlreadyDropout($studentId)) {
                $this->response([
                    'status' => false,
                    'message' => 'Student is already marked as dropout',
                    'data' => []
                ], self::HTTP_CONFLICT);
                return;
            }

            // Start transaction
            $this->db->trans_start();

            // Update student table
            $studentData = [
                'is_active' => 'no',
                'dis_reason' => $reason,
                'disable_at' => $dateOfEntry,
                'dis_note' => $remarks,
                'updated_at' => date('Y-m-d')
            ];

            $this->db->where('id', $studentId);
            $this->db->update('students', $studentData);

            // Update student_session table
            $sessionData = [
                'is_leave' => 1,
                'is_active' => 'no',
                'updated_at' => date('Y-m-d')
            ];

            $this->db->where('student_id', $studentId);
            $this->db->update('student_session', $sessionData);

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to mark student as dropout',
                    'data' => []
                ], self::HTTP_INTERNAL_ERROR);
                return;
            }

            // Get updated student data
            $updatedStudent = $this->getDropoutStudentById($studentId);

            $hemisSync = $this->hemis_client->sync_dropout_post((int) $studentId, $input);

            $this->response([
                'status' => true,
                'message' => 'Student marked as dropout successfully',
                'data' => $updatedStudent,
                'hemisSync' => $hemisSync,
            ], self::HTTP_CREATED);

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->trans_rollback();
            
            $this->response([
                'status' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
                'data' => []
            ], self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Validate dropout input data
     * 
     * @param array $input
     * @return array
     */
    private function validateDropoutInput($input)
    {
        if (empty($input)) {
            return [
                'valid' => false,
                'message' => 'Request body is required'
            ];
        }

        // Check required fields
        $requiredFields = ['studentId', 'reason', 'dateOfEntry', 'remarks'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                return [
                    'valid' => false,
                    'message' => "Field '{$field}' is required"
                ];
            }
        }

        // Validate studentId is numeric
        if (!is_numeric($input['studentId']) || $input['studentId'] <= 0) {
            return [
                'valid' => false,
                'message' => 'Student ID must be a valid positive number'
            ];
        }

        // Validate date format
        $dateOfEntry = $input['dateOfEntry'];
        if (!DateTime::createFromFormat('Y-m-d', $dateOfEntry)) {
            return [
                'valid' => false,
                'message' => 'Date of entry must be in YYYY-MM-DD format'
            ];
        }

        // Validate date is not in the future
        if (strtotime($dateOfEntry) > time()) {
            return [
                'valid' => false,
                'message' => 'Date of entry cannot be in the future'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Valid input'
        ];
    }

    /**
     * Get active student by ID
     * 
     * @param int $id
     * @return array
     */
    private function getActiveStudentById($id)
    {
        $this->db->select('id, firstname, lastname, is_active');
        $this->db->from('students');
        $this->db->where('id', $id);
        $this->db->where('is_active', 'yes');
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Check if student is already marked as dropout
     * 
     * @param int $studentId
     * @return bool
     */
    private function isStudentAlreadyDropout($studentId)
    {
        $this->db->select('id');
        $this->db->from('student_session');
        $this->db->where('student_id', $studentId);
        $this->db->where('is_leave', 1);
        
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Retrieve all dropout students from the database
     * 
     * @return array
     */
    private function getDropoutStudents()
    {
        // Join student and student_session tables to get dropout students
        $this->db->select('
            students.id as studentId,
            students.firstname,
            students.middlename,
            students.lastname,
            students.first_name_np as studentNameNepali,
            students.email,
            students.roll_no as rollNo,
            students.batch_name as batch,
            student_session.is_leave,
            students.dis_reason as reason,
            students.disable_at as dateOfEntry,
            students.dis_note as remarks
        ');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
        $this->db->where('student_session.is_leave', 1);
        $this->db->where('students.is_active', 'no');
        
        $query = $this->db->get();
        $result = $query->result_array();
        
        // Format the response according to API documentation
        $formattedResult = [];
        foreach ($result as $student) {
            $studentName = trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']);
            
            // Format batch value to integer if possible
            $batch = is_numeric($student['batch']) ? (int)$student['batch'] : $student['batch'];
            
            $formattedResult[] = [
                'id' => (int)$student['studentId'] ?? 0,
                'studentName' => $studentName,
                'studentNameNepali' => $student['studentNameNepali'] ?? '',
                'email' => $student['email'] ?? '',
                'batch' => $batch,
                'rollNo' => $student['rollNo'] ?? '',
                'reason' => $student['reason'] ?? '',
                'dateOfEntry' => $student['dateOfEntry'] ?? '',
                'remarks' => $student['remarks'] ?? ''
            ];
        }
        
        return $formattedResult;
    }

    /**
     * Retrieve a specific dropout student by ID
     * 
     * @param int $id Student ID
     * @return array
     */
    private function getDropoutStudentById($id)
    {
        // Join student and student_session tables to get dropout student details
        $this->db->select('
            students.id as studentId,
            students.firstname,
            students.middlename,
            students.lastname,
            students.first_name_np as studentNameNepali,
            students.email,
            students.roll_no as rollNo,
            students.batch_name as batch,
            student_session.is_leave,
            students.dis_reason as reason,
            students.disable_at as dateOfEntry,
            students.dis_note as remarks
        ');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
        $this->db->where('student_session.is_leave', 1);
        $this->db->where('students.is_active', 'no');
        $this->db->where('students.id', $id);
        
        $query = $this->db->get();
        $student = $query->row_array();
        
        if (empty($student)) {
            return [];
        }
        
        // Format the student details
        $studentName = trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']);
        
        // Format batch value to integer if possible
        $batch = is_numeric($student['batch']) ? (int)$student['batch'] : $student['batch'];
        
        return [
            'id' => (int)$student['studentId'] ?? 0,
            'studentName' => $studentName,
            'studentNameNepali' => $student['studentNameNepali'] ?? '',
            'email' => $student['email'] ?? '',
            'batch' => $batch,
            'rollNo' => $student['rollNo'] ?? '',
            'reason' => $student['reason'] ?? '',
            'dateOfEntry' => $student['dateOfEntry'] ?? '',
            'remarks' => $student['remarks'] ?? ''
        ];
    }
}