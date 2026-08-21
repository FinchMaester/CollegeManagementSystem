<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_instance')) {
    function get_instance()
    {
        // Return the CodeIgniter super object
        return CI_Controller::get_instance();
    }
}

if (!function_exists('return_response')) {
    /**
     * Return a standardized API response
     * 
     * @param string $status The status of the response (Success/Failed)
     * @param string $message The message to return
     * @param mixed $data Optional data to return
     * @param int $code HTTP status code
     * @return array The formatted response array
     */
    function return_response($status = 'Success', $message = '', $data = NULL, $code = 200)
    {
        $CI =& get_instance();
        
        $response = [
            'status' => $status,
            'message' => $message
        ];
        
        if ($data !== NULL) {
            $response['data'] = $data;
        }
        
        $CI->output->set_status_header($code);
        $CI->output->set_content_type('application/json');
        $CI->output->set_output(json_encode($response));
        
        return $response;
    }
}

if (!function_exists('json_output')) {
    /**
     * Format and output JSON response
     * 
     * @param int $statusCode HTTP status code
     * @param array $response Response data
     * @return void
     */
    function json_output($statusCode, $response)
    {
        $CI =& get_instance();
        $CI->output->set_status_header($statusCode);
        $CI->output->set_content_type('application/json');
        $CI->output->set_output(json_encode($response));
        $CI->output->_display();
        exit;
    }
}

if (!function_exists('validate_api_token')) {
    /**
     * Validate API token
     * 
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    function validate_api_token($token)
    {
        // For demonstration, using a hardcoded token
        // In production, this should validate against tokens in your database
        return ($token === '5.5.2');
    }
}

if (!function_exists('xss_clean_array')) {
    /**
     * XSS clean an array of data
     * 
     * @param array $data Data to clean
     * @return array Cleaned data
     */
    function xss_clean_array($data)
    {
        $CI =& get_instance();
        
        if (!is_array($data)) {
            return $CI->security->xss_clean($data);
        }
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = xss_clean_array($value);
            } else {
                $data[$key] = $CI->security->xss_clean($value);
            }
        }
        
        return $data;
    }
}

if (!function_exists('get_authorization_header')) {
    /**
     * Get the authorization header
     * 
     * @return string|null Authorization header or null if not found
     */
    function get_authorization_header()
    {
        $headers = NULL;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return $headers;
    }
}

if (!function_exists('get_bearer_token')) {
    /**
     * Get the bearer token from the authorization header
     * 
     * @return string|null Bearer token or null if not found
     */
    function get_bearer_token()
    {
        $headers = get_authorization_header();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}