<?php




if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}




class Classsection_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }




    /**
     * This function takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($classid = null)
    {
        $this->db->select('class_sections.id,class_sections.section_id,class_sections.program_id,sections.section,programs.title as program_name,programs.code as program_code');
        $this->db->from('class_sections');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->join('programs', 'programs.id = class_sections.program_id', 'left');
        $this->db->where('class_sections.class_id', $classid);
        $this->db->order_by('class_sections.id');
        $query = $this->db->get();
       
        return $query->result_array();
    }




    public function update($data)
    {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('classes', $data);
        }
    }




    public function add($class_array, $sections, $section_programs = array())
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
       
        // Insert or update class
        if (isset($class_array['id'])) {
            $class_id = $class_array['id'];
            $this->db->where('id', $class_array['id']);
            $this->db->update('classes', $class_array);
           
            // Log the update
            $message = "Updated class with ID: " . $class_id;
            $this->log($message, $class_id, "Update");
        } else {
            $this->db->insert('classes', $class_array);
            $class_id = $this->db->insert_id();
           
            // Log the insert
            $message = "Added new class with ID: " . $class_id;
            $this->log($message, $class_id, "Insert");
        }
       
        // Validate class_id
        if (empty($class_id)) {
            log_message('error', 'Failed to get class_id in Classsection_model::add()');
            $this->db->trans_rollback();
            return false;
        }
       
        // Parse section_programs data
        $section_program_map = array();
        if (!empty($section_programs)) {
            // Handle different formats of section_programs data
            if (is_string($section_programs)) {
                // Format: "section_id:program1,program2|section_id2:program3,program4"
                $pairs = explode('|', $section_programs);
                foreach ($pairs as $pair) {
                    if (strpos($pair, ':') !== false) {
                        list($section_id, $program_ids_str) = explode(':', $pair, 2);
                        $section_id = trim($section_id);
                        if (!empty($program_ids_str)) {
                            $program_ids = array_map('trim', explode(',', $program_ids_str));
                            $program_ids = array_filter($program_ids); // Remove empty values
                            $section_program_map[$section_id] = $program_ids;
                        }
                    }
                }
            } elseif (is_array($section_programs)) {
                // Handle array format
                $section_program_map = $section_programs;
            }
        }
       
        // Prepare class_sections data - Create separate records for each program
        $sections_array = array();
        foreach ($sections as $section_id) {
            $section_id = trim($section_id);
            if (empty($section_id)) continue;
           
            $program_ids = isset($section_program_map[$section_id]) ? $section_program_map[$section_id] : array();
           
            // Ensure program_ids is an array and clean it
            if (!is_array($program_ids)) {
                $program_ids = array($program_ids);
            }
            $program_ids = array_filter(array_map('trim', $program_ids));
           
            // Create separate records for each program
            if (!empty($program_ids)) {
                foreach ($program_ids as $program_id) {
                    $section_array = array(
                        'class_id'   => $class_id,
                        'section_id' => $section_id,
                        'program_id' => intval($program_id), // Store as integer
                        'is_active'  => 'yes',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    $sections_array[] = $section_array;
                }
            } else {
                // If no programs specified, create record with null program_id
                $section_array = array(
                    'class_id'   => $class_id,
                    'section_id' => $section_id,
                    'program_id' => null,
                    'is_active'  => 'yes',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $sections_array[] = $section_array;
            }
        }
       
        // Insert class_sections
        if (!empty($sections_array)) {
            $insert_result = $this->db->insert_batch('class_sections', $sections_array);
            if (!$insert_result) {
                log_message('error', 'Failed to insert class_sections: ' . $this->db->last_query());
                $this->db->trans_rollback();
                return false;
            }
           
            // Log class_sections creation
            $message = "Added " . count($sections_array) . " class-section-program relationships for class ID: " . $class_id;
            $this->log($message, $class_id, "Insert");
        }
       
        $this->db->trans_complete();
       
        if ($this->db->trans_status() === false) {
            log_message('error', 'Transaction failed in Classsection_model::add()');
            $this->db->trans_rollback();
            return false;
        } else {
            return $class_id;
        }
    }




    public function getDetailbyClassSection($class_id, $section_id)
    {
        $this->db->select('class_sections.*,classes.class,sections.section,programs.title as program_name,programs.code as program_code')->from('class_sections');
        $this->db->where('class_id', $class_id);
        $this->db->where('section_id', $section_id);
        $this->db->join('classes', 'classes.id = class_sections.class_id');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->join('programs', 'programs.id = class_sections.program_id', 'left');
       
        $query = $this->db->get();
        return $query->row_array();
    }




    public function check_data_exists($data)
    {
        $this->db->where('class_id', $data['class_id']);
        $this->db->where('section_id', $data['section_id']);
       
        // Check for program_id if provided
        if (isset($data['program_id'])) {
            $this->db->where('program_id', $data['program_id']);
        }
       
        $query = $this->db->get('class_sections');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }




    public function getByID($id = null)
    {
        $this->db->select('c.*, cs.section_id, cs.program_id, s.section, p.title AS program_name, p.code AS program_code');
        $this->db->from('classes c');
        $this->db->join('class_sections cs', 'cs.class_id = c.id');
        $this->db->join('sections s', 's.id = cs.section_id');
        $this->db->join('programs p', 'p.id = cs.program_id', 'left'); // LEFT JOIN to allow null programs




        if ($id !== null) {
            $this->db->where('c.id', $id);
        }




        $this->db->order_by('c.id', 'asc');




        $query = $this->db->get();
        $results = $query->result();




        $classData = [];




        foreach ($results as $row) {
            $class_id = $row->id;




            if (!isset($classData[$class_id])) {
                $classData[$class_id] = $row;
                $classData[$class_id]->vehicles = [];
            }




            $classData[$class_id]->vehicles[] = (object)[
                'section_id' => $row->section_id,
                'section' => $row->section,
                'program_id' => $row->program_id,
                'program_name' => $row->program_name,
                'program_code' => $row->program_code,
            ];
        }




        return array_values($classData);
    }




    public function getVechileByRoute($route_id)
    {
        $this->db->select('class_sections.id as class_section_id,class_sections.class_id,class_sections.section_id,class_sections.program_id,sections.*,programs.title as program_name,programs.code as program_code')->from('class_sections');
        $this->db->join('sections', 'sections.id = class_sections.section_id');
        $this->db->join('programs', 'programs.id = class_sections.program_id', 'left');
        $this->db->where('class_sections.class_id', $route_id);
        $this->db->where('class_sections.is_active', 'yes');
        $this->db->order_by('sections.section', 'ASC');
        $query = $this->db->get();
       
        return $query->result();
    }




    public function remove($class_id, $array)
    {
        $this->db->trans_start();
       
        $this->db->where('class_id', $class_id);
        if (!empty($array)) {
            $this->db->where_in('section_id', $array);
        }
        $delete_result = $this->db->delete('class_sections');
       
        if ($delete_result) {
            $message = "Removed class-section relationships for class ID: " . $class_id;
            $this->log($message, $class_id, "Delete");
        }
       
        $this->db->trans_complete();
       
        return $this->db->trans_status();
    }




    public function allClassSections()
    {
        $classes = $this->class_model->get();
        if (!empty($classes)) {
            foreach ($classes as $class_key => $class_value) {
                $classes[$class_key]['sections'] = $this->get($class_value['id']);
            }
        }
        return $classes;
    }




    public function getClassSectionStudentCount()
    {
        $class_section_array = $this->customlib->get_myClassSectionQuerystring('class_sections');
        $userdata = $this->customlib->getUserData();
       
        // Get the basic class section data with total student counts
        $base_query = "SELECT
                    cs.id,
                    c.id as class_id,
                    s.id as section_id,
                    cs.program_id,
                    c.class,
                    s.section,
                    p.title as program_name,
                    p.code as program_code,
                    (SELECT COUNT(*) FROM student_session ss
                     INNER JOIN students st ON st.id = ss.student_id
                     WHERE ss.class_id = c.id
                     AND ss.section_id = s.id
                     AND (cs.program_id IS NULL OR ss.program_id = cs.program_id)
                     AND ss.is_active = 'yes'
                     AND ss.session_id = " . $this->current_session . ") as student_count
                FROM
                    class_sections cs
                INNER JOIN
                    classes c ON c.id = cs.class_id
                INNER JOIN
                    sections s ON s.id = cs.section_id
                LEFT JOIN
                    programs p ON p.id = cs.program_id
                WHERE
                    cs.is_active = 'yes'
                    AND c.is_active = 'yes' " . $class_section_array . "
                ORDER BY
                    c.class ASC, s.section ASC, p.title ASC";
       
        $result = $this->db->query($base_query)->result();
       
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
       
        return $result;
    }
   
    /**
     * Get programs for specific class-section combinations
     */
    public function getClassSectionPrograms($class_id, $section_id = null)
    {
        $this->db->select('cs.program_id, p.id, p.title, p.code');
        $this->db->from('class_sections cs');
        $this->db->join('programs p', 'p.id = cs.program_id', 'left');
        $this->db->where('cs.class_id', $class_id);
        $this->db->where('cs.is_active', 'yes');
        $this->db->where('p.is_active', 'yes');
       
        if ($section_id !== null) {
            $this->db->where('cs.section_id', $section_id);
        }
       
        $query = $this->db->get();
        return $query->result_array();
    }




    public function getExistingSectionPrograms($class_id)
    {
        if (empty($class_id)) {
            return array();
        }
       
        // Query to get existing section-program relationships
        $this->db->select('cs.section_id, cs.program_id');
        $this->db->from('class_sections cs');
        $this->db->where('cs.class_id', $class_id);
        $this->db->where('cs.program_id IS NOT NULL'); // Only get records with program_id
        $this->db->order_by('cs.section_id ASC');
       
        $query = $this->db->get();
        $results = $query->result();
       
        // Log for debugging
        log_message('debug', 'getExistingSectionPrograms for class_id ' . $class_id . ': ' . print_r($results, true));
       
        return $results;
    }
}





