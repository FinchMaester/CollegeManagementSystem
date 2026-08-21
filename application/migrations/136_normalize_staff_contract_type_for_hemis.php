<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Normalize staff.contract_type to lowercase HEMIS joiningType values.
 */
class Migration_Normalize_staff_contract_type_for_hemis extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('staff') || !$this->db->field_exists('contract_type', 'staff')) {
            return;
        }

        $map = array(
            'Permanent'  => 'permanent',
            'PERMANENT'  => 'permanent',
            'Temporary'  => 'temporary',
            'TEMPORARY'  => 'temporary',
            'Contract'   => 'contract',
            'CONTRACT'   => 'contract',
            'Part-Time'  => 'part-time',
            'Part-time'  => 'part-time',
            'PART-TIME'  => 'part-time',
            'Intern'     => 'intern',
            'INTERN'     => 'intern',
            'Probation'  => 'probation',
            'PROBATION'  => 'probation',
        );

        foreach ($map as $from => $to) {
            $this->db->where('contract_type', $from)->update('staff', array('contract_type' => $to));
        }
    }

    public function down()
    {
        // Data migration only; no rollback of normalized values.
    }
}
