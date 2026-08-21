<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Track discount usage per fee line (fee_groups_feetype_id), not only per feetype.
 */
class Migration_Discount_usage_per_fee_line extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('student_fees_discount_usages')) {
            return;
        }

        if (!$this->db->field_exists('fee_groups_feetype_id', 'student_fees_discount_usages')) {
            $this->db->query('ALTER TABLE `student_fees_discount_usages` ADD `fee_groups_feetype_id` int DEFAULT NULL AFTER `feetype_id`');
        }

        $rows = $this->db->get('student_fees_discount_usages')->result_array();
        foreach ($rows as $row) {
            if (!empty($row['fee_groups_feetype_id'])) {
                continue;
            }
            $fgf_id = $this->_fgf_from_payment_ref($row['payment_id']);
            if ($fgf_id > 0) {
                $this->db->where('id', (int) $row['id']);
                $this->db->update('student_fees_discount_usages', array('fee_groups_feetype_id' => $fgf_id));
            }
        }

        // Re-open discounts that still have unused fee lines under allowed feetypes
        $this->_reopen_partial_discounts();
    }

    public function down()
    {
        if ($this->db->field_exists('fee_groups_feetype_id', 'student_fees_discount_usages')) {
            $this->db->query('ALTER TABLE `student_fees_discount_usages` DROP COLUMN `fee_groups_feetype_id`');
        }
    }

    private function _fgf_from_payment_ref($payment_ref)
    {
        if (!preg_match('/^(\d+)\/\/(\d+)$/', trim((string) $payment_ref), $m)) {
            return 0;
        }
        $dep = $this->db->select('fee_groups_feetype_id')
            ->from('student_fees_deposite')
            ->where('id', (int) $m[1])
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($dep['fee_groups_feetype_id']) ? (int) $dep['fee_groups_feetype_id'] : 0;
    }

    private function _reopen_partial_discounts()
    {
        $applied = $this->db->query(
            "SELECT id, student_session_id, fees_discount_id FROM student_fees_discounts WHERE status = 'applied'"
        )->result_array();

        foreach ($applied as $sfd) {
            $sfd_id = (int) $sfd['id'];
            $sid    = (int) $sfd['student_session_id'];
            $mid    = (int) $sfd['fees_discount_id'];
            if ($sid < 1 || $mid < 1) {
                continue;
            }

            $allowed = $this->db->query(
                'SELECT feetype_id FROM fees_discount_feetypes WHERE fees_discount_id = ?',
                array($mid)
            )->result_array();
            if (count($allowed) < 1) {
                continue;
            }

            $allowed_ids = array();
            foreach ($allowed as $a) {
                $allowed_ids[] = (int) $a['feetype_id'];
            }
            $in = implode(',', array_map('intval', $allowed_ids));

            $sql = "SELECT fgf.id AS fgf_id
                FROM student_fees_master sfm
                INNER JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                INNER JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
                WHERE sfm.student_session_id = ? AND fgf.feetype_id IN ($in)";
            $lines = $this->db->query($sql, array($sid))->result_array();

            $has_open = false;
            foreach ($lines as $line) {
                $fgf = (int) $line['fgf_id'];
                $used = $this->db->where('student_fees_discount_id', $sfd_id)
                    ->where('fee_groups_feetype_id', $fgf)
                    ->count_all_results('student_fees_discount_usages');
                if ($used < 1) {
                    $has_open = true;
                    break;
                }
            }

            if ($has_open) {
                $this->db->where('id', $sfd_id);
                $this->db->update('student_fees_discounts', array(
                    'status'     => 'assigned',
                    'payment_id' => '',
                ));
            }
        }
    }
}
