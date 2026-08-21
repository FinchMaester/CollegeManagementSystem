<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/**
 * Library API Controller
 *
 * This controller handles API requests for the Library resource
 */
class Library extends REST_Controller {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Load necessary models
        $this->load->model('library_model');
        $this->load->library('hemis_client');
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
    }
    
    /**
     * GET /api/Library — PDF V1.3 §5.1.6
     */
    public function index_get()
    {
        $rows = $this->library_model->get_all_libraries();
        return $this->response($rows, self::HTTP_OK);
    }

    /**
     * GET /api/Library/{id}
     */
    public function view_get($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response([
                'status' => false,
                'message' => 'Library ID is required',
            ], self::HTTP_BAD_REQUEST);
        }
        $row = $this->library_model->get_library($id);
        if ($row === null) {
            return $this->response([
                'status' => false,
                'message' => 'Library not found',
            ], self::HTTP_NOT_FOUND);
        }
        $row['buildingName'] = '';
        $row['campusName'] = '';
        return $this->response($row, self::HTTP_OK);
    }

    /**
     * Create a new library record
     * Responds to POST request at /api/Library
     *
     * @return void
     */
    public function index_post() {
        // Validate required fields
        $required_fields = [
            'libraryName', 'buildingId', 'campusId', 'noOfLibraryRooms',
            'totalAreaOfLibraryRoom', 'noOfReadingHalls', 'areaOfReadingHall',
            'noOfBooks', 'noOfDailyJournals', 'noOfWeeklyJournals',
            'noOfMonthlyJournals', 'noOfAnnualJournals', 'hasELibraryFacility'
        ];
        
        $missing_fields = [];
        foreach ($required_fields as $field) {
            if (!$this->post($field) && $this->post($field) !== 0 && $this->post($field) !== false) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            return $this->response([
                'status' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
            ], self::HTTP_BAD_REQUEST);
        }
        
        // Get data from request body
        $library_data = [
            'libraryName' => $this->post('libraryName'),
            'buildingId' => $this->post('buildingId'),
            'campusId' => $this->post('campusId'),
            'noOfLibraryRooms' => $this->post('noOfLibraryRooms'),
            'totalAreaOfLibraryRoom' => $this->post('totalAreaOfLibraryRoom'),
            'noOfReadingHalls' => $this->post('noOfReadingHalls'),
            'areaOfReadingHall' => $this->post('areaOfReadingHall'),
            'noOfBooks' => $this->post('noOfBooks'),
            'noOfDailyJournals' => $this->post('noOfDailyJournals'),
            'noOfWeeklyJournals' => $this->post('noOfWeeklyJournals'),
            'noOfMonthlyJournals' => $this->post('noOfMonthlyJournals'),
            'noOfAnnualJournals' => $this->post('noOfAnnualJournals'),
            'hasELibraryFacility' => $this->post('hasELibraryFacility'),
            'remarks' => $this->post('remarks')
        ];
        
        // Save to database
        try {
            $id = $this->library_model->create_library($library_data);
            
            if ($id) {
                // Add the ID to the data
                $library_data['id'] = $id;

                $hemisSync = $this->hemis_client->sync_library_create($library_data);
                
                // Return success response
                return $this->response(array_merge($library_data, array('hemisSync' => $hemisSync)), self::HTTP_CREATED);
            } else {
                return $this->response([
                    'status' => false,
                    'message' => 'Failed to create library record'
                ], self::HTTP_INTERNAL_ERROR);
            }
        } catch (Exception $e) {
            return $this->response([
                'status' => false,
                'message' => $e->getMessage()
            ], self::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Update a library record — PUT /api/Library/{id}
     *
     * @param int|null $id
     * @return void
     */
    public function index_put($id = null) {
        if ($id === null) {
            return $this->response([
                'status' => false,
                'message' => 'Library ID is required'
            ], self::HTTP_BAD_REQUEST);
        }

        $existing_library = $this->library_model->get_library($id);
        if (!$existing_library) {
            return $this->response([
                'status' => false,
                'message' => 'Library not found with ID: ' . $id
            ], self::HTTP_NOT_FOUND);
        }

        $library_data = [];
        $update_fields = [
            'libraryName', 'buildingId', 'campusId', 'noOfLibraryRooms',
            'totalAreaOfLibraryRoom', 'noOfReadingHalls', 'areaOfReadingHall',
            'noOfBooks', 'noOfDailyJournals', 'noOfWeeklyJournals',
            'noOfMonthlyJournals', 'noOfAnnualJournals', 'hasELibraryFacility',
            'remarks'
        ];

        foreach ($update_fields as $field) {
            $value = $this->put($field);
            if ($value !== null) {
                $library_data[$field] = $value;
            }
        }

        if (empty($library_data)) {
            return $this->response([
                'status' => false,
                'message' => 'No data provided for update'
            ], self::HTTP_BAD_REQUEST);
        }

        try {
            $updated = $this->library_model->update_library($id, $library_data);

            if ($updated) {
                $updated_library = $this->library_model->get_library($id);
                $hemisSync = $this->hemis_client->sync_library_update((int) $id, $updated_library);

                return $this->response(array_merge($updated_library, array('hemisSync' => $hemisSync)), self::HTTP_OK);
            }

            return $this->response([
                'status' => false,
                'message' => 'No changes made to library record'
            ], self::HTTP_OK);
        } catch (Exception $e) {
            return $this->response([
                'status' => false,
                'message' => $e->getMessage()
            ], self::HTTP_INTERNAL_ERROR);
        }
    }
    
}