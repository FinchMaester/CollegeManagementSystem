<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/** Proxy GET /api/Address/GetAll (PDF §5.5). */
class Address extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('hemis_rest_auth');
        $this->hemis_rest_auth->guard_controller($this);
        $this->load->library('hemis_client');
    }

    public function getall_get()
    {
        $r = $this->hemis_client->hemis_get_address_all();
        if ($r['ok']) {
            $data = $r['data'];
            return $this->response(is_array($data) ? $data : array(), self::HTTP_OK);
        }
        if ($r['mode'] === 'disabled') {
            return $this->response(array(), self::HTTP_OK);
        }
        return $this->response(array(
            'status'  => false,
            'message' => $r['error'] ? $r['error'] : 'HEMIS address pull failed',
        ), self::HTTP_BAD_GATEWAY);
    }
}
