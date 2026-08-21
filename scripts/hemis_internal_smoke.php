#!/usr/bin/env php
<?php
/**
 * Internal HEMIS integration smoke check (no DB required).
 *
 * Usage: php scripts/hemis_internal_smoke.php [--lint]
 *
 * Cross-check: UGC "API Documentation (V1.3)" (e.g. newapi.pdf) — base URL
 * https://hemisapi.ugcnepal.edu.np before /api/...
 */
$root = dirname(__DIR__);
$doLint = in_array('--lint', $argv ?? array(), true);

$checkFiles = array(
    $root . '/application/libraries/Hemis_client.php',
    $root . '/application/libraries/Hemis_rest_auth.php',
    $root . '/application/config/hemis.php',
    $root . '/application/controllers/Mockhemis.php',
    $root . '/application/controllers/api/Student.php',
    $root . '/application/controllers/api/StudentUpgrade.php',
    $root . '/application/controllers/api/Graduation.php',
    $root . '/application/controllers/api/DropOut.php',
    $root . '/application/controllers/api/Library.php',
    $root . '/application/controllers/api/Employee.php',
    $root . '/application/controllers/api/Labs.php',
    $root . '/application/controllers/api/ProgramMgmt.php',
    $root . '/application/controllers/api/Buildings.php',
    $root . '/application/controllers/api/FurnitureEquipmentVehicle.php',
    $root . '/application/controllers/api/Hostels.php',
    $root . '/application/controllers/api/Lands.php',
    $root . '/application/controllers/api/Management.php',
    $root . '/application/controllers/api/Batch.php',
    $root . '/application/controllers/api/Fiscalyear.php',
    $root . '/application/controllers/api/Address.php',
    $root . '/application/controllers/api/EmployeePositions.php',
    $root . '/application/models/Library_model.php',
    $root . '/application/models/Api_building_model.php',
    $root . '/application/models/Api_fev_model.php',
    $root . '/application/models/Api_hostel_model.php',
    $root . '/application/models/Api_land_model.php',
    $root . '/application/models/Management_department_model.php',
    $root . '/application/models/Management_section_model.php',
);

echo "=== HEMIS internal smoke ===\n";
echo "Project: {$root}\n\n";

if ($doLint) {
    echo "--- php -l ---\n";
    $fail = false;
    foreach ($checkFiles as $f) {
        if (!is_file($f)) {
            echo "MISSING: {$f}\n";
            $fail = true;
            continue;
        }
        $cmd = 'php -l ' . escapeshellarg($f) . ' 2>&1';
        $out = array();
        exec($cmd, $out, $code);
        echo implode("\n", $out) . "\n";
        if ($code !== 0) {
            $fail = true;
        }
    }
    echo $fail ? "\nLINT: FAIL\n" : "\nLINT: OK\n";
}

$hc = $root . '/application/libraries/Hemis_client.php';
if (is_file($hc)) {
    $src = file_get_contents($hc);
    preg_match_all('/public function (sync_\w+)\s*\(/', $src, $m);
    echo "\n--- Hemis_client::sync_* (outbound push to UGC) ---\n";
    sort($m[1]);
    echo implode("\n", $m[1]) . "\n";

    preg_match_all('/public function (hemis_\w+)\s*\(/', $src, $mh);
    if (!empty($mh[1])) {
        sort($mh[1]);
        echo "\n--- Hemis_client::hemis_* (inbound / pull) ---\n";
        echo implode("\n", $mh[1]) . "\n";
    }

    echo "\n--- newapi.pdf (V1.3) vs this campus repo ---\n";
    $lines = array(
        'IMPLEMENTED push/sync: Student/Create + PATCH, StudentUpgrade, Graduation (POST apps + PUT), DropOut POST/PUT, Library POST/PUT + GET list/id, Labs POST/PUT, Employee POST + PATCH (PATCH outbound as multipart per PDF).',
        'Infrastructure (PDF §5.1, §5.6): Buildings, FurnitureEquipmentVehicle, Hostels, Lands (local tables + POST/PUT + Hemis_client sync); Management Department/Section (PDF Managent paths for Section).',
        'Reference pulls (PDF §5.2–5.5, §5.10): hemis_get_batch, hemis_get_fiscal_year, hemis_get_address_all, hemis_get_employee_positions (+ by id). Campus proxies: api/Batch, api/FiscalYear, api/Address/GetAll, api/EmployeePositions — enable hemis.php pull_* flags.',
        'ProgramMgmt: local GET GetCollegePrograms returns campus DB array (PDF §5.4). Pull FROM UGC: Hemis_client::hemis_get_college_programs + hemis.php pull_programmgmt.',
        'DB: run application/sql/hemis_infrastructure_tables.sql for api_buildings, api_furniture_equipment_vehicle, api_hostels, api_lands, api_management_*.',
        'Auth: campus APIs use Bearer (auth_model); UGC POST /api/User/Login — set remote_bearer_token for outbound sync/pull.',
    );
    echo implode("\n", $lines) . "\n";
}

echo "\n--- Optional HTTP test (set BASE_URL) ---\n";
echo "Example: set BASE_URL=http://localhost:8000/index.php && curl -s \"%BASE_URL%/api/DropOut\"\n";
echo "Done.\n";
