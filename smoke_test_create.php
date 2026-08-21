<?php
/**
 * CLI smoke test: multipart POST to /api/Student/Create with two dummy images.
 *
 * Usage (from project root):
 *   php smoke_test_create.php
 *
 * Optional environment:
 *   SMOKE_BASE_URL  default http://localhost:8000/index.php/api/Student/Create
 *   SMOKE_BEARER    default test123456
 *   SMOKE_DB_HOST, SMOKE_DB_USER, SMOKE_DB_PASS, SMOKE_DB_NAME
 */

declare(strict_types=1);

$baseUrl = getenv('SMOKE_BASE_URL') ?: 'http://localhost:8000/index.php/api/Student/Create';
$bearer  = getenv('SMOKE_BEARER') ?: 'test123456';

$dbHost = getenv('SMOKE_DB_HOST') ?: 'localhost';
$dbUser = getenv('SMOKE_DB_USER') ?: 'root';
$dbPass = getenv('SMOKE_DB_PASS') ?: 'root';
$dbName = getenv('SMOKE_DB_NAME') ?: 'a_final_college';

// --- Tiny valid PNG (1x1 transparent) ---
$pngB64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
$tmpDir = sys_get_temp_dir();
$ppPath = $tmpDir . DIRECTORY_SEPARATOR . 'smoke_pp_' . uniqid('', true) . '.png';
$czPath = $tmpDir . DIRECTORY_SEPARATOR . 'smoke_cz_' . uniqid('', true) . '.png';
file_put_contents($ppPath, base64_decode($pngB64, true));
file_put_contents($czPath, base64_decode($pngB64, true));

// --- Pull a valid (parent_id, class_id, section_id, program_id) from an existing student ---
$parentId = $classId = $sectionId = $programId = null;
$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "DB connect failed: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8');
$parentId  = (int) (getenv('SMOKE_PARENT_ID') ?: 0);
$classId   = (int) (getenv('SMOKE_CLASS_ID') ?: 0);
$sectionId = (int) (getenv('SMOKE_SECTION_ID') ?: 0);
$programId = (int) (getenv('SMOKE_PROGRAM_ID') ?: 0);

if (!$parentId || !$classId || !$sectionId || !$programId) {
    $res = $mysqli->query(
        'SELECT parent_id, class_id, section_id, program_id FROM students
         WHERE parent_id > 0 AND class_id > 0 AND section_id > 0 AND program_id > 0
         ORDER BY id DESC LIMIT 1'
    );
    if ($res && $row = $res->fetch_assoc()) {
        $parentId  = (int) $row['parent_id'];
        $classId   = (int) $row['class_id'];
        $sectionId = (int) $row['section_id'];
        $programId = (int) $row['program_id'];
    }
    if ($res) {
        $res->free();
    }
}

if (!$parentId || !$classId || !$sectionId || !$programId) {
    fwrite(STDERR, "Set SMOKE_PARENT_ID, SMOKE_CLASS_ID, SMOKE_SECTION_ID, SMOKE_PROGRAM_ID or ensure students has at least one row and DB credentials are correct.\n");
    fwrite(STDERR, 'DB error: ' . $mysqli->error . "\n");
    @unlink($ppPath);
    @unlink($czPath);
    $mysqli->close();
    exit(1);
}

// --- Full HEMIS-shaped form fields (camelCase matches api/Student::create_post) ---
$fields = array(
    'firstName'             => 'Smoke',
    'lastName'              => 'ApiCreate',
    'middleName'            => 'Test',
    'gender'                => 'male',
    'phoneNumber'           => '9800000000',
    'email'                 => 'smoke.api.' . time() . '@example.test',
    'admissionYearId'       => '2079',
    'campusId'              => '1',
    'nepaliName'            => 'स्मोक टेस्ट प्रयोगकर्ता',
    'doBBS'                 => '2080-01-15',
    'doBAD'                 => '2024-05-28',
    'ethnicity'             => 'General',
    'nationality'           => 'Nepali',
    'disabilityStatus'      => 'no',
    'citizenshipNo'         => '12-34-56-78901',
    'citizenIssueDist'      => 'Kathmandu',
    'pProvince'             => '3',
    'pDistrict'             => '23',
    'pLocalLevel'           => '274',
    'pWardNo'               => '5',
    'pBlockNo'              => '',
    'pHouseNo'              => '10',
    'pLocality'             => 'Baneshwor',
    'isSameAsPermanent'     => '1',
    'fatherName'            => 'Smoke Father',
    'guardianName'          => 'Smoke Guardian',
    'guardianPhone'         => '9800000001',
    'programId'             => (string) $programId,
    'programName'           => 'Bachelors of Business Studies',
    'levelName'             => 'Year 1',
    'facultyName'           => 'Management',
    'complitionYear'        => '2028',
    'dateOfEnrollment'      => '2080-01-01',
    'fiscalYearId'          => '1',
    'year'                  => '1',
    'programType'           => 'annual',
    'religion'              => 'Hindu',
    'parentId'              => (string) $parentId,
    'classId'               => (string) $classId,
    'sectionId'             => (string) $sectionId,
    'guardianIs'            => 'father',
    'academicProgramDuration' => '4',
);

$post = $fields;
$post['ppSizePhoto']     = new CURLFile($ppPath, 'image/png', 'pp_smoke.png');
$post['citizenshipFront'] = new CURLFile($czPath, 'image/png', 'citizenship_smoke.png');

$ch = curl_init($baseUrl);
curl_setopt_array($ch, array(
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $post,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => array(
        'Authorization: Bearer ' . $bearer,
        'Accept: application/json',
    ),
));

$body = curl_exec($ch);
$errno = curl_errno($ch);
$err  = curl_error($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

@unlink($ppPath);
@unlink($czPath);

echo "=== HTTP {$code} ===\n";
if ($errno) {
    echo "cURL error {$errno}: {$err}\n";
}

echo "=== Response body ===\n";
echo $body . "\n";

$decoded = json_decode((string) $body, true);
$studentId = null;
if (is_array($decoded)) {
    if (isset($decoded['data']['id'])) {
        $studentId = (int) $decoded['data']['id'];
    } elseif (isset($decoded['id'])) {
        $studentId = (int) $decoded['id'];
    }
}

if (is_array($decoded) && !empty($decoded['data']['hemisSync'])) {
    echo "\n=== hemisSync (API response) ===\n";
    echo json_encode($decoded['data']['hemisSync'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

if ($studentId) {
    $stmt = $mysqli->prepare('SELECT id, hemis_student_id, hemis_sync_error FROM students WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    echo "\n=== DB row (student id {$studentId}) ===\n";
    print_r($r);
    if (!empty($r['hemis_sync_error'])) {
        echo "\n=== hemis_sync_error (sync failed or remote error) ===\n";
        echo $r['hemis_sync_error'] . "\n";
    } else {
        echo "\n=== hemis_sync_error ===\n(null or empty — sync did not persist an error string)\n";
    }
} else {
    echo "\n=== DB follow-up skipped (no student id in JSON response)\n";
}

$mysqli->close();
exit($code >= 400 ? 1 : 0);
