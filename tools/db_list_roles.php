<?php
$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}

$r = $db->query('SELECT id,name FROM roles ORDER BY id ASC');
if (!$r) {
    fwrite(STDERR, $db->error . PHP_EOL);
    exit(2);
}
echo "roles:\n";
while ($row = $r->fetch_assoc()) {
    echo $row['id'] . ' ' . $row['name'] . "\n";
}

$c = $db->query('SELECT COUNT(*) c FROM permission_category');
$cc = $c ? $c->fetch_assoc() : array('c' => '?');
echo "permission_categories=" . $cc['c'] . "\n";

$c2 = $db->query("SELECT id, short_code, name FROM permission_category WHERE short_code IN ('superadmin','super_admin','super_admin_visibility') OR name LIKE '%Superadmin%' OR name LIKE '%Super Admin%' LIMIT 20");
echo "permission_category_superadmin_like:\n";
if ($c2 && $c2->num_rows > 0) {
    while ($r = $c2->fetch_assoc()) {
        echo "- {$r['id']} {$r['short_code']} {$r['name']}\n";
    }
} else {
    echo "- (none)\n";
}

