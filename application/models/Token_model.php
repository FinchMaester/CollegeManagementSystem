<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Token_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function create($data) {
        $this->db->insert('api_tokens', $data);
        
        // Add debug logging
        log_message('debug', 'Token create query: ' . $this->db->last_query());
        log_message('debug', 'DB Error: ' . $this->db->error()['message'] ?? 'No error');
        
        return $this->db->insert_id();
    }
    
    public function get_active_token($token) {
        $this->db->where('token', $token);
        $this->db->where('is_active', 1);
        $this->db->where('expiry_date >', date('Y-m-d H:i:s'));
        return $this->db->get('api_tokens')->row();
    }
    
    public function deactivate($token_id) {
        $this->db->where('id', $token_id);
        return $this->db->update('api_tokens', ['is_active' => 0]);
    }
}