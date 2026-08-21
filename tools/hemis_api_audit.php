<?php
/**
 * Comprehensive HEMIS campus API audit runner (local).
 *
 * Usage:
 *   php tools/hemis_api_audit.php --base-url=http://localhost:8000 --token=test123456
 *
 * Exit codes:
 *   0 = all endpoints returned 2xx
 *   2 = at least one endpoint failed (non-2xx)
 */

function arg(string $name, ?string $default = null): ?string {
    foreach ($_SERVER['argv'] as $a) {
        if (str_starts_with($a, "--{$name}=")) {
            return substr($a, strlen("--{$name}="));
        }
    }
    return $default;
}

$baseUrl = rtrim((string) arg('base-url', 'http://localhost:8000'), '/');
$token = (string) arg('token', '');
if ($token === '') {
    fwrite(STDERR, "Missing --token\n");
    exit(1);
}

function http_json(string $method, string $url, string $token, ?array $payload = null): array {
    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [
        'method' => $method,
        'url' => $url,
        'http_code' => $code,
        'curl_error' => $err ?: null,
        'body' => is_string($body) ? $body : '',
    ];
}

function http_multipart(string $method, string $url, string $token, array $fields): array {
    $ch = curl_init($url);
    $postFields = [];
    foreach ($fields as $k => $v) {
        $postFields[$k] = (string) $v;
    }
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postFields,
    ]);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [
        'method' => $method,
        'url' => $url,
        'http_code' => $code,
        'curl_error' => $err ?: null,
        'body' => is_string($body) ? $body : '',
    ];
}

function decode_json_body(string $body): mixed {
    $t = trim($body);
    if ($t === '') return null;
    $j = json_decode($t, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $j : null;
}

function endpoint(string $baseUrl, string $path): string {
    return $baseUrl . '/' . ltrim($path, '/');
}

$results = [];

// Reference GETs
$results[] = http_json('GET', endpoint($baseUrl, 'api/Address/getall'), $token);
$results[] = http_json('GET', endpoint($baseUrl, 'api/Batch'), $token);
$results[] = http_json('GET', endpoint($baseUrl, 'api/Fiscalyear'), $token);
$results[] = http_json('GET', endpoint($baseUrl, 'api/EmployeePositions'), $token);
$results[] = http_json('GET', endpoint($baseUrl, 'api/ProgramMgmt/GetCollegePrograms'), $token);

// Buildings create -> view -> update
$b = http_json('POST', endpoint($baseUrl, 'api/Buildings'), $token, [
    'houseName' => 'QA Building ' . date('YmdHis'),
    'areaCoveredByBuilding' => 123.4,
    'noOfClassrooms' => 10,
    'areaCoveredByAllRooms' => 456.7,
    'ownershipOfBuilding' => 'Owned',
    'hasInternetConnection' => 'Yes',
    'remarks' => 'QA audit',
]);
$results[] = $b;
$bJson = decode_json_body($b['body']);
$buildingId = is_array($bJson) && isset($bJson['id']) ? (int) $bJson['id'] : 0;
if ($buildingId > 0) {
    $results[] = http_json('GET', endpoint($baseUrl, "api/Buildings/view/$buildingId"), $token);
    $results[] = http_json('PUT', endpoint($baseUrl, "api/Buildings/$buildingId"), $token, ['remarks' => 'QA audit updated']);
}
$buildingIdForChild = $buildingId > 0 ? $buildingId : 1;

// Library create -> view -> update (critical fields)
$lib = http_json('POST', endpoint($baseUrl, 'api/Library'), $token, [
    'libraryName' => 'QA Library ' . date('YmdHis'),
    'buildingId' => $buildingIdForChild,
    'campusId' => 1,
    'noOfLibraryRooms' => 2,
    'totalAreaOfLibraryRoom' => 250.0,
    'noOfReadingHalls' => 1,
    'areaOfReadingHall' => 150.5,
    'noOfBooks' => 5000,
    'noOfDailyJournals' => 1,
    'noOfWeeklyJournals' => 1,
    'noOfMonthlyJournals' => 1,
    'noOfAnnualJournals' => 1,
    'hasELibraryFacility' => true,
    'remarks' => 'QA audit',
]);
$results[] = $lib;
$libJson = decode_json_body($lib['body']);
$libraryId = is_array($libJson) && isset($libJson['id']) ? (int) $libJson['id'] : 0;
if ($libraryId > 0) {
    $results[] = http_json('GET', endpoint($baseUrl, "api/Library/view/$libraryId"), $token);
    $results[] = http_json('PUT', endpoint($baseUrl, "api/Library/$libraryId"), $token, ['remarks' => 'QA audit updated']);
}

// FEV create -> view -> update
$fev = http_json('POST', endpoint($baseUrl, 'api/FurnitureEquipmentVehicle'), $token, [
    'itemType' => 'Furniture',
    'itemName' => 'QA Desk ' . date('YmdHis'),
    'noOfQty' => 3,
    'itemDescription' => 'QA audit',
    'remarks' => 'QA audit',
]);
$results[] = $fev;
$fevJson = decode_json_body($fev['body']);
$fevId = is_array($fevJson) && isset($fevJson['id']) ? (int) $fevJson['id'] : 0;
if ($fevId > 0) {
    $results[] = http_json('GET', endpoint($baseUrl, "api/FurnitureEquipmentVehicle/view/$fevId"), $token);
    $results[] = http_json('PUT', endpoint($baseUrl, "api/FurnitureEquipmentVehicle/$fevId"), $token, ['remarks' => 'QA audit updated']);
}

// Hostels, Lands, Labs (create smoke)
$results[] = http_json('POST', endpoint($baseUrl, 'api/Hostels'), $token, [
    'hostelType' => 'Boys',
    'noOfRoomsInHostel' => 5,
    'noOfSeats' => 20,
    'areaCoveredByHostel' => 300.0,
    'buildingId' => $buildingIdForChild,
    'hasPlayground' => true,
    'hasInternet' => true,
    'hasDrinkingWater' => true,
    'hasToilet' => true,
    'remarks' => 'QA audit',
]);
$results[] = http_json('POST', endpoint($baseUrl, 'api/Lands'), $token, [
    'totalArea' => 1000.0,
    'unit' => 'sqft',
    'sheetNo' => 'QA-' . date('YmdHis'),
    'kittaNo' => '1',
    'ownerShip' => 'Owned',
    'remarks' => 'QA audit',
]);
$results[] = http_json('POST', endpoint($baseUrl, 'api/Labs'), $token, [
    'labName' => 'QA Lab ' . date('YmdHis'),
    'buildingId' => $buildingIdForChild,
    'areaCoveredByLab' => 120.0,
    'labType' => 'Computer',
    'hasInternetConnection' => true,
    'remarks' => 'QA audit',
]);

// Management create smoke
$results[] = http_json('POST', endpoint($baseUrl, 'api/Management/add_department'), $token, [
    'departmentName' => 'QA Dept ' . date('YmdHis'),
    'remarks' => 'QA',
]);
$results[] = http_json('POST', endpoint($baseUrl, 'api/Management/add_section'), $token, [
    'sectionName' => 'QA Section ' . date('YmdHis'),
    'remarks' => 'QA',
]);

// Employee create (critical spouse_name + joiningDate)
$results[] = http_json('POST', endpoint($baseUrl, 'api/Employee'), $token, [
    'firstName' => 'QA',
    'middleName' => '',
    'lastName' => 'Employee',
    'phoneNumber' => '9800000000',
    'gender' => 'male',
    'email' => 'qa.employee.' . date('YmdHis') . '@example.test',
    'ethnicity' => 'Brahmin',
    'campusId' => 1,
    'postName' => 'Lecturer',
    'joiningDate' => '2082-01-01',
    'spouseName' => 'QA Spouse',
    'dependentInfo' => 'QA dependent info',
]);

// Student Create multipart (critical path)
// Create two students so Graduation can be tested on an ACTIVE student, and DropOut can inactivate a different one.
$stuCreate = http_multipart('POST', endpoint($baseUrl, 'api/Student/Create'), $token, [
    'firstName' => 'QA',
    'middleName' => '',
    'lastName' => 'StudentLifecycle',
    'gender' => 'male',
    'admissionYearId' => '2082',
    'campusId' => '1',
    'nepaliName' => 'क्यूए विद्यार्थी',
    'doBBS' => '2067-02-15',
    'doBAD' => '2010-05-29',
    'ethnicity' => 'Brahmin',
    'nationality' => 'Nepalese',
    'disabilityStatus' => 'No',
    'pProvince' => 'Bagmati',
    'pDistrict' => 'Kathmandu',
    'pLocalLevel' => 'Kathmandu',
    'pWardNo' => '1',
    'fatherName' => 'QA Father',
    'guardianName' => 'QA Guardian',
    'programId' => '5',
    'programName' => 'Bachelors of Business Studies',
    'levelName' => 'Bachelors - 1st Year',
    'facultyName' => 'Faculty of Management',
    'complitionYear' => '2086',
    'dateOfEnrollment' => '2082-01-01',
    'fiscalYearId' => '1',
    'year' => '1',
    'programType' => 'Semester',
    'religion' => 'Hindu',
]);
$results[] = $stuCreate;
$stuJson = decode_json_body($stuCreate['body']);
$studentId = 0;
if (is_array($stuJson)) {
    if (isset($stuJson['data']['id'])) {
        $studentId = (int) $stuJson['data']['id'];
    } elseif (isset($stuJson['studentId'])) {
        $studentId = (int) $stuJson['studentId'];
    }
}

// Student list/view
$results[] = http_json('GET', endpoint($baseUrl, 'api/Student/GetAllStudent'), $token);
if ($studentId > 0) {
    $results[] = http_json('GET', endpoint($baseUrl, "api/Student/view/$studentId"), $token);
}

// StudentUpgrade (JSON array) + Graduation tests on ACTIVE student
if ($studentId > 0) {
    $results[] = http_json('POST', endpoint($baseUrl, 'api/StudentUpgrade'), $token, [[
        'studentId' => $studentId,
        'programId' => 5,
        'year' => '2',
        'semester' => '1',
        'batchId' => 1,
    ]]);
    // Graduation (form-data / multipart endpoints)
    $results[] = http_json('GET', endpoint($baseUrl, 'api/Graduation'), $token);
    $results[] = http_multipart('POST', endpoint($baseUrl, 'api/Graduation/GenerateGraduationApplication'), $token, [
        'StudentID' => (string) $studentId,
        'remarks' => 'QA audit graduation',
    ]);
    $results[] = http_multipart('POST', endpoint($baseUrl, 'api/Graduation/GenerateGraduationApplicationForStudent'), $token, [
        'ApplicantNameEng' => 'QA Applicant ' . date('YmdHis'),
        'ProgramMgmtId' => '1',
        'remarks' => 'QA audit graduation (for student)',
    ]);
}

// Create a second student to test DropOut (this will flip is_active to 'no')
$stuDrop = http_multipart('POST', endpoint($baseUrl, 'api/Student/Create'), $token, [
    'firstName' => 'QA',
    'middleName' => '',
    'lastName' => 'StudentDropout',
    'gender' => 'male',
    'admissionYearId' => '2082',
    'campusId' => '1',
    'nepaliName' => 'क्यूए विद्यार्थी',
    'doBBS' => '2067-02-15',
    'doBAD' => '2010-05-29',
    'ethnicity' => 'Brahmin',
    'nationality' => 'Nepalese',
    'disabilityStatus' => 'No',
    'pProvince' => 'Bagmati',
    'pDistrict' => 'Kathmandu',
    'pLocalLevel' => 'Kathmandu',
    'pWardNo' => '1',
    'fatherName' => 'QA Father',
    'guardianName' => 'QA Guardian',
    'programId' => '5',
    'programName' => 'Bachelors of Business Studies',
    'levelName' => 'Bachelors - 1st Year',
    'facultyName' => 'Faculty of Management',
    'complitionYear' => '2086',
    'dateOfEnrollment' => '2082-01-01',
    'fiscalYearId' => '1',
    'year' => '1',
    'programType' => 'Semester',
    'religion' => 'Hindu',
]);
$results[] = $stuDrop;
$stuDropJson = decode_json_body($stuDrop['body']);
$dropStudentId = 0;
if (is_array($stuDropJson)) {
    if (isset($stuDropJson['data']['id'])) {
        $dropStudentId = (int) $stuDropJson['data']['id'];
    } elseif (isset($stuDropJson['studentId'])) {
        $dropStudentId = (int) $stuDropJson['studentId'];
    }
}
if ($dropStudentId > 0) {
    $results[] = http_json('POST', endpoint($baseUrl, 'api/DropOut'), $token, [
        'studentId' => $dropStudentId,
        'reason' => 'QA audit dropout reason (text)',
        'dateOfEntry' => date('Y-m-d'),
        'remarks' => 'QA audit',
    ]);
}

// Emit compact summary
$fail = 0;
foreach ($results as $r) {
    $ok = ($r['http_code'] >= 200 && $r['http_code'] < 300) && empty($r['curl_error']);
    if (!$ok) $fail++;
    printf(
        "[%s] %s %s\n",
        $ok ? 'OK' : 'FAIL',
        str_pad($r['method'], 6, ' '),
        $r['url'] . ' => ' . $r['http_code'] . ($r['curl_error'] ? (' curl=' . $r['curl_error']) : '')
    );
}

if ($fail > 0) {
    fwrite(STDERR, "\nFailures: $fail\n");
    exit(2);
}

echo "\nAll audited endpoints returned 2xx.\n";
exit(0);

