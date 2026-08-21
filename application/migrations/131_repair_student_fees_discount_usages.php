<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Repair_student_fees_discount_usages extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('student_fees_discount_usages')
            || !$this->db->table_exists('fees_discount_feetypes')) {
            return;
        }

        $rows = $this->db->query(
            "SELECT sfd.id AS sfd_id, sfd.fees_discount_id, sfd.payment_id, sfd.description
             FROM student_fees_discounts sfd
             WHERE sfd.payment_id IS NOT NULL AND sfd.payment_id != ''"
        )->result_array();

        foreach ($rows as $row) {
            $sfd_id    = (int) $row['sfd_id'];
            $master_id = (int) $row['fees_discount_id'];

            $allowed = $this->db->query(
                'SELECT feetype_id FROM fees_discount_feetypes WHERE fees_discount_id = ?',
                array($master_id)
            )->result_array();
            if (count($allowed) < 2) {
                continue;
            }

            $allowed_ids = array();
            foreach ($allowed as $a) {
                $allowed_ids[] = (int) $a['feetype_id'];
            }

            $feetype_id = $this->_feetype_id_from_payment_ref($row['payment_id']);
            if ($feetype_id < 1) {
                continue;
            }

            if ($this->db->get_where('student_fees_discount_usages', array(
                'student_fees_discount_id' => $sfd_id,
                'feetype_id'               => $feetype_id,
            ))->num_rows() < 1) {
                $this->db->insert('student_fees_discount_usages', array(
                    'student_fees_discount_id' => $sfd_id,
                    'feetype_id'               => $feetype_id,
                    'payment_id'               => $row['payment_id'],
                    'description'              => $row['description'],
                ));
            }

            $used_count = (int) $this->db->where('student_fees_discount_id', $sfd_id)
                ->count_all_results('student_fees_discount_usages');

            if ($used_count < count($allowed_ids)) {
                $this->db->where('id', $sfd_id);
                $this->db->update('student_fees_discounts', array(
                    'status'     => 'assigned',
                    'payment_id' => '',
                ));
            }
        }
    }

    public function down()
    {
    }

    private function _feetype_id_from_payment_ref($payment_ref)
    {
        if (!preg_match('/^(\d+)\/\/(\d+)$/', trim((string) $payment_ref), $m)) {
            return 0;
        }
        $dep_id = (int) $m[1];
        $dep    = $this->db->select('fee_groups_feetype_id')
            ->from('student_fees_deposite')
            ->where('id', $dep_id)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($dep['fee_groups_feetype_id'])) {
            return 0;
        }
        $fgf = $this->db->select('feetype_id')
            ->from('fee_groups_feetype')
            ->where('id', (int) $dep['fee_groups_feetype_id'])
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($fgf['feetype_id']) ? (int) $fgf['feetype_id'] : 0;
    }
}
