<?php




if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}




class Class_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }




    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function getAll($id = null)
    {
        $this->db->select()->from('classes');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            $classlist = $query->row_array();
        } else {
            $classlist = $query->result_array();
        }




        return $classlist;
    }




    public function get($id = null, $classteacher = null)
    {
        $userdata = $this->customlib->getUserData();
        $role_id  = $userdata["role_id"];
        $carray   = array();
        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if ($userdata["class_teacher"] == 'yes') {
                $classlist = $this->teacher_model->get_teacherrestricted_mode($userdata["id"]);
            }
        } else {
            $this->db->select()->from('classes');
            if ($id != null) {
                $this->db->where('id', $id);
            } else {
                $this->db->order_by('id');
            }
            $query = $this->db->get();
            if ($id != null) {
                $classlist = $query->row_array();
            } else {
                $classlist = $query->result_array();
            }
        }




        return $classlist;
    }




    public function getProgramsBySections($section_ids)
    {
        if (empty($section_ids)) {
            return array();
        }
       
        // Make sure section_ids is an array
        if (!is_array($section_ids)) {
            $section_ids = array($section_ids);
        }
       
        // Log the input for debugging
        log_message('debug', 'getProgramsBySections called with: ' . print_r($section_ids, true));
       
        $this->db->select('p.id, p.title, p.code, sp.section_id');
        $this->db->from('section_programs sp');
        $this->db->join('programs p', 'p.id = sp.program_id');
        $this->db->where_in('sp.section_id', $section_ids);
        $this->db->where('p.is_active', 'yes');
        $this->db->order_by('sp.section_id ASC, p.title ASC');
       
        $query = $this->db->get();
        $result = $query->result_array();
       
        // Ensure section_id is properly set for each program
        foreach ($result as &$program) {
            if (!isset($program['section_id']) || empty($program['section_id'])) {
                // If section_id is not set, we need to get it from the section_programs table
                $this->db->select('section_id');
                $this->db->from('section_programs');
                $this->db->where('program_id', $program['id']);
                $this->db->where_in('section_id', $section_ids);
                $section_query = $this->db->get();
                if ($section_query->num_rows() > 0) {
                    $program['section_id'] = $section_query->row()->section_id;
                }
            }
        }
       
        // Log the result for debugging
        log_message('debug', 'getProgramsBySections result: ' . print_r($result, true));
       
        return $result;
    }
   
    // Alternative method if the above doesn't work
    public function getProgramsBySectionsAlternative($section_ids)
    {
        if (empty($section_ids)) {
            return array();
        }
       
        // Make sure section_ids is an array
        if (!is_array($section_ids)) {
            $section_ids = array($section_ids);
        }
       
        $results = array();
       
        foreach ($section_ids as $section_id) {
            $this->db->select('p.id, p.title, p.code, sp.section_id');
            $this->db->from('section_programs sp');
            $this->db->join('programs p', 'p.id = sp.program_id');
            $this->db->where('sp.section_id', $section_id);
            $this->db->where('p.is_active', 'yes');
            $this->db->order_by('p.title ASC');
           
            $query = $this->db->get();
            $section_programs = $query->result_array();
           
            // Add section_id to each program record if not present
            foreach ($section_programs as &$program) {
                $program['section_id'] = $section_id;
            }
           
            $results = array_merge($results, $section_programs);
        }
       
        return $results;
    }




    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('classes'); //class record delete.
        $this->db->where('class_id', $id);
        $this->db->delete('class_sections'); //class_sections record delete.
        $message   = DELETE_RECORD_CONSTANT . " On classes id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }




    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */
    public function add($data)
    {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('classes', $data);
        } else {
            $this->db->insert('classes', $data);
        }
    }




    public function check_data_exists($class) {
        $this->db->where('class', $class);
        $query = $this->db->get('classes');
       
        if ($query->num_rows() > 0) {
            return $query->row();
        }
       
        return false;
    }




    public function class_exists($str) {
        $id = $this->input->post('id');
       
        if ($id) {
            $this->db->where('class', $str);
            $this->db->where('id !=', $id);
            $query = $this->db->get('classes');
           
            if ($query->num_rows() > 0) {
                $this->form_validation->set_message('class_exists', 'This class name already exists');
                return false;
            }
        } else {
            $this->db->where('class', $str);
            $query = $this->db->get('classes');
           
            if ($query->num_rows() > 0) {
                $this->form_validation->set_message('class_exists', 'This class name already exists');
                return false;
            }
        }
       
        return true;
    }




    public function check_classteacher_exists($class, $section, $teacher)
    {
        $this->db->where(array('class_id' => $class, 'section_id' => $section, 'session_id' => $this->current_session));




        $query = $this->db->get('class_teacher');
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }




    public function class_teacher_exists($str)
    {
        $class    = $this->input->post('class');
        $section  = $this->input->post('section');
        $teachers = $this->input->post('teachers');
        $res = $this->check_classteacher_exists($class, $section, $teachers);




        if ($res) {
            $prev_class_id   = $this->input->post('prev_class_id');
            $prev_section_id = $this->input->post('prev_section_id');
            if (isset($prev_class_id) && isset($prev_section_id)) {
                if ($prev_class_id == $class && $prev_section_id == $section) {
                    return true;
                }
            }
            $this->form_validation->set_message('class_exists', 'Record already exists');
            return false;
        } else {
            return true;
        }
    }




    public function getClassTeacher()
    {
        $this->db->select('class_teacher.class_id, class_teacher.section_id, class_teacher.program_id, class_teacher.session_id, classes.class, classes.degree, sections.section, programs.title as program, GROUP_CONCAT(CONCAT(staff.name, " ", staff.surname, " (", staff.employee_id, ")") SEPARATOR ", ") as teacher_names');
        $this->db->from('class_teacher');
        $this->db->join('classes', 'classes.id=class_teacher.class_id');
        $this->db->join('sections', 'sections.id=class_teacher.section_id');
        $this->db->join('programs', 'programs.id=class_teacher.program_id', 'left');
        $this->db->join('staff', 'staff.id=class_teacher.staff_id');
        $this->db->where('class_teacher.session_id', $this->current_session);
        $this->db->group_by([
            'class_teacher.class_id',
            'class_teacher.section_id',
            'class_teacher.program_id',
            'class_teacher.session_id',
            'classes.class',
            'sections.section',
            'programs.title'
        ]);
        $this->db->order_by('length(classes.class), classes.class');
   
        $query = $this->db->get();
   
        if (!$query) {
            echo "<pre>";
            print_r($this->db->error());
            exit;
        }
   
        return $query->result_array();
    }
   










    public function get_section($id)
    {
        return $this->db->select('sections.id,sections.section')->from('class_sections')->join('sections', 'class_sections.section_id=sections.id')->where('class_id', $id)->get()->result_array();
    }
   
    /**
     * Whether a class row is a final / graduation-eligible academic level.
     * Keep in sync with stdtransfer.js checkIfFinalClassLogic (degree + class name).
     *
     * @param array $classRow classes row (needs class, degree)
     * @return bool
     */
    public function isGraduationEligibleClassRow($classRow)
    {
        if (!is_array($classRow)) {
            return false;
        }

        $degree = strtolower(trim((string) (isset($classRow['degree']) ? $classRow['degree'] : '')));
        $className = strtolower(trim((string) (isset($classRow['class']) ? $classRow['class'] : '')));
        if ($className === '') {
            return false;
        }

        if (strpos($className, 'final year') !== false) {
            return true;
        }

        $hasAny = function (array $needles) use ($className) {
            foreach ($needles as $n) {
                if ($n !== '' && strpos($className, $n) !== false) {
                    return true;
                }
            }
            return false;
        };

        if ($degree === 'bachelors' || $degree === 'bachelor') {
            return $hasAny(array(
                '4th year', 'fourth year', '4 year',
                '8th sem', 'eighth sem', '8th semester', 'eighth semester',
                '8 sem', 'sem 8', 'semester 8',
            ));
        }

        if ($degree === 'masters' || $degree === 'master') {
            return $hasAny(array(
                '2nd year', 'second year',
                '4th sem', 'fourth sem', '4th semester', 'fourth semester',
                '4 sem', 'sem 4', 'semester 4',
            ));
        }

        // Degree unset: still treat clear final markers as graduation-eligible.
        return $hasAny(array(
            '4th year', 'fourth year', 'final year',
            '8th semester', 'eighth semester',
        ));
    }

    /**
     * @param int $classId
     * @return bool
     */
    public function isGraduationEligibleClassId($classId)
    {
        $classId = (int) $classId;
        if ($classId < 1) {
            return false;
        }
        $row = $this->getAll($classId);
        return $this->isGraduationEligibleClassRow(is_array($row) ? $row : array());
    }

    /**
     * First graduation-eligible active class (not "highest id").
     *
     * @return array|null
     */
    public function getFinalYearClass()
    {
        $list = $this->getGraduationEligibleClasses();
        if (!empty($list[0]) && is_array($list[0])) {
            return $list[0];
        }
        return null;
    }

    /**
     * All active classes that match graduation final-level naming (same rules as UI).
     *
     * @return array
     */
    public function getGraduationEligibleClasses()
    {
        $this->db->select('*');
        $this->db->from('classes');
        $this->db->where('is_active', 'yes');
        $this->db->order_by('id', 'asc');
        $query = $this->db->get();

        if ($query === false) {
            log_message('error', 'Classes query failed: ' . (isset($this->db->error()['message']) ? $this->db->error()['message'] : 'unknown'));
            return array();
        }

        $out = array();
        foreach ($query->result_array() as $row) {
            if ($this->isGraduationEligibleClassRow($row)) {
                $out[] = $row;
            }
        }
        return $out;
    }
   
    public function getGraduatedStudents($class_id = null, $section_id = null, $program_id = null, $session_id = null) {
        $this->db->select('students.*, classes.class, sections.section, programs.title as program_name,
                          student_session.session_id, student_session.id as student_session_id,
                          student_session.updated_at, student_session.class_id, student_session.section_id,
                          student_session.program_id')
                 ->from('students')
                 ->join('student_session', 'students.id = student_session.student_id')
                 ->join('classes', 'student_session.class_id = classes.id', 'left')
                 ->join('sections', 'student_session.section_id = sections.id', 'left')
                 ->join('programs', 'student_session.program_id = programs.id', 'left')
                 ->where('student_session.is_graduated', 1);




        // Apply filters only if they're provided
        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }




        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }




        if (!empty($program_id)) {
            $this->db->where('student_session.program_id', $program_id);
        }




        if (!empty($session_id)) {
            $this->db->where('student_session.session_id', $session_id);
        }




        // For debugging
        $sql = $this->db->get_compiled_select();
        error_log("Graduated students SQL: " . $sql);




        $query = $this->db->get();
        return $query->result_array();
    }




    public function getBySectionAndProgram($section_id, $program_id) {
        $this->db->select('classes.id, classes.class, classes.degree');
        $this->db->from('classes');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id');
        $this->db->where('class_sections.section_id', $section_id);
        $this->db->where('class_sections.program_id', $program_id);
        $this->db->order_by('classes.class', 'asc');
        $query = $this->db->get();
        return $query->result_array();
    }




    public function getClassesByProgram($program_id)
    {
        $this->db->select('classes.*');
        $this->db->from('classes');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id');
        $this->db->where('class_sections.program_id', $program_id);
        $this->db->where('classes.is_active', 'yes');
        $this->db->group_by('classes.id');
        $this->db->order_by('classes.class', 'ASC');
       
        $query = $this->db->get();
        return $query->result_array();
    }
}





