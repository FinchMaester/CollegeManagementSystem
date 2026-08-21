<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DB-backed overrides for application/config/hemis.php (UGC / central API).
 */
class Hemis_integration_model extends CI_Model
{
    protected $table = 'hemis_integration_settings';

    protected $boolKeys = array(
        'sync_enabled', 'sync_student_update', 'sync_student_upgrade', 'sync_graduation',
        'sync_dropout', 'sync_library', 'sync_employee', 'sync_labs', 'sync_buildings',
        'sync_fev', 'sync_hostels', 'sync_lands', 'sync_management',
        'pull_batch', 'pull_fiscal_year', 'pull_address', 'pull_employee_positions',
        'pull_programmgmt', 'pull_management', 'mock_mode', 'mock_http_enabled',
    );

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Append-only ALTER for installs created before pull_management existed (matches hemis.php / boolKeys).
     */
    protected function ensure_schema_columns()
    {
        static $done = false;
        if ($done || !$this->db->table_exists($this->table)) {
            return;
        }
        $done = true;
        if (!$this->db->field_exists('pull_management', $this->table)) {
            $this->load->dbforge();
            $field = array(
                'pull_management' => array(
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                ),
            );
            if ($this->db->field_exists('pull_programmgmt', $this->table)) {
                $this->dbforge->add_column($this->table, $field, 'pull_programmgmt');
            } else {
                $this->dbforge->add_column($this->table, $field);
            }
        }
    }

    /**
     * File hemis.php merged with DB row id=1 when present.
     *
     * @return array
     */
    public function get_effective_config()
    {
        $this->ensure_schema_columns();
        $this->load->config('hemis', false);
        $defaults = $this->config->item('hemis');
        if (!is_array($defaults)) {
            $defaults = array();
        }
        if (!$this->db->table_exists($this->table)) {
            return $this->apply_hemis_env_overrides($defaults);
        }
        $q = $this->db->get_where($this->table, array('id' => 1), 1);
        if ($q->num_rows() === 0) {
            return $this->apply_hemis_env_overrides($defaults);
        }
        return $this->apply_hemis_env_overrides($this->merge_row($q->row_array(), $defaults));
    }

    /**
     * Non-empty .env values override file + DB (for production secrets without storing in repo/DB).
     *
     * @param array $cfg
     * @return array
     */
    protected function apply_hemis_env_overrides(array $cfg)
    {
        if (!function_exists('app_env')) {
            return $cfg;
        }
        $map = array(
            'HEMIS_REMOTE_BASE_URL'       => 'remote_base_url',
            'HEMIS_LOCAL_MOCK_BASE_URL'   => 'local_mock_base_url',
            'HEMIS_REMOTE_LOGIN_EMAIL'    => 'remote_login_email',
            'HEMIS_REMOTE_LOGIN_PASSWORD' => 'remote_login_password',
        );
        foreach ($map as $envKey => $cfgKey) {
            $v = app_env($envKey, '');
            if ($v !== '') {
                $cfg[$cfgKey] = $v;
            }
        }

        // Bearer token: .env is bootstrap-only unless HEMIS_ENV_TOKEN_OVERRIDES_DB=true.
        // Otherwise a stale HEMIS_REMOTE_BEARER_TOKEN in .env blocks auto-login tokens saved to DB.
        $envToken = app_env('HEMIS_REMOTE_BEARER_TOKEN', '');
        if ($envToken !== '') {
            $dbToken = isset($cfg['remote_bearer_token']) ? trim((string) $cfg['remote_bearer_token']) : '';
            $forceEnv = app_env('HEMIS_ENV_TOKEN_OVERRIDES_DB', '');
            if ($forceEnv !== '' && filter_var($forceEnv, FILTER_VALIDATE_BOOLEAN)) {
                $cfg['remote_bearer_token'] = $envToken;
            } elseif ($dbToken === '') {
                $cfg['remote_bearer_token'] = $envToken;
            }
        }

        // Optional numeric overrides (avoid storing IDs in DB if you prefer env-only config).
        $collegeIdRaw = app_env('HEMIS_REMOTE_COLLEGE_ID', '');
        if ($collegeIdRaw !== '') {
            $cfg['remote_college_id'] = (int) $collegeIdRaw;
        }
        $uniIdRaw = app_env('HEMIS_REMOTE_UNI_ID', '');
        if ($uniIdRaw !== '') {
            $u = (int) $uniIdRaw;
            $cfg['remote_uni_id'] = $u;
            $cfg['remote_university_id'] = $u;
        }
        $uniEnv = app_env('HEMIS_UNIVERSITY_ID', '');
        if ($uniEnv !== '') {
            $u = (int) $uniEnv;
            $cfg['remote_uni_id'] = $u;
            $cfg['remote_university_id'] = $u;
        }

        $multipartMax = app_env('HEMIS_STUDENT_MULTIPART_MAX_BYTES', '');
        if ($multipartMax !== '') {
            $cfg['student_multipart_max_bytes'] = (int) $multipartMax;
        }

        $mockRaw = app_env('HEMIS_MOCK', '');
        if ($mockRaw !== '') {
            $cfg['mock_mode'] = filter_var($mockRaw, FILTER_VALIDATE_BOOLEAN);
        }
        $mockModeAlt = app_env('HEMIS_MOCK_MODE', '');
        if ($mockModeAlt !== '') {
            $cfg['mock_mode'] = filter_var($mockModeAlt, FILTER_VALIDATE_BOOLEAN);
        }

        // Optional: force-enable pulls via env (useful for local testing even if DB has flags off).
        $pullMap = array(
            'HEMIS_PULL_BATCH'               => 'pull_batch',
            'HEMIS_PULL_FISCAL_YEAR'         => 'pull_fiscal_year',
            'HEMIS_PULL_ADDRESS'             => 'pull_address',
            'HEMIS_PULL_PROGRAMMGMT'        => 'pull_programmgmt',
            'HEMIS_PULL_MANAGEMENT'          => 'pull_management',
            'HEMIS_PULL_EMPLOYEE_POSITIONS'  => 'pull_employee_positions',
            'HEMIS_SYNC_ENABLED'             => 'sync_enabled',
            'HEMIS_SYNC_EMPLOYEE'            => 'sync_employee',
        );
        foreach ($pullMap as $envKey => $cfgKey) {
            $raw = app_env($envKey, '');
            if ($raw !== '') {
                $cfg[$cfgKey] = filter_var($raw, FILTER_VALIDATE_BOOLEAN);
            }
        }
        return $cfg;
    }

    /**
     * Single row for admin form (defaults + DB).
     *
     * @return array
     */
    public function get_for_form()
    {
        return $this->get_effective_config();
    }

    /**
     * @param array $post Sanitized POST map
     * @return bool
     */
    public function save_from_post(array $post)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }
        $this->ensure_schema_columns();

        $row = array(
            'remote_base_url'       => isset($post['remote_base_url']) ? trim($post['remote_base_url']) : '',
            'local_mock_base_url'   => isset($post['local_mock_base_url']) ? trim($post['local_mock_base_url']) : '',
            'remote_timeout'        => isset($post['remote_timeout']) ? (int) $post['remote_timeout'] : 30,
        );

        if ($row['remote_timeout'] < 5) {
            $row['remote_timeout'] = 5;
        }
        if ($row['remote_timeout'] > 300) {
            $row['remote_timeout'] = 300;
        }

        foreach ($this->boolKeys as $k) {
            $row[$k] = !empty($post[$k]) ? 1 : 0;
        }

        $newToken = isset($post['remote_bearer_token']) ? trim($post['remote_bearer_token']) : '';
        if ($newToken !== '') {
            $row['remote_bearer_token'] = $newToken;
        } else {
            $existing = $this->db->get_where($this->table, array('id' => 1), 1)->row_array();
            if (!empty($existing) && isset($existing['remote_bearer_token'])) {
                $row['remote_bearer_token'] = $existing['remote_bearer_token'];
            } else {
                $row['remote_bearer_token'] = '';
            }
        }

        if ($this->db->field_exists('remote_login_email', $this->table)) {
            $existingRow = $this->db->get_where($this->table, array('id' => 1), 1)->row_array();
            if (isset($post['remote_login_email'])) {
                $row['remote_login_email'] = trim((string) $post['remote_login_email']);
            } elseif (!empty($existingRow) && isset($existingRow['remote_login_email'])) {
                $row['remote_login_email'] = $existingRow['remote_login_email'];
            } else {
                $row['remote_login_email'] = '';
            }
            $newPass = isset($post['remote_login_password']) ? (string) $post['remote_login_password'] : '';
            if ($newPass !== '') {
                $row['remote_login_password'] = $newPass;
            } elseif (!empty($existingRow) && isset($existingRow['remote_login_password'])) {
                $row['remote_login_password'] = $existingRow['remote_login_password'];
            } else {
                $row['remote_login_password'] = '';
            }
            $row['remote_college_id'] = isset($post['remote_college_id']) ? (int) $post['remote_college_id'] : 0;
            $row['remote_uni_id']     = isset($post['remote_uni_id']) ? (int) $post['remote_uni_id'] : 0;
            $row['remote_is_ugc']     = !empty($post['remote_is_ugc']) ? 1 : 0;
        }

        $exists = $this->db->get_where($this->table, array('id' => 1), 1)->num_rows() > 0;
        if ($exists) {
            $this->db->where('id', 1);
            return (bool) $this->db->update($this->table, $row);
        }

        $row['id'] = 1;
        return (bool) $this->db->insert($this->table, $row);
    }

    /**
     * @param array $row
     * @param array $defaults
     * @return array
     */
    protected function merge_row(array $row, array $defaults)
    {
        $out = $defaults;
        foreach ($this->boolKeys as $k) {
            if (array_key_exists($k, $row)) {
                $out[$k] = (bool) (int) $row[$k];
            }
        }
        if (isset($row['local_mock_base_url'])) {
            $out['local_mock_base_url'] = (string) $row['local_mock_base_url'];
        }
        if (isset($row['remote_base_url'])) {
            $out['remote_base_url'] = (string) $row['remote_base_url'];
        }
        if (isset($row['remote_bearer_token'])) {
            $out['remote_bearer_token'] = (string) $row['remote_bearer_token'];
        }
        if (isset($row['remote_timeout'])) {
            $out['remote_timeout'] = (int) $row['remote_timeout'];
        }
        $loginKeys = array(
            'remote_login_email', 'remote_login_password', 'remote_college_id', 'remote_uni_id',
            'remote_university_id',
            'remote_is_ugc', 'ugc_token_fetched_at',
        );
        foreach ($loginKeys as $k) {
            if (array_key_exists($k, $row)) {
                if ($k === 'remote_college_id' || $k === 'remote_uni_id' || $k === 'remote_university_id') {
                    $out[$k] = (int) $row[$k];
                } elseif ($k === 'remote_is_ugc') {
                    $out[$k] = (bool) (int) $row[$k];
                } else {
                    $out[$k] = $row[$k];
                }
            }
        }
        if (array_key_exists('remote_uni_id', $out)) {
            $out['remote_university_id'] = (int) $out['remote_uni_id'];
        }
        return $out;
    }

    /**
     * Persist bearer token after UGC /api/User/Login (does not touch password).
     *
     * @param string $token
     * @return bool
     */
    public function save_remote_bearer_token_only($token)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }
        $this->ensure_schema_columns();
        $row = array(
            'remote_bearer_token'  => (string) $token,
        );
        if ($this->db->field_exists('ugc_token_fetched_at', $this->table)) {
            $row['ugc_token_fetched_at'] = date('Y-m-d H:i:s');
        }
        $exists = $this->db->get_where($this->table, array('id' => 1), 1)->num_rows() > 0;
        if ($exists) {
            $this->db->where('id', 1);
            return (bool) $this->db->update($this->table, $row);
        }
        $row['id'] = 1;
        return (bool) $this->db->insert($this->table, $row);
    }
}
