<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/** PDF §5.1.5 Lands. */
class Lands extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
        $this->load->model('api_land_model');
        $this->load->library('hemis_client');
    }

    public function index_get()
    {
        return $this->response($this->api_land_model->get_all(), self::HTTP_OK);
    }

    public function view_get($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Land ID is required',
            ), self::HTTP_BAD_REQUEST);
        }
        $row = $this->api_land_model->get($id);
        if ($row === null) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Land record not found',
            ), self::HTTP_NOT_FOUND);
        }
        return $this->response($row, self::HTTP_OK);
    }

    public function index_post()
    {
        $data = array(
            'totalArea' => $this->post('totalArea'),
            'unit'      => $this->post('unit'),
            'sheetNo'   => $this->post('sheetNo'),
            'kittaNo'   => $this->post('kittaNo'),
            'ownerShip' => $this->post('ownerShip'),
            'remarks'   => $this->post('remarks'),
        );

        $newId = $this->api_land_model->create($data);
        if (!$newId) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Failed to create land record',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $rowN = $this->api_land_model->get($newId);
        $payload = $rowN;
        $payload['id'] = 0;
        $hemisSync = $this->hemis_client->sync_land_create($rowN);
        return $this->response(array_merge($rowN, array('hemisSync' => $hemisSync)), self::HTTP_CREATED);
    }

    public function index_put($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Land ID is required',
            ), self::HTTP_BAD_REQUEST);
        }
        if (!$this->api_land_model->get($id)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Land record not found',
            ), self::HTTP_NOT_FOUND);
        }

        $fields = array('totalArea', 'unit', 'sheetNo', 'kittaNo', 'ownerShip', 'remarks');
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

        if (!$this->api_land_model->update($id, $patch)) {
            return $this->response(array(
                'status'  => false,
                'message' => 'Update failed',
            ), self::HTTP_INTERNAL_SERVER_ERROR);
        }

        $row = $this->api_land_model->get($id);
        $hemisSync = $this->hemis_client->sync_land_update((int) $id, $row);
        return $this->response(array_merge($row, array('hemisSync' => $hemisSync)), self::HTTP_OK);
    }
}
