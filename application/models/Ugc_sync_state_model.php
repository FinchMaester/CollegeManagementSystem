<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Generic per-record UGC sync state (so we don't have to alter every module table).
 */
class Ugc_sync_state_model extends CI_Model
{
    protected $table = 'ugc_sync_states';

    protected function ensure_table()
    {
        if ($this->db->table_exists($this->table)) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `ugc_sync_states` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `module` VARCHAR(64) NOT NULL,
            `local_id` INT NOT NULL,
            `status` VARCHAR(16) NOT NULL DEFAULT 'pending', /* synced|pending|error|blocked */
            `ugc_record_id` VARCHAR(64) NULL,
            `last_synced_at` DATETIME NULL,
            `last_http_code` INT NULL,
            `last_error` VARCHAR(500) NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_module_local` (`module`, `local_id`),
            KEY `idx_status` (`status`),
            KEY `idx_updated_at` (`updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->query($sql);
    }

    public function upsert(string $module, int $local_id, array $patch)
    {
        $this->ensure_table();
        $module = substr(trim($module), 0, 64);
        if ($module === '' || $local_id < 1) {
            return;
        }

        $row = array(
            'module'       => $module,
            'local_id'     => (int) $local_id,
            'updated_at'   => date('Y-m-d H:i:s'),
        );
        foreach (array('status','ugc_record_id','last_synced_at','last_http_code','last_error') as $k) {
            if (array_key_exists($k, $patch)) {
                $row[$k] = $patch[$k];
            }
        }

        $exists = $this->db->get_where($this->table, array('module' => $module, 'local_id' => (int) $local_id))->row_array();
        if ($exists) {
            $this->db->where('module', $module)->where('local_id', (int) $local_id)->update($this->table, $row);
        } else {
            if (!isset($row['status'])) {
                $row['status'] = 'pending';
            }
            $this->db->insert($this->table, $row);
        }
    }

    public function get_state(string $module, int $local_id): ?array
    {
        $this->ensure_table();
        $q = $this->db->get_where($this->table, array('module' => substr(trim($module), 0, 64), 'local_id' => (int) $local_id));
        $r = $q ? $q->row_array() : null;
        return $r ?: null;
    }

    public function map_states(string $module, array $local_ids): array
    {
        $this->ensure_table();
        $ids = array();
        foreach ($local_ids as $id) {
            $id = (int) $id;
            if ($id > 0) $ids[$id] = $id;
        }
        if (empty($ids)) return array();

        $module = substr(trim($module), 0, 64);
        $this->db->where('module', $module);
        $this->db->where_in('local_id', array_values($ids));
        $rows = $this->db->get($this->table)->result_array();
        $out = array();
        foreach ($rows as $r) {
            $out[(int) $r['local_id']] = $r;
        }
        return $out;
    }

    /**
     * Resolve UGC sync badge for staff list (staff.hemis_employee_id wins over stale ugc_sync_states).
     *
     * @param array      $staff
     * @param array|null $state Row from ugc_sync_states
     * @return array { status, badge, text, last_error, hemis_employee_id }
     */
    public function employee_display_for_staff(array $staff, $state = null)
    {
        $hid = isset($staff['hemis_employee_id']) ? (int) $staff['hemis_employee_id'] : 0;
        $hemisErr = isset($staff['hemis_sync_error']) ? trim((string) $staff['hemis_sync_error']) : '';

        if ($hid > 0) {
            return array(
                'status'            => 'synced',
                'badge'             => 'label-success',
                'text'              => 'Synced',
                'last_error'        => '',
                'hemis_employee_id' => $hid,
            );
        }

        if ($hemisErr !== '') {
            return array(
                'status'            => 'error',
                'badge'             => 'label-danger',
                'text'              => 'Error',
                'last_error'        => $hemisErr,
                'hemis_employee_id' => null,
            );
        }

        if (is_array($state)) {
            $status = isset($state['status']) ? (string) $state['status'] : 'pending';
            $lastError = isset($state['last_error']) ? (string) $state['last_error'] : '';
            $badge = 'label-default';
            $txt = 'Pending';
            if ($status === 'synced') {
                $badge = 'label-success';
                $txt = 'Synced';
            } elseif ($status === 'blocked') {
                $badge = 'label-warning';
                $txt = 'Blocked (UGC)';
            } elseif ($status === 'error') {
                $badge = 'label-danger';
                $txt = 'Error';
            }
            $stateHid = null;
            if (!empty($state['ugc_record_id']) && is_numeric($state['ugc_record_id'])) {
                $stateHid = (int) $state['ugc_record_id'];
            }
            return array(
                'status'            => $status,
                'badge'             => $badge,
                'text'              => $txt,
                'last_error'        => $lastError,
                'hemis_employee_id' => $stateHid,
            );
        }

        return array(
            'status'            => 'pending',
            'badge'             => 'label-default',
            'text'              => 'Pending',
            'last_error'        => '',
            'hemis_employee_id' => null,
        );
    }
}

