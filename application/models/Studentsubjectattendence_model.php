<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentsubjectattendence_model extends CI_Model
{
    public $db_debug_info = null; // Store debug info for view

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
    }

    /**
     * Guarantee name + subject_name on each subject row for report JSON (reportbydate view).
     * Resolves via subject_group_subjects → subjects, then direct subjects.id = st.subject_group_subject_id
     * (same idea as Timetable_model) for legacy/mis-keyed rows, then attendance-scoped join.
     */
    private function ensureSubjectDisplayNames(&$subjects, $class_id = null, $section_id = null)
    {
        if (empty($subjects) || !is_array($subjects)) {
            return;
        }
        foreach ($subjects as $k => $s) {
            if (is_array($s)) {
                $s = (object) $s;
            }
            if (is_object($s) && (!isset($s->id) || (int) $s->id <= 0) && isset($s->subject_timetable_id)) {
                $s->id = $s->subject_timetable_id;
            }
            $subjects[$k] = $s;
        }
        $ids = array();
        foreach ($subjects as $s) {
            if (!is_object($s)) {
                continue;
            }
            $tid = isset($s->id) ? (int) $s->id : 0;
            if ($tid > 0) {
                $ids[] = $tid;
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return;
        }
        $in = implode(',', array_map('intval', $ids));

        // sd = direct subject row where subjects.id matches subject_group_subject_id (some DBs / legacy data)
        $sql = "SELECT st.id AS timetable_id,
                s.id AS subject_id,
                s.name AS name,
                s.code AS code,
                s.type AS type,
                sd.id AS direct_subject_id,
                sd.name AS direct_name,
                sd.code AS direct_code,
                COALESCE(st.time_from, '') AS time_from,
                COALESCE(st.time_to, '') AS time_to
                FROM subject_timetable st
                LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                LEFT JOIN subjects s ON s.id = sgs.subject_id
                LEFT JOIN subjects sd ON sd.id = st.subject_group_subject_id
                WHERE st.id IN (" . $in . ")";
        $rows = $this->db->query($sql)->result();
        $byTid = array();
        foreach ($rows as $r) {
            $byTid[(int) $r->timetable_id] = $r;
        }

        $applyRow = function ($subject, $r) {
            $nm = isset($r->name) ? trim((string) $r->name) : '';
            $cd = isset($r->code) ? trim((string) $r->code) : '';
            $dn = isset($r->direct_name) ? trim((string) $r->direct_name) : '';
            $dc = isset($r->direct_code) ? trim((string) $r->direct_code) : '';

            $pick = '';
            if ($nm !== '' && $nm !== '-') {
                $pick = $nm;
            } elseif ($cd !== '') {
                $pick = $cd;
            } elseif ($dn !== '' && $dn !== '-') {
                $pick = $dn;
            } elseif ($dc !== '') {
                $pick = $dc;
            }

            if ($pick !== '') {
                $subject->name         = $pick;
                $subject->subject_name = $pick;
            }
            $sidUse = 0;
            if (!empty($r->subject_id)) {
                $sidUse = (int) $r->subject_id;
            } elseif (!empty($r->direct_subject_id)) {
                $sidUse = (int) $r->direct_subject_id;
            }
            if ($sidUse > 0) {
                $subject->subject_id = $sidUse;
            }
            if (isset($r->code) && trim((string) $r->code) !== '') {
                $subject->code = $r->code;
            } elseif (isset($r->direct_code) && trim((string) $r->direct_code) !== '') {
                $subject->code = $r->direct_code;
            }
            if (isset($r->type)) {
                $subject->type = $r->type;
            }
            if (isset($r->time_from)) {
                $subject->time_from = $r->time_from;
            }
            if (isset($r->time_to)) {
                $subject->time_to = $r->time_to;
            }
        };

        foreach ($subjects as $subject) {
            if (empty($subject->id)) {
                continue;
            }
            $tid = (int) $subject->id;
            $nm  = isset($subject->name) ? trim((string) $subject->name) : '';
            $bad = ($nm === '' || $nm === '-');
            if (!$bad && isset($subject->subject_name) && trim((string) $subject->subject_name) !== '') {
                $subject->subject_name = trim((string) $subject->subject_name);
                continue;
            }
            if (!$bad && $nm !== '') {
                if (!isset($subject->subject_name) || trim((string) $subject->subject_name) === '') {
                    $subject->subject_name = $nm;
                }
                continue;
            }
            if (isset($byTid[$tid])) {
                $applyRow($subject, $byTid[$tid]);
            }
            $nmAfter = isset($subject->name) ? trim((string) $subject->name) : '';
            if ($nmAfter !== '' && $nmAfter !== '-') {
                continue;
            }
            $sid = isset($subject->subject_id) ? (int) $subject->subject_id : 0;
            if ($sid > 0) {
                $srow = $this->db->query('SELECT id, name, code, type FROM subjects WHERE id = ?', array($sid))->row();
                if ($srow) {
                    $nm2 = isset($srow->name) ? trim((string) $srow->name) : '';
                    $cd2 = isset($srow->code) ? trim((string) $srow->code) : '';
                    $pick = ($nm2 !== '' && $nm2 !== '-') ? $nm2 : (($cd2 !== '') ? $cd2 : '');
                    if ($pick !== '') {
                        $subject->name         = $pick;
                        $subject->subject_name = $pick;
                    }
                }
            }
        }

        // Names from actual attendance rows (same class/section/session as the report)
        $missingAfter = array();
        foreach ($subjects as $sub) {
            if (empty($sub->id)) {
                continue;
            }
            $n = isset($sub->name) ? trim((string) $sub->name) : '';
            if ($n === '' || $n === '-') {
                $missingAfter[] = (int) $sub->id;
            }
        }
        if (!empty($missingAfter) && $class_id !== null && $class_id !== '' && $section_id !== null && $section_id !== '') {
            $missIn = implode(',', array_map('intval', array_unique($missingAfter)));
            $attSql = "SELECT DISTINCT ssa.subject_timetable_id AS timetable_id, subj.id AS subject_id, subj.name, subj.code, subj.type
                FROM student_subject_attendances ssa
                INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                    AND ss.class_id = " . $this->db->escape($class_id) . "
                    AND ss.section_id = " . $this->db->escape($section_id) . "
                    AND ss.session_id = " . $this->db->escape($this->current_session) . "
                INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                INNER JOIN subjects subj ON subj.id = sgs.subject_id
                WHERE ssa.subject_timetable_id IN (" . $missIn . ")
                AND (
                    (subj.name IS NOT NULL AND TRIM(subj.name) <> '' AND TRIM(subj.name) <> '-')
                    OR (subj.code IS NOT NULL AND TRIM(subj.code) <> '')
                )";
            $attRows = $this->db->query($attSql)->result();
            $attMap = array();
            foreach ($attRows as $ar) {
                $attMap[(int) $ar->timetable_id] = $ar;
            }
            foreach ($subjects as $subject) {
                if (empty($subject->id)) {
                    continue;
                }
                $n = isset($subject->name) ? trim((string) $subject->name) : '';
                if (($n === '' || $n === '-') && isset($attMap[(int) $subject->id])) {
                    $ar = $attMap[(int) $subject->id];
                    $nmA = isset($ar->name) ? trim((string) $ar->name) : '';
                    $cdA = isset($ar->code) ? trim((string) $ar->code) : '';
                    $label = ($nmA !== '' && $nmA !== '-') ? $nmA : $cdA;
                    if ($label !== '') {
                        $subject->name         = $label;
                        $subject->subject_name = $label;
                    }
                    $subject->subject_id   = (int) $ar->subject_id;
                    if (!empty($ar->code)) {
                        $subject->code = $ar->code;
                    }
                    if (!empty($ar->type)) {
                        $subject->type = $ar->type;
                    }
                }
            }

            // Same attendance rows, but resolve subject via subjects.id = subject_group_subject_id (legacy)
            $stillMissing = array();
            foreach ($subjects as $sub) {
                if (empty($sub->id)) {
                    continue;
                }
                $n = isset($sub->name) ? trim((string) $sub->name) : '';
                if ($n === '' || $n === '-') {
                    $stillMissing[] = (int) $sub->id;
                }
            }
            if (!empty($stillMissing)) {
                $smIn = implode(',', array_map('intval', array_unique($stillMissing)));
                $attDirectSql = "SELECT DISTINCT ssa.subject_timetable_id AS timetable_id, subj.id AS subject_id, subj.name, subj.code, subj.type
                    FROM student_subject_attendances ssa
                    INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                        AND ss.class_id = " . $this->db->escape($class_id) . "
                        AND ss.section_id = " . $this->db->escape($section_id) . "
                        AND ss.session_id = " . $this->db->escape($this->current_session) . "
                    INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                    INNER JOIN subjects subj ON subj.id = st.subject_group_subject_id
                    WHERE ssa.subject_timetable_id IN (" . $smIn . ")
                    AND (
                        (subj.name IS NOT NULL AND TRIM(subj.name) <> '' AND TRIM(subj.name) <> '-')
                        OR (subj.code IS NOT NULL AND TRIM(subj.code) <> '')
                    )";
                $attDirectRows = $this->db->query($attDirectSql)->result();
                foreach ($attDirectRows as $ar) {
                    $tid = (int) $ar->timetable_id;
                    foreach ($subjects as $subject) {
                        if (empty($subject->id) || (int) $subject->id !== $tid) {
                            continue;
                        }
                        $n = isset($subject->name) ? trim((string) $subject->name) : '';
                        if ($n !== '' && $n !== '-') {
                            continue;
                        }
                        $nmA = isset($ar->name) ? trim((string) $ar->name) : '';
                        $cdA = isset($ar->code) ? trim((string) $ar->code) : '';
                        $label = ($nmA !== '' && $nmA !== '-') ? $nmA : $cdA;
                        if ($label !== '') {
                            $subject->name         = $label;
                            $subject->subject_name = $label;
                            $subject->subject_id   = (int) $ar->subject_id;
                            if (!empty($ar->code)) {
                                $subject->code = $ar->code;
                            }
                            if (!empty($ar->type)) {
                                $subject->type = $ar->type;
                            }
                        }
                        break;
                    }
                }
            }
        }

        // Slots may only have subject_group_id (no subject_group_subject_id) — resolve any subject in that group
        $stillNoName = array();
        foreach ($subjects as $sub) {
            if (!is_object($sub) || empty($sub->id)) {
                continue;
            }
            $n = isset($sub->name) ? trim((string) $sub->name) : '';
            if ($n === '' || $n === '-') {
                $stillNoName[] = (int) $sub->id;
            }
        }
        if (!empty($stillNoName)) {
            $sgIn = implode(',', array_map('intval', array_unique($stillNoName)));
            $sess = $this->db->escape($this->current_session);
            $sgSql = "SELECT st.id AS timetable_id, MIN(s.name) AS name, MIN(s.id) AS subject_id, MIN(s.code) AS code
                FROM subject_timetable st
                INNER JOIN subject_group_subjects sgs ON sgs.subject_group_id = st.subject_group_id
                    AND sgs.session_id = " . $sess . "
                INNER JOIN subjects s ON s.id = sgs.subject_id
                WHERE st.id IN (" . $sgIn . ")
                GROUP BY st.id";
            $sgRows = $this->db->query($sgSql)->result();
            if (empty($sgRows)) {
                $sgSql2 = "SELECT st.id AS timetable_id, MIN(s.name) AS name, MIN(s.id) AS subject_id, MIN(s.code) AS code
                    FROM subject_timetable st
                    INNER JOIN subject_group_subjects sgs ON sgs.subject_group_id = st.subject_group_id
                    INNER JOIN subjects s ON s.id = sgs.subject_id
                    WHERE st.id IN (" . $sgIn . ")
                    GROUP BY st.id";
                $sgRows = $this->db->query($sgSql2)->result();
            }
            $sgMap = array();
            foreach ($sgRows as $r) {
                $nmR = isset($r->name) ? trim((string) $r->name) : '';
                if ($nmR === '' || $nmR === '-') {
                    continue;
                }
                $sgMap[(int) $r->timetable_id] = $r;
            }
            foreach ($subjects as $subject) {
                if (!is_object($subject) || empty($subject->id)) {
                    continue;
                }
                $n = isset($subject->name) ? trim((string) $subject->name) : '';
                if (($n === '' || $n === '-') && isset($sgMap[(int) $subject->id])) {
                    $r = $sgMap[(int) $subject->id];
                    $nmS = isset($r->name) ? trim((string) $r->name) : '';
                    $cdS = isset($r->code) ? trim((string) $r->code) : '';
                    $pick = ($nmS !== '' && $nmS !== '-') ? $nmS : $cdS;
                    if ($pick !== '') {
                        $subject->name         = $pick;
                        $subject->subject_name = $pick;
                        $subject->subject_id   = (int) $r->subject_id;
                        if ($cdS !== '') {
                            $subject->code = $cdS;
                        }
                    }
                }
            }
        }

        // Last resort: code, time slot — avoid "Timetable #" (reads like a DB table name to users)
        foreach ($subjects as $subject) {
            if (!is_object($subject)) {
                continue;
            }
            $nm = isset($subject->name) ? trim((string) $subject->name) : '';
            if ($nm !== '' && $nm !== '-') {
                if (!isset($subject->subject_name) || trim((string) $subject->subject_name) === '') {
                    $subject->subject_name = $nm;
                }
                continue;
            }
            $cd = isset($subject->code) ? trim((string) $subject->code) : '';
            if ($cd !== '') {
                $subject->name         = $cd;
                $subject->subject_name = $cd;
                continue;
            }
            $tf = isset($subject->time_from) ? trim((string) $subject->time_from) : '';
            $tt = isset($subject->time_to) ? trim((string) $subject->time_to) : '';
            if ($tf !== '' || $tt !== '') {
                $slot                  = $tf . (($tf !== '' && $tt !== '') ? ' - ' : '') . $tt;
                $subject->name         = $slot;
                $subject->subject_name = $slot;
            }
        }
    }

    public function add($insert_array, $update_array)
    {

        $this->db->trans_start();
        $this->db->trans_strict(false);
        if (!empty($insert_array)) {

            $this->db->insert_batch('student_subject_attendances', $insert_array);
        }
        if (!empty($update_array)) {
            $this->db->update_batch('student_subject_attendances', $update_array, 'id');
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

    public function searchAttendenceClassSection($class_id, $section_id, $subject_timetable_id, $date)
    {
        // Ensure we only get students from the current session
        // Filter by session_id FIRST in the JOIN to prevent students from other sessions
        $sql   = "SELECT  
                    IFNULL(student_subject_attendances.id, '0') as student_subject_attendance_id,
                    student_subject_attendances.subject_timetable_id,
                    student_subject_attendances.attendence_type_id, 
                    IFNULL(student_subject_attendances.date, 'xxx') as date, 
                    student_subject_attendances.remark,
                    students.*,
                    student_session.id as student_session_id 
                FROM students 
                INNER JOIN student_session ON students.id = student_session.student_id 
                    AND student_session.class_id = " . $this->db->escape($class_id) . " 
                    AND student_session.section_id = " . $this->db->escape($section_id) . "  
                    AND student_session.session_id = " . $this->db->escape($this->current_session) . "
                    AND student_session.is_active = 'yes'
                LEFT JOIN student_subject_attendances ON student_session.id = student_subject_attendances.student_session_id 
                    AND student_subject_attendances.subject_timetable_id = " . $this->db->escape($subject_timetable_id) . " 
                    AND student_subject_attendances.date = " . $this->db->escape($date) . " 
                WHERE students.is_active = 'yes'
                    AND student_session.session_id = " . $this->db->escape($this->current_session) . "
                    AND student_session.is_active = 'yes'
                ORDER BY students.roll_no ASC, students.admission_no ASC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function getStudentMontlyAttendence($class_id, $section_id, $from_date, $to_date, $student_id,$subject_id)
    {

        $student_array = array();

        $student_array['students_attendances'] = array();
   
             for ($i = strtotime($from_date); $i <= strtotime($to_date); $i+=86400) {

             $date_no=$date = date('d',$i);
            $date = date('Y-m-d',$i); 
            $day = date('l', strtotime($date));

            $students_time_table = $this->searchByStudentAttendanceByDate($class_id, $section_id, $day, $date, $student_id,$subject_id);
            $a                   = array();
            $a['date']           = $this->customlib->dateformat($date);
            $a['day']            = $day;
            $a['subjects']       = array();
            $a['attendances']    = array();

            if (!empty($students_time_table)) {
                $students_time_table = json_decode($students_time_table);

                $a['subjects'] = ($students_time_table->subjects);
                foreach ($students_time_table->student_record as $students_time_table_key => $students_time_table_value) {
                    $a['attendances'] = ($students_time_table->student_record[$students_time_table_key]);
                }
            }
            $student_array['students_attendances'][$date_no] = $a;
        }
        return $student_array;
    }

    public function searchByStudentAttendanceByDate($class_id, $section_id, $day, $date, $student_id,$subject_id)
    {

        $sql = "SELECT subject_timetable.*,subjects.id as `subject_id`,subjects.name,subjects.code,subjects.type FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id=subject_timetable.subject_group_subject_id INNER JOIN subjects on subjects.id=subject_group_subjects.subject_id WHERE subject_timetable.class_id=" . $this->db->escape($class_id) . " AND subject_timetable.section_id=" . $this->db->escape($section_id) . " and subject_timetable.session_id=" . $this->db->escape($this->current_session) . " and subject_timetable.day=" . $this->db->escape($day);
         if($subject_id !=""){
            $sql .=" AND subjects.id=".$subject_id;
        }
       

        $query    = $this->db->query($sql);
        $subjects = $query->result();

        if (!empty($subjects)) {
            $count        = 1;
            $append_sql   = "";
            $append_param = "";
            foreach ($subjects as $subject_key => $subject_value) {
                $append_param .= ",student_subject_attendances_" . $count . ".attendence_type_id as attendence_type_id_" . $count;
                $append_sql .= " LEFT JOIN student_subject_attendances as student_subject_attendances_" . $count . " on  student_subject_attendances_" . $count . ".student_session_id=student_session.id and student_subject_attendances_" . $count . ".subject_timetable_id=" . $this->db->escape($subject_value->id) . " and student_subject_attendances_" . $count . ".date=" . $this->db->escape($date);
                $count++;
            }
            $sql_student_record = "SELECT students.id,students.firstname" . $append_param . " FROM `students` INNER JOIN student_session on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " AND student_session.section_id=" . $this->db->escape($section_id) . " AND student_session.session_id=" . $this->db->escape($this->current_session) . $append_sql . " WHERE students.id=" . $student_id;
            $query              = $this->db->query($sql_student_record);
            $student_record     = $query->result();
            return json_encode(array('subjects' => $subjects, 'student_record' => $student_record));
        }

        return false;
    }

    public function studentAttendanceByDate($class_id, $section_id, $day, $date, $student_session_id)
    {
        $sql        = "SELECT subject_timetable.*,subject_group_subjects.subject_group_id,subjects.id as `subject_id`,subjects.name,subjects.code,subjects.type,student_subject_attendances.student_session_id,student_subject_attendances.attendence_type_id,student_subject_attendances.date,student_subject_attendances.remark,student_subject_attendances.id as `student_subject_attendance_id`,student_subject_attendances.date  FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id = subject_timetable.subject_group_subject_id and subject_group_subjects.session_id=" . $this->current_session . " INNER JOIN subjects on subjects.id=subject_group_subjects.subject_id LEFT JOIN student_subject_attendances on student_subject_attendances.subject_timetable_id=subject_timetable.id and student_subject_attendances.student_session_id=" . $this->db->escape($student_session_id) . " WHERE subject_timetable.class_id=" . $this->db->escape($class_id) . " AND subject_timetable.section_id=" . $this->db->escape($section_id) . " and subject_timetable.day=" . $this->db->escape($day) . "and student_subject_attendances.date=" . $this->db->escape($date);
        $query      = $this->db->query($sql);
        $attendance = $query->result();
        return $attendance;
    }

    public function getStudentsMontlyAttendence($class_id, $section_id, $from_date, $to_date,$subject_id)
    {

        $student_array                   = array();
        $student_array['class_students'] = $this->student_model->searchByClassSectionWithSession($class_id, $section_id);

        $student_array['students_attendances'] = array();
        for ($i = strtotime($from_date); $i <= strtotime($to_date); $i+=86400) {
            $date_no=$date = date('d',$i);
            $date = date('Y-m-d',$i);
            $day = date('l', strtotime($date));
            $students_time_table = $this->searchByStudentsAttendanceByDate($class_id, $section_id, $day, $date,$subject_id);
            $a             = array();
            $a['date']     = $date;
            $a['day']      = $day;
            $a['subjects'] = array();
            $a['students'] = array();

            if (!empty($students_time_table)) {
                if (is_array($students_time_table)) {
                    $students_time_table = json_decode(json_encode($students_time_table));
                } else {
                    $students_time_table = json_decode($students_time_table);
                }

                $a['subjects'] = ($students_time_table->subjects);
                foreach ($students_time_table->student_record as $students_time_table_key => $students_time_table_value) {
                    $a['students'][$students_time_table_value->id] = ($students_time_table->student_record[$students_time_table_key]);
                }
            }
            $student_array['students_attendances'][$date_no] = $a;
        }

        return $student_array;
    }

    public function searchByStudentsAttendanceByDate($class_id, $section_id, $day, $date,$subject_id)
    {
        // Debug: Log received parameters
        $this->db_debug_info = array();
        $this->db_debug_info['received_params'] = array(
            'class_id' => $class_id,
            'section_id' => $section_id,
            'day' => $day,
            'date' => $date,
            'subject_id' => $subject_id,
            'current_session' => $this->current_session
        );
        
        // Check what dates are actually stored in the database for debugging
        $check_sql = "SELECT DISTINCT ssa.date, COUNT(*) as count 
                      FROM student_subject_attendances ssa
                      INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                      WHERE ss.class_id = " . $this->db->escape($class_id) . "
                        AND ss.section_id = " . $this->db->escape($section_id) . "
                        AND ss.session_id = " . $this->db->escape($this->current_session) . "
                      GROUP BY ssa.date
                      ORDER BY ssa.date DESC
                      LIMIT 10";
        $check_query = $this->db->query($check_sql);
        $stored_dates = $check_query->result();
        
        // PRIORITY: First get ALL distinct subject_timetable_ids from attendance records
        // This ensures we get ALL subjects with attendance, regardless of JOIN success
        $sql_attendance_ids = "SELECT DISTINCT ssa.subject_timetable_id as id
                            FROM student_subject_attendances ssa
                            INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                            WHERE ss.class_id = " . $this->db->escape($class_id) . "
                                AND ss.section_id = " . $this->db->escape($section_id) . "
                                AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                AND ssa.date = " . $this->db->escape($date) . "
                                AND ssa.subject_timetable_id IS NOT NULL
                                AND ssa.subject_timetable_id != 0";
        
        $attendance_ids_query = $this->db->query($sql_attendance_ids);
        $attendance_ids_result = $attendance_ids_query->result();
        
        // DEBUG: Log attendance IDs found
        $this->db_debug_info['step1_attendance_ids_found'] = count($attendance_ids_result);
        $this->db_debug_info['step1_attendance_ids_list'] = array();
        foreach ($attendance_ids_result as $aid) {
            $this->db_debug_info['step1_attendance_ids_list'][] = $aid->id;
        }
        
        // Now fetch subject details for these IDs - use LEFT JOINs to ensure we get all IDs even if some JOINs fail
        $attendance_subjects = array();
        if (!empty($attendance_ids_result)) {
            $timetable_ids = array();
            foreach ($attendance_ids_result as $aid) {
                if (!empty($aid->id)) {
                    $timetable_ids[] = $aid->id;
                }
            }
            
            // DEBUG: Log timetable IDs after processing
            $this->db_debug_info['step2_timetable_ids_count'] = count($timetable_ids);
            $this->db_debug_info['step2_timetable_ids_list'] = $timetable_ids;
            
            // If subject_id filter is provided, filter the timetable_ids first
            if($subject_id != "" && $subject_id != null && !empty($timetable_ids)){
                $ids_escaped_temp = array_map(array($this->db, 'escape'), $timetable_ids);
                // Filter timetable_ids by subject_id
                $subject_filter_query = "SELECT DISTINCT st.id
                                        FROM subject_timetable st
                                        INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                        WHERE st.id IN (" . implode(',', $ids_escaped_temp) . ")
                                            AND sgs.subject_id = " . $this->db->escape($subject_id)
                                            . " AND st.session_id = " . $this->db->escape($this->current_session);
                $subject_filter_result = $this->db->query($subject_filter_query)->result();
                $filtered_ids = array();
                foreach ($subject_filter_result as $sfr) {
                    $filtered_ids[] = $sfr->id;
                }
                $timetable_ids = $filtered_ids;
            }
            
            if (!empty($timetable_ids)) {
                $ids_escaped = array_map(array($this->db, 'escape'), $timetable_ids);
                
                // CRITICAL: Fetch subject names directly by timetable_id - NO session_id filter in JOIN
                // This ensures we get names for ALL subjects regardless of which day they're scheduled
                // First, get basic timetable info
                $sql_attendance = "SELECT DISTINCT 
                            st.id,
                            0 as `subject_id`,
                            '' as name,
                            '' as code,
                            '' as type,
                            COALESCE(st.time_from, '') as time_from,
                            COALESCE(st.time_to, '') as time_to
                        FROM subject_timetable st
                        WHERE st.id IN (" . implode(',', $ids_escaped) . ")";
                
                $query = $this->db->query($sql_attendance);
                $attendance_subjects = $query->result();
                
                // DEBUG: Log basic timetable query results
                $this->db_debug_info['step3_basic_timetable_query_count'] = count($attendance_subjects);
                $this->db_debug_info['step3_basic_timetable_subjects'] = array();
                foreach ($attendance_subjects as $as) {
                    $this->db_debug_info['step3_basic_timetable_subjects'][] = array(
                        'id' => $as->id,
                        'name' => isset($as->name) ? $as->name : 'EMPTY',
                        'subject_id' => isset($as->subject_id) ? $as->subject_id : 'EMPTY'
                    );
                }
                
                // Now populate subject names directly - this works for ALL subjects regardless of day
                if (!empty($attendance_subjects)) {
                    $name_lookup_query = "SELECT DISTINCT 
                                st.id as timetable_id,
                                subjects.id as `subject_id`,
                                subjects.name,
                                subjects.code,
                                subjects.type
                            FROM subject_timetable st
                            INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                            INNER JOIN subjects ON subjects.id = sgs.subject_id
                            WHERE st.id IN (" . implode(',', $ids_escaped) . ")
                            AND subjects.name IS NOT NULL AND subjects.name != ''";
                    
                    // DEBUG: Log the name lookup query
                    $this->db_debug_info['step4_name_lookup_query'] = $name_lookup_query;
                    $this->db_debug_info['step4_name_lookup_ids'] = $ids_escaped;
                    
                    $name_lookup_result = $this->db->query($name_lookup_query)->result();
                    
                    // DEBUG: Log name lookup results
                    $this->db_debug_info['step4_name_lookup_result_count'] = count($name_lookup_result);
                    $this->db_debug_info['step4_name_lookup_results'] = array();
                    foreach ($name_lookup_result as $nlr) {
                        $this->db_debug_info['step4_name_lookup_results'][] = array(
                            'timetable_id' => $nlr->timetable_id,
                            'subject_id' => $nlr->subject_id,
                            'name' => $nlr->name,
                            'code' => $nlr->code
                        );
                    }
                    
                    // Create a map of timetable_id => subject data
                    $name_map = array();
                    foreach ($name_lookup_result as $nlr) {
                        if (!empty($nlr->timetable_id)) {
                            $tid_int = intval($nlr->timetable_id);
                            $tid_str = (string)$nlr->timetable_id;
                            $name_map[$tid_int] = $nlr;
                            $name_map[$tid_str] = $nlr;
                        }
                    }
                    
                    // Populate names in attendance_subjects
                    $this->db_debug_info['step5_name_population'] = array();
                    foreach ($attendance_subjects as $as) {
                        if (!empty($as->id)) {
                            $tid_int = intval($as->id);
                            $tid_str = (string)$as->id;
                            $old_name = isset($as->name) ? $as->name : 'EMPTY';
                            
                            if (isset($name_map[$tid_int])) {
                                $name_data = $name_map[$tid_int];
                                $as->subject_id = $name_data->subject_id;
                                $as->name = $name_data->name;
                                $as->code = $name_data->code;
                                $as->type = $name_data->type;
                                $this->db_debug_info['step5_name_population'][] = array(
                                    'timetable_id' => $as->id,
                                    'found_via' => 'int_key',
                                    'old_name' => $old_name,
                                    'new_name' => $name_data->name
                                );
                            } elseif (isset($name_map[$tid_str])) {
                                $name_data = $name_map[$tid_str];
                                $as->subject_id = $name_data->subject_id;
                                $as->name = $name_data->name;
                                $as->code = $name_data->code;
                                $as->type = $name_data->type;
                                $this->db_debug_info['step5_name_population'][] = array(
                                    'timetable_id' => $as->id,
                                    'found_via' => 'str_key',
                                    'old_name' => $old_name,
                                    'new_name' => $name_data->name
                                );
                            } else {
                                $this->db_debug_info['step5_name_population'][] = array(
                                    'timetable_id' => $as->id,
                                    'found_via' => 'NOT_FOUND',
                                    'old_name' => $old_name,
                                    'new_name' => 'STILL_EMPTY',
                                    'name_map_keys' => array_keys($name_map)
                                );
                            }
                        }
                    }
                    
                    // If any subjects still don't have names, try a more flexible query (without session_id restrictions)
                    $missing_name_ids = array();
                    foreach ($attendance_subjects as $as) {
                        if (!empty($as->id) && (empty($as->name) || trim($as->name) == '')) {
                            $missing_name_ids[] = $as->id;
                        }
                    }
                    
                    // DEBUG: Log missing name IDs
                    $this->db_debug_info['step6_missing_name_ids_count'] = count($missing_name_ids);
                    $this->db_debug_info['step6_missing_name_ids_list'] = $missing_name_ids;
                    
                    if (!empty($missing_name_ids)) {
                        $missing_ids_escaped = array_map(array($this->db, 'escape'), $missing_name_ids);
                        $ids_list = implode(',', $missing_ids_escaped);
                        $flexible_name_query = "SELECT DISTINCT st.id as timetable_id, subjects.id as subject_id, subjects.name, subjects.code, subjects.type FROM subject_timetable st LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id LEFT JOIN subjects ON subjects.id = sgs.subject_id WHERE st.id IN (" . $ids_list . ") AND subjects.name IS NOT NULL AND subjects.name != ''";
                        
                        // DEBUG: Log flexible query
                        $this->db_debug_info['step6_flexible_query'] = $flexible_name_query;
                        
                        $flexible_name_result = $this->db->query($flexible_name_query)->result();
                        
                        // DEBUG: Log flexible query results
                        $this->db_debug_info['step6_flexible_result_count'] = count($flexible_name_result);
                        $this->db_debug_info['step6_flexible_results'] = array();
                        foreach ($flexible_name_result as $fnr) {
                            $this->db_debug_info['step6_flexible_results'][] = array(
                                'timetable_id' => $fnr->timetable_id,
                                'subject_id' => $fnr->subject_id,
                                'name' => $fnr->name,
                                'code' => $fnr->code
                            );
                        }
                        
                        $flexible_populated_count = 0;
                        foreach ($flexible_name_result as $fnr) {
                            foreach ($attendance_subjects as $as) {
                                if (!empty($as->id) && intval($as->id) == intval($fnr->timetable_id)) {
                                    if (empty($as->name) || trim($as->name) == '') {
                                        $as->subject_id = $fnr->subject_id;
                                        $as->name = $fnr->name;
                                        $as->code = $fnr->code;
                                        $as->type = $fnr->type;
                                        $flexible_populated_count++;
                                        break;
                                    }
                                }
                            }
                        }
                        $this->db_debug_info['step6_flexible_populated_count'] = $flexible_populated_count;
                    }
                }
            }
        }
        
        // Also get subjects from timetable for ALL days (to show subjects regardless of which day they're scheduled)
        // This ensures subjects scheduled on any day can be displayed in period-wise attendance
        $sql_timetable = "SELECT DISTINCT 
                    st.id,
                    subjects.id as `subject_id`,
                    subjects.name,
                    subjects.code,
                    subjects.type,
                    COALESCE(st.time_from, '') as time_from,
                    COALESCE(st.time_to, '') as time_to
                FROM subject_group_class_sections sgcs
                INNER JOIN class_sections cs ON cs.id = sgcs.class_section_id
                INNER JOIN subject_groups sg ON sg.id = sgcs.subject_group_id
                INNER JOIN subject_group_subjects sgs ON sgs.subject_group_id = sg.id
                INNER JOIN subjects ON subjects.id = sgs.subject_id
                INNER JOIN subject_timetable st ON st.subject_group_subject_id = sgs.id 
                    AND st.class_id = " . $this->db->escape($class_id) . "
                    AND st.section_id = " . $this->db->escape($section_id) . "
                    AND st.session_id = " . $this->db->escape($this->current_session);
        
        if($subject_id != ""){
            $sql_timetable .= " AND subjects.id = " . $this->db->escape($subject_id);
        }
        
        // Debug: Check if we have any attendance records for this date at all
        $check_attendance_exists = "SELECT COUNT(*) as cnt FROM student_subject_attendances ssa
                                   INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                WHERE ss.class_id = " . $this->db->escape($class_id) . "
                    AND ss.section_id = " . $this->db->escape($section_id) . "
                    AND ss.session_id = " . $this->db->escape($this->current_session) . "
                    AND ssa.date = " . $this->db->escape($date) . "
                    AND ssa.subject_timetable_id IS NOT NULL
                    AND ssa.subject_timetable_id != 0";
        $check_result = $this->db->query($check_attendance_exists)->row();
        $attendance_count = $check_result ? $check_result->cnt : 0;
        
        // Store debug info
        $this->db_debug_info['attendance_records_count'] = $attendance_count;
        $this->db_debug_info['search_date'] = $date;
        $this->db_debug_info['search_day'] = $day;
        
        // attendance_subjects is now populated above with all subjects that have attendance records
        
        // Store debug info
        $this->db_debug_info['attendance_records_count'] = $attendance_count;
        $this->db_debug_info['attendance_query_subjects'] = count($attendance_subjects);
        $this->db_debug_info['search_date'] = $date;
        $this->db_debug_info['search_day'] = $day;
        
        // Always try to get subjects from timetable (for ALL days, not just the specific day)
        // This ensures we get subject names even if attendance query returns empty names
        // and works for subjects scheduled on any day of the week
        $timetable_subjects = array();
        $timetable_query = $this->db->query($sql_timetable);
        $timetable_subjects = $timetable_query->result();
        $this->db_debug_info['timetable_query_subjects'] = count($timetable_subjects);
        
        // CRITICAL: Use ONLY attendance records to determine which subjects to show
        // This ensures ALL subjects with attendance records are shown, regardless of which day they're scheduled
        // We do NOT want to filter by the day of the week - we want ALL subjects that have attendance for this date
        $subjects = $attendance_subjects;
        
        // DEBUG: Log final attendance subjects before any further processing
        $this->db_debug_info['step7_final_attendance_subjects_count'] = count($attendance_subjects);
        $this->db_debug_info['step7_final_attendance_subjects'] = array();
        foreach ($attendance_subjects as $as) {
            $this->db_debug_info['step7_final_attendance_subjects'][] = array(
                'id' => isset($as->id) ? $as->id : 'NULL',
                'subject_id' => isset($as->subject_id) ? $as->subject_id : 'NULL',
                'name' => isset($as->name) ? ($as->name == '' ? 'EMPTY_STRING' : $as->name) : 'NULL',
                'code' => isset($as->code) ? $as->code : 'NULL',
                'type' => isset($as->type) ? $as->type : 'NULL',
                'time_from' => isset($as->time_from) ? $as->time_from : 'NULL',
                'time_to' => isset($as->time_to) ? $as->time_to : 'NULL'
            );
        }
        
        // If no subjects from attendance query but we have attendance records, get timetable_ids directly
        // This handles the case where the JOIN might have failed but attendance records exist
        if (empty($subjects) && $attendance_count > 0) {
            // Get distinct subject_timetable_ids from attendance records
            $attendance_ids_query = "SELECT DISTINCT ssa.subject_timetable_id as id
                                    FROM student_subject_attendances ssa
                                    INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                                    WHERE ss.class_id = " . $this->db->escape($class_id) . "
                                        AND ss.section_id = " . $this->db->escape($section_id) . "
                                        AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                        AND ssa.date = " . $this->db->escape($date) . "
                                        AND ssa.subject_timetable_id IS NOT NULL
                                        AND ssa.subject_timetable_id != 0";
            $attendance_ids_result = $this->db->query($attendance_ids_query)->result();
            
            // Create subject objects from attendance records
            foreach ($attendance_ids_result as $att_id) {
                $subject_obj = new stdClass();
                $subject_obj->id = $att_id->id;
                $subject_obj->subject_id = 0;
                $subject_obj->name = '';
                $subject_obj->code = '';
                $subject_obj->type = '';
                $subject_obj->time_from = '';
                $subject_obj->time_to = '';
                $subjects[] = $subject_obj;
            }
        }
        
        // CRITICAL: If we have subjects but no names, populate names IMMEDIATELY before any other processing
        // This ensures names are available even if the initial query didn't return them
        // Use attendance records directly - this works for ALL subjects regardless of which day they're scheduled
        if (!empty($subjects)) {
            $timetable_ids_for_names = array();
            foreach ($subjects as $subject) {
                if (!empty($subject->id) && $subject->id != 0 && (empty($subject->name) || trim($subject->name) == '')) {
                    $timetable_ids_for_names[] = $subject->id;
                }
            }
            
            // CRITICAL: Use attendance records directly to get names - this works regardless of timetable day
            if (!empty($timetable_ids_for_names)) {
                $ids_escaped = array_map(array($this->db, 'escape'), $timetable_ids_for_names);
                
                // Query directly from attendance records - this is the most reliable method
                // It works for ALL subjects that have attendance records, regardless of which day they're scheduled
                // Try without date filter first (more flexible)
                $attendance_name_query = "SELECT DISTINCT ssa.subject_timetable_id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                        COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                        FROM student_subject_attendances ssa
                                        INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                                        INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                        INNER JOIN subjects s ON s.id = sgs.subject_id
                                        INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                                        WHERE ssa.subject_timetable_id IN (" . implode(',', $ids_escaped) . ")
                                        AND ss.class_id = " . $this->db->escape($class_id) . "
                                        AND ss.section_id = " . $this->db->escape($section_id) . "
                                        AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                        AND s.name IS NOT NULL AND s.name != ''
                                        GROUP BY ssa.subject_timetable_id, s.name, s.id, s.code, s.type, st.time_from, st.time_to
                                        LIMIT 100";
                $attendance_name_results = $this->db->query($attendance_name_query)->result();
                
                // If we didn't get all names, try with date filter
                if (count($attendance_name_results) < count($timetable_ids_for_names)) {
                    $found_att_ids = array();
                    foreach ($attendance_name_results as $anr) {
                        $found_att_ids[] = intval($anr->timetable_id);
                    }
                    $missing_att_ids = array();
                    foreach ($timetable_ids_for_names as $tid) {
                        if (!in_array(intval($tid), $found_att_ids)) {
                            $missing_att_ids[] = $tid;
                        }
                    }
                    
                    if (!empty($missing_att_ids)) {
                        $missing_att_escaped = array_map(array($this->db, 'escape'), $missing_att_ids);
                        $attendance_name_query2 = "SELECT DISTINCT ssa.subject_timetable_id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                                  COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                                  FROM student_subject_attendances ssa
                                                  INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                                                  INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                                  INNER JOIN subjects s ON s.id = sgs.subject_id
                                                  INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                                                  WHERE ssa.subject_timetable_id IN (" . implode(',', $missing_att_escaped) . ")
                                                  AND ss.class_id = " . $this->db->escape($class_id) . "
                                                  AND ss.section_id = " . $this->db->escape($section_id) . "
                                                  AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                                  AND ssa.date = " . $this->db->escape($date) . "
                                                  AND s.name IS NOT NULL AND s.name != ''
                                                  GROUP BY ssa.subject_timetable_id, s.name, s.id, s.code, s.type, st.time_from, st.time_to";
                        $attendance_name_results2 = $this->db->query($attendance_name_query2)->result();
                        
                        // Merge results
                        foreach ($attendance_name_results2 as $anr2) {
                            $attendance_name_results[] = $anr2;
                        }
                    }
                }
                
                // Create name map from attendance query results
                $attendance_name_map = array();
                foreach ($attendance_name_results as $anr) {
                    if (!empty($anr->name)) {
                        $tid_int = intval($anr->timetable_id);
                        $tid_str = (string)$anr->timetable_id;
                        $attendance_name_map[$tid_int] = $anr;
                        $attendance_name_map[$tid_str] = $anr;
                    }
                }
                
                // Populate names immediately from attendance records
                $attendance_populated = 0;
                foreach ($subjects as $subject) {
                    if (!empty($subject->id) && (empty($subject->name) || trim($subject->name) == '')) {
                        $subject_id_int = intval($subject->id);
                        $subject_id_str = (string)$subject->id;
                        
                        $name_data = null;
                        if (isset($attendance_name_map[$subject_id_int])) {
                            $name_data = $attendance_name_map[$subject_id_int];
                        } elseif (isset($attendance_name_map[$subject_id_str])) {
                            $name_data = $attendance_name_map[$subject_id_str];
                        }
                        
                        if ($name_data && !empty($name_data->name) && trim($name_data->name) != '') {
                            $subject->name = trim($name_data->name);
                            $subject->subject_id = $name_data->subject_id;
                            $subject->code = $name_data->code;
                            $subject->type = $name_data->type;
                            $subject->time_from = $name_data->time_from;
                            $subject->time_to = $name_data->time_to;
                            $attendance_populated++;
                        }
                    }
                }
                
                $this->db_debug_info['attendance_name_query_ids'] = $timetable_ids_for_names;
                $this->db_debug_info['attendance_name_query_results'] = array();
                foreach ($attendance_name_results as $r) {
                    $this->db_debug_info['attendance_name_query_results'][] = array(
                        'timetable_id' => $r->timetable_id,
                        'name' => $r->name,
                        'subject_id' => $r->subject_id
                    );
                }
                $this->db_debug_info['attendance_name_populated_count'] = $attendance_populated;
            }
            
            // Now continue with the original immediate name population as fallback
            $timetable_ids_for_names = array();
            foreach ($subjects as $subject) {
                if (!empty($subject->id) && $subject->id != 0 && (empty($subject->name) || trim($subject->name) == '')) {
                    $timetable_ids_for_names[] = $subject->id;
                }
            }
            
            if (!empty($timetable_ids_for_names)) {
                $ids_escaped = array_map(array($this->db, 'escape'), $timetable_ids_for_names);
                
                // Try query without session_id filter first (more flexible) - this should work for ALL subjects regardless of day
                // Query by timetable_id directly - each timetable entry has its own ID regardless of which day it's scheduled
                $name_query = "SELECT DISTINCT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                              COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                              FROM subject_timetable st
                              INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                              INNER JOIN subjects s ON s.id = sgs.subject_id
                              WHERE st.id IN (" . implode(',', $ids_escaped) . ")
                              AND s.name IS NOT NULL AND s.name != ''";
                $name_results = $this->db->query($name_query)->result();
                
                // If query returned fewer results than expected, try without any JOIN restrictions
                if (count($name_results) < count($timetable_ids_for_names)) {
                    // Try a more direct approach - get names directly from subject_timetable
                    $name_query_direct = "SELECT DISTINCT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                         COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                         FROM subject_timetable st
                                         LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                         LEFT JOIN subjects s ON s.id = sgs.subject_id
                                         WHERE st.id IN (" . implode(',', $ids_escaped) . ")
                                         AND s.name IS NOT NULL AND s.name != ''";
                    $name_results_direct = $this->db->query($name_query_direct)->result();
                    
                    // Merge results - use direct query results if they're better
                    if (count($name_results_direct) > count($name_results)) {
                        $name_results = $name_results_direct;
                    } else {
                        // Merge both results to get maximum coverage
                        $existing_ids = array();
                        foreach ($name_results as $nr) {
                            $existing_ids[] = intval($nr->timetable_id);
                        }
                        foreach ($name_results_direct as $nrd) {
                            if (!in_array(intval($nrd->timetable_id), $existing_ids)) {
                                $name_results[] = $nrd;
                            }
                        }
                    }
                }
                
                // Debug: Log what we got
                $this->db_debug_info['immediate_name_query_ids'] = $timetable_ids_for_names;
                $this->db_debug_info['immediate_name_query_results'] = array();
                foreach ($name_results as $r) {
                    $this->db_debug_info['immediate_name_query_results'][] = array(
                        'timetable_id' => $r->timetable_id,
                        'name' => $r->name,
                        'subject_id' => $r->subject_id
                    );
                }
                
                // Create a map of timetable_id => subject data (use both int and string keys for flexible matching)
                $name_map = array();
                foreach ($name_results as $nr) {
                    if (!empty($nr->name)) {
                        $tid_int = intval($nr->timetable_id);
                        $tid_str = (string)$nr->timetable_id;
                        $name_map[$tid_int] = $nr;
                        $name_map[$tid_str] = $nr; // Also store as string for flexible matching
                    }
                }
                
                // If we didn't get all names, try with session_id filter
                $found_ids = array();
                foreach ($name_map as $key => $value) {
                    if (is_int($key)) {
                        $found_ids[] = $key;
                    }
                }
                $missing_ids = array_diff($timetable_ids_for_names, $found_ids);
                
                if (!empty($missing_ids)) {
                    $missing_escaped = array_map(array($this->db, 'escape'), $missing_ids);
                    // Try with session_id filter first
                    $name_query2 = "SELECT DISTINCT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                  COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                  FROM subject_timetable st
                                  INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                      AND sgs.session_id = " . $this->db->escape($this->current_session) . "
                                  INNER JOIN subjects s ON s.id = sgs.subject_id
                                  WHERE st.id IN (" . implode(',', $missing_escaped) . ")
                                  AND s.name IS NOT NULL AND s.name != ''";
                    $name_results2 = $this->db->query($name_query2)->result();
                    
                    // If still missing some, try without session_id filter (more flexible)
                    if (count($name_results2) < count($missing_ids)) {
                        $name_query3 = "SELECT DISTINCT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                      COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                      FROM subject_timetable st
                                      LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                      LEFT JOIN subjects s ON s.id = sgs.subject_id
                                      WHERE st.id IN (" . implode(',', $missing_escaped) . ")
                                      AND s.name IS NOT NULL AND s.name != ''";
                        $name_results3 = $this->db->query($name_query3)->result();
                        
                        // Merge results - add any that weren't found in query2
                        $existing_ids2 = array();
                        foreach ($name_results2 as $nr2) {
                            $existing_ids2[] = intval($nr2->timetable_id);
                        }
                        foreach ($name_results3 as $nr3) {
                            if (!in_array(intval($nr3->timetable_id), $existing_ids2)) {
                                $name_results2[] = $nr3;
                            }
                        }
                    }
                    
                    foreach ($name_results2 as $nr) {
                        if (!empty($nr->name)) {
                            $tid_int = intval($nr->timetable_id);
                            $tid_str = (string)$nr->timetable_id;
                            $name_map[$tid_int] = $nr;
                            $name_map[$tid_str] = $nr;
                        }
                    }
                }
                
                // Populate names immediately - handle both string and integer IDs
                $populated_count = 0;
                foreach ($subjects as $subject) {
                    if (!empty($subject->id)) {
                        $subject_id_int = intval($subject->id);
                        $subject_id_str = (string)$subject->id;
                        
                        // Try both integer and string keys
                        $name_data = null;
                        if (isset($name_map[$subject_id_int])) {
                            $name_data = $name_map[$subject_id_int];
                        } elseif (isset($name_map[$subject_id_str])) {
                            $name_data = $name_map[$subject_id_str];
                        }
                        
                        if ($name_data && !empty($name_data->name) && trim($name_data->name) != '') {
                            $subject->name = trim($name_data->name);
                            $subject->subject_id = $name_data->subject_id;
                            $subject->code = $name_data->code;
                            $subject->type = $name_data->type;
                            $subject->time_from = $name_data->time_from;
                            $subject->time_to = $name_data->time_to;
                            $populated_count++;
                        }
                    }
                }
                
                $this->db_debug_info['immediate_name_populated_count'] = $populated_count;
            }
        }
        
        // CRITICAL: Populate ALL subject names IMMEDIATELY - use the most direct query possible
        // Collect ALL timetable_ids (even if they already have names, we'll preserve existing ones)
        $all_timetable_ids = array();
        foreach ($subjects as $subject) {
            if (!empty($subject->id) && $subject->id != 0) {
                $all_timetable_ids[] = $subject->id;
            }
        }
        
        // DEBUG: Log timetable IDs we're trying to get names for
        $this->db_debug_info['timetable_ids_to_fetch'] = $all_timetable_ids;
        $this->db_debug_info['subjects_before_name_population'] = array();
        foreach ($subjects as $s) {
            $this->db_debug_info['subjects_before_name_population'][] = array(
                'id' => $s->id,
                'name' => isset($s->name) ? $s->name : 'NOT SET',
                'subject_id' => isset($s->subject_id) ? $s->subject_id : 'NOT SET'
            );
        }
        
        // Fetch ALL subject names in ONE query - this should work for ALL subjects regardless of day
        // Query by timetable_id directly - each timetable entry has its own ID regardless of which day
        if (!empty($all_timetable_ids)) {
            $ids_escaped = array_map(array($this->db, 'escape'), $all_timetable_ids);
            
            // Single comprehensive query to get ALL subject names - NO DAY FILTER, just by timetable_id
            $comprehensive_query = "SELECT DISTINCT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                   COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                   FROM subject_timetable st
                                   INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                   INNER JOIN subjects s ON s.id = sgs.subject_id
                                   WHERE st.id IN (" . implode(',', $ids_escaped) . ")
                                   AND s.name IS NOT NULL AND s.name != ''";
            $comprehensive_results = $this->db->query($comprehensive_query)->result();
            
            // If query didn't find all subjects, try a more direct approach using attendance records
            if (count($comprehensive_results) < count($all_timetable_ids)) {
                $found_ids = array();
                foreach ($comprehensive_results as $cr) {
                    $found_ids[] = intval($cr->timetable_id);
                }
                $still_missing_ids = array();
                foreach ($all_timetable_ids as $tid) {
                    if (!in_array(intval($tid), $found_ids)) {
                        $still_missing_ids[] = $tid;
                    }
                }
                
                if (!empty($still_missing_ids)) {
                    $missing_escaped = array_map(array($this->db, 'escape'), $still_missing_ids);
                    // Direct query from attendance records - this should definitely work for ALL subjects
                    // Filter by date to ensure we get the right records, but this works regardless of which day subject is scheduled
                    $direct_query = "SELECT DISTINCT ssa.subject_timetable_id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                    COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                    FROM student_subject_attendances ssa
                                    INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                                    INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                    INNER JOIN subjects s ON s.id = sgs.subject_id
                                    INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                                    WHERE ssa.subject_timetable_id IN (" . implode(',', $missing_escaped) . ")
                                    AND ss.class_id = " . $this->db->escape($class_id) . "
                                    AND ss.section_id = " . $this->db->escape($section_id) . "
                                    AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                    AND ssa.date = " . $this->db->escape($date) . "
                                    AND s.name IS NOT NULL AND s.name != ''
                                    GROUP BY ssa.subject_timetable_id, s.name, s.id, s.code, s.type, st.time_from, st.time_to";
                    $direct_results = $this->db->query($direct_query)->result();
                    
                    // Merge with comprehensive results
                    foreach ($direct_results as $dr) {
                        if (!empty($dr->name)) {
                            $comprehensive_results[] = $dr;
                        }
                    }
                    
                    // If still missing some, try without date filter (most flexible)
                    if (count($comprehensive_results) < count($all_timetable_ids)) {
                        $found_direct_ids = array();
                        foreach ($comprehensive_results as $cr) {
                            $found_direct_ids[] = intval($cr->timetable_id);
                        }
                        $still_missing_direct = array();
                        foreach ($still_missing_ids as $smid) {
                            if (!in_array(intval($smid), $found_direct_ids)) {
                                $still_missing_direct[] = $smid;
                            }
                        }
                        
                        if (!empty($still_missing_direct)) {
                            $still_missing_escaped = array_map(array($this->db, 'escape'), $still_missing_direct);
                            $no_date_query = "SELECT DISTINCT ssa.subject_timetable_id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                             COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                             FROM student_subject_attendances ssa
                                             INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                                             INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                             INNER JOIN subjects s ON s.id = sgs.subject_id
                                             WHERE ssa.subject_timetable_id IN (" . implode(',', $still_missing_escaped) . ")
                                             AND s.name IS NOT NULL AND s.name != ''
                                             GROUP BY ssa.subject_timetable_id, s.name, s.id, s.code, s.type, st.time_from, st.time_to
                                             LIMIT 100";
                            $no_date_results = $this->db->query($no_date_query)->result();
                            
                            foreach ($no_date_results as $ndr) {
                                if (!empty($ndr->name)) {
                                    $comprehensive_results[] = $ndr;
                                }
                            }
                        }
                    }
                }
            }
            
            // If we didn't get all names, try without the name filter (in case some subjects have empty names in DB)
            if (count($comprehensive_results) < count($all_timetable_ids)) {
                $missing_comp_ids = array();
                $found_comp_ids = array();
                foreach ($comprehensive_results as $cr) {
                    $found_comp_ids[] = intval($cr->timetable_id);
                }
                foreach ($all_timetable_ids as $tid) {
                    if (!in_array(intval($tid), $found_comp_ids)) {
                        $missing_comp_ids[] = $tid;
                    }
                }
                
                if (!empty($missing_comp_ids)) {
                    $missing_comp_escaped = array_map(array($this->db, 'escape'), $missing_comp_ids);
                    $comprehensive_query2 = "SELECT DISTINCT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, 
                                            COALESCE(st.time_from, '') as time_from, COALESCE(st.time_to, '') as time_to
                                            FROM subject_timetable st
                                            LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                            LEFT JOIN subjects s ON s.id = sgs.subject_id
                                            WHERE st.id IN (" . implode(',', $missing_comp_escaped) . ")
                                            AND (s.name IS NOT NULL AND s.name != '')";
                    $comprehensive_results2 = $this->db->query($comprehensive_query2)->result();
                    
                    // Merge results
                    foreach ($comprehensive_results2 as $cr2) {
                        if (!empty($cr2->name)) {
                            $comprehensive_results[] = $cr2;
                        }
                    }
                }
            }
            
            // DEBUG: Log what we got from the query
            $this->db_debug_info['comprehensive_query_results'] = array();
            foreach ($comprehensive_results as $r) {
                $this->db_debug_info['comprehensive_query_results'][] = array(
                    'timetable_id' => $r->timetable_id,
                    'name' => $r->name,
                    'subject_id' => $r->subject_id
                );
            }
            
            // Create complete name map - use both int and string keys for flexible matching
            $complete_name_map = array();
            foreach ($comprehensive_results as $result) {
                if (!empty($result->name)) {
                    $tid_int = intval($result->timetable_id);
                    $tid_str = (string)$result->timetable_id;
                    $complete_name_map[$tid_int] = $result;
                    $complete_name_map[$tid_str] = $result; // Also store as string for flexible matching
                }
            }
            
            // DEBUG: Log name map
            $this->db_debug_info['name_map_keys'] = array_keys($complete_name_map);
            $this->db_debug_info['name_map_count'] = count($complete_name_map);
            
            // If some IDs weren't found, try direct lookup from attendance records
            $still_missing = array();
            foreach ($all_timetable_ids as $tid) {
                if (!isset($complete_name_map[$tid])) {
                    $still_missing[] = $tid;
                }
            }
            
            $this->db_debug_info['missing_ids_after_comprehensive'] = $still_missing;
            
            if (!empty($still_missing)) {
                $missing_escaped = array_map(array($this->db, 'escape'), $still_missing);
                $attendance_lookup_query = "SELECT DISTINCT ssa.subject_timetable_id as timetable_id, s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                                           FROM student_subject_attendances ssa
                                           INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                                           INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                           INNER JOIN subjects s ON s.id = sgs.subject_id
                                           WHERE ssa.subject_timetable_id IN (" . implode(',', $missing_escaped) . ")
                                           GROUP BY ssa.subject_timetable_id, s.name, s.id, s.code, s.type, st.time_from, st.time_to";
                $attendance_lookup_results = $this->db->query($attendance_lookup_query)->result();
                
                // DEBUG: Log attendance lookup results
                $this->db_debug_info['attendance_lookup_results'] = array();
                foreach ($attendance_lookup_results as $r) {
                    $this->db_debug_info['attendance_lookup_results'][] = array(
                        'timetable_id' => $r->timetable_id,
                        'name' => $r->name,
                        'subject_id' => $r->subject_id
                    );
                }
                
                foreach ($attendance_lookup_results as $result) {
                    if (!empty($result->name)) {
                        $tid_int = intval($result->timetable_id);
                        $tid_str = (string)$result->timetable_id;
                        // Add to map with both int and string keys if not already present
                        if (!isset($complete_name_map[$tid_int]) && !isset($complete_name_map[$tid_str])) {
                            $complete_name_map[$tid_int] = $result;
                            $complete_name_map[$tid_str] = $result;
                        }
                    }
                }
            }
            
            // NOW populate ALL subject names - but preserve existing names if they're already set
            $populated_count = 0;
            foreach ($subjects as $subject) {
                if (!empty($subject->id)) {
                    $subject_id_int = intval($subject->id);
                    $subject_id_str = (string)$subject->id;
                    
                    // Try both integer and string keys
                    $name_data = null;
                    if (isset($complete_name_map[$subject_id_int])) {
                        $name_data = $complete_name_map[$subject_id_int];
                    } elseif (isset($complete_name_map[$subject_id_str])) {
                        $name_data = $complete_name_map[$subject_id_str];
                    } elseif (isset($complete_name_map[$subject->id])) {
                        $name_data = $complete_name_map[$subject->id];
                    }
                    
                    if ($name_data) {
                        $old_name = isset($subject->name) ? $subject->name : 'NOT SET';
                        // Only overwrite if name is empty or missing - preserve names already fetched from attendance query
                        if (empty($subject->name) || trim($subject->name) == '' || trim($subject->name) == '-') {
                            $subject->name = trim($name_data->name);
                            $subject->subject_id = $name_data->subject_id;
                            $subject->code = $name_data->code;
                            $subject->type = $name_data->type;
                            $subject->time_from = $name_data->time_from;
                            $subject->time_to = $name_data->time_to;
                            $populated_count++;
                        } else {
                            // Name already exists, just ensure other fields are set if missing
                            if (empty($subject->subject_id) && !empty($name_data->subject_id)) {
                                $subject->subject_id = $name_data->subject_id;
                            }
                            if (empty($subject->code) && !empty($name_data->code)) {
                                $subject->code = $name_data->code;
                            }
                        }
                    }
                }
            }
            
            // DEBUG: Log how many were populated
            $this->db_debug_info['names_populated_count'] = $populated_count;
            $this->db_debug_info['subjects_after_name_population'] = array();
            foreach ($subjects as $s) {
                $this->db_debug_info['subjects_after_name_population'][] = array(
                    'id' => $s->id,
                    'name' => isset($s->name) ? $s->name : 'NOT SET',
                    'subject_id' => isset($s->subject_id) ? $s->subject_id : 'NOT SET'
                );
            }
        }
        
        // Also use timetable subjects map as backup (if available)
        $timetable_name_map = array();
        foreach ($timetable_subjects as $ts) {
            if (!empty($ts->id) && !empty($ts->name)) {
                $timetable_name_map[$ts->id] = $ts;
            }
        }
        
        // Fill in any remaining gaps from timetable map - but only if name is truly empty
        foreach ($subjects as $subject) {
            if ((empty($subject->name) || trim($subject->name) == '' || trim($subject->name) == '-') && !empty($subject->id) && isset($timetable_name_map[$subject->id])) {
                $ts = $timetable_name_map[$subject->id];
                if (!empty($ts->name) && trim($ts->name) != '') {
                    $subject->name = $ts->name;
                    $subject->subject_id = $ts->subject_id;
                    $subject->code = $ts->code;
                    $subject->type = $ts->type;
                    $subject->time_from = $ts->time_from;
                    $subject->time_to = $ts->time_to;
                }
            }
        }
        
        // IMPORTANT: Do NOT replace attendance subjects with timetable subjects
        // Attendance subjects are the source of truth - they represent actual attendance records
        // Timetable subjects are only used as a backup for names, not to determine which subjects to show
        // Only use timetable subjects if we have NO attendance records at all (which shouldn't happen in normal flow)
        if (empty($subjects) && !empty($timetable_subjects) && $attendance_count == 0) {
            $subjects = $timetable_subjects;
        }
        
        // Don't filter out subjects - keep all that have valid timetable_id
        $valid_subjects = array();
        foreach ($subjects as $subject) {
            // Keep subject if it has a valid timetable_id (id field)
            if (!empty($subject->id) && $subject->id != 0) {
                $valid_subjects[] = $subject;
            }
        }
        $subjects = $valid_subjects;
        
        // Store debug info about filtering
        $this->db_debug_info['subjects_before_filter'] = count($subjects) + (count($subjects) - count($valid_subjects));
        $this->db_debug_info['subjects_after_filter'] = count($valid_subjects);
        
        // Deduplicate by subject_timetable_id (not subject_id) to prevent duplicates
        // Use timetable_id as key since that's what we use for joining attendance
        $unique_subjects = array();
        $timetable_id_map = array(); // Map timetable_id => subject object
        
        foreach ($subjects as $subject) {
            $timetable_id = $subject->id; // This is the subject_timetable_id
            if (!empty($timetable_id) && $timetable_id != 0) {
                if (!isset($timetable_id_map[$timetable_id])) {
                    // First time seeing this timetable_id
                    $timetable_id_map[$timetable_id] = $subject;
                }
                // If duplicate timetable_id, keep the first one (should be from attendance query)
            }
        }
        $subjects = array_values($timetable_id_map);
        
        // CRITICAL: After deduplication, ensure ALL subjects have names - query one more time
        $final_timetable_ids = array();
        foreach ($subjects as $subject) {
            if ((empty($subject->name) || $subject->name == '') && !empty($subject->id) && $subject->id != 0) {
                $final_timetable_ids[] = $subject->id;
            }
        }
        
        if (!empty($final_timetable_ids)) {
            $final_ids_escaped = array_map(array($this->db, 'escape'), $final_timetable_ids);
            $final_name_query = "SELECT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                                FROM subject_timetable st
                                INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                INNER JOIN subjects s ON s.id = sgs.subject_id
                                WHERE st.id IN (" . implode(',', $final_ids_escaped) . ")";
            $final_name_results = $this->db->query($final_name_query)->result();
            
            $final_name_map = array();
            foreach ($final_name_results as $result) {
                if (!empty($result->name)) {
                    $final_name_map[$result->timetable_id] = $result;
                }
            }
            
            // Populate names
            foreach ($subjects as $subject) {
                if ((empty($subject->name) || $subject->name == '') && !empty($subject->id) && isset($final_name_map[$subject->id])) {
                    $name_data = $final_name_map[$subject->id];
                    $subject->name = $name_data->name;
                    $subject->subject_id = $name_data->subject_id;
                    $subject->code = $name_data->code;
                    $subject->type = $name_data->type;
                    $subject->time_from = $name_data->time_from;
                    $subject->time_to = $name_data->time_to;
                }
            }
        }
        
        // Store debug info
        $this->db_debug_info['subjects_after_deduplication'] = count($subjects);
        
        // Debug: Log subject details
        $subject_details = array();
        foreach ($subjects as $s) {
            $subject_details[] = array(
                'timetable_id' => isset($s->id) ? $s->id : 'null',
                'subject_id' => isset($s->subject_id) ? $s->subject_id : 'null',
                'name' => isset($s->name) ? $s->name : 'null'
            );
        }
        $this->db_debug_info['subject_details'] = $subject_details;
        
        // If no subjects found BUT we have attendance records, create subjects from attendance
        // This ensures subjects are shown whenever attendance exists
        if (empty($subjects) && $attendance_count > 0) {
            // Get distinct subject_timetable_ids from attendance records
            $attendance_ids_query = "SELECT DISTINCT ssa.subject_timetable_id as id
                                    FROM student_subject_attendances ssa
                                    INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                                    WHERE ss.class_id = " . $this->db->escape($class_id) . "
                                        AND ss.section_id = " . $this->db->escape($section_id) . "
                                        AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                        AND ssa.date = " . $this->db->escape($date) . "
                                        AND ssa.subject_timetable_id IS NOT NULL
                                        AND ssa.subject_timetable_id != 0";
            $attendance_ids_result = $this->db->query($attendance_ids_query)->result();
            
            // Create subject objects from attendance records
            foreach ($attendance_ids_result as $att_id) {
                $subject_obj = new stdClass();
                $subject_obj->id = $att_id->id;
                $subject_obj->subject_id = 0;
                $subject_obj->name = '';
                $subject_obj->code = '';
                $subject_obj->type = '';
                $subject_obj->time_from = '';
                $subject_obj->time_to = '';
                $subjects[] = $subject_obj;
            }
            
            // Now try to populate names for these subjects
        if (!empty($subjects)) {
                $timetable_ids_to_fetch = array();
                foreach ($subjects as $subject) {
                    if (!empty($subject->id) && $subject->id != 0) {
                        $timetable_ids_to_fetch[] = $subject->id;
                    }
                }
                
                if (!empty($timetable_ids_to_fetch)) {
                    $ids_escaped = array_map(array($this->db, 'escape'), $timetable_ids_to_fetch);
                    $batch_query = "SELECT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                                   FROM subjects s
                                   INNER JOIN subject_group_subjects sgs ON sgs.subject_id = s.id
                                   INNER JOIN subject_timetable st ON st.subject_group_subject_id = sgs.id
                                   WHERE st.id IN (" . implode(',', $ids_escaped) . ")";
                    $batch_results = $this->db->query($batch_query)->result();
                    
                    $name_map = array();
                    foreach ($batch_results as $result) {
                        $name_map[$result->timetable_id] = $result;
                    }
                    
                    foreach ($subjects as $subject) {
                        if (isset($name_map[$subject->id])) {
                            $name_data = $name_map[$subject->id];
                            $subject->name = $name_data->name;
                            $subject->subject_id = $name_data->subject_id;
                            $subject->code = $name_data->code;
                            $subject->type = $name_data->type;
                            $subject->time_from = $name_data->time_from;
                            $subject->time_to = $name_data->time_to;
                        }
                    }
                }
            }
        }
        
        // Final pass: Ensure ALL subjects have names - try one more time if any are missing
        $final_missing_ids = array();
        foreach ($subjects as $subject) {
            if ((empty($subject->name) || $subject->name == '') && !empty($subject->id) && $subject->id != 0) {
                $final_missing_ids[] = $subject->id;
            }
        }
        
        if (!empty($final_missing_ids)) {
            // Last attempt: Direct query from subject_timetable
            $final_ids_escaped = array_map(array($this->db, 'escape'), $final_missing_ids);
            $final_query = "SELECT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type
                           FROM subject_timetable st
                           INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                           INNER JOIN subjects s ON s.id = sgs.subject_id
                           WHERE st.id IN (" . implode(',', $final_ids_escaped) . ")";
            $final_results = $this->db->query($final_query)->result();
            
            $final_name_map = array();
            foreach ($final_results as $result) {
                if (!empty($result->name)) {
                    $final_name_map[$result->timetable_id] = $result;
                }
            }
            
            // Populate any remaining missing names
            foreach ($subjects as $subject) {
                if ((empty($subject->name) || $subject->name == '') && !empty($subject->id) && isset($final_name_map[$subject->id])) {
                    $name_data = $final_name_map[$subject->id];
                    $subject->name = $name_data->name;
                    $subject->subject_id = $name_data->subject_id;
                    if (!empty($name_data->code)) {
                        $subject->code = $name_data->code;
                    }
                    if (!empty($name_data->type)) {
                        $subject->type = $name_data->type;
                    }
                }
            }
        }
        
        // If still no subjects found, return false (original behavior)
        if (empty($subjects)) {
            return false;
        }
        
        // FINAL CHECK: Right before returning, ensure ALL subjects have names
        // Query ALL subject names one final time - this is the last chance
        $all_final_ids = array();
        foreach ($subjects as $subject) {
            if (!empty($subject->id) && $subject->id != 0) {
                $all_final_ids[] = $subject->id;
            }
        }
        
        if (!empty($all_final_ids)) {
            $all_final_ids_escaped = array_map(array($this->db, 'escape'), $all_final_ids);
            
            // Final comprehensive query - get ALL names
            $ultimate_query = "SELECT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                              FROM subject_timetable st
                              INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                              INNER JOIN subjects s ON s.id = sgs.subject_id
                              WHERE st.id IN (" . implode(',', $all_final_ids_escaped) . ")";
            $ultimate_results = $this->db->query($ultimate_query)->result();
            
            // Create final name map (int keys — timetable_id from MySQL can be string)
            $ultimate_name_map = array();
            foreach ($ultimate_results as $result) {
                if (!empty($result->timetable_id)) {
                    $ultimate_name_map[(int) $result->timetable_id] = $result;
                }
            }
            
            // OVERWRITE ALL subject names - force populate
            foreach ($subjects as $subject) {
                $tidKey = !empty($subject->id) ? (int) $subject->id : 0;
                if ($tidKey && isset($ultimate_name_map[$tidKey])) {
                    $name_data = $ultimate_name_map[$tidKey];
                    if (!empty($name_data->name) && trim((string) $name_data->name) !== '' && trim((string) $name_data->name) !== '-') {
                        $subject->name = $name_data->name;
                        $subject->subject_name = $name_data->name;
                    }
                    if (!empty($name_data->subject_id)) {
                        $subject->subject_id = $name_data->subject_id;
                    }
                    if (isset($name_data->code)) {
                        $subject->code = $name_data->code;
                    }
                    if (isset($name_data->type)) {
                        $subject->type = $name_data->type;
                    }
                    if (isset($name_data->time_from)) {
                        $subject->time_from = $name_data->time_from;
                    }
                    if (isset($name_data->time_to)) {
                        $subject->time_to = $name_data->time_to;
                    }
                } elseif (!empty($subject->id) && empty($subject->name)) {
                    // If still no name, try individual query as last resort
                    $last_resort_query = "SELECT s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                                         FROM subject_timetable st
                                         INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                         INNER JOIN subjects s ON s.id = sgs.subject_id
                                         WHERE st.id = " . $this->db->escape($subject->id) . "
                                         LIMIT 1";
                    $last_resort_result = $this->db->query($last_resort_query)->row();
                    if ($last_resort_result && !empty($last_resort_result->name)) {
                        $subject->name = $last_resort_result->name;
                        $subject->subject_id = $last_resort_result->subject_id;
                        $subject->code = $last_resort_result->code;
                        $subject->type = $last_resort_result->type;
                        $subject->time_from = $last_resort_result->time_from;
                        $subject->time_to = $last_resort_result->time_to;
                    }
                }
            }
        }
        
        // Store subject info for debugging - log final state
        $subject_debug = array();
        foreach ($subjects as $subject_key => $subject_value) {
            $subject_debug[] = array(
                'id' => $subject_value->id,
                'name' => $subject_value->name,
                'code' => isset($subject_value->code) ? $subject_value->code : ''
            );
        }
        
        // Build student query with attendance joins
            $count        = 1;
            $append_sql   = "";
            $append_param = "";
            foreach ($subjects as $subject_key => $subject_value) {
                $append_param .= ",student_subject_attendances_" . $count . ".attendence_type_id as attendence_type_id_" . $count;
                $append_sql .= " LEFT JOIN student_subject_attendances as student_subject_attendances_" . $count . " on  student_subject_attendances_" . $count . ".student_session_id=student_session.id and student_subject_attendances_" . $count . ".subject_timetable_id=" . $this->db->escape($subject_value->id) . " and student_subject_attendances_" . $count . ".date=" . $this->db->escape($date);
                $count++;
            }
        
        // Get all students for this class/section (regardless of whether they have attendance)
        $sql_student_record = "SELECT students.id,students.firstname,students.middlename,students.lastname,students.admission_no " . $append_param . " FROM `students` INNER JOIN student_session on students.id=student_session.student_id and student_session.class_id=" . $this->db->escape($class_id) . " AND student_session.section_id=" . $this->db->escape($section_id) . " AND student_session.session_id=" . $this->db->escape($this->current_session) . " AND student_session.is_active='yes' " . $append_sql . " WHERE students.is_active='yes'";
        
        // Check what attendance records actually exist for debugging
        $check_attendance_sql = "SELECT ssa.id, ssa.student_session_id, ssa.subject_timetable_id, ssa.date, ssa.attendence_type_id, 
                                st.subject_group_subject_id, sgs.subject_id, subjects.name as subject_name
                                FROM student_subject_attendances ssa
                                INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
                                INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                INNER JOIN subjects ON subjects.id = sgs.subject_id
                                INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                                WHERE ss.class_id = " . $this->db->escape($class_id) . "
                                    AND ss.section_id = " . $this->db->escape($section_id) . "
                                    AND ss.session_id = " . $this->db->escape($this->current_session) . "
                                    AND ssa.date = " . $this->db->escape($date) . "
                                LIMIT 10";
        $check_attendance_query = $this->db->query($check_attendance_sql);
        $existing_attendance = $check_attendance_query->result();

            $query              = $this->db->query($sql_student_record);
            $student_record     = $query->result();
        
        // Convert stored dates to arrays for JSON encoding
        $stored_dates_array = array();
        foreach ($stored_dates as $sd) {
            $stored_dates_array[] = array(
                'date' => $sd->date,
                'count' => $sd->count
            );
        }
        
        $existing_attendance_array = array();
        foreach ($existing_attendance as $ea) {
            $existing_attendance_array[] = array(
                'id' => $ea->id,
                'student_session_id' => $ea->student_session_id,
                'subject_timetable_id' => $ea->subject_timetable_id,
                'date' => $ea->date,
                'attendence_type_id' => $ea->attendence_type_id,
                'subject_name' => $ea->subject_name
            );
        }
        
        // ABSOLUTE FINAL CHECK: Right before JSON encoding, ensure NO subject has empty name or "-"
        // This MUST run and MUST populate names - query each subject individually if needed
        $final_fix_attempts = array();
        foreach ($subjects as $subject) {
            // Check if name is empty, null, "-", or just whitespace
            $current_name = isset($subject->name) ? $subject->name : 'NOT SET';
            if ((empty($subject->name) || trim($subject->name) == '' || trim($subject->name) == '-') && !empty($subject->id)) {
                // Convert ID to int to ensure proper matching
                $timetable_id = intval($subject->id);
                
                // Last resort: individual query for THIS specific timetable_id
                $final_individual_query = "SELECT s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                                          FROM subject_timetable st
                                          INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                          INNER JOIN subjects s ON s.id = sgs.subject_id
                                          WHERE st.id = " . $this->db->escape($timetable_id) . "
                                          LIMIT 1";
                $final_individual_result = $this->db->query($final_individual_query)->row();
                
                $final_fix_attempts[] = array(
                    'timetable_id' => $timetable_id,
                    'old_name' => $current_name,
                    'query_executed' => true,
                    'result_found' => $final_individual_result ? true : false,
                    'result_name' => $final_individual_result ? $final_individual_result->name : 'NO RESULT'
                );
                
                if ($final_individual_result && !empty($final_individual_result->name) && trim($final_individual_result->name) != '-') {
                    $subject->name = $final_individual_result->name;
                    $subject->subject_id = $final_individual_result->subject_id;
                    $subject->code = $final_individual_result->code;
                    $subject->type = $final_individual_result->type;
                    $subject->time_from = $final_individual_result->time_from;
                    $subject->time_to = $final_individual_result->time_to;
                } else {
                    // If still no name, try one more query using JOIN instead of subquery with LIMIT
                    $desperate_query = "SELECT s.name, s.id as subject_id
                                       FROM subjects s
                                       INNER JOIN subject_group_subjects sgs ON sgs.subject_id = s.id
                                       INNER JOIN subject_timetable st ON st.subject_group_subject_id = sgs.id
                                       WHERE st.id = " . $this->db->escape($timetable_id) . "
                                       LIMIT 1";
                    $desperate_result = $this->db->query($desperate_query)->row();
                    if ($desperate_result && !empty($desperate_result->name)) {
                        $subject->name = $desperate_result->name;
                        $subject->subject_id = $desperate_result->subject_id;
                    }
                }
            }
        }
        
        // Merge debug info instead of overwriting - preserve all the detailed debug info
        $existing_debug = isset($this->db_debug_info) ? $this->db_debug_info : array();
        $this->db_debug_info = array_merge($existing_debug, array(
            'subjects_found' => count($subjects),
            'subjects' => $subject_debug,
            'students_found' => count($student_record),
            'existing_attendance_count' => count($existing_attendance),
            'existing_attendance' => $existing_attendance_array,
            'search_date' => $date,
            'search_day' => $day,
            'stored_dates' => $stored_dates_array,
            'class_id' => $class_id,
            'section_id' => $section_id,
            'first_student_attendance' => !empty($student_record) ? (array)$student_record[0] : null,
            'final_fix_attempts' => $final_fix_attempts,
            'subjects_final_before_json' => array()
        ));
        
        // Log final state of ALL subjects right before JSON encoding
        foreach ($subjects as $s) {
            $this->db_debug_info['subjects_final_before_json'][] = array(
                'id' => $s->id,
                'name' => isset($s->name) ? $s->name : 'NOT SET',
                'name_length' => isset($s->name) ? strlen($s->name) : 0,
                'subject_id' => isset($s->subject_id) ? $s->subject_id : 'NOT SET'
            );
        }
        
        // LAST CHANCE: Right before JSON encoding, query ALL names one final time
        // Convert all IDs to integers and query in one batch
        $final_ids_int = array();
        foreach ($subjects as $subject) {
            if (!empty($subject->id)) {
                $final_ids_int[] = intval($subject->id);
            }
        }
        
        if (!empty($final_ids_int)) {
            $final_ids_escaped = array_map(array($this->db, 'escape'), $final_ids_int);
            $last_chance_query = "SELECT st.id as timetable_id, s.name, s.id as subject_id, s.code, s.type, st.time_from, st.time_to
                                 FROM subject_timetable st
                                 INNER JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
                                 INNER JOIN subjects s ON s.id = sgs.subject_id
                                 WHERE st.id IN (" . implode(',', $final_ids_escaped) . ")";
            $last_chance_results = $this->db->query($last_chance_query)->result();
            
            // Create map with integer keys
            $last_chance_map = array();
            foreach ($last_chance_results as $result) {
                if (!empty($result->name)) {
                    $last_chance_map[intval($result->timetable_id)] = $result;
                }
            }
            
            // Populate names - but preserve existing names
            foreach ($subjects as $subject) {
                // Only populate if name is empty or missing
                if (!empty($subject->id) && (empty($subject->name) || trim($subject->name) == '' || trim($subject->name) == '-')) {
                    $subject_id_int = intval($subject->id);
                    if (isset($last_chance_map[$subject_id_int])) {
                        $name_data = $last_chance_map[$subject_id_int];
                        if (!empty($name_data->name)) {
                            $subject->name = $name_data->name;
                            $subject->subject_id = $name_data->subject_id;
                            $subject->code = $name_data->code;
                            $subject->type = $name_data->type;
                            $subject->time_from = $name_data->time_from;
                            $subject->time_to = $name_data->time_to;
                        }
                    }
                }
            }
        }
        
        // DEBUG: Log final state before returning
        $this->db_debug_info['step8_final_before_return'] = array();
        $this->db_debug_info['step8_final_before_return']['subjects_count'] = count($subjects);
        $this->db_debug_info['step8_final_before_return']['students_count'] = count($student_record);
        $this->db_debug_info['step8_final_before_return']['subjects'] = array();
        foreach ($subjects as $s) {
            $this->db_debug_info['step8_final_before_return']['subjects'][] = array(
                'id' => isset($s->id) ? $s->id : 'NULL',
                'subject_id' => isset($s->subject_id) ? $s->subject_id : 'NULL',
                'name' => isset($s->name) ? ($s->name == '' ? 'EMPTY_STRING' : $s->name) : 'NULL',
                'code' => isset($s->code) ? $s->code : 'NULL',
                'type' => isset($s->type) ? $s->type : 'NULL'
            );
        }

        $this->ensureSubjectDisplayNames($subjects, $class_id, $section_id);
        $this->applyFinalSubjectLabelsFromTimetable($subjects);
        $this->fillSubjectNamesFromAttendanceContext($subjects, $class_id, $section_id, $date);

        // Return native PHP structure (controller/view). Avoid json_encode round-trip which can drop labels.
        if (!empty($student_record)) {
            return array('subjects' => $subjects, 'student_record' => $student_record);
        }
        if (!empty($subjects)) {
            return array('subjects' => $subjects, 'student_record' => array());
        }

        return false;
    }

    /**
     * Last resort for report-by-date: resolve labels from rows that actually have attendance on this date,
     * using COALESCE(subject via sgs, subject via direct id, subject group name).
     */
    private function fillSubjectNamesFromAttendanceContext(&$subjects, $class_id, $section_id, $date)
    {
        if (empty($subjects) || !is_array($subjects) || $date === null || $date === '') {
            return;
        }
        $need = array();
        foreach ($subjects as $sub) {
            if (!is_object($sub) || empty($sub->id)) {
                continue;
            }
            $n = isset($sub->name) ? trim((string) $sub->name) : '';
            $sn = isset($sub->subject_name) ? trim((string) $sub->subject_name) : '';
            if (($n === '' || $n === '-') && ($sn === '' || $sn === '-')) {
                $need[] = (int) $sub->id;
            }
        }
        $need = array_values(array_unique(array_filter($need)));
        if (empty($need)) {
            return;
        }
        $in = implode(',', array_map('intval', $need));
        $sess = $this->db->escape($this->current_session);
        $dt = $this->db->escape($date);
        $sql = "SELECT ssa.subject_timetable_id AS timetable_id,
                MAX(COALESCE(
                    NULLIF(TRIM(s.name), ''),
                    NULLIF(TRIM(sd.name), ''),
                    NULLIF(TRIM(s.code), ''),
                    NULLIF(TRIM(sd.code), ''),
                    NULLIF(TRIM(sg.name), '')
                )) AS disp_name,
                MAX(COALESCE(s.id, sd.id)) AS subject_id,
                MAX(COALESCE(NULLIF(TRIM(s.code), ''), NULLIF(TRIM(sd.code), ''))) AS code
            FROM student_subject_attendances ssa
            INNER JOIN student_session ss ON ss.id = ssa.student_session_id
                AND ss.class_id = " . $this->db->escape($class_id) . "
                AND ss.section_id = " . $this->db->escape($section_id) . "
                AND ss.session_id = " . $sess . "
            INNER JOIN subject_timetable st ON st.id = ssa.subject_timetable_id
            LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
            LEFT JOIN subjects s ON s.id = sgs.subject_id
            LEFT JOIN subjects sd ON sd.id = st.subject_group_subject_id
            LEFT JOIN subject_groups sg ON sg.id = st.subject_group_id
            WHERE ssa.subject_timetable_id IN (" . $in . ")
            AND ssa.date = " . $dt . "
            GROUP BY ssa.subject_timetable_id";
        $rows = $this->db->query($sql)->result();
        $byTid = array();
        foreach ($rows as $r) {
            if (empty($r->timetable_id)) {
                continue;
            }
            $byTid[(int) $r->timetable_id] = $r;
        }
        foreach ($subjects as $subject) {
            if (!is_object($subject) || empty($subject->id)) {
                continue;
            }
            $tid = (int) $subject->id;
            if (!isset($byTid[$tid])) {
                continue;
            }
            $r = $byTid[$tid];
            $lbl = isset($r->disp_name) ? trim((string) $r->disp_name) : '';
            if ($lbl === '' || $lbl === '-') {
                continue;
            }
            $n = isset($subject->name) ? trim((string) $subject->name) : '';
            if ($n === '' || $n === '-') {
                $subject->name = $lbl;
            }
            $sn = isset($subject->subject_name) ? trim((string) $subject->subject_name) : '';
            if ($sn === '' || $sn === '-') {
                $subject->subject_name = $lbl;
            }
            if (empty($subject->subject_id) && !empty($r->subject_id)) {
                $subject->subject_id = (int) $r->subject_id;
            }
            if ((empty($subject->code) || trim((string) $subject->code) === '') && !empty($r->code)) {
                $subject->code = $r->code;
            }
        }
    }

    /**
     * Single batch query: resolve display name from every possible link (sgs, direct id, subject group title).
     */
    private function applyFinalSubjectLabelsFromTimetable(&$subjects)
    {
        if (empty($subjects) || !is_array($subjects)) {
            return;
        }
        $ids = array();
        foreach ($subjects as $s) {
            if (!is_object($s)) {
                continue;
            }
            $tid = isset($s->id) ? (int) $s->id : 0;
            if ($tid > 0) {
                $ids[] = $tid;
            }
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            return;
        }
        $in = implode(',', array_map('intval', $ids));
        $sql = "SELECT st.id AS timetable_id,
                s.name AS nm_sgs,
                s.code AS cd_sgs,
                sd.name AS nm_dir,
                sd.code AS cd_dir,
                sg.name AS nm_group
            FROM subject_timetable st
            LEFT JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
            LEFT JOIN subjects s ON s.id = sgs.subject_id
            LEFT JOIN subjects sd ON sd.id = st.subject_group_subject_id
            LEFT JOIN subject_groups sg ON sg.id = st.subject_group_id
            WHERE st.id IN (" . $in . ")";
        $rows = $this->db->query($sql)->result();
        $map = array();
        foreach ($rows as $r) {
            $map[(int) $r->timetable_id] = $r;
        }
        foreach ($subjects as $subject) {
            if (!is_object($subject) || empty($subject->id)) {
                continue;
            }
            $tid = (int) $subject->id;
            if (!isset($map[$tid])) {
                continue;
            }
            $r = $map[$tid];
            $candidates = array(
                isset($r->nm_sgs) ? $r->nm_sgs : '',
                isset($r->nm_dir) ? $r->nm_dir : '',
                isset($r->cd_sgs) ? $r->cd_sgs : '',
                isset($r->cd_dir) ? $r->cd_dir : '',
                isset($r->nm_group) ? $r->nm_group : '',
            );
            $label = '';
            foreach ($candidates as $c) {
                $t = trim((string) $c);
                if ($t !== '' && $t !== '-') {
                    $label = $t;
                    break;
                }
            }
            if ($label !== '') {
                $subject->name         = $label;
                $subject->subject_name = $label;
            }
        }
    }

    public function attendanceYearCount()
    {

        $query = $this->db->select("distinct year(date) as year")->get("student_subject_attendances");

        return $query->result_array();
    }

    public function is_biometricAttendence()
    {

        $this->db->select('sch_settings.id,sch_settings.biometric,sch_settings.attendence_type,sch_settings.is_rtl,sch_settings.timezone,
          sch_settings.name,sch_settings.email,sch_settings.biometric,sch_settings.biometric_device,sch_settings.phone,languages.language,
          sch_settings.address,sch_settings.dise_code,sch_settings.date_format,sch_settings.currency,sch_settings.currency_symbol,sch_settings.start_month,sch_settings.session_id,sch_settings.image,sch_settings.theme,sessions.session'
        );

        $this->db->from('sch_settings');
        $this->db->join('sessions', 'sessions.id = sch_settings.session_id');
        $this->db->join('languages', 'languages.id = sch_settings.lang_id');
        $this->db->order_by('sch_settings.id');
        $query  = $this->db->get();
        $result = $query->row();

        if ($result->biometric) {
            return true;
        }

        return false;
    }

}
