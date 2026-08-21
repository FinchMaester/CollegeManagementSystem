<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Generatelibrarycard extends Admin_Controller
{
    private function hasLibraryCardPermission($action = 'can_view')
    {
        return $this->rbac->hasPrivilege('library_card', $action)
            || $this->rbac->hasPrivilege('issue_return', $action);
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Customlib');
        $this->load->model('Generatelibrarycard_model');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->hasLibraryCardPermission('can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'admin/generatelibrarycard');
        $data['idcardlist'] = $this->Generatelibrarycard_model->getLibraryTemplates();
        $data['member_type'] = 'all';
        $data['print_mode'] = 'card';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/generatelibrarycard/generatelibrarycardview', $data);
        $this->load->view('layout/footer', $data);
    }

    public function search()
    {
        if (!$this->hasLibraryCardPermission('can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'admin/generatelibrarycard');

        $data['idcardlist'] = $this->Generatelibrarycard_model->getLibraryTemplates();
        $data['member_type'] = $this->input->post('member_type');
        $data['print_mode'] = $this->input->post('print_mode') ? $this->input->post('print_mode') : 'card';
        $id_card = $this->input->post('id_card');

        $this->form_validation->set_rules('id_card', $this->lang->line('id_card_template'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('member_type', $this->lang->line('member_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {
            $data['idcardResult'] = $this->Generatelibrarycard_model->getTemplateById($id_card);
            $data['resultlist'] = $this->Generatelibrarycard_model->getLibraryMembers($data['member_type']);
            if (empty($data['resultlist'])) {
                $this->session->set_flashdata('msg', '<div class="alert alert-warning alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><i class="fa fa-warning"></i> ' . $this->lang->line('no_record_found') . '</div>');
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/generatelibrarycard/generatelibrarycardview', $data);
        $this->load->view('layout/footer', $data);
    }

    public function generatemultiple()
    {
        if (!$this->hasLibraryCardPermission('can_add')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array('status' => 0, 'message' => 'Permission denied'));
            return;
        }
        $this->output->set_content_type('application/json');
        try {
            $ids_json = $this->input->post('data');
            $id_card = (int) $this->input->post('id_card');
            $member_type = $this->input->post('member_type');
            $print_mode = $this->input->post('print_mode');
            $print_mode = ($print_mode === 'full') ? 'full' : 'card';

            if (empty($ids_json) || $id_card <= 0) {
                throw new Exception('Missing required input');
            }

            $raw = json_decode($ids_json, true);
            if (!is_array($raw)) {
                throw new Exception('Invalid selection data');
            }

            $selected_map = array();
            foreach ($raw as $row) {
                if (isset($row['lib_member_id'])) {
                    $selected_map[(int) $row['lib_member_id']] = true;
                }
            }
            if (empty($selected_map)) {
                throw new Exception('No members selected');
            }

            $all_members = $this->Generatelibrarycard_model->getLibraryMembers($member_type);
            $members = array();
            foreach ($all_members as $m) {
                if (isset($selected_map[(int) $m['lib_member_id']])) {
                    if (!empty($m['library_card_no'])) {
                        try {
                            $m['barcode'] = $this->customlib->generatestaffbarcode($m['library_card_no']);
                        } catch (Exception $e) {
                            $m['barcode'] = '';
                        }
                    } else {
                        $m['barcode'] = '';
                    }
                    $members[] = (object) $m;
                }
            }
            if (empty($members)) {
                throw new Exception('No matching members found');
            }

            $template = $this->Generatelibrarycard_model->getTemplateById($id_card);
            if (empty($template)) {
                throw new Exception('Card template not found');
            }

            $data = array(
                'id_card'  => $template,
                'members'  => $members,
                'sch_setting' => $this->setting_model->get(),
                'print_mode' => $print_mode,
            );

            $html = $this->load->view('admin/generatelibrarycard/generatemultiplelibrarycard', $data, true);
            echo json_encode(array('status' => 1, 'page' => $html));
        } catch (Exception $e) {
            echo json_encode(array('status' => 0, 'message' => $e->getMessage()));
        }
    }
}

