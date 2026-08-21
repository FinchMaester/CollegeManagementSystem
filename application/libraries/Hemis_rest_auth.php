<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bearer validation for HEMIS-shaped campus APIs (matches Library::_authenticate).
 */
class Hemis_rest_auth
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Call from REST controller __construct after parent::__construct().
     * Exits with 401 JSON when invalid (via REST_Controller::response).
     *
     * @param object $rest REST_Controller instance
     * @return void
     */
    public function guard_controller($rest)
    {
        $method = strtolower((string) $rest->input->server('REQUEST_METHOD'));
        if ($method === 'options') {
            return;
        }

        $auth_header = $rest->input->get_request_header('Authorization');
        if (empty($auth_header)) {
            $rest->response(array(
                'status'  => false,
                'message' => 'Authorization header not found',
            ), 401);
            return;
        }

        if (!preg_match('/Bearer\s+(\S+)/i', $auth_header, $m)) {
            $rest->response(array(
                'status'  => false,
                'message' => 'Invalid authorization format. Use Bearer token',
            ), 401);
            return;
        }
        $token = $m[1];

        $this->CI->load->model('auth_model');
        if ($this->CI->auth_model->validate_token($token)) {
            return;
        }

        $this->CI->load->library('authorization_token');
        $jwt = $this->CI->authorization_token->validateBearerTokenString($token);
        if (!empty($jwt['status'])) {
            return;
        }

        $rest->response(array(
            'status'  => false,
            'message' => 'Invalid or expired token',
        ), 401);
    }

    /**
     * Same rules as guard_controller but returns an array (no exit) for controllers
     * that need JSON shaped like Authorization_token::validateToken().
     * Order: opaque tokens table first, then JWT (matches infrastructure APIs).
     *
     * @return array{status:bool,message:string,data:mixed}
     */
    public function validate_campus_bearer_array()
    {
        $auth_header = $this->CI->input->get_request_header('Authorization');
        if (empty($auth_header) || !preg_match('/Bearer\s+(\S+)/i', $auth_header, $m)) {
            return array(
                'status'  => false,
                'message' => 'Token not found in request',
                'data'    => null,
            );
        }
        $token = $m[1];

        $this->CI->load->model('auth_model');
        if ($this->CI->auth_model->validate_token($token)) {
            return array(
                'status'  => true,
                'message' => 'Token is valid',
                'data'    => null,
            );
        }

        $this->CI->load->library('authorization_token');
        $jwt = $this->CI->authorization_token->validateBearerTokenString($token);
        if (!empty($jwt['status'])) {
            return $jwt;
        }

        return array(
            'status'  => false,
            'message' => 'Invalid or expired token',
            'data'    => null,
        );
    }
}
