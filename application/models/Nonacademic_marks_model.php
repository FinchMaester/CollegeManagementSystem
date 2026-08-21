<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Nonacademic_marks_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get non-academic marks for a student
     * @param int $exam_id
     * @param int $student_id
     * @return array
     */
    public function getStudentNonAcademicMarks($exam_id, $student_id)
    {
        $this->db->select('nonacademic_marks.*, subjects.name as subject_name, subjects.code as subject_code');
        $this->db->from('nonacademic_marks');
        $this->db->join('subjects', 'subjects.id = nonacademic_marks.subject_id');
        $this->db->where('nonacademic_marks.exam_id', $exam_id);
        $this->db->where('nonacademic_marks.student_id', $student_id);
        $this->db->order_by('subjects.name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get non-academic marks for multiple students
     * @param int $exam_id
     * @param array $student_ids
     * @return array
     */
    public function getStudentsNonAcademicMarks($exam_id, $student_ids = array())
    {
        $this->db->select('nonacademic_marks.*, subjects.name as subject_name, subjects.code as subject_code');
        $this->db->from('nonacademic_marks');
        $this->db->join('subjects', 'subjects.id = nonacademic_marks.subject_id');
        $this->db->where('nonacademic_marks.exam_id', $exam_id);
        if (!empty($student_ids)) {
            $this->db->where_in('nonacademic_marks.student_id', $student_ids);
        }
        $this->db->order_by('nonacademic_marks.student_id', 'ASC');
        $this->db->order_by('subjects.name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Save or update non-academic marks
     * @param array $data
     * @return bool|int
     */
    public function saveNonAcademicMarks($data)
    {
        // Check if record exists
        $this->db->where('exam_id', $data['exam_id']);
        $this->db->where('student_id', $data['student_id']);
        $this->db->where('subject_id', $data['subject_id']);
        $query = $this->db->get('nonacademic_marks');

        if ($query->num_rows() > 0) {
            // Update existing record
            $this->db->where('exam_id', $data['exam_id']);
            $this->db->where('student_id', $data['student_id']);
            $this->db->where('subject_id', $data['subject_id']);
            $this->db->update('nonacademic_marks', $data);
            return $query->row()->id;
        } else {
            // Insert new record
            $this->db->insert('nonacademic_marks', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Save multiple non-academic marks (bulk save)
     * @param array $marks_data Array of marks data
     * @return bool
     */
    public function saveBulkNonAcademicMarks($marks_data)
    {
        if (empty($marks_data)) {
            return false;
        }

        $this->db->trans_start();

        foreach ($marks_data as $mark_data) {
            // Check if record exists
            $this->db->where('exam_id', $mark_data['exam_id']);
            $this->db->where('student_id', $mark_data['student_id']);
            $this->db->where('subject_id', $mark_data['subject_id']);
            $query = $this->db->get('nonacademic_marks');

            if ($query->num_rows() > 0) {
                // Update existing
                $this->db->where('exam_id', $mark_data['exam_id']);
                $this->db->where('student_id', $mark_data['student_id']);
                $this->db->where('subject_id', $mark_data['subject_id']);
                $this->db->update('nonacademic_marks', $mark_data);
            } else {
                // Insert new
                $this->db->insert('nonacademic_marks', $mark_data);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }
        return true;
    }

    /**
     * Get non-academic subjects for a class and section (filtered through subject groups)
     * Similar to how students are assigned - subjects are available based on class/section, not exam assignment
     * @param int $class_id
     * @param int $section_id
     * @param int $session_id
     * @return array
     */
    public function getNonAcademicSubjects($class_id = null, $section_id = null, $session_id = null)
    {
        if (!$class_id || !$section_id || !$session_id) {
            return array();
        }
        
        // Get class_section_id
        $class_section = $this->db->select('id')
            ->from('class_sections')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get()
            ->row();
        
        if (!$class_section) {
            return array();
        }
        
        // Get non-academic subjects through subject groups (same as how subjects are associated with class/section)
        $sql = "SELECT DISTINCT subjects.id, subjects.name, subjects.code, subjects.type, subjects.is_academic
                FROM subject_group_class_sections
                INNER JOIN subject_groups ON subject_groups.id = subject_group_class_sections.subject_group_id
                INNER JOIN subject_group_subjects ON subject_group_subjects.subject_group_id = subject_groups.id
                INNER JOIN subjects ON subjects.id = subject_group_subjects.subject_id
                WHERE subject_group_class_sections.class_section_id = ?
                AND subject_group_class_sections.session_id = ?
                AND subjects.is_academic = 0
                ORDER BY subjects.name ASC";
        
        $query = $this->db->query($sql, array($class_section->id, $session_id));
        $subjects = $query->result_array();
        
        // If no subjects found in subject groups, fallback to all non-academic subjects
        // This allows non-academic subjects to be used even if not assigned to subject groups
        if (empty($subjects)) {
            $this->db->select('id, name, code, type, is_academic');
            $this->db->from('subjects');
            $this->db->where('is_academic', 0);
            $this->db->order_by('name', 'ASC');
            $fallback_query = $this->db->get();
            $subjects = $fallback_query->result_array();
        }
        
        return $subjects;
    }

    /**
     * Delete non-academic marks
     * @param int $exam_id
     * @param int $student_id
     * @param int $subject_id
     * @return bool
     */
    public function deleteNonAcademicMarks($exam_id, $student_id, $subject_id = null)
    {
        $this->db->where('exam_id', $exam_id);
        $this->db->where('student_id', $student_id);
        if ($subject_id !== null) {
            $this->db->where('subject_id', $subject_id);
        }
        $this->db->delete('nonacademic_marks');
        return $this->db->affected_rows() > 0;
    }
}

