<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarysettings extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('library_settings_model');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_settings', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'admin/librarysettings');

        $data['title'] = 'Library Settings';
        $data['currency_symbol'] = $this->customlib->getSchoolCurrencyFormat();
        $data['settings'] = $this->library_settings_model->get_for_form();
        $can_edit = $this->rbac->hasPrivilege('library_settings', 'can_edit');
        $data['can_edit'] = $can_edit;

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if (!$can_edit) {
                access_denied();
            }
            $this->form_validation->set_rules('renewal_days', 'Renewal extension days', 'trim|required|integer|greater_than[0]');
            $this->form_validation->set_rules('max_renewals', 'Maximum renewals', 'trim|required|integer');
            $this->form_validation->set_rules('fine_per_day', 'Fine per day', 'trim|required|numeric');
            $this->form_validation->set_rules('fine_grace_days', 'Grace days', 'trim|required|integer');
            $this->form_validation->set_rules('fine_max', 'Maximum fine', 'trim|required|numeric');

            if ($this->form_validation->run() === false) {
                $data['settings'] = array(
                    'renewal_enabled' => $this->input->post('renewal_enabled') ? 1 : 0,
                    'renewal_days'    => (int) $this->input->post('renewal_days'),
                    'max_renewals'    => (int) $this->input->post('max_renewals'),
                    'fine_enabled'    => $this->input->post('fine_enabled') ? 1 : 0,
                    'fine_per_day'    => (float) $this->input->post('fine_per_day'),
                    'fine_grace_days' => (int) $this->input->post('fine_grace_days'),
                    'fine_max'        => (float) $this->input->post('fine_max'),
                );
            } else {
                $this->library_settings_model->save_from_post(array(
                    'renewal_enabled' => $this->input->post('renewal_enabled'),
                    'renewal_days'    => $this->input->post('renewal_days'),
                    'max_renewals'    => $this->input->post('max_renewals'),
                    'fine_enabled'    => $this->input->post('fine_enabled'),
                    'fine_per_day'    => $this->input->post('fine_per_day'),
                    'fine_grace_days' => $this->input->post('fine_grace_days'),
                    'fine_max'        => $this->input->post('fine_max'),
                ));
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
                redirect('admin/librarysettings');
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/library/settings', $data);
        $this->load->view('layout/footer', $data);
    }
}
