<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Optional auto-login to UGC POST /api/User/Login when bearer token is empty.
 */
class Hemis_ugc_auth
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('hemis_integration_model');
    }

    /**
     * If configured, obtain a bearer token and persist it to hemis_integration_settings.
     *
     * @param array $cfg effective hemis config
     * @return array updated cfg (merged with new remote_bearer_token if success)
     */
    public function merge_auto_login_into_config(array $cfg)
    {
        if (empty($cfg['sync_enabled']) || !empty($cfg['mock_mode'])) {
            return $cfg;
        }

        $email = isset($cfg['remote_login_email']) ? trim((string) $cfg['remote_login_email']) : '';
        $pass  = isset($cfg['remote_login_password']) ? (string) $cfg['remote_login_password'] : '';

        $token = isset($cfg['remote_bearer_token']) ? trim((string) $cfg['remote_bearer_token']) : '';
        if ($token !== '') {
            if (!$this->_jwt_is_expired($token)) {
                return $cfg;
            }
            if ($email === '' || $pass === '') {
                log_message('error', 'HEMIS UGC token expired and no login email/password configured to refresh it. Update the token in HEMIS Integration settings.');
                return $cfg;
            }
            log_message('info', 'HEMIS UGC bearer token expired; attempting automatic re-login.');
        }

        if ($email === '' || $pass === '') {
            return $cfg;
        }

        $result = $this->_attempt_ugc_login($cfg);
        if (empty($result['success']) || empty($result['token'])) {
            return $cfg;
        }

        $this->CI->hemis_integration_model->save_remote_bearer_token_only($result['token']);
        $cfg['remote_bearer_token'] = $result['token'];
        return $cfg;
    }

    /**
     * Test UGC login from admin UI (always hits remote API; ignores mock_mode).
     *
     * @param array $cfg
     * @param bool  $persist_token Save token to hemis_integration_settings on success
     * @param bool  $include_token Include raw JWT in JSON (for "Get token manually" UI fill)
     * @return array
     */
    public function test_login_from_config(array $cfg, $persist_token = true, $include_token = false)
    {
        $email = isset($cfg['remote_login_email']) ? trim((string) $cfg['remote_login_email']) : '';
        $pass  = isset($cfg['remote_login_password']) ? (string) $cfg['remote_login_password'] : '';
        $remote = isset($cfg['remote_base_url']) ? trim((string) $cfg['remote_base_url']) : '';

        if ($email === '' || $pass === '') {
            return array(
                'status'  => 0,
                'message' => 'UGC login email and password are required (form or saved settings / .env).',
            );
        }
        if ($remote === '') {
            return array(
                'status'  => 0,
                'message' => 'Central API base URL is empty.',
            );
        }
        if (!function_exists('curl_init')) {
            return array(
                'status'  => 0,
                'message' => 'PHP curl extension is required.',
            );
        }

        $result = $this->_attempt_ugc_login($cfg);
        $token = isset($result['token']) ? (string) $result['token'] : '';
        $tokenSaved = false;
        $fetchedAt = null;

        if (!empty($result['success']) && $token !== '' && $persist_token) {
            $tokenSaved = (bool) $this->CI->hemis_integration_model->save_remote_bearer_token_only($token);
            if ($tokenSaved && $this->CI->db->table_exists('hemis_integration_settings')
                && $this->CI->db->field_exists('ugc_token_fetched_at', 'hemis_integration_settings')) {
                $row = $this->CI->db->get_where('hemis_integration_settings', array('id' => 1), 1)->row_array();
                if (!empty($row['ugc_token_fetched_at'])) {
                    $fetchedAt = $row['ugc_token_fetched_at'];
                }
            }
        }

        $expAt = $token !== '' ? $this->_jwt_expires_at_human($token) : null;

        if (!empty($result['success']) && $token !== '') {
            $msg = 'UGC login succeeded.';
            if ($tokenSaved) {
                $msg .= ' Bearer token saved to integration settings.';
            } elseif ($persist_token) {
                $msg .= ' Token received but could not be saved (check database table).';
            } elseif ($include_token) {
                $msg .= ' Token filled into the Bearer token field — click Save to store it.';
            }
            if ($expAt !== null) {
                $msg .= ' JWT expires: ' . $expAt . '.';
            }
            $out = array(
                'status'            => 1,
                'message'           => $msg,
                'http_code'         => isset($result['http_code']) ? (int) $result['http_code'] : null,
                'login_url'         => isset($result['login_url']) ? $result['login_url'] : '',
                'token_length'      => strlen($token),
                'token_saved'       => $tokenSaved,
                'token_fetched_at'  => $fetchedAt,
                'token_expires_at'  => $expAt,
                'login_payload'     => array(
                    'email'     => $email,
                    'collegeId' => isset($cfg['remote_college_id']) ? (int) $cfg['remote_college_id'] : 0,
                    'UniId'     => $this->_resolve_login_uni_id($cfg),
                    'isUgc'     => !empty($cfg['remote_is_ugc']),
                ),
            );
            if ($include_token) {
                $out['token'] = $token;
            }
            return $out;
        }

        $err = isset($result['error']) ? (string) $result['error'] : 'Login failed.';
        return array(
            'status'           => 0,
            'message'          => $err,
            'http_code'        => isset($result['http_code']) ? (int) $result['http_code'] : null,
            'login_url'        => isset($result['login_url']) ? $result['login_url'] : '',
            'response_snippet' => isset($result['response_snippet']) ? $result['response_snippet'] : '',
            'verify_url'       => isset($result['verify_url']) ? $result['verify_url'] : '',
            'token_saved'      => false,
            'login_payload'    => array(
                'email'     => $email,
                'collegeId' => isset($cfg['remote_college_id']) ? (int) $cfg['remote_college_id'] : 0,
                'UniId'     => $this->_resolve_login_uni_id($cfg),
                'isUgc'     => !empty($cfg['remote_is_ugc']),
            ),
        );
    }

    /**
     * UniId for POST /api/User/Login only (Postman college login uses 0).
     * Distinct from HEMIS_UNIVERSITY_ID used for EmployeePositions / Employee create.
     *
     * @param array $cfg
     * @return int
     */
    protected function _resolve_login_uni_id(array $cfg)
    {
        if (isset($cfg['remote_login_uni_id']) && $cfg['remote_login_uni_id'] !== '' && $cfg['remote_login_uni_id'] !== null) {
            return (int) $cfg['remote_login_uni_id'];
        }
        if (function_exists('app_env')) {
            $loginUniEnv = app_env('HEMIS_REMOTE_LOGIN_UNI_ID', '');
            if ($loginUniEnv !== '') {
                return (int) $loginUniEnv;
            }
        }
        return 0;
    }

    /**
     * POST /api/User/Login (or /api/login) using config credentials.
     *
     * @param array $cfg
     * @return array { success, http_code, error, token, login_url, response_snippet }
     */
    protected function _attempt_ugc_login(array $cfg)
    {
        $email = isset($cfg['remote_login_email']) ? trim((string) $cfg['remote_login_email']) : '';
        $pass  = isset($cfg['remote_login_password']) ? (string) $cfg['remote_login_password'] : '';
        $remote = isset($cfg['remote_base_url']) ? trim((string) $cfg['remote_base_url']) : '';

        if ($email === '' || $pass === '' || $remote === '' || !function_exists('curl_init')) {
            return array(
                'success' => false,
                'http_code' => 0,
                'error' => 'Missing login credentials, base URL, or curl.',
                'token' => null,
                'login_url' => '',
                'response_snippet' => '',
                'verify_url' => '',
            );
        }

        $collegeId = isset($cfg['remote_college_id']) ? (int) $cfg['remote_college_id'] : 0;
        $uniId     = $this->_resolve_login_uni_id($cfg);
        $isUgc     = !empty($cfg['remote_is_ugc']);
        $timeout   = (int) (isset($cfg['remote_timeout']) ? $cfg['remote_timeout'] : 30);

        $loginUrls = array(
            rtrim($remote, '/') . '/api/User/Login',
            rtrim($remote, '/') . '/api/login',
        );
        $loginPayload = array(
            'email'     => $email,
            'password'  => $pass,
            'collegeId' => $collegeId,
            'UniId'     => $uniId,
            'isUgc'     => (bool) $isUgc,
        );

        log_message(
            'info',
            'HEMIS UGC login POST ' . $loginUrls[0]
            . ' payload={email:' . $email
            . ',collegeId:' . $collegeId
            . ',UniId:' . $uniId
            . ',isUgc:' . ($isUgc ? 'true' : 'false')
            . ',password_len:' . strlen($pass) . '}'
        );

        $code = 0;
        $body = '';
        $json = null;
        $usedUrl = $loginUrls[0];
        foreach ($loginUrls as $url) {
            $usedUrl = $url;
            $res = $this->_ugc_http_post_json($url, $loginPayload, $timeout);
            $code = (int) $res['http_code'];
            $body = (string) $res['body'];
            if ($res['curl_error'] !== '') {
                return array(
                    'success' => false,
                    'http_code' => $code,
                    'error' => 'Network error: ' . $res['curl_error'],
                    'token' => null,
                    'login_url' => $usedUrl,
                    'response_snippet' => '',
                    'verify_url' => '',
                );
            }
            if ($code >= 200 && $code < 300 && $body !== '') {
                $json = json_decode($body, true);
                break;
            }
        }

        $snippet = preg_replace('/\s+/', ' ', trim(substr((string) $body, 0, 240)));

        if ($code < 200 || $code >= 300 || $body === '') {
            log_message('error', 'HEMIS UGC login failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 300));
            return array(
                'success' => false,
                'http_code' => $code,
                'error' => 'UGC login failed (HTTP ' . $code . ')' . ($snippet !== '' ? ': ' . $snippet : ''),
                'token' => null,
                'login_url' => $usedUrl,
                'response_snippet' => $snippet,
                'verify_url' => '',
            );
        }

        $newToken = $this->_extract_token_from_login_json($json);
        if ($newToken !== null && $newToken !== '') {
            return array(
                'success' => true,
                'http_code' => $code,
                'error' => null,
                'token' => $newToken,
                'login_url' => $usedUrl,
                'response_snippet' => $snippet,
                'verify_url' => '',
            );
        }

        // Some UGC tenants return HTTP 202 + verificationToken first.
        // Try to exchange it for a JWT automatically (no OTP / email code).
        $verificationToken = $this->_extract_verification_token_from_login_json($json);
        if ($verificationToken !== '') {
            $verifyResult = $this->_attempt_ugc_verification_step(
                $remote,
                $loginPayload,
                $verificationToken,
                $timeout,
                $usedUrl
            );
            if (!empty($verifyResult['token'])) {
                return array(
                    'success' => true,
                    'http_code' => isset($verifyResult['http_code']) ? (int) $verifyResult['http_code'] : $code,
                    'error' => null,
                    'token' => $verifyResult['token'],
                    'login_url' => isset($verifyResult['login_url']) ? $verifyResult['login_url'] : $usedUrl,
                    'response_snippet' => isset($verifyResult['response_snippet']) ? $verifyResult['response_snippet'] : $snippet,
                    'verify_url' => isset($verifyResult['login_url']) ? $verifyResult['login_url'] : '',
                );
            }

            log_message('error', 'HEMIS UGC login: verificationToken received but no bearer JWT after auto-confirm.');
            return array(
                'success' => false,
                'http_code' => $code,
                'error' => 'UGC login returned a verificationToken (HTTP ' . $code . ') but no bearer JWT. '
                    . 'Ask UGC to issue a direct JWT for this campus account, or paste a token from Postman into Bearer token and Save.',
                'token' => null,
                'login_url' => $usedUrl,
                'response_snippet' => isset($verifyResult['response_snippet']) && $verifyResult['response_snippet'] !== ''
                    ? $verifyResult['response_snippet']
                    : $snippet,
                'verify_url' => isset($verifyResult['login_url']) ? $verifyResult['login_url'] : '',
            );
        }

        log_message('error', 'HEMIS UGC login: token not found in response body');
        return array(
            'success' => false,
            'http_code' => $code,
            'error' => 'Login HTTP ' . $code . ' OK but no bearer token (tokenString/JWT) in response. Bearer token was not saved.',
            'token' => null,
            'login_url' => $usedUrl,
            'response_snippet' => $snippet,
            'verify_url' => '',
        );
    }

    /**
     * Exchange verificationToken for tokenString/JWT (no OTP).
     *
     * @param string $remote
     * @param array  $loginPayload
     * @param string $verificationToken
     * @param int    $timeout
     * @param string $loginUrl
     * @return array { token, http_code, login_url, response_snippet }
     */
    protected function _attempt_ugc_verification_step($remote, array $loginPayload, $verificationToken, $timeout, $loginUrl)
    {
        $remote = rtrim((string) $remote, '/');
        $verificationToken = trim((string) $verificationToken);
        $out = array('token' => null, 'http_code' => 0, 'login_url' => '', 'response_snippet' => '');

        if ($verificationToken === '') {
            return $out;
        }

        $verifyPaths = array(
            '/api/User/ConfirmLogin',
            '/api/User/VerifyLogin',
            '/api/User/Verify',
            '/api/User/LoginVerify',
        );

        $payloadBases = array(
            array(
                'verificationToken' => $verificationToken,
                'VerificationToken' => $verificationToken,
                'email'             => isset($loginPayload['email']) ? $loginPayload['email'] : '',
                'collegeId'         => isset($loginPayload['collegeId']) ? $loginPayload['collegeId'] : 0,
                'UniId'             => isset($loginPayload['UniId']) ? $loginPayload['UniId'] : 0,
                'isUgc'             => isset($loginPayload['isUgc']) ? $loginPayload['isUgc'] : false,
            ),
            array(
                'verificationToken' => $verificationToken,
                'token'             => $verificationToken,
            ),
            array_merge($loginPayload, array(
                'verificationToken' => $verificationToken,
                'VerificationToken' => $verificationToken,
            )),
        );

        $urls = array_merge(array($loginUrl), array_map(function ($p) use ($remote) {
            return $remote . $p;
        }, $verifyPaths));

        foreach ($payloadBases as $base) {
            foreach ($urls as $url) {
                $res = $this->_ugc_http_post_json($url, $base, $timeout);
                if ($res['curl_error'] !== '') {
                    continue;
                }
                $out['http_code'] = (int) $res['http_code'];
                $out['login_url'] = $url;
                $out['response_snippet'] = preg_replace('/\s+/', ' ', trim(substr((string) $res['body'], 0, 240)));
                if ($out['http_code'] < 200 || $out['http_code'] >= 300 || $res['body'] === '') {
                    continue;
                }
                $json = json_decode($res['body'], true);
                $token = $this->_extract_token_from_login_json($json);
                if ($token !== null && $token !== '') {
                    $out['token'] = $token;
                    return $out;
                }
            }
        }

        return $out;
    }

    /**
     * @param string $url
     * @param array  $payload
     * @param int    $timeout
     * @return array { http_code, body, curl_error }
     */
    protected function _ugc_http_post_json($url, array $payload, $timeout)
    {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => max(5, (int) $timeout),
                CURLOPT_HTTPHEADER     => array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                ),
            ));
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
            curl_close($ch);

        return array(
            'http_code'   => $code,
            'body'        => ($body === false) ? '' : (string) $body,
            'curl_error'  => (string) $curlErr,
        );
    }

    /**
     * Flatten UGC responses like {"name":"verificationToken","value":"..."} or arrays of those.
     *
     * @param mixed $json
     * @return array
     */
    protected function _normalize_login_response_json($json)
    {
        if (!is_array($json)) {
            return array();
        }

        if (isset($json['name'], $json['value']) && !isset($json['tokenString']) && !isset($json['token'])) {
            return array((string) $json['name'] => $json['value']);
        }

        $isList = array_keys($json) === range(0, count($json) - 1);
        if ($isList) {
            $flat = array();
            foreach ($json as $item) {
                if (is_array($item) && isset($item['name'], $item['value'])) {
                    $flat[(string) $item['name']] = $item['value'];
                }
            }
            if (!empty($flat)) {
                return $flat;
            }
        }

        return $json;
    }

    /**
     * @param mixed $json
     * @return string
     */
    protected function _extract_verification_token_from_login_json($json)
    {
        $flat = $this->_normalize_login_response_json($json);
        $keys = array('verificationToken', 'VerificationToken', 'verification_token');
        foreach ($keys as $key) {
            if (!empty($flat[$key])) {
                return trim((string) $flat[$key]);
            }
        }
        if (isset($flat['name'], $flat['value']) && strtolower((string) $flat['name']) === 'verificationtoken') {
            return trim((string) $flat['value']);
        }
        return '';
    }

    /**
     * @param mixed $json
     * @return string|null
     */
    protected function _extract_token_from_login_json($json)
    {
        $flat = $this->_normalize_login_response_json($json);
        if (empty($flat)) {
            return null;
        }

        $candidates = array(
            'tokenString',
            'TokenString',
            'token',
            'Token',
            'access_token',
            'accessToken',
            'bearer_token',
        );
        foreach ($candidates as $key) {
            if (!empty($flat[$key]) && $this->_looks_like_bearer_token((string) $flat[$key])) {
                return (string) $flat[$key];
            }
        }

        if (!empty($flat['data']['token'])) {
            return (string) $flat['data']['token'];
        }
        if (!empty($flat['data']['tokenString'])) {
            return (string) $flat['data']['tokenString'];
        }

        return null;
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function _looks_like_bearer_token($value)
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }
        if (strpos($value, '.') !== false && strlen($value) > 40) {
            return true;
        }
        return strlen($value) > 60;
    }

    /**
     * @param string $token
     * @return string|null Human-readable expiry from JWT exp claim
     */
    protected function _jwt_expires_at_human($token)
    {
        $parts = explode('.', (string) $token);
        if (count($parts) !== 3) {
            return null;
        }
        $payload = $this->_b64url_decode($parts[1]);
        if ($payload === false || $payload === '') {
            return null;
        }
        $claims = json_decode($payload, true);
        if (!is_array($claims) || empty($claims['exp'])) {
            return null;
        }
        $exp = (int) $claims['exp'];
        return $exp > 0 ? date('Y-m-d H:i:s', $exp) : null;
    }

    /**
     * True when $token is a JWT whose `exp` claim is in the past (with a small clock skew).
     *
     * @param string $token raw bearer token (no "Bearer " prefix)
     * @param int    $skew  seconds of leeway; refresh slightly before the hard expiry
     * @return bool
     */
    protected function _jwt_is_expired($token, $skew = 60)
    {
        $parts = explode('.', (string) $token);
        if (count($parts) !== 3) {
            return false;
        }

        $payload = $this->_b64url_decode($parts[1]);
        if ($payload === false || $payload === '') {
            return false;
        }

        $claims = json_decode($payload, true);
        if (!is_array($claims) || !isset($claims['exp'])) {
            return false;
        }

        $exp = (int) $claims['exp'];
        if ($exp <= 0) {
            return false;
        }

        return ($exp - (int) $skew) <= time();
    }

    /**
     * @param string $data
     * @return string|false
     */
    protected function _b64url_decode($data)
    {
        $data = strtr((string) $data, '-_', '+/');
        $pad = strlen($data) % 4;
        if ($pad > 0) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($data, true);
    }

    /**
     * Admin dashboard summary for Central API bearer token + auto-refresh readiness.
     *
     * @param array $cfg Effective hemis config (get_effective_config).
     * @return array
     */
    public function get_bearer_token_status(array $cfg)
    {
        $token = isset($cfg['remote_bearer_token']) ? trim((string) $cfg['remote_bearer_token']) : '';
        $email = isset($cfg['remote_login_email']) ? trim((string) $cfg['remote_login_email']) : '';
        $pass  = isset($cfg['remote_login_password']) ? (string) $cfg['remote_login_password'] : '';
        $collegeId = isset($cfg['remote_college_id']) ? (int) $cfg['remote_college_id'] : 0;

        $fetchedAt = null;
        $dbToken = '';
        $table = 'hemis_integration_settings';
        if ($this->CI->db->table_exists($table)) {
            $select = array('remote_bearer_token');
            if ($this->CI->db->field_exists('ugc_token_fetched_at', $table)) {
                $select[] = 'ugc_token_fetched_at';
            }
            $row = $this->CI->db->select(implode(', ', $select))
                ->get_where($table, array('id' => 1), 1)
                ->row_array();
            if (is_array($row)) {
                if (!empty($row['ugc_token_fetched_at'])) {
                    $fetchedAt = (string) $row['ugc_token_fetched_at'];
                }
                if (isset($row['remote_bearer_token'])) {
                    $dbToken = trim((string) $row['remote_bearer_token']);
                }
            }
        }

        $envToken = (function_exists('app_env')) ? trim((string) app_env('HEMIS_REMOTE_BEARER_TOKEN', '')) : '';
        $envOverrides = false;
        if (function_exists('app_env')) {
            $force = app_env('HEMIS_ENV_TOKEN_OVERRIDES_DB', '');
            $envOverrides = ($force !== '' && filter_var($force, FILTER_VALIDATE_BOOLEAN));
        }

        $tokenSource = 'none';
        if ($token !== '') {
            if ($dbToken !== '' && $token === $dbToken) {
                $tokenSource = 'database';
            } elseif ($envToken !== '' && $token === $envToken) {
                $tokenSource = '.env';
            } else {
                $tokenSource = 'merged';
            }
        }

        $hasToken = ($token !== '');
        $isJwt = $hasToken && substr_count($token, '.') === 2;
        $expiresAt = $hasToken ? $this->_jwt_expires_at_human($token) : null;
        $isExpired = $hasToken && $this->_jwt_is_expired($token);
        $expiringSoon = false;
        if ($hasToken && $isJwt && !$isExpired) {
            $parts = explode('.', $token);
            $payload = $this->_b64url_decode($parts[1]);
            $claims = is_string($payload) && $payload !== '' ? json_decode($payload, true) : null;
            if (is_array($claims) && !empty($claims['exp'])) {
                $expiringSoon = ((int) $claims['exp'] - time()) < 86400;
            }
        }

        $blockers = array();
        if (empty($cfg['sync_enabled'])) {
            $blockers[] = 'Enable outbound sync (master checkbox above).';
        }
        if (!empty($cfg['mock_mode'])) {
            $blockers[] = 'Turn off mock mode for real UGC API calls.';
        }
        if ($email === '') {
            $blockers[] = 'Save UGC login email below.';
        }
        if ($pass === '') {
            $blockers[] = 'Save UGC login password below.';
        }
        if ($collegeId < 1) {
            $blockers[] = 'Set College / campus ID (e.g. 211) — required for production login.';
        }

        $autoRefreshReady = empty($blockers);
        $statusLevel = 'ok';
        if (!$hasToken || $isExpired) {
            $statusLevel = $autoRefreshReady ? 'warn' : 'danger';
        } elseif ($expiringSoon) {
            $statusLevel = 'warn';
        }

        return array(
            'has_token'            => $hasToken,
            'token_length'         => $hasToken ? strlen($token) : 0,
            'token_source'         => $tokenSource,
            'expires_at'           => $expiresAt,
            'is_jwt'               => $isJwt,
            'is_expired'           => $isExpired,
            'expiring_soon'        => $expiringSoon,
            'fetched_at'           => $fetchedAt,
            'auto_refresh_ready'   => $autoRefreshReady,
            'auto_refresh_blockers'=> $blockers,
            'env_token_overrides_db' => $envOverrides,
            'status_level'         => $statusLevel,
            'college_id'           => $collegeId,
        );
    }
}
