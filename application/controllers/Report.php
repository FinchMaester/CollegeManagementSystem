<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Report extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->time               = strtotime(date('d-m-Y H:i:s'));
        $this->payment_mode       = $this->customlib->payment_mode();
        $this->search_type        = $this->customlib->get_searchtype();
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->library('media_storage');
        $this->load->model("state_model");
    }

    public function pdfStudentFeeRecord()
    {
        $data                    = [];
        $class_id                = $this->uri->segment(3);
        $section_id              = $this->uri->segment(4);
        $student_id              = $this->uri->segment(5);
        $student                 = $this->student_model->get($student_id);
        $setting_result          = $this->setting_model->get();
        $data['settinglist']     = $setting_result;
        $data['student']         = $student;
        $student_due_fee         = $this->studentfee_model->getDueFeeBystudent($class_id, $section_id, $student_id);
        $data['student_due_fee'] = $student_due_fee;
        
        $html                    = $this->load->view('reports/students_detail', $data, true);
        $pdfFilePath             = $this->time . ".pdf";
        $this->fontdata          = array(
            "opensans" => array(
                'R'  => "OpenSans-Regular.ttf",
                'B'  => "OpenSans-Bold.ttf",
                'I'  => "OpenSans-Italic.ttf",
                'BI' => "OpenSans-BoldItalic.ttf",
            ),
        );
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function pdfByInvoiceNo()
    {
        $data                    = [];
        $invoice_id              = $this->uri->segment(3);
        $setting_result          = $this->setting_model->get();
        $data['settinglist']     = $setting_result;
        $student_due_fee         = $this->studentfee_model->getFeeByInvoice($invoice_id);
        $data['student_due_fee'] = $student_due_fee;
        $html                    = $this->load->view('reports/pdfinvoiceno', $data, true);
        $pdfFilePath             = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function pdfDepositeFeeByStudent($id)
    {
        $data                        = [];
        $data['title']               = 'Student Detail';
        $student                     = $this->student_model->get($id);
        $setting_result              = $this->setting_model->get();
        $data['settinglist']         = $setting_result;
        $student_fee_history         = $this->studentfee_model->getStudentFees($id);
        $data['student_fee_history'] = $student_fee_history;
        $data['student']             = $student;
        $array                       = array();
        $feecategory                 = $this->feecategory_model->get();
        foreach ($feecategory as $key => $value) {
            $dataarray            = array();
            $value_id             = $value['id'];
            $dataarray[$value_id] = $value['category'];
            $category             = $value['category'];
            $datatype             = array();
            $data_fee_type        = array();
            $feetype              = $this->feetype_model->getFeetypeByCategory($value['id']);
            foreach ($feetype as $feekey => $feevalue) {
                $ftype            = $feevalue['id'];
                $datatype[$ftype] = $feevalue['type'];
            }
            $data_fee_type[]      = $datatype;
            $dataarray[$category] = $datatype;
            $array[]              = $dataarray;
        }
        $data['category_array'] = $array;
        $data['feecategory']    = $feecategory;
        $html                   = $this->load->view('reports/pdfStudentDeposite', $data, true);
        $pdfFilePath            = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function pdfStudentListByText()
    {
        $data                = [];
        $search_text         = $this->uri->segment(3);
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $resultlist          = $this->student_model->searchFullText($search_text);
        $data['resultlist']  = $resultlist;
        $html                = $this->load->view('reports/pdfStudentListByText', $data, true);
        $pdfFilePath         = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function marksreport()
    {
        $setting_result        = $this->setting_model->get();
        $data['settinglist']   = $setting_result;
        $exam_id               = $this->uri->segment(3);
        $class_id              = $this->uri->segment(4);
        $section_id            = $this->uri->segment(5);
        $data['exam_id']       = $exam_id;
        $data['class_id']      = $class_id;
        $data['section_id']    = $section_id;
        $exam_arrylist         = $this->exam_model->get($exam_id);
        $data['exam_arrylist'] = $exam_arrylist;
        $section               = $this->section_model->getClassNameBySection($class_id, $section_id);
        $data['class']         = $section;
        $examSchedule          = $this->examschedule_model->getDetailbyClsandSection($class_id, $section_id, $exam_id);
        $studentList           = $this->student_model->searchByClassSection($class_id, $section_id);
        $data['examSchedule']  = array();
        if (!empty($examSchedule)) {
            $new_array                      = array();
            $data['examSchedule']['status'] = "yes";
            foreach ($studentList as $stu_key => $stu_value) {
                $array                 = array();
                $array['student_id']   = $stu_value['id'];
                $array['roll_no']      = $stu_value['roll_no'];
                $array['firstname']    = $stu_value['firstname'];
                $array['lastname']     = $stu_value['lastname'];
                $array['admission_no'] = $stu_value['admission_no'];
                $array['dob']          = $stu_value['dob'];
                $array['father_name']  = $stu_value['father_name'];
                $x                     = array();
                foreach ($examSchedule as $ex_key => $ex_value) {
                    $exam_array                     = array();
                    $exam_array['exam_schedule_id'] = $ex_value['id'];
                    $exam_array['exam_id']          = $ex_value['exam_id'];
                    $exam_array['full_marks']       = $ex_value['full_marks'];
                    $exam_array['passing_marks']    = $ex_value['passing_marks'];
                    $exam_array['exam_name']        = $ex_value['name'];
                    $exam_array['exam_type']        = $ex_value['type'];
                    $student_exam_result            = $this->examresult_model->get_result($ex_value['id'], $stu_value['id']);
                    if (empty($student_exam_result)) {
                        $data['examSchedule']['status'] = "no";
                    } else {
                        $exam_array['attendence'] = $student_exam_result->attendence;
                        $exam_array['get_marks']  = $student_exam_result->get_marks;
                    }
                    $x[] = $exam_array;
                }
                $array['exam_array'] = $x;
                $new_array[]         = $array;
            }
            $data['examSchedule']['result'] = $new_array;
        } else {
            $s                    = array('status' => 'no');
            $data['examSchedule'] = $s;
        }
        $html        = $this->load->view('reports/marksreport', $data, true);
        $pdfFilePath = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
        $this->load->view('reports/marksreport', $data);
    }

    public function pdfStudentListByClassSection()
    {
        $data                = [];
        $class_id            = $this->uri->segment(3);
        $section_id          = $this->uri->segment(4);
        $setting_result      = $this->setting_model->get();
        $section             = $this->section_model->getClassNameBySection($class_id, $section_id);
        $data['class']       = $section;
        $data['settinglist'] = $setting_result;
        $resultlist          = $this->student_model->searchByClassSection($class_id, $section_id);
        $data['resultlist']  = $resultlist;
        $html                = $this->load->view('reports/pdfStudentListByClassSection', $data, true);
        $pdfFilePath         = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function pdfStudentListDifferentCriteria()
    {
        $data           = [];
        $class_id       = $this->input->get('class_id');
        $section_id     = $this->input->get('section_id');
        $category_id    = $this->input->get('category_id');
        $gender         = $this->input->get('gender');
        $rte            = $this->input->get('rte');
        $setting_result = $this->setting_model->get();
        $class          = $this->class_model->get($class_id);
        $data['class']  = $class;
        if ($section_id != "") {
            $section         = $this->section_model->getClassNameBySection($class_id, $section_id);
            $data['section'] = $section;
        }
        if ($gender != "") {
            $data['gender'] = $gender;
        }
        if ($rte != "") {
            $data['rte'] = $rte;
        }
        if ($category_id != "") {
            $category         = $this->category_model->get($category_id);
            $data['category'] = $category;
        }
        $data['settinglist'] = $setting_result;
        $resultlist          = $this->student_model->searchByClassSectionCategoryGenderRte($class_id, $section_id, $category_id, $gender, $rte);
        $data['resultlist']  = $resultlist;
        $html                = $this->load->view('reports/pdfStudentListDifferentCriteria', $data, true);
        $pdfFilePath         = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function pdfStudentListByClass()
    {
        $data                = [];
        $class_id            = $this->uri->segment(3);
        $section_id          = "";
        $setting_result      = $this->setting_model->get();
        $section             = $this->class_model->get($class_id);
        $data['class']       = $section;
        $data['settinglist'] = $setting_result;
        $resultlist          = $this->student_model->searchByClassSection($class_id, $section_id);
        $data['resultlist']  = $resultlist;
        $html                = $this->load->view('reports/pdfStudentListByClass', $data, true);
        $pdfFilePath         = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function transactionSearch()
    {
        $data                = [];
        $date_from           = $this->input->get('datefrom');
        $date_to             = $this->input->get('dateto');
        $setting_result      = $this->setting_model->get();
        $data['exp_title']   = 'Transaction From ' . $date_from . " To " . $date_to;
        $date_from           = date('Y-m-d', $this->customlib->datetostrtotime($date_from));
        $date_to             = date('Y-m-d', $this->customlib->datetostrtotime($date_to));
        $expenseList         = $this->expense_model->search("", $date_from, $date_to);
        $feeList             = $this->studentfee_model->getFeeBetweenDate($date_from, $date_to);
        $data['expenseList'] = $expenseList;
        $data['feeList']     = $feeList;
        $data['settinglist'] = $setting_result;
        $html                = $this->load->view('reports/transactionSearch', $data, true);
        $pdfFilePath         = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function pdfExamschdule()
    {
        $data                 = [];
        $setting_result       = $this->setting_model->get();
        $data['settinglist']  = $setting_result;
        $exam_id              = $this->uri->segment(3);
        $section_id           = $this->uri->segment(4);
        $class_id             = $this->uri->segment(5);
        $class                = $this->class_model->get($class_id);
        $data['class']        = $class;
        $examSchedule         = $this->examschedule_model->getDetailbyClsandSection($class_id, $section_id, $exam_id);
        $section              = $this->section_model->getClassNameBySection($class_id, $section_id);
        $data['section']      = $section;
        $data['examSchedule'] = $examSchedule;
        $exam                 = $this->exam_model->get($exam_id);
        $data['exam']         = $exam;
        $html                 = $this->load->view('reports/examSchedule', $data, true);
        $pdfFilePath          = $this->time . ".pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function get_betweendate($type)
    {
        $this->load->view('reports/betweenDate');
    }

    public function class_subject()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/class_subject_report');
        $data['title']       = 'Add Fees Type';
        $data['searchlist']  = $this->search_type;
        $class               = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist']   = $class;
        $data['search_type'] = '';
        $data['class_id']    = $class_id    = $this->input->post('class_id');
        $data['section_id']  = $section_id  = $this->input->post('section_id');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $data['subjects'] = array();
        } else {
            $data['section_list'] = $this->section_model->getClassBySection($this->input->post('class_id'));

            $data['resultlist'] = $this->subjecttimetable_model->getSubjectByClassandSection($class_id, $section_id);

            $subject = array();
            foreach ($data['resultlist'] as $value) {
                $subject[$value->subject_id][] = $value;
            }

            $data['subjects'] = $subject;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('reports/class_subject', $data);
        $this->load->view('layout/footer', $data);

    }

    public function admission_report()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/admission_report');
        $data['title']           = 'Add Fees Type';
        $data['searchlist']      = $this->search_type;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $searchterm              = '';
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $this->load->view('layout/header', $data);
        $this->load->view('reports/admission_report', $data);
        $this->load->view('layout/footer', $data);

    }
    
    public function searchreportvalidation()
    {

        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $error = array();

            $error['search_type'] = form_error('search_type');

            $array = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        } else {
            $search_type = $this->input->post('search_type');
            $date_from   = "";
            $date_to     = "";
            if ($search_type == 'period') {

                $date_from = $this->input->post('date_from');
                $date_to   = $this->input->post('date_to');
            }

            $params = array('search_type' => $search_type, 'date_from' => $date_from, 'date_to' => $date_to);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }
    }

    public function sibling_report()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/sibling_report');
        $data['title']           = 'Add Fees Type';
        $data['searchlist']      = $this->search_type;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $searchterm              = '';
        $condition               = array();
        $class                   = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist']       = $class;

        $data['class_id']     = $class_id     = $this->input->post('class_id');
        $data['section_id']   = $section_id   = $this->input->post('section_id');
        $data['section_list'] = $this->section_model->getClassBySection($this->input->post('class_id'));

        if (isset($_POST['class_id']) && $_POST['class_id'] != '') {
            $condition['classes.id'] = $_POST['class_id'];
        }

        if (isset($_POST['section_id']) && $_POST['section_id'] != '') {
            $condition['sections.id'] = $_POST['section_id'];
        }

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['resultlist'] = array();
        } else {
            $data['sibling_list'] = $this->student_model->sibling_reportsearch($searchterm, $carray = null, $condition);

            $sibling_parent = array();

            foreach ($data['sibling_list'] as $value) {

                $sibling_parent[] = $value['parent_id'];
            }

            $data['resultlist'] = $this->student_model->sibling_report($searchterm, $carray = null);
            $sibling            = array();

            foreach ($data['resultlist'] as $value) {

                if (in_array($value['parent_id'], $sibling_parent)) {

                    $sibling[$value['parent_id']][] = $value;

                }

            }
            $data['resultlist'] = $sibling;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('reports/sibling_report', $data);
        $this->load->view('layout/footer', $data);
    }    

    public function studentbookissuereport()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/library');
        $this->session->set_userdata('subsub_menu', 'Reports/library/book_issue_report');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['members']    = array('' => $this->lang->line('all'), 'student' => $this->lang->line('student'), 'teacher' => $this->lang->line('teacher'));
        $this->load->view('layout/header', $data);
        $this->load->view('reports/studentBookIssueReport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function bookduereport()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/library');
        $this->session->set_userdata('subsub_menu', 'Reports/library/bookduereport');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['members']     = array('' => $this->lang->line('all'), 'student' => $this->lang->line('student'), 'teacher' => $this->lang->line('teacher'));
        $this->load->view('layout/header', $data);
        $this->load->view('reports/bookduereport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function bookinventory()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/library');
        $this->session->set_userdata('subsub_menu', 'Reports/library/bookinventory');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $this->load->view('layout/header', $data);
        $this->load->view('reports/bookinventory', $data);
        $this->load->view('layout/footer', $data);
    }

    public function feescollectionreport()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/fees_collection');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('reports/feescollectionreport');
        $this->load->view('layout/footer');
    }

    public function gerenalincomereport()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/bookinventory');
        $data['searchlist'] = $this->customlib->get_searchtype();
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];

        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';

        }

        $start_date       = date('Y-m-d', strtotime($dates['from_date']));
        $end_date         = date('Y-m-d', strtotime($dates['to_date']));
        $data['label']    = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $listbook         = $this->book_model->bookinventory($start_date, $end_date);
        $data['listbook'] = $listbook;
        $this->load->view('layout/header', $data);
        $this->load->view('reports/gerenalincomereport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function studentinformation()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('reports/studentinformation');
        $this->load->view('layout/footer');
    }

    public function human_resource()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/human_resource');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('reports/human_resource');
        $this->load->view('layout/footer');
    }

        

    public function library()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/library');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('reports/library');
        $this->load->view('layout/footer');
    }

    public function inventory()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/inventory');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('reports/inventory');
        $this->load->view('layout/footer');
    }

    public function onlineexams()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/online_examinations');
        $this->session->set_userdata('subsub_menu', 'Reports/online_examinations/onlineexams');
        $condition          = "";
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['date_type']  = $this->customlib->date_type();

        $this->load->view('layout/header', $data);
        $this->load->view('reports/onlineexams', $data);
        $this->load->view('layout/footer', $data);

    }

    public function onlineexamsresult()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/examinations');
        $this->session->set_userdata('subsub_menu', 'Reports/examinations/onlineexamsresult');
        $condition           = "";
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];

        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';

        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        if (isset($_POST['date_type']) && $_POST['date_type'] != '') {

            $data['date_typeid'] = $_POST['date_type'];

            if ($_POST['date_type'] == 'exam_from_date') {

                $condition = " date_format(exam_from,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

            } elseif ($_POST['date_type'] == 'exam_to_date') {

                $condition = " date_format(exam_to,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

            }

        } else {

            $condition = " date_format(created_at,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

        }

        $data['resultlist'] = $this->onlineexam_model->onlineexamReport($condition);
        $this->load->view('layout/header', $data);
        $this->load->view('reports/onlineexamsresult', $data);
        $this->load->view('layout/footer', $data);
    }

    public function onlineexamattend()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/online_examinations');
        $this->session->set_userdata('subsub_menu', 'Reports/online_examinations/onlineexamattend');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['date_type']  = $this->customlib->date_type();
        $this->load->view('layout/header', $data);
        $this->load->view('reports/onlineexamattend', $data);
        $this->load->view('layout/footer', $data);
    }

    public function onlineexamrank()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/online_examinations');
        $this->session->set_userdata('subsub_menu', 'Reports/online_examinations/onlineexamrank');

        $exam_id             = $class_id             = $section_id             = $condition             = '';
        $studentrecord       = array();
        $getResultByStudent1 = array();

        $examList          = $this->onlineexam_model->get();
        $data['examList']  = $examList;
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'required');

        if ($this->form_validation->run() == false) {

        } else {
            if (isset($_POST['class_id']) && $_POST['class_id'] != '') {
                $class_id = $_POST['class_id'];
            }

            if (isset($_POST['section_id']) && $_POST['section_id'] != '') {
                $section_id = $_POST['section_id'];
            }

            if (isset($_POST['exam_id']) && $_POST['exam_id'] != '') {
                $exam_id = $_POST['exam_id'];
            }

            $exam = $this->onlineexam_model->get($exam_id);

            if (!empty($exam)) {

                $student_data = $this->onlineexam_model->searchAllOnlineExamStudents($exam_id, $class_id, $section_id, 1);

                if (!empty($student_data)) {
                    foreach ($student_data as $student_key => $student_value) {
                        $student_data[$student_key]['questions_results'] = $this->onlineexamresult_model->getResultByStudent($student_value['onlineexam_student_id'], $exam_id);
                    }
                }

                $data['exam']         = $exam;
                $data['student_data'] = $student_data;
            }

        }
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('reports/onlineexamrank', $data);
        $this->load->view('layout/footer', $data);

    }

    public function inventorystock()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/inventory');
        $this->session->set_userdata('subsub_menu', 'Reports/inventory/inventorystock');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $this->load->view('layout/header');
        $this->load->view('reports/inventorystock', $data);
        $this->load->view('layout/footer');
    }

    public function additem()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/inventory');
        $this->session->set_userdata('subsub_menu', 'Reports/inventory/additem');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';
        $this->load->view('layout/header', $data);
        $this->load->view('reports/additem', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getadditemlistbydt()
    {

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $itemresult    = $this->itemstock_model->get_ItemByBetweenDate($start_date, $end_date);

        $resultlist      = json_decode($itemresult);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $key => $value) {

                $row       = array();
                $row[]     = $value->name;
                $row[]     = $value->item_category;
                $row[]     = $value->item_supplier;
                $row[]     = $value->item_store;
                $row[]     = $value->quantity;
                $row[]     = $currency_symbol . amountFormat($value->purchase_price);
                $row[]     = date($this->customlib->getSchoolDateFormat(), strtotime($value->date));
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function issueinventory()
    {

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/inventory');
        $this->session->set_userdata('subsub_menu', 'Reports/inventory/issueinventory');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];

        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';

        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label']         = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        // $data['itemissueList'] = $this->itemissue_model->get_IssueInventoryReport($start_date, $end_date);

        $this->load->view('layout/header', $data);
        $this->load->view('reports/issueinventory', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getissueinventorylistbydt()
    {
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label']   = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $itemresult      = $this->itemissue_model->get_IssueInventoryReport($start_date, $end_date);
        $resultlist      = json_decode($itemresult);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $key => $value) {                

                $row   = array();
                $row[] = $value->item_name;
                $row[] = $value->note;                
                
                $row[] = $value->item_category;
                if ($value->return_date == "0000-00-00") {
                    $return_date = "";
                } else {
                    $return_date = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->return_date));
                }
                
                $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->issue_date)) . " - " . $return_date;
                $row[]     = $value->staff_name . " " . $value->surname . "(" . $value->employee_id . ")";
                $row[]     = $value->issued_by_name . " " . $value->issued_by_surname . "(" . $value->issued_by_employee_id . ")";        
                
                $row[]     = $value->quantity;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function finance()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('reports/finance');
        $this->load->view('layout/footer');
    }    

    public function student_profile()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_profile');
        $data['title']           = 'Add Fees Type';
        $data['searchlist']      = $this->search_type;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $searchterm              = '';
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['class_id']        = $class_id        = $this->input->post('class_id');
        $data['section_id']      = $section_id      = $this->input->post('section_id');
        $condition1              = "";
        $condition2              = "";
        // $data['section_list']    = $this->section_model->getClassBySection($this->input->post('class_id'));

        $data['search_type']  = '';
        $data['filter_label'] = '';
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $between_date        = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $search_type = $_POST['search_type'];

            $from_date            = date('Y-m-d', strtotime($between_date['from_date']));
            $to_date              = date('Y-m-d', strtotime($between_date['to_date']));
            $condition2           = " date_format(admission_date,'%Y-%m-%d') between  '" . $from_date . "' and '" . $to_date . "'";
            $data['filter_label'] = date($this->customlib->getSchoolDateFormat(), strtotime($from_date)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($to_date));
        }

        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $data['resultlist'] = array();
        } else {
            $condition1         = " classes.id='" . $this->input->post('class_id') . "' and sections.id='" . $this->input->post('section_id') . "'";
            $data['resultlist'] = $this->student_model->student_profile($condition1, $condition2);
        }
        $this->load->view('layout/header', $data);
        $this->load->view('reports/student_profile', $data);
        $this->load->view('layout/footer', $data);
    }

    public function staff_report()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/human_resource');
        $this->session->set_userdata('subsub_menu', 'Reports/human_resource/staff_report');
        $data['title'] = 'Add Fees Type';
        $data['searchlist'] = $this->search_type;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $searchterm = '';
        $condition = "";
        
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            // Existing code for search filters
            $between_date = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $search_type = $_POST['search_type'];
            $from_date = date('Y-m-d', strtotime($between_date['from_date']));
            $to_date = date('Y-m-d', strtotime($between_date['to_date']));
            $condition .= " and date_format(date_of_joining,'%Y-%m-%d') between '" . $from_date . "' and '" . $to_date . "'";
            $data['filter_label'] = date($this->customlib->getSchoolDateFormat(), strtotime($from_date)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($to_date));
        }

        if (isset($_POST['staff_status']) && $_POST['staff_status'] != '') {
            if ($_POST['staff_status'] == 'both') {
                $search_status = "1,2";
            } elseif ($_POST['staff_status'] == '2') {
                $search_status = "0";
            } else {
                $search_status = "1";
            }
            $condition .= " and `staff`.`is_active` in (" . $search_status . ")";
            $data['status_val'] = $_POST['staff_status'];
        } else {
            $data['status_val'] = 1;
        }
    
        if (isset($_POST['role']) && $_POST['role'] != '') {
            $condition .= " and `staff_roles`.`role_id`=" . $_POST['role'];
            $data['role_val'] = $_POST['role'];
        }
    
        if (isset($_POST['designation']) && $_POST['designation'] != '') {
            $condition .= " and `staff_designation`.`id`=" . $_POST['designation'];
            $data['designation_val'] = $_POST['designation'];
        }

        $data['resultlist'] = $this->staff_model->staff_report($condition);

        $data['ethnicity_gender_stats'] = $this->staff_model->get_staff_by_ethnicity_gender();
        $data['position_jobtype_gender_stats'] = $this->staff_model->get_staff_by_position_jobtype_gender();

        $leave_type = $this->leavetypes_model->getLeaveType();
        foreach ($leave_type as $key => $leave_value) {
            $data['leave_type'][$leave_value['id']] = $leave_value['type'];
        }
        $data['status'] = $this->customlib->staff_status();
        $data['roles'] = $this->role_model->get();
        $data['designation'] = $this->designation_model->get();
        $data['fields'] = $this->customfield_model->get_custom_fields('staff', 1);
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
    
        $this->load->view('layout/header', $data);
        $this->load->view('reports/staff_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function lesson_plan()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/lesson_plan');
        $this->session->set_userdata('subsub_menu', 'Reports/lesson_plan/lesson_plan');
        $data                     = array();
        $data['subjects_data']    = array();
        $class                    = $this->class_model->get();
        $data['classlist']        = $class;
        $data['class_id']         = "";
        $data['section_id']       = "";
        $data['subject_group_id'] = "";
        $data['subject_id']       = "";
        $data['lessons']          = array();
        $lebel                    = "";

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_group_id', $this->lang->line('subject'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

        } else {

            $data['class_id']               = $_POST['class_id'];
            $data['section_id']             = $_POST['section_id'];
            $data['subject_group_id']       = $_POST['subject_group_id'];
            $subjects                       = $this->subjectgroup_model->getGroupsubjects($_POST['subject_group_id']);
            $subject_group_class_sectionsId = $this->lessonplan_model->getsubject_group_class_sectionsId($_POST['class_id'], $_POST['section_id'], $_POST['subject_group_id']);

            foreach ($subjects as $key => $value) {
                $show_status     = 0;
                $teacher_summary = array();
                $lesson_result   = array();
                $complete        = 0;
                $incomplete      = 0;
                $array[]         = $value;
                $lebel           = ($value->code == '') ? $value->name : $value->name . ' (' . $value->code . ')';

                $subject_details = $this->syllabus_model->get_subjectstatus($value->id, $subject_group_class_sectionsId['id']);
                if ($subject_details[0]->total != 0) {

                    $complete   = ($subject_details[0]->complete / $subject_details[0]->total) * 100;
                    $incomplete = ($subject_details[0]->incomplete / $subject_details[0]->total) * 100;

                    $data['subjects_data'][$value->id] = array(
                        'lebel'      => $lebel,
                        'complete'   => round($complete),
                        'incomplete' => round($incomplete),
                        'id'         => $value->id . '_' . $value->code,
                        'total'      => $subject_details[0]->total,
                        'name'       => $value->name,
                    );

                } else {

                    $data['subjects_data'][$value->id] = array(
                        'lebel'      => $lebel,
                        'complete'   => 0,
                        'incomplete' => 0,
                        'id'         => $value->id . '_' . $value->code,
                        'total'      => 0,
                        'name'       => $value->name,

                    );
                }

                $syllabus_report = $this->syllabus_model->get_subjectsyllabussreport($value->id, $subject_group_class_sectionsId['id']);
                $lesson_result   = array();
                foreach ($syllabus_report as $syllabus_reportkey => $syllabus_reportvalue) {

                    $topic_data     = array();
                    $topic_result   = $this->syllabus_model->get_topicbylessonid($syllabus_reportvalue['id']);
                    $topic_complete = 0;
                    foreach ($topic_result as $topic_resultkey => $topic_resultvalue) {
                        if ($topic_resultvalue['status'] == 1) {
                            $topic_complete++;
                        }

                        $topic_data[] = array('name' => $topic_resultvalue['name'], 'status' => $topic_resultvalue['status'], 'complete_date' => $topic_resultvalue['complete_date']);
                    }
                    $total_topic = count($topic_data);
                    if ($total_topic > 0) {
                        $incomplete_percent = round((($total_topic - $topic_complete) / $total_topic) * 100);
                        $complete_percent   = round(($topic_complete / $total_topic) * 100);
                    } else {
                        $incomplete_percent = 0;
                        $complete_percent   = 0;
                    }

                    $show_status     = 1;
                    $lesson_result[] = array('name' => $syllabus_reportvalue['name'], 'topics' => $topic_data, 'incomplete_percent' => $incomplete_percent, 'complete_percent' => $complete_percent);

                }

                $data['subjects_data'][$value->id]['lesson_summary'] = $lesson_result;

            }
        }

        $data['status'] = array('1' => $this->lang->line('complete'), '0' => $this->lang->line('incomplete'));
        $this->load->view('layout/header', $data);
        $this->load->view('reports/syllabus', $data);
        $this->load->view('layout/footer', $data);
    }

    public function teachersyllabusstatus()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/lesson_plan');
        $this->session->set_userdata('subsub_menu', 'Reports/lesson_plan/teachersyllabusstatus');
        $data                     = array();
        $data['subjects_data']    = array();
        $class                    = $this->class_model->get();
        $data['classlist']        = $class;
        $data['class_id']         = "";
        $data['section_id']       = "";
        $data['subject_group_id'] = "";
        $data['subject_id']       = "";
        $data['lessons']          = array();

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_group_id', $this->lang->line('subject_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

        } else {
            $lebel = "";

            $data['class_id']         = $_POST['class_id'];
            $data['section_id']       = $_POST['section_id'];
            $data['subject_group_id'] = $_POST['subject_group_id'];
            $data['subject_id']       = $_POST['subject_id'];

            $subject_group_class_sectionsId = $this->lessonplan_model->getsubject_group_class_sectionsId($_POST['class_id'], $_POST['section_id'], $_POST['subject_group_id']);

            if (!$subject_group_class_sectionsId || !isset($subject_group_class_sectionsId['id'])) {
                $data['error_message'] = 'No subject group class section found for the selected criteria.';
                $this->load->view('layout/header', $data);
                $this->load->view('reports/teacherSyllabusStatus', $data);
                $this->load->view('layout/footer', $data);
                return;
            }

            $teacher_summary          = array();
            $complete                 = 0;
            $incomplete               = 0;
            $data['subject_name']     = "";
            $data['subject_complete'] = 0;
            $subjectdata              = $this->subject_model->get($_POST['subject_id']);

            if (!$subjectdata || !isset($subjectdata['id'])) {
                $data['error_message'] = 'No subject found for the selected criteria.';
                $this->load->view('layout/header', $data);
                $this->load->view('reports/teacherSyllabusStatus', $data);
                $this->load->view('layout/footer', $data);
                return;
            }

            $subject_details = $this->syllabus_model->get_subjectstatus($_POST['subject_id'], $subject_group_class_sectionsId['id']);
            if (isset($subject_details[0]) && isset($subject_details[0]->total) && $subject_details[0]->total != 0) {
                $complete   = ($subject_details[0]->complete / $subject_details[0]->total) * 100;
                $incomplete = ($subject_details[0]->incomplete / $subject_details[0]->total) * 100;
                if ($subjectdata['code'] == '') {
                    $lebel = $subjectdata['name'];
                } else {
                    $lebel = $subjectdata['name'] . ' (' . $subjectdata['code'] . ')';
                }
                $data['subjects_data'][$subjectdata['id']] = array(
                    'lebel'      => $lebel,
                    'complete'   => round($complete),
                    'incomplete' => round($incomplete),
                    'id'         => $subjectdata['id'] . '_' . $subjectdata['code'],
                );
                $data['subject_complete'] = round($complete);
            } else {
                $data['subjects_data'][$subjectdata['id']] = array(
                    'lebel'      => isset($lebel) ? $lebel : '',
                    'complete'   => 0,
                    'incomplete' => 0,
                    'id'         => $subjectdata['id'] . '_' . (isset($subjectdata['code']) ? $subjectdata['code'] : ''),
                );
                $data['subject_complete'] = 0;
            }

            $teachers_report = $this->syllabus_model->get_subjectteachersreport($_POST['subject_id'], $subject_group_class_sectionsId['id']);
            if (is_array($teachers_report)) {
                foreach ($teachers_report as $teachers_reportkey => $teachers_reportvalue) {
                    if ($teachers_reportvalue['code'] == '') {
                        $data['subject_name'] = $teachers_reportvalue['subject_name'];
                    } else {
                        $data['subject_name'] = $teachers_reportvalue['subject_name'] . " (" . $teachers_reportvalue['code'] . ")";
                    }
                    $syllabus_id       = explode(',', $teachers_reportvalue['subject_syllabus_id']);
                    $staff_periodsdata = array();
                    foreach ($syllabus_id as $syllabus_idkey => $syllabus_idvalue) {
                        $staff_periods       = $this->syllabus_model->get_subjectsyllabusbyid($syllabus_idvalue);
                        $staff_periodsdata[] = $staff_periods;
                    }
                    $teacher_summary[] = array(
                        'name'           => $teachers_reportvalue['name'],
                        'total_periods'  => $teachers_reportvalue['total_priodes'],
                        'summary_report' => $staff_periodsdata,
                    );
                }
            }
            $data['subjects_data'][$subjectdata['id']]['teachers_summary'] = $teacher_summary;

        }

        $this->load->view('layout/header', $data);
        $this->load->view('reports/teacherSyllabusStatus', $data);
        $this->load->view('layout/footer', $data);
    }

    public function alumnireport()
    {
        if (!$this->rbac->hasPrivilege('alumni_report', 'can_view')) {
            access_denied();
        }
        $data                = array();
        $data['sessionlist'] = $this->session_model->get();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/alumni_report');
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['title']           = $this->lang->line('alumni_student_for_passout_session');
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['session_id']      = $session_id      = "";
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();
        $alumni_student          = $this->alumni_model->get();
        $alumni_studets          = array();
        foreach ($alumni_student as $key => $value) {
            $alumni_studets[$value['student_id']] = $value;
        }
        $data['alumni_studets'] = $alumni_studets;
        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $button = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('reports/alumnireport', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class              = $this->input->post('class_id');
            $section            = $this->input->post('section_id');
            $search             = $this->input->post('search');
            $search_text        = $this->input->post('search_text');
            $data['session_id'] = $session_id = $this->input->post('session_id');
            if (isset($search)) {
                if ($search == 'search_filter') {
                    $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
                    $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                    if ($this->form_validation->run() == false) {

                    } else {
                        $data['searchby']    = "filter";
                        $data['class_id']    = $this->input->post('class_id');
                        $data['section_id']  = $this->input->post('section_id');
                        $data['search_text'] = $this->input->post('search_text');
                        $resultlist          = $this->student_model->search_alumniStudentReport($class, $section, $session_id);
                        $data['resultlist']  = $resultlist;

                    }
                } else if ($search == 'search_full') {
                    $data['searchby'] = "text";

                    $data['search_text'] = trim($this->input->post('search_text'));
                    $resultlist          = $this->student_model->search_alumniStudentbyAdmissionNoReport($search_text, $carray);
                    $data['resultlist']  = $resultlist;

                }
            }

            $this->load->view('layout/header');
            $this->load->view('reports/alumnireport', $data);
            $this->load->view('layout/footer');
        }

    }

    public function boys_girls_ratio()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/boys_girls_ratio');
        $data['title']           = 'Add Fees Type';
        $data['searchlist']      = $this->search_type;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $searchterm              = '';
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        foreach ($data['classlist'] as $key => $value) {
            $carray[] = $value['id'];
        }
    
        $data['resultlist'] = $this->student_model->student_ratio();
        $total_boys         = $total_girls         = 0;
        foreach ($data['resultlist'] as $key => $value) {
    
            $total_boys += $value['male'];
            $total_girls += $value['female'];
    
            // Added program_title to the result array
            $data['result'][] = array(
                'total_student' => $value['total_student'], 
                'male' => $value['male'], 
                'female' => $value['female'], 
                'class' => $value['class'], 
                'section' => $value['section'], 
                'class_id' => $value['class_id'], 
                'section_id' => $value['section_id'], 
                'program_title' => isset($value['program_title']) ? $value['program_title'] : 'N/A', // Added this line
                'boys_girls_ratio' => $this->getRatio($value['male'], $value['female'])
            );
        }
    
        $data['all_boys_girls_ratio']      = $this->getRatio($total_boys, $total_girls);
        $data['all_student_teacher_ratio'] = $this->getRatio($total_boys, $total_girls);
    
        $this->load->view('layout/header', $data);
        $this->load->view('reports/student_ratio_report', $data);
        $this->load->view('layout/footer', $data);
    }


 public function student_teacher_ratio()
{
    $this->session->set_userdata('top_menu', 'Reports');
    $this->session->set_userdata('sub_menu', 'Reports/student_information');
    $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_teacher_ratio');
    $data['title']           = 'Add Fees Type';
    $data['searchlist']      = $this->search_type;
    $data['sch_setting']     = $this->sch_setting_detail;
    $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
    $searchterm              = '';    

    $data['resultlist'] = $this->student_model->student_ratio();
    $total_boys         = $total_girls         = $all_teacher         = $all_student         = 0;
    foreach ($data['resultlist'] as $key => $value) {

        $all_student += $value['total_student'];
        $count_classteachers = array();
        $count_classteachers = $this->student_model->count_classteachers($value['class_id'], $value['section_id']);

        if (!empty($count_classteachers)) {
            $total_teacher = $count_classteachers;
        } else {
            $total_teacher = 0;
        }

        // Added program_title to the result array
        $data['result'][] = array(
            'total_student' => $value['total_student'], 
            'male' => $value['male'], 
            'female' => $value['female'], 
            'class' => $value['class'], 
            'section' => $value['section'], 
            'class_id' => $value['class_id'], 
            'section_id' => $value['section_id'], 
            'total_teacher' => $total_teacher, 
            'program_title' => isset($value['program_title']) ? $value['program_title'] : 'N/A', // Added this line
            'boys_girls_ratio' => $this->getRatio($value['male'], $value['female']), 
            'teacher_ratio' => $this->getRatio($value['total_student'], $total_teacher)
        );

        $all_teacher += $total_teacher;
    }

    $data['all_student_teacher_ratio'] = $this->getRatio($all_student, $all_teacher);
    $this->load->view('layout/header', $data);
    $this->load->view('reports/teacher_ratio_report', $data);
    $this->load->view('layout/footer', $data);
}

    public function getRatio($num1, $num2)
    {
        if ($num2 > 0 && $num1 > 0) {
            $num = round($num2 / $num1, 2);

        } else {
            $num = 0;
        }

        if ($num1 == '0') {
            $by = 0;
            return "$by:$num2";
        } else {
            $by = 1;
            return "$by:$num";
        }

    }    

    public function getAvailQuantity($item_id)
    {
        $data      = $this->item_model->getItemAvailable($item_id);
        $available = ($data['added_stock'] - $data['issued']);
        if ($available >= 0) {
            return $available;
        } else {
            return 0;
        }
    }

    public function getinventorylist()
    {
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        $dstockresult1 = $this->itemstock_model->get_currentstock($start_date, $end_date);
        $m             = json_decode($dstockresult1);
        $dt_data       = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $available_stock = $this->getAvailQuantity($value->id);
                $row             = array();
                $row[]           = $value->name;
                 
                $row[]           = $value->item_category;
                $row[]           = $value->item_supplier;
                $row[]           = $value->item_store;
                $row[]           = $available_stock;
                $row[]           = $value->available_stock;
                $row[]           = $value->total_not_returned;
                $dt_data[]       = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

   

    public function dtadmissionreport()
    {
        $sch_setting = $this->sch_setting_detail;
        $searchterm  = '';
        $class       = $this->class_model->get();
        $classlist   = $class;
        $count       = 0;

        foreach ($classlist as $key => $value) {
            $carray[] = $value['id'];
        }
        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $between_date        = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $search_type = $_POST['search_type'];
        } else {

            $between_date        = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = $search_type = '';
        }

        $from_date    = date('Y-m-d', strtotime($between_date['from_date']));
        $to_date      = date('Y-m-d', strtotime($between_date['to_date']));
        $condition    = " date_format(admission_date,'%Y-%m-%d') between  '" . $from_date . "' and '" . $to_date . "'";
        $filter_label = date($this->customlib->getSchoolDateFormat(), strtotime($from_date)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($to_date));

        $result = $this->student_model->admission_report($searchterm, $carray, $condition);

        $resultlist = json_decode($result);
        $dt_data    = array();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $student) {

                $count++;
                $viewbtn = "<a  href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";

                $row = array();

                $row[] = $student->admission_no;
                $row[] = $viewbtn;
                $row[] = $student->class . " (" . $student->section . ")";
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }
                if ($student->dob != null && $student->dob != '0000-00-00') {
                    $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student->dob));
                } else {
                    $row[] = "";
                }

                if ($sch_setting->admission_date) {
                    if ($student->admission_date != null && $student->admission_date != '0000-00-00') {

                        $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student->admission_date));
                    } else {
                        $row[] = "";
                    }
                }

                $row[] = $this->lang->line(strtolower($student->gender));

                if ($sch_setting->category) {
                    $row[] = $student->category;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno;
                }

                $dt_data[] = $row;
            }

            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = $this->lang->line('total_admission_in_this_duration');
            $footer_row[] = $filter_label;
            $footer_row[] = $count;
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $dt_data[]    = $footer_row;

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    function to get formparateter */
    public function getformparameter()
    {
        $search_type = $this->input->post('search_type');
        $date_type   = $this->input->post("date_type");
        $date_from   = "";
        $date_to     = "";
        if ($search_type == 'period') {
            $date_from = $this->input->post('date_from');
            $date_to   = $this->input->post('date_to');
        }

        $params = array('search_type' => $search_type, 'date_type' => $date_type, 'date_from' => $date_from, 'date_to' => $date_to);
        $array  = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function dtexamreportlist()
    {
        $search_type = $this->input->post('search_type');
        $date_type   = $this->input->post('date_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');

        $data['date_typeid'] = '';
        if (isset($search_type) && $search_type != '') {

            $dates               = $this->customlib->get_betweendate($search_type);
            $data['search_type'] = $search_type;

        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';

        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        if (isset($date_type) && $date_type != '') {

            $data['date_typeid'] = $date_type;

            if ($date_type == 'exam_from_date') {
                $condition = " date_format(exam_from,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
            } elseif ($date_type == 'exam_to_date') {
                $condition = " date_format(exam_to,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
            }

        } else {
            $condition = " date_format(onlineexam.created_at,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
        }

        $sch_setting = $this->sch_setting_detail;
        $results     = $this->onlineexam_model->onlineexamReport($condition);

        $resultlist = json_decode($results);
        $dt_data    = array();

        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $subject_value) {

                if ($subject_value->is_active == 1) {
                    $publish_btn = " <i class='fa fa-check-square-o'></i><span style='display:none'>" . $this->lang->line('yes') . "</span>";
                } else {
                    $publish_btn = " <i class='fa fa-exclamation-circle'></i><span style='display:none'>" . $this->lang->line('no') . "</span>";
                }

                if ($subject_value->is_active == 1) {
                    $result_publish = " <i class='fa fa-check-square-o'></i><span style='display:none'>" . $this->lang->line('yes') . "</span>";
                } else {
                    $result_publish = "<i class='fa fa-exclamation-circle'></i><span style='display:none'>" . $this->lang->line('no') . "</span>";
                }

                $row   = array();
                $row[] = $subject_value->exam;
                $row[] = $subject_value->attempt;
                $row[] = $this->customlib->dateyyyymmddToDateTimeformat($subject_value->exam_from, false);
                $row[] = $this->customlib->dateyyyymmddToDateTimeformat($subject_value->exam_to, false);
                $row[] = $subject_value->duration;
                $row[] = $subject_value->assign;
                $row[] = $subject_value->questions;
                $row[] = $publish_btn;
                $row[] = $result_publish;

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /* function to get exam attempt report using datatable*/

    public function dtexamattemptreport()
    {
        $condition   = "";
        $search_type = $this->input->post('search_type');
        $date_type   = $this->input->post('date_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['date_type']  = $this->customlib->date_type();

        $data['date_typeid'] = '';
        if (isset($search_type) && $search_type != '') {
            $dates               = $this->customlib->get_betweendate($search_type);
            $data['search_type'] = $search_type;
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        if (isset($date_type) && $date_type != '') {

            $data['date_typeid'] = $_POST['date_type'];

            if ($date_type == 'exam_from_date') {

                $condition .= " and date_format(onlineexam.exam_from,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

            } elseif ($date_type == 'exam_to_date') {

                $condition .= " and date_format(onlineexam.exam_to,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
            }

        } else {

            $condition .= " and  date_format(onlineexam.created_at,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

        }

        $result      = $this->onlineexam_model->onlineexamatteptreport($condition);
        $sch_setting = $this->sch_setting_detail;
        $resultlist  = json_decode($result);
        $dt_data     = array();

        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $student_value) {

                $exams = explode(',', $student_value->exams);

                $exam_name               = "";
                $exam_from               = "";
                $exam_to                 = "";
                $exam_duration           = "";
                $exam_publish            = "";
                $exam_resultpublish      = "";
                $exam_publishprint       = "";
                $exam_resultpublishprint = "";
                foreach ($exams as $exams_key => $exams_value) {
                    $exam_details = explode('@', $exams_value);

                    if (count($exam_details) == 9) {

                        $exam_name .= $exam_details[1];
                        $exam_from .= date($this->customlib->getSchoolDateFormat(), $this->customlib->dateYYYYMMDDtoStrtotime($exam_details[3]));
                        $exam_to .= date($this->customlib->getSchoolDateFormat(), $this->customlib->dateYYYYMMDDtoStrtotime($exam_details[4]));
                        $exam_duration .= $exam_details[5];
                        $exam_publish .= ($exam_details[7] == 1) ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-exclamation-circle' aria-hidden='true'></i>";
                        $exam_resultpublish .= ($exam_details[8] == 1) ? "<i class='fa fa-check-square-o' aria-hidden='true'></i>" : "<i class='fa fa-exclamation-circle' aria-hidden='true'></i>";

                        $exam_publishprint .= ($exam_details[7] == 1) ? "<span style='display:none'>" . $this->lang->line('yes') . "</span>" : "<span style='display:none'>" . $this->lang->line('no') . "</span>";
                        $exam_resultpublishprint .= ($exam_details[8] == 1) ? "<span style='display:none'>" . $this->lang->line('yes') . "</span>" : "<span style='display:none'>" . $this->lang->line('no') . "</span>";

                        $exam_name .= '<br>';
                        $exam_from .= "<br>";
                        $exam_to .= "<br>";
                        $exam_duration .= "<br>";
                        $exam_publish .= "<br>";
                        $exam_resultpublish .= "<br>";
                        $exam_publishprint .= "<br>";
                        $exam_resultpublishprint .= "<br>";
                    }
                }

                $row   = array();
                $row[] = $student_value->admission_no;
                $row[] = $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $sch_setting->middlename, $sch_setting->lastname);
                $row[] = $student_value->class;
                $row[] = $student_value->section;
                $row[] = $exam_name;
                $row[] = $exam_from;
                $row[] = $exam_to;
                $row[] = $exam_duration;
                $row[] = $exam_publish . $exam_publishprint;
                $row[] = $exam_resultpublish . $exam_resultpublishprint;

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    function to get formparateter */
    /**
     * Library dates are stored as Bikram Sambat (YYYY-MM-DD).
     * Empty search type = no date filter (show all).
     *
     * @return array{0:?string,1:?string} start, end as BS Y-m-d
     */
    private function library_report_bs_date_bounds()
    {
        $search_type = $this->input->post('search_type');
        if ($search_type === null || $search_type === '') {
            return array(null, null);
        }
        $dates = $this->customlib->get_betweendate_bs($search_type);
        $start = $this->customlib->parseLibraryDateYmd(isset($dates['from_date']) ? $dates['from_date'] : '');
        $end   = $this->customlib->parseLibraryDateYmd(isset($dates['to_date']) ? $dates['to_date'] : '');
        return array(
            $start !== '' ? $start : null,
            $end !== '' ? $end : null,
        );
    }

    public function getbookissueparameter()
    {
        $search_type  = $this->input->post('search_type');
        $members_type = $this->input->post("members_type");
        $date_from    = "";
        $date_to      = "";
        if ($search_type == 'period') {
            $date_from = $this->input->post('date_from');
            $date_to   = $this->input->post('date_to');
        }

        $params = array(
            'search_type'  => $search_type,
            'members_type' => $members_type,
            'date_from'    => $date_from,
            'date_to'      => $date_to,
            'section_id'   => $this->input->post('section_id'),
            'program_id'   => $this->input->post('program_id'),
            'class_id'     => $this->input->post('class_id'),
        );
        $array  = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);

    }

    /* function to get book issue report by using datatable */
    public function dtbookissuereportlist()
    {
        $superadmin_visible = $this->customlib->superadmin_visible();
        $getStaffRole       = $this->customlib->getStaffRole();
        $staffrole          = json_decode($getStaffRole);
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['members']    = array('' => $this->lang->line('all'), 'student' => $this->lang->line('student'), 'teacher' => $this->lang->line('teacher'));
        list($start_date, $end_date) = $this->library_report_bs_date_bounds();

        $result = $this->bookissue_model->studentBookIssue_report($start_date, $end_date);
        $sch_setting = $this->sch_setting_detail;
        $resultlist = json_decode($result);
        $dt_data    = array();

        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $value) {

                $row   = array();
                $row[] = $value->book_title;
                $row[] = $value->book_no;
                $row[] = $this->customlib->formatLibraryDateBsForDisplay($value->issue_date);
                $row[] = $this->customlib->formatLibraryDateBsForDisplay($value->duereturn_date);
                $row[] = $value->members_id;
                $row[] = $value->library_card_no;
                
                if ($value->admission) {
                    $admission = ' (' . $value->admission . ')';
                    $row[]     = $value->admission;
                } else {
                    $admission = '';
                    $row[]     = "";
                }

                if ($value->employee_id) {
                    $staff_employee_id = ' (' . $value->employee_id . ')';
                } else {
                    $staff_employee_id = '';
                }

                if ($value->member_type == 'student') {
                    $row[] = $this->customlib->getFullName($value->firstname, $value->middlename, $value->lastname, $sch_setting->middlename, $sch_setting->lastname) . $admission;
                } else {

                    if ($superadmin_visible == 'disabled') {
                        if ($staffrole->id != 7) {
                            $staffresult = $this->staff_model->getAll($value->staff_id);

                            if (isset($staffresult['role_id']) && $staffresult['role_id'] == 7) {
                                $row[] = '';
                            } else {
                                $row[] = ucwords($value->staff_name) . $staff_employee_id;
                            }

                        } else {
                            $row[] = ucwords($value->staff_name) . $staff_employee_id;
                        }
                    } else {
                        $row[] = ucwords($value->staff_name) . $staff_employee_id;
                    }

                }
                $row[] = ucwords($value->member_type);

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /* function to get book due report by using datatable */
    public function dtbookduereportlist()
    {
        $superadmin_visible = $this->customlib->superadmin_visible();
        $getStaffRole       = $this->customlib->getStaffRole();
        $staffrole          = json_decode($getStaffRole);

        $data['members'] = array('' => $this->lang->line('all'), 'student' => $this->lang->line('student'), 'teacher' => $this->lang->line('teacher'));
        list($start_date, $end_date) = $this->library_report_bs_date_bounds();
        $issued_books  = $this->bookissue_model->bookduereport($start_date, $end_date);
        $sch_setting = $this->sch_setting_detail;
        $resultlist = json_decode($issued_books);
        $dt_data    = array();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $value) {

                $row   = array();
                $row[] = $value->book_title;
                $row[] = $value->book_no;
                $row[] = $this->customlib->formatLibraryDateBsForDisplay($value->issue_date);
                $row[] = $this->customlib->formatLibraryDateBsForDisplay($value->duereturn_date);
                $row[] = $value->members_id;
                $row[] = $value->library_card_no;
                if ($value->admission != 0) {
                    $row[] = $value->admission;
                } else {
                    $row[] = "";
                }
                if ($value->member_type == 'student') {
                    $row[] = $this->customlib->getFullName($value->firstname, $value->middlename, $value->lastname, $sch_setting->middlename, $sch_setting->lastname) . ' (' . $value->admission . ')';
                } else {

                    if (!empty($value->employee_id)) {

                        if ($superadmin_visible == 'disabled') {
                            if ($staffrole->id != 7) {
                                $staffresult = $this->staff_model->getAll($value->staff_id);
                                if ($staffresult['role_id'] == 7) {
                                    $row[] = '';
                                } else {
                                    $row[] = ucwords($value->fname) . " " . ucwords($value->lname) . ' (' . $value->employee_id . ')';
                                }

                            } else {
                                $row[] = ucwords($value->fname) . " " . ucwords($value->lname) . ' (' . $value->employee_id . ')';
                            }
                        } else {
                            $row[] = ucwords($value->fname) . " " . ucwords($value->lname) . ' (' . $value->employee_id . ')';
                        }

                    } else {
                        $row[] = '';
                    }

                }
                $row[] = ucwords($value->member_type);

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /* function to get book issue return report by using datatable */
    public function dtbookinventoryreportlist()
    {
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        list($start_date, $end_date) = $this->library_report_bs_date_bounds();
        $listbook = $this->book_model->bookinventory($start_date, $end_date);

        $resultlist = json_decode($listbook);
        $dt_data    = array();

        if (!empty($resultlist->data)) {

            $editbtn   = "";
            $deletebtn = "";
            foreach ($resultlist->data as $resultlist_key => $value) {

                
                $condition = "<p class='text text-info no-print' >" . $value->description . "</p>";
                

                $title = "<a href='#' data-toggle='popover' class='detail_popover'>" . $value->book_title . "</a> <div class='fee_detail_popover' style='display: none'> " . $condition . " </div> ";

                $row   = array();
                $row[] = $title;
                $row[] = $value->book_no;
                $row[] = $value->isbn_no;
                $row[] = $value->publish;
                $row[] = $value->author;
                $row[] = $value->subject;
                $row[] = $value->rack_no;
                $row[] = $value->qty;
                $row[] = $value->qty - $value->total_issue;
                $row[]     = $value->qty - ($value->qty - $value->total_issue);
                $row[]     = ($currency_symbol . amountFormat($value->perunitcost));
                $row[]     = $this->customlib->formatLibraryDateBsForDisplay($value->postdate);
                $row[]     = $editbtn . " " . $deletebtn;
                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    this function is used to get and return  form parameter without applying any validation  */
    public function getsearchtypeparam()
    {
        $search_type = $this->input->post('search_type');
        $date_from   = "";
        $date_to     = "";
        if ($search_type == 'period') {

            $date_from = $this->input->post('date_from');
            $date_to   = $this->input->post('date_to');
        }

        $params = array('search_type' => $search_type, 'date_from' => $date_from, 'date_to' => $date_to);
        $array  = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }    

    public function online_admission_report()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/online_admission');
        $this->session->set_userdata('subsub_menu', 'Reports/online_admission');
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $this->load->view('layout/header', $data);
        $this->load->view('reports/online_admission_report', $data);
        $this->load->view('layout/footer', $data);

    }

    public function checkvalidation()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $status     = $this->input->post('status');
        $params     = array('class_id' => $class_id, 'section_id' => $section_id, 'status' => $status);
        $array      = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function dtonlineadmissionreportlist()
    {
        $class_id   = $this->input->post("class_id");
        $section_id = $this->input->post("section_id");
        $status     = $this->input->post("status");
        $sch_setting = $this->sch_setting_detail;
        $result          = $this->student_model->getonlineadmissionreport($class_id, $section_id, $status);
        $resultlist      = json_decode($result);
        $dt_data         = array();
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $online_admission) {

                $dob                      = "";
                $category                 = "";
                $online_admission_payment = '';

                if ($online_admission->form_status == 1) {
                    $form_status = '<span class="label label-success">' . $this->lang->line('submitted') . '</span>';
                } else if ($online_admission->form_status == 0) {
                    $form_status = '<span class="label label-danger">' . $this->lang->line('not_submitted') . '</span>';
                }

                if ($online_admission->dob != null) {
                    $dob = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($online_admission->dob));
                }

                if ($sch_setting->online_admission_payment == 'yes') {
                    if ($online_admission->paid_status == 1) {
                        $online_admission_payment = '<span class="label label-success">' . $this->lang->line('paid') . '</span>';
                    } elseif ($online_admission->paid_status == 2) {
                        $online_admission_payment = '<span class="label label-info">' . $this->lang->line('processing') . '</span>';
                    } else {
                        $online_admission_payment = '<span class="label label-danger">' . $this->lang->line('unpaid') . '</span>';
                    }
                }

                if (($online_admission->is_enroll)) {
                    $enroll = "<i class='fa fa-check'></i><span style='display:none'>" . $this->lang->line('yes') . "</span>";
                } else {
                    $enroll = "<i class='fa fa-minus-circle'></i><span style='display:none'>" . $this->lang->line('no') . "</span>";
                }

                $row   = array();
                $row[] = $online_admission->reference_no;
                $row[] = $online_admission->admission_no;
                $row[] = $this->customlib->getFullName($online_admission->firstname, $online_admission->middlename, $online_admission->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);

                $row[] = $online_admission->class . "(" . $online_admission->section . ")";
                $row[] = $online_admission->mobileno;
                $row[] = $dob;
                $row[] = $this->lang->line(strtolower($online_admission->gender));
                $row[] = $form_status;
                $row[] = $online_admission_payment;
                $row[] = $enroll;
                $row[] = $currency_symbol . amountFormat($online_admission->paid_amount);

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }  
        
    public function studentreport()
    {
        if (!$this->rbac->hasPrivilege('student_report', 'can_view')) {
            access_denied();
        }    
    
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_report');
        $data['title']           = 'student fee';
        $genderList              = $this->customlib->getGender();
        $data['genderList']      = $genderList;
        $RTEstatusList           = $this->customlib->getRteStatus();
        $data['RTEstatusList']   = $RTEstatusList;
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $userdata                = $this->customlib->getUserData();
        $category                = $this->category_model->get();
        $data['categorylist']    = $category;
       
        // Get ethnicity list - defining static ethnicity options
        $ethnicitylist = [
            ['id' => 'Brahmin', 'name' => 'Brahmin'],
            ['id' => 'Chhetri', 'name' => 'Chhetri'],
            ['id' => 'Dalit', 'name' => 'Dalit'],
            ['id' => 'Janajati', 'name' => 'Janajati'],
            ['id' => 'Madhesi', 'name' => 'Madhesi'],
            ['id' => 'Muslim', 'name' => 'Muslim'],
            ['id' => 'Tharu', 'name' => 'Tharu'],
            ['id' => 'Others', 'name' => 'Others']
        ];
        $data['ethnicitylist'] = $ethnicitylist;
       
        // UGC / HEMIS federal address list (same source as student create/edit)
        $this->load->model('ugc_metadata_model');
        $data['provincelist'] = $this->ugc_metadata_model->get_distinct_provinces();
        $data['totalStudents'] = $this->getTotalStudentCount();
       
        $this->load->view('layout/header', $data);
        $this->load->view('reports/studentReport', $data);
        $this->load->view('layout/footer', $data);
    }    


   
    public function studentreportvalidation()
{
    $class_id        = $this->input->post('class_id');
    $section_id      = $this->input->post('section_id');
    $program_id      = $this->input->post('program_id');
    $category_id     = $this->input->post('category_id');
    $gender          = $this->input->post('gender');
    $rte             = $this->input->post('rte');
    $ethnicity_id    = $this->input->post('ethnicity_id');
    $province_id     = $this->input->post('province_id');
    $district_id     = $this->input->post('district_id');
    $municipality_id = $this->input->post('municipality_id');


    $srch_type = $this->input->post('search_type');


    if ($srch_type == 'search_filter') {
        $has_filter = $this->_studentreport_has_filter(
            $class_id,
            $section_id,
            $program_id,
            $category_id,
            $gender,
            $rte,
            $ethnicity_id,
            $province_id,
            $district_id,
            $municipality_id
        );

        if ($has_filter) {
            $params = array(
                'srch_type'       => $srch_type,
                'class_id'        => $class_id,
                'section_id'      => $section_id,
                'program_id'      => $program_id,
                'category_id'     => $category_id,
                'gender'          => $gender,
                'rte'             => $rte,
                'ethnicity_id'    => $ethnicity_id,
                'province_id'     => $province_id,
                'district_id'     => $district_id,
                'municipality_id' => $municipality_id
            );

            $array = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        } else {
            $array = array(
                'status' => 0,
                'error'  => array(
                    'filters' => 'Please select at least one search criterion.',
                ),
            );
            echo json_encode($array);
        }
    } else {
        $params = array('srch_type' => 'search_full', 'class_id' => $class_id, 'section_id' => $section_id);
        $array  = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }
}


public function dtstudentreportlist()
{
    $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
    $class           = $this->input->post('class_id');
    $section         = $this->input->post('section_id');
    $program         = $this->input->post('program_id');
    $category_id     = $this->input->post('category_id');
    $gender          = $this->input->post('gender');
    $rte             = $this->input->post('rte');
    $ethnicity_id    = $this->input->post('ethnicity_id');
    $province_id     = $this->input->post('province_id');
    $district_id     = $this->input->post('district_id');
    $municipality_id = $this->input->post('municipality_id');
    $sch_setting     = $this->sch_setting_detail;


    // Update the model function to include the new search parameters
    $result     = $this->student_model->searchdatatableByMultipleParams(
        $class,
        $section,
        $program,
        $category_id,
        $gender,
        $rte,
        $ethnicity_id,
        $province_id,
        $district_id,
        $municipality_id
    );
   
    $resultlist = json_decode($result);
    $dt_data    = array();
   
    if (!empty($resultlist->data)) {
        foreach ($resultlist->data as $resultlist_key => $student) {
            $viewbtn = "<a href='" . base_url() . "student/view/" . $student->id . "'>" .
                      $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) .
                      "</a>";
           
            $row   = array();
            $row[] = $student->section;
            $row[] = $student->admission_no;
            $row[] = $viewbtn;
           
            if ($sch_setting->father_name) {
                $row[] = $student->father_name;
            }
           
            if ($student->dob != null && $student->dob != '0000-00-00') {
                $row[] = date($student->dob);
            } else {
                $row[] = "";
            }
           
            $row[] = $this->lang->line(strtolower($student->gender));
           
            if ($sch_setting->category) {
                $row[] = $student->category;
            }
           
            if ($sch_setting->mobile_no) {
                $row[] = $student->mobileno;
            }
           
            if ($sch_setting->national_identification_no) {
                $row[] = $student->national_id;  // Using national_id field instead of samagra_id
            }
           
            if ($sch_setting->local_identification_no) {
                $row[] = $student->adhar_no;
            }
           
            if ($sch_setting->rte) {
                $row[] = $student->rte;
            }
           
            $dt_data[] = $row;
        }
    }
   
    $json_data = array(
        "draw"            => intval($resultlist->draw),
        "recordsTotal"    => intval($resultlist->recordsTotal),
        "recordsFiltered" => intval($resultlist->recordsFiltered),
        "data"            => $dt_data,
    );
   
    echo json_encode($json_data);
}
   
    public function getDistrictsByProvince() {
        $province = trim((string) $this->input->post('province_id'));
        if ($province === '') {
            echo json_encode([]);
            return;
        }

        $this->load->model('ugc_metadata_model');
        $rows = $this->ugc_metadata_model->get_districts_by_province_name($province);
        $out  = array();
        foreach ($rows as $row) {
            if (!empty($row['district'])) {
                $out[] = array('id' => $row['district'], 'name' => $row['district']);
            }
        }
        echo json_encode($out);
    }

    public function getMunicipalitiesByDistrict() {
        $province = trim((string) $this->input->post('province_id'));
        $district = trim((string) $this->input->post('district_id'));
        if ($province === '' || $district === '') {
            echo json_encode([]);
            return;
        }

        $this->load->model('ugc_metadata_model');
        $rows = $this->ugc_metadata_model->get_local_levels_by_province_district($province, $district);
        $out  = array();
        foreach ($rows as $row) {
            if (!empty($row['local_level'])) {
                $out[] = array(
                    'id'   => !empty($row['combined_code']) ? $row['combined_code'] : $row['local_level'],
                    'name' => $row['local_level'],
                );
            }
        }
        echo json_encode($out);
    }

    /**
     * True when at least one student-report filter field is set (academic, demographic, or address).
     */
    private function _studentreport_has_filter($class_id, $section_id, $program_id, $category_id, $gender, $rte, $ethnicity_id, $province_id, $district_id, $municipality_id)
    {
        $fields = array(
            $class_id,
            $section_id,
            $program_id,
            $category_id,
            $gender,
            $rte,
            $ethnicity_id,
            $province_id,
            $district_id,
            $municipality_id,
        );
        foreach ($fields as $value) {
            if ($value !== null && $value !== '' && $value !== '0') {
                return true;
            }
        }
        return false;
    }
    
    public function classsectionreport()
    {
        if (!$this->rbac->hasPrivilege('student_report', 'can_view')) {
            access_denied();
        }
        
    
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/classsectionreport');
        
        $data['title'] = 'Class & Section Report';
        $data['class_section_list'] = $this->classsection_model->getClassSectionStudentCount();
        
        
        // Try to get program data if available
        try {
            $data['program_list'] = $this->student_model->getProgramStudentCount();
        } catch (Exception $e) {
            // If error occurs, just set an empty array
            $data['program_list'] = [];
            // Optionally log the error
            log_message('error', 'Error fetching program data: ' . $e->getMessage());
        }
        
        $this->load->view('layout/header', $data);
        $this->load->view('reports/classsectionreport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function guardianreport()
    {
        if (!$this->rbac->hasPrivilege('guardian_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/guardian_report');
        $data['title']           = 'Student Guardian Report';
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $class_id   = $this->input->post("class_id");
        $section_id = $this->input->post("section_id");

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $resultlist         = $this->student_model->studentGuardianDetails($carray);
            $data["resultlist"] = "";
        } else {

            $resultlist         = $this->student_model->searchGuardianDetails($class_id, $section_id);
            $data["resultlist"] = $resultlist;
        }

        $this->load->view("layout/header", $data);
        $this->load->view("reports/guardianReport", $data);
        $this->load->view("layout/footer", $data);
    }
    
    public function admissionreport()
    {
        if (!$this->rbac->hasPrivilege('student_history', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_history');
        $data['title'] = 'Admission Report';

        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $userdata                = $this->customlib->getUserData();
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $carray                  = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $admission_year         = $this->student_model->admissionYear();
        $data["admission_year"] = $admission_year;
        $this->load->view("layout/header", $data);
        $this->load->view("reports/admissionReport", $data);
        $this->load->view("layout/footer", $data);
    }
    
    public function admissionsearchvalidation()
    {
        $class_id = $this->input->post('class_id');
        $year     = $this->input->post('year');

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $error = array();

            $error['class_id'] = form_error('class_id');
            $array             = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        } else {

            $params = array('class_id' => $class_id, 'year' => $year);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }
    }
    public function dtadmissionreportlist()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    
        $class_id = $this->input->post('class_id');
        $year     = $this->input->post('year');
    
        // Defensive: check if model method exists
        if (!method_exists($this->student_model, 'searchdatatablebyAdmissionDetails')) {
            echo json_encode([
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Model method searchdatatablebyAdmissionDetails not found"
            ]);
            return;
        }
    
        $result = $this->student_model->searchdatatablebyAdmissionDetails($class_id, $year);
    
        // Error handling for empty or invalid JSON
        if (empty($result)) {
            echo json_encode([
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "No data returned from model"
            ]);
            return;
        }
    
        $resultlist  = json_decode($result);
    
        if ($resultlist === null) {
            echo json_encode([
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Invalid JSON returned from model: " . $result
            ]);
            return;
        }
    
        // If the model returns the correct DataTables JSON, just echo it
        if (isset($resultlist->draw) && isset($resultlist->data)) {
            echo $result;
            return;
        }
    
        // If not, reformat as DataTables expects
        $dt_data = array();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $student) {
                $row = [];
                $row[] = $student->admission_no ?? '';
                $row[] = $student->firstname ?? '';
                // Add other fields as needed
                $dt_data[] = $row;
            }
        }
        $json_data = array(
            "draw"            => intval($resultlist->draw ?? 1),
            "recordsTotal"    => intval($resultlist->recordsTotal ?? 0),
            "recordsFiltered" => intval($resultlist->recordsFiltered ?? 0),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }
    
    public function logindetailreport()
    {
        if (!$this->rbac->hasPrivilege('student_login_credential_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_login_credential');
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        $this->load->view("layout/header");
        $this->load->view("reports/logindetailreport", $data);
        $this->load->view("layout/footer");
    }
    
     public function searchloginvalidation()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $error = array();

            $error['class_id']   = form_error('class_id');
            $error['section_id'] = form_error('section_id');

            $array = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        } else {

            $params = array('class_id' => $class_id, 'section_id' => $section_id);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }
    }
    
    public function dtcredentialreportlist()
    {
        $sch_setting = $this->sch_setting_detail;
        $class_id    = $this->input->post("class_id");
        $section_id  = $this->input->post("section_id");
        $result      = $this->student_model->getdtforlogincredential($class_id, $section_id);
        $resultlist  = json_decode($result);
        $dt_data     = array();

        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $student) {
                $studentlist      = $this->user_model->getUserLoginDetails($student->id);
                $student_username = $studentlist["username"];
                $student_password = $studentlist["password"];

                $viewbtn = "<a  href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";

                $row   = array();
                $row[] = $student->admission_no;
                $row[] = $viewbtn;

                if (isset($student_username)) {
                    $row[] = $student_username;
                } else {
                    $row[] = "";
                }

                if (isset($student_password)) {
                    $row[] = $student_password;
                } else {
                    $row[] = "";
                }

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }
    
    public function parentlogindetailreport()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/parent_login_credential');
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        $this->load->view("layout/header");
        $this->load->view("reports/parentlogindetailreport", $data);
        $this->load->view("layout/footer");
    }

    public function dtparentcredentialreportlist()
    {
        $sch_setting = $this->sch_setting_detail;
        $class_id    = $this->input->post("class_id");
        $section_id  = $this->input->post("section_id");
        $result      = $this->student_model->getdtforlogincredential($class_id, $section_id);
        $resultlist  = json_decode($result);
        $dt_data     = array();

        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $student) {
                $parentlist      = $this->user_model->getParentLoginDetails($student->id);
                $parent_username = $parentlist["username"];
                $parent_password = $parentlist["password"];

                $viewbtn = "<a  href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";

                $row   = array();
                $row[] = $student->admission_no;
                $row[] = $viewbtn;

                if (isset($parent_username)) {
                    $row[] = $parent_username;
                } else {
                    $row[] = "";
                }

                if (isset($parent_password)) {
                    $row[] = $parent_password;
                } else {
                    $row[] = "";
                }

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }


    public function studentregistrationreport()
    {
        // Get user role to check if Admin or Super Admin
        $roles = $this->customlib->getStaffRole();
        $is_admin_or_superadmin = false;
        
        if (!empty($roles)) {
            $role_obj = json_decode($roles);
            if (!empty($role_obj) && isset($role_obj->name)) {
                $logged_user_role = $role_obj->name;
                $normalized_role = strtolower(trim($logged_user_role));
                // Check if user is Admin or Super Admin
                if ($logged_user_role == 'Super Admin' || $normalized_role == 'super admin' || 
                    $logged_user_role == 'Admin' || $normalized_role == 'admin') {
                    $is_admin_or_superadmin = true;
                }
            }
        }
        
        // Allow access if Admin/Super Admin, otherwise check privilege
        if (!$is_admin_or_superadmin && !$this->rbac->hasPrivilege('student_registration_report', 'can_view')) {
            access_denied();
        }
    
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_registration_report');
        
        $data['title'] = 'Student Registration Report';
        $data['genderList'] = $this->customlib->getGender();
        
        // Get sessions and convert to BS format
        $sessions = $this->student_model->get_sessions();
        foreach ($sessions as &$session) {
            if (!empty($session['session'])) {
                $session['session_bs'] = $this->customlib->convertSessionToBS($session['session']);
            }
        }
        $data['sessions'] = $sessions;
        
        $data['classlist'] = $this->class_model->get();
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['categorylist'] = $this->category_model->get();
        
        $this->load->view('layout/header', $data);
        $this->load->view('reports/studentRegistrationReport', $data);
        $this->load->view('layout/footer', $data);
    }
    
    public function studentregistrationreportvalidation() {
        $this->form_validation->set_rules('session_id', $this->lang->line('academic_session'), 'trim|required');
        
        if ($this->form_validation->run() == false) {
            $errors = array(
                'session_id' => form_error('session_id'),
            );
            echo json_encode(array('status' => false, 'error' => $errors));
        } else {
            $session_id = $this->input->post('session_id');
            echo json_encode(array('status' => true, 'session_id' => $session_id));
        }
    }
    
    public function dtstudentregistrationreportlist() {
        // Check if this is an AJAX request
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }
        
        // Get user role to check if Admin or Super Admin
        $roles = $this->customlib->getStaffRole();
        $is_admin_or_superadmin = false;
        
        if (!empty($roles)) {
            $role_obj = json_decode($roles);
            if (!empty($role_obj) && isset($role_obj->name)) {
                $logged_user_role = $role_obj->name;
                $normalized_role = strtolower(trim($logged_user_role));
                // Check if user is Admin or Super Admin
                if ($logged_user_role == 'Super Admin' || $normalized_role == 'super admin' || 
                    $logged_user_role == 'Admin' || $normalized_role == 'admin') {
                    $is_admin_or_superadmin = true;
                }
            }
        }
        
        // Allow access if Admin/Super Admin, otherwise check privilege
        if (!$is_admin_or_superadmin && !$this->rbac->hasPrivilege('student_registration_report', 'can_view')) {
            echo json_encode(array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array(),
                "error" => "Unauthorized access"
            ));
            return;
        }
    
        try {
            $session_id = $this->input->post('session_id');
            
            // Validate session_id
            if (empty($session_id)) {
                echo json_encode(array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => array(),
                    "error" => "Session ID is required"
                ));
                return;
            }
            
            // Datatables variables
            $draw = intval($this->input->post("draw"));
            $start = intval($this->input->post("start"));
            $length = intval($this->input->post("length"));
            $search_value = $this->input->post("search")["value"] ?? '';
            
            // Handle ordering
            $order = $this->input->post('order');
            $order_column = isset($order[0]['column']) ? intval($order[0]['column']) : 0;
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'ASC';
    
            // Column mapping for ordering
            $columns = array(
                0 => 'ses.session',
                1 => 's.admission_no',
                2 => 's.roll_no',
                3 => 's.firstname',
                4 => 's.gender',
                5 => 'd_perm.name',
                6 => 'm_perm.name',
                7 => 's.cast',
                8 => 's.dob',
                9 => 's.admission_year',
                10 => 'c.class',
                11 => 'sec.section',
                12 => 'p.title'
            );
    
            $order_by = isset($columns[$order_column]) ? $columns[$order_column] : 's.id';
    
            // Get data from model
            $result = $this->student_model->get_student_registration_data(
                $session_id,
                $start,
                $length,
                $search_value,
                $order_by,
                $order_dir
            );
    
            // Prepare data for DataTables
            $data = array();
            foreach ($result['data'] as $row) {
                // Calculate age
                $age = '';
                if (!empty($row->dob) && $row->dob != '0000-00-00') {
                    try {
                        $birthDate = new DateTime($row->dob);
                        $today = new DateTime();
                        $age = $today->diff($birthDate)->y;
                    } catch (Exception $e) {
                        $age = '';
                    }
                }
    
                // Build full name
                $name_parts = array();
                if (!empty($row->firstname)) $name_parts[] = trim($row->firstname);
                if (!empty($row->middlename)) $name_parts[] = trim($row->middlename);
                if (!empty($row->lastname)) $name_parts[] = trim($row->lastname);
                $full_name = implode(' ', $name_parts);
    
                // Show session in Nepali (BS) when stored as AD; leave BS as-is
                $session_bs = '';
                if (!empty($row->session)) {
                    $session_bs = $this->customlib->convertSessionToBSIfNeeded($row->session);
                }
                
                $data[] = array(
                    "academic_session" => $session_bs,
                    "admission_no" => $row->admission_no ?? '',
                    "roll_no" => $row->roll_no ?? '',
                    "student_name" => $full_name,
                    "gender" => $row->gender ?? '',
                    "district" => (!empty($row->permanent_district_name) ? $row->permanent_district_name : ($row->permanent_district ?? '')),
                    "local_level" => (!empty($row->permanent_local_level_name) ? $row->permanent_local_level_name : ($row->permanent_local_level ?? '')),
                    "cast" => $row->cast ?? '',
                    "age" => $age,
                    "batch_year" => $row->admission_year ?? '',
                    "faculty" => $row->class ?? '',
                    "level" => $row->section ?? '',
                    "program" => $row->program_title ?? '',
                    "id" => $row->student_id
                );
            }
    
            // Output
            $output = array(
                "draw" => $draw,
                "recordsTotal" => $result['total_records'],
                "recordsFiltered" => $result['filtered_records'],
                "data" => $data
            );
    
            echo json_encode($output);
            
        } catch (Exception $e) {
            // Log the error
            log_message('error', 'Student Registration Report Error: ' . $e->getMessage());
            
            // Return error response
            echo json_encode(array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array(),
                "error" => "An error occurred while fetching data"
            ));
        }
    }

public function getProgramsByClassSection()
{
    $class_id = $this->input->post('class_id');
    $section_id = $this->input->post('section_id');
    
    if(!$class_id || !$section_id) {
        echo json_encode([]);
        return;
    }
    
    $this->load->model('program_model');
    $programs = $this->program_model->getProgramsByClassAndSection($class_id, $section_id);
    
    echo json_encode($programs);
}
public function getTotalStudentCount() {
    $totalStudents = $this->student_model->getTotalActiveStudents();
    return $totalStudents;
}



public function graduation_report() {
    if (!$this->rbac->hasPrivilege('graduation_report', 'can_view')) {
        access_denied();
    }


    $this->session->set_userdata('top_menu', 'Reports');
    $this->session->set_userdata('sub_menu', 'Reports/student_information');
    $this->session->set_userdata('subsub_menu', 'Reports/student_information/graduation_report');


    $data['title'] = 'Graduation Report';
    $data['sch_setting'] = $this->setting_model->get(); 


    // Get graduation eligible classes (4th year)
    $data['classes'] = $this->class_model->getGraduationEligibleClasses();
    $data['sessions'] = $this->session_model->get();


    // Default values
    $data['class_id'] = '';
    $data['section_id'] = '';
    $data['program_id'] = '';
    $data['session_id'] = $this->setting_model->getCurrentSession();
    $data['gender'] = '';
    $data['caste'] = '';
    $data['studentlist'] = array();
    $data['search_note'] = '';


    if ($this->input->post('search') == 'search') {
        // Get and sanitize inputs
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');
        $session_id = $this->input->post('session_id');
        $gender = $this->input->post('gender');
        $caste = $this->input->post('caste');


        // Get graduated students with strict filters
        $students = $this->student_model->getGraduatedStudents(
            $class_id,
            $section_id,
            $program_id,
            $session_id,
            $gender,
            $caste
        );


        // Store results
        $data['studentlist'] = $students;
        $data['class_id'] = $class_id;
        $data['section_id'] = $section_id;
        $data['program_id'] = $program_id;
        $data['session_id'] = $session_id;
        $data['gender'] = $gender;
        $data['caste'] = $caste;


        // Set appropriate message
        if (empty($students)) {
            $data['search_note'] = "No graduated students found matching the selected criteria.";
        }


        $data['studentlist'] = $students;
    }


    $this->load->view('layout/header', $data);
    $this->load->view('reports/graduation_report', $data);
    $this->load->view('layout/footer', $data);
}





public function dropout_report() {
    if (!$this->rbac->hasPrivilege('dropout_report', 'can_view')) {
        access_denied();
    }

    $this->session->set_userdata('top_menu', 'Reports');
    $this->session->set_userdata('sub_menu', 'Reports/student_information');
    $this->session->set_userdata('subsub_menu', 'Reports/student_information/dropout_report');

    $data['title'] = 'Dropout Report';
    $data['sch_setting'] = $this->setting_model->get();

    // Get all classes for dropout report
    $data['classes'] = $this->class_model->get();
    $data['sessions'] = $this->session_model->get();

    // Default values
    $data['class_id'] = '';
    $data['section_id'] = '';
    $data['program_id'] = '';
    $data['session_id'] = $this->setting_model->getCurrentSession();
    $data['gender'] = '';
    $data['studentlist'] = array();
    $data['search_note'] = '';

    if ($this->input->post('search') == 'search') {
        // Get and sanitize inputs
        $data['class_id'] = $this->input->post('class_id');
        $data['section_id'] = $this->input->post('section_id');
        $data['program_id'] = $this->input->post('program_id');
        $data['session_id'] = $this->input->post('session_id');
        $data['gender'] = $this->input->post('gender');

        // Get dropout students with filters
        $students = $this->student_model->getDropoutStudents(
            $data['class_id'],
            $data['section_id'],
            $data['program_id'],
            $data['session_id'],
            $data['gender']
        );

        // Store results
        $data['studentlist'] = $students;

        // Set appropriate message
        if (empty($students)) {
            $data['search_note'] = "No dropout students found matching the selected criteria.";
        }
    }

    $this->load->view('layout/header', $data);
    $this->load->view('reports/dropout_report', $data);
    $this->load->view('layout/footer', $data);
}

public function student_counts() {
    if (!$this->rbac->hasPrivilege('student_counts', 'can_view')) {
        access_denied();
    }
   
    $this->session->set_userdata('top_menu', 'Reports');
    $this->session->set_userdata('sub_menu', 'Reports/student_counts');
   
    $data = array();


    $ethnicity_mapping = [
        '1' => 'Brahmin',
        '2' => 'Chhetri',
        '3' => 'Dalit',
        '4' => 'Janajati',
        '5' => 'Madhesi',
        '6' => 'Muslim',
        '7' => 'Tharu'
    ];
   
    // Define ethnicity list for view
    $data['ethnicitylist'] = [
        ['id' => 'Brahmin', 'name' => 'Brahmin'],
        ['id' => 'Chhetri', 'name' => 'Chhetri'],
        ['id' => 'Dalit', 'name' => 'Dalit'],
        ['id' => 'Janajati', 'name' => 'Janajati'],
        ['id' => 'Madhesi', 'name' => 'Madhesi'],
        ['id' => 'Muslim', 'name' => 'Muslim'],
        ['id' => 'Tharu', 'name' => 'Tharu'],
        ['id' => 'Others', 'name' => 'Others']
    ];
   
    $this->db->select('sd.designation')
    ->from('staff s')
    ->join('staff_designation sd', 's.designation = sd.id', 'inner')
    ->group_by('sd.designation')
    ->order_by('sd.designation');
    $query = $this->db->get();
    $used_designations = $query->result_array();


    $data['positionlist'] = array();
    foreach ($used_designations as $designation) {
    $data['positionlist'][] = [
    'id' => $designation['designation'],
    'name' => $designation['designation']
    ];
    }
    $data['positionlist'][] = ['id' => 'Others', 'name' => 'Others'];
   
   
    // Get standard ethnicities list from the ethnicity list
    $standard_ethnicities = array_column($data['ethnicitylist'], 'name');
   
    // Get standard positions list from the position list
    $standard_positions = array_column($data['positionlist'], 'name');
   
    // Get faculty and gender counts
    $data['faculty_gender_counts'] = $this->student_model->getFacultyGenderCounts();
   
    // Get faculty and ethnicity counts - Pass mapping and standard ethnicities
    $data['faculty_ethnicity_structured'] = $this->student_model->getFacultyEthnicityCountsStructured($ethnicity_mapping, $standard_ethnicities);
   
    // Get level and gender counts
    $data['level_gender_counts'] = $this->student_model->getLevelGenderCounts();
   
    // Get program and gender counts
    $data['program_gender_counts'] = $this->student_model->getProgramGenderCounts();
   
    // Get program ethnicity gender counts - Pass mapping and standard ethnicities
    $data['program_ethnicity_counts'] = $this->student_model->getProgramEthnicityCountsStructured($ethnicity_mapping, $standard_ethnicities);
   
    // Get district ethnicity gender counts - Pass mapping and standard ethnicities
    $data['district_ethnicity_structured'] = $this->student_model->getDistrictEthnicityCountsStructured($ethnicity_mapping, $standard_ethnicities);
   
    // Get province ethnicity gender counts
    $data['province_ethnicity_counts'] = $this->student_model->getProvinceEthnicityCounts($ethnicity_mapping, $standard_ethnicities);
   
    // Get faculty level program local counts
    $data['faculty_level_program_local_counts'] = $this->student_model->getFacultyLevelProgramLocalCounts();
   
    // Get graduated students counts
    $data['graduated_students_counts'] = $this->student_model->getGraduatedStudentsCounts($ethnicity_mapping, $standard_ethnicities);
   
    // Get pass rates
    $data['pass_rates'] = $this->student_model->getPassRates();
   
    // Get staff details grouped by position
    $data['staff_details_by_position'] = $this->student_model->getStaffDetailCountsByPosition($standard_positions);
   
    // Get staff ethnicity counts
    $data['staff_ethnicity_counts'] = $this->student_model->getStaffEthnicityCounts($ethnicity_mapping, $standard_ethnicities);
   
    // Get staff position counts
    $data['staff_position_counts'] = $this->student_model->getStaffPositionCounts($standard_positions);
   
    // Get dropout counts
    $data['dropout_counts'] = $this->student_model->getDropoutCounts();


    $data['campus_enrollment_summary'] = $this->student_model->getCampusEnrollmentSummary($ethnicity_mapping, $standard_ethnicities);
    $data['ugc_matrix_headcount']      = $this->student_model->sumCampusEnrollmentMatrixHeadcount($data['campus_enrollment_summary']);
    $gpi_mf = $this->student_model->sumCampusEnrollmentMaleFemale($data['campus_enrollment_summary']);
    $data['ugc_gpi_male']               = $gpi_mf['male'];
    $data['ugc_gpi_female']             = $gpi_mf['female'];
    $data['ugc_gpi_ratio']              = $gpi_mf['male'] > 0 ? round($gpi_mf['female'] / $gpi_mf['male'], 4) : null;
    $data['ugc_session_student_total'] = $this->student_model->countActiveStudentsCurrentSession();
    $data['ugc_hemis_synced_total']    = $this->student_model->countHemisSyncedStudentsCurrentSession();
    $data['ugc_dq_ready_count']        = $this->student_model->countUgcDataQualityStudentsReadyForSync();
    $data['ugc_dq_gap_count']          = $this->student_model->countUgcDataQualityStudentsWithGaps();
    $data['ugc_data_quality_rows']     = $this->student_model->getUgcDataQualityReportRows(600);

    $this->load->view('layout/header');
    $this->load->view('layout/sidebar');
    $this->load->view('reports/student_counts', $data);
    $this->load->view('layout/footer');
}

    /**
     * CSV export for Data Quality Report rows (offline follow-up for missing ethnicity / citizenship / sync issues).
     */
    public function ugc_data_quality_csv()
    {
        if (!$this->rbac->hasPrivilege('student_counts', 'can_view')) {
            access_denied();
        }
        $rows = $this->student_model->getUgcDataQualityReportRows(2000);
        $filename = 'ugc_data_quality_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, array('student_id', 'admission_no', 'name', 'issues'));
        foreach ($rows as $entry) {
            $r = $entry['row'];
            $name = trim(
                (isset($r['firstname']) ? $r['firstname'] : '') . ' '
                . (isset($r['middlename']) ? $r['middlename'] : '') . ' '
                . (isset($r['lastname']) ? $r['lastname'] : '')
            );
            fputcsv($out, array(
                isset($r['id']) ? $r['id'] : '',
                isset($r['admission_no']) ? $r['admission_no'] : '',
                $name,
                implode(' | ', $entry['issues']),
            ));
        }
        fclose($out);
        exit;
    }


}





