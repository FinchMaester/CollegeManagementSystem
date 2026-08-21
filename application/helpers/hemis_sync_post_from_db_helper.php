<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Best-effort: derive UGC annual `year` label (First…Fourth) from class row when semister_or_year_no is empty.
 *
 * @param array $cl row from class_model->getAll
 * @return string|null
 */
function hemis_student_infer_year_label_from_class_row(array $cl)
{
    $parts = array();
    foreach (array('class', 'degree', 'level_name') as $k) {
        if (!empty($cl[$k])) {
            $parts[] = (string) $cl[$k];
        }
    }
    $blob = strtolower(implode(' ', $parts));
    if ($blob === '') {
        return null;
    }
    if (preg_match('/\b([1-4])(?:st|nd|rd|th)?\s*year\b/', $blob, $m)) {
        $map = array('1' => 'First', '2' => 'Second', '3' => 'Third', '4' => 'Fourth');
        return isset($map[$m[1]]) ? $map[$m[1]] : null;
    }
    if (preg_match('/\byear\s*([1-4])\b/', $blob, $m)) {
        $map = array('1' => 'First', '2' => 'Second', '3' => 'Third', '4' => 'Fourth');
        return isset($map[$m[1]]) ? $map[$m[1]] : null;
    }
    foreach (array('fourth' => 'Fourth', 'third' => 'Third', 'second' => 'Second', 'first' => 'First') as $word => $label) {
        if (strpos($blob, $word) !== false) {
            return $label;
        }
    }
    $first = isset($parts[0]) ? trim($parts[0]) : '';
    if (preg_match('/^[1-4]$/', $first)) {
        $map = array('1' => 'First', '2' => 'Second', '3' => 'Third', '4' => 'Fourth');
        return $map[$first] ?? null;
    }
    return null;
}

/**
 * HEMIS BS dates expect YYYY/MM/DD (slashes), not ISO-style hyphens.
 *
 * @param mixed $raw
 * @return string
 */
function hemis_student_normalize_bs_date_slashes($raw)
{
    $s = trim((string) $raw);
    if ($s === '') {
        return '';
    }

    return str_replace('-', '/', $s);
}

/**
 * Normalize free-text province labels so they align better with ugc_addresses.province (e.g. "Sudurpaschim Province" vs "Sudurpashchim").
 *
 * @param string $s
 * @return string
 */
function hemis_student_normalize_ugc_compare_label($s)
{
    $s = trim(preg_replace('/\s+/', ' ', (string) $s));
    if ($s === '') {
        return '';
    }
    if (preg_match('/^(.+?)\s+province$/iu', $s, $m)) {
        return trim($m[1]);
    }

    return $s;
}

/**
 * When combined_code is unknown but district + local_level match a single ugc_addresses row, adopt canonical
 * province/district/local_level strings (matches HEMIS GET, e.g. "Bagmati Province") and optionally set combined_code.
 *
 * @param object     $ci
 * @param mixed      $province
 * @param mixed      $district
 * @param mixed      $localLevel
 * @param string     $combinedCode in/out; only filled when empty and a unique row is found
 */
function hemis_student_try_align_address_labels_with_ugc_addresses($ci, &$province, &$district, &$localLevel, &$combinedCode)
{
    if (!isset($ci->db) || !$ci->db->table_exists('ugc_addresses')) {
        return;
    }
    $d = trim((string) $district);
    $l = hemis_student_fix_common_local_level_typos($localLevel);
    if ($d === '' || $l === '') {
        return;
    }
    $tryLocal = array_unique(array_filter(array(trim((string) $localLevel), $l), function ($x) {
        return $x !== '';
    }));

    $pick = null;
    foreach ($tryLocal as $locTry) {
        $q = $ci->db->select('province, district, local_level, combined_code')
            ->from('ugc_addresses')
            ->where('district', $d)
            ->where('local_level', $locTry)
            ->limit(2)
            ->get();
        if ($q && $q->num_rows() === 1) {
            $pick = $q->row_array();
            break;
        }
    }
    if ($pick === null && strlen($l) >= 6) {
        $esc = $ci->db->escape_like_str(substr($l, 0, 18));
        $q2 = $ci->db->select('province, district, local_level, combined_code')
            ->from('ugc_addresses')
            ->where('district', $d)
            ->like('local_level', $esc, 'after')
            ->limit(3)
            ->get();
        if ($q2 && $q2->num_rows() === 1) {
            $pick = $q2->row_array();
        }
    }
    if (empty($pick) || !isset($pick['combined_code']) || trim((string) $pick['combined_code']) === '') {
        return;
    }
    if (!empty($pick['province'])) {
        $province = trim((string) $pick['province']);
    }
    if (!empty($pick['district'])) {
        $district = trim((string) $pick['district']);
    }
    if (!empty($pick['local_level'])) {
        $localLevel = trim((string) $pick['local_level']);
    }
    $cc = trim((string) $pick['combined_code']);
    if ($cc !== '' && trim((string) $combinedCode) === '') {
        $combinedCode = $cc;
    }
    hemis_student_normalize_ugc_address_wire_triple($province, $district, $localLevel);
}

/**
 * Resolve a single official combined_code from ugc_addresses when province + district + local_level match exactly one row.
 *
 * @param object $ci CodeIgniter instance
 * @param mixed  $province
 * @param mixed  $district
 * @param mixed  $localLevel
 * @return string non-empty combined code, or '' if none / ambiguous
 */
function hemis_student_lookup_unique_combined_code($ci, $province, $district, $localLevel)
{
    $p = trim((string) ($province ?? ''));
    $d = trim((string) ($district ?? ''));
    $l = trim((string) ($localLevel ?? ''));
    if ($p === '' && $d === '' && $l === '') {
        return '';
    }
    if (!isset($ci->db) || !$ci->db->table_exists('ugc_addresses')) {
        return '';
    }
    $q = $ci->db->select('combined_code')->from('ugc_addresses');
    if ($p !== '') {
        $q->where('province', $p);
    }
    if ($d !== '') {
        $q->where('district', $d);
    }
    if ($l !== '') {
        $q->where('local_level', $l);
    }
    $rows = $q->get()->result_array();
    if (count($rows) !== 1 || empty($rows[0]['combined_code'])) {
        return '';
    }

    return trim((string) $rows[0]['combined_code']);
}

/**
 * When province wording differs between legacy selects and UGC cache, still resolve a unique combined_code.
 *
 * @param object $ci CodeIgniter instance
 * @param mixed  $province
 * @param mixed  $district
 * @param mixed  $localLevel
 * @return string
 */
function hemis_student_lookup_unique_combined_code_ci($ci, $province, $district, $localLevel)
{
    $p = trim((string) ($province ?? ''));
    $d = trim((string) ($district ?? ''));
    $l = trim((string) ($localLevel ?? ''));
    if ($p === '' || $d === '' || $l === '') {
        return '';
    }
    if (!isset($ci->db) || !$ci->db->table_exists('ugc_addresses')) {
        return '';
    }
    $sql = 'SELECT `combined_code` FROM `ugc_addresses`'
        . ' WHERE LOWER(TRIM(`province`)) = LOWER(?)'
        . ' AND LOWER(TRIM(`district`)) = LOWER(?)'
        . ' AND LOWER(TRIM(`local_level`)) = LOWER(?)';
    $rows = $ci->db->query($sql, array($p, $d, $l))->result_array();
    if (count($rows) !== 1 || empty($rows[0]['combined_code'])) {
        return '';
    }

    return trim((string) $rows[0]['combined_code']);
}

/**
 * Match by district + local_level only (handles province label mismatches between states.name and ugc_addresses.province).
 *
 * @param object $ci CodeIgniter instance
 * @param mixed  $district
 * @param mixed  $localLevel
 * @return string
 */
function hemis_student_lookup_combined_code_by_district_and_local($ci, $district, $localLevel)
{
    $d = trim((string) ($district ?? ''));
    $l = trim((string) ($localLevel ?? ''));
    if ($d === '' || $l === '') {
        return '';
    }
    if (!isset($ci->db) || !$ci->db->table_exists('ugc_addresses')) {
        return '';
    }
    $rows = $ci->db->select('combined_code')
        ->from('ugc_addresses')
        ->where('district', $d)
        ->where('local_level', $l)
        ->get()
        ->result_array();
    if (count($rows) !== 1 || empty($rows[0]['combined_code'])) {
        return '';
    }

    return trim((string) $rows[0]['combined_code']);
}

/**
 * Strict triple, then normalized province, case-insensitive triple, then district+local (unique only).
 *
 * @param object $ci CodeIgniter instance
 * @param mixed  $province
 * @param mixed  $district
 * @param mixed  $localLevel
 * @return string
 */
function hemis_student_lookup_unique_combined_code_best_effort($ci, $province, $district, $localLevel)
{
    $try = hemis_student_lookup_unique_combined_code($ci, $province, $district, $localLevel);
    if ($try !== '') {
        return $try;
    }
    $pn = hemis_student_normalize_ugc_compare_label((string) $province);
    if ($pn !== (string) $province) {
        $try = hemis_student_lookup_unique_combined_code($ci, $pn, $district, $localLevel);
        if ($try !== '') {
            return $try;
        }
    }
    $try = hemis_student_lookup_unique_combined_code_ci($ci, $province, $district, $localLevel);
    if ($try !== '') {
        return $try;
    }
    if ($pn !== '') {
        $try = hemis_student_lookup_unique_combined_code_ci($ci, $pn, $district, $localLevel);
        if ($try !== '') {
            return $try;
        }
    }

    return hemis_student_lookup_combined_code_by_district_and_local($ci, $district, $localLevel);
}

/**
 * Normalise local DB / form values to a strict boolean (for JSON-shaped payloads).
 *
 * @param mixed $v
 * @return bool
 */
function hemis_student_db_value_to_bool($v)
{
    if (is_bool($v)) {
        return $v;
    }
    if ($v === null || $v === '') {
        return false;
    }
    if (is_int($v) || is_float($v)) {
        return ((int) $v) !== 0;
    }
    $s = strtolower(trim((string) $v));
    if ($s === '' || $s === '0' || $s === 'false' || $s === 'no' || $s === 'off' || $s === 'n') {
        return false;
    }

    return true;
}

/**
 * Trim NBSP / odd Unicode spaces and treat literal "null" as empty (UGC PATCH crashes on these).
 *
 * @param mixed $v
 * @param bool  $treat_null_literal
 * @return string
 */
function hemis_student_trim_wire_scalar($v, $treat_null_literal = true)
{
    if ($v === null || is_bool($v) || is_array($v) || is_object($v)) {
        return '';
    }
    $s = (string) $v;
    $s = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{FEFF}]/u', '', $s);
    $s = trim($s);
    if ($treat_null_literal) {
        $low = strtolower($s);
        if ($low === 'null' || $low === 'undefined' || $s === '-') {
            return '';
        }
    }

    return $s;
}

/**
 * Required name/phone fields: trim wire noise but keep non-empty value.
 *
 * @param mixed $v
 * @return string
 */
function hemis_student_required_wire_string($v)
{
    return hemis_student_trim_wire_scalar($v, true);
}

/**
 * Optional scalar: empty string / whitespace / literal "null" → null.
 *
 * @param mixed $v
 * @return string|null
 */
function hemis_student_optional_string_null_if_empty($v)
{
    if ($v === null) {
        return null;
    }
    $s = hemis_student_trim_wire_scalar($v, true);

    return $s === '' ? null : $s;
}

/**
 * UGC citizenshipNo: prefer students.citizenship (number), else national_id; default Unknown when blank.
 *
 * @param array $row
 * @return string|null
 */
function hemis_student_resolve_citizenship_no_for_ugc(array $row)
{
    foreach (array('citizenship', 'national_id') as $col) {
        $v = hemis_student_optional_string_null_if_empty(hemis_student_field($row, $col));
        if ($v === null) {
            continue;
        }
        $low = strtolower($v);
        if ($low === 'unknown' || $low === 'n/a' || $low === 'na') {
            continue;
        }

        return $v;
    }

    return 'Unknown';
}

/**
 * UGC English DOB: strict yyyy-mm-dd or null.
 *
 * @param mixed $raw
 * @return string|null
 */
function hemis_student_normalize_do_bad_value($raw)
{
    $s = hemis_student_trim_wire_scalar($raw, true);
    if ($s === '' || $s === '0000-00-00' || $s === '0000-00-00 00:00:00') {
        return null;
    }
    $s = preg_replace('/Z$/i', '', $s);
    if (preg_match('/^(\d{4}-\d{2}-\d{2})[T\s]/', $s, $m)) {
        return $m[1];
    }
    if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})$/', $s, $m)) {
        $s = $m[1] . '-' . $m[2] . '-' . $m[3];
    }
    $ts = strtotime($s);
    if ($ts === false || $ts <= 0) {
        return null;
    }

    return date('Y-m-d', $ts);
}

/**
 * Multipart map → JSON-friendly types (bool, int, null) for parity checks / logging (not sent as JSON to UGC Create today).
 *
 * @param array $m
 * @return array
 */
function hemis_student_multipart_payload_to_json_typed_array(array $m)
{
    $boolKeys = array(
        'hasScholarship', 'isSameAsPermanent', 'isVerified', 'edg', 'isMuktaKamaiya', 'isFromMartyrFamily',
        'isRegNoManual', 'isRollNoManual',
    );
    $out = $m;
    foreach ($boolKeys as $bk) {
        if (!array_key_exists($bk, $out) || $out[$bk] instanceof CURLFile) {
            continue;
        }
        $out[$bk] = hemis_student_db_value_to_bool($out[$bk]);
    }
    $optionalStrings = array(
        'middleName', 'phoneNumber', 'email', 'fOccupation', 'mOccupation', 'fatherEmail', 'motherEmail',
        'citizenshipNo', 'citizenIssueDist', 'nidno', 'disabilityType', 'pBlockNo', 'pHouseNo', 'pLocality',
        'tBlockNo', 'tHouseNo', 'tLocality', 'gAddress', 'gEmail', 'entranceScore', 'entranceSymbol',
        'regNoManual', 'rollNoManual', 'universityRegdNo', 'religion', 'medium', 'shift', 'type', 'section',
    );
    foreach ($optionalStrings as $ok) {
        if (!array_key_exists($ok, $out) || $out[$ok] instanceof CURLFile) {
            continue;
        }
        $out[$ok] = hemis_student_optional_string_null_if_empty($out[$ok]);
    }
    foreach (array('campusId', 'programId', 'batchId', 'admissionYearId', 'complitionYear', 'pWardNo', 'tWardNo') as $ik) {
        if (!array_key_exists($ik, $out) || $out[$ik] instanceof CURLFile) {
            continue;
        }
        if ($out[$ik] === null || $out[$ik] === '') {
            $out[$ik] = null;
            continue;
        }
        $iv = (int) $out[$ik];
        $out[$ik] = $iv > 0 ? $iv : null;
    }
    foreach (array('FiscalYearId', 'fiscalYearId') as $fk) {
        if (!array_key_exists($fk, $out) || $out[$fk] instanceof CURLFile) {
            continue;
        }
        if ($out[$fk] === null || $out[$fk] === '') {
            $out[$fk] = null;
        } else {
            $out[$fk] = (int) $out[$fk];
        }
    }
    if (isset($out['doBAD']) && is_string($out['doBAD'])) {
        $out['doBAD'] = hemis_student_normalize_do_bad_value($out['doBAD']);
    }
    if (isset($out['doBBS']) && is_string($out['doBBS'])) {
        $nb = hemis_student_normalize_bs_date_slashes($out['doBBS']);
        $out['doBBS'] = $nb === '' ? null : $nb;
    }

    return $out;
}

/**
 * Refined JSON-typed Student payload (bool / int / null) aligned with HEMIS GET-style contracts — use for QA, logs, or future JSON APIs.
 *
 * @param array $row students row_array
 * @return array
 */
function hemis_student_refined_ugc_student_json_payload_from_row(array $row)
{
    return hemis_student_multipart_payload_to_json_typed_array(hemis_student_db_row_to_create_post($row));
}

/**
 * Encode PHP booleans in the multipart map to literal "true"/"false" strings for UGC form posts.
 *
 * @param array $post
 */
function hemis_student_apply_boolean_multipart_wire_strings(array &$post)
{
    $boolKeys = array(
        'hasScholarship', 'isSameAsPermanent', 'isVerified', 'edg', 'isMuktaKamaiya', 'isFromMartyrFamily',
        'isRegNoManual', 'isRollNoManual',
    );
    foreach ($boolKeys as $bk) {
        if (!array_key_exists($bk, $post) || $post[$bk] instanceof CURLFile) {
            continue;
        }
        if ($post[$bk] === null) {
            unset($post[$bk]);
            continue;
        }
        if (is_bool($post[$bk])) {
            $post[$bk] = $post[$bk] ? 'true' : 'false';
            continue;
        }
        $post[$bk] = hemis_student_db_value_to_bool($post[$bk]) ? 'true' : 'false';
    }
}

/**
 * Cast numeric UGC multipart fields to integers and normalize BS date / boolean strings for strict upstream APIs.
 *
 * @param array $post multipart map (modified in place)
 */
function hemis_student_finalize_ugc_multipart_scalar_types(array &$post)
{
    $intKeys = array('campusId', 'programId', 'batchId', 'admissionYearId', 'complitionYear');
    foreach ($intKeys as $k) {
        if (!array_key_exists($k, $post)) {
            continue;
        }
        if ($post[$k] === null || $post[$k] === '') {
            unset($post[$k]);
            continue;
        }
        if ($post[$k] instanceof CURLFile) {
            continue;
        }
        $iv = (int) $post[$k];
        if ($iv > 0) {
            $post[$k] = $iv;
        } else {
            unset($post[$k]);
        }
    }
    foreach (array('FiscalYearId', 'fiscalYearId') as $fk) {
        if (!array_key_exists($fk, $post)) {
            continue;
        }
        if ($post[$fk] === null || $post[$fk] === '') {
            unset($post[$fk]);
            continue;
        }
        if ($post[$fk] instanceof CURLFile) {
            continue;
        }
        $fi = (int) $post[$fk];
        if ($fi > 0) {
            $post[$fk] = $fi;
        } else {
            unset($post[$fk]);
        }
    }
    if (isset($post['doBBS']) && is_string($post['doBBS'])) {
        $post['doBBS'] = hemis_student_normalize_bs_date_slashes($post['doBBS']);
        if ($post['doBBS'] === '') {
            unset($post['doBBS']);
        }
    }
    if (array_key_exists('doBAD', $post) && !($post['doBAD'] instanceof CURLFile)) {
        $ad = hemis_student_normalize_do_bad_value($post['doBAD']);
        if ($ad === null) {
            unset($post['doBAD']);
        } else {
            // UGC Student responses use DateTime-style strings (yyyy-mm-ddTHH:mm:ss); match on wire for model binding parity.
            $post['doBAD'] = (strlen($ad) === 10 && strpos($ad, 'T') === false) ? ($ad . 'T00:00:00') : $ad;
        }
    }
    if (isset($post['pWardNo']) && !($post['pWardNo'] instanceof CURLFile)) {
        $pw = (int) $post['pWardNo'];
        if ($pw > 0) {
            $post['pWardNo'] = $pw;
        }
    }
    if (isset($post['tWardNo']) && !($post['tWardNo'] instanceof CURLFile)) {
        $tw = (int) $post['tWardNo'];
        if ($tw > 0) {
            $post['tWardNo'] = $tw;
        }
    }
    $optionalStrings = array(
        'middleName', 'phoneNumber', 'email', 'fOccupation', 'mOccupation', 'fatherEmail', 'motherEmail',
        'citizenshipNo', 'citizenIssueDist', 'nidno', 'disabilityType', 'pBlockNo', 'pHouseNo', 'pLocality',
        'tBlockNo', 'tHouseNo', 'tLocality', 'gAddress', 'gEmail', 'entranceScore', 'entranceSymbol',
        'regNoManual', 'rollNoManual', 'universityRegdNo', 'religion', 'medium', 'shift', 'type', 'section',
    );
    foreach ($optionalStrings as $ok) {
        if (!array_key_exists($ok, $post) || $post[$ok] instanceof CURLFile) {
            continue;
        }
        if ($post[$ok] === null) {
            continue;
        }
        $s = hemis_student_trim_wire_scalar($post[$ok], true);
        $post[$ok] = ($s === '') ? null : $s;
    }
    hemis_student_apply_boolean_multipart_wire_strings($post);
}

/**
 * Keys that must never be sent on UGC Student create/PATCH (response-only or crash the updater).
 *
 * @param array $post
 */
function hemis_student_strip_forbidden_ugc_multipart_fields(array &$post)
{
    $drop = array(
        'id', 'studentGuid', 'universityId', 'userId', 'institutionId', 'levelId', 'facultyId',
        'admissionYear', 'fullName', 'collegeName', 'uniName', 'fiscalYear', 'batchNameNepali',
        'programfee', 'paidAmount', 'scholarshipAmt', 'isIDGenerated', 'studentProfile',
        'transferDoc', 'shiftmedium', 'programShortName', 'majorSubjectName',
        'citizenshipFront', 'citizenshipBack', 'nidPic', 'ppSizePhoto', 'receiptImage',
        'digitalSignature', 'semisterOrYearNo', 'pEcologicalBelt', 'tEcologicalBelt',
        'validDate', 'validDateNep', 'sectionForStudentId', 'transferStatus',
        'isTransferredOut', 'isTransferredIn', 'transferredDate', 'transferredFrom',
    );
    foreach ($drop as $k) {
        unset($post[$k]);
    }
    if (isset($post['doBAD']) && !($post['doBAD'] instanceof CURLFile)) {
        $bad = hemis_student_trim_wire_scalar($post['doBAD'], true);
        if ($bad === '' || strpos($bad, '0001-01-01') === 0) {
            unset($post['doBAD']);
        }
    }
    if (isset($post['validDate']) && !($post['validDate'] instanceof CURLFile)) {
        $vd = hemis_student_trim_wire_scalar($post['validDate'], true);
        if ($vd === '' || strpos($vd, '0001-01-01') === 0) {
            unset($post['validDate']);
        }
    }
}

/**
 * Annual programs need year; semester programs need semester — never send both null.
 *
 * @param array $post
 */
function hemis_student_ensure_year_semester_present(array &$post)
{
    $pt = isset($post['programType']) ? strtolower(trim((string) $post['programType'])) : '';
    $semesterish = (strpos($pt, 'semester') !== false);

    if ($semesterish) {
        $sem = hemis_student_trim_wire_scalar(isset($post['semester']) ? $post['semester'] : '', false);
        if ($sem === '') {
            $sem = 'First';
        }
        $post['semester'] = $sem;
        unset($post['year'], $post['Year'], $post['Semester']);
        return;
    }

    $year = hemis_student_trim_wire_scalar(isset($post['year']) ? $post['year'] : '', false);
    if ($year === '') {
        $year = 'First';
    }
    $post['year'] = $year;
    unset($post['semester'], $post['Semester']);
}

/**
 * When PATCHing an existing UGC student, keep server-owned enrollment fields aligned with
 * the remote record. Local DB values often drift (roll_no, batch, admission year) and
 * changing them on PATCH triggers opaque HTTP 500 responses from UGC.
 *
 * @param array $post   Outbound multipart map (camelCase + Pascal address keys).
 * @param array $remote Decoded GET /api/Student/{id} body.
 */
function hemis_student_align_patch_payload_with_remote(array &$post, array $remote)
{
    if (empty($remote)) {
        return;
    }

    $remotePick = function ($keys) use ($remote) {
        foreach ((array) $keys as $k) {
            if (!array_key_exists($k, $remote)) {
                continue;
            }
            $v = $remote[$k];
            if ($v === null || $v === '') {
                continue;
            }
            return $v;
        }
        return null;
    };

    foreach (array(
        'rollNo', 'admissionYearId', 'batchId', 'fiscalYearId', 'year', 'semester',
        'regNo', 'doBBS', 'citizenshipNo',
    ) as $field) {
        $v = $remotePick($field);
        if ($v !== null) {
            $post[$field] = $v;
        }
    }

    foreach (array('isVerified', 'isRegNoManual', 'isRollNoManual') as $boolKey) {
        if (array_key_exists($boolKey, $remote)) {
            $post[$boolKey] = !empty($remote[$boolKey]);
        }
    }

    if (!array_key_exists('pBlockNo', $post) || $post['pBlockNo'] === null || $post['pBlockNo'] === '') {
        $pb = $remotePick('pBlockNo');
        $post['pBlockNo'] = ($pb !== null) ? $pb : 0;
    }
    if (!array_key_exists('tBlockNo', $post) || $post['tBlockNo'] === null || $post['tBlockNo'] === '') {
        $tb = $remotePick('tBlockNo');
        $post['tBlockNo'] = ($tb !== null) ? $tb : 0;
    }

    $same = false;
    if (array_key_exists('isSameAsPermanent', $post)) {
        $raw = $post['isSameAsPermanent'];
        if (is_bool($raw)) {
            $same = $raw;
        } else {
            $same = (strtolower(trim((string) $raw)) === 'true' || $raw === 1 || $raw === '1');
        }
    }
    if (!$same && !empty($remote['isSameAsPermanent'])) {
        $same = true;
        $post['isSameAsPermanent'] = true;
    }

    $pWard = 0;
    foreach (array('PWardNo', 'pWardNo') as $wk) {
        if (isset($post[$wk]) && (int) $post[$wk] > 0) {
            $pWard = (int) $post[$wk];
            break;
        }
    }
    if ($pWard < 1) {
        $rp = $remotePick(array('pWardNo', 'PWardNo'));
        if ($rp !== null) {
            $pWard = (int) $rp;
        }
    }
    if ($pWard > 0) {
        $post['PWardNo'] = $pWard;
        if ($same) {
            $post['TWardNo'] = $pWard;
        }
    }
    if (!$same) {
        $tw = $remotePick(array('tWardNo', 'TWardNo'));
        if ($tw !== null && (int) $tw > 0) {
            $post['TWardNo'] = (int) $tw;
        }
    }
    unset($post['pWardNo'], $post['tWardNo']);
}

/**
 * Final wire cleanup for UGC Student multipart (create + PATCH).
 *
 * @param array $post
 */
function hemis_student_prepare_ugc_multipart_wire(array &$post)
{
    hemis_student_strip_forbidden_ugc_multipart_fields($post);

    $samePerm = false;
    if (array_key_exists('isSameAsPermanent', $post)) {
        $rawSame = $post['isSameAsPermanent'];
        if (is_bool($rawSame)) {
            $samePerm = $rawSame;
        } else {
            $samePerm = (strtolower(trim((string) $rawSame)) === 'true' || $rawSame === 1 || $rawSame === '1');
        }
    }
    if ($samePerm) {
        $pWard = 0;
        foreach (array('PWardNo', 'pWardNo') as $wk) {
            if (isset($post[$wk]) && (int) $post[$wk] > 0) {
                $pWard = (int) $post[$wk];
                break;
            }
        }
        if ($pWard > 0) {
            $post['PWardNo'] = $pWard;
            $post['TWardNo'] = $pWard;
            unset($post['pWardNo'], $post['tWardNo']);
        }
    }

    $requiredStrings = array(
        'firstName', 'lastName', 'nepaliName', 'fatherName', 'motherName', 'guardianName',
        'phoneNumber', 'guardianPhone', 'GuardianPhone', 'fatherPhoneNo', 'motherPhoneNo',
        'gender', 'ethnicity', 'nationality', 'disabilityStatus', 'religion',
        'programName', 'facultyName', 'levelName', 'programType',
        'pProvince', 'pDistrict', 'pLocalLevel', 'tProvince', 'tDistrict', 'tLocalLevel',
        'rollNo', 'doBBS', 'dateOfEnrollment',
    );
    foreach ($requiredStrings as $rk) {
        if (!array_key_exists($rk, $post) || $post[$rk] instanceof CURLFile) {
            continue;
        }
        if ($post[$rk] === null) {
            unset($post[$rk]);
            continue;
        }
        $s = hemis_student_required_wire_string($post[$rk]);
        if ($rk === 'gender') {
            $s = strtolower($s);
        }
        $post[$rk] = $s;
    }

    foreach ($post as $k => $v) {
        if ($v instanceof CURLFile || is_array($v) || is_object($v) || is_bool($v)) {
            continue;
        }
        if ($v === null) {
            unset($post[$k]);
            continue;
        }
        $s = hemis_student_trim_wire_scalar($v, true);
        if ($s === '') {
            unset($post[$k]);
            continue;
        }
        $post[$k] = $s;
    }

    hemis_student_ensure_year_semester_present($post);
    hemis_student_finalize_ugc_multipart_scalar_types($post);

    if (array_key_exists('doBAD', $post) && is_string($post['doBAD'])) {
        $post['doBAD'] = preg_replace('/Z$/i', '', $post['doBAD']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $post['doBAD'])) {
            $post['doBAD'] .= 'T00:00:00';
        }
    }

    foreach ($post as $k => $v) {
        if ($v === null) {
            unset($post[$k]);
        }
    }
}

/**
 * @param int $n 1–8
 * @return string|null First…Eighth
 */
function hemis_student_semester_digit_to_label($n)
{
    $n = (int) $n;
    $ord = array(
        1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth',
        5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth',
    );
    return isset($ord[$n]) ? $ord[$n] : null;
}

/**
 * Detect UGC semester label (First…Eighth) from free text (e.g. semister_or_year_no or class title).
 *
 * @param string $raw
 * @return string|null
 */
function hemis_student_infer_semester_label_from_text($raw)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return null;
    }
    if (preg_match('/\b([1-8])\s*(st|nd|rd|th)\s*sem\b/i', $raw, $m)) {
        return hemis_student_semester_digit_to_label((int) $m[1]);
    }
    if (preg_match('/\bsem(?:ester)?\b\s*[:\-]?\s*([1-8])\b/i', $raw, $m)) {
        return hemis_student_semester_digit_to_label((int) $m[1]);
    }
    return null;
}

/**
 * @param array $cl row from class_model->getAll
 * @return string|null
 */
function hemis_student_infer_semester_label_from_class_row(array $cl)
{
    $parts = array();
    foreach (array('class', 'degree', 'level_name') as $k) {
        if (!empty($cl[$k])) {
            $parts[] = (string) $cl[$k];
        }
    }
    foreach ($parts as $p) {
        $sem = hemis_student_infer_semester_label_from_text($p);
        if ($sem !== null && $sem !== '') {
            return $sem;
        }
    }
    $blob = implode(' ', $parts);
    list(, $ss) = hemis_student_split_year_semester_for_ugc($blob);
    return ($ss !== null && $ss !== '') ? $ss : null;
}

/**
 * Derive local semister_or_year_no label (First…Fourth / First…Eighth) from campus class + UGC program_type.
 * This is what we store on student_session / students; HEMIS wire year|semester is built from the same rules.
 *
 * @param int $classId
 * @param int $ugcProgramId
 * @return string empty if unresolved
 */
function hemis_student_derive_semister_or_year_no($classId, $ugcProgramId = 0)
{
    $classId = (int) $classId;
    $ugcProgramId = (int) $ugcProgramId;
    if ($classId < 1) {
        return '';
    }

    $ci =& get_instance();
    $ci->load->model('class_model');
    $classRow = $ci->class_model->getAll($classId);
    if (!is_array($classRow) || empty($classRow)) {
        return '';
    }

    $programType = 'annual';
    if ($ugcProgramId > 0 && $ci->db->table_exists('ugc_programs')) {
        $ugcp = $ci->db->select('program_type')
            ->from('ugc_programs')
            ->where('program_id', $ugcProgramId)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($ugcp['program_type'])) {
            $programType = (string) $ugcp['program_type'];
        }
    } else {
        // Fall back to class title hints (Semester in name → semester program)
        $title = strtolower((string) (isset($classRow['class']) ? $classRow['class'] : ''));
        if (strpos($title, 'sem') !== false) {
            $programType = 'semester';
        }
    }

    $wire = array('programType' => $programType);
    hemis_student_apply_year_semester_for_ugc_program_type($wire, '', $classRow);
    if (!empty($wire['semester'])) {
        return (string) $wire['semester'];
    }
    if (!empty($wire['year'])) {
        return (string) $wire['year'];
    }
    return '';
}

/**
 * Set multipart `year` vs `semester` from UGC program_type (annual vs semester) and local hints.
 *
 * @param array      $post     multipart map (modified)
 * @param string     $syRaw    students.semister_or_year_no
 * @param array|null $classRow class_model->getAll row or null
 */
function hemis_student_apply_year_semester_for_ugc_program_type(array &$post, $syRaw, $classRow)
{
    $pt = isset($post['programType']) ? strtolower(trim((string) $post['programType'])) : '';
    $semesterish = (strpos($pt, 'semester') !== false);
    $syRaw = trim((string) $syRaw);

    if ($semesterish) {
        $sem = hemis_student_infer_semester_label_from_text($syRaw);
        if ($sem === null || $sem === '') {
            list($yy, $ss) = hemis_student_split_year_semester_for_ugc($syRaw);
            if ($ss !== null && $ss !== '') {
                $sem = $ss;
            }
        }
        if (($sem === null || $sem === '') && preg_match('/^([1-8])$/', $syRaw, $m)) {
            $sem = hemis_student_semester_digit_to_label((int) $m[1]);
        }
        if (($sem === null || $sem === '') && is_array($classRow)) {
            $sem = hemis_student_infer_semester_label_from_class_row($classRow);
        }
        if ($sem === null || $sem === '') {
            $sem = 'First';
            log_message('info', 'HEMIS sync: defaulted semester to First (UGC program_type is semester-based; set a precise value in Academic year/semester if needed).');
        }
        $post['semester'] = $sem;
        unset($post['year']);
        return;
    }

    list($y,) = hemis_student_split_year_semester_for_ugc($syRaw);
    $year = ($y !== null && $y !== '') ? $y : null;
    if (($year === null || $year === '') && is_array($classRow)) {
        $year = hemis_student_infer_year_label_from_class_row($classRow);
    }
    if ($year === null || $year === '') {
        $year = 'First';
        log_message('info', 'HEMIS sync: defaulted year to First (annual program; set Academic year/semester or class title if this should differ).');
    }
    $post['year'] = $year;
    unset($post['semester']);
}

/**
 * Build HEMIS multipart field map (camelCase) from students table row for outbound sync.
 *
 * @param array $row row_array from students
 * @return array
 */
function hemis_student_db_row_to_create_post(array $row)
{
    $thisCI =& get_instance();
    $thisCI->load->helper('hemis_student'); // fix_common_local_level_typos, normalize_ugc_address_wire_triple

    // Booleans are stored as strict PHP bool in the map until hemis_student_finalize_ugc_multipart_scalar_types()
    // converts them to literal "true"/"false" for multipart wire format.

    $post = array();
    $post['programName'] = '';
    $post['facultyName'] = '';
    $post['levelName']   = '';

    // campusId: UGC expects its own campus/college id (not local school IDs).
    // Prefer effective config remote_college_id (DB overrides + env), fallback to students.school_id if present.
    $thisCI->load->model('hemis_integration_model');
    $hemisCfg = $thisCI->hemis_integration_model->get_effective_config();
    $cfgCampusId = (is_array($hemisCfg) && !empty($hemisCfg['remote_college_id'])) ? (int) $hemisCfg['remote_college_id'] : 0;
    $rowCampusId = (int) (hemis_student_field($row, 'school_id') ?: 0);
    $campusId = $cfgCampusId > 0 ? $cfgCampusId : ($rowCampusId > 0 ? $rowCampusId : 0);

    $np = hemis_student_required_wire_string(
        (hemis_student_field($row, 'first_name_np') ?: '') . ' '
        . (hemis_student_field($row, 'middle_name_np') ? hemis_student_field($row, 'middle_name_np') . ' ' : '')
        . (hemis_student_field($row, 'last_name_np') ?: '')
    );

    // Fallbacks for required live HEMIS validations
    if ($np === '') {
        // Some datasets don't have Nepali name filled yet; use English full name as fallback.
        $np = hemis_student_required_wire_string(
            (hemis_student_field($row, 'firstname') ?: '') . ' '
            . (hemis_student_field($row, 'middlename') ? hemis_student_field($row, 'middlename') . ' ' : '')
            . (hemis_student_field($row, 'lastname') ?: '')
        );
    }

    // Guardian phone is required by live API in many colleges; fallback to student mobile / parents if empty.
    $guardian_phone = hemis_student_trim_wire_scalar(hemis_student_field($row, 'guardian_phone'), true);
    if ($guardian_phone === '') {
        $guardian_phone = hemis_student_trim_wire_scalar(hemis_student_field($row, 'mobileno'), true);
    }
    if ($guardian_phone === '') {
        $guardian_phone = hemis_student_trim_wire_scalar(hemis_student_field($row, 'father_phone'), true);
    }
    if ($guardian_phone === '') {
        $guardian_phone = hemis_student_trim_wire_scalar(hemis_student_field($row, 'mother_phone'), true);
    }

    // Prefer official UGC address names (stored from Address/GetAll cascade) when present.
    $pProvince   = hemis_student_field($row, 'ugc_p_province');
    $pDistrict   = hemis_student_field($row, 'ugc_p_district');
    $pLocalLevel = hemis_student_field($row, 'ugc_p_local_level');
    if ($pProvince === null || $pProvince === '') {
        $pProvince = hemis_student_field($row, 'permanent_province');
    }
    if ($pDistrict === null || $pDistrict === '') {
        $pDistrict = hemis_student_field($row, 'permanent_district');
    }
    if ($pLocalLevel === null || $pLocalLevel === '') {
        $pLocalLevel = hemis_student_field($row, 'permanent_local_level');
    }
    $pWardNo     = hemis_student_field($row, 'permanent_ward_no');
    // Ward is required by HEMIS. If permanent ward is empty, fallback to temporary ward if present.
    if ($pWardNo === null || $pWardNo === '') {
        $tw = hemis_student_field($row, 'temporary_ward_no');
        if ($tw !== null && $tw !== '') {
            $pWardNo = $tw;
        }
    }
    if (($pProvince === null || $pProvince === '') && ($pDistrict === null || $pDistrict === '') && ($pLocalLevel === null || $pLocalLevel === '') && ($pWardNo === null || $pWardNo === '')) {
        $tpProvince   = hemis_student_field($row, 'ugc_t_province');
        $tpDistrict   = hemis_student_field($row, 'ugc_t_district');
        $tpLocalLevel = hemis_student_field($row, 'ugc_t_local_level');
        if ($tpProvince === null || $tpProvince === '') {
            $tpProvince = hemis_student_field($row, 'temporary_province');
        }
        if ($tpDistrict === null || $tpDistrict === '') {
            $tpDistrict = hemis_student_field($row, 'temporary_district');
        }
        if ($tpLocalLevel === null || $tpLocalLevel === '') {
            $tpLocalLevel = hemis_student_field($row, 'temporary_local_level');
        }
        $tpWardNo     = hemis_student_field($row, 'temporary_ward_no');
        if ($tpProvince !== null && $tpProvince !== '') {
            $pProvince = $tpProvince;
        }
        if ($tpDistrict !== null && $tpDistrict !== '') {
            $pDistrict = $tpDistrict;
        }
        if ($tpLocalLevel !== null && $tpLocalLevel !== '') {
            $pLocalLevel = $tpLocalLevel;
        }
        if ($tpWardNo !== null && $tpWardNo !== '') {
            $pWardNo = $tpWardNo;
        }
    }

    $pCombined = trim((string) (hemis_student_field($row, 'ugc_p_combined_code') ?: ''));
    // Many local forms save only combined_code while ugc_p_* text columns stay empty — resolve official names for UGC.
    if ($pCombined !== '' && $thisCI->db->table_exists('ugc_addresses')) {
        $addrRow = $thisCI->db->select('province, district, local_level')
            ->from('ugc_addresses')
            ->where('combined_code', $pCombined)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($addrRow)) {
            if ($pProvince === null || $pProvince === '') {
                $pProvince = $addrRow['province'];
            }
            if ($pDistrict === null || $pDistrict === '') {
                $pDistrict = $addrRow['district'];
            }
            if ($pLocalLevel === null || $pLocalLevel === '') {
                $pLocalLevel = $addrRow['local_level'];
            }
        }
    }

    $geo_name = function ($table, $val) use ($thisCI) {
        if ($val === null || $val === '') {
            return $val;
        }
        $s = trim((string) $val);
        if ($s === '' || !is_numeric($s)) {
            return $s;
        }
        $id = (int) $s;
        if ($id < 1 || !$thisCI->db->table_exists($table)) {
            return $s;
        }
        $q = $thisCI->db->select('name')->from($table)->where('id', $id)->limit(1)->get()->row_array();

        return !empty($q['name']) ? trim((string) $q['name']) : $s;
    };
    $pProvince = $geo_name('states', $pProvince);
    $pDistrict = $geo_name('districts', $pDistrict);
    $pLocalLevel = $geo_name('municipalities', $pLocalLevel);

    hemis_student_try_align_address_labels_with_ugc_addresses($thisCI, $pProvince, $pDistrict, $pLocalLevel, $pCombined);

    if ($pCombined === '' && $thisCI->db->table_exists('ugc_addresses')) {
        $lookP = hemis_student_lookup_unique_combined_code_best_effort($thisCI, $pProvince, $pDistrict, $pLocalLevel);
        if ($lookP !== '') {
            $pCombined = $lookP;
        }
        if ($pCombined === '') {
            $pp = trim((string) (hemis_student_field($row, 'permanent_province') ?: ''));
            $pd = trim((string) (hemis_student_field($row, 'permanent_district') ?: ''));
            $pl = trim((string) (hemis_student_field($row, 'permanent_local_level') ?: ''));
            if ($pp !== '' && $pd !== '' && $pl !== '') {
                $lookP2 = hemis_student_lookup_unique_combined_code_best_effort($thisCI, $pp, $pd, $pl);
                if ($lookP2 !== '') {
                    $pCombined = $lookP2;
                }
            }
        }
    }

    // Authoritative labels from ugc_addresses whenever combined_code is known (matches HEMIS GET / cache strings).
    if ($pCombined !== '' && $thisCI->db->table_exists('ugc_addresses')) {
        $authP = $thisCI->db->select('province, district, local_level')
            ->from('ugc_addresses')
            ->where('combined_code', $pCombined)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($authP['province'])) {
            $pProvince = trim((string) $authP['province']);
        }
        if (!empty($authP['district'])) {
            $pDistrict = trim((string) $authP['district']);
        }
        if (!empty($authP['local_level'])) {
            $pLocalLevel = trim((string) $authP['local_level']);
        }
    }
    hemis_student_normalize_ugc_address_wire_triple($pProvince, $pDistrict, $pLocalLevel);

    $rawSame = hemis_student_field($row, 'is_same_as_permanent');
    $samePerm = ($rawSame === true || $rawSame === 1 || $rawSame === '1'
        || strtolower(trim((string) $rawSame)) === 'yes'
        || strtolower(trim((string) $rawSame)) === 'true');

    $tProvSel = hemis_student_field($row, 'ugc_t_province');
    if ($tProvSel === null || $tProvSel === '') {
        $tProvSel = hemis_student_field($row, 'temporary_province');
    }
    $tDistSel = hemis_student_field($row, 'ugc_t_district');
    if ($tDistSel === null || $tDistSel === '') {
        $tDistSel = hemis_student_field($row, 'temporary_district');
    }
    $tLocSel = hemis_student_field($row, 'ugc_t_local_level');
    if ($tLocSel === null || $tLocSel === '') {
        $tLocSel = hemis_student_field($row, 'temporary_local_level');
    }
    $tProvSel = $geo_name('states', $tProvSel);
    $tDistSel = $geo_name('districts', $tDistSel);
    $tLocSel = $geo_name('municipalities', $tLocSel);

    $tCombined = trim((string) (hemis_student_field($row, 'ugc_t_combined_code') ?: ''));
    if (!($samePerm && $pCombined !== '')) {
        hemis_student_try_align_address_labels_with_ugc_addresses($thisCI, $tProvSel, $tDistSel, $tLocSel, $tCombined);
    }
    if ($samePerm && $pCombined !== '') {
        $tCombined = $pCombined;
    } elseif ($tCombined === '' && !$samePerm && $thisCI->db->table_exists('ugc_addresses')) {
        $lookT = hemis_student_lookup_unique_combined_code_best_effort($thisCI, $tProvSel, $tDistSel, $tLocSel);
        if ($lookT !== '') {
            $tCombined = $lookT;
        }
        if ($tCombined === '') {
            $tp = trim((string) (hemis_student_field($row, 'temporary_province') ?: ''));
            $td = trim((string) (hemis_student_field($row, 'temporary_district') ?: ''));
            $tl = trim((string) (hemis_student_field($row, 'temporary_local_level') ?: ''));
            if ($tp !== '' && $td !== '' && $tl !== '') {
                $lookT2 = hemis_student_lookup_unique_combined_code_best_effort($thisCI, $tp, $td, $tl);
                if ($lookT2 !== '') {
                    $tCombined = $lookT2;
                }
            }
        }
    }

    if ($samePerm) {
        $tProvinceOut = $pProvince;
        $tDistrictOut = $pDistrict;
        $tLocalLevelOut = $pLocalLevel;
    } else {
        $tProvinceOut = $tProvSel;
        $tDistrictOut = $tDistSel;
        $tLocalLevelOut = $tLocSel;
    }
    if ($tCombined !== '' && $thisCI->db->table_exists('ugc_addresses')) {
        $authT = $thisCI->db->select('province, district, local_level')
            ->from('ugc_addresses')
            ->where('combined_code', $tCombined)
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($authT['province'])) {
            $tProvinceOut = trim((string) $authT['province']);
        }
        if (!empty($authT['district'])) {
            $tDistrictOut = trim((string) $authT['district']);
        }
        if (!empty($authT['local_level'])) {
            $tLocalLevelOut = trim((string) $authT['local_level']);
        }
    }
    hemis_student_normalize_ugc_address_wire_triple($tProvinceOut, $tDistrictOut, $tLocalLevelOut);

    // UGC Student/Create requires a fiscal year id; prefer current student_session, then student columns, then active FY.
    $resolvedFy = null;
    $studentPkForFy = (int) (hemis_student_field($row, 'id') ?: 0);
    if ($studentPkForFy > 0 && $thisCI->db->table_exists('student_session')
        && $thisCI->db->field_exists('ugc_fiscal_year_id', 'student_session')) {
        $activeSessionId = 0;
        if ($thisCI->db->table_exists('sch_settings')) {
            $cfg = $thisCI->db->select('session_id')->from('sch_settings')->limit(1)->get()->row_array();
            $activeSessionId = isset($cfg['session_id']) ? (int) $cfg['session_id'] : 0;
        }
        $thisCI->db->select('ugc_fiscal_year_id, semister_or_year_no, class_id');
        $thisCI->db->from('student_session');
        $thisCI->db->where('student_id', $studentPkForFy);
        if ($activeSessionId > 0) {
            $thisCI->db->order_by("(session_id = " . $activeSessionId . ")", 'DESC', false);
        }
        $thisCI->db->order_by("(is_active = 'yes')", 'DESC', false);
        $thisCI->db->order_by('id', 'DESC');
        $thisCI->db->limit(1);
        $ssRow = $thisCI->db->get()->row_array();
        if (!empty($ssRow['ugc_fiscal_year_id'])) {
            $resolvedFy = (int) $ssRow['ugc_fiscal_year_id'];
        }
        if ((!hemis_student_field($row, 'semister_or_year_no') || trim((string) hemis_student_field($row, 'semister_or_year_no')) === '')
            && !empty($ssRow['semister_or_year_no'])) {
            $row['semister_or_year_no'] = $ssRow['semister_or_year_no'];
        }
    }
    if ($resolvedFy === null || $resolvedFy === '' || (int) $resolvedFy < 1) {
        $resolvedFy = hemis_student_field($row, 'ugc_fiscal_year_id');
    }
    if ($resolvedFy === null || $resolvedFy === '' || (int) $resolvedFy < 1) {
        $resolvedFy = hemis_student_field($row, 'fiscal_year_id');
    }
    if (($resolvedFy === null || $resolvedFy === '' || (int) $resolvedFy < 1) && $thisCI->db->table_exists('ugc_fiscal_years')) {
        $fyRow = $thisCI->db->select('id')
            ->from('ugc_fiscal_years')
            ->where('active_fiscal_year', 1)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row_array();
        if (!empty($fyRow['id'])) {
            $resolvedFy = (int) $fyRow['id'];
        } else {
            $fyRow2 = $thisCI->db->select('id')
                ->from('ugc_fiscal_years')
                ->order_by('idx', 'DESC')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($fyRow2['id'])) {
                $resolvedFy = (int) $fyRow2['id'];
            }
        }
    }
    $fiscalYearStr = ($resolvedFy !== null && $resolvedFy !== '' && (int) $resolvedFy > 0) ? (string) (int) $resolvedFy : '';

    $syRaw = hemis_student_field($row, 'semister_or_year_no');

    // Normalize / default some required fields that are often left blank in local UI.
    $nationality = hemis_resolve_nationality_value($row);
    $nationality = strtolower(trim((string) $nationality));
    if ($nationality === '') {
        // UGC GET payloads for this campus use "unknown" when unset; match on create.
        $nationality = 'unknown';
    }
    // Common local variants
    if ($nationality === 'nepalese' || $nationality === 'nepal') {
        $nationality = 'nepali';
    }

    $disabilityStatus = trim((string) hemis_student_field($row, 'disability_status'));
    $dsLower = strtolower($disabilityStatus);
    if ($dsLower === '' || $dsLower === 'unknown' || $dsLower === 'no' || $dsLower === 'none') {
        // HEMIS docs use "able" for non-disabled.
        $disabilityStatus = 'able';
    }

    $resolvedBatchId = (int) (hemis_student_field($row, 'ugc_batch_id') ?: hemis_student_field($row, 'batch_id'));
    if ($resolvedBatchId < 1) {
        $resolvedBatchId = (int) hemis_resolve_admission_year_id($row);
    }

    $post = array_merge($post, array(
        'firstName'            => hemis_student_required_wire_string(hemis_student_field($row, 'firstname')),
        'middleName'           => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'middlename')),
        'lastName'             => hemis_student_required_wire_string(hemis_student_field($row, 'lastname')),
        'phoneNumber'          => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'mobileno')),
        'gender'               => hemis_student_field($row, 'gender'),
        'email'                => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'email')),
        'admissionYearId'      => hemis_resolve_admission_year_id($row),
        'hasScholarship'       => hemis_student_db_value_to_bool(hemis_student_field($row, 'has_scolorship')),
        'isSameAsPermanent'    => hemis_student_db_value_to_bool(hemis_student_field($row, 'is_same_as_permanent')),
        'isVerified'           => hemis_student_db_value_to_bool(hemis_student_field($row, 'is_verified')),
        'rollNo'               => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'roll_no')),
        'campusId'             => $campusId > 0 ? $campusId : null,
        'nepaliName'           => $np,
        // Dual-date contract: prefer explicit BS column; HEMIS expects slashes (YYYY/MM/DD).
        'doBBS'                => hemis_student_normalize_bs_date_slashes(
            (string) (hemis_student_field($row, 'dob_bs') ?: hemis_student_field($row, 'dob') ?: '')
        ),
        'doBAD'                => hemis_student_normalize_do_bad_value(hemis_student_field($row, 'dob_ad')),
        'ethnicity'            => hemis_resolve_ethnicity_value($row),
        'nationality'          => $nationality,
        'disabilityStatus'     => $disabilityStatus,
        'disabilityType'       => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'disability_type')),
        'citizenshipNo'        => hemis_student_resolve_citizenship_no_for_ugc($row),
        'citizenIssueDist'     => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'citizenship_issue_dist')),
        'nidno'                => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'national_id')),
        'pProvince'            => $pProvince,
        'pDistrict'            => $pDistrict,
        'pLocalLevel'          => $pLocalLevel,
        'pWardNo'              => $pWardNo,
        'pBlockNo'             => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'p_block_no')),
        'pHouseNo'             => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'permanent_house_no')),
        'pLocality'            => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'p_locality')),
        'tProvince'            => $tProvinceOut,
        'tDistrict'            => $tDistrictOut,
        'tLocalLevel'          => $tLocalLevelOut,
        'tWardNo'              => $samePerm ? $pWardNo : hemis_student_field($row, 'temporary_ward_no'),
        'tBlockNo'             => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 't_block_no')),
        'tHouseNo'             => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'temporary_house_no')),
        'tLocality'            => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 't_locality')),
        'fatherName'           => hemis_student_required_wire_string(hemis_student_field($row, 'father_name')),
        'fOccupation'          => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'father_occupation')),
        'fatherPhoneNo'        => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'father_phone')),
        'fatherEmail'          => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'father_email')),
        'motherName'           => hemis_student_required_wire_string(hemis_student_field($row, 'mother_name')),
        'mOccupation'          => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'mother_occupation')),
        'motherPhoneNo'        => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'mother_phone')),
        'motherEmail'          => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'mother_email')),
        'guardianName'         => hemis_student_required_wire_string(hemis_student_field($row, 'guardian_name')),
        'guardianOccupation'   => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'guardian_occupation')),
        'guardianPhone'        => $guardian_phone !== '' ? $guardian_phone : null,
        'gAddress'             => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'guardian_address')),
        'gEmail'               => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'guardian_email')),
        // Prefer official UGC Program ID if present (from ProgramMgmt metadata).
        'programId'            => (hemis_student_field($row, 'ugc_program_id') ?: hemis_student_field($row, 'program_id')),
        // Prefer official UGC Batch ID if present (from Batch metadata).
        'batchId'              => $resolvedBatchId > 0 ? $resolvedBatchId : null,
        'complitionYear'       => hemis_student_field($row, 'complition_year'),
        'dateOfEnrollment'     => hemis_student_field($row, 'admission_date'),
        'edg'                  => hemis_student_db_value_to_bool(
            hemis_student_field($row, 'edg') !== null
                ? hemis_student_field($row, 'edg')
                : hemis_student_field($row, 'edj')
        ),
        'programType'          => 'annual',
        'section'              => null,
        'type'                 => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'type')),
        'isMuktaKamaiya'       => hemis_student_db_value_to_bool(hemis_student_field($row, 'is_mukta_kamaiya')),
        'isFromMartyrFamily'   => hemis_student_db_value_to_bool(hemis_student_field($row, 'is_from_martyr_family')),
        'isRegNoManual'        => hemis_student_db_value_to_bool(hemis_student_field($row, 'is_reg_no_manual')),
        'isRollNoManual'       => hemis_student_db_value_to_bool(hemis_student_field($row, 'is_roll_no_manual')),
        'entranceScore'        => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'entrance_score')),
        'entranceSymbol'       => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'entrance_symbol')),
        'regNoManual'          => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'reg_no_manual')),
        'religion'             => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'religion')),
        'rollNoManual'         => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'roll_no_manual')),
        'universityRegdNo'     => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'university_regd_no')),
        'medium'               => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'medium')),
        'shift'                => hemis_student_optional_string_null_if_empty(hemis_student_field($row, 'shift')),
    ));

    if ($pCombined !== '') {
        $post['pCombinedCode'] = $pCombined;
    }
    if ($tCombined !== '') {
        $post['tCombinedCode'] = $tCombined;
    }

    if ($fiscalYearStr !== '') {
        // HEMIS validation reports `FiscalYearId` (PascalCase). Avoid sending both
        // casings because some upstream binders choke on duplicate semantic keys.
        $post['FiscalYearId'] = $fiscalYearStr;
    }

    // Program metadata: prefer UGC cache if ugc_program_id is selected, else fallback to local program_model.
    $ugcProgId = (int) (hemis_student_field($row, 'ugc_program_id') ?: 0);
    if ($ugcProgId > 0 && $thisCI->db->table_exists('ugc_programs')) {
        $ugcp = $thisCI->db->select('program_id, program_name, level_name, faculty_name, program_type')
            ->from('ugc_programs')->where('program_id', $ugcProgId)->get()->row_array();
        if (!empty($ugcp)) {
            if (!empty($ugcp['program_name'])) {
                $post['programName'] = $ugcp['program_name'];
            }
            if (!empty($ugcp['faculty_name'])) {
                $post['facultyName'] = $ugcp['faculty_name'];
            }
            if (!empty($ugcp['level_name'])) {
                $post['levelName'] = $ugcp['level_name'];
            }
            if (!empty($ugcp['program_type'])) {
                $post['programType'] = $ugcp['program_type'];
            }
        }
    } else {
        $progId = hemis_student_field($row, 'program_id');
        if ($progId) {
            $thisCI->load->model('program_model');
            $pr = $thisCI->program_model->get_program_by_id((int) $progId);
            if (is_object($pr)) {
                $post['programName'] = isset($pr->title) ? $pr->title : (isset($pr->name) ? $pr->name : '');
                if (!empty($pr->faculty_id) && $thisCI->db->table_exists('faculty')) {
                    $thisCI->db->where('id', (int) $pr->faculty_id);
                    $fac = $thisCI->db->get('faculty', 1)->row_array();
                    if (!empty($fac['name'])) {
                        $post['facultyName'] = $fac['name'];
                    }
                }
            }
        }
    }

    $cid = hemis_student_field($row, 'class_id');
    // Prefer current campus placement from student_session (students.class_id can be stale).
    $studentPk = (int) (hemis_student_field($row, 'id') ?: 0);
    if ($studentPk > 0 && isset($thisCI->db) && $thisCI->db->table_exists('student_session')) {
        $activeSessionId = 0;
        if ($thisCI->db->table_exists('sch_settings')) {
            $cfg = $thisCI->db->select('session_id')->from('sch_settings')->limit(1)->get()->row_array();
            $activeSessionId = isset($cfg['session_id']) ? (int) $cfg['session_id'] : 0;
        }
        $thisCI->db->select('class_id, section_id, program_id');
        $thisCI->db->from('student_session');
        $thisCI->db->where('student_id', $studentPk);
        if ($activeSessionId > 0) {
            $thisCI->db->order_by("(session_id = " . $activeSessionId . ")", 'DESC', false);
        }
        $thisCI->db->order_by("(is_active = 'yes')", 'DESC', false);
        $thisCI->db->order_by('id', 'DESC');
        $thisCI->db->limit(1);
        $ssPlacement = $thisCI->db->get()->row_array();
        if (!empty($ssPlacement['class_id'])) {
            $cid = $ssPlacement['class_id'];
        }
    }
    $classRow = null;
    if ($cid) {
        $thisCI->load->model('class_model');
        $classRow = $thisCI->class_model->getAll((int) $cid);
        if (is_array($classRow)) {
            $haveLevel = isset($post['levelName']) && trim((string) $post['levelName']) !== '';
            if (!$haveLevel) {
                if (!empty($classRow['degree'])) {
                    $post['levelName'] = $classRow['degree'];
                } elseif (!empty($classRow['level_name'])) {
                    $post['levelName'] = $classRow['level_name'];
                } elseif (!empty($classRow['class'])) {
                    $post['levelName'] = $classRow['class'];
                }
            }
        }
    }

    // `year` vs `semester` from UGC program_type + semister_or_year_no + class title; safe defaults (First) if unknown.
    hemis_student_apply_year_semester_for_ugc_program_type($post, $syRaw, $classRow);

    if (!isset($post['admissionYearId']) || (int) $post['admissionYearId'] < 1) {
        $bsy = trim((string) (hemis_student_field($row, 'admission_year') ?? ''));
        log_message(
            'info',
            'HEMIS Student sync payload: admissionYearId is missing or invalid after resolve.'
            . ($bsy !== ''
                ? ' Configure hemis admission_year_id_map for BS year "' . $bsy
                . '" (hemis_integration_settings / effective config) or set students.admissionYearId from the UGC admission-year list.'
                : ' Set students.admissionYearId from the UGC admission-year list.')
        );
    }

    hemis_student_prepare_ugc_multipart_wire($post);

    return $post;
}

if (!function_exists('hemis_student_split_year_semester_for_ugc')) {
    /**
     * Map students.semister_or_year_no to UGC-style labels (year: First…Fourth, semester: First…Eighth).
     *
     * @param mixed $raw
     * @return array{0:?string,1:?string}
     */
    function hemis_student_split_year_semester_for_ugc($raw)
    {
        $s = trim((string) $raw);
        if ($s === '') {
            return array(null, null);
        }
        $low = strtolower($s);
        if (preg_match('/\b([1-8])\s*(st|nd|rd|th)\s*sem\b/i', $s, $m)) {
            $ord = array(
                1 => 'First', 2 => 'Second', 3 => 'Third', 4 => 'Fourth',
                5 => 'Fifth', 6 => 'Sixth', 7 => 'Seventh', 8 => 'Eighth',
            );
            $n = (int) $m[1];
            return array(null, isset($ord[$n]) ? $ord[$n] : null);
        }
        if (preg_match('/^[1-4]$/', $s)) {
            $map = array('1' => 'First', '2' => 'Second', '3' => 'Third', '4' => 'Fourth');
            return array($map[$s], null);
        }
        if (preg_match('/\b(first|1st)\b/', $low)) {
            return array('First', null);
        }
        if (preg_match('/\b(second|2nd)\b/', $low)) {
            return array('Second', null);
        }
        if (preg_match('/\b(third|3rd)\b/', $low)) {
            return array('Third', null);
        }
        if (preg_match('/\b(fourth|4th)\b/', $low)) {
            return array('Fourth', null);
        }
        return array(null, null);
    }
}

/**
 * Build one UGC StudentUpgrade JSON row after a local promote (class change).
 * Reuses create-path UGC program_type → year vs semester mapping.
 *
 * @param int $localStudentId   students.id
 * @param int $promotedClassId  destination classes.id
 * @return array {
 *   ok: bool,
 *   local_student_id: int,
 *   skip_reason?: string,
 *   row?: array,
 *   semister_or_year_no?: string
 * }
 */
function hemis_student_build_upgrade_row_after_promote($localStudentId, $promotedClassId)
{
    $localStudentId = (int) $localStudentId;
    $promotedClassId = (int) $promotedClassId;
    $out = array(
        'ok' => false,
        'local_student_id' => $localStudentId,
    );

    if ($localStudentId < 1 || $promotedClassId < 1) {
        $out['skip_reason'] = 'Invalid student or promoted class id.';
        return $out;
    }

    $ci =& get_instance();
    $ci->load->helper('hemis_student');
    $ci->load->model('student_model');
    $ci->load->model('class_model');

    $row = $ci->student_model->get_student_by_id($localStudentId);
    if (!is_array($row) || empty($row)) {
        $out['skip_reason'] = 'Student not found.';
        return $out;
    }

    $hemisId = (int) (hemis_student_field($row, 'hemis_student_id') ?: 0);
    if ($hemisId < 1) {
        $out['skip_reason'] = 'No hemis_student_id (not synced to UGC yet).';
        return $out;
    }

    $ugcProgId = (int) (hemis_student_field($row, 'ugc_program_id') ?: 0);
    if ($ugcProgId < 1) {
        $out['skip_reason'] = 'No ugc_program_id on student (required for StudentUpgrade).';
        return $out;
    }

    $programType = 'annual';
    if ($ci->db->table_exists('ugc_programs')) {
        $ugcp = $ci->db->select('program_type')
            ->from('ugc_programs')
            ->where('program_id', $ugcProgId)
            ->get()
            ->row_array();
        if (!empty($ugcp['program_type'])) {
            $programType = (string) $ugcp['program_type'];
        }
    }

    $classRow = $ci->class_model->getAll($promotedClassId);
    if (!is_array($classRow)) {
        $classRow = null;
    }

    // Prefer level implied by destination class; fall back to stored academic year/semester.
    $syRaw = '';
    if (is_array($classRow)) {
        $parts = array();
        foreach (array('class', 'degree', 'level_name') as $k) {
            if (!empty($classRow[$k])) {
                $parts[] = (string) $classRow[$k];
            }
        }
        $syRaw = trim(implode(' ', $parts));
    }
    if ($syRaw === '') {
        $syRaw = trim((string) (hemis_student_field($row, 'semister_or_year_no') ?: ''));
    }

    $wire = array('programType' => $programType);
    hemis_student_apply_year_semester_for_ugc_program_type($wire, $syRaw, $classRow);

    $batchId = (int) (hemis_student_field($row, 'ugc_batch_id') ?: hemis_student_field($row, 'batch_id'));
    if ($batchId < 1) {
        $resolvedAid = hemis_resolve_admission_year_id($row);
        $batchId = ($resolvedAid !== null) ? (int) $resolvedAid : 0;
    }
    if ($batchId < 1) {
        $out['skip_reason'] = 'No UGC batchId / admissionYearId on student.';
        return $out;
    }

    $upgrade = array(
        'Id'           => 0,
        'studentId'    => $localStudentId, // Hemis_client maps to hemis_student_id
        'programId'    => $ugcProgId,
        'batchId'      => $batchId,
        'isIrregular'  => false,
    );

    $label = '';
    if (isset($wire['semester']) && $wire['semester'] !== null && $wire['semester'] !== '') {
        $upgrade['semester'] = (string) $wire['semester'];
        $upgrade['year'] = null;
        $label = (string) $wire['semester'];
    } else {
        $upgrade['year'] = isset($wire['year']) ? (string) $wire['year'] : 'First';
        $upgrade['semester'] = null;
        $label = (string) $upgrade['year'];
    }

    $out['ok'] = true;
    $out['row'] = $upgrade;
    $out['semister_or_year_no'] = $label;
    return $out;
}
