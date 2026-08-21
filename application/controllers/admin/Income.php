<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Income extends Admin_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('media_storage');
        $this->config->load('app-config');
        $this->load->library("datatables");
    }


    /**
     * Validate Nepali date in YYYY-MM-DD format (basic check: month 1-12, day 1-32)
     */
    private function is_valid_nepali_date($date) {
        if (!preg_match('/^20\\d{2}-\\d{2}-\\d{2}$/', $date)) return false;
        list($year, $month, $day) = explode('-', $date);
        if ($month < 1 || $month > 12) return false;
        if ($day < 1 || $day > 32) return false;
        return true;
    }

    /**
     * Convert BS date to AD date for display purposes (approximate conversion)
     */
    private function convertBSToADForDisplay($bs_date) {
        if (empty($bs_date) || $bs_date == '0000-00-00') {
            return false;
        }
        
        $parts = explode('-', $bs_date);
        if (count($parts) == 3) {
            $bs_year = intval($parts[0]);
            $month = $parts[1];
            $day = $parts[2];
            
            // Convert year: BS - 57 = AD (approximate)
            $ad_year = $bs_year - 57;
            
            return sprintf('%04d-%02s-%02s', $ad_year, $month, $day);
        }
        
        return false;
    }


    public function index()
    {
        if (!$this->rbac->hasPrivilege('income', 'can_view')) {
            access_denied();
        }
       
        $this->session->set_userdata('top_menu', 'Income');
        $this->session->set_userdata('sub_menu', 'income/index');
        $this->form_validation->set_rules('inc_head_id', $this->lang->line('income_head'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('documents', $this->lang->line('documents'), 'callback_handle_upload');
        if ($this->form_validation->run() == true) {
            // Normalize and validate Nepali date before saving
            $date_input = $this->input->post('date');
            $date_norm = $this->customlib->dateFormatToYYYYMMDD($date_input);
            if (!$this->is_valid_nepali_date($date_norm)) {
                $data['error_message'] = 'Invalid Nepali date selected.';
                $income_result       = $this->income_model->get();
                $data['incomelist']  = $income_result;
                $incomeHead          = $this->incomehead_model->get();
                $data['incheadlist'] = $incomeHead;
                $this->load->view('layout/header', $data);
                $this->load->view('admin/income/incomeList', $data);
                $this->load->view('layout/footer', $data);
                return;
            }
            $img_name = $this->media_storage->fileupload("documents", "./uploads/school_income/");
            $data = array(
                'income_head_id' => $this->input->post('inc_head_id'),
                'name'        => $this->input->post('name'),
                'date'        => $date_norm, // Store as string, do not convert
                'amount'      => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
                'invoice_no'  => $this->input->post('invoice_no'),
                'note'        => $this->input->post('description'),
                'documents'   => $img_name,
            );
            $insert_id = $this->income_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/income/index');
        }


        $income_result       = $this->income_model->get();
        $data['incomelist']  = $income_result;
        $incomeHead          = $this->incomehead_model->get();
        $data['incheadlist'] = $incomeHead;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/income/incomeList', $data);
        $this->load->view('layout/footer', $data);
    }


    public function download($id)
    {
        $income = $this->income_model->get($id);
        $this->media_storage->filedownload($income['documents'], "uploads/school_income");
    }


    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('income', 'can_view')) {
            access_denied();
        }
        $data['title']  = 'Fees Master List';
        $income         = $this->income_model->get($id);
        $data['income'] = $income;
        $this->load->view('layout/header', $data);
        $this->load->view('income/incomeShow', $data);
        $this->load->view('layout/footer', $data);
    }


    public function getByFeecategory()
    {
        $feecategory_id = $this->input->get('feecategory_id');
        $data           = $this->feetype_model->getTypeByFeecategory($feecategory_id);
        echo json_encode($data);
    }


    public function getStudentCategoryFee()
    {
        $type     = $this->input->post('type');
        $class_id = $this->input->post('class_id');
        $data     = $this->income_model->getTypeByFeecategory($type, $class_id);
        if (empty($data)) {
            $status = 'fail';
        } else {
            $status = 'success';
        }
        $array = array('status' => $status, 'data' => $data);
        echo json_encode($array);
    }


    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('income', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $row           = $this->income_model->get($id);
        if ($row['documents'] != '') {
            $this->media_storage->filedelete($row['documents'], "uploads/school_income/");
        }


        $this->income_model->remove($id);
        redirect('admin/income/index');
    }


    public function create()
    {
        $data['title'] = 'Add Fees Master';
        $this->form_validation->set_rules('income', $this->lang->line('fees_master'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('income/incomeCreate', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'income' => $this->input->post('income'),
            );
            $this->income_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('income/index');
        }
    }


    public function handle_upload()
    {
        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["documents"]) && !empty($_FILES['documents']['name'])) {


            $file_type = $_FILES["documents"]['type'];
            $file_size = $_FILES["documents"]["size"];
            $file_name = $_FILES["documents"]["name"];


            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));


            if ($files = filesize($_FILES['documents']['tmp_name'])) {


                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }


                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_extension_error_uploading_image'));
                return false;
            }


            return true;
        }
        return true;
    }


    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('income', 'can_edit')) {
            access_denied();
        }


        $data['id']          = $id;
        $income              = $this->income_model->get($id);
        $data['income']      = $income;
        $data['title_list']  = 'Fees Master List';
        $expnseHead          = $this->incomehead_model->get();
        $data['incheadlist'] = $expnseHead;
        $this->form_validation->set_rules('inc_head_id', $this->lang->line('income_head'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('documents', $this->lang->line('documents'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/income/incomeEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // Normalize and validate Nepali date before saving
            $date_input = $this->input->post('date');
            $date_norm = $this->customlib->dateFormatToYYYYMMDD($date_input);
            if (!$this->is_valid_nepali_date($date_norm)) {
                $data['error_message'] = 'Invalid Nepali date selected.';
                $this->load->view('layout/header', $data);
                $this->load->view('admin/income/incomeEdit', $data);
                $this->load->view('layout/footer', $data);
                return;
            }
            $data = array(
                'id'          => $id,
                'income_head_id' => $this->input->post('inc_head_id'),
                'name'        => $this->input->post('name'),
                'date'        => $date_norm, // Store as string, do not convert
                'amount'      => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
                'invoice_no'  => $this->input->post('invoice_no'),
                'note'        => $this->input->post('description'),
            );
            if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                $img_name = $this->media_storage->fileupload("documents", "./uploads/school_income/");
            } else {
                $img_name = $income['documents'];
            }
            $data['documents'] = $img_name;
            if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                if ($income['documents'] != '') {
                    $this->media_storage->filedelete($income['documents'], "uploads/school_income");
                }
            }
            $this->income_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/income/index');
        }
    }


    public function incomeSearch()
    {
        if (!$this->rbac->hasPrivilege('search_income', 'can_view')) {
            access_denied();
        }
        $data['searchlist'] = $this->customlib->get_searchtype();
        $this->session->set_userdata('top_menu', 'Income');
        $this->session->set_userdata('sub_menu', 'income/incomesearch');
        $data['search_type'] = '';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/income/incomeSearch', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Get between date view for income search (with Nepali date pickers)
     * Uses Customlib for date conversion
     */
    public function get_betweendate($type)
    {
        // Ensure clean output for AJAX response
        ob_clean();
        
        // Pass today's BS date to view using Customlib
        $today_ad = date('Y-m-d');
        $today_bs = $this->customlib->convertADDateToBS($today_ad);
        
        // Pass to view
        $data['today_bs'] = $today_bs;
        $this->load->view('admin/income/betweenDate', $data);
    }


    /**
     * Encode DataTables payload; UTF-8 safe for live servers with Nepali text.
     */
    private function _encode_income_datatable_json(array $payload)
    {
        $flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }
        $out = json_encode($payload, $flags);
        return ($out !== false) ? $out : '{"draw":0,"recordsTotal":0,"recordsFiltered":0,"data":[]}';
    }

    public function getincomelist()
    {
        $m               = $this->income_model->getincomelist();
        $m               = json_decode($m);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        if (!is_object($m) || empty($m)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output($this->_encode_income_datatable_json(array(
                    'draw'            => (int) $this->input->post('draw'),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => array(),
                )));
            return;
        }
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $editbtn     = '';
                $deletebtn   = '';
                $documents   = '';
                $inc_head_id = $value->income_head_id;
                $arr1        = str_split($inc_head_id);


                $title = "<a href='#' tabindex='0' data-toggle='popover' class='detail_popover'>" . $value->name . "</a>  ";
                if ($value->documents) {
                    $documents = "<a href='" . base_url() . "admin/income/download/" . $value->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('download') . "'><i class='fa fa-download'></i></a>";
                }


                if ($this->rbac->hasPrivilege('income', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "admin/income/edit/" . $value->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                if ($this->rbac->hasPrivilege('income', 'can_delete')) {
                    $deletebtn = '';
                    $deletebtn = "<a onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . "  )' href='" . base_url() . "admin/income/delete/" . $value->id . "' class='btn btn-default btn-xs' title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-trash'></i></a>";
                }
               
                $row   = array();
                $row[] = $title;


                if ($value->note == "") {
                    $row[] = $this->lang->line('no_description');
                } else {
                    $row[] = $value->note;
                }


                $row[]     = $value->invoice_no;
                $row[]     = $this->customlib->dateformat($value->date);
                $row[]     = $value->income_category;
                $row[]     = $currency_symbol . amountFormat($value->amount);
                $row[]     = $documents . ' ' . $editbtn . ' ' . $deletebtn;
                $dt_data[] = $row;
            }
        }


        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        $this->output
            ->set_content_type('application/json')
            ->set_output($this->_encode_income_datatable_json($json_data));
    }


    public function checkvalidation()
    {
        $button_type = $this->input->post('button_type');


        if ($button_type == "search_filter") {
            $this->form_validation->set_rules('search_type', $this->lang->line('search') . " " . $this->lang->line('type'), 'required|trim|xss_clean');
        } elseif ($button_type == "search_full") {
            $this->form_validation->set_rules('search_text', $this->lang->line('keyword'), 'required|trim|xss_clean');
        }
       
        if ($this->form_validation->run() == false) {
            $error = array();
            if ($button_type == "search_filter") {
                $error['search_type'] = form_error('search_type');
            } elseif ($button_type == "search_full") {
                $error['search_text'] = form_error('search_text');
            }


            $array = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        } else {
            $button_type = $this->input->post('button_type');
            $search_text = $this->input->post('search_text');
            $date_from   = "";
            $date_to     = "";


            $search_type = $this->input->post('search_type');
            if ($search_type == 'period') {
                $date_from = $this->input->post('date_from');
                $date_to   = $this->input->post('date_to');
                
                // Convert Nepali date format to YYYY-MM-DD if needed
                if (!empty($date_from)) {
                    $date_from = $this->customlib->dateFormatToYYYYMMDD($date_from);
                }
                if (!empty($date_to)) {
                    $date_to = $this->customlib->dateFormatToYYYYMMDD($date_to);
                }
            }


            $params = array('button_type' => $button_type, 'search_type' => $search_type, 'search_text' => $search_text, 'date_from' => $date_from, 'date_to' => $date_to);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        } 
    }


    /**
     * Get current BS date - convert today's AD date to BS format
     */
    private function getCurrentBSDate() {
        // Convert today's AD date to BS date
        $today_ad = date('Y-m-d');
        $today_bs = $this->customlib->convertADDateToBS($today_ad);
        
        // Validate the BS date format
        if (!$this->is_valid_nepali_date($today_bs)) {
            // If conversion fails, try to get from most recent income record as fallback
            $this->db->select('date');
            $this->db->from('income');
            $this->db->order_by('date', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $result = $query->row();
                $recent_date = $result->date;
                if ($this->is_valid_nepali_date($recent_date)) {
                    return $recent_date;
                }
            }
            // Last resort: return approximate today's BS date
            return $today_bs;
        }
        
        return $today_bs;
    }

    /**
     * Get BS date range based on search type using Nepali calendar
     */
    private function getBSDateRange($search_type, $current_bs_date) {
        // Parse current BS date
        $parts = explode('-', $current_bs_date);
        if (count($parts) != 3) {
            // Fallback to AD conversion
            $ad_dates = $this->customlib->get_betweendate($search_type);
            return array(
                'from_date' => $this->customlib->convertADDateToBS($ad_dates['from_date']),
                'to_date' => $this->customlib->convertADDateToBS($ad_dates['to_date'])
            );
        }
        
        $bs_year = intval($parts[0]);
        $bs_month = intval($parts[1]);
        $bs_day = intval($parts[2]);
        
        // Calculate ranges based on BS calendar
        $from_date = '';
        $to_date = '';
        
        switch ($search_type) {
            case 'today':
                // For today, use the current BS date exactly
                $from_date = $current_bs_date;
                $to_date = $current_bs_date;
                break;
                
            case 'this_month':
                // First day of current BS month
                $from_date = sprintf('%04d-%02d-01', $bs_year, $bs_month);
                // Last day of current BS month (use 32 as max day for Nepali calendar)
                $to_date = sprintf('%04d-%02d-32', $bs_year, $bs_month);
                break;
                
            case 'last_month':
                $prev_month = $bs_month - 1;
                $prev_year = $bs_year;
                if ($prev_month < 1) {
                    $prev_month = 12;
                    $prev_year--;
                }
                $from_date = sprintf('%04d-%02d-01', $prev_year, $prev_month);
                $to_date = sprintf('%04d-%02d-32', $prev_year, $prev_month);
                break;
                
            case 'last_3_month':
                $from_month = $bs_month - 2;
                $from_year = $bs_year;
                if ($from_month < 1) {
                    $from_month += 12;
                    $from_year--;
                }
                $from_date = sprintf('%04d-%02d-01', $from_year, $from_month);
                $to_date = sprintf('%04d-%02d-32', $bs_year, $bs_month);
                break;
                
            case 'last_6_month':
                $from_month = $bs_month - 5;
                $from_year = $bs_year;
                if ($from_month < 1) {
                    $from_month += 12;
                    $from_year--;
                }
                $from_date = sprintf('%04d-%02d-01', $from_year, $from_month);
                $to_date = sprintf('%04d-%02d-32', $bs_year, $bs_month);
                break;
                
            case 'last_12_month':
            case 'this_year':
                // First day of current BS year
                $from_date = sprintf('%04d-01-01', $bs_year);
                // Last day of current BS year
                $to_date = sprintf('%04d-12-32', $bs_year);
                break;
                
            case 'last_year':
                $from_date = sprintf('%04d-01-01', $bs_year - 1);
                $to_date = sprintf('%04d-12-32', $bs_year - 1);
                break;
                
            default:
                // Fallback to AD conversion
                $ad_dates = $this->customlib->get_betweendate($search_type);
                return array(
                    'from_date' => $this->customlib->convertADDateToBS($ad_dates['from_date']),
                    'to_date' => $this->customlib->convertADDateToBS($ad_dates['to_date'])
                );
        }
        
        return array('from_date' => $from_date, 'to_date' => $to_date);
    }

    public function getincomesearchlist()
    {
        // Ensure clean JSON output - prevent any output before JSON
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Get search parameters
        $search_type = $this->input->post('search_type');
        $button_type = $this->input->post('button_type');
        $search_text = $this->input->post('search_text');


        if ($button_type == 'search_filter') {
            // Handle period search type separately
            if ($search_type == 'period') {
                $date_from = $this->input->post('date_from');
                $date_to = $this->input->post('date_to');
                
                // Convert Nepali date format to YYYY-MM-DD if needed
                if (!empty($date_from)) {
                    $date_from = $this->customlib->dateFormatToYYYYMMDD($date_from);
                }
                if (!empty($date_to)) {
                    $date_to = $this->customlib->dateFormatToYYYYMMDD($date_to);
                }
                
                // Use the provided dates directly
                $dates = array(
                    'from_date' => $date_from,
                    'to_date' => $date_to
                );
            } else {
                // Get current BS date and calculate ranges based on Nepali calendar
                $current_bs_date = $this->getCurrentBSDate();
                
                if ($search_type != "") {
                    if ($search_type == 'all') {
                        $dates = $this->getBSDateRange('this_year', $current_bs_date);
                    } else {
                        $dates = $this->getBSDateRange($search_type, $current_bs_date);
                    }
                } else {
                    $dates = $this->getBSDateRange('this_year', $current_bs_date);
                    $search_type = '';
                }
            }

            $dateformat = $this->customlib->getSchoolDateFormat();
            
            // Dates are already in BS format (YYYY-MM-DD)
            if (!empty($dates['from_date']) && !empty($dates['to_date'])) {
                // Use BS dates directly for database search
                $date_from = $dates['from_date'];
                $date_to = $dates['to_date'];
                
                // Ensure dates are in YYYY-MM-DD format for comparison
                if (strlen($date_from) == 10 && strlen($date_to) == 10) {
                    $resultList = $this->income_model->search("", $date_from, $date_to);
                } else {
                    // Fallback: search all if date conversion fails
                    $resultList = $this->income_model->search("", "", "");
                }
            } else {
                // Fallback: search all if dates are empty
                $resultList = $this->income_model->search("", "", "");
            }


        } else {


            $search_text = $this->input->post('search_text');
            $resultList  = $this->income_model->search($search_text, "", "");
            $resultList  = $resultList;
        }

        // Decode the JSON response from the model
        $m = json_decode($resultList);
        
        // Handle JSON decode errors
        if (json_last_error() !== JSON_ERROR_NONE || !is_object($m)) {
            // Log the error for debugging
            log_message('error', 'Income search JSON decode error: ' . json_last_error_msg() . ' | Response: ' . substr($resultList, 0, 500));
            
            $json_data = array(
                "draw"            => intval($this->input->post('draw')) ?: 0,
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => array(),
                "error"           => "Error processing search results"
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output($this->_encode_income_datatable_json($json_data));
            return;
        }
        
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        $total_amount    = 0;
        
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $total_amount += $value->amount;
                $row       = array();
                $row[]     = $value->name;
                $row[]     = $value->invoice_no;
                $row[]     = $value->income_category;
                // Display BS date directly - dates are stored in BS format (YYYY-MM-DD)
                // Use customlib dateformat to format the BS date for display
                $row[]     = $this->customlib->dateformat($value->date);
                $row[]     = $currency_symbol . amountFormat($value->amount);
                $dt_data[] = $row;
            }
            // Add footer row with total
            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('grand_total') . " : " . $currency_symbol . amountFormat($total_amount) . "</b>";
            $dt_data[]    = $footer_row;
        }

        // Build JSON response with DataTables format
        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        
        $this->output
            ->set_content_type('application/json')
            ->set_output($this->_encode_income_datatable_json($json_data));
    }

}



