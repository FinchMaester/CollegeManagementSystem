<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * File Upload Helper
 */

if (!function_exists('handle_file_upload')) {
    function handle_file_upload($field_name, $config) {
        $CI =& get_instance();
        $CI->load->library('upload', $config);
        
        if (!$CI->upload->do_upload($field_name)) {
            return [
                'success' => false,
                'error' => $CI->upload->display_errors()
            ];
        } else {
            return [
                'success' => true,
                'data' => $CI->upload->data()
            ];
        }
    }
}