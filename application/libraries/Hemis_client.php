<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Outbound sync to HEMIS (UGC) or local mock — used after a student is saved locally.
 */
class Hemis_client
{
    /** @var CI_Controller */
    protected $CI;

    /** @var array */
    protected $cfg;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('hemis_integration_model');
        $this->CI->load->model('ugc_error_log_model');
        $this->cfg = $this->CI->hemis_integration_model->get_effective_config();
        if (!is_array($this->cfg)) {
            $this->cfg = array();
        }
        $this->CI->load->library('hemis_ugc_auth');
        $this->cfg = $this->CI->hemis_ugc_auth->merge_auto_login_into_config($this->cfg);
    }

    /**
     * Live UGC often expects Employee **create** at POST /api/Employee/{universityId} (same id as EmployeePositions/Get/{id}).
     * Employee **update** stays PATCH /api/Employee/{hemisCentralEmployeeId}.
     *
     * @return string Path beginning with /api/Employee (no trailing slash)
     */
    protected function _hemis_employee_create_path()
    {
        if (function_exists('app_env')) {
            $legacy = app_env('HEMIS_EMPLOYEE_POST_LEGACY_PATH', '');
            if ($legacy !== '' && filter_var($legacy, FILTER_VALIDATE_BOOLEAN)) {
                return '/api/Employee';
            }
        }

        $uniId = 0;
        if (isset($this->cfg['remote_university_id'])) {
            $uniId = (int) $this->cfg['remote_university_id'];
        }
        if ($uniId < 1 && isset($this->cfg['remote_uni_id'])) {
            $uniId = (int) $this->cfg['remote_uni_id'];
        }
        if ($uniId < 1) {
            return '/api/Employee';
        }

        return '/api/Employee/' . $uniId;
    }

    /**
     * Ensure Student date fields are in the formats required by UGC:
     * - doBBS: yyyy/mm/dd
     * - doBAD: yyyy-mm-dd
     *
     * Normalizes common variants (yyyy-mm-dd <=> yyyy/mm/dd).
     */
    protected function _normalize_student_dates(array &$postfields)
    {
        if (isset($postfields['doBBS']) && is_string($postfields['doBBS'])) {
            $v = trim($postfields['doBBS']);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $postfields['doBBS'] = str_replace('-', '/', $v);
            }
        }
        if (isset($postfields['doBAD']) && is_string($postfields['doBAD'])) {
            $v = trim($postfields['doBAD']);
            if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $v)) {
                $postfields['doBAD'] = str_replace('/', '-', $v);
            }
        }
    }

    /**
     * Student Create/PATCH outbound: safe doBAD (from dob_ad / post), resolve address IDs to names,
     * optional ugc_addresses combined_code names, then preflight required permanent address fields.
     *
     * @param array $postfields Multipart field map (mutated).
     * @param int   $student_id Local students.id.
     * @param array $out        Result template; returned filled when sync is blocked.
     * @return array|null       Return array to short-circuit caller, or null to continue.
     */
    protected function _student_hemis_multipart_sanitize_and_preflight(array &$postfields, $student_id, array $out)
    {
        $student_id = (int) $student_id;
        $row = null;
        if ($student_id > 0 && isset($this->CI->student_model) && is_object($this->CI->student_model)) {
            $row = $this->CI->student_model->get_student_by_id($student_id);
        }
        if (!is_array($row)) {
            $row = null;
        }

        $this->CI->load->helper('hemis_student');

        $name_from_id = function ($table, $id) {
            $id = is_numeric($id) ? (int) $id : 0;
            if ($id < 1 || !$this->CI->db->table_exists($table)) {
                return '';
            }
            $q = $this->CI->db->select('name')->from($table)->where('id', $id)->limit(1)->get();
            if ($q && $q->num_rows() > 0) {
                return (string) $q->row()->name;
            }
            return '';
        };

        $pickStr = function ($v) {
            if ($v === null) {
                return '';
            }
            return trim((string) $v);
        };

        $get_row_field = function ($k) use ($row, $pickStr) {
            if ($row === null || !is_array($row)) {
                return '';
            }
            $v = hemis_student_field($row, $k);
            if ($v === null) {
                return '';
            }
            return $pickStr($v);
        };

        // Multipart from browsers / JS may use UGC PascalCase names only; internal logic uses camelCase.
        // Copy Pascal → camel when camel is missing/empty so resolution and DB fallbacks see the same values.
        $pascalToCamel = array(
            'PProvince' => 'pProvince',
            'PDistrict' => 'pDistrict',
            'PLocalLevel' => 'pLocalLevel',
            'TProvince' => 'tProvince',
            'TDistrict' => 'tDistrict',
            'TLocalLevel' => 'tLocalLevel',
            'DoBAD' => 'doBAD',
            'DateOfEnrollment' => 'dateOfEnrollment',
            'ComplitionYear' => 'complitionYear',
            'LevelName' => 'levelName',
            'Year' => 'year',
            'Semester' => 'semester',
            'PWardNo' => 'pWardNo',
        );
        foreach ($pascalToCamel as $pascal => $camel) {
            $camelEmpty = !isset($postfields[$camel]) || $postfields[$camel] === null || $pickStr($postfields[$camel]) === '';
            if ($camelEmpty && array_key_exists($pascal, $postfields) && $postfields[$pascal] !== null) {
                $postfields[$camel] = $postfields[$pascal];
            }
        }

        // --- doBAD: English AD date; DB column is `dob_ad` (see hemis_sync_post_from_db helper). ---
        $rawCandidates = array();
        if (isset($postfields['doBAD']) && $postfields['doBAD'] !== null && $postfields['doBAD'] !== '') {
            $rawCandidates[] = $postfields['doBAD'];
        }
        if ($row !== null && is_array($row)) {
            $dobRow = hemis_student_field($row, 'dob_ad');
            if ($dobRow !== null && $dobRow !== '') {
                $rawCandidates[] = $dobRow;
            }
        }

        $dobAD = null;
        foreach ($rawCandidates as $raw_dob_ad) {
            if (is_string($raw_dob_ad)) {
                $raw_dob_ad = trim($raw_dob_ad);
            } else {
                $raw_dob_ad = trim((string) $raw_dob_ad);
            }
            if ($raw_dob_ad === '' || $raw_dob_ad === '0000-00-00' || $raw_dob_ad === '0000-00-00 00:00:00') {
                continue;
            }
            $ts = strtotime($raw_dob_ad);
            if ($ts !== false && $ts > 0) {
                $dobAD = date('Y-m-d', $ts);
                break;
            }
        }

        if ($dobAD !== null) {
            // Match UGC GET shape (DateTime string) for multipart binding parity.
            $postfields['doBAD'] = (strlen($dobAD) === 10 && strpos($dobAD, 'T') === false)
                ? ($dobAD . 'T00:00:00')
                : $dobAD;
        } else {
            unset($postfields['doBAD']);
        }

        // ── dateOfEnrollment ──────────────────────────────────
        $valid_doe = null;

        // Helper: check if a string is a valid AD date
        $is_valid_ad = function ($d) {
            if (empty($d) || $d === '0000-00-00') {
                return false;
            }
            $ts = strtotime($d);
            if ($ts === false || $ts <= 0) {
                return false;
            }
            $y = (int) date('Y', $ts);
            return ($y >= 1950 && $y <= 2060);
        };

        // BS→AD: same rule as Customlib::datetostrtotime() for Y-m-d when year is 2070–2100 (BS − 57 = AD year, same m/d).
        $bs_to_ad_customlib_style = function ($bs_date) use ($is_valid_ad) {
            $clean = str_replace('/', '-', trim($bs_date));
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $clean, $m)) {
                return null;
            }
            $by = (int) $m[1];
            $bm = (int) $m[2];
            $bd = (int) $m[3];
            if ($by < 2070 || $by > 2100) {
                return null;
            }
            $ad_year = $by - 57;
            $result = sprintf('%04d-%02d-%02d', $ad_year, $bm, $bd);
            $ts = strtotime($result);
            if ($ts === false || $ts <= 0) {
                return null;
            }
            if (!$is_valid_ad($result)) {
                return null;
            }
            return date('Y-m-d', $ts);
        };

        // Step 1: Try dateOfEnrollment from POST/postfields
        $doe_raw = isset($postfields['dateOfEnrollment'])
            ? trim((string) $postfields['dateOfEnrollment'])
            : (isset($postfields['DateOfEnrollment'])
                ? trim((string) $postfields['DateOfEnrollment'])
                : '');

        if ($is_valid_ad($doe_raw)) {
            $valid_doe = date('Y-m-d', strtotime($doe_raw));
        } elseif ($doe_raw !== '') {
            $converted = $bs_to_ad_customlib_style($doe_raw);
            if ($converted !== null) {
                $valid_doe = $converted;
                log_message('info', 'HEMIS Student: dateOfEnrollment BS→AD: ' . $doe_raw . ' → ' . $valid_doe);
            }
        }

        // Step 2: Try admission_date from DB if still empty
        if ($valid_doe === null) {
            $sid = $student_id > 0 ? $student_id : (isset($postfields['id'])
                ? (int) $postfields['id']
                : (isset($postfields['studentId'])
                    ? (int) $postfields['studentId']
                    : 0));

            if ($sid > 0) {
                $sRow = $this->CI->db
                    ->select('admission_date, dob_ad, id')
                    ->where('id', $sid)
                    ->get('students')
                    ->row_array();

                if (!empty($sRow)) {
                    $db_doe = trim((string) ($sRow['admission_date'] ?? ''));
                    if ($is_valid_ad($db_doe)) {
                        $valid_doe = date('Y-m-d', strtotime($db_doe));
                    } elseif ($db_doe !== '') {
                        $converted = $bs_to_ad_customlib_style($db_doe);
                        if ($converted !== null) {
                            $valid_doe = $converted;
                            log_message('info', 'HEMIS Student: DB admission_date BS→AD: ' . $db_doe . ' → ' . $valid_doe);
                        }
                    }
                }
            }
        }

        // Step 3: Absolute last resort — use today's AD date
        if ($valid_doe === null) {
            $valid_doe = date('Y-m-d');
            log_message('error', 'HEMIS Student: could not resolve dateOfEnrollment, using today: ' . $valid_doe);
        }

        $postfields['dateOfEnrollment'] = $valid_doe;
        // Remote sync strips duplicate Pascal form keys so UGC receives camelCase (matches API JSON).
        // ── end dateOfEnrollment ──────────────────────────────

        // Fix complitionYear
        $cy_raw = isset($postfields['complitionYear'])
            ? (int) $postfields['complitionYear']
            : (isset($postfields['ComplitionYear'])
                ? (int) $postfields['ComplitionYear']
                : 0);

        if ($cy_raw >= 2070 && $cy_raw <= 2100) {
            $postfields['complitionYear'] = $cy_raw;
        } else {
            $approx_bs_year = (int) date('Y') + 57;
            $postfields['complitionYear'] = $approx_bs_year;
            log_message('error', 'HEMIS Student: complitionYear was invalid (' . $cy_raw . '), using approx: ' . $approx_bs_year);
        }

        // --- Permanent address: ugc_* names, combined_code cache, permanent_* + ID→name ---
        $pProv = isset($postfields['pProvince']) ? $pickStr($postfields['pProvince']) : '';
        $pDist = isset($postfields['pDistrict']) ? $pickStr($postfields['pDistrict']) : '';
        $pLoc = isset($postfields['pLocalLevel']) ? $pickStr($postfields['pLocalLevel']) : '';

        if ($pProv === '') {
            $pProv = $get_row_field('ugc_p_province');
        }
        if ($pDist === '') {
            $pDist = $get_row_field('ugc_p_district');
        }
        if ($pLoc === '') {
            $pLoc = $get_row_field('ugc_p_local_level');
        }

        $combined = '';
        foreach (array('pCombinedCode', 'ugc_p_combined_code') as $ck) {
            if (array_key_exists($ck, $postfields) && $postfields[$ck] !== null && $postfields[$ck] !== '') {
                $combined = $pickStr($postfields[$ck]);
                if ($combined !== '') {
                    break;
                }
            }
        }
        if ($combined === '') {
            $combined = $get_row_field('ugc_p_combined_code');
        }

        if ($combined !== '' && $this->CI->db->table_exists('ugc_addresses')) {
            $addr = $this->CI->db->select('province, district, local_level')
                ->from('ugc_addresses')->where('combined_code', $combined)->limit(1)->get()->row_array();
            if (!empty($addr)) {
                if (!empty($addr['province'])) {
                    $pProv = trim((string) $addr['province']);
                }
                if (!empty($addr['district'])) {
                    $pDist = trim((string) $addr['district']);
                }
                if (!empty($addr['local_level'])) {
                    $pLoc = trim((string) $addr['local_level']);
                }
            }
        }

        if ($pProv === '') {
            $pProv = $get_row_field('permanent_province');
        }
        if ($pDist === '') {
            $pDist = $get_row_field('permanent_district');
        }
        if ($pLoc === '') {
            $pLoc = $get_row_field('permanent_local_level');
        }

        if ($pProv !== '' && is_numeric($pProv)) {
            $pProv = $name_from_id('states', $pProv);
        }
        if ($pDist !== '' && is_numeric($pDist)) {
            $pDist = $name_from_id('districts', $pDist);
        }
        if ($pLoc !== '' && is_numeric($pLoc)) {
            $pLoc = $name_from_id('municipalities', $pLoc);
        }

        hemis_student_normalize_ugc_address_wire_triple($pProv, $pDist, $pLoc);

        $postfields['pProvince'] = trim((string) $pProv);
        $postfields['pDistrict'] = trim((string) $pDist);
        $postfields['pLocalLevel'] = trim((string) $pLoc);

        // Permanent ward — required by UGC as multipart field pWardNo (camelCase).
        $pWardRaw = '';
        if (isset($postfields['PWardNo'])) {
            $pWardRaw = $pickStr($postfields['PWardNo']);
        }
        if ($pWardRaw === '' && isset($postfields['pWardNo'])) {
            $pWardRaw = $pickStr($postfields['pWardNo']);
        }
        if ($pWardRaw === '') {
            $pWardRaw = $get_row_field('permanent_ward_no');
        }
        if ($pWardRaw === '') {
            $pWardRaw = $get_row_field('temporary_ward_no');
        }
        $pWardInt = (int) $pWardRaw;
        if ($pWardInt > 0) {
            $postfields['pWardNo'] = $pWardInt;
        } else {
            unset($postfields['pWardNo']);
        }
        unset($postfields['PWardNo']);

        // --- Temporary address (mirror validation style; same name resolution). ---
        $tProv = isset($postfields['tProvince']) ? $pickStr($postfields['tProvince']) : '';
        $tDist = isset($postfields['tDistrict']) ? $pickStr($postfields['tDistrict']) : '';
        $tLoc = isset($postfields['tLocalLevel']) ? $pickStr($postfields['tLocalLevel']) : '';

        if ($tProv === '') {
            $tProv = $get_row_field('ugc_t_province');
        }
        if ($tDist === '') {
            $tDist = $get_row_field('ugc_t_district');
        }
        if ($tLoc === '') {
            $tLoc = $get_row_field('ugc_t_local_level');
        }

        $tCombined = '';
        foreach (array('tCombinedCode', 'ugc_t_combined_code') as $tck) {
            if (array_key_exists($tck, $postfields) && $postfields[$tck] !== null && $postfields[$tck] !== '') {
                $tCombined = $pickStr($postfields[$tck]);
                if ($tCombined !== '') {
                    break;
                }
            }
        }
        if ($tCombined === '') {
            $tCombined = $get_row_field('ugc_t_combined_code');
        }

        if ($tCombined !== '' && $this->CI->db->table_exists('ugc_addresses')) {
            $tAddr = $this->CI->db->select('province, district, local_level')
                ->from('ugc_addresses')->where('combined_code', $tCombined)->limit(1)->get()->row_array();
            if (!empty($tAddr)) {
                if (!empty($tAddr['province'])) {
                    $tProv = trim((string) $tAddr['province']);
                }
                if (!empty($tAddr['district'])) {
                    $tDist = trim((string) $tAddr['district']);
                }
                if (!empty($tAddr['local_level'])) {
                    $tLoc = trim((string) $tAddr['local_level']);
                }
            }
        }

        if ($tProv === '') {
            $tProv = $get_row_field('temporary_province');
        }
        if ($tDist === '') {
            $tDist = $get_row_field('temporary_district');
        }
        if ($tLoc === '') {
            $tLoc = $get_row_field('temporary_local_level');
        }

        $sameRaw = isset($postfields['isSameAsPermanent']) ? $postfields['isSameAsPermanent'] : null;
        $sameStr = strtolower(trim((string) $sameRaw));
        $isSame = ($sameRaw === true || $sameRaw === 1 || $sameRaw === '1' || $sameStr === 'true' || $sameStr === 'yes');
        if ($isSame) {
            $tProv = $postfields['pProvince'];
            $tDist = $postfields['pDistrict'];
            $tLoc = $postfields['pLocalLevel'];
        } else {
            if ($tProv !== '' && is_numeric($tProv)) {
                $tProv = $name_from_id('states', $tProv);
            }
            if ($tDist !== '' && is_numeric($tDist)) {
                $tDist = $name_from_id('districts', $tDist);
            }
            if ($tLoc !== '' && is_numeric($tLoc)) {
                $tLoc = $name_from_id('municipalities', $tLoc);
            }
        }

        hemis_student_normalize_ugc_address_wire_triple($tProv, $tDist, $tLoc);

        $postfields['tProvince'] = trim((string) $tProv);
        $postfields['tDistrict'] = trim((string) $tDist);
        $postfields['tLocalLevel'] = trim((string) $tLoc);

        $required_address = array('pProvince', 'pDistrict', 'pLocalLevel', 'pWardNo');
        $address_missing = array();
        foreach ($required_address as $field) {
            if ($field === 'pWardNo') {
                if (!isset($postfields['pWardNo']) || (int) $postfields['pWardNo'] < 1) {
                    $address_missing[] = $field;
                }
                continue;
            }
            if (empty($postfields[$field])) {
                $address_missing[] = $field;
            }
        }
        if (!empty($address_missing)) {
            log_message('error', 'HEMIS Student sync blocked: empty address fields: '
                . implode(', ', $address_missing) . ' for student id: ' . $student_id);
            $out['synced'] = false;
            $out['mode'] = 'local_only';
            $out['error'] = 'Address fields missing: ' . implode(', ', $address_missing) . '. Check address mapping.';
            $this->_save_sync_error($student_id, $out['error']);
            return $out;
        }

        if (isset($postfields['doBAD']) && $postfields['doBAD'] === '0000-00-00') {
            unset($postfields['doBAD']);
            log_message('info', 'HEMIS Student: doBAD was 0000-00-00, set to null');
        }

        return null;
    }

    /**
     * UGC Student multipart: map permanent/temporary address + ward to PascalCase form keys the API validates
     * (errors cite PProvince, PDistrict, PLocalLevel, PWardNo). Drops camelCase counterparts to avoid duplicate parts.
     *
     * @param array $postfields
     */
    protected function _student_remote_apply_ugc_address_pascal_form_keys(array &$postfields)
    {
        $this->CI->load->helper('hemis_student');

        $scalarStr = function ($v) {
            if ($v === null || is_array($v) || is_object($v) || is_bool($v)) {
                return null;
            }
            $s = is_string($v) ? trim($v) : trim((string) $v);
            return $s === '' ? null : $s;
        };
        foreach (array(
            array('pProvince', 'PProvince'),
            array('pDistrict', 'PDistrict'),
            array('pLocalLevel', 'PLocalLevel'),
            array('tProvince', 'TProvince'),
            array('tDistrict', 'TDistrict'),
            array('tLocalLevel', 'TLocalLevel'),
        ) as $pair) {
            list($camel, $pascal) = $pair;
            if (!array_key_exists($camel, $postfields)) {
                continue;
            }
            $s = $scalarStr($postfields[$camel]);
            unset($postfields[$camel]);
            if ($s !== null) {
                $postfields[$pascal] = $s;
            } else {
                unset($postfields[$pascal]);
            }
        }

        // Last-chance wire alignment: ugc_addresses cache rows may omit "Province" or use "Metropolitian".
        $wireTriple = function ($pK, $dK, $lK) use (&$postfields) {
            $has = array_key_exists($pK, $postfields) || array_key_exists($dK, $postfields) || array_key_exists($lK, $postfields);
            if (!$has) {
                return;
            }
            $p = array_key_exists($pK, $postfields) && $postfields[$pK] !== null ? trim((string) $postfields[$pK]) : '';
            $d = array_key_exists($dK, $postfields) && $postfields[$dK] !== null ? trim((string) $postfields[$dK]) : '';
            $l = array_key_exists($lK, $postfields) && $postfields[$lK] !== null ? trim((string) $postfields[$lK]) : '';
            hemis_student_normalize_ugc_address_wire_triple($p, $d, $l);
            if ($p !== '') {
                $postfields[$pK] = $p;
            } else {
                unset($postfields[$pK]);
            }
            if ($d !== '') {
                $postfields[$dK] = $d;
            } else {
                unset($postfields[$dK]);
            }
            if ($l !== '') {
                $postfields[$lK] = $l;
            } else {
                unset($postfields[$lK]);
            }
        };
        $wireTriple('PProvince', 'PDistrict', 'PLocalLevel');
        $wireTriple('TProvince', 'TDistrict', 'TLocalLevel');
        if (isset($postfields['pWardNo']) && (int) $postfields['pWardNo'] > 0) {
            // Prefer native int for ASP.NET int model binding (string "1" can fail on some hosts).
            $postfields['PWardNo'] = (int) $postfields['pWardNo'];
            unset($postfields['pWardNo']);
        } else {
            unset($postfields['PWardNo'], $postfields['pWardNo']);
        }
        if (array_key_exists('tWardNo', $postfields)) {
            $tw = (int) $postfields['tWardNo'];
            unset($postfields['tWardNo']);
            if ($tw > 0) {
                $postfields['TWardNo'] = $tw;
            } else {
                unset($postfields['TWardNo']);
            }
        }
        // Same Pascal rule as address — validation cites GuardianPhone.
        if (array_key_exists('guardianPhone', $postfields)) {
            $s = $scalarStr($postfields['guardianPhone']);
            unset($postfields['guardianPhone']);
            if ($s !== null) {
                $postfields['GuardianPhone'] = $s;
            } else {
                unset($postfields['GuardianPhone']);
            }
        }
    }

    /**
     * Last-chance fill for UGC-required GuardianPhone / PWardNo when multipart map lost them (sanitize order, empty strings, etc.).
     *
     * @param array $postfields
     * @param int   $student_id
     */
    protected function _student_remote_ensure_guardian_phone_and_ward(array &$postfields, $student_id)
    {
        $student_id = (int) $student_id;
        $pick = function ($v) {
            if ($v === null) {
                return '';
            }
            return trim((string) $v);
        };

        $row = null;
        if ($student_id > 0 && isset($this->CI->student_model) && is_object($this->CI->student_model)) {
            $row = $this->CI->student_model->get_student_by_id($student_id);
        }
        if (!is_array($row)) {
            $row = null;
        }

        $this->CI->load->helper('hemis_student');

        $g = '';
        if (isset($postfields['GuardianPhone'])) {
            $g = $pick($postfields['GuardianPhone']);
        }
        if ($g === '' && isset($postfields['guardianPhone'])) {
            $g = $pick($postfields['guardianPhone']);
        }
        if ($g === '' && $row !== null) {
            foreach (array('guardian_phone', 'mobileno', 'father_phone', 'mother_phone') as $col) {
                $t = $pick(hemis_student_field($row, $col));
                if ($t !== '') {
                    $g = $t;
                    break;
                }
            }
        }
        if ($g === '') {
            $g = $pick(isset($postfields['phoneNumber']) ? $postfields['phoneNumber'] : '');
        }
        if ($g !== '') {
            $postfields['GuardianPhone'] = $g;
        }
        unset($postfields['guardianPhone']);

        $ward = 0;
        if (isset($postfields['PWardNo'])) {
            $ward = (int) $postfields['PWardNo'];
        }
        if ($ward < 1 && isset($postfields['pWardNo'])) {
            $ward = (int) $postfields['pWardNo'];
        }
        if ($ward < 1 && $row !== null) {
            foreach (array('permanent_ward_no', 'temporary_ward_no') as $col) {
                $w = (int) $pick(hemis_student_field($row, $col));
                if ($w > 0) {
                    $ward = $w;
                    break;
                }
            }
        }
        if ($ward > 0) {
            $postfields['PWardNo'] = $ward;
        }
        unset($postfields['pWardNo']);

        $samePerm = false;
        if (isset($postfields['isSameAsPermanent'])) {
            $rawSame = $postfields['isSameAsPermanent'];
            if (is_bool($rawSame)) {
                $samePerm = $rawSame;
            } else {
                $samePerm = (strtolower(trim((string) $rawSame)) === 'true' || $rawSame === 1 || $rawSame === '1');
            }
        }
        if ($samePerm && $ward > 0) {
            $postfields['TWardNo'] = $ward;
        }
        unset($postfields['tWardNo']);
    }

    /**
     * Remove stray Pascal duplicates for non-address fields where we prefer camelCase on the wire.
     * Address keys are handled by _student_remote_apply_ugc_address_pascal_form_keys (must not be unset here).
     *
     * @param array $postfields
     */
    protected function _student_remote_strip_duplicate_pascal_student_keys(array &$postfields)
    {
        foreach (array(
            'DateOfEnrollment', 'ComplitionYear', 'LevelName', 'Year', 'Semester',
            'DoBAD',
            'Ethnicity',
        ) as $legacy) {
            unset($postfields[$legacy]);
        }
        if (isset($postfields['FiscalYearId']) && !array_key_exists('fiscalYearId', $postfields)) {
            $postfields['fiscalYearId'] = $postfields['FiscalYearId'];
            unset($postfields['FiscalYearId']);
        }
    }

    protected function _is_dev_blocker_module($context)
    {
        $c = strtolower((string) $context);
        // Modules where upstream 404/500 is frequently an integration-side issue
        // and we want to keep local data flowing while surfacing a clear blocker.
        return in_array($c, array('library', 'employee', 'section', 'student'), true);
    }

    /**
     * Parse UGC error envelope and capture traceId/message where possible.
     *
     * @return array{trace_id:?string,message:?string}
     */
    protected function _extract_ugc_error_details($body)
    {
        $traceId = null;
        $msg = null;
        $json = json_decode((string) $body, true);
        if (is_array($json)) {
            if (isset($json['traceId']) && is_string($json['traceId'])) {
                $traceId = $json['traceId'];
            } elseif (isset($json['trace_id']) && is_string($json['trace_id'])) {
                $traceId = $json['trace_id'];
            }

            if (isset($json['message']) && is_string($json['message'])) {
                $msg = $json['message'];
            } elseif (isset($json['title']) && is_string($json['title'])) {
                $msg = $json['title'];
            } elseif (isset($json['error']) && is_string($json['error'])) {
                $msg = $json['error'];
            }
            if ($msg === null && isset($json['detail']) && is_string($json['detail']) && trim($json['detail']) !== '') {
                $msg = trim($json['detail']);
            }
            if ($msg === null && isset($json['errors']) && is_array($json['errors'])) {
                $flat = array();
                foreach ($json['errors'] as $k => $v) {
                    if (is_string($v) && trim($v) !== '') {
                        $flat[] = trim($v);
                    } elseif (is_array($v)) {
                        foreach ($v as $vv) {
                            if (is_string($vv) && trim($vv) !== '') {
                                $flat[] = trim($vv);
                            }
                        }
                    }
                }
                if (!empty($flat)) {
                    $msg = implode('; ', array_slice(array_unique($flat), 0, 10));
                }
            }
        }
        return array('trace_id' => $traceId, 'message' => $msg);
    }

    protected function _log_ugc_attempt($context, $method, $url, array $requestHeaders, $requestBody, $httpCode, $responseHeaders, $responseBody)
    {
        $details = $this->_extract_ugc_error_details($responseBody);
        $traceId = $details['trace_id'];
        $msg = $details['message'];

        $snip = substr((string) $responseBody, 0, 2000);
        $reqBodyStr = is_string($requestBody) ? $requestBody : json_encode($requestBody);

        if (isset($this->CI->ugc_error_log_model) && is_object($this->CI->ugc_error_log_model)) {
            $this->CI->ugc_error_log_model->log(array(
                'module'           => (string) $context,
                'endpoint'         => (string) $url,
                'http_method'      => strtoupper((string) $method),
                'http_code'        => (int) $httpCode,
                'trace_id'         => $traceId,
                'message'          => $msg ? $msg : null,
                'response_snippet' => $snip,
                'request_headers'  => json_encode($requestHeaders),
                'request_body'     => is_string($reqBodyStr) ? $reqBodyStr : '',
                'response_headers' => (string) $responseHeaders,
                'response_body'    => (string) $responseBody,
            ));
        }
    }

    protected function _ugc_dev_blocker_out(array $out, $context, $method, $url, $httpCode, $body)
    {
        $details = $this->_extract_ugc_error_details($body);
        $traceId = $details['trace_id'];
        $msg = $details['message'];

        $out['synced'] = false;
        $out['mode'] = 'local_only';
        $out['http_code'] = (int) $httpCode;
        $out['sync_pending'] = 'UGC_DEV_BLOCKER';

        $snip = substr((string) $body, 0, 2000);
        $out['error'] = 'UGC DEV blocker for ' . $context . ' (' . strtoupper((string) $method) . ' ' . $url . ', HTTP ' . (int) $httpCode . ')'
            . ($traceId ? ' traceId=' . $traceId : '')
            . ': ' . ($msg ? $msg : $snip);

        log_message('error', $out['error']);
        return $out;
    }

    protected function _normalize_building_payload(array &$payload)
    {
        $intFields = array('id', 'noOfClassrooms');
        foreach ($intFields as $f) {
            if (isset($payload[$f]) && $payload[$f] !== '' && $payload[$f] !== null) {
                $payload[$f] = (int) $payload[$f];
            }
        }

        $floatFields = array('areaCoveredByBuilding', 'areaCoveredByAllRooms');
        foreach ($floatFields as $f) {
            if (isset($payload[$f]) && $payload[$f] !== '' && $payload[$f] !== null) {
                $payload[$f] = (float) $payload[$f];
            }
        }

        // UGC expects booleans (JSON true/false), not "1"/"0" strings.
        $boolFields = array('hasInternetConnection', 'ownershipOfBuilding');
        foreach ($boolFields as $bf) {
            if (!array_key_exists($bf, $payload)) {
                continue;
            }
            $v = $payload[$bf];
            if (is_string($v)) {
                $vv = strtolower(trim($v));
                $payload[$bf] = in_array($vv, array('1', 'true', 'yes', 'y', 'on', 'owned'), true);
            } else {
                $payload[$bf] = (bool) $v;
            }
        }

        // Legacy: hasInternetConnection handled above with boolFields.
    }

    /**
     * Extract UGC central employee id from POST /api/Employee response body (best-effort).
     *
     * @param string $body Raw HTTP body
     * @return int|null
     */
    protected function parse_hemis_employee_id_from_create_response($body)
    {
        $s = trim((string) $body);
        if ($s === '') {
            return null;
        }
        $j = json_decode($s, true);
        if (!is_array($j)) {
            return null;
        }
        $try = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            if (is_numeric($v) && (int) $v > 0) {
                return (int) $v;
            }
            return null;
        };
        $pickFromArray = function (array $arr) use ($try) {
            foreach (array('id', 'hemis_employee_id', 'employeeId', 'employee_id') as $k) {
                if (isset($arr[$k])) {
                    $id = $try($arr[$k]);
                    if ($id !== null) {
                        return $id;
                    }
                }
            }
            return null;
        };
        // (a) {"id": ...}, (b) {"hemis_employee_id": ...} at root
        $root = $pickFromArray($j);
        if ($root !== null) {
            return $root;
        }
        // (c) {"data": {"id": ...}}, (d) {"employee": {"id": ...}},
        // (e) {"status": "Success", "data": {"id": ...}}, etc.
        foreach (array('data', 'result', 'employee') as $wrap) {
            if (!isset($j[$wrap]) || !is_array($j[$wrap])) {
                continue;
            }
            $inner = $j[$wrap];
            $id = $pickFromArray($inner);
            if ($id !== null) {
                return $id;
            }
            foreach (array('employee', 'data', 'result') as $wrap2) {
                if (isset($inner[$wrap2]) && is_array($inner[$wrap2])) {
                    $id2 = $pickFromArray($inner[$wrap2]);
                    if ($id2 !== null) {
                        return $id2;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Map a local staff row (staff_model->get()) to the HEMIS Employee payload keys (camelCase per UGC docs).
     *
     * @param array $staff
     * @return array
     */
    public function map_staff_row_to_hemis_employee_payload(array $staff)
    {
        $get = function ($k, $default = '') use ($staff) {
            return array_key_exists($k, $staff) && $staff[$k] !== null ? $staff[$k] : $default;
        };

        $first_non_empty = function (array $vals, $default = '') {
            foreach ($vals as $v) {
                $s = is_string($v) ? trim($v) : $v;
                if ($s !== '' && $s !== null) {
                    return $v;
                }
            }
            return $default;
        };

        $bs_slash = function ($v) {
            $s = trim((string) $v);
            if ($s === '') {
                return '';
            }
            // Normalize YYYY-MM-DD to YYYY/MM/DD for BS fields expected by HEMIS.
            if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $s)) {
                return str_replace('-', '/', $s);
            }
            return $s;
        };

        $ad_dash = function ($v) {
            $s = trim((string) $v);
            if ($s === '') {
                return '';
            }
            // Normalize YYYY/MM/DD to YYYY-MM-DD for AD fields expected by HEMIS.
            if (preg_match('/^\\d{4}\\/\\d{2}\\/\\d{2}$/', $s)) {
                return str_replace('/', '-', $s);
            }
            return $s;
        };

        $extract_citizenship_no = function ($citizenship_json) {
            $s = trim((string) $citizenship_json);
            if ($s === '') {
                return '';
            }
            $j = json_decode($s, true);
            if (is_array($j) && !empty($j) && isset($j[0]) && is_array($j[0]) && isset($j[0]['number'])) {
                return (string) $j[0]['number'];
            }
            return '';
        };

        $name_from_id = function ($table, $id) {
            $id = is_numeric($id) ? (int) $id : 0;
            if ($id < 1) {
                return '';
            }
            $q = $this->CI->db->select('name')->from($table)->where('id', $id)->limit(1)->get();
            if ($q && $q->num_rows() > 0) {
                return (string) $q->row()->name;
            }
            return '';
        };

        $gender = $get('gender', '');
        if (is_string($gender)) {
            $gender = strtolower(trim($gender));
        }

        // Dates: keep as stored; HEMIS expects non-empty strings.
        // staff.dob_bs exists in this schema; use that for DateOFBirth (BS) and staff.dob for AD where possible.
        $dob_bs = $bs_slash($get('dob_bs', ''));
        $dob_ad = $ad_dash($get('dob', ''));

        $joining = $bs_slash($get('date_of_joining', '') !== '' ? $get('date_of_joining', '') : $get('joining_date', ''));

        // Permanent address: prefer UGC/HEMIS text fields; legacy ID columns optional.
        $pProvince = trim((string) $get('ugc_p_province', ''));
        $pDistrict = trim((string) $get('ugc_p_district', ''));
        $pLocal = trim((string) $get('ugc_p_local_level', ''));
        $pCombined = trim((string) $get('ugc_p_combined_code', ''));
        if ($pCombined !== '' && $this->CI->db->table_exists('ugc_addresses')) {
            $addr = $this->CI->db->select('province, district, local_level')
                ->from('ugc_addresses')->where('combined_code', $pCombined)->limit(1)->get()->row_array();
            if (!empty($addr)) {
                if (!empty($addr['province'])) {
                    $pProvince = trim((string) $addr['province']);
                }
                if (!empty($addr['district'])) {
                    $pDistrict = trim((string) $addr['district']);
                }
                if (!empty($addr['local_level'])) {
                    $pLocal = trim((string) $addr['local_level']);
                }
            }
        }
        $pProvince = trim((string) $pProvince);
        $pDistrict = trim((string) $pDistrict);
        $pLocal = trim((string) $pLocal);
        $pWardNo = (int) $get('permanent_ward_no', 0);

        $rawSame = $get('is_same_as_permanent', 0);
        $isSameAsPermanent = ($rawSame === true || $rawSame === 1 || $rawSame === '1');

        $tProvince = trim((string) $get('ugc_t_province', ''));
        $tDistrict = trim((string) $get('ugc_t_district', ''));
        $tLocal = trim((string) $get('ugc_t_local_level', ''));
        $tCombined = trim((string) $get('ugc_t_combined_code', ''));
        if ($tCombined !== '' && $this->CI->db->table_exists('ugc_addresses')) {
            $addrT = $this->CI->db->select('province, district, local_level')
                ->from('ugc_addresses')->where('combined_code', $tCombined)->limit(1)->get()->row_array();
            if (!empty($addrT)) {
                if (!empty($addrT['province'])) {
                    $tProvince = trim((string) $addrT['province']);
                }
                if (!empty($addrT['district'])) {
                    $tDistrict = trim((string) $addrT['district']);
                }
                if (!empty($addrT['local_level'])) {
                    $tLocal = trim((string) $addrT['local_level']);
                }
            }
        }
        $tProvince = trim((string) $tProvince);
        $tDistrict = trim((string) $tDistrict);
        $tLocal = trim((string) $tLocal);
        $tWardNo = (int) $get('temporary_ward_no', 0);

        if ($isSameAsPermanent) {
            $tProvince = $pProvince;
            $tDistrict = $pDistrict;
            $tLocal = $pLocal;
            $tWardNo = $pWardNo > 0 ? $pWardNo : $tWardNo;
        }

        // Campus / institution ID:
        // Prefer configured Central API college/campus ID (remote_college_id), then staff.institution_id,
        // then sch_settings.id, then final fallback=1.
        $campusId = 0;
        if (isset($this->cfg['remote_college_id']) && (int) $this->cfg['remote_college_id'] > 0) {
            $campusId = (int) $this->cfg['remote_college_id'];
        }
        if ($campusId < 1) {
            $campusId = (int) $get('institution_id', 0);
        }
        if ($campusId < 1 && $this->CI->db->table_exists('sch_settings')) {
            $sch = $this->CI->db->select('id')->from('sch_settings')->limit(1)->get()->row_array();
            if (!empty($sch['id'])) {
                $campusId = (int) $sch['id'];
            }
        }
        if ($campusId < 1) {
            $campusId = 1;
        }

        $firstName = trim((string) $get('name', ''));
        $middleName = trim((string) $first_non_empty(array(
            $get('middle_name', ''),
            $get('middlenameNp', ''),
        ), ''));
        $lastName = trim((string) $get('surname', ''));
        $email = trim((string) $get('email', ''));
        $phone = trim((string) $first_non_empty(array(
            $get('contact_no', ''),
            $get('work_phone', ''),
            $get('emergency_contact_no', ''),
        ), ''));
        $ethnicity = trim((string) $get('caste', ''));
        $employeeType = trim((string) $get('employee_type', ''));
        $employeePositionId = (int) $get('employee_position_id', 0);
        $postName = trim((string) $get('post_name', ''));
        $positionType = trim((string) $get('position_type', ''));
        if ($positionType === '') {
            $positionType = 'Other';
        }

        // Resolve from cached EmployeePositions when staff row only has the id.
        if ($employeePositionId > 0 && ($employeeType === '' || $postName === '')) {
            $posRow = $this->_hemis_employee_position_row_from_cache($employeePositionId);
            if (is_array($posRow)) {
                if ($postName === '' && !empty($posRow['postName'])) {
                    $postName = trim((string) $posRow['postName']);
                }
                if ($employeeType === '' && !empty($posRow['type'])) {
                    $typeRaw = trim((string) $posRow['type']);
                    $employeeType = (strcasecmp($typeRaw, 'Teaching') === 0) ? 'teaching' : $typeRaw;
                }
            }
        }
        $salutation = trim((string) $get('salutation', ''));
        $citizenshipNo = trim((string) $get('citizenship_no', ''));
        if ($citizenshipNo === '') {
            $citizenshipNo = $extract_citizenship_no($get('citizenship', ''));
        }
        $panNo = trim((string) $get('pan_no', ''));
        $bankAc = trim((string) $get('bank_account_no', ''));
        $bankName = trim((string) $get('bank_name', ''));
        $bankBranch = trim((string) $get('bank_branch', ''));
        $joiningType = trim((string) $get('contract_type', ''));
        if ($joiningType === '') {
            $joiningType = 'permanent';
        } else {
            $joiningMap = array(
                'permanent'  => 'permanent',
                'probation'  => 'probation',
                'temporary'  => 'temporary',
                'contract'   => 'contract',
                'part-time'  => 'part-time',
                'intern'     => 'intern',
            );
            $jtKey = strtolower(str_replace(' ', '-', $joiningType));
            if (isset($joiningMap[$jtKey])) {
                $joiningType = $joiningMap[$jtKey];
            } else {
                $joiningType = strtolower($joiningType);
            }
        }
        $graduatedFrom = trim((string) $get('graduated_from', ''));
        if ($graduatedFrom === '') {
            $pi = $get('previous_institution_name', '');
            if (is_string($pi) && trim($pi) !== '') {
                $decoded = json_decode($pi, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $first = $decoded[0];
                    if (is_string($first)) {
                        $graduatedFrom = trim($first);
                    }
                }
            }
        }
        $religion = trim((string) $get('religion', ''));

        $ntType = $get('non_teaching_type', null);
        if ($ntType === '0' || $ntType === 0) {
            $isTeachingStaff = true;
        } elseif ($ntType === '1' || $ntType === 1) {
            $isTeachingStaff = false;
        } elseif ($employeePositionId > 0 && $this->CI->db->table_exists('ugc_employee_positions')) {
            $this->CI->load->model('ugc_metadata_model');
            $posRowNt = $this->CI->ugc_metadata_model->get_employee_position_by_id($employeePositionId);
            if (is_array($posRowNt) && !empty($posRowNt)) {
                $isTeachingStaff = $this->CI->ugc_metadata_model->is_teaching_position_row($posRowNt);
            } else {
                $etLower = strtolower($employeeType);
                $isTeachingStaff = ($etLower === 'teaching' || strpos($etLower, 'teach') !== false);
            }
        } else {
            $etLower = strtolower($employeeType);
            $isTeachingStaff = ($etLower === 'teaching' || strpos($etLower, 'teach') !== false);
        }

        $departmentId = null;
        $sectionId = null;
        $ugcDeptId = (int) $get('ugc_department_id', 0);
        $ugcSecId = (int) $get('ugc_section_id', 0);
        $localDeptId = (int) $get('department', 0);
        if ($isTeachingStaff) {
            if ($ugcDeptId > 0) {
                $departmentId = $ugcDeptId;
            } elseif ($localDeptId > 0) {
                $departmentId = $localDeptId;
            }
        } else {
            if ($ugcSecId > 0) {
                $sectionId = $ugcSecId;
            } elseif ($localDeptId > 0) {
                $sectionId = $localDeptId;
            }
        }

        if (($isTeachingStaff && $departmentId === null) || (!$isTeachingStaff && $sectionId === null)) {
            if ($employeePositionId > 0 && $this->CI->db->table_exists('ugc_employee_positions')) {
                if (!isset($this->CI->ugc_metadata_model)) {
                    $this->CI->load->model('ugc_metadata_model');
                }
                $posOrg = $this->CI->ugc_metadata_model->get_employee_position_by_id($employeePositionId);
                if (is_array($posOrg) && !empty($posOrg)) {
                    $orgIds = $this->CI->ugc_metadata_model->parse_position_row_org_ids($posOrg);
                    if ($isTeachingStaff && $departmentId === null && $orgIds['departmentId'] > 0) {
                        $departmentId = $orgIds['departmentId'];
                    }
                    if (!$isTeachingStaff && $sectionId === null && $orgIds['sectionId'] > 0) {
                        $sectionId = $orgIds['sectionId'];
                    }
                }
            }
        }

        $fiscalYearId = null;
        if (!empty($staff['ugc_fiscal_year_id']) && (int) $staff['ugc_fiscal_year_id'] > 0) {
            $fiscalYearId = (int) $staff['ugc_fiscal_year_id'];
        } elseif (!empty($staff['fiscal_year_id']) && (int) $staff['fiscal_year_id'] > 0) {
            $fiscalYearId = (int) $staff['fiscal_year_id'];
        } else {
            // DB fallback — get active fiscal year from ugc_fiscal_years table
            if ($this->CI->db->table_exists('ugc_fiscal_years')) {
                $fyRow = $this->CI->db
                    ->select('id')
                    ->from('ugc_fiscal_years')
                    ->where('active_fiscal_year', 1)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row_array();
                if (!empty($fyRow['id'])) {
                    $fiscalYearId = (int) $fyRow['id'];
                } else {
                    // Secondary fallback — get latest fiscal year by index
                    $fy2 = $this->CI->db
                        ->select('id')
                        ->from('ugc_fiscal_years')
                        ->order_by('idx', 'DESC')
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row_array();
                    if (!empty($fy2['id'])) {
                        $fiscalYearId = (int) $fy2['id'];
                    }
                }
            }
        }

        $teachingFacultyName = trim((string) $get('teaching_faculty_name', ''));
        $facultyName = trim((string) $get('faculty_name', ''));
        $programName = trim((string) $get('program_name', ''));
        $levelName = trim((string) $get('level_name', ''));
        $enrolledYear = trim((string) $get('enrolled_year', ''));
        $passedYear = trim((string) $get('passed_year', ''));

        $collegeName = trim((string) $get('college_name', ''));
        if ($collegeName === '') {
            $collegeName = trim((string) $get('campus_name', ''));
        }
        if ($collegeName === '' && $this->CI->db->table_exists('sch_settings')) {
            $sn = $this->CI->db->select('name')->from('sch_settings')->limit(1)->get()->row_array();
            if (!empty($sn['name'])) {
                $collegeName = trim((string) $sn['name']);
            }
        }

        $workPhone = trim((string) $first_non_empty(array(
            $get('work_phone', ''),
            $get('contact_no', ''),
        ), ''));
        $workEmail = trim((string) $first_non_empty(array(
            $get('work_email', ''),
            $get('email', ''),
        ), ''));

        $positionLabel = trim((string) $get('position', ''));
        if ($positionLabel === '' && $employeePositionId > 0) {
            $posRowCat = $this->_hemis_employee_position_row_from_cache($employeePositionId);
            if (is_array($posRowCat) && !empty($posRowCat['category'])) {
                $positionLabel = trim((string) $posRowCat['category']);
            }
        }
        if ($positionLabel === '') {
            $positionLabel = $isTeachingStaff ? 'Teaching' : 'Nonteaching';
        }

        // IMPORTANT (data integrity): do not fake PAN/bank/citizenship/email/phone to "make it pass".
        if ($salutation === '') {
            $salutation = ((string) $gender === 'female') ? 'Ms.' : 'Mr.';
        } else {
            $salNorm = array(
                'mr'        => 'Mr.',
                'mrs'       => 'Mrs.',
                'miss'      => 'Miss',
                'ms'        => 'Ms.',
                'dr'        => 'Dr.',
                'professor' => 'Professor',
            );
            $salKey = strtolower(rtrim($salutation, '.'));
            if (isset($salNorm[$salKey])) {
                $salutation = $salNorm[$salKey];
            }
        }

        $pTole = trim((string) $get('permanent_tole', ''));
        $pHouseNo = trim((string) $get('permanent_house_no', ''));
        $pAddress = trim((string) $get('permanent_address', ''));
        $tTole = trim((string) $get('temporary_tole', ''));
        $tHouseNo = trim((string) $get('temporary_house_no', ''));
        if ($tHouseNo === '') {
            $tHouseNo = $pHouseNo;
        }
        $tAddress = trim((string) $get('local_address', ''));
        if ($isSameAsPermanent) {
            $tTole = $pTole;
            $tHouseNo = $pHouseNo;
            $tAddress = $pAddress;
        }

        $ctzIssueDistrictCode = trim((string) $get('ctz_issue_district_code', ''));
        $nidNo = trim((string) $get('nid_no', ''));

        // HEMIS samples use the literal string "undefined" when faculty is not applicable.
        if ($teachingFacultyName === '') {
            $teachingFacultyName = 'undefined';
        }
        if ($facultyName === '') {
            $facultyName = 'undefined';
        }

        $payload = array(
            'firstName'            => (string) $firstName,
            'middleName'           => (string) $middleName,
            'lastName'             => (string) $lastName,
            'phoneNumber'          => (string) $phone,
            'gender'               => (string) $gender,
            'email'                => (string) $email,
            'ethnicity'            => (string) $ethnicity,
            'campusId'             => $campusId,
            'institutionId'        => $campusId,
            'teachingFacultyName'  => (string) $teachingFacultyName,

            'employeeType'         => (string) $employeeType,
            'employeePositionId'   => (int) $employeePositionId,
            'postName'             => (string) $postName,
            'positionType'         => (string) $positionType,
            'salutation'           => (string) $salutation,

            'citizenshipNo'        => (string) $citizenshipNo,
            'panNo'                => (string) $panNo,

            'bankAc'               => (string) $bankAc,
            'bankName'             => (string) $bankName,
            'bankBranch'           => (string) $bankBranch,

            'pProvince'            => (string) $pProvince,
            'pDistrict'            => (string) $pDistrict,
            'pLocalLevel'          => (string) $pLocal,
            'pWardNo'              => (int) $pWardNo,
            'pTole'                => $pTole !== '' ? (string) $pTole : null,
            'pHouseNo'             => $pHouseNo !== '' ? (string) $pHouseNo : null,
            'pAddress'             => $pAddress !== '' ? (string) $pAddress : null,

            'tProvince'            => (string) $tProvince,
            'tDistrict'            => (string) $tDistrict,
            'tLocalLevel'          => (string) $tLocal,
            'tWardNo'              => (int) $tWardNo,
            'tTole'                => $tTole !== '' ? (string) $tTole : null,
            'tHouseNo'             => $tHouseNo !== '' ? (string) $tHouseNo : null,
            'tAddress'             => $tAddress !== '' ? (string) $tAddress : null,

            'ctzIssueDistrictCode' => $ctzIssueDistrictCode !== '' ? (string) $ctzIssueDistrictCode : null,
            'nidNo'                => $nidNo !== '' ? (string) $nidNo : null,

            'dateOFBirth'          => $dob_bs !== '' ? $dob_bs : $bs_slash($dob_ad),
            'dateOFBirthAd'        => $dob_ad !== '' ? $dob_ad : $ad_dash($dob_bs),
            'joiningDate'          => $joining,
            'joiningType'          => (string) $joiningType,

            'graduatedFrom'        => (string) $graduatedFrom,
            'facultyName'          => (string) $facultyName,
            'programName'          => (string) $programName,
            'levelName'            => $levelName !== '' ? (string) $levelName : null,
            'enrolledYear'         => $enrolledYear !== '' ? (string) $enrolledYear : null,
            'passedYear'           => $passedYear !== '' ? (string) $passedYear : null,
            'collegeName'          => (string) $collegeName,
            'workPhone'            => (string) $workPhone,
            'workEmail'            => (string) $workEmail,
            'position'             => (string) $positionLabel,

            'religion'             => (string) $religion,
            'isSameAsPermanent'    => (bool) $isSameAsPermanent,

            'departmentId'         => $departmentId,
            'sectionId'            => $sectionId,
            'fiscalYearId'         => $fiscalYearId,
        );

        return $payload;
    }

    /**
     * Cached ugc_employee_positions row by HEMIS position id.
     *
     * @param int $position_id
     * @return array|null
     */
    protected function _hemis_employee_position_row_from_cache($position_id)
    {
        $position_id = (int) $position_id;
        if ($position_id < 1) {
            return null;
        }
        if (!$this->CI->db->table_exists('ugc_employee_positions')) {
            return null;
        }
        $row = $this->CI->db->select('id, category, type, post_name as postName')
            ->from('ugc_employee_positions')
            ->where('id', $position_id)
            ->limit(1)
            ->get()
            ->row_array();
        return is_array($row) && !empty($row) ? $row : null;
    }

    /**
     * Attach stored staff document paths as CURLFile for outbound Employee multipart sync.
     *
     * @param int   $staff_id
     * @param array $postfields
     */
    protected function _merge_staff_db_files_into_postfields($staff_id, array &$postfields)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id < 1 || !class_exists('CURLFile')) {
            return;
        }
        if (!$this->CI->db->table_exists('staff')) {
            return;
        }
        $row = $this->CI->db->where('id', $staff_id)->limit(1)->get('staff')->row_array();
        if (!is_array($row) || empty($row)) {
            return;
        }
        $map = array(
            'citizenship_front' => 'citizenshipFront',
            'citizenship_back'  => 'citizenshipBack',
            'nid_page'          => 'nidPage',
            'pp_size_photo'     => 'ppSizePhoto',
            'digital_signature' => 'digitalSignature',
        );
        $base = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR;
        foreach ($map as $col => $apiKey) {
            if (empty($row[$col])) {
                continue;
            }
            if (!empty($postfields[$apiKey]) && $postfields[$apiKey] instanceof CURLFile) {
                continue;
            }
            $path = $row[$col];
            if (!is_file($path)) {
                $try = $base . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, ltrim((string) $path, '/'));
                if (is_file($try)) {
                    $path = $try;
                } else {
                    continue;
                }
            }
            $mime = function_exists('mime_content_type') ? @mime_content_type($path) : 'application/octet-stream';
            $postfields[$apiKey] = new CURLFile($path, $mime, basename($path));
        }
    }

    /**
     * Precheck required employee fields (avoid pushing invalid records).
     *
     * @param array $payload
     * @return string[] list of missing keys
     */
    protected function _employee_required_missing(array $payload)
    {
        $need = array(
            'firstName', 'lastName', 'phoneNumber', 'gender', 'email', 'ethnicity',
            'employeeType', 'employeePositionId',
            'citizenshipNo', 'panNo', 'bankAc', 'bankName',
            'pProvince', 'pDistrict', 'pLocalLevel', 'pWardNo',
            'joiningDate', 'joiningType',
        );
        $same = !empty($payload['isSameAsPermanent']);
        if (!$same) {
            $need = array_merge($need, array('tProvince', 'tDistrict', 'tLocalLevel', 'tWardNo'));
        }
        $missing = array();
        foreach ($need as $k) {
            if (!array_key_exists($k, $payload)) {
                $missing[] = $k;
                continue;
            }
            $v = $payload[$k];
            if (is_int($v)) {
                if ($v < 1 && in_array($k, array('employeePositionId', 'pWardNo', 'tWardNo'), true)) {
                    $missing[] = $k;
                }
                continue;
            }
            $s = trim((string) $v);
            if ($s === '' || ($k === 'employeePositionId' && (int) $s < 1) || (in_array($k, array('pWardNo', 'tWardNo'), true) && (int) $s < 1)) {
                $missing[] = $k;
            }
        }
        $positionLabel = isset($payload['position']) ? strtolower(trim((string) $payload['position'])) : '';
        $isNonTeaching = ($positionLabel === 'nonteaching' || strpos($positionLabel, 'non') === 0);
        if ($isNonTeaching) {
            $sec = isset($payload['sectionId']) ? (int) $payload['sectionId'] : 0;
            if ($sec < 1 && $this->CI->db->table_exists('ugc_sections') && (int) $this->CI->db->count_all('ugc_sections') > 0) {
                $missing[] = 'sectionId';
            }
        }
        return $missing;
    }

    /**
     * Employee create with multipart + DB-file streaming (recommended).
     *
     * @param int $staff_id
     * @return array
     */
    public function sync_employee_after_local_create($staff_id)
    {
        $out = array(
            'synced'             => false,
            'mode'               => 'disabled',
            'error'              => null,
            'http_code'          => null,
            'status_code'        => null,
            'hemis_employee_id'  => null,
        );
        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_employee'])) {
            return $out;
        }
        $staff_id = (int) $staff_id;
        $this->CI->load->model('staff_model');
        $row = $this->CI->staff_model->get($staff_id);
        if (!is_array($row) || empty($row)) {
            $out['error'] = 'Employee not found';
            $out['mode'] = 'local_only';
            return $out;
        }
        $payload = $this->map_staff_row_to_hemis_employee_payload($row);
        $missing = $this->_employee_required_missing($payload);
        if (!empty($missing)) {
            $out['error'] = 'HEMIS employee precheck failed: missing/invalid ' . implode(', ', $missing);
            $out['mode'] = 'local_only';
            return $out;
        }

        // Build multipart fields (scalars + files)
        $postfields = $this->_flatten_payload_for_multipart($payload);
        $this->_merge_staff_db_files_into_postfields($staff_id, $postfields);

        // Execute multipart POST directly (same behavior as _exec_module_multipart_post but with files).
        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            $out['mode'] = 'local_only';
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $fake = 800000000 + ((int) $staff_id * 17 % 999999);
            if ($fake < 1) {
                $fake = 800000001;
            }
            $out['synced'] = true;
            $out['mode'] = 'mock';
            $out['hemis_employee_id'] = $fake;
            $out['status_code'] = 200;
            return $out;
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            $out['mode'] = 'local_only';
            return $out;
        }
        $url = rtrim($remote, '/') . $this->_hemis_employee_create_path();

        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            $out['mode'] = 'remote';
            $out['http_code'] = 0;
            $out['status_code'] = 0;
            return $out;
        }

        $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
        $body = is_string($raw) ? substr($raw, $hsz) : '';
        $out['mode'] = 'remote';
        $out['http_code'] = $code;
        $out['status_code'] = $code;
        if ($code >= 200 && $code < 300) {
            $out['synced'] = true;
            $parsedId = $this->parse_hemis_employee_id_from_create_response($body);
            if ($parsedId !== null && $parsedId > 0) {
                $out['hemis_employee_id'] = $parsedId;
            }
            $this->_log_ugc_attempt('Employee', 'POST', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
            return $out;
        }
        $this->_log_ugc_attempt('Employee', 'POST', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
        $out['error'] = 'HEMIS Employee create failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        return $out;
    }

    /**
     * Employee patch with multipart + DB-file streaming.
     *
     * @param int $staff_id
     * @return array
     */
    public function sync_employee_after_local_update($staff_id)
    {
        $staff_id = (int) $staff_id;
        $this->CI->load->model('staff_model');
        $row = $this->CI->staff_model->get($staff_id);
        if (!is_array($row) || empty($row)) {
            return array('synced' => false, 'mode' => 'local_only', 'error' => 'Employee not found');
        }
        $payload = $this->map_staff_row_to_hemis_employee_payload($row);
        $missing = $this->_employee_required_missing($payload);
        if (!empty($missing)) {
            return array('synced' => false, 'mode' => 'local_only', 'error' => 'HEMIS employee precheck failed: missing/invalid ' . implode(', ', $missing));
        }

        $pathEmployeeId = $staff_id;
        $hemisCentral = isset($row['hemis_employee_id']) ? (int) $row['hemis_employee_id'] : 0;
        if ($hemisCentral > 0) {
            $pathEmployeeId = $hemisCentral;
            $payload['id'] = $hemisCentral;
        }

        // Execute multipart PATCH directly (same as _exec_multipart_patch).
        if (!function_exists('curl_init')) {
            return array('synced' => false, 'mode' => 'local_only', 'error' => 'PHP curl extension is required for HEMIS HTTP sync.');
        }
        if (!empty($this->cfg['mock_mode'])) {
            return array('synced' => true, 'mode' => 'mock', 'error' => null);
        }
        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            return array('synced' => false, 'mode' => 'local_only', 'error' => 'HEMIS remote_base_url is empty.');
        }

        $url = rtrim($remote, '/') . '/api/Employee/' . (int) $pathEmployeeId;

        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $postfields = $this->_flatten_payload_for_multipart($payload);
        $this->_merge_staff_db_files_into_postfields($staff_id, $postfields);

        $out = array('synced' => false, 'mode' => 'remote', 'error' => null, 'http_code' => null);
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            $out['http_code'] = 0;
            return $out;
        }
        $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
        $body = is_string($raw) ? substr($raw, $hsz) : '';
        $out['http_code'] = $code;
        if ($code >= 200 && $code < 300) {
            $out['synced'] = true;
            $this->_log_ugc_attempt('Employee', 'PATCH', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
            return $out;
        }
        $this->_log_ugc_attempt('Employee', 'PATCH', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
        $out['error'] = 'HEMIS Employee patch failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        return $out;
    }

    /**
     * Call after api/Student local insert; uses current POST and $_FILES when forwarding HTTP.
     *
     * @param int        $student_id        Local students.id
     * @param array|null $multipart_fields Optional multipart map (e.g. from hemis_student_db_row_to_create_post).
     *                                     When set, used instead of CI input->post() so DB snapshot sync does not
     *                                     depend on mutating $_POST.
     * @return array { synced, hemis_student_id, mode, error }
     */
    public function sync_student_after_local_create($student_id, array $multipart_fields = null)
    {
        $out = array(
            'synced'           => false,
            'hemis_student_id' => null,
            'mode'             => 'disabled',
            'error'            => null,
        );

        if (empty($this->cfg['sync_enabled'])) {
            $out['mode'] = 'disabled';
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            return $this->_mock_assign_id($student_id, $out);
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/student_create';
            return $this->_post_multipart_remote($url, $student_id, $out, 'local_http', $multipart_fields);
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty and mock_mode is off.';
            $this->_save_sync_error($student_id, $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/Student/Create';
        return $this->_post_multipart_remote($url, $student_id, $out, 'remote', $multipart_fields);
    }

    protected function _mock_assign_id($student_id, array $out)
    {
        if (!$this->CI->db->field_exists('hemis_student_id', 'students')) {
            $out['error'] = 'Database is missing hemis_* columns. Run application/sql/hemis_student_columns.sql';
            $out['mode'] = 'mock';
            return $out;
        }

        $fakeId = 900000000 + ((int) $student_id * 17 % 1000000) + ((int) time() % 10000);
        $this->CI->student_model->update_hemis_fields((int) $student_id, array(
            'hemis_student_id' => $fakeId,
            'hemis_sync_at'    => date('Y-m-d H:i:s'),
            'hemis_sync_error' => null,
            'hemis_sync_error_at' => null,
        ));
        $out['synced'] = true;
        $out['hemis_student_id'] = $fakeId;
        $out['mode'] = 'mock';
        return $out;
    }

    /**
     * Max length for students.hemis_sync_error (matches migration 128 VARCHAR(1000); older DBs stay 500 until migrated).
     */
    protected function _hemis_student_sync_error_max_len()
    {
        static $len = null;
        if ($len !== null) {
            return $len;
        }
        if ($this->CI->db->table_exists('students') && $this->CI->db->field_exists('hemis_sync_error', 'students')) {
            $row = $this->CI->db->query('SHOW COLUMNS FROM `students` LIKE ' . $this->CI->db->escape('hemis_sync_error'))->row_array();
            $typeCol = '';
            if (!empty($row)) {
                $typeCol = isset($row['Type']) ? (string) $row['Type'] : (isset($row['type']) ? (string) $row['type'] : '');
            }
            if ($typeCol !== '' && preg_match('/varchar\((\d+)\)/i', $typeCol, $m)) {
                $len = max(100, (int) $m[1]);
                return $len;
            }
        }
        $len = 500;
        return $len;
    }

    protected function _save_sync_error($student_id, $msg)
    {
        $max = $this->_hemis_student_sync_error_max_len();
        $fields = array(
            'hemis_sync_error' => substr((string) $msg, 0, $max),
        );
        if ($this->CI->db->field_exists('hemis_sync_error_at', 'students')) {
            $fields['hemis_sync_error_at'] = date('Y-m-d H:i:s');
        }
        $this->CI->student_model->update_hemis_fields((int) $student_id, $fields);
    }

    /**
     * Rich message when PATCH is skipped because students.hemis_student_id is missing.
     *
     * @param int   $student_id
     * @param array $row        students row from get_student_by_id
     */
    protected function _build_patch_skipped_no_hemis_id_message($student_id, array $row)
    {
        $max = $this->_hemis_student_sync_error_max_len();
        $sid = (int) $student_id;
        $adm = isset($row['admission_no']) ? trim((string) $row['admission_no']) : '';
        $fn = isset($row['firstname']) ? trim((string) $row['firstname']) : '';
        $ln = isset($row['lastname']) ? trim((string) $row['lastname']) : '';
        $syncAt = isset($row['hemis_sync_at']) ? trim((string) $row['hemis_sync_at']) : '';
        $prevErr = isset($row['hemis_sync_error']) ? trim((string) $row['hemis_sync_error']) : '';
        if ($prevErr !== '' && (stripos($prevErr, 'PATCH skipped') === 0 || stripos($prevErr, 'UGC PATCH skipped') === 0)) {
            $prevErr = '';
        }

        $parts = array(
            'UGC PATCH skipped: hemis_student_id is empty',
            '(UGC Create never returned an id, or the id was cleared).',
            'local_id=' . $sid,
        );
        if ($adm !== '') {
            $parts[] = 'admission=' . $adm;
        }
        $nm = trim($fn . ' ' . $ln);
        if ($nm !== '') {
            $parts[] = 'name=' . $nm;
        }
        if ($syncAt !== '') {
            $parts[] = 'hemis_sync_at=' . $syncAt;
        }
        if ($prevErr !== '') {
            $oneLine = preg_replace('/\s+/', ' ', $prevErr);
            $parts[] = 'prior_sync_error=' . substr($oneLine, 0, min(320, $max - 120));
        }
        $parts[] = 'Fix: resolve prior CREATE/sync error, use Student Search retry or manual sync create path until hemis_student_id is set.';

        $msg = implode(' ', $parts);
        return substr($msg, 0, $max);
    }

    /**
     * UGC gateway overload / upstream timeout — treat as "Server Busy" for operators and bulk-sync JS.
     */
    protected function _hemis_transient_upstream_http($code)
    {
        return in_array((int) $code, array(502, 503, 504), true);
    }

    /**
     * @param string $curlErr
     * @return bool
     */
    protected function _curl_err_implies_upstream_busy($curlErr)
    {
        $e = strtolower((string) $curlErr);
        return $curlErr !== '' && (strpos($e, 'timed out') !== false || strpos($e, 'timeout') !== false
            || strpos($e, 'connection reset') !== false || strpos($e, 'connection refused') !== false);
    }

    /**
     * @param int    $httpCode 0 if unknown (curl-only failure)
     * @param string $curlErr
     * @return string Non-empty user-facing message
     */
    protected function _hemis_server_busy_message($httpCode, $curlErr = '')
    {
        if ($this->_curl_err_implies_upstream_busy($curlErr)) {
            return 'Server Busy: UGC connection timed out or was reset. Try again later.';
        }
        if ($this->_hemis_transient_upstream_http($httpCode)) {
            return 'Server Busy: UGC gateway is temporarily unavailable (HTTP ' . (int) $httpCode . '). Try again later.';
        }
        return '';
    }

    /**
     * @param int   $student_id
     * @param int   $httpCode
     * @param string $curlErr
     * @param array $out
     * @return array
     */
    protected function _out_server_busy($student_id, $httpCode, $curlErr, array $out)
    {
        $msg = $this->_hemis_server_busy_message($httpCode, $curlErr);
        if ($msg === '') {
            $msg = $curlErr !== '' ? (string) $curlErr : ('Server Busy: UGC returned HTTP ' . (int) $httpCode . '. Try again later.');
        }
        $out['error'] = $msg;
        $out['server_busy'] = true;
        $out['http_code'] = (int) $httpCode;
        $this->_save_sync_error($student_id, $msg);
        return $out;
    }

    /**
     * Attach stored student document paths as CURLFile when the current request has no upload (e.g. admin save + sync).
     *
     * @param int   $student_id
     * @param array $postfields
     */
    protected function _merge_student_db_files_into_postfields($student_id, array &$postfields)
    {
        if ($student_id < 1 || !class_exists('CURLFile')) {
            return;
        }
        $row = $this->CI->student_model->get_student_by_id((int) $student_id);
        if (!is_array($row) || empty($row)) {
            return;
        }
        $map = array(
            'citizenship_front' => 'citizenshipFront',
            'citizenship_back'  => 'citizenshipBack',
            'nid_pic'           => 'nidPic',
            'pp_size_photo'     => 'ppSizePhoto',
            'digital_signature' => 'digitalSignature',
            'receipt_image'     => 'receiptImage',
        );
        $base = rtrim(FCPATH, "/\\") . DIRECTORY_SEPARATOR;
        foreach ($map as $col => $apiKey) {
            if (empty($row[$col])) {
                continue;
            }
            if (!empty($postfields[$apiKey]) && $postfields[$apiKey] instanceof CURLFile) {
                continue;
            }
            $path = $row[$col];
            if (!is_file($path)) {
                $try = $base . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, ltrim((string) $path, '/'));
                if (is_file($try)) {
                    $path = $try;
                } else {
                    continue;
                }
            }
            $mime = function_exists('mime_content_type') ? @mime_content_type($path) : 'application/octet-stream';
            $postfields[$apiKey] = new CURLFile($path, $mime, basename($path));
        }
    }

    /**
     * UGC nginx commonly limits multipart bodies (~1 MB). Drop largest file parts first until under cap.
     *
     * @param array $postfields
     * @return array{removed: string[], before_bytes: int, after_bytes: int}
     */
    protected function _trim_student_multipart_files(array &$postfields)
    {
        $empty = array('removed' => array(), 'before_bytes' => 0, 'after_bytes' => 0);
        $max = isset($this->cfg['student_multipart_max_bytes']) ? (int) $this->cfg['student_multipart_max_bytes'] : 0;
        if ($max < 1 || !class_exists('CURLFile')) {
            return $empty;
        }

        $sizes = array();
        foreach ($postfields as $k => $v) {
            if (!$v instanceof CURLFile) {
                continue;
            }
            $path = $v->getFilename();
            $sz = (is_string($path) && $path !== '' && @is_file($path)) ? (int) @filesize($path) : 0;
            $sizes[$k] = $sz;
        }
        if (empty($sizes)) {
            return $empty;
        }

        $before = array_sum($sizes);
        $after = $before;
        asort($sizes);
        $keysOrdered = array_keys($sizes);

        $removed = array();
        while ($after > $max && !empty($keysOrdered)) {
            $dropKey = array_pop($keysOrdered);
            if (!isset($postfields[$dropKey]) || !$postfields[$dropKey] instanceof CURLFile) {
                continue;
            }
            $removed[] = $dropKey;
            $after -= isset($sizes[$dropKey]) ? (int) $sizes[$dropKey] : 0;
            unset($postfields[$dropKey]);
        }

        if (!empty($removed)) {
            log_message('info', 'HEMIS student multipart: removed ' . count($removed)
                . ' attachment(s) to stay under ~' . $max . ' bytes (files were ~' . $before . ' B). Dropped: '
                . implode(', ', $removed));
        }

        return array('removed' => $removed, 'before_bytes' => $before, 'after_bytes' => $after);
    }

    /**
     * @param array $postfields
     * @return int Number of CURLFile entries removed
     */
    protected function _strip_all_student_multipart_files(array &$postfields)
    {
        $n = 0;
        foreach ($postfields as $k => $v) {
            if ($v instanceof CURLFile) {
                unset($postfields[$k]);
                $n++;
            }
        }
        return $n;
    }

    /**
     * @param array|null $multipart_fields When an array, used as the multipart body (DB snapshot path). Otherwise CI POST.
     */
    protected function _post_multipart_remote($url, $student_id, array $out, $mode_label, array $multipart_fields = null)
    {
        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            $this->_save_sync_error($student_id, $out['error']);
            return $out;
        }

        if (is_array($multipart_fields)) {
            $postfields = $multipart_fields;
        } else {
            $post = $this->CI->input->post(NULL, false);
            if (!is_array($post)) {
                $post = array();
            }
            $postfields = $post;
        }

        // Remote UGC: address/ward use Pascal multipart keys; other scalars often camelCase.
        if ($mode_label === 'remote') {
            if (isset($postfields['gender']) && is_string($postfields['gender'])) {
                $postfields['gender'] = strtolower(trim($postfields['gender']));
            }
        }

        if (!empty($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || $file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if (is_uploaded_file($file['tmp_name']) && class_exists('CURLFile')) {
                    $postfields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                }
            }
        }
        $this->_merge_student_db_files_into_postfields((int) $student_id, $postfields);
        $this->_normalize_student_dates($postfields);
        $blocked = $this->_student_hemis_multipart_sanitize_and_preflight($postfields, $student_id, $out);
        if ($blocked !== null) {
            return $blocked;
        }
        if ($mode_label === 'remote') {
            if (isset($postfields['pWardNo'])) {
                $postfields['pWardNo'] = (int) $postfields['pWardNo'];
            }
            $this->_student_remote_apply_ugc_address_pascal_form_keys($postfields);
            $this->_student_remote_ensure_guardian_phone_and_ward($postfields, $student_id);
            $this->_student_remote_strip_duplicate_pascal_student_keys($postfields);
            $this->CI->load->helper('hemis_sync_post_from_db');
            hemis_student_prepare_ugc_multipart_wire($postfields);
        }
        $multipartTrim = $this->_trim_student_multipart_files($postfields);

        $reqLogBody = array('multipart_fields' => array_keys($postfields));
        // Include a small, safe preview of scalar values to help diagnose upstream 500s.
        // Files are logged only as filenames.
        $preview = array();
        foreach ($postfields as $k => $v) {
            if ($v instanceof CURLFile) {
                $preview[$k] = array('file' => $v->getPostFilename());
                continue;
            }
            if (is_array($v) || is_object($v)) {
                continue;
            }
            if ($v === null) {
                continue;
            }
            $sv = trim((string) $v);
            if ($sv === '') {
                continue;
            }
            // Keep previews short to avoid huge logs / PII sprawl.
            $preview[$k] = strlen($sv) > 120 ? (substr($sv, 0, 117) . '...') : $sv;
        }
        if (!empty($preview)) {
            $reqLogBody['multipart_preview'] = $preview;
        }
        if (!empty($multipartTrim['removed'])) {
            $reqLogBody['multipart_files_trimmed'] = $multipartTrim;
        }

        // Pre-flight validation for known-required fields.
        // Avoid hitting upstream with obviously-invalid payloads (which can surface as generic 500s).
        $missing = array();
        $needInt = array('campusId', 'programId', 'batchId');
        foreach ($needInt as $k) {
            if (!isset($postfields[$k]) || (int) $postfields[$k] < 1) {
                $missing[] = $k;
            }
        }
        $fyVal = 0;
        if (isset($postfields['fiscalYearId'])) {
            $fyVal = (int) $postfields['fiscalYearId'];
        } elseif (isset($postfields['FiscalYearId'])) {
            $fyVal = (int) $postfields['FiscalYearId'];
        }
        if ($fyVal < 1) {
            $missing[] = 'fiscalYearId';
        }

        $mandatoryIdGateFail = array();
        foreach (array('campusId', 'programId', 'batchId') as $_mid) {
            if (!isset($postfields[$_mid]) || (int) $postfields[$_mid] < 1) {
                $mandatoryIdGateFail[] = $_mid;
            }
        }
        if ($fyVal < 1) {
            $mandatoryIdGateFail[] = 'fiscalYearId';
        }
        if (!empty($mandatoryIdGateFail)) {
            log_message('error', 'HEMIS Validation Failure: campusId, programId, batchId, and fiscalYearId must all be > 0; '
                . 'invalid or missing: ' . implode(', ', $mandatoryIdGateFail) . ' (student_id=' . (int) $student_id . ')');
        }

        // UGC Student/Create: semester-based programs need `semester`; otherwise require annual `year` (First…Fourth).
        $scalarPart = function ($v) {
            if ($v === null || $v instanceof CURLFile) {
                return '';
            }
            return trim((string) $v);
        };
        $progTypeRaw = $scalarPart(isset($postfields['programType']) ? $postfields['programType'] : '');
        $progTypeLower = strtolower($progTypeRaw);
        $yearVal = $scalarPart(isset($postfields['year']) ? $postfields['year'] : '');
        if ($yearVal === '') {
            $yearVal = $scalarPart(isset($postfields['Year']) ? $postfields['Year'] : '');
        }
        $semVal = $scalarPart(isset($postfields['semester']) ? $postfields['semester'] : '');
        if ($semVal === '') {
            $semVal = $scalarPart(isset($postfields['Semester']) ? $postfields['Semester'] : '');
        }
        if (strpos($progTypeLower, 'semester') !== false) {
            if ($semVal === '') {
                $missing[] = 'semester';
            }
        } else {
            if ($yearVal === '') {
                $missing[] = 'year';
            }
        }

        if ($mode_label === 'remote') {
            $pc = '';
            if (!empty($postfields['pCombinedCode'])) {
                $pc = trim((string) $postfields['pCombinedCode']);
            }
            if ($pc === '') {
                $missing[] = 'pCombinedCode';
            }
            $sameRaw = isset($postfields['isSameAsPermanent']) ? $postfields['isSameAsPermanent'] : null;
            $same = ($sameRaw === true || $sameRaw === 1 || $sameRaw === '1'
                || strtolower(trim((string) $sameRaw)) === 'true'
                || strtolower(trim((string) $sameRaw)) === 'yes');
            if (!$same) {
                $tc = '';
                if (!empty($postfields['tCombinedCode'])) {
                    $tc = trim((string) $postfields['tCombinedCode']);
                }
                if ($tc === '') {
                    $missing[] = 'tCombinedCode';
                }
            }
            $ay = 0;
            if (isset($postfields['admissionYearId'])) {
                $ay = (int) $postfields['admissionYearId'];
            }
            if ($ay < 1) {
                $missing[] = 'admissionYearId';
            }
        }

        if (!empty($missing)) {
            $out['synced'] = false;
            $out['mode'] = 'local_only';
            $out['http_code'] = 0;
            $out['sync_pending'] = 'UGC_PRECHECK_FAILED';
            $out['error'] = 'HEMIS precheck failed: missing/invalid ' . implode(', ', $missing)
                . ' (set Academic year/semester on the student, or ensure class title implies year for auto-fill).';
            $this->_save_sync_error($student_id, $out['error']);
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt('Student', 'POST', $url, array('Accept: application/json'), $reqLogBody, 0, '', $out['error']);
            }
            log_message('error', $out['error']);
            return $out;
        }

        $headers = array('Accept: application/json');
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $attempt = 0;
        $raw = '';
        $curlErr = '';
        $code = 0;
        $hsz = 0;
        $respHeaders = '';
        $body = '';

        while ($attempt < 2) {
            $attempt++;
            $ch = curl_init($url);
            // TEMPORARY: remove before production — scalar preview only (no CURLFile contents).
            $preview = array();
            $log_key_alternates = array(
                array('firstName'),
                array('lastName'),
                array('campusId'),
                array('fiscalYearId', 'FiscalYearId'),
                array('dateOfEnrollment', 'DateOfEnrollment'),
                array('complitionYear', 'ComplitionYear'),
                array('levelName', 'LevelName'),
                array('programType'),
                array('year', 'Year'),
                array('semester', 'Semester'),
                array('PProvince', 'pProvince'),
                array('PDistrict', 'pDistrict'),
                array('PLocalLevel', 'pLocalLevel'),
                array('PWardNo', 'pWardNo'),
                array('GuardianPhone', 'guardianPhone'),
                array('doBAD', 'DoBAD'),
            );
            foreach ($log_key_alternates as $alts) {
                $label = $alts[0];
                $found = 'MISSING';
                foreach ($alts as $ak) {
                    if (isset($postfields[$ak]) && !($postfields[$ak] instanceof CURLFile)) {
                        $found = $postfields[$ak];
                        break;
                    }
                }
                $preview[$label] = $found;
            }
            log_message('error', 'HEMIS Student pre-send preview: ' . json_encode($preview));

            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postfields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
                CURLOPT_HTTPHEADER     => $headers,
            ));

            $raw = curl_exec($ch);
            $curlErr = curl_error($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            if ($curlErr) {
                break;
            }

            $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
            $body = is_string($raw) ? substr($raw, $hsz) : '';

            if ((int) $code === 401 && $mode_label === 'remote' && $attempt === 1) {
                if ($this->_refresh_remote_token_via_auto_login()) {
                    $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
                    $headers = array('Accept: application/json');
                    if ($token !== '') {
                        $headers[] = 'Authorization: Bearer ' . $token;
                    }
                    log_message('info', 'HEMIS Student Create: HTTP 401; auto-refreshed UGC token and retrying.');
                    continue;
                }
            }
            if ($code === 413 && $mode_label === 'remote' && $attempt === 1) {
                $stripped = $this->_strip_all_student_multipart_files($postfields);
                if ($stripped > 0) {
                    log_message('info', 'HEMIS Student Create: HTTP 413; retrying without attachments (removed '
                        . $stripped . ' file part(s)).');
                    $reqLogBody['multipart_413_retry_no_files'] = true;
                    $reqLogBody['multipart_fields'] = array_keys($postfields);
                    continue;
                }
            }
            if ((int) $code === 500 && $mode_label === 'remote' && $attempt === 1) {
                $hadFiles = false;
                foreach ($postfields as $v) {
                    if ($v instanceof CURLFile) {
                        $hadFiles = true;
                        break;
                    }
                }
                if ($hadFiles) {
                    $stripped500 = $this->_strip_all_student_multipart_files($postfields);
                    if ($stripped500 > 0) {
                        log_message('info', 'HEMIS Student Create: HTTP 500; retrying without attachments (removed '
                            . $stripped500 . ' file part(s)).');
                        $reqLogBody['multipart_500_retry_no_files'] = true;
                        $reqLogBody['multipart_fields'] = array_keys($postfields);
                        continue;
                    }
                }
            }
            break;
        }

        if ($curlErr) {
            if ($this->_curl_err_implies_upstream_busy($curlErr)) {
                return $this->_out_server_busy($student_id, 0, $curlErr, $out);
            }
            $out['error'] = $curlErr;
            $this->_save_sync_error($student_id, $curlErr);
            return $out;
        }

        if ($this->_hemis_transient_upstream_http($code)) {
            return $this->_out_server_busy($student_id, $code, '', $out);
        }

        if ($code < 200 || $code >= 300) {
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt('Student', 'POST', $url, $headers, $reqLogBody, $code, $respHeaders, $body);
            }

            // Treat upstream 500 as DEV blocker for Student Create (still persist error on the row for ops / retry).
            if ($mode_label === 'remote' && $this->_is_dev_blocker_module('Student') && in_array((int) $code, array(500), true)) {
                $out = $this->_ugc_dev_blocker_out($out, 'Student', 'POST', $url, $code, $body);
                if (!empty($out['error'])) {
                    $this->_save_sync_error($student_id, $out['error']);
                }
                $out['http_code'] = $code;
                return $out;
            }

            if ((int) $code === 413) {
                $out['error'] = 'HEMIS Create failed (HTTP 413): request body too large for the central server (nginx limit). '
                    . 'Use smaller images/PDFs, or set HEMIS_STUDENT_MULTIPART_MAX_BYTES in .env (bytes cap before send; 0 disables trimming).';
            } else {
                $out['error'] = 'HEMIS Create failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
            }
            $this->_save_sync_error($student_id, $out['error']);
            $out['http_code'] = $code;
            return $out;
        }

        $json = json_decode($body, true);
        $hid = null;
        if (is_array($json)) {
            if (isset($json['id'])) {
                $hid = (int) $json['id'];
            }
            if (!$hid && isset($json['data']['id'])) {
                $hid = (int) $json['data']['id'];
            }
        }

        if ($hid > 0) {
            $this->CI->student_model->update_hemis_fields((int) $student_id, array(
                'hemis_student_id' => $hid,
                'hemis_sync_at'    => date('Y-m-d H:i:s'),
                'hemis_sync_error' => null,
                'hemis_sync_error_at' => null,
            ));
            $out['synced'] = true;
            $out['hemis_student_id'] = $hid;
            $out['mode'] = $mode_label;
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt('Student', 'POST', $url, $headers, $reqLogBody, $code, $respHeaders, $body);
            }
            return $out;
        }

        $out['error'] = 'HEMIS response could not be parsed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        if ($mode_label === 'remote') {
            $this->_log_ugc_attempt('Student', 'POST', $url, $headers, $reqLogBody, $code, $respHeaders, $body);
        }
        $this->_save_sync_error($student_id, $out['error']);
        $out['http_code'] = $code;
        return $out;
    }

    /**
     * After local student update (PATCH). Uses HEMIS student id in URL; same multipart as create.
     *
     * @param int        $local_student_id students.id
     * @param array|null $multipart_fields Optional multipart map (DB snapshot); when set, replaces CI input->post().
     * @return array { synced, mode, error }
     */
    public function sync_student_after_local_update($local_student_id, array $multipart_fields = null)
    {
        $out = array(
            'synced' => false,
            'mode'   => 'disabled',
            'error'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_student_update'])) {
            return $out;
        }

        $row = $this->CI->student_model->get_student_by_id($local_student_id);
        $hid = isset($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : 0;
        if (!empty($this->cfg['mock_mode'])) {
            // In mock mode we must be able to "sync" existing records too.
            // If there is no hemis_student_id yet, assign a deterministic fake one (same as create).
            if ($hid < 1) {
                $assignOut = $this->_mock_assign_id((int) $local_student_id, array(
                    'synced'           => false,
                    'hemis_student_id' => null,
                    'mode'             => 'mock',
                    'error'            => null,
                ));
                return array(
                    'synced' => !empty($assignOut['synced']),
                    'mode'   => 'mock',
                    'error'  => isset($assignOut['error']) ? $assignOut['error'] : null,
                );
            }

            $this->CI->student_model->update_hemis_fields((int) $local_student_id, array(
                'hemis_sync_at'    => date('Y-m-d H:i:s'),
                'hemis_sync_error' => null,
                'hemis_sync_error_at' => null,
            ));
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if ($hid < 1) {
            $out['error'] = is_array($row)
                ? $this->_build_patch_skipped_no_hemis_id_message($local_student_id, $row)
                : 'UGC PATCH skipped: hemis_student_id is empty (student row not found).';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/student_update/' . $hid;
            return $this->_patch_multipart_remote($url, $local_student_id, $out, 'local_http', $multipart_fields);
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/Student/' . $hid;
        return $this->_patch_multipart_remote($url, $local_student_id, $out, 'remote', $multipart_fields);
    }

    /**
     * Forward StudentUpgrade JSON array to HEMIS POST /api/StudentUpgrade (doc 5.9).
     *
     * @param array $payload JSON-decoded array of upgrade rows
     * @return array { synced, mode, error, http_code? }
     */
    public function sync_student_upgrade_batch(array $payload)
    {
        $out = array(
            'synced' => false,
            'mode'   => 'disabled',
            'error'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_student_upgrade']) || empty($payload)) {
            return $out;
        }

        $this->_map_upgrade_payload_student_ids_to_hemis($payload);

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/student_upgrade';
            return $this->_post_json_remote($url, $payload, $out, 'local_http');
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/StudentUpgrade';
        return $this->_post_json_remote($url, $payload, $out, 'remote');
    }

    /**
     * @param array|null $multipart_fields When an array, used as PATCH body fields; otherwise CI input->post().
     */
    protected function _patch_multipart_remote($url, $student_id, array $out, $mode_label, array $multipart_fields = null)
    {
        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            $this->_save_sync_error($student_id, $out['error']);
            return $out;
        }

        if (is_array($multipart_fields)) {
            $postfields = $multipart_fields;
        } else {
            $post = $this->CI->input->post(NULL, false);
            if (!is_array($post)) {
                $post = array();
            }
            $postfields = $post;
        }

        // Same casing normalization as create (address Pascal; other fields camel where applicable).
        if ($mode_label === 'remote') {
            if (isset($postfields['gender']) && is_string($postfields['gender'])) {
                $postfields['gender'] = strtolower(trim($postfields['gender']));
            }
        }

        if (!empty($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || $file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if (is_uploaded_file($file['tmp_name']) && class_exists('CURLFile')) {
                    $postfields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                }
            }
        }
        $this->_merge_student_db_files_into_postfields((int) $student_id, $postfields);
        $this->_normalize_student_dates($postfields);
        $blockedPatch = $this->_student_hemis_multipart_sanitize_and_preflight($postfields, $student_id, $out);
        if ($blockedPatch !== null) {
            return $blockedPatch;
        }
        if ($mode_label === 'remote') {
            if (isset($postfields['pWardNo'])) {
                $postfields['pWardNo'] = (int) $postfields['pWardNo'];
            }
            $this->_student_remote_apply_ugc_address_pascal_form_keys($postfields);
            $this->_student_remote_ensure_guardian_phone_and_ward($postfields, $student_id);
            $this->_student_remote_strip_duplicate_pascal_student_keys($postfields);
            $this->CI->load->helper('hemis_sync_post_from_db');
            $patchHemisId = 0;
            if (preg_match('#/api/Student/(\d+)\s*$#', $url, $hemisIdMatch)) {
                $patchHemisId = (int) $hemisIdMatch[1];
            }
            // NOTE: do not pull UGC's stored values back over local edits here. A PATCH is a push of
            // campus data, so we send local fields as-is. (Earlier remote alignment was a workaround
            // for the 500, which was actually caused by the missing `id` field below.)
            hemis_student_prepare_ugc_multipart_wire($postfields);
            // UGC PATCH /api/Student/{id} binds the record by the `id` form field (not the route),
            // so a missing id makes the lookup default to 0 and the server throws a generic HTTP 500.
            if ($patchHemisId > 0) {
                $postfields['id'] = $patchHemisId;
            }
        }
        $multipartTrim = $this->_trim_student_multipart_files($postfields);

        $reqLogBody = array('multipart_fields' => array_keys($postfields));
        $preview = array();
        foreach ($postfields as $k => $v) {
            if ($v instanceof CURLFile) {
                $preview[$k] = array('file' => $v->getPostFilename());
                continue;
            }
            if (is_array($v) || is_object($v)) {
                continue;
            }
            if ($v === null) {
                continue;
            }
            $sv = trim((string) $v);
            if ($sv === '') {
                continue;
            }
            $preview[$k] = strlen($sv) > 120 ? (substr($sv, 0, 117) . '...') : $sv;
        }
        if (!empty($preview)) {
            $reqLogBody['multipart_preview'] = $preview;
        }
        if (!empty($multipartTrim['removed'])) {
            $reqLogBody['multipart_files_trimmed'] = $multipartTrim;
        }

        $headers = array('Accept: application/json');
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $attempt = 0;
        $raw = '';
        $curlErr = '';
        $code = 0;
        $hsz = 0;
        $respHeaders = '';
        $body = '';

        while ($attempt < 2) {
            $attempt++;
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_CUSTOMREQUEST  => 'PATCH',
                CURLOPT_POSTFIELDS     => $postfields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
                CURLOPT_HTTPHEADER     => $headers,
            ));

            $raw = curl_exec($ch);
            $curlErr = curl_error($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            if ($curlErr) {
                break;
            }

            $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
            $body = is_string($raw) ? substr($raw, $hsz) : '';

            if ((int) $code === 401 && $mode_label === 'remote' && $attempt === 1) {
                if ($this->_refresh_remote_token_via_auto_login()) {
                    $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
                    $headers = array('Accept: application/json');
                    if ($token !== '') {
                        $headers[] = 'Authorization: Bearer ' . $token;
                    }
                    log_message('info', 'HEMIS Student PATCH: HTTP 401; auto-refreshed UGC token and retrying.');
                    continue;
                }
            }
            if ($code === 413 && $mode_label === 'remote' && $attempt === 1) {
                $stripped = $this->_strip_all_student_multipart_files($postfields);
                if ($stripped > 0) {
                    log_message('info', 'HEMIS Student PATCH: HTTP 413; retrying without attachments (removed '
                        . $stripped . ' file part(s)).');
                    $reqLogBody['multipart_413_retry_no_files'] = true;
                    $reqLogBody['multipart_fields'] = array_keys($postfields);
                    continue;
                }
            }
            break;
        }

        if ($curlErr) {
            if ($this->_curl_err_implies_upstream_busy($curlErr)) {
                return $this->_out_server_busy($student_id, 0, $curlErr, $out);
            }
            $out['error'] = $curlErr;
            $this->_save_sync_error($student_id, $curlErr);
            return $out;
        }

        if ($this->_hemis_transient_upstream_http($code)) {
            return $this->_out_server_busy($student_id, $code, '', $out);
        }

        if ($code >= 200 && $code < 300) {
            $this->CI->student_model->update_hemis_fields((int) $student_id, array(
                'hemis_sync_at'    => date('Y-m-d H:i:s'),
                'hemis_sync_error' => null,
                'hemis_sync_error_at' => null,
            ));
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt('Student', 'PATCH', $url, $headers, $reqLogBody, $code, $respHeaders, $body);
            }
            return $out;
        }

        if ((int) $code === 413) {
            $out['error'] = 'HEMIS PATCH failed (HTTP 413): request body too large for the central server. '
                . 'Use smaller images/PDFs, or adjust HEMIS_STUDENT_MULTIPART_MAX_BYTES in .env.';
        } else {
            $out['error'] = 'HEMIS PATCH failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
            if ((int) $code === 500) {
                $out['error'] .= ' The UGC central server returned a generic error after accepting the payload.'
                    . ' Local changes are saved; contact UGC support with the HEMIS student id if this persists.';
            }
        }
        if ($mode_label === 'remote') {
            $this->_log_ugc_attempt('Student', 'PATCH', $url, $headers, $reqLogBody, $code, $respHeaders, $body);
        }
        $this->_save_sync_error($student_id, $out['error']);
        $out['http_code'] = $code;
        return $out;
    }

    protected function _post_json_remote($url, array $payload, array $out, $mode_label)
    {
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            return $out;
        }

        $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
        $body = is_string($raw) ? substr($raw, $hsz) : '';

        if ($code >= 200 && $code < 300) {
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt('StudentUpgrade', 'POST', $url, $headers, $payload, $code, $respHeaders, $body);
            }
            return $out;
        }

        $out['error'] = 'HEMIS StudentUpgrade failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        $out['http_code'] = $code;
        if ($mode_label === 'remote') {
            $this->_log_ugc_attempt('StudentUpgrade', 'POST', $url, $headers, $payload, $code, $respHeaders, $body);
        }
        return $out;
    }

    /**
     * HEMIS expects central studentId; map from local students.id when hemis_student_id exists.
     *
     * @param array $payload (modified by reference)
     */
    protected function _map_upgrade_payload_student_ids_to_hemis(array &$payload)
    {
        foreach ($payload as &$row) {
            if (!is_array($row) || empty($row['studentId'])) {
                continue;
            }
            $local = (int) $row['studentId'];
            $st = $this->CI->student_model->get_student_by_id($local);
            if (!empty($st['hemis_student_id'])) {
                $row['studentId'] = (int) $st['hemis_student_id'];
            }
        }
        unset($row);
    }

    /**
     * Forward GenerateGraduationApplication (existing student) to UGC — uses current POST + $_FILES.
     *
     * @param int $local_student_id students.id (must match StudentID in form)
     * @return array { synced, mode, error }
     */
    public function sync_graduation_generate_application($local_student_id)
    {
        $post = $this->CI->input->post(NULL, false);
        if (!is_array($post)) {
            $post = array();
        }
        return $this->sync_graduation_generate_application_fields((int) $local_student_id, $post, true);
    }

    /**
     * Build GenerateGraduationApplication fields from the local student row (campus graduate / promote).
     * Files are optional; StudentID is required by UGC.
     *
     * @param int   $local_student_id students.id
     * @param array $extra Optional overrides (Remarks, PassedYear, …)
     * @return array { synced, mode, error, skipped?, skip_reason? }
     */
    public function sync_graduation_generate_application_from_db($local_student_id, array $extra = array())
    {
        $local_student_id = (int) $local_student_id;
        $row = $this->CI->student_model->get_student_by_id($local_student_id);
        if (empty($row) || !is_array($row)) {
            return array(
                'synced' => false,
                'mode' => 'disabled',
                'error' => null,
                'skipped' => true,
                'skip_reason' => 'Student not found.',
            );
        }
        if (empty($row['hemis_student_id']) || (int) $row['hemis_student_id'] < 1) {
            return array(
                'synced' => false,
                'mode' => 'disabled',
                'error' => null,
                'skipped' => true,
                'skip_reason' => 'Missing hemis_student_id — sync student to HEMIS first.',
            );
        }

        $reg = '';
        foreach (array('university_regd_no', 'registration_no', 'admission_no') as $k) {
            if (!empty($row[$k])) {
                $reg = trim((string) $row[$k]);
                break;
            }
        }
        $fields = array(
            'StudentID'    => (string) (int) $row['hemis_student_id'],
            'StudentRegNo' => $reg,
            'CampusRolNo'  => !empty($row['roll_no']) ? trim((string) $row['roll_no']) : '',
            'FatherName'   => !empty($row['father_name']) ? trim((string) $row['father_name']) : '',
            'MotherName'   => !empty($row['mother_name']) ? trim((string) $row['mother_name']) : '',
            'ContactNo'    => !empty($row['mobileno']) ? trim((string) $row['mobileno']) : '',
            'GPA'          => isset($row['entrance_score']) ? (string) $row['entrance_score'] : '',
            'PassedYear'   => !empty($row['complition_year']) ? (string) (int) $row['complition_year'] : date('Y'),
            'Remarks'      => 'Campus graduate via Promote Students',
        );
        foreach ($extra as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $fields[$k] = is_scalar($v) ? (string) $v : $v;
        }

        return $this->sync_graduation_generate_application_fields($local_student_id, $fields, false);
    }

    /**
     * Shared outbound for GenerateGraduationApplication.
     *
     * @param int   $local_student_id
     * @param array $post Field map (StudentID, …)
     * @param bool  $merge_uploaded_files Merge $_FILES when true (API form path)
     * @return array
     */
    public function sync_graduation_generate_application_fields($local_student_id, array $post, $merge_uploaded_files = false)
    {
        $out = array(
            'synced' => false,
            'mode'   => 'disabled',
            'error'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_graduation'])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        $post = $this->_map_graduation_student_id_to_hemis($local_student_id, $post);
        if ($merge_uploaded_files) {
            $postfields = $this->_build_multipart_postfields($post);
        } else {
            $postfields = $post;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/graduation_generate';
            return $this->_post_multipart_url($url, $postfields, $local_student_id, $out, 'local_http');
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/Graduation/GenerateGraduationApplication';
        return $this->_post_multipart_url($url, $postfields, $local_student_id, $out, 'remote');
    }

    /**
     * Forward GenerateGraduationApplicationForStudent (external applicant) after local insert.
     *
     * @param int $local_student_id New students.id
     * @param array $postData Same keys as form (ApplicantNameEng, etc.)
     * @param array $uploadedFiles db column => filename under uploads/graduation/
     */
    public function sync_graduation_for_student($local_student_id, array $postData, array $uploadedFiles)
    {
        $out = array(
            'synced' => false,
            'mode'   => 'disabled',
            'error'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_graduation'])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        $postfields = $this->_build_graduation_for_student_fields($postData, $uploadedFiles);

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/graduation_for_student';
            return $this->_post_multipart_url($url, $postfields, $local_student_id, $out, 'local_http');
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/Graduation/GenerateGraduationApplicationForStudent';
        return $this->_post_multipart_url($url, $postfields, $local_student_id, $out, 'remote');
    }

    /**
     * POST /api/DropOut after local dropout save.
     *
     * @param int $local_student_id
     * @param array $input Decoded JSON (studentId, reason, dateOfEntry, remarks)
     */
    public function sync_dropout_post($local_student_id, array $input)
    {
        $out = array(
            'synced' => false,
            'mode'   => 'disabled',
            'error'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_dropout'])) {
            return $out;
        }

        $body = $this->_map_dropout_body_to_hemis($local_student_id, $input);
        $reasonLog = isset($input['reason']) ? (string) $input['reason'] : '';
        log_message('info', 'HEMIS DropOut outbound (POST /api/DropOut): reason=' . substr($reasonLog, 0, 250));

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/dropout_post';
            return $this->_post_json_remote_with_student_error($url, $body, $local_student_id, $out, 'local_http');
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/DropOut';
        return $this->_post_json_remote_with_student_error($url, $body, $local_student_id, $out, 'remote');
    }

    /**
     * PUT /api/DropOut/{id} after local dropout update.
     *
     * @param int $local_student_id URL/body student id (local)
     * @param array $input Same shape as POST
     */
    public function sync_dropout_put($local_student_id, array $input)
    {
        $out = array(
            'synced' => false,
            'mode'   => 'disabled',
            'error'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['sync_dropout'])) {
            return $out;
        }

        $body = $this->_map_dropout_body_to_hemis($local_student_id, $input);
        $urlId = isset($body['studentId']) ? (int) $body['studentId'] : (int) $local_student_id;

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/dropout_put/' . $urlId;
            return $this->_put_json_remote_with_student_error($url, $body, $local_student_id, $out, 'local_http');
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            $this->_save_sync_error($local_student_id, $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/api/DropOut/' . $urlId;
        return $this->_put_json_remote_with_student_error($url, $body, $local_student_id, $out, 'remote');
    }

    /**
     * PUT /api/Graduation/{id} after local graduation record update (JSON body per UGC doc).
     * URL id prefers hemis_student_id when set on the row.
     *
     * @param int $local_student_id students.id
     * @param array $payload Serializable graduation snapshot (e.g. HEMIS-shaped keys)
     */
    public function sync_graduation_put($local_student_id, array $payload)
    {
        $row = $this->CI->student_model->get_student_by_id($local_student_id);
        $urlId = !empty($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : (int) $local_student_id;
        $path = '/api/Graduation/' . $urlId;
        $mock = 'graduation_put/' . $urlId;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_graduation', 'Graduation');
    }

    protected function _map_graduation_student_id_to_hemis($local_student_id, array $post)
    {
        $row = $this->CI->student_model->get_student_by_id($local_student_id);
        if (!empty($row['hemis_student_id'])) {
            $post['StudentID'] = (string) (int) $row['hemis_student_id'];
        } elseif (!isset($post['StudentID']) || $post['StudentID'] === '' || $post['StudentID'] === null) {
            $post['StudentID'] = (string) (int) $local_student_id;
        }
        return $post;
    }

    protected function _build_multipart_postfields(array $post)
    {
        $postfields = $post;
        if (!empty($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || $file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if (is_uploaded_file($file['tmp_name']) && class_exists('CURLFile')) {
                    $postfields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
                }
            }
        }
        return $postfields;
    }

    /**
     * Map DB upload field names to multipart input names expected by UGC.
     */
    protected function _build_graduation_for_student_fields(array $postData, array $uploadedFiles)
    {
        $postfields = $postData;
        $map = array(
            'digital_signature' => 'UploadSignature',
            'pp_size_photo'     => 'UploadPPSizePhoto',
            'receipt_image'     => 'UploadReceipt',
            'transcript_image'  => 'UploadTranscript',
            'other_document'    => 'UploadOtherDoc',
        );
        $base = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'graduation' . DIRECTORY_SEPARATOR;
        foreach ($uploadedFiles as $dbField => $filename) {
            if (empty($filename) || empty($map[$dbField])) {
                continue;
            }
            $path = $base . $filename;
            if (!is_file($path) || !class_exists('CURLFile')) {
                continue;
            }
            $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
            $postfields[$map[$dbField]] = new CURLFile($path, $mime, $filename);
        }
        return $postfields;
    }

    protected function _map_dropout_body_to_hemis($local_student_id, array $input)
    {
        $body = $input;
        $row = $this->CI->student_model->get_student_by_id($local_student_id);
        if (!empty($row['hemis_student_id'])) {
            $body['studentId'] = (int) $row['hemis_student_id'];
        } elseif (isset($body['studentId'])) {
            $body['studentId'] = (int) $body['studentId'];
        }
        return $body;
    }

    protected function _post_multipart_url($url, array $postfields, $student_id, array $out, $mode_label)
    {
        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            $this->_save_sync_error($student_id, $out['error']);
            return $out;
        }

        $headers = array('Accept: application/json');
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            $this->_save_sync_error($student_id, $curlErr);
            return $out;
        }

        if ($code >= 200 && $code < 300) {
            $this->CI->student_model->update_hemis_fields((int) $student_id, array(
                'hemis_sync_at'    => date('Y-m-d H:i:s'),
                'hemis_sync_error' => null,
                'hemis_sync_error_at' => null,
            ));
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            return $out;
        }

        $out['error'] = 'HEMIS Graduation sync failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        $this->_save_sync_error($student_id, $out['error']);
        $out['http_code'] = $code;
        return $out;
    }

    protected function _post_json_remote_with_student_error($url, array $payload, $student_id, array $out, $mode_label)
    {
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            $this->_save_sync_error($student_id, $curlErr);
            return $out;
        }

        if ($code >= 200 && $code < 300) {
            $this->CI->student_model->update_hemis_fields((int) $student_id, array(
                'hemis_sync_at'    => date('Y-m-d H:i:s'),
                'hemis_sync_error' => null,
                'hemis_sync_error_at' => null,
            ));
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            return $out;
        }

        $out['error'] = 'HEMIS DropOut POST failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        $this->_save_sync_error($student_id, $out['error']);
        $out['http_code'] = $code;
        return $out;
    }

    protected function _put_json_remote_with_student_error($url, array $payload, $student_id, array $out, $mode_label)
    {
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            $this->_save_sync_error($student_id, $curlErr);
            return $out;
        }

        if ($code >= 200 && $code < 300) {
            $this->CI->student_model->update_hemis_fields((int) $student_id, array(
                'hemis_sync_at'    => date('Y-m-d H:i:s'),
                'hemis_sync_error' => null,
                'hemis_sync_error_at' => null,
            ));
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            return $out;
        }

        $out['error'] = 'HEMIS DropOut PUT failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        $this->_save_sync_error($student_id, $out['error']);
        $out['http_code'] = $code;
        return $out;
    }

    // --- Library, Employee, Labs, ProgramMgmt (JSON; errors logged, no student row update) ---

    public function sync_library_create(array $payload)
    {
        $nb = isset($payload['noOfBooks']) ? $payload['noOfBooks'] : null;
        $ar = isset($payload['areaOfReadingHall']) ? $payload['areaOfReadingHall'] : null;
        log_message('info', 'HEMIS Library outbound (POST /api/Library): noOfBooks=' . json_encode($nb) . ', areaOfReadingHall=' . json_encode($ar));
        return $this->_sync_module_json('POST', '/api/Library', 'library_post', $payload, 'sync_library', 'Library');
    }

    public function sync_library_update($local_id, array $payload)
    {
        $path = '/api/Library/' . (int) $local_id;
        $mock = 'library_put/' . (int) $local_id;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_library', 'Library');
    }

    public function sync_employee_create(array $payload)
    {
        // DEV schema mismatch workaround: always omit workingStatus/WorkingStatus if present.
        if (array_key_exists('workingStatus', $payload)) {
            unset($payload['workingStatus']);
        }
        if (array_key_exists('WorkingStatus', $payload)) {
            unset($payload['WorkingStatus']);
        }

        $empPostPath = $this->_hemis_employee_create_path();

        // HEMIS employee create is inconsistent across deployments:
        // - Some accept JSON array of employees
        // - Some accept single JSON object
        // - Some require PascalCase keys despite docs showing camelCase
        //
        // We attempt (1) array + camelCase, then retry (2) object + camelCase,
        // then retry (3) array + PascalCase, then (4) object + PascalCase when we see
        // the characteristic "all required fields missing" 400 validation envelope.

        $is_list = (!empty($payload) && array_keys($payload) === range(0, count($payload) - 1));
        $obj = $is_list ? (isset($payload[0]) && is_array($payload[0]) ? $payload[0] : array()) : $payload;
        $arr = $is_list ? $payload : array($payload);

        // Order matters: many deployments expect a single object or PascalCase.
        $attempts = array(
            array('shape' => 'object', 'case' => 'camel', 'body' => $obj),
            array('shape' => 'array', 'case' => 'camel', 'body' => $arr),
            array('shape' => 'object', 'case' => 'pascal', 'body' => $this->_employee_keys_pascalize($obj)),
            array('shape' => 'array', 'case' => 'pascal', 'body' => $this->_employee_keys_pascalize($arr)),
        );

        $out = null;
        $did_reauth_retry = false;
        foreach ($attempts as $i => $a) {
            $out = $this->_sync_module_json('POST', $empPostPath, 'employee_post', $a['body'], 'sync_employee', 'Employee');
            if (!is_array($out) || !empty($out['synced'])) {
                return $out;
            }

            // If unauthorized, try one-time auto re-login + retry same attempt.
            if (!$did_reauth_retry && isset($out['http_code']) && (int) $out['http_code'] === 401 && ($out['mode'] ?? '') === 'remote') {
                $did_reauth_retry = true;
                if ($this->_refresh_remote_token_via_auto_login()) {
                    $out = $this->_sync_module_json('POST', $empPostPath, 'employee_post', $a['body'], 'sync_employee', 'Employee');
                    if (!is_array($out) || !empty($out['synced'])) {
                        return $out;
                    }
                }
                // If still 401 after reauth attempt, stop early (permission issue).
                if (isset($out['http_code']) && (int) $out['http_code'] === 401) {
                    return $out;
                }
            }

            if (!$this->_looks_like_employee_required_fields_binding_failure($out)) {
                return $out;
            }
        }

        // Multipart fallback: some HEMIS deployments bind Employee create only from form fields.
        // Try camelCase first (matches API docs), then PascalCase (matches validation property names).
        $last = is_array($out) ? $out : array();

        $mfOut = $this->_sync_module_multipart_post($empPostPath, 'employee_post', $obj, 'sync_employee', 'Employee');
        if (is_array($mfOut) && !empty($mfOut['synced'])) {
            return $mfOut;
        }

        $objPascal = $this->_employee_keys_pascalize($obj);
        $mfOut2 = $this->_sync_module_multipart_post($empPostPath, 'employee_post', $objPascal, 'sync_employee', 'Employee');
        if (is_array($mfOut2)) {
            return $mfOut2;
        }

        return $last ? $last : array('synced' => false, 'mode' => 'disabled', 'error' => 'Employee sync failed');
    }

    /**
     * Force-refresh remote bearer token using configured login credentials.
     * Returns true when a new token is obtained and stored in $this->cfg.
     */
    protected function _refresh_remote_token_via_auto_login()
    {
        // Only attempt if credentials are configured.
        $email = isset($this->cfg['remote_login_email']) ? trim((string) $this->cfg['remote_login_email']) : '';
        $pass  = isset($this->cfg['remote_login_password']) ? (string) $this->cfg['remote_login_password'] : '';
        if ($email === '' || $pass === '') {
            return false;
        }

        $this->CI->load->library('hemis_ugc_auth');

        // Hemis_ugc_auth only logs in when token is empty; temporarily clear it for refresh.
        $saved = isset($this->cfg['remote_bearer_token']) ? (string) $this->cfg['remote_bearer_token'] : '';
        $this->cfg['remote_bearer_token'] = '';
        $newCfg = $this->CI->hemis_ugc_auth->merge_auto_login_into_config($this->cfg);
        $newToken = isset($newCfg['remote_bearer_token']) ? trim((string) $newCfg['remote_bearer_token']) : '';
        if ($newToken !== '') {
            $this->cfg = $newCfg;
            return true;
        }

        // Restore old token if refresh failed.
        $this->cfg['remote_bearer_token'] = $saved;
        return false;
    }

    /**
     * Multipart POST helper for modules whose upstream doesn't bind JSON.
     */
    protected function _sync_module_multipart_post($remote_path, $mock_path, array $payload, $flag_key, $context)
    {
        $out = array(
            'synced'     => false,
            'mode'       => 'disabled',
            'error'      => null,
            'http_code'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg[$flag_key])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            log_message('error', 'HEMIS ' . $context . ': ' . $out['error']);
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/' . ltrim($mock_path, '/');
            return $this->_exec_module_multipart_post('POST', $url, $payload, $out, 'local_http', $context);
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            log_message('error', 'HEMIS ' . $context . ': ' . $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/' . ltrim($remote_path, '/');
        return $this->_exec_module_multipart_post('POST', $url, $payload, $out, 'remote', $context);
    }

    protected function _exec_module_multipart_post($method, $url, array $payload, array $out, $mode_label, $context)
    {
        $out['mode'] = $mode_label;

        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $postfields = $this->_flatten_payload_for_multipart($payload);

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => strtoupper((string) $method),
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            log_message('error', 'HEMIS ' . $context . ' HTTP: ' . $curlErr);
            return $out;
        }

        $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
        $body = is_string($raw) ? substr($raw, $hsz) : '';

        if ($code >= 200 && $code < 300) {
            $out['synced'] = true;
            $out['http_code'] = $code;
            if ($context === 'Employee') {
                $parsedId = $this->parse_hemis_employee_id_from_create_response($body);
                if ($parsedId !== null && $parsedId > 0) {
                    $out['hemis_employee_id'] = $parsedId;
                }
            }
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt($context, strtoupper((string) $method), $url, $headers, $postfields, $code, $respHeaders, $body);
            }
            return $out;
        }

        // Graceful degradation for known DEV blockers (Library/Employee/Section) on 404/500.
        if ($mode_label === 'remote' && $this->_is_dev_blocker_module($context) && in_array((int) $code, array(404, 500), true)) {
            $this->_log_ugc_attempt($context, strtoupper((string) $method), $url, $headers, $postfields, $code, $respHeaders, $body);
            return $this->_ugc_dev_blocker_out($out, $context, strtoupper((string) $method), $url, $code, $body);
        }

        $out['error'] = 'HEMIS ' . $context . ' failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        $out['http_code'] = $code;
        if ($mode_label === 'remote') {
            $this->_log_ugc_attempt($context, strtoupper((string) $method), $url, $headers, $postfields, $code, $respHeaders, $body);
        }
        return $out;
    }

    /**
     * Convert known employee payload keys to PascalCase (firstName -> FirstName, panNo -> PanNo, ...).
     * Accepts either a single associative array or an array of associative arrays.
     *
     * @param array $payload
     * @return array
     */
    protected function _employee_keys_pascalize(array $payload)
    {
        $map = array(
            'firstName' => 'FirstName',
            'middleName' => 'MiddleName',
            'lastName' => 'LastName',
            'phoneNumber' => 'PhoneNumber',
            'gender' => 'Gender',
            'email' => 'Email',
            'ethnicity' => 'Ethnicity',
            'campusId' => 'CampusId',
            'institutionId' => 'InstitutionId',
            'employeeType' => 'EmployeeType',
            'employeePositionId' => 'EmployeePositionId',
            'postName' => 'PostName',
            'positionType' => 'PositionType',
            'salutation' => 'Salutation',
            'citizenshipNo' => 'CitizenshipNo',
            'panNo' => 'PanNo',
            'bankAc' => 'BankAc',
            'bankName' => 'BankName',
            'bankBranch' => 'BankBranch',
            'pProvince' => 'PProvince',
            'pDistrict' => 'PDistrict',
            'pLocalLevel' => 'PLocalLevel',
            'pWardNo' => 'PWardNo',
            'dateOFBirth' => 'DateOFBirth',
            'dateOFBirthAd' => 'DateOFBirthAd',
            'joiningDate' => 'JoiningDate',
            'joiningType' => 'JoiningType',
            'graduatedFrom' => 'GraduatedFrom',
            'religion' => 'Religion',
            'isSameAsPermanent' => 'IsSameAsPermanent',
        );

        $is_list = (!empty($payload) && array_keys($payload) === range(0, count($payload) - 1));
        if ($is_list) {
            $out = array();
            foreach ($payload as $row) {
                $out[] = is_array($row) ? $this->_employee_keys_pascalize($row) : $row;
            }
            return $out;
        }

        $out = array();
        foreach ($payload as $k => $v) {
            $nk = isset($map[$k]) ? $map[$k] : $k;
            $out[$nk] = $v;
        }
        return $out;
    }

    /**
     * Detect the specific 400 envelope that indicates payload didn't bind at all (so it reports many required fields missing).
     *
     * @param array $out Hemis_client out array
     * @return bool
     */
    protected function _looks_like_employee_required_fields_binding_failure(array $out)
    {
        if (empty($out['error']) || empty($out['http_code']) || (int) $out['http_code'] !== 400) {
            return false;
        }
        $err = (string) $out['error'];
        // HEMIS response JSON is truncated in error strings (for UI safety), so json_decode may fail.
        // Use a robust string-based check first; fall back to JSON parse when possible.
        $need = array('Email', 'PanNo', 'BankAc', 'Gender', 'FirstName', 'LastName', 'PhoneNumber', 'EmployeeType', 'EmployeePositionId');
        $hit = 0;
        foreach ($need as $k) {
            if (strpos($err, '"' . $k . '"') !== false) {
                $hit++;
            }
        }
        if ($hit >= 4) {
            return true;
        }

        $jsonStart = strpos($err, '{');
        if ($jsonStart === false) {
            return false;
        }
        $j = json_decode(substr($err, $jsonStart), true);
        if (!is_array($j) || empty($j['errors']) || !is_array($j['errors'])) {
            return false;
        }
        $keys = array_keys($j['errors']);
        $hit = 0;
        foreach ($need as $k) {
            if (in_array($k, $keys, true)) {
                $hit++;
            }
        }
        return $hit >= 4;
    }

    /**
     * PATCH /api/Employee/{id} — PDF V1.3 uses multipart/form-data; forward as multipart (scalar fields).
     */
    public function sync_employee_patch($local_id, array $payload)
    {
        // DEV schema mismatch workaround: omit workingStatus/WorkingStatus if present.
        if (array_key_exists('workingStatus', $payload)) {
            unset($payload['workingStatus']);
        }
        if (array_key_exists('WorkingStatus', $payload)) {
            unset($payload['WorkingStatus']);
        }

        $pathEmployeeId = (int) $local_id;
        $this->CI->load->model('staff_model');
        $row = $this->CI->staff_model->get((int) $local_id);
        if (is_array($row) && !empty($row['hemis_employee_id']) && (int) $row['hemis_employee_id'] > 0) {
            $pathEmployeeId = (int) $row['hemis_employee_id'];
            $payload['id'] = $pathEmployeeId;
        }

        $path = '/api/Employee/' . $pathEmployeeId;
        $mock = 'employee_patch/' . $pathEmployeeId;
        return $this->_sync_module_multipart_patch($path, $mock, $payload, 'sync_employee', 'Employee');
    }

    public function sync_labs_create(array $payload)
    {
        return $this->_sync_module_json('POST', '/api/Labs', 'labs_post', $payload, 'sync_labs', 'Labs');
    }

    public function sync_labs_update($local_id, array $payload)
    {
        $path = '/api/Labs/' . (int) $local_id;
        $mock = 'labs_put/' . (int) $local_id;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_labs', 'Labs');
    }

    public function sync_building_create(array $payload)
    {
        $this->_normalize_building_payload($payload);
        return $this->_sync_module_json('POST', '/api/Buildings', 'buildings_post', $payload, 'sync_buildings', 'Buildings');
    }

    public function sync_building_update($local_id, array $payload)
    {
        $path = '/api/Buildings/' . (int) $local_id;
        $mock = 'buildings_put/' . (int) $local_id;
        $this->_normalize_building_payload($payload);
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_buildings', 'Buildings');
    }

    public function sync_fev_create(array $payload)
    {
        return $this->_sync_module_json('POST', '/api/FurnitureEquipmentVehicle', 'fev_post', $payload, 'sync_fev', 'FurnitureEquipmentVehicle');
    }

    public function sync_fev_update($local_id, array $payload)
    {
        $path = '/api/FurnitureEquipmentVehicle/' . (int) $local_id;
        $mock = 'fev_put/' . (int) $local_id;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_fev', 'FurnitureEquipmentVehicle');
    }

    public function sync_hostel_create(array $payload)
    {
        return $this->_sync_module_json('POST', '/api/Hostels', 'hostels_post', $payload, 'sync_hostels', 'Hostels');
    }

    public function sync_hostel_update($local_id, array $payload)
    {
        $path = '/api/Hostels/' . (int) $local_id;
        $mock = 'hostels_put/' . (int) $local_id;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_hostels', 'Hostels');
    }

    public function sync_land_create(array $payload)
    {
        return $this->_sync_module_json('POST', '/api/Lands', 'lands_post', $payload, 'sync_lands', 'Lands');
    }

    public function sync_land_update($local_id, array $payload)
    {
        $path = '/api/Lands/' . (int) $local_id;
        $mock = 'lands_put/' . (int) $local_id;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_lands', 'Lands');
    }

    public function sync_management_add_department(array $payload)
    {
        return $this->_sync_module_json('POST', '/api/Management/AddDepartment', 'management_add_department', $payload, 'sync_management', 'Management');
    }

    public function sync_management_update_department($local_id, array $payload)
    {
        $path = '/api/Management/UpdateDepartment/' . (int) $local_id;
        $mock = 'management_put_department/' . (int) $local_id;
        return $this->_sync_module_json('PUT', $path, $mock, $payload, 'sync_management', 'Management');
    }

    public function sync_management_add_section(array $payload)
    {
        // Fallback spelling: try correct /api/Management/AddSection first, then PDF typo /api/Managent/AddSection on 404.
        $first = $this->_sync_module_json('POST', '/api/Management/AddSection', 'management_add_section', $payload, 'sync_management', 'Section');
        if (isset($first['http_code']) && (int) $first['http_code'] === 404) {
            return $this->_sync_module_json('POST', '/api/Managent/AddSection', 'management_add_section', $payload, 'sync_management', 'Section');
        }
        return $first;
    }

    public function sync_management_update_section($local_id, array $payload)
    {
        $id = (int) $local_id;
        $mock = 'management_put_section/' . $id;

        // Fallback spelling: try correct /api/Management/UpdateSection/{id} first, then PDF typo /api/Managent/UpdateSection/{id} on 404.
        $first = $this->_sync_module_json('PUT', '/api/Management/UpdateSection/' . $id, $mock, $payload, 'sync_management', 'Section');
        if (isset($first['http_code']) && (int) $first['http_code'] === 404) {
            return $this->_sync_module_json('PUT', '/api/Managent/UpdateSection/' . $id, $mock, $payload, 'sync_management', 'Section');
        }
        return $first;
    }

    /**
     * GET /api/ProgramMgmt/GetCollegePrograms from UGC (master programs for campus). PDF V1.3 §5.4.
     *
     * @return array { ok, data, mode, error, http_code } data = decoded JSON array or null
     */
    public function hemis_get_college_programs()
    {
        $out = array(
            'ok'         => false,
            'data'       => null,
            'mode'       => 'disabled',
            'error'      => null,
            'http_code'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['pull_programmgmt'])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['ok'] = true;
            $out['data'] = array();
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required.';
            return $out;
        }

        $path = '/api/ProgramMgmt/GetCollegePrograms';
        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/programmgmt_get';
            return $this->_exec_module_get_request($url, $out, 'local_http', 'ProgramMgmt');
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            return $out;
        }

        $url = rtrim($remote, '/') . $path;
        $res = $this->_exec_module_get_request($url, $out, 'remote', 'ProgramMgmt');

        // Normalize possible response envelopes to a plain list of rows.
        if (!empty($res['ok']) && is_array($res['data'])) {
            $rows = $this->_hemis_unwrap_get_response_to_rows($res['data']);
            if (is_array($rows)) {
                $res['data'] = $rows;
            }

            // If upstream returns an empty list, some deployments require campusId as a query param.
            // Retry once with campusId if configured.
            if (empty($res['data'])) {
                $campusId = 0;
                if (isset($this->cfg['remote_college_id'])) {
                    $campusId = (int) $this->cfg['remote_college_id'];
                } elseif (isset($this->cfg['college_id'])) {
                    $campusId = (int) $this->cfg['college_id'];
                }
                if ($campusId > 0) {
                    $url2 = $url . (strpos($url, '?') === false ? '?' : '&') . 'campusId=' . $campusId;
                    $res2 = $this->_exec_module_get_request($url2, $out, 'remote', 'ProgramMgmt');
                    if (!empty($res2['ok']) && is_array($res2['data'])) {
                        $rows2 = $this->_hemis_unwrap_get_response_to_rows($res2['data']);
                        if (is_array($rows2)) {
                            $res2['data'] = $rows2;
                        }
                    }
                    // Keep the retry if it returned any rows.
                    if (!empty($res2['ok']) && is_array($res2['data']) && !empty($res2['data'])) {
                        $res = $res2;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * UGC GET helper (Batch, FiscalYear, Address, EmployeePositions). PDF §5.2–5.5, §5.10.
     *
     * @param string      $path         e.g. /api/Batch
     * @param string      $pull_flag_key hemis.php toggle
     * @param string      $context      log label
     * @param string|null $mock_suffix  mockhemis path segment; null forces remote only even if local_mock_base_url is set
     * @return array { ok, data, mode, error, http_code }
     */
    protected function hemis_pull($path, $pull_flag_key, $context, $mock_suffix = null)
    {
        $out = array(
            'ok'         => false,
            'data'       => null,
            'mode'       => 'disabled',
            'error'      => null,
            'http_code'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg[$pull_flag_key])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['ok'] = true;
            $out['data'] = array();
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required.';
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '' && $mock_suffix !== null && $mock_suffix !== '') {
            $url = rtrim($local, '/') . '/' . ltrim($mock_suffix, '/');
            return $this->_exec_module_get_request($url, $out, 'local_http', $context);
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            return $out;
        }

        $url = rtrim($remote, '/') . '/' . ltrim($path, '/');
        return $this->_exec_module_get_request($url, $out, 'remote', $context);
    }

    /**
     * True when JSON decoded to a sequential 0..n-1 list (array of rows).
     *
     * @param array $arr
     * @return bool
     */
    protected function _hemis_json_array_is_list(array $arr)
    {
        if ($arr === array()) {
            return true;
        }
        $i = 0;
        foreach ($arr as $k => $_) {
            if ($k !== $i) {
                return false;
            }
            $i++;
        }
        return true;
    }

    /**
     * UGC GET bodies are often wrapped ({ data: [...] }) and/or use PascalCase keys.
     * Unwrap to a flat list of row arrays for ugc_metadata_model upserts.
     *
     * @param mixed $decoded
     * @return array
     */
    protected function _hemis_unwrap_get_response_to_rows($decoded)
    {
        if (!is_array($decoded)) {
            return array();
        }
        if ($this->_hemis_json_array_is_list($decoded)) {
            return $decoded;
        }
        $keys = array(
            'data', 'Data', 'result', 'Result', 'items', 'Items', 'value', 'Value',
            'fiscalYears', 'FiscalYears', 'batches', 'Batches', 'batchList', 'BatchList',
            'employeePositions', 'EmployeePositions', 'positions', 'Positions',
            'employeePositionList', 'EmployeePositionList',
        );
        foreach ($keys as $k) {
            if (isset($decoded[$k]) && is_array($decoded[$k])) {
                return $this->_hemis_unwrap_get_response_to_rows($decoded[$k]);
            }
        }
        return array();
    }

    /**
     * @param array $row
     * @param array $candidates
     * @return mixed|null
     */
    protected function _hemis_pick_first_key(array $row, array $candidates)
    {
        foreach ($candidates as $k) {
            if (array_key_exists($k, $row)) {
                return $row[$k];
            }
        }
        return null;
    }

    /**
     * @param array $r Raw row from UGC or mock
     * @return array { id, yearNepali, yearEnglish, activeFiscalYear, index } id 0 = invalid
     */
    protected function _hemis_normalize_fiscal_year_row_for_cache(array $r)
    {
        $id = $this->_hemis_pick_first_key($r, array('id', 'Id', 'ID'));
        if ($id === null || $id === '' || (int) $id < 1) {
            return array('id' => 0);
        }
        $yearNepali = $this->_hemis_pick_first_key($r, array('yearNepali', 'YearNepali'));
        $yearEnglish = $this->_hemis_pick_first_key($r, array('yearEnglish', 'YearEnglish'));
        $active = $this->_hemis_pick_first_key($r, array('activeFiscalYear', 'ActiveFiscalYear'));
        $index = $this->_hemis_pick_first_key($r, array('index', 'Index'));
        return array(
            'id'               => (int) $id,
            'yearNepali'       => $yearNepali !== null ? (string) $yearNepali : null,
            'yearEnglish'      => $yearEnglish !== null ? (string) $yearEnglish : null,
            'activeFiscalYear' => is_bool($active) ? $active : ($active === null ? null : (bool) $active),
            'index'            => $index !== null && $index !== '' ? (int) $index : null,
        );
    }

    /**
     * @param mixed $decoded json_decode root
     * @return array list of rows for Ugc_metadata_model::upsert_fiscal_years
     */
    protected function _hemis_normalize_fiscal_year_list_for_cache($decoded)
    {
        $rows = $this->_hemis_unwrap_get_response_to_rows($decoded);
        $out = array();
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $n = $this->_hemis_normalize_fiscal_year_row_for_cache($r);
            if (!empty($n['id'])) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /**
     * @param array $r
     * @return array { id, name, batchNepali, index, isActive } id 0 = invalid
     */
    protected function _hemis_normalize_batch_row_for_cache(array $r)
    {
        $id = $this->_hemis_pick_first_key($r, array('id', 'Id', 'ID'));
        if ($id === null || $id === '' || (int) $id < 1) {
            return array('id' => 0);
        }
        $index = $this->_hemis_pick_first_key($r, array('index', 'Index'));
        $isActive = $this->_hemis_pick_first_key($r, array('isActive', 'IsActive'));
        return array(
            'id'          => (int) $id,
            'name'        => $this->_hemis_pick_first_key($r, array('name', 'Name')),
            'batchNepali' => $this->_hemis_pick_first_key($r, array('batchNepali', 'BatchNepali')),
            'index'       => $index !== null && $index !== '' ? (int) $index : null,
            'isActive'    => is_bool($isActive) ? $isActive : ($isActive === null ? null : (bool) $isActive),
        );
    }

    /**
     * @param mixed $decoded
     * @return array
     */
    protected function _hemis_normalize_batch_list_for_cache($decoded)
    {
        $rows = $this->_hemis_unwrap_get_response_to_rows($decoded);
        $out = array();
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $n = $this->_hemis_normalize_batch_row_for_cache($r);
            if (!empty($n['id'])) {
                $out[] = $n;
            }
        }
        return $out;
    }

    /**
     * One EmployeePositions row → keys expected by Ugc_metadata_model::upsert_employee_positions.
     *
     * @param array $r
     * @return array { id, category, type, postName, index, status, remarks } id 0 = skip
     */
    protected function _hemis_normalize_employee_position_row_for_cache(array $r)
    {
        $id = $this->_hemis_pick_first_key($r, array('id', 'Id', 'ID', 'employeePositionId', 'EmployeePositionId'));
        if ($id === null || $id === '' || (int) $id < 1) {
            return array('id' => 0);
        }
        $postName = $this->_hemis_pick_first_key($r, array(
            'postName', 'PostName', 'post_name', 'name', 'Name', 'positionName', 'PositionName',
        ));
        $category = $this->_hemis_pick_first_key($r, array('category', 'Category'));
        $type = $this->_hemis_pick_first_key($r, array('type', 'Type', 'positionType', 'PositionType'));
        $index = $this->_hemis_pick_first_key($r, array('index', 'Index', 'idx', 'Idx'));
        $status = $this->_hemis_pick_first_key($r, array('status', 'Status'));
        $remarks = $this->_hemis_pick_first_key($r, array('remarks', 'Remarks'));

        return array(
            'id'        => (int) $id,
            'category'  => $category !== null ? (string) $category : null,
            'type'      => $type !== null ? (string) $type : null,
            'postName'  => $postName !== null ? (string) $postName : null,
            'index'     => $index !== null && $index !== '' ? (int) $index : null,
            'status'    => $status !== null ? $status : null,
            'remarks'   => $remarks !== null ? (string) $remarks : null,
        );
    }

    /**
     * @param mixed $decoded json_decode root from GET /api/EmployeePositions
     * @return array
     */
    protected function _hemis_normalize_employee_position_list_for_cache($decoded)
    {
        if (!is_array($decoded)) {
            return array();
        }
        $rows = $this->_hemis_unwrap_get_response_to_rows($decoded);
        // Single DTO at root (no list wrapper): e.g. { "Id": 1, "PostName": "..." }
        if ($rows === array() && !$this->_hemis_json_array_is_list($decoded)) {
            $idTry = $this->_hemis_pick_first_key($decoded, array('id', 'Id', 'ID', 'employeePositionId', 'EmployeePositionId'));
            if ($idTry !== null && (int) $idTry > 0) {
                $rows = array($decoded);
            }
        }
        $out = array();
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $n = $this->_hemis_normalize_employee_position_row_for_cache($r);
            if (!empty($n['id'])) {
                $out[] = $n;
            }
        }
        return $out;
    }

    public function hemis_get_batch()
    {
        $out = $this->hemis_pull('/api/Batch', 'pull_batch', 'Batch', 'batch_get');
        if (!empty($out['ok']) && is_array($out['data'])) {
            $out['data'] = $this->_hemis_normalize_batch_list_for_cache($out['data']);
        }
        return $out;
    }

    public function hemis_get_fiscal_year()
    {
        $out = $this->hemis_pull('/api/FiscalYear', 'pull_fiscal_year', 'FiscalYear', 'fiscalyear_get');
        if (!empty($out['ok']) && is_array($out['data'])) {
            $out['data'] = $this->_hemis_normalize_fiscal_year_list_for_cache($out['data']);
        }
        return $out;
    }

    public function hemis_get_address_all()
    {
        return $this->hemis_pull('/api/Address/GetAll', 'pull_address', 'Address', 'address_getall');
    }

    /**
     * GET /api/Management/Department — PDF V1.3 §5.6.
     */
    public function hemis_get_management_departments()
    {
        // UGC environments sometimes differ in path naming; try a small fallback list on 404.
        $candidates = array(
            '/api/Management/Department',
            '/api/Management/Departments',
            '/api/Management/GetDepartments',
            '/api/Management/Department/GetAll',
        );
        $last = null;
        foreach ($candidates as $path) {
            $last = $this->hemis_pull($path, 'pull_management', 'Management', 'management_department_get');
            if (empty($last['http_code']) || (int) $last['http_code'] !== 404) {
                return $last;
            }
        }
        return $last;
    }

    /**
     * GET /api/Managent/Section (typo in PDF) — PDF V1.3 §5.6.
     * Try correct /api/Management/Section first, then fallback to /api/Managent/Section.
     */
    public function hemis_get_management_sections()
    {
        // Try correct paths first, then PDF typo "Managent", then other known variants.
        $candidates = array(
            '/api/Management/Section',
            '/api/Management/Sections',
            '/api/Management/GetSections',
            '/api/Management/Section/GetAll',
            '/api/Managent/Section',
            '/api/Managent/Sections',
        );
        $last = null;
        foreach ($candidates as $path) {
            $last = $this->hemis_pull($path, 'pull_management', 'Section', 'management_section_get');
            if (empty($last['http_code']) || (int) $last['http_code'] !== 404) {
                return $last;
            }
        }
        return $last;
    }

    public function hemis_get_employee_positions()
    {
        $out = array(
            'ok'         => false,
            'data'       => null,
            'mode'       => 'disabled',
            'error'      => null,
            'http_code'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg['pull_employee_positions'])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['ok'] = true;
            $out['data'] = array();
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required.';
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/employeepositions_get';
            $out = $this->_exec_module_get_request($url, $out, 'local_http', 'EmployeePositions');
            if (!empty($out['ok']) && is_array($out['data'])) {
                $out['data'] = $this->_hemis_normalize_employee_position_list_for_cache($out['data']);
            }
            return $out;
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            return $out;
        }

        $uniId = isset($this->cfg['remote_university_id'])
            ? (int) $this->cfg['remote_university_id']
            : (isset($this->cfg['remote_uni_id']) ? (int) $this->cfg['remote_uni_id'] : 1);
        if ($uniId < 1) {
            $uniId = 1;
        }

        $path = '/api/EmployeePositions/Get/' . $uniId;
        $url = rtrim($remote, '/') . $path;
        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // TEMPORARY: remove before production
        log_message('info', 'HEMIS GET EmployeePositions request to: ' . $url);
        log_message('info', 'HEMIS GET EmployeePositions response code: ' . $code);
        log_message('info', 'HEMIS GET EmployeePositions response body: ' . substr((string) $body, 0, 500));

        if ($curlErr !== '') {
            $out['error'] = $curlErr;
            log_message('error', 'HEMIS EmployeePositions GET: ' . $curlErr);
        } elseif ($code >= 200 && $code < 300) {
            $json = json_decode($body, true);
            $out['ok'] = true;
            $out['data'] = is_array($json) ? $json : null;
            $out['mode'] = 'remote';
            $out['http_code'] = $code;
        } else {
            $out['error'] = 'HEMIS EmployeePositions GET failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
            log_message('error', $out['error']);
            $out['http_code'] = $code;
        }

        if (!empty($out['ok']) && is_array($out['data'])) {
            $out['data'] = $this->_hemis_normalize_employee_position_list_for_cache($out['data']);
        }

        $normalizedEmpty = empty($out['data']) || !is_array($out['data']) || count($out['data']) === 0;
        $allowCacheFallback = empty($this->cfg['mock_mode'])
            && !empty($this->cfg['sync_enabled'])
            && !empty($this->cfg['pull_employee_positions']);

        if ($allowCacheFallback && ((!empty($out['ok']) && $normalizedEmpty) || empty($out['ok']))) {
            $failCode = isset($out['http_code']) ? (int) $out['http_code'] : 0;
            log_message('error',
                'HEMIS EmployeePositions: using local DB cache fallback (HTTP '
                . $failCode . ' or empty list). Error: '
                . (isset($out['error']) ? (string) $out['error'] : 'none'));
            $this->CI->load->model('ugc_metadata_model');
            $cached = $this->CI->ugc_metadata_model->get_employee_positions_from_db_only();
            if (!empty($cached) && is_array($cached)) {
                log_message('info',
                    'HEMIS: using local positions cache (live API unavailable or empty), count: '
                    . count($cached));
                $out['ok'] = true;
                $out['data'] = $cached;
                $out['mode'] = 'local_cache';
                $out['error'] = null;
                $out['http_code'] = 200;
            }
        }

        return $out;
    }

    /**
     * TEMPORARY: probe which EmployeePositions-style path the UGC API accepts (GET + Bearer).
     */
    public function hemis_test_positions_endpoint()
    {
        $uniId = isset($this->cfg['remote_university_id'])
            ? (int) $this->cfg['remote_university_id']
            : (isset($this->cfg['remote_uni_id']) ? (int) $this->cfg['remote_uni_id'] : 1);
        if ($uniId < 1) {
            $uniId = 1;
        }
        $paths = array(
            '/api/EmployeePositions/Get/' . $uniId,
            '/api/EmployeePositions',
            '/api/EmployeePositions/GetAll',
        );
        $results = array();
        $token = isset($this->cfg['remote_bearer_token'])
            ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        foreach ($paths as $path) {
            $url = rtrim($remote, '/') . $path;
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_HTTPGET        => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => $headers,
            ));
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $results[$path] = array(
                'http_code' => $code,
                'success'   => ($code >= 200 && $code < 300),
                'sample'    => substr((string) $body, 0, 200),
            );
        }
        return $results;
    }

    public function hemis_get_employee_position($id)
    {
        $id = (int) $id;
        $out = $this->hemis_pull('/api/EmployeePositions/' . $id, 'pull_employee_positions', 'EmployeePositions', 'employeepositions_get/' . $id);
        if (!empty($out['ok']) && is_array($out['data'])) {
            $list = $this->_hemis_normalize_employee_position_list_for_cache($out['data']);
            if (!empty($list[0]) && !empty($list[0]['id'])) {
                $out['data'] = $list[0];
            } else {
                $one = $this->_hemis_normalize_employee_position_row_for_cache($out['data']);
                $out['data'] = !empty($one['id']) ? $one : array();
            }
        }
        return $out;
    }

    /**
     * @param string $context Short label for logs (Library, Employee, …)
     */
    protected function _sync_module_json($http_method, $remote_path, $mock_path, array $payload, $flag_key, $context)
    {
        $out = array(
            'synced'     => false,
            'mode'       => 'disabled',
            'error'      => null,
            'http_code'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg[$flag_key])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            log_message('error', 'HEMIS ' . $context . ': ' . $out['error']);
            return $out;
        }

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/' . ltrim($mock_path, '/');
            return $this->_exec_module_json_request($http_method, $url, $payload, $out, 'local_http', $context);
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            log_message('error', 'HEMIS ' . $context . ': ' . $out['error']);
            return $out;
        }

        $path = '/' . ltrim($remote_path, '/');
        $url = rtrim($remote, '/') . $path;
        return $this->_exec_module_json_request($http_method, $url, $payload, $out, 'remote', $context);
    }

    protected function _exec_module_json_request($method, $url, array $payload, array $out, $mode_label, $context)
    {
        // Record attempt mode even on failures (useful for QA screens).
        $out['mode'] = $mode_label;

        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $method = strtoupper((string) $method);
        $ch = curl_init($url);
        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        );

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        } else {
            $opts[CURLOPT_CUSTOMREQUEST] = $method;
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($ch, $opts);

        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            log_message('error', 'HEMIS ' . $context . ' HTTP: ' . $curlErr);
            return $out;
        }

        $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
        $body = is_string($raw) ? substr($raw, $hsz) : '';

        if ($code >= 200 && $code < 300) {
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt($context, $method, $url, $headers, $payload, $code, $respHeaders, $body);
            }
            return $out;
        }

        if ($code === 401 && $mode_label === 'remote' && empty($out['_hemis_reauth_tried'])) {
            $out['_hemis_reauth_tried'] = true;
            if ($this->_refresh_remote_token_via_auto_login()) {
                return $this->_exec_module_json_request($method, $url, $payload, $out, $mode_label, $context);
            }
        }

        // Graceful degradation for known DEV blockers (Library/Employee/Section) on 404/500.
        if ($mode_label === 'remote' && $this->_is_dev_blocker_module($context) && in_array((int) $code, array(404, 500), true)) {
            $this->_log_ugc_attempt($context, $method, $url, $headers, $payload, $code, $respHeaders, $body);
            return $this->_ugc_dev_blocker_out($out, $context, $method, $url, $code, $body);
        }

        $out['error'] = 'HEMIS ' . $context . ' failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        log_message('error', $out['error']);
        $out['http_code'] = $code;
        if ($mode_label === 'remote') {
            $this->_log_ugc_attempt($context, $method, $url, $headers, $payload, $code, $respHeaders, $body);
        }
        return $out;
    }

    /**
     * PATCH with multipart/form-data (PDF V1.3 employee update).
     */
    protected function _sync_module_multipart_patch($remote_path, $mock_path, array $payload, $flag_key, $context)
    {
        $out = array(
            'synced'     => false,
            'mode'       => 'disabled',
            'error'      => null,
            'http_code'  => null,
        );

        if (empty($this->cfg['sync_enabled']) || empty($this->cfg[$flag_key])) {
            return $out;
        }

        if (!empty($this->cfg['mock_mode'])) {
            $out['synced'] = true;
            $out['mode'] = 'mock';
            return $out;
        }

        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required for HEMIS HTTP sync.';
            log_message('error', 'HEMIS ' . $context . ': ' . $out['error']);
            return $out;
        }

        $postfields = $this->_flatten_payload_for_multipart($payload);

        $local = isset($this->cfg['local_mock_base_url']) ? trim($this->cfg['local_mock_base_url']) : '';
        if ($local !== '') {
            $url = rtrim($local, '/') . '/' . ltrim($mock_path, '/');
            return $this->_exec_multipart_patch($url, $postfields, $out, 'local_http', $context);
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim($this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'HEMIS remote_base_url is empty.';
            log_message('error', 'HEMIS ' . $context . ': ' . $out['error']);
            return $out;
        }

        $url = rtrim($remote, '/') . '/' . ltrim($remote_path, '/');
        return $this->_exec_multipart_patch($url, $postfields, $out, 'remote', $context);
    }

    protected function _flatten_payload_for_multipart(array $payload)
    {
        $m = array();
        foreach ($payload as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $m[$k] = json_encode($v);
            } elseif (is_bool($v)) {
                // HEMIS endpoints that accept multipart still validate booleans; use true/false strings.
                $m[$k] = $v ? 'true' : 'false';
            } elseif ($v === null) {
                $m[$k] = '';
            } else {
                $m[$k] = (string) $v;
            }
        }
        return $m;
    }

    protected function _exec_multipart_patch($url, array $postfields, array $out, $mode_label, $context)
    {
        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($curlErr) {
            $out['error'] = $curlErr;
            log_message('error', 'HEMIS ' . $context . ' HTTP: ' . $curlErr);
            return $out;
        }

        $respHeaders = is_string($raw) ? substr($raw, 0, $hsz) : '';
        $body = is_string($raw) ? substr($raw, $hsz) : '';

        if ($code >= 200 && $code < 300) {
            $out['synced'] = true;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            if ($mode_label === 'remote') {
                $this->_log_ugc_attempt($context, 'PATCH', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
            }
            return $out;
        }

        // Graceful degradation for known DEV blockers (Employee PATCH) on 404/500.
        if ($mode_label === 'remote' && $this->_is_dev_blocker_module($context) && in_array((int) $code, array(404, 500), true)) {
            $this->_log_ugc_attempt($context, 'PATCH', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
            return $this->_ugc_dev_blocker_out($out, $context, 'PATCH', $url, $code, $body);
        }

        $out['error'] = 'HEMIS ' . $context . ' PATCH failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        log_message('error', $out['error']);
        $out['http_code'] = $code;
        if ($mode_label === 'remote') {
            $this->_log_ugc_attempt($context, 'PATCH', $url, $headers, array('multipart_fields' => array_keys($postfields)), $code, $respHeaders, $body);
        }
        return $out;
    }

    protected function _exec_module_get_request($url, array $out, $mode_label, $context)
    {
        // Record attempt mode even on failures (useful for refresh_metadata).
        $out['mode'] = $mode_label;

        $token = isset($this->cfg['remote_bearer_token']) ? trim($this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));

        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($context === 'EmployeePositions') {
            // TEMPORARY: remove before production
            log_message('info', 'HEMIS GET EmployeePositions request to: ' . $url);
            log_message('info', 'HEMIS GET EmployeePositions response code: ' . $code);
            log_message('info', 'HEMIS GET EmployeePositions response body: ' . substr((string) $body, 0, 500));
        }

        if ($curlErr) {
            $out['error'] = $curlErr;
            log_message('error', 'HEMIS ' . $context . ' GET: ' . $curlErr);
            return $out;
        }

        if ($code >= 200 && $code < 300) {
            $json = json_decode($body, true);
            $out['ok'] = true;
            $out['data'] = is_array($json) ? $json : null;
            $out['mode'] = $mode_label;
            $out['http_code'] = $code;
            return $out;
        }

        if ($code === 401 && $mode_label === 'remote' && empty($out['_hemis_reauth_tried'])) {
            $out['_hemis_reauth_tried'] = true;
            if ($this->_refresh_remote_token_via_auto_login()) {
                return $this->_exec_module_get_request($url, $out, $mode_label, $context);
            }
        }

        $out['error'] = 'HEMIS ' . $context . ' GET failed (HTTP ' . $code . '): ' . substr((string) $body, 0, 500);
        log_message('error', $out['error']);
        $out['http_code'] = $code;
        return $out;
    }

    /**
     * After admin web save: rebuild HEMIS multipart scalars from DB and reuse create sync (same as REST).
     *
     * @param int $student_id Local students.id
     * @return array { synced, hemis_student_id, mode, error }
     */
    public function sync_student_after_database_snapshot($student_id)
    {
        $this->CI->load->helper('hemis_sync_post_from_db');
        $row = $this->CI->student_model->get_student_by_id((int) $student_id);
        if (!$row) {
            return array(
                'synced'           => false,
                'hemis_student_id' => null,
                'mode'             => 'skipped',
                'error'            => 'Student not found',
            );
        }
        $map = hemis_student_db_row_to_create_post($row);
        return $this->sync_student_after_local_create((int) $student_id, $map);
    }

    /**
     * After admin web update: rebuild multipart from DB and PATCH to UGC when hemis_student_id is set.
     * If Create never succeeded (no hemis_student_id), POST Create instead — same behaviour as manual sync.
     *
     * @param int $student_id
     * @return array { synced, mode, error, hemis_student_id? }
     */
    public function sync_student_update_from_database_snapshot($student_id)
    {
        $this->CI->load->helper('hemis_sync_post_from_db');
        $row = $this->CI->student_model->get_student_by_id((int) $student_id);
        if (!$row) {
            return array('synced' => false, 'mode' => 'skipped', 'error' => 'Student not found');
        }
        $map = hemis_student_db_row_to_create_post($row);
        $hid = isset($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : 0;
        if ($hid < 1) {
            if (empty($this->cfg['sync_enabled'])) {
                return array(
                    'synced'           => false,
                    'hemis_student_id' => null,
                    'mode'             => 'disabled',
                    'error'            => null,
                );
            }

            return $this->sync_student_after_local_create((int) $student_id, $map);
        }

        return $this->sync_student_after_local_update((int) $student_id, $map);
    }

    /**
     * GET /api/Student/{hemisId} for PATCH alignment (Bearer).
     *
     * @param int $hemis_id UGC student id
     * @return array|null Decoded student object or null on failure
     */
    protected function _hemis_fetch_remote_student_by_id($hemis_id)
    {
        $hemis_id = (int) $hemis_id;
        if ($hemis_id < 1 || !function_exists('curl_init')) {
            return null;
        }

        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            return null;
        }

        $url = rtrim($remote, '/') . '/api/Student/' . $hemis_id;
        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr || $code < 200 || $code >= 300) {
            log_message(
                'error',
                'HEMIS GET Student/' . $hemis_id . ' failed (HTTP ' . $code . '): '
                . ($curlErr !== '' ? $curlErr : substr((string) $body, 0, 200))
            );
            return null;
        }

        $decoded = json_decode((string) $body, true);
        if (!is_array($decoded)) {
            return null;
        }

        if (isset($decoded['firstName']) || isset($decoded['rollNo']) || isset($decoded['id'])) {
            return $decoded;
        }

        if (isset($decoded['data']) && is_array($decoded['data']) && !isset($decoded['data'][0])) {
            return $decoded['data'];
        }

        $rows = $this->_hemis_unwrap_get_response_to_rows($decoded);
        return !empty($rows[0]) ? $rows[0] : null;
    }

    /**
     * TEMPORARY: remove before production — diagnostic GET (Bearer); does not change sync_* behaviour.
     *
     * @param string $relative_path e.g. /api/Employee/GetAllEmployeees
     * @return array http_code, raw_body, error, count, sample (first row), decoded_root
     */
    public function diagnostic_http_get_list_sample($relative_path)
    {
        $relative_path = '/' . ltrim((string) $relative_path, '/');
        $ret = array(
            'http_code'    => 0,
            'raw_body'     => '',
            'error'        => null,
            'count'        => 0,
            'sample'       => null,
            'decoded_root' => null,
        );
        if (!function_exists('curl_init')) {
            $ret['error'] = 'PHP curl extension is required.';
            return $ret;
        }
        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $ret['error'] = 'remote_base_url is empty.';
            return $ret;
        }
        $url = rtrim($remote, '/') . $relative_path;
        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $body = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $ret['http_code'] = $code;
        $ret['raw_body'] = is_string($body) ? $body : '';
        if ($curlErr) {
            $ret['error'] = $curlErr;
            return $ret;
        }
        $decoded = json_decode($ret['raw_body'], true);
        $ret['decoded_root'] = $decoded;
        $rows = is_array($decoded) ? $this->_hemis_unwrap_get_response_to_rows($decoded) : array();
        $ret['count'] = count($rows);
        $ret['sample'] = isset($rows[0]) ? $rows[0] : null;
        return $ret;
    }

    /**
     * TEMPORARY: remove before production — multipart POST /api/Employee with given payload (mirrors flatten + curl only).
     *
     * @param array $payload camelCase Employee fields
     * @return array success, http_code, raw_response, parsed_hemis_id, error
     */
    public function diagnostic_employee_multipart_post(array $payload)
    {
        $out = array(
            'success'           => false,
            'http_code'         => null,
            'raw_response'      => '',
            'parsed_hemis_id'   => null,
            'error'             => null,
        );
        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required.';
            return $out;
        }
        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'remote_base_url is empty.';
            return $out;
        }
        $url = rtrim($remote, '/') . $this->_hemis_employee_create_path();
        $postfields = $this->_flatten_payload_for_multipart($payload);
        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $out['http_code'] = $curlErr ? 0 : $code;
        if ($curlErr) {
            $out['error'] = $curlErr;
            return $out;
        }
        $respBody = is_string($raw) ? substr($raw, $hsz) : '';
        $out['raw_response'] = $respBody;
        log_message('error', 'HEMIS diagnostic Employee POST raw response: ' . substr($respBody, 0, 2000));
        if ($code >= 200 && $code < 300) {
            $out['success'] = true;
            $parsedId = $this->parse_hemis_employee_id_from_create_response($respBody);
            if ($parsedId !== null && $parsedId > 0) {
                $out['parsed_hemis_id'] = $parsedId;
            }
        } else {
            $out['error'] = 'HTTP ' . $code . ': ' . substr($respBody, 0, 500);
        }
        return $out;
    }

    /**
     * TEMPORARY: remove before production — multipart PATCH /api/Employee/{id}.
     *
     * @param int   $hemis_id UGC central employee id
     * @param array $payload  camelCase fields including id
     * @return array success, http_code, raw_response, error, patch_url
     */
    public function diagnostic_employee_multipart_patch($hemis_id, array $payload)
    {
        $hemis_id = (int) $hemis_id;
        $out = array(
            'success'      => false,
            'http_code'    => null,
            'raw_response' => '',
            'error'        => null,
            'patch_url'    => '/api/Employee/' . $hemis_id,
        );
        if ($hemis_id < 1) {
            $out['error'] = 'Invalid hemis_id';
            return $out;
        }
        if (!function_exists('curl_init')) {
            $out['error'] = 'PHP curl extension is required.';
            return $out;
        }
        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        if ($remote === '') {
            $out['error'] = 'remote_base_url is empty.';
            return $out;
        }
        $url = rtrim($remote, '/') . '/api/Employee/' . $hemis_id;
        $payload['id'] = $hemis_id;
        $postfields = $this->_flatten_payload_for_multipart($payload);
        $token = isset($this->cfg['remote_bearer_token']) ? trim((string) $this->cfg['remote_bearer_token']) : '';
        $headers = array('Accept: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_POSTFIELDS     => $postfields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => (int) (isset($this->cfg['remote_timeout']) ? $this->cfg['remote_timeout'] : 30),
            CURLOPT_HTTPHEADER     => $headers,
        ));
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsz = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        $out['http_code'] = $curlErr ? 0 : $code;
        if ($curlErr) {
            $out['error'] = $curlErr;
            return $out;
        }
        $respBody = is_string($raw) ? substr($raw, $hsz) : '';
        $out['raw_response'] = $respBody;
        log_message('error', 'HEMIS diagnostic Employee PATCH raw response: ' . substr($respBody, 0, 2000));
        if ($code >= 200 && $code < 300) {
            $out['success'] = true;
        } else {
            $out['error'] = 'HTTP ' . $code . ': ' . substr($respBody, 0, 500);
        }
        return $out;
    }

    /**
     * Build wire-ready multipart preview (no HTTP) for POST or PATCH diagnostics.
     *
     * @param int        $student_id
     * @param array      $multipart_fields From hemis_student_db_row_to_create_post()
     * @param int|null   $patch_hemis_id   When set, align payload with GET /api/Student/{id}
     * @return array scalar key => string|int preview (files shown as [file])
     */
    protected function _diagnostic_student_wire_preview($student_id, array $multipart_fields, $patch_hemis_id = null)
    {
        $postfields = $multipart_fields;
        if (isset($postfields['gender']) && is_string($postfields['gender'])) {
            $postfields['gender'] = strtolower(trim($postfields['gender']));
        }
        $this->_merge_student_db_files_into_postfields((int) $student_id, $postfields);
        $this->_normalize_student_dates($postfields);
        $dummyOut = array();
        $this->_student_hemis_multipart_sanitize_and_preflight($postfields, (int) $student_id, $dummyOut);
        if (isset($postfields['pWardNo'])) {
            $postfields['pWardNo'] = (int) $postfields['pWardNo'];
        }
        $this->_student_remote_apply_ugc_address_pascal_form_keys($postfields);
        $this->_student_remote_ensure_guardian_phone_and_ward($postfields, (int) $student_id);
        $this->_student_remote_strip_duplicate_pascal_student_keys($postfields);
        $this->CI->load->helper('hemis_sync_post_from_db');
        $patch_hemis_id = $patch_hemis_id !== null ? (int) $patch_hemis_id : 0;
        hemis_student_prepare_ugc_multipart_wire($postfields);
        if ($patch_hemis_id > 0) {
            $postfields['id'] = $patch_hemis_id;
        }
        return $this->_diagnostic_multipart_scalar_preview($postfields);
    }

    /**
     * @param array $postfields
     * @return array
     */
    protected function _diagnostic_multipart_scalar_preview(array $postfields)
    {
        $preview = array();
        foreach ($postfields as $k => $v) {
            if ($v instanceof CURLFile) {
                $preview[$k] = '[file:' . $v->getPostFilename() . ']';
                continue;
            }
            if (is_bool($v)) {
                $preview[$k] = $v ? 'true' : 'false';
                continue;
            }
            if (is_array($v) || is_object($v)) {
                continue;
            }
            if ($v === null) {
                continue;
            }
            $preview[$k] = $v;
        }
        ksort($preview);
        return $preview;
    }

    /**
     * POST precheck missing fields (mirrors _post_multipart_remote gate; no HTTP).
     *
     * @param array $postfields wire-ready preview map
     * @return array list of missing field names
     */
    protected function _diagnostic_student_post_precheck_missing(array $postfields)
    {
        $missing = array();
        foreach (array('campusId', 'programId', 'batchId') as $k) {
            if (!isset($postfields[$k]) || (int) $postfields[$k] < 1) {
                $missing[] = $k;
            }
        }
        $fyVal = isset($postfields['fiscalYearId']) ? (int) $postfields['fiscalYearId'] : 0;
        if ($fyVal < 1) {
            $missing[] = 'fiscalYearId';
        }
        $scalarPart = function ($v) {
            if ($v === null) {
                return '';
            }
            return trim((string) $v);
        };
        $progTypeLower = strtolower($scalarPart(isset($postfields['programType']) ? $postfields['programType'] : ''));
        $yearVal = $scalarPart(isset($postfields['year']) ? $postfields['year'] : '');
        $semVal = $scalarPart(isset($postfields['semester']) ? $postfields['semester'] : '');
        if (strpos($progTypeLower, 'semester') !== false) {
            if ($semVal === '') {
                $missing[] = 'semester';
            }
        } elseif ($yearVal === '') {
            $missing[] = 'year';
        }
        $pc = isset($postfields['pCombinedCode']) ? trim((string) $postfields['pCombinedCode']) : '';
        if ($pc === '') {
            $missing[] = 'pCombinedCode';
        }
        $sameRaw = isset($postfields['isSameAsPermanent']) ? $postfields['isSameAsPermanent'] : null;
        $same = ($sameRaw === true || $sameRaw === 1 || $sameRaw === '1'
            || strtolower(trim((string) $sameRaw)) === 'true');
        if (!$same) {
            $tc = isset($postfields['tCombinedCode']) ? trim((string) $postfields['tCombinedCode']) : '';
            if ($tc === '') {
                $missing[] = 'tCombinedCode';
            }
        }
        $ay = isset($postfields['admissionYearId']) ? (int) $postfields['admissionYearId'] : 0;
        if ($ay < 1) {
            $missing[] = 'admissionYearId';
        }
        return $missing;
    }

    /**
     * Compare POST vs PATCH wire payloads for one local student (diagnostics only).
     *
     * @param int $student_id students.id
     * @return array
     */
    public function diagnostic_student_post_vs_patch($student_id)
    {
        $student_id = (int) $student_id;
        $out = array(
            'student_id'       => $student_id,
            'student_found'    => false,
            'hemis_student_id' => null,
            'student_summary'  => null,
            'remote_get'       => null,
            'remote_get_error' => null,
            'post_url'         => null,
            'patch_url'        => null,
            'post_preview'     => array(),
            'patch_preview'    => array(),
            'post_precheck_missing' => array(),
            'post_wire_warnings' => array(),
            'field_diff'       => array(),
            'local_vs_remote'  => array(),
            'latest_logs'      => array(),
            'sync_config'      => array(),
        );

        if ($student_id < 1) {
            $out['error'] = 'Invalid student id';
            return $out;
        }

        $this->CI->load->helper('hemis_sync_post_from_db');
        $row = $this->CI->student_model->get_student_by_id($student_id);
        if (!is_array($row) || empty($row)) {
            $out['error'] = 'Student not found';
            return $out;
        }

        $out['student_found'] = true;
        $hid = isset($row['hemis_student_id']) ? (int) $row['hemis_student_id'] : 0;
        $out['hemis_student_id'] = $hid > 0 ? $hid : null;
        $out['student_summary'] = array(
            'admission_no' => isset($row['admission_no']) ? $row['admission_no'] : '',
            'name'         => trim(
                (isset($row['firstname']) ? $row['firstname'] : '') . ' '
                . (isset($row['lastname']) ? $row['lastname'] : '')
            ),
            'roll_no'           => isset($row['roll_no']) ? $row['roll_no'] : '',
            'ugc_batch_id'      => isset($row['ugc_batch_id']) ? $row['ugc_batch_id'] : '',
            'admissionYearId'   => isset($row['admissionYearId']) ? $row['admissionYearId'] : '',
            'hemis_sync_error'  => isset($row['hemis_sync_error']) ? $row['hemis_sync_error'] : '',
            'hemis_sync_at'     => isset($row['hemis_sync_at']) ? $row['hemis_sync_at'] : '',
        );

        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        $out['post_url'] = $remote !== '' ? rtrim($remote, '/') . '/api/Student/Create' : null;
        $out['patch_url'] = ($remote !== '' && $hid > 0) ? rtrim($remote, '/') . '/api/Student/' . $hid : null;

        $out['sync_config'] = array(
            'sync_enabled'        => !empty($this->cfg['sync_enabled']),
            'sync_student_update' => !empty($this->cfg['sync_student_update']),
            'mock_mode'           => !empty($this->cfg['mock_mode']),
        );

        $base = hemis_student_db_row_to_create_post($row);
        $out['post_preview'] = $this->_diagnostic_student_wire_preview($student_id, $base, null);
        $out['post_precheck_missing'] = $this->_diagnostic_student_post_precheck_missing($out['post_preview']);
        $out['post_wire_warnings'] = array();

        $remoteRow = null;
        if ($hid > 0) {
            $remoteRow = $this->_hemis_fetch_remote_student_by_id($hid);
            if (is_array($remoteRow) && !empty($remoteRow)) {
                $out['remote_get'] = $this->_diagnostic_remote_student_subset($remoteRow);
            } else {
                $out['remote_get_error'] = 'Could not GET /api/Student/' . $hid;
            }
            $out['patch_preview'] = $this->_diagnostic_student_wire_preview($student_id, $base, $hid);
        }

        $allKeys = array_unique(array_merge(
            array_keys($out['post_preview']),
            array_keys($out['patch_preview'])
        ));
        sort($allKeys);
        foreach ($allKeys as $key) {
            $postVal = array_key_exists($key, $out['post_preview']) ? $out['post_preview'][$key] : null;
            $patchVal = array_key_exists($key, $out['patch_preview']) ? $out['patch_preview'][$key] : null;
            if ((string) $postVal !== (string) $patchVal) {
                $out['field_diff'][$key] = array(
                    'post'  => $postVal,
                    'patch' => $patchVal,
                );
            }
        }

        if (is_array($remoteRow) && !empty($remoteRow)) {
            $remotePreview = $this->_diagnostic_remote_student_subset($remoteRow);
            foreach ($remotePreview as $rk => $rv) {
                $localRoll = isset($row['roll_no']) ? trim((string) $row['roll_no']) : '';
                if ($rk === 'rollNo' && $localRoll !== '' && (string) $rv !== $localRoll) {
                    $out['local_vs_remote'][$rk] = array('local_db' => $localRoll, 'hemis' => $rv);
                }
                if ($rk === 'batchId' && isset($row['ugc_batch_id']) && (string) $row['ugc_batch_id'] !== (string) $rv) {
                    $out['local_vs_remote'][$rk] = array('local_db' => $row['ugc_batch_id'], 'hemis' => $rv);
                }
                if ($rk === 'admissionYearId' && isset($row['admissionYearId']) && (string) $row['admissionYearId'] !== (string) $rv) {
                    $out['local_vs_remote'][$rk] = array('local_db' => $row['admissionYearId'], 'hemis' => $rv);
                }
            }
        }

        if ($this->CI->db->table_exists('ugc_error_logs')) {
            $q = $this->CI->db->select('id, created_at, http_method, http_code, endpoint, request_body, response_body')
                ->from('ugc_error_logs')
                ->where('module', 'Student')
                ->order_by('id', 'DESC')
                ->limit(8);
            if ($hid > 0) {
                $q->group_start();
                $q->like('endpoint', '/Student/' . $hid, 'both');
                $q->or_like('endpoint', 'Student/Create', 'both');
                $q->group_end();
            } else {
                $q->like('endpoint', 'Student/Create', 'both');
            }
            $logs = $q->get()->result_array();
            foreach ($logs as $log) {
                $body = isset($log['request_body']) ? $log['request_body'] : '';
                $decoded = json_decode((string) $body, true);
                if (is_array($decoded) && isset($decoded['multipart_preview'])) {
                    $log['multipart_preview'] = $decoded['multipart_preview'];
                }
                unset($log['request_body']);
                $out['latest_logs'][] = $log;
            }
        }

        return $out;
    }

    /**
     * @param array $remoteRow
     * @return array
     */
    protected function _diagnostic_remote_student_subset(array $remoteRow)
    {
        $keys = array(
            'id', 'firstName', 'lastName', 'rollNo', 'admissionYearId', 'batchId', 'fiscalYearId',
            'year', 'semester', 'programId', 'campusId', 'isVerified', 'isRegNoManual', 'isRollNoManual',
            'pWardNo', 'tWardNo', 'isSameAsPermanent', 'doBBS', 'doBAD', 'gender', 'programType',
        );
        $sub = array();
        foreach ($keys as $k) {
            if (array_key_exists($k, $remoteRow)) {
                $sub[$k] = $remoteRow[$k];
            }
        }
        return $sub;
    }

    /**
     * Compare POST (Create) vs PATCH (Update) wire payloads for one local staff/employee.
     *
     * @param int $staff_id staff.id
     * @return array
     */
    public function diagnostic_employee_post_vs_patch($staff_id)
    {
        $staff_id = (int) $staff_id;
        $out = array(
            'staff_id'              => $staff_id,
            'staff_found'           => false,
            'hemis_employee_id'     => null,
            'staff_summary'         => null,
            'post_url'              => null,
            'patch_url'             => null,
            'post_preview'          => array(),
            'patch_preview'         => array(),
            'post_precheck_missing' => array(),
            'field_diff'            => array(),
            'latest_logs'           => array(),
            'sync_config'           => array(),
        );

        if ($staff_id < 1) {
            $out['error'] = 'Invalid staff id';
            return $out;
        }

        $this->CI->load->model('staff_model');
        $row = $this->CI->staff_model->get($staff_id);
        if (!is_array($row) || empty($row)) {
            $out['error'] = 'Employee not found';
            return $out;
        }

        $out['staff_found'] = true;
        $hid = isset($row['hemis_employee_id']) ? (int) $row['hemis_employee_id'] : 0;
        $out['hemis_employee_id'] = $hid > 0 ? $hid : null;
        $out['staff_summary'] = array(
            'employee_id'      => isset($row['employee_id']) ? $row['employee_id'] : '',
            'name'             => trim(
                (isset($row['name']) ? $row['name'] : '') . ' '
                . (isset($row['surname']) ? $row['surname'] : '')
            ),
            'email'            => isset($row['email']) ? $row['email'] : '',
            'hemis_sync_error' => isset($row['hemis_sync_error']) ? $row['hemis_sync_error'] : '',
            'hemis_sync_at'    => isset($row['hemis_sync_at']) ? $row['hemis_sync_at'] : '',
        );

        $remote = isset($this->cfg['remote_base_url']) ? trim((string) $this->cfg['remote_base_url']) : '';
        $out['post_url'] = $remote !== '' ? rtrim($remote, '/') . $this->_hemis_employee_create_path() : null;
        $out['patch_url'] = ($remote !== '' && $hid > 0) ? rtrim($remote, '/') . '/api/Employee/' . $hid : null;

        $out['sync_config'] = array(
            'sync_enabled'  => !empty($this->cfg['sync_enabled']),
            'sync_employee' => !empty($this->cfg['sync_employee']),
            'mock_mode'     => !empty($this->cfg['mock_mode']),
        );

        $payload = $this->map_staff_row_to_hemis_employee_payload($row);
        $out['post_precheck_missing'] = $this->_employee_required_missing($payload);

        $postFlat = $this->_flatten_payload_for_multipart($payload);
        $out['post_preview'] = $this->_diagnostic_multipart_scalar_preview($postFlat);

        // PATCH mirrors sync_employee_after_local_update(): same payload + `id` when a HEMIS id exists.
        $patchPayload = $payload;
        if ($hid > 0) {
            $patchPayload['id'] = $hid;
        }
        $patchFlat = $this->_flatten_payload_for_multipart($patchPayload);
        $out['patch_preview'] = $this->_diagnostic_multipart_scalar_preview($patchFlat);

        $allKeys = array_unique(array_merge(
            array_keys($out['post_preview']),
            array_keys($out['patch_preview'])
        ));
        sort($allKeys);
        foreach ($allKeys as $key) {
            $postVal = array_key_exists($key, $out['post_preview']) ? $out['post_preview'][$key] : null;
            $patchVal = array_key_exists($key, $out['patch_preview']) ? $out['patch_preview'][$key] : null;
            if ((string) $postVal !== (string) $patchVal) {
                $out['field_diff'][$key] = array('post' => $postVal, 'patch' => $patchVal);
            }
        }

        if ($this->CI->db->table_exists('ugc_error_logs')) {
            $q = $this->CI->db->select('id, created_at, http_method, http_code, endpoint, request_body, response_body')
                ->from('ugc_error_logs')
                ->where('module', 'Employee')
                ->order_by('id', 'DESC')
                ->limit(8);
            if ($hid > 0) {
                $q->group_start();
                $q->like('endpoint', '/Employee/' . $hid, 'both');
                $q->or_like('endpoint', '/api/Employee', 'both');
                $q->group_end();
            }
            $logs = $q->get()->result_array();
            foreach ($logs as $log) {
                $body = isset($log['request_body']) ? $log['request_body'] : '';
                $decoded = json_decode((string) $body, true);
                if (is_array($decoded) && isset($decoded['multipart_preview'])) {
                    $log['multipart_preview'] = $decoded['multipart_preview'];
                }
                unset($log['request_body']);
                $out['latest_logs'][] = $log;
            }
        }

        return $out;
    }
}
