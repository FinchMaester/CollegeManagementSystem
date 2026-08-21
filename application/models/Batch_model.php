<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Batch_model
 * 
 * This model handles operations related to student batches
 */
class Batch_model extends CI_Model {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Get batch by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function get_batch_by_id($id) {
        $this->db->select('*');
        $this->db->from('batches');
        $this->db->where('id', $id);
        $query = $this->db->get();
        
        return $query->num_rows() > 0 ? $query->row_array() : null;
    }
    
    /**
     * Get all active batches
     * 
     * @return array
     */
    public function get_active_batches() {
        $this->db->select('*');
        $this->db->from('batches');
        $this->db->where('is_active', 'yes');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        
        return $query->result_array();
    }
    
    /**
     * Get batches by program ID
     * 
     * @param int $program_id
     * @return array
     */
    public function get_batches_by_program($program_id) {
        $this->db->select('batches.*');
        $this->db->from('batches');
        $this->db->join('program_batches', 'program_batches.batch_id = batches.id', 'left');
        $this->db->where('program_batches.program_id', $program_id);
        $this->db->where('batches.is_active', 'yes');
        $this->db->order_by('batches.id', 'DESC');
        $query = $this->db->get();
        
        return $query->result_array();
    }
    
    /**
     * Add a new batch
     * 
     * @param array $data
     * @return int The inserted ID
     */
    public function add_batch($data) {
        $this->db->insert('batches', $data);
        return $this->db->insert_id();
    }
}