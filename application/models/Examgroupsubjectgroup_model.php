<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Examgroupsubjectgroup_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    /**
     * Get subject groups assigned to an exam group
     * @param int $exam_group_id
     * @return array
     */
    public function getSubjectGroupsByExamGroup($exam_group_id)
    {
        $this->db->select('exam_group_subject_groups.*, subject_groups.name, subject_groups.id as subject_group_id');
        $this->db->from('exam_group_subject_groups');
        $this->db->join('subject_groups', 'subject_groups.id = exam_group_subject_groups.subject_group_id');
        $this->db->where('exam_group_subject_groups.exam_group_id', $exam_group_id);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get all subject groups with assignment status for an exam group
     * @param int $exam_group_id
     * @return array
     */
    public function getAllSubjectGroupsWithAssignment($exam_group_id)
    {
        $sql = "SELECT 
                    IFNULL(exam_group_subject_groups.id, 0) as `exam_group_subject_group_id`,
                    subject_groups.id as `subject_group_id`,
                    subject_groups.name as `subject_group_name`,
                    subject_groups.session_id
                FROM `subject_groups`
                LEFT JOIN exam_group_subject_groups 
                    ON exam_group_subject_groups.exam_group_id = " . $this->db->escape($exam_group_id) . "
                    AND exam_group_subject_groups.subject_group_id = subject_groups.id
                WHERE subject_groups.session_id = " . $this->db->escape($this->current_session) . "
                ORDER BY subject_groups.name ASC";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * Add/Remove subject groups for an exam group
     * @param array $data_insert Subject groups to add
     * @param array $data_delete Subject group IDs to remove
     * @param int $exam_group_id
     * @return bool
     */
    public function add($data_insert, $data_delete, $exam_group_id)
    {
        $this->db->trans_begin();
        
        // Insert new assignments
        if (!empty($data_insert)) {
            foreach ($data_insert as $subject_group_key => $subject_group_value) {
                $this->db->where('exam_group_id', $subject_group_value['exam_group_id']);
                $this->db->where('subject_group_id', $subject_group_value['subject_group_id']);
                $q = $this->db->get('exam_group_subject_groups');
                
                if ($q->num_rows() == 0) {
                    $this->db->insert('exam_group_subject_groups', $data_insert[$subject_group_key]);
                }
            }
        }
        
        // Delete removed assignments
        if (!empty($data_delete)) {
            $this->db->where('exam_group_id', $exam_group_id);
            $this->db->where_in('subject_group_id', $data_delete);
            $this->db->delete('exam_group_subject_groups');
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
     * Get all subject IDs from subject groups assigned to an exam group
     * @param int $exam_group_id
     * @return array Array of subject IDs
     */
    public function getSubjectIdsByExamGroup($exam_group_id)
    {
        $sql = "SELECT DISTINCT subject_group_subjects.subject_id
                FROM exam_group_subject_groups
                INNER JOIN subject_group_subjects 
                    ON subject_group_subjects.subject_group_id = exam_group_subject_groups.subject_group_id
                WHERE exam_group_subject_groups.exam_group_id = " . $this->db->escape($exam_group_id) . "
                    AND subject_group_subjects.session_id = " . $this->db->escape($this->current_session);
        $query = $this->db->query($sql);
        $result = $query->result_array();
        
        $subject_ids = array();
        if (!empty($result)) {
            foreach ($result as $row) {
                $subject_ids[] = $row['subject_id'];
            }
        }
        
        return $subject_ids;
    }

    /**
     * Get all subject groups with assignment status for a specific exam (exam_group_class_batch_exam_id).
     * Used for per-exam "Assign Subject Group" in the exam list.
     *
     * @param int $exam_group_class_batch_exam_id
     * @return array
     */
    public function getAllSubjectGroupsWithAssignmentForExam($exam_group_class_batch_exam_id)
    {
        if (!$this->examSubjectGroupsTableExists()) {
            $this->db->select('0 AS exam_subject_group_id, id AS subject_group_id, name AS subject_group_name, session_id');
            $this->db->from('subject_groups');
            $this->db->where('session_id', $this->current_session);
            $this->db->order_by('name', 'ASC');
            $query = $this->db->get();
            return $query ? $query->result_array() : array();
        }
        $sql = "SELECT 
                    IFNULL(exam_subject_groups.id, 0) AS `exam_subject_group_id`,
                    subject_groups.id AS `subject_group_id`,
                    subject_groups.name AS `subject_group_name`,
                    subject_groups.session_id
                FROM `subject_groups`
                LEFT JOIN exam_subject_groups 
                    ON exam_subject_groups.exam_group_class_batch_exam_id = " . $this->db->escape($exam_group_class_batch_exam_id) . "
                    AND exam_subject_groups.subject_group_id = subject_groups.id
                WHERE subject_groups.session_id = " . $this->db->escape($this->current_session) . "
                ORDER BY subject_groups.name ASC";
        $query = $this->db->query($sql);
        return $query ? $query->result_array() : array();
    }

    /**
     * Get all subject IDs from all subject groups assigned to this exam.
     * Runs both session-filtered and non-session queries and merges so that subjects
     * from every assigned group are included (some groups may have session_id different or null).
     * If none assigned, returns empty array (caller can fallback to exam group).
     *
     * @param int $exam_group_class_batch_exam_id
     * @return array
     */
    public function getSubjectIdsByExam($exam_group_class_batch_exam_id)
    {
        if (!$this->examSubjectGroupsTableExists()) {
            return array();
        }
        $this->db->reset_query();
        $subject_ids = array();
        $exam_id = (int) $exam_group_class_batch_exam_id;

        // Query 1: subjects from subject_group_subjects matching current session
        $this->db->distinct();
        $this->db->select('subject_group_subjects.subject_id');
        $this->db->from('exam_subject_groups');
        $this->db->join('subject_group_subjects', 'subject_group_subjects.subject_group_id = exam_subject_groups.subject_group_id');
        $this->db->where('exam_subject_groups.exam_group_class_batch_exam_id', $exam_id);
        $this->db->where('subject_group_subjects.session_id', $this->current_session);
        $query = $this->db->get();
        if ($query) {
            foreach ($query->result_array() as $row) {
                $id = isset($row['subject_id']) ? (int) $row['subject_id'] : 0;
                if ($id > 0) {
                    $subject_ids[$id] = true;
                }
            }
        }

        // Query 2: subjects from all subject groups for this exam without session filter,
        // so we include groups whose subject_group_subjects use a different or null session_id
        $this->db->reset_query();
        $this->db->distinct();
        $this->db->select('subject_group_subjects.subject_id');
        $this->db->from('exam_subject_groups');
        $this->db->join('subject_group_subjects', 'subject_group_subjects.subject_group_id = exam_subject_groups.subject_group_id');
        $this->db->where('exam_subject_groups.exam_group_class_batch_exam_id', $exam_id);
        $q2 = $this->db->get();
        if ($q2) {
            foreach ($q2->result_array() as $row) {
                $id = isset($row['subject_id']) ? (int) $row['subject_id'] : 0;
                if ($id > 0) {
                    $subject_ids[$id] = true;
                }
            }
        }

        return array_keys($subject_ids);
    }

    /**
     * Check if exam_subject_groups table exists.
     * @return bool
     */
    public function examSubjectGroupsTableExists()
    {
        $q = $this->db->query("SHOW TABLES LIKE 'exam_subject_groups'");
        return $q && $q->num_rows() > 0;
    }

    /**
     * Whether this exam has subject groups assigned to it (per-exam in exam_subject_groups).
     * When true, Exam Subject list must use only subjects from those groups (never exam group or all subjects).
     *
     * @param int $exam_group_class_batch_exam_id
     * @return bool
     */
    public function examHasSubjectGroupsAssigned($exam_group_class_batch_exam_id)
    {
        if (!$this->examSubjectGroupsTableExists()) {
            return false;
        }
        $this->db->reset_query();
        $this->db->from('exam_subject_groups');
        $this->db->where('exam_group_class_batch_exam_id', (int) $exam_group_class_batch_exam_id);
        return $this->db->count_all_results() > 0;
    }

    /**
     * Add/Remove subject groups for a specific exam.
     *
     * @param array $data_insert [ ['exam_group_class_batch_exam_id' => x, 'subject_group_id' => y], ... ]
     * @param array $data_delete Subject group IDs to remove
     * @param int   $exam_group_class_batch_exam_id
     * @return bool
     */
    public function addForExam($data_insert, $data_delete, $exam_group_class_batch_exam_id)
    {
        if (!$this->examSubjectGroupsTableExists()) {
            return true;
        }
        $this->db->trans_begin();

        if (!empty($data_insert)) {
            foreach ($data_insert as $row) {
                $this->db->where('exam_group_class_batch_exam_id', $row['exam_group_class_batch_exam_id']);
                $this->db->where('subject_group_id', $row['subject_group_id']);
                $q = $this->db->get('exam_subject_groups');
                if ($q->num_rows() == 0) {
                    $this->db->insert('exam_subject_groups', $row);
                }
            }
        }

        if (!empty($data_delete)) {
            $this->db->where('exam_group_class_batch_exam_id', $exam_group_class_batch_exam_id);
            $this->db->where_in('subject_group_id', $data_delete);
            $this->db->delete('exam_subject_groups');
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    /**
     * Get subject group IDs assigned to this exam (per-exam first, then exam group).
     * @param int $exam_group_class_batch_exam_id
     * @param int|null $exam_group_id optional, from exam detail
     * @return array [ subject_group_id, ... ]
     */
    public function getSubjectGroupIdsByExam($exam_group_class_batch_exam_id, $exam_group_id = null)
    {
        $ids = array();
        if ($this->examSubjectGroupsTableExists()) {
            $this->db->select('subject_group_id');
            $this->db->from('exam_subject_groups');
            $this->db->where('exam_group_class_batch_exam_id', (int) $exam_group_class_batch_exam_id);
            $q = $this->db->get();
            if ($q && $q->num_rows() > 0) {
                foreach ($q->result_array() as $row) {
                    $ids[] = (int) $row['subject_group_id'];
                }
                return array_unique($ids);
            }
        }
        if ($exam_group_id) {
            $this->db->select('subject_group_id');
            $this->db->from('exam_group_subject_groups');
            $this->db->where('exam_group_id', (int) $exam_group_id);
            $q = $this->db->get();
            if ($q && $q->num_rows() > 0) {
                foreach ($q->result_array() as $row) {
                    $ids[] = (int) $row['subject_group_id'];
                }
                return array_unique($ids);
            }
        }
        return $ids;
    }

    /**
     * Get levels (section + program + class) linked to subject groups assigned to this exam.
     * Used to restrict "Assign Student" to only levels that belong to the exam's subject group(s).
     *
     * @param int $exam_group_class_batch_exam_id
     * @param int|null $exam_group_id
     * @return array of objects with section_id, section, program_id, program_name, class_id, class
     */
    public function getLevelsByExam($exam_group_class_batch_exam_id, $exam_group_id = null)
    {
        $subject_group_ids = $this->getSubjectGroupIdsByExam($exam_group_class_batch_exam_id, $exam_group_id);
        if (empty($subject_group_ids)) {
            return array();
        }
        $this->db->select('sections.id AS section_id, sections.section,
            IFNULL(programs.id, 0) AS program_id, IFNULL(programs.title, "") AS program_name,
            classes.id AS class_id, classes.class');
        $this->db->from('subject_group_class_sections');
        $this->db->join('class_sections', 'class_sections.id = subject_group_class_sections.class_section_id');
        $this->db->join('classes', 'classes.id = class_sections.class_id');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->join('programs', 'programs.id = subject_group_class_sections.program_id', 'left');
        $this->db->where('subject_group_class_sections.session_id', $this->current_session);
        $this->db->where_in('subject_group_class_sections.subject_group_id', $subject_group_ids);
        $this->db->group_by(array('sections.id', 'sections.section', 'classes.id', 'classes.class', 'programs.id', 'programs.title'));
        $this->db->order_by('sections.section');
        $this->db->order_by('programs.title');
        $this->db->order_by('classes.class');
        $query = $this->db->get();
        return $query ? $query->result() : array();
    }
}

