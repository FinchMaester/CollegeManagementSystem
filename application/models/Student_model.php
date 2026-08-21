<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Student_model extends MY_Model
{
    protected $table = 'students';
    protected $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
        $this->load->database();
    }

    public function getBirthDayStudents($date, $email = false, $contact_no = false)
    {
        $userdata            = $this->customlib->getUserData();
        $class_section_array = $this->customlib->get_myClassSection();
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob , students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`,students.app_key,students.parent_app_key')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');
        $this->db->where('users.role', 'student');
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->db->or_where($query_string);
                }
            }
            $this->db->group_end();
        }
        if ($email) {
            $this->db->where('students.email !=', "");
        }
        if ($contact_no) {
            $this->db->where('students.mobileno !=', "");
        }

        $this->db->where("DATE_FORMAT(students.dob,'%m-%d') = DATE_FORMAT('" . $date . "','%m-%d')");
        $this->db->order_by('students.id');

        $query  = $this->db->get();
        $result = $query->result_array();
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
        return $result;
    }

    public function getStudents()
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,   students.dob_ad ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_email,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('users.role', 'student');
        $this->db->order_by('students.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getAppStudents()
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,  students.middlename,students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.app_key ,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');
        $this->db->where('students.app_key !=', "");
        $this->db->where('users.role', 'student');

        $this->db->order_by('students.id');

        $query = $this->db->get();
        return $query->result();
    }

    public function getRecentRecord($id = null)
    {
        $this->db->select('classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename, students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,students.category_id,    students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.guardian_is')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_session.session_id', $this->current_session);
        if ($id != null) {
            $this->db->where('students.id', $id);
        } else {

        }
        $this->db->order_by('students.id', 'desc');
        $this->db->limit(5);
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getParentChilds($parent_id)
    {
        $sql   = "SELECT students.*,student_session.id as `student_session_id`,student_session.session_id,student_session.student_id,student_session.class_id,student_session.default_login,student_session.section_id,classes.class,sections.section From students inner JOIN student_session on student_session.student_id=students.id inner join classes on student_session.class_id=classes.id INNER JOIN sections on sections.id=student_session.section_id WHERE students.parent_id=" . $this->db->escape($parent_id) . " and student_session.session_id=" . $this->current_session . " and students.is_active = 'yes' order by student_session.default_login desc,student_session.class_id asc";
        $query = $this->db->query($sql);
        return $query->result();
    }

    /**
     * College CMS: `class_id` / `section_id` on `student_session` are academic level and semester cohort
     * (tables `classes` / `sections`), not primary-school “grades”. Program is via `programs` / `class_sections`.
     */
    public function getStudentByClassSectionID($class_id = null, $section_id = null, $id = null, $session_id = null)
    {
        if ($session_id != "") {
            $session_id = $session_id;
        } else {
            $session_id = $this->current_session;
        }
    
        $this->db->select('students.program_id, programs.title as program_title,pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename, students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`,students.app_key,students.parent_app_key')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('sessions', 'sessions.id = student_session.session_id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'categories.id = students.category_id', 'left'); // Added missing join
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id AND users.role = "student"', 'left'); // Moved role condition to JOIN
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
        $this->db->join('transport_route', 'route_pickup_point.transport_route_id = transport_route.id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('programs', 'programs.id = students.program_id', 'left');
        
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where('student_session.session_id', $session_id);
    
        if ($id != null) {
            $this->db->where('students.id', $id);
        } else {
            $this->db->where('student_session.is_active', 'yes');
            $this->db->order_by('students.id', 'desc');
        }
        
        $query = $this->db->get();
        
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getProgramStudentCount()
    {
        if (!$this->db->table_exists('programs')) {
            return [];
        }
    
        // Fix the query - particularly the ORDER BY clause
        $query = "SELECT 
                    programs.id, 
                    programs.title as program_title, 
                    COUNT(DISTINCT students.id) as student_count 
                  FROM 
                    programs 
                  LEFT JOIN 
                    students ON students.program_id = programs.id 
                  LEFT JOIN 
                    student_session ON student_session.student_id = students.id 
                  WHERE 
                    student_session.session_id = " . $this->db->escape($this->current_session) . " 
                    AND students.is_active = 'yes' 
                  GROUP BY 
                    programs.id 
                  ORDER BY 
                    programs.title ASC";
        
        $query = $this->db->query($query);
        
        // Check if query executed successfully
        if ($query !== FALSE) {
            return $query->result();
        } else {
            // Return empty array if query failed
            return [];
        }
    }

    public function getStudentByProgramID($program_id = null, $session_id = null)
{
    if ($session_id != "") {
        $session_id = $session_id;
    } else {
        $session_id = $this->current_session;
    }

    $this->db->select('students.program_id, programs.title as program_title, student_session.id as `student_session_id`, 
                      classes.id AS `class_id`, classes.class, sections.id AS `section_id`, sections.section, 
                      students.id, students.admission_no, students.roll_no, students.admission_date,
                      students.firstname, students.middlename, students.lastname, students.image, 
                      students.mobileno, students.email, students.gender, students.dob, 
                      students.father_name, students.category_id, categories.category')
        ->from('students')
        ->join('student_session', 'student_session.student_id = students.id')
        ->join('classes', 'student_session.class_id = classes.id')
        ->join('sections', 'sections.id = student_session.section_id')
        ->join('programs', 'programs.id = students.program_id', 'left')
        ->join('categories', 'categories.id = students.category_id', 'left')
        ->where('student_session.session_id', $session_id)
        ->where('student_session.is_active', 'yes');
        
    if ($program_id != null) {
        $this->db->where('students.program_id', $program_id);
    }
    
    $this->db->order_by('students.id', 'desc');
    $query = $this->db->get();
    
    return $query->result_array();
}

    public function getByStudentSession($student_session_id)
    {
        $this->db->select('pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.transport_fees,students.app_key,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,class_sections.id as `class_section_id`,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode , students.note, students.religion, students.cast, school_houses.house_name,   students.dob ,students.current_address, students.previous_school,students.guardian_is,students.parent_id,          students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.father_pic ,students.height ,students.weight,students.measurement_date, students.mother_pic , students.guardian_pic , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.guardian_is,students.rte,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note,students.app_key,students.parent_app_key,programs.title as program_title,programs.code as program_code')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id and class_sections.section_id = sections.id');
        $this->db->join('programs', 'programs.id = students.program_id', 'left');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
        $this->db->join('transport_route', 'route_pickup_point.transport_route_id = transport_route.id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('users.role', 'student');
        $this->db->where('student_session.id', $student_session_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function getByStudentSessionAnySession($id)
    {
        // Same shape as getByStudentSession(), but without restricting to current session.
        $this->db->select('pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.transport_fees,students.app_key,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,class_sections.id as `class_section_id`,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode , students.note, students.religion, students.cast, school_houses.house_name,   students.dob ,students.current_address, students.previous_school,students.guardian_is,students.parent_id,          students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.father_pic ,students.height ,students.weight,students.measurement_date, students.mother_pic , students.guardian_pic , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.guardian_is,students.rte,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note,students.app_key,students.parent_app_key,programs.title as program_title,programs.code as program_code')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id and class_sections.section_id = sections.id');
        $this->db->join('programs', 'programs.id = students.program_id', 'left');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
        $this->db->join('transport_route', 'route_pickup_point.transport_route_id = transport_route.id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id AND users.role = "student"', 'left');

        // Prefer interpreting $id as student_session.id (most callers pass session id).
        $this->db->where('student_session.id', (int) $id);
        $this->db->limit(1);
        $query = $this->db->get();
        $row   = $query->row_array();
        if (!empty($row) && is_array($row)) {
            return $row;
        }

        // Fallback: interpret $id as students.id and pick latest session for that student.
        $this->db->select('pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.transport_fees,students.app_key,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,class_sections.id as `class_section_id`,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode , students.note, students.religion, students.cast, school_houses.house_name,   students.dob ,students.current_address, students.previous_school,students.guardian_is,students.parent_id,          students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.father_pic ,students.height ,students.weight,students.measurement_date, students.mother_pic , students.guardian_pic , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.guardian_is,students.rte,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note,students.app_key,students.parent_app_key,programs.title as program_title,programs.code as program_code')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id and class_sections.section_id = sections.id');
        $this->db->join('programs', 'programs.id = students.program_id', 'left');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
        $this->db->join('transport_route', 'route_pickup_point.transport_route_id = transport_route.id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id AND users.role = "student"', 'left');
        $this->db->where('students.id', (int) $id);
        $this->db->order_by('student_session.session_id', 'desc');
        $this->db->order_by('student_session.id', 'desc');
        $this->db->limit(1);
        $query2 = $this->db->get();
        return $query2->row_array();
    }

    public function getStudentBy_class_section_id($cls_section_id)
    {
        $i = 1;

        $custom_fields   = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);

        $this->db->select('student_session.transport_fees,students.app_key,student_session.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,class_sections.id as `class_section_id`,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno, students.email ,students.state,students.city,students.pincode,students.note,students.religion,students.cast, school_houses.house_name,students.dob ,students.current_address, students.previous_school,students.guardian_is,students.parent_id,        students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.father_pic ,students.height ,students.weight,students.measurement_date, students.mother_pic , students.guardian_pic , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.guardian_is,students.rte,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note,students.app_key,students.parent_app_key,programs.title as program_title,programs.code as program_code,IFNULL(categories.category, "") as `category`,' . $field_variable)->from('students');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id and class_sections.section_id = sections.id');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('class_sections.id', $cls_section_id);
        $this->db->where('users.role', 'student');
        $this->db->where('student_session.is_active', 'yes');
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->db->or_where($query_string);
                }
            }
            $this->db->group_end();
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get($id = null)
    {
        $this->db->select('pickup_point.name as pickup_point_name,student_session.route_pickup_point_id,student_session.transport_fees,students.app_key,students.parent_app_key,student_session.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,vehicles.vehicle_model,vehicles.manufacture_year,vehicles.driver_licence,vehicles.vehicle_photo,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,student_session.program_id AS program_id,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno,students.email,students.state,students.city,students.pincode,students.note,students.religion,students.cast,school_houses.house_name,students.dob,students.dob_ad,students.current_address,students.previous_school,students.guardian_is,students.parent_id,students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code,students.guardian_name,students.father_pic,students.height,students.weight,students.measurement_date,students.mother_pic,students.guardian_pic,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,students.created_at,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.rte,students.guardian_email,users.username,users.password,users.id as user_id,students.dis_reason,students.dis_note,students.disable_at');
        $this->db->select('students.first_name_np, students.middle_name_np, students.last_name_np');
        $this->db->select('students.permanent_district, students.permanent_province, students.permanent_local_level, students.permanent_ward_no, students.permanent_house_no');
        $this->db->select('students.temporary_district, students.temporary_province, students.temporary_local_level, students.temporary_ward_no, students.temporary_house_no');
        $this->db->select('students.ethnicity, students.edj, students.national_id, students.citizenship, students.citizenship_issue_dist, students.complition_year, students.admissionYearId');
        $this->db->select('students.admission_year, students.academic_program_duration');
    
    
        $this->db->select('students.previous_level_of_study, students.previous_board_university_college, students.previous_registration_no, students.previous_institution_name, students.previous_study_records_attachment');
       
        /* Do not select students.class_id / section_id / program_id here — they overwrite session joins above. */
        $this->db->select('students.school_id');
        // HEMIS / UGC (stored on students row; must be present for edit form preselect + sync).
        $this->db->select('students.ugc_program_id, students.ugc_batch_id, students.ugc_fiscal_year_id, students.ugc_t_province, students.ugc_t_district, students.ugc_t_local_level, students.ugc_p_province, students.ugc_p_district, students.ugc_p_local_level, students.ugc_p_combined_code, students.ugc_t_combined_code');
       
        $this->db->select('students.disability_status, students.disability_type, students.program_fee, students.scholarship_amt, students.paid_amount, students.batch_name, students.batch_name_nepali, students.entrance_score, students.entrance_symbol, students.medium, students.shift, students.type, students.nationality, students.semister_or_year_no, students.father_email, students.mother_email, students.is_verified, students.has_scolorship, students.is_mukta_kamaiya, students.is_from_martyr_family');
        // HEMIS / registration document scans (needed so the edit form can preview existing uploads).
        $this->db->select('students.pp_size_photo, students.citizenship_front, students.citizenship_back, students.nid_pic, students.digital_signature, students.receipt_image');
       
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id AND users.role = "student"', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
       
        if ($id != null) {
            $this->db->where('students.id', $id);
        } else {
            // Show all students in the current session, regardless of active status
            // This allows viewing students who were upgraded to other sessions
            $this->db->order_by('students.id', 'desc');
        }
    
    
        $query = $this->db->get();
       
        if ($query === FALSE) {
            echo "Database Error: " . $this->db->error()['message'];
            echo "<br>Last Query: " . $this->db->last_query();
            return false;
        }
       
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * Load one row from students by id (no joins). Used by API, HEMIS sync, tests.
     *
     * @param int $id
     * @return array|null
     */
    public function get_student_by_id($id)
    {
        $this->db->where('id', (int) $id);
        $q = $this->db->get('students');
        if ($q === false || $q->num_rows() === 0) {
            return null;
        }
        return $q->row_array();
    }

    /**
     * Update only HEMIS integration columns (never pass arbitrary keys from API payloads).
     *
     * @param int   $id
     * @param array $fields hemis_student_id, hemis_sync_at, hemis_sync_error (subset)
     * @return bool
     */
    public function update_hemis_fields($id, array $fields)
    {
        $id = (int) $id;
        if ($id < 1) {
            return false;
        }
        $allowed = array('hemis_student_id', 'hemis_sync_at', 'hemis_sync_error', 'hemis_sync_error_at');
        $row = array();
        foreach ($allowed as $col) {
            if (!array_key_exists($col, $fields)) {
                continue;
            }
            if (!$this->db->field_exists($col, 'students')) {
                continue;
            }
            $row[$col] = $fields[$col];
        }
        if (empty($row)) {
            return false;
        }
        $this->db->where('id', $id);
        return (bool) $this->db->update('students', $row);
    }

    /**
     * REST api/Student create: insert a row built from multipart fields (columns must exist on students).
     *
     * @param array $data
     * @return int|false new students.id
     */
    public function create_student(array $data)
    {
        $fields = $this->db->list_fields('students');
        $allowed = array_flip($fields);
        $row = array();
        foreach ($data as $k => $v) {
            if (isset($allowed[$k])) {
                $row[$k] = $v;
            }
        }
        if (empty($row['admission_no'])) {
            do {
                $suffix = function_exists('random_bytes')
                    ? strtoupper(bin2hex(random_bytes(4)))
                    : strtoupper(substr(sha1(uniqid((string) mt_rand(), true)), 0, 8));
                $row['admission_no'] = 'HEMIS-API-' . date('Ymd') . '-' . $suffix;
            } while ($this->db->where('admission_no', $row['admission_no'])->count_all_results('students') > 0);
        } else {
            $this->db->where('admission_no', $row['admission_no']);
            if ($this->db->count_all_results('students') > 0) {
                return false;
            }
        }
        return $this->add($row, array('adm_auto_insert' => 0));
    }

    /**
     * REST api/Student update: partial update of students row (HEMIS columns are managed by Hemis_client only).
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update_student($id, array $data)
    {
        $id = (int) $id;
        if ($id < 1) {
            return false;
        }
        $fields = $this->db->list_fields('students');
        $allowed = array_flip($fields);
        $row = array();
        foreach ($data as $k => $v) {
            if ($k === 'id') {
                continue;
            }
            if (strpos((string) $k, 'hemis_') === 0) {
                continue;
            }
            if (isset($allowed[$k])) {
                $row[$k] = $v;
            }
        }
        if (empty($row)) {
            return false;
        }
        $this->db->where('id', $id);
        return (bool) $this->db->update('students', $row);
    }

    /**
     * api/Student GetAllStudent — optional filters on students table columns.
     *
     * @param array $filters column => value
     * @return array
     */
    public function get_all_students(array $filters = array())
    {
        $this->db->from('students');
        foreach ($filters as $col => $val) {
            if (!$this->db->field_exists($col, 'students')) {
                continue;
            }
            $this->db->where($col, $val);
        }
        $this->db->order_by('students.id', 'asc');
        $q = $this->db->get();
        if (!$q) {
            return array();
        }
        return $q->result_array();
    }

    /**
     * Bulk HEMIS retry: current session, active students needing sync or with sync error.
     *
     * @param int $limit max rows
     * @return int[] student ids
     */
    public function getStudentIdsForBulkHemisSync($limit = 50)
    {
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 50;
        }
        if ($limit > 10000) {
            $limit = 10000;
        }
        if (!$this->db->field_exists('hemis_student_id', 'students')) {
            return array();
        }
        $this->db->select('st.id', false);
        $this->db->from('students st');
        $this->db->join('student_session ss', 'ss.student_id = st.id', 'inner');
        $this->db->where('ss.session_id', (int) $this->current_session);
        $this->db->where('ss.is_active', 'yes');
        $this->db->where('st.is_active', 'yes');
        $this->db->group_start();
        $this->db->where('st.hemis_student_id IS NULL', null, false);
        $this->db->or_where('st.hemis_student_id <', 1);
        if ($this->db->field_exists('hemis_sync_error', 'students')) {
            $this->db->or_where('(st.hemis_sync_error IS NOT NULL AND TRIM(st.hemis_sync_error) != \'\')', null, false);
        }
        $this->db->group_end();
        $this->db->group_by('st.id');
        $this->db->order_by('st.id', 'ASC');
        $this->db->limit($limit);
        $q = $this->db->get();
        if (!$q) {
            return array();
        }
        $out = array();
        foreach ($q->result_array() as $r) {
            if (!empty($r['id'])) {
                $out[] = (int) $r['id'];
            }
        }
        return $out;
    }

    public function findByAdmission($admission_no = null)
    {
        $this->db->select('student_session.transport_fees,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no, students.roll_no,students.admission_date,students.firstname, students.middlename,students.lastname,students.image,   students.mobileno,students.email,students.state,students.city,students.pincode,students.note,students.religion, students.cast,school_houses.house_name,students.dob,students.current_address,students.previous_school,
            students.guardian_is,students.parent_id,            students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.father_pic ,students.height ,students.weight,students.measurement_date, students.mother_pic , students.guardian_pic , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.rte,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id', 'left');
        $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('users.role', 'student');
        $this->db->where('students.is_active', 'yes');
        $this->db->where('students.admission_no', $admission_no);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function search_alumniStudent($class_id = null, $section_id = null, $session_id = null, $program_id = null)
    {
        $i = 1;

        $custom_fields   = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);
        $field_select   = $field_variable !== '' ? (',' . $field_variable) : '';

        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,GROUP_CONCAT(DISTINCT classes.class,"(",sections.section,")") as class,sections.id AS `section_id`,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno,students.email,students.state,students.city, students.pincode,students.religion,students.dob,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code , students.guardian_name,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.rte,students.guardian_is,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note' . $field_select)->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where('student_session.is_alumni', 1);
        $this->db->where('student_session.is_active', "yes");
        if ($class_id != null && $class_id !== '') {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null && $section_id !== '') {
            $this->db->where('student_session.section_id', $section_id);
        }
        if ($program_id != null && $program_id !== '') {
            $this->db->where('student_session.program_id', $program_id);
        }
        if ($session_id != null && $session_id !== '') {
            $this->db->where('student_session.session_id', $session_id);
        }
        $this->db->group_by('students.id');
        $this->db->order_by('students.admission_no', 'asc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getSearchFullView($searchterm, $start, $limit, $search, $carray, $section_id = null, $program_id = null, $class_id = null)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $userdata            = $this->customlib->getUserData();
        $staff_id            = $userdata['id'];

        $i             = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);

        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

      $field_variable = (empty($field_var_array))? "": ",".implode(',', $field_var_array);
      
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $this->db->or_group_start();
                    $this->db->where('student_session.class_id', $class_sectionkey);
                    $this->db->where('student_session.section_id', $class_sectionvaluevalue);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();
        }

        $this->db->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,classes.class,sections.id AS `section_id`,sections.section,programs.id as program_id,programs.title as program_title,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id,student_session.is_active as session_is_active' . $field_variable)->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        // Include program_id so shared class+section across programs does not duplicate rows.
        $this->db->join('class_sections', 'class_sections.class_id = student_session.class_id AND class_sections.section_id = student_session.section_id AND class_sections.program_id = student_session.program_id', 'left');
        $this->db->join('programs', 'programs.id = class_sections.program_id', 'left');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('school_houses', 'students.school_house_id = school_houses.id', 'left');
        // Match list search: include all current-session enrollments (active + marked promoted).
        // Exclude graduated/alumni rows (kept is_active=yes for Alumni module).
        $this->db->where('student_session.session_id', $this->current_session);
        if ($this->db->field_exists('is_alumni', 'student_session')) {
            $this->db->where('COALESCE(student_session.is_alumni, 0) = 0', null, false);
        }
        if ($this->db->field_exists('is_graduated', 'student_session')) {
            $this->db->where('COALESCE(student_session.is_graduated, 0) = 0', null, false);
        }
        if ($section_id !== null && $section_id !== '') {
            $this->db->where('student_session.section_id', $section_id);
        }
        if ($class_id !== null && $class_id !== '') {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($program_id !== null && $program_id !== '') {
            $this->db->where('student_session.program_id', $program_id);
        }
        $this->db->group_start();
        $this->db->like('students.firstname', $searchterm);
        $this->db->or_like('students.middlename', $searchterm);
        $this->db->or_like('students.lastname', $searchterm);
        $this->db->or_like('school_houses.house_name', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->or_like('students.adhar_no', $searchterm);
        $this->db->or_like('students.samagra_id', $searchterm);
        $this->db->or_like('students.roll_no', $searchterm);
        $this->db->or_like('students.admission_no', $searchterm);
        $this->db->or_like('students.mobileno', $searchterm);
        $this->db->or_like('students.email', $searchterm);
        $this->db->or_like('students.religion', $searchterm);
        $this->db->or_like('students.cast', $searchterm);
        $this->db->or_like('students.gender', $searchterm);
        $this->db->or_like('students.current_address', $searchterm);
        $this->db->or_like('students.permanent_address', $searchterm);
        $this->db->or_like('students.blood_group', $searchterm);
        $this->db->or_like('students.bank_name', $searchterm);
        $this->db->or_like('students.ifsc_code', $searchterm);
        $this->db->or_like('students.father_name', $searchterm);
        $this->db->or_like('students.father_phone', $searchterm);
        $this->db->or_like('students.father_occupation', $searchterm);
        $this->db->or_like('students.mother_name', $searchterm);
        $this->db->or_like('students.mother_phone', $searchterm);
        $this->db->or_like('students.mother_occupation', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->or_like('students.guardian_relation', $searchterm);
        $this->db->or_like('students.guardian_phone', $searchterm);
        $this->db->or_like('students.guardian_occupation', $searchterm);
        $this->db->or_like('students.guardian_address', $searchterm);
        $this->db->or_like('students.guardian_email', $searchterm);
        $this->db->or_like('students.previous_school', $searchterm);
        $this->db->or_like('students.note', $searchterm);
        $this->db->group_end();

        $searchable = 'students.admission_no,students.firstname,students.middlename,students.lastname,classes.class,students.father_name,students.dob,students.gender,categories.category,students.mobileno' . $field_variable;
        $column     = explode(',', $searchable);

        $search_colomn        = array();

        foreach ($column as $key => $col) {
            $col         = strtolower($col);
            $col         = strstr($col, ' as ', true) ?: $col;
            $search_colomn[] = $col;
        }
        // DataTables sends `search[value]`. Guard against missing/invalid shapes so
        // PHP notices don't corrupt JSON responses (which triggers "DataTables error").
        $search = (is_array($search) && isset($search['value'])) ? (string) $search['value'] : '';
        if ($search !== '' && $searchable != '') {
                for ($i = 0; $i < count($search_colomn); $i++) {
                    if ($i == 0) {
                        $this->db->group_start();
                        $this->db->like($search_colomn[$i], $search);
                    } else {
                        $this->db->or_like($search_colomn[$i], $search);
                    }

                    if (count($search_colomn) - 1 == $i) //last loop
                    {
                        $this->db->group_end();
                    }
                    //close bracket
                }    
        }

  

        $this->db->limit($limit, $start);
        $this->db->order_by('students.id');
        $query  = $this->db->get();
        $result = $query->result_array();
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
        return $result;
    }

    public function search_alumniStudentbyAdmissionNo($searchterm, $carray)
    {
        $userdata        = $this->customlib->getUserData();
        $staff_id        = $userdata['id'];
        $i               = 1;
        $custom_fields   = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);
        $field_select   = $field_variable !== '' ? (',' . $field_variable) : '';

        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if (!empty($carray)) {
                $this->db->where_in("student_session.class_id", $carray);
                $sections = $this->teacher_model->get_teacherrestricted_modeallsections($staff_id);
                foreach ($sections as $key => $value) {
                    $sections_id[] = $value['section_id'];
                }
                $this->db->where_in("student_session.section_id", $sections_id);
            } else {
                // No allowed classes, return empty result to avoid SQL error
                return array();
            }
        }
        $this->db->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,GROUP_CONCAT(DISTINCT classes.class,"(",sections.section,")") as class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,   students.mobileno, students.email ,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code ,students.father_name,students.guardian_name,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at,students.updated_at,students.gender,students.rte,student_session.session_id' . $field_select)->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('alumni_students', 'alumni_students.student_id = students.id', 'left');
        // Do not lock to current academic session — alumni may have passed out in any session.
        $this->db->where('student_session.is_alumni', '1');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->group_start();
        $this->db->like('students.admission_no', $searchterm);
        $this->db->or_like('students.firstname', $searchterm);
        $this->db->or_like('students.middlename', $searchterm);
        $this->db->or_like('students.lastname', $searchterm);
        $this->db->or_like('students.email', $searchterm);
        $this->db->or_like('students.mobileno', $searchterm);
        $this->db->or_like('alumni_students.current_email', $searchterm);
        $this->db->or_like('alumni_students.current_phone', $searchterm);
        $this->db->group_end();
        $this->db->group_by('students.id');
        $this->db->order_by('students.id');
        $query = $this->db->get();
        if ($query === false) {
            return array();
        }
        return $query->result_array();
    }

    public function guardian_credential($parent_id)
    {
        $this->db->select('id,user_id,username,password')->from('users');
        $this->db->where('id', $parent_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function search_student()
    {
        $this->db->select('classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,students.category_id,    students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_session.session_id', $this->current_session);
        if ($id != null) {
            $this->db->where('students.id', $id);
        } else {
            $this->db->order_by('students.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getstudentdoc($id)
    {
        $this->db->select()->from('student_doc');
        $this->db->where('student_id', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getDatatableByClassSection($class_id = null, $section_id = null)
    {
        $this->datatables
            ->select('classes.id as `class_id`, student_session.id as student_session_id, students.id, classes.class, sections.id as `section_id`, sections.section, programs.id as program_id, programs.title as program, students.id, students.admission_no, students.roll_no, students.admission_date, students.firstname, students.middlename, students.lastname, students.image, students.mobileno, students.email, students.state, students.city, students.pincode, students.religion, students.dob, students.current_address, students.permanent_address, IFNULL(students.category_id, 0) as `category_id`, IFNULL(categories.category, "") as `category`, students.adhar_no, students.samagra_id, students.bank_account_no, students.bank_name, students.ifsc_code, students.guardian_name, students.guardian_relation, students.guardian_phone, students.guardian_address, students.is_active, students.created_at, students.updated_at, students.father_name, students.app_key, students.parent_app_key, students.rte, students.gender')
            // IMPORTANT: qualify class_id/section_id to avoid ambiguity with class_sections.*
            ->searchable('student_session.class_id, student_session.section_id, admission_no, students.firstname, students.middlename, students.lastname, students.father_name, students.dob, students.guardian_phone, programs.title')
            ->orderable('student_session.class_id, student_session.section_id, admission_no, students.firstname, students.father_name, students.dob, students.guardian_phone, programs.title')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('class_sections', 'class_sections.class_id = student_session.class_id AND class_sections.section_id = student_session.section_id AND class_sections.program_id = student_session.program_id', 'left')
            ->join('classes', 'student_session.class_id = classes.id')
            ->join('sections', 'sections.id = student_session.section_id')
            ->join('programs', 'programs.id = class_sections.program_id', 'left')
            ->join('categories', 'students.category_id = categories.id', 'left')
            ->where('student_session.session_id', $this->current_session)
            ->where('student_session.is_active', "yes")
            ->sort('students.admission_no', 'asc');
        
        if ($class_id != null) {
            $this->datatables->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->datatables->where('student_session.section_id', $section_id);
        }
    
        $this->datatables->from('students');
        return $this->datatables->generate('json');
    }

    public function getDatatableByFullTextSearch($searchterm, $section_id = null, $program_id = null, $class_id = null)
    {
        $userdata = $this->customlib->getUserData();
        $class_section_array = $this->customlib->get_myClassSection();

        $hemis_extra = '';
        if ($this->db->field_exists('hemis_student_id', 'students')) {
            $hemis_extra .= ', students.hemis_student_id';
        }
        if ($this->db->field_exists('hemis_sync_error', 'students')) {
            $hemis_extra .= ', students.hemis_sync_error';
        }
        
        $this->datatables->select('classes.id as `class_id`, students.id, student_session.id as `student_session_id`, classes.class, sections.id as `section_id`, sections.section, programs.id as program_id, programs.title as program, students.id, students.admission_no, students.roll_no, students.admission_date, students.firstname, students.middlename, students.lastname, students.image, students.mobileno, students.email, students.state, students.city, students.pincode, students.religion, students.dob, students.current_address, students.permanent_address, IFNULL(students.category_id, 0) as `category_id`, IFNULL(categories.category, "") as `category`, students.adhar_no, students.samagra_id, students.bank_account_no, students.bank_name, students.ifsc_code, students.father_name, students.guardian_name, students.guardian_relation, students.guardian_phone, students.guardian_address, students.is_active, students.created_at, students.updated_at, students.gender, students.rte, student_session.session_id, student_session.is_active as session_is_active' . $hemis_extra)
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('class_sections', 'class_sections.class_id = student_session.class_id AND class_sections.section_id = student_session.section_id AND class_sections.program_id = student_session.program_id', 'left')
            ->join('classes', 'student_session.class_id = classes.id')
            ->join('sections', 'sections.id = student_session.section_id')
            ->join('programs', 'programs.id = class_sections.program_id', 'left')
            ->join('categories', 'students.category_id = categories.id', 'left')
            ->join('school_houses', 'students.school_house_id = school_houses.id', 'left');
        
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $this->datatables->or_group_start();
                    $this->datatables->where('student_session.class_id', $class_sectionkey);
                    $this->datatables->where('student_session.section_id', $class_sectionvaluevalue);
                    $this->datatables->group_end();
                }
            }
            $this->datatables->group_end();
        }
        
        $this->datatables->group_start();
        $this->datatables->or_like_string('students.firstname, students.middlename, students.lastname, school_houses.house_name, students.guardian_name, students.adhar_no, students.samagra_id, students.roll_no, students.admission_no, students.mobileno, students.email, students.religion, students.cast, students.gender, students.current_address, students.permanent_address, students.blood_group, students.bank_name, students.ifsc_code, students.father_name, students.father_phone, students.father_occupation, students.mother_name, students.mother_phone, students.mother_occupation, students.guardian_name, students.guardian_relation, students.guardian_phone, students.guardian_occupation, students.guardian_address, students.guardian_email, students.previous_school, students.note, programs.title', $searchterm);
        $this->datatables->group_end();

        if ($section_id !== null && $section_id !== '') {
            $this->datatables->where('student_session.section_id', $section_id);
        }
        if ($class_id !== null && $class_id !== '') {
            $this->datatables->where('student_session.class_id', $class_id);
        }
        if ($program_id !== null && $program_id !== '') {
            $this->datatables->where('student_session.program_id', $program_id);
        }
        
        // Student Search UI states: keyword search should include both active and promoted students
        // in the current session, so we must not filter out student_session.is_active = 'no'.
        // Graduation creates a new session row with is_alumni/is_graduated=1 and is_active='yes'
        // at the same class — exclude those so alumni do not appear as enrolled students.
        // IMPORTANT: use a full SQL condition (no separate value). CI's _has_operator() treats
        // the space in "COALESCE(..., 0)" as an operator and would emit "COALESCE(...) 0"
        // (missing "="), which breaks DataTables JSON.
        $this->datatables->where('student_session.session_id', $this->current_session);
        if ($this->db->field_exists('is_alumni', 'student_session')) {
            $this->datatables->where('COALESCE(student_session.is_alumni, 0) = 0', null, false, false);
        }
        if ($this->db->field_exists('is_graduated', 'student_session')) {
            $this->datatables->where('COALESCE(student_session.is_graduated, 0) = 0', null, false, false);
        }
        $this->datatables->sort('students.admission_no', 'asc');
        // IMPORTANT: qualify class_id/section_id to avoid ambiguity with class_sections.*
        $this->datatables->searchable('student_session.class_id, student_session.section_id, admission_no, students.firstname, students.middlename, students.lastname, students.father_name, students.dob, students.guardian_phone, programs.title');
        $this->datatables->orderable('student_session.class_id, student_session.section_id, admission_no, students.firstname, students.father_name, students.dob, students.guardian_phone, programs.title');
        $this->datatables->from('students');
        
        $std_data = $this->datatables->generate('json');
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $std_data = json_decode($std_data);
            $std_data->data = array();
            return json_encode($std_data);
        } else {
            return $std_data;
        }
    }

    public function getStudentsByArray($array)
    {
        $i = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students');
        $field_var_array = array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "tablecustom" . $i;
                array_push($field_var_array, 'tablecustom' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }
    
        $field_variable = implode(',', $field_var_array);
    
        $this->db->select('classes.id AS class_id, student_session.id as student_session_id, students.id, classes.class, 
            sections.id AS section_id, sections.section, students.id, students.admission_no, students.roll_no, 
            students.admission_date, students.firstname, students.middlename, students.lastname, students.image, 
            students.mobileno, students.email, students.state, students.city, students.pincode, students.religion, 
            students.dob, students.current_address, students.permanent_address, IFNULL(students.category_id, 0) as category_id, 
            IFNULL(categories.category, "") as category, students.adhar_no, students.samagra_id, students.bank_account_no, 
            students.cast, students.bank_name, students.ifsc_code, students.guardian_name, students.guardian_relation, 
            students.guardian_phone, students.guardian_address, students.is_active, students.created_at, students.updated_at, 
            students.father_name, students.rte, students.gender, students.blood_group, users.id as user_tbl_id, 
            users.username, users.password as user_tbl_password, users.is_active as user_tbl_active, 
            programs.id as program_id, programs.title as program_name, ' . $field_variable)
            ->from('students');
        
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('class_sections', 'classes.id = class_sections.class_id AND sections.id = class_sections.section_id', 'left');
        $this->db->join('programs', 'class_sections.program_id = programs.id', 'left');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'students.id = users.id', 'left');
        $this->db->where_in('students.id', $array);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function searchByClassSection($class_id = null, $section_id = null)
    {
        $userdata = $this->customlib->getUserData();
        $i = 1;
        $class_section_array = $this->customlib->get_myClassSection();
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }
    
        $field_variable = implode(',', $field_var_array);
    
        $this->db->select('classes.id AS `class_id`, student_session.id as student_session_id, students.id, classes.class, 
            sections.id AS `section_id`, sections.section, students.id, students.admission_no, students.roll_no, 
            students.admission_date, students.firstname, students.middlename, students.lastname, students.image, 
            students.mobileno, students.email, students.state, students.city, students.pincode, students.religion, 
            students.dob, students.current_address, students.permanent_address, IFNULL(students.category_id, 0) as `category_id`, 
            IFNULL(categories.category, "") as `category`, students.adhar_no, students.samagra_id, students.bank_account_no, 
            students.bank_name, students.ifsc_code, students.guardian_name, students.guardian_relation, 
            students.guardian_phone, students.guardian_address, students.is_active, students.created_at, students.updated_at, 
            students.father_name, students.app_key, students.parent_app_key, students.rte, students.gender, 
            vehicles.vehicle_no, transport_route.route_title, route_pickup_point.id as `route_pickup_point_id`, 
            pickup_point.name as `pickup_point`, programs.id as program_id, programs.title as program_name, ' . $field_variable)
            ->from('students');
        
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('class_sections', 'classes.id = class_sections.class_id AND sections.id = class_sections.section_id', 'left');
        $this->db->join('programs', 'class_sections.program_id = programs.id', 'left');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('route_pickup_point', 'student_session.route_pickup_point_id = route_pickup_point.id', 'left');
        $this->db->join('pickup_point', 'pickup_point.id = route_pickup_point.pickup_point_id', 'left');
        $this->db->join('vehicle_routes', 'student_session.vehroute_id = vehicle_routes.id', 'left');
        $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
        $this->db->join('vehicles', 'vehicle_routes.vehicle_id = vehicles.id', 'left');
        
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', "yes");
        
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
        
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->db->or_where($query_string);
                }
            }
            $this->db->group_end();
        }
        
        $this->db->order_by('students.admission_no', 'asc');
        $query = $this->db->get();
        $result = $query->result_array();
        
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
        
        return $result;
    }

    public function searchdatatablebyAdmissionDetails($class_id, $year)
    {
        if (!empty($year)) {
            $data = array('year(admission_date)' => $year, 'student_session.class_id' => $class_id);
        } else {
            $data = array('student_session.class_id' => $class_id);
        }


        $this->datatables->select('students.firstname,students.middlename,students.lastname,students.is_active, students.mobileno, students.id as sid ,students.admission_no, students.admission_date, students.guardian_name, students.guardian_relation, students.guardian_phone, classes.class, sessions.id, sections.section')
            ->searchable('students.admission_no,students.firstname,students.admission_date,students.mobileno,students.guardian_name,students.guardian_phone')
            ->join('student_session', 'student_session.student_id = students.id AND student_session.is_active = "yes"')
            ->join('classes', 'student_session.class_id = classes.id')
            ->join('sections', 'sections.id = student_session.section_id')
            ->join('sessions', 'student_session.session_id = sessions.id')
            ->where($data)
            ->group_by('students.id, students.firstname, students.middlename, students.lastname, students.is_active, students.mobileno, students.admission_no, students.admission_date, students.guardian_name, students.guardian_relation, students.guardian_phone, classes.class, sessions.id, sections.section')
            ->orderable('students.admission_no,students.firstname,students.admission_date," "," "," ",students.mobileno,students.guardian_name,students.guardian_phone')
            ->sort('students.id')
            ->from('students');
        $json = $this->datatables->generate('json');
        $result = json_decode($json);
        // Convert data objects to arrays for DataTables compatibility and match table headings
        if (isset($result->data) && is_array($result->data)) {
            $new_data = array();
            foreach ($result->data as $student) {
                $row = array();
                $row[] = $student->admission_no ?? '';
                // Student Name (concatenate first, middle, last)
                $row[] = trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? ''));
                $row[] = $student->admission_date ?? '';
                // Academic Level (was Class (Start - End))
                $row[] = $student->section ?? '';
                // Faculty (was Session (Start - End))
                $row[] = $student->class ?? '';
                $row[] = $student->years ?? '';
                $row[] = $student->mobileno ?? '';
                $row[] = $student->guardian_name ?? '';
                $row[] = $student->guardian_phone ?? '';
                $new_data[] = $row;
            }
            $result->data = $new_data;
        }
        return json_encode($result);
    }

    public function admission_report($searchterm, $carray = null, $condition = null)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $userdata            = $this->customlib->getUserData();


        $i               = 1;
        $custom_fields   = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }


        $field_variable = implode(',', $field_var_array);


        if ($condition != null) {
            $this->datatables->where($condition);
        }


        /*----------------------------------------*/
        $this->datatables->select('classes.id AS `class_id`,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no, students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,   students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id,' . $field_variable);
        $this->datatables->searchable('admission_no,students.firstname,classes.class,students.father_name,students.dob,students.admission_date,students.gender,categories.category');
        $this->datatables->orderable('admission_no,students.firstname,classes.class,students.father_name,students.dob,students.admission_date,students.gender,categories.category');
        $this->datatables->join('student_session', 'student_session.student_id = students.id AND student_session.is_active = "yes"');
        $this->datatables->join('classes', 'student_session.class_id = classes.id');
        $this->datatables->join('sections', 'sections.id = student_session.section_id');
        $this->datatables->join('categories', 'students.category_id = categories.id', 'left');
        $this->datatables->where('student_session.session_id', $this->current_session);
        $this->datatables->where('student_session.is_active', 'yes');
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->datatables->or_where($query_string);
                }
            }
            $this->datatables->group_end();
        }
        $this->datatables->group_start();
        $this->datatables->or_like_string('students.firstname,students.lastname,students.guardian_name,students.adhar_no,students.samagra_id,students.roll_no,students.admission_no', $searchterm);
        $this->datatables->group_end();
        $this->datatables->sort('students.id');
        $this->datatables->from('students');
        $json = $this->datatables->generate('json');
        $result = json_decode($json);
        // Convert data objects to arrays for DataTables compatibility and match table headings
        if (isset($result->data) && is_array($result->data)) {
            $new_data = array();
            foreach ($result->data as $student) {
                $row = array();
                $row[] = $student->admission_no ?? '';
                // Student Name (concatenate first, middle, last)
                $row[] = trim(($student->firstname ?? '') . ' ' . ($student->middlename ?? '') . ' ' . ($student->lastname ?? ''));
                $row[] = $student->admission_date ?? '';
                // Academic Level (was Class (Start - End))
                $row[] = $student->section ?? '';
                // Faculty (was Session (Start - End))
                $row[] = $student->class ?? '';
                $row[] = $student->years ?? '';
                $row[] = $student->mobileno ?? '';
                $row[] = $student->guardian_name ?? '';
                $row[] = $student->guardian_phone ?? '';
                $new_data[] = $row;
            }
            $result->data = $new_data;
        }
        return json_encode($result);
    }



    public function searchByClassSectionWithoutCurrent($class_id = null, $section_id = null, $student_id = null)
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', "yes");
        $this->db->where('students.id !=', $student_id);
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
        $this->db->order_by('students.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function searchdatatableByClassSectionCategoryGenderRte($class_id = null, $section_id = null, $category = null, $gender = null, $rte = null)
    {

        if ($class_id != null) {
            $this->datatables->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->datatables->where('student_session.section_id', $section_id);
        }
        if ($category != null) {
            $this->datatables->where('students.category_id', $category);
        }
        if ($gender != null) {
            $this->datatables->where('students.gender', $gender);
        }
        if ($rte != null) {
            $this->datatables->where('students.rte', $rte);
        }

        $this->datatables->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,students.category_id, categories.category,   students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender')

            ->searchable('sections.section,students.admission_no,students.firstname,students.father_name,students.dob,students.gender,categories.category,students.mobileno,students.samagra_id,students.adhar_no,students.rte')
            ->orderable('sections.section,students.admission_no,students.firstname,students.father_name,students.dob,students.gender,categories.category,students.mobileno,students.samagra_id,students.adhar_no,students.rte')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('classes', 'student_session.class_id = classes.id')
            ->join('sections', 'sections.id = student_session.section_id')
            ->join('categories', 'students.category_id = categories.id', 'left')
            ->where('student_session.session_id', $this->current_session)
            ->where('students.is_active', 'yes')
            ->sort('students.id')
            ->from('students');
        return $this->datatables->generate('json');
    }

    public function searchusersbyFullText($searchterm, $carray = null)
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $userdata            = $this->customlib->getUserData();
        $staff_id            = $userdata['id'];

        $i             = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);

        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);

        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $this->db->or_group_start();
                    $this->db->where('student_session.class_id', $class_sectionkey);
                    $this->db->where('student_session.section_id', $class_sectionvaluevalue);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();
        }

        $this->db->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id,' . $field_variable)->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('school_houses', 'students.school_house_id = school_houses.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->group_start();
        $this->db->like('students.firstname', $searchterm);
        $this->db->or_like('students.middlename', $searchterm);
        $this->db->or_like('students.lastname', $searchterm);
        $this->db->or_like('school_houses.house_name', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->or_like('students.adhar_no', $searchterm);
        $this->db->or_like('students.samagra_id', $searchterm);
        $this->db->or_like('students.roll_no', $searchterm);
        $this->db->or_like('students.admission_no', $searchterm);
        $this->db->or_like('students.mobileno', $searchterm);
        $this->db->or_like('students.email', $searchterm);
        $this->db->or_like('students.religion', $searchterm);
        $this->db->or_like('students.cast', $searchterm);
        $this->db->or_like('students.gender', $searchterm);
        $this->db->or_like('students.current_address', $searchterm);
        $this->db->or_like('students.permanent_address', $searchterm);
        $this->db->or_like('students.blood_group', $searchterm);
        $this->db->or_like('students.bank_name', $searchterm);
        $this->db->or_like('students.ifsc_code', $searchterm);
        $this->db->or_like('students.father_name', $searchterm);
        $this->db->or_like('students.father_phone', $searchterm);
        $this->db->or_like('students.father_occupation', $searchterm);
        $this->db->or_like('students.mother_name', $searchterm);
        $this->db->or_like('students.mother_phone', $searchterm);
        $this->db->or_like('students.mother_occupation', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->or_like('students.guardian_relation', $searchterm);
        $this->db->or_like('students.guardian_phone', $searchterm);
        $this->db->or_like('students.guardian_occupation', $searchterm);
        $this->db->or_like('students.guardian_address', $searchterm);
        $this->db->or_like('students.guardian_email', $searchterm);
        $this->db->or_like('students.previous_school', $searchterm);
        $this->db->or_like('students.note', $searchterm);
        $this->db->group_end();
        $this->db->order_by('students.id');
        $query  = $this->db->get();
        $result = $query->result_array();
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
        return $result;
    }

    public function student_ratio()
    {
        $i = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, $tb_counter . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 
                    'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }
    
        // Build select statement - Added programs.title and programs.code
        $base_select = 'COUNT(*) as total_student, ' .
                       'SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) AS "male", ' .
                       'SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) AS "female", ' .
                       'classes.class, sections.section, classes.id as class_id, sections.id as section_id, ' .
                       'programs.title as program_title, programs.code as program_code, programs.id as program_id';
        
        $field_variable = '';
        if (!empty($field_var_array)) {
            $field_variable = ', ' . implode(', ', $field_var_array);
        }
        
        $this->db->select($base_select . $field_variable);
        $this->db->from('students');
        
        // Join tables - Updated to use class_sections.program_id
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('class_sections', 'class_sections.class_id = classes.id AND class_sections.section_id = sections.id', 'inner');
        
        // Join programs table using class_sections.program_id
        $this->db->join('programs', 'programs.id = class_sections.program_id', 'left');
        
        // Add conditions
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        
        // Group and order - Added programs.id to GROUP BY
        $this->db->group_by('class_sections.id, programs.id');
        $this->db->order_by('classes.id, sections.id, programs.title');
        
        $query = $this->db->get();
        
        // Error handling
        if (!$query) {
            // Log the database error for debugging
            $db_error = $this->db->error();
            log_message('error', 'Database query failed in student_ratio: ' . $db_error['message'] . ' (Code: ' . $db_error['code'] . ')');
            
            return array();
        }
        
        $result = $query->result_array();
    
        foreach ($result as &$row) {
            if ($row['total_student'] > 0) {
                $row['male_percentage'] = round(($row['male'] / $row['total_student']) * 100, 2);
                $row['female_percentage'] = round(($row['female'] / $row['total_student']) * 100, 2);
            } else {
                $row['male_percentage'] = 0;
                $row['female_percentage'] = 0;
            }
        }
        
        return $result;
    }

    public function sibling_report($searchterm, $carray = null, $condition = null)
    {
        $userdata = $this->customlib->getUserData();
        $i = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, $tb_counter . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 
                    'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }
    
        // Build select statement with custom fields
        $base_select = 'classes.id AS `class_id`, students.id, classes.class, sections.id AS `section_id`, sections.section, ' .
                       'students.admission_no, students.roll_no, students.admission_date, students.firstname, students.middlename, ' .
                       'students.lastname, students.image, students.mobileno, students.email, students.state, students.city, ' .
                       'students.pincode, students.religion, students.dob, students.current_address, students.permanent_address, ' .
                       'IFNULL(students.category_id, 0) as `category_id`, IFNULL(categories.category, "") as `category`, ' .
                       'students.adhar_no, students.samagra_id, students.bank_account_no, students.bank_name, students.ifsc_code, ' .
                       'students.father_name, students.mother_name, students.guardian_name, students.guardian_relation, ' .
                       'students.guardian_phone, students.guardian_address, students.is_active, students.created_at, ' .
                       'students.updated_at, students.gender, students.rte, student_session.session_id, students.parent_id';
        
        $field_variable = '';
        if (!empty($field_var_array)) {
            $field_variable = ', ' . implode(', ', $field_var_array);
        }
        
        $this->db->select($base_select . $field_variable);
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        
        // Add base conditions
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        
        // Handle class teacher permissions
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if (!empty($carray)) {
                $this->db->where_in("student_session.class_id", $carray);
            } else {
                // If no classes specified for class teacher, return empty result
                return array();
            }
        }
        
        // Add custom condition if provided
        if ($condition != null) {
            $this->db->where($condition);
        }
        
        // Add search functionality if search term is provided
        if (!empty($searchterm)) {
            $this->db->group_start();
            $this->db->like('students.firstname', $searchterm);
            $this->db->or_like('students.lastname', $searchterm);
            $this->db->or_like('students.admission_no', $searchterm);
            $this->db->or_like('students.father_name', $searchterm);
            $this->db->or_like('students.mother_name', $searchterm);
            $this->db->or_like('classes.class', $searchterm);
            $this->db->or_like('sections.section', $searchterm);
            $this->db->group_end();
        }
        
        $this->db->group_by('students.admission_no');
        $this->db->order_by('students.id');
        
        $query = $this->db->get();
        
        // Error handling
        if (!$query) {
            // Log the database error for debugging
            $db_error = $this->db->error();
            log_message('error', 'Database query failed in sibling_report: ' . $db_error['message'] . ' (Code: ' . $db_error['code'] . ')');
            
            return array(); // Return empty array instead of letting it fail
        }
        
        return $query->result_array();
    }

    public function sibling_reportsearch($searchterm, $carray = null, $condition = null) 
    {
        $userdata = $this->customlib->getUserData();
        $i = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, $tb_counter . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 
                    'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }
    
        // Build the select statement
        $select_fields = 'students.parent_id, students.admission_no, students.father_name';
        if (!empty($field_var_array)) {
            $field_variable = implode(',', $field_var_array);
            $select_fields .= ', ' . $field_variable;
        }
        
        $this->db->select($select_fields);
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        
        // Add conditions
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        
        // Handle class teacher permissions
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if (!empty($carray)) {
                $this->db->where_in("student_session.class_id", $carray);
            } else {
                // If carray is empty, return empty result instead of invalid where_in
                return array();
            }
        }
        
        // Add custom condition if provided
        if ($condition != null) {
            $this->db->where($condition);
        }
        
        // Add search term condition (assuming it's for searching names or admission numbers)
        if (!empty($searchterm)) {
            $this->db->group_start();
            $this->db->like('students.firstname', $searchterm);
            $this->db->or_like('students.lastname', $searchterm);
            $this->db->or_like('students.admission_no', $searchterm);
            $this->db->or_like('students.father_name', $searchterm);
            $this->db->group_end();
        }
        
        $this->db->group_by('students.parent_id');
        $this->db->group_by('students.admission_no');
        $this->db->order_by('students.father_name');
        
        $query = $this->db->get();
        
        // Error handling
        if (!$query) {
            // Log the database error
            log_message('error', 'Database query failed in sibling_reportsearch: ' . $this->db->error()['message']);
            return array(); // Return empty array instead of false
        }
        
        return $query->result_array();
    }

    public function getStudentListBYStudentsessionID($array)
    {
        $array = implode(',', $array);
        $sql   = ' SELECT students.* FROM students INNER join (SELECT * FROM `student_session` WHERE `student_session`.`id` IN (' . $array . ')) as student_session on students.id=student_session.student_id';
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function remove($id)
    {
        $this->db->trans_start();

        $sql   = "SELECT * FROM `users` WHERE childs LIKE '%," . $id . ",%' OR childs LIKE '" . $id . ",%' OR childs LIKE '%," . $id . "' OR childs = " . $id;
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $result      = $query->row();
            $array_slice = explode(',', $result->childs);
            if (count($array_slice) > 1) {
                $arr    = array_diff($array_slice, array($id));
                $update = implode(",", $arr);
                $data   = array('childs' => $update);
                $this->db->where('id', $result->id);
                $this->db->update('users', $data);
            } else {
                $this->db->where('id', $result->id);
                $this->db->delete('users');
            }
        }

        $this->db->where('id', $id);
        $this->db->delete('students');

        $this->db->where('student_id', $id);
        $this->db->delete('student_session');

        $this->db->where('user_id', $id);
        $this->db->where('role', 'student');
        $this->db->delete('users');
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function doc_delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('student_doc');
    }

    public function add($data, $data_setting = array())
    {
        // Log the incoming data
        error_log("Attempting to add student with data: " . print_r($data, true));
        
        try {
            if (isset($data['id'])) {
                $this->db->where('id', $data['id']);
                $this->db->update('students', $data);
                $message   = UPDATE_RECORD_CONSTANT . " On students id " . $data['id'];
                $action    = "Update";
                $record_id = $insert_id = $data['id'];
                $this->log($message, $record_id, $action);
                return $data['id'];
            } else {
                // Start transaction
                $this->db->trans_start();

                if (!empty($data_setting) && isset($data_setting['adm_auto_insert'])) {
                    if ($data_setting['adm_auto_insert']) {
                        if ($data_setting['adm_update_status'] == 0) {
                            $data_setting['adm_update_status'] = 1;
                            $this->setting_model->add($data_setting);
                        }
                    }
                }

                // Check if admission number already exists
                $this->db->where('admission_no', $data['admission_no']);
                $query = $this->db->get('students');
                if ($query->num_rows() > 0) {
                    error_log("Admission number " . $data['admission_no'] . " already exists");
                    $this->db->trans_rollback();
                    return false;
                }

                // Log the SQL query before execution
                $this->db->set($data);
                $sql = $this->db->get_compiled_insert('students');
                error_log("SQL Query for insert: " . $sql);

                // Attempt insert with error handling
                $insert_result = false;
                try {
                    $insert_result = $this->db->insert('students', $data);
                } catch (Exception $e) {
                    error_log("Exception during insert: " . $e->getMessage());
                    $this->db->trans_rollback();
                    return false;
                }
                
                if (!$insert_result) {
                    $error = $this->db->error();
                    error_log("Insert failed. DB Error: " . print_r($error, true));
                    error_log("Last query: " . $this->db->last_query());
                    $this->db->trans_rollback();
                    return false;
                }

                $insert_id = $this->db->insert_id();
                if (!$insert_id) {
                    $error = $this->db->error();
                    error_log("Failed to get insert_id. DB Error: " . print_r($error, true));
                    error_log("Last query: " . $this->db->last_query());
                    $this->db->trans_rollback();
                    return false;
                }

                error_log("Successfully inserted student with ID: " . $insert_id);

                $message   = INSERT_RECORD_CONSTANT . " On students id " . $insert_id;
                $action    = "Insert";
                $record_id = $insert_id;
                $this->log($message, $record_id, $action);

                // Complete transaction
                $this->db->trans_complete();

                if ($this->db->trans_status() === false) {
                    error_log("Transaction failed. Rolling back.");
                    error_log("Last error: " . print_r($this->db->error(), true));
                    error_log("Last query: " . $this->db->last_query());
                    $this->db->trans_rollback();
                    return false;
                }

                return $insert_id;
            }
        } catch (Exception $e) {
            error_log("Exception in Student_model->add(): " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            if ($this->db->trans_status()) {
                $this->db->trans_rollback();
            }
            return false;
        }
    }

    public function add_student_sibling($data_sibling)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('student_sibling', $data_sibling);
            $message   = UPDATE_RECORD_CONSTANT . " On  student sibling id " . $data['id'];
            $action    = "Update";
            $record_id = $insert_id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('student_sibling', $data_sibling);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On student sibling id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);

        }
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $insert_id;
        }
    }

    public function add_student_session($data)
    {
        try {
            $this->db->trans_start();
            // Unique key is (student_id, session_id) — look up by that only.
            $this->db->where('session_id', $data['session_id']);
            $this->db->where('student_id', $data['student_id']);
            $q = $this->db->get('student_session');
            
            if ($q->num_rows() > 0) {
                $rec = $q->row_array();
                $this->db->where('id', $rec['id']);
                $this->db->update('student_session', $data);
                $record_id = $rec['id'];
                $message = UPDATE_RECORD_CONSTANT . " On student session id " . $record_id;
                $action = "Update";
            } else {
                $this->db->insert('student_session', $data);
                $record_id = $this->db->insert_id();
                
                if (!$record_id) {
                    $error = $this->db->error();
                    error_log("Failed to insert student_session. DB Error: " . print_r($error, true));
                    $this->db->trans_rollback();
                    return false;
                }
                
                $message = INSERT_RECORD_CONSTANT . " On student session id " . $record_id;
                $action = "Insert";
            }
            
            $this->log($message, $record_id, $action);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === false) {
                error_log("Transaction failed in add_student_session. Rolling back.");
                $this->db->trans_rollback();
                return false;
            }
            
            return $record_id;
            
        } catch (Exception $e) {
            error_log("Exception in add_student_session: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            if ($this->db->trans_status()) {
                $this->db->trans_rollback();
            }
            return false;
        }
    }

    /**
     * Current campus placement from student_session (source of truth).
     * students.class_id / section_id / program_id are legacy mirrors only.
     *
     * @param int      $student_id
     * @param int|null $session_id  defaults to sch_settings.session_id
     * @return array|null
     */
    public function get_current_placement($student_id, $session_id = null)
    {
        $student_id = (int) $student_id;
        if ($student_id < 1) {
            return null;
        }
        if ($session_id === null) {
            $cfg = $this->db->select('session_id')->from('sch_settings')->limit(1)->get()->row_array();
            $session_id = isset($cfg['session_id']) ? (int) $cfg['session_id'] : 0;
        } else {
            $session_id = (int) $session_id;
        }

        $this->db->from('student_session');
        $this->db->where('student_id', $student_id);
        if ($session_id > 0) {
            $this->db->order_by("(session_id = " . $session_id . ")", 'DESC', false);
        }
        $this->db->order_by("(is_active = 'yes')", 'DESC', false);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get()->row_array();
        return !empty($row) ? $row : null;
    }

    /**
     * Sync legacy students.class_id/section_id/program_id cache from a placement row.
     * Does not change HEMIS identity fields.
     *
     * @param int   $student_id
     * @param array $placement
     */
    public function sync_students_placement_cache($student_id, array $placement)
    {
        $student_id = (int) $student_id;
        if ($student_id < 1) {
            return;
        }
        $data = array();
        if ($this->db->field_exists('class_id', 'students') && isset($placement['class_id'])) {
            $data['class_id'] = (int) $placement['class_id'];
        }
        if ($this->db->field_exists('section_id', 'students') && isset($placement['section_id'])) {
            $data['section_id'] = (int) $placement['section_id'];
        }
        if ($this->db->field_exists('program_id', 'students') && array_key_exists('program_id', $placement)) {
            $data['program_id'] = (int) ($placement['program_id'] ?: 0);
        }
        if ($this->db->field_exists('semister_or_year_no', 'students') && array_key_exists('semister_or_year_no', $placement)
            && $placement['semister_or_year_no'] !== null && $placement['semister_or_year_no'] !== '') {
            $data['semister_or_year_no'] = $placement['semister_or_year_no'];
        }
        if ($this->db->field_exists('ugc_fiscal_year_id', 'students') && !empty($placement['ugc_fiscal_year_id'])) {
            $data['ugc_fiscal_year_id'] = (int) $placement['ugc_fiscal_year_id'];
            if ($this->db->field_exists('fiscal_year_id', 'students')) {
                $data['fiscal_year_id'] = (int) $placement['ugc_fiscal_year_id'];
            }
        }
        if ($data) {
            $this->db->where('id', $student_id)->update('students', $data);
        }
    }
    public function get_student_vehicle_months($student_session_id)
    {
        $get_transportmonth = $this->db->select('*')->from('student_vehicle_months')->where('student_session_id', $student_session_id)->get()->result_array();
        foreach ($get_transportmonth as $mkey => $mvalue) {
            $get_transportmonth[] = $mvalue['month'];
        }
        return $get_transportmonth;
    }

    public function add_student_session_update($data)
    {
        $this->db->where('session_id', $data['session_id']);
        $q = $this->db->get('student_session');
        if ($q->num_rows() > 0) {
            $this->db->where('session_id', $student_session);
            $this->db->update('student_session', $data);
        } else {
            $this->db->insert('student_session', $data);
            return $this->db->insert_id();
        }
    }

    public function alumni_student_status($data)
    {
        $this->db->where('student_id', $data['student_id']);
        $this->db->where('session_id', $this->current_session);
        $this->db->update('student_session', $data);
    }

    /**
     * Preview promote moves inferred from student_session pairs
     * (active destination + inactive previous same faculty/program).
     *
     * @param array $filters session_id, section_id, program_id, class_id (destination), search_text, limit
     * @return array
     */
    public function getPromoteHistory($filters = array())
    {
        $session_id  = !empty($filters['session_id']) ? (int) $filters['session_id'] : 0;
        $section_id  = !empty($filters['section_id']) ? (int) $filters['section_id'] : 0;
        $program_id  = !empty($filters['program_id']) ? (int) $filters['program_id'] : 0;
        $class_id    = !empty($filters['class_id']) ? (int) $filters['class_id'] : 0;
        $search_text = isset($filters['search_text']) ? trim((string) $filters['search_text']) : '';
        $limit       = !empty($filters['limit']) ? (int) $filters['limit'] : 200;
        if ($limit < 1 || $limit > 500) {
            $limit = 200;
        }

        $sql = "SELECT
                    s.id AS student_id,
                    s.admission_no,
                    s.firstname,
                    s.middlename,
                    s.lastname,
                    s.father_name,
                    s.is_active AS student_is_active,
                    new_ss.id AS to_session_row_id,
                    new_ss.session_id AS to_session_id,
                    new_ss.class_id AS to_class_id,
                    new_ss.section_id AS to_section_id,
                    new_ss.program_id AS to_program_id,
                    new_ss.is_active AS to_is_active,
                    new_ss.created_at AS to_created_at,
                    new_ss.updated_at AS to_updated_at,
                    old_ss.id AS from_session_row_id,
                    old_ss.session_id AS from_session_id,
                    old_ss.class_id AS from_class_id,
                    old_ss.section_id AS from_section_id,
                    old_ss.program_id AS from_program_id,
                    old_ss.is_active AS from_is_active,
                    old_ss.updated_at AS from_updated_at,
                    tc.class AS to_class,
                    ts.section AS to_section,
                    tp.title AS to_program,
                    tses.session AS to_session,
                    fc.class AS from_class,
                    fs.section AS from_section,
                    fp.title AS from_program,
                    fses.session AS from_session,
                    (
                        SELECT COUNT(*)
                        FROM student_fees_master sfm
                        INNER JOIN student_fees_deposite sfd ON sfd.student_fees_master_id = sfm.id
                        WHERE sfm.student_session_id = new_ss.id
                    ) AS fee_deposit_count
                FROM student_session new_ss
                INNER JOIN students s ON s.id = new_ss.student_id
                INNER JOIN student_session old_ss ON old_ss.id = (
                    SELECT MAX(ss2.id)
                    FROM student_session ss2
                    WHERE ss2.student_id = new_ss.student_id
                      AND ss2.is_active = 'no'
                      AND COALESCE(ss2.is_graduated, 0) = 0
                      AND ss2.section_id = new_ss.section_id
                      AND ss2.program_id = new_ss.program_id
                      AND (ss2.class_id <> new_ss.class_id OR ss2.session_id <> new_ss.session_id)
                      AND ss2.id <> new_ss.id
                )
                LEFT JOIN classes tc ON tc.id = new_ss.class_id
                LEFT JOIN sections ts ON ts.id = new_ss.section_id
                LEFT JOIN programs tp ON tp.id = new_ss.program_id
                LEFT JOIN sessions tses ON tses.id = new_ss.session_id
                LEFT JOIN classes fc ON fc.id = old_ss.class_id
                LEFT JOIN sections fs ON fs.id = old_ss.section_id
                LEFT JOIN programs fp ON fp.id = old_ss.program_id
                LEFT JOIN sessions fses ON fses.id = old_ss.session_id
                WHERE new_ss.is_active = 'yes'
                  AND COALESCE(new_ss.is_graduated, 0) = 0
                  AND COALESCE(new_ss.is_alumni, 0) = 0";

        $binds = array();
        if ($session_id > 0) {
            $sql .= " AND new_ss.session_id = ?";
            $binds[] = $session_id;
        }
        if ($section_id > 0) {
            $sql .= " AND new_ss.section_id = ?";
            $binds[] = $section_id;
        }
        if ($program_id > 0) {
            $sql .= " AND new_ss.program_id = ?";
            $binds[] = $program_id;
        }
        if ($class_id > 0) {
            $sql .= " AND new_ss.class_id = ?";
            $binds[] = $class_id;
        }
        if ($search_text !== '') {
            $sql .= " AND (
                s.admission_no LIKE ? OR s.firstname LIKE ? OR s.lastname LIKE ?
                OR CONCAT(IFNULL(s.firstname,''),' ',IFNULL(s.middlename,''),' ',IFNULL(s.lastname,'')) LIKE ?
            )";
            $like = '%' . $search_text . '%';
            $binds[] = $like;
            $binds[] = $like;
            $binds[] = $like;
            $binds[] = $like;
        }

        $sql .= " ORDER BY new_ss.updated_at DESC, new_ss.id DESC LIMIT " . (int) $limit;

        $query = empty($binds) ? $this->db->query($sql) : $this->db->query($sql, $binds);
        return $query->result_array();
    }

    /**
     * Preview graduation rows (is_graduated = 1) with previous study row when available.
     *
     * @param array $filters
     * @return array
     */
    public function getGraduateHistory($filters = array())
    {
        $session_id  = !empty($filters['session_id']) ? (int) $filters['session_id'] : 0;
        $section_id  = !empty($filters['section_id']) ? (int) $filters['section_id'] : 0;
        $program_id  = !empty($filters['program_id']) ? (int) $filters['program_id'] : 0;
        $class_id    = !empty($filters['class_id']) ? (int) $filters['class_id'] : 0;
        $search_text = isset($filters['search_text']) ? trim((string) $filters['search_text']) : '';
        $limit       = !empty($filters['limit']) ? (int) $filters['limit'] : 200;
        if ($limit < 1 || $limit > 500) {
            $limit = 200;
        }

        $sql = "SELECT
                    s.id AS student_id,
                    s.admission_no,
                    s.firstname,
                    s.middlename,
                    s.lastname,
                    s.father_name,
                    s.is_active AS student_is_active,
                    g.id AS to_session_row_id,
                    g.session_id AS to_session_id,
                    g.class_id AS to_class_id,
                    g.section_id AS to_section_id,
                    g.program_id AS to_program_id,
                    g.is_active AS to_is_active,
                    g.is_alumni,
                    g.is_graduated,
                    g.created_at AS to_created_at,
                    g.updated_at AS to_updated_at,
                    prev.id AS from_session_row_id,
                    prev.session_id AS from_session_id,
                    prev.class_id AS from_class_id,
                    prev.section_id AS from_section_id,
                    prev.program_id AS from_program_id,
                    prev.is_active AS from_is_active,
                    gc.class AS to_class,
                    gs.section AS to_section,
                    gp.title AS to_program,
                    gses.session AS to_session,
                    pc.class AS from_class,
                    ps.section AS from_section,
                    pp.title AS from_program,
                    pses.session AS from_session,
                    (
                        SELECT COUNT(*)
                        FROM student_fees_master sfm
                        INNER JOIN student_fees_deposite sfd ON sfd.student_fees_master_id = sfm.id
                        WHERE sfm.student_session_id = g.id
                    ) AS fee_deposit_count
                FROM student_session g
                INNER JOIN students s ON s.id = g.student_id
                LEFT JOIN student_session prev ON prev.id = (
                    SELECT MAX(ss2.id)
                    FROM student_session ss2
                    WHERE ss2.student_id = g.student_id
                      AND ss2.id <> g.id
                      AND COALESCE(ss2.is_graduated, 0) = 0
                )
                LEFT JOIN classes gc ON gc.id = g.class_id
                LEFT JOIN sections gs ON gs.id = g.section_id
                LEFT JOIN programs gp ON gp.id = g.program_id
                LEFT JOIN sessions gses ON gses.id = g.session_id
                LEFT JOIN classes pc ON pc.id = prev.class_id
                LEFT JOIN sections ps ON ps.id = prev.section_id
                LEFT JOIN programs pp ON pp.id = prev.program_id
                LEFT JOIN sessions pses ON pses.id = prev.session_id
                WHERE COALESCE(g.is_graduated, 0) = 1";

        $binds = array();
        if ($session_id > 0) {
            $sql .= " AND g.session_id = ?";
            $binds[] = $session_id;
        }
        if ($section_id > 0) {
            $sql .= " AND g.section_id = ?";
            $binds[] = $section_id;
        }
        if ($program_id > 0) {
            $sql .= " AND g.program_id = ?";
            $binds[] = $program_id;
        }
        if ($class_id > 0) {
            $sql .= " AND g.class_id = ?";
            $binds[] = $class_id;
        }
        if ($search_text !== '') {
            $sql .= " AND (
                s.admission_no LIKE ? OR s.firstname LIKE ? OR s.lastname LIKE ?
                OR CONCAT(IFNULL(s.firstname,''),' ',IFNULL(s.middlename,''),' ',IFNULL(s.lastname,'')) LIKE ?
            )";
            $like = '%' . $search_text . '%';
            $binds[] = $like;
            $binds[] = $like;
            $binds[] = $like;
            $binds[] = $like;
        }

        $sql .= " ORDER BY g.updated_at DESC, g.id DESC LIMIT " . (int) $limit;

        $query = empty($binds) ? $this->db->query($sql) : $this->db->query($sql, $binds);
        return $query->result_array();
    }

    /**
     * Count fee deposits linked to a student_session row.
     *
     * @param int $student_session_id
     * @return int
     */
    public function countFeeDepositsForSessionRow($student_session_id)
    {
        $student_session_id = (int) $student_session_id;
        if ($student_session_id < 1) {
            return 0;
        }
        $sql = "SELECT COUNT(*) AS cnt
                FROM student_fees_master sfm
                INNER JOIN student_fees_deposite sfd ON sfd.student_fees_master_id = sfm.id
                WHERE sfm.student_session_id = ?";
        $row = $this->db->query($sql, array($student_session_id))->row_array();
        return !empty($row['cnt']) ? (int) $row['cnt'] : 0;
    }

    /**
     * Revert a promote: reactivate from-row, deactivate (or delete) to-row, restore students placement.
     *
     * @param int  $student_id
     * @param int  $from_session_row_id
     * @param int  $to_session_row_id
     * @param bool $allow_with_fees if true, deactivate destination even when fee deposits exist
     * @return array{ok:bool,message:string,warnings?:array}
     */
    public function revertPromotion($student_id, $from_session_row_id, $to_session_row_id, $allow_with_fees = false)
    {
        $student_id = (int) $student_id;
        $from_session_row_id = (int) $from_session_row_id;
        $to_session_row_id = (int) $to_session_row_id;
        if ($student_id < 1 || $from_session_row_id < 1 || $to_session_row_id < 1) {
            return array('ok' => false, 'message' => 'Invalid promote revert identifiers.');
        }
        if ($from_session_row_id === $to_session_row_id) {
            return array('ok' => false, 'message' => 'From and to session rows must be different.');
        }

        $from = $this->db->get_where('student_session', array('id' => $from_session_row_id))->row_array();
        $to   = $this->db->get_where('student_session', array('id' => $to_session_row_id))->row_array();
        if (empty($from) || empty($to)) {
            return array('ok' => false, 'message' => 'Promote session rows not found.');
        }
        if ((int) $from['student_id'] !== $student_id || (int) $to['student_id'] !== $student_id) {
            return array('ok' => false, 'message' => 'Session rows do not belong to this student.');
        }
        if (!empty($to['is_graduated']) && (int) $to['is_graduated'] === 1) {
            return array('ok' => false, 'message' => 'Destination row is a graduation record. Use graduate revert.');
        }

        $fee_deposits = $this->countFeeDepositsForSessionRow($to_session_row_id);
        $warnings = array();
        if ($fee_deposits > 0 && !$allow_with_fees) {
            return array(
                'ok' => false,
                'message' => 'Destination session has ' . $fee_deposits . ' fee deposit(s). Confirm allow_with_fees to deactivate it without deleting fee history.',
                'fee_deposit_count' => $fee_deposits,
            );
        }
        if ($fee_deposits > 0) {
            $warnings[] = 'Destination session has fee deposits; row was deactivated (not deleted) to preserve fee history.';
        }

        $this->db->trans_start();

        $this->db->where('id', $from_session_row_id);
        $this->db->update('student_session', array(
            'is_active' => 'yes',
            'is_pass'   => 0,
            'is_fail'   => 0,
            'is_alumni' => 0,
            'updated_at'=> date('Y-m-d'),
        ));

        if ($fee_deposits > 0) {
            $this->db->where('id', $to_session_row_id);
            $this->db->update('student_session', array(
                'is_active' => 'no',
                'updated_at'=> date('Y-m-d'),
            ));
        } else {
            // Soft-deactivate first; delete only when no fee masters exist either.
            $masters_row = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM student_fees_master WHERE student_session_id = ?",
                array($to_session_row_id)
            )->row_array();
            $masters = !empty($masters_row['cnt']) ? (int) $masters_row['cnt'] : 0;
            if ($masters > 0) {
                $this->db->where('id', $to_session_row_id);
                $this->db->update('student_session', array(
                    'is_active' => 'no',
                    'updated_at'=> date('Y-m-d'),
                ));
                $warnings[] = 'Destination had fee assignments; row deactivated instead of deleted.';
            } else {
                $this->db->where('id', $to_session_row_id);
                $this->db->delete('student_session');
            }
        }

        $this->db->where('id', $student_id);
        $this->db->update('students', array(
            'class_id'   => $from['class_id'],
            'section_id' => $from['section_id'],
            'program_id' => $from['program_id'],
        ));

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return array('ok' => false, 'message' => 'Database error while reverting promotion.');
        }

        return array(
            'ok' => true,
            'message' => 'Promotion reverted successfully.',
            'warnings' => $warnings,
            'hemis_note' => 'If HEMIS StudentUpgrade was synced for this promote, correct it manually in HEMIS.',
        );
    }

    /**
     * Revert a graduation: clear graduated/alumni on grad row, reactivate previous study row.
     *
     * @param int  $student_id
     * @param int  $grad_session_row_id
     * @param int  $previous_session_row_id 0 = auto-detect
     * @param bool $allow_with_fees
     * @return array
     */
    public function revertGraduation($student_id, $grad_session_row_id, $previous_session_row_id = 0, $allow_with_fees = false)
    {
        $student_id = (int) $student_id;
        $grad_session_row_id = (int) $grad_session_row_id;
        $previous_session_row_id = (int) $previous_session_row_id;
        if ($student_id < 1 || $grad_session_row_id < 1) {
            return array('ok' => false, 'message' => 'Invalid graduate revert identifiers.');
        }

        $grad = $this->db->get_where('student_session', array('id' => $grad_session_row_id))->row_array();
        if (empty($grad) || (int) $grad['student_id'] !== $student_id) {
            return array('ok' => false, 'message' => 'Graduation session row not found for this student.');
        }
        if (empty($grad['is_graduated']) || (int) $grad['is_graduated'] !== 1) {
            return array('ok' => false, 'message' => 'Selected row is not marked as graduated.');
        }

        if ($previous_session_row_id < 1) {
            $prev = $this->db->query(
                "SELECT id FROM student_session
                 WHERE student_id = ? AND id <> ? AND COALESCE(is_graduated, 0) = 0
                 ORDER BY id DESC LIMIT 1",
                array($student_id, $grad_session_row_id)
            )->row_array();
            $previous_session_row_id = !empty($prev['id']) ? (int) $prev['id'] : 0;
        }

        $previous = null;
        if ($previous_session_row_id > 0) {
            $previous = $this->db->get_where('student_session', array('id' => $previous_session_row_id))->row_array();
            if (empty($previous) || (int) $previous['student_id'] !== $student_id) {
                return array('ok' => false, 'message' => 'Previous study session row is invalid.');
            }
        }

        $fee_deposits = $this->countFeeDepositsForSessionRow($grad_session_row_id);
        $warnings = array();
        if ($fee_deposits > 0 && !$allow_with_fees) {
            return array(
                'ok' => false,
                'message' => 'Graduation session has ' . $fee_deposits . ' fee deposit(s). Confirm allow_with_fees to continue.',
                'fee_deposit_count' => $fee_deposits,
            );
        }
        if ($fee_deposits > 0) {
            $warnings[] = 'Graduation session has fee deposits; flags cleared and row deactivated (not deleted).';
        }

        $this->db->trans_start();

        $this->db->where('id', $grad_session_row_id);
        $this->db->update('student_session', array(
            'is_graduated' => 0,
            'is_alumni'    => 0,
            'is_pass'      => 0,
            'is_fail'      => 0,
            'is_active'    => 'no',
            'updated_at'   => date('Y-m-d'),
        ));

        if ($previous_session_row_id > 0) {
            $this->db->where('id', $previous_session_row_id);
            $this->db->update('student_session', array(
                'is_active'    => 'yes',
                'is_graduated' => 0,
                'is_alumni'    => 0,
                'updated_at'   => date('Y-m-d'),
            ));

            $this->db->where('id', $student_id);
            $this->db->update('students', array(
                'class_id'   => $previous['class_id'],
                'section_id' => $previous['section_id'],
                'program_id' => $previous['program_id'],
            ));
        } else {
            $warnings[] = 'No previous study session found to reactivate. Graduation flags were cleared only.';
        }

        // If grad row has no fee masters and is distinct from previous, delete the empty graduation shell.
        if ($fee_deposits === 0 && $previous_session_row_id > 0 && $previous_session_row_id !== $grad_session_row_id) {
            $masters_row = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM student_fees_master WHERE student_session_id = ?",
                array($grad_session_row_id)
            )->row_array();
            $masters = !empty($masters_row['cnt']) ? (int) $masters_row['cnt'] : 0;
            if ($masters === 0) {
                $this->db->where('id', $grad_session_row_id);
                $this->db->delete('student_session');
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return array('ok' => false, 'message' => 'Database error while reverting graduation.');
        }

        return array(
            'ok' => true,
            'message' => 'Graduation reverted successfully.',
            'warnings' => $warnings,
        );
    }

    public function adddoc($data)
    {
        $this->db->insert('student_doc', $data);
        return $this->db->insert_id();
    }

    public function read_siblings_students($parent_id)
    {
        $this->db->select('*')->from('students');
        $this->db->where('parent_id', $parent_id);
        $this->db->where('students.is_active', 'yes');
        $query = $this->db->get();
        return $query->result();
    }

    public function getMySiblings($parent_id, $student_id)
    {
        $this->db->select('students.*,classes.id as `class_id`,classes.class,sections.id as `section_id`,sections.section,student_session.session_id as `session_id`')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where_not_in('students.id', $student_id);
        $this->db->where('students.parent_id', $parent_id);
        $this->db->where('students.is_active', 'yes');
        $query = $this->db->get();
        return $query->result();
    }

    public function getAttedenceByDateandClass($date)
    {
        $sql   = "SELECT IFNULL(student_attendences.id, 0) as attencence FROM `student_session`left JOIN student_attendences on student_attendences.student_session_id=student_session.id and student_attendences.date=" . $this->db->escape($date) . " and student_attendences.attendence_type_id != 2 where student_session.class_id=7 and student_session.session_id=$this->current_session";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function searchCurrentSessionStudents()
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->order_by('students.firstname', 'asc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function searchLibraryStudent($class_id = null, $section_id = null)
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,
           IFNULL(libarary_members.id,0) as `libarary_member_id`,
           IFNULL(libarary_members.library_card_no,0) as `library_card_no`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename, students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('libarary_members', 'libarary_members.member_id = students.id and libarary_members.member_type = "student"', 'left');

        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
        $this->db->order_by('students.id');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function searchNameLike($searchterm)
    {
        $userdata            = $this->customlib->getUserData();
        $class_section_array = $this->customlib->get_myClassSection();
        $this->db->select('classes.id AS `class_id`,students.id, students.parent_id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,   students.mobileno,students.email,students.state,students.city,students.pincode ,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code ,students.father_name,students.guardian_name, students.guardian_relation,students.guardian_email,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,students.app_key,students.parent_app_key,student_session.session_id')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->db->or_where($query_string);
                }
            }
            $this->db->group_end();
        }
        $this->db->group_start();
        $this->db->like('students.firstname', $searchterm);
        $this->db->or_like('students.lastname', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->group_end();
        $this->db->order_by('students.id');
        $this->db->limit(15);
        $query  = $this->db->get();
        $result = $query->result_array();
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
        return $result;
    }

    public function searchGuardianNameLike($searchterm)
    {
        $this->db->select('classes.id AS `class_id`,students.id,`users`.`id` as `guardian_user_id`,students.parent_id,classes.class,sections.id AS `section_id`,sections.section,students.admission_no, students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,  students.mobileno,students.email,students.state , students.city , students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.guardian_email,students.rte,student_session.session_id,students.app_key,students.parent_app_key')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');
        $this->db->join('users', 'users.id = students.parent_id');
        $this->db->where('users.role', 'parent');
        $this->db->group_by('students.parent_id');
        $this->db->group_start();
        $this->db->like('students.guardian_name', $searchterm);
        $this->db->group_end();
        $this->db->order_by('students.id');
        $this->db->limit(15);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function searchByClassSectionWithSession($class_id = null, $section_id = null, $session_id = null, $program_id = null, $for_promotion = false)
    {
        $target_session = $session_id ? $session_id : $this->current_session;
        
        // Add debug logging
        log_message('debug', 'searchByClassSectionWithSession called with: class_id=' . $class_id . ', section_id=' . $section_id . ', session_id=' . $session_id . ', program_id=' . $program_id . ', for_promotion=' . ($for_promotion ? 'true' : 'false'));
        log_message('debug', 'Target session: ' . $target_session . ', Current session: ' . $this->current_session);
    
        $this->db->select('
            classes.id AS class_id,
            student_session.id as student_session_id,
            students.id,
            classes.class,
            sections.id AS section_id,
            sections.section,
            students.admission_no,
            students.roll_no,
            students.admission_date,
            students.firstname,
            students.middlename,
            students.lastname,
            students.image,
            students.mobileno,
            students.email,
            students.state,
            students.city,
            students.pincode,
            students.religion,
            students.dob,
            students.current_address,
            students.permanent_address,
            IFNULL(students.category_id, 0) as category_id,
            IFNULL(categories.category, "") as category,
            students.adhar_no,
            students.samagra_id,
            students.bank_account_no,
            students.bank_name,
            students.ifsc_code,
            students.guardian_name,
            students.guardian_relation,
            students.guardian_phone,
            students.guardian_address,
            students.is_active,
            students.created_at,
            students.updated_at,
            students.father_name,
            students.rte,
            students.gender,
            student_session.is_active as session_is_active
        ');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $target_session);
    
        // Different logic based on context
        if ($for_promotion) {
            // For promotion, show all students (regardless of is_active status)
            // Only exclude students who have left or graduated
            $this->db->where('COALESCE(student_session.is_leave, 0)', 0);
            $this->db->where('COALESCE(student_session.is_graduated, 0)', 0);
            log_message('debug', 'Applying promotion filter: excluding only is_leave=1 and is_graduated=1');
        } else {
            // For all cases, show ALL students (both active and inactive)
            // Only exclude students who actually left
            $this->db->where('COALESCE(student_session.is_leave, 0)', 0);
            log_message('debug', 'Applying filter: only excluding is_leave = 1');
        }
    
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
        if ($program_id != null) {
            $this->db->where('student_session.program_id', $program_id);
        }
    
        $this->db->order_by('students.id');
        
        // Log the final query before execution
        $compiled_query = $this->db->get_compiled_select();
        log_message('debug', 'Final compiled query: ' . $compiled_query);
        
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'DB error in searchByClassSectionWithSession: ' . $this->db->last_query());
            log_message('error', 'DB error: ' . print_r($this->db->error(), true));
            return [];
        }
        
        $result = $query->result_array();
        log_message('debug', 'searchByClassSectionWithSession returned ' . count($result) . ' records');
        
        return $result;
    }


   
    public function searchNonPromotedStudents($class_id = null, $section_id = null, $promoted_session_id = null, $promoted_class_id = null, $promoted_section_id = null, $program_id = null, $promoted_program_id = null, $ignore_is_active = false)
    {
        // Load the debug helper
        $this->load->helper('debug');
       
        // Log the parameters
        debug_log("searchNonPromotedStudents parameters", [
            'class_id' => $class_id,
            'section_id' => $section_id,
            'promoted_session_id' => $promoted_session_id,
            'promoted_class_id' => $promoted_class_id,
            'promoted_section_id' => $promoted_section_id,
            'program_id' => $program_id,
            'promoted_program_id' => $promoted_program_id,
            'ignore_is_active' => $ignore_is_active
        ]);
       
        // Check if all parameters are provided
        if (empty($class_id) || empty($section_id) || empty($promoted_session_id) ||
            empty($promoted_class_id) || empty($promoted_section_id) ||
            empty($program_id) || empty($promoted_program_id)) {
            debug_log("Missing parameters for searchNonPromotedStudents", null, 'error');
            return array();
        }
       
        // Get students who are in the current session/class/section/program
        // but have NOT been promoted to the next session yet
        $sql = "SELECT 
                students.id,
                classes.id AS class_id,
                student_session.id as student_session_id,
                classes.class,
                sections.id AS section_id,
                sections.section,
                programs.id AS program_id,
                programs.title AS program,
                students.admission_no,
                students.roll_no,
                students.admission_date,
                students.firstname,
                students.middlename,
                students.lastname,
                students.image,
                students.mobileno,
                students.email,
                students.state,
                students.city,
                students.pincode,
                students.religion,
                students.dob,
                students.current_address,
                students.permanent_address,
                IFNULL(students.category_id, 0) as category_id,
                IFNULL(categories.category, '') as category,
                students.adhar_no,
                students.samagra_id,
                students.bank_account_no,
                students.bank_name,
                students.ifsc_code,
                students.guardian_name,
                students.guardian_relation,
                students.guardian_phone,
                students.guardian_address,
                students.is_active,
                students.created_at,
                students.updated_at,
                students.father_name,
                students.rte,
                students.gender,
                student_session.is_active as session_is_active
            FROM students
            JOIN student_session ON student_session.student_id = students.id
            JOIN classes ON student_session.class_id = classes.id
            JOIN sections ON sections.id = student_session.section_id
            JOIN programs ON programs.id = student_session.program_id
            LEFT JOIN categories ON students.category_id = categories.id
            WHERE student_session.session_id = ?
            AND student_session.class_id = ?
            AND student_session.section_id = ?
            AND student_session.program_id = ?
            AND COALESCE(student_session.is_leave, 0) = 0
            AND COALESCE(student_session.is_graduated, 0) = 0
            AND NOT EXISTS (
                SELECT 1 FROM student_session ps
                WHERE ps.student_id = students.id
                AND ps.session_id = ?
                AND ps.class_id = ?
                AND ps.section_id = ?
                AND ps.program_id = ?
            )
            ORDER BY students.id";
    
        $query = $this->db->query($sql, [
            $this->current_session, $class_id, $section_id, $program_id,
            $promoted_session_id, $promoted_class_id, $promoted_section_id, $promoted_program_id
        ]);
        
        if (!$query) {
            debug_log("Database error in searchNonPromotedStudents: " . $this->db->last_query(), null, 'error');
            debug_log("DB error: " . print_r($this->db->error(), true), null, 'error');
            return array();
        }
        
        $result = $query->result_array();
       
        debug_log("searchNonPromotedStudents found " . count($result) . " students");
        debug_log("Query executed: " . $this->db->last_query());
       
        return $result;
    }

    public function isStudentPromoted($student_id, $next_session_id, $next_class_id, $next_section_id, $next_program_id)
    {
        $this->db->where('student_id', $student_id);
        $this->db->where('session_id', $next_session_id);
        $this->db->where('class_id', $next_class_id);
        $this->db->where('section_id', $next_section_id);
        $this->db->where('program_id', $next_program_id);
        $query = $this->db->get('student_session');
        return $query->num_rows() > 0;
    }


    
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('students', $data);
        return $this->db->affected_rows();
    }

   

   
    public function getSemesterModeStudents($class_id = null, $section_id = null, $program_id = null, $session_id = null)
    {
        // Get students for semester mode promotion
        // This function gets students who are in the current session and can be promoted
        $target_session = $session_id ? $session_id : $this->current_session;
        
        log_message('debug', 'getSemesterModeStudents called with: class_id=' . $class_id . ', section_id=' . $section_id . ', program_id=' . $program_id . ', session_id=' . $session_id);
        
        $this->db->select('
            classes.id AS class_id,
            student_session.id as student_session_id,
            students.id,
            classes.class,
            sections.id AS section_id,
            sections.section,
            programs.id AS program_id,
            programs.title AS program,
            students.admission_no,
            students.roll_no,
            students.admission_date,
            students.firstname,
            students.middlename,
            students.lastname,
            students.image,
            students.mobileno,
            students.email,
            students.state,
            students.city,
            students.pincode,
            students.religion,
            students.dob,
            students.current_address,
            students.permanent_address,
            IFNULL(students.category_id, 0) as category_id,
            IFNULL(categories.category, "") as category,
            students.adhar_no,
            students.samagra_id,
            students.bank_account_no,
            students.bank_name,
            students.ifsc_code,
            students.guardian_name,
            students.guardian_relation,
            students.guardian_phone,
            students.guardian_address,
            students.is_active,
            students.created_at,
            students.updated_at,
            students.father_name,
            students.rte,
            students.gender,
            student_session.is_active as session_is_active
        ');
        
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('programs', 'programs.id = student_session.program_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        
        $this->db->where('student_session.session_id', $target_session);
        $this->db->where('student_session.is_active', 'yes');
        
        // Only exclude students who have left or graduated
        $this->db->where('COALESCE(student_session.is_leave, 0)', 0);
        $this->db->where('COALESCE(student_session.is_graduated, 0)', 0);
        
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
        if ($program_id != null) {
            $this->db->where('student_session.program_id', $program_id);
        }
        
        $this->db->order_by('students.id');
        
        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'DB error in getSemesterModeStudents: ' . $this->db->last_query());
            log_message('error', 'DB error: ' . print_r($this->db->error(), true));
            return [];
        }
        
        $result = $query->result_array();
        log_message('debug', 'getSemesterModeStudents returned ' . count($result) . ' records');
        
        return $result;
    }

    public function getStudentsForGraduation($class_id = null, $section_id = null, $program_id = null)
    {
        // Load the debug helper
        $this->load->helper('debug');
       
        // Log the parameters
        debug_log("getStudentsForGraduation parameters", [
            'class_id' => $class_id,
            'section_id' => $section_id,
            'program_id' => $program_id
        ]);
       
        // Check if all parameters are provided
        if (empty($class_id) || empty($section_id) || empty($program_id)) {
            debug_log("Missing parameters for getStudentsForGraduation", null, 'error');
            return array();
        }
       
        // First, let's check if we have any students in the current class/section/program
        $check_sql = "SELECT COUNT(*) as count FROM student_session
                      WHERE session_id = ?
                      AND class_id = ?
                      AND section_id = ?
                      AND program_id = ?
                      AND is_active = 'yes'";
                     
        $query = $this->db->query($check_sql, [$this->current_session, $class_id, $section_id, $program_id]);
        $count_result = $query->row_array();
       
        debug_log("Students in current selection for graduation: " . $count_result['count']);
       
        if ($count_result['count'] == 0) {
            debug_log("No students found in the selected class/section/program for graduation", null, 'error');
            return array();
        }
       
        // Now get the students eligible for graduation
        $sql = "SELECT
                students.id,
                classes.id AS class_id,
                student_session.id as student_session_id,
                classes.class,
                sections.id AS section_id,
                sections.section,
                programs.id AS program_id,
                programs.title AS program,
                students.admission_no,
                students.roll_no,
                students.admission_date,
                students.firstname,
                students.middlename,
                students.lastname,
                students.image,
                students.mobileno,
                students.email,
                students.state,
                students.city,
                students.pincode,
                students.religion,
                students.dob,
                students.current_address,
                students.permanent_address,
                IFNULL(students.category_id, 0) as category_id,
                IFNULL(categories.category, '') as category,
                students.adhar_no,
                students.samagra_id,
                students.bank_account_no,
                students.bank_name,
                students.ifsc_code,
                students.guardian_name,
                students.guardian_relation,
                students.guardian_phone,
                students.guardian_address,
                students.is_active,
                students.created_at,
                students.updated_at,
                students.father_name,
                students.rte,
                students.gender
            FROM students
            JOIN student_session ON student_session.student_id = students.id
            JOIN classes ON student_session.class_id = classes.id
            JOIN sections ON sections.id = student_session.section_id
            JOIN programs ON programs.id = student_session.program_id
            LEFT JOIN categories ON students.category_id = categories.id
            WHERE student_session.session_id = ?
            AND student_session.class_id = ?
            AND student_session.section_id = ?
            AND student_session.program_id = ?
            AND student_session.is_active = 'yes'
            AND COALESCE(student_session.is_leave, 0) = 0
            AND COALESCE(student_session.is_graduated, 0) = 0
            ORDER BY students.id";
       
        $query = $this->db->query($sql, [
            $this->current_session, $class_id, $section_id, $program_id
        ]);
       
        $result = $query->result_array();
       
        debug_log("getStudentsForGraduation found " . count($result) . " students");
       
        return $result;
    }



    public function getPreviousSessionStudent($previous_session_id, $class_id, $section_id)
    {
        $sql   = "SELECT student_session.student_id as student_id, student_session.id as current_student_session_id, student_session.class_id as current_session_class_id ,previous_session.id as previous_student_session_id,students.firstname,students.middlename,students.lastname,students.admission_no,students.roll_no,students.father_name,students.admission_date FROM `student_session` left JOIN (SELECT * FROM `student_session` where session_id=$previous_session_id) as previous_session on student_session.student_id=previous_session.student_id INNER join students on students.id =student_session.student_id where student_session.session_id=$this->current_session and student_session.class_id=$class_id and student_session.section_id=$section_id and student_session.is_active='yes' ORDER BY students.firstname ASC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function studentGuardianDetails($carray)
    {
        $userdata = $this->customlib->getUserData();
        $this->db->SELECT("students.admission_no,students.firstname,students.middlename,students.mobileno,students.father_phone,students.mother_phone,students.lastname,students.father_name,students.mother_name,students.guardian_name,students.guardian_relation,students.guardian_phone,students.id,classes.class,sections.section");
        $this->db->join("student_session", "student_session.student_id = students.id");
        $this->db->join("classes", "student_session.class_id = classes.id");
        $this->db->join("sections", "student_session.section_id = sections.id");
        $this->db->where("student_session.is_active", "yes");
        $this->db->where('student_session.session_id', $this->current_session);
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if (!empty($carray)) {
                $this->db->where_in("student_session.class_id", $carray);
            } else {
                $this->db->where_in("student_session.class_id", "");
            }
        }
        $query = $this->db->get("students");
        return $query->result_array();
    }

    public function searchGuardianDetails($class_id, $section_id)
    {
        $this->db->SELECT("students.admission_no,students.firstname,students.middlename,students.lastname,students.mobileno,students.father_phone,students.mother_phone,students.father_name,students.mother_name,students.guardian_name,students.guardian_relation,students.guardian_phone,students.id,classes.class,sections.section");
        $this->db->join("student_session", "student_session.student_id = students.id");
        $this->db->join("classes", "student_session.class_id = classes.id");
        $this->db->join("sections", "student_session.section_id = sections.id");
        $this->db->where("student_session.is_active", "yes");
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where(array('student_session.class_id' => $class_id, 'student_session.section_id' => $section_id));
        $query = $this->db->get("students");
        return $query->result_array();
    }

    public function studentAdmissionDetails($carray = null)
    {
        $userdata = $this->customlib->getUserData();
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if (!empty($carray)) {
                $this->db->where_in("student_session.class_id", $carray);
                $sections = $this->teacher_model->get_teacherrestricted_modeallsections($staff_id);
                $sections_id = array();
                foreach ($sections as $key => $value) {
                    $sections_id[] = $value['section_id'];
                }
                $this->db->where_in("student_session.section_id", $sections_id);
            } else {
                // No allowed classes, return empty result to avoid SQL error
                return array();
            }
        }
        $this->db->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,GROUP_CONCAT(classes.class,"(",sections.section,")") as class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,   students.mobileno, students.email ,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code ,students.father_name,students.guardian_name,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at,students.updated_at,students.gender,students.rte,student_session.session_id,' . $field_variable)->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');
        $this->db->where('student_session.is_alumni', '1');
        $this->db->group_start();
        $this->db->like('students.admission_no', $searchterm);
        $this->db->group_end();
        $this->db->group_by('students.id');
        $this->db->order_by('students.id');
        $query = $this->db->get();
        if ($query === false) {
            return array();
        }
        return $query->result_array();
    }

    public function studentSessionDetails($id)
    {
        $query = $this->db->query("SELECT min(sessions.session) as start , max(sessions.session) as end, min(classes.class) as startclass, max(classes.class) as endclass from sessions join student_session on (sessions.id = student_session.session_id) join classes on (classes.id = student_session.class_id) where student_session.student_id = " . $id);
        return $query->row_array();
    }


    public function admissionYear()
    {
        $query = $this->db->SELECT("distinct(year(admission_date)) as year")->where_not_in('admission_date', array('0000-00-00', '1970-01-01'))->get("students");
        return $query->result_array();
    }

    public function getStudentSession($id)
    {
        $query = $this->db->query("SELECT  max(sessions.id) as student_session_id, max(sessions.session) as session from sessions join student_session on (sessions.id = student_session.session_id)  where student_session.student_id = " . $id);
        return $query->row_array();
    }

    public function valid_student_roll()
    {
        $roll_no    = $this->input->post('roll_no');
        $student_id = $this->input->post('studentid');
        $class      = $this->input->post('class_id');

        if ($roll_no != "") {

            if (!isset($student_id)) {
                $student_id = 0;
            }

            if ($this->check_rollno_exists($roll_no, $student_id, $class)) {
                $this->form_validation->set_message('check_exists', 'Roll Number should be unique at Class level');
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function check_rollno_exists($roll_no, $student_id, $class)
    {
        if ($student_id != 0) {
            $data  = array('students.id != ' => $student_id, 'student_session.class_id' => $class, 'students.roll_no' => $roll_no);
            $query = $this->db->where($data)->join("student_session", "students.id = student_session.student_id")->get('students');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->db->where(array('class_id' => $class, 'roll_no' => $roll_no));
            $query = $this->db->join("student_session", "students.id = student_session.student_id")->get('students');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function gethouselist()
    {
        $query = $this->db->where("is_active", "yes")->get("school_houses");
        return $query->result_array();
    }

    public function disableStudent($id, $data)
    {
        $this->db->where("id", $id)->update("students", $data);
    }

    /**
     * True when students.is_active = 'no' (admin disabled the student account).
     *
     * @param int|array|object $student_id_or_row students.id or row with is_active
     * @return bool
     */
    public function isStudentAccountDisabled($student_id_or_row)
    {
        if (is_array($student_id_or_row)) {
            return isset($student_id_or_row['is_active'])
                && strtolower(trim((string) $student_id_or_row['is_active'])) === 'no';
        }
        if (is_object($student_id_or_row)) {
            return isset($student_id_or_row->is_active)
                && strtolower(trim((string) $student_id_or_row->is_active)) === 'no';
        }
        $row = $this->db->select('is_active')
            ->from('students')
            ->where('id', (int) $student_id_or_row)
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($row)
            && strtolower(trim((string) $row['is_active'])) === 'no';
    }

    /**
     * True when the student linked to a student_session row is disabled.
     *
     * @param int $student_session_id
     * @return bool
     */
    public function isStudentSessionAccountDisabled($student_session_id)
    {
        $row = $this->db->select('students.is_active')
            ->from('student_session')
            ->join('students', 'students.id = student_session.student_id')
            ->where('student_session.id', (int) $student_session_id)
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($row)
            && strtolower(trim((string) $row['is_active'])) === 'no';
    }

    public function getdisableStudent()
    {
        $class_section_array = $this->customlib->get_myClassSection();
        $this->db->select('classes.id AS `class_id`,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,    students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code ,students.father_name,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id,dis_reason,dis_note')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'no');
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->db->or_where($query_string);
                }
            }
            $this->db->group_end();
        }
        $this->db->order_by('students.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function disablestudentByClassSection($class, $section)
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,  students.mobileno,students.email,students.state,students.city,students.pincode ,students.religion,students.dob,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,dis_reason,dis_note')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', "no");
        if ($class != null) {
            $this->db->where('student_session.class_id', $class);
        }
        if ($section != null) {
            $this->db->where('student_session.section_id', $section);
        }
        $this->db->order_by('students.id');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function disablestudentFullText($searchterm)
    {
        $userdata            = $this->customlib->getUserData();
        $class_section_array = $this->customlib->get_myClassSection();
        $this->db->select('classes.id AS `class_id`,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename,students.lastname,students.image,  students.mobileno,students.email,students.state,students.city , students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id,dis_reason,dis_note')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->join('school_houses', 'students.school_house_id = school_houses.id', 'left');
        $this->db->where('students.is_active', 'no');
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                $query_string = "";
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $query_string = "( student_session.class_id=" . $class_sectionkey . " and student_session.section_id=" . $class_sectionvaluevalue . " )";
                    $this->db->or_where($query_string);
                }
            }
            $this->db->group_end();
        }
        $this->db->group_start();
        $this->db->like('students.firstname', $searchterm);
        $this->db->or_like('students.middlename', $searchterm);
        $this->db->or_like('students.lastname', $searchterm);
        $this->db->or_like('school_houses.house_name', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->or_like('students.adhar_no', $searchterm);
        $this->db->or_like('students.samagra_id', $searchterm);
        $this->db->or_like('students.roll_no', $searchterm);
        $this->db->or_like('students.admission_no', $searchterm);
        $this->db->or_like('students.mobileno', $searchterm);
        $this->db->or_like('students.email', $searchterm);
        $this->db->or_like('students.religion', $searchterm);
        $this->db->or_like('students.cast', $searchterm);
        $this->db->or_like('students.gender', $searchterm);
        $this->db->or_like('students.current_address', $searchterm);
        $this->db->or_like('students.permanent_address', $searchterm);
        $this->db->or_like('students.blood_group', $searchterm);
        $this->db->or_like('students.bank_name', $searchterm);
        $this->db->or_like('students.ifsc_code', $searchterm);
        $this->db->or_like('students.father_name', $searchterm);
        $this->db->or_like('students.father_phone', $searchterm);
        $this->db->or_like('students.father_occupation', $searchterm);
        $this->db->or_like('students.mother_name', $searchterm);
        $this->db->or_like('students.mother_phone', $searchterm);
        $this->db->or_like('students.mother_occupation', $searchterm);
        $this->db->or_like('students.guardian_name', $searchterm);
        $this->db->or_like('students.guardian_relation', $searchterm);
        $this->db->or_like('students.guardian_phone', $searchterm);
        $this->db->or_like('students.guardian_occupation', $searchterm);
        $this->db->or_like('students.guardian_address', $searchterm);
        $this->db->or_like('students.guardian_email', $searchterm);
        $this->db->or_like('students.previous_school', $searchterm);
        $this->db->or_like('students.note', $searchterm);
        $this->db->group_end();

        $this->db->order_by('students.id');
        $query  = $this->db->get();
        $result = $query->result_array();
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $result = array();
        }
        return $result;
    }

    public function getClassSection($id)
    {
        $query = $this->db->SELECT("*")->join("sections", "class_sections.section_id = sections.id")->where("class_sections.class_id", $id)->get("class_sections");
        return $query->result_array();
    }

    public function getStudentClassSection($id, $sessionid)
    {
        $query = $this->db->SELECT("students.firstname,students.middlename,students.id,students.lastname,students.image,student_session.section_id, student_session.id as student_session_id")->join("student_session", "students.id = student_session.student_id")->where("student_session.class_id", $id)->where("student_session.session_id", $sessionid)->where("students.is_active", "yes")->get("students");

        return $query->result_array();
    }

    public function get_studentsession($student_session_id)
    {
        $query = $this->db->select('sessions.session')->join("student_session", "sessions.id = student_session.session_id")->where('student_session.id', $student_session_id)->get("sessions");
        return $query->row_array();
    }

    public function check_adm_exists($admission_no)
    {
        $this->db->where(array('admission_no' => $admission_no));
        $query = $this->db->get('students');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * CSV import: whether admission_no is already used on any student row.
     *
     * @param string $admission_no
     * @return bool
     */
    public function import_admission_no_exists($admission_no)
    {
        $admission_no = trim((string) $admission_no);
        if ($admission_no === '') {
            return false;
        }

        return $this->check_adm_exists($admission_no);
    }

    /**
     * CSV import: whether student email is already used (ignore empty).
     *
     * @param string $email
     * @return bool
     */
    public function import_student_email_exists($email)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return false;
        }

        return $this->check_data_exists($email, 0);
    }

    /**
     * Students in current session for bulk Symbol / Registration / Roll editor.
     *
     * @param int $section_id
     * @param int $program_id
     * @param int $class_id
     * @return array
     */
    public function get_students_for_bulk_ids($section_id, $program_id, $class_id)
    {
        $section_id = (int) $section_id;
        $program_id = (int) $program_id;
        $class_id   = (int) $class_id;

        // Must run before any query builder calls — get_myClassSection() loads other models
        // that share $this->db and would corrupt a partially built query.
        $class_section_array = $this->customlib->get_myClassSection();

        $this->db->reset_query();

        $select = 'students.id, students.admission_no, students.firstname, students.middlename, students.lastname, students.roll_no, classes.class, sections.section';
        if ($this->db->field_exists('symbol_no', 'students')) {
            $select .= ', students.symbol_no';
        }
        if ($this->db->field_exists('registration_no', 'students')) {
            $select .= ', students.registration_no';
        }

        $this->db->select($select);
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('class_sections', 'class_sections.class_id = student_session.class_id AND class_sections.section_id = student_session.section_id AND class_sections.program_id = student_session.program_id', 'left');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        if (!empty($class_section_array)) {
            $this->db->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                foreach ($class_sectionvalue as $class_sectionvaluevalue) {
                    $this->db->or_group_start();
                    $this->db->where('student_session.class_id', $class_sectionkey);
                    $this->db->where('student_session.section_id', $class_sectionvaluevalue);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();
        }

        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        if ($section_id > 0) {
            $this->db->where('student_session.section_id', $section_id);
        }
        if ($class_id > 0) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($program_id > 0) {
            $this->db->where('student_session.program_id', $program_id);
        }
        $this->db->order_by('students.admission_no', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Non-empty symbol / registration / roll values already stored for a cohort.
     *
     * @param int $section_id
     * @param int $program_id
     * @param int $class_id
     * @return array
     */
    public function get_bulk_ids_used_values($section_id, $program_id, $class_id)
    {
        $rows = $this->get_students_for_bulk_ids($section_id, $program_id, $class_id);
        $used = array(
            'symbol_no'       => array(),
            'registration_no' => array(),
            'roll_no'         => array(),
        );
        foreach ($rows as $row) {
            foreach (array_keys($used) as $field) {
                if (!isset($row[$field])) {
                    continue;
                }
                $val = trim((string) $row[$field]);
                if ($val !== '') {
                    $used[$field][] = $val;
                }
            }
        }
        foreach ($used as $field => $values) {
            $used[$field] = array_values(array_unique($values));
        }

        return $used;
    }

    /**
     * Bulk update symbol_no, registration_no, roll_no from student search modal.
     *
     * @param array $items [{id, symbol_no, registration_no, roll_no}, ...]
     * @return array {updated:int, errors:array}
     */
    public function bulk_update_symbol_reg_roll(array $items)
    {
        $updated = 0;
        $errors  = array();
        $has_symbol = $this->db->field_exists('symbol_no', 'students');
        $has_reg    = $this->db->field_exists('registration_no', 'students');
        $has_roll   = $this->db->field_exists('roll_no', 'students');

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $id = isset($item['id']) ? (int) $item['id'] : 0;
            if ($id < 1) {
                continue;
            }

            $symbol = isset($item['symbol_no']) ? trim((string) $item['symbol_no']) : '';
            $reg    = isset($item['registration_no']) ? trim((string) $item['registration_no']) : '';
            $roll   = isset($item['roll_no']) ? trim((string) $item['roll_no']) : '';
            $rowErr = array();

            if ($has_symbol && $symbol !== '' && $this->_bulk_student_field_taken('symbol_no', $symbol, $id)) {
                $rowErr['symbol_no'] = 'Already used';
            }
            if ($has_reg && $reg !== '' && $this->_bulk_student_field_taken('registration_no', $reg, $id)) {
                $rowErr['registration_no'] = 'Already used';
            }
            if ($has_roll && $roll !== '') {
                $class_id = $this->_student_active_class_id($id);
                if ($class_id && $this->check_rollno_exists($roll, $id, $class_id)) {
                    $rowErr['roll_no'] = 'Roll number already used in this class';
                }
            }

            if (!empty($rowErr)) {
                $errors[$id] = $rowErr;
                continue;
            }

            $upd = array();
            if ($has_symbol) {
                $upd['symbol_no'] = $symbol;
            }
            if ($has_reg) {
                $upd['registration_no'] = $reg;
            }
            if ($has_roll) {
                $upd['roll_no'] = $roll;
            }
            if (empty($upd)) {
                continue;
            }

            $this->db->where('id', $id)->update('students', $upd);
            $updated++;
        }

        return array(
            'updated' => $updated,
            'errors'  => $errors,
        );
    }

    /**
     * @param string $column
     * @param string $value
     * @param int    $exclude_id
     * @return bool
     */
    private function _bulk_student_field_taken($column, $value, $exclude_id)
    {
        if ($value === '' || !$this->db->field_exists($column, 'students')) {
            return false;
        }
        $this->db->where($column, $value);
        $this->db->where('id !=', (int) $exclude_id);

        return $this->db->count_all_results('students') > 0;
    }

    /**
     * @param int $student_id
     * @return int|null
     */
    private function _student_active_class_id($student_id)
    {
        $q = $this->db->select('class_id')
            ->from('student_session')
            ->where('student_id', (int) $student_id)
            ->where('session_id', $this->current_session)
            ->where('is_active', 'yes')
            ->limit(1)
            ->get();
        if ($q->num_rows() === 0) {
            return null;
        }

        return (int) $q->row()->class_id;
    }

    public function lastRecord()
    {
        $last_row = $this->db->select('*')->order_by('id', "desc")->limit(1)->get('students')->row();
        return $last_row;
    }

    public function currentClassSectionById($studentid, $schoolsessionId)
    {
        return $this->db->select('class_id,section_id')->from('student_session')->where('session_id', $schoolsessionId)->where('student_id', $studentid)->get()->row_array();
    }

    public function reportClassSection($class_id = null, $section_id = null)
    {
        $i = 1;

        $custom_fields   = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);

        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,  students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code, students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,' . $field_variable)->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', "yes");
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->group_by('students.id');
        $this->db->order_by('students.admission_no', 'asc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getAllClassSection($class_id = null, $section_id = null)
    {
        $where = array();
        if ($class_id != null) {
            $where['class_id'] = $class_id;
        }

        if ($section_id != null) {
            $where['section_id'] = $section_id;
        }

        return $this->db->select('*')->from('class_sections')->join('classes', 'class_sections.class_id=classes.id', 'inner')->join('sections', 'class_sections.section_id=sections.id', 'inner')->where($where)->get()->result_array();
    }

    public function student_profile($condition1, $condition2)
    {
        $this->db->select('student_session.transport_fees,student_session.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,students.hostel_room_id,student_session.id as `student_session_id`,student_session.fees_discount,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,  students.mobileno, students.email ,students.state,students.city,students.pincode,students.note, students.religion, students.cast,school_houses.house_name,students.dob,students.current_address,students.previous_school,
            students.guardian_is,students.parent_id,
            students.permanent_address,students.category_id,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code,students.guardian_name,students.father_pic ,students.height ,students.weight,students.measurement_date, students.mother_pic,students.guardian_pic , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.father_phone,students.blood_group,students.school_house_id,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_occupation,students.gender,students.guardian_is,students.rte,students.guardian_email, users.username,users.password,students.dis_reason,students.dis_note,category')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = students.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left');
        $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = students.school_house_id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->join('categories', 'categories.id = students.category_id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('users.role', 'student');
        $this->db->where('student_session.is_active', 'yes');
        if ($condition1 != '') {
            $this->db->where($condition1);
        }
        if ($condition2 != '') {
            $this->db->where($condition2);
        }

        $this->db->order_by('students.id', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function bulkdelete($students)
    {
        if (!empty($students)) {

            $this->db->trans_start();
            $student_comma_seprate = implode(', ', $students);
            //delete from students
            $this->db->where_in('id', $students);
            $this->db->delete('students');

            //delete from users
            $this->db->where_in('user_id', $students);
            $this->db->where_in('role', 'student');
            $this->db->delete('users');
            //delete from custom_field_value

            $sql = "DELETE FROM custom_field_values WHERE id IN (select * from (SELECT t2.id as `id` FROM `custom_fields` INNER JOIN custom_field_values as t2 on t2.custom_field_id=custom_fields.id WHERE custom_fields.belong_to='students' and t2.belong_table_id IN (" . implode(', ', $students) . ")) as m2)";

            $query = $this->db->query($sql);

            $sql_parent = "DELETE from users WHERE id in (SELECT id from (SELECT users.*,students.id as `student_id` FROM `users` LEFT JOIN students on users.id= students.parent_id WHERE role ='parent') as a WHERE a.student_id IS NULL)";
            $query      = $this->db->query($sql_parent);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function valid_student_admission_no()
    {
        $admission_no = $this->input->post('admission_no');
        $student_id   = $this->input->post('studentid');

        if ($admission_no != "") {

            if (!isset($student_id)) {
                $student_id = 0;
            }

            if ($this->check_admission_no_exists($admission_no, $student_id)) {
                $this->form_validation->set_message('check_admission_no_exists', 'Admission No Exists');
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function check_admission_no_exists($admission_no, $student_id)
    {
        if ($student_id != 0) {
            $data  = array('students.id != ' => $student_id, 'students.admission_no' => $admission_no);
            $query = $this->db->where($data)->join("student_session", "students.id = student_session.student_id")->get('students');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->db->where('admission_no', $admission_no);
            $query = $this->db->get('students');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function search_alumniStudentReport($class_id = null, $section_id = null, $session_id = null)
    {
        $this->db->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,GROUP_CONCAT(classes.class,"(",sections.section,")") as class,sections.id AS `section_id`,sections.section,students.id,students.admission_no, students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,  students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name,students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id')->from('alumni_students');
    
        $this->db->join('students', 'students.id = alumni_students.student_id');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.is_alumni', 1);
        $this->db->where('student_session.is_active', "yes");
        
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
        if ($session_id != null) {
            $this->db->where('student_session.session_id', $session_id);
        }
        
        $this->db->group_by('students.id');
        $this->db->order_by('students.admission_no', 'asc');
        
        // Add error handling
        $query = $this->db->get();
        
        // Check if query was successful
        if ($query === false) {
            // Get the database error
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return empty array or handle error appropriately
        }
        
        return $query->result_array();
    }

    public function search_alumniStudentbyAdmissionNoReport($searchterm, $carray)
    {
        $userdata = $this->customlib->getUserData();
        $staff_id = $userdata['id'];

        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if (!empty($carray)) {

                $this->db->where_in("student_session.class_id", $carray);
                $sections = $this->teacher_model->get_teacherrestricted_modeallsections($staff_id);
                foreach ($sections as $key => $value) {
                    $sections_id[] = $value['section_id'];
                }
                $this->db->where_in("student_session.section_id", $sections_id);
            } else {
                // No allowed classes, return empty result to avoid SQL error
                return array();
            }
        }
        $this->db->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,GROUP_CONCAT(classes.class,"(",sections.section,")") as class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,   students.mobileno,students.email ,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,      students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code ,students.father_name,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.gender,students.rte,student_session.session_id')->from('alumni_students');
        $this->db->join('students', 'students.id = alumni_students.student_id');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.is_alumni', '1');
        $this->db->group_start();
        $this->db->like('students.admission_no', $searchterm);
        $this->db->group_end();
        $this->db->group_by('students.id');
        $this->db->order_by('students.id');
        $query = $this->db->get();
        if ($query === false) {
            return array();
        }
        return $query->result_array();
    }

    public function getParentList()
    {
        $sql = "SELECT students.*,users.username,users.password,users.role,users.is_active FROM `students` INNER JOIN users on users.id = students.parent_id WHERE parent_id != 0 GROUP BY parent_id";

        $query   = $this->db->query($sql);
        $parents = $query->result();

        return $parents;
    }

    public function count_classteachers($class_id, $section_id)
    {
        $sql = "SELECT staff.id FROM `subject_timetable` JOIN `subject_group_subjects` ON `subject_timetable`.`subject_group_subject_id` = `subject_group_subjects`.`id`inner JOIN subjects on subject_group_subjects.subject_id = subjects.id INNER JOIN staff on staff.id=subject_timetable.staff_id   WHERE staff.is_active='1' and `subject_timetable`.`class_id` = " . $class_id . " AND `subject_timetable`.`section_id` = " . $section_id . "  AND `subject_timetable`.`session_id` = " . $this->current_session;

        $query   = $this->db->query($sql);
        $count   = $query->result();
        $teacher = array();
        if (!empty($count)) {
            foreach ($count as $key => $value) {
                $teacher[$value->id] = $value->id;
            }
        }

        return count($teacher);
        die;
    }

    //===========
    public function check_student_email_exists($str)
    {
        $email = $this->security->xss_clean($str);
        if ($email != "") {
            $id = $this->input->post('student_id');
            if (!isset($id)) {
                $id = 0;
            }

            if ($this->check_data_exists($email, $id)) {
                $this->form_validation->set_message('check_student_email_exists', $this->lang->line('record_already_exist'));
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function check_data_exists($email, $id)
    {
        $this->db->where('email', $email);
        $this->db->where('id !=', $id);
        $query = $this->db->get('students');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function searchdtByClassSection($class_id = null, $section_id = null)
    {
        $userdata = $this->customlib->getUserData();
        
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $class_section_array = $this->customlib->get_myClassSection();
        }
        
        $i                    = 1;
        $custom_fields        = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array      = array();
        $field_var_array_name = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->datatables->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                array_push($field_var_array_name, 'table_custom_' . $i . '.field_value');
                $i++;
            }
        }
    
        $field_variable = (empty($field_var_array)) ? "" : "," . implode(',', $field_var_array);
        $field_name     = (empty($field_var_array_name)) ? "" : "," . implode(',', $field_var_array_name);
    
        if ($class_id != null) {
            $this->datatables->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->datatables->where('student_session.section_id', $section_id);
        }
    
        // Fixed SELECT statement with proper field mapping
        $this->datatables->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no, students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.cast,DATE(students.dob) as dob,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code,students.guardian_name,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,students.created_at,students.updated_at,students.father_name,students.app_key,students.parent_app_key,students.rte,students.gender,programs.title as program_title,programs.code as program_code' . $field_variable);       
        
        // Updated searchable fields
        $this->datatables->searchable('students.admission_no,students.firstname,students.father_name,students.dob,students.gender,categories.category,students.mobileno,students.religion,students.cast,programs.title,sections.section,classes.class' . $field_variable);
        
        $this->datatables->join('student_session', 'student_session.student_id = students.id');
        $this->datatables->join('classes', 'student_session.class_id = classes.id');
        $this->datatables->join('sections', 'sections.id = student_session.section_id');
        $this->datatables->join('categories', 'students.category_id = categories.id', 'left');
        $this->datatables->join('programs', 'students.program_id = programs.id', 'left');
            
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $this->datatables->or_group_start();
                    $this->datatables->where('student_session.class_id', $class_sectionkey);
                    $this->datatables->where('student_session.section_id', $class_sectionvaluevalue);
                    $this->datatables->group_end();
                }
            }
            $this->datatables->group_end();
        }
            
        $this->datatables->where('student_session.session_id', $this->current_session);
        $this->datatables->where('student_session.is_active', "yes");
        
        // Updated orderable fields
        $this->datatables->orderable('students.admission_no,students.firstname,sections.section,programs.title,classes.class,students.gender,students.mobileno,students.religion,students.cast,categories.category' . $field_name);
        
        $this->datatables->from('students');
        $this->datatables->sort('students.admission_no', 'asc');
        return $this->datatables->generate('json');
    }

    public function searchFullText($searchterm, $carray = null)
    {
        $userdata = $this->customlib->getUserData();
        $staff_id = $userdata['id'];
    
        $i             = 1;
        $custom_fields = $this->customfield_model->get_custom_fields('students', 1);
    
        $field_var_array      = array();
        $field_var_array_name = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->db->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                array_push($field_var_array_name, 'table_custom_' . $i . '.field_value');
                $i++;
            }
        }
        $field_variable = (empty($field_var_array)) ? "" : "," . implode(',', $field_var_array);
        $field_name     = (empty($field_var_array_name)) ? "" : "," . implode(',', $field_var_array_name);
    
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $class_section_array = $this->customlib->get_myClassSection();
        }
    
        // Fixed SELECT statement with proper field mapping
        $this->datatables->select('classes.id AS `class_id`,students.id,student_session.id as student_session_id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.cast,DATE(students.dob) as dob,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name,students.ifsc_code,students.father_name,students.guardian_name,students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active,students.created_at,students.updated_at,students.gender,students.rte,student_session.session_id,programs.title as program_title,programs.code as program_code' . $field_variable);
        
        $this->datatables->join('student_session', 'student_session.student_id = students.id');
        $this->datatables->join('classes', 'student_session.class_id = classes.id');
        $this->datatables->join('sections', 'sections.id = student_session.section_id');
        $this->datatables->join('categories', 'students.category_id = categories.id', 'left');
        $this->datatables->join('programs', 'students.program_id = programs.id', 'left');
        $this->datatables->join('school_houses', 'students.school_house_id = school_houses.id', 'left');
    
        if (!empty($class_section_array)) {
            $this->datatables->group_start();
            foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                    $this->datatables->or_group_start();
                    $this->datatables->where('student_session.class_id', $class_sectionkey);
                    $this->datatables->where('student_session.section_id', $class_sectionvaluevalue);
                    $this->datatables->group_end();
                }
            }
            $this->datatables->group_end();
        }
    
        $this->datatables->group_start();
        // Updated search fields to include sections and classes properly
        $this->datatables->or_like_string('students.firstname,students.middlename,students.lastname,school_houses.house_name,students.guardian_name,students.adhar_no,students.samagra_id,students.roll_no,students.admission_no,students.mobileno,students.email,students.religion,students.cast,students.gender,students.current_address,students.permanent_address,students.blood_group,students.bank_name,students.ifsc_code,students.father_name,students.father_phone,students.father_occupation,students.mother_name,students.mother_phone,students.mother_occupation,students.guardian_name,students.guardian_relation,students.guardian_phone,students.guardian_occupation,students.guardian_address,students.guardian_email,students.previous_school,students.note,programs.title,programs.code,sections.section,classes.class,categories.category', $searchterm);
        $this->datatables->group_end();
        
        $this->datatables->where('student_session.session_id', $this->current_session);
        $this->datatables->where('student_session.is_active', 'yes');
        
        // Updated searchable and orderable fields
        $this->datatables->searchable('students.admission_no,students.firstname,students.middlename,students.lastname,sections.section,programs.title,classes.class,students.gender,students.mobileno,students.religion,students.cast,categories.category' . $field_variable);
        $this->datatables->orderable('students.admission_no,students.firstname,sections.section,programs.title,classes.class,students.gender,students.mobileno,students.religion,students.cast,categories.category' . $field_name);
        
        $this->datatables->sort('students.id');
        $this->datatables->from('students');
        $std_data = $this->datatables->generate('json');
    
        if (($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes") && (empty($class_section_array))) {
            $std_data       = json_decode($std_data);
            $std_data->data = array();
            return json_encode($std_data);
        } else {
            return $std_data;
        }
    }

    /* function to get record for login credential report */
    public function getdtforlogincredential($class_id = null, $section_id = null)
    {
        $i               = 1;
        $custom_fields   = $this->customfield_model->get_custom_fields('students', 1);
        $field_var_array = array();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                $tb_counter = "table_custom_" . $i;
                array_push($field_var_array, 'table_custom_' . $i . '.field_value as ' . $custom_fields_value->name);
                $this->datatables->join('custom_field_values as ' . $tb_counter, 'students.id = ' . $tb_counter . '.belong_table_id AND ' . $tb_counter . '.custom_field_id = ' . $custom_fields_value->id, 'left');
                $i++;
            }
        }

        $field_variable = implode(',', $field_var_array);

        $this->datatables
            ->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,students.mobileno,students.email,students.state,students.city, students.pincode,students.religion,students.dob,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.app_key,students.parent_app_key,students.rte,students.gender,' . $field_variable)
            ->searchable('students.admission_no,students.firstname')
            ->orderable('students.admission_no,students.firstname," "," ", " "')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('classes', 'student_session.class_id = classes.id')
            ->join('sections', 'sections.id = student_session.section_id')
            ->join('categories', 'students.category_id = categories.id', 'left')
            ->where('student_session.session_id', $this->current_session)
            ->where('student_session.is_active', "yes")
            ->from('students');

        if ($class_id != null) {
            $this->datatables->where('student_session.class_id', $class_id);
        }
        if ($section_id != null) {
            $this->datatables->where('student_session.section_id', $section_id);
        }
        $this->datatables->sort('students.admission_no', 'asc');
        return $this->datatables->generate('json');

    }

    public function getUndefinedStudent()
    {
        $sql    = "SELECT students.id FROM `students` LEFT join student_session on student_session.student_id=students.id WHERE student_session.id IS NULL";
        $query  = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    public function biometric_attendance($admission_no = null)
    {
        $sql    = "SELECT staff.id,staff.employee_id,staff.name,staff.surname,staff.contact_no,staff.email,'staff' as `table_type` FROM `staff` WHERE employee_id=" . $this->db->escape($admission_no) . " UNION SELECT student_session.id as `student_session_id`,students.id, students.admission_no,students.firstname,students.middlename,students.lastname,'student' as `table_type` FROM `students` JOIN `student_session` ON `student_session`.`student_id` = `students`.`id` JOIN `classes` ON `student_session`.`class_id` = `classes`.`id` JOIN `sections` ON `sections`.`id` = `student_session`.`section_id` LEFT JOIN `hostel_rooms` ON `hostel_rooms`.`id` = `students`.`hostel_room_id` LEFT JOIN `hostel` ON `hostel`.`id` = `hostel_rooms`.`hostel_id` LEFT JOIN `room_types` ON `room_types`.`id` = `hostel_rooms`.`room_type_id` LEFT JOIN `vehicle_routes` ON `vehicle_routes`.`id` = `student_session`.`vehroute_id` LEFT JOIN `route_pickup_point` ON `route_pickup_point`.`id` = `student_session`.`route_pickup_point_id` LEFT JOIN `pickup_point` ON `route_pickup_point`.`pickup_point_id` = `pickup_point`.`id` LEFT JOIN `transport_route` ON `vehicle_routes`.`route_id` = `transport_route`.`id` LEFT JOIN `vehicles` ON `vehicles`.`id` = `vehicle_routes`.`vehicle_id` LEFT JOIN `school_houses` ON `school_houses`.`id` = `students`.`school_house_id` LEFT JOIN `users` ON `users`.`user_id` = `students`.`id` WHERE `student_session`.`session_id` = '" . $this->current_session . "' AND `users`.`role` = 'student' AND `students`.`is_active` = 'yes' AND `students`.`admission_no` = " . $this->db->escape($admission_no);
        $query  = $this->db->query($sql);
        $result = $query->row();
        return $result;

    }
    //===========

    public function getonlineadmissionreport($class_id = null, $section_id = null, $status = null)
    {
        $this->datatables
            ->select('students.*,online_admissions.form_status,online_admissions.is_enroll,online_admissions.firstname, online_admissions.gender,online_admissions.dob,online_admissions.lastname,online_admissions.paid_status,online_admissions.reference_no,online_admissions.mobileno,classes.class,sections.section,(SELECT ifnull(SUM(online_admission_payment.paid_amount),0) as amount  from online_admission_payment WHERE online_admission_payment.online_admission_id= online_admissions.id) as paid_amount')
            ->searchable('online_admissions.admission_no,online_admissions.firstname')
            ->orderable('online_admissions.admission_no,online_admissions.firstname,online_admissions.mobileno," ",online_admissions.gender," "," "," "," "," " ," "')
            ->join('students', 'students.admission_no = online_admissions.admission_no', "left")
            ->join('student_session', 'student_session.student_id = students.id', "left")
            ->join('class_sections', 'class_sections.id = online_admissions.class_section_id', 'left')
            ->join('classes', 'class_sections.class_id = classes.id', 'left')
            ->join('sections', 'sections.id = class_sections.section_id', 'left')
            ->from('online_admissions');

        if ($class_id != null) {
            $this->datatables->where('student_session.class_id', $class_id);
        }

        if ($section_id != null) {
            $this->datatables->where('student_session.section_id', $section_id);
        }
        if ($status != null) {
            $this->datatables->where('online_admissions.is_enroll', $status);
        }

        $this->datatables->sort('online_admissions.admission_no', 'desc');
        return $this->datatables->generate('json');
    }

    public function getstudentdetailbyid($id)
    {
        $this->db->select('students.firstname,students.middlename,students.lastname,students.admission_no,students.guardian_name,email,mobileno,guardian_phone,guardian_email');
        $this->db->from('students');
        $this->db->where('students.id', $id);
        $result = $this->db->get();
        return $result->row_array();
    }

    public function check_guardian_email_exists($str)
    {
        $email = $this->security->xss_clean($str);
        if ($email != "") {
            $id = $this->input->post('student_id');
            if (!isset($id)) {
                $id = 0;
            }

            if ($this->check_guardian_data_exists($email, $id)) {
                $this->form_validation->set_message('check_guardian_email_exists', $this->lang->line('record_already_exist'));
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function check_guardian_data_exists($email, $id)
    {
        $this->db->where('guardian_email', $email);
        $this->db->where('id !=', $id);
        $query = $this->db->get('students');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function check_student_mobile_no_exists($str)
    {
        $mobile = $this->security->xss_clean($str);
        if ($mobile != "") {
            $id = $this->input->post('student_id');
            if (!isset($id)) {
                $id = 0;
            }

            $isexist = $this->check_mobile_no_data_exists($mobile, $id);
            if ($isexist) {
                $this->form_validation->set_message('check_student_mobile_exists', $this->lang->line('record_already_exist'));
                return false;
            }
        }
        return true;
    }

    public function check_mobile_no_data_exists($mobile, $id)
    {
        $this->db->where('mobileno', $mobile);
        $this->db->where('id !=', $id);
        $query = $this->db->get('students');
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function studentdocbyid($id)
    {
        $this->db->select()->from('student_doc');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_sessions() {
        $this->db->select('*');
        $this->db->from('sessions');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_student_registration_data($session_id, $start = 0, $length = 10, $search_value = '', $order_by = 's.id', $order_dir = 'ASC') {
        try {
            // Reset query builder
            $this->db->reset_query();
            
            // First, get the total count without filters
            $this->db->select('COUNT(*) as total');
            $this->db->from('students s');
            $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
            $this->db->join('sessions ses', 'ss.session_id = ses.id', 'inner');
            $this->db->join('programs p', 'ss.program_id = p.id', 'left');
            $this->db->join('classes c', 'p.class_id = c.id', 'left');
            $this->db->join('sections sec', 'p.section_id = sec.id', 'left');
            
            $this->db->where('ss.session_id', $session_id);
            $this->db->where('s.is_active', 'yes');
            $this->db->where('ss.is_active', 'yes');
            
            $total_query = $this->db->get();
            
            // Check if query executed successfully
            if (!$total_query) {
                log_message('error', 'Total count query failed: ' . $this->db->error()['message']);
                throw new Exception('Database query failed');
            }
            
            $total_row = $total_query->row();
            $total_records = $total_row ? $total_row->total : 0;
            
            // Reset query builder for main query
            $this->db->reset_query();
            
            // Build the main query for data
            $this->db->select('
                s.id as student_id,
                s.admission_no,
                s.roll_no,
                s.firstname,
                s.middlename,
                s.lastname,
                s.gender,
                s.permanent_district,
                s.permanent_local_level,
                s.cast,
                s.dob,
                s.admission_year,
                ses.session,
                c.class,
                sec.section,
                p.title as program_title
            ');
            
            $this->db->from('students s');
            $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
            $this->db->join('sessions ses', 'ss.session_id = ses.id', 'inner');
            $this->db->join('programs p', 'ss.program_id = p.id', 'left');
            $this->db->join('classes c', 'p.class_id = c.id', 'left');
            $this->db->join('sections sec', 'p.section_id = sec.id', 'left');
            
            $this->db->where('ss.session_id', $session_id);
            $this->db->where('s.is_active', 'yes');
            $this->db->where('ss.is_active', 'yes');
    
            // Search functionality
            if (!empty($search_value)) {
                $this->db->group_start();
                $this->db->like('s.admission_no', $search_value);
                $this->db->or_like('s.roll_no', $search_value);
                $this->db->or_like('s.firstname', $search_value);
                $this->db->or_like('s.middlename', $search_value);
                $this->db->or_like('s.lastname', $search_value);
                $this->db->or_like('s.gender', $search_value);
                $this->db->or_like('s.permanent_district', $search_value);
                $this->db->or_like('s.permanent_local_level', $search_value);
                $this->db->or_like('s.cast', $search_value);
                $this->db->or_like('c.class', $search_value);
                $this->db->or_like('sec.section', $search_value);
                $this->db->or_like('p.title', $search_value);
                $this->db->group_end();
            }
    
            // Get filtered count before applying limit
            // We need to compile the query to get the SQL for counting
            $count_query = clone $this->db;
            $count_sql = $count_query->get_compiled_select();
            
            // Wrap the query in a count query
            $filtered_query = $this->db->query("SELECT COUNT(*) as filtered_total FROM ($count_sql) as filtered_data");
            
            if (!$filtered_query) {
                log_message('error', 'Filtered count query failed: ' . $this->db->error()['message']);
                throw new Exception('Filtered count query failed');
            }
            
            $filtered_row = $filtered_query->row();
            $filtered_count = $filtered_row ? $filtered_row->filtered_total : 0;
    
            // Rebuild the main query (since we used it for counting)
            $this->db->reset_query();
            
            $this->db->select('
                s.id as student_id,
                s.admission_no,
                s.roll_no,
                s.firstname,
                s.middlename,
                s.lastname,
                s.gender,
                s.permanent_district,
                s.permanent_local_level,
                s.cast,
                s.dob,
                s.admission_year,
                ses.session,
                c.class,
                sec.section,
                p.title as program_title
            ');
            
            $this->db->from('students s');
            $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
            $this->db->join('sessions ses', 'ss.session_id = ses.id', 'inner');
            $this->db->join('programs p', 'ss.program_id = p.id', 'left');
            $this->db->join('classes c', 'p.class_id = c.id', 'left');
            $this->db->join('sections sec', 'p.section_id = sec.id', 'left');
            
            $this->db->where('ss.session_id', $session_id);
            $this->db->where('s.is_active', 'yes');
            $this->db->where('ss.is_active', 'yes');
    
            // Apply search again
            if (!empty($search_value)) {
                $this->db->group_start();
                $this->db->like('s.admission_no', $search_value);
                $this->db->or_like('s.roll_no', $search_value);
                $this->db->or_like('s.firstname', $search_value);
                $this->db->or_like('s.middlename', $search_value);
                $this->db->or_like('s.lastname', $search_value);
                $this->db->or_like('s.gender', $search_value);
                $this->db->or_like('s.permanent_district', $search_value);
                $this->db->or_like('s.permanent_local_level', $search_value);
                $this->db->or_like('s.cast', $search_value);
                $this->db->or_like('c.class', $search_value);
                $this->db->or_like('sec.section', $search_value);
                $this->db->or_like('p.title', $search_value);
                $this->db->group_end();
            }
    
            // Apply ordering
            $this->db->order_by($order_by, $order_dir);
    
            // Apply limit for pagination
            if ($length != -1) {
                $this->db->limit($length, $start);
            }
    
            $query = $this->db->get();
            
            // Check if main query executed successfully
            if (!$query) {
                log_message('error', 'Main query failed: ' . $this->db->error()['message']);
                throw new Exception('Main data query failed');
            }
            
            $result = $query->result();
    
            return array(
                'data' => $result,
                'total_records' => $total_records,
                'filtered_records' => $filtered_count
            );
            
        } catch (Exception $e) {
            log_message('error', 'get_student_registration_data error: ' . $e->getMessage());
            
            // Return empty result set
            return array(
                'data' => array(),
                'total_records' => 0,
                'filtered_records' => 0
            );
        }
    }


    public function get_student_count_by_session($session_id) {
        $this->db->select('COUNT(*) as total');
        $this->db->from('students s');
        $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
        $this->db->where('ss.session_id', $session_id);
        $this->db->where('s.is_active', 'yes');
        $query = $this->db->get();
        $result = $query->row();
        return $result->total;
    }

public function searchdatatableByMultipleParams($class_id = null, $section_id = null, $program_id = null, $category_id = null, $gender = null, $rte = null, $ethnicity_id = null, $province_id = null, $district_id = null, $municipality_id = null)
    {
        $class_id        = $this->_student_report_filter_value($class_id);
        $section_id      = $this->_student_report_filter_value($section_id);
        $program_id      = $this->_student_report_filter_value($program_id);
        $category_id     = $this->_student_report_filter_value($category_id);
        $gender          = $this->_student_report_filter_value($gender);
        $rte             = $this->_student_report_filter_value($rte);
        $ethnicity_id    = $this->_student_report_filter_value($ethnicity_id);
        $province_id     = $this->_student_report_filter_value($province_id);
        $district_id     = $this->_student_report_filter_value($district_id);
        $municipality_id = $this->_student_report_filter_value($municipality_id);

        $this->db->select('students.*, classes.class, sections.section, categories.category, student_session.id as student_session_id')
            ->from('students')
            ->join('student_session', 'student_session.student_id = students.id', 'left')
            ->join('classes', 'student_session.class_id = classes.id', 'left')
            ->join('sections', 'student_session.section_id = sections.id', 'left')
            ->join('categories', 'students.category_id = categories.id', 'left')
            ->where('student_session.session_id', $this->current_session)
            ->where('student_session.is_active', 'yes');
       
        if ($class_id != null) {
            $this->db->where('student_session.class_id', $class_id);
        }
       
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }
       
        if ($program_id != null) {
            $this->db->where('students.program_id', $program_id);
        }
       
        if ($category_id != null) {
            $this->db->where('students.category_id', $category_id);
        }
       
        if ($gender != null) {
            $this->db->where('students.gender', $gender);
        }
       
        if ($rte != null) {
            $this->db->where('students.rte', $rte);
        }
       
        // Add new filter conditions
        if ($ethnicity_id != null) {
            $this->db->where('students.ethnicity', $ethnicity_id);
        }

        $this->_apply_student_report_address_filters($province_id, $district_id, $municipality_id);
       
        $this->db->order_by('students.id');
       
        // For DataTables server-side processing
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
       
        // Search functionality
        if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            $search_value = $_POST['search']['value'];
            $this->db->group_start();
            $this->db->like('students.firstname', $search_value);
            $this->db->or_like('students.lastname', $search_value);
            $this->db->or_like('students.admission_no', $search_value);
            $this->db->or_like('students.roll_no', $search_value);
            $this->db->or_like('students.father_name', $search_value);
            $this->db->or_like('students.mother_name', $search_value);
            $this->db->or_like('students.guardian_name', $search_value);
            $this->db->or_like('students.mobileno', $search_value);
            $this->db->or_like('students.adhar_no', $search_value);
            $this->db->or_like('students.samagra_id', $search_value);
            $this->db->or_like('students.national_id', $search_value);
            $this->db->or_like('students.email', $search_value);
            $this->db->group_end();
        }
       
        // Counting for pagination
        $query_result = $this->db->get();
        $total_result = $query_result->num_rows();
       
        // Get all records without limit for total count
        $this->db->select('students.*, classes.class, sections.section, categories.category, student_session.id as student_session_id')
            ->from('students')
            ->join('student_session', 'student_session.student_id = students.id', 'left')
            ->join('classes', 'student_session.class_id = classes.id', 'left')
            ->join('sections', 'student_session.section_id = sections.id', 'left')
            ->join('categories', 'students.category_id = categories.id', 'left')
            ->where('student_session.session_id', $this->current_session)
            ->where('student_session.is_active', 'yes');
       
        $total_records = $this->db->count_all_results();
       
        $return_array = array(
            'draw' => intval($_POST['draw']),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_result,
            'data' => $query_result->result()
        );
       
        return json_encode($return_array);
}

    /**
     * Normalize report filter POST values: empty strings and "0" become null.
     */
    private function _student_report_filter_value($value)
    {
        if ($value === null) {
            return null;
        }
        $value = is_string($value) ? trim($value) : $value;
        if ($value === '' || $value === '0' || $value === 0) {
            return null;
        }
        return $value;
    }

    /**
     * Filter by UGC/HEMIS permanent address (ugc_p_*) with legacy ID fallback.
     */
    private function _apply_student_report_address_filters($province, $district, $municipality)
    {
        if ($municipality !== null) {
            $this->db->group_start();
            if (preg_match('/^\d{4,}$/', (string) $municipality)) {
                $this->db->where('students.ugc_p_combined_code', (string) $municipality);
                $legacy_muni_id = $this->_legacy_municipality_id_from_combined_code($municipality);
                if ($legacy_muni_id !== null) {
                    $this->db->or_where('students.permanent_local_level', $legacy_muni_id);
                }
            } else {
                $this->db->where('LOWER(TRIM(students.ugc_p_local_level))', strtolower((string) $municipality));
                $legacy_muni_id = $this->_legacy_municipality_id_from_name($municipality, $district);
                if ($legacy_muni_id !== null) {
                    $this->db->or_where('students.permanent_local_level', $legacy_muni_id);
                }
            }
            $this->db->group_end();
        }

        if ($district !== null) {
            $this->db->group_start();
            $this->db->where('LOWER(TRIM(students.ugc_p_district))', strtolower((string) $district));
            $legacy_district_id = $this->_legacy_district_id_from_name($district, $province);
            if ($legacy_district_id !== null) {
                $this->db->or_where('students.permanent_district', $legacy_district_id);
            }
            $this->db->group_end();
        }

        if ($province !== null) {
            $base     = preg_replace('/\s+province$/i', '', (string) $province);
            $variants = array_unique(array_filter(array(
                (string) $province,
                $base,
                $base . ' Province',
            )));
            $this->db->group_start();
            $this->db->where_in('students.ugc_p_province', $variants);
            $legacy_province_ids = $this->_legacy_province_ids_from_name($province);
            if (!empty($legacy_province_ids)) {
                $this->db->or_where_in('students.permanent_province', $legacy_province_ids);
            }
            $this->db->group_end();
        }
    }

    private function _legacy_province_ids_from_name($province)
    {
        $base = preg_replace('/\s+province$/i', '', trim((string) $province));
        if ($base === '') {
            return array();
        }
        if (!$this->db->table_exists('states')) {
            return array();
        }
        $q = $this->db->query(
            'SELECT id FROM states WHERE name = ? OR name = ? OR name = ?',
            array((string) $province, $base, $base . ' Province')
        );
        $ids = array();
        foreach ($q->result_array() as $row) {
            $ids[] = (int) $row['id'];
        }
        return $ids;
    }

    private function _legacy_district_id_from_name($district, $province = null)
    {
        $district = trim((string) $district);
        if ($district === '' || !$this->db->table_exists('districts')) {
            return null;
        }
        $sql    = 'SELECT id FROM districts WHERE (name = ? OR name_np = ?)';
        $params = array($district, $district);
        if ($province !== null) {
            $legacy_pids = $this->_legacy_province_ids_from_name($province);
            if (count($legacy_pids) === 1) {
                $sql     .= ' AND province_id = ?';
                $params[] = $legacy_pids[0];
            }
        }
        $sql .= ' LIMIT 1';
        $q = $this->db->query($sql, $params);
        return ($q->num_rows() > 0) ? (int) $q->row()->id : null;
    }

    private function _legacy_municipality_id_from_name($municipality, $district = null)
    {
        $municipality = trim((string) $municipality);
        if ($municipality === '' || !$this->db->table_exists('municipalities')) {
            return null;
        }
        $sql    = 'SELECT id FROM municipalities WHERE (name = ? OR name_np = ?)';
        $params = array($municipality, $municipality);
        if ($district !== null) {
            $legacy_did = $this->_legacy_district_id_from_name($district);
            if ($legacy_did !== null) {
                $sql     .= ' AND district_id = ?';
                $params[] = $legacy_did;
            }
        }
        $sql .= ' LIMIT 1';
        $q = $this->db->query($sql, $params);
        return ($q->num_rows() > 0) ? (int) $q->row()->id : null;
    }

    private function _legacy_municipality_id_from_combined_code($combined_code)
    {
        if (!$this->db->table_exists('ugc_addresses') || !$this->db->table_exists('municipalities')) {
            return null;
        }
        $q = $this->db->query(
            'SELECT local_level, district FROM ugc_addresses WHERE combined_code = ? LIMIT 1',
            array((string) $combined_code)
        );
        if ($q->num_rows() === 0 || empty($q->row()->local_level)) {
            return null;
        }
        $row = $q->row();
        return $this->_legacy_municipality_id_from_name($row->local_level, $row->district);
    }

    public function getStudentDocuments($student_id) {
        $this->db->select('*');
        $this->db->from('student_doc');
        $this->db->where('student_id', $student_id);
        $query = $this->db->get();
        
        // Check if query was successful before calling result_array()
        if ($query !== FALSE) {
            return $query->result_array();
        } else {
            // Return empty array if query fails
            return array();
        }
    }

    public function getDocumentById($doc_id) {
        return $this->db->select('*')
                        ->from('student_doc')
                        ->where('id', $doc_id)
                        ->get()
                        ->row();
    }
    
    /**
     * Delete document by ID
     * 
     * @param int $doc_id
     * @return bool
     */
    public function deleteDocument($doc_id) {
        $this->db->where('id', $doc_id);
        $this->db->delete('student_doc');
        return $this->db->affected_rows() > 0;
    }


public function deleteMultipleDocuments($doc_ids) {
    if (empty($doc_ids)) {
        return 0;
    }
    
    // Get document details before deletion for file removal
    $this->db->select('id, student_id, doc');
    $this->db->from('student_doc');
    $this->db->where_in('id', $doc_ids);
    $query = $this->db->get();
    $documents = $query->result();
    
    // Delete documents from database
    $this->db->where_in('id', $doc_ids);
    $this->db->delete('student_doc');
    $affected_rows = $this->db->affected_rows();
    
    // Return the documents data for file deletion in the controller
    return [
        'affected_rows' => $affected_rows,
        'documents' => $documents
    ];
}

    public function updatedoc($doc_id, $data)
{
    $this->db->where('id', $doc_id);
    $this->db->update('student_doc', $data);
    return true;
}

public function getTotalActiveStudents() {
    $this->db->select('COUNT(*) as count');
    $this->db->from('students');
    $this->db->where('is_active', 'yes');
    $query = $this->db->get();
    return $query->row()->count;
}




    public function getStudentByClassSection($class_id, $section_id)
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename,students.lastname,students.image,   students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,     students.dob ,students.current_address,students.blood_group,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.cast,students.bank_name, students.ifsc_code,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.mother_name,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'students.id = users.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getStudentByClassSectionBySearch($class_id, $section_id, $search_text)
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename,students.lastname,students.image,   students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,     students.dob ,students.current_address,students.blood_group,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.cast,students.bank_name, students.ifsc_code,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.mother_name,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'students.id = users.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->group_start();
        $this->db->like('students.firstname', $search_text);
        $this->db->or_like('students.middlename', $search_text);
        $this->db->or_like('students.lastname', $search_text);
        $this->db->or_like('students.admission_no', $search_text);
        $this->db->or_like('students.roll_no', $search_text);
        $this->db->or_like('students.mobileno', $search_text);
        $this->db->or_like('students.email', $search_text);
        $this->db->or_like('students.adhar_no', $search_text);
        $this->db->or_like('students.samagra_id', $search_text);
        $this->db->or_like('students.bank_account_no', $search_text);
        $this->db->or_like('students.bank_name', $search_text);
        $this->db->or_like('students.ifsc_code', $search_text);
        $this->db->or_like('students.guardian_name', $search_text);
        $this->db->or_like('students.guardian_relation', $search_text);
        $this->db->or_like('students.guardian_phone', $search_text);
        $this->db->or_like('students.guardian_address', $search_text);
        $this->db->or_like('students.father_name', $search_text);
        $this->db->or_like('students.mother_name', $search_text);
        $this->db->or_like('students.rte', $search_text);
        $this->db->or_like('students.gender', $search_text);
        $this->db->or_like('students.religion', $search_text);
        $this->db->or_like('students.cast', $search_text);
        $this->db->or_like('students.dob', $search_text);
        $this->db->or_like('students.current_address', $search_text);
        $this->db->or_like('students.permanent_address', $search_text);
        $this->db->or_like('students.blood_group', $search_text);
        $this->db->or_like('students.note', $search_text);
        $this->db->or_like('students.state', $search_text);
        $this->db->or_like('students.city', $search_text);
        $this->db->or_like('students.pincode', $search_text);
        $this->db->or_like('students.admission_date', $search_text);
        $this->db->or_like('students.created_at', $search_text);
        $this->db->or_like('students.updated_at', $search_text);
        $this->db->or_like('students.father_phone', $search_text);
        $this->db->or_like('students.mother_phone', $search_text);
        $this->db->or_like('students.father_occupation', $search_text);
        $this->db->or_like('students.mother_occupation', $search_text);
        $this->db->or_like('students.guardian_occupation', $search_text);
        $this->db->or_like('students.guardian_email', $search_text);
        $this->db->or_like('students.previous_school', $search_text);
        $this->db->or_like('students.height', $search_text);
        $this->db->or_like('students.weight', $search_text);
        $this->db->or_like('students.measurement_date', $search_text);
        $this->db->or_like('students.dis_reason', $search_text);
        $this->db->or_like('students.dis_note', $search_text);
        $this->db->or_like('categories.category', $search_text);
        $this->db->group_end();
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getStudentByClassSectionBySearchByArray($class_id, $section_id, $search_text, $array)
    {
        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname, students.middlename,students.lastname,students.image,   students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,     students.dob ,students.current_address,students.blood_group,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.cast,students.bank_name, students.ifsc_code,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.mother_name,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`')->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'students.id = users.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where_in('students.id', $array);
        $this->db->group_start();
        $this->db->like('students.firstname', $search_text);
        $this->db->or_like('students.middlename', $search_text);
        $this->db->or_like('students.lastname', $search_text);
        $this->db->or_like('students.admission_no', $search_text);
        $this->db->or_like('students.roll_no', $search_text);
        $this->db->or_like('students.mobileno', $search_text);
        $this->db->or_like('students.email', $search_text);
        $this->db->or_like('students.adhar_no', $search_text);
        $this->db->or_like('students.samagra_id', $search_text);
        $this->db->or_like('students.bank_account_no', $search_text);
        $this->db->or_like('students.bank_name', $search_text);
        $this->db->or_like('students.ifsc_code', $search_text);
        $this->db->or_like('students.guardian_name', $search_text);
        $this->db->or_like('students.guardian_relation', $search_text);
        $this->db->or_like('students.guardian_phone', $search_text);
        $this->db->or_like('students.guardian_address', $search_text);
        $this->db->or_like('students.father_name', $search_text);
        $this->db->or_like('students.mother_name', $search_text);
        $this->db->or_like('students.rte', $search_text);
        $this->db->or_like('students.gender', $search_text);
        $this->db->or_like('students.religion', $search_text);
        $this->db->or_like('students.cast', $search_text);
        $this->db->or_like('students.dob', $search_text);
        $this->db->or_like('students.current_address', $search_text);
        $this->db->or_like('students.permanent_address', $search_text);
        $this->db->or_like('students.blood_group', $search_text);
        $this->db->or_like('students.note', $search_text);
        $this->db->or_like('students.state', $search_text);
        $this->db->or_like('students.city', $search_text);
        $this->db->or_like('students.pincode', $search_text);
        $this->db->or_like('students.admission_date', $search_text);
        $this->db->or_like('students.created_at', $search_text);
        $this->db->or_like('students.updated_at', $search_text);
        $this->db->or_like('students.father_phone', $search_text);
        $this->db->or_like('students.mother_phone', $search_text);
        $this->db->or_like('students.father_occupation', $search_text);
        $this->db->or_like('students.mother_occupation', $search_text);
        $this->db->or_like('students.guardian_occupation', $search_text);
        $this->db->or_like('students.guardian_email', $search_text);
        $this->db->or_like('students.previous_school', $search_text);
        $this->db->or_like('students.height', $search_text);
        $this->db->or_like('students.weight', $search_text);
        $this->db->or_like('students.measurement_date', $search_text);
        $this->db->or_like('students.dis_reason', $search_text);
        $this->db->or_like('students.dis_note', $search_text);
        $this->db->or_like('categories.category', $search_text);
        $this->db->group_end();
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCounts()
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClass($class_id)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClassSection($class_id, $section_id)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClassSectionBySearch($class_id, $section_id, $search_text)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->group_start();
        $this->db->like('students.firstname', $search_text);
        $this->db->or_like('students.middlename', $search_text);
        $this->db->or_like('students.lastname', $search_text);
        $this->db->or_like('students.admission_no', $search_text);
        $this->db->or_like('students.roll_no', $search_text);
        $this->db->or_like('students.mobileno', $search_text);
        $this->db->or_like('students.email', $search_text);
        $this->db->or_like('students.adhar_no', $search_text);
        $this->db->or_like('students.samagra_id', $search_text);
        $this->db->or_like('students.bank_account_no', $search_text);
        $this->db->or_like('students.bank_name', $search_text);
        $this->db->or_like('students.ifsc_code', $search_text);
        $this->db->or_like('students.guardian_name', $search_text);
        $this->db->or_like('students.guardian_relation', $search_text);
        $this->db->or_like('students.guardian_phone', $search_text);
        $this->db->or_like('students.guardian_address', $search_text);
        $this->db->or_like('students.father_name', $search_text);
        $this->db->or_like('students.mother_name', $search_text);
        $this->db->or_like('students.rte', $search_text);
        $this->db->or_like('students.gender', $search_text);
        $this->db->or_like('students.religion', $search_text);
        $this->db->or_like('students.cast', $search_text);
        $this->db->or_like('students.dob', $search_text);
        $this->db->or_like('students.current_address', $search_text);
        $this->db->or_like('students.permanent_address', $search_text);
        $this->db->or_like('students.blood_group', $search_text);
        $this->db->or_like('students.note', $search_text);
        $this->db->or_like('students.state', $search_text);
        $this->db->or_like('students.city', $search_text);
        $this->db->or_like('students.pincode', $search_text);
        $this->db->or_like('students.admission_date', $search_text);
        $this->db->or_like('students.created_at', $search_text);
        $this->db->or_like('students.updated_at', $search_text);
        $this->db->or_like('students.father_phone', $search_text);
        $this->db->or_like('students.mother_phone', $search_text);
        $this->db->or_like('students.father_occupation', $search_text);
        $this->db->or_like('students.mother_occupation', $search_text);
        $this->db->or_like('students.guardian_occupation', $search_text);
        $this->db->or_like('students.guardian_email', $search_text);
        $this->db->or_like('students.previous_school', $search_text);
        $this->db->or_like('students.height', $search_text);
        $this->db->or_like('students.weight', $search_text);
        $this->db->or_like('students.measurement_date', $search_text);
        $this->db->or_like('students.dis_reason', $search_text);
        $this->db->or_like('students.dis_note', $search_text);
        $this->db->or_like('categories.category', $search_text);
        $this->db->group_end();
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClassSectionBySearchByArray($class_id, $section_id, $search_text, $array)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where_in('students.id', $array);
        $this->db->group_start();
        $this->db->like('students.firstname', $search_text);
        $this->db->or_like('students.middlename', $search_text);
        $this->db->or_like('students.lastname', $search_text);
        $this->db->or_like('students.admission_no', $search_text);
        $this->db->or_like('students.roll_no', $search_text);
        $this->db->or_like('students.mobileno', $search_text);
        $this->db->or_like('students.email', $search_text);
        $this->db->or_like('students.adhar_no', $search_text);
        $this->db->or_like('students.samagra_id', $search_text);
        $this->db->or_like('students.bank_account_no', $search_text);
        $this->db->or_like('students.bank_name', $search_text);
        $this->db->or_like('students.ifsc_code', $search_text);
        $this->db->or_like('students.guardian_name', $search_text);
        $this->db->or_like('students.guardian_relation', $search_text);
        $this->db->or_like('students.guardian_phone', $search_text);
        $this->db->or_like('students.guardian_address', $search_text);
        $this->db->or_like('students.father_name', $search_text);
        $this->db->or_like('students.mother_name', $search_text);
        $this->db->or_like('students.rte', $search_text);
        $this->db->or_like('students.gender', $search_text);
        $this->db->or_like('students.religion', $search_text);
        $this->db->or_like('students.cast', $search_text);
        $this->db->or_like('students.dob', $search_text);
        $this->db->or_like('students.current_address', $search_text);
        $this->db->or_like('students.permanent_address', $search_text);
        $this->db->or_like('students.blood_group', $search_text);
        $this->db->or_like('students.note', $search_text);
        $this->db->or_like('students.state', $search_text);
        $this->db->or_like('students.city', $search_text);
        $this->db->or_like('students.pincode', $search_text);
        $this->db->or_like('students.admission_date', $search_text);
        $this->db->or_like('students.created_at', $search_text);
        $this->db->or_like('students.updated_at', $search_text);
        $this->db->or_like('students.father_phone', $search_text);
        $this->db->or_like('students.mother_phone', $search_text);
        $this->db->or_like('students.father_occupation', $search_text);
        $this->db->or_like('students.mother_occupation', $search_text);
        $this->db->or_like('students.guardian_occupation', $search_text);
        $this->db->or_like('students.guardian_email', $search_text);
        $this->db->or_like('students.previous_school', $search_text);
        $this->db->or_like('students.height', $search_text);
        $this->db->or_like('students.weight', $search_text);
        $this->db->or_like('students.measurement_date', $search_text);
        $this->db->or_like('students.dis_reason', $search_text);
        $this->db->or_like('students.dis_note', $search_text);
        $this->db->or_like('categories.category', $search_text);
        $this->db->group_end();
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClassSectionByArray($class_id, $section_id, $array)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where_in('students.id', $array);
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClassByArray($class_id, $array)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where_in('students.id', $array);
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByArray($array)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where_in('students.id', $array);
        $this->db->group_by('faculty.name, ethnicity.ethnicity');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getFacultyEthnicityCountsByClassSectionBySearchByProgram($class_id, $section_id, $search_text, $program_id)
    {
        $this->db->select('faculty.name, ethnicity.ethnicity, COUNT(*) as count');
        $this->db->from('faculty');
        $this->db->join('student_faculty', 'faculty.id = student_faculty.faculty_id');
        $this->db->join('students', 'student_faculty.student_id = students.id');
        $this->db->join('ethnicity', 'students.ethnicity_id = ethnicity.id');
        $this->db->join('student_session', 'students.id = student_session.student_id');
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where('student_session.program_id', $program_id);
        $this->db->group_start();
        $this->db->like('students.firstname', $search_text);
        $this->db->or_like('students.middlename', $search_text);
        $this->db->or_like('students.lastname', $search_text);
        $this->db->or_like('students.admission_no', $search_text);
        $this->db->or_like('students.roll_no', $search_text);
        $this->db->or_like('students.mobileno', $search_text);
        $this->db->or_like('students.email', $search_text);
        $this->db->or_like('students.adhar_no', $search_text);
        $this->db->or_like('students.samagra_id', $search_text);
        $this->db->or_like('students.bank_account_no', $search_text);
        $this->db->or_like('students.bank_name', $search_text);
        $this->db->or_like('students.ifsc_code', $search_text);
        $this->db->or_like('students.guardian_name', $search_text);
        $this->db->or_like('students.guardian_relation', $search_text);
        $this->db->or_like('students.guardian_phone', $search_text);
        $this->db->or_like('students.guardian_address', $search_text);
        $this->db->or_like('students.father_name', $search_text);
        $this->db->or_like('students.mother_name', $search_text);
        $this->db->or_like('students.rte', $search_text);
        $this->db->or_like('students.gender', $search_text);
        $this->db->or_like('students.religion', $search_text);
        $this->db->or_like('students.cast', $search_text);
        $this->db->or_like('students.dob', $search_text);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->group_by('students.id');
        $query = $this->db->get();
        
        return $query->result_array();
    }

    /**
     * Get students by program and current year/semester
     * 
     * @param int $program_id
     * @param string $year
     * @param string $semester
     * @return array
     */
    public function get_students_by_program_year_semester($program_id, $year = null, $semester = null) {
        $this->db->select('students.*');
        $this->db->from('students');
        
        // Only apply year or semester filtering if provided
        if (!empty($year)) {
            $this->db->where('students.semister_or_year_no', $year);
        }
        
        if (!empty($semester)) {
            $this->db->where('students.semester', $semester);
        }
        
        // Filter by program ID
        $this->db->where('students.program_id', $program_id);
        $this->db->where('student_session.is_active', 'yes');
        $query = $this->db->get();
        
        return $query->result_array();
    }


    public function check_graduation_status($student_id) {
        $this->db->where('student_id', $student_id);
        $this->db->where('is_graduated', 1);
        $query = $this->db->get('student_graduation_applications');
        return $query->num_rows() > 0;
    }

    /**
     * Save graduation application
     */
    public function save_graduation_application($data) {
        $this->db->insert('student_graduation_applications', $data);
        return $this->db->insert_id();
    }

/**
 * Mark student as graduated
 */
public function mark_student_graduated($student_id) {
    $this->db->where('student_id', $student_id);
    return $this->db->update('student_session', ['is_graduated' => 1]);
}

public function getGraduatedStudents($class_id = null, $section_id = null, $program_id = null, $session_id = null, $gender = null) {
    $this->db->select('
        s.id,
        s.admission_no,
        s.roll_no,
        s.firstname,
        s.middlename,
        s.lastname,
        s.gender,
        s.dob,
        s.mobileno,
        s.email,
        s.guardian_name,
        s.dis_note,
        s.updated_at,
        c.class,
        sec.section,
        p.title as program_name,
        ses.session as session_name,
        ss.id as student_session_id
    ');
    
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id AND ss.is_graduated = 1', 'inner');
    $this->db->join('classes c', 'ss.class_id = c.id', 'left');
    $this->db->join('sections sec', 'ss.section_id = sec.id', 'left');
    $this->db->join('programs p', 'ss.program_id = p.id', 'left');
    $this->db->join('sessions ses', 'ss.session_id = ses.id', 'left');
    
    if (!empty($class_id)) {
        $this->db->where('ss.class_id', $class_id);
    }
    
    if (!empty($section_id)) {
        $this->db->where('ss.section_id', $section_id);
    }
    
    if (!empty($program_id)) {
        $this->db->where('ss.program_id', $program_id);
    }
    
    if (!empty($session_id)) {
        $this->db->where('ss.session_id', $session_id);
    }
    
    if (!empty($gender)) {
        $gender = strtolower(trim((string) $gender));
        $this->db->where('LOWER(TRIM(s.gender)) = ' . $this->db->escape($gender), null, false);
    }
    
    $this->db->order_by('s.updated_at', 'DESC');
    $this->db->group_by('s.id');
    
    $query = $this->db->get();
    return $query === false ? array() : $query->result_array();
}

public function getDropoutStudents($class_id = null, $section_id = null, $program_id = null, $session_id = null, $gender = null) {
    $this->db->select('
        s.id,
        s.admission_no,
        s.roll_no,
        s.firstname,
        s.middlename,
        s.lastname,
        s.gender,
        s.dob,
        s.mobileno,
        s.email,
        s.guardian_name,
        s.dis_note,
        s.dis_reason,
        s.updated_at,
        c.class,
        sec.section,
        p.title as program_name,
        ses.session as session_name,
        ss.id as student_session_id
    ');
    
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id AND ss.is_leave = 1', 'inner');
    $this->db->join('classes c', 'ss.class_id = c.id', 'left');
    $this->db->join('sections sec', 'ss.section_id = sec.id', 'left');
    $this->db->join('programs p', 'ss.program_id = p.id', 'left');
    $this->db->join('sessions ses', 'ss.session_id = ses.id', 'left');
    
    if (!empty($class_id)) {
        $this->db->where('ss.class_id', $class_id);
    }
    
    if (!empty($section_id)) {
        $this->db->where('ss.section_id', $section_id);
    }
    
    if (!empty($program_id)) {
        $this->db->where('ss.program_id', $program_id);
    }
    
    if (!empty($session_id)) {
        $this->db->where('ss.session_id', $session_id);
    }
    
    if (!empty($gender)) {
        $gender = strtolower(trim((string) $gender));
        $this->db->where('LOWER(TRIM(s.gender)) = ' . $this->db->escape($gender), null, false);
    }
    
    $this->db->order_by('s.updated_at', 'DESC');
    $this->db->group_by('s.id');
    
    $query = $this->db->get();
    return $query === false ? array() : $query->result_array();
}

// Add this new method to get gender-wise counts
public function getDropoutGenderStats($session_id = null) {
    $this->db->select('s.gender, COUNT(s.id) as count');
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id AND ss.is_leave = 1', 'inner');
    
    if ($session_id) {
        $this->db->where('ss.session_id', $session_id);
    }
    
    $this->db->group_by('s.gender');
    $query = $this->db->get();
    
    $result = $query->result_array();
    
    // Format the results for easy display
    $stats = [
        'Male' => 0,
        'Female' => 0,
        'Other' => 0,
        'Total' => 0
    ];
    
    foreach ($result as $row) {
        $gender = $row['gender'];
        $stats[$gender] = $row['count'];
        $stats['Total'] += $row['count'];
    }
    
    return $stats;
}

public function getDropoutStudentsBySession($session_id) {
    $this->db->select('
        s.id,
        s.admission_no,
        s.firstname,
        s.middlename,
        s.lastname,
        s.gender,
        s.dob,
        s.guardian_name,
        s.dis_note,
        s.updated_at,
        c.class,
        sec.section,
        p.title as program_name
    ');
    
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
    $this->db->join('classes c', 'ss.class_id = c.id', 'left');
    $this->db->join('sections sec', 'ss.section_id = sec.id', 'left');
    $this->db->join('programs p', 'ss.program_id = p.id', 'left');
    
    $this->db->where('ss.session_id', $session_id);
    $this->db->where('ss.is_leave', 1);
    $this->db->where('ss.is_graduated', 0);
    $this->db->where('s.is_active', 'no');
    
    $this->db->order_by('s.updated_at', 'DESC');
    $this->db->group_by('s.id');
    
    $query = $this->db->get();
    return $query->result_array();
}

public function markStudentAsDropout($student_id, $session_id, $dropout_reason = '') {
    // Start transaction
    $this->db->trans_start();
    
    // Update student record
    $student_data = array(
        'is_active' => 'no',
        'dis_note' => $dropout_reason,
        'updated_at' => date('Y-m-d'),
        'disable_at' => date('Y-m-d')
    );
    
    $this->db->where('id', $student_id);
    $this->db->update('students', $student_data);
    
    // Update student_session record
    $session_data = array(
        'is_leave' => 1,
        'is_active' => 'no',
        'updated_at' => date('Y-m-d')
    );
    
    $this->db->where('student_id', $student_id);
    $this->db->where('session_id', $session_id);
    $this->db->update('student_session', $session_data);
    
    // Complete transaction
    $this->db->trans_complete();
    
    return $this->db->trans_status();
}

public function getDropoutCount($session_id = null) {
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
    
    $this->db->where('ss.is_leave', 1);
    $this->db->where('ss.is_graduated', 0);
    $this->db->where('s.is_active', 'no');
    
    if ($session_id) {
        $this->db->where('ss.session_id', $session_id);
    }
    
    return $this->db->count_all_results();
}

public function getDropoutStatsByClass($session_id = null) {
    $this->db->select('c.class, COUNT(s.id) as dropout_count');
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
    $this->db->join('classes c', 'ss.class_id = c.id', 'left');
    
    $this->db->where('ss.is_leave', 1);
    $this->db->where('ss.is_graduated', 0);
    $this->db->where('s.is_active', 'no');
    
    if ($session_id) {
        $this->db->where('ss.session_id', $session_id);
    }
    
    $this->db->group_by('ss.class_id');
    $this->db->order_by('c.class');
    
    $query = $this->db->get();
    return $query->result_array();
}


public function getFacultyGenderCounts() {
    try {
        $this->db->select('sections.section as faculty,
                      SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) as female,
                      COUNT(*) as total')
             ->from('students')
             ->join('student_session', 'student_session.student_id = students.id')
             ->join('sections', 'sections.id = student_session.section_id')
             ->where('student_session.session_id', $this->current_session)
             ->where('student_session.is_active', 'yes')
             ->group_by('sections.id')
             ->order_by('sections.section');
       
        $query = $this->db->get();
       
        if ($query === FALSE) {
            return array();
        }
       
        return $query->result_array();
    } catch (Exception $e) {
        error_log('Error in getFacultyGenderCounts: ' . $e->getMessage());
        return array();
    }
}


public function getProgramGenderCounts() {
    try {
        $this->db->select('programs.title as program,
                      SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) as female,
                      COUNT(*) as total')
             ->from('students')
             ->join('student_session', 'student_session.student_id = students.id')
             ->join('programs', 'programs.id = student_session.program_id')
             ->where('student_session.session_id', $this->current_session)
             ->where('student_session.is_active', 'yes')
             ->group_by('programs.id')
             ->order_by('programs.title');
       
        $query = $this->db->get();
       
        if ($query === FALSE) {
            return array();
        }
       
        return $query->result_array();
    } catch (Exception $e) {
        error_log('Error in getProgramGenderCounts: ' . $e->getMessage());
        return array();
    }
}


public function getFacultyLevelProgramLocalCounts() {
    try {
        $this->db->select('
            sections.section as faculty,
            CONCAT(classes.degree, " - ", classes.class) as level,
            CONCAT(programs.title, " (", classes.degree, " - ", classes.class, ")") as program,
            IFNULL(municipalities.name, IFNULL(students.permanent_local_level, "Not Specified")) as local_level,
            COUNT(*) as total
        ')
        ->from('students')
        ->join('student_session', 'student_session.student_id = students.id')
        ->join('sections', 'sections.id = student_session.section_id')
        ->join('classes', 'classes.id = student_session.class_id')
        ->join('programs', 'programs.id = student_session.program_id')
        ->join('municipalities', 'municipalities.id = students.permanent_local_level', 'left')
        ->where('student_session.session_id', $this->current_session)
        ->where('student_session.is_active', 'yes')
        ->group_by('sections.section, classes.degree, classes.class, programs.title, students.permanent_local_level')
        ->order_by('sections.section, classes.degree, classes.class, programs.title, municipalities.name');

        $query = $this->db->get();

        if ($query === FALSE) {
            error_log('Query failed in getFacultyLevelProgramLocalCounts: ' . $this->db->error()['message']);
            return array();
        }

        $result = $query->result_array();
        
        foreach ($result as &$row) { 
            if (strpos($row['local_level'], 'Kathmandu Metropolitan City') !== false) {
                $row['local_level'] = 'Kathmandu';
            }
        }

        error_log('Number of results: ' . count($result));
        return $result;

    } catch (Exception $e) {
        error_log('Error in getFacultyLevelProgramLocalCounts: ' . $e->getMessage());
        return array();
    }
}


public function getDistrictEthnicityCountsStructured($ethnicity_mapping = array(), $standard_ethnicities = array()) {
    try {
        $fields = $this->db->list_fields('students');
        if (!in_array('permanent_district', $fields) || !in_array('ethnicity', $fields)) {
            return array();
        }

        // Define the ethnicity list
        $ethnicityList = ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];
       
        // Modified query to handle both ID and name cases
        $this->db->select('
            CASE 
                WHEN districts.id IS NOT NULL THEN districts.name
                WHEN students.permanent_district REGEXP \'^[0-9]+$\' THEN (SELECT name FROM districts WHERE id = students.permanent_district)
                ELSE students.permanent_district
            END as district,
            students.cast as cast_raw,
            students.gender,
            COUNT(*) as count')
         ->from('students')
         ->join('student_session', 'student_session.student_id = students.id')
         ->join('districts', 'districts.id = students.permanent_district AND districts.id = students.permanent_district', 'left')
         ->where('student_session.session_id', $this->current_session)
         ->where('student_session.is_active', 'yes')
         ->group_by('district, students.cast, students.gender')
         ->order_by('district, students.cast, students.gender');
       
        $query = $this->db->get();
       
        if ($query === FALSE) {
            error_log('Query failed in getDistrictEthnicityCountsStructured');
            return array();
        }
       
        $results = $query->result_array();
        $structured = array();
       
        // Initialize structure for all districts and ethnicities
        foreach ($results as $row) {
            $district = $row['district'] ?? 'Not Specified';
            $cast_raw = $row['cast_raw'];
            $gender = $row['gender'];
            $count = (int)$row['count'];
           
            // Map the cast ID to ethnicity name
            $ethnicity = $this->mapCastToEthnicity($cast_raw, $ethnicity_mapping, $standard_ethnicities);
           
            // Ensure the mapped ethnicity is in our standard list
            if (!in_array($ethnicity, $ethnicityList)) {
                $ethnicity = 'Others';
            }
           
            if (!isset($structured[$district])) {
                $structured[$district] = array();
                // Initialize all ethnicities for this district
                foreach ($ethnicityList as $eth) {
                    $structured[$district][$eth] = array(
                        'Male' => 0,
                        'Female' => 0,
                        'Total' => 0
                    );
                }
                $structured[$district]['Total'] = array(
                    'Male' => 0,
                    'Female' => 0,
                    'Total' => 0
                );
            }
           
            // Add count to appropriate ethnicity and gender
            if ($gender == 'Male' || $gender == 'Female') {
                $structured[$district][$ethnicity][$gender] += $count;
                $structured[$district][$ethnicity]['Total'] += $count;
                $structured[$district]['Total'][$gender] += $count;
                $structured[$district]['Total']['Total'] += $count;
            }
        }
       
        return $structured;
    } catch (Exception $e) {
        error_log('Error in getDistrictEthnicityCountsStructured: ' . $e->getMessage());
        return array();
    }
}

public function getPassRates() {
    // Return empty array if tables don't exist
    if (!$this->db->table_exists('student_session') || !$this->db->table_exists('students')) {
        return array();
    }
   
    try {
        // First get all active sections for table headers
        $sections_query = $this->db->query("SELECT id, section FROM sections WHERE is_active = 'yes' ORDER BY section");
        $all_sections = $sections_query->result_array();
       
        // Query using classes table for degree and class information
        $query = $this->db->query("
            SELECT
                c.id as class_id,
                CONCAT(c.degree, ' - ', c.class) as level,
                s.id as section_id,
                s.section as faculty,
                COUNT(CASE WHEN st.gender = 'Male' THEN 1 END) as total_male,
                COUNT(CASE WHEN st.gender = 'Male' AND ss.is_pass = 1 THEN 1 END) as passed_male,
                COUNT(CASE WHEN st.gender = 'Female' THEN 1 END) as total_female,
                COUNT(CASE WHEN st.gender = 'Female' AND ss.is_pass = 1 THEN 1 END) as passed_female,
                COUNT(st.id) as total_students,
                COUNT(CASE WHEN ss.is_pass = 1 THEN 1 END) as total_passed,
                ROUND(
                    (COUNT(CASE WHEN st.gender = 'Male' AND ss.is_pass = 1 THEN 1 END) * 100.0) /
                    NULLIF(COUNT(CASE WHEN st.gender = 'Male' THEN 1 END), 0), 2
                ) as male_pass,
                ROUND(
                    (COUNT(CASE WHEN st.gender = 'Female' AND ss.is_pass = 1 THEN 1 END) * 100.0) /
                    NULLIF(COUNT(CASE WHEN st.gender = 'Female' THEN 1 END), 0), 2
                ) as female_pass,
                ROUND(
                    (COUNT(CASE WHEN ss.is_pass = 1 THEN 1 END) * 100.0) /
                    NULLIF(COUNT(st.id), 0), 2
                ) as total_pass
            FROM students st
            INNER JOIN student_session ss ON ss.student_id = st.id
            INNER JOIN classes c ON ss.class_id = c.id
            INNER JOIN sections s ON ss.section_id = s.id
            WHERE st.is_active = 'yes'
            " . (!empty($this->current_session) ? "AND ss.session_id = " . $this->db->escape($this->current_session) : "") . "
            GROUP BY c.id, s.id
            ORDER BY c.degree, c.class, s.section
        ");
       
        if (!$query) {
            log_message('error', 'Pass rates query failed: ' . $this->db->error()['message']);
            return array();
        }
       
        $raw_data = $query->result_array();
       
        // Organize data by level
        $organized_data = array();
        foreach ($raw_data as $row) {
            $level = $row['level'];
            if (!isset($organized_data[$level])) {
                $organized_data[$level] = array();
            }
            $organized_data[$level][$row['faculty']] = array(
                'male_pass' => ($row['male_pass'] !== null) ? number_format($row['male_pass'], 2) . '%' : 'N/A',
                'female_pass' => ($row['female_pass'] !== null) ? number_format($row['female_pass'], 2) . '%' : 'N/A',
                'total_pass' => ($row['total_pass'] !== null) ? number_format($row['total_pass'], 2) . '%' : 'N/A',
                'total_male' => $row['total_male'],
                'passed_male' => $row['passed_male'],
                'total_female' => $row['total_female'],
                'passed_female' => $row['passed_female'],
                'total_students' => $row['total_students'],
                'total_passed' => $row['total_passed']
            );
        }
       
        return array(
            'data' => $organized_data,
            'sections' => $all_sections
        );
       
    } catch (Exception $e) {
        log_message('error', 'Exception in getPassRates(): ' . $e->getMessage());
        return array();
    }
}




public function getDropoutCounts() {
    if (!$this->db->table_exists('programs')) {
        return array();
    }


    $this->db->select('IFNULL(programs.title, "Not Specified") as program,
                      SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) as male_dropouts,
                      SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) as female_dropouts,
                      COUNT(*) as total_dropouts')
             ->from('students')
             ->join('student_session', 'student_session.student_id = students.id')
             ->join('programs', 'student_session.program_id = programs.id', 'left')
             ->where('student_session.session_id', $this->current_session)
             ->where('student_session.is_leave', 1) 
             ->group_by('programs.id')
             ->order_by('programs.title');
   
    $query = $this->db->get();
    return ($query) ? $query->result_array() : array();
}


public function getFacultyEthnicityCountsStructured($ethnicity_mapping = [], $standard_ethnicities = []) {
    // Use default values if not provided
    if (empty($standard_ethnicities)) {
        $standard_ethnicities = ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];
    }

    // First, get all active sections (faculties)
    $this->db->select('id, section');
    $this->db->from('sections');
    $this->db->where('is_active', 'yes');
    $sections_query = $this->db->get();

    if (!$sections_query) {
        log_message('error', 'Failed to fetch sections: ' . $this->db->last_query());
        return [];
    }

    $sections = $sections_query->result_array();
    $structured_data = [];

    foreach ($sections as $section) {
        // Get students for this section with their ethnicity information
        $this->db->select('
            st.cast as cast_raw,
            COUNT(*) as count
        ');
        $this->db->from('students st');
        $this->db->where('st.section_id', $section['id']);
        $this->db->where('st.is_active', 'yes');
        $this->db->group_by(['st.cast']);
        $counts_query = $this->db->get();

        if (!$counts_query) {
            log_message('error', 'Failed to fetch counts for section ID ' . $section['id'] . ': ' . $this->db->last_query());
            continue;
        }

        $counts = $counts_query->result_array();
        
        // Initialize the faculty entry with all ethnicities set to 0
        $faculty_key = $section['section'];
        $structured_data[$faculty_key] = [];
        foreach ($standard_ethnicities as $eth) {
            $structured_data[$faculty_key][$eth] = 0;
        }
        $structured_data[$faculty_key]['Total'] = 0;

        // Fill in the counts
        foreach ($counts as $count) {
            $ethnicity = $this->mapCastToEthnicity($count['cast_raw'], $ethnicity_mapping, $standard_ethnicities);
            $structured_data[$faculty_key][$ethnicity] += (int)$count['count'];
            $structured_data[$faculty_key]['Total'] += (int)$count['count'];
        }
    }

    return $structured_data;
}

public function getLevelGenderCounts() {
    try {
        $this->db->select('CONCAT(classes.degree, " - ", classes.class) as level,
                      SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) as female,
                      COUNT(*) as total')
             ->from('students')
             ->join('student_session', 'student_session.student_id = students.id')
             ->join('classes', 'student_session.class_id = classes.id')
             ->where('student_session.session_id', $this->current_session)
             ->where('student_session.is_active', 'yes')
             ->group_by('classes.id')
             ->order_by('classes.degree, classes.class');
       
        $query = $this->db->get();
       
        if ($query === FALSE) {
            return array();
        }
       
        return $query->result_array();
    } catch (Exception $e) {
        error_log('Error in getLevelGenderCounts: ' . $e->getMessage());
        return array();
    }
}

public function getProvinceEthnicityCounts($ethnicity_mapping = array(), $standard_ethnicities = array()) {
    try {
        $fields = $this->db->list_fields('students');
        if (!in_array('permanent_province', $fields) || !in_array('cast', $fields)) {
            return array();
        }

        // Get all provinces from states table
        $this->db->select('id, name, name_np');
        $this->db->from('states');
        $this->db->order_by('name');
        $provinces_query = $this->db->get();
        $provinces = $provinces_query->result_array();

        // Define the standard ethnicities
        $ethnicities = $standard_ethnicities ?: ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];

        // Get all student data first with LEFT JOIN
        $this->db->select('
            states.id as province_id,
            states.name as province_name,
            states.name_np as province_name_np,
            students.permanent_province as permanent_province_raw,
            students.cast as cast_raw,
            students.gender,
            COUNT(*) as count
        ');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
        
        // Try to join by ID first (using CAST to handle string/int comparison)
        // Also handle partial name matching (e.g., "Bagmati" matches "Bagmati Province")
        $this->db->join('states', '(states.id = CAST(students.permanent_province AS UNSIGNED) AND students.permanent_province != "" AND students.permanent_province + 0 = students.permanent_province) OR states.name = students.permanent_province OR states.name LIKE CONCAT(students.permanent_province, " %") OR students.permanent_province LIKE CONCAT(states.name, " %")', 'left');
        
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where_in('students.gender', ['Male', 'Female', 'male', 'female']);
        $this->db->where('students.permanent_province IS NOT NULL');
        $this->db->where('students.permanent_province !=', '');
        $this->db->where('students.permanent_province !=', '0');
        
        $this->db->group_by(['students.permanent_province', 'students.cast', 'students.gender']);
        
        $counts_query = $this->db->get();
        
        // Check if query was successful
        if ($counts_query === FALSE) {
            error_log('Database query failed. Last query: ' . $this->db->last_query());
            // Fallback to simpler query without complex JOIN
            return $this->getProvinceEthnicityCountsSimple($ethnicity_mapping, $standard_ethnicities);
        }
        
        $raw_counts = $counts_query->result_array();

        // Debug: Log the raw counts to see what we're getting
        error_log('Raw counts data: ' . print_r($raw_counts, true));
        error_log('Total raw counts: ' . count($raw_counts));

        // Structure the data in matrix format (Province x Ethnicity x Gender)
        $structured_data = [];
        
        // Process known provinces first
        foreach ($provinces as $province) {
            $province_data = [
                'province_id' => $province['id'],
                'province' => $province['name'],
                'province_np' => $province['name_np'],
                'ethnicities' => []
            ];
           
            $province_total_male = 0;
            $province_total_female = 0;
           
            // Initialize all ethnicities for this province
            foreach ($ethnicities as $ethnicity) {
                $province_data['ethnicities'][$ethnicity] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0
                ];
            }
           
            // Process raw counts for this province
            foreach ($raw_counts as $count) {
                $matches_province = false;
                
                // Check if this count belongs to this province
                if (!empty($count['province_id']) && $count['province_id'] == $province['id']) {
                    $matches_province = true;
                } elseif (empty($count['province_id']) && !empty($count['permanent_province_raw'])) {
                    // Check if raw province matches by name (exact or partial match)
                    $raw_province = strtolower(trim($count['permanent_province_raw']));
                    $state_name = strtolower(trim($province['name']));
                    
                    // Check for exact match
                    if ($raw_province == $state_name) {
                        $matches_province = true;
                    }
                    // Check if state name starts with raw province (e.g., "Bagmati Province" starts with "Bagmati")
                    else if (strpos($state_name, $raw_province) === 0) {
                        $matches_province = true;
                    }
                    // Check if raw province starts with state name (reverse case)
                    else if (strpos($raw_province, $state_name) === 0) {
                        $matches_province = true;
                    }
                }
                
                if ($matches_province) {
                    // Map the cast name to ethnicity category
                    $ethnicity = $this->mapCastToEthnicity($count['cast_raw'], $ethnicity_mapping, $standard_ethnicities);
                   
                    // Ensure the ethnicity is in our standard list
                    if (!in_array($ethnicity, $ethnicities)) {
                        $ethnicity = 'Others';
                    }
                   
                    $gender = strtolower(trim($count['gender']));
                    $count_val = (int)$count['count'];
                   
                    if ($gender == 'male') {
                        $province_data['ethnicities'][$ethnicity]['male'] += $count_val;
                        $province_total_male += $count_val;
                    } elseif ($gender == 'female') {
                        $province_data['ethnicities'][$ethnicity]['female'] += $count_val;
                        $province_total_female += $count_val;
                    }
                   
                    $province_data['ethnicities'][$ethnicity]['total'] += $count_val;
                }
            }
           
            $province_data['total_male'] = $province_total_male;
            $province_data['total_female'] = $province_total_female;
            $province_data['total'] = $province_total_male + $province_total_female;
           
            $structured_data[] = $province_data;
        }

        // Process unmatched provinces (where permanent_province doesn't match any known province)
        $unmatched_provinces = [];
        foreach ($raw_counts as $count) {
            if (empty($count['province_id']) && !empty($count['permanent_province_raw'])) {
                $province_name = trim($count['permanent_province_raw']);
                
                // Check if this province name already exists in our known provinces (with flexible matching)
                $found_in_known = false;
                $raw_province_lower = strtolower($province_name);
                foreach ($provinces as $known_province) {
                    $known_name_lower = strtolower(trim($known_province['name']));
                    // Exact match
                    if ($raw_province_lower == $known_name_lower) {
                        $found_in_known = true;
                        break;
                    }
                    // Check if known name starts with raw province (e.g., "Bagmati Province" starts with "Bagmati")
                    if (strpos($known_name_lower, $raw_province_lower) === 0) {
                        $found_in_known = true;
                        break;
                    }
                    // Check if raw province starts with known name (reverse case)
                    if (strpos($raw_province_lower, $known_name_lower) === 0) {
                        $found_in_known = true;
                        break;
                    }
                }
                
                if (!$found_in_known && !isset($unmatched_provinces[$province_name])) {
                    $unmatched_provinces[$province_name] = [
                        'province_id' => null,
                        'province' => $province_name,
                        'province_np' => $province_name,
                        'ethnicities' => [],
                        'total_male' => 0,
                        'total_female' => 0,
                        'total' => 0
                    ];
                    
                    // Initialize ethnicities for unmatched province
                    foreach ($ethnicities as $ethnicity) {
                        $unmatched_provinces[$province_name]['ethnicities'][$ethnicity] = [
                            'male' => 0,
                            'female' => 0,
                            'total' => 0
                        ];
                    }
                }
                
                if (!$found_in_known && isset($unmatched_provinces[$province_name])) {
                    $ethnicity = $this->mapCastToEthnicity($count['cast_raw'], $ethnicity_mapping, $standard_ethnicities);
                    if (!in_array($ethnicity, $ethnicities)) {
                        $ethnicity = 'Others';
                    }
                    
                    $gender = strtolower(trim($count['gender']));
                    $count_val = (int)$count['count'];
                    
                    if ($gender == 'male') {
                        $unmatched_provinces[$province_name]['ethnicities'][$ethnicity]['male'] += $count_val;
                        $unmatched_provinces[$province_name]['total_male'] += $count_val;
                    } elseif ($gender == 'female') {
                        $unmatched_provinces[$province_name]['ethnicities'][$ethnicity]['female'] += $count_val;
                        $unmatched_provinces[$province_name]['total_female'] += $count_val;
                    }
                    
                    $unmatched_provinces[$province_name]['ethnicities'][$ethnicity]['total'] += $count_val;
                    $unmatched_provinces[$province_name]['total'] = $unmatched_provinces[$province_name]['total_male'] + $unmatched_provinces[$province_name]['total_female'];
                }
            }
        }
        
        // Add unmatched provinces to the result
        foreach ($unmatched_provinces as $unmatched_province) {
            $structured_data[] = $unmatched_province;
        }

        return $structured_data;
       
    } catch (Exception $e) {
        error_log('Error in getProvinceEthnicityCounts: ' . $e->getMessage());
        return array();
    }
}

private function getProvinceEthnicityCountsSimple($ethnicity_mapping = array(), $standard_ethnicities = array()) {
    try {
        // Get all provinces from states table
        $this->db->select('id, name, name_np');
        $this->db->from('states');
        $this->db->order_by('name');
        $provinces_query = $this->db->get();
        $provinces = $provinces_query->result_array();

        // Define the standard ethnicities
        $ethnicities = $standard_ethnicities ?: ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];

        // Simple query without complex JOINs
        $this->db->select('
            students.permanent_province,
            students.cast as cast_raw,
            students.gender,
            COUNT(*) as count
        ');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id', 'inner');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->where_in('students.gender', ['Male', 'Female', 'male', 'female']);
        $this->db->where('students.permanent_province IS NOT NULL');
        $this->db->where('students.permanent_province !=', '');
        $this->db->where('students.permanent_province !=', '0');
        $this->db->group_by(['students.permanent_province', 'students.cast', 'students.gender']);
        
        $counts_query = $this->db->get();
        $raw_counts = $counts_query->result_array();

        // Create province lookup with flexible matching
        $province_lookup = [];
        foreach ($provinces as $province) {
            $province_lookup[$province['id']] = $province;
            $province_lookup[strtolower($province['name'])] = $province;
            
            // Add partial name matching (e.g., "Bagmati" for "Bagmati Province")
            $name_parts = explode(' ', strtolower($province['name']));
            if (count($name_parts) > 1) {
                // Add first part (e.g., "bagmati" for "Bagmati Province")
                $province_lookup[$name_parts[0]] = $province;
            }
        }

        // Structure the data
        $structured_data = [];
        
        // Initialize all provinces
        foreach ($provinces as $province) {
            $province_data = [
                'province_id' => $province['id'],
                'province' => $province['name'],
                'province_np' => $province['name_np'],
                'ethnicities' => [],
                'total_male' => 0,
                'total_female' => 0,
                'total' => 0
            ];
           
            foreach ($ethnicities as $ethnicity) {
                $province_data['ethnicities'][$ethnicity] = [
                    'male' => 0,
                    'female' => 0,
                    'total' => 0
                ];
            }
            $structured_data[$province['id']] = $province_data;
        }

        // Process counts
        $unmatched_provinces = [];
        foreach ($raw_counts as $count) {
            $prov_key = $count['permanent_province'];
            $province_data = null;
            
            // Try to find province by ID or name
            if (isset($province_lookup[$prov_key])) {
                $province_data = $province_lookup[$prov_key];
            } else if (isset($province_lookup[strtolower($prov_key)])) {
                $province_data = $province_lookup[strtolower($prov_key)];
            }
            
            $ethnicity = $this->mapCastToEthnicity($count['cast_raw'], $ethnicity_mapping, $standard_ethnicities);
            if (!in_array($ethnicity, $ethnicities)) {
                $ethnicity = 'Others';
            }
            
            $gender = strtolower(trim($count['gender']));
            $count_val = (int)$count['count'];
            
            if ($province_data) {
                // Found matching province
                $prov_id = $province_data['id'];
                if ($gender == 'male') {
                    $structured_data[$prov_id]['ethnicities'][$ethnicity]['male'] += $count_val;
                    $structured_data[$prov_id]['total_male'] += $count_val;
                } elseif ($gender == 'female') {
                    $structured_data[$prov_id]['ethnicities'][$ethnicity]['female'] += $count_val;
                    $structured_data[$prov_id]['total_female'] += $count_val;
                }
                $structured_data[$prov_id]['ethnicities'][$ethnicity]['total'] += $count_val;
                $structured_data[$prov_id]['total'] += $count_val;
            } else {
                // Unmatched province
                if (!isset($unmatched_provinces[$prov_key])) {
                    $unmatched_provinces[$prov_key] = [
                        'province_id' => null,
                        'province' => $prov_key,
                        'province_np' => $prov_key,
                        'ethnicities' => [],
                        'total_male' => 0,
                        'total_female' => 0,
                        'total' => 0
                    ];
                    foreach ($ethnicities as $eth) {
                        $unmatched_provinces[$prov_key]['ethnicities'][$eth] = ['male' => 0, 'female' => 0, 'total' => 0];
                    }
                }
                
                if ($gender == 'male') {
                    $unmatched_provinces[$prov_key]['ethnicities'][$ethnicity]['male'] += $count_val;
                    $unmatched_provinces[$prov_key]['total_male'] += $count_val;
                } elseif ($gender == 'female') {
                    $unmatched_provinces[$prov_key]['ethnicities'][$ethnicity]['female'] += $count_val;
                    $unmatched_provinces[$prov_key]['total_female'] += $count_val;
                }
                $unmatched_provinces[$prov_key]['ethnicities'][$ethnicity]['total'] += $count_val;
                $unmatched_provinces[$prov_key]['total'] += $count_val;
            }
        }
        
        // Convert to indexed array and add unmatched
        $result = array_values($structured_data);
        foreach ($unmatched_provinces as $unmatched) {
            $result[] = $unmatched;
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Error in getProvinceEthnicityCountsSimple: ' . $e->getMessage());
        return array();
    }
}


private function mapCastToEthnicity($cast_raw, $ethnicity_mapping, $standard_ethnicities) {
    // Handle null, empty, or zero values
    if (empty($cast_raw) || $cast_raw === '' || $cast_raw === null || $cast_raw === '0') {
        return 'Others';
    }
   
    $cast_name = trim((string)$cast_raw);
    
    $cast_name_lower = strtolower($cast_name);

    foreach ($standard_ethnicities as $ethnicity) {
        if (strtolower($ethnicity) === $cast_name_lower) {
            return $ethnicity;
        }
    }

    $cast_variations = [
        'brahman' => 'Brahmin',
        'brahmins' => 'Brahmin',
        'chhetris' => 'Chhetri',
        'dalits' => 'Dalit',
        'janajatis' => 'Janajati',
        'madhesis' => 'Madhesi',
        'muslims' => 'Muslim',
        'tharus' => 'Tharu',
    ];
    
    // Check variations
    if (isset($cast_variations[$cast_name_lower])) {
        return $cast_variations[$cast_name_lower];
    }
   
    // If cast name is not found in any category, categorize as Others
    return 'Others';
}


public function getGraduatedStudentsCounts($ethnicity_mapping = array(), $standard_ethnicities = array()) {
    try {
        // Check if ethnicity field exists in students table
        $fields = $this->db->list_fields('students');
        if (!in_array('ethnicity', $fields) && !in_array('cast', $fields)) {
            return array();
        }


        // Determine which field to use (cast or ethnicity)
        $ethnicity_field = in_array('ethnicity', $fields) ? 'ethnicity' : 'cast';


        $this->db->select('sections.section as faculty,
                      CONCAT(programs.title, " (", classes.degree, " - ", classes.class, ")") as program,
                      students.' . $ethnicity_field . ' as cast_raw,
                      SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) as female,
                      COUNT(*) as total')
             ->from('students')
             ->join('student_session', 'student_session.student_id = students.id')
             ->join('sections', 'sections.id = student_session.section_id')
             ->join('programs', 'programs.id = student_session.program_id', 'left')
             ->join('classes', 'classes.id = student_session.class_id')
             ->where('student_session.session_id', $this->current_session)
             ->where('student_session.is_graduated', 1)
             ->group_by('sections.id, programs.id, classes.id, students.' . $ethnicity_field)
             ->order_by('sections.section, programs.title, classes.degree, classes.class, students.' . $ethnicity_field);


        $query = $this->db->get();


        if ($query === FALSE) {
            return array();
        }


        $results = $query->result_array();
        $processed_results = array();


        foreach ($results as $row) {
            // Map the cast/ethnicity ID to name
            $ethnicity = $this->mapCastToEthnicity($row['cast_raw'], $ethnicity_mapping, $standard_ethnicities);
           
            $processed_row = array(
                'faculty' => $row['faculty'],
                'program' => $row['program'],
                'ethnicity' => $ethnicity,
                'male' => $row['male'],
                'female' => $row['female'],
                'total' => $row['total']
            );
           
            $processed_results[] = $processed_row;
        }


        return $processed_results;
    } catch (Exception $e) {
        error_log('Error in getGraduatedStudentsCounts: ' . $e->getMessage());
        return array();
    }
}




public function getStaffDetailCountsByPosition($standard_positions = []) {
    // Check if staff table exists
    if (!$this->db->table_exists('staff')) {
        return array();
    }

    // Get staff details with designation from staff_designation table
    $this->db->select([
            's.name',
            'sd.designation AS position',
            's.qualification',
            'COALESCE(s.contract_type, s.employee_type) AS job_type',
            's.date_of_joining AS appointment_date'
        ])
        ->from('staff s')
        ->join('staff_designation sd', 's.designation = sd.id', 'left')
        ->where('s.is_active', 1)
        ->order_by('sd.designation, s.name');

    $query = $this->db->get();
    $staff_data = ($query) ? $query->result_array() : array();
   
    // Initialize the result array with all standard positions
    $result = array();
    foreach ($standard_positions as $position) {
        $result[$position] = array();
    }
   
    // Group staff by position
    foreach ($staff_data as $staff) {
        $position = $staff['position'];
       
        // If position is empty (due to left join) or not in standard positions, categorize as 'Others'
        if (empty($position) || !in_array($position, $standard_positions)) {
            $position = 'Others';
        }
       
        // Ensure the position key exists in result
        if (!isset($result[$position])) {
            $result[$position] = array();
        }
       
        $result[$position][] = $staff;
    }
   
    return $result;
}


public function getActiveStaffCount() {
    if (!$this->db->table_exists('staff')) {
        return 0;
    }
    return $this->db->where('is_active', 1)->count_all_results('staff');
}


public function getStaffEthnicityCounts() {
    if (!$this->db->table_exists('staff')) {
        return array();
    }




    // Get teaching staff counts
    $this->db->select('IFNULL(staff.caste, "Others") as ethnicity,
                      SUM(CASE WHEN staff.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN staff.gender = "Female" THEN 1 ELSE 0 END) as female,
                      COUNT(*) as total,
                      "Teaching" as staff_type')
             ->from('staff')
             ->where('staff.is_active', 1)
             ->where('staff.non_teaching_type', 0)
             ->group_by('staff.caste')
             ->order_by('total', 'DESC');
   
    $teaching_query = $this->db->get();
    $teaching_results = ($teaching_query) ? $teaching_query->result_array() : array();




    // Get non-teaching staff counts
    $this->db->select('IFNULL(staff.caste, "Others") as ethnicity,
                      SUM(CASE WHEN staff.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN staff.gender = "Female" THEN 1 ELSE 0 END) as female,
                      COUNT(*) as total,
                      "Non-Teaching" as staff_type')
             ->from('staff')
             ->where('staff.is_active', 1)
             ->where('staff.non_teaching_type', 1)
             ->group_by('staff.caste')
             ->order_by('total', 'DESC');
   
    $non_teaching_query = $this->db->get();
    $non_teaching_results = ($non_teaching_query) ? $non_teaching_query->result_array() : array();




    return array_merge($teaching_results, $non_teaching_results);
}




public function getStaffPositionCounts($standard_positions = []) {
    if (!$this->db->table_exists('staff')) {
        return array();
    }

    // Get raw data with designation from staff_designation table
    $this->db->select('COALESCE(sd.designation, "Not Specified") as position,
                      COALESCE(s.contract_type, s.employee_type, "Not Specified") as job_type,
                      SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) as male,
                      SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) as female,
                      SUM(CASE WHEN s.gender NOT IN ("Male", "Female") OR s.gender IS NULL THEN 1 ELSE 0 END) as other,
                      COUNT(*) as total')
             ->from('staff s')
             ->join('staff_designation sd', 's.designation = sd.id', 'left')
             ->where('s.is_active', 1)
             ->group_by('sd.designation, s.contract_type, s.employee_type')
             ->order_by('total', 'DESC');

    $query = $this->db->get();
    $raw_results = ($query) ? $query->result_array() : array();

    // If no standard positions provided, return raw results
    if (empty($standard_positions)) {
        return $raw_results;
    }

    // Process results to standardize positions
    $standardized_results = [];
   
    foreach ($raw_results as $row) {
        $original_position = $row['position'];
        $standardized_position = 'Others'; // Default fallback
       
        // Check if position matches any standard position (case-insensitive)
        foreach ($standard_positions as $std_position) {
            if (strcasecmp(trim($original_position), trim($std_position)) === 0) {
                $standardized_position = $std_position;
                break;
            }
        }
       
        // If position is "Not Specified", keep it as is
        if ($original_position === 'Not Specified') {
            $standardized_position = 'Not Specified';
        }
       
        // Create a unique key for grouping
        $key = $standardized_position . '|' . $row['job_type'];
       
        // Initialize or accumulate counts
        if (!isset($standardized_results[$key])) {
            $standardized_results[$key] = [
                'position' => $standardized_position,
                'job_type' => $row['job_type'],
                'male' => 0,
                'female' => 0,
                'other' => 0,
                'total' => 0
            ];
        }
       
        $standardized_results[$key]['male'] += (int)$row['male'];
        $standardized_results[$key]['female'] += (int)$row['female'];
        $standardized_results[$key]['other'] += (int)$row['other'];
        $standardized_results[$key]['total'] += (int)$row['total'];
    }
   
    // Convert to indexed array and sort by total descending
    $final_results = array_values($standardized_results);
    usort($final_results, function($a, $b) {
        return $b['total'] - $a['total'];
    });
   
    return $final_results;
}




public function getProgramEthnicityCountsStructured($ethnicity_mapping = [], $standard_ethnicities = []) {
    // Use default values if not provided
    if (empty($standard_ethnicities)) {
        $standard_ethnicities = ['Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others'];
    }

    try {
        $this->db->select('
            programs.title as program,
            students.cast as cast_raw,
            students.gender,
            COUNT(*) as count
        ');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('programs', 'programs.id = student_session.program_id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.is_active', 'yes');
        $this->db->group_by('programs.id, students.cast, students.gender');
        $this->db->order_by('programs.title');

        $query = $this->db->get();
        
        if (!$query) {
            log_message('error', 'Failed to fetch program ethnicity counts: ' . $this->db->last_query());
            return [];
        }

        $results = $query->result_array();
        $structured_data = [];

        foreach ($results as $row) {
            $program = $row['program'];
            $cast_raw = $row['cast_raw'];
            $gender = strtolower($row['gender']);
            $count = (int)$row['count'];

            // Map the cast to ethnicity
            $ethnicity = $this->mapCastToEthnicity($cast_raw, $ethnicity_mapping, $standard_ethnicities);

            // Initialize program if not exists
            if (!isset($structured_data[$program])) {
                $structured_data[$program] = [
                    'ethnicities' => [],
                    'gender' => ['male' => 0, 'female' => 0, 'total' => 0]
                ];
                foreach ($standard_ethnicities as $eth) {
                    $structured_data[$program]['ethnicities'][$eth] = [
                        'male' => 0,
                        'female' => 0
                    ];
                }
            }

            // Add counts
            if ($gender === 'male' || $gender === 'female') {
                $structured_data[$program]['ethnicities'][$ethnicity][$gender] += $count;
                $structured_data[$program]['gender'][$gender] += $count;
                $structured_data[$program]['gender']['total'] += $count;
            }
        }

        return $structured_data;
    } catch (Exception $e) {
        error_log('Error in getProgramEthnicityCountsStructured: ' . $e->getMessage());
        return [];
    }
}


public function getCampusEnrollmentSummary($ethnicity_mapping = array(), $standard_ethnicities = array()) {
    try {
        $fields = $this->db->list_fields('students');
        $has_cast      = in_array('cast', $fields);
        $has_ethnicity = in_array('ethnicity', $fields);
        if (!$has_cast && !$has_ethnicity) {
            log_message('error', 'getCampusEnrollmentSummary: students table has neither cast nor ethnicity column');
            return array();
        }

        if ($has_ethnicity && $has_cast) {
            $raw_sel = 'IF(LENGTH(TRIM(COALESCE(st.ethnicity, ""))) > 0, st.ethnicity, st.cast)';
        } elseif ($has_ethnicity) {
            $raw_sel = 'st.ethnicity';
        } else {
            $raw_sel = 'st.cast';
        }

        $gender_case = 'CASE
                WHEN LOWER(TRIM(st.gender)) IN ("male", "m") THEN "male"
                WHEN LOWER(TRIM(st.gender)) IN ("female", "f") THEN "female"
                ELSE "other"
            END';

        $this->db->select('
            c.class as level,
            s.section as faculty,
            IFNULL(p.title, "Unknown Program") as program,
            ' . $gender_case . ' as gender_norm,
            ' . $raw_sel . ' as cast_raw,
            COUNT(DISTINCT st.id) as count
        ', false);
        $this->db->from('students st');
        $this->db->join('student_session ss', 'ss.student_id = st.id', 'inner');
        $this->db->where('ss.session_id', (int) $this->current_session);
        $this->db->where('ss.is_active', 'yes');
        $this->db->where('st.is_active', 'yes');
        $this->db->join('classes c', 'c.id = ss.class_id', 'left');
        $this->db->join('sections s', 's.id = ss.section_id', 'left');
        $this->db->join('class_sections cs', 'cs.class_id = ss.class_id AND cs.section_id = ss.section_id', 'left');
        $this->db->join('programs p', 'p.id = COALESCE(NULLIF(ss.program_id, 0), cs.program_id)', 'left', false);

        $this->db->group_by('c.class, s.section, p.title, ' . $gender_case . ', ' . $raw_sel, false);
        $this->db->order_by('c.class, s.section, p.title, ' . $raw_sel . ', ' . $gender_case, false);

        $query = $this->db->get();

        if (!$query) {
            log_message('error', 'getCampusEnrollmentSummary query failed: ' . $this->db->error()['message']);
            return array();
        }

        $results = $query->result_array();

        $structured_data = array();

        if (empty($standard_ethnicities)) {
            $standard_ethnicities = array('Brahmin', 'Chhetri', 'Dalit', 'Janajati', 'Madhesi', 'Muslim', 'Tharu', 'Others');
        }

        foreach ($results as $row) {
            $level   = !empty($row['level']) ? $row['level'] : 'Unknown Level';
            $faculty = !empty($row['faculty']) ? $row['faculty'] : 'Unknown Faculty';
            $program = !empty($row['program']) ? $row['program'] : 'Unknown Program';
            $gender  = isset($row['gender_norm']) ? $row['gender_norm'] : 'other';
            $cast_raw = isset($row['cast_raw']) ? $row['cast_raw'] : '';
            $count   = (int) $row['count'];

            if ($gender !== 'male' && $gender !== 'female') {
                continue;
            }

            $ethnicity = $this->mapCastToEthnicity($cast_raw, $ethnicity_mapping, $standard_ethnicities);
            if (!in_array($ethnicity, $standard_ethnicities)) {
                $ethnicity = 'Others';
            }

            if (!isset($structured_data[$level])) {
                $structured_data[$level] = array();
            }
            if (!isset($structured_data[$level][$faculty])) {
                $structured_data[$level][$faculty] = array();
            }
            if (!isset($structured_data[$level][$faculty][$program])) {
                $structured_data[$level][$faculty][$program] = array();
                foreach ($standard_ethnicities as $eth) {
                    $structured_data[$level][$faculty][$program][$eth] = array(
                        'male' => 0,
                        'female' => 0,
                        'total' => 0,
                    );
                }
            }

            if ($gender === 'male') {
                $structured_data[$level][$faculty][$program][$ethnicity]['male'] += $count;
            } elseif ($gender === 'female') {
                $structured_data[$level][$faculty][$program][$ethnicity]['female'] += $count;
            }
            $structured_data[$level][$faculty][$program][$ethnicity]['total'] += $count;
        }

        return $structured_data;

    } catch (Exception $e) {
        log_message('error', 'Error in getCampusEnrollmentSummary: ' . $e->getMessage());
        return array();
    }
}

/**
 * Active students in the configured current session (UGC headcount scope).
 *
 * @return int
 */
public function countActiveStudentsCurrentSession()
{
    $this->db->select('COUNT(DISTINCT st.id) as c', false);
    $this->db->from('students st');
    $this->db->join('student_session ss', 'ss.student_id = st.id', 'inner');
    $this->db->where('ss.session_id', (int) $this->current_session);
    $this->db->where('ss.is_active', 'yes');
    $this->db->where('st.is_active', 'yes');
    $q = $this->db->get();
    if (!$q || $q->num_rows() === 0) {
        return 0;
    }
    return (int) $q->row()->c;
}

/**
 * Same definition as admin HEMIS "Synced": hemis_student_id set and no sync error.
 *
 * @return int|null
 */
public function countHemisSyncedStudentsCurrentSession()
{
    if (!$this->db->field_exists('hemis_student_id', 'students')) {
        return null;
    }
    $this->db->select('COUNT(DISTINCT st.id) as c', false);
    $this->db->from('students st');
    $this->db->join('student_session ss', 'ss.student_id = st.id', 'inner');
    $this->db->where('ss.session_id', (int) $this->current_session);
    $this->db->where('ss.is_active', 'yes');
    $this->db->where('st.is_active', 'yes');
    $this->db->where('st.hemis_student_id IS NOT NULL', null, false);
    $this->db->where('st.hemis_student_id >', 0);
    $this->db->where('(st.hemis_sync_error IS NULL OR TRIM(st.hemis_sync_error) = "")', null, false);
    $q = $this->db->get();
    if (!$q || $q->num_rows() === 0) {
        return 0;
    }
    return (int) $q->row()->c;
}

/**
 * Sum of matrix cells (male+female by ethnicity) for Campus Enrollment Summary.
 *
 * @param array $structured_data
 * @return int
 */
public function sumCampusEnrollmentMatrixHeadcount(array $structured_data)
{
    $sum = 0;
    foreach ($structured_data as $faculties) {
        foreach ($faculties as $programs) {
            foreach ($programs as $ethnicities) {
                foreach ($ethnicities as $cell) {
                    if (is_array($cell) && isset($cell['total'])) {
                        $sum += (int) $cell['total'];
                    }
                }
            }
        }
    }
    return $sum;
}

/**
 * Male / female totals from the same matrix as Campus Enrollment Summary (GPI numerator/denominator).
 *
 * @param array $structured_data Output of getCampusEnrollmentSummary()
 * @return array{male:int,female:int}
 */
public function sumCampusEnrollmentMaleFemale(array $structured_data)
{
    $male = 0;
    $female = 0;
    foreach ($structured_data as $faculties) {
        foreach ($faculties as $programs) {
            foreach ($programs as $ethnicities) {
                foreach ($ethnicities as $cell) {
                    if (is_array($cell)) {
                        $male += isset($cell['male']) ? (int) $cell['male'] : 0;
                        $female += isset($cell['female']) ? (int) $cell['female'] : 0;
                    }
                }
            }
        }
    }
    return array('male' => $male, 'female' => $female);
}

/**
 * Compute UGC / HEMIS blocking issues for one session row (shared by report + counts).
 *
 * @param array $r
 * @param bool  $has_eth
 * @param bool  $has_cast
 * @param bool  $has_hemis_id
 * @param bool  $has_hemis_err
 * @return array<int, string>
 */
private function ugc_compute_data_quality_issues(array $r, $has_eth, $has_cast, $has_hemis_id, $has_hemis_err)
{
    $issues = array();

    $g = isset($r['gender']) ? strtolower(trim((string) $r['gender'])) : '';
    if ($g !== 'male' && $g !== 'female') {
        $issues[] = 'Gender missing or not male/female (HEMIS gender)';
    }

    $eth_ok = false;
    if ($has_eth && isset($r['ethnicity']) && trim((string) $r['ethnicity']) !== '') {
        $eth_ok = true;
    }
    if ($has_cast && isset($r['cast']) && trim((string) $r['cast']) !== '' && trim((string) $r['cast']) !== '0') {
        $eth_ok = true;
    }
    if (!$eth_ok) {
        $issues[] = 'Ethnicity / caste missing (HEMIS ethnicity)';
    }

    $prog_ok = (!empty($r['session_program_id']) && (int) $r['session_program_id'] > 0)
        || (!empty($r['cs_program_id']) && (int) $r['cs_program_id'] > 0);
    if (!$prog_ok && (string) $r['program_title'] === '') {
        $issues[] = 'Program not resolved for session (HEMIS program)';
    }

    $ay = isset($r['admission_year']) ? (int) $r['admission_year'] : 0;
    if ($ay < 1) {
        $issues[] = 'Admission / academic year missing (HEMIS admissionYearId)';
    }

    if ($has_hemis_id && (empty($r['hemis_student_id']) || (int) $r['hemis_student_id'] < 1)) {
        $issues[] = 'Not synced to HEMIS (no hemis_student_id)';
    }
    if ($has_hemis_err && !empty($r['hemis_sync_error']) && trim((string) $r['hemis_sync_error']) !== '') {
        $issues[] = 'HEMIS sync error: ' . substr(trim((string) $r['hemis_sync_error']), 0, 200);
    }

    return $issues;
}

/**
 * Active session students with at least one UGC data-quality / HEMIS alignment gap (full scan).
 *
 * @return int
 */
public function countUgcDataQualityStudentsWithGaps()
{
    $rows = $this->ugc_data_quality_session_student_rows(null);
    if (empty($rows)) {
        return 0;
    }
    $fields = $this->db->list_fields('students');
    $has_eth = in_array('ethnicity', $fields);
    $has_cast = in_array('cast', $fields);
    $has_hemis_id = in_array('hemis_student_id', $fields);
    $has_hemis_err = in_array('hemis_sync_error', $fields);
    $n = 0;
    foreach ($rows as $r) {
        $issues = $this->ugc_compute_data_quality_issues($r, $has_eth, $has_cast, $has_hemis_id, $has_hemis_err);
        if (!empty($issues)) {
            $n++;
        }
    }
    return $n;
}

/**
 * Students in current session with no UGC DQ issues (profile ready for HEMIS push).
 *
 * @return int
 */
public function countUgcDataQualityStudentsReadyForSync()
{
    $total = $this->countActiveStudentsCurrentSession();
    $gaps = $this->countUgcDataQualityStudentsWithGaps();
    return max(0, $total - $gaps);
}

/**
 * @param int|null $limit null = all rows (for counts)
 * @return array
 */
private function ugc_data_quality_session_student_rows($limit = null)
{
    $fields = $this->db->list_fields('students');
    $has_eth = in_array('ethnicity', $fields);
    $has_cast = in_array('cast', $fields);
    $has_hemis_id = in_array('hemis_student_id', $fields);
    $has_hemis_err = in_array('hemis_sync_error', $fields);

    $sel = 'st.id, st.admission_no, st.firstname, st.middlename, st.lastname, st.gender, st.admission_year, '
        . 'IFNULL(p.title, "") as program_title, ss.program_id as session_program_id, cs.program_id as cs_program_id';
    if ($has_eth) {
        $sel .= ', st.ethnicity';
    }
    if ($has_cast) {
        $sel .= ', st.cast';
    }
    if ($has_hemis_id) {
        $sel .= ', st.hemis_student_id';
    }
    if ($has_hemis_err) {
        $sel .= ', st.hemis_sync_error';
    }

    $this->db->select($sel, false);
    $this->db->from('students st');
    $this->db->join('student_session ss', 'ss.student_id = st.id', 'inner');
    $this->db->where('ss.session_id', (int) $this->current_session);
    $this->db->where('ss.is_active', 'yes');
    $this->db->where('st.is_active', 'yes');
    $this->db->join('class_sections cs', 'cs.class_id = ss.class_id AND cs.section_id = ss.section_id', 'left');
    $this->db->join('programs p', 'p.id = COALESCE(NULLIF(ss.program_id, 0), cs.program_id)', 'left', false);
    $this->db->order_by('st.admission_no', 'ASC');
    if ($limit !== null) {
        $this->db->limit((int) $limit);
    }
    $q = $this->db->get();
    if (!$q) {
        return array();
    }
    return $q->result_array();
}

/**
 * Students blocking 100% UGC data / HEMIS alignment (current session, active).
 *
 * @param int $limit
 * @return array<int, array{row: array, issues: array<int, string>}>
 */
public function getUgcDataQualityReportRows($limit = 800)
{
    $limit = (int) $limit;
    if ($limit < 1) {
        $limit = 500;
    }
    if ($limit > 2000) {
        $limit = 2000;
    }

    $fields = $this->db->list_fields('students');
    $has_eth = in_array('ethnicity', $fields);
    $has_cast = in_array('cast', $fields);
    $has_hemis_id = in_array('hemis_student_id', $fields);
    $has_hemis_err = in_array('hemis_sync_error', $fields);

    $rows = $this->ugc_data_quality_session_student_rows($limit);

    $out = array();
    foreach ($rows as $r) {
        $issues = $this->ugc_compute_data_quality_issues($r, $has_eth, $has_cast, $has_hemis_id, $has_hemis_err);
        if (!empty($issues)) {
            $out[] = array('row' => $r, 'issues' => $issues);
        }
    }

    return $out;
}

public function getCountByGender($gender) {
    // Active enrolled students in current session (exclude graduated/leave).
    $gender = strtolower(trim((string) $gender));
    $this->db->from('students');
    $this->db->join('student_session', 'students.id = student_session.student_id', 'inner');
    $this->db->where('LOWER(TRIM(students.gender)) = ' . $this->db->escape($gender), null, false);
    $this->db->where('students.is_active', 'yes');
    $this->db->where('student_session.is_active', 'yes');
    $this->db->where('student_session.session_id', $this->current_session);
    $this->db->where('COALESCE(student_session.is_graduated, 0) = 0', null, false);
    $this->db->where('COALESCE(student_session.is_leave, 0) = 0', null, false);
    return $this->db->count_all_results();
}

/**
 * Active enrolled students for current session filtered by gender.
 *
 * @param string $gender male|female
 * @return array
 */
public function getActiveStudentsByGender($gender)
{
    $gender = strtolower(trim((string) $gender));
    $this->db->select('
        students.id,
        students.admission_no,
        students.roll_no,
        students.firstname,
        students.middlename,
        students.lastname,
        students.gender,
        students.mobileno,
        students.email,
        classes.class,
        sections.section,
        programs.title as program_name,
        student_session.id as student_session_id
    ');
    $this->db->from('students');
    $this->db->join('student_session', 'students.id = student_session.student_id', 'inner');
    $this->db->join('classes', 'student_session.class_id = classes.id', 'left');
    $this->db->join('sections', 'student_session.section_id = sections.id', 'left');
    $this->db->join('programs', 'student_session.program_id = programs.id', 'left');
    $this->db->where('LOWER(TRIM(students.gender)) = ' . $this->db->escape($gender), null, false);
    $this->db->where('students.is_active', 'yes');
    $this->db->where('student_session.is_active', 'yes');
    $this->db->where('student_session.session_id', $this->current_session);
    $this->db->where('COALESCE(student_session.is_graduated, 0) = 0', null, false);
    $this->db->where('COALESCE(student_session.is_leave, 0) = 0', null, false);
    $this->db->order_by('students.firstname', 'ASC');
    $this->db->group_by('students.id');
    $query = $this->db->get();
    return $query === false ? array() : $query->result_array();
}

public function getEnrollmentStatusByYear($years_limit = 5)
{
    // Backwards-compatible alias: chart is by academic session, not calendar year.
    return $this->getEnrollmentStatusBySession($years_limit);
}

/**
 * Enrollment / graduated / dropout counts grouped by academic session label.
 *
 * @param int $sessions_limit
 * @return array
 */
public function getEnrollmentStatusBySession($sessions_limit = 5)
{
    $sessions_limit = (int) $sessions_limit;
    if ($sessions_limit < 1) {
        $sessions_limit = 5;
    }
    if ($sessions_limit > 15) {
        $sessions_limit = 15;
    }

    // Recent sessions that actually have student_session rows (skip empty future shells).
    // Sort by approximate Gregorian year so BS labels (e.g. 2082-83) align with AD sessions.
    $sql = "SELECT
                ses.id AS session_id,
                ses.session AS session_name,
                SUM(
                    CASE
                        WHEN ss.is_active = 'yes'
                             AND COALESCE(ss.is_graduated, 0) = 0
                             AND COALESCE(ss.is_leave, 0) = 0
                             AND s.is_active = 'yes'
                        THEN 1 ELSE 0
                    END
                ) AS enrolled,
                SUM(CASE WHEN COALESCE(ss.is_graduated, 0) = 1 THEN 1 ELSE 0 END) AS graduated,
                SUM(CASE WHEN COALESCE(ss.is_leave, 0) = 1 THEN 1 ELSE 0 END) AS dropout
            FROM sessions ses
            INNER JOIN student_session ss ON ss.session_id = ses.id
            LEFT JOIN students s ON s.id = ss.student_id
            GROUP BY ses.id, ses.session
            HAVING (enrolled + graduated + dropout) > 0
            ORDER BY
                CASE
                    WHEN CAST(SUBSTRING_INDEX(ses.session, '-', 1) AS UNSIGNED) >= 2070
                    THEN CAST(SUBSTRING_INDEX(ses.session, '-', 1) AS UNSIGNED) - 57
                    ELSE CAST(SUBSTRING_INDEX(ses.session, '-', 1) AS UNSIGNED)
                END DESC,
                ses.id DESC
            LIMIT " . (int) $sessions_limit;

    $query = $this->db->query($sql);
    if ($query === false) {
        log_message('error', 'getEnrollmentStatusBySession failed: ' . $this->db->last_query());
        return array();
    }

    $rows = $query->result_array();
    // Oldest → newest for readable left-to-right chart axis
    $rows = array_reverse($rows);

    $result = array();
    foreach ($rows as $row) {
        $label = !empty($row['session_name']) ? (string) $row['session_name'] : ('Session #' . (int) $row['session_id']);
        $result[] = array(
            'year'         => $label, // kept for existing chart JS key
            'session'      => $label,
            'session_id'   => (int) $row['session_id'],
            'enrolled'     => (int) $row['enrolled'],
            'graduated'    => (int) $row['graduated'],
            'dropout'      => (int) $row['dropout'],
        );
    }

    return $result;
}


public function getStudentTeacherRatioData() {
    // Get total active students with active student_session for current session
    $this->db->select('COUNT(DISTINCT s.id) as student_count');
    $this->db->from('students s');
    $this->db->join('student_session ss', 's.id = ss.student_id', 'inner');
    $this->db->where('s.is_active', 'yes');
    $this->db->where('ss.is_active', 'yes');
    $this->db->where('ss.session_id', $this->current_session);
    $this->db->where('COALESCE(ss.is_graduated, 0) = 0', null, false);
    $this->db->where('COALESCE(ss.is_leave, 0) = 0', null, false);
   
    $query = $this->db->get();
    $result = $query->row();
    $total_students = $result ? $result->student_count : 0;
   
    // Get total teachers (existing logic)
    $this->db->select('COUNT(DISTINCT s.id) as teacher_count');
    $this->db->from('staff s');
    $this->db->join('staff_roles sr', 's.id = sr.staff_id', 'inner');
    $this->db->join('roles r', 'sr.role_id = r.id', 'inner');
    $this->db->where('r.id', 2);
   
    $query = $this->db->get();
    $result = $query->row();
    $total_teachers = $result ? $result->teacher_count : 0;
   
    return [
        'total_students' => $total_students,
        'total_teachers' => $total_teachers,
        'ratio' => ($total_teachers > 0) ? round($total_students / $total_teachers, 2) : 0
    ];
}



public function getStudentCountByGenderAndProgram() 
{
    // Prefer student_session class/program (current enrollment) over denormalized students.* fields.
    $this->db->select('
        COALESCE(programs.title, "Unassigned") as program_name,
        COALESCE(classes.class, "Unassigned") as class_name,
        COALESCE(classes.degree, "") as degree_name,
        CONCAT(
            COALESCE(programs.title, "Unassigned"),
            " - ",
            COALESCE(classes.class, "Unassigned"),
            CASE
                WHEN classes.degree IS NULL OR classes.degree = "" THEN ""
                ELSE CONCAT(" (", classes.degree, ")")
            END
        ) as full_program_name,
        SUM(CASE WHEN LOWER(students.gender) = "male" THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN LOWER(students.gender) = "female" THEN 1 ELSE 0 END) as female_count
    ', false);
    $this->db->from('students');
    $this->db->join('student_session', 'students.id = student_session.student_id', 'inner');
    $this->db->join('programs', 'student_session.program_id = programs.id', 'left');
    $this->db->join('classes', 'student_session.class_id = classes.id', 'left');
    
    $this->db->where('students.is_active', 'yes');
    $this->db->where('student_session.is_active', 'yes');
    $this->db->where('student_session.session_id', $this->current_session);
    $this->db->where('COALESCE(student_session.is_graduated, 0) = 0', null, false);
    $this->db->where('COALESCE(student_session.is_leave, 0) = 0', null, false);
    
    $this->db->group_by('programs.id, classes.id');
    $this->db->order_by('programs.title, classes.class', 'ASC');
    
    $query = $this->db->get();

    if ($query === false) {
        log_message('error', 'Database query failed in getStudentCountByGenderAndProgram: ' . $this->db->last_query());
        return array(); 
    }
    
    return $query->result_array();
}



public function searchByClassSectionProgram($class_id, $section_id, $program_id) {
    $this->db->select('students.id, students.firstname, students.middlename, students.lastname, students.admission_no')
             ->from('students')
             ->join('student_session', 'student_session.student_id = students.id')
             ->join('classes', 'classes.id = student_session.class_id')
             ->join('sections', 'sections.id = student_session.section_id')
             ->join('programs', 'programs.id = student_session.program_id', 'left')
             ->where('student_session.class_id', $class_id)
             ->where('student_session.section_id', $section_id)
             ->where('student_session.program_id', $program_id)
             ->where('student_session.session_id', $this->current_session)
             ->where('student_session.is_active', 'yes')
             ->where('students.is_active', 'yes')
             ->order_by('students.firstname');
    
    return $this->db->get()->result_array();
}


    
    public function student_exists($student_id) 
    {
        $this->db->where('id', $student_id);
        return $this->db->count_all_results('students') > 0;
    }


}



