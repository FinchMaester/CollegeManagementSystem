<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Campus hostel master (table `hostel`). Used by admin/student flows.
 * Not the same as Api_hostel_model (HEMIS api_hostels).
 */
class Hostel_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int|null $id
     * @return array|null|array[]
     */
    public function get($id = null)
    {
        $this->db->select()->from('hostel');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('hostel_name');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        }
        return $query->result_array();
    }

    /**
     * @return array[]
     */
    public function listhostel()
    {
        return $this->get();
    }

    /**
     * Insert or update hostel row.
     *
     * @param array $data
     * @return int|false insert id on insert
     */
    public function addhostel($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        if (isset($data['id']) && $data['id'] !== '' && $data['id'] !== null) {
            $hid = (int) $data['id'];
            $this->db->where('id', $hid);
            $this->db->update('hostel', $data);
            $message   = UPDATE_RECORD_CONSTANT . ' On hostel id ' . $hid;
            $action    = 'Update';
            $record_id = $hid;
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('hostel', $data);
            $insert_id = (int) $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . ' On hostel id ' . $insert_id;
            $action    = 'Insert';
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
            return $insert_id;
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        return true;
    }

    /**
     * @param int $id
     * @return void
     */
    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $this->db->delete('hostel');
        $message   = DELETE_RECORD_CONSTANT . ' On hostel id ' . $id;
        $action    = 'Delete';
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
    }
}
