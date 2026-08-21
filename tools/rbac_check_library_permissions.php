<?php
/**
 * Quick DB check: show permission_category rows related to library + library_card.
 *
 * Usage:
 *   php tools/rbac_check_library_permissions.php
 */

$host = getenv('DB_HOSTNAME') ?: 'localhost';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'root';
$name = getenv('DB_DATABASE') ?: 'a_final_college';

$db = new mysqli($host, $user, $pass, $name);
if ($db->connect_error) {
    fwrite(STDERR, "DB connect failed: " . $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

echo "db={$name}\n";

// 1) Find the perm_group_id used by existing issue_return (Library usually has this)
$issue = $db->query("SELECT id, name, short_code, perm_group_id FROM permission_category WHERE short_code='issue_return' LIMIT 1");
if ($issue && $issue->num_rows > 0) {
    $r = $issue->fetch_assoc();
    echo "issue_return: id={$r['id']} perm_group_id={$r['perm_group_id']} name={$r['name']}\n";
} else {
    echo "issue_return: (not found)\n";
}

// 2) Check whether library_card exists
$lc = $db->query("SELECT id, name, short_code, perm_group_id FROM permission_category WHERE short_code='library_card' LIMIT 1");
if ($lc && $lc->num_rows > 0) {
    $r = $lc->fetch_assoc();
    echo "library_card: id={$r['id']} perm_group_id={$r['perm_group_id']} name={$r['name']}\n";
} else {
    echo "library_card: (not found)\n";
}

// 3) List all permission_category rows under the same perm_group_id as issue_return (if any)
$issue2 = $db->query("SELECT perm_group_id FROM permission_category WHERE short_code='issue_return' LIMIT 1");
$gid = null;
if ($issue2 && $issue2->num_rows > 0) {
    $gid = (int) $issue2->fetch_assoc()['perm_group_id'];
}
if ($gid) {
    echo "\nLibrary group categories (perm_group_id={$gid}):\n";
    $res = $db->query("SELECT id, name, short_code FROM permission_category WHERE perm_group_id={$gid} ORDER BY id");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "- {$r['id']} {$r['short_code']} ({$r['name']})\n";
        }
    }
}

// 4) Try heuristic listing by name/short_code contains library
echo "\nHeuristic search (short_code/name contains 'library'):\n";
$res2 = $db->query("SELECT id, name, short_code, perm_group_id FROM permission_category WHERE short_code LIKE '%library%' OR name LIKE '%Library%' ORDER BY perm_group_id, id");
if ($res2) {
    while ($r = $res2->fetch_assoc()) {
        echo "- {$r['id']} {$r['short_code']} gid={$r['perm_group_id']} ({$r['name']})\n";
    }
}

