<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Teaching departments — api_management_departments. PDF §5.6. */
class Management_department_model extends CI_Model
{
    protected $table = 'api_management_departments';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create(array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }
        $row = $this->_map_to_row($data);
        $row['created_at'] = date('Y-m-d H:i:s');
        $row['updated_at'] = $row['created_at'];
        if ($this->db->insert($this->table, $row)) {
            return (int) $this->db->insert_id();
        }
        return false;
    }

    public function get_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return array();
        }
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get($this->table)->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[] = $this->_map_from_row($r);
        }
        return $out;
    }

    public function get($id)
    {
        if (!$this->db->table_exists($this->table)) {
            return null;
        }
        $q = $this->db->get_where($this->table, array('id' => (int) $id));
        $row = $q->row_array();
        return $row ? $this->_map_from_row($row) : null;
    }

    public function update($id, array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }
        $row = $this->_map_to_row($data, true);
        if (empty($row)) {
            return false;
        }
        $row['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $id);
        return $this->db->update($this->table, $row) && $this->db->affected_rows() >= 0;
    }

    protected function _map_to_row(array $data, $partial = false)
    {
        $row = array();
        if (array_key_exists('departmentName', $data)) {
            $row['department_name'] = $data['departmentName'];
        }
        if (array_key_exists('status', $data)) {
            $row['status'] = $data['status'] ? 1 : 0;
        }
        if (array_key_exists('remarks', $data)) {
            $row['remarks'] = $data['remarks'];
        }
        if (!$partial && empty($row)) {
            return array();
        }
        return $row;
    }

    protected function _map_from_row(array $row)
    {
        return array(
            'id'             => (int) $row['id'],
            'departmentName' => $row['department_name'],
            'status'         => (bool) $row['status'],
            'remarks'        => $row['remarks'],
        );
    }
}
