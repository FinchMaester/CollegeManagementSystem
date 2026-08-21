<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Lightweight operational checks (JSON). Requires staff login.
 */
class Check extends Admin_Controller
{
    /**
     * GET/POST check/health — sessions dir, logs dir, disk space, .env + UGC/HEMIS base URL shape.
     */
    public function health()
    {
        header('Content-Type: application/json; charset=utf-8');

        $sessions = rtrim(APPPATH, "/\\") . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'sessions';
        $logs = rtrim(APPPATH, "/\\") . DIRECTORY_SEPARATOR . 'logs';
        $minBytes = 100 * 1024 * 1024;

        $free = @disk_free_space(FCPATH);
        $envPath = FCPATH . '.env';
        $envReadable = is_readable($envPath);

        $base = '';
        if (function_exists('app_env')) {
            $base = (string) app_env('HEMIS_REMOTE_BASE_URL', '');
            if ($base === '') {
                $base = (string) app_env('UGC_API_BASE_URL', '');
            }
        }

        $urlOk = ($base !== '' && filter_var($base, FILTER_VALIDATE_URL) !== false);

        echo json_encode(array(
            'ok' => true,
            'application_cache_sessions_writable' => is_dir($sessions) && is_writable($sessions),
            'application_logs_writable' => is_dir($logs) && is_writable($logs),
            'disk_free_bytes' => $free !== false ? (int) $free : null,
            'disk_ok_at_least_100mb' => ($free !== false && $free >= $minBytes),
            'dotenv_readable' => $envReadable,
            'api_base_url' => $base !== '' ? $base : null,
            'api_base_url_format_ok' => $urlOk,
        ));
    }
}
