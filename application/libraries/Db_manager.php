<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Db_manager
{
    public $connections = array();
    public $CI;
    public $db;

    public function __construct()
    {
         $this->CI = &get_instance();

        if ($this->CI->session->has_userdata('admin')) {

            $database_session = $this->CI->session->userdata('admin');
            $database_group   = $database_session['db_array']['db_group'];
            
            $this->CI->db=$this->CI->load->database($database_group, TRUE); 
        } else {
           
            $this->CI->db=$this->CI->load->database('default', TRUE); 
        }

        $this->_ensure_db_utf8mb4($this->CI->db);
             
    }

    /**
     * Ensure MySQL client charset supports Nepali/Devanagari (import + forms).
     *
     * @param object $db CI DB instance
     */
    protected function _ensure_db_utf8mb4($db)
    {
        if (!is_object($db) || !method_exists($db, 'query')) {
            return;
        }
        @$db->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
        @$db->query("SET CHARACTER SET utf8mb4");
    }

    public function get_connection($db_name)
    {

        // create connection. return it.
        $this->connections[$db_name] = $this->CI->load->database($db_name, true);
        return $this->connections[$db_name];

    }

}
