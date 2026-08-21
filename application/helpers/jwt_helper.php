<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/JWT.php';
use \Firebase\JWT\JWT;

if (!defined('JWT_KEY')) {
    define('JWT_KEY', 'Pj8d$4kL!7^qW2xZvBn*T@5mRe9yHgFcU'); // Change this to a secure random key
}

if (!defined('JWT_ALGORITHM')) {
    define('JWT_ALGORITHM', 'HS256');
}

if (!function_exists('generate_jwt')) {
    function generate_jwt($data) {
        $data['created_at'] = time();
        $data['exp'] = time() + (60 * 60 * 24); // 24 hours expiration
        
        return JWT::encode($data, JWT_KEY);
    }
}

if (!function_exists('validate_jwt')) {
    function validate_jwt($token) {
        try {
            $decoded = JWT::decode($token, JWT_KEY, array(JWT_ALGORITHM));
            return (array) $decoded;
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}

if (!function_exists('get_authorization_header')) {
    function get_authorization_header() {
        $CI =& get_instance();
        $headers = $CI->input->request_headers();
        
        if (isset($headers['Authorization'])) {
            return str_replace('Bearer ', '', $headers['Authorization']);
        }
        
        return null;
    }
}