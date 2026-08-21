<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Superadmin UI for UGC / central API URLs, bearer token, sync flags.
 */
class Hemisintegration extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('hemis_integration_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'hemisintegration/index');

        $data['cfg'] = $this->hemis_integration_model->get_for_form();
        $data['title'] = $this->lang->line('hemis_integration');
        $this->load->library('hemis_ugc_auth');
        $data['ugc_token_status'] = $this->hemis_ugc_auth->get_bearer_token_status(
            $this->hemis_integration_model->get_effective_config()
        );

        if (!$this->db->table_exists('hemis_integration_settings')) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">'
                . 'Run <code>application/sql/hemis_integration_settings.sql</code> on your database, then reload this page.'
                . '</div>');
        }

        if ($this->input->post('submit') && $this->rbac->hasPrivilege('superadmin', 'can_edit')) {
            $this->form_validation->set_rules('remote_base_url', 'Remote API base URL', 'trim');
            $this->form_validation->set_rules('remote_timeout', 'Timeout', 'integer');
            if ($this->form_validation->run()) {
                $post = $this->input->post();
                if ($this->hemis_integration_model->save_from_post($post)) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success">'
                        . $this->lang->line('update_message') . '</div>');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger">'
                        . 'Could not save settings. Ensure the database table exists.</div>');
                }
                redirect('hemisintegration');
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/hemis_integration/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX: test POST /api/User/Login with current form values (saves token on success).
     * POST /hemisintegration/test_ugc_login
     */
    public function test_ugc_login()
    {
        if (!$this->rbac->hasPrivilege('superadmin', 'can_edit')) {
            $this->output->set_status_header(403);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array('status' => 0, 'message' => 'Permission denied')));
            return;
        }

        if (strtoupper((string) $this->input->method()) !== 'POST') {
            $this->output->set_status_header(405);
            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode(array('status' => 0, 'message' => 'POST required')));
            return;
        }

        $cfg = $this->hemis_integration_model->get_effective_config();
        if (!is_array($cfg)) {
            $cfg = array();
        }

        $baseUrl = trim((string) $this->input->post('remote_base_url'));
        if ($baseUrl !== '') {
            $cfg['remote_base_url'] = $baseUrl;
        }

        $email = trim((string) $this->input->post('remote_login_email'));
        if ($email !== '') {
            $cfg['remote_login_email'] = $email;
        }

        $pass = (string) $this->input->post('remote_login_password');
        if ($pass !== '') {
            $cfg['remote_login_password'] = $pass;
        }

        if ($this->input->post('remote_college_id') !== null && $this->input->post('remote_college_id') !== '') {
            $cfg['remote_college_id'] = (int) $this->input->post('remote_college_id');
        }
        if ($this->input->post('remote_uni_id') !== null && $this->input->post('remote_uni_id') !== '') {
            $cfg['remote_uni_id'] = (int) $this->input->post('remote_uni_id');
            $cfg['remote_university_id'] = (int) $this->input->post('remote_uni_id');
        }

        $cfg['remote_is_ugc'] = $this->input->post('remote_is_ugc') ? true : false;

        $timeout = (int) $this->input->post('remote_timeout');
        if ($timeout >= 5 && $timeout <= 300) {
            $cfg['remote_timeout'] = $timeout;
        }

        $persist = $this->input->post('save_token') !== '0';
        $includeToken = $this->input->post('include_token') === '1';
        $this->load->library('hemis_ugc_auth');
        $result = $this->hemis_ugc_auth->test_login_from_config($cfg, $persist, $includeToken);

        if (!empty($cfg['mock_mode'])) {
            $result['mock_mode_note'] = 'Mock mode is ON in settings — sync still uses mock, but this test called the real UGC login URL.';
        }

        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($result));
    }
}
