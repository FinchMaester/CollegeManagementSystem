<?php
$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}

$q1 = $db->query('SELECT COUNT(*) c FROM permission_category');
$c1 = $q1 ? $q1->fetch_assoc()['c'] : '?';

$q2 = $db->query('SELECT COUNT(*) c FROM roles_permissions WHERE role_id=7');
$c2 = $q2 ? $q2->fetch_assoc()['c'] : '?';

echo "permission_category_count={$c1}\n";
echo "roles_permissions_count_role7={$c2}\n";

$q3 = $db->query('SELECT rp.*, pc.short_code FROM roles_permissions rp INNER JOIN permission_category pc ON pc.id=rp.perm_cat_id WHERE rp.role_id=7 LIMIT 5');
if ($q3 && $q3->num_rows > 0) {
    echo "sample_role7_rows:\n";
    while ($r = $q3->fetch_assoc()) {
        echo "- {$r['short_code']} view={$r['can_view']} add={$r['can_add']} edit={$r['can_edit']} del={$r['can_delete']}\n";
    }
} else {
    echo "sample_role7_rows: (none)\n";
}

