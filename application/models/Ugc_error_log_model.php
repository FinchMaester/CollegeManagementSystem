<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ugc_error_log_model extends CI_Model
{
    protected $table = 'ugc_error_logs';

    protected function _column_exists($col)
    {
        $db = $this->db->database;
        $q = $this->db->query(
            "SELECT COUNT(*) AS c
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            array($db, $this->table, (string) $col)
        );
        if ($q && $q->num_rows() > 0) {
            $r = $q->row_array();
            return !empty($r['c']);
        }
        return false;
    }

    protected function ensure_table()
    {
        $exists = $this->db->table_exists($this->table);
        if ($exists) {
            // Evolve schema if older version exists.
            $adds = array(
                'http_method'      => "ALTER TABLE `{$this->table}` ADD COLUMN `http_method` VARCHAR(16) NULL AFTER `endpoint`",
                'request_headers'  => "ALTER TABLE `{$this->table}` ADD COLUMN `request_headers` MEDIUMTEXT NULL AFTER `message`",
                'request_body'     => "ALTER TABLE `{$this->table}` ADD COLUMN `request_body` MEDIUMTEXT NULL AFTER `request_headers`",
                'response_headers' => "ALTER TABLE `{$this->table}` ADD COLUMN `response_headers` MEDIUMTEXT NULL AFTER `request_body`",
                'response_body'    => "ALTER TABLE `{$this->table}` ADD COLUMN `response_body` MEDIUMTEXT NULL AFTER `response_headers`",
            );
            foreach ($adds as $col => $sql) {
                if (!$this->_column_exists($col)) {
                    $this->db->query($sql);
                }
            }
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `ugc_error_logs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `created_at` DATETIME NOT NULL,
            `module` VARCHAR(64) NULL,
            `endpoint` VARCHAR(255) NULL,
            `http_method` VARCHAR(16) NULL,
            `http_code` INT NULL,
            `trace_id` VARCHAR(128) NULL,
            `message` VARCHAR(255) NULL,
            `response_snippet` TEXT NULL,
            `request_headers` MEDIUMTEXT NULL,
            `request_body` MEDIUMTEXT NULL,
            `response_headers` MEDIUMTEXT NULL,
            `response_body` MEDIUMTEXT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_created_at` (`created_at`),
            KEY `idx_module` (`module`),
            KEY `idx_http_code` (`http_code`),
            KEY `idx_trace_id` (`trace_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->query($sql);
    }

    /**
     * @param array $row keys: module, endpoint, http_code, trace_id, message, response_snippet
     */
    public function log(array $row)
    {
        $this->ensure_table();

        $ins = array(
            'created_at'       => date('Y-m-d H:i:s'),
            'module'           => isset($row['module']) ? substr((string) $row['module'], 0, 64) : null,
            'endpoint'         => isset($row['endpoint']) ? substr((string) $row['endpoint'], 0, 255) : null,
            'http_method'      => isset($row['http_method']) ? substr((string) $row['http_method'], 0, 16) : null,
            'http_code'        => isset($row['http_code']) ? (int) $row['http_code'] : null,
            'trace_id'         => isset($row['trace_id']) ? substr((string) $row['trace_id'], 0, 128) : null,
            'message'          => isset($row['message']) ? substr((string) $row['message'], 0, 255) : null,
            'response_snippet' => isset($row['response_snippet']) ? (string) $row['response_snippet'] : null,
            'request_headers'  => isset($row['request_headers']) ? (string) $row['request_headers'] : null,
            'request_body'     => isset($row['request_body']) ? (string) $row['request_body'] : null,
            'response_headers' => isset($row['response_headers']) ? (string) $row['response_headers'] : null,
            'response_body'    => isset($row['response_body']) ? (string) $row['response_body'] : null,
        );

        $this->db->insert($this->table, $ins);
    }

    public function latest($limit = 200)
    {
        $this->ensure_table();
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 50;
        }
        if ($limit > 1000) {
            $limit = 1000;
        }
        $q = $this->db->order_by('id', 'DESC')->limit($limit)->get($this->table);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }
}

