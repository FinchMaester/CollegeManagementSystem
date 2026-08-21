<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/** Proxy GET /api/EmployeePositions (PDF §5.10). */
class EmployeePositions extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
        $this->load->library('hemis_client');
    }

    public function index_get()
    {
        $r = $this->hemis_client->hemis_get_employee_positions();
        if ($r['ok']) {
            $data = $r['data'];
            return $this->response(is_array($data) ? $data : array(), self::HTTP_OK);
        }
        if ($r['mode'] === 'disabled') {
            return $this->response(array(), self::HTTP_OK);
        }
        return $this->response(array(
            'status'  => false,
            'message' => $r['error'] ? $r['error'] : 'HEMIS employee positions pull failed',
        ), self::HTTP_BAD_GATEWAY);
    }

    public function view_get($id = null)
    {
        if ($id === null || $id === '') {
            return $this->response(array(
                'status'  => false,
                'message' => 'Position id is required',
            ), self::HTTP_BAD_REQUEST);
        }
        $r = $this->hemis_client->hemis_get_employee_position($id);
        if ($r['ok'] && is_array($r['data'])) {
            return $this->response($r['data'], self::HTTP_OK);
        }
        if ($r['mode'] === 'disabled') {
            return $this->response(array(
                'status'  => false,
                'message' => 'pull_employee_positions is disabled',
            ), self::HTTP_NOT_FOUND);
        }
        return $this->response(array(
            'status'  => false,
            'message' => $r['error'] ? $r['error'] : 'Not found',
        ), self::HTTP_BAD_GATEWAY);
    }
}
