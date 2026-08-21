<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Member extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            access_denied();
        }
    
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/index');
        $data['title']      = 'Member';
        $data['title_list'] = 'Members';
        $superadmin_visible = $this->customlib->superadmin_visible();
    
        $isPost     = (strtoupper((string) $this->input->method()) === 'POST');
        $button     = $this->input->post('search');
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');
        $class_id   = $this->input->post('class_id');
        $subject_group_id = (int) $this->input->post('subject_group_id');
        $borrower_type = $this->input->post('borrower_type');
        $role_id = (int) $this->input->post('role_id');

        // Persist the member search so returning from the issue page (Back button = GET) restores
        // the Faculty/Program/Academic Level/Subject Group filters and the matching student list.
        if ($isPost && !empty($button) && $button == 'search_filter') {
            $this->session->set_userdata('library_member_search_filters', array(
                'borrower_type'    => ($borrower_type === 'staff') ? 'staff' : 'student',
                'section_id'       => $section_id,
                'program_id'       => $program_id,
                'class_id'         => $class_id,
                'subject_group_id' => $subject_group_id,
                'role_id'          => $role_id,
            ));
        } elseif (!$isPost) {
            $saved = $this->session->userdata('library_member_search_filters');
            if (is_array($saved) && !empty($saved)) {
                $button           = 'search_filter';
                $borrower_type    = isset($saved['borrower_type']) ? $saved['borrower_type'] : $borrower_type;
                $section_id       = isset($saved['section_id']) ? $saved['section_id'] : null;
                $program_id       = isset($saved['program_id']) ? $saved['program_id'] : null;
                $class_id         = isset($saved['class_id']) ? $saved['class_id'] : null;
                $subject_group_id = isset($saved['subject_group_id']) ? (int) $saved['subject_group_id'] : 0;
                $role_id          = isset($saved['role_id']) ? (int) $saved['role_id'] : 0;
            }
        }

        $borrower_type = ($borrower_type === 'staff') ? 'staff' : 'student';
    
        $memberList = $this->librarymember_model->getAllMembers();
    
        if (!empty($button) && $button == 'search_filter') {
            if ($borrower_type === 'staff') {
                $memberList = $this->librarymember_model->getLibraryStaffMembersByRole($role_id);
            } else {
                if (!empty($section_id) || !empty($program_id) || !empty($class_id) || $subject_group_id > 0) {
                    $memberList = $this->librarymember_model->getBySectionProgramClass(
                        $section_id,
                        $program_id,
                        $class_id,
                        $subject_group_id > 0 ? $subject_group_id : null
                    );
                } else {
                    $memberList = array_values(array_filter($this->librarymember_model->getAllMembers(), function ($member) {
                        return isset($member['member_type']) && strtolower(trim($member['member_type'])) === 'student';
                    }));
                }
            }
        }
    
        if ($superadmin_visible == 'disabled') {
            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);
    
            if ($staffrole->id != 7) {
                $result = [];
                foreach ($memberList as $member) {
                    if ($member['member_type'] != "student") {
                        $getrole = $this->staff_model->getAll($member['staff_id']);
                        if ($getrole['role_id'] != 7) {
                            $result[] = $member;
                        }
                    } else {
                        $result[] = $member;
                    }
                }
            } else {
                $result = $memberList;
            }
        } else {
            $result = $memberList;
        }
    
        $data['memberList']  = $result;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['library_student_member_list_mode'] = $this->librarymember_model->getLibraryStudentMemberListMode();
        $data['borrower_type'] = $borrower_type;
        $data['role_id'] = $role_id;
        $data['subject_group_id'] = $subject_group_id;
        $data['section_id'] = ($section_id !== null) ? $section_id : '';
        $data['program_id'] = ($program_id !== null) ? $program_id : '';
        $data['class_id']   = ($class_id !== null) ? $class_id : '';
        $data['roles_list'] = $this->role_model->get();
        $data['outstanding_issues'] = $this->bookissue_model->getOutstandingIssues();
    
        $data['total_books'] = $this->book_model->getTotalBooksCount();
        $data['total_issues'] = $this->bookissue_model->getTotalIssuesCount();
        $data['today_issues'] = $this->bookissue_model->getTodayIssuesCount();
    
        $this->load->view('layout/header');
        $this->load->view('admin/librarian/index', $data);
        $this->load->view('layout/footer');
    }

    /**
     * AJAX: subject groups for library member search (faculty + program + academic level).
     */
    public function getSubjectGroupsByCriteria()
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array()));
            return;
        }

        $section_id = (int) $this->input->post('section_id');
        $program_id = (int) $this->input->post('program_id');
        $class_id   = (int) $this->input->post('class_id');

        $groups = $this->subjectgroup_model->getGroupsBySectionProgramClass($section_id, $program_id, $class_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($groups));
    }

    public function issue($id)
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            access_denied();
        }

        $id = (int) $id;
        if ($id < 1) {
            $txt = $this->lang->line('library_issue_requires_member');
            if ($txt === false) {
                $txt = 'Add this person as a library member first (Library → Member → Student or Teacher), then issue books.';
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">' . $txt . '</div>');
            redirect('admin/member');
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/index');
        $data['title']        = 'Member';
        $data['title_list']   = 'Members';
        $memberList           = $this->librarymember_model->getByMemberID($id);
        if (empty($memberList)) {
            $txt = $this->lang->line('library_member_not_found');
            if ($txt === false) {
                $txt = 'Library member not found. Add the member from Library → Member first.';
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">' . $txt . '</div>');
            redirect('admin/member');
        }
        $data['memberList']   = $memberList;
        $data['student_member_disabled'] = $this->librarymember_model->isStudentMemberAccountDisabled($memberList);
        $data['member_barcode_url'] = '';
        if (!empty($memberList->member_type) && strtolower($memberList->member_type) === 'student'
            && !empty($this->sch_setting_detail->student_barcode)
            && !empty($memberList->admission_no)) {
            $barcode_rel = $this->customlib->ensure_student_barcode($memberList->admission_no);
            if ($barcode_rel !== '') {
                $data['member_barcode_url'] = $this->media_storage->getImageURL($barcode_rel);
            }
        }
        $issued_books         = $this->bookissue_model->getMemberBooks($id);
        $data['issued_books'] = $this->bookissue_model->enrich_issued_books($issued_books);
        $data['library_settings'] = $this->bookissue_model->get_library_settings();
        $bookList             = $this->book_model->get();
        
        $this->form_validation->set_rules('issue_date', $this->lang->line('issue_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('return_date', $this->lang->line('due_return_date'), 'trim|required|xss_clean|callback_validate_due_after_issue');
        $this->form_validation->set_rules('book_id', $this->lang->line('book'), array('required', array('check_exists', array($this->bookissue_model, 'valid_check_exists')),
        )
        );
        if (!empty($data['student_member_disabled']) && $this->input->server('REQUEST_METHOD') === 'POST' && $this->input->post('book_id')) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">This student is disabled. New book issues are blocked; you can still return outstanding books.</div>');
            redirect('admin/member/issue/' . $id);
        }
        if ($this->form_validation->run() == false) {

        } else {
            $member_id = $this->input->post('member_id');
            $issue_norm = $this->customlib->dateFormatToYYYYMMDD($this->input->post('issue_date'));
            $due_norm   = $this->customlib->dateFormatToYYYYMMDD($this->input->post('return_date'));
            $data      = array(
                'book_id'        => $this->input->post('book_id'),
                'duereturn_date' => $due_norm !== null ? $due_norm : $this->input->post('return_date'),
                'issue_date'     => $issue_norm !== null ? $issue_norm : $this->input->post('issue_date'),
                'member_id'      => $this->input->post('member_id'),
            );
            $this->bookissue_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/member/issue/' . $member_id);
        }
       
        
        $data['bookList']     = $bookList;
        
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header');
        $this->load->view('admin/librarian/issue', $data);
        $this->load->view('layout/footer');
    }

    /**
     * Library issue form: due return date must not be before issue date (BS/AD normalized to Y-m-d).
     */
    public function validate_due_after_issue($return_date)
    {
        $issue_raw = $this->input->post('issue_date');
        if ($return_date === '' || $issue_raw === '') {
            return true;
        }
        $issue = $this->customlib->dateFormatToYYYYMMDD($issue_raw);
        $due   = $this->customlib->dateFormatToYYYYMMDD($return_date);
        if ($issue === null || $due === null || $issue === '' || $due === '') {
            return true;
        }
        if (strcmp($due, $issue) < 0) {
            $msg = $this->lang->line('due_date_must_be_after_issue_date');
            if ($msg === false) {
                $msg = 'Due date cannot be earlier than the issue date.';
            }
            $this->form_validation->set_message('validate_due_after_issue', $msg);
            return false;
        }
        return true;
    }

    public function bookreturn()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('member_id', $this->lang->line('member_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('return_date'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'id'        => form_error('id'),
                'member_id' => form_error('member_id'),
                'date'      => form_error('date'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $id        = (int) $this->input->post('id');
            $member_id = (int) $this->input->post('member_id');
            $date      = $this->input->post('date');
            $issue     = $this->bookissue_model->get($id);
            if (empty($issue) || (int) $issue['member_id'] !== $member_id) {
                echo json_encode(array('status' => 'fail', 'error' => array('id' => 'Invalid issue record.')));
                return;
            }
            $return_norm = $this->customlib->dateFormatToYYYYMMDD($date);
            $return_store = $return_norm !== null ? $return_norm : $date;
            $fine_info = $this->bookissue_model->calculate_fine_for_issue($issue, $return_store);
            $fine_paid = $this->input->post('fine_paid');
            $fine_paid_amount = ($fine_paid !== null && $fine_paid !== '') ? (float) $fine_paid : (float) $fine_info['amount'];
            if ($fine_paid_amount < 0) {
                $fine_paid_amount = 0;
            }
            $data = array(
                'id'          => $id,
                'return_date' => $return_store,
                'is_returned' => 1,
            );
            if ($this->db->field_exists('fine_amount', 'book_issues')) {
                $data['fine_amount'] = (float) $fine_info['amount'];
                $data['fine_paid']   = $fine_paid_amount;
            }
            $this->bookissue_model->update($data);

            $msg = $this->lang->line('success_message');
            if ($fine_info['amount'] > 0) {
                $msg .= ' ' . $this->lang->line('fine') . ': ' . $this->customlib->getSchoolCurrencyFormat() . number_format($fine_info['amount'], 2);
            }
            $array = array(
                'status'  => 'success',
                'error'   => '',
                'message' => $msg,
                'fine'    => $fine_info,
            );
            echo json_encode($array);
        }
    }

    /**
     * AJAX: renew an outstanding book issue (extend due date).
     */
    public function bookrenew()
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_edit')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access denied.'));
            return;
        }
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'required|trim|integer');
        $this->form_validation->set_rules('member_id', $this->lang->line('member_id'), 'required|trim|integer');
        if ($this->form_validation->run() == false) {
            echo json_encode(array(
                'status' => 'fail',
                'error'  => array(
                    'id'        => form_error('id'),
                    'member_id' => form_error('member_id'),
                ),
            ));
            return;
        }
        $member_id = (int) $this->input->post('member_id');
        if ($this->librarymember_model->isStudentMemberAccountDisabled($member_id)) {
            echo json_encode(array(
                'status'  => 'fail',
                'message' => 'This student is disabled. Book renewal is not allowed.',
            ));
            return;
        }
        $result = $this->bookissue_model->renew_issue(
            (int) $this->input->post('id'),
            $member_id
        );
        if ($result['status'] === 'success' && !empty($result['new_due_date'])) {
            $result['new_due_date_display'] = $this->customlib->formatLibraryDateBsForDisplay($result['new_due_date']);
        }
        echo json_encode($result);
    }

    /**
     * AJAX: preview fine for a return date (return modal).
     */
    public function bookfine_preview()
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            echo json_encode(array('status' => 'fail'));
            return;
        }
        $id   = (int) $this->input->post('id');
        $date = $this->input->post('date');
        $issue = $this->bookissue_model->get($id);
        if (empty($issue)) {
            echo json_encode(array('status' => 'fail'));
            return;
        }
        $return_norm = $this->customlib->dateFormatToYYYYMMDD($date);
        $fine_info = $this->bookissue_model->calculate_fine_for_issue(
            $issue,
            $return_norm !== null ? $return_norm : $date
        );
        $currency = $this->customlib->getSchoolCurrencyFormat();
        echo json_encode(array(
            'status'         => 'success',
            'amount'         => $fine_info['amount'],
            'overdue_days'   => $fine_info['overdue_days'],
            'is_overdue'     => $fine_info['is_overdue'],
            'amount_display' => $currency . number_format($fine_info['amount'], 2),
        ));
    }

    public function student()
    {
        if (!$this->rbac->hasPrivilege('add_student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/student');
        $data['title']     = 'Student Search';
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $button            = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/member/studentSearch', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class       = $this->input->post('class_id');
            $section     = $this->input->post('section_id');
            $search      = $this->input->post('search');
            $search_text = $this->input->post('search_text');
            if (isset($search)) {
                if ($search == 'search_filter') {
                    $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                    if ($this->form_validation->run() == false) {

                    } else {
                        $data['searchby']    = "filter";
                        $data['class_id']    = $this->input->post('class_id');
                        $data['section_id']  = $this->input->post('section_id');
                        $data['search_text'] = $this->input->post('search_text');
                        $resultlist          = $this->student_model->searchLibraryStudent($class, $section);

                        $data['resultlist'] = $resultlist;
                    }
                } else if ($search == 'search_full') {
                    $data['searchby']    = "text";
                    $data['class_id']    = $this->input->post('class_id');
                    $data['section_id']  = $this->input->post('section_id');
                    $data['search_text'] = trim($this->input->post('search_text'));
                    $resultlist          = $this->student_model->searchFullText($search_text);
                    $data['resultlist']  = $resultlist;
                }
            }
            $data['sch_setting'] = $this->sch_setting_detail;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/member/studentSearch', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function add()
    {
        if ($this->input->post('library_card_no') != "") {

            $this->form_validation->set_rules('library_card_no', $this->lang->line('library_card_number'), 'required|trim|xss_clean|callback_check_cardno_exists');
            if ($this->form_validation->run() == false) {
                $data = array(
                    'library_card_no' => form_error('library_card_no'),
                );
                $array = array('status' => 'fail', 'error' => $data);
                echo json_encode($array);
            } else {
                $library_card_no = $this->input->post('library_card_no');
                $student         = $this->input->post('member_id');
                $data            = array(
                    'member_type'     => 'student',
                    'member_id'       => $student,
                    'library_card_no' => $library_card_no,
                );

                $inserted_id = $this->librarymanagement_model->add($data);
                $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
                echo json_encode($array);
            }
        } else {
            $library_card_no = $this->input->post('library_card_no');
            $student         = $this->input->post('member_id');
            $data            = array(
                'member_type'     => 'student',
                'member_id'       => $student,
                'library_card_no' => $library_card_no,
            );

            $inserted_id = $this->librarymanagement_model->add($data);
            $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
            echo json_encode($array);
        }
    }

    public function check_cardno_exists()
    {
        $data['library_card_no'] = $this->security->xss_clean($this->input->post('library_card_no'));
        $data['member_id'] = $this->input->post('member_id');
        
        // Determine member type based on the method being called
        $current_method = $this->router->fetch_method();
        if ($current_method == 'addteacher') {
            $data['member_type'] = 'teacher';
        } else {
            $data['member_type'] = 'student';
        }

        if ($this->librarymanagement_model->check_data_exists($data)) {
            $this->form_validation->set_message('check_cardno_exists', $this->lang->line('card_no_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function teacher()
    {
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'Library/member/teacher');
        $data['title']       = 'Add Teacher';
        $data['teacherlist'] = $this->teacher_model->getLibraryTeacher(); 
        $data['genderList'] = $this->customlib->getGender();         
        $this->load->view('layout/header', $data);
        $this->load->view('admin/member/teacher', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addteacher()
    {
        if ($this->input->post('library_card_no') != "") {
            $this->form_validation->set_rules('library_card_no', $this->lang->line('library_card_number'), 'required|trim|xss_clean|callback_check_cardno_exists');
            if ($this->form_validation->run() == false) {
                $data = array(
                    'library_card_no' => form_error('library_card_no'),
                );
                $array = array('status' => 'fail', 'error' => $data);
                echo json_encode($array);
            } else {
                $library_card_no = $this->input->post('library_card_no');
                $student         = $this->input->post('member_id');
                $data            = array(
                    'member_type'     => 'teacher',
                    'member_id'       => $student,
                    'library_card_no' => $library_card_no,
                );

                $inserted_id = $this->librarymanagement_model->add($data);
                $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
                echo json_encode($array);
            }
        } else { 
            $library_card_no = $this->input->post('library_card_no');
            $student         = $this->input->post('member_id');
            $data            = array(
                'member_type'     => 'teacher',
                'member_id'       => $student,
                'library_card_no' => $library_card_no,
            );

            $inserted_id = $this->librarymanagement_model->add($data);
            $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
            echo json_encode($array);
        }
    }

    public function surrender()
    {
        $this->form_validation->set_rules('member_id', $this->lang->line('book'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

        } else {
            $member_id = $this->input->post('member_id');
              $row_affected=$this->librarymember_model->surrender($member_id);
            $array = array('status' => 'success', 'row_affected'=>$row_affected, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    /**
     * Search for member by book number (AJAX)
     */
    public function searchByBookNumber()
    {
        $book_no = $this->input->post('book_no');
        
        if (empty($book_no)) {
            $array = array(
                'status' => 'error',
                'message' => 'Please enter a book number',
                'data' => null
            );
            $this->output->set_content_type('application/json')->set_output(json_encode($array));
            return;
        }

        // Check if book number exists and get book details
        $this->load->model('book_model');
        $book_details = $this->book_model->getBookByNumber($book_no);
        
        if (!$book_details) {
            $array = array(
                'status' => 'not_found',
                'message' => 'Book number not found in system',
                'data' => null
            );
            $this->output->set_content_type('application/json')->set_output(json_encode($array));
            return;
        }

        // Check if book is issued to anyone
        $member_data = $this->bookissue_model->getMemberByBookNumber($book_no);
        
        if ($member_data) {
            // Merge book details with member data
            $member_data['book_title'] = isset($book_details['book_title']) ? $book_details['book_title'] : '';
            $member_data['author'] = isset($book_details['author']) ? $book_details['author'] : '';
            $array = array(
                'status' => 'issued',
                'message' => 'Book is issued to a member',
                'data' => $member_data
            );
        } else {
            // Book exists but not issued - return book details
            $array = array(
                'status' => 'not_issued',
                'message' => 'Book is not issued to any member',
                'data' => array(
                    'book_id' => isset($book_details['id']) ? $book_details['id'] : '',
                    'book_title' => isset($book_details['book_title']) ? $book_details['book_title'] : '',
                    'book_no' => $book_no,
                    'author' => isset($book_details['author']) ? $book_details['author'] : '',
                    'isbn_no' => isset($book_details['isbn_no']) ? $book_details['isbn_no'] : '',
                    'publish' => isset($book_details['publish']) ? $book_details['publish'] : '',
                    'subject' => isset($book_details['subject']) ? $book_details['subject'] : '',
                    'rack_no' => isset($book_details['rack_no']) ? $book_details['rack_no'] : '',
                    'qty' => isset($book_details['qty']) ? $book_details['qty'] : 0,
                    'available' => isset($book_details['qty']) ? $book_details['qty'] : 0,
                    'description' => isset($book_details['description']) ? $book_details['description'] : ''
                )
            );
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($array));
    }

    /**
     * Display issues list - all issues or today's issues
     * @param string $type 'all' or 'today'
     */
    public function issuesList($type = 'all')
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            access_denied();
        }
    
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/index');
        
        if ($type == 'today') {
            $data['title'] = "Today's Issues";
            $data['title_list'] = "Today's Issues";
            $data['issues'] = $this->bookissue_model->getTodayIssues();
        } else {
            $data['title'] = 'All Issues';
            $data['title_list'] = 'All Issues';
            $data['issues'] = $this->bookissue_model->getAllIssues();
        }
        
        $data['type'] = $type;
        $data['sch_setting'] = $this->sch_setting_detail;
        
        $this->load->view('layout/header');
        $this->load->view('admin/librarian/issues_list', $data);
        $this->load->view('layout/footer');
    }

}
