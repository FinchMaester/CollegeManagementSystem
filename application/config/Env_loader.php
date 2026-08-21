<?php
/**
 * Load .env into getenv() / $_ENV before CodeIgniter bootstrap.
 * Included from index.php (see bottom of that file).
 *
 * Create FCPATH .env from .env.example — never commit real secrets.
 */

if (!function_exists('app_env')) {
    /**
     * Read environment variable (from putenv / $_ENV).
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    function app_env($key, $default = '')
    {
        $v = getenv($key);
        if ($v !== false) {
            return $v;
        }
        if (isset($_ENV[$key])) {
            return (string) $_ENV[$key];
        }
        return $default;
    }
}

if (!function_exists('load_application_environment')) {
    /**
     * Parse FCPATH .env (KEY=value, # comments, optional "quotes").
     *
     * @return void
     */
    function load_application_environment()
    {
        if (!defined('FCPATH')) {
            return;
        }
        $path = FCPATH . '.env';
        if (!is_readable($path)) {
            return;
        }
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if ($name === '') {
                continue;
            }
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $q = $value[0];
                if (strlen($value) >= 2 && substr($value, -1) === $q) {
                    $value = substr($value, 1, -1);
                }
            }
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        if (defined('APPPATH')) {
            $sess = rtrim(APPPATH, "/\\") . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'sessions';
            if (!is_dir($sess)) {
                @mkdir($sess, 0770, true);
            }
        }
    }
}
