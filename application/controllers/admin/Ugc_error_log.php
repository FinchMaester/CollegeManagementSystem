<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Ugc_error_log extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ugc_error_log_model');
    }

    public function index()
    {
        // Restrict to Super Admin only (maintenance/support tooling).
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'admin/ugc_error_log/index');

        $data = array();
        $data['title'] = 'UGC Error Log';
        $data['rows'] = $this->ugc_error_log_model->latest(200);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/ugc_error_log/index', $data);
        $this->load->view('layout/footer', $data);
    }
}

