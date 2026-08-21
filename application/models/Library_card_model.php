<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Library_card_model extends MY_model
{
    public function __construct()
    {
        parent::__construct();
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

        // Backward compatibility for already-created tables.
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

    public function templateList()
    {
        return $this->db->select('*')->from('library_card')->order_by('id', 'DESC')->get()->result();
    }

    public function get($id)
    {
        return $this->db->select('*')
            ->from('library_card')
            ->where('id', (int) $id)
            ->where('status', 1)
            ->get()
            ->result();
    }

    public function templateById($id)
    {
        return $this->db->select('*')->from('library_card')->where('id', (int) $id)->get()->row();
    }

    public function saveTemplate($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        if (isset($data['id'])) {
            $id = (int) $data['id'];
            $this->db->where('id', $id);
            $this->db->update('library_card', $data);
            $this->log(UPDATE_RECORD_CONSTANT . " On library card id " . $id, $id, "Update");
            $insert_id = $id;
        } else {
            $this->db->insert('library_card', $data);
            $insert_id = $this->db->insert_id();
            $this->log(INSERT_RECORD_CONSTANT . " On library card id " . $insert_id, $insert_id, "Insert");
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return $insert_id;
    }

    public function remove($id)
    {
        $id = (int) $id;
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id)->delete('library_card');
        $this->log(DELETE_RECORD_CONSTANT . " On library card id " . $id, $id, "Delete");
        $this->db->trans_complete();
        return $this->db->trans_status() !== false;
    }
}

