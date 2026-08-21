<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Generatelibrarycard_model extends CI_model
{
    protected $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->ensureTable();
    }

    private function ensureTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `library_card` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) DEFAULT NULL,
            `school_name` varchar(255) DEFAULT NULL,
            `school_address` text,
            `header_color` varchar(50) DEFAULT NULL,
            `background` varchar(255) DEFAULT NULL,
            `logo` varchar(255) DEFAULT NULL,
            `sign_image` varchar(255) DEFAULT NULL,
            `enable_library_card_no` tinyint(1) NOT NULL DEFAULT '0',
            `enable_member_type` tinyint(1) NOT NULL DEFAULT '0',
            `enable_name` tinyint(1) NOT NULL DEFAULT '0',
            `enable_admission_no` tinyint(1) NOT NULL DEFAULT '0',
            `enable_employee_id` tinyint(1) NOT NULL DEFAULT '0',
            `enable_class` tinyint(1) NOT NULL DEFAULT '0',
            `enable_section` tinyint(1) NOT NULL DEFAULT '0',
            `enable_program` tinyint(1) NOT NULL DEFAULT '0',
            `enable_phone` tinyint(1) NOT NULL DEFAULT '0',
            `enable_dob` tinyint(1) NOT NULL DEFAULT '0',
            `enable_library_barcode` tinyint(1) NOT NULL DEFAULT '0',
            `enable_vertical_card` tinyint(1) NOT NULL DEFAULT '0',
            `card_width_mm` decimal(6,2) NOT NULL DEFAULT '86.00',
            `card_height_mm` decimal(6,2) NOT NULL DEFAULT '54.00',
            `header_offset_x_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `header_offset_y_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `photo_offset_x_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `photo_offset_y_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `details_offset_x_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `details_offset_y_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `signature_offset_x_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `signature_offset_y_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `barcode_offset_x_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `barcode_offset_y_mm` decimal(6,2) NOT NULL DEFAULT '0.00',
            `header_align` varchar(20) NOT NULL DEFAULT 'left',
            `content_layout` varchar(20) NOT NULL DEFAULT 'same_row',
            `photo_align` varchar(20) NOT NULL DEFAULT 'left',
            `details_align` varchar(20) NOT NULL DEFAULT 'left',
            `signature_align` varchar(20) NOT NULL DEFAULT 'right',
            `barcode_align` varchar(20) NOT NULL DEFAULT 'right',
            `status` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $this->db->query($sql);

        if (!$this->db->field_exists('card_width_mm', 'library_card')) {
            $this->db->query("ALTER TABLE `library_card` ADD `card_width_mm` decimal(6,2) NOT NULL DEFAULT '86.00' AFTER `enable_vertical_card`");
        }
        if (!$this->db->field_exists('card_height_mm', 'library_card')) {
            $this->db->query("ALTER TABLE `library_card` ADD `card_height_mm` decimal(6,2) NOT NULL DEFAULT '54.00' AFTER `card_width_mm`");
        }
        $offset_columns = array(
            'header_offset_x_mm', 'header_offset_y_mm',
            'photo_offset_x_mm', 'photo_offset_y_mm',
            'details_offset_x_mm', 'details_offset_y_mm',
            'signature_offset_x_mm', 'signature_offset_y_mm',
            'barcode_offset_x_mm', 'barcode_offset_y_mm',
        );
        foreach ($offset_columns as $col) {
            if (!$this->db->field_exists($col, 'library_card')) {
                $this->db->query("ALTER TABLE `library_card` ADD `" . $col . "` decimal(6,2) NOT NULL DEFAULT '0.00' AFTER `card_height_mm`");
            }
        }
        $easy_columns = array(
            'header_align' => "ALTER TABLE `library_card` ADD `header_align` varchar(20) NOT NULL DEFAULT 'left' AFTER `barcode_offset_y_mm`",
            'content_layout' => "ALTER TABLE `library_card` ADD `content_layout` varchar(20) NOT NULL DEFAULT 'same_row' AFTER `header_align`",
            'photo_align' => "ALTER TABLE `library_card` ADD `photo_align` varchar(20) NOT NULL DEFAULT 'left' AFTER `content_layout`",
            'details_align' => "ALTER TABLE `library_card` ADD `details_align` varchar(20) NOT NULL DEFAULT 'left' AFTER `photo_align`",
            'signature_align' => "ALTER TABLE `library_card` ADD `signature_align` varchar(20) NOT NULL DEFAULT 'right' AFTER `details_align`",
            'barcode_align' => "ALTER TABLE `library_card` ADD `barcode_align` varchar(20) NOT NULL DEFAULT 'right' AFTER `signature_align`",
        );
        foreach ($easy_columns as $col => $sql_add) {
            if (!$this->db->field_exists($col, 'library_card')) {
                $this->db->query($sql_add);
            }
        }
    }

    public function getLibraryTemplates()
    {
        return $this->db->select('*')->from('library_card')->order_by('id', 'DESC')->get()->result();
    }

    public function getTemplateById($id)
    {
        return $this->db->select('*')->from('library_card')->where('id', (int) $id)->get()->result();
    }

    public function getLibraryMembers($member_type = 'all')
    {
        $student_sql = "SELECT
                lm.id AS lib_member_id,
                lm.library_card_no,
                'student' AS member_type,
                students.id AS member_id,
                students.admission_no,
                '' AS employee_id,
                students.firstname,
                students.middlename,
                students.lastname,
                students.guardian_phone AS phone,
                students.dob,
                students.image,
                classes.class AS class_name,
                sections.section AS section_name,
                programs.title AS program_name
            FROM libarary_members lm
            INNER JOIN students ON students.id = lm.member_id
            LEFT JOIN student_session ON student_session.student_id = students.id AND student_session.session_id = " . (int) $this->current_session . "
            LEFT JOIN classes ON classes.id = student_session.class_id
            LEFT JOIN sections ON sections.id = student_session.section_id
            LEFT JOIN programs ON programs.id = student_session.program_id
            WHERE lm.member_type = 'student'";

        $teacher_sql = "SELECT
                lm.id AS lib_member_id,
                lm.library_card_no,
                'teacher' AS member_type,
                staff.id AS member_id,
                '' AS admission_no,
                staff.employee_id,
                staff.name AS firstname,
                '' AS middlename,
                staff.surname AS lastname,
                staff.contact_no AS phone,
                staff.dob,
                CONCAT('uploads/staff_images/', staff.image) AS image,
                '' AS class_name,
                '' AS section_name,
                '' AS program_name
            FROM libarary_members lm
            INNER JOIN staff ON staff.id = lm.member_id
            WHERE lm.member_type = 'teacher'";

        if ($member_type === 'student') {
            $sql = $student_sql . " ORDER BY lib_member_id DESC";
        } elseif ($member_type === 'teacher') {
            $sql = $teacher_sql . " ORDER BY lib_member_id DESC";
        } else {
            $sql = $student_sql . " UNION ALL " . $teacher_sql . " ORDER BY lib_member_id DESC";
        }

        return $this->db->query($sql)->result_array();
    }
}

