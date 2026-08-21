<?php
/**
 * Router script for PHP built-in server (development only).
 * Usage: php -S 127.0.0.1:8000 router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && $uri !== '' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}
require __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
