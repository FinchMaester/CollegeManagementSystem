<?php
/**
 * Simple CLI audit: compute union-permission counts for one or more role IDs.
 *
 * Usage:
 *   php tools/rbac_union_audit.php --role-ids=1,2,7
 */

function arg(string $name, ?string $default = null): ?string
{
    foreach ($_SERVER['argv'] as $a) {
        if (str_starts_with($a, "--{$name}=")) {
            return substr($a, strlen("--{$name}="));
        }
    }
    return $default;
}

$roleIdsRaw = (string) arg('role-ids', '');
if ($roleIdsRaw === '') {
    fwrite(STDERR, "Missing --role-ids\n");
    exit(1);
}
$roleIds = array();
foreach (explode(',', $roleIdsRaw) as $p) {
    $id = (int) trim($p);
    if ($id > 0) {
        $roleIds[$id] = $id;
    }
}
$roleIds = array_values($roleIds);
sort($roleIds);
if (empty($roleIds)) {
    fwrite(STDERR, "No valid role IDs\n");
    exit(1);
}

$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(2);
}

$in = implode(',', array_map('intval', $roleIds));
$sql = "SELECT pc.short_code, rp.can_view, rp.can_add, rp.can_edit, rp.can_delete
        FROM roles_permissions rp
        INNER JOIN permission_category pc ON pc.id = rp.perm_cat_id
        WHERE rp.role_id IN ($in)";
$res = $db->query($sql);
if (!$res) {
    fwrite(STDERR, $db->error . PHP_EOL);
    exit(3);
}

$merged = array();
$rows = 0;
while ($r = $res->fetch_assoc()) {
    $rows++;
    $cat = (string) $r['short_code'];
    if ($cat === '') {
        continue;
    }
    if (!isset($merged[$cat])) {
        $merged[$cat] = array('can_view' => false, 'can_add' => false, 'can_edit' => false, 'can_delete' => false);
    }
    foreach (array('can_view', 'can_add', 'can_edit', 'can_delete') as $p) {
        $merged[$cat][$p] = $merged[$cat][$p] || ((int) $r[$p] === 1);
    }
}

$trueCount = 0;
foreach ($merged as $cat => $perms) {
    foreach ($perms as $p => $v) {
        if ($v) {
            $trueCount++;
        }
    }
}

echo "roles=" . implode(',', $roleIds) . PHP_EOL;
echo "db_rows=" . $rows . PHP_EOL;
echo "categories=" . count($merged) . PHP_EOL;
echo "true_permission_flags=" . $trueCount . PHP_EOL;

