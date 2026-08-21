<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/** PDF V1.3 §5.1.1 Buildings — local api_buildings + outbound sync. */
class Buildings extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
        $this->load->model('api_building_model');
        $this->load->library('hemis_client');
    }

    public function index_get()
    {
        return $this->response($this->api_building_model->get_all(), self::HTTP_OK);
    }

    public function view_get($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Building ID is required',
            ), self::HTTP_BAD_REQUEST);
        }
        $row = $this->api_building_model->get($id);
        if ($row === null) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Building not found',
            ), self::HTTP_NOT_FOUND);
        }
        return $this->response($row, self::HTTP_OK);
    }

    public function index_post()
    {
        if (!$this->post('houseName') && $this->post('houseName') !== '0') {
            return $this->response(array(
                'status'  => false,
                'message' => 'houseName is required',
            ), self::HTTP_BAD_REQUEST);
        }

        $data = array(
            'houseName'               => $this->post('houseName'),
            'areaCoveredByBuilding'   => $this->post('areaCoveredByBuilding'),
            'noOfClassrooms'          => $this->post('noOfClassrooms'),
            'areaCoveredByAllRooms'   => $this->post('areaCoveredByAllRooms'),
            'ownershipOfBuilding'     => $this->post('ownershipOfBuilding'),
            'hasInternetConnection'   => $this->post('hasInternetConnection'),
            'remarks'                 => $this->post('remarks'),
        );

        $newId = $this->api_building_model->create($data);
        if (!$newId) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Failed to create building',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data['id'] = $newId;
        $hemisSync = $this->hemis_client->sync_building_create($data);
        return $this->response(array_merge($data, array('hemisSync' => $hemisSync)), self::HTTP_CREATED);
    }

    public function index_put($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Building ID is required',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->api_building_model->get($id)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Building not found',
            ), self::HTTP_NOT_FOUND);
        }

        $fields = array(
            'houseName', 'areaCoveredByBuilding', 'noOfClassrooms', 'areaCoveredByAllRooms',
            'ownershipOfBuilding', 'hasInternetConnection', 'remarks',
        );
        $patch = array();
        foreach ($fields as $f) {
            $v = $this->put($f);
            if ($v !== null) {
                $patch[$f] = $v;
            }
        }
        if (empty($patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'No data provided for update',
            ), self::HTTP_BAD_REQUEST);
        }

        if (!$this->api_building_model->update($id, $patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Update failed',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $row = $this->api_building_model->get($id);
        $hemisSync = $this->hemis_client->sync_building_update((int) $id, $row);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_OK);
    }
}
