<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_auth {
    
    protected $CI;
    protected $token_secret;
    
    public function __construct() {
        $this->CI =& get_instance();
        
        // Load JWT library
        $this->CI->load->library('JWT');
        
        // Set secret key from config
        $this->CI->config->load('jwt', TRUE);
        $this->token_secret = $this->CI->config->item('jwt_secret', 'jwt') ?? 'eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6IjQ4IiwiSW5zdGl0dXRpb25JZCI6IjEyIiwicm9sZSI6IkNvbGxlZ2VBZG1pbiIsIm5iZiI6MTc0NjU5NjcyOCwiZXhwIjoxNzQ2NjgzMTI4LCJpYXQiOjE3NDY1OTY3Mjh9.gpcFtPXVzvtC-CnmqU3-U20Fzetmj4f9ZLEaninx16u2wIcpP8huUw32PPBdppDMnbEPpw9sdBcE_uipJ7ZeoA';
    }
    
    /**
     * Verify JWT token from request headers
     *
     * @return array Result with status and data
     */
    public function verify_token() {
        // Get token from Authorization header
        $headers = $this->CI->input->request_headers();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        } else if (isset($headers['authorization'])) {
            $token = str_replace('Bearer ', '', $headers['authorization']);
        }
        
        if (!$token) {
            return [
                'status' => false,
                'message' => 'Authorization token not found',
                'data' => null
            ];
        }
        
        try {
            // Decode token
            $decoded = $this->CI->jwt->decode($token, $this->token_secret, ['HS512']);
            
            return [
                'status' => true,
                'message' => 'Token is valid',
                'data' => $decoded
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Create a new JWT token
     *
     * @param array $data Data to include in token
     * @param int $expiration Expiration time in seconds
     * @return string JWT token
     */
    public function create_token($data, $expiration = 86400) {
        $time = time();
        
        $payload = [
            'iat' => $time, // Issued at time
            'exp' => $time + $expiration, // Expiration time
            'data' => $data
        ];
        
        return $this->CI->jwt->encode($payload, $this->token_secret, 'HS512');
    }
    
    /**
     * Check if user has required role
     *
     * @param string $role Required role
     * @param object $user_data User data from token
     * @return bool
     */
    public function has_role($role, $user_data) {
        if (!isset($user_data->role)) {
            return false;
        }
        
        return $user_data->role === $role;
    }
}