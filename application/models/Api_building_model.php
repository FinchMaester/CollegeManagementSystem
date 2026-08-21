<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** HEMIS Buildings API persistence (api_buildings). PDF V1.3 §5.1.1. */
class Api_building_model extends CI_Model
{
    protected $table = 'api_buildings';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create(array $data)
    {
        if (!$this->db->table_exists($this->table)) {
            log_message('error', 'api_buildings missing; run application/sql/hemis_infrastructure_tables.sql');
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
        return array('ownershipOfBuilding', 'hasInternetConnection');
    }

    protected function _map_to_row(array $data, $partial = false)
    {
        $map = array(
            'houseName'               => 'house_name',
            'areaCoveredByBuilding'   => 'area_covered_by_building',
            'noOfClassrooms'          => 'no_of_classrooms',
            'areaCoveredByAllRooms'   => 'area_covered_by_all_rooms',
            'ownershipOfBuilding'     => 'ownership_of_building',
            'hasInternetConnection'   => 'has_internet_connection',
            'remarks'                 => 'remarks',
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
            'id'                      => (int) $row['id'],
            'houseName'               => $row['house_name'],
            'areaCoveredByBuilding'   => (float) $row['area_covered_by_building'],
            'noOfClassrooms'          => (int) $row['no_of_classrooms'],
            'areaCoveredByAllRooms'   => (float) $row['area_covered_by_all_rooms'],
            'ownershipOfBuilding'     => (bool) $row['ownership_of_building'],
            'hasInternetConnection'   => (bool) $row['has_internet_connection'],
            'remarks'                 => $row['remarks'],
        );
    }
}
