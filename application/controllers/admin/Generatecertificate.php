<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Generatecertificate extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Customlib');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->library('media_storage');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('generate_certificate', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generatecertificate');

        $certificateList         = $this->Certificate_model->getstudentcertificate();
        $data['certificateList'] = $certificateList;
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/certificate/generatecertificate', $data);
        $this->load->view('layout/footer', $data);
    }

    public function search()
    {
        $this->session->set_userdata('top_menu', 'Certificate');
        $this->session->set_userdata('sub_menu', 'admin/generatecertificate');

        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $certificateList         = $this->Certificate_model->getstudentcertificate();
        $data['certificateList'] = $certificateList;

        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/certificate/generatecertificate', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class       = $this->input->post('class_id');
            $section     = $this->input->post('section_id');
            $program     = $this->input->post('program_id');
            $search      = $this->input->post('search');
            $certificate = $this->input->post('certificate_id');

            if (isset($search)) {
                $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('certificate_id', $this->lang->line('certificate'), 'trim|required|xss_clean');
                if ($this->form_validation->run() == false) {
                    // fallthrough to view
                } else {
                    $data['searchby']          = "filter";
                    $data['class_id']          = $this->input->post('class_id');
                    $data['section_id']        = $this->input->post('section_id');
                    $data['program_id']        = $this->input->post('program_id');
                    $data['certificate_id']    = $this->input->post('certificate_id');

                    // For dropdown retention
                    $_POST['program_id']     = $data['program_id'];
                    $_POST['class_id']       = $data['class_id'];
                    $_POST['section_id']     = $data['section_id'];
                    $_POST['certificate_id'] = $data['certificate_id'];

                    $certificate               = $this->input->post('certificate_id');
                    $certificateResult         = $this->Generatecertificate_model->getcertificatebyid($certificate);
                    $data['certificateResult'] = $certificateResult;

                    // Only fetch students with active student_session
                    $this->db->select('students.id, students.firstname, students.middlename, students.lastname,
                        students.admission_no, students.father_name, students.dob, students.gender, students.mobileno,
                        IFNULL(categories.category, "") as category,
                        students.registration_no, students.symbol_no, students.roll_no,
                        classes.id as class_id, classes.class, sections.id as section_id, sections.section,
                        programs.id as program_id, programs.title as program_title');
                    $this->db->from('students');
                    $this->db->join('student_session', 'student_session.student_id = students.id');
                    $this->db->join('classes', 'classes.id = student_session.class_id');
                    $this->db->join('sections', 'sections.id = student_session.section_id');
                    $this->db->join('programs', 'programs.id = student_session.program_id', 'left');
                    $this->db->join('categories', 'students.category_id = categories.id', 'left');
                    $this->db->where('student_session.class_id', $data['class_id']);
                    $this->db->where('student_session.section_id', $data['section_id']);
                    $this->db->where('student_session.program_id', $data['program_id']);
                    $this->db->where('student_session.session_id', $this->sch_setting_detail->session_id);
                    $this->db->where('student_session.is_active', 'yes');
                    $this->db->where('students.is_active', 'yes');
                    $this->db->order_by('students.firstname');
                    $resultlist = $this->db->get()->result_array();
                    $data['resultlist'] = $resultlist;

                    $title         = $this->classsection_model->getDetailbyClassSection($data['class_id'], $data['section_id']);
                    $data['title'] = $this->lang->line('std_dtl_for').' '.$title['class'].'('.$title['section'].')';
                }
            }

            $data['sch_setting'] = $this->sch_setting_detail;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/certificate/generatecertificate', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function generate($student, $class, $certificate)
    {
        $certificateResult         = $this->Generatecertificate_model->getcertificatebyid($certificate);
        $data['certificateResult'] = $certificateResult;
        $resultlist                = $this->student_model->searchByClassStudent($class, $student);
        $data['resultlist']        = $resultlist;

        $this->load->view('admin/certificate/transfercertificate', $data);
    }

    public function generatemultiple()
    {
        try {
            $studentid      = $this->input->post('data');
            $certificate_id = $this->input->post('certificate_id');
            $class          = $this->input->post('class_id');
            $program        = $this->input->post('program_id');

            if (empty($studentid)) {
                echo json_encode(['error' => 'No student data received']); return;
            }
            if (empty($certificate_id)) {
                echo json_encode(['error' => 'No certificate ID received']); return;
            }

            $student_array = json_decode($studentid);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['error' => 'Invalid JSON data: '.json_last_error_msg()]); return;
            }

            $data = [];
            $std_arr = [];

            $data['sch_setting'] = $this->setting_model->get();
            $data['certificate'] = $this->Generatecertificate_model->getcertificatebyid($certificate_id);
            if (empty($data['certificate'])) {
                echo json_encode(['error' => 'Certificate not found']); return;
            }

            if (empty($student_array) || !is_array($student_array)) {
                echo json_encode(['error' => 'No students selected']); return;
            }
            foreach ($student_array as $value) {
                if (isset($value->student_id) && is_numeric($value->student_id)) {
                    $std_arr[] = (int)$value->student_id;
                }
            }
            if (empty($std_arr)) {
                echo json_encode(['error' => 'No valid student IDs found']); return;
            }

            $students = $this->student_model->getStudentsByArray($std_arr);

            $valid_students = [];
            if (!empty($students) && is_array($students)) {
                foreach ($students as $student) {
                    if (is_array($student) && isset($student['firstname'])) {
                        $student_obj = (object)$student;

                        // Ensure these placeholders exist for the template
                        $student_obj->registration_no = $student['registration_no'] ?? '';
                        $student_obj->symbol_no       = $student['symbol_no'] ?? '';
                        $student_obj->roll_no         = $student['roll_no'] ?? '';

                        // Friendly full name if template uses [name]
                        $student_obj->name = $this->customlib->getFullName(
                            $student['firstname'] ?? '',
                            $student['middlename'] ?? '',
                            $student['lastname'] ?? '',
                            $data['sch_setting']->middlename ?? '',
                            $data['sch_setting']->lastname ?? ''
                        );

                        $valid_students[] = $student_obj;
                    }
                }
            }
            if (empty($valid_students)) {
                echo json_encode(['error' => 'No valid students found for certificate generation.']); return;
            }

            $data['students'] = $valid_students;
            $data['sch_setting'] = $this->sch_setting_detail ?? $data['sch_setting'];

            // Render final HTML (not JSON) — caller injects into print iframe
            $certificates = $this->load->view('admin/certificate/printcertificate', $data, true);
            if (empty($certificates)) {
                echo json_encode(['error' => 'Certificate template generated empty content']); return;
            }
            echo $certificates;

        } catch (Exception $e) {
            log_message('error', 'Error in generatemultiple(): '.$e->getMessage());
            echo json_encode(['error' => 'Unexpected error: '.$e->getMessage()]);
        }
    }
}
