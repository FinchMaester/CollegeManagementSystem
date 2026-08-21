<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * DB-backed library renewal & fine settings (merged with application/config/library.php).
 */
class Library_settings_model extends CI_Model
{
    protected $table = 'library_settings';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_table();
    }

    public function ensure_table()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `library_settings` (
                  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
                  `renewal_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                  `renewal_days` INT NOT NULL DEFAULT 14,
                  `max_renewals` INT NOT NULL DEFAULT 2,
                  `fine_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                  `fine_per_day` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
                  `fine_grace_days` INT NOT NULL DEFAULT 0,
                  `fine_max` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
                  `updated_at` DATETIME NULL DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
        if ($this->db->table_exists($this->table)) {
            $row = $this->db->get_where($this->table, array('id' => 1), 1)->row_array();
            if (empty($row)) {
                $this->db->insert($this->table, $this->defaults_from_config());
            }
        }
    }

    /**
     * @return array
     */
    public function defaults_from_config()
    {
        $this->load->config('library');
        $max_fine = (float) $this->config->item('library_fine_max');
        return array(
            'id'              => 1,
            'renewal_enabled' => $this->config->item('library_renewal_enabled') ? 1 : 0,
            'renewal_days'    => max(1, (int) $this->config->item('library_renewal_days')),
            'max_renewals'    => max(0, (int) $this->config->item('library_max_renewals')),
            'fine_enabled'    => $this->config->item('library_fine_enabled') ? 1 : 0,
            'fine_per_day'    => max(0, (float) $this->config->item('library_fine_per_day')),
            'fine_grace_days' => max(0, (int) $this->config->item('library_fine_grace_days')),
            'fine_max'        => $max_fine > 0 ? $max_fine : 0,
            'updated_at'      => date('Y-m-d H:i:s'),
        );
    }

    /**
     * Settings for issue/renew/fine logic (normalized keys).
     *
     * @return array
     */
    public function get_effective_settings()
    {
        $defaults = $this->defaults_from_config();
        if (!$this->db->table_exists($this->table)) {
            return $this->normalize_row($defaults);
        }
        $row = $this->db->get_where($this->table, array('id' => 1), 1)->row_array();
        if (empty($row)) {
            return $this->normalize_row($defaults);
        }
        return $this->normalize_row(array_merge($defaults, $row));
    }

    /**
     * @return array
     */
    public function get_for_form()
    {
        $s = $this->get_effective_settings();
        return array(
            'renewal_enabled' => !empty($s['renewal_enabled']) ? 1 : 0,
            'renewal_days'    => (int) $s['renewal_days'],
            'max_renewals'    => (int) $s['max_renewals'],
            'fine_enabled'    => !empty($s['fine_enabled']) ? 1 : 0,
            'fine_per_day'    => (float) $s['fine_per_day'],
            'fine_grace_days' => (int) $s['fine_grace_days'],
            'fine_max'        => (float) $s['fine_max'],
        );
    }

    /**
     * @param array $post
     * @return bool
     */
    public function save_from_post(array $post)
    {
        $this->ensure_table();
        $row = array(
            'renewal_enabled' => !empty($post['renewal_enabled']) ? 1 : 0,
            'renewal_days'    => max(1, (int) (isset($post['renewal_days']) ? $post['renewal_days'] : 14)),
            'max_renewals'    => max(0, (int) (isset($post['max_renewals']) ? $post['max_renewals'] : 2)),
            'fine_enabled'    => !empty($post['fine_enabled']) ? 1 : 0,
            'fine_per_day'    => max(0, (float) (isset($post['fine_per_day']) ? $post['fine_per_day'] : 0)),
            'fine_grace_days' => max(0, (int) (isset($post['fine_grace_days']) ? $post['fine_grace_days'] : 0)),
            'fine_max'        => max(0, (float) (isset($post['fine_max']) ? $post['fine_max'] : 0)),
            'updated_at'      => date('Y-m-d H:i:s'),
        );
        $exists = $this->db->get_where($this->table, array('id' => 1), 1)->row_array();
        if (!empty($exists)) {
            $this->db->where('id', 1)->update($this->table, $row);
        } else {
            $row['id'] = 1;
            $this->db->insert($this->table, $row);
        }
        return true;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function normalize_row(array $row)
    {
        $max_fine = isset($row['fine_max']) ? (float) $row['fine_max'] : 0;
        return array(
            'renewal_enabled' => !empty($row['renewal_enabled']),
            'renewal_days'    => max(1, (int) (isset($row['renewal_days']) ? $row['renewal_days'] : 14)),
            'max_renewals'    => max(0, (int) (isset($row['max_renewals']) ? $row['max_renewals'] : 2)),
            'fine_enabled'    => !empty($row['fine_enabled']),
            'fine_per_day'    => max(0, (float) (isset($row['fine_per_day']) ? $row['fine_per_day'] : 0)),
            'fine_grace_days' => max(0, (int) (isset($row['fine_grace_days']) ? $row['fine_grace_days'] : 0)),
            'fine_max'        => $max_fine > 0 ? $max_fine : 0,
        );
    }
}
