<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('debug_log')) {
    function debug_log($message, $data = null, $level = 'debug') {
        $CI = &get_instance();
        $log_message = is_string($message) ? $message : print_r($message, true);
        
        if ($data !== null) {
            $log_message .= " Data: " . print_r($data, true);
        }
        
        $log_message .= " SQL: " . $CI->db->last_query();
        
        log_message($level, $log_message);
    }
}