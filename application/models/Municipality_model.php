<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Municipality_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll()
    {
        $this->db->select('*');
        $this->db->from('municipalities');
        $this->db->order_by('name');
        return $this->db->get()->result_array();
    }

    public function getByDistrictId($district_id)
    {
        $this->db->select('*');
        $this->db->from('municipalities');
        $this->db->where('district_id', $district_id);
        $this->db->order_by('name');
        return $this->db->get()->result_array();
    }
}