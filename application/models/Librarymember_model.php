<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarymember_model extends MY_Model
{
    protected $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->load->config('library');
    }

    /**
     * @return string 'registered_only'|'all_session_students'
     */
    public function getLibraryStudentMemberListMode()
    {
        $mode = $this->config->item('library_student_member_list');
        return ($mode === 'all_session_students') ? 'all_session_students' : 'registered_only';
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get()
    {

        $query = "SELECT libarary_members.id as `lib_member_id`,libarary_members.library_card_no,libarary_members.member_type,students.admission_no,students.firstname,students.lastname,students.mobileno,students.guardian_phone,null as `teacher_name`,null as `teacher_email`,null as `teacher_sex`,null as `teacher_phone`,students.middlename, null as staff_id,students.id as stu_id,null as emp_id FROM `libarary_members` INNER JOIN students on libarary_members.member_id= students.id INNER JOIN student_session ON student_session.student_id = students.id WHERE libarary_members.member_type='student' and students.is_active = 'yes' AND student_session.session_id = " . (int)$this->current_session . " AND student_session.is_active = 'yes' UNION SELECT libarary_members.id as `lib_member_id`,libarary_members.library_card_no,libarary_members.member_type,null,null,null,null,null,CONCAT_WS(' ',staff.name,staff.surname) as name,staff.email,null,staff.contact_no,null, staff.id as staff_id,null as stu_id,staff.employee_id as emp_id FROM `libarary_members` INNER JOIN staff on libarary_members.member_id= staff.id WHERE libarary_members.member_type='teacher' ";
        $query = $this->db->query($query);
        return $query->result_array();
    }

    public function checkIsMember($member_type, $id)
    {
        $this->db->select()->from('libarary_members');
        $this->db->where('libarary_members.member_id', $id);
        $this->db->where('libarary_members.member_type', $member_type);
        $query = $this->db->get();
        $result = $query->num_rows();
        if ($result > 0) {
            $row        = $query->row();
            $book_lists = $this->bookissue_model->book_issuedByMemberID($row->id);
            return $book_lists;
        } else {
            return false;
        }
    }

    public function getByMemberID($id = null)
    {
        $this->db->select()->from('libarary_members');
        if ($id != null) {
            $this->db->where('libarary_members.id', $id);
        }
        $query = $this->db->get();
        if ($id != null) {
            $result = $query->row();
            if (empty($result)) {
                return null;
            }
            if ($result->member_type == "student") {
                $return = $this->getStudentData($result->id);
            } else {
                $return = $this->getTeacherData($result->id);
            }
            return $return;
        }
    }

    public function getTeacherData($id)
    {
        $this->db->select('libarary_members.id as `lib_member_id`,libarary_members.library_card_no,libarary_members.member_type,staff.*');
        $this->db->from('libarary_members');
        $this->db->join('staff', 'libarary_members.member_id = staff.id');
        $this->db->where('libarary_members.id', $id);
        $query  = $this->db->get();
        $result = $query->row();
        return $result;
    }

    /**
     * True when a library member row is a student with students.is_active = 'no'.
     *
     * @param int|object $member_row_or_id libarary_members.id or getByMemberID() row
     * @return bool
     */
    public function isStudentMemberAccountDisabled($member_row_or_id)
    {
        if (is_numeric($member_row_or_id)) {
            $row = $this->getByMemberID((int) $member_row_or_id);
        } else {
            $row = $member_row_or_id;
        }
        if (empty($row) || empty($row->member_type) || strtolower((string) $row->member_type) !== 'student') {
            return false;
        }
        return isset($row->is_active) && strtolower(trim((string) $row->is_active)) === 'no';
    }

    public function getStudentData($id)
    {
        $session_id = (int) $this->current_session;
        $this->db->select('libarary_members.id as `lib_member_id`,libarary_members.library_card_no,libarary_members.member_type,students.*,sessions.session as session_year,classes.class,sections.section');
        $this->db->from('libarary_members');
        $this->db->join('students', 'libarary_members.member_id = students.id');
        $this->db->join(
            'student_session',
            'student_session.student_id = students.id' . ($session_id > 0 ? ' AND student_session.session_id = ' . $session_id : ''),
            'left'
        );
        $this->db->join('sessions', 'sessions.id = student_session.session_id', 'left');
        $this->db->join('classes', 'classes.id = student_session.class_id', 'left');
        $this->db->join('sections', 'sections.id = student_session.section_id', 'left');
        $this->db->where('libarary_members.id', $id);
        $query  = $this->db->get();
        $result = $query->row();
        return $result;
    }

    public function surrender($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('libarary_members');
        $message   = DELETE_RECORD_CONSTANT . " On libarary members id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->where('member_id', $id);
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
            return true;
        }
    }

    public function getAllMembers()
    {
        if ($this->getLibraryStudentMemberListMode() === 'all_session_students') {
            return $this->getAllMembersAllSessionStudents();
        }
        return $this->getAllMembersRegisteredOnly();
    }

    /**
     * Students who have been added as library members (libarary_members) — safe for issue/return URLs.
     *
     * Do not filter by student_session.is_active / is_leave here: those flags often exclude valid
     * library members while teacher rows have no session table — only staff would appear.
     * List every lib row tied to students; match member_type case-insensitively.
     */
    private function getAllMembersRegisteredOnly()
    {
        $query = "
            SELECT 
                libarary_members.id AS lib_member_id,
                libarary_members.library_card_no,
                'student' AS member_type,
                students.admission_no,
                students.firstname,
                students.middlename,
                students.lastname,
                students.father_name,
                students.dob,
                students.mobileno,
                students.guardian_phone,
                students.is_active AS student_is_active,
                NULL AS teacher_name,
                NULL AS teacher_phone,
                NULL AS emp_id,
                students.id AS stu_id,
                NULL AS staff_id
            FROM libarary_members
            INNER JOIN students ON libarary_members.member_id = students.id
            WHERE LOWER(TRIM(CAST(libarary_members.member_type AS CHAR))) = 'student'

            UNION

            SELECT 
                libarary_members.id AS lib_member_id,
                libarary_members.library_card_no,
                libarary_members.member_type,
                NULL AS admission_no,
                NULL AS firstname,
                NULL AS middlename,
                NULL AS lastname,
                NULL AS father_name,
                NULL AS dob,
                NULL AS mobileno,
                NULL AS guardian_phone,
                NULL AS student_is_active,
                CONCAT_WS(' ', staff.name, staff.surname) AS teacher_name,
                staff.contact_no AS teacher_phone,
                staff.employee_id AS emp_id,
                NULL AS stu_id,
                staff.id AS staff_id
            FROM libarary_members
            INNER JOIN staff ON libarary_members.member_id = staff.id
            WHERE LOWER(TRIM(CAST(libarary_members.member_type AS CHAR))) = 'teacher'
            ORDER BY lib_member_id DESC
        ";
        return $this->db->query($query)->result_array();
    }

    /**
     * All students in current session (may lack libarary_members row — issue links can break until registered).
     */
    private function getAllMembersAllSessionStudents()
    {
        $query = "
            SELECT 
                IFNULL(libarary_members.id, 0) AS lib_member_id,
                IFNULL(libarary_members.library_card_no, '') AS library_card_no,
                'student' AS member_type,
                students.admission_no,
                students.firstname,
                students.middlename,
                students.lastname,
                students.father_name,
                students.dob,
                students.mobileno,
                students.guardian_phone,
                students.is_active AS student_is_active,
                NULL AS teacher_name,
                NULL AS teacher_phone,
                NULL AS emp_id,
                students.id AS stu_id,
                NULL AS staff_id
            FROM students
            INNER JOIN student_session ON student_session.student_id = students.id
            LEFT JOIN libarary_members ON libarary_members.member_id = students.id AND libarary_members.member_type = 'student'
            WHERE student_session.session_id = " . (int)$this->current_session . "
            AND (student_session.is_leave IS NULL OR student_session.is_leave = 0)
    
            UNION
    
            SELECT 
                libarary_members.id AS lib_member_id,
                libarary_members.library_card_no,
                libarary_members.member_type,
                NULL AS admission_no,
                NULL AS firstname,
                NULL AS middlename,
                NULL AS lastname,
                NULL AS father_name,
                NULL AS dob,
                NULL AS mobileno,
                NULL AS guardian_phone,
                NULL AS student_is_active,
                CONCAT_WS(' ', staff.name, staff.surname) AS teacher_name,
                staff.contact_no AS teacher_phone,
                staff.employee_id AS emp_id,
                NULL AS stu_id,
                staff.id AS staff_id
            FROM libarary_members
            INNER JOIN staff ON libarary_members.member_id = staff.id
            WHERE libarary_members.member_type = 'teacher'
            ORDER BY lib_member_id DESC
        ";
        return $this->db->query($query)->result_array();
    }
    
    
    public function getBySectionProgramClass($section_id, $program_id, $class_id, $subject_group_id = null)
    {
        if ($this->getLibraryStudentMemberListMode() === 'all_session_students') {
            return $this->getBySectionProgramClassAllSessionStudents($section_id, $program_id, $class_id, $subject_group_id);
        }
        return $this->getBySectionProgramClassRegisteredOnly($section_id, $program_id, $class_id, $subject_group_id);
    }

    /**
     * Optional library member filter: students assigned to a subject group or enrolled
     * at a class_section + program linked to that group for the current session.
     *
     * @param int|null $subject_group_id
     */
    private function applyLibraryMemberSubjectGroupFilter($subject_group_id)
    {
        $subject_group_id = (int) $subject_group_id;
        if ($subject_group_id < 1) {
            return;
        }

        $sid = (int) $this->current_session;
        $parts = array();

        if ($this->db->table_exists('student_subject_groups')) {
            $parts[] = 'EXISTS (
                SELECT 1 FROM student_subject_groups ssg
                WHERE ssg.student_session_id = student_session.id
                AND ssg.subject_group_id = ' . $subject_group_id . '
            )';
        }

        $parts[] = 'EXISTS (
            SELECT 1 FROM subject_group_class_sections sgcs
            INNER JOIN class_sections cs ON cs.id = sgcs.class_section_id
            WHERE sgcs.subject_group_id = ' . $subject_group_id . '
            AND sgcs.session_id = ' . $sid . '
            AND cs.class_id = student_session.class_id
            AND cs.section_id = student_session.section_id
            AND (sgcs.program_id IS NULL OR sgcs.program_id = 0 OR sgcs.program_id = student_session.program_id)
        )';

        $this->db->where('(' . implode(' OR ', $parts) . ')', null, false);
    }

    private function getBySectionProgramClassRegisteredOnly($section_id, $program_id, $class_id, $subject_group_id = null)
    {
        $sid = (int) $this->current_session;
        $this->db->select('libarary_members.id AS lib_member_id, libarary_members.library_card_no, "student" AS member_type, students.admission_no, students.firstname, students.middlename, students.lastname, students.father_name, students.dob, students.mobileno, students.guardian_phone, students.is_active AS student_is_active, NULL AS teacher_name, NULL AS teacher_phone, NULL AS emp_id, students.id AS stu_id, NULL AS staff_id', false);
        $this->db->from('libarary_members');
        $this->db->join('students', 'libarary_members.member_id = students.id', 'inner');
        $this->db->where("LOWER(TRIM(CAST(libarary_members.member_type AS CHAR))) = 'student'", null, false);
        $this->db->join('student_session', 'student_session.student_id = students.id AND student_session.session_id = ' . $sid, 'inner');

        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }
        if (!empty($program_id)) {
            $this->db->where('student_session.program_id', $program_id);
        }
        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }

        $this->applyLibraryMemberSubjectGroupFilter($subject_group_id);

        $this->db->order_by('students.id', 'ASC');
        return $this->db->get()->result_array();
    }

    private function getBySectionProgramClassAllSessionStudents($section_id, $program_id, $class_id, $subject_group_id = null)
    {
        $this->db->select('IFNULL(libarary_members.id, 0) AS lib_member_id, IFNULL(libarary_members.library_card_no, "") AS library_card_no, "student" AS member_type, students.admission_no, students.firstname, students.middlename, students.lastname, students.father_name, students.dob, students.mobileno, students.guardian_phone, students.is_active AS student_is_active, NULL AS teacher_name, NULL AS teacher_phone, NULL AS emp_id, students.id AS stu_id, NULL AS staff_id', false);
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
        $this->db->join('libarary_members', 'libarary_members.member_id = students.id AND libarary_members.member_type = "student"', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('(student_session.is_leave IS NULL OR student_session.is_leave = 0)', null, false);

        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }
        if (!empty($program_id)) {
            $this->db->where('student_session.program_id', $program_id);
        }
        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }

        $this->applyLibraryMemberSubjectGroupFilter($subject_group_id);

        $this->db->order_by('students.id', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Library members who are staff (teacher), optionally filtered by staff role.
     * Uses the same staff join rules as getAllMembersRegisteredOnly() (no GROUP BY).
     *
     * @param int $role_id 0 = any role
     * @return array
     */
    public function getLibraryStaffMembersByRole($role_id = 0)
    {
        $role_id = (int) $role_id;
        $role_sql = '';
        if ($role_id > 0) {
            $role_sql = ' AND EXISTS (
                SELECT 1 FROM staff_roles sr_f
                WHERE sr_f.staff_id = staff.id AND sr_f.role_id = ' . (int) $role_id . '
            )';
        }

        $sql = "
            SELECT
                libarary_members.id AS lib_member_id,
                libarary_members.library_card_no,
                libarary_members.member_type,
                NULL AS admission_no,
                NULL AS firstname,
                NULL AS middlename,
                NULL AS lastname,
                NULL AS mobileno,
                NULL AS guardian_phone,
                CONCAT_WS(' ', staff.name, staff.surname) AS teacher_name,
                staff.contact_no AS teacher_phone,
                staff.employee_id AS emp_id,
                NULL AS stu_id,
                staff.id AS staff_id,
                (
                    SELECT GROUP_CONCAT(DISTINCT rsub.name ORDER BY rsub.name SEPARATOR ', ')
                    FROM staff_roles srsub
                    INNER JOIN roles rsub ON rsub.id = srsub.role_id
                    WHERE srsub.staff_id = staff.id
                ) AS role_name
            FROM libarary_members
            INNER JOIN staff ON libarary_members.member_id = staff.id
            WHERE (
                LOWER(TRIM(CAST(libarary_members.member_type AS CHAR))) = 'teacher'
                OR LOWER(TRIM(CAST(libarary_members.member_type AS CHAR))) = 'staff'
            )
            " . $role_sql . '
            ORDER BY lib_member_id DESC
        ';

        return $this->db->query($sql)->result_array();
    }


}
