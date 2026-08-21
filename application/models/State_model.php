<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class State_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getAll()
    {
        $this->db->select('*');
        $this->db->from('states');
        $this->db->order_by('name');
        return $this->db->get()->result_array();
    }
}

