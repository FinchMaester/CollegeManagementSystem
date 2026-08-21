<?php
/**
 * Generate a JWT for testing POST /api/Student/Create locally (Plan A mock).
 *
 * Run from project root:
 *   php scripts/generate_hemis_api_jwt.php
 *
 * Token-only (for scripting): append argument "token"
 *   php scripts/generate_hemis_api_jwt.php token
 *
 * Token uses the same secret as application/config/jwt.php ($config['jwt_secret']).
 */
if (isset($argv[1]) && $argv[1] === 'token') {
    $token_only = true;
} else {
    $token_only = false;
}
$root = dirname(__DIR__);
$configFile = $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'jwt.php';
if (!is_readable($configFile)) {
    fwrite(STDERR, "Cannot read: {$configFile}\n");
    exit(1);
}
if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR);
}
include $configFile;
if (empty($config['jwt_secret'])) {
    fwrite(STDERR, "jwt_secret missing in jwt.php\n");
    exit(1);
}
$secret = $config['jwt_secret'];
$ttl = isset($config['token_expire_time']) ? (int) $config['token_expire_time'] : 86400;
$now = time();
$payload = array(
    'sub' => 'hemis_api_test',
    'iat' => $now,
    'nbf' => $now,
    'exp' => $now + $ttl,
);

$header = json_encode(array('typ' => 'JWT', 'alg' => 'HS256'));
$payloadJson = json_encode($payload);
$base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
$base64UrlPayload = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
$signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $secret, true);
$base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
$jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

if (!$token_only) {
    echo "Paste this value as the Bearer token in Postman (Authorization → Type: Bearer Token):\n\n";
}
echo $jwt . "\n";
