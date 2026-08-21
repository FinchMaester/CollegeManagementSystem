<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/**
 * API Controller for Lab Management (HEMIS §5.1.4).
 * Class name must match file Labs.php for CodeIgniter routing.
 */
class Labs extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('hemis_client');
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
    }

    /**
     * Get all labs
     * 
     * @return void
     */
    public function index_get()
    {
        try {
            $labs = $this->getAllLabs();
            
            if (!empty($labs)) {
                $this->response([
                    'status' => true,
                    'message' => 'Labs retrieved successfully',
                    'data' => $labs
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'No labs found',
                    'data' => []
                ], REST_Controller::HTTP_OK);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a specific lab by ID
     * 
     * @param int $id Lab ID
     * @return void
     */
    public function view_get($id)
    {
        try {
            // Validate if ID is provided
            if (empty($id)) {
                $this->response([
                    'status' => false,
                    'message' => 'Lab ID is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            $lab = $this->getLabById($id);
            
            if (!empty($lab)) {
                $this->response([
                    'status' => true,
                    'message' => 'Lab details retrieved successfully',
                    'data' => $lab
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Lab not found',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new lab
     * 
     * @return void
     */
    public function index_post()
    {
        try {
            // Get request data
            $data = $this->_post_args;

            // Validate required fields
            $requiredFields = [
                'labName', 
                'buildingId', 
                'areaCoveredByLab', 
                'labType'
            ];
            
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $this->response([
                        'status' => false,
                        'message' => $field . ' is required',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }
            }

            // Validate building exists
            $building = $this->getBuildingById($data['buildingId']);
            if (empty($building)) {
                $this->response([
                    'status' => false,
                    'message' => 'Building not found',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            // Prepare data for insertion
            $labData = [
                'lab_name' => $data['labName'],
                'building_id' => $data['buildingId'],
                'area_covered' => $data['areaCoveredByLab'],
                'lab_type' => $data['labType'],
                'adequency_of_equipment' => isset($data['adequencyOfLabEquipment']) ? $data['adequencyOfLabEquipment'] : false,
                'has_internet' => isset($data['hasInternetConnection']) ? $data['hasInternetConnection'] : false,
                'equipment' => isset($data['equipmentAtLab']) ? $data['equipmentAtLab'] : '',
                'remarks' => isset($data['remarks']) ? $data['remarks'] : '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert lab
            $labId = $this->createLab($labData);

            if ($labId) {
                // Get the newly created lab details
                $newLab = $this->getLabById($labId);
                $hemisSync = $this->hemis_client->sync_labs_create($newLab);

                $this->response([
                    'status' => true,
                    'message' => 'Lab created successfully',
                    'data' => $newLab,
                    'hemisSync' => $hemisSync,
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to create lab',
                    'data' => []
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a lab by ID
     * 
     * @param int $id Lab ID
     * @return void
     */
    public function view_put($id)
    {
        try {
            // Validate if ID is provided
            if (empty($id)) {
                $this->response([
                    'status' => false,
                    'message' => 'Lab ID is required',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            // Check if lab exists
            $existingLab = $this->getLabById($id);
            if (empty($existingLab)) {
                $this->response([
                    'status' => false,
                    'message' => 'Lab not found',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
                return;
            }

            // Get request data
            $data = $this->_put_args;

            // Check if at least one field is provided
            if (empty($data)) {
                $this->response([
                    'status' => false,
                    'message' => 'No data provided for update',
                    'data' => []
                ], REST_Controller::HTTP_BAD_REQUEST);
                return;
            }

            // Validate building exists if buildingId is provided
            if (isset($data['buildingId']) && !empty($data['buildingId'])) {
                $building = $this->getBuildingById($data['buildingId']);
                if (empty($building)) {
                    $this->response([
                        'status' => false,
                        'message' => 'Building not found',
                        'data' => []
                    ], REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }
            }

            // Prepare data for update
            $labData = [];
            
            if (isset($data['labName'])) {
                $labData['lab_name'] = $data['labName'];
            }
            
            if (isset($data['buildingId'])) {
                $labData['building_id'] = $data['buildingId'];
            }
            
            if (isset($data['areaCoveredByLab'])) {
                $labData['area_covered'] = $data['areaCoveredByLab'];
            }
            
            if (isset($data['labType'])) {
                $labData['lab_type'] = $data['labType'];
            }
            
            if (isset($data['adequencyOfLabEquipment'])) {
                $labData['adequency_of_equipment'] = $data['adequencyOfLabEquipment'];
            }
            
            if (isset($data['hasInternetConnection'])) {
                $labData['has_internet'] = $data['hasInternetConnection'];
            }
            
            if (isset($data['equipmentAtLab'])) {
                $labData['equipment'] = $data['equipmentAtLab'];
            }
            
            if (isset($data['remarks'])) {
                $labData['remarks'] = $data['remarks'];
            }
            
            $labData['updated_at'] = date('Y-m-d H:i:s');

            // Update lab
            $updated = $this->updateLab($id, $labData);

            if ($updated) {
                // Get the updated lab details
                $updatedLab = $this->getLabById($id);
                $hemisSync = $this->hemis_client->sync_labs_update((int) $id, $updatedLab);

                $this->response([
                    'status' => true,
                    'message' => 'Lab updated successfully',
                    'data' => $updatedLab,
                    'hemisSync' => $hemisSync,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Failed to update lab',
                    'data' => []
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (Exception $e) {
            $this->response([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve all labs from the database
     * 
     * @return array
     */
    private function getAllLabs()
    {
        $this->db->select('
            labs.id,
            labs.lab_name as labName,
            labs.building_id as buildingId,
            api_buildings.house_name as buildingName,
            labs.area_covered as areaCoveredByLab,
            labs.lab_type as labType,
            labs.adequency_of_equipment as adequencyOfLabEquipment,
            labs.has_internet as hasInternetConnection,
            labs.equipment as equipmentAtLab,
            labs.remarks
        ');
        $this->db->from('labs');
        $this->db->join('api_buildings', 'api_buildings.id = labs.building_id', 'left');
        $this->db->where('labs.is_active', 1);
        
        $query = $this->db->get();
        $result = $query->result_array();
        
        // Format the response according to API documentation
        $formattedResult = [];
        foreach ($result as $lab) {
            $formattedResult[] = [
                'id' => (int)$lab['id'],
                'labName' => $lab['labName'] ?? '',
                'buildingId' => (int)$lab['buildingId'],
                'buildingName' => $lab['buildingName'] ?? '',
                'areaCoveredByLab' => (float)$lab['areaCoveredByLab'],
                'labType' => $lab['labType'] ?? '',
                'adequencyOfLabEquipment' => (bool)$lab['adequencyOfLabEquipment'],
                'hasInternetConnection' => (bool)$lab['hasInternetConnection'],
                'equipmentAtLab' => $lab['equipmentAtLab'] ?? '',
                'remarks' => $lab['remarks'] ?? ''
            ];
        }
        
        return $formattedResult;
    }

    /**
     * Retrieve a specific lab by ID
     * 
     * @param int $id Lab ID
     * @return array
     */
    private function getLabById($id)
    {
        $this->db->select('
            labs.id,
            labs.lab_name as labName,
            labs.building_id as buildingId,
            api_buildings.house_name as buildingName,
            labs.area_covered as areaCoveredByLab,
            labs.lab_type as labType,
            labs.adequency_of_equipment as adequencyOfLabEquipment,
            labs.has_internet as hasInternetConnection,
            labs.equipment as equipmentAtLab,
            labs.remarks
        ');
        $this->db->from('labs');
        $this->db->join('api_buildings', 'api_buildings.id = labs.building_id', 'left');
        $this->db->where('labs.is_active', 1);
        $this->db->where('labs.id', $id);
        
        $query = $this->db->get();
        $lab = $query->row_array();
        
        if (empty($lab)) {
            return [];
        }
        
        // Format the lab details
        return [
            'id' => (int)$lab['id'],
            'labName' => $lab['labName'] ?? '',
            'buildingId' => (int)$lab['buildingId'],
            'buildingName' => $lab['buildingName'] ?? '',
            'areaCoveredByLab' => (float)$lab['areaCoveredByLab'],
            'labType' => $lab['labType'] ?? '',
            'adequencyOfLabEquipment' => (bool)$lab['adequencyOfLabEquipment'],
            'hasInternetConnection' => (bool)$lab['hasInternetConnection'],
            'equipmentAtLab' => $lab['equipmentAtLab'] ?? '',
            'remarks' => $lab['remarks'] ?? ''
        ];
    }

    /**
     * Create a new lab
     * 
     * @param array $data Lab data
     * @return int|bool
     */
    private function createLab($data)
    {
        $this->db->insert('labs', $data);
        return $this->db->insert_id();
    }

    /**
     * Update a lab
     * 
     * @param int $id Lab ID
     * @param array $data Lab data
     * @return bool
     */
    private function updateLab($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('labs', $data);
    }

    /**
     * Get building by ID
     * 
     * @param int $id Building ID
     * @return array
     */
    private function getBuildingById($id)
    {
        if (!$this->db->table_exists('api_buildings')) {
            return array();
        }
        $this->db->select('id, house_name as name');
        $this->db->from('api_buildings');
        $this->db->where('id', (int) $id);

        $query = $this->db->get();
        return $query->row_array();
    }
}