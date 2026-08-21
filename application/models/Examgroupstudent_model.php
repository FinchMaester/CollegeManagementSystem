<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Examgroupstudent_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function searchExamGroupStudentAttempted1($exam_group_id, $class_id, $batch_id)
    {
        $sql   = "select IFNULL(exam_group_students.id, 0) as `exam_group_student_id`,students.admission_no , students.id as `student_id`, students.roll_no,students.admission_date,students.firstname, students.middlename, students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,students.dob ,students.current_address,    students.permanent_address,students.category_id, IFNULL(categories.category, '') as `category`,   students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,`classes`.`class`,students.guardian_address,students.is_active,`students`.`father_name`,`students`.`gender`,students.batch_id,batch.name,student_session.* from student_session INNER join students on students.id=student_session.student_id JOIN `classes` ON `student_session`.`class_id` = `classes`.`id` LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id` inner join batch on students.batch_id=batch.id INNER JOIN exam_group_students on exam_group_students.exam_group_id=" . $this->db->escape($exam_group_id) . " and exam_group_students.student_id =students.id WHERE student_session.class_id=" . $this->db->escape($class_id) . " and students.batch_id=" . $this->db->escape($batch_id) . " GROUP BY students.id ORDER BY students.id asc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function searchExamGroupStudentAttempted($exam_group_id, $exam_id, $class_id, $section_id, $session_id)
    {
        $sql   = "select IFNULL(exam_group_students.id, 0) as `exam_group_student_id`,students.admission_no , students.id as `student_id`, students.roll_no,students.admission_date,students.firstname,students.middlename, students.lastname,students.image, students.mobileno, students.email ,students.state , students.city , students.pincode , students.religion,students.dob ,students.current_address, students.permanent_address,students.category_id, IFNULL(categories.category, '') as `category`, students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,`classes`.`class`,students.guardian_address,students.is_active,`students`.`father_name`,`students`.`gender`,student_session.* from student_session INNER join students on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " and student_session.section_ids=" . $this->db->escape($section_id) . " and student_session.session_id=" . $this->db->escape($session_id) . " JOIN `classes` ON `student_session`.`class_id` = `classes`.`id` LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id` INNER JOIN exam_group_students on exam_group_students.exam_group_id=" . $this->db->escape($exam_group_id) . " and exam_group_students.student_id =students.id ORDER BY students.id asc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    public function searchExamStudentsByExam($exam_id)
    {
        $sql = "SELECT
                    egcbes.id AS exam_group_class_batch_exam_student_id,
                    egcbes.rank,
                    egcbes.roll_no AS exam_roll_no,
                    egcbes.teacher_remark,
    
                    s.admission_no,
                    s.id AS student_id,
                    s.symbol_no,                 /* <-- symbol number */
                    s.roll_no,                   /* <-- roll comes from students */
                    s.admission_date,
                    s.firstname, s.middlename, s.lastname,
                    s.image, s.mobileno, s.email, s.state, s.city, s.pincode, s.religion,
                    s.dob, s.current_address, s.permanent_address, s.category_id,
                    IFNULL(categories.category,'') AS category,
                    s.adhar_no, s.samagra_id, s.bank_account_no, s.bank_name, s.ifsc_code,
                    s.guardian_name, s.guardian_relation, s.guardian_phone, s.guardian_email,
                    classes.class,
                    s.guardian_address, s.is_active, s.father_name, s.gender,
                    sections.section AS student_section
                FROM exam_group_class_batch_exam_students egcbes
                INNER JOIN student_session ss ON ss.id = egcbes.student_session_id
                INNER JOIN students s         ON s.id  = ss.student_id
                INNER JOIN classes            ON ss.class_id   = classes.id
                INNER JOIN sections           ON ss.section_id = sections.id
                LEFT  JOIN categories         ON s.category_id = categories.id
                WHERE egcbes.exam_group_class_batch_exam_id = " . $this->db->escape($exam_id) . "
                  AND s.is_active = 'yes'
                ORDER BY s.roll_no ASC";       /* <-- order by students.roll_no */
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    

    public function searchExamStudents($exam_group_id, $exam_id, $class_id, $section_id, $session_id)
    {
        $sql = "SELECT
                    egcbes.id AS exam_group_class_batch_exam_student_id,
                    egcbes.rank,
                    egcbes.roll_no AS exam_roll_no,
    
                    s.admission_no,
                    s.id AS student_id,
                    s.symbol_no,                 /* <-- symbol number */
                    s.roll_no,                   /* <-- roll from students */
                    s.admission_date,
                    s.firstname, s.middlename, s.lastname,
                    s.image, s.mobileno, s.email, s.state, s.city, s.pincode, s.religion,
                    s.dob, s.current_address, s.permanent_address, s.category_id,
                    IFNULL(categories.category,'') AS category,
                    s.adhar_no, s.samagra_id, s.bank_account_no, s.bank_name, s.ifsc_code,
                    s.guardian_name, s.guardian_relation, s.guardian_phone,
                    classes.class,
                    s.guardian_address, s.is_active, s.father_name, s.gender
                FROM exam_group_class_batch_exam_students egcbes
                INNER JOIN student_session ss ON ss.id = egcbes.student_session_id
                INNER JOIN students s         ON s.id  = ss.student_id
                INNER JOIN classes            ON ss.class_id   = classes.id
                LEFT  JOIN categories         ON s.category_id = categories.id
                WHERE egcbes.exam_group_class_batch_exam_id = " . $this->db->escape($exam_id) . "
                  AND s.is_active   = 'yes'
                  AND ss.class_id   = " . $this->db->escape($class_id) . "
                  AND ss.section_id = " . $this->db->escape($section_id) . "
                  AND ss.session_id = " . $this->db->escape($session_id) . "
                ORDER BY s.roll_no ASC";       /* <-- order by students.roll_no */
        $query = $this->db->query($sql);
        return $query->result();
    }
    
    

    public function searchExamGroupStudents($exam_group_id, $class_id, $section_id, $session_id)
    {
        $sql   = "select IFNULL(exam_group_students.id, 0) as `exam_group_student_id`,students.admission_no , students.id as `student_id`, students.roll_no,students.admission_date,students.firstname,students.middlename, students.lastname,students.image, students.mobileno, students.email ,students.state , students.city , students.pincode , students.religion,students.dob ,students.current_address, students.permanent_address,students.category_id, IFNULL(categories.category, '') as `category`, students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,`classes`.`class`,students.guardian_address,students.is_active,`students`.`father_name`,`students`.`gender`,student_session.* from student_session INNER join students on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " and student_session.section_id=" . $this->db->escape($section_id) . " and student_session.session_id=" . $this->db->escape($session_id) . " JOIN `classes` ON `student_session`.`class_id` = `classes`.`id` LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id` LEFT JOIN exam_group_students on exam_group_students.exam_group_id=" . $this->db->escape($exam_group_id) . " and exam_group_students.student_id =students.id ORDER BY students.id asc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function add($data_insert, $data_delete, $exam_group_id)
    {
        $this->db->trans_begin();
        if (!empty($data_insert)) {
            foreach ($data_insert as $student_key => $student_value) {
                $this->db->where('exam_group_id', $student_value['exam_group_id']);
                $this->db->where('student_id', $student_value['student_id']);
                $q = $this->db->get('exam_group_students');
                if ($q->num_rows() == 0) {
                    $this->db->insert('exam_group_students', $data_insert[$student_key]);
                }
            }
        }
        if (!empty($data_delete)) {
            $this->db->where('exam_group_id', $exam_group_id);
            $this->db->where_in('student_id', $data_delete);
            $this->db->delete('exam_group_students');
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * Get linked students for marks entry. Filters by class/section/session; when program_id given, matches that program or NULL.
     */
    public function examGroupSubjectResult($exam_subject_id, $class_id, $section_id, $session_id, $program_id = null)
    {
        $sql = "SELECT IFNULL(exam_group_exam_results.id, 0) as exam_group_exam_result_id,IFNULL(exam_group_exam_results.attendence,'') as `exam_group_exam_result_attendance`,IFNULL(exam_group_exam_results.get_marks,'') as `exam_group_exam_result_get_marks`,IFNULL(exam_group_exam_results.note,'') as `exam_group_exam_result_note`,exam_group_class_batch_exam_students.id as `exam_group_class_batch_exam_students_id`,exam_group_class_batch_exam_students.roll_no as `exam_roll_no`,exam_group_class_batch_exam_subjects.*,subjects.name,subjects.code,subjects.type,students.admission_no , students.roll_no,students.id as `student_id`, students.roll_no,students.admission_date,students.firstname,students.middlename, students.lastname,students.image, students.mobileno, students.email ,students.state , students.city , students.pincode , students.religion,students.dob ,students.current_address, students.permanent_address,students.category_id, IFNULL(categories.category, '') as `category`, students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,`students`.`father_name`,`students`.`gender`,exam_group_class_batch_exams.use_exam_roll_no FROM `exam_group_class_batch_exam_subjects` INNER JOIN exam_group_class_batch_exams on exam_group_class_batch_exams.id=exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id INNER JOIN subjects on subjects.id=exam_group_class_batch_exam_subjects.subject_id INNER JOIN exam_group_class_batch_exam_students on exam_group_class_batch_exam_students.exam_group_class_batch_exam_id=exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id INNER join student_session on student_session.id=exam_group_class_batch_exam_students.student_session_id LEFT join exam_group_exam_results on exam_group_exam_results.exam_group_class_batch_exam_subject_id=exam_group_class_batch_exam_subjects.id and exam_group_exam_results.exam_group_class_batch_exam_student_id=exam_group_class_batch_exam_students.id  INNER JOIN students on students.id=student_session.student_id LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id`  WHERE students.is_active='yes' AND exam_group_class_batch_exam_subjects.id=" . $this->db->escape($exam_subject_id) . " and student_session.class_id=" . $this->db->escape($class_id) . " and student_session.section_id=" . $this->db->escape($section_id) . " and student_session.session_id=" . $this->db->escape($session_id);
        if ($program_id !== null && $program_id !== '') {
            $sql .= " and (student_session.program_id=" . $this->db->escape($program_id) . " OR student_session.program_id IS NULL)";
        }
        $sql .= " ORDER BY students.id asc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * Fallback: get marks-entry list by first resolving linked student_session IDs for this exam+level, then same SELECT as examGroupSubjectResult with IN clause. Use when main query returns empty but getLinkedLevelsByExam has rows.
     */
    public function examGroupSubjectResultByLinkedLevel($exam_subject_id, $exam_group_class_batch_exam_id, $class_id, $section_id, $session_id)
    {
        $this->db->select('exam_group_class_batch_exam_students.student_session_id');
        $this->db->from('exam_group_class_batch_exam_students');
        $this->db->join('student_session', 'student_session.id = exam_group_class_batch_exam_students.student_session_id');
        $this->db->where('exam_group_class_batch_exam_students.exam_group_class_batch_exam_id', (int) $exam_group_class_batch_exam_id);
        $this->db->where('student_session.class_id', (int) $class_id);
        $this->db->where('student_session.section_id', (int) $section_id);
        $this->db->where('student_session.session_id', $session_id);
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $session_ids = array();
        foreach ($q->result_array() as $row) {
            $session_ids[] = (int) $row['student_session_id'];
        }
        $session_ids = array_unique($session_ids);
        if (empty($session_ids)) {
            return array();
        }
        $ids_list = implode(',', array_map('intval', $session_ids));
        $sql = "SELECT IFNULL(exam_group_exam_results.id, 0) as exam_group_exam_result_id, IFNULL(exam_group_exam_results.attendence,'') as exam_group_exam_result_attendance, IFNULL(exam_group_exam_results.get_marks,'') as exam_group_exam_result_get_marks, IFNULL(exam_group_exam_results.note,'') as exam_group_exam_result_note, exam_group_class_batch_exam_students.id as exam_group_class_batch_exam_students_id, exam_group_class_batch_exam_students.roll_no as exam_roll_no, exam_group_class_batch_exam_subjects.*, subjects.name, subjects.code, subjects.type, students.admission_no, students.roll_no, students.id as student_id, students.admission_date, students.firstname, students.middlename, students.lastname, students.image, students.mobileno, students.email, students.state, students.city, students.pincode, students.religion, students.dob, students.current_address, students.permanent_address, students.category_id, IFNULL(categories.category, '') as category, students.adhar_no, students.samagra_id, students.bank_account_no, students.bank_name, students.ifsc_code, students.guardian_name, students.guardian_relation, students.guardian_phone, students.guardian_address, students.is_active, students.father_name, students.gender, exam_group_class_batch_exams.use_exam_roll_no FROM exam_group_class_batch_exam_subjects INNER JOIN exam_group_class_batch_exams ON exam_group_class_batch_exams.id = exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id INNER JOIN subjects ON subjects.id = exam_group_class_batch_exam_subjects.subject_id INNER JOIN exam_group_class_batch_exam_students ON exam_group_class_batch_exam_students.exam_group_class_batch_exam_id = exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id AND exam_group_class_batch_exam_students.student_session_id IN (" . $ids_list . ") INNER JOIN student_session ON student_session.id = exam_group_class_batch_exam_students.student_session_id LEFT JOIN exam_group_exam_results ON exam_group_exam_results.exam_group_class_batch_exam_subject_id = exam_group_class_batch_exam_subjects.id AND exam_group_exam_results.exam_group_class_batch_exam_student_id = exam_group_class_batch_exam_students.id INNER JOIN students ON students.id = student_session.student_id LEFT JOIN categories ON students.category_id = categories.id WHERE students.is_active='yes' AND exam_group_class_batch_exam_subjects.id=" . (int) $exam_subject_id . " ORDER BY students.id ASC";
        $query = $this->db->query($sql);
        return $query ? $query->result_array() : array();
    }

    /**
     * Last resort: get marks-entry list for ALL students linked to this exam in current session (no class/section filter).
     * Only active students (is_active='yes') are returned.
     */
    public function examGroupSubjectResultAllLinked($exam_subject_id, $exam_group_class_batch_exam_id)
    {
        $session_id = $this->current_session;
        if ($session_id === null || $session_id === '') {
            return array();
        }
        $this->db->select('exam_group_class_batch_exam_students.student_session_id');
        $this->db->from('exam_group_class_batch_exam_students');
        $this->db->join('student_session', 'student_session.id = exam_group_class_batch_exam_students.student_session_id');
        $this->db->where('exam_group_class_batch_exam_students.exam_group_class_batch_exam_id', (int) $exam_group_class_batch_exam_id);
        $this->db->where('student_session.session_id', $session_id);
        $q = $this->db->get();
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        $session_ids = array();
        foreach ($q->result_array() as $row) {
            $session_ids[] = (int) $row['student_session_id'];
        }
        $session_ids = array_unique($session_ids);
        if (empty($session_ids)) {
            return array();
        }
        $ids_list = implode(',', array_map('intval', $session_ids));
        $sql = "SELECT IFNULL(exam_group_exam_results.id, 0) as exam_group_exam_result_id, IFNULL(exam_group_exam_results.attendence,'') as exam_group_exam_result_attendance, IFNULL(exam_group_exam_results.get_marks,'') as exam_group_exam_result_get_marks, IFNULL(exam_group_exam_results.note,'') as exam_group_exam_result_note, exam_group_class_batch_exam_students.id as exam_group_class_batch_exam_students_id, exam_group_class_batch_exam_students.roll_no as exam_roll_no, exam_group_class_batch_exam_subjects.*, subjects.name, subjects.code, subjects.type, students.admission_no, students.roll_no, students.id as student_id, students.admission_date, students.firstname, students.middlename, students.lastname, students.image, students.mobileno, students.email, students.state, students.city, students.pincode, students.religion, students.dob, students.current_address, students.permanent_address, students.category_id, IFNULL(categories.category, '') as category, students.adhar_no, students.samagra_id, students.bank_account_no, students.bank_name, students.ifsc_code, students.guardian_name, students.guardian_relation, students.guardian_phone, students.guardian_address, students.is_active, students.father_name, students.gender, exam_group_class_batch_exams.use_exam_roll_no FROM exam_group_class_batch_exam_subjects INNER JOIN exam_group_class_batch_exams ON exam_group_class_batch_exams.id = exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id INNER JOIN subjects ON subjects.id = exam_group_class_batch_exam_subjects.subject_id INNER JOIN exam_group_class_batch_exam_students ON exam_group_class_batch_exam_students.exam_group_class_batch_exam_id = exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id AND exam_group_class_batch_exam_students.student_session_id IN (" . $ids_list . ") INNER JOIN student_session ON student_session.id = exam_group_class_batch_exam_students.student_session_id LEFT JOIN exam_group_exam_results ON exam_group_exam_results.exam_group_class_batch_exam_subject_id = exam_group_class_batch_exam_subjects.id AND exam_group_exam_results.exam_group_class_batch_exam_student_id = exam_group_class_batch_exam_students.id INNER JOIN students ON students.id = student_session.student_id LEFT JOIN categories ON students.category_id = categories.id WHERE students.is_active='yes' AND exam_group_class_batch_exam_subjects.id=" . (int) $exam_subject_id . " ORDER BY students.id ASC";
        $query = $this->db->query($sql);
        return $query ? $query->result_array() : array();
    }

    /**
     * Count students linked to an exam (for hint when marks search returns none).
     */
    public function countLinkedStudentsByExam($exam_group_class_batch_exam_id)
    {
        $this->db->where('exam_group_class_batch_exam_id', (int) $exam_group_class_batch_exam_id);
        return (int) $this->db->count_all_results('exam_group_class_batch_exam_students');
    }

    /**
     * Get distinct (section, program, class) for students linked to this exam (current session).
     * Used to show which Faculty / Program / Level to select in Enter Marks when no match.
     */
    public function getLinkedLevelsByExam($exam_group_class_batch_exam_id)
    {
        $this->db->select('student_session.section_id, sections.section AS section_name, student_session.program_id, IFNULL(programs.title, "") AS program_name, student_session.class_id, classes.class AS class_name');
        $this->db->from('exam_group_class_batch_exam_students');
        $this->db->join('student_session', 'student_session.id = exam_group_class_batch_exam_students.student_session_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('programs', 'programs.id = student_session.program_id', 'left');
        $this->db->where('exam_group_class_batch_exam_students.exam_group_class_batch_exam_id', (int) $exam_group_class_batch_exam_id);
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->group_by(array('student_session.section_id', 'sections.section', 'student_session.program_id', 'programs.title', 'student_session.class_id', 'classes.class'));
        $this->db->order_by('sections.section');
        $this->db->order_by('programs.title');
        $this->db->order_by('classes.class');
        $q = $this->db->get();
        return $q ? $q->result_array() : array();
    }

    public function add_result($insert_array)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        if (!empty($insert_array)) {
            foreach ($insert_array as $student_key => $student_value) {
                $student_value['exam_group_class_batch_exam_subject_id'];
                $student_value['exam_group_class_batch_exam_student_id'];
                $this->db->where('exam_group_class_batch_exam_subject_id', $student_value['exam_group_class_batch_exam_subject_id']);
                $this->db->where('exam_group_class_batch_exam_student_id', $student_value['exam_group_class_batch_exam_student_id']);
                $q = $this->db->get('exam_group_exam_results');
                if ($q->num_rows() > 0) {
                    $update_result = $q->row();
                    $this->db->where('id', $update_result->id);
                    $this->db->update('exam_group_exam_results', $student_value);
                } else {
                    $this->db->insert('exam_group_exam_results', $student_value);
                }
            }
        }

        $this->db->trans_complete(); # Completing transaction

        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            # Everything is Perfect.
            # Committing data to the database.
            $this->db->trans_commit();
            return true;
        }
    }

    public function searchStudentByClassSectionSession($class_id, $section_id, $session_id)
    {
        $sql = "SELECT students.admission_no , students.id as `student_id`, students.roll_no,students.admission_date,students.firstname, students.middlename,students.lastname,students.image, students.mobileno, students.email ,students.state , students.city , students.pincode , students.religion,students.dob ,students.current_address, students.permanent_address,students.category_id, IFNULL(categories.category, '') as `category`, students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,`students`.`father_name`,`students`.`gender` FROM `students` LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id` INNER join student_session on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " and student_session.section_id=" . $this->db->escape($section_id) . " and student_session.session_id=" . $this->db->escape($session_id) . " ORDER BY students.id asc";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function searchStudentExams($student_session_id, $is_active = false, $is_publish = false)
    {
        $inner_sql = "";
        if ($is_active) {
            $inner_sql = "and exam_group_class_batch_exams.is_active=1 ";
        }
        if ($is_publish) {
            $inner_sql .= "and exam_group_class_batch_exams.is_publish=1 ";
        }
        $sql = "SELECT exam_group_class_batch_exam_students.*,exam_group_class_batch_exams.exam_group_id,exam_group_class_batch_exams.exam,exam_group_class_batch_exams.date_from,exam_group_class_batch_exams.date_to,exam_group_class_batch_exams.description,exam_groups.name,exam_groups.exam_type,exam_group_class_batch_exams.passing_percentage FROM `exam_group_class_batch_exam_students` INNER JOIN exam_group_class_batch_exams on exam_group_class_batch_exams.id=exam_group_class_batch_exam_students.exam_group_class_batch_exam_id  INNER JOIN exam_groups on exam_groups.id=exam_group_class_batch_exams.exam_group_id WHERE student_session_id=" . $this->db->escape($student_session_id) . $inner_sql . " ORDER BY id asc";
        $query        = $this->db->query($sql);
        $student_exam = $query->result();
        if (!empty($student_exam)) {
            foreach ($student_exam as $student_exam_key => $student_exam_value) {
                $student_exam_value->exam_result = $this->examresult_model->getStudentExamResults($student_exam_value->exam_group_class_batch_exam_id, $student_exam_value->exam_group_id, $student_exam_value->id, $student_exam_value->student_id);
            }
        }
        return $student_exam;
    }

    public function studentExams($student_session_id)
    {
        $sql = "SELECT exam_group_class_batch_exam_students.*,exam_group_class_batch_exams.id as `exam_group_class_batch_exam_id`,exam_group_class_batch_exams.exam,exam_group_class_batch_exams.description FROM `exam_group_class_batch_exam_students` INNER JOIN exam_group_class_batch_exams on exam_group_class_batch_exam_students.exam_group_class_batch_exam_id=exam_group_class_batch_exams.id WHERE student_session_id=" . $this->db->escape($student_session_id) . " and exam_group_class_batch_exams.is_active=1";
        $query        = $this->db->query($sql);
        $student_exam = $query->result();
        return $student_exam;
    }
    public function updateExamStudent($data)
    {
        $this->db->update_batch('exam_group_class_batch_exam_students', $data, 'id');
    }

    public function getexamresult($student_session_id, $exam_id, $is_active = false, $is_publish = false)
    {
        $inner_sql = "";
        if ($is_active) {
            $inner_sql = "and exam_group_class_batch_exams.is_active=1 ";
        }
        if ($is_publish) {
            $inner_sql .= "and exam_group_class_batch_exams.is_publish=1 ";
        }
        $sql = "SELECT exam_group_class_batch_exam_students.*,exam_group_class_batch_exams.exam_group_id,exam_group_class_batch_exams.exam,exam_group_class_batch_exams.passing_percentage,exam_group_class_batch_exams.date_from,exam_group_class_batch_exams.date_to,exam_group_class_batch_exams.description,exam_groups.name,exam_groups.exam_type FROM `exam_group_class_batch_exam_students` INNER JOIN exam_group_class_batch_exams on exam_group_class_batch_exams.id=exam_group_class_batch_exam_students.exam_group_class_batch_exam_id  INNER JOIN exam_groups on exam_groups.id=exam_group_class_batch_exams.exam_group_id WHERE exam_group_class_batch_exam_students.exam_group_class_batch_exam_id = " . $this->db->escape($exam_id) . " and  student_session_id=" . $this->db->escape($student_session_id) . $inner_sql . " ORDER BY id asc";

        $query        = $this->db->query($sql);
        $student_exam = $query->result();

        if (!empty($student_exam)) {
            foreach ($student_exam as $student_exam_key => $student_exam_value) {
                $student_exam_value->exam_result = $this->examresult_model->getStudentExamResults($student_exam_value->exam_group_class_batch_exam_id, $student_exam_value->exam_group_id, $student_exam_value->id, $student_exam_value->student_id);
            }
        }
        return $student_exam;
    }

}
