<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

/** Proxy GET /api/FiscalYear (PDF §5.3; UGC doc: no auth — campus still requires Bearer). */
class Fiscalyear extends REST_Controller
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
        $r = $this->hemis_client->hemis_get_fiscal_year();
        if ($r['ok']) {
            $data = $r['data'];
            return $this->response(is_array($data) ? $data : array(), self::HTTP_OK);
        }
        if ($r['mode'] === 'disabled') {
            return $this->response(array(), self::HTTP_OK);
        }
        return $this->response(array(
            'status'  => false,
            'message' => $r['error'] ? $r['error'] : 'HEMIS fiscal year pull failed',
        ), self::HTTP_BAD_GATEWAY);
    }
}
