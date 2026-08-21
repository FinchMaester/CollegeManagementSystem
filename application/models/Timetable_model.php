<?php


if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class Timetable_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }


    public function remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('subject_timetable');
    }


    public function add($delete_array, $insert_array, $update_array)
    {
        $this->db->trans_start();


        // Delete records if any
        if (!empty($delete_array)) {
            $this->db->where_in('id', $delete_array);
            $this->db->delete('subject_timetable');
        }


        // Insert new records
        if (!empty($insert_array)) {
            $this->db->insert_batch('subject_timetable', $insert_array);
        }


        // Update existing records
        if (!empty($update_array)) {
            foreach ($update_array as $update) {
                $this->db->where('id', $update['id']);
                $this->db->update('subject_timetable', $update);
            }
        }


        $this->db->trans_complete();


        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        return true;
    }


    public function get($data)
    {
        $this->db->select('subject_timetable.*, subjects.name as subject_name, staff.name as staff_name')
            ->from('subject_timetable')
            ->join('subjects', 'subjects.id = subject_timetable.subject_group_subject_id', 'left')
            ->join('staff', 'staff.id = subject_timetable.staff_id', 'left')
            ->where($data)
            ->where('subject_timetable.session_id', $this->current_session);
           
        $query = $this->db->get();
        return $query->result_array();
    }


    public function getBySubjectGroupDayClassSection($subject_group_id, $day, $class_id, $section_id)
    {
        return $this->db->select('subject_timetable.*, subjects.name as subject_name, staff.name as staff_name')
            ->from('subject_timetable')
            ->join('subjects', 'subjects.id = subject_timetable.subject_group_subject_id', 'left')
            ->join('staff', 'staff.id = subject_timetable.staff_id', 'left')
            ->where('subject_timetable.subject_group_id', $subject_group_id)
            ->where('subject_timetable.day', $day)
            ->where('subject_timetable.class_id', $class_id)
            ->where('subject_timetable.section_id', $section_id)
            ->where('subject_timetable.session_id', $this->current_session)
            ->get()
            ->result_array();
    }
}



