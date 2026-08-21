<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Drop legacy staff province/district/local_level columns superseded by ugc_p_* / ugc_t_*.
 */
class Migration_Drop_staff_legacy_address_columns extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('staff')) {
            return;
        }

        $legacy = array(
            'permanent_district',
            'permanent_province',
            'permanent_local_level',
            'temporary_district',
            'temporary_province',
            'temporary_local_level',
        );

        foreach ($legacy as $col) {
            if ($this->db->field_exists($col, 'staff')) {
                $this->db->query("ALTER TABLE `staff` DROP COLUMN `{$col}`");
            }
        }
    }

    public function down()
    {
        if (!$this->db->table_exists('staff')) {
            return;
        }

        $restore = array(
            'permanent_district'    => 'VARCHAR(125) NULL',
            'permanent_province'    => 'VARCHAR(125) NULL',
            'permanent_local_level' => 'VARCHAR(125) NULL',
            'temporary_district'    => 'VARCHAR(125) NULL',
            'temporary_province'    => 'VARCHAR(125) NULL',
            'temporary_local_level' => 'VARCHAR(125) NULL',
        );

        foreach ($restore as $col => $def) {
            if (!$this->db->field_exists($col, 'staff')) {
                $this->db->query("ALTER TABLE `staff` ADD COLUMN `{$col}` {$def}");
            }
        }
    }
}
