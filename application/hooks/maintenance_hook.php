<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Check whether the site is offline or not.
 *
 */
class Maintenance_hook
{
    public function __construct()
    {
        log_message('debug', 'Accessing maintenance hook!');
    }

    public function offline_check()
    {
        // Skip maintenance mode for API requests
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($request_uri, '/api/') !== false) {
            return;
        }

        if (file_exists(APPPATH . 'config/maintenance_config.php')) {
            include APPPATH . 'config/maintenance_config.php';

            $remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

            if (!in_array($remoteAddr, $config['maintenance_ips'], true)
                && (isset($config['maintenance_mode']) && $config['maintenance_mode'] === true)) {
                include APPPATH . 'views/maintenance.php';
                exit;
            }
        }
    }
}