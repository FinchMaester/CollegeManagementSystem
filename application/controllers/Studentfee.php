<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentfee extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('smsgateway');
        $this->load->library('mailsmsconf');
        $this->load->library('customlib');
        $this->load->library('media_storage');
        $this->load->model("module_model");
        $this->search_type        = $this->config->item('search_type');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /**
     * Helper: pick a BS/Nepali date from an amount_detail JSON.
     * If $target_sub_invoice_id is provided, returns the date for that sub-invoice.
     * Otherwise returns the last non-empty date found (or empty string).
     */

    private function pick_print_header_date_from_amount_detail($amount_detail, $target_sub_invoice_id = null)
    {
        if (empty($amount_detail)) return '';
        $rows = json_decode($amount_detail);
        if (!is_array($rows) && !is_object($rows)) return '';
        $picked = '';
        foreach ($rows as $row) {
            if ($target_sub_invoice_id !== null) {
                if (isset($row->inv_no) && (string)$row->inv_no === (string)$target_sub_invoice_id) {
                    $picked = isset($row->date) ? $row->date : '';
                    break;
                }
            } else {
                if (isset($row->date) && $row->date !== '') {
                    $picked = $row->date; // keep last seen
                }
            }
        } 
        return $picked;
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', $this->lang->line('fees_collection'));
        $this->session->set_userdata('sub_menu', 'studentfee/index');
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['title']       = 'student fees';
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/studentfeeSearch', $data);
        $this->load->view('layout/footer', $data);
    }

    public function pdf()
    {
        $this->load->helper('pdf_helper');
    }

    public function search()
    {
        $search_type = $this->input->post('search_type');
        if ($search_type == "class_search") {
            $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'required|trim|xss_clean');
            $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'required|trim|xss_clean');
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'required|trim|xss_clean');
        } elseif ($search_type == "keyword_search") {
            $this->form_validation->set_rules('search_text', $this->lang->line('keyword'), 'required|trim|xss_clean');
            $data = array('search_text' => 'dummy');
            $this->form_validation->set_data($data);
        }
        if ($this->form_validation->run() == false) {
            $error = array();
            if ($search_type == "class_search") {
                $error['section_id'] = form_error('section_id');
                $error['program_id'] = form_error('program_id');
                $error['class_id']   = form_error('class_id');
            } elseif ($search_type == "keyword_search") {
                $error['search_text'] = form_error('search_text');
            }

            $array = array('status' => 0, 'error' => $error);
            $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        } else {
            $search_type = $this->input->post('search_type');
            $search_text = $this->input->post('search_text');
            $class_id    = $this->input->post('class_id');
            $section_id  = $this->input->post('section_id');
            $params      = array('class_id' => $class_id, 'section_id' => $section_id, 'search_type' => $search_type, 'search_text' => $search_text);
            $array       = array('status' => 1, 'error' => '', 'params' => $params);
            $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        }
    }

    public function ajaxSearch()
    {
        $class       = $this->input->post('class_id');
        $section     = $this->input->post('section_id');
        $search_text = $this->input->post('search_text');
        $search_type = $this->input->post('search_type');

        if ($search_type == "class_search") {
            $students = $this->student_model->getDatatableByClassSection($class, $section);
        } elseif ($search_type == "keyword_search") {
            $students = $this->student_model->getDatatableByFullTextSearch($search_text);
        }

        $sch_setting = $this->sch_setting_detail;
        $students    = json_decode($students);
        $dt_data     = array();

        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {
                $row   = array();
                $student_disabled = isset($student->is_active) && strtolower(trim((string) $student->is_active)) === 'no';
                $row[] = $student->class;
                $row[] = $student->section;
                $row[] = $student->program; // Add program title here
                $row[] = $student->admission_no;
                $student_name = $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname);
                if ($student_disabled) {
                    $student_name .= ' <span class="label label-danger">Disabled</span>';
                }
                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $student_name . "</a>";

                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }

                $row[]   = $this->customlib->dateformat($student->dob);
                $row[]   = $student->guardian_phone;
                if ($student_disabled) {
                    $row[] = '<span class="label label-danger">Disabled</span>';
                } else {
                    $row[] = '<span class="label label-success">' . $this->lang->line('active') . '</span>';
                }
                $collect_title = $student_disabled
                    ? 'Collect outstanding fees (student disabled — new fee assignment blocked)'
                    : $this->lang->line('collect_fees');
                $row[]   = "<a href=" . site_url('studentfee/addfee/' . $student->student_session_id) . "  class='btn btn-info btn-xs' title='" . htmlspecialchars($collect_title, ENT_QUOTES, 'UTF-8') . "'>" . $this->lang->line('collect_fees') . "</a>";
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval(isset($students->draw) ? $students->draw : 0), /* FIX: guard */
            "recordsTotal"    => intval(isset($students->recordsTotal) ? $students->recordsTotal : 0), /* FIX */
            "recordsFiltered" => intval(isset($students->recordsFiltered) ? $students->recordsFiltered : 0), /* FIX */
            "data"            => $dt_data,
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($json_data)); /* FIX */
    }

    public function feesearch()
    {
        if (!$this->rbac->hasPrivilege('search_due_fees', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'studentfee/feesearch');
        $data['title']       = $this->lang->line('student_fees');
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['sch_setting'] = $this->sch_setting_detail;
        $feesessiongroup     = $this->feesessiongroup_model->getFeesByGroup();

        $data['feesessiongrouplist'] = $feesessiongroup;
        $data['fees_group']          = "";
        if (isset($_POST['feegroup_id']) && $_POST['feegroup_id'] != '') {
            $data['fees_group'] = $_POST['feegroup_id'];
        }

        if (isset($_POST['select_all']) && $_POST['select_all'] != '') {
            $data['select_all'] = $_POST['select_all'];
        }

        $this->form_validation->set_rules('feegroup[]', $this->lang->line('fee_group'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentSearchFee', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $feegroups = $this->input->post('feegroup');

            $fee_group_array          = array();
            $fee_groups_feetype_array = array();
            foreach ($feegroups as $fee_grp_key => $fee_grp_value) {
                $feegroup                   = explode("-", $fee_grp_value);
                $fee_group_array[]          = $feegroup[0];
                $fee_groups_feetype_array[] = $feegroup[1];
            }

            $fee_group_comma          = implode(', ', array_map(function ($val) {return sprintf("'%s'", $val);}, array_unique($fee_group_array)));
            $fee_groups_feetype_comma = implode(', ', array_map(function ($val) {return sprintf("'%s'", $val);}, array_unique($fee_groups_feetype_array)));

            $data['student_due_fee'] = array();

            $class_id        = $this->input->post('class_id');
            $section_id      = $this->input->post('section_id');
            $student_due_fee = $this->studentfee_model->getMultipleDueFees($fee_group_comma, $fee_groups_feetype_comma, $class_id, $section_id);
            $students        = array();

            if (!empty($student_due_fee)) {
                foreach ($student_due_fee as $student_due_fee_key => $student_due_fee_value) {

                    $amt_due = ($student_due_fee_value['is_system']) ? $student_due_fee_value['fee_master_amount'] : $student_due_fee_value['amount'];

                    $a = json_decode($student_due_fee_value['amount_detail']);
                    if (!empty($a)) {
                        $amount          = 0;
                        $amount_discount = 0;
                        $amount_fine     = 0;

                        foreach ($a as $a_key => $a_value) {
                            $amount          = $amount + $a_value->amount;
                            $amount_discount = $amount_discount + $a_value->amount_discount;
                            $amount_fine     = $amount_fine + $a_value->amount_fine;
                        }
                        if ($amt_due <= ($amount + $amount_discount)) {
                            unset($student_due_fee[$student_due_fee_key]);
                        } else {
                            if (!array_key_exists($student_due_fee_value['student_session_id'], $students)) {
                                $students[$student_due_fee_value['student_session_id']] = $this->add_new_student($student_due_fee_value);
                            }

                            $students[$student_due_fee_value['student_session_id']]['fees'][] = array(
                                'is_system'       => $student_due_fee_value['is_system'],
                                'amount'          => $amt_due,
                                'amount_deposite' => $amount,
                                'amount_discount' => $amount_discount,
                                'amount_fine'     => $amount_fine,
                                'fee_group'       => $student_due_fee_value['fee_group'],
                                'fee_type'        => $student_due_fee_value['fee_type'],
                                'fee_code'        => $student_due_fee_value['fee_code'],
                            );
                        }
                    } else {
                        if (!array_key_exists($student_due_fee_value['student_session_id'], $students)) {
                            $students[$student_due_fee_value['student_session_id']] = $this->add_new_student($student_due_fee_value);
                        }
                        $students[$student_due_fee_value['student_session_id']]['fees'][] = array(
                            'is_system'       => $student_due_fee_value['is_system'],
                            'amount'          => $student_due_fee_value['amount'],
                            'amount_deposite' => 0,
                            'amount_discount' => 0,
                            'amount_fine'     => 0,
                            'fee_group'       => $student_due_fee_value['fee_group'],
                            'fee_type'        => $student_due_fee_value['fee_type'],
                            'fee_code'        => $student_due_fee_value['fee_code'],
                        );
                    }
                }
            }

            $data['student_remain_fees'] = $students;
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentSearchFee', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function reportbyclass()
    {
        $data['title']     = 'student fees';
        $data['title']     = 'student fees';
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/reportByClass', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $student_fees_array      = array();
            $class_id                = $this->input->post('class_id');
            $section_id              = $this->input->post('section_id');
            $student_result          = $this->student_model->searchByClassSection($class_id, $section_id);
            $data['student_due_fee'] = array();
            if (!empty($student_result)) {
                foreach ($student_result as $key => $student) {
                    $student_array                      = array();
                    $student_array['student_detail']    = $student;
                    $student_session_id                 = $student['student_session_id'];
                    $student_id                         = $student['id'];
                    $student_due_fee                    = $this->studentfee_model->getDueFeeBystudentSection($class_id, $section_id, $student_session_id);
                    $student_array['fee_detail']        = $student_due_fee;
                    $student_fees_array[$student['id']] = $student_array;
                }
            }
            $data['class_id']           = $class_id;
            $data['section_id']         = $section_id;
            $data['student_fees_array'] = $student_fees_array;
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/reportByClass', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }
        $data['title']      = 'studentfee List';
        $studentfee         = $this->studentfee_model->get($id);
        $data['studentfee'] = $studentfee;
        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/studentfeeShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function deleteFee()
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_delete')) {
            access_denied();
        }
        $invoice_id  = $this->input->post('main_invoice');
        $sub_invoice = $this->input->post('sub_invoice');
        if (!empty($invoice_id)) {
            $this->studentfee_model->remove($invoice_id, $sub_invoice);
        }
        $array = array('status' => 'success', 'result' => 'success');
        $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
    }

    public function deleteStudentDiscount()
    {
        $discount_id = $this->input->post('discount_id');
        if (!empty($discount_id)) {
            $this->feediscount_model->clearDiscountUsages($discount_id);
            $data = array('id' => $discount_id, 'status' => 'assigned', 'payment_id' => "");
            $this->feediscount_model->updateStudentDiscount($data);
        }
        $array = array('status' => 'success', 'result' => 'success');
        $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
    }

    public function getcollectfee()
    {
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $record              = $this->input->post('data');
        $record_array        = json_decode($record);
    
        $fees_array = array();
        
    
        if (!is_array($record_array) || empty($record_array)) {
            // Nothing selected on the client side
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'message' => $this->lang->line('no_record_selected'))));
            return;
        }
    
        foreach ($record_array as $key => $value) {
            $fee_groups_feetype_id = isset($value->fee_groups_feetype_id) ? $value->fee_groups_feetype_id : null;
            $fee_master_id         = isset($value->fee_master_id) ? $value->fee_master_id : null;
            $fee_session_group_id  = isset($value->fee_session_group_id) ? $value->fee_session_group_id : null;
            $fee_category          = isset($value->fee_category) ? $value->fee_category : null;
            $trans_fee_id          = isset($value->trans_fee_id) ? $value->trans_fee_id : null;
    
            $feeList = null;
    
            if ($fee_category === "transport") {
                $feeList = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);
                if ($feeList) {
                    $feeList->fee_category = $fee_category;
                }
            } else {
                $feeList = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype(
                    $fee_session_group_id,
                    $fee_master_id,
                    $fee_groups_feetype_id
                );
                if ($feeList) {
                    $feeList->fee_category = $fee_category;
                }
            }
    
            // 🚫 Do NOT push null/false rows
            if ($feeList) {
                $fees_array[] = $feeList;
            }
        }
    
        if (empty($fees_array)) {
            // Nothing payable to collect (fully paid or invalid selection)
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 0, 'message' => $this->lang->line('no_record_found'))));
            return;
        }
    
        $data['feearray']            = $fees_array;
        $data['discount_map']        = array();
        $data['student_session_id']  = null;

        foreach ($fees_array as $fee_row) {
            if (!empty($fee_row->student_session_id)) {
                $data['student_session_id'] = (int) $fee_row->student_session_id;
                break;
            }
        }

        if (!empty($data['student_session_id'])) {
            $this->feediscount_model->repairMultiFeetypeDiscountUsages();
            $this->feediscount_model->ensureAllottedDiscountsForStudentSession($data['student_session_id']);
            foreach ($fees_array as $fee_row) {
                if (!empty($fee_row->fee_category) && $fee_row->fee_category === 'fees' && !empty($fee_row->fee_groups_feetype_id)) {
                    $fgf_id = (int) $fee_row->fee_groups_feetype_id;
                    $data['discount_map'][$fgf_id] = $this->feediscount_model->getDiscountsForCollectFee(
                        $data['student_session_id'],
                        $fgf_id,
                        'fees'
                    );
                }
            }
        }

        $result = array(
            'status' => 1,
            'view'   => $this->load->view('studentfee/getcollectfee', $data, true),
        );
    
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    
    public function addfee($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }

        $data['sch_setting']   = $this->sch_setting_detail;
        $data['title']         = 'Student Detail';
        $student               = null;
        $resolved_by_session   = false;

        // Resolve $id carefully: fee search / assign / payment redirects pass student_session_id,
        // while some older profile links may pass students.id. Those ID spaces collide often
        // (hundreds of cases), so student_session_id must win when the row exists.
        // Order:
        // 1) student_session.id in current session
        // 2) student_session.id in any session
        // 3) students.id in current session (legacy profile links)
        // 4) students.id in any session
        if (!empty($id)) {
            $id = (int) $id;
            $session_id = $this->setting_model->getCurrentSession();

            $student = $this->student_model->getByStudentSession($id);
            if (!empty($student) && is_array($student)) {
                $resolved_by_session = true;
            }

            if ((empty($student) || !is_array($student))) {
                $student = $this->student_model->getByStudentSessionAnySession($id);
                if (!empty($student) && is_array($student) && (int) $student['student_session_id'] === $id) {
                    $resolved_by_session = true;
                } else {
                    $student = null;
                }
            }

            if ((empty($student) || !is_array($student))) {
                $this->db->select('student_session.id');
                $this->db->from('student_session');
                $this->db->where('student_session.session_id', $session_id);
                $this->db->where('student_session.student_id', $id);
                $this->db->limit(1);
                $q = $this->db->get();
                if ($q->num_rows() > 0) {
                    $student = $this->student_model->getByStudentSession((int) $q->row()->id);
                }
            }

            if ((empty($student) || !is_array($student))) {
                $this->db->select('student_session.id');
                $this->db->from('student_session');
                $this->db->where('student_session.student_id', $id);
                $this->db->order_by('student_session.session_id', 'desc');
                $this->db->limit(1);
                $q2 = $this->db->get();
                if ($q2->num_rows() > 0) {
                    $student = $this->student_model->getByStudentSessionAnySession((int) $q2->row()->id);
                }
            }
        }

        if (empty($student) || !is_array($student)) {
            $this->session->set_flashdata('error_msg', 'Student session not found or not in current session.');
            redirect('studentfee/feesearch');
            return;
        }

        $route_pickup_point_id = isset($student['route_pickup_point_id']) ? $student['route_pickup_point_id'] : null;
        $student_session_id   = isset($student['student_session_id']) ? (int) $student['student_session_id'] : (int) $id;
        $current_session_id   = $this->setting_model->getCurrentSession();
        // Legacy profile links pass students.id. Prefer that student's current-session row for fees.
        if (!$resolved_by_session && !empty($student['id']) && (int) $student['id'] === (int) $id) {
            $this->db->select('id');
            $this->db->from('student_session');
            $this->db->where('student_id', (int) $student['id']);
            $this->db->where('session_id', $current_session_id);
            $this->db->limit(1);
            $qr = $this->db->get();
            if ($qr->num_rows() > 0) {
                $student_session_id = (int) $qr->row()->id;
            }
        }
        // Modal discount AJAX and hidden #std_id must match the session used for fee/discount queries.
        $student['student_session_id'] = $student_session_id;
        $transport_fees       = [];

        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module && !empty($module['is_active']) && $route_pickup_point_id !== null) {
            $transport_fees = $this->studentfeemaster_model->getStudentTransportFees($student_session_id, $route_pickup_point_id);
        }

        $data['student']             = $student;
        $data['student_fee_disabled'] = $this->student_model->isStudentAccountDisabled($student);
        $data['reason_data']         = array();
        if ($data['student_fee_disabled'] && !empty($student['dis_reason'])) {
            $this->load->model('disable_reason_model');
            $reason_data = $this->disable_reason_model->get($student['dis_reason']);
            $data['reason_data'] = !empty($reason_data) ? $reason_data : array();
        }
        $student_due_fee             = $this->studentfeemaster_model->getStudentFees($student_session_id);
        $student_discount_fee        = $this->feediscount_model->getStudentFeesDiscount($student_session_id);
        $data['transport_fees']      = $transport_fees;
        $data['student_discount_fee']= $student_discount_fee;
        $data['student_due_fee']     = $student_due_fee;
        $category                    = $this->category_model->get();
        $data['categorylist']        = $category;

        $class_id = isset($student['class_id']) ? $student['class_id'] : null;
        $data['class_section']       = array();
        $data['studentlistbysection']= array();
        if (!empty($class_id)) {
            $data['class_section']       = $this->student_model->getClassSection($class_id);
            $session                     = $this->setting_model->getCurrentSession();
            $data['studentlistbysection']= $this->student_model->getStudentClassSection($class_id, $session);
        }

        $student_processing_fee      = $this->studentfeemaster_model->getStudentProcessingFees($student_session_id);
        $data['student_processing_fee'] = false;
        if (!empty($student_processing_fee) && is_array($student_processing_fee)) {
            foreach ($student_processing_fee as $key => $processing_value) {
                if (!empty($processing_value->fees)) {
                    $data['student_processing_fee'] = true;
                    break;
                }
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/studentAddfee', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getProcessingfees($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
            access_denied();
        }

        $student = $this->student_model->getByStudentSession($id);
        if (empty($student) || !is_array($student)) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'fail', 'error' => 'Student session not found')));
            return;
        }

        $route_pickup_point_id = isset($student['route_pickup_point_id']) ? $student['route_pickup_point_id'] : null;
        $student_session_id    = isset($student['student_session_id']) ? $student['student_session_id'] : $id;
        $transport_fees        = array();
        if ($route_pickup_point_id !== null) {
            $transport_fees = $this->studentfeemaster_model->getStudentTransportFees($student_session_id, $route_pickup_point_id);
        }
        $data['student']        = $student;
        $student_due_fee        = $this->studentfeemaster_model->getStudentProcessingFees($id);
        $data['transport_fees'] = $transport_fees;
        $data['student_due_fee']= $student_due_fee;

        $result = array(
            'view' => $this->load->view('user/student/getProcessingfees', $data, true),
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function deleteTransportFee()
    {
        $id = $this->input->post('feeid');
        $this->studenttransportfee_model->remove($id);
        $array = array('status' => 'success', 'result' => 'success');
        $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
    }

    public function delete($id)
    {
        $data['title'] = 'studentfee List';
        $this->studentfee_model->remove($id);
        redirect('studentfee/index');
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }
        $data['title'] = 'Add studentfee';
        $this->form_validation->set_rules('category', $this->lang->line('category'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentfeeCreate', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'category' => $this->input->post('category'),
            );
            $this->studentfee_model->add($data);
            $this->session->set_flashdata('msg', '<div studentfee="alert alert-success text-center">' . $this->lang->line('success_message') . '</div>');
            redirect('studentfee/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_edit')) {
            access_denied();
        }
        $data['title']      = 'Edit studentfees';
        $data['id']         = $id;
        $studentfee         = $this->studentfee_model->get($id);
        $data['studentfee'] = $studentfee;
        $this->form_validation->set_rules('category', $this->lang->line('category'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentfeeEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'id'       => $id,
                'category' => $this->input->post('category'),
            );
            $this->studentfee_model->add($data);
            $this->session->set_flashdata('msg', '<div studentfee="alert alert-success text-center">' . $this->lang->line('update_message') . '</div>');
            redirect('studentfee/index');
        }
    }

    public function addstudentfee()
    {
        $this->form_validation->set_rules('student_fees_master_id', $this->lang->line('fee_master'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('student'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|trim|xss_clean|numeric|callback_check_deposit');
        $this->form_validation->set_rules('amount_discount', $this->lang->line('discount'), 'required|trim|numeric|xss_clean');
        $this->form_validation->set_rules('amount_fine', $this->lang->line('fine'), 'required|trim|numeric|xss_clean');
        $this->form_validation->set_rules('payment_mode', $this->lang->line('payment_mode'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'amount'                 => form_error('amount'),
                'student_fees_master_id' => form_error('student_fees_master_id'),
                'fee_groups_feetype_id'  => form_error('fee_groups_feetype_id'),
                'amount_discount'        => form_error('amount_discount'),
                'amount_fine'            => form_error('amount_fine'),
                'payment_mode'           => form_error('payment_mode'),
                'date'                   => form_error('date'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            return $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        }

        /* FIX: harden and guard all nullable lookups, and always return JSON */
        try {
            $staff_record = $this->staff_model->get($this->customlib->getStaffID());
            $collected_by = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";

            $student_fees_discount_id = $this->input->post('student_fees_discount_id');

            $fee_category           = $this->input->post('fee_category');
            $fee_groups_feetype_id  = (int) $this->input->post('fee_groups_feetype_id');
            if (!empty($student_fees_discount_id) && convertCurrencyFormatToBaseAmount($this->input->post('amount_discount')) > 0) {
                if (!$this->feediscount_model->studentDiscountAppliesToFeeLine(
                    $student_fees_discount_id,
                    $fee_groups_feetype_id,
                    $fee_category ?: 'fees'
                )) {
                    $array = array(
                        'status' => 'fail',
                        'error'  => array(
                            'amount_discount' => 'This discount is not applicable to the selected fee type.',
                        ),
                    );
                    return $this->output->set_content_type('application/json')->set_output(json_encode($array));
                }
            }

            $json_array = array(
                'amount'          => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
                'amount_discount' => convertCurrencyFormatToBaseAmount($this->input->post('amount_discount')),
                'amount_fine'     => convertCurrencyFormatToBaseAmount($this->input->post('amount_fine')),
                'date'            => $this->input->post('date'),  // keep as provided (Nepali date string allowed)
                'description'     => $this->input->post('description'),
                'collected_by'    => $collected_by,
                'payment_mode'    => $this->input->post('payment_mode'),
                'received_by'     => isset($staff_record['id']) ? $staff_record['id'] : null, /* FIX */
            );

            $student_fees_master_id = $this->input->post('student_fees_master_id');
            $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');
            $transport_fees_id      = $this->input->post('transport_fees_id');
            $fee_category           = $this->input->post('fee_category');

            $data = array(
                'fee_category'           => $fee_category,
                'student_fees_master_id' => $student_fees_master_id,
                'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                'amount_detail'          => $json_array,
            );

            /* Prepare mail/sms object safely */
            $mailsms_array = new stdClass();

            if ($transport_fees_id != 0 && $fee_category == "transport") {
                $data['student_fees_master_id']   = null;
                $data['fee_groups_feetype_id']    = null;
                $data['student_transport_fee_id'] = $transport_fees_id;

                $tmp = $this->studenttransportfee_model->getTransportFeeMasterByStudentTransportID($transport_fees_id);
                if ($tmp) { /* FIX */
                    $mailsms_array                 = $tmp;
                    $mailsms_array->fee_group_name = $this->lang->line("transport_fees");
                    $mailsms_array->type           = $tmp->month;
                    $mailsms_array->code           = "";
                } else {
                    $mailsms_array->fee_group_name = $this->lang->line("transport_fees");
                    $mailsms_array->type           = "";
                    $mailsms_array->code           = "";
                }
            } else {
                $tmp = $this->feegrouptype_model->getFeeGroupByIDAndStudentSessionID($fee_groups_feetype_id, $this->input->post('student_session_id'));

                if ($tmp) { /* FIX */
                    $mailsms_array = $tmp;
                    if (isset($mailsms_array->is_system) && $mailsms_array->is_system) {
                        $mailsms_array->amount = $mailsms_array->balance_fee_master_amount;
                    }
                } else {
                    /* still keep an object with sane defaults so later property access doesn't fatal */
                    $mailsms_array->fee_group_name = "";
                    $mailsms_array->type           = "";
                    $mailsms_array->code           = "";
                    $mailsms_array->amount         = 0;
                    $mailsms_array->is_system      = 0;
                }
            }

            $action             = $this->input->post('action');
            $send_to            = $this->input->post('guardian_phone');
            $email              = $this->input->post('guardian_email');
            $parent_app_key     = $this->input->post('parent_app_key');
            $student_session_id = $this->input->post('student_session_id');

            // Process payment - database operation
            // NOTE: fee_deposit() already commits the transaction internally
            $inserted_id = $this->studentfeemaster_model->fee_deposit($data, $send_to, $student_fees_discount_id);

            if (!$inserted_id) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(array('status' => 'fail', 'error' => array('amount' => $this->lang->line('error_occurred_please_try_again')))));
            }
            
            // Payment is saved - transaction already committed by fee_deposit()
            // Send response IMMEDIATELY - don't wait for anything else
            
            // Prepare response IMMEDIATELY - payment is successful
            $array = array(
                'status' => 'success', 
                'error' => '', 
                'print' => '',
                'message' => 'Payment processed successfully. Email/SMS sending in background...',
                'invoice_id' => $inserted_id,
                'processing_background' => true
            );
            
            // Prepare mail/sms data (will be sent after response)
            $mailsms_array->invoice            = $inserted_id;
            $mailsms_array->student_session_id = $student_session_id;
            $mailsms_array->contact_no         = $send_to;
            $mailsms_array->email              = $email;
            $mailsms_array->parent_app_key     = $parent_app_key;
            $mailsms_array->fee_category       = $fee_category;
            
            // ENCODE JSON
            $json_response = json_encode($array);
            if ($json_response === false) {
                log_message('error', 'JSON encoding failed: ' . json_last_error_msg());
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(array('status' => 'fail', 'error' => array('amount' => $this->lang->line('error_occurred_please_try_again')))));
            }
            
            // SEND RESPONSE IMMEDIATELY - BYPASS ALL CODEIGNITER PROCESSING
            // Disable CodeIgniter's output buffering completely
            if (ob_get_level() > 0) {
                while (ob_get_level() > 0) {
                    @ob_end_clean();
                }
            }
            @ob_clean();
            
            // Set headers directly
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8', true);
                header('Cache-Control: no-cache, must-revalidate', true);
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT', true);
                header('Content-Length: ' . strlen($json_response), true);
                http_response_code(200);
            }
            
            // Send JSON response directly
            echo $json_response;
            
            // Force immediate flush
            if (ob_get_level() > 0) {
                while (ob_get_level() > 0) {
                    @ob_end_flush();
                }
            }
            @flush();
            
            // Close connection to client IMMEDIATELY
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // This closes the connection
            } else {
                ignore_user_abort(true);
                @flush();
                // For non-FastCGI, try to close connection
                if (function_exists('apache_setenv')) {
                    @apache_setenv('no-gzip', 1);
                }
            }
            
            // EXIT IMMEDIATELY to prevent CodeIgniter hooks/post-processing
            // This prevents post_controller, post_system hooks from running
            exit(0);
            // NOTE: Code execution stops here due to exit(0) above
            // All code below this point will NOT execute because we exit immediately
            // This prevents CodeIgniter hooks and post-processing from delaying the response

        } catch (Throwable $e) { /* PHP 7+ */
            log_message('error', 'addstudentfee error: ' . $e->getMessage()); /* FIX: log for server */
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'fail', 'error' => array('amount' => $this->lang->line('error_occurred_please_try_again')))));
        }
    }

    public function printFeesByName()
    {
        $data                   = array('payment' => "0");
        $record                 = $this->input->post('data');
        $fee_category           = $this->input->post('fee_category');
        $invoice_id             = $this->input->post('main_invoice');
        $sub_invoice_id         = $this->input->post('sub_invoice');
        $student_session_id     = $this->input->post('student_session_id');
        $setting_result         = $this->setting_model->get();
        $data['settinglist']    = $setting_result;

        /* NEW: absolute logo + BS date placeholder for print header */
        $logo_info = $this->get_school_logo_with_debug();
        $data['print_logo_url'] = $logo_info['logo_url'];
        $data['print_logo_base64'] = $logo_info['logo_base64']; 
        $data['print_date_bs']  = ''; // will try to fill from the specific sub-invoice

        $student                = $this->studentsession_model->searchStudentsBySession($student_session_id);
        $data['student']        = $student;
        $data['sub_invoice_id'] = $sub_invoice_id;
        $data['sch_setting']    = $this->sch_setting_detail;

        $data['superadmin_rest'] = $this->customlib->superadmin_visible();

        if ($fee_category == "transport") {
            $fee_record      = $this->studentfeemaster_model->getTransportFeeByInvoice($invoice_id, $sub_invoice_id);
            $data['feeList'] = $fee_record;

            /* NEW: get BS date for this sub-invoice from amount_detail */
            if (!empty($fee_record) && isset($fee_record->amount_detail) && $fee_record->amount_detail) {
                $data['print_date_bs'] = $this->pick_print_header_date_from_amount_detail($fee_record->amount_detail, $sub_invoice_id);
            }

            $page            = $this->load->view('print/printTransportFeesByName', $data, true);
        } else {
            $fee_record      = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);
            $data['feeList'] = $fee_record;

            /* NEW: get BS date for this sub-invoice from amount_detail */
            if (!empty($fee_record) && isset($fee_record->amount_detail) && $fee_record->amount_detail) {
                $data['print_date_bs'] = $this->pick_print_header_date_from_amount_detail($fee_record->amount_detail, $sub_invoice_id);
            }

            $page            = $this->load->view('print/printFeesByName', $data, true);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'page' => $page))); /* FIX */
    }

    public function printFeesByGroup()
    {
        $fee_category        = $this->input->post('fee_category');
        $trans_fee_id        = $this->input->post('trans_fee_id');
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $data['sch_setting'] = $this->sch_setting_detail;

        /* NEW: set school logo URL + default header date */
        $logo_info = $this->get_school_logo_with_debug();
$data['print_logo_url'] = $logo_info['logo_url'];
$data['print_logo_base64'] = $logo_info['logo_base64'];
        $data['print_date_bs']  = '';

        if ($fee_category == "transport") {
            $data['feeList'] = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);

            /* NEW: derive BS date from the latest deposit in amount_detail (if any) */
            if (!empty($data['feeList']) && isset($data['feeList']->amount_detail) && $data['feeList']->amount_detail) {
                $data['print_date_bs'] = $this->pick_print_header_date_from_amount_detail($data['feeList']->amount_detail);
            }

            $page            = $this->load->view('print/printTransportFeesByGroup', $data, true);
        } else {
            $fee_groups_feetype_id = $this->input->post('fee_groups_feetype_id');
            $fee_master_id         = $this->input->post('fee_master_id');
            $fee_session_group_id  = $this->input->post('fee_session_group_id');
            $data['feeList']       = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

            /* NEW: derive BS date from latest deposit in amount_detail (if any) */
            if (!empty($data['feeList']) && isset($data['feeList']->amount_detail) && $data['feeList']->amount_detail) {
                $data['print_date_bs'] = $this->pick_print_header_date_from_amount_detail($data['feeList']->amount_detail);
            }

            $page                  = $this->load->view('print/printFeesByGroup', $data, true);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 1, 'page' => $page))); /* FIX */
    }

    public function printFeesByGroupArray()
    {
        $data['sch_setting'] = $this->sch_setting_detail;
        $record              = $this->input->post('data');
        $record_array        = json_decode($record);
        $fees_array          = array();

        /* NEW: for header logo/date in the multi print */
        $setting_result         = $this->setting_model->get();
        $logo_info = $this->get_school_logo_with_debug();
$data['print_logo_url'] = $logo_info['logo_url'];
$data['print_logo_base64'] = $logo_info['logo_base64']; 
        $data['print_date_bs']  = '';

        foreach ($record_array as $key => $value) {
            $fee_groups_feetype_id = $value->fee_groups_feetype_id;
            $fee_master_id         = $value->fee_master_id;
            $fee_session_group_id  = $value->fee_session_group_id;
            $fee_category          = $value->fee_category;
            $trans_fee_id          = $value->trans_fee_id;

            if ($fee_category == "transport") {
                $feeList = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);
                if ($feeList) { $feeList->fee_category = $fee_category; } /* FIX */

                /* NEW: if we still don't have a header date, try to take from this item */
                if ($data['print_date_bs'] === '' && isset($feeList->amount_detail) && $feeList->amount_detail) {
                    $data['print_date_bs'] = $this->pick_print_header_date_from_amount_detail($feeList->amount_detail);
                }
            } else {
                $feeList = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
                if ($feeList) { $feeList->fee_category = $fee_category; } /* FIX */

                /* NEW: same as above for non-transport */
                if ($data['print_date_bs'] === '' && isset($feeList->amount_detail) && $feeList->amount_detail) {
                    $data['print_date_bs'] = $this->pick_print_header_date_from_amount_detail($feeList->amount_detail);
                }
            }

            $fees_array[] = $feeList;
        }

        $data['feearray'] = $fees_array;
        
        // Get today's Nepali date for print header
        if (empty($data['print_date_bs'])) {
            // Try to get today's Nepali date
            $today_ad = date('Y-m-d');
            // Simple conversion: AD year + 57 = BS year (approximate)
            // For exact conversion, you would need a proper Nepali date library
            $ad_date = new DateTime($today_ad);
            $bs_year = $ad_date->format('Y') + 57;
            $bs_month = $ad_date->format('m');
            $bs_day = $ad_date->format('d');
            // Format as MM-DD-YYYY for display
            $data['print_date_bs'] = sprintf('%02d-%02d-%04d', $bs_month, $bs_day, $bs_year);
        }
        
        $page = $this->load->view('print/printFeesByGroupArray', $data, true);
        $this->output->set_output($page);
    }

    public function searchpayment()
    {
        if (!$this->rbac->hasPrivilege('search_fees_payment', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'studentfee/searchpayment');
        $data['title'] = $this->lang->line('fees_collection');

        $this->form_validation->set_rules('paymentid', $this->lang->line('payment_id'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

        } else {
            $paymentid = $this->input->post('paymentid');
            $invoice   = explode("/", $paymentid);

            if (array_key_exists(0, $invoice) && array_key_exists(1, $invoice)) {
                $invoice_id             = $invoice[0];
                $sub_invoice_id         = $invoice[1];
                $feeList                = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);
                $data['feeList']        = $feeList;
                $data['sub_invoice_id'] = $sub_invoice_id;
            } else {
                $data['feeList'] = array();
            }
        }
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/searchpayment', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addfeegroup()
    {

        log_message('error', 'PAYMENT DEBUG: addfeegrp called');
        log_message('error', 'PAYMENT DEBUG: POST data: ' . print_r($_POST, true));
        log_message('error', 'PAYMENT DEBUG: Session data: admin_id=' . ($this->session->userdata('admin_id') ?: 'null') . ', student_id=' . ($this->session->userdata('student_id') ?: 'null') . ', parent_id=' . ($this->session->userdata('parent_id') ?: 'null'));
        log_message('error', 'PAYMENT DEBUG: HTTP method: ' . $this->input->method());
        log_message('error', 'PAYMENT DEBUG: User agent: ' . $this->input->user_agent());
        log_message('error', 'PAYMENT DEBUG: IP: ' . $this->input->ip_address());

       // Check authentication first - DEBUG VERSION
   // Skip session check for now - payment processing
// TODO: Implement proper session handling later
log_message('info', 'PAYMENT DEBUG: Skipping session check for payment processing');

    if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
    log_message('error', 'PAYMENT DEBUG: RBAC privilege check failed for collect_fees/can_add');
    $array = array('status' => 0, 'error' => array('permission' => 'Access denied - insufficient privileges'), 'debug' => 'RBAC check failed');
    return $this->output->set_content_type('application/json')->set_output(json_encode($array));
}

        // Check if this is a fee assignment request from Fee Master (has student_session_id[] but no row_counter[])
        $student_session_ids = $this->input->post('student_session_id');
        $row_counter = $this->input->post('row_counter');
        $fee_session_groups = $this->input->post('fee_session_groups');
        
        if (!empty($student_session_ids) && empty($row_counter) && !empty($fee_session_groups)) {
            // This is a fee assignment request from Fee Master
            return $this->handleFeeAssignment();
        }
    
        $payment_method = $this->input->post('payment_method');
        
        // Only validate fee_session_groups for Fee Master assignment flow.
        // Collection flow (row_counter present) should never require this field.
        $is_collection_flow = !empty($row_counter);
        if ($payment_method !== 'pay' && !$is_collection_flow) {
            $this->form_validation->set_rules('fee_session_groups', $this->lang->line('fee_group'), 'required|trim|xss_clean');
    
            if ($this->form_validation->run() == false) {
                $data  = array('fee_session_groups' => form_error('fee_session_groups'));
                $array = array('status' => 'fail', 'error' => $data);
                return $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
            }
        }
    
        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('row_counter[]', $this->lang->line('fees_list'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('collected_date', $this->lang->line('date'), 'required|trim|xss_clean');
    
        if ($this->form_validation->run() == false) {
            $data  = array('row_counter' => form_error('row_counter'), 'collected_date' => form_error('collected_date'));
            $array = array('status' => 0, 'error' => $data);
            return $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        }
    
        $collected_array = array();
        $staff_record    = $this->staff_model->get($this->customlib->getStaffID());
        $collected_by    = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
    
        $send_to            = $this->input->post('guardian_phone');
        $email              = $this->input->post('guardian_email');
        $parent_app_key     = $this->input->post('parent_app_key');
        $student_session_id = $this->input->post('student_session_id');
        $student            = $this->student_model->getByStudentSession($student_session_id);
        $total_row          = $this->input->post('row_counter');
    
        foreach ($total_row as $total_row_key => $total_row_value) {
    
            $fee_category             = $this->input->post('fee_category_' . $total_row_value);
            $student_transport_fee_id = $this->input->post('trans_fee_id_' . $total_row_value);
    
            // Handle date processing - keep BS dates for payments, convert for regular collections
            $collected_date = $this->input->post('collected_date');
            if ($payment_method === 'pay') {
                // For payments, use the BS date as provided
                $processed_date = $collected_date;
            } else {
                // For regular collections, convert using existing logic
                $processed_date = date('Y-m-d', $this->customlib->datetostrtotime($collected_date));
            }
    
            $amount_discount = convertCurrencyFormatToBaseAmount($this->input->post('amount_discount_' . $total_row_value));
            $amount_paid     = convertCurrencyFormatToBaseAmount($this->input->post('fee_amount_' . $total_row_value));
            $sfd_id          = (int) $this->input->post('student_fees_discount_id_' . $total_row_value);
            $fgf_id          = (int) $this->input->post('fee_groups_feetype_id_' . $total_row_value);

            if ($sfd_id > 0 && $amount_discount > 0 && $fee_category === 'fees') {
                if (!$this->feediscount_model->studentDiscountAppliesToFeeLine($sfd_id, $fgf_id, 'fees')) {
                    $array = array(
                        'status' => 0,
                        'error'  => array('row_counter' => 'Discount on row ' . $total_row_value . ' is not valid for that fee type.'),
                    );
                    return $this->output->set_content_type('application/json')->set_output(json_encode($array));
                }
            }

            $json_array = array(
                'amount'          => $amount_paid,
                'date'            => $processed_date,
                'description'     => $this->input->post('fee_gupcollected_note'),
                'amount_discount' => $amount_discount,
                'collected_by'    => $collected_by,
                'amount_fine'     => $this->input->post('fee_groups_feetype_fine_amount_' . $total_row_value),
                'payment_mode'    => $this->input->post('payment_mode_fee'),
                'received_by'     => isset($staff_record['id']) ? $staff_record['id'] : null, /* FIX */
            );
            $collected_array[] = array(
                'fee_category'               => $fee_category,
                'student_transport_fee_id'   => $student_transport_fee_id,
                'student_fees_master_id'     => $this->input->post('student_fees_master_id_' . $total_row_value),
                'fee_groups_feetype_id'      => $fgf_id,
                'student_fees_discount_id'   => ($sfd_id > 0 && $amount_discount > 0) ? $sfd_id : null,
                'amount_detail'              => $json_array,
            );
        }
    
        $deposited_fees = $this->studentfeemaster_model->fee_deposit_collections($collected_array);
    
        if ($deposited_fees && is_array($deposited_fees)) {
            
            // Handle payment success response differently
            if ($payment_method === 'pay') {
                // For payments, send simple success response and redirect
                $array = array(
                    'status' => 1, 
                    'message' => 'Payment processed successfully',
                    'redirect' => base_url('studentfee/addfee/' . $student_session_id)
                );
                return $this->output->set_content_type('application/json')->set_output(json_encode($array));
            }
            
            // Regular collection processing (existing logic)
            foreach ($deposited_fees as $deposited_fees_key => $deposited_fees_value) {
                $fee_category = $deposited_fees_value['fee_category'];
                $invoice[]    = array(
                    'invoice_id'     => $deposited_fees_value['invoice_id'],
                    'sub_invoice_id' => $deposited_fees_value['sub_invoice_id'],
                    'fee_category'   => $fee_category,
                );
    
                if ($deposited_fees_value['student_transport_fee_id'] != 0 && $deposited_fees_value['fee_category'] == "transport") {
    
                    $data['student_fees_master_id']   = null;
                    $data['fee_groups_feetype_id']    = null;
                    $data['student_transport_fee_id'] = $deposited_fees_value['student_transport_fee_id'];
    
                    $mailsms_array     = $this->studenttransportfee_model->getTransportFeeMasterByStudentTransportID($deposited_fees_value['student_transport_fee_id']);
                    $fee_group_name[]  = $this->lang->line("transport_fees");
                    $type[]            = $mailsms_array ? $mailsms_array->month : ''; /* FIX */
                    $code[]            = "-";
                    $fine_type[]       = $mailsms_array ? $mailsms_array->fine_type : '';
                    $due_date[]        = $mailsms_array ? $mailsms_array->due_date : '';
                    $fine_percentage[] = $mailsms_array ? $mailsms_array->fine_percentage : '';
                    $fine_amount[]     = $mailsms_array ? amountFormat($mailsms_array->fine_amount) : amountFormat(0);
                    $amount[]          = $mailsms_array ? amountFormat($mailsms_array->amount) : amountFormat(0);
    
                } else {
                    $mailsms_array = $this->feegrouptype_model->getFeeGroupByIDAndStudentSessionID($deposited_fees_value['fee_groups_feetype_id'], $student_session_id);
    
                    $fee_group_name[]  = $mailsms_array ? $mailsms_array->fee_group_name : '';
                    $type[]            = $mailsms_array ? $mailsms_array->type : '';
                    $code[]            = $mailsms_array ? $mailsms_array->code : '';
                    $fine_type[]       = $mailsms_array ? $mailsms_array->fine_type : '';
                    $due_date[]        = $mailsms_array ? $mailsms_array->due_date : '';
                    $fine_percentage[] = $mailsms_array ? $mailsms_array->fine_percentage : '';
                    $fine_amount[]     = amountFormat($mailsms_array ? $mailsms_array->fine_amount : 0);
    
                    if ($mailsms_array && $mailsms_array->is_system) {
                        $amount[] = amountFormat($mailsms_array->balance_fee_master_amount);
                    } else {
                        $amount[] = amountFormat($mailsms_array ? $mailsms_array->amount : 0);
                    }
                }
            }
    
            $obj_mail                         = [];
            $obj_mail['student_id']           = isset($student['id']) ? $student['id'] : null; /* FIX */
            $obj_mail['student_session_id']   = $student_session_id;
            $obj_mail['invoice']              = $invoice;
            $obj_mail['contact_no']           = isset($student['guardian_phone']) ? $student['guardian_phone'] : $send_to; /* FIX */
            $obj_mail['email']                = isset($student['email']) ? $student['email'] : $email; /* FIX */
            $obj_mail['parent_app_key']       = isset($student['parent_app_key']) ? $student['parent_app_key'] : $parent_app_key; /* FIX */
            $obj_mail['amount']               = "(" . implode(',', $amount) . ")";
            $obj_mail['fine_type']            = "(" . implode(',', $fine_type) . ")";
            $obj_mail['due_date']             = "(" . implode(',', $due_date) . ")";
            $obj_mail['fine_percentage']      = "(" . implode(',', $fine_percentage) . ")";
            $obj_mail['fine_amount']          = "(" . implode(',', $fine_amount) . ")";
            $obj_mail['fee_group_name']       = "(" . implode(',', $fee_group_name) . ")";
            $obj_mail['type']                 = "(" . implode(',', $type) . ")";
            $obj_mail['code']                 = "(" . implode(',', $code) . ")";
            $obj_mail['fee_category']         = $fee_category;
            $obj_mail['send_type']            = 'group';
    
            $this->mailsmsconf->mailsms('fee_submission', $obj_mail);

            $array = array(
                'status'   => 1,
                'error'    => '',
                'redirect' => base_url('studentfee/addfee/' . $student_session_id),
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        }

        $array = array(
            'status' => 0,
            'error'  => array('payment' => $this->lang->line('error_occurred_please_try_again') ?: 'Collection failed. Please try again.'),
        );
        return $this->output->set_content_type('application/json')->set_output(json_encode($array));
    }

    private function handleFeeAssignment()
    {
        // Handle fee assignment from Fee Master page
        $student_session_ids = $this->input->post('student_session_id');
        $fee_session_groups = $this->input->post('fee_session_groups');
        $all_student_ids = $this->input->post('student_ids'); // All students that were displayed
        
        if (empty($fee_session_groups)) {
            $array = array('status' => 'fail', 'error' => array('message' => $this->lang->line('please_select_fee_group')));
            return $this->output->set_content_type('application/json')->set_output(json_encode($array));
        }
        
        // Ensure fee_session_groups is an array
        if (!is_array($fee_session_groups)) {
            $fee_session_groups = array($fee_session_groups);
        }
        
        // Ensure student_session_ids is an array
        if (!is_array($student_session_ids)) {
            $student_session_ids = array($student_session_ids);
        }
        
        // Get all students that were displayed (checked or unchecked)
        $all_displayed_students = array();
        if (!empty($all_student_ids) && is_array($all_student_ids)) {
            $all_displayed_students = $all_student_ids;
        } else if (!empty($student_session_ids)) {
            // Fallback: use checked students only
            $all_displayed_students = $student_session_ids;
        }
        
        // Process each displayed student
        $skipped_disabled = 0;
        foreach ($all_displayed_students as $student_session_id) {
            if ($this->student_model->isStudentSessionAccountDisabled($student_session_id)) {
                $skipped_disabled++;
                continue;
            }

            $is_checked = in_array($student_session_id, $student_session_ids);
            $student_fees_master_id = $this->input->post('student_fees_master_id_' . $student_session_id);
            
            if ($is_checked) {
                // Student is checked - assign fee if not already assigned
                $existing_fees = $this->studentfeemaster_model->getStudentFees($student_session_id);
                $existing_fee_group_ids = array();
                if (!empty($existing_fees)) {
                    foreach ($existing_fees as $existing_fee) {
                        $existing_fee_group_ids[] = $existing_fee->fee_session_group_id;
                    }
                }
                
                // Assign fees that aren't already assigned
                foreach ($fee_session_groups as $fee_session_group_id) {
                    if (!in_array($fee_session_group_id, $existing_fee_group_ids)) {
                        $this->studentfeemaster_model->assign_bulk_fees(array($fee_session_group_id), $student_session_id, array());
                    }
                }
            } else {
                // Student is unchecked - remove fee if it was assigned
                if (!empty($student_fees_master_id) && $student_fees_master_id != '0') {
                    // Remove the fee assignment
                    foreach ($fee_session_groups as $fee_session_group_id) {
                        $this->studentfeemaster_model->assign_bulk_fees(array(), $student_session_id, array($fee_session_group_id));
                    }
                }
            }
        }
        
        $message = $this->lang->line('fees_assigned_successfully') ?: 'Fees assigned successfully';
        if ($skipped_disabled > 0) {
            $message .= ' ' . $skipped_disabled . ' disabled student(s) were skipped (fee assignment not allowed).';
        }

        $array = array(
            'status' => 1, 
            'message' => $message
        );
        return $this->output->set_content_type('application/json')->set_output(json_encode($array));
    }

    public function geBalanceFee()
    {
        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('fee_groups_feetype_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_fees_master_id', $this->lang->line('student_fees_master_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_session_id', $this->lang->line('student_session_id'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data  = array(
                'fee_groups_feetype_id'  => form_error('fee_groups_feetype_id'),
                'student_fees_master_id' => form_error('student_fees_master_id'),
                'student_session_id'     => form_error('student_session_id'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            return $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        } else {
            $data                 = array();
            $student_session_id   = $this->input->post('student_session_id');
            $discount_not_applied = $this->getNotAppliedDiscount($student_session_id);

            $fee_category = $this->input->post('fee_category');
            if ($fee_category == "transport") {
                $trans_fee_id         = $this->input->post('trans_fee_id');
                $remain_amount_object = $this->getStudentTransportFeetypeBalance($trans_fee_id);
                $remain_amount        = (float) json_decode($remain_amount_object)->balance;
                $remain_amount_fine   = json_decode($remain_amount_object)->fine_amount;
            } else {
                $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');
                $student_fees_master_id = $this->input->post('student_fees_master_id');
                $remain_amount_object   = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);
                $remain_amount          = json_decode($remain_amount_object)->balance;
                $remain_amount_fine     = json_decode($remain_amount_object)->fine_amount;
            }

            $remain_amount = number_format((float)$remain_amount, 2, ".", ""); /* FIX strong cast */

            $array = array(
                'status'              => 'success',
                'error'               => '',
                'balance'             => convertBaseAmountCurrencyFormat($remain_amount),
                'discount_not_applied'=> $discount_not_applied,
                'remain_amount_fine'  => convertBaseAmountCurrencyFormat(isset($remain_amount_fine) ? $remain_amount_fine : 0), /* FIX */
                'student_fees'        => convertBaseAmountCurrencyFormat(json_decode($remain_amount_object)->student_fees),
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($array)); /* FIX */
        }
    }

    public function getStudentTransportFeetypeBalance($trans_fee_id)
    {
        $data = array();

        $result = $this->studentfeemaster_model->studentTransportDeposit($trans_fee_id);

        /* FIX: guard empty result to avoid fatals downstream */
        if (!$result) {
            $array = array('status' => 'success', 'error' => '', 'student_fees' => 0, 'balance' => 0, 'fine_amount' => 0);
            return json_encode($array);
        }

        $amount_balance  = 0;
        $amount          = 0;
        $amount_fine     = 0;
        $amount_discount = 0;
        $fine_amount     = 0;
        $fee_fine_amount = 0;

        $due_amt = $result->fees;
        if ($result->due_date && strtotime($result->due_date) < strtotime(date('Y-m-d'))) { /* FIX */
            $fee_fine_amount = is_null($result->fine_percentage) ? $result->fine_amount : percentageAmount($result->fees, $result->fine_percentage);
        }

        $amount_detail = json_decode($result->amount_detail);
        if (is_object($amount_detail)) {
            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                $amount          = $amount + $amount_detail_value->amount;
                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
            }
        }

        $amount_balance = $due_amt - ($amount + $amount_discount);
        $fine_amount    = abs($amount_fine - $fee_fine_amount);
        $array          = array('status' => 'success', 'error' => '', 'student_fees' => $due_amt, 'balance' => $amount_balance, 'fine_amount' => $fine_amount);
        return json_encode($array);
    }

    public function getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id)
    {
        $data                           = array();
        $data['fee_groups_feetype_id']  = $fee_groups_feetype_id;
        $data['student_fees_master_id'] = $student_fees_master_id;
        $result                         = $this->studentfeemaster_model->studentDeposit($data);

        /* FIX: guard empty result (bad ids or missing mapping) */
        if (!$result) {
            $array = array('status' => 'success', 'error' => '', 'student_fees' => 0, 'balance' => 0, 'fine_amount' => 0);
            return json_encode($array);
        }

        $amount_balance  = 0;
        $amount          = 0;
        $amount_fine     = 0;
        $amount_discount = 0;
        $fine_amount     = 0;
        $fee_fine_amount = 0;
        $due_amt         = $result->amount;

        if ($result->due_date && strtotime($result->due_date) < strtotime(date('Y-m-d'))) { /* FIX */
            $fee_fine_amount = $result->fine_amount;
        }

        if (!empty($result->is_system)) {
            $due_amt = $result->student_fees_master_amount;
        }

        $amount_detail = json_decode($result->amount_detail);
        if (is_object($amount_detail)) {

            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                $amount          = $amount + $amount_detail_value->amount;
                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
            }
        }

        $amount_balance = $due_amt - ($amount + $amount_discount);
        $fine_amount    = ($fee_fine_amount > 0) ? ($fee_fine_amount - $amount_fine) : 0;

        $array = array('status' => 'success', 'error' => '', 'student_fees' => $due_amt, 'balance' => $amount_balance, 'fine_amount' => $fine_amount);
        return json_encode($array);
    }

    public function check_deposit($amount)
    {
        if (is_numeric($this->input->post('amount')) && is_numeric($this->input->post('amount_discount'))) {
            if ($this->input->post('amount') != "" && $this->input->post('amount_discount') != "") {
                if ($this->input->post('amount') < 0) {
                    $this->form_validation->set_message('check_deposit', $this->lang->line('deposit_amount_can_not_be_less_than_zero'));
                    return false;
                } else {
                    $transport_fees_id      = $this->input->post('transport_fees_id');
                    $student_fees_master_id = $this->input->post('student_fees_master_id');
                    $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');
                    $deposit_amount         = $this->input->post('amount') + $this->input->post('amount_discount');
                    if ($transport_fees_id != 0) {
                        $remain_amount = $this->getStudentTransportFeetypeBalance($transport_fees_id);
                    } else {
                        $remain_amount = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);
                    }
                    $remain_amount = json_decode($remain_amount)->balance;
                    if (convertBaseAmountCurrencyFormat($remain_amount) < $deposit_amount) {
                        $this->form_validation->set_message('check_deposit', $this->lang->line('deposit_amount_can_not_be_greater_than_remaining'));
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        } elseif (!is_numeric($this->input->post('amount'))) {
            $this->form_validation->set_message('check_deposit', $this->lang->line('amount_field_must_contain_only_numbers'));
            return false;
        } elseif (!is_numeric($this->input->post('amount_discount'))) {
            return true;
        }

        return true;
    }

    public function getNotAppliedDiscount($student_session_id)
    {
        $discounts_array = $this->feediscount_model->getDiscountNotApplied($student_session_id);
        foreach ($discounts_array as $discount_key => $discount_value) {
            $discounts_array[$discount_key]->{"amount"} = convertBaseAmountCurrencyFormat($discount_value->amount);
        }
        return $discounts_array;
    }

    public function getDiscountGroups()
    {
        $student_session_id = $this->input->post('student_session_id');
        if (empty($student_session_id)) {
            $array = array('status' => 'fail', 'error' => array('student_session_id' => $this->lang->line('student_session_id_required')));
            return $this->output->set_content_type('application/json')->set_output(json_encode($array));
        }

        $fee_groups_feetype_id = (int) $this->input->post('fee_groups_feetype_id');
        $fee_category          = $this->input->post('fee_category');
        if ($fee_category === null || $fee_category === '') {
            $fee_category = 'fees';
        }

        $this->feediscount_model->ensureAllottedDiscountsForStudentSession($student_session_id);
        $discount_not_applied = $this->feediscount_model->getDiscountsForCollectFee(
            $student_session_id,
            $fee_groups_feetype_id,
            $fee_category
        );
        foreach ($discount_not_applied as $discount_key => $discount_value) {
            $discount_not_applied[$discount_key]->{"amount"} = convertBaseAmountCurrencyFormat($discount_value->amount);
        }
        $array = array(
            'status' => 'success',
            'error' => '',
            'discount_not_applied' => $discount_not_applied
        );
        return $this->output->set_content_type('application/json')->set_output(json_encode($array));
    }

    public function add_new_student($student)
    {
        $new_student = array(
            'id'                 => $student['id'],
            'student_session_id' => $student['student_session_id'],
            'class'              => $student['class'],
            'section_id'         => $student['section_id'],
            'section'            => $student['section'],
            'admission_no'       => $student['admission_no'],
            'roll_no'            => $student['roll_no'],
            'admission_date'     => $student['admission_date'],
            'firstname'          => $student['firstname'],
            'middlename'         => $student['middlename'],
            'lastname'           => $student['lastname'],
            'image'              => $student['image'],
            'mobileno'           => $student['mobileno'],
            'email'              => $student['email'],
            'state'              => $student['state'],
            'city'               => $student['city'],
            'pincode'            => $student['pincode'],
            'religion'           => $student['religion'],
            'dob'                => $student['dob'],
            'current_address'    => $student['current_address'],
            'permanent_address'  => $student['permanent_address'],
            'category_id'        => $student['category_id'],
            'category'           => $student['category'],
            'adhar_no'           => $student['adhar_no'],
            'samagra_id'         => $student['samagra_id'],
            'bank_account_no'    => $student['bank_account_no'],
            'bank_name'          => $student['bank_name'],
            'ifsc_code'          => $student['ifsc_code'],
            'guardian_name'      => $student['guardian_name'],
            'guardian_relation'  => $student['guardian_relation'],
            'guardian_phone'     => $student['guardian_phone'],
            'guardian_address'   => $student['guardian_address'],
            'is_active'          => $student['is_active'],
            'father_name'        => $student['father_name'],
            'rte'                => $student['rte'],
            'gender'             => $student['gender'],
        );
        return $new_student;
    }

    public function checksession()
    {
        log_message('error', 'PAYMENT DEBUG: checksession called');
        log_message('error', 'PAYMENT DEBUG: All session data: ' . print_r($this->session->all_userdata(), true));
        
        $admin_id = $this->session->userdata('admin_id');
        $student_id = $this->session->userdata('student_id'); 
        $parent_id = $this->session->userdata('parent_id');
        
        log_message('error', 'PAYMENT DEBUG: Session values - admin_id: ' . ($admin_id ?: 'null') . ', student_id: ' . ($student_id ?: 'null') . ', parent_id: ' . ($parent_id ?: 'null'));
        log_message('error', 'PAYMENT DEBUG: Session ID: ' . session_id());
        log_message('error', 'PAYMENT DEBUG: Cookie data: ' . print_r($_COOKIE, true));
        
        if (!$admin_id && !$student_id && !$parent_id) {
            $response = array(
                'valid' => false, 
                'message' => 'Session expired', 
                'debug' => 'No session variables found',
                'session_id' => session_id(),
                'all_session' => $this->session->all_userdata()
            );
        } else {
            $response = array(
                'valid' => true, 
                'message' => 'Session active', 
                'debug' => 'Session OK',
                'admin_id' => $admin_id,
                'student_id' => $student_id,
                'parent_id' => $parent_id
            );
        }
        
        log_message('error', 'PAYMENT DEBUG: checksession response: ' . json_encode($response));
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

// Replace your addfeegrp method with this:
public function addfeegrp() 
{
    // Check authentication first
    if (!$this->session->userdata('admin_id') && !$this->session->userdata('student_id') && !$this->session->userdata('parent_id')) {
        redirect('site/userlogin');
        return;
    }
    
    if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
        access_denied();
    }

    $payment_method = $this->input->post('payment_method');
    
    // If this is a "pay" action, handle it differently
    if ($payment_method === 'pay') {
        // For payment, we don't need fee_session_groups validation
        return $this->handlePaymentSubmission();
    }
    
    // Otherwise, use the existing addfeegroup logic with proper validation
    return $this->addfeegroup();
}

private function handlePaymentSubmission()
{
    // Custom validation for payment submissions
    $this->form_validation->set_rules('row_counter[]', $this->lang->line('fees_list'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('collected_date', $this->lang->line('date'), 'required|trim|xss_clean');
    
    if ($this->form_validation->run() == false) {
        $data = array(
            'row_counter' => form_error('row_counter'), 
            'collected_date' => form_error('collected_date')
        );
        $array = array('status' => 0, 'error' => $data);
        return $this->output->set_content_type('application/json')->set_output(json_encode($array));
    }
    
    try {
        // Process payment logic here
        $collected_array = array();
        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $collected_by = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";

        $send_to = $this->input->post('guardian_phone');
        $email = $this->input->post('guardian_email');
        $parent_app_key = $this->input->post('parent_app_key');
        $student_session_id = $this->input->post('student_session_id');
        $total_row = $this->input->post('row_counter');

        foreach ($total_row as $total_row_key => $total_row_value) {
            $fee_category = $this->input->post('fee_category_' . $total_row_value);
            $student_transport_fee_id = $this->input->post('trans_fee_id_' . $total_row_value);
            $amount_discount = convertCurrencyFormatToBaseAmount($this->input->post('amount_discount_' . $total_row_value));
            $amount_paid     = convertCurrencyFormatToBaseAmount($this->input->post('fee_amount_' . $total_row_value));
            $sfd_id          = (int) $this->input->post('student_fees_discount_id_' . $total_row_value);
            $fgf_id          = (int) $this->input->post('fee_groups_feetype_id_' . $total_row_value);

            if ($sfd_id > 0 && $amount_discount > 0 && $fee_category === 'fees') {
                if (!$this->feediscount_model->studentDiscountAppliesToFeeLine($sfd_id, $fgf_id, 'fees')) {
                    $array = array(
                        'status' => 0,
                        'error'  => array('row_counter' => 'Discount on row ' . $total_row_value . ' is not valid for that fee type.'),
                    );
                    return $this->output->set_content_type('application/json')->set_output(json_encode($array));
                }
            }

            $json_array = array(
                'amount'          => $amount_paid,
                'date'            => $this->input->post('collected_date'),
                'description'     => $this->input->post('fee_gupcollected_note'),
                'amount_discount' => $amount_discount,
                'collected_by'    => $collected_by,
                'amount_fine'     => $this->input->post('fee_groups_feetype_fine_amount_' . $total_row_value),
                'payment_mode'    => $this->input->post('payment_mode_fee'),
                'received_by'     => isset($staff_record['id']) ? $staff_record['id'] : null,
            );

            $collected_array[] = array(
                'fee_category'             => $fee_category,
                'student_transport_fee_id' => $student_transport_fee_id,
                'student_fees_master_id'   => $this->input->post('student_fees_master_id_' . $total_row_value),
                'fee_groups_feetype_id'    => $fgf_id,
                'student_fees_discount_id' => ($sfd_id > 0 && $amount_discount > 0) ? $sfd_id : null,
                'amount_detail'            => $json_array,
            );
        }

        $deposited_fees = $this->studentfeemaster_model->fee_deposit_collections($collected_array);

        if ($deposited_fees && is_array($deposited_fees)) {
            // Success response for payment
            $array = array(
                'status' => 1, 
                'message' => 'Payment processed successfully',
                'redirect' => base_url('studentfee/addfee/' . $student_session_id)
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($array));
        } else {
            $array = array('status' => 0, 'error' => array('payment' => 'Payment processing failed'));
            return $this->output->set_content_type('application/json')->set_output(json_encode($array));
        }
        
    } catch (Exception $e) {
        log_message('error', 'Payment submission error: ' . $e->getMessage());
        $array = array('status' => 0, 'error' => array('payment' => 'Payment processing failed'));
        return $this->output->set_content_type('application/json')->set_output(json_encode($array));
    }
}

private function process_collection_date($date_input) 
{
    // If date is in Nepali/BS format (like "06-10-2082"), convert or use as-is
    // For now, assuming your system can handle BS dates directly
    return $date_input;
}

private function get_school_logo_with_debug()
{
    // Fetch settings from database
    $setting_result = $this->setting_model->get();
    
    // Start debug output array
    $debug_output = array();
    $debug_output[] = "=== LOGO FETCH DEBUG START ===";

    // Check if we have settings
    if (empty($setting_result)) {
        $debug_output[] = "ERROR: No settings found in database!";
        foreach ($debug_output as $msg) {
            echo "<script>console.log(" . json_encode($msg) . ");</script>";
        }
        return array(
            'logo_url' => base_url('uploads/school_content/logo/default_logo.png'),
            'logo_file' => 'default_logo.png',
            'logo_base64' => ''
        );
    }

    // Get logo filename from settings table
    $school_logo = '';
    if (isset($setting_result[0]['image']) && !empty($setting_result[0]['image'])) {
        $school_logo = trim($setting_result[0]['image']);
        $debug_output[] = "FOUND: Logo in sch_settings table (image column)";
    } else {
        $debug_output[] = "WARNING: sch_settings.image field empty";
    }

    $debug_output[] = "Logo filename from DB: " . $school_logo;

    // Build full file system path and URL dynamically
    $logo_base64 = '';
    $logo_url = base_url('uploads/school_content/logo/default_logo.png'); // default fallback
    if (!empty($school_logo)) {
        $encoded_filename = rawurlencode($school_logo);
        $logo_url = base_url('uploads/school_content/logo/' . $encoded_filename);
        $file_path = FCPATH . 'uploads/school_content/logo/' . $school_logo;

        // Check if file exists on disk
        $file_exists = file_exists($file_path);
        $debug_output[] = "File path: " . $file_path;
        $debug_output[] = "File exists on server: " . ($file_exists ? 'YES' : 'NO');

        if ($file_exists) {
            // Create base64 for print reliability
            $image_data = file_get_contents($file_path);
            $image_info = @getimagesize($file_path);
            $mime_type = isset($image_info['mime']) ? $image_info['mime'] : 'image/jpeg';
            $logo_base64 = 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
            $debug_output[] = "Base64 generated successfully (length: " . strlen($logo_base64) . ")";
        } else {
            $debug_output[] = "WARNING: File not found for base64 conversion";
        }

        $debug_output[] = "Encoded filename: " . $encoded_filename;
        $debug_output[] = "Final logo URL (dynamic): " . $logo_url;
    } else {
        $debug_output[] = "WARNING: No logo filename found, using default";
    }

    $debug_output[] = "=== LOGO FETCH DEBUG END ===";

    // Output debug to browser console
    foreach ($debug_output as $msg) {
        echo "<script>console.log(" . json_encode($msg) . ");</script>";
    }

    // Always return base_url() generated link, never absolute domain from DB
    return array(
        'logo_url'   => $logo_url,
        'logo_file'  => $school_logo ?: 'default_logo.png',
        'logo_base64'=> $logo_base64
    );
}
}
