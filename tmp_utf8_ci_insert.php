<?php
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');
define('ENVIRONMENT', 'development');

require BASEPATH . 'database/DB.php';
require BASEPATH . 'database/DB_driver.php';
require BASEPATH . 'database/DB_query_builder.php';
require BASEPATH . 'database/drivers/mysqli/mysqli_driver.php';
require BASEPATH . 'database/drivers/mysqli/mysqli_result.php';
require BASEPATH . 'database/drivers/mysqli/mysqli_forge.php';
require BASEPATH . 'database/drivers/mysqli/mysqli_utility.php';
require APPPATH . 'config/database.php';

$active_group = 'default';
$db_config = $db[$active_group];

$CI_DB = new CI_DB($db_config);
$CI_DB->initialize();

echo "CI char_set: " . $CI_DB->char_set . "\n";
$q = $CI_DB->query("SHOW VARIABLES LIKE 'character_set_client'");
echo "client: " . $q->row_array()['Value'] . "\n";

$data = array(
    'admission_no' => 'UTF8TEST_' . time(),
    'firstname' => 'Ram',
    'first_name_np' => 'राम',
    'last_name_np' => 'सिंह',
    'dob' => '2000-01-01',
    'gender' => 'Male',
    'is_active' => 'yes',
);
$fields = $CI_DB->list_fields('students');
$insert = array();
foreach ($data as $k => $v) {
    if (in_array($k, $fields, true)) {
        $insert[$k] = $v;
    }
}

$CI_DB->insert('students', $insert);
$id = $CI_DB->insert_id();
echo "inserted id: $id\n";
$q2 = $CI_DB->select('first_name_np, last_name_np')->where('id', $id)->get('students');
echo "read back: " . json_encode($q2->row_array(), JSON_UNESCAPED_UNICODE) . "\n";
$CI_DB->where('id', $id)->delete('students');
