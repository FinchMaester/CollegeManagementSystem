<?php
$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}

$cats = $db->query("SELECT id, short_code, name FROM permission_category WHERE LOWER(short_code)='superadmin' OR short_code LIKE '%superadmin%'");
echo "permission_category matches:\n";
if ($cats && $cats->num_rows > 0) {
    while ($r = $cats->fetch_assoc()) {
        echo "- id={$r['id']} short_code={$r['short_code']} name={$r['name']}\n";
    }
} else {
    echo "- (none)\n";
}

$q = $db->query("SELECT rp.role_id, pc.short_code, rp.can_view, rp.can_add, rp.can_edit, rp.can_delete
                 FROM roles_permissions rp
                 JOIN permission_category pc ON pc.id=rp.perm_cat_id
                 WHERE rp.role_id=7 AND (LOWER(pc.short_code)='superadmin' OR pc.short_code LIKE '%superadmin%')
                 LIMIT 10");
echo "role7 superadmin permissions:\n";
if ($q && $q->num_rows > 0) {
    while ($r = $q->fetch_assoc()) {
        echo "- role_id={$r['role_id']} short_code={$r['short_code']} view={$r['can_view']} add={$r['can_add']} edit={$r['can_edit']} del={$r['can_delete']}\n";
    }
} else {
    echo "- (none)\n";
}

