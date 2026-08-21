<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authorization Token Library
 * 
 * This library handles JWT token validation for API authentication.
 * 
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 */
class Authorization_token {
    
    /**
     * CI instance
     * @var object
     */
    protected $CI;
    
    /**
     * Token secret key
     * @var string
     */
    protected $token_secret;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->CI =& get_instance();
        
        $this->CI->load->config('jwt', true);
        $secret = $this->CI->config->item('jwt_secret', 'jwt');
        if (!is_string($secret) || $secret === '') {
            $secret = function_exists('app_env') ? app_env('JWT_SECRET', '') : '';
        }
        $this->token_secret = $secret;
    }
    
    /**
     * Generate JWT token
     * 
     * @param array $data Payload data
     * @return string JWT token
     */
    public function generateToken($data) {
        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Create token payload as a JSON string
        $payload = json_encode($data);
        
        // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        
        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->token_secret, true);
        
        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $jwt;
    }
    
    /**
     * Validate JWT from Authorization header (HS256).
     *
     * @return array Status and data
     */
    public function validateToken() {
        return $this->validateBearerTokenString($this->getBearerToken());
    }

    /**
     * Validate a JWT string (same algorithm as validateToken).
     * Used by Hemis_rest_auth alongside opaque tokens table tokens.
     *
     * @param string|null $token Raw JWT without "Bearer " prefix
     * @return array{status:bool,message:string,data:mixed}
     */
    public function validateBearerTokenString($token) {
        if (!$token) {
            return [
                'status' => false,
                'message' => 'Token not found in request',
                'data' => null,
            ];
        }

        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return [
                'status' => false,
                'message' => 'Invalid token format',
                'data' => null,
            ];
        }

        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $this->token_secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64UrlSignature !== $signatureProvided) {
            return [
                'status' => false,
                'message' => 'Invalid token signature',
                'data' => null,
            ];
        }

        $payload_data = json_decode($payload, true);

        if (isset($payload_data['exp'])) {
            $now = time();
            if ($payload_data['exp'] < $now) {
                return [
                    'status' => false,
                    'message' => 'Token has expired',
                    'data' => null,
                ];
            }
        }

        return [
            'status' => true,
            'message' => 'Token is valid',
            'data' => $payload_data,
        ];
    }
    
    /**
     * Get bearer token from authorization header
     * 
     * @return string|null The token or null if not found
     */
    private function getBearerToken() {
        $headers = $this->CI->input->request_headers();
        
        // Check Authorization header
        if (isset($headers['Authorization'])) {
            $header = $headers['Authorization'];
        } else if (isset($headers['authorization'])) {
            $header = $headers['authorization'];
        } else {
            return null;
        }
        
        // Extract token from bearer
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}