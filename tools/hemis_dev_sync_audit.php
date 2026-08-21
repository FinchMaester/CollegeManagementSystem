<?php
/**
 * UGC HEMIS DEV multi-module sync audit (V1.3 DEV PDF).
 *
 * Runs:
 * - Login -> bearer token
 * - POST Buildings, FEV, Library
 * - POST Management/AddDepartment, Managent/AddSection
 * - POST Student/Create (multipart), StudentUpgrade, DropOut
 * - POST Employee
 *
 * Usage:
 *   php tools/hemis_dev_sync_audit.php
 */

date_default_timezone_set('Asia/Kathmandu');

$BASE = 'https://hemisapidev.ugcnepal.edu.np';
$COLLEGE_ID = 12;
$LOGIN = [
    'email' => 'testcampus@gmail.com',
    'password' => 'BMC@123',
    'collegeId' => $COLLEGE_ID,
    'UniId' => 0,
    'isUgc' => false,
];

function curl_json(string $method, string $url, array $headers, ?array $payload = null): array {
    $ch = curl_init($url);
    $h = $headers;
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $h,
    ];
    if ($payload !== null) {
        $h[] = 'Content-Type: application/json';
        $opts[CURLOPT_HTTPHEADER] = $h;
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
    curl_setopt_array($ch, $opts);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
    $body = is_string($raw) ? substr($raw, $hsz) : '';
    return ['http_code'=>$code,'body'=>is_string($body)?$body:'','response_headers'=>$respHeaders,'curl_error'=>$err?:null];
}

function curl_multipart(string $method, string $url, array $headers, array $fields): array {
    $ch = curl_init($url);
    $postFields = [];
    foreach ($fields as $k => $v) {
        $postFields[$k] = (string) $v;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postFields,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
    $body = is_string($raw) ? substr($raw, $hsz) : '';
    return ['http_code'=>$code,'body'=>is_string($body)?$body:'','response_headers'=>$respHeaders,'curl_error'=>$err?:null];
}

function ugc_trace_id(?string $body): ?string {
    $d = j($body);
    if (!is_array($d)) return null;
    if (!empty($d['traceId']) && is_string($d['traceId'])) return $d['traceId'];
    if (!empty($d['trace_id']) && is_string($d['trace_id'])) return $d['trace_id'];
    return null;
}

function ugc_message(?string $body): ?string {
    $d = j($body);
    if (!is_array($d)) return null;
    if (!empty($d['message']) && is_string($d['message'])) return $d['message'];
    if (!empty($d['title']) && is_string($d['title'])) return $d['title'];
    if (!empty($d['error']) && is_string($d['error'])) return $d['error'];
    return null;
}

function ugc_log_db(string $module, string $method, string $url, array $reqHeaders, $reqBody, array $res): void {
    $db = new mysqli('localhost', 'root', 'root', 'a_final_college');
    if ($db->connect_error) {
        return;
    }
    $db->set_charset('utf8mb4');
    $db->query("CREATE TABLE IF NOT EXISTS `ugc_error_logs` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `created_at` DATETIME NOT NULL,
        `module` VARCHAR(64) NULL,
        `endpoint` VARCHAR(255) NULL,
        `http_method` VARCHAR(16) NULL,
        `http_code` INT NULL,
        `trace_id` VARCHAR(128) NULL,
        `message` VARCHAR(255) NULL,
        `response_snippet` TEXT NULL,
        `request_headers` MEDIUMTEXT NULL,
        `request_body` MEDIUMTEXT NULL,
        `response_headers` MEDIUMTEXT NULL,
        `response_body` MEDIUMTEXT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_created_at` (`created_at`),
        KEY `idx_module` (`module`),
        KEY `idx_http_code` (`http_code`),
        KEY `idx_trace_id` (`trace_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $colExists = function (string $col) use ($db): bool {
        $stmt = $db->prepare("SELECT COUNT(*) c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='ugc_error_logs' AND COLUMN_NAME=?");
        if (!$stmt) return false;
        $stmt->bind_param('s', $col);
        $stmt->execute();
        $r = $stmt->get_result();
        $row = $r ? $r->fetch_assoc() : null;
        $stmt->close();
        return $row && (int) $row['c'] > 0;
    };

    $adds = [
        'http_method' => "ALTER TABLE `ugc_error_logs` ADD COLUMN `http_method` VARCHAR(16) NULL AFTER `endpoint`",
        'request_headers' => "ALTER TABLE `ugc_error_logs` ADD COLUMN `request_headers` MEDIUMTEXT NULL AFTER `message`",
        'request_body' => "ALTER TABLE `ugc_error_logs` ADD COLUMN `request_body` MEDIUMTEXT NULL AFTER `request_headers`",
        'response_headers' => "ALTER TABLE `ugc_error_logs` ADD COLUMN `response_headers` MEDIUMTEXT NULL AFTER `request_body`",
        'response_body' => "ALTER TABLE `ugc_error_logs` ADD COLUMN `response_body` MEDIUMTEXT NULL AFTER `response_headers`",
    ];
    foreach ($adds as $col => $sql) {
        if (!$colExists($col)) {
            @$db->query($sql);
        }
    }

    $code = (int) ($res['http_code'] ?? 0);
    $body = (string) ($res['body'] ?? '');
    $trace = ugc_trace_id($body);
    $msg = ugc_message($body);
    $snip = substr($body, 0, 2000);
    $rh = (string) ($res['response_headers'] ?? '');
    $rb = is_string($reqBody) ? $reqBody : json_encode($reqBody);

    $stmt = $db->prepare("INSERT INTO ugc_error_logs
        (created_at,module,endpoint,http_method,http_code,trace_id,message,response_snippet,request_headers,request_body,response_headers,response_body)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) return;
    $created = date('Y-m-d H:i:s');
    $m = substr($module, 0, 64);
    $ep = substr($url, 0, 255);
    $hm = substr(strtoupper($method), 0, 16);
    $tr = $trace ? substr($trace, 0, 128) : null;
    $ms = $msg ? substr($msg, 0, 255) : null;
    $reqh = json_encode($reqHeaders);
    $reqb = $rb ?: '';
    $stmt->bind_param('ssssisssssss', $created, $m, $ep, $hm, $code, $tr, $ms, $snip, $reqh, $reqb, $rh, $body);
    $stmt->execute();
    $stmt->close();
}

function j($body) {
    $t = trim((string)$body);
    if ($t === '') return null;
    $d = json_decode($t, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $d : null;
}

function pick_id($decoded) : ?int {
    if (!is_array($decoded)) return null;
    if (isset($decoded['id']) && is_numeric($decoded['id'])) return (int)$decoded['id'];
    if (isset($decoded['data']['id']) && is_numeric($decoded['data']['id'])) return (int)$decoded['data']['id'];
    if (isset($decoded[0]['id']) && is_numeric($decoded[0]['id'])) return (int)$decoded[0]['id'];
    return null;
}

$snippet = function (?string $body): string {
    $t = trim((string) $body);
    if ($t === '') return '';
    $t = preg_replace('/\s+/', ' ', $t);
    return substr($t, 0, 240);
};

$note = function (array $res, string $base) use ($snippet): string {
    $code = (int) ($res['http_code'] ?? 0);
    if ($code >= 200 && $code < 300) {
        return $base;
    }
    $snip = $snippet($res['body'] ?? '');
    return $snip !== '' ? ($base . ' | response=' . $snip) : $base;
};

$ts0 = date('Y-m-d H:i:s');
$headersBase = ['Accept: application/json'];

// 1) Login handshake
$loginRes = curl_json('POST', $BASE . '/api/User/Login', $headersBase, $LOGIN);
$loginHeaders = $headersBase;
$loginHeaders[] = 'Content-Type: application/json';
ugc_log_db('Auth', 'POST', $BASE . '/api/User/Login', $loginHeaders, $LOGIN, $loginRes);
$loginJson = j($loginRes['body']);
$token = '';
if (is_array($loginJson)) {
    if (!empty($loginJson['tokenString'])) $token = (string)$loginJson['tokenString'];
    elseif (!empty($loginJson['token'])) $token = (string)$loginJson['token'];
    elseif (!empty($loginJson['data']['token'])) $token = (string)$loginJson['data']['token'];
    elseif (!empty($loginJson['access_token'])) $token = (string)$loginJson['access_token'];
}

if ($token === '') {
    fwrite(STDERR, "LOGIN FAILED\nHTTP {$loginRes['http_code']}\n{$loginRes['body']}\n");
    exit(2);
}

$authHeaders = [
    'Accept: application/json',
    'Authorization: Bearer ' . $token,
];

$log = [];
$log[] = ['name'=>'Handshake: POST /api/User/Login','url'=>$BASE.'/api/User/Login','http'=>$loginRes['http_code'],'id'=>null,'notes'=>$note($loginRes,'token received')];

// Helper GETs (for audit statement)
$addrRes = curl_json('GET', $BASE . '/api/Address/GetAll', $authHeaders);
ugc_log_db('Address', 'GET', $BASE . '/api/Address/GetAll', $authHeaders, null, $addrRes);
$log[] = ['name'=>'GET /api/Address/GetAll','url'=>$BASE.'/api/Address/GetAll','http'=>$addrRes['http_code'],'id'=>null,'notes'=>$note($addrRes,'federal address reference')];
$batchRes = curl_json('GET', $BASE . '/api/Batch', $authHeaders);
ugc_log_db('Batch', 'GET', $BASE . '/api/Batch', $authHeaders, null, $batchRes);
$batchJson = j($batchRes['body']);
$batchId = null;
if (is_array($batchJson) && isset($batchJson[0]['id']) && is_numeric($batchJson[0]['id'])) $batchId = (int)$batchJson[0]['id'];
$log[] = ['name'=>'GET /api/Batch','url'=>$BASE.'/api/Batch','http'=>$batchRes['http_code'],'id'=>$batchId,'notes'=>$note($batchRes,'picked batchId from first row')];
$fyRes = curl_json('GET', $BASE . '/api/FiscalYear', $authHeaders);
ugc_log_db('FiscalYear', 'GET', $BASE . '/api/FiscalYear', $authHeaders, null, $fyRes);
$fyJson = j($fyRes['body']);
$fiscalYearId = null;
if (is_array($fyJson) && isset($fyJson[0]['id']) && is_numeric($fyJson[0]['id'])) $fiscalYearId = (int)$fyJson[0]['id'];
$log[] = ['name'=>'GET /api/FiscalYear','url'=>$BASE.'/api/FiscalYear','http'=>$fyRes['http_code'],'id'=>$fiscalYearId,'notes'=>$note($fyRes,'picked fiscalYearId from first row')];
$progRes = curl_json('GET', $BASE . '/api/ProgramMgmt/GetCollegePrograms', $authHeaders);
ugc_log_db('ProgramMgmt', 'GET', $BASE . '/api/ProgramMgmt/GetCollegePrograms', $authHeaders, null, $progRes);
$progJson = j($progRes['body']);
$programMgmtId = null;
$programId = null;
$programName = null;
$levelName = null;
$facultyName = null;
$programType = null;
if (is_array($progJson) && isset($progJson[0])) {
    $p0 = $progJson[0];
    if (isset($p0['id']) && is_numeric($p0['id'])) $programMgmtId = (int)$p0['id'];
    if (isset($p0['programId']) && is_numeric($p0['programId'])) $programId = (int)$p0['programId'];
    $programName = isset($p0['programName']) ? (string)$p0['programName'] : null;
    $levelName = isset($p0['levelName']) ? (string)$p0['levelName'] : null;
    $facultyName = isset($p0['facultyName']) ? (string)$p0['facultyName'] : null;
    $programType = isset($p0['programType']) ? (string)$p0['programType'] : null;
}
$log[] = ['name'=>'GET /api/ProgramMgmt/GetCollegePrograms','url'=>$BASE.'/api/ProgramMgmt/GetCollegePrograms','http'=>$progRes['http_code'],'id'=>$programMgmtId,'notes'=>$note($progRes,'picked first assigned program')];

// 2) Buildings
$buildingPayload = [
    'id' => 0,
    'houseName' => 'QA DEV Block ' . date('His'),
    'areaCoveredByBuilding' => 2500,
    'noOfClassrooms' => 10,
    'areaCoveredByAllRooms' => 200,
    'ownershipOfBuilding' => true,
    'hasInternetConnection' => true,
    'remarks' => 'DEV sync audit',
];
$bRes = curl_json('POST', $BASE . '/api/Buildings', $authHeaders, $buildingPayload);
ugc_log_db('Buildings', 'POST', $BASE . '/api/Buildings', $authHeaders, $buildingPayload, $bRes);
$bJson = j($bRes['body']);
$buildingId = pick_id($bJson);
$log[] = ['name'=>'POST /api/Buildings','url'=>$BASE.'/api/Buildings','http'=>$bRes['http_code'],'id'=>$buildingId,'notes'=>$note($bRes,'houseName/noOfClassrooms captured')];

// 3) FEV
$fevPayload = [
    'itemType' => 'Furniture',
    'itemName' => 'QA Chair ' . date('His'),
    'noOfQty' => 3,
    'itemDescription' => 'DEV sync audit',
    'remarks' => 'DEV sync audit',
];
$fRes = curl_json('POST', $BASE . '/api/FurnitureEquipmentVehicle', $authHeaders, $fevPayload);
ugc_log_db('FEV', 'POST', $BASE . '/api/FurnitureEquipmentVehicle', $authHeaders, $fevPayload, $fRes);
$fJson = j($fRes['body']);
$fevId = pick_id($fJson);
$log[] = ['name'=>'POST /api/FurnitureEquipmentVehicle','url'=>$BASE.'/api/FurnitureEquipmentVehicle','http'=>$fRes['http_code'],'id'=>$fevId,'notes'=>$note($fRes,'itemName/noOfQty captured')];

// 4) Library (requires buildingId)
$libraryPayload = [
    'libraryName' => 'QA DEV Library ' . date('His'),
    'buildingId' => $buildingId ?? 0,
    'campusId' => $COLLEGE_ID,
    'noOfLibraryRooms' => 2,
    'totalAreaOfLibraryRoom' => 250,
    'noOfReadingHalls' => 1,
    'areaOfReadingHall' => 150.5,
    'noOfBooks' => 5000,
    'noOfDailyJournals' => 1,
    'noOfWeeklyJournals' => 1,
    'noOfMonthlyJournals' => 1,
    'noOfAnnualJournals' => 1,
    'hasELibraryFacility' => true,
    'remarks' => 'DEV sync audit',
];
$lRes = curl_json('POST', $BASE . '/api/Library', $authHeaders, $libraryPayload);
ugc_log_db('Library', 'POST', $BASE . '/api/Library', $authHeaders, $libraryPayload, $lRes);
$lJson = j($lRes['body']);
$libraryId = pick_id($lJson);
$log[] = ['name'=>'POST /api/Library','url'=>$BASE.'/api/Library','http'=>$lRes['http_code'],'id'=>$libraryId,'notes'=>$note($lRes,'noOfBooks/areaOfReadingHall captured')];

// 5) Management: Department + Section
$deptPayload = ['departmentName'=>'QA Dept '.date('His'),'status'=>true,'remarks'=>'DEV sync audit'];
$dRes = curl_json('POST', $BASE . '/api/Management/AddDepartment', $authHeaders, $deptPayload);
ugc_log_db('Department', 'POST', $BASE . '/api/Management/AddDepartment', $authHeaders, $deptPayload, $dRes);
$dJson = j($dRes['body']);
$deptId = pick_id($dJson);
$log[] = ['name'=>'POST /api/Management/AddDepartment','url'=>$BASE.'/api/Management/AddDepartment','http'=>$dRes['http_code'],'id'=>$deptId,'notes'=>$note($dRes,'departmentName captured')];

$secPayload = ['SectionName'=>'QA Section '.date('His'),'status'=>true,'remarks'=>'DEV sync audit'];
$sRes = curl_json('POST', $BASE . '/api/Managent/AddSection', $authHeaders, $secPayload);
ugc_log_db('Section', 'POST', $BASE . '/api/Managent/AddSection', $authHeaders, $secPayload, $sRes);
$sJson = j($sRes['body']);
$sectionId = pick_id($sJson);
$log[] = ['name'=>'POST /api/Managent/AddSection','url'=>$BASE.'/api/Managent/AddSection','http'=>$sRes['http_code'],'id'=>$sectionId,'notes'=>$note($sRes,'SectionName captured')];

// 6) Student registration (multipart/form-data)
// Use canonical sample values from PDF to avoid validation errors.
$studentFields = [
    'firstName' => 'QA',
    'middleName' => 'DEV',
    'lastName' => 'STUDENT',
    'phoneNumber' => '',
    'gender' => 'female',
    'email' => '',
    'admissionYearId' => '25',
    'hasScholarship' => 'false',
    'isSameAsPermanent' => 'true',
    'rollNo' => '',
    'campusId' => (string)$COLLEGE_ID,
    'nepaliName' => 'क्यूए देव विद्यार्थी',
    // DEV PDF requires BS yyyy/mm/dd and AD yyyy-mm-dd
    'doBBS' => '2061/07/06',
    'doBAD' => '2024-01-01',
    'ethnicity' => 'Brahman',
    'nationality' => 'nepali',
    'disabilityStatus' => 'able',
    'disabilityType' => '',
    'citizenshipNo' => '',
    'citizenIssueDist' => '',
    'nidno' => '',
    'pProvince' => 'Karnali',
    'pDistrict' => 'Kalikot',
    'pLocalLevel' => 'Naraharinath Rural Municipality',
    'pWardNo' => '3',
    'pBlockNo' => '0',
    'pHouseNo' => '',
    'pLocality' => '',
    'tProvince' => 'Karnali',
    'tDistrict' => 'Kalikot',
    'tLocalLevel' => 'Naraharinath Rural Municipality',
    'tWardNo' => '3',
    'tBlockNo' => '0',
    'tHouseNo' => '',
    'tLocality' => '',
    'fatherName' => 'QA FATHER',
    'fOccupation' => '',
    'fatherPhoneNo' => '',
    'fatherEmail' => '',
    'motherName' => '',
    'mOccupation' => '',
    'motherPhoneNo' => '',
    'motherEmail' => '',
    'guardianName' => 'QA GUARDIAN',
    'guardianOccupation' => '',
    'guardianPhone' => '9742226166',
    'gAddress' => 'Naraharinath-3,Kalikot',
    'gEmail' => '',
    // Use program details from GET /GetCollegePrograms when available; else defaults from PDF example.
    'programId' => (string)($programId ?? 37),
    'programName' => (string)($programName ?? 'Bachelor in Education'),
    'levelName' => (string)($levelName ?? 'Bachelor'),
    'facultyName' => (string)($facultyName ?? 'Education'),
    'complitionYear' => '2084',
    'dateOfEnrollment' => '2025-01-08',
    'fiscalYearId' => (string)($fiscalYearId ?? 7),
    'edg' => 'false',
    'semester' => '',
    'year' => 'First',
    'programType' => (string)($programType ?? 'annual'),
    'section' => '',
    'type' => '',
    'isMuktaKamaiya' => 'false',
    'isFromMartyrFamily' => 'false',
    'entranceScore' => '',
    'entranceSymbol' => '',
    'digitalSignature' => '',
    'regNoManual' => '',
    'religion' => 'Hinduism',
    'rollNoManual' => '',
    'universityRegdNo' => '',
    'medium' => '',
    'shift' => '',
];
$stuRes = curl_multipart('POST', $BASE . '/api/Student/Create', $authHeaders, $studentFields);
ugc_log_db('Student', 'POST', $BASE . '/api/Student/Create', $authHeaders, array('multipart_fields'=>array_keys($studentFields)), $stuRes);
$stuJson = j($stuRes['body']);
$studentId = null;
if (is_array($stuJson) && isset($stuJson['data']['id']) && is_numeric($stuJson['data']['id'])) {
    $studentId = (int)$stuJson['data']['id'];
} elseif (is_array($stuJson) && isset($stuJson['id']) && is_numeric($stuJson['id'])) {
    $studentId = (int)$stuJson['id'];
}
$log[] = ['name'=>'POST /api/Student/Create (multipart)','url'=>$BASE.'/api/Student/Create','http'=>$stuRes['http_code'],'id'=>$studentId,'notes'=>$note($stuRes,'BS/AD formats used; campusId=12')];

// 7) Upgrade (move First -> Second)
$upgradePayload = [[
    'Id' => 0,
    'programId' => (int)($programId ?? 37),
    'studentId' => (int)($studentId ?? 0),
    'year' => 'Second',
    'semester' => null,
    'batchId' => (int)($batchId ?? 0),
    'isIrregular' => false,
]];
$uRes = curl_json('POST', $BASE . '/api/StudentUpgrade', $authHeaders, $upgradePayload);
ugc_log_db('StudentUpgrade', 'POST', $BASE . '/api/StudentUpgrade', $authHeaders, $upgradePayload, $uRes);
$uJson = j($uRes['body']);
$upgradeId = pick_id($uJson);
$log[] = ['name'=>'POST /api/StudentUpgrade','url'=>$BASE.'/api/StudentUpgrade','http'=>$uRes['http_code'],'id'=>$upgradeId,'notes'=>$note($uRes,'First→Second year')];

// 8) Dropout
$dropPayload = [
    'studentId' => (int)($studentId ?? 0),
    'reason' => 'DEV audit dropout reason (text)',
    'dateOfEntry' => date('Y-m-d'),
    'remarks' => 'DEV sync audit',
];
$drRes = curl_json('POST', $BASE . '/api/DropOut', $authHeaders, $dropPayload);
ugc_log_db('DropOut', 'POST', $BASE . '/api/DropOut', $authHeaders, $dropPayload, $drRes);
$drJson = j($drRes['body']);
$dropId = pick_id($drJson);
$log[] = ['name'=>'POST /api/DropOut','url'=>$BASE.'/api/DropOut','http'=>$drRes['http_code'],'id'=>$dropId,'notes'=>$note($drRes,'reason captured')];

// 9) Employee registration
// DEV server validates PascalCase field names (per its error payload).
$empPayload = [
    'FirstName' => 'QA',
    'MiddleName' => 'DEV',
    'LastName' => 'EMPLOYEE',
    'PhoneNumber' => '9800000000',
    'Gender' => 'male',
    'Email' => 'qa.dev.employee+'.date('His').'@example.test',
    'Ethnicity' => 'Chhetri',
    'CampusId' => $COLLEGE_ID,
    'EmployeeType' => 'Other',
    'CitizenshipNo' => '650',
    'DateOFBirth' => '2023/04/07',
    'DateOFBirthAd' => '2000-05-06',
    'EmployeePositionId' => 55,
    'PostName' => 'Peon',
    'PositionType' => 'Other',
    'Salutation' => 'Mr.',
    'PProvince' => 'Karnali',
    'PDistrict' => 'Surkhet',
    'PLocalLevel' => 'Simta Rural Municipality',
    'PWardNo' => 6,
    'JoiningType' => 'permanent',
    'JoiningDate' => '2070/08/24',
    'InstitutionId' => $COLLEGE_ID,
    'GraduatedFrom' => 'Bhanu Secondary School',
    'BankAc' => '08020022538',
    'BankName' => 'Laxmi Sunrise Bank',
    'BankBranch' => 'Simta Surkhet',
    'PanNo' => '138880088',
];

// Try JSON first (per PDF). If server returns "required" errors for fields we sent, retry as multipart/form-data.
$eRes = curl_json('POST', $BASE . '/api/Employee', $authHeaders, [$empPayload]);
ugc_log_db('Employee', 'POST', $BASE . '/api/Employee', $authHeaders, array($empPayload), $eRes);
$eJson = j($eRes['body']);
$empId = pick_id($eJson);
$empNotes = 'firstName/postName/joiningDate captured';
if ($eRes['http_code'] === 400 && is_array($eJson) && isset($eJson['errors']) && is_array($eJson['errors']) && isset($eJson['errors']['FirstName'])) {
    $eRes2 = curl_multipart('POST', $BASE . '/api/Employee', $authHeaders, $empPayload);
    ugc_log_db('Employee', 'POST', $BASE . '/api/Employee (multipart retry)', $authHeaders, array('multipart_fields'=>array_keys($empPayload)), $eRes2);
    $eJson2 = j($eRes2['body']);
    $empId2 = pick_id($eJson2);
    $empNotes = 'DEV rejected JSON body; retried as multipart/form-data';
    $eRes = $eRes2;
    $empId = $empId2;
}
$log[] = ['name'=>'POST /api/Employee','url'=>$BASE.'/api/Employee','http'=>$eRes['http_code'],'id'=>$empId,'notes'=>$note($eRes,$empNotes)];

// Report (markdown, but printed to stdout)
echo "## The Master Sync Compliance Report (UGC HEMIS DEV)\n\n";
echo "- **Run timestamp (NPT)**: {$ts0}\n";
echo "- **Base URL**: `{$BASE}`\n";
echo "- **CollegeId**: **{$COLLEGE_ID}**\n";
echo "- **Auth**: Bearer token from `POST /api/User/Login` (token received)\n\n";

echo "### Connectivity Log\n\n";
echo "- **{$ts0}**: `POST /api/User/Login` => HTTP {$loginRes['http_code']} (token issued)\n\n";

echo "### Success Matrix (modules executed)\n\n";
foreach ($log as $row) {
    $idTxt = ($row['id'] === null) ? '-' : (string)$row['id'];
    echo "- **{$row['name']}**\n";
    echo "  - **Status**: HTTP {$row['http']}\n";
    echo "  - **UGC ID**: {$idTxt}\n";
    echo "  - **Notes**: {$row['notes']}\n";
}

echo "\n### Data Integrity Statement\n\n";
echo "- **Address mapping**: pulled the federal structure reference via `GET /api/Address/GetAll` (HTTP {$addrRes['http_code']}); student payload used Province/District/LocalLevel consistent with the PDF example (Karnali/Kalikot/Naraharinath).\n";
echo "- **Date formats**:\n";
echo "  - **BS**: student `doBBS` sent as `yyyy/mm/dd` (e.g. `2061/07/06`).\n";
echo "  - **AD**: student `doBAD` and dropout `dateOfEntry` sent as `yyyy-mm-dd`.\n";
echo "- **Captured fields (per audit scope)**:\n";
echo "  - Buildings: `houseName=\"{$buildingPayload['houseName']}\"`, `noOfClassrooms={$buildingPayload['noOfClassrooms']}`\n";
echo "  - FEV: `itemName=\"{$fevPayload['itemName']}\"`, `noOfQty={$fevPayload['noOfQty']}`\n";
echo "  - Library: `noOfBooks={$libraryPayload['noOfBooks']}`, `areaOfReadingHall={$libraryPayload['areaOfReadingHall']}`\n";
echo "  - Department: `departmentName=\"{$deptPayload['departmentName']}\"`\n";
echo "  - Section: `SectionName=\"{$secPayload['SectionName']}\"`\n";
echo "  - Employee: `firstName=\"{$empPayload['FirstName']}\"`, `postName=\"{$empPayload['PostName']}\"`, `joiningDate=\"{$empPayload['JoiningDate']}\"`\n";
echo "  - DropOut: `reason=\"{$dropPayload['reason']}\"`\n";

echo "\n";

