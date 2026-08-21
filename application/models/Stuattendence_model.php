<?php








if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}








class Stuattendence_model extends MY_Model {








    public function __construct() {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date = $this->setting_model->getDateYmd();
    }
















    public function batch_insert($data)
    {
        $this->db->insert_batch('student_attendences', $data);
    }








    public function get($id = null) {
        $this->db->select()->from('student_attendences');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }








    public function onlineattendence($data) {








        $this->db->where('student_session_id', $data['student_session_id']);
        $this->db->where('date', $data['date']);
        $q = $this->db->get('student_attendences');








        if ($q->num_rows() == 0) {
            $this->db->insert('student_attendences', $data);
            return ($this->db->affected_rows() != 1) ? false : true;
        }
        return false;
    }








    public function add($insert_array,$update_array) {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        if (!empty($insert_array)) {








            $this->db->insert_batch('student_attendences', $insert_array);
        }
        if (!empty($update_array)) {
            $this->db->update_batch('student_attendences', $update_array, 'id');
        }
        $this->db->trans_complete();








        if ($this->db->trans_status() === false) {








            $this->db->trans_rollback();
            return false;
        } else {








            $this->db->trans_commit();
            return true;
        }
    }








    public function searchAttendenceClassSection($class_id, $section_id, $date) {
        $sql = "SELECT student_sessions.attendence_id, student_sessions.attendence_dt,
                students.firstname, students.middlename, students.lastname,
                student_sessions.date, student_sessions.remark, student_sessions.biometric_attendence,
                students.roll_no, students.admission_no, students.id as std_id, students.lastname,
                student_sessions.attendence_type_id, student_sessions.id as student_session_id,
                attendence_type.type as `att_type`, attendence_type.key_value as `key`
                FROM students,
                (SELECT student_session.id, student_session.student_id,
                 IFNULL(student_attendences.date, 'xxx') as date,
                 IFNULL(student_attendences.created_at, 'xxx') as attendence_dt,
                 student_attendences.remark, student_attendences.biometric_attendence,
                 IFNULL(student_attendences.id, 0) as attendence_id,
                 student_attendences.attendence_type_id
                 FROM `student_session`
                 LEFT JOIN student_attendences ON student_attendences.student_session_id=student_session.id  
                 AND student_attendences.date=" . $this->db->escape($date) . "
                 WHERE student_session.session_id=" . $this->db->escape($this->current_session) . "
                 AND student_session.class_id=" . $this->db->escape($class_id) . "
                 AND student_session.section_id=" . $this->db->escape($section_id) . "
                 AND student_session.is_active = 'yes'
                ) as student_sessions  
                LEFT JOIN attendence_type ON attendence_type.id=student_sessions.attendence_type_id
                WHERE student_sessions.student_id = students.id
                ORDER BY students.id DESC";
   
        $query = $this->db->query($sql);
        return $query->result_array();
    }








    public function searchAttendenceReport($class_id, $section_id, $date) {
        $sql = "SELECT
                student_sessions.attendence_id,
                students.firstname,
                students.middlename,
                student_sessions.date,
                student_sessions.remark,
                students.roll_no,
                students.admission_no,
                students.lastname,
                student_sessions.attendence_type_id,
                student_sessions.id as student_session_id,
                attendence_type.type as `att_type`,
                attendence_type.key_value as `key`
            FROM students,
            (
                SELECT
                    student_session.id,
                    student_session.student_id,
                    IFNULL(student_attendences.date, 'xxx') as date,
                    student_attendences.remark,
                    IFNULL(student_attendences.id, 0) as attendence_id,
                    student_attendences.attendence_type_id,
                    student_session.is_active
                FROM `student_session`
                LEFT JOIN student_attendences ON
                    student_attendences.student_session_id = student_session.id AND
                    student_attendences.date = " . $this->db->escape($date) . "
                WHERE
                    student_session.session_id = " . $this->db->escape($this->current_session) . " AND
                    student_session.class_id = " . $this->db->escape($class_id) . " AND
                    student_session.section_id = " . $this->db->escape($section_id) . " AND
                    student_session.is_active = 'yes'
            ) as student_sessions  
            LEFT JOIN attendence_type ON
                attendence_type.id = student_sessions.attendence_type_id
            WHERE
                student_sessions.student_id = students.id AND
                students.is_active = 'yes'
            ORDER BY students.roll_no ASC";
   
        $query = $this->db->query($sql);
       
        // Add error handling
        if (!$query) {
            log_message('error', 'Database error in searchAttendenceReport: ' . $this->db->error()['message']);
            return [];
        }
       
        return $query->result_array();
    }




    public function searchAttendenceClassSectionPrepare($class_id, $section_id, $date) {
        $query = $this->db->query("select student_sessions.attendence_id,student_sessions.remark,students.id as std_id,students.firstname,students.middlename,students.admission_no,student_sessions.date,students.roll_no,students.lastname,student_sessions.attendence_type_id,student_sessions.id as student_session_id from students ,(SELECT student_session.id,student_session.student_id ,IFNULL(student_attendences.date, 'xxx') as date,student_attendences.remark,IFNULL(student_attendences.id, 0) as attendence_id,student_attendences.attendence_type_id FROM `student_session` RIGHT JOIN student_attendences ON student_attendences.student_session_id=student_session.id  and student_attendences.date=" . $this->db->escape($date) . " where  student_session.session_id=" . $this->db->escape($this->current_session) . " and student_session.class_id=" . $this->db->escape($class_id) . " and student_session.section_id=" . $this->db->escape($section_id) . ") as student_sessions where student_sessions.student_id=students.id ");
        return $query->result_array();
    }








    public function count_attendance_obj($month, $year, $student_id, $attendance_type = 1) {








        $query = $this->db->select('count(*) as attendence')->join("student_session", "student_attendences.student_session_id = student_session.id")->where(array('student_attendences.student_session_id' => $student_id, 'month(date)' => $month, 'year(date)' => $year, 'student_attendences.attendence_type_id' => $attendance_type))->get("student_attendences");








        return $query->row()->attendence;
    }








    public function attendanceYearCount() {
        $current_year = date('Y');
        $years = [];
        for ($i = $current_year - 5; $i <= $current_year + 1; $i++) {
            $years[] = ['year' => $i];
        }
        return $years;
    }








    public function getTodayDayAttendance($total_student) {








        $query = $this->db->query("SELECT
            concat(round((sum( case when `attendence_type_id`=1 then 1 else 0 end)*100/" . $total_student . "),2),'%') as present, concat(round((sum( case when `attendence_type_id`=3 then 1 else 0 end)*100/" . $total_student . "),2),'%') as late,
            concat(round((sum( case when `attendence_type_id`=4 then 1 else 0 end)*100/" . $total_student . "),2),'%') as absent,concat(round((sum( case when `attendence_type_id`=6 then 1 else 0 end)*100/" . $total_student . "),2),'%') as half_day,sum( case when `attendence_type_id`=1 then 1 else 0 end) as total_present,sum( case when `attendence_type_id`=3 then 1 else 0 end) as total_late,sum( case when `attendence_type_id`=4 then 1 else 0 end) as total_absent,sum( case when `attendence_type_id`=6 then 1 else 0 end) as total_half_day FROM `student_attendences` inner join student_session on student_attendences.student_session_id=student_session.id where date_format(date,'%Y-%m-%d')='" . date('Y-m-d') . "' and student_session.session_id='" . $this->current_session . "'");
        return $query->row_array();
    }
 


public function student_attendences($filters = [], $unused_date_condition = null)
{
    // Required filters
    $class_id    = isset($filters['class_id']) ? (int)$filters['class_id'] : 0;
    $section_id  = isset($filters['section_id']) ? (int)$filters['section_id'] : 0;
    $att_type_id = isset($filters['attendance_type_id']) ? (int)$filters['attendance_type_id'] : 0;
    $date_from   = isset($filters['date_from']) ? $filters['date_from'] : null;
    $date_to     = isset($filters['date_to']) ? $filters['date_to'] : null;

    // Optional
    $program_id  = isset($filters['program_id']) && $filters['program_id'] !== '' ? (int)$filters['program_id'] : null;

    if (!$class_id || !$section_id || !$att_type_id || !$date_from || !$date_to) {
        return [];
    }

    // ---- Subquery: count matching attendance rows per student_session ----
    $this->db->reset_query();
    $this->db->select('ss.id AS student_session_id, COUNT(sa.id) AS total_type', false);
    $this->db->from('student_session ss');
    $this->db->join('students s', 's.id = ss.student_id');

    // IMPORTANT: INNER JOIN so only matching attendance types appear
    $this->db->join(
        'student_attendences sa',
        "sa.student_session_id = ss.id
         AND sa.attendence_type_id = ".$this->db->escape($att_type_id)."
         AND sa.date >= ".$this->db->escape($date_from)."
         AND sa.date <= ".$this->db->escape($date_to),
        'inner', // <-- changed from left to inner
        false
    );

    if (!empty($program_id)) {
        $this->db->join(
            'class_sections cs',
            'cs.class_id = ss.class_id AND cs.section_id = ss.section_id',
            'inner'
        );
        $this->db->where('cs.program_id', $program_id);
    }

    $this->db->where('ss.session_id', $this->current_session);
    $this->db->where('ss.class_id', $class_id);
    $this->db->where('ss.section_id', $section_id);
    $this->db->where('s.is_active', 'yes');
    $this->db->group_by('ss.id');

    $sub_sql = $this->db->get_compiled_select();

    // ---- Outer query: join aggregated counts to fetch student details ----
    $this->db->reset_query();
    $this->db->select("
        c.id   AS class_id,
        c.class,
        sec.id AS section_id,
        sec.section,
        s.id,
        s.admission_no,
        s.roll_no,
        s.admission_date,
        s.firstname, s.middlename, s.lastname,
        s.image, s.mobileno, s.email, s.state, s.city, s.pincode, s.religion,
        s.dob, s.current_address, s.adhar_no, s.samagra_id, s.bank_account_no,
        s.bank_name, s.ifsc_code, s.father_name, s.guardian_name, s.guardian_relation,
        s.guardian_phone, s.guardian_address, s.is_active, s.created_at, s.updated_at,
        s.gender, s.rte,
        ss.session_id,
        agg.total_type
    ");
    $this->db->from('student_session ss');
    $this->db->join('students s', 's.id = ss.student_id');
    $this->db->join('classes c', 'c.id = ss.class_id');
    $this->db->join('sections sec', 'sec.id = ss.section_id');

    if (!empty($program_id)) {
        $this->db->join(
            'class_sections cs',
            'cs.class_id = ss.class_id AND cs.section_id = ss.section_id',
            'inner'
        );
        $this->db->where('cs.program_id', $program_id);
    }

    $this->db->join("($sub_sql) agg", 'agg.student_session_id = ss.id', 'inner');
    $this->db->where('ss.session_id', $this->current_session);
    $this->db->where('ss.class_id', $class_id);
    $this->db->where('ss.section_id', $section_id);
    $this->db->where('s.is_active', 'yes');
    $this->db->order_by('s.id', 'ASC');

    $query = $this->db->get();
    if (!$query) {
        log_message('error', 'DB error in student_attendences: '.$this->db->error()['message']);
        return [];
    }
    return $query->result_array();
}









    public function checkholidatbydate($date) {
        $where['attendence_type_id'] = '5';
        $where['date'] = date('Y-m-d', strtotime($date));
        $query = $this->db->select('count(*) as day ')->where($where)->get('student_attendences')->row_array();
        return $query['day'];
    }








    public function biometric_attlog($limit = null, $offset = NULL) {
        return $this->db->select('student_attendences.*,CONCAT_WS(students.firstname," ",students.lastname) as name,students.firstname,students.middlename,students.lastname,students.roll_no')->from('student_attendences')->join('student_session', 'student_session.id=student_attendences.student_session_id', 'left')->join('students', 'student_session.student_id=students.id', 'left')->where('biometric_attendence', 1)->limit($limit, $offset)->get()->result_array();
    }








    public function biometric_attlogcount() {
        $count = $this->db->select('count(*) as total')->from('student_attendences')->where('biometric_attendence', 1)->get()->row_array();
        return $count['total'];
    }








    public function get_attendancebydate($date) {
        $sql = 'SELECT classes.class as class_name,sections.section as section_name, SUM(CASE WHEN `attendence_type_id` = 1 THEN 1 ELSE 0 END) AS "present",SUM(CASE WHEN `attendence_type_id` = 2 THEN 1 ELSE 0 END) AS "excuse",SUM(CASE WHEN `attendence_type_id` = 4 THEN 1 ELSE 0 END) AS "absent",SUM(CASE WHEN `attendence_type_id` = 3 THEN 1 ELSE 0 END) AS "late",SUM(CASE WHEN `attendence_type_id` = 6 THEN 1 ELSE 0 END) AS "half_day" FROM `student_attendences` join student_session on student_attendences.student_session_id=student_session.id inner join class_sections on (student_session.class_id=class_sections.class_id and student_session.section_id=class_sections.section_id) inner join classes on classes.id=class_sections.class_id inner join sections on sections.id=class_sections.section_id WHERE 1  and `student_session`.`session_id`=' . $this->current_session . ' ' . $date . ' group by class_sections.id';








        $query = $this->db->query($sql);
        $count_studentattendance = $query->result();

        return $count_studentattendance;
    }



    public function studentattendance($date, $student_session_id) {

        $sql = "select student_attendences.*,student_session.student_id,attendence_type.type as `att_type`,attendence_type.key_value as `key` from student_attendences join student_session ON student_session.id=student_attendences.student_session_id left join attendence_type ON attendence_type.id = student_attendences.attendence_type_id where student_attendences.student_session_id = $student_session_id and student_attendences.date =".$this->db->escape($date);
     
        $query = $this->db->query($sql);
        return $query->row_array();
    }



    public function studentattendancecount($year, $student_id, $att_type)
    {
        $query = $this->db->select('count(*) as attendence')
        ->join('student_session','student_session.id = student_attendences.student_session_id','left')
        ->where('student_attendences.student_session_id',$student_id)
        ->where('year(date)',$year)
        ->where('student_attendences.attendence_type_id',$att_type)
        ->get("student_attendences");
        return $query->row()->attendence;
    }



    public function student_attendence_bw_date($date_from, $date_to, $student_session_id)
    {
        $query = $this->db->select('student_attendences.*,attendence_type.type as `att_type`,attendence_type.key_value as `key`')
        ->join('student_session','student_session.id = student_attendences.student_session_id')
        ->join('attendence_type','attendence_type.id = student_attendences.attendence_type_id')
        ->where('student_attendences.student_session_id',$student_session_id)
        ->where("date BETWEEN '{$date_from}' AND '{$date_to}'")
        ->get("student_attendences");
        return $query->result();
    }



}





















