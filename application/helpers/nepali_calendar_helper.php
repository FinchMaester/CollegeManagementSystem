<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AD ↔ BS helpers for UGC / HEMIS reporting (Bikram Sambat).
 * Uses Customlib::convertADDateToBS when the app instance is available.
 */

if (!function_exists('convert_ad_to_bs')) {
    /**
     * Convert stored AD date string to BS for API fields (e.g. doBBS).
     * Returns Y/m/d for compatibility with HEMIS-shaped payloads; empty/invalid → null.
     *
     * @param string|null $ad_date Y-m-d or similar
     * @return string|null
     */
    function convert_ad_to_bs($ad_date)
    {
        if ($ad_date === null || $ad_date === '' || $ad_date === '0000-00-00') {
            return null;
        }
        $CI =& get_instance();
        if (isset($CI->customlib) && method_exists($CI->customlib, 'convertADDateToBS')) {
            $bs = $CI->customlib->convertADDateToBS($ad_date);
            if ($bs === null || $bs === '' || $bs === $ad_date) {
                return $bs;
            }
            return str_replace('-', '/', $bs);
        }
        try {
            $date = new DateTime((string) $ad_date);
            return $date->format('Y/m/d');
        } catch (Exception $e) {
            return (string) $ad_date;
        }
    }
}
