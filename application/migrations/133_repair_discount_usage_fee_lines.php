<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Repair_discount_usage_fee_lines extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('student_fees_discount_usages')) {
            return;
        }
        if (!$this->db->field_exists('fee_groups_feetype_id', 'student_fees_discount_usages')) {
            $this->db->query('ALTER TABLE `student_fees_discount_usages` ADD `fee_groups_feetype_id` int DEFAULT NULL AFTER `feetype_id`');
        }

        $rows = $this->db->query(
            "SELECT u.id, u.student_fees_discount_id, u.feetype_id, u.payment_id, sfd.student_session_id
             FROM student_fees_discount_usages u
             INNER JOIN student_fees_discounts sfd ON sfd.id = u.student_fees_discount_id
             WHERE u.fee_groups_feetype_id IS NULL OR u.fee_groups_feetype_id = 0"
        )->result_array();

        foreach ($rows as $row) {
            $fgf_id = 0;
            if (!empty($row['payment_id']) && preg_match('/^(\d+)\/\//', $row['payment_id'], $m)) {
                $dep = $this->db->select('fee_groups_feetype_id')
                    ->from('student_fees_deposite')
                    ->where('id', (int) $m[1])
                    ->limit(1)
                    ->get()
                    ->row_array();
                if (!empty($dep['fee_groups_feetype_id'])) {
                    $fgf_id = (int) $dep['fee_groups_feetype_id'];
                }
            }
            if ($fgf_id < 1 && !empty($row['student_session_id']) && !empty($row['feetype_id'])) {
                $used_fgfs = array();
                $uq = $this->db->query(
                    'SELECT fee_groups_feetype_id FROM student_fees_discount_usages WHERE student_fees_discount_id = ? AND fee_groups_feetype_id > 0',
                    array((int) $row['student_fees_discount_id'])
                )->result_array();
                foreach ($uq as $u) {
                    $used_fgfs[(int) $u['fee_groups_feetype_id']] = true;
                }
                $candidates = $this->db->query(
                    "SELECT fgf.id FROM student_fees_master sfm
                     INNER JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                     INNER JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
                     WHERE sfm.student_session_id = ? AND fgf.feetype_id = ?
                     ORDER BY fgf.id ASC",
                    array((int) $row['student_session_id'], (int) $row['feetype_id'])
                )->result_array();
                foreach ($candidates as $c) {
                    $cid = (int) $c['id'];
                    if (empty($used_fgfs[$cid])) {
                        $fgf_id = $cid;
                        break;
                    }
                }
            }
            if ($fgf_id > 0) {
                $this->db->where('id', (int) $row['id']);
                $this->db->update('student_fees_discount_usages', array('fee_groups_feetype_id' => $fgf_id));
            }
        }
    }

    public function down()
    {
    }
}
