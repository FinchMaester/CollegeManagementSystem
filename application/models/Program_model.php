<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Program_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null)
    {
        $this->db->select('programs.*')
                 ->from('programs');
       
        if ($id != null) {
            $this->db->where('programs.id', $id);
            $result = $this->db->get()->row_array();
            return $result;
        } else {
            $this->db->order_by('programs.id');
            $results = $this->db->get()->result_array();
            return $results;
        }
    }

    public function getAll()
    {
        return $this->get();
    }

    /**
     * Get all programs with their associated sections for display
     */
    public function getAllWithSections()
    {
        $this->db->select('programs.*, sections.section as class_name')
                 ->from('programs')
                 ->join('section_programs', 'section_programs.program_id = programs.id', 'left')
                 ->join('sections', 'sections.id = section_programs.section_id', 'left')
                 ->order_by('programs.id');
        
        $results = $this->db->get()->result_array();
        return $results;
    }

    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
       
        // Remove from pivot table first
        $this->db->where('program_id', $id);
        $this->db->delete('section_programs');
        
        // Then remove from programs table
        $this->db->where('id', $id);
        $this->db->delete('programs');
       
        $message = DELETE_RECORD_CONSTANT . " On programs id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
       
        $this->db->trans_complete();
       
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        return true;
    }

    public function add($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
       
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('programs', $data);
           
            $message = UPDATE_RECORD_CONSTANT . " On programs id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            
            $this->db->trans_complete();
            return ($this->db->trans_status() !== false);
        } else {
            $this->db->insert('programs', $data);
            $id = $this->db->insert_id();
           
            $message = INSERT_RECORD_CONSTANT . " On programs id " . $id;
            $action = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
            return $id;
        }
    }

    /**
     * Add program with section relationship
     */
    public function addWithSection($program_data, $section_id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        
        // Insert program
        $this->db->insert('programs', $program_data);
        $program_id = $this->db->insert_id();
        
        if ($program_id && $section_id) {
            // Insert into pivot table
            $pivot_data = array(
                'program_id' => $program_id,
                'section_id' => $section_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('section_programs', $pivot_data);
        }
        
        $message = INSERT_RECORD_CONSTANT . " On programs id " . $program_id;
        $action = "Insert";
        $record_id = $program_id;
        $this->log($message, $record_id, $action);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
        
        return $program_id;
    }

    /**
     * Update program with section relationship
     */
    public function updateWithSection($program_data, $section_id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        
        $program_id = $program_data['id'];
        
        // Update program
        $this->db->where('id', $program_id);
        $this->db->update('programs', $program_data);
        
        if ($section_id) {
            // Remove existing relationships
            $this->db->where('program_id', $program_id);
            $this->db->delete('section_programs');
            
            // Add new relationship
            $pivot_data = array(
                'program_id' => $program_id,
                'section_id' => $section_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('section_programs', $pivot_data);
        }
        
        $message = UPDATE_RECORD_CONSTANT . " On programs id " . $program_id;
        $action = "Update";
        $record_id = $program_id;
        $this->log($message, $record_id, $action);
        
        $this->db->trans_complete();
        
        return ($this->db->trans_status() !== false);
    }

    public function getProgramsBySection($section_id) {
        $this->db->select('programs.*')
            ->from('programs')
            ->join('section_programs', 'section_programs.program_id = programs.id', 'inner')
            ->where('section_programs.section_id', $section_id)
            ->order_by('programs.title');
       
        return $this->db->get()->result_array();
    }

    public function getAllActivePrograms()
    {
        $this->db->select('programs.*')
                 ->from('programs')
                 ->where('programs.is_active', 1)
                 ->order_by('programs.title');
       
        return $this->db->get()->result_array();
    }

    // Get programs by multiple sections using pivot table
    public function getProgramsBySections($section_ids)
    {
        if (!is_array($section_ids)) {
            $section_ids = [$section_ids];
        }
       
        // Filter out any empty/null values
        $section_ids = array_filter($section_ids, function($value) {
            return ($value !== null && $value !== false && $value !== '');
        });
       
        // If no valid section IDs, return empty array
        if (empty($section_ids)) {
            return [];
        }
       
        $this->db->select('programs.*')
             ->from('programs')
             ->join('section_programs', 'section_programs.program_id = programs.id', 'inner')
             ->where('programs.is_active', 1)
             ->where_in('section_programs.section_id', $section_ids)
             ->order_by('programs.title');
       
        return $this->db->get()->result_array();
    }

    public function getProgramCount() {
        $query = $this->db->get('programs');
       
        if ($query === FALSE) {
            log_message('error', 'Database error in getProgramCount: ' . $this->db->error()['message']);
            return 0;
        }
       
        return $query->num_rows();
    }

    // Add program to sections (using pivot table)
    public function addProgramToSections($program_id, $section_ids)
    {
        if (!is_array($section_ids)) {
            $section_ids = [$section_ids];
        }

        $this->db->trans_start();
       
        // First, remove existing relationships for this program
        $this->db->where('program_id', $program_id);
        $this->db->delete('section_programs');
       
        // Then add new relationships
        foreach ($section_ids as $section_id) {
            if (!empty($section_id)) {
                $data = array(
                    'program_id' => $program_id,
                    'section_id' => $section_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('section_programs', $data);
            }
        }
       
        $this->db->trans_complete();
       
        return $this->db->trans_status();
    }

    // Get sections for a program
    public function getProgramSections($program_id)
    {
        $this->db->select('sections.*')
            ->from('sections')
            ->join('section_programs', 'section_programs.section_id = sections.id', 'inner')
            ->where('section_programs.program_id', $program_id);
       
        return $this->db->get()->result_array();
    }

    public function get_program_by_id($id) {
        $this->db->select('*');
        $this->db->from('programs');
        $this->db->where('id', $id);
        $query = $this->db->get();
       
        return $query->num_rows() > 0 ? $query->row_array() : null;
    }
   
    /**
     * Get all active programs
     *
     * @return array
     */
    public function get_active_programs() {
        $this->db->select('*');
        $this->db->from('programs');
        $this->db->where('is_active', 1);
        $this->db->order_by('title', 'ASC');
        $query = $this->db->get();
       
        return $query->result_array();
    }
   
    /**
     * Get programs by section ID using pivot table
     *
     * @param int $section_id
     * @return array
     */
    public function get_programs_by_section($section_id) {
        $this->db->select('programs.*');
        $this->db->from('programs');
        $this->db->join('section_programs', 'section_programs.program_id = programs.id', 'inner');
        $this->db->where('section_programs.section_id', $section_id);
        $this->db->where('programs.is_active', 1);
        $this->db->order_by('programs.title', 'ASC');
        $query = $this->db->get();
       
        return $query->result_array();
    }

    // Backward compatibility methods
    public function getByClass($class_id)
    {
        // This method is deprecated since we removed class_id
        return $this->getAllActivePrograms();
    }

    public function getProgramsByClassAndSection($class_id, $section_id)
    {
        // Use the new section-based method
        return $this->getProgramsBySection($section_id);
    }

    public function getProgramsByClassAndSections($class_id, $section_ids)
    {
        // Use the new sections-based method
        return $this->getProgramsBySections($section_ids);
    }

    public function getProgramsByClassSection($class_id, $section_id) {
        // Use the new section-based method
        return $this->getProgramsBySection($section_id);
    }

    public function get_college_programs($campusId = null) {
        $campusSql = ($campusId !== null && $campusId !== '') ? (int) $campusId : 'NULL';
        $this->db->select('
            programs.id,
            NULL as universityId,
            ' . $campusSql . ' as campusId,
            NULL as levelId,
            NULL as levelName,
            NULL as facultyId,
            NULL as facultyName,
            CASE
                WHEN programs.session_type = \'yearly\' THEN \'annual\'
                ELSE programs.session_type
            END as programType,
            programs.id as programId,
            programs.title as programName,
            programs.code as shortName,
            programs.code as code,
            programs.duration as duration,
            NULL as alias,
            programs.description as remarks,
            programs.is_active as status
        ', false);
       
        $this->db->from('programs');
       
        if ($campusId) {
            // Add campus filtering logic if needed
        }
       
        $this->db->where('programs.is_active', 1);
       
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_programs_by_faculty($section_id) {
        return $this->get_programs_by_section($section_id);
    }
}