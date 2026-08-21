<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}








class Examgroup extends Admin_Controller
{
    public $exam_type            = array();
    private $sch_current_session = "";








    public function __construct()
    {
        parent::__construct();
        $this->load->library('encoding_lib');
        $this->load->library('mailsmsconf');
        $this->exam_type           = $this->config->item('exam_type');
        $this->sch_current_session = $this->setting_model->getCurrentSession();
        $this->attendence_exam     = $this->config->item('attendence_exam');
        $this->sch_setting_detail  = $this->setting_model->getSetting();
    }








    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_marks_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_marks_sample_file.csv';
        force_download($name, $data);
    }








    public function uploadfile()
    {
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {
            $return_array = array();
//====================
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {








                $fileName = $_FILES["file"]["tmp_name"];
                if (isset($_FILES["file"]) && !empty($_FILES['file']['name']) && $_FILES["file"]["size"] > 0) {








                    $file = fopen($fileName, "r");
                    $flag = true;
                    while (($column = fgetcsv($file, 10000, ",")) !== false) {
                        if ($flag) {
                            $flag = false;
                            continue;
                        }
                        if (trim($column['0']) != "" && trim($column['1']) != "" && trim($column['2']) != "") {
                            $return_array[] = json_encode(
                                array(
                                    'adm_no'     => $column['0'],
                                    'attendence' => $column['1'],
                                    'marks'      => number_format($column['2'], 2, '.', ''),
                                    'note'       => $this->encoding_lib->toUTF8($column['3']),
                                )
                            );
                        }
                    }
                }








                $array = array('status' => '1', 'error' => '', 'student_marks' => $return_array);
                echo json_encode($array);
            }
            //=============
        }
    }








    public function handle_upload()
    {
        $image_validate = $this->config->item('csv_validate');








        if (isset($_FILES["file"]) && !empty($_FILES['file']['name']) && $_FILES["file"]["size"] > 0) {








            $file_type         = $_FILES["file"]['type'];
            $file_size         = $_FILES["file"]["size"];
            $file_name         = $_FILES["file"]["name"];
            $allowed_extension = $image_validate['allowed_extension'];
            $ext               = pathinfo($file_name, PATHINFO_EXTENSION);
            $allowed_mime_type = $image_validate['allowed_mime_type'];
            $finfo             = finfo_open(FILEINFO_MIME_TYPE);
            $mtype             = finfo_file($finfo, $_FILES['file']['tmp_name']);
            finfo_close($finfo);








            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }








            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($file_size > $image_validate['upload_size']) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                return false;
            }








            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }








    public function index()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/examgroup');








        $data['examType'] = $this->exam_type;
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_type', $this->lang->line('exam_type'), 'trim|required|xss_clean');








        if ($this->form_validation->run() == false) {








        } else {
            $is_active = 0;








            $data = array(
                'name'        => $this->input->post('name'),
                'exam_type'   => $this->input->post('exam_type'),
                'is_active'   => $is_active,
                'description' => $this->input->post('description'),
            );








            $insert_id = $this->examgroup_model->add($data);








            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/examgroup/index');
        }
        $examgroup_result      = $this->examgroup_model->get();
        $data['examgrouplist'] = $examgroup_result;
        
        // Load subject groups for dropdown
        $data['subject_groups'] = $this->subjectgroup_model->getByID();






        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/examgroupList', $data);
        $this->load->view('layout/footer', $data);
    }








    public function getExamByExamgroup()
    {
        $exam_group_id = $this->input->post('exam_group_id');
        $data          = $this->examgroup_model->getExamByExamGroup($exam_group_id, true);
        echo json_encode($data);
    }








    public function deleteExam()
    {
        $data['title'] = 'deleteExam';
        $id            = $this->input->post('id');
        if (!$this->examgroup_model->delete_exam($id)) {
            echo json_encode(array('status' => 0, 'message' => $this->lang->line('something_went_wrong')));
        } else {
            echo json_encode(array('status' => 1, 'message' => $this->lang->line('record_deleted_successfully')));
        }
    }








    public function exam($id)
    {
        $data                    = array();
        $data['examgroupDetail'] = $this->examgroup_model->getExamByID($id);
        $data['exam_subjects']   = $this->batchsubject_model->getExamSubjects($id);
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $session                 = $this->session_model->get();
        $data['sessionlist']     = $session;
        $data['current_session'] = $this->sch_current_session;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/exam', $data);
        $this->load->view('layout/footer', $data);
    }








    public function examresult($id)
    {
        $data = array();








        $data['id']        = $id;
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $exam_subject_id                                = $this->input->post('exam_group_class_batch_exam_subject_id');
            $class_id                                       = $this->input->post('class_id');
            $batch_id                                       = $this->input->post('batch_id');
            $data['class_id']                               = $this->input->post('class_id');
            $data['batch_id']                               = $this->input->post('batch_id');
            $data['exam_group_class_batch_exam_subject_id'] = $this->input->post('exam_group_class_batch_exam_subject_id');








            $data['exam_subjects'] = $this->batchsubject_model->getExamSubjects($id);
            $resultlist            = $this->batchsubject_model->examGroupExamResult($class_id, $batch_id, $id);








            $data['resultlist'] = $resultlist;
        }








        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/examresult', $data);
        $this->load->view('layout/footer', $data);
    }








    public function addmark($id)
    {
        $data = array();








        $data['exam_subjects'] = $this->batchsubject_model->getExamSubjects($id);
        $data['id']            = $id;
        $class                 = $this->class_model->get();
        $data['classlist']     = $class;
        $session               = $this->session_model->get();
        $data['sessionlist']   = $session;
        if ($this->input->server('REQUEST_METHOD') == 'POST') {








            $exam_subject_id                                = $this->input->post('exam_group_class_batch_exam_subject_id');
            $data['exam_group_class_batch_exam_subject_id'] = $this->input->post('exam_group_class_batch_exam_subject_id');
            $class_id                                       = $this->input->post('class_id');
            $section_id                                     = $this->input->post('section_id');
            $session_id                                     = $this->input->post('session_id');
            $data['class_id']                               = $this->input->post('class_id');
            $data['section_id']                             = $this->input->post('section_id');
            $data['session_id']                             = $this->input->post('session_id');
            $subject_detail                                 = $this->batchsubject_model->getExamSubject($exam_subject_id);
            $resultlist                                     = $this->examgroupstudent_model->examGroupSubjectResult($exam_subject_id, $class_id, $section_id, $session_id);

            // If no exam-student mapping exists yet, auto-sync active students for this class/section/session.
            if (empty($resultlist) && !empty($subject_detail) && !empty($subject_detail->exam_group_class_batch_exams_id)) {
                $exam_group_class_batch_exam_id = $subject_detail->exam_group_class_batch_exams_id;

                $student_sessions = $this->db
                    ->select('id as student_session_id, student_id')
                    ->from('student_session')
                    ->where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('session_id', $session_id)
                    ->where('is_active', 'yes')
                    ->get()
                    ->result_array();

                if (!empty($student_sessions)) {
                    foreach ($student_sessions as $student_session_row) {
                        $this->examstudent_model->insert(array(
                            'exam_group_class_batch_exam_id' => $exam_group_class_batch_exam_id,
                            'student_id'                     => $student_session_row['student_id'],
                            'student_session_id'             => $student_session_row['student_session_id'],
                            'roll_no'                        => 0,
                        ));
                    }
                }

                // Reload result list after sync.
                $resultlist = $this->examgroupstudent_model->examGroupSubjectResult($exam_subject_id, $class_id, $section_id, $session_id);
            }
            $data['subject_detail']                         = $subject_detail;
            $data['attendence_exam']                        = $this->attendence_exam;
            $data['resultlist']                             = $resultlist;
        }








        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/addmark', $data);
        $this->load->view('layout/footer', $data);
    }








    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_delete')) {
            access_denied();
        }








        $this->examgroup_model->remove($id);
        redirect('admin/examgroup');
    }








    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_edit')) {
            access_denied();
        }








        $data['id']            = $id;
        $examgroup             = $this->examgroup_model->get($id);
        $data['examgroup']     = $examgroup;
        $data['examType']      = $this->exam_type;
        $examgroup_result      = $this->examgroup_model->get();
        $data['examgrouplist'] = $examgroup_result;
        
        // Load subject groups for dropdown
        $data['subject_groups'] = $this->subjectgroup_model->getByID();








        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');








        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/examgroup/examgroupEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $is_active = 0;








            $data = array(
                'id'          => $this->input->post('id'),
                'name'        => $this->input->post('name'),
                'exam_type'   => $this->input->post('exam_type'),
                'is_active'   => $is_active,
                'description' => $this->input->post('description'),
            );
            $insert_id = $this->examgroup_model->add($data);








            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/examgroup/index');
        }
    }








    public function getByClassSection()
    {
        $section_id = $this->input->post('section_id');
        $data       = $this->examgroup_model->getStudentBatch($section_id);
        echo json_encode($data);
    }








   public function addexam($id)
    {








        if (!$this->rbac->hasPrivilege('exam', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/examgroup');
        $data['title']      = 'Add Batch';
        $data['title_list'] = 'Recent Batch';








        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['examType']    = $this->exam_type;
        $session             = $this->session_model->get();
        $data['sessionlist'] = $session;
        $subjectlist         = $this->subject_model->get();
        $data['subjectlist'] = $subjectlist;








        $data['current_session'] = $this->sch_current_session;
        
        // Load subject groups for dropdown in exam creation modal
        $data['subject_groups'] = $this->subjectgroup_model->getByID();
        
        if (empty($subjectlist)) {
            $this->session->set_flashdata('msg', $this->lang->line('there_is_no_class_subject_assigned_for_you'));
            access_denied();
        } else {
            $data['examgroup'] = $this->examgroup_model->get($id);
            if (empty($data['examgroup'])) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Exam group not found or may have been removed.</div>');
                redirect('admin/examgroup/index');
            }
        }
       








        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/addexam', $data);
        $this->load->view('layout/footer', $data);
    }








    public function getNotAppliedDiscount($student_session_id)
    {
        return $this->feediscount_model->getDiscountNotApplied($student_session_id);
    }








    public function subjectstudent()
    {
        $this->form_validation->set_error_delimiters('<p>', '</p>');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'required|trim|xss_clean');
        
        // Get session_id from POST, or use current session if not provided
        $post_session_id = $this->input->post('session_id');
        if (empty($post_session_id)) {
            // Use current session if session_id is not provided
            $current_session = $this->sch_current_session;
            $_POST['session_id'] = $current_session;
        }
        
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean');
        $userdata = $this->customlib->getUserData();
        $role_id  = $userdata["role_id"];
        $can_edit = 1;
        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            $myclasssubjects = $this->subjecttimetable_model->canAddExamMarks($userdata["id"], $this->input->post('class_id'), $this->input->post('section_id'), $this->input->post('teachersubject_id'));
            $can_edit        = $myclasssubjects;
        }








        if ($this->form_validation->run() == false) {
            $data = array(
                'class_id'   => form_error('class_id'),
                'section_id' => form_error('section_id'),
                'session_id' => form_error('session_id'),
                'subject_id' => form_error('subject_id'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } elseif ($can_edit == 0) {
            $msg   = array('lesson' => $this->lang->line('not_authoried'));
            $array = array('status' => 0, 'error' => $msg);
            echo json_encode($array);
        } else {
            $exam_subject_id                                = $this->input->post('subject_id');
            $data['exam_group_class_batch_exam_subject_id'] = $exam_subject_id;
            $class_id                                       = $this->input->post('class_id');
            $section_id                                     = $this->input->post('section_id');
            // Use backend current session so marks search matches Link Students (which uses current session)
            $session_id                                     = $this->sch_current_session;
            $program_id                                     = $this->input->post('program_id');
            $data['class_id']                               = $this->input->post('class_id');
            $data['section_id']                             = $this->input->post('section_id');
            $data['session_id']                             = $session_id;
            $subject_detail                                 = $this->batchsubject_model->getExamSubject($exam_subject_id);
            if (empty($subject_detail) || empty((int) $exam_subject_id)) {
                $array = array('status' => 0, 'error' => array('subject_id' => $this->lang->line('invalid_subject') ?: 'Invalid subject.'));
                echo json_encode($array);
                return;
            }
            $resultlist                                     = $this->examgroupstudent_model->examGroupSubjectResult($exam_subject_id, $class_id, $section_id, $session_id, $program_id);

            $data['linked_levels'] = array();
            if (empty($resultlist) && !empty($subject_detail->exam_group_class_batch_exams_id)) {
                $data['linked_levels'] = $this->examgroupstudent_model->getLinkedLevelsByExam($subject_detail->exam_group_class_batch_exams_id);
                // Use same session as getLinkedLevelsByExam (model's current_session) so retry matches
                $session_for_retry = $this->examgroupstudent_model->current_session;
                if ($session_for_retry === null || $session_for_retry === '') {
                    $session_for_retry = $session_id;
                }
                // First try: show ALL linked students for this exam (current session), no class/section filter
                $resultlist = $this->examgroupstudent_model->examGroupSubjectResultAllLinked(
                    $exam_subject_id,
                    $subject_detail->exam_group_class_batch_exams_id
                );
                // If still empty, try exact IDs from linked students per level (handles dropdown ID/label mismatch)
                if (empty($resultlist)) foreach ($data['linked_levels'] as $lev) {
                    $try_section  = isset($lev['section_id']) ? $lev['section_id'] : null;
                    $try_class    = isset($lev['class_id']) ? $lev['class_id'] : null;
                    $try_program  = array_key_exists('program_id', $lev) ? $lev['program_id'] : null;
                    if ($try_section === null || $try_class === null) {
                        continue;
                    }
                    // First try with program_id (exact match)
                    $resultlist = $this->examgroupstudent_model->examGroupSubjectResult($exam_subject_id, $try_class, $try_section, $session_for_retry, $try_program);
                    // If no rows, retry without program filter (section+class+session only)
                    if (empty($resultlist)) {
                        $resultlist = $this->examgroupstudent_model->examGroupSubjectResult($exam_subject_id, $try_class, $try_section, $session_for_retry, null);
                    }
                    // Fallback: resolve linked student_session IDs for this exam+level then build result list (avoids JOIN/type issues)
                    if (empty($resultlist)) {
                        $resultlist = $this->examgroupstudent_model->examGroupSubjectResultByLinkedLevel(
                            $exam_subject_id,
                            $subject_detail->exam_group_class_batch_exams_id,
                            $try_class,
                            $try_section,
                            $session_for_retry
                        );
                    }
                    if (!empty($resultlist)) {
                        $data['section_id'] = $try_section;
                        $data['class_id']   = $try_class;
                        break;
                    }
                }
            }




            $data['subject_detail']  = $subject_detail;
            $data['attendence_exam'] = $this->attendence_exam;
            $data['resultlist']      = $resultlist;
            $data['sch_setting']     = $this->sch_setting_detail;
            $data['no_record_hint'] = '';
            if (empty($resultlist) && !empty($data['linked_levels'])) {
                $data['no_record_hint'] = $this->lang->line('no_students_match_faculty_level_session') ?: 'No students match the selected Faculty / Academic Level for the current session. Use the same Faculty and Academic Level you selected in Link Students for this exam.';
            }
            $student_exam_page       = $this->load->view('admin/examgroup/_partialstudentmarkEntry', $data, true);








            $array = array('status' => '1', 'error' => '', 'page' => $student_exam_page);
            echo json_encode($array);
        }
    }








    public function examstudent()
    {
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'required|trim|xss_clean');





        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting']     = $this->sch_setting_detail;
        if ($this->form_validation->run() == false) {
            $msg = array(
                'class_id'   => form_error('class_id'),
                'section_id' => form_error('section_id'),
            );
            $array = array('status' => 0, 'error' => $msg);
            echo json_encode($array);
        } else {








            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $data['class_id']   = $class_id;
            $data['section_id'] = $section_id;
            $data['exam_id']    = $this->input->post('exam_id');
            $exam_id            = $data['exam_id'];

            // When exam has subject groups assigned, only levels from those groups are allowed
            $this->load->model('examgroupsubjectgroup_model');
            $exam = $this->examgroup_model->getExamByID($exam_id);
            $exam_group_id = isset($exam->exam_group_id) ? $exam->exam_group_id : null;
            $allowed_levels = $this->examgroupsubjectgroup_model->getLevelsByExam($exam_id, $exam_group_id);
            if (!empty($allowed_levels)) {
                $allowed = false;
                foreach ($allowed_levels as $lev) {
                    if ((int) $lev->class_id === (int) $class_id && (int) $lev->section_id === (int) $section_id) {
                        $allowed = true;
                        break;
                    }
                }
                if (!$allowed) {
                    $msg = array(
                        'class_id'   => $this->lang->line('this_level_not_in_exam_subject_group') ?: 'This level is not in the subject group(s) assigned to this exam.',
                        'section_id' => '',
                    );
                    echo json_encode(array('status' => 0, 'error' => $msg));
                    return;
                }
            }

            $resultlist = $this->examstudent_model->searchExamStudents($data['class_id'], $data['section_id'], $data['exam_id']);








            $data['resultlist'] = $resultlist;
            $student_exam_page  = $this->load->view('admin/examgroup/_partialexamstudent', $data, true);
            $array              = array('status' => '1', 'error' => '', 'page' => $student_exam_page);
            echo json_encode($array);
        }
    }








    public function ajaxaddexam()
    {
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('exam', $this->lang->line('exam'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean');








        if ($this->input->post('exam_type') == "average_passing") {
            $this->form_validation->set_rules('passing_percentage', $this->lang->line('exam_passing_percentage'), 'required|trim|xss_clean');
        }








        if ($this->form_validation->run() == false) {
            $data = array(
                'exam'       => form_error('exam'),
                'session_id' => form_error('session_id'),
            );








            if ($this->input->post('exam_type') == "average_passing") {
                $data['passing_percentage'] = form_error('passing_percentage');
            }








            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {








            $exam_id    = $this->input->post('exam_id');
            $is_active  = $this->input->post('is_active');
            $is_publish = $this->input->post('is_publish');
            $exam_type  = $this->input->post('exam_type');








            if (isset($is_active)) {
                $is_active = 1;
            } else {
                $is_active = 0;
            }








            if (isset($is_publish)) {
                $is_publish = 1;
            } else {
                $is_publish = 0;
            }








            $postarray = array(
                'exam'             => $this->input->post('exam'),
                'exam_group_id'    => $this->input->post('exam_group_id'),
                'session_id'       => $this->input->post('session_id'),
                'is_active'        => $is_active,
                'is_publish'       => $is_publish,
                'description'      => $this->input->post('description'),
                'use_exam_roll_no' => $this->input->post('use_exam_roll_no'),
            );








            $passing_percentage = $this->input->post('passing_percentage');
            if (isset($passing_percentage)) {
                $postarray['passing_percentage'] = $passing_percentage;
            }








            if ($exam_id != 0) {
                $postarray['id'] = $exam_id;
            }








            $inserted_id = $this->examgroup_model->add_exam($postarray);
            $exam_data   = $this->examgroup_model->getExamByID($exam_id);
            if ($is_publish) {
                $exam_students = $this->examgroupstudent_model->searchExamStudentsByExam($exam_id);
                $student_exams = array('exam' => $exam_data, 'exam_result' => $exam_students);
                $s             = $this->mailsmsconf->mailsms('exam_result', $student_exams);
            }








            if ($exam_id != 0) {
                $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('update_message'));
            } else {
                $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'));
            }








            echo json_encode($array);
        }
    }








    public function getExamsByExamGroup()
    {
        $exam_group_id = $this->input->post('exam_group_id');
        $exams         = $this->examgroup_model->getExamByExamGroup($exam_group_id, true);








        $array = array('status' => '1', 'error' => '', 'result' => $exams);
        echo json_encode($array);
    }








    public function entrymarks()
    {
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('exam_group_class_batch_exam_subject_id', 'Subject', 'required|trim|xss_clean');








        if ($this->form_validation->run() == false) {
            $data = array(
                'exam_group_class_batch_exam_subject_id' => form_error('exam_group_class_batch_exam_subject_id'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {








            $exam_group_student_id = $this->input->post('exam_group_student_id');
            $insert_array          = array();
            $update_array          = array();
            if (!empty($exam_group_student_id)) {
                foreach ($exam_group_student_id as $exam_group_student_key => $exam_group_student_value) {
                    $attendance_post = $this->input->post('exam_group_student_attendance_' . $exam_group_student_value);
                    if (isset($attendance_post)) {
                        $attendance = $this->input->post('exam_group_student_attendance_' . $exam_group_student_value);
                    } else {
                        $attendance = "present";
                    }
                    $array = array(
                        'exam_group_class_batch_exam_subject_id' => $this->input->post('exam_group_class_batch_exam_subject_id'),
                        'exam_group_class_batch_exam_student_id' => $exam_group_student_value,
                        'attendence'                             => $attendance,
                        'get_marks'                              => $this->input->post('exam_group_student_mark_' . $exam_group_student_value),
                        'note'                                   => $this->input->post('exam_group_student_note_' . $exam_group_student_value),
                    );
                    $insert_array[] = $array;
                }
            }








            $this->examgroupstudent_model->add_result($insert_array);
            $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }








    public function getexam()
    {
        $examgroup_id = $this->input->post('examgroup_id');
        if (empty($examgroup_id) || !is_numeric($examgroup_id)) {
            $data['examList']   = array();
            $data['exam_page'] = '';
            echo json_encode($data);
            return;
        }
        $examList = $this->examgroup_model->getExamByExamGroup($examgroup_id);
        
        // Ensure sessions are in BS format (Nepali) and attach assigned section/program/class (levels) per exam
        if (!empty($examList)) {
            foreach ($examList as $exam) {
                if (isset($exam->session)) {
                    $exam->session = $this->customlib->convertSessionToBSIfNeeded($exam->session);
                }
                $exam->assigned_levels = $this->examstudent_model->getAssignedLevelsByExam($exam->id);
            }
        }
        
        $data['examList'] = $examList;
        $data['exam_page'] = $this->load->view('admin/examgroup/_partialexamList', $data, true);
        echo json_encode($data);
    }








    public function connectexams()
    {
        $examgroup_id         = $this->input->post('examgroup_id');
        $data['examList']     = $this->examgroup_model->getExamByExamGroupConnection($examgroup_id);
        $data['examgroup_id'] = $examgroup_id;
        $data['exam_page'] = $this->load->view('admin/examgroup/_partialexamListConnection', $data, true);
        echo json_encode($data);
    }








    public function getExamByID()
    {
        $exam_id = $this->input->post('exam_id');
        $result  = $this->examgroup_model->getExamByID($exam_id);
        if (!empty($result)) {
            $result->date_from = $this->customlib->dateformat($result->date_from);
            $result->date_to   = $this->customlib->dateformat($result->date_to);
        }
        $data['exam'] = $result;
        echo json_encode($data);
    }

    /**
     * Get levels (section/program/class) allowed for assigning students to this exam.
     * Only levels linked to the exam's assigned subject group(s) are returned.
     * Used by Assign Student modal to restrict dropdowns.
     */
    public function getLevelsForExam()
    {
        header('Content-Type: application/json; charset=utf-8');
        $out = function ($status, $levels, $sections, $message) {
            echo json_encode(array('status' => $status, 'levels' => $levels, 'sections' => $sections, 'message' => $message));
        };
        try {
            $exam_id = $this->input->post('exam_id') ?: $this->input->get('exam_id');
            if (empty($exam_id)) {
                $out(0, array(), array(), 'Invalid exam.');
                return;
            }
            $this->load->model('examgroupsubjectgroup_model');
            $exam = $this->examgroup_model->getExamByID($exam_id);
            $exam_group_id = (isset($exam->exam_group_id) && $exam->exam_group_id !== null) ? $exam->exam_group_id : null;
            $levels = $this->examgroupsubjectgroup_model->getLevelsByExam($exam_id, $exam_group_id);
            $levels_arr = array();
            $sections_seen = array();
            $sections = array();
            foreach ($levels as $lev) {
                $levels_arr[] = array(
                    'section_id'   => (int) $lev->section_id,
                    'section'      => isset($lev->section) ? $lev->section : '',
                    'program_id'   => (int) (isset($lev->program_id) ? $lev->program_id : 0),
                    'program_name' => isset($lev->program_name) && $lev->program_name ? $lev->program_name : '',
                    'class_id'     => (int) $lev->class_id,
                    'class'        => isset($lev->class) ? $lev->class : '',
                );
                if (!isset($sections_seen[$lev->section_id])) {
                    $sections_seen[$lev->section_id] = true;
                    $sections[] = array('id' => (int) $lev->section_id, 'section' => isset($lev->section) ? $lev->section : '');
                }
            }
            if (empty($levels_arr)) {
                $out(0, array(), array(), $this->lang->line('assign_subject_groups_first') ?: 'Please assign subject groups to this exam first, then you can link students (levels).');
                return;
            }
            $out(1, $levels_arr, $sections, '');
        } catch (Exception $e) {
            $detail = $e->getMessage() . ' (in ' . basename($e->getFile()) . ' line ' . $e->getLine() . ')';
            log_message('error', 'getLevelsForExam: ' . $detail);
            $out(0, array(), array(), 'Error loading levels: ' . $detail);
        }
    }

    public function getexamSubjects()
    {
        try {
            return $this->_getexamSubjects();
        } catch (Exception $e) {
            $msg = 'Server error loading subjects: ' . $e->getMessage();
            log_message('error', 'getexamSubjects: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $html = '<div class="alert alert-danger"><strong>Error loading subjects</strong><br>' . htmlspecialchars($msg) . '</div>';
            echo json_encode(array(
                'status' => 0,
                'error' => $msg,
                'message' => $msg,
                'subject_page' => $html
            ));
        }
    }

    /**
     * Output JSON for Exam Subject modal when there are no subjects to show (no fallback to full list).
     * @param int $exam_id
     * @param array $exam_subjects existing exam subjects for count
     * @param object|null $examgroupDetail for view (required for _partialexamSubjects)
     * @param string|null $custom_message optional override message
     */
    private function _returnEmptyExamSubjects($exam_id, $exam_subjects = array(), $examgroupDetail = null, $custom_message = null)
    {
        $msg = $custom_message !== null
            ? $custom_message
            : 'No subjects found. Assign subject groups to this exam first (use Assign Subject Group for this exam).';
        $data = array(
            'batch_subjects' => array(),
            'batch_subject_dropdown' => '<option value="">' . htmlspecialchars($msg) . '</option>',
            'exam_id' => $exam_id,
            'exam_subjects_count' => is_array($exam_subjects) ? count($exam_subjects) : 0,
            'exam_subjects' => is_array($exam_subjects) ? $exam_subjects : array(),
            'examgroupDetail' => $examgroupDetail,
        );
        if ($examgroupDetail !== null) {
            $data['subject_page'] = $this->load->view('admin/examgroup/_partialexamSubjects', $data, true);
        } else {
            $data['subject_page'] = '<div class="alert alert-warning">' . htmlspecialchars($msg) . '</div>';
        }
        echo json_encode($data);
    }

    private function _getexamSubjects()
    {
        $this->db->reset_query();
        $exam_id = $this->input->post('exam_id');
        $class_batch_id = $this->input->post('class_batch_id');
        $exam_group_ids = $this->input->post('exam_group_id');

        // Require a valid exam so we never accidentally show a global list
        if (empty($exam_id) && $exam_id !== '0') {
            $this->_returnEmptyExamSubjects($exam_id, array(), null, 'No exam selected.');
            return;
        }
        $exam_id = (int) $exam_id;
        if ($exam_id <= 0) {
            $this->_returnEmptyExamSubjects(0, array(), null, 'Invalid exam.');
            return;
        }

        $data['examgroupDetail'] = $this->examgroup_model->getExamByID($exam_id);
        if (empty($data['examgroupDetail'])) {
            $this->_returnEmptyExamSubjects($exam_id, array(), null, 'Exam not found.');
            return;
        }
        $data['exam_subjects'] = $this->batchsubject_model->getExamSubjects($exam_id);
        $exam_group_id = isset($data['examgroupDetail']->exam_group_id) ? $data['examgroupDetail']->exam_group_id : null;

        // Exam Subject list = ONLY from subject groups assigned to THIS exam (per-exam).
        // No exam-group (global) fallback: so subjects show only when you assign subject groups to this exam.
        $subject_ids = null;
        $this->load->model('examgroupsubjectgroup_model');
        if (!$this->examgroupsubjectgroup_model->examSubjectGroupsTableExists()) {
            // Table missing: prompt to run application/sql/add_exam_subject_groups.sql
            $this->_returnEmptyExamSubjects(
                $exam_id,
                $data['exam_subjects'],
                $data['examgroupDetail'],
                'Exam subject groups are not set up. Please run application/sql/add_exam_subject_groups.sql and assign subject groups to this exam.'
            );
            return;
        }
        $has_per_exam_groups = $this->examgroupsubjectgroup_model->examHasSubjectGroupsAssigned($exam_id);
        if ($has_per_exam_groups) {
            try {
                $subject_ids = $this->examgroupsubjectgroup_model->getSubjectIdsByExam($exam_id);
            } catch (Exception $e) {
                $subject_ids = array();
            }
        }
        if ($subject_ids === null || empty($subject_ids)) {
            $this->_returnEmptyExamSubjects($exam_id, $data['exam_subjects'], $data['examgroupDetail'], null);
            return;
        }

        // Normalize to integers
        $subject_ids = array_map('intval', $subject_ids);
        $subject_ids = array_filter($subject_ids);
        if (empty($subject_ids)) {
            $this->_returnEmptyExamSubjects($exam_id, $data['exam_subjects'], $data['examgroupDetail'], null);
            return;
        }

        // Build list only from these subject IDs (never from full subject table)
        $this->db->reset_query();
        $this->db->select('*')->from('subjects');
        $this->db->where_in('id', $subject_ids);
        $this->db->order_by('id');
        $all_subjects = $this->db->get()->result_array();
        $data['batch_subjects'] = array();
        foreach ($all_subjects as $subject) {
            if (isset($subject['is_academic']) && $subject['is_academic'] == 0) {
                continue;
            }
            $data['batch_subjects'][] = $subject;
        }

        if (empty($data['batch_subjects'])) {
            $data['batch_subject_dropdown'] = '<option value="">No academic subjects in assigned subject groups for current session.</option>';
            $data['exam_id'] = $exam_id;
            $data['exam_subjects_count'] = count($data['exam_subjects']);
            $data['subject_page'] = $this->load->view('admin/examgroup/_partialexamSubjects', $data, true);
            echo json_encode(array(
                'status' => 0,
                'error' => 'No academic subjects found in assigned subject groups!',
                'message' => 'No academic subjects found in assigned subject groups!',
                'subject_page' => $data['subject_page'],
                'batch_subject_dropdown' => $data['batch_subject_dropdown'],
                'exam_id' => $data['exam_id'],
                'exam_subjects_count' => $data['exam_subjects_count'],
            ));
            return;
        }
        

        $dropdown_html = '<option value="">Select Subject</option>';
        foreach ($data['batch_subjects'] as $subject) {
            $dropdown_html .= '<option value="'.$subject['id'].'">'.$subject['name'].' ('.$subject['code'].')</option>';
        }
        $data['batch_subject_dropdown'] = $dropdown_html;
        
        $data['exam_id'] = $exam_id;
        $data['exam_subjects_count'] = count($data['exam_subjects']);
        
        $data['batch_subject_dropdown'] = $this->load->view('admin/examgroup/_partialexamSubjectDropdown', $data, true);
        $data['subject_page'] = $this->load->view('admin/examgroup/_partialexamSubjects', $data, true);
        
        echo json_encode($data);
    }





    public function getSubjectByExam()
    {
        $data                    = array();
        $id                      = $this->input->post('recordid');
        $data['exam_id']         = $id;
        $data['examgroupDetail'] = $this->examgroup_model->getExamByID($id);
        $data['exam_subjects']   = $this->batchsubject_model->getExamSubjects($id);
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $session                 = $this->session_model->get();
        $data['sessionlist']     = $session;
        $data['current_session'] = $this->sch_current_session;
        $data['subject_page']    = $this->load->view('admin/examgroup/_getSubjectByExam', $data, true);
        echo json_encode($data);
    }








    public function getTeacherRemarkByExam()
    {
        $data                      = array();
        $id                        = $this->input->post('recordid');
        $data['examgroupDetail']   = $this->examgroup_model->getExamByID($id);
        $data['examgroupStudents'] = $this->examgroupstudent_model->searchExamStudentsByExam($id);
        $data['sch_setting']       = $this->sch_setting_detail;
        $data['subject_page']      = $this->load->view('admin/examgroup/_getTeacherRemarkByExam', $data, true);
        echo json_encode($data);
    }








    public function addexamsubject()
    {
        $student_id = '';
        $this->form_validation->set_rules('examgroup_id', $this->lang->line('exam_group'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_group_class_batch_exam_id', $this->lang->line('exam_id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('rows[]', $this->lang->line('subject'), 'trim|required|xss_clean');
        $rows = $this->input->post('rows');
        if (isset($rows) && !empty($rows)) {
            foreach ($rows as $row_key => $row_value) {
                if (trim($this->input->post('subject_' . $row_value))    == "" ||
                    trim($this->input->post('date_from_' . $row_value))  == "" ||
                    trim($this->input->post('time_from' . $row_value))   == "" ||
                    trim($this->input->post('duration' . $row_value))    == "" ||
                    trim($this->input->post('credit_hours' . $row_value))    == "" ||
                    trim($this->input->post('room_no_' . $row_value))    == "" ||
                    trim($this->input->post('max_marks_' . $row_value))  == "" ||
                    trim($this->input->post('min_marks_' . $row_value))  == "") {
                    $this->form_validation->set_rules('parameter', 'parameter', 'trim|required|xss_clean', array('required' =>$this->lang->line('fields_values_required')));
                }








             
                 if (trim($this->input->post('max_marks_' . $row_value)) <= 0 ) {
                    $this->form_validation->set_rules('max_marks', $this->lang->line('max_marks'), 'trim|required|xss_clean', array('required' =>$this->lang->line('invalid_max_marks')));
                }
                if (trim($this->input->post('min_marks_' . $row_value)) <= 0 ) {
                    $this->form_validation->set_rules('min_marks', $this->lang->line('min_marks'), 'trim|required|xss_clean', array('required' =>$this->lang->line('invalid_min_marks')));
                }








            }
        }








        if ($this->form_validation->run() == false) {








            $msg = array(
                'max_marks'                      => form_error('max_marks'),
                'min_marks'                      => form_error('min_marks'),
                'parameter'                      => form_error('parameter'),
                'examgroup_id'                   => form_error('examgroup_id'),
                'exam_group_class_batch_exam_id' => form_error('exam_group_class_batch_exam_id'),
                'rows'                           => form_error('rows[]'),
            );

            $array = array('status' => '0', 'error' => $msg, 'message' => '');
        } else {
            $insert_array  = array();
            $update_array  = array();
            $subject_array = array();
            $not_be_del = array();
            $rows = $this->input->post('rows');
            foreach ($rows as $row_key => $row_value) {
                $update_id = $this->input->post('prev_row[' . $row_value . ']');
                if ($update_id == 0) {
                    if ($this->input->post('exam_group_class_batch_exam_id') != "" && $this->input->post('subject_' . $row_value) != "" && $this->input->post('date_from_' . $row_value) != "" && $this->input->post('time_from' . $row_value) != "" && $this->input->post('duration' . $row_value) != "" && $this->input->post('max_marks_' . $row_value) != "" && $this->input->post('min_marks_' . $row_value) != "") {

                        $insert_array[] = array(
                            'exam_group_class_batch_exams_id' => $this->input->post('exam_group_class_batch_exam_id'),
                            'subject_id'                      => $this->input->post('subject_' . $row_value),
                            'credit_hours'                    => $this->input->post('credit_hours' . $row_value),
                            'date_from'                       => $this->input->post('date_from_' . $row_value),
                            'time_from'                       => $this->input->post('time_from' . $row_value),
                            'duration'                        => $this->input->post('duration' . $row_value),
                            'room_no'                         => $this->input->post('room_no_' . $row_value),
                            'max_marks'                       => $this->input->post('max_marks_' . $row_value),
                            'min_marks'                       => $this->input->post('min_marks_' . $row_value),
                        );
                    }
                } else {
                    $not_be_del[]   = $update_id;
                    $update_array[] = array(
                        'id'                              => $update_id,
                        'credit_hours'                    => $this->input->post('credit_hours' . $row_value),
                        'exam_group_class_batch_exams_id' => $this->input->post('exam_group_class_batch_exam_id'),
                        'subject_id'                      => $this->input->post('subject_' . $row_value),
                        'date_from'                       => $this->input->post('date_from_' . $row_value),
                        'time_from'                       => $this->input->post('time_from' . $row_value),
                        'duration'                        => $this->input->post('duration' . $row_value),
                        'room_no'                         => $this->input->post('room_no_' . $row_value),
                        'max_marks'                       => $this->input->post('max_marks_' . $row_value),
                        'min_marks'                       => $this->input->post('min_marks_' . $row_value),
                    );
                }
            }

            $this->examsubject_model->add($insert_array, $update_array, $not_be_del, $this->input->post('exam_group_class_batch_exam_id'));

            $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'));
        }

        echo json_encode($array);
    }


    public function assign($id)
    {
        if (!$this->rbac->hasPrivilege('fees_group_assign', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Batch');
        $this->session->set_userdata('sub_menu', 'examgroup/index');
        $data['id']        = $id;
        $data['title']     = 'student fees';
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $examgroup         = $this->examgroup_model->getExamGroupDetailByID($id);
        $data['examgroup']   = $examgroup;
        $session_result      = $this->session_model->get();
        $data['sessionlist'] = $session_result;

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $data['class_id']     = $this->input->post('class_id');
            $data['section_id']   = $this->input->post('section_id');
            $data['session_id']   = $this->input->post('session_id');
            $data['examgroup_id'] = $this->input->post('examgroup_id');

            $resultlist = $this->examgroupstudent_model->searchExamGroupStudents($data['examgroup_id'], $data['class_id'], $data['section_id'], $data['session_id']);
            $data['resultlist'] = $resultlist;
        }
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/assign', $data);
        $this->load->view('layout/footer', $data);
    }


    public function addstudent()
    {
        $this->form_validation->set_rules('exam_group', $this->lang->line('exam') . " " . $this->lang->line('group'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'exam_group' => form_error('exam_group'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $array_insert = array();
            $array_delete = array();
            $exam_group   = $this->input->post('exam_group');
            $students_id  = $this->input->post('students_id');
            $all_students = $this->input->post('all_students');
            $students     = array();
            if (!isset($students_id)) {
                $students_id = array();
            }
            if (!empty($all_students)) {
                foreach ($all_students as $all_students_key => $all_students_value) {
                    if (in_array($all_students_value, $students_id)) {
                        $array_insert[] = array(
                            'exam_group_id'      => $exam_group,
                            'student_id'         => $all_students_value,
                            'student_session_id' => $all_students_value,
                        );
                    } else {
                        $array_delete[] = $all_students_value;
                    }
                }
            }
            $this->examgroupstudent_model->add($array_insert, $array_delete, $exam_group);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }


    public function assignsubjectgroups($id)
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/examgroup');
        $data['id'] = $id;
        $data['title'] = 'Assign Subject Groups';
        $examgroup = $this->examgroup_model->getExamGroupDetailByID($id);
        $data['examgroup'] = $examgroup;
        
        // Load the model
        $this->load->model('examgroupsubjectgroup_model');
        
        // Get all subject groups with assignment status
        $data['subject_groups'] = $this->examgroupsubjectgroup_model->getAllSubjectGroupsWithAssignment($id);
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/examgroup/assignsubjectgroups', $data);
        $this->load->view('layout/footer', $data);
    }


    public function addsubjectgroups()
    {
        $this->form_validation->set_rules('exam_group', $this->lang->line('exam') . " " . $this->lang->line('group'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'exam_group' => form_error('exam_group'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $this->load->model('examgroupsubjectgroup_model');
            
            $array_insert = array();
            $array_delete = array();
            $exam_group = $this->input->post('exam_group');
            $subject_groups_id = $this->input->post('subject_groups_id');
            $all_subject_groups = $this->input->post('all_subject_groups');
            
            if (!isset($subject_groups_id)) {
                $subject_groups_id = array();
            }
            
            if (!empty($all_subject_groups)) {
                foreach ($all_subject_groups as $all_subject_groups_key => $all_subject_groups_value) {
                    if (in_array($all_subject_groups_value, $subject_groups_id)) {
                        $array_insert[] = array(
                            'exam_group_id' => $exam_group,
                            'subject_group_id' => $all_subject_groups_value,
                        );
                    } else {
                        $array_delete[] = $all_subject_groups_value;
                    }
                }
            }
            
            $result = $this->examgroupsubjectgroup_model->add($array_insert, $array_delete, $exam_group);
            
            if ($result) {
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            } else {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('something_went_wrong'));
            }
            echo json_encode($array);
        }
    }

    /**
     * Return HTML for per-exam "Assign Subject Group" modal body (AJAX).
     * POST: exam_id
     */
    public function assignSubjectGroupsExam()
    {
        $exam_id = $this->input->post('exam_id');
        if (empty($exam_id) || !is_numeric($exam_id)) {
            echo json_encode(array('status' => 0, 'error' => 'Invalid exam', 'page' => '<p class="text-danger">Invalid exam.</p>'));
            return;
        }
        $exam = $this->examgroup_model->getExamByID($exam_id);
        if (empty($exam)) {
            echo json_encode(array('status' => 0, 'error' => 'Exam not found', 'page' => '<p class="text-danger">Exam not found.</p>'));
            return;
        }
        $this->load->model('examgroupsubjectgroup_model');
        $data['exam'] = $exam;
        $data['subject_groups'] = $this->examgroupsubjectgroup_model->getAllSubjectGroupsWithAssignmentForExam($exam_id);
        $data['exam_id'] = $exam_id;
        $html = $this->load->view('admin/examgroup/_partialassignsubjectgroups_exam', $data, true);
        echo json_encode(array('status' => 1, 'page' => $html, 'exam_name' => $exam->exam));
    }

    /**
     * Save per-exam subject group assignment (AJAX).
     * POST: exam_id, subject_groups_id[], all_subject_groups[]
     */
    public function addsubjectgroupsforexam()
    {
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'required|trim|integer');
        if ($this->form_validation->run() == false) {
            echo json_encode(array('status' => 'fail', 'error' => array('exam_id' => form_error('exam_id')), 'message' => ''));
            return;
        }
        $this->load->model('examgroupsubjectgroup_model');
        $exam_id = (int) $this->input->post('exam_id');
        $subject_groups_id = $this->input->post('subject_groups_id');
        $all_subject_groups = $this->input->post('all_subject_groups');
        if (!is_array($subject_groups_id)) {
            $subject_groups_id = array();
        }
        if (!is_array($all_subject_groups)) {
            $all_subject_groups = array();
        }
        $array_insert = array();
        $array_delete = array();
        foreach ($all_subject_groups as $sg_id) {
            if (in_array($sg_id, $subject_groups_id)) {
                $array_insert[] = array(
                    'exam_group_class_batch_exam_id' => $exam_id,
                    'subject_group_id' => $sg_id,
                );
            } else {
                $array_delete[] = $sg_id;
            }
        }
        $result = $this->examgroupsubjectgroup_model->addForExam($array_insert, $array_delete, $exam_id);
        if ($result) {
            echo json_encode(array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message')));
        } else {
            echo json_encode(array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('something_went_wrong')));
        }
    }

    public function ajaxConnectForm()
    {
        if (isset($_POST['action'])) {
            if ($this->input->post('action') == "reset") {
                $exam_group_id = $this->input->post('examgroup_id');
                $this->examgroup_model->deleteExamGroupConnection($exam_group_id);
                $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('update_message'));
                echo json_encode($array);
            } elseif ($this->input->post('action') == "save") {
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('examgroup_id', $this->lang->line('exam_group'), 'required|trim|xss_clean');
                if ($this->form_validation->run() == false) {
                    $data = array(
                        'examgroup_id' => form_error('examgroup_id'),
                    );
                    $array = array('status' => 0, 'error' => $data);
                    echo json_encode($array);
                } else {
                    $array      = array();
                    $exam_array = $this->input->post('exam[]');
                    $exam_percentage_total=0;
                    if (!empty($exam_array)) {
                        foreach ( $exam_array as $e_key => $e_value) {
                            $exam_percentage_total+= $this->input->post('exam_' . $e_value);
                        }
                    }

                    if (!empty($exam_array)) {
                        if (count($exam_array) <= 1) {
                            $array = array('status' => 0, 'error' => '', 'message' => $this->lang->line('please_select_atleast_two_or_more_exams'));
                        }elseif($exam_percentage_total != 100){
                            $array = array('status' => 0, 'message' => $this->lang->line('exam_weightage_must_be_equal_to'));
                        } else {
                            $exam_group = $this->examgroup_model->verifyExamConnection($exam_array);
                            if ($exam_group['no_record']) {
                                if (count($exam_group['exam_subject_array']) != count($exam_array)) {
                                    $array          = array('status' => 0, 'error' => '', 'message' => $this->lang->line('please_check_exam_subjects'));
                                    $insert_success = 0;
                                } else {
                                    reset($exam_group['exam_subject_array']);
                                    $result = key($exam_group['exam_subject_array']);
                                    $insert_success = 1;
                                    foreach ($exam_group['exam_subject_array'] as $exam_subject_key => $exam_subject_value) {
                                        $compair_result = $this->compare_multi_Arrays($exam_group['exam_subject_array'][$result], $exam_group['exam_subject_array'][$exam_subject_key]);

                                        if ($compair_result) {

                                            if (!empty($compair_result['more']) || !empty($compair_result['less']) || !empty($compair_result['diff'])) {
                                                $array          = array('status' => 0, 'error' => '', 'message' => $this->lang->line('please_check_exam_subjects'));
                                                $insert_success = 0;
                                                break;
                                            }
                                        } else {
                                            $array          = array('status' => 0, 'error' => '', 'message' => $this->lang->line('please_check_exam_subjects'));
                                            $insert_success = 0;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $array          = array('status' => 0, 'error' => '', 'message' => $this->lang->line('exams_subject_may_be_empty_please_check_exam_subjects'));
                                $insert_success = 0;
                            }

                            if ($insert_success) {
                                $insert_array  = array();
                                $exam_group_id = $this->input->post('examgroup_id');
                                if (!empty($exam_array)) {
                                    foreach ($exam_array as $exam_key => $exam_value) {
                                        $insert_array[] = array(
                                            'exam_group_id'                   => $exam_group_id,
                                            'exam_group_class_batch_exams_id' => $exam_value,
                                            'exam_weightage'                  => $this->input->post('exam_' . $exam_value),
                                        );
                                    }
                                }
                                $this->examgroup_model->connectExam($insert_array, $exam_group_id);
                                $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('exam_connected_successfully'));
                            }
                        }
                    } else {
                        $array = array('status' => 0, 'error' => '', 'message' => $this->lang->line('no_exams_selected'));
                    }
                    echo json_encode($array);
                }
            }
        }
    }


    public function compare_multi_Arrays($array1, $array2)
    {
        if (!empty($array1) && !empty($array2)) {
            $result = array("more" => array(), "less" => array(), "diff" => array());
            foreach ($array1 as $k => $v) {
                if (is_array($v) && isset($array2[$k]) && is_array($array2[$k])) {
                    $sub_result = compare_multi_Arrays($v, $array2[$k]);
                    foreach (array_keys($sub_result) as $key) {
                        if (!empty($sub_result[$key])) {
                            $result[$key] = array_merge_recursive($result[$key], array($k => $sub_result[$key]));
                        }
                    }
                } else {
                    if (isset($array2[$k])) {
                        if ($v !== $array2[$k]) {
                            $result["diff"][$k] = array("from" => $v, "to" => $array2[$k]);
                        }
                    } else {
                        $result["more"][$k] = $v;
                    }
                }
            }
            foreach ($array2 as $k => $v) {
                if (!isset($array1[$k])) {
                    $result["less"][$k] = $v;
                }
            }
            return $result;
        }
        return false;
    }


    public function getExamGroupByClassSection()
    {
        $exam_group = array();
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $session_id = $this->input->post('session_id');
        $exam_group = $this->examgroup_model->getExamGroupByClassSection($class_id, $section_id, $session_id);
        echo json_encode(array('status' => 1, 'exam_group' => $exam_group));
    }


    public function entrystudents()
    {
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('exam_group_class_batch_exam_id', $this->lang->line('exam'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'exam_group_class_batch_exam_id' => form_error('exam_group_class_batch_exam_id'),
            );

            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {
            $check_alreay_inserted_students = array();
            $state                          = 1;
            $exam_group_class_batch_exam_id = $this->input->post('exam_group_class_batch_exam_id');
            $student_session                = $this->input->post('student_session_id');
            $all_students                   = $this->input->post('all_students');
            $insert_array                   = array();
            if (isset($student_session) && !empty($student_session)) {
                foreach ($student_session as $student_key => $student_value) {
                    $check_alreay_inserted_students[] = $this->input->post('student_' . $student_value);
                    $insert_array[]                   = array(
                        'exam_group_class_batch_exam_id' => $exam_group_class_batch_exam_id,
                        'student_id'                     => $this->input->post('student_' . $student_value),
                        'student_session_id'             => $student_value,
                    );
                }
            }

            $this->examstudent_model->add_student($insert_array, $exam_group_class_batch_exam_id, $all_students);
            $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'));

            echo json_encode($array);
        }
    }



    public function saveexamremark()
    {
        $students = $this->input->post('exam_group_class_batch_exam_student');
        if (!empty($students)) {
            $batch_update_array = array();
            foreach ($students as $student_key => $student_value) {
                $update_array = array(
                    'id'             => $student_value,
                    'teacher_remark' => $this->input->post('remark_' . $student_value),
                );
                $batch_update_array[] = $update_array;
            }
            $this->examgroupstudent_model->updateExamStudent($batch_update_array);
        }
        $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'));
        echo json_encode($array);
    }


    public function getAssignedStudents()
    {
        if (!$this->rbac->hasPrivilege('exam_assign_view_student', 'can_view')) {
            access_denied();
        }

        $exam_id = $this->input->post('exam_id');
        $exam = $this->examgroup_model->getExamByID($exam_id);
       
        if (empty($exam)) {
            $array = array('status' => 0, 'message' => $this->lang->line('invalid_exam'));
            echo json_encode($array);
            return;
        }
        $data['exam'] = $exam;
        $data['students'] = $this->examgroupstudent_model->getExamStudents($exam_id);
       
        $page = $this->load->view('admin/examgroup/_getAssignedStudents', $data, true);
        $array = array('status' => 1, 'page' => $page);
        echo json_encode($array);
    }
}