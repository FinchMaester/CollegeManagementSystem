<?php
/**
 * Seed missing permission_category short_code=superadmin and grant role_id=7 full rights.
 *
 * Usage:
 *   php tools/seed_superadmin_permission_category.php
 */

$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

// 1) Ensure permission_category exists
$q = $db->query("SELECT id FROM permission_category WHERE short_code='superadmin' LIMIT 1");
$permCatId = null;
if ($q && $q->num_rows > 0) {
    $permCatId = (int) $q->fetch_assoc()['id'];
} else {
    $name = 'Superadmin Tools';
    $short = 'superadmin';
    $stmt = $db->prepare("INSERT INTO permission_category (name, short_code) VALUES (?,?)");
    if (!$stmt) {
        fwrite(STDERR, $db->error . PHP_EOL);
        exit(2);
    }
    $stmt->bind_param('ss', $name, $short);
    if (!$stmt->execute()) {
        fwrite(STDERR, $stmt->error . PHP_EOL);
        exit(3);
    }
    $permCatId = (int) $stmt->insert_id;
    $stmt->close();
}

// 2) Ensure role 7 has full rights on it
$roleId = 7;
$q2 = $db->query("SELECT 1 FROM roles_permissions WHERE role_id=" . (int) $roleId . " AND perm_cat_id=" . (int) $permCatId . " LIMIT 1");
if ($q2 && $q2->num_rows > 0) {
    $db->query("UPDATE roles_permissions SET can_view=1, can_add=1, can_edit=1, can_delete=1
                WHERE role_id=" . (int) $roleId . " AND perm_cat_id=" . (int) $permCatId);
    echo "permission_category(superadmin) exists: id={$permCatId}; updated role7 permissions\n";
    exit(0);
}

$db->query("INSERT INTO roles_permissions (role_id, perm_cat_id, can_view, can_add, can_edit, can_delete)
            VALUES (" . (int) $roleId . ", " . (int) $permCatId . ", 1, 1, 1, 1)");

echo "permission_category(superadmin) ready: id={$permCatId}; inserted role7 permissions\n";

