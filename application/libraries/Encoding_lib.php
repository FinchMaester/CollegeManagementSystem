<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
include_once(APPPATH . 'third_party/Encoding.php');

use \ForceUTF8\Encoding;

class Encoding_lib {

    public function toUTF8($string) {
        if (!is_string($string)) {
            return $string;
        }
        if ($string === '') {
            return '';
        }
        // Already valid UTF-8 (e.g. Nepali Devanagari from UTF-8 CSV) — do not run ForceUTF8 (can corrupt).
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        return Encoding::toUTF8($string);
    }

}
