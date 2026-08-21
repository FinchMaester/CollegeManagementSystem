<?php
$db = new mysqli('localhost', 'root', 'root', 'a_final_college');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . PHP_EOL);
    exit(1);
}
$res = $db->query("SELECT token FROM tokens WHERE expired=0 ORDER BY id DESC LIMIT 1");
if (!$res || $res->num_rows < 1) {
    fwrite(STDERR, "no token\n");
    exit(2);
}
$row = $res->fetch_assoc();
echo (string) $row['token'];

