<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** api_hostels — PDF §5.1.3. */
class Api_hostel_model extends CI_Model
{
    protected $table = 'api_hostels';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create(array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            log_message('error', 'api_hostels missing; run hemis_infrastructure_tables.sql');
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

    protected function _bool_cols()
    {
        return array('hasPlayground', 'hasInternet', 'hasDrinkingWater', 'hasToilet');
    }

    protected function _map_to_row(array $data, $partial = false)
    {
        $map = array(
            'hostelType'           => 'hostel_type',
            'noOfRoomsInHostel'    => 'no_of_rooms_in_hostel',
            'noOfSeats'            => 'no_of_seats',
            'areaCoveredByHostel'  => 'area_covered_by_hostel',
            'buildingId'           => 'building_id',
            'hasPlayground'        => 'has_playground',
            'hasInternet'          => 'has_internet',
            'hasDrinkingWater'     => 'has_drinking_water',
            'hasToilet'            => 'has_toilet',
            'remarks'              => 'remarks',
        );
        $row = array();
        foreach ($map as $camel => $col) {
            if (!array_key_exists($camel, $data)) {
                continue;
            }
            $v = $data[$camel];
            if (in_array($camel, $this->_bool_cols(), true)) {
                $row[$col] = $v ? 1 : 0;
            } else {
                $row[$col] = $v;
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
            'id'                   => (int) $row['id'],
            'hostelType'          => $row['hostel_type'],
            'noOfRoomsInHostel'   => (int) $row['no_of_rooms_in_hostel'],
            'noOfSeats'           => (int) $row['no_of_seats'],
            'areaCoveredByHostel' => (float) $row['area_covered_by_hostel'],
            'buildingId'          => (int) $row['building_id'],
            'hasPlayground'       => (bool) $row['has_playground'],
            'hasInternet'         => (bool) $row['has_internet'],
            'hasDrinkingWater'    => (bool) $row['has_drinking_water'],
            'hasToilet'           => (bool) $row['has_toilet'],
            'remarks'             => $row['remarks'],
        );
    }
}
