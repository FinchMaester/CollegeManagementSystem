<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/**
 * Program Management API Controller
 * 
 * Handles API endpoints related to program management
 */
class ProgramMgmt extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('program_model');
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
    }


    /**
     * GET /api/ProgramMgmt/GetCollegePrograms — PDF V1.3: campus returns local catalog copy (array body).
     * Central master list is obtained FROM UGC via Hemis_client::hemis_get_college_programs() when needed.
     */
    public function GetCollegePrograms_get() {
        $campusId = $this->get('campus_id');
        
        $programs = $this->program_model->get_college_programs($campusId);
        
        if (!empty($programs)) {
            $this->response($programs, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No programs found'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}