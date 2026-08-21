<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Examresult extends Admin_Controller
{


    public $exam_type = array();


    public function __construct()
    {
        parent::__construct();
        $this->exam_type          = $this->config->item('exam_type');
        $this->attendence_exam    = $this->config->item('attendence_exam');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model(array('marksdivision_model', 'marksdivision_model', 'nonacademic_marks_model'));
        $this->load->library('mailsmsconf');
         $this->load->library('media_storage');
    }

    /**
     * Shared marksheet PDF CSS. Do not use file_get_contents(base_url()) — the server
     * request has no session and often receives HTML (login page), which corrupts mPDF.
     */
    private function _pdf_stylesheet()
    {
        $path = FCPATH . 'backend/pdf_style.css';
        return is_readable($path) ? (string) file_get_contents($path) : '';
    }

    /**
     * JSON error payload for bulk marksheet AJAX — includes aggregated `message` and optional `detail` for the UI.
     */
    private function _json_marksheet_bulk_fail(array $errorFields, $summaryMessage = null, $technicalDetail = null)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        $clean = array();
        foreach ($errorFields as $k => $v) {
            $clean[$k] = is_array($v) ? $v : strip_tags(trim((string) $v));
        }
        $labels = array(
            'post_exam_id' => 'Exam',
            'post_exam_group_id' => 'Exam group',
            'marksheet_template' => 'Marksheet template',
            'exam_group_class_batch_exam_student_id' => 'Students selection',
            'pdf' => 'PDF',
            'exception' => 'Error',
        );
        $parts = array();
        foreach ($clean as $k => $v) {
            if ($v === '' || $v === null) {
                continue;
            }
            $label = isset($labels[$k]) ? $labels[$k] : ucfirst(str_replace(array('_', '[]'), array(' ', ''), $k));
            $parts[] = $label . ': ' . $v;
        }
        $message = $summaryMessage ? (string) $summaryMessage : (implode('; ', $parts) ?: 'Bulk marksheet could not be generated.');
        $payload = array(
            'status' => 0,
            'error' => $clean,
            'message' => $message,
        );
        if ($technicalDetail !== null && $technicalDetail !== '') {
            $td = (string) $technicalDetail;
            $td = str_replace(array(FCPATH, APPPATH), array('', ''), $td);
            $payload['detail'] = trim($td);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
    }

    public function printCard()
{
    $this->form_validation->set_error_delimiters('', '');
    $this->form_validation->set_rules('admitcard_template', $this->lang->line('template'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('post_exam_id', $this->lang->line('exam'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('post_exam_group_id', $this->lang->line('exam_group'), 'required|trim|xss_clean');
    $this->form_validation->set_rules('exam_group_class_batch_exam_student_id[]', $this->lang->line('students'), 'required|trim|xss_clean');

    if ($this->form_validation->run() == false) {
        $data = array(
            'admitcard_template'                     => form_error('admitcard_template'),
            'post_exam_id'                           => form_error('post_exam_id'),
            'post_exam_group_id'                     => form_error('post_exam_group_id'),
            'exam_group_class_batch_exam_student_id' => form_error('exam_group_class_batch_exam_student_id'),
        );
        echo json_encode(array('status' => 0, 'error' => $data));
        return;
    }

    // --- existing payload prep (unchanged) ---
    $post_exam_id            = $this->input->post('post_exam_id');
    $post_exam_group_id      = $this->input->post('post_exam_group_id');
    $students_array          = $this->input->post('exam_group_class_batch_exam_student_id');
    $exam                    = $this->examgroup_model->getExamByID($post_exam_id);
    $data['exam']            = $exam;
    $exam_grades             = $this->grade_model->getByExamType($exam->exam_group_type);
    $data['exam_grades']     = $exam_grades;
    $data['admitcard']       = $this->admitcard_model->get($this->input->post('admitcard_template'));
    $data['exam_subjects']   = $this->batchsubject_model->getExamSubjects($post_exam_id);
    $data['student_details'] = $this->examstudent_model->getStudentsAdmitCardByExamAndStudentID($students_array, $post_exam_id);
    $data['sch_setting']     = $this->sch_setting_detail;

    // --- FIX: force roll_no & symbol_no directly from students table ---
    // build a unique list of "students.id" from the returned rows
    $studentIds = array();
    if (!empty($data['student_details'])) {
        foreach ($data['student_details'] as $row) {
            // the model usually exposes "student_id"; fallback to "id" if needed
            $sid = isset($row->student_id) ? (int)$row->student_id : (isset($row->id) ? (int)$row->id : 0);
            if ($sid > 0) $studentIds[] = $sid;
        }
    }
    $studentIds = array_values(array_unique($studentIds));

    if (!empty($studentIds)) {
        // fetch ONLY from students table
        $q = $this->db->select('id, roll_no, symbol_no, image')
                      ->from('students')
                      ->where_in('id', $studentIds)
                      ->get();
        $byId = array();
        foreach ($q->result() as $s) {
            $byId[(int)$s->id] = $s;
        }

        // inject into the payload using non-colliding aliases
        foreach ($data['student_details'] as $k => $row) {
            $sid = isset($row->student_id) ? (int)$row->student_id : (isset($row->id) ? (int)$row->id : 0);
            if ($sid && isset($byId[$sid])) {
                $stu = $byId[$sid];

                // 1) expose explicit aliases used by the admit card views
                $data['student_details'][$k]->students_roll_no   = (string)$stu->roll_no;
                $data['student_details'][$k]->students_symbol_no = (string)$stu->symbol_no;

                // 2) also override generic names so any legacy view still works
                $data['student_details'][$k]->roll_no   = (string)$stu->roll_no;
                $data['student_details'][$k]->symbol_no = (string)$stu->symbol_no;

                // 3) photo fallback from students.image if missing
                if (empty($data['student_details'][$k]->image) && !empty($stu->image)) {
                    $data['student_details'][$k]->image = $stu->image;
                }
            }
        }
    }
    // --- /FIX ---

    // render the standard admit card print view
    $student_admit_cards = $this->load->view('admin/admitcard/_printadmitcard', $data, true);
    echo json_encode(array('status' => 1, 'error' => '', 'page' => $student_admit_cards));
}



    public function admitcard()
    {
        if (!$this->rbac->hasPrivilege('print_admit_card', 'can_view')) {
            access_denied();
        }


        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/examresult/admitcard');
        $examgroup_result      = $this->examgroup_model->get();
        $data['examgrouplist'] = $examgroup_result;
        $admitcard_result      = $this->admitcard_model->get();
        $data['admitcardlist'] = $admitcard_result;
        $class                 = $this->class_model->get();
        $data['title']         = 'Add Batch';
        $data['title_list']    = 'Recent Batch';
        $data['examType']      = $this->exam_type;
        $data['classlist']     = $class;
        $session               = $this->session_model->get();
        $data['sessionlist']   = $session;
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_group_id', $this->lang->line('exam_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('admitcard', $this->lang->line('admit_card_template'), 'trim|required|xss_clean');


        if ($this->form_validation->run() == false) {


        } else {
            $exam_group_id              = $this->input->post('exam_group_id');
            $exam_id                    = $this->input->post('exam_id');
            $session_id                 = $this->input->post('session_id');
            $class_id                   = $this->input->post('class_id');
            $section_id                 = $this->input->post('section_id');
            $admitcard_template         = $this->input->post('admitcard');
            $data['admitcard_template'] = $admitcard_template;


            $data['studentList'] = $this->examgroupstudent_model->searchExamStudents($exam_group_id, $exam_id, $class_id, $section_id, $session_id);


            $data['examList'] = $this->examgroup_model->getExamByExamGroup($exam_group_id, true);


            $data['exam_id']       = $exam_id;
            $data['exam_group_id'] = $exam_group_id;
        }
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examresult/admitcard', $data);
        $this->load->view('layout/footer', $data);
    }


    public function marksheet()
    {


        if (!$this->rbac->hasPrivilege('print_marksheet', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/examresult/marksheet');


        $examgroup_result      = $this->examgroup_model->get();
        $data['examgrouplist'] = $examgroup_result;
        $marksheet_result      = $this->marksheet_model->get();
        $data['marksheetlist'] = $marksheet_result;
        $class                 = $this->class_model->get();
        $data['title']         = 'Add Batch';
        $data['title_list']    = 'Recent Batch';
        $data['examType']      = $this->exam_type;
        $data['classlist']     = $class;
        $session               = $this->session_model->get();
        $data['sessionlist']   = $session;
        $this->form_validation->set_rules('marksheet', $this->lang->line('marksheet_template'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_group_id', $this->lang->line('exam_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'trim|required|xss_clean');


        if ($this->form_validation->run() == false) {


        } else {
            $exam_group_id = $this->input->post('exam_group_id');
            $exam_id       = $this->input->post('exam_id');
            $session_id    = $this->input->post('session_id');
            $class_id      = $this->input->post('class_id');
            $section_id    = $this->input->post('section_id');


            $marksheet_template         = $this->input->post('marksheet');
            $data['marksheet_template'] = $marksheet_template;
            $data['studentList']        = $this->examgroupstudent_model->searchExamStudents($exam_group_id, $exam_id, $class_id, $section_id, $session_id);
            $data['examList']           = $this->examgroup_model->getExamByExamGroup($exam_group_id, true);
            $data['exam_id']            = $exam_id;
            $data['exam_group_id']      = $exam_group_id;
        }
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examresult/marksheet', $data);
        $this->load->view('layout/footer', $data);
    }


    // public function demopdf()
    // {
    //     $data = array();
    //     $html = $this->load->view('admin/examresult/_printpdfmarksheet - Copy', $data, true);
    //     $this->load->library('m_pdf');
    //     $mpdf       = $this->m_pdf->load();
    //     $stylesheet = file_get_contents(base_url() . 'backend/pdf_style.css'); // external css
    //     $mpdf->SetDefaultBodyCSS('background', "url('./uploads/marksheet/e335ee85f71b9e2e29af3de3c6b588ec.jpg')");
    //     $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
    //     $mpdf->SetDisplayMode('fullpage');
    //     $mpdf->WriteHTML($stylesheet, 1); // Writing style to pdf
    //     $mpdf->showWatermarkText = true;
    //     $mpdf->autoScriptToLang  = true;
    //     $mpdf->baseScript        = 1;
    //     $mpdf->autoLangToFont    = true;
    //     $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    //     $mpdf->Output();
    //     exit();


    // }

    public function pdftmarksheet()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            $template = $this->marksheet_model->get($this->input->post('marksheet_template'));
            $data['template'] = $template;
    
            $student_id = $this->input->post('student_id');
            $post_exam_id = $this->input->post('post_exam_id');
            $post_exam_group_id = $this->input->post('post_exam_group_id');
            $exam_group_class_batch_exam_student_id = $this->input->post('exam_group_class_batch_exam_student_id');
            $exam = $this->examgroup_model->getExamByID($post_exam_id);
            $data['exam'] = $exam;
            $marks_division = $this->marksdivision_model->get();
            $data['marks_division'] = $marks_division;
            $student_data = $this->student_model->get($student_id);
            $exam_grades = $this->grade_model->getByExamType($exam->exam_group_type);
            $data['exam_grades'] = $exam_grades;
            $data['marksheet'] = $this->examresult_model->getStudentExamResults($post_exam_id, $post_exam_group_id, $exam_group_class_batch_exam_student_id, $student_id);
            $data['sch_setting'] = $this->sch_setting_detail;
            
            $html = $this->load->view('admin/examresult/_printpdfmarksheet', $data, true);
    
            $type = $this->input->post('type');
            $this->load->library('m_pdf');
            $mpdf = $this->m_pdf->load();
            
            // Configure mPDF settings
            $mpdf->SetAuthor($this->sch_setting_detail->name);
            $mpdf->SetCreator($this->sch_setting_detail->name);
            $mpdf->SetSubject('Student Marksheet');
            
            $stylesheet = $this->_pdf_stylesheet();
            
            if ($template->background_img != "") {
                $mpdf->SetDefaultBodyCSS('background', "url('".$this->customlib->getFolderPath()."./uploads/marksheet/" . $template->background_img . "')");
                $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
            }
            
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->SetWatermarkText($this->sch_setting_detail->name, .2);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->showWatermarkText = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoLangToFont = true;
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    
            if ($type == "email") {
                $content = $mpdf->Output('', 'S');
                $this->load->library('mailer');
    
                $student_value = $data['marksheet']['student'];
                $exam_roll_no = ($exam->use_exam_roll_no) ? $student_value['exam_roll_no'] : $student_value['student_roll_no'];
                $student_name = $this->customlib->getFullName($student_data['firstname'], $student_data['middlename'], $student_data['lastname'], $data['sch_setting']->middlename, $data['sch_setting']->lastname);
    
                $sender_details = array(
                    'email' => $student_data['email'], 
                    'student_name' => $student_name, 
                    'class' => $student_data['class'], 
                    'section' => $student_data['section'], 
                    'admission_no' => $student_data['admission_no'], 
                    'roll_no' => $student_data['roll_no'], 
                    'admit_card_roll_no' => $exam_roll_no,
                    'dob' => $student_data['dob'], 
                    'guardian_name' => $student_data['guardian_name'], 
                    'guardian_relation' => $student_data['guardian_relation'], 
                    'guardian_phone' => $student_data['guardian_phone'], 
                    'father_name' => $student_data['father_name'], 
                    'father_phone' => $student_data['father_phone'], 
                    'mother_name' => $student_data['mother_name'], 
                    'gender' => $student_data['gender'], 
                    'guardian_email' => $student_data['guardian_email'], 
                    'exam' => $exam->exam
                );
    
                $response = $this->mailsmsconf->mailsms('email_pdf_exam_marksheet', $sender_details, '', '', $content);
                
                if ($response) {
                    $array = array('status' => 1, 'message' => $this->lang->line('mail_sent_successfully'));
                } else {
                    $array = array('status' => 0, 'message' => $this->lang->line('something_went_wrong'));
                }
                echo json_encode($array);
                
            } elseif ($type == "download") {
                $filename = 'marksheet_' . $student_data['admission_no'] . '_' . date('Y-m-d') . '.pdf';
                
                // Clear any output buffers
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                // Set headers for PDF download
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');
                header('Cache-Control: private, no-transform, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // Output PDF
                echo $mpdf->Output($filename, 'S');
                exit();
            }
            
        } catch (Exception $e) {
            log_message('error', 'PDF Generation Error: ' . $e->getMessage());
            show_error('Error generating PDF: ' . $e->getMessage());
        }
    }
    
    // Fixed bulk PDF download method
    public function printmarksheet()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('post_exam_id', $this->lang->line('exam'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('post_exam_group_id', $this->lang->line('exam_group'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('marksheet_template', $this->lang->line('marksheet_template'), 'required|trim|integer');
        $this->form_validation->set_rules('exam_group_class_batch_exam_student_id[]', $this->lang->line('students'), 'required|trim|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $data = array(
                'post_exam_id' => form_error('post_exam_id'),
                'post_exam_group_id' => form_error('post_exam_group_id'),
                'marksheet_template' => form_error('marksheet_template'),
                'exam_group_class_batch_exam_student_id' => form_error('exam_group_class_batch_exam_student_id'),
            );
            $this->_json_marksheet_bulk_fail($data, 'Please fix the following and try again.');
            return;
        }

        // Release session lock so long mPDF work does not block other tabs or appear to hang
        if (function_exists('session_write_close')) {
            @session_write_close();
        }
    
        try {
            $template = $this->marksheet_model->get($this->input->post('marksheet_template'));
            if (empty($template)) {
                $mt = $this->input->post('marksheet_template');
                $this->_json_marksheet_bulk_fail(
                    array('marksheet_template' => $this->lang->line('invalid') ?: 'Invalid marksheet template'),
                    'The selected marksheet template was not found (ID: ' . (string) $mt . ').',
                    'template_marksheets row missing for id=' . var_export($mt, true)
                );
                return;
            }
            $data['template'] = $template;
    
            $post_exam_id = $this->input->post('post_exam_id');
            $post_exam_group_id = $this->input->post('post_exam_group_id');
            $students_array = $this->input->post('exam_group_class_batch_exam_student_id');
            if (!is_array($students_array)) {
                $students_array = ($students_array !== null && $students_array !== '') ? array($students_array) : array();
            }
            $marks_division = $this->marksdivision_model->get();
            $data['marks_division'] = $marks_division;
            $exam = $this->examgroup_model->getExamByID($post_exam_id);
            if (empty($exam)) {
                $this->_json_marksheet_bulk_fail(
                    array('post_exam_id' => $this->lang->line('no_record_found') ?: 'Exam not found'),
                    'No exam record for the selected exam ID (' . (string) $post_exam_id . ').',
                    'getExamByID returned empty for post_exam_id=' . var_export($post_exam_id, true)
                );
                return;
            }
            $data['exam'] = $exam;
            $exam_grades = $this->grade_model->getByExamType($exam->exam_group_type);
            $data['exam_grades'] = $exam_grades;
            $data['marksheet'] = $this->examresult_model->getExamResults($post_exam_id, $post_exam_group_id, $students_array);
            $data['sch_setting'] = $this->sch_setting_detail;
            
            $html = $this->load->view('admin/examresult/_printmarksheet', $data, true);
            
            $this->load->library('m_pdf');
            $mpdf = $this->m_pdf->load();
            
            // Configure mPDF settings
            $mpdf->SetAuthor($this->sch_setting_detail->name);
            $mpdf->SetCreator($this->sch_setting_detail->name);
            $mpdf->SetSubject('Bulk Marksheets');
            
            $stylesheet = $this->_pdf_stylesheet();
            
            if ($template->background_img != "") {
                $mpdf->SetDefaultBodyCSS('background', "url('".$this->customlib->getFolderPath()."./uploads/marksheet/" . $template->background_img . "')");
                $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
            }
            
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->SetWatermarkText($this->sch_setting_detail->name, .2);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->showWatermarkText = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoLangToFont = true;
            $prevDisplayErrors = @ini_set('display_errors', '0');
            $html = (string) $html;
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            if ($prevDisplayErrors !== false) {
                @ini_set('display_errors', $prevDisplayErrors);
            }
    
            $filename = 'bulk_marksheets_' . date('Y-m-d_H-i-s') . '.pdf';
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $pdfBinary = $mpdf->Output('', 'S');
            if (is_string($pdfBinary) && strlen($pdfBinary) > 8) {
                $pdfStart = strpos($pdfBinary, '%PDF');
                if ($pdfStart !== false && $pdfStart > 0) {
                    log_message('error', 'Bulk marksheet: removed ' . $pdfStart . ' byte(s) of leading output before PDF signature');
                    $pdfBinary = substr($pdfBinary, $pdfStart);
                }
            }
            if (!is_string($pdfBinary) || strlen($pdfBinary) < 8 || substr($pdfBinary, 0, 4) !== '%PDF') {
                $len = is_string($pdfBinary) ? strlen($pdfBinary) : 0;
                $headHex = ($len > 0) ? bin2hex(substr($pdfBinary, 0, min(48, $len))) : '';
                log_message('error', 'Bulk marksheet mPDF invalid output (length ' . $len . ') head=' . $headHex);
                $this->_json_marksheet_bulk_fail(
                    array('pdf' => $this->lang->line('something_went_wrong') ?: 'PDF generation failed'),
                    'mPDF did not return a valid PDF (length ' . $len . '). See application/logs and fix PHP warnings/HTML in marksheet template.',
                    'First bytes (hex): ' . $headHex
                );
                return;
            }
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($pdfBinary));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: private, no-transform, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo $pdfBinary;
            exit();
            
        } catch (Exception $e) {
            log_message('error', 'Bulk PDF Generation Error: ' . $e->getMessage());
            $this->_json_marksheet_bulk_fail(
                array('exception' => $this->lang->line('error_occurred_please_try_again')),
                'PDF generation failed: ' . $e->getMessage(),
                $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()
            );
        }
    }
    
    // Alternative: ZIP download method for individual PDFs
    public function downloadMarksheetZip()
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('post_exam_id', $this->lang->line('exam'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('post_exam_group_id', $this->lang->line('exam_group'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('exam_group_class_batch_exam_student_id[]', $this->lang->line('students'), 'required|trim|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $data = array(
                'post_exam_id' => form_error('post_exam_id'),
                'post_exam_group_id' => form_error('post_exam_group_id'),
                'exam_group_class_batch_exam_student_id' => form_error('exam_group_class_batch_exam_student_id'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
            return;
        }
    
        try {
            $template = $this->marksheet_model->get($this->input->post('marksheet_template'));
            $post_exam_id = $this->input->post('post_exam_id');
            $post_exam_group_id = $this->input->post('post_exam_group_id');
            $students_array = $this->input->post('exam_group_class_batch_exam_student_id');
            if (!is_array($students_array)) {
                $students_array = ($students_array !== null && $students_array !== '') ? array($students_array) : array();
            }
            
            // Create ZIP file
            $this->load->library('zip');
            $zip_filename = 'marksheets_' . date('Y-m-d_H-i-s') . '.zip';
            
            foreach ($students_array as $exam_group_class_batch_exam_student_id) {
                // Get student data for this iteration
                $student_data = $this->examresult_model->getStudentDataByExamGroupId($exam_group_class_batch_exam_student_id);
                
                if (!$student_data) continue;
                
                // Prepare data for PDF generation
                $data['template'] = $template;
                $data['exam'] = $this->examgroup_model->getExamByID($post_exam_id);
                $data['marks_division'] = $this->marksdivision_model->get();
                $data['exam_grades'] = $this->grade_model->getByExamType($data['exam']->exam_group_type);
                $data['marksheet'] = $this->examresult_model->getStudentExamResults($post_exam_id, $post_exam_group_id, $exam_group_class_batch_exam_student_id, $student_data->student_id);
                $data['sch_setting'] = $this->sch_setting_detail;
                
                $html = $this->load->view('admin/examresult/_printpdfmarksheet', $data, true);
                
                // Generate PDF for this student
                $this->load->library('m_pdf');
                $mpdf = $this->m_pdf->load();
                
                $stylesheet = $this->_pdf_stylesheet();
                
                if ($template->background_img != "") {
                    $mpdf->SetDefaultBodyCSS('background', "url('".$this->customlib->getFolderPath()."./uploads/marksheet/" . $template->background_img . "')");
                    $mpdf->SetDefaultBodyCSS('background-image-resize', 6);
                }
                
                $mpdf->WriteHTML($stylesheet, 1);
                $mpdf->SetWatermarkText($this->sch_setting_detail->name, .2);
                $mpdf->SetDisplayMode('fullpage');
                $mpdf->showWatermarkText = true;
                $mpdf->autoScriptToLang = true;
                $mpdf->baseScript = 1;
                $mpdf->autoLangToFont = true;
                $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
                
                // Get PDF content
                $pdf_content = $mpdf->Output('', 'S');
                $pdf_filename = 'marksheet_' . $student_data->admission_no . '.pdf';
                
                // Add to ZIP
                $this->zip->add_data($pdf_filename, $pdf_content);
            }
            
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Set headers for ZIP download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: private, no-transform, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output ZIP file
            echo $this->zip->get_zip();
            exit();
            
        } catch (Exception $e) {
            log_message('error', 'ZIP Generation Error: ' . $e->getMessage());
            show_error('Error generating ZIP file: ' . $e->getMessage());
        }
    }


    public function index()
    {
        if (!$this->rbac->hasPrivilege('exam_result', 'can_view')) {
            access_denied();
        }
    
        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/Examresult');
    
        $data['examgrouplist'] = $this->examgroup_model->get();
        $data['marksheetlist'] = $this->marksheet_model->get();
        $data['classlist']     = $this->class_model->get();
        $data['title']         = 'Add Batch';
        $data['title_list']    = 'Recent Batch';
        $data['examType']      = $this->exam_type;
        $data['sessionlist']   = $this->session_model->get();
    
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_group_id', $this->lang->line('exam_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'trim|required|xss_clean');
    
        if ($this->form_validation->run() == true) {
            $exam_group_id = $this->input->post('exam_group_id');
            $exam_id       = $this->input->post('exam_id');
            $session_id    = $this->input->post('session_id');
            $class_id      = $this->input->post('class_id');
            $section_id    = $this->input->post('section_id');
    
            $data['marksheet_template'] = $this->input->post('marksheet');
    
            $exam_details  = $this->examgroup_model->getExamByID($exam_id);
            $exam_subjects = $this->batchsubject_model->getExamSubjects($exam_id);
            $data['subjectList'] = $exam_subjects;
    
            $studentList = $this->examgroupstudent_model
                ->searchExamStudents($exam_group_id, $exam_id, $class_id, $section_id, $session_id);
    
            // (2a) attach subject results
            if (!empty($studentList)) {
                foreach ($studentList as $k => $stu) {
                    $studentList[$k]->subject_results = $this->examresult_model
                        ->getStudentResultByExam($exam_id, $stu->exam_group_class_batch_exam_student_id);
                }
            }
    
            // (2b) FALLBACK: inject symbol_no if the query didn’t bring it
            if (!empty($studentList) && !property_exists($studentList[0], 'symbol_no')) {
                $ids = array_map(function($s){ return (int)$s->student_id; }, $studentList);
                if (!empty($ids)) {
                    $this->db->select('id, symbol_no')->from('students')->where_in('id', $ids);
                    $rows = $this->db->get()->result();
                    $map  = [];
                    foreach ($rows as $r) { $map[$r->id] = $r->symbol_no; }
                    foreach ($studentList as $k => $stu) {
                        $studentList[$k]->symbol_no = isset($map[$stu->student_id]) ? $map[$stu->student_id] : '';
                    }
                }
            }
    
            // (2c) Precompute Absent/NG flags and final GPA/grade (so the view can be simple)
            $exam_grades         = $this->grade_model->getByExamType($exam_details->exam_group_type);
            $grade_of = function($perc) use ($exam_grades) {
                if ($perc === null) return '-';
                $p = (float) $perc;
                foreach ($exam_grades as $g) {
                    $low  = min((float) $g->mark_from, (float) $g->mark_upto);
                    $high = max((float) $g->mark_from, (float) $g->mark_upto);
                    if (($p + 1e-9) >= $low && ($p - 1e-9) <= $high) return $g->name;
                }
                return '-';
            };
    
            foreach ($studentList as $k => $stu) {
                $any_absent = false; $any_ng = false;
                $tot_marks = 0.0; $obt_marks = 0.0;
                $tch = 0.0; $tqp = 0.0; // credit hours / quality points
    
                foreach ((array)$stu->subject_results as $res) {
                    // find the subject to get max_marks/credit_hours
                    $sub = null;
                    foreach ($exam_subjects as $s) { if ((int)$s->subject_id === (int)$res->subject_id) { $sub = $s; break; } }
                    if (!$sub) continue;
    
                    $max = (float)$sub->max_marks;
                    $mark = (float)$res->get_marks;
    
                    if (strtolower((string)$res->attendence) === 'absent') { $any_absent = true; continue; }
    
                    $tot_marks += $max;
                    $obt_marks += $mark;
    
                    // NG check by grade at subject level
                    if ($max > 0) {
                        $perc  = ($mark * 100.0) / $max;
                        $grade = $grade_of($perc);
                        if (strtoupper($grade) === 'NG' || ($exam_details->exam_group_type === 'gpa' && $grade === '-')) $any_ng = true;
                    }
    
                    if ($exam_details->exam_group_type === 'gpa') {
                        $ch = (float)$sub->credit_hours;
                        $tch += $ch;
                        if ($max > 0) {
                            $perc  = ($mark * 100.0) / $max;
                            // map to grade point roughly like your findGradePoints
                            $gp = (float)$this->findGradePoints($exam_grades, $perc);
                            $tqp += ($gp * $ch);
                        }
                    }
                }
    
                $studentList[$k]->_absent = $any_absent;
                $studentList[$k]->_ng     = $any_ng;
                $studentList[$k]->_total_max = $tot_marks;
                $studentList[$k]->_total_obt = $obt_marks;
    
                if ($exam_details->exam_group_type === 'gpa' && $tch > 0) {
                    $gpa = $tqp / $tch;
                    $studentList[$k]->_final_gpa   = round($gpa, 2);
                    // convert GPA → final grade letter
                    $studentList[$k]->_final_grade = $this->getGradeFromPoints($gpa);
                } else {
                    $studentList[$k]->_final_gpa   = null;
                    $studentList[$k]->_final_grade = ($tot_marks > 0)
                        ? $grade_of(($obt_marks * 100.0) / $tot_marks)
                        : '';
                }
            }
    
            // (2d) compute rank among eligible (not Absent/NG)
            $score = [];
            foreach ($studentList as $s) {
                if (!$s->_absent && !$s->_ng) { $score[$s->student_id] = $s->_total_obt; }
            }
            arsort($score, SORT_NUMERIC);
            $rankMap = []; $pos = 0; $rank = 0; $prev = null;
            foreach ($score as $sid => $tot) { $pos++; if ($prev === null || $tot < $prev) $rank = $pos; $rankMap[$sid] = $rank; $prev = $tot; }
            foreach ($studentList as $k => $s) { $studentList[$k]->rank = isset($rankMap[$s->student_id]) ? $rankMap[$s->student_id] : ''; }
    
            $data['studentList']   = $studentList;
            $data['exam_grades']   = $exam_grades;
            $data['exam_details']  = $exam_details;
            $data['exam_id']       = $exam_id;
            $data['exam_group_id'] = $exam_group_id;
        }
    
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examresult/index', $data);
        $this->load->view('layout/footer', $data);
    }
    
    /**
     * Map percentage to grade point from exam_grades (same logic as view findGradePoints).
     */
    private function findGradePoints($exam_grades, $percentage)
    {
        if ($percentage === null) {
            return 0;
        }
        if (empty($exam_grades)) {
            return 0;
        }
        $p = (float) $percentage;
        foreach ($exam_grades as $g) {
            $low  = min((float) $g->mark_from, (float) $g->mark_upto);
            $high = max((float) $g->mark_from, (float) $g->mark_upto);
            if (($p + 1e-9) >= $low && ($p - 1e-9) <= $high) {
                return isset($g->point) ? (float) $g->point : 0.0;
            }
        }
        return 0;
    }

    /**
     * Convert GPA points to grade letter (same as view grade_from_points / getGradeFromPoints).
     */
    private function getGradeFromPoints($points)
    {
        $p = (float) $points;
        if ($p >= 3.61) return 'A+';
        if ($p >= 3.21) return 'A';
        if ($p >= 2.81) return 'B+';
        if ($p >= 2.41) return 'B';
        if ($p >= 2.01) return 'C+';
        if ($p >= 1.61) return 'C';
        if ($p >= 1.21) return 'D';
        return 'NG';
    }
    
    


    public function updaterank()
    {
        $exam_group_class_batch_exam_id         = $this->input->post('exam_group_class_batch_exam_id');
        $exam_group_class_batch_exam_student_id = $this->input->post('exam_group_class_batch_exam_student_id');
        if (!empty($exam_group_class_batch_exam_student_id)) {
            $exam_group_class_batch_exam_students = array();
            foreach ($exam_group_class_batch_exam_student_id as $exam_student_id_key => $exam_student_id_value) {
                $exam_group_class_batch_exam_students[] = array(
                    'id'   => $exam_student_id_value,
                    'rank' => $this->input->post('exam_group_class_batch_exam_student_id_' . $exam_student_id_value),
                );


            }
            $this->examresult_model->updaterank($exam_group_class_batch_exam_students, $exam_group_class_batch_exam_id);
        }


        $array = array('status' => '1', 'message' => $this->lang->line('update_message'));
        echo json_encode($array);
    }


    public function examrank()
    {
        $exam_id       = $this->input->post('exam_id');
        $studentList   = $this->examgroupstudent_model->searchExamStudentsByExam($exam_id);
         
        $exam_details  = $this->examgroup_model->getExamByID($exam_id);
        $exam_subjects = $this->batchsubject_model->getExamSubjects($exam_id);
       
        $subjectList   = $exam_subjects;


        if (!empty($studentList)) {
            foreach ($studentList as $student_key => $student_value) {
                $studentList[$student_key]->subject_results = $this->examresult_model->getStudentResultByExam($exam_id, $student_value->exam_group_class_batch_exam_student_id);
            }
        }
        $data['subjectList']  = $exam_subjects;
        $data['studentList']  = $studentList;
        $exam_grades          = $this->grade_model->getByExamType($exam_details->exam_group_type);
        $data['exam_grades']  = $exam_grades;
        $data['exam_details'] = $exam_details;
        $data['exam_id']      = $exam_id;
        $data['sch_setting']  = $this->sch_setting_detail;
        $page                 = $this->load->view('admin/examresult/_partialexamrank', $data, true);


        $array = array('status' => '1', 'page' => $page, 'exam_details' => $exam_details, 'message' => $this->lang->line('success_message'));
        echo json_encode($array);


    }


    public function getStudentByClassBatch()
    {
        $class_id            = $this->input->post('class_id');
        $section_id          = $this->input->post('section_id');
        $session_id          = $this->input->post('session_id');
        $data['studentList'] = $this->examgroupstudent_model->searchStudentByClassSectionSession($class_id, $section_id, $session_id);
        echo json_encode($data);
    }


    public function getExamGroupByStudent()
    {
        $student_id = $this->input->post('student_id');
        $data['examgrouplist'] = $this->examgroup_model->getExamGroupByStudent($student_id);
        echo json_encode($data);
    }


    public function studentresult()
    {
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('exam_group_id', $this->lang->line('exam_group_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_id', $this->lang->line('student_id'), 'required|trim|xss_clean');


        if ($this->form_validation->run() == false) {
            $data = array(
                'exam_group_id' => form_error('exam_group_id'),
                'student_id'    => form_error('student_id'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {


            $student_id         = $this->input->post('student_id');
            $exam_group_id      = $this->input->post('exam_group_id');
            $exam_group_exam_id = $this->input->post('exam_id');


            $examresult  = array();
            $exam_grades = array();
            if ($exam_group_exam_id != "") {
                $examresult = $this->examgroup_model->getExamResultDetailStudent($exam_group_exam_id, $exam_group_id, $student_id);


                $data['examresult']  = $examresult;
                $exam_grades         = $this->grade_model->getByExamType($examresult->exam_type);
                $data['exam_grades'] = $exam_grades;
                $examresult          = $this->load->view('admin/examresult/_getExam', $data, true);
            } else {
                $exam_group         = $this->examgroup_model->get($exam_group_id);
                $data['exam_group'] = $exam_group;
                $exam_grades         = $this->grade_model->getByExamType($exam_group->exam_type);
                $data['exam_grades'] = $exam_grades;
                $exam_result              = $this->examgroup_model->getExamGroupExamsResultByStudentID($exam_group_id, $student_id);
                $data['examresult']       = $exam_result;
                $exam_connections         = $this->examgroup_model->getExamGroupConnection($exam_group_id);
                $data['exam_connections'] = $exam_connections;
                $examresult               = $this->load->view('admin/examresult/_getExamGroupResult', $data, true);
            }


            $data['exam_grades'] = $exam_grades;


            $array = array('status' => '1', 'result' => $examresult, 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }


    public function getStudentCurrentResult()
    {
        $this->form_validation->set_rules('student_session_id', $this->lang->line('student_id'), 'trim|required|xss_clean');


        if ($this->form_validation->run() == false) {


            $msg = array(
                'student_session_id' => form_error('student_session_id'),
            );


            $array = array('status' => 0, 'error' => $msg);
        } else {
            $student_session_id  = $this->input->post('student_session_id');
            $data['exam_grades'] = $this->grade_model->get();
            $exam_groups_attempt = $this->examgroup_model->getExamGroupByStudentSession($student_session_id);


            $data['exam_groups_attempt'] = $exam_groups_attempt;
            $examresult                  = $this->load->view('admin/examresult/_getExamGroupResult', $data, true);
            $array                       = array('status' => 1, 'error' => '', 'result' => $examresult);
        }
        echo json_encode($array);
    }


    public function generatemarksheet()
    {
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam_id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('check[]', $this->lang->line('students'), 'trim|required|xss_clean');


        if ($this->form_validation->run() == false) {


            $msg = array(
                'exam_id' => form_error('exam_id'),
                'check'   => form_error('check'),
            );


            $array = array('status' => 0, 'error' => $msg);
        } else {
            echo "<pre/>";
            $exam_id         = $this->input->post('exam_id');
            $students        = $this->input->post('check');
            $exam            = $this->examgroup_model->getExamByID($exam_id);
            $exam_id         = $exam->id;
            $students_result = array();
            if (!empty($students)) {
                foreach ($students as $student_key => $student_value) {
                    print_r($student_value);
                    exit();


                    $students_result[] = $this->examresult_model->getStudentExamResult($exam_id, $student_value);
                }
            }
            print_r($students_result);
            exit();
        }
        echo json_encode($array);
    }


    public function rankreport()
    {
        if (!$this->rbac->hasPrivilege('rank_report', 'can_view')) {
            access_denied();
        }
    
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/examinations');
        $this->session->set_userdata('subsub_menu', 'Reports/examinations/rankreport');
        
        $examgroup_result = $this->examgroup_model->get();
        $data['examgrouplist'] = $examgroup_result;
    
        $marksheet_result = $this->marksheet_model->get();
        $data['marksheetlist'] = $marksheet_result;
    
        $class = $this->class_model->get();
        $data['title'] = 'Add Batch';
        $data['title_list'] = 'Recent Batch';
        $data['examType'] = $this->exam_type;
        $data['classlist'] = $class;
        
        $session = $this->session_model->get();
        $data['sessionlist'] = $session;
        
    
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_group_id', $this->lang->line('exam_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('program_id', $this->lang->line('program'), 'trim|required|xss_clean');
    
        if ($this->form_validation->run() == true) {
            $exam_group_id = $this->input->post('exam_group_id');
            $exam_id = $this->input->post('exam_id');
            $session_id = $this->input->post('session_id');
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $program_id = $this->input->post('program_id'); // Get program_id from post
    
            $marksheet_template = $this->input->post('marksheet');
            $data['marksheet_template'] = $marksheet_template;
            $exam_details = $this->examgroup_model->getExamByID($exam_id);
    
            // Update the search function to include program_id
            $studentList = $this->examgroupstudent_model->searchExamStudents($exam_group_id, $exam_id, $class_id, $section_id, $session_id, $program_id);
    
            $exam_subjects = $this->batchsubject_model->getExamSubjects($exam_id);
            $data['subjectList'] = $exam_subjects;
    
            if (!empty($studentList)) {
                foreach ($studentList as $student_key => $student_value) {
                    $studentList[$student_key]->subject_results = $this->examresult_model->getStudentResultByExam($exam_id, $student_value->exam_group_class_batch_exam_student_id);
                }
            }
    
            $data['studentList'] = $studentList;
            $exam_grades = $this->grade_model->getByExamType($exam_details->exam_group_type);
            $data['exam_grades'] = $exam_grades;
            $data['exam_details'] = $exam_details;
            $data['exam_id'] = $exam_id;
            $data['exam_group_id'] = $exam_group_id;
        }
        
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examresult/rankreport', $data);
        $this->load->view('layout/footer', $data);
    }
    public function examinations()
    {
        if (!$this->rbac->hasPrivilege('rank_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/examinations');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('admin/examresult/examinations');
        $this->load->view('layout/footer');
    }

    public function getProgramsByClassSection()
    {
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        
        $this->load->model('program_model');
        $programs = $this->program_model->getProgramsByClassAndSection($class_id, $section_id);
        
        echo json_encode($programs);
    }

    /*@param int|string $marksheet_template
    * @param int|string $post_exam_id
    * @param int|string $post_exam_group_id
    * @param array|null $student_row_ids  exam_group_class_batch_exam_student_id[] (bulk)
    * @param int|string|null $student_id   single-student preview
    * @return array
    */

   
    private function _prepareMarksheetData($marksheet_template, $post_exam_id, $post_exam_group_id, $exam_group_class_batch_exam_student_ids = null, $single_student_id = null)
    {
        $data = [];
    
        // template
        $template = $this->marksheet_model->get($marksheet_template);
        $data['template'] = $template;
    
        // exam (may be NULL if user opened preview directly)
        $exam = null;
        if (!empty($post_exam_id)) {
            $exam = $this->examgroup_model->getExamByID($post_exam_id);
        }
        $data['exam'] = $exam;
    
        // resolve exam_group_type for grades
        $exam_type = null;
        if ($exam && !empty($exam->exam_group_type)) {
            $exam_type = $exam->exam_group_type; // 'gpa', 'coll_grade_system', etc.
        } else {
            // graceful fallback: take the first exam_type we find in grades table
            $row = $this->db->select('exam_type')
                            ->from('grades')
                            ->order_by('id', 'ASC')
                            ->limit(1)
                            ->get()->row();
            $exam_type = $row ? (string)$row->exam_type : 'coll_grade_system';
        }
    
        // grades + marks divisions
        $data['exam_grades']    = $this->grade_model->getByExamType($exam_type);
        $data['marks_division'] = $this->marksdivision_model->get();
    
        // school setting
        $data['sch_setting'] = $this->sch_setting_detail;
    
        // marksheet payload (single vs bulk)
        if ($single_student_id) {
            // single
            $egcbes_id = is_array($exam_group_class_batch_exam_student_ids)
                ? reset($exam_group_class_batch_exam_student_ids)
                : $exam_group_class_batch_exam_student_ids;
    
            $data['marksheet'] = $this->examresult_model->getStudentExamResults(
                $post_exam_id,
                $post_exam_group_id,
                $egcbes_id,
                $single_student_id
            );
    
            // helpful: also hand the view the raw student row
            $data['_student_row'] = $this->student_model->get($single_student_id);
        } else {
            // bulk
            $ids = is_array($exam_group_class_batch_exam_student_ids)
                ? $exam_group_class_batch_exam_student_ids
                : [];
            $data['marksheet'] = $this->examresult_model->getExamResults(
                $post_exam_id,
                $post_exam_group_id,
                $ids
            );
        }
    
        return $data;
    }
  
    public function viewmarksheet()
{
    try {
        // Allow both POST and GET, and multiple param name variants
        $marksheet_template =
            $this->input->post('marksheet_template') ??
            $this->input->get('marksheet_template') ??
            $this->input->post('template_id') ??
            $this->input->get('template_id');

        $exam_id =
            $this->input->post('post_exam_id') ??
            $this->input->get('post_exam_id') ??
            $this->input->post('exam_id') ??
            $this->input->get('exam_id');

        $exam_group_id =
            $this->input->post('post_exam_group_id') ??
            $this->input->get('post_exam_group_id') ??
            $this->input->post('exam_group_id') ??
            $this->input->get('exam_group_id');

        // students can come as JSON, as an array of IDs, or a single id for preview
        $students_json = $this->input->post('students');
        $student_ids = [];

        if ($students_json) {
            $arr = json_decode($students_json, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($arr)) {
                // expect [{"id":123}, ...]
                $student_ids = array_column($arr, 'id');
            }
        }

        if (empty($student_ids)) {
            // bulk selection from the standard form
            $bulk = $this->input->post('exam_group_class_batch_exam_student_id');
            if (is_array($bulk) && !empty($bulk)) {
                $student_ids = $bulk;
            }
        }

        if (empty($student_ids)) {
            // single-student preview
            $sid = $this->input->post('student_id') ?? $this->input->get('student_id')
                ?? $this->input->get('egcbes_id'); // allow ?egcbes_id=...
            if (!empty($sid)) {
                $student_ids = [$sid];
            }
        }

        // If core params are missing, show a tiny helper form instead of a blank page
        if (!$marksheet_template || !$exam_id || !$exam_group_id) {
            $missing = [];
            if (!$marksheet_template) $missing[] = 'marksheet_template';
            if (!$exam_id)           $missing[] = 'post_exam_id';
            if (!$exam_group_id)     $missing[] = 'post_exam_group_id';

            // helpful error with a tiny HTML form so you can retry quickly
            show_error(
                'Missing required parameters: '.implode(', ', $missing).
                '<br><br><form method="get" style="font-family:monospace">
                    <label>marksheet_template: <input name="marksheet_template" required></label><br>
                    <label>post_exam_id: <input name="post_exam_id" required></label><br>
                    <label>post_exam_group_id: <input name="post_exam_group_id" required></label><br>
                    <label>student_id (optional): <input name="student_id"></label><br>
                    <button type="submit">Try viewmarksheet</button>
                 </form>',
                400
            );
            return;
        }

        // Fetch all the data the view needs
        $data                   = [];
        $data['marksheet']      = $this->examresult_model->getExamResults($exam_id, $exam_group_id, $student_ids);
        $data['template']       = $this->marksheet_model->get($marksheet_template);
        $data['exam']           = $this->examgroup_model->getExamByID($exam_id);
        $data['exam_grades']    = $this->grade_model->getByExamType($data['exam']->exam_group_type);
        $data['marks_division'] = $this->marksdivision_model->get();
        $data['sch_setting']    = $this->sch_setting_detail;

        // ✅ NEW: Fetch attendance data and non-academic marks for each student
        // Get class_id, section_id, session_id from post/get (for fallback)
        $class_id = $this->input->post('class_id') ?: $this->input->get('class_id');
        $section_id = $this->input->post('section_id') ?: $this->input->get('section_id');
        $session_id = $this->input->post('session_id') ?: $this->input->get('session_id');
        
        // If session_id not provided, get it from exam
        if (!$session_id && isset($data['exam']->session_id)) {
            $session_id = $data['exam']->session_id;
        }
        
        // Check marksheet structure - it might be ['students'] or direct array
        $students_array = isset($data['marksheet']['students']) ? $data['marksheet']['students'] : (isset($data['marksheet'][0]) ? $data['marksheet'] : array());
        
        if (!empty($students_array) && is_array($students_array)) {
            foreach ($students_array as $key => $student_entry) {
                // Extract class_id, section_id from student data (getExamStudentByID includes these)
                $student_class_id = isset($student_entry['class_id']) ? $student_entry['class_id'] : $class_id;
                $student_section_id = isset($student_entry['section_id']) ? $student_entry['section_id'] : $section_id;
                $student_session_id = $session_id; // Use session from exam or post/get
                
                // Get exam_group_class_batch_exam_student_id - check different possible keys
                $egcbes_id = null;
                if (isset($student_entry['exam_group_class_batch_exam_student_id'])) {
                    $egcbes_id = $student_entry['exam_group_class_batch_exam_student_id'];
                } elseif (isset($student_entry['id'])) {
                    $egcbes_id = $student_entry['id'];
                }
                
                if ($egcbes_id && $student_class_id && $student_section_id && $student_session_id) {
                    // Fetch attendance data
                    $attendance_data = $this->get_student_attendance(
                        $egcbes_id, 
                        $student_class_id, 
                        $student_section_id, 
                        $student_session_id
                    );
                    
                    // Fetch non-academic marks - get student_id from student_entry or query
                    $student_id = isset($student_entry['student_id']) ? $student_entry['student_id'] : null;
                    if (!$student_id) {
                        $exam_student = $this->db->select('student_id')
                            ->where('id', $egcbes_id)
                            ->get('exam_group_class_batch_exam_students')
                            ->row();
                        if ($exam_student) {
                            $student_id = $exam_student->student_id;
                        }
                    }
                    
                    $nonacademic_marks = array();
                    if ($student_id) {
                        $nonacademic_marks = $this->nonacademic_marks_model->getStudentNonAcademicMarks(
                            $exam_id, 
                            $student_id
                        );
                    }
                    
                    // Add to marksheet data structure
                    if (isset($data['marksheet']['students'])) {
                        $data['marksheet']['students'][$key]['attendance_data'] = $attendance_data;
                        $data['marksheet']['students'][$key]['nonacademic_marks'] = $nonacademic_marks;
                    } else {
                        $data['marksheet'][$key]['attendance_data'] = $attendance_data;
                        $data['marksheet'][$key]['nonacademic_marks'] = $nonacademic_marks;
                    }
                } else {
                    // Set defaults if class/section/session not available
                    $default_attendance = ['working_days' => 0, 'present_days' => 0, 'absent_days' => 0];
                    if (isset($data['marksheet']['students'])) {
                        $data['marksheet']['students'][$key]['attendance_data'] = $default_attendance;
                        $data['marksheet']['students'][$key]['nonacademic_marks'] = array();
                    } else {
                        $data['marksheet'][$key]['attendance_data'] = $default_attendance;
                        $data['marksheet'][$key]['nonacademic_marks'] = array();
                    }
                }
            }
        }

        echo "<!-- reached Examresult::viewmarksheet with template={$marksheet_template}, exam={$exam_id}, group={$exam_group_id} -->";
        $data['marksheet_autoprint'] = (
            $this->input->post('autoprint') === '1'
            || $this->input->get('autoprint') === '1'
        );
        $this->load->view('admin/examresult/_printmarksheet', $data);

    } catch (Throwable $e) {
        log_message('error', 'view_marksheet fatal: '.$e->getMessage());
        show_error('Internal server error in view_marksheet()', 500);
    }
}

    /**
     * Helper method to fetch attendance data for a student
     * @param int $exam_group_class_batch_exam_student_id
     * @param int $class_id
     * @param int $section_id
     * @param int $session_id
     * @return array
     */
    private function get_student_attendance($exam_group_class_batch_exam_student_id, $class_id, $section_id, $session_id)
    {
        // First, get the actual student_id from exam_group_class_batch_exam_students table
        $exam_student = $this->db
            ->select('student_id')
            ->where('id', $exam_group_class_batch_exam_student_id)
            ->get('exam_group_class_batch_exam_students')
            ->row();
    
        if (!$exam_student) {
            return [
                'working_days' => 0,
                'present_days' => 0,
                'absent_days' => 0
            ];
        }
    
        $student_id = $exam_student->student_id;
    
        // Get student_session_id
        $student_session_id = $this->student_model->getStudentSessionID(
            $student_id, $class_id, $section_id, $session_id
        );
    
        if (!$student_session_id) {
            return [
                'working_days' => 0,
                'present_days' => 0,
                'absent_days' => 0
            ];
        }
    
        // Fetch attendance record
        $attendance = $this->db
            ->select('working_days, remark')
            ->where('student_session_id', $student_session_id)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('student_attendences')
            ->row();
    
        if (!$attendance) {
            return [
                'working_days' => 0,
                'present_days' => 0,
                'absent_days' => 0
            ];
        }
    
        // Parse present and absent days from remark
        $present_days = 0;
        $absent_days = 0;
    
        if (preg_match('/Present:\s*(\d+)/', $attendance->remark, $m)) {
            $present_days = (int)$m[1];
        }
        if (preg_match('/Absent:\s*(\d+)/', $attendance->remark, $m)) {
            $absent_days = (int)$m[1];
        }
    
        return [
            'working_days' => (int)$attendance->working_days,
            'present_days' => $present_days,
            'absent_days' => $absent_days
        ];
    }

    /**
     * Get saved attendance data for selected students
     * Route: POST admin/examresult/get_saved_attendance
     */
    public function get_saved_attendance()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $session_id = $this->input->post('session_id');
        $students   = json_decode($this->input->post('students'), true);
    
        $response = [];
        $debug = [];
        $working_days = 0;
    
        // First, try to get working_days from ANY student in this class/section/session
        // This ensures all students see the same working days
        if (!empty($students)) {
            $first_student = $students[0];
            $student_session_id = $this->student_model->getStudentSessionID(
                $first_student['student_id'], $class_id, $section_id, $session_id
            );
    
            if ($student_session_id) {
                $working_days_record = $this->db
                    ->select('working_days')
                    ->where('student_session_id', $student_session_id)
                    ->where('working_days >', 0)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('student_attendences')
                    ->row();
    
                if ($working_days_record && isset($working_days_record->working_days)) {
                    $working_days = (int)$working_days_record->working_days;
                }
            }
        }
    
        // Now fetch individual student attendance data
        foreach ($students as $stu) {
            $student_id = $stu['student_id'];
            $student_session_id = $this->student_model->getStudentSessionID(
                $student_id, $class_id, $section_id, $session_id
            );
    
            if (!$student_session_id) {
                $debug[] = "Missing session for student_id {$student_id}";
                continue;
            }
    
            $att = $this->db
                ->select('remark, updated_at')
                ->where('student_session_id', $student_session_id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('student_attendences')
                ->row();
    
            if ($att) {
                $present = $absent = 0;
    
                if (preg_match('/Present:\s*(\d+)/', $att->remark, $m)) $present = (int)$m[1];
                if (preg_match('/Absent:\s*(\d+)/', $att->remark, $m)) $absent = (int)$m[1];
    
                $response[$student_id] = [
                    'present_days' => $present,
                    'absent_days'  => $absent,
                    'updated_at'   => $att->updated_at ?: '—'
                ];
            }
        }
    
        echo json_encode([
            'status' => 1,
            'data' => $response,
            'working_days' => $working_days, // This applies to ALL students
            'debug' => $debug
        ]);
    }
    
    /**
     * Save attendance summary for students
     * Route: POST admin/examresult/save_attendance_summary
     */
    public function save_attendance_summary()
    {
        $class_id     = $this->input->post('class_id');
        $section_id   = $this->input->post('section_id');
        $session_id   = $this->input->post('session_id');
        $working_days = $this->input->post('working_days');
        $students     = json_decode($this->input->post('students'), true);

        if (empty($students) || !$working_days) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid data']);
            return;
        }

        $saved_count = 0;
        $failed_count = 0;

        foreach ($students as $stu) {
            $student_id = $stu['student_id'];
            $present_days = $stu['present_days'];
            $absent_days = $stu['absent_days'];

            // Get student_session_id
            $student_session_id = $this->student_model->getStudentSessionID(
                $student_id, $class_id, $section_id, $session_id
            );

            if (!$student_session_id) {
                $failed_count++;
                continue;
            }

            // Prepare remark with present/absent info
            $remark = "Present: {$present_days}, Absent: {$absent_days}";

            // Check if record exists for this student
            $existing = $this->db
                ->where('student_session_id', $student_session_id)
                ->get('student_attendences')
                ->row();

            $data = [
                'student_session_id' => $student_session_id,
                'working_days' => $working_days, // Save working_days for EACH student
                'remark' => $remark,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                // Update existing record
                $this->db->where('student_session_id', $student_session_id)
                         ->update('student_attendences', $data);
            } else {
                // Insert new record
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('student_attendences', $data);
            }

            $saved_count++;
        }

        // IMPORTANT: Also update working_days for ALL OTHER students in same class/section/session
        // This ensures when you select different students later, they all have the same working_days
        $this->update_working_days_for_all_students($class_id, $section_id, $session_id, $working_days, $students);

        echo json_encode([
            'status' => 1,
            'msg' => "Saved {$saved_count} records successfully",
            'saved' => $saved_count,
            'failed' => $failed_count
        ]);
    }

    /**
     * Helper function to update working_days for all students in the class/section/session
     */
    private function update_working_days_for_all_students($class_id, $section_id, $session_id, $working_days, $selected_students)
    {
        // Get all students in this class/section/session (not just selected ones)
        $all_students_query = $this->db
            ->select('ss.id as student_session_id')
            ->from('student_session ss')
            ->where('ss.class_id', $class_id)
            ->where('ss.section_id', $section_id)
            ->where('ss.session_id', $session_id)
            ->get();

        if ($all_students_query->num_rows() > 0) {
            foreach ($all_students_query->result() as $row) {
                $student_session_id = $row->student_session_id;

                // Check if attendance record exists
                $existing = $this->db
                    ->where('student_session_id', $student_session_id)
                    ->get('student_attendences')
                    ->row();

                if ($existing) {
                    // Only update working_days, keep existing remark
                    $this->db->where('student_session_id', $student_session_id)
                             ->update('student_attendences', [
                                 'working_days' => $working_days,
                                 'updated_at' => date('Y-m-d H:i:s')
                             ]);
                }
                // Note: We don't create new records for students who haven't been selected yet
                // They will get the working_days when they are first selected and saved
            }
        }
    }

    /**
     * Get non-academic subjects and saved marks for selected students
     * Route: POST admin/examresult/get_nonacademic_marks
     */
    public function get_nonacademic_marks()
    {
        try {
            $exam_id = $this->input->post('exam_id');
            $exam_group_id = $this->input->post('exam_group_id');
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $session_id = $this->input->post('session_id');
            $students_json = $this->input->post('students');
            
            if (empty($exam_id) || empty($class_id) || empty($section_id) || empty($session_id) || empty($students_json)) {
                echo json_encode([
                    'status' => 0,
                    'msg' => 'Missing required parameters'
                ]);
                return;
            }

            $students = json_decode($students_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE || empty($students)) {
                echo json_encode([
                    'status' => 0,
                    'msg' => 'Invalid students data format'
                ]);
                return;
            }
            
            // Get non-academic subjects for this class/section through subject groups
            $subjects = $this->nonacademic_marks_model->getNonAcademicSubjects($class_id, $section_id, $session_id);
            
            // Get student IDs
            $student_ids = array();
            foreach ($students as $stu) {
                if (isset($stu['student_id'])) {
                    $student_ids[] = $stu['student_id'];
                }
            }
            
            // Get saved marks for these students
            $saved_marks = $this->nonacademic_marks_model->getStudentsNonAcademicMarks($exam_id, $student_ids);
            
            // Organize marks by student_id_subject_id key
            $marks_organized = array();
            foreach ($saved_marks as $mark) {
                $key = $mark['student_id'] . '_' . $mark['subject_id'];
                $marks_organized[$key] = $mark;
            }
            
            echo json_encode([
                'status' => 1,
                'subjects' => $subjects,
                'marks' => $marks_organized
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Exception in get_nonacademic_marks: ' . $e->getMessage());
            echo json_encode([
                'status' => 0,
                'msg' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save non-academic marks for students
     * Route: POST admin/examresult/save_nonacademic_marks
     */
    public function save_nonacademic_marks()
    {
        $marks_json = $this->input->post('marks');
        
        if (empty($marks_json)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'No marks data provided'
            ]);
            return;
        }

        $marks = json_decode($marks_json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || empty($marks)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Invalid marks data format'
            ]);
            return;
        }

        // Prepare data for bulk save
        $marks_data = array();
        foreach ($marks as $mark) {
            if (!isset($mark['exam_id']) || !isset($mark['student_id']) || !isset($mark['subject_id'])) {
                continue;
            }

            $marks_data[] = array(
                'exam_id' => $mark['exam_id'],
                'exam_group_id' => isset($mark['exam_group_id']) ? $mark['exam_group_id'] : 0,
                'student_id' => $mark['student_id'],
                'subject_id' => $mark['subject_id'],
                'marks_obtained' => isset($mark['marks_obtained']) ? floatval($mark['marks_obtained']) : 0,
                'remarks' => isset($mark['remarks']) ? $mark['remarks'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }

        if (empty($marks_data)) {
            echo json_encode([
                'status' => 0,
                'msg' => 'No valid marks data to save'
            ]);
            return;
        }

        // Save marks
        $result = $this->nonacademic_marks_model->saveBulkNonAcademicMarks($marks_data);

        if ($result) {
            echo json_encode([
                'status' => 1,
                'msg' => 'Non-academic marks saved successfully',
                'saved_count' => count($marks_data)
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'msg' => 'Failed to save non-academic marks'
            ]);
        }
    }
    
    
}