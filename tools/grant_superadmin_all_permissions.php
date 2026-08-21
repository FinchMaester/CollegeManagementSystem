<?php
/**
 * Seed/fix: grant role_id=7 (Super Admin) full permissions on all permission_category rows.
 *
 * Usage:
 *   php tools/grant_superadmin_all_permissions.php
 *
 * Safe to run multiple times (idempotent).
 */

$ROLE_ID = 7;

$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

$cats = $db->query('SELECT id FROM permission_category');
if (!$cats) {
    fwrite(STDERR, $db->error . PHP_EOL);
    exit(2);
}

$existing = array();
$q = $db->query('SELECT perm_cat_id FROM roles_permissions WHERE role_id=' . (int) $ROLE_ID);
if ($q) {
    while ($r = $q->fetch_assoc()) {
        $existing[(int) $r['perm_cat_id']] = true;
    }
}

$ins = 0;
$skip = 0;
while ($c = $cats->fetch_assoc()) {
    $cid = (int) $c['id'];
    if ($cid < 1) {
        continue;
    }
    if (isset($existing[$cid])) {
        $skip++;
        continue;
    }
    $sql = "INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete)
            VALUES (" . (int) $ROLE_ID . ", " . (int) $cid . ", 1, 1, 1, 1)";
    if (!$db->query($sql)) {
        fwrite(STDERR, "insert failed for perm_cat_id={$cid}: " . $db->error . PHP_EOL);
        exit(3);
    }
    $ins++;
}

echo "role_id={$ROLE_ID}\n";
echo "inserted={$ins}\n";
echo "skipped_existing={$skip}\n";

