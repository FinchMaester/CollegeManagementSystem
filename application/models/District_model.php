<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class District_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getAll()
    {
        $this->db->select('*');
        $this->db->from('districts');
        $this->db->order_by('name');
        return $this->db->get()->result_array();
    }


    public function getByProvinceId($province_id)
    {
        $this->db->select('*');
        $this->db->from('districts');
        $this->db->where('province_id', $province_id);
        $this->db->order_by('name');
        return $this->db->get()->result_array();
    }

  
}


