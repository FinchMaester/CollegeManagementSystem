<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Model
 *
 * This model handles authentication and token validation
 */
class Auth_model extends CI_Model {
    
    // Table name
    private $table = 'tokens';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Validate token
     *
     * @param string $token Bearer token
     * @return bool True if valid, false otherwise
     */
    public function validate_token($token) {
        if (empty($token)) {
            return false;
        }
        
        // Query the database for the token
        $query = $this->db->get_where($this->table, [
            'token' => $token,
            'expired' => 0
        ]);
        
        if ($query->num_rows() > 0) {
            $token_data = $query->row();
            
            // Check if token has expired
            $expiry_time = strtotime($token_data->expiry_date);
            if ($expiry_time < time()) {
                // Mark token as expired
                $this->db->where('token', $token);
                $this->db->update($this->table, ['expired' => 1]);
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Authenticate user credentials and generate token
     *
     * @param string $email User email
     * @param string $password User password
     * @param int $collegeId College ID
     * @return string|bool Token if successful, false otherwise
     */
    public function authenticate($email, $password, $collegeId) {
        // Check credentials in users table
        $this->db->where('email', $email);
        $this->db->where('college_id', $collegeId);
        $query = $this->db->get('users');
        
        if ($query->num_rows() > 0) {
            $user = $query->row();
            
            // Verify password
            if (password_verify($password, $user->password)) {
                // Generate token
                $token = $this->_generate_token();
                
                // Store token
                $token_data = [
                    'user_id' => $user->id,
                    'token' => $token,
                    'created_date' => date('Y-m-d H:i:s'),
                    'expiry_date' => date('Y-m-d H:i:s', strtotime('+24 hours')),
                    'expired' => 0
                ];
                
                $this->db->insert($this->table, $token_data);
                
                return $token;
            }
        }
        
        return false;
    }
    
    /**
     * Generate a random token
     *
     * @return string Random token
     */
    private function _generate_token() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Remove expired API tokens so the `tokens` table does not grow without bound.
     * Safe to run from cron (e.g. nightly).
     *
     * @param int $retention_days Delete soft-expired rows older than this many days
     * @return int Approximate rows affected (delete queries)
     */
    public function prune_expired_tokens($retention_days = 90)
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }
        $retention_days = (int) $retention_days;
        if ($retention_days < 7) {
            $retention_days = 7;
        }
        $now = date('Y-m-d H:i:s');
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $retention_days . ' days'));

        $this->db->where('expiry_date <', $now);
        $this->db->delete($this->table);
        $n = (int) $this->db->affected_rows();

        $this->db->where('expired', 1);
        $this->db->where('created_date <', $cutoff);
        $this->db->delete($this->table);
        $n += (int) $this->db->affected_rows();

        return $n;
    }

    /**
     * Clear stored HEMIS sync error text for rows whose error timestamp is older than N days.
     * Requires column students.hemis_sync_error_at (see application/sql/hemis_sync_error_at.sql).
     *
     * @param int $days
     * @return int affected rows
     */
    public function prune_stale_hemis_sync_errors($days = 30)
    {
        if (!$this->db->table_exists('students') || !$this->db->field_exists('hemis_sync_error', 'students')) {
            return 0;
        }
        if (!$this->db->field_exists('hemis_sync_error_at', 'students')) {
            return 0;
        }
        $days = (int) $days;
        if ($days < 7) {
            $days = 7;
        }
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
        $this->db->where('hemis_sync_error IS NOT NULL', null, false);
        $this->db->where('TRIM(hemis_sync_error) !=', '', false);
        $this->db->where('hemis_sync_error_at IS NOT NULL', null, false);
        $this->db->where('hemis_sync_error_at <', $cutoff);
        $this->db->update('students', array(
            'hemis_sync_error'    => null,
            'hemis_sync_error_at' => null,
        ));
        return (int) $this->db->affected_rows();
    }

    /**
     * Production checklist: students whose gender cannot be mapped to UGC-style 1 / 2 / 3, and program codes not in allow-list.
     *
     * @param array $official_program_codes Codes from UGC ProgramMgmt (trimmed, case-insensitive match)
     * @return array
     */
    public function hemis_go_live_readiness(array $official_program_codes = array())
    {
        $out = array(
            'students_gender_not_ugc_ready' => 0,
            'programs_not_in_official_list' => array(),
            'official_list_configured'      => !empty($official_program_codes),
        );
        if (!$this->db->table_exists('students')) {
            return $out;
        }

        $sql = "SELECT COUNT(*) AS c FROM students WHERE TRIM(IFNULL(gender,'')) = ''
            OR (
                LOWER(TRIM(gender)) NOT IN ('1','2','3','male','female','other','m','f','o')
                AND TRIM(gender) NOT IN ('1','2','3')
            )";
        $q = $this->db->query($sql);
        $row = $q && $q->num_rows() ? $q->row_array() : array('c' => 0);
        $out['students_gender_not_ugc_ready'] = (int) $row['c'];

        if ($this->db->table_exists('programs') && !empty($official_program_codes)) {
            $codes = array();
            foreach ($official_program_codes as $c) {
                $u = strtoupper(trim((string) $c));
                if ($u !== '') {
                    $codes[$u] = true;
                }
            }
            if (!empty($codes)) {
                $pq = $this->db->query('SELECT DISTINCT TRIM(code) AS code FROM programs WHERE TRIM(IFNULL(code,\'\')) != \'\'');
                if ($pq && $pq->num_rows()) {
                    foreach ($pq->result_array() as $pr) {
                        $c = strtoupper(trim((string) $pr['code']));
                        if ($c !== '' && !isset($codes[$c])) {
                            $out['programs_not_in_official_list'][] = $pr['code'];
                        }
                    }
                }
            }
        }

        return $out;
    }

    /**
     * Clear conservative dev/test markers from hemis_sync_error so dashboards start clean.
     *
     * @return int affected rows
     */
    public function hemis_clear_leftover_dev_hemis_errors()
    {
        if (!$this->db->table_exists('students') || !$this->db->field_exists('hemis_sync_error', 'students')) {
            return 0;
        }
        $this->db->where('hemis_sync_error IS NOT NULL', null, false);
        $this->db->group_start();
        $this->db->like('hemis_sync_error', 'Test_Sync');
        $this->db->or_like('hemis_sync_error', 'mock_mode');
        $this->db->or_like('hemis_sync_error', 'localhost');
        $this->db->group_end();
        $clear = array('hemis_sync_error' => null);
        if ($this->db->field_exists('hemis_sync_error_at', 'students')) {
            $clear['hemis_sync_error_at'] = null;
        }
        $this->db->update('students', $clear);
        return (int) $this->db->affected_rows();
    }

    /**
     * Map common gender labels to varchar "1", "2", "3" for UGC. Backup DB first; verify forms still display gender.
     *
     * @return int approximate rows touched (sum of statement row counts)
     */
    public function hemis_normalize_student_gender_to_ugc_int_strings()
    {
        if (!$this->db->table_exists('students') || !$this->db->field_exists('gender', 'students')) {
            return 0;
        }
        $n = 0;
        $this->db->query("UPDATE students SET gender = '1' WHERE LOWER(TRIM(gender)) IN ('male','m') OR TRIM(gender) = '1'");
        $n += (int) $this->db->affected_rows();
        $this->db->query("UPDATE students SET gender = '2' WHERE LOWER(TRIM(gender)) IN ('female','f') OR TRIM(gender) = '2'");
        $n += (int) $this->db->affected_rows();
        $this->db->query("UPDATE students SET gender = '3' WHERE LOWER(TRIM(gender)) IN ('other','o') OR TRIM(gender) = '3'");
        $n += (int) $this->db->affected_rows();
        return $n;
    }
}