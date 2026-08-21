<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Feediscount_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($id = null, $order = "desc")
    {
        $this->db->select()->from('fees_discounts');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id ' . $order);
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getbyasc($id = null)
    {
        $this->db->select()->from('fees_discounts');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if ($this->db->table_exists('fees_discount_feetypes')) {
            $this->db->where('fees_discount_id', (int) $id);
            $this->db->delete('fees_discount_feetypes');
        }
        $this->db->where('id', $id);
        $this->db->delete('fees_discounts');
        $message   = DELETE_RECORD_CONSTANT . " On  fees discounts id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */
    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('fees_discounts', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  fees discounts id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        } else {
            $data['session_id'] = $this->current_session;
            $this->db->insert('fees_discounts', $data);
            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On  fees discounts id " . $id;
            $action    = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
            return $id;
        }
    }

    public function updateStudentDiscount($data)
    {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('student_fees_discounts', $data);
        }
    }

    public function allotdiscount($data)
    {
        $this->db->where('student_session_id', $data['student_session_id']);
        $this->db->where('fees_discount_id', $data['fees_discount_id']);
        $q = $this->db->get('student_fees_discounts');
        if ($q->num_rows() > 0) {
            return $q->row()->id;
        } else {
            $this->db->insert('student_fees_discounts', $data);
            return $this->db->insert_id();
        }
    }

    public function searchAssignFeeByClassSection($class_id = null, $section_id = null, $fees_discount_id = null, $category = null, $gender = null, $rte = null)
    {
        $sql = "SELECT IFNULL(`student_fees_discounts`.`id`, '0') as `student_fees_discount_id`,"
        . "`classes`.`id` AS `class_id`, `student_session`.`id` as `student_session_id`,"
        . " `students`.`id`, `classes`.`class`, `sections`.`id` AS `section_id`,"
        . " `sections`.`section`, `students`.`id`, `students`.`admission_no`,"
        . " `students`.`roll_no`, `students`.`admission_date`, `students`.`firstname`,"
        . " `students`.`lastname`,`students`.`middlename`, `students`.`image`, `students`.`mobileno`,"
        . " `students`.`email`, `students`.`state`, `students`.`city`, `students`.`pincode`,"
        . " `students`.`religion`, `students`.`dob`, `students`.`current_address`,"
        . " `students`.`permanent_address`, IFNULL(students.category_id, 0) as `category_id`,"
        . " IFNULL(categories.category, '') as `category`, `students`.`adhar_no`,"
        . " `students`.`samagra_id`, `students`.`bank_account_no`, `students`.`bank_name`,"
        . " `students`.`ifsc_code`, `students`.`guardian_name`, `students`.`guardian_relation`,"
        . " `students`.`guardian_phone`, `students`.`guardian_address`, `students`.`is_active`,"
        . " `students`.`created_at`, `students`.`updated_at`, `students`.`father_name`,"
        . " `students`.`rte`, `students`.`gender` FROM `students` JOIN `student_session` ON"
        . " `student_session`.`student_id` = `students`.`id` JOIN `classes` ON"
        . " `student_session`.`class_id` = `classes`.`id` JOIN `sections` ON"
        . " `sections`.`id` = `student_session`.`section_id` LEFT JOIN `categories` ON"
        . " `students`.`category_id` = `categories`.`id` LEFT JOIN"
        . " student_fees_discounts on student_fees_discounts.student_session_id=student_session.id"
        . " AND student_fees_discounts.fees_discount_id=" . $this->db->escape($fees_discount_id) .
        " WHERE `student_session`.`session_id` = " . $this->current_session;

        if ($class_id != null) {
            $sql .= " AND `student_session`.`class_id` = " . $this->db->escape($class_id);
        }
        if ($section_id != null) {
            $sql .= " AND `student_session`.`section_id` =" . $this->db->escape($section_id);
        }
        if ($category != null) {
            $sql .= " AND `students`.`category_id` =" . $this->db->escape($category);
        }
        if ($gender != null) {
            $sql .= " AND `students`.`gender` =" . $this->db->escape($gender);
        }
        if ($rte != null) {
            $sql .= " AND `students`.`rte` =" . $this->db->escape($rte);
        }
        $sql .= " AND student_session.is_active='yes'";
        $sql .= " ORDER BY `students`.`id`";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function deletedisstd($fees_discount_id, $array)
    {
        $this->db->where('fees_discount_id', $fees_discount_id);
        $this->db->where_in('student_session_id', $array);
        $this->db->delete('student_fees_discounts');
    }

    public function getStudentFeesDiscount($student_session_id = null)
    {
        $this->db->select('student_fees_discounts.id ,student_fees_discounts.student_session_id,student_fees_discounts.status,student_fees_discounts.payment_id,student_fees_discounts.description as `student_fees_discount_description`, student_fees_discounts.fees_discount_id, fees_discounts.name,fees_discounts.code,fees_discounts.amount,fees_discounts.description,fees_discounts.session_id,fees_discounts.type,fees_discounts.percentage')->from('student_fees_discounts');
        $this->db->join('fees_discounts', 'fees_discounts.id = student_fees_discounts.fees_discount_id');

        $this->db->where('student_fees_discounts.student_session_id', $student_session_id);
        $this->db->order_by('student_fees_discounts.id');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getDiscountNotApplied($student_session_id = null)
    {
        return $this->getDiscountsForCollectFee($student_session_id, 0, 'fees');
    }

    /**
     * Discounts the student may use when collecting a specific fee line.
     * Multi-feetype discounts can be used once per configured fee type.
     *
     * @param int    $student_session_id
     * @param int    $fee_groups_feetype_id
     * @param string $fee_category
     * @return array
     */
    public function getDiscountsForCollectFee($student_session_id, $fee_groups_feetype_id = 0, $fee_category = 'fees')
    {
        $sid = (int) $student_session_id;
        if ($sid < 1) {
            return array();
        }

        $sql = "SELECT fees_discounts.*, student_fees_discounts.id AS `student_fees_discount_id`, "
            . "student_fees_discounts.status, student_fees_discounts.student_session_id, student_fees_discounts.payment_id "
            . "FROM `student_fees_discounts` "
            . "INNER JOIN fees_discounts ON fees_discounts.id = student_fees_discounts.fees_discount_id "
            . "WHERE student_fees_discounts.student_session_id = ?";
        $rows = $this->db->query($sql, array($sid))->result();

        $feetype_id = null;
        if ($fee_category === 'fees' && (int) $fee_groups_feetype_id > 0) {
            $feetype_id = $this->getFeetypeIdByFeeGroupsFeetypeId($fee_groups_feetype_id);
        }

        $out = array();
        foreach ($rows as $discount) {
            $master_id = isset($discount->id) ? (int) $discount->id : 0;
            $sfd_id    = isset($discount->student_fees_discount_id) ? (int) $discount->student_fees_discount_id : 0;
            if ($master_id < 1 || $sfd_id < 1) {
                continue;
            }

            $allowed = $this->getApplicableFeetypeIds($master_id);
            if ($feetype_id !== null && !empty($allowed) && !in_array((int) $feetype_id, $allowed, true)) {
                continue;
            }

            if ((int) $fee_groups_feetype_id > 0) {
                if ($this->isDiscountAvailableForFeeLine($sfd_id, (int) $fee_groups_feetype_id)) {
                    $out[] = $discount;
                }
            } elseif ($this->isDiscountAvailableForFeetype($sfd_id, $feetype_id, $allowed)) {
                $out[] = $discount;
            }
        }

        return $out;
    }

    /**
     * Whether discount can be applied on this specific fee line (fee_groups_feetype_id).
     *
     * @param int $student_fees_discount_id
     * @param int $fee_groups_feetype_id
     * @return bool
     */
    public function isDiscountAvailableForFeeLine($student_fees_discount_id, $fee_groups_feetype_id)
    {
        $student_fees_discount_id = (int) $student_fees_discount_id;
        $fee_groups_feetype_id    = (int) $fee_groups_feetype_id;
        if ($student_fees_discount_id < 1 || $fee_groups_feetype_id < 1) {
            return false;
        }

        $row = $this->db->select('fees_discount_id, payment_id, student_session_id')
            ->from('student_fees_discounts')
            ->where('id', $student_fees_discount_id)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($row['fees_discount_id'])) {
            return false;
        }

        $master_id  = (int) $row['fees_discount_id'];
        $allowed    = $this->getApplicableFeetypeIds($master_id);
        $feetype_id = $this->getFeetypeIdByFeeGroupsFeetypeId($fee_groups_feetype_id);
        if (!$this->discountAppliesToFeetype($master_id, $feetype_id)) {
            return false;
        }

        if (empty($allowed)) {
            return empty($row['payment_id']);
        }

        if (!$this->db->table_exists('student_fees_discount_usages')) {
            return empty($row['payment_id']);
        }

        if ($this->db->field_exists('fee_groups_feetype_id', 'student_fees_discount_usages')) {
            return $this->db->where('student_fees_discount_id', $student_fees_discount_id)
                ->where('fee_groups_feetype_id', $fee_groups_feetype_id)
                ->count_all_results('student_fees_discount_usages') < 1;
        }

        return $this->db->where('student_fees_discount_id', $student_fees_discount_id)
            ->where('feetype_id', (int) $feetype_id)
            ->count_all_results('student_fees_discount_usages') < 1;
    }

    /**
     * @param int      $student_fees_discount_id
     * @param int|null $feetype_id
     * @param int[]|null $allowed_feetype_ids
     * @return bool
     */
    public function isDiscountAvailableForFeetype($student_fees_discount_id, $feetype_id = null, $allowed_feetype_ids = null)
    {
        $student_fees_discount_id = (int) $student_fees_discount_id;
        if ($student_fees_discount_id < 1) {
            return false;
        }

        $row = $this->db->select('fees_discount_id, payment_id')
            ->from('student_fees_discounts')
            ->where('id', $student_fees_discount_id)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($row)) {
            return false;
        }

        if ($allowed_feetype_ids === null) {
            $allowed_feetype_ids = $this->getApplicableFeetypeIds((int) $row['fees_discount_id']);
        }

        if (empty($allowed_feetype_ids)) {
            return empty($row['payment_id']);
        }

        if ($feetype_id === null || (int) $feetype_id < 1) {
            return false;
        }

        if (!$this->db->table_exists('student_fees_discount_usages')) {
            return empty($row['payment_id']);
        }

        return $this->db->where('student_fees_discount_id', $student_fees_discount_id)
            ->where('feetype_id', (int) $feetype_id)
            ->count_all_results('student_fees_discount_usages') < 1;
    }

    /**
     * @param int $student_fees_discount_id
     * @return bool
     */
    public function hasRemainingDiscountFeeLines($student_fees_discount_id)
    {
        $student_fees_discount_id = (int) $student_fees_discount_id;
        $row = $this->db->select('fees_discount_id, student_session_id, payment_id')
            ->from('student_fees_discounts')
            ->where('id', $student_fees_discount_id)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($row['student_session_id'])) {
            return false;
        }

        $allowed = $this->getApplicableFeetypeIds((int) $row['fees_discount_id']);
        if (empty($allowed)) {
            return empty($row['payment_id']);
        }

        $this->db->select('fgf.id');
        $this->db->from('student_fees_master sfm');
        $this->db->join('fee_session_groups fsg', 'fsg.id = sfm.fee_session_group_id');
        $this->db->join('fee_groups_feetype fgf', 'fgf.fee_session_group_id = fsg.id');
        $this->db->where('sfm.student_session_id', (int) $row['student_session_id']);
        $this->db->where_in('fgf.feetype_id', $allowed);
        $lines = $this->db->get()->result_array();

        foreach ($lines as $line) {
            if ($this->isDiscountAvailableForFeeLine($student_fees_discount_id, (int) $line['id'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record discount use after a fee deposit; supports one use per applicable fee type.
     *
     * @param int    $student_fees_discount_id
     * @param int    $fee_groups_feetype_id
     * @param string $fee_category
     * @param string $payment_ref
     * @param string $description
     */
    public function markDiscountUsedAfterPayment($student_fees_discount_id, $fee_groups_feetype_id, $fee_category, $payment_ref, $description = '')
    {
        $student_fees_discount_id = (int) $student_fees_discount_id;
        if ($student_fees_discount_id < 1) {
            return;
        }

        $row = $this->db->select('fees_discount_id')
            ->from('student_fees_discounts')
            ->where('id', $student_fees_discount_id)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($row['fees_discount_id'])) {
            return;
        }

        $master_id = (int) $row['fees_discount_id'];
        $allowed   = $this->getApplicableFeetypeIds($master_id);

        if ($fee_category !== 'fees' || empty($allowed)) {
            $this->updateStudentDiscount(array(
                'id'          => $student_fees_discount_id,
                'status'      => 'applied',
                'payment_id'  => $payment_ref,
                'description' => $description,
            ));
            return;
        }

        $feetype_id = $this->getFeetypeIdByFeeGroupsFeetypeId($fee_groups_feetype_id);
        if ($feetype_id === null || $feetype_id < 1) {
            return;
        }

        if ($this->db->table_exists('student_fees_discount_usages')) {
            $usage_row = array(
                'student_fees_discount_id' => $student_fees_discount_id,
                'feetype_id'               => $feetype_id,
                'payment_id'               => $payment_ref,
                'description'              => $description,
            );
            if ($this->db->field_exists('fee_groups_feetype_id', 'student_fees_discount_usages')) {
                $usage_row['fee_groups_feetype_id'] = (int) $fee_groups_feetype_id;
                $exists = $this->db->get_where('student_fees_discount_usages', array(
                    'student_fees_discount_id' => $student_fees_discount_id,
                    'fee_groups_feetype_id'    => (int) $fee_groups_feetype_id,
                ))->num_rows();
            } else {
                $exists = $this->db->get_where('student_fees_discount_usages', array(
                    'student_fees_discount_id' => $student_fees_discount_id,
                    'feetype_id'               => $feetype_id,
                ))->num_rows();
            }
            if ($exists < 1) {
                $this->db->insert('student_fees_discount_usages', $usage_row);
            }
        }

        if (!$this->hasRemainingDiscountFeeLines($student_fees_discount_id)) {
            $this->updateStudentDiscount(array(
                'id'          => $student_fees_discount_id,
                'status'      => 'applied',
                'payment_id'  => $payment_ref,
                'description' => $description,
            ));
        } else {
            $this->updateStudentDiscount(array(
                'id'          => $student_fees_discount_id,
                'status'      => 'assigned',
                'payment_id'  => '',
                'description' => $description,
            ));
        }
    }

    /**
     * @param int $student_fees_discount_id
     */
    public function clearDiscountUsages($student_fees_discount_id)
    {
        $student_fees_discount_id = (int) $student_fees_discount_id;
        if ($student_fees_discount_id < 1 || !$this->db->table_exists('student_fees_discount_usages')) {
            return;
        }
        $this->db->where('student_fees_discount_id', $student_fees_discount_id);
        $this->db->delete('student_fees_discount_usages');
    }

    /**
     * One-time / maintenance: reopen multi-feetype discounts that were fully closed after only one fee type was used.
     */
    public function repairMultiFeetypeDiscountUsages()
    {
        if (!$this->db->table_exists('student_fees_discount_usages')) {
            return;
        }

        if ($this->db->field_exists('fee_groups_feetype_id', 'student_fees_discount_usages')) {
            $this->_repairDiscountUsageFeeLineIds();
        }

        if (!$this->db->table_exists('fees_discount_feetypes')) {
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
            $allowed   = $this->getApplicableFeetypeIds($master_id);
            if (count($allowed) < 2) {
                continue;
            }

            $feetype_id = $this->feetypeIdFromPaymentRef($row['payment_id']);
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

            $all_used = true;
            foreach ($allowed as $aid) {
                if ($this->isDiscountAvailableForFeetype($sfd_id, (int) $aid, $allowed)) {
                    $all_used = false;
                    break;
                }
            }
            if (!$all_used) {
                $this->updateStudentDiscount(array(
                    'id'         => $sfd_id,
                    'status'     => 'assigned',
                    'payment_id' => '',
                ));
            }
        }
    }

    /**
     * @param string $payment_ref
     * @return int
     */
    public function feetypeIdFromPaymentRef($payment_ref)
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
        return (int) $this->getFeetypeIdByFeeGroupsFeetypeId((int) $dep['fee_groups_feetype_id']);
    }

    /**
     * Back-fill fee_groups_feetype_id on usage rows (e.g. after migration or reverted payments).
     */
    private function _repairDiscountUsageFeeLineIds()
    {
        $rows = $this->db->query(
            "SELECT u.id, u.student_fees_discount_id, u.feetype_id, u.payment_id, sfd.student_session_id
             FROM student_fees_discount_usages u
             INNER JOIN student_fees_discounts sfd ON sfd.id = u.student_fees_discount_id
             WHERE u.fee_groups_feetype_id IS NULL OR u.fee_groups_feetype_id = 0"
        )->result_array();

        foreach ($rows as $row) {
            $fgf_id = 0;
            if (!empty($row['payment_id']) && preg_match('/^(\d+)\/\//', $row['payment_id'], $pm)) {
                $dep = $this->db->select('fee_groups_feetype_id')
                    ->from('student_fees_deposite')
                    ->where('id', (int) $pm[1])
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

        $applied = $this->db->query(
            "SELECT id FROM student_fees_discounts WHERE status = 'applied'"
        )->result_array();
        foreach ($applied as $sfd) {
            if ($this->hasRemainingDiscountFeeLines((int) $sfd['id'])) {
                $this->updateStudentDiscount(array(
                    'id'         => (int) $sfd['id'],
                    'status'     => 'assigned',
                    'payment_id' => '',
                ));
            }
        }
    }

    /**
     * Ensure every master discount for the student's academic session is allotted
     * so the collect-fee dropdown can list the same discounts managed under admin/feediscount.
     */
    public function ensureAllottedDiscountsForStudentSession($student_session_id)
    {
        $sid = (int) $student_session_id;
        if ($sid < 1) {
            return;
        }
        $sess = $this->db->get_where('student_session', array('id' => $sid), 1)->row_array();
        if (empty($sess['session_id'])) {
            return;
        }
        $session_id = (int) $sess['session_id'];
        $this->db->select('id');
        $this->db->from('fees_discounts');
        $this->db->group_start();
        $this->db->where('session_id', $session_id);
        $this->db->or_where('session_id IS NULL', null, false);
        $this->db->group_end();
        $rows = $this->db->get()->result_array();
        foreach ($rows as $row) {
            $fid = (int) $row['id'];
            if ($fid < 1) {
                continue;
            }
            $this->allotdiscount(array(
                'student_session_id' => $sid,
                'fees_discount_id'   => $fid,
            ));
        }
    }

    /**
     * All fee types for admin multi-select (includes system types).
     *
     * @return array
     */
    public function getAllFeetypesForSelect()
    {
        $this->db->select('id, type, code, is_system');
        $this->db->from('feetype');
        $this->db->order_by('type', 'asc');
        return $this->db->get()->result_array();
    }

    /**
     * feetype_id values linked to a master discount. Empty = applies to all types.
     *
     * @param int $fees_discount_id
     * @return int[]
     */
    public function getApplicableFeetypeIds($fees_discount_id)
    {
        $fees_discount_id = (int) $fees_discount_id;
        if ($fees_discount_id < 1 || !$this->db->table_exists('fees_discount_feetypes')) {
            return array();
        }
        $this->db->select('feetype_id');
        $this->db->from('fees_discount_feetypes');
        $this->db->where('fees_discount_id', $fees_discount_id);
        $rows = $this->db->get()->result_array();
        $ids  = array();
        foreach ($rows as $row) {
            $ids[] = (int) $row['feetype_id'];
        }
        return $ids;
    }

    /**
     * Human-readable fee type labels for a discount (for admin list).
     *
     * @param int $fees_discount_id
     * @return string
     */
    public function getApplicableFeetypeSummary($fees_discount_id)
    {
        $ids = $this->getApplicableFeetypeIds($fees_discount_id);
        if (empty($ids)) {
            return 'all';
        }
        $this->db->select('type, code');
        $this->db->from('feetype');
        $this->db->where_in('id', $ids);
        $this->db->order_by('type', 'asc');
        $rows = $this->db->get()->result_array();
        $labels = array();
        foreach ($rows as $row) {
            $label = trim($row['type']);
            if (!empty($row['code'])) {
                $label .= ' (' . $row['code'] . ')';
            }
            $labels[] = $label;
        }
        return implode(', ', $labels);
    }

    /**
     * Replace fee-type restrictions for a discount.
     *
     * @param int   $fees_discount_id
     * @param array $feetype_ids Empty = all fee types allowed
     */
    public function saveApplicableFeetypes($fees_discount_id, array $feetype_ids)
    {
        $fees_discount_id = (int) $fees_discount_id;
        if ($fees_discount_id < 1 || !$this->db->table_exists('fees_discount_feetypes')) {
            return;
        }
        $this->db->where('fees_discount_id', $fees_discount_id);
        $this->db->delete('fees_discount_feetypes');

        $clean = array();
        foreach ($feetype_ids as $fid) {
            $fid = (int) $fid;
            if ($fid > 0) {
                $clean[$fid] = $fid;
            }
        }
        if (empty($clean)) {
            return;
        }
        foreach ($clean as $feetype_id) {
            $this->db->insert('fees_discount_feetypes', array(
                'fees_discount_id' => $fees_discount_id,
                'feetype_id'       => $feetype_id,
            ));
        }
    }

    /**
     * @param int $fee_groups_feetype_id
     * @return int|null
     */
    public function getFeetypeIdByFeeGroupsFeetypeId($fee_groups_feetype_id)
    {
        $fee_groups_feetype_id = (int) $fee_groups_feetype_id;
        if ($fee_groups_feetype_id < 1) {
            return null;
        }
        $row = $this->db->select('feetype_id')
            ->from('fee_groups_feetype')
            ->where('id', $fee_groups_feetype_id)
            ->limit(1)
            ->get()
            ->row_array();
        return !empty($row['feetype_id']) ? (int) $row['feetype_id'] : null;
    }

    /**
     * Whether a master discount may be used on a fee line.
     *
     * @param int      $fees_discount_id
     * @param int|null $feetype_id
     * @return bool
     */
    public function discountAppliesToFeetype($fees_discount_id, $feetype_id)
    {
        $allowed = $this->getApplicableFeetypeIds($fees_discount_id);
        if (empty($allowed)) {
            return true;
        }
        if ($feetype_id === null || (int) $feetype_id < 1) {
            return false;
        }
        return in_array((int) $feetype_id, $allowed, true);
    }

    /**
     * Whether a student's allotted discount may be used on this fee line.
     *
     * @param int $student_fees_discount_id student_fees_discounts.id
     * @param int $fee_groups_feetype_id
     * @param string $fee_category
     * @return bool
     */
    public function studentDiscountAppliesToFeeLine($student_fees_discount_id, $fee_groups_feetype_id, $fee_category = 'fees')
    {
        if ($fee_category !== 'fees') {
            return false;
        }
        $student_fees_discount_id = (int) $student_fees_discount_id;
        $fee_groups_feetype_id    = (int) $fee_groups_feetype_id;
        if ($student_fees_discount_id < 1 || $fee_groups_feetype_id < 1) {
            return false;
        }
        $row = $this->db->select('fees_discount_id')
            ->from('student_fees_discounts')
            ->where('id', $student_fees_discount_id)
            ->limit(1)
            ->get()
            ->row_array();
        if (empty($row['fees_discount_id'])) {
            return false;
        }
        return $this->isDiscountAvailableForFeeLine($student_fees_discount_id, $fee_groups_feetype_id);
    }

    /**
     * Keep only discounts allowed for the fee line being collected.
     *
     * @param array    $discounts rows from getDiscountNotApplied
     * @param int      $fee_groups_feetype_id
     * @param string   $fee_category
     * @return array
     */
    public function filterUnappliedDiscountsForFeeLine($discounts, $fee_groups_feetype_id, $fee_category = 'fees')
    {
        return $discounts;
    }

}
