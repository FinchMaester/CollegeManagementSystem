<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Normalize student row (array or object) for HEMIS-shaped API responses.
 */

if (!function_exists('hemis_student_row_to_array')) {
    /**
     * @param array|object $row
     * @return array
     */
    function hemis_student_row_to_array($row)
    {
        if (is_array($row)) {
            return $row;
        }
        if (is_object($row)) {
            return get_object_vars($row);
        }
        return array();
    }
}

if (!function_exists('hemis_student_field')) {
    /**
     * Read column with case-insensitive fallback (MySQL driver quirks).
     *
     * @param array $row
     * @param string $name Preferred key (e.g. admissionYearId)
     * @return mixed|null
     */
    function hemis_student_field(array $row, $name)
    {
        if (array_key_exists($name, $row)) {
            return $row[$name];
        }
        $lower = strtolower($name);
        foreach ($row as $k => $v) {
            if (strtolower((string) $k) === $lower) {
                return $v;
            }
        }
        return null;
    }
}

if (!function_exists('hemis_student_fix_common_local_level_typos')) {
    /**
     * Fix common cascade / cache typos so labels match UGC expectations (e.g. Metropolitian → Metropolitan).
     *
     * @param mixed $localLevel
     * @return string
     */
    function hemis_student_fix_common_local_level_typos($localLevel)
    {
        $s = trim((string) $localLevel);
        if ($s === '') {
            return '';
        }

        return str_ireplace('Metropolitian', 'Metropolitan', $s);
    }
}

if (!function_exists('hemis_student_normalize_ugc_province_wire_label')) {
    /**
     * UGC Student GET payloads use names like "Bagmati Province"; Address/GetAll cache rows may omit the suffix.
     *
     * @param mixed $province
     * @return string
     */
    function hemis_student_normalize_ugc_province_wire_label($province)
    {
        $p = trim((string) $province);
        if ($p === '') {
            return '';
        }
        if (preg_match('/province\s*$/iu', $p)) {
            return $p;
        }

        return $p . ' Province';
    }
}

if (!function_exists('hemis_student_normalize_ugc_address_wire_triple')) {
    /**
     * Apply province + local_level wire normalizations (in place).
     *
     * @param mixed $province
     * @param mixed $district
     * @param mixed $localLevel
     */
    function hemis_student_normalize_ugc_address_wire_triple(&$province, &$district, &$localLevel)
    {
        $province = hemis_student_normalize_ugc_province_wire_label($province);
        $district = trim((string) $district);
        $localLevel = hemis_student_fix_common_local_level_typos($localLevel);
    }
}

if (!function_exists('hemis_resolve_ethnicity_value')) {
    /**
     * HEMIS ethnicity: prefer ethnicity column, fallback to legacy cast/category.
     *
     * @param array $row
     * @return string|null
     */
    function hemis_resolve_ethnicity_value(array $row)
    {
        $e = hemis_student_field($row, 'ethnicity');
        if ($e !== null && $e !== '') {
            return (string) $e;
        }
        $c = hemis_student_field($row, 'cast');
        if ($c !== null && $c !== '') {
            return (string) $c;
        }
        return null;
    }
}

if (!function_exists('hemis_resolve_admission_year_id')) {
    /**
     * Resolve UGC admissionYearId: stored column first, then hemis `admission_year_id_map` (BS year → id).
     *
     * @param array $row
     * @return int|null
     */
    function hemis_resolve_admission_year_id(array $row)
    {
        $aid = hemis_student_field($row, 'admissionYearId');
        if ($aid !== null && $aid !== '' && (int) $aid > 0) {
            return (int) $aid;
        }
        $bs = hemis_student_field($row, 'admission_year');
        if ($bs === null || $bs === '') {
            return null;
        }
        $key = trim((string) $bs);
        if ($key === '') {
            return null;
        }
        $ci =& get_instance();
        if (!isset($ci->hemis_integration_model) || !is_object($ci->hemis_integration_model)) {
            $ci->load->model('hemis_integration_model');
        }
        $cfg = $ci->hemis_integration_model->get_effective_config();
        $map = (is_array($cfg) && isset($cfg['admission_year_id_map']) && is_array($cfg['admission_year_id_map']))
            ? $cfg['admission_year_id_map']
            : array();
        if (isset($map[$key]) && (int) $map[$key] > 0) {
            return (int) $map[$key];
        }

        return null;
    }
}

if (!function_exists('hemis_resolve_nationality_value')) {
    /**
     * @param array $row
     * @return string|null
     */
    function hemis_resolve_nationality_value(array $row)
    {
        $n = hemis_student_field($row, 'nationality');
        if ($n !== null && $n !== '') {
            return (string) $n;
        }
        return null;
    }
}
