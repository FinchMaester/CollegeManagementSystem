<?php

defined('BASEPATH') OR exit('No direct script access allowed');



/**

 * HEMIS / UGC central integration (outbound from this campus system).

 *

 * Testing without UGC:

 * - Set HEMIS_MOCK=true or HEMIS_MOCK_MODE=true in `.env` (default below): no HTTP; assigns a fake hemis_student_id after local create.

 * - Or set mock env false, mock_http_enabled = TRUE, and POST to this app's

 *   mockhemis/student_create via local_mock_base_url (see below).

 *

 * .env keys (loaded via application/config/Env_loader.php → app_env):

 * - HEMIS_SYNC_ENABLED, HEMIS_SYNC_EMPLOYEE, HEMIS_PULL_EMPLOYEE_POSITIONS (boolean strings)

 * - HEMIS_REMOTE_BASE_URL, HEMIS_REMOTE_BEARER_TOKEN

 * - HEMIS_COLLEGE_ID, HEMIS_UNIVERSITY_ID (EmployeePositions/Get/{id}; also POST /api/Employee/{universityId} when not legacy)
 * - HEMIS_EMPLOYEE_POST_LEGACY_PATH=true → POST /api/Employee only (older gateways)

 * - HEMIS_MOCK_MODE or HEMIS_MOCK for mock_mode default

 *

 * Note: hemis_integration_settings DB row is merged first; Hemis_integration_model::apply_hemis_env_overrides()

 * applies non-empty HEMIS_* env values on top (see that method for full list).

 */

$__hemis_env_bool = function ($key, $default) {

    if (!function_exists('app_env')) {

        return $default;

    }

    $raw = app_env($key, '');

    if ($raw === '') {

        return $default;

    }

    return filter_var($raw, FILTER_VALIDATE_BOOLEAN);

};



$__hemis_mock_raw = '';

if (function_exists('app_env')) {

    $__hemis_mock_raw = app_env('HEMIS_MOCK_MODE', '');

    if ($__hemis_mock_raw === '') {

        $__hemis_mock_raw = app_env('HEMIS_MOCK', '');

    }

}

if ($__hemis_mock_raw === '') {

    $__hemis_mock = false; // default to LIVE mode, not mock

} else {

    $__hemis_mock = filter_var($__hemis_mock_raw, FILTER_VALIDATE_BOOLEAN);

}

$config['hemis_mock_mode'] = $__hemis_mock;

$__hemis_university_id = 1;
if (function_exists('app_env')) {
    $__hemis_university_id = (int) app_env('HEMIS_UNIVERSITY_ID', 1);
}
if ($__hemis_university_id < 1) {
    $__hemis_university_id = 1;
}

$config['hemis'] = array(

    /** Master switch: outbound HEMIS sync from api controllers / hooks. */

    'sync_enabled'            => $__hemis_env_bool('HEMIS_SYNC_ENABLED', true),

    /** After PATCH api/Student/{id} (local DB update). Requires students.hemis_student_id. */

    'sync_student_update'     => true,

    /** After POST api/StudentUpgrade (local session/year change); forwards JSON to UGC. */

    'sync_student_upgrade'    => true,

    /** After graduation application saved locally (multipart to UGC GenerateGraduationApplication / ForStudent). */

    'sync_graduation'         => true,

    /** After POST/PUT api/DropOut (JSON to UGC; studentId mapped to hemis_student_id when set). */

    'sync_dropout'            => true,

    /** After POST/PUT api/Library (JSON). */

    'sync_library'            => true,

    /** After POST api/employee and PATCH api/Employee/{id}. */

    'sync_employee'           => $__hemis_env_bool('HEMIS_SYNC_EMPLOYEE', true),

    /** After POST / PUT api/Labs. */

    'sync_labs'               => true,

    /** POST/PUT api/Buildings, FurnitureEquipmentVehicle, Hostels, Lands (PDF §5.1). */

    'sync_buildings'          => true,

    'sync_fev'                => true,

    'sync_hostels'            => true,

    'sync_lands'              => true,

    /** POST/PUT Management Department + Section (PDF §5.6; AddSection path uses Managent per PDF). */

    'sync_management'         => true,

    /** GET reference data FROM UGC; used by Hemis_client::hemis_get_* and campus proxy controllers. */

    'pull_batch'              => true,

    'pull_fiscal_year'        => true,

    'pull_address'            => true,

    'pull_employee_positions' => $__hemis_env_bool('HEMIS_PULL_EMPLOYEE_POSITIONS', true),

    /** GET departments + sections FROM UGC (PDF §5.6). */

    'pull_management'         => true,

    /**

     * Pull programs FROM UGC (GET /api/ProgramMgmt/GetCollegePrograms). Optional; see Hemis_client::hemis_get_college_programs.

     * Per HEMIS PDF V1.3 there is no campus POST of programs to this path.

     */

    'pull_programmgmt'        => true,

    /**

     * TRUE: no call to UGC; generates a fake central ID and stores it (requires DB columns).

     * FALSE: use local_mock_base_url if set, else remote_base_url.

     */

    'mock_mode'               => $__hemis_mock,

    /**

     * Allow /mockhemis/student_create on this server (only when TRUE).

     * Use for HTTP-based local testing that mimics UGC response shape.

     */

    'mock_http_enabled'       => false,

    /**

     * Example: http://localhost/A%20Final%20College%20System/mockhemis

     * Hemis_client will POST multipart to {url}/student_create, /graduation_generate,

     * /graduation_for_student, /graduation_put/{id}, /library_post, /library_put/{id}, /employee_post,

     * /employee_patch/{id}, /labs_post, /labs_put/{id}, /buildings_post, /buildings_put/{id}, /fev_post, /fev_put/{id},

     * /hostels_post, /hostels_put/{id}, /lands_post, /lands_put/{id}, /management_add_department, /management_put_department/{id},

     * /management_add_section, /management_put_section/{id};

     * GET mock: /programmgmt_get, /batch_get, /fiscalyear_get, /address_getall, /employeepositions_get, /employeepositions_get/{id},

     * /management_department_get, /management_section_get, /managent_section_get

     */

    'local_mock_base_url'     => function_exists('app_env') ? app_env('HEMIS_LOCAL_MOCK_BASE_URL', '') : '',

    /** Production UGC API base (no trailing slash). Doc: https://hemisapi.ugcnepal.edu.np */

    'remote_base_url'         => function_exists('app_env') ? app_env('HEMIS_REMOTE_BASE_URL', 'https://hemisapi.ugcnepal.edu.np') : 'https://hemisapi.ugcnepal.edu.np',

    /** Bearer token from POST /api/User/Login (optional; leave empty if not used yet). */

    'remote_bearer_token'     => function_exists('app_env') ? app_env('HEMIS_REMOTE_BEARER_TOKEN', '') : '',

    /**

     * Optional: when remote_bearer_token is empty, Hemis_ugc_auth can POST /api/User/Login

     * (requires DB columns from application/sql/hemis_integration_ugc_login.sql).

     */

    'remote_login_email'      => function_exists('app_env') ? app_env('HEMIS_REMOTE_LOGIN_EMAIL', '') : '',

    'remote_login_password'   => function_exists('app_env') ? app_env('HEMIS_REMOTE_LOGIN_PASSWORD', '') : '',

    'remote_college_id'       => function_exists('app_env')
        ? (int) app_env('HEMIS_COLLEGE_ID', 0)
        : 0,

    'remote_university_id'    => $__hemis_university_id,

    'remote_uni_id'           => $__hemis_university_id,

    'remote_is_ugc'           => false,

    'remote_timeout'          => 30,

    /**

     * Map your campus "admission_year" (BS year) to the numeric HEMIS "admissionYearId".

     *

     * Example:

     *  array(

     *    '2079' => 25,

     *    '2080' => 26,

     *  )

     *

     * Leave empty if you prefer operators to type Admission Year ID manually.

     */

    'admission_year_id_map'   => array(
        // Built from live ugc_batches.batch_nepali → id (UGC Batch catalog = admission year IDs at KBMC)
        '2068' => 33,
        '2069' => 34,
        '2070' => 35,
        '2071' => 36,
        '2072' => 37,
        '2073' => 38,
        '2074' => 32,
        '2075' => 31,
        '2076' => 20,
        '2077' => 21,
        '2078' => 22,
        '2079' => 23,
        '2080' => 24,
        '2081' => 25,
        '2082' => 26,
        '2083' => 27,
        '2084' => 28,
        '2085' => 29,
        '2086' => 30,
    ),

    /**
     * UGC Program (HEMIS programId) → campus Faculty (sections.id) + Program (programs.id).
     * Used by student create/edit to auto-select placement after choosing UGC Program.
     * Keys are ugc_programs.program_id from GET /api/ProgramMgmt/GetCollegePrograms.
     */
    'ugc_program_campus_map' => array(
        33 => array('section_id' => 5, 'program_id' => 5),  // Bachelor in Business Studies → FoM / BBS
        34 => array('section_id' => 5, 'program_id' => 6),  // Master in Business Studies → FoM / MBS
        37 => array('section_id' => 11, 'program_id' => 7), // Bachelor in Education → FoE / B.Ed
        38 => array('section_id' => 11, 'program_id' => 8), // Master in Education → FoE / M.Ed
        41 => array('section_id' => 5, 'program_id' => 5),  // BBA → FoM (closest campus program)
        42 => array('section_id' => 11, 'program_id' => 7), // BCA → FoE fallback if no dedicated program
    ),

    /**
     * Student Create/PATCH multipart: UGC nginx often returns HTTP 413 if total body exceeds ~1MB.
     * Before sending, drop largest CURLFile parts until estimated file total is under this cap.
     * Set HEMIS_STUDENT_MULTIPART_MAX_BYTES=0 in .env to disable trimming (may still 413 upstream).
     */
    'student_multipart_max_bytes' => function_exists('app_env')
        ? (int) app_env('HEMIS_STUDENT_MULTIPART_MAX_BYTES', 943718)
        : 943718,

);

