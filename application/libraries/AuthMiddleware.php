<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @deprecated Use Hemis_rest_auth (opaque `tokens` row or JWT) from REST API controllers.
 */
class AuthMiddleware
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->model('user_model');
    }

    /**
     * Verify the token from the Authorization header
     * 
     * @return bool
     */
    public function verifyToken()
    {
        // Get headers
        $headers = $this->getAuthorizationHeader();
        
        // Extract the token
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $token = $matches[1];
                
                // Verify token in the database or using JWT
                if ($this->validateToken($token)) {
                    return true;
                }
            }
        }

        // If no valid token is found, return error
        $this->CI->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode([
                'status' => false,
                'message' => 'Unauthorized access. Invalid or missing token.',
                'data' => []
            ]));
        
        exit;
    }

    /**
     * Get authorization header
     * 
     * @return string
     */
    private function getAuthorizationHeader()
    {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
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

    /**
     * Validate the token
     * 
     * @param string $token
     * @return bool
     */
    private function validateToken($token)
    {
        // Check the token in your database or validate JWT
        // This is a simplified example - implement your token validation logic
        
        // Example: Check in database
        $this->CI->db->where('app_key', $token);
        $this->CI->db->or_where('parent_app_key', $token);
        $query = $this->CI->db->get('students');
        
        if ($query->num_rows() > 0) {
            return true;
        }
        
        // Check if it's a staff/user token
        $this->CI->db->where('api_token', $token);
        $query = $this->CI->db->get('users');
        
        return ($query->num_rows() > 0);
    }
}