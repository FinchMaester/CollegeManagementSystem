<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Staff UGC/HEMIS address fields as TEXT to avoid MySQL 65535 row-size limit on wide staff table.
 */
class Migration_Staff_ugc_address_text_columns extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('staff')) {
            return;
        }

        $cols = array(
            'ugc_p_province'      => 'TEXT NULL',
            'ugc_p_district'      => 'TEXT NULL',
            'ugc_p_local_level'   => 'TEXT NULL',
            'ugc_p_combined_code' => 'VARCHAR(20) NULL',
            'ugc_t_province'      => 'TEXT NULL',
            'ugc_t_district'      => 'TEXT NULL',
            'ugc_t_local_level'   => 'TEXT NULL',
            'ugc_t_combined_code' => 'VARCHAR(20) NULL',
        );

        foreach ($cols as $name => $def) {
            if (!$this->db->field_exists($name, 'staff')) {
                $this->db->query("ALTER TABLE `staff` ADD COLUMN `{$name}` {$def}");
            }
        }
    }

    public function down()
    {
        // Non-destructive: keep columns on rollback.
    }
}
