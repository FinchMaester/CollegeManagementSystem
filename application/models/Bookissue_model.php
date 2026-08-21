<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Bookissue_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->ensure_renew_fine_schema();
    }

    /**
     * Add renewal/fine columns when missing (MySQL 5.7 safe).
     */
    public function ensure_renew_fine_schema()
    {
        if (!$this->db->table_exists('book_issues')) {
            return;
        }
        $columns = array(
            'renew_count'     => "ALTER TABLE `book_issues` ADD `renew_count` INT NOT NULL DEFAULT 0 AFTER `is_returned`",
            'fine_amount'     => "ALTER TABLE `book_issues` ADD `fine_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `renew_count`",
            'fine_paid'       => "ALTER TABLE `book_issues` ADD `fine_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `fine_amount`",
            'last_renewed_at' => "ALTER TABLE `book_issues` ADD `last_renewed_at` DATETIME NULL DEFAULT NULL AFTER `fine_paid`",
        );
        foreach ($columns as $col => $sql) {
            if (!$this->db->field_exists($col, 'book_issues')) {
                $this->db->query($sql);
            }
        }
    }

    /**
     * @return array
     */
    public function get_library_settings()
    {
        $this->load->model('library_settings_model');
        return $this->library_settings_model->get_effective_settings();
    }

    /**
     * @param array       $issue row from book_issues (+ book fields)
     * @param string|null $as_of_raw return date or null for today
     * @return array amount, overdue_days, is_overdue
     */
    public function calculate_fine_for_issue(array $issue, $as_of_raw = null)
    {
        $settings = $this->get_library_settings();
        $out = array(
            'amount'       => 0.0,
            'overdue_days' => 0,
            'is_overdue'   => false,
        );
        if (empty($settings['fine_enabled']) || !empty($issue['is_returned'])) {
            return $out;
        }
        $this->load->library('customlib');
        $due = isset($issue['duereturn_date']) ? $issue['duereturn_date'] : '';
        $overdue_days = $this->customlib->libraryOverdueDays($due, $as_of_raw);
        $billable = $overdue_days - (int) $settings['fine_grace_days'];
        if ($billable <= 0) {
            return $out;
        }
        $amount = $billable * (float) $settings['fine_per_day'];
        if ($settings['fine_max'] > 0 && $amount > $settings['fine_max']) {
            $amount = $settings['fine_max'];
        }
        $out['amount']       = round($amount, 2);
        $out['overdue_days'] = $overdue_days;
        $out['is_overdue']   = true;
        return $out;
    }

    /**
     * @param array $books getMemberBooks() rows
     * @return array
     */
    public function enrich_issued_books(array $books)
    {
        $settings = $this->get_library_settings();
        $this->load->library('customlib');
        foreach ($books as $key => $book) {
            $renew_count = isset($book['renew_count']) ? (int) $book['renew_count'] : 0;
            $fine_info   = $this->calculate_fine_for_issue($book);
            $books[$key]['renew_count']       = $renew_count;
            $books[$key]['renewals_left']     = max(0, (int) $settings['max_renewals'] - $renew_count);
            $books[$key]['can_renew']         = !empty($settings['renewal_enabled'])
                && empty($book['is_returned'])
                && $books[$key]['renewals_left'] > 0;
            $books[$key]['is_overdue']        = $fine_info['is_overdue'];
            $books[$key]['overdue_days']      = $fine_info['overdue_days'];
            $books[$key]['calculated_fine']   = $fine_info['amount'];
            $books[$key]['stored_fine']       = isset($book['fine_amount']) ? (float) $book['fine_amount'] : 0;
            $books[$key]['fine_paid']         = isset($book['fine_paid']) ? (float) $book['fine_paid'] : 0;
        }
        return $books;
    }

    /**
     * Extend due date for an outstanding issue.
     *
     * @param int $issue_id
     * @param int $member_id
     * @return array status, message, new_due_date?
     */
    public function renew_issue($issue_id, $member_id)
    {
        $settings = $this->get_library_settings();
        if (empty($settings['renewal_enabled'])) {
            return array('status' => 'fail', 'message' => 'Book renewal is disabled.');
        }
        $issue_id  = (int) $issue_id;
        $member_id = (int) $member_id;
        $issue     = $this->get($issue_id);
        if (empty($issue) || (int) $issue['member_id'] !== $member_id) {
            return array('status' => 'fail', 'message' => 'Issue record not found.');
        }
        if (!empty($issue['is_returned'])) {
            return array('status' => 'fail', 'message' => 'Returned books cannot be renewed.');
        }
        $renew_count = isset($issue['renew_count']) ? (int) $issue['renew_count'] : 0;
        if ($renew_count >= (int) $settings['max_renewals']) {
            return array('status' => 'fail', 'message' => 'Maximum renewals reached for this book.');
        }
        $this->load->library('customlib');
        $days = (int) $settings['renewal_days'];
        $due  = isset($issue['duereturn_date']) ? $issue['duereturn_date'] : '';
        if ($this->customlib->isLibraryDueDateOverdue($due)) {
            $today_bs = $this->customlib->libraryAdTimestampToBsYmd($this->customlib->libraryTodayAdStartTimestamp());
            $new_due  = $this->customlib->libraryAddDays($today_bs, $days);
        } else {
            $new_due = $this->customlib->libraryAddDays($due, $days);
        }
        if ($new_due === '') {
            return array('status' => 'fail', 'message' => 'Could not calculate new due date.');
        }
        $this->update(array(
            'id'              => $issue_id,
            'duereturn_date'  => $new_due,
            'renew_count'     => $renew_count + 1,
            'last_renewed_at' => date('Y-m-d H:i:s'),
        ));
        return array(
            'status'       => 'success',
            'message'      => 'Book renewed successfully.',
            'new_due_date' => $new_due,
            'renew_count'  => $renew_count + 1,
        );
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($id = null)
    {
        $this->db->select()->from('book_issues');
        if ($id != null) {
            $this->db->where('book_issues.id', $id);
        } else {
            $this->db->order_by('book_issues.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('book_issues');
        $message   = DELETE_RECORD_CONSTANT . " On book issues id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->insert('book_issues', $data);
        $insert_id = $this->db->insert_id();
        $message   = INSERT_RECORD_CONSTANT . " On book issues id " . $insert_id;
        $action    = "Insert";
        $record_id = $insert_id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
        return $insert_id;
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function getMemberBooks($member_id = null)
    {
        $select = 'book_issues.id,book_issues.return_date,book_issues.duereturn_date,book_issues.issue_date,book_issues.is_returned,books.book_title,books.book_no,books.author';
        if ($this->db->field_exists('renew_count', 'book_issues')) {
            $select .= ',book_issues.renew_count,book_issues.fine_amount,book_issues.fine_paid,book_issues.last_renewed_at';
        }
        $this->db->select($select)->from('book_issues');
        $this->db->join('books', 'books.id = book_issues.book_id', 'left');
        if ($member_id != null) {
            $this->db->where('book_issues.member_id', $member_id);
            $this->db->order_by("book_issues.is_returned", "asc");
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getissueMemberBooks($member_id = null)
    {
        $sql   = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,`book_issues`.`id`,staff.name as fname,staff.surname as lname, 'admission'=' ' as admission ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`duereturn_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id WHERE `book_issues`.`is_returned` = '0' and libarary_members.member_type='teacher' union all SELECT libarary_members.id as members_id, libarary_members.library_card_no, `book_issues`.`id`,students.firstname as fname,students.lastname as lname,students.middlename, students.admission_no as adminssion,libarary_members.member_type, `book_issues`.`return_date`, `book_issues`.`duereturn_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join students on students.id=libarary_members.member_id WHERE `book_issues`.`is_returned` = '0' and libarary_members.member_type='student'";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getissuereturnMemberBooks($member_id = null, $start_date = null, $end_date = null)
    {
        $condition  = "";
        $condition2 = "";
        if ($start_date != "" && $end_date != "") {
            $condition = " and date_format(issue_date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
        }

        if ($this->session->has_userdata('admin')) {
            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);
            $superadmin_visible = $this->customlib->superadmin_visible();
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $condition2 = " and staff_roles.role_id != 7 ";
            }
        }

        $sql = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,`book_issues`.`id`,staff.name as fname,staff.name as mname,staff.surname as lname, staff.employee_id as admission ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id left join staff_roles on staff_roles.staff_id = staff.id
        WHERE `book_issues`.`is_returned` = '1' and libarary_members.member_type='teacher' " . $condition2 . "
        union all
        SELECT libarary_members.id as members_id, libarary_members.library_card_no, `book_issues`.`id`,students.firstname as fname,students.middlename as mname,students.lastname as lname, students.admission_no as admission,libarary_members.member_type, `book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join students on students.id=libarary_members.member_id WHERE `book_issues`.`is_returned` = '1' and libarary_members.member_type='student' " . $condition . "  ";
        $this->datatables->query($sql)
            ->searchable('book_title,book_no,issue_date,return_date,book_no,libarary_members.id,library_card_no,students.admission_no,students.firstname,member_type')
            ->orderable('book_title,book_no,issue_date,return_date,members_id,library_card_no,admission,fname,member_type')
            ->query_where_enable(true);
        return $this->datatables->generate('json');
    }

    public function update($data)
    {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('book_issues', $data);
        }
    }

    public function book_issuedByMemberID($member_id)
    {
        $this->db->select('book_issues.return_date,books.book_no,book_issues.issue_date,book_issues.is_returned,books.book_title,books.author,`book_issues`.`duereturn_date`')
            ->from('book_issues')
            ->join('libarary_members', 'libarary_members.id = book_issues.member_id', 'left')
            ->join('books', 'books.id = book_issues.book_id', 'left')
            ->where('libarary_members.id', $member_id)
            ->order_by('book_issues.is_returned', 'asc');
        $result = $this->db->get();
        return $result->result_array();
    }

    public function getAvailQuantity($id = null)
    {
        $sql = "SELECT books.*,IFNULL(total_issue, '0') as `total_issue` FROM books LEFT JOIN (SELECT COUNT(*) as `total_issue`, book_id from book_issues  where is_returned= 0 GROUP by book_id ) as `book_count` on books.id=book_count.book_id WHERE books.id=$id";
        $query = $this->db->query($sql);
        return $query->row();
    }

    public function valid_check_exists($str)
    {
        $member_id = $this->input->post('member_id');
        $book_id = $this->input->post('book_id');
        if ($book_id == "") {
            return true;
        }

        $this->load->model('librarymember_model');
        if ($this->librarymember_model->isStudentMemberAccountDisabled((int) $member_id)) {
            $this->form_validation->set_message('check_exists', 'This student is disabled. New book issues are not allowed.');
            return false;
        }

        if ($this->checkAvailQuantity($book_id)) {
            $this->form_validation->set_message('check_exists', $this->lang->line('book_not_available'));
            return false;
        } 
        
        if ($this->checkBookIssuedOrNot($book_id,$member_id)) {            
            $this->form_validation->set_message('check_exists', $this->lang->line('book_already_issued'));
            return false;
        } else {
            return true;
        }        
    }

    public function checkBookIssuedOrNot($id, $member_id)
    {
        $sql = "SELECT IFNULL(book_issues.id, '0') as total_issue FROM book_issues  where book_issues.book_id=$id and book_issues.member_id=$member_id and book_issues.is_returned=0";       
        $query     = $this->db->query($sql);        
        $result    = $query->row();       
        if (!empty($result) && $result->total_issue > 0) {           
            return true;
        }
        return false;        
    }
    
    public function checkAvailQuantity($id = null)
    {
        $sql = "SELECT books.*,IFNULL(total_issue, '0') as `total_issue` FROM books LEFT JOIN (SELECT COUNT(*) as `total_issue`, book_id from book_issues  where is_returned= 0 GROUP by book_id) as `book_count` on books.id=book_count.book_id WHERE books.id=$id";
        $query     = $this->db->query($sql);
        $result    = $query->row();
        $remaining = ($result->qty - $result->total_issue);
        if ($remaining > 0) {
            return false;
        }
        return true;
    }

    public function studentBookIssue_report($start_date, $end_date)
    {
        $condition = $this->_library_bs_date_sql("book_issues.issue_date", $start_date, $end_date);
        $condition .= $this->_library_member_academic_sql();
        if (isset($_POST['members_type']) && $_POST['members_type'] != '') {
            $condition .= " and libarary_members.member_type='" . $this->db->escape_str($_POST['members_type']) . "'";
        }

        $sql = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,`book_issues`.`id`,CONCAT_WS(' ',staff.name,staff.surname) as staff_name,staff.employee_id,staff.id as staff_id,CONCAT_WS(' ',students.firstname,students.lastname) as student_name,students.firstname,students.middlename,students.lastname, students.admission_no as admission ,students.id as sid ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `books`.`book_title`, `books`.`book_no`, `books`.`author`,book_issues.duereturn_date FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id left join students on (students.id=libarary_members.member_id and libarary_members.member_type='student') WHERE `book_issues`.`is_returned` = '0' " . $condition;

        $this->datatables->query($sql)
            ->orderable('book_title,book_no,issue_date,duereturn_date,members_id,library_card_no, students.admission_no,firstname')
            ->searchable('book_title,book_no,issue_date,duereturn_date,libarary_members.id,library_card_no, students.admission_no,firstname')
            ->query_where_enable(true);
        return $this->datatables->generate('json');
    }

    public function bookduereport($start_date, $end_date)
    {
        $condition = $this->_library_bs_date_sql("book_issues.duereturn_date", $start_date, $end_date);
        $condition .= $this->_library_member_academic_sql();
        if (isset($_POST['members_type']) && $_POST['members_type'] != '') {
            $condition .= " and libarary_members.member_type='" . $this->db->escape_str($_POST['members_type']) . "'";
        }
        $sql = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,staff.id as staff_id, staff.employee_id,`book_issues`.`id`,CONCAT_WS(' ',staff.name,students.firstname) as fname,CONCAT_WS(' ',staff.surname,students.lastname) as lname, students.firstname,students.middlename,students.lastname,students.admission_no as admission ,students.id as sid ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `books`.`book_title`, `books`.`book_no`, `books`.`author`,`book_issues`.duereturn_date FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on (staff.id=libarary_members.member_id and libarary_members.member_type='teacher') left join students on (students.id=libarary_members.member_id and libarary_members.member_type='student') WHERE `book_issues`.`is_returned` = '0' " . $condition;

        $this->datatables->query($sql)
            ->orderable('book_title,book_no,issue_date,duereturn_date,members_id,library_card_no, students.admission_no,firstname')
            ->searchable('book_title,book_no,issue_date,duereturn_date,libarary_members.id,library_card_no, students.admission_no,firstname')
            ->query_where_enable(true);
        return $this->datatables->generate('json');       
    }

    public function dueforreturn($start_date, $end_date)
    {
        $condition = " and date_format(book_issues.duereturn_date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
        $sql   = "SELECT count(*) as total FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id WHERE `book_issues`.`is_returned` = '0' " . $condition;
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function forreturn($start_date, $end_date)
    {
        $condition = " and date_format(book_issues.duereturn_date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

        $sql   = "SELECT count(*) as total FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id WHERE `book_issues`.`is_returned` = '1' " . $condition;
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * Compare VARCHAR library dates stored as BS YYYY-MM-DD.
     * Do not use MySQL DATE()/DATE_FORMAT — BS months can have day 31/32.
     */
    private function _library_bs_date_sql($column, $start_date, $end_date)
    {
        if ($start_date === null || $start_date === '' || $end_date === null || $end_date === '') {
            return '';
        }
        return " AND " . $column . " IS NOT NULL AND TRIM(" . $column . ") <> '' AND TRIM(" . $column . ") <> '0000-00-00'"
            . " AND LEFT(TRIM(" . $column . "), 10) BETWEEN " . $this->db->escape($start_date)
            . " AND " . $this->db->escape($end_date);
    }

    /**
     * Optional Faculty / Program / Class for student borrowers (current session).
     * Staff issues are excluded when any of these filters is set.
     */
    private function _library_member_academic_sql()
    {
        $section_id = $this->input->post('section_id');
        $program_id = $this->input->post('program_id');
        $class_id   = $this->input->post('class_id');
        $has_section = ($section_id !== null && $section_id !== '' && $section_id !== '0');
        $has_program = ($program_id !== null && $program_id !== '' && $program_id !== '0');
        $has_class   = ($class_id !== null && $class_id !== '' && $class_id !== '0');
        if (!$has_section && !$has_program && !$has_class) {
            return '';
        }
        $sql = " AND libarary_members.member_type = 'student' AND EXISTS ("
            . " SELECT 1 FROM student_session ss"
            . " WHERE ss.student_id = students.id"
            . " AND ss.session_id = " . (int) $this->current_session;
        if ($has_section) {
            $sql .= " AND ss.section_id = " . (int) $section_id;
        }
        if ($has_program) {
            $sql .= " AND ss.program_id = " . (int) $program_id;
        }
        if ($has_class) {
            $sql .= " AND ss.class_id = " . (int) $class_id;
        }
        $sql .= " )";
        return $sql;
    }

    /**
     * Find which member has a book by book number
     * @param string $book_no
     * @return array|null
     */
    public function getMemberByBookNumber($book_no)
    {
        $sql = "SELECT 
                    book_issues.id as issue_id,
                    book_issues.issue_date,
                    book_issues.duereturn_date,
                    book_issues.return_date,
                    book_issues.is_returned,
                    books.id as book_id,
                    books.book_title,
                    books.book_no,
                    books.author,
                    libarary_members.id as member_id,
                    libarary_members.library_card_no,
                    libarary_members.member_type,
                    CASE 
                        WHEN libarary_members.member_type = 'student' THEN 
                            CONCAT_WS(' ', students.firstname, students.middlename, students.lastname)
                        WHEN libarary_members.member_type = 'teacher' THEN 
                            CONCAT_WS(' ', staff.name, staff.surname)
                        ELSE ''
                    END as member_name,
                    CASE 
                        WHEN libarary_members.member_type = 'student' THEN students.admission_no
                        WHEN libarary_members.member_type = 'teacher' THEN staff.employee_id
                        ELSE ''
                    END as admission_emp_no,
                    CASE 
                        WHEN libarary_members.member_type = 'student' THEN students.guardian_phone
                        WHEN libarary_members.member_type = 'teacher' THEN staff.contact_no
                        ELSE ''
                    END as phone
                FROM book_issues
                LEFT JOIN books ON books.id = book_issues.book_id
                LEFT JOIN libarary_members ON libarary_members.id = book_issues.member_id
                LEFT JOIN students ON (students.id = libarary_members.member_id AND libarary_members.member_type = 'student')
                LEFT JOIN staff ON (staff.id = libarary_members.member_id AND libarary_members.member_type = 'teacher')
                WHERE books.book_no = ? 
                AND book_issues.is_returned = '0'
                LIMIT 1";
        
        $query = $this->db->query($sql, array($book_no));
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return null;
    }

    /**
     * Check if book number exists in books table
     * @param string $book_no
     * @return bool
     */
    public function bookNumberExists($book_no)
    {
        $this->db->select('id');
        $this->db->from('books');
        $this->db->where('book_no', $book_no);
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Get total count of all issues in the system
     * @return int
     */
    public function getTotalIssuesCount()
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('book_issues');
        $query = $this->db->get();
        $result = $query->row();
        return isset($result->total) ? (int)$result->total : 0;
    }

    /**
     * Get count of issues created today
     * @return int
     */
    public function getTodayIssuesCount()
    {
        // Get today's AD date
        $today_ad = date('Y-m-d');
        $this->load->library('customlib');
        
        // Get today's BS date using simple conversion (AD year + 57)
        $today_bs_simple = $this->customlib->convertADDateToBS($today_ad);
        
        // Also try BS date with year + 56 and + 58 to account for conversion variations
        $ad_year = (int)date('Y');
        $ad_month = date('m');
        $ad_day = date('d');
        
        $today_bs_alt1 = sprintf('%04d-%02s-%02s', $ad_year + 56, $ad_month, $ad_day);
        $today_bs_alt2 = sprintf('%04d-%02s-%02s', $ad_year + 58, $ad_month, $ad_day);
        
        // Get the MOST RECENT issue_date to determine today's BS date
        // This is the most reliable way since users select "today" using the Nepali date picker
        $this->db->select('DATE(issue_date) as issue_date_only');
        $this->db->from('book_issues');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $recent_query = $this->db->get();
        $today_bs_from_db = null;
        if ($recent_query->num_rows() > 0) {
            $row = $recent_query->row();
            $today_bs_from_db = $row->issue_date_only;
        }
        
        // Build list of possible "today" dates to check
        $possible_today_dates = array();
        $possible_today_dates[] = $today_ad;
        $possible_today_dates[] = $today_bs_simple;
        $possible_today_dates[] = $today_bs_alt1;
        $possible_today_dates[] = $today_bs_alt2;
        if ($today_bs_from_db) {
            $possible_today_dates[] = $today_bs_from_db;
        }
        
        // Remove duplicates
        $possible_today_dates = array_unique($possible_today_dates);
        
        // Count issues matching any of the possible "today" dates
        $this->db->select('COUNT(*) as total');
        $this->db->from('book_issues');
        if (!empty($possible_today_dates)) {
            $this->db->group_start();
            $first = true;
            foreach ($possible_today_dates as $date) {
                if (!$first) {
                    $this->db->or_where('DATE(issue_date)', $date);
                } else {
                    $this->db->where('DATE(issue_date)', $date);
                    $first = false;
                }
            }
            $this->db->group_end();
        }
        $query = $this->db->get();
        $result = $query->row();
        return isset($result->total) ? (int)$result->total : 0;
    }

    /**
     * Get all issues (both returned and not returned) with full details
     * @return array
     */
    public function getAllIssues()
    {
        $sql = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,`book_issues`.`id`,staff.name as fname,staff.surname as lname, '' as mname, staff.employee_id as admission ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `book_issues`.`duereturn_date`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id WHERE libarary_members.member_type='teacher' 
        union all 
        SELECT libarary_members.id as members_id, libarary_members.library_card_no, `book_issues`.`id`,students.firstname as fname,students.lastname as lname,students.middlename as mname, students.admission_no as admission,libarary_members.member_type, `book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `book_issues`.`duereturn_date`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join students on students.id=libarary_members.member_id WHERE libarary_members.member_type='student' ORDER BY issue_date DESC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * Books currently issued (not yet returned), with borrower details.
     *
     * @return array
     */
    public function getOutstandingIssues()
    {
        $sql = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,`book_issues`.`id`,staff.name as fname,staff.surname as lname, '' as mname, staff.employee_id as admission ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `book_issues`.`duereturn_date`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id WHERE libarary_members.member_type='teacher' AND book_issues.is_returned = 0
        union all
        SELECT libarary_members.id as members_id, libarary_members.library_card_no, `book_issues`.`id`,students.firstname as fname,students.lastname as lname,students.middlename as mname, students.admission_no as admission,libarary_members.member_type, `book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `book_issues`.`duereturn_date`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join students on students.id=libarary_members.member_id WHERE libarary_members.member_type='student' AND book_issues.is_returned = 0 ORDER BY issue_date DESC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * Get today's issues with full details
     * @return array
     */
    public function getTodayIssues()
    {
        // Get today's AD date
        $today_ad = date('Y-m-d');
        $this->load->library('customlib');
        
        // Get today's BS date using simple conversion (AD year + 57)
        $today_bs_simple = $this->customlib->convertADDateToBS($today_ad);
        
        // Also try BS date with year + 56 and + 58 to account for conversion variations
        $ad_year = (int)date('Y');
        $ad_month = date('m');
        $ad_day = date('d');
        
        $today_bs_alt1 = sprintf('%04d-%02s-%02s', $ad_year + 56, $ad_month, $ad_day);
        $today_bs_alt2 = sprintf('%04d-%02s-%02s', $ad_year + 58, $ad_month, $ad_day);
        
        // Get the MOST RECENT issue_date to determine today's BS date
        $this->db->select('DATE(issue_date) as issue_date_only');
        $this->db->from('book_issues');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $recent_query = $this->db->get();
        $today_bs_from_db = null;
        if ($recent_query->num_rows() > 0) {
            $row = $recent_query->row();
            $today_bs_from_db = $row->issue_date_only;
        }
        
        // Build list of possible "today" dates to check
        $possible_today_dates = array();
        $possible_today_dates[] = $today_ad;
        $possible_today_dates[] = $today_bs_simple;
        $possible_today_dates[] = $today_bs_alt1;
        $possible_today_dates[] = $today_bs_alt2;
        if ($today_bs_from_db) {
            $possible_today_dates[] = $today_bs_from_db;
        }
        
        // Remove duplicates
        $possible_today_dates = array_unique($possible_today_dates);
        
        // Build the WHERE condition with all possible dates
        $date_conditions = array();
        foreach ($possible_today_dates as $date) {
            $date_escaped = $this->db->escape($date);
            $date_conditions[] = "DATE(`book_issues`.`issue_date`) = " . $date_escaped;
        }
        $date_condition_sql = !empty($date_conditions) ? "(" . implode(" OR ", $date_conditions) . ")" : "1=0";
        
        $sql = "SELECT libarary_members.id as members_id,libarary_members.library_card_no,`book_issues`.`id`,staff.name as fname,staff.surname as lname, '' as mname, staff.employee_id as admission ,libarary_members.member_type,`book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `book_issues`.`duereturn_date`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join staff on staff.id=libarary_members.member_id WHERE " . $date_condition_sql . " and libarary_members.member_type='teacher' 
        union all 
        SELECT libarary_members.id as members_id, libarary_members.library_card_no, `book_issues`.`id`,students.firstname as fname,students.lastname as lname,students.middlename as mname, students.admission_no as admission,libarary_members.member_type, `book_issues`.`return_date`, `book_issues`.`issue_date`, `book_issues`.`is_returned`, `book_issues`.`duereturn_date`, `books`.`book_title`, `books`.`book_no`, `books`.`author` FROM `book_issues` LEFT JOIN `books` ON `books`.`id` = `book_issues`.`book_id` left join libarary_members on libarary_members.id=book_issues.member_id left join students on students.id=libarary_members.member_id WHERE " . $date_condition_sql . " and libarary_members.member_type='student' ORDER BY issue_date DESC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

}
