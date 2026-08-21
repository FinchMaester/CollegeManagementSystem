<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarycard extends Admin_Controller
{
    private function hasLibraryCardPermission($action = 'can_view')
    {
        return $this->rbac->hasPrivilege('library_card', $action)
            || $this->rbac->hasPrivilege('issue_return', $action);
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->model('Library_card_model');
    }

    public function index()
    {
        if (!$this->hasLibraryCardPermission('can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'admin/librarycard');
        $this->data['template_list'] = $this->Library_card_model->templateList();
        $this->load->view('layout/header');
        $this->load->view('admin/librarycard/librarycardView', $this->data);
        $this->load->view('layout/footer');
    }

    private function getTemplatePostData($id = null)
    {
        $vertical_card = $this->input->post('enable_vertical_card') ? 1 : 0;
        $card_width_mm = (float) $this->input->post('card_width_mm');
        $card_height_mm = (float) $this->input->post('card_height_mm');
        if ($card_width_mm <= 0) {
            $card_width_mm = 86.00;
        }
        if ($card_height_mm <= 0) {
            $card_height_mm = 54.00;
        }
        $card_width_mm = max(40, min(150, $card_width_mm));
        $card_height_mm = max(25, min(120, $card_height_mm));

        $offset = function ($field) {
            $value = $this->input->post($field);
            if ($value === null || $value === '') {
                return '0.00';
            }
            $num = (float) $value;
            $num = max(-30, min(30, $num));
            return number_format($num, 2, '.', '');
        };
        $pick = function ($field, $allowed, $default) {
            $value = $this->input->post($field);
            if (!in_array($value, $allowed, true)) {
                return $default;
            }
            return $value;
        };

        $toggle_fields = array(
            'enable_library_card_no' => (int) ($this->input->post('is_active_library_card_no') == 1),
            'enable_member_type'     => (int) ($this->input->post('is_active_member_type') == 1),
            'enable_name'            => (int) ($this->input->post('is_active_name') == 1),
            'enable_admission_no'    => (int) ($this->input->post('is_active_admission_no') == 1),
            'enable_employee_id'     => (int) ($this->input->post('is_active_employee_id') == 1),
            'enable_class'           => (int) ($this->input->post('is_active_class') == 1),
            'enable_section'         => (int) ($this->input->post('is_active_section') == 1),
            'enable_program'         => (int) ($this->input->post('is_active_program') == 1),
            'enable_phone'           => (int) ($this->input->post('is_active_phone') == 1),
            'enable_dob'             => (int) ($this->input->post('is_active_dob') == 1),
            'enable_library_barcode' => (int) ($this->input->post('enable_library_barcode') == 1),
        );

        $data = array(
            'title'                => $this->input->post('title'),
            'school_name'          => $this->input->post('school_name'),
            'school_address'       => $this->input->post('address'),
            'header_color'         => $this->input->post('header_color'),
            'card_width_mm'        => number_format($card_width_mm, 2, '.', ''),
            'card_height_mm'       => number_format($card_height_mm, 2, '.', ''),
            'header_offset_x_mm'   => $offset('header_offset_x_mm'),
            'header_offset_y_mm'   => $offset('header_offset_y_mm'),
            'photo_offset_x_mm'    => $offset('photo_offset_x_mm'),
            'photo_offset_y_mm'    => $offset('photo_offset_y_mm'),
            'details_offset_x_mm'  => $offset('details_offset_x_mm'),
            'details_offset_y_mm'  => $offset('details_offset_y_mm'),
            'signature_offset_x_mm'=> $offset('signature_offset_x_mm'),
            'signature_offset_y_mm'=> $offset('signature_offset_y_mm'),
            'barcode_offset_x_mm'  => $offset('barcode_offset_x_mm'),
            'barcode_offset_y_mm'  => $offset('barcode_offset_y_mm'),
            'header_align'         => $pick('header_align', array('left', 'center', 'right'), 'left'),
            'content_layout'       => $pick('content_layout', array('same_row', 'stacked'), 'same_row'),
            'photo_align'          => $pick('photo_align', array('left', 'center', 'right'), 'left'),
            'details_align'        => $pick('details_align', array('left', 'center', 'right'), 'left'),
            'signature_align'      => $pick('signature_align', array('left', 'center', 'right'), 'right'),
            'barcode_align'        => $pick('barcode_align', array('left', 'center', 'right'), 'right'),
            'enable_vertical_card' => $vertical_card,
            'status'               => 1,
        );

        if (!empty($id)) {
            $data['id'] = (int) $id;
        }

        return array_merge($data, $toggle_fields);
    }

    public function create()
    {
        if (!$this->hasLibraryCardPermission('can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('school_name', $this->lang->line('school_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('address', $this->lang->line('address_phone_email'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('title', $this->lang->line('id_card_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('card_width_mm', 'Card Width (mm)', 'trim|required|numeric');
        $this->form_validation->set_rules('card_height_mm', 'Card Height (mm)', 'trim|required|numeric');
        $this->form_validation->set_rules('header_align', 'Header Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('content_layout', 'Content Layout', 'trim|in_list[same_row,stacked]');
        $this->form_validation->set_rules('photo_align', 'Photo Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('details_align', 'Details Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('signature_align', 'Signature Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('barcode_align', 'Barcode Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('header_offset_x_mm', 'Header X Offset', 'trim|numeric');
        $this->form_validation->set_rules('header_offset_y_mm', 'Header Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('photo_offset_x_mm', 'Photo X Offset', 'trim|numeric');
        $this->form_validation->set_rules('photo_offset_y_mm', 'Photo Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('details_offset_x_mm', 'Details X Offset', 'trim|numeric');
        $this->form_validation->set_rules('details_offset_y_mm', 'Details Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('signature_offset_x_mm', 'Signature X Offset', 'trim|numeric');
        $this->form_validation->set_rules('signature_offset_y_mm', 'Signature Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('barcode_offset_x_mm', 'Barcode X Offset', 'trim|numeric');
        $this->form_validation->set_rules('barcode_offset_y_mm', 'Barcode Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('background_image', $this->lang->line('background_image'), 'callback_handle_upload[background_image]');
        $this->form_validation->set_rules('logo_img', $this->lang->line('logo'), 'callback_handle_upload[logo_img]');
        $this->form_validation->set_rules('sign_image', $this->lang->line('signature'), 'callback_handle_upload[sign_image]');

        if ($this->form_validation->run() == false) {
            return $this->index();
        }

        $data = $this->getTemplatePostData();
        $data['background'] = !empty($_FILES['background_image']['name']) ? $this->media_storage->fileupload("background_image", "./uploads/library_card/background/") : '';
        $data['logo'] = !empty($_FILES['logo_img']['name']) ? $this->media_storage->fileupload("logo_img", "./uploads/library_card/logo/") : '';
        $data['sign_image'] = !empty($_FILES['sign_image']['name']) ? $this->media_storage->fileupload("sign_image", "./uploads/library_card/signature/") : '';

        $this->Library_card_model->saveTemplate($data);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
        redirect('admin/librarycard/index');
    }

    public function edit($id)
    {
        if (!$this->hasLibraryCardPermission('can_edit')) {
            access_denied();
        }

        $edit_template = $this->Library_card_model->get($id);
        if (empty($edit_template)) {
            redirect('admin/librarycard/index');
        }

        $this->data['edit_template'] = $edit_template;
        $this->form_validation->set_rules('school_name', $this->lang->line('school_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('address', $this->lang->line('address_phone_email'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('title', $this->lang->line('id_card_title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('card_width_mm', 'Card Width (mm)', 'trim|required|numeric');
        $this->form_validation->set_rules('card_height_mm', 'Card Height (mm)', 'trim|required|numeric');
        $this->form_validation->set_rules('header_align', 'Header Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('content_layout', 'Content Layout', 'trim|in_list[same_row,stacked]');
        $this->form_validation->set_rules('photo_align', 'Photo Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('details_align', 'Details Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('signature_align', 'Signature Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('barcode_align', 'Barcode Alignment', 'trim|in_list[left,center,right]');
        $this->form_validation->set_rules('header_offset_x_mm', 'Header X Offset', 'trim|numeric');
        $this->form_validation->set_rules('header_offset_y_mm', 'Header Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('photo_offset_x_mm', 'Photo X Offset', 'trim|numeric');
        $this->form_validation->set_rules('photo_offset_y_mm', 'Photo Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('details_offset_x_mm', 'Details X Offset', 'trim|numeric');
        $this->form_validation->set_rules('details_offset_y_mm', 'Details Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('signature_offset_x_mm', 'Signature X Offset', 'trim|numeric');
        $this->form_validation->set_rules('signature_offset_y_mm', 'Signature Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('barcode_offset_x_mm', 'Barcode X Offset', 'trim|numeric');
        $this->form_validation->set_rules('barcode_offset_y_mm', 'Barcode Y Offset', 'trim|numeric');
        $this->form_validation->set_rules('background_image', $this->lang->line('background_image'), 'callback_handle_upload[background_image]');
        $this->form_validation->set_rules('logo_img', $this->lang->line('logo'), 'callback_handle_upload[logo_img]');
        $this->form_validation->set_rules('sign_image', $this->lang->line('signature'), 'callback_handle_upload[sign_image]');

        if ($this->form_validation->run() == false) {
            $this->data['template_list'] = $this->Library_card_model->templateList();
            $this->load->view('layout/header');
            $this->load->view('admin/librarycard/librarycardedit', $this->data);
            $this->load->view('layout/footer');
            return;
        }

        $data = $this->getTemplatePostData($id);
        if ($this->input->post('removebackground_image') != '') {
            $data['background'] = '';
        }
        if ($this->input->post('removelogo_image') != '') {
            $data['logo'] = '';
        }
        if ($this->input->post('removesign_image') != '') {
            $data['sign_image'] = '';
        }

        if (!empty($_FILES['background_image']['name'])) {
            $data['background'] = $this->media_storage->fileupload("background_image", "./uploads/library_card/background/");
            $this->media_storage->filedelete($edit_template[0]->background, "uploads/library_card/background");
        }
        if (!empty($_FILES['logo_img']['name'])) {
            $data['logo'] = $this->media_storage->fileupload("logo_img", "./uploads/library_card/logo/");
            $this->media_storage->filedelete($edit_template[0]->logo, "uploads/library_card/logo");
        }
        if (!empty($_FILES['sign_image']['name'])) {
            $data['sign_image'] = $this->media_storage->fileupload("sign_image", "./uploads/library_card/signature/");
            $this->media_storage->filedelete($edit_template[0]->sign_image, "uploads/library_card/signature");
        }

        $this->Library_card_model->saveTemplate($data);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
        redirect('admin/librarycard/index');
    }

    public function delete($id)
    {
        if (!$this->hasLibraryCardPermission('can_delete')) {
            access_denied();
        }

        $row = $this->Library_card_model->get($id);
        if (!empty($row)) {
            if ($row[0]->background != '') {
                $this->media_storage->filedelete($row[0]->background, "uploads/library_card/background/");
            }
            if ($row[0]->logo != '') {
                $this->media_storage->filedelete($row[0]->logo, "uploads/library_card/logo/");
            }
            if ($row[0]->sign_image != '') {
                $this->media_storage->filedelete($row[0]->sign_image, "uploads/library_card/signature/");
            }
        }

        $this->Library_card_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/librarycard/index');
    }

    public function view()
    {
        if (!$this->hasLibraryCardPermission('can_view')) {
            access_denied();
        }
        $id = $this->input->post('certificateid');
        $data['idcard'] = $this->Library_card_model->templateById($id);
        $this->load->view('admin/librarycard/librarycardpreview', $data);
    }

    public function handle_upload($str, $var)
    {
        $image_validate = $this->config->item('image_validate');
        $result = $this->filetype_model->get();
        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {
            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]['size'];
            $file_name = $_FILES[$var]['name'];
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES[$var]['tmp_name'])) {
                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->image_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed_or_extension_not_allowed'));
                return false;
            }
        }

        return true;
    }
}

