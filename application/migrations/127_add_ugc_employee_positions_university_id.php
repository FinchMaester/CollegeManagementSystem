<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Store UGC universityId on cached employee positions (live API rows).
 * Safe / additive; no data loss.
 */
class Migration_Add_ugc_employee_positions_university_id extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('ugc_employee_positions')) {
            return;
        }
        if ($this->db->field_exists('university_id', 'ugc_employee_positions')) {
            return;
        }
        $this->load->dbforge();
        $this->dbforge->add_column('ugc_employee_positions', array(
            'university_id' => array(
                'type' => 'INT',
                'null' => true,
            ),
        ), 'idx');
    }

    public function down()
    {
        // Intentionally no down (append-only).
    }
}
