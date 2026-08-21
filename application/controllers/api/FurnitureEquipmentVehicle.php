<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/** PDF §5.1.2 Furniture, Equipment, Vehicle. */
class FurnitureEquipmentVehicle extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
        $this->load->model('api_fev_model');
        $this->load->library('hemis_client');
    }

    public function index_get()
    {
        return $this->response($this->api_fev_model->get_all(), self::HTTP_OK);
    }

    public function view_get($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Item ID is required',
            ), self::HTTP_BAD_REQUEST);
        }
        $row = $this->api_fev_model->get($id);
        if ($row === null) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Item not found',
            ), self::HTTP_NOT_FOUND);
        }
        return $this->response($row, self::HTTP_OK);
    }

    public function index_post()
    {
        if (!$this->post('itemName') && $this->post('itemName') !== '0') {
            return $this->response(array(
                'status'  => false,
                'message' => 'itemName is required',
            ), self::HTTP_BAD_REQUEST);
        }

        $data = array(
            'itemType'        => $this->post('itemType'),
            'itemName'        => $this->post('itemName'),
            'noOfQty'         => $this->post('noOfQty'),
            'itemDescription' => $this->post('itemDescription'),
            'remarks'         => $this->post('remarks'),
        );

        $newId = $this->api_fev_model->create($data);
        if (!$newId) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Failed to create item',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $data['id'] = $newId;
        $hemisSync = $this->hemis_client->sync_fev_create($data);
        return $this->response(array_merge($data, array('hemisSync' => $hemisSync)), self::HTTP_CREATED);
    }

    public function index_put($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Item ID is required',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->api_fev_model->get($id)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Item not found',
            ), self::HTTP_NOT_FOUND);
        }

        $fields = array('itemType', 'itemName', 'noOfQty', 'itemDescription', 'remarks');
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

        if (!$this->api_fev_model->update($id, $patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Update failed',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $row = $this->api_fev_model->get($id);
        $hemisSync = $this->hemis_client->sync_fev_update((int) $id, $row);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_OK);
    }
}
