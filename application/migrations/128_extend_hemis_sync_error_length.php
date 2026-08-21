<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Longer hemis_sync_error text for richer UGC diagnostics (students + staff).
 */
class Migration_Extend_hemis_sync_error_length extends CI_Migration
{
    public function up()
    {
        if ($this->db->table_exists('students') && $this->db->field_exists('hemis_sync_error', 'students')) {
            $this->db->query('ALTER TABLE `students` MODIFY `hemis_sync_error` VARCHAR(1000) NULL');
        }
        if ($this->db->table_exists('staff') && $this->db->field_exists('hemis_sync_error', 'staff')) {
            $this->db->query('ALTER TABLE `staff` MODIFY `hemis_sync_error` VARCHAR(1000) NULL');
        }
    }

    public function down()
    {
        if ($this->db->table_exists('students') && $this->db->field_exists('hemis_sync_error', 'students')) {
            $this->db->query('ALTER TABLE `students` MODIFY `hemis_sync_error` VARCHAR(500) NULL');
        }
        if ($this->db->table_exists('staff') && $this->db->field_exists('hemis_sync_error', 'staff')) {
            $this->db->query('ALTER TABLE `staff` MODIFY `hemis_sync_error` VARCHAR(500) NULL');
        }
    }
}
