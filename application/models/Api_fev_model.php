<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Furniture / equipment / vehicle — api_furniture_equipment_vehicle. PDF §5.1.2. */
class Api_fev_model extends CI_Model
{
    protected $table = 'api_furniture_equipment_vehicle';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create(array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            log_message('error', 'api_furniture_equipment_vehicle missing; run hemis_infrastructure_tables.sql');
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
        $map = array(
            'itemType'        => 'item_type',
            'itemName'        => 'item_name',
            'noOfQty'         => 'no_of_qty',
            'itemDescription' => 'item_description',
            'remarks'         => 'remarks',
        );
        $row = array();
        foreach ($map as $camel => $col) {
            if (array_key_exists($camel, $data)) {
                $row[$col] = $data[$camel];
            }
        }
        if (!$partial && empty($row)) {
            return array();
        }
        return $row;
    }

    protected function _map_from_row(array $row)
    {
        return array(
            'id'               => (int) $row['id'],
            'itemType'        => $row['item_type'],
            'itemName'        => $row['item_name'],
            'noOfQty'         => (int) $row['no_of_qty'],
            'itemDescription' => $row['item_description'],
            'remarks'         => $row['remarks'],
        );
    }
}
