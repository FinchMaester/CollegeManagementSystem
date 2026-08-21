<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Marksheet_model extends MY_model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->ensure_marksheet_template_schema();
    }

    /**
     * Adds dynamic marksheet columns if missing (safe to run repeatedly).
     */
    public function ensure_marksheet_template_schema()
    {
        if (!$this->db->table_exists('template_marksheets')) {
            return;
        }
        if (!$this->db->field_exists('column_layout_json', 'template_marksheets')) {
            $this->db->query('ALTER TABLE `template_marksheets` ADD `column_layout_json` LONGTEXT NULL');
        }
        if (!$this->db->field_exists('marksheet_display_mode', 'template_marksheets')) {
            $this->db->query("ALTER TABLE `template_marksheets` ADD `marksheet_display_mode` VARCHAR(32) NOT NULL DEFAULT 'legacy'");
        }
        if (!$this->db->field_exists('grading_system_mode', 'template_marksheets')) {
            $this->db->query("ALTER TABLE `template_marksheets` ADD `grading_system_mode` VARCHAR(32) NOT NULL DEFAULT 'inherit'");
        }
        if (!$this->db->field_exists('marksheet_show_grading_details', 'template_marksheets')) {
            $this->db->query('ALTER TABLE `template_marksheets` ADD `marksheet_show_grading_details` TINYINT(1) NOT NULL DEFAULT 1');
        }
        if (!$this->db->field_exists('marksheet_show_attendance_block', 'template_marksheets')) {
            $this->db->query('ALTER TABLE `template_marksheets` ADD `marksheet_show_attendance_block` TINYINT(1) NOT NULL DEFAULT 1');
        }
        if (!$this->db->field_exists('marksheet_show_nonacademic_block', 'template_marksheets')) {
            $this->db->query('ALTER TABLE `template_marksheets` ADD `marksheet_show_nonacademic_block` TINYINT(1) NOT NULL DEFAULT 1');
        }
        if (!$this->db->field_exists('student_detail_layout_json', 'template_marksheets')) {
            $this->db->query('ALTER TABLE `template_marksheets` ADD `student_detail_layout_json` LONGTEXT NULL');
        }
        if (!$this->db->field_exists('marksheet_footer_json', 'template_marksheets')) {
            $this->db->query('ALTER TABLE `template_marksheets` ADD `marksheet_footer_json` LONGTEXT NULL');
        }
    }

    public function get($id = null)
    {
        $this->db->select()->from('template_marksheets');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

    public function getidcardbyid($idcard)
    {
        $this->db->select('*');
        $this->db->from('  template_marksheets');
        $this->db->where('id', $idcard);
        $query = $this->db->get();
        return $query->result();
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('template_marksheets', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  marksheets id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
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
        } else {
            $this->db->insert('template_marksheets', $data);
            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On  marksheets id " . $id;
            $action    = "Insert";
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
            return $id;
        }
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('template_marksheets');
        $message   = DELETE_RECORD_CONSTANT . " On marksheets id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return true;
        }
    }

}
