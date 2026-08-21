<?php
/**
 * Tail latest UGC log rows (CLI helper).
 *
 * Usage:
 *   php tools/ugc_log_tail.php --limit=20
 */

function arg(string $name, ?string $default = null): ?string {
    foreach ($_SERVER['argv'] as $a) {
        if (str_starts_with($a, "--{$name}=")) {
            return substr($a, strlen("--{$name}="));
        }
    }
    return $default;
}

$limit = (int) arg('limit', '15');
if ($limit < 1) $limit = 15;
if ($limit > 200) $limit = 200;

$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}
$q = $db->query("SELECT id,created_at,module,http_method,endpoint,http_code,trace_id,message
                 FROM ugc_error_logs
                 ORDER BY id DESC
                 LIMIT " . (int) $limit);
if (!$q) {
    fwrite(STDERR, $db->error . PHP_EOL);
    exit(2);
}
while ($r = $q->fetch_assoc()) {
    $msg = isset($r['message']) ? (string) $r['message'] : '';
    $msg = preg_replace('/\s+/', ' ', $msg);
    if (strlen($msg) > 120) $msg = substr($msg, 0, 120) . '...';
    echo $r['id'] . ' | ' . $r['created_at'] . ' | ' . $r['module'] . ' | ' . $r['http_method'] . ' | HTTP ' . $r['http_code']
        . ' | traceId=' . ($r['trace_id'] ?: '-') . ' | ' . $msg . PHP_EOL;
}

