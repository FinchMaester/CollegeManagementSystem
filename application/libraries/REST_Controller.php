<?php
/**
 * CodeIgniter Rest Controller
 *
 * A fully RESTful server implementation for CodeIgniter using one library, one config file and one controller.
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 * @version         3.0.0
 */

namespace Restserver\Libraries;

use Exception;
use stdClass;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Rest Controller class
 *
 * @codeCoverageIgnore
 */
class REST_Controller extends \CI_Controller {

    // Note: Only the widely used HTTP status codes are included
    const HTTP_OK                    = 200;
    const HTTP_CREATED               = 201;
    const HTTP_NO_CONTENT            = 204;
    const HTTP_BAD_REQUEST           = 400;
    const HTTP_UNAUTHORIZED          = 401;
    const HTTP_FORBIDDEN             = 403;
    const HTTP_NOT_FOUND             = 404;
    const HTTP_METHOD_NOT_ALLOWED    = 405;
    const HTTP_NOT_ACCEPTABLE        = 406;
    const HTTP_INTERNAL_ERROR        = 500;
    /** @deprecated Use HTTP_INTERNAL_ERROR; alias for older controllers */
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * This defines the rest format
     *
     * @var string|null
     */
    protected $rest_format = null;

    /**
     * List of allowed HTTP methods
     *
     * @var array
     */
    protected $allowed_http_methods = ['get', 'post', 'put', 'delete', 'options', 'patch'];

    /**
     * Contains details about the request
     *Library
     * @var object
     */
    protected $request = null;

    /**
     * Contains details about the response
     *
     * @var object
     */
    protected $response = null;

    /**
     * Compatibility shims for controllers written against older
     * codeigniter-restserver versions that use these arrays directly.
     *
     * @var array
     */
    public $_get_args = [];
    public $_post_args = [];
    public $_put_args = [];
    public $_patch_args = [];
    public $_delete_args = [];

    /**
     * Constructor function
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();

        // Initialize request and response objects
        $this->request = new stdClass();
        $this->response = new stdClass();

        // Input is provided by CI_Controller; do not load as application library (breaks on some setups).

        // Set the request method
        $this->request->method = $this->input->method();

        // Check if the request method is allowed
        if (!in_array($this->request->method, array_map('strtolower', $this->allowed_http_methods))) {
            $this->response([
                'status' => false,
                'error' => 'Method Not Allowed'
            ], self::HTTP_METHOD_NOT_ALLOWED);
        }

        // Call the request method handler
        $this->_parse_request();

        // Set the output format
        $this->_detect_output_format();

        // Parse the query parameters
        $this->_parse_query();

        // Set default output format if not detected
        if ($this->rest_format === null) {
            $this->rest_format = 'json';
        }

        log_message('debug', 'REST Controller Initialized');
    }

    /**
     * Remap the CI request to the appropriate method
     *
     * @access public
     * @param  string $object_called The method being called
     * @param  array  $arguments The arguments passed to the method
     * @return mixed
     */
    public function _remap($object_called, $arguments) {
        // If the request is OPTIONS, handle it separately
        if ($this->request->method === 'options') {
            $this->_handle_options_request();
            return;
        }

        // Get the HTTP method-specific function name
        $method = $object_called . '_' . $this->request->method;
        
        // Does this method exist?
        if (method_exists($this, $method)) {
            try {
                // Call the method
                $result = call_user_func_array([$this, $method], $arguments);
                
                // If the method doesn't end up responding, set a default response
                if ($result === null && !$this->response->format) {
                    $this->response([
                        'status' => false,
                        'error' => 'Unknown method'
                    ], self::HTTP_NOT_FOUND);
                }
            } catch (Exception $e) {
                // Handle any exceptions
                $this->response([
                    'status' => false,
                    'error' => $e->getMessage()
                ], self::HTTP_INTERNAL_ERROR);
            }
        } else {
            // No method exists - send 404
            $this->response([
                'status' => false,
                'error' => 'Unknown method'
            ], self::HTTP_NOT_FOUND);
        }
    }

    /**
     * Handle OPTIONS requests
     * 
     * @access protected
     * @return void
     */
    protected function _handle_options_request() {
        $this->load->config('api_cors', true);
        $origin = $this->config->item('allow_origin', 'api_cors') ?: '*';
        $methods = $this->config->item('allow_methods', 'api_cors') ?: 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
        $headers = $this->config->item('allow_headers', 'api_cors') ?: 'Content-Type, Authorization, X-Requested-With';

        $this->output
            ->set_header('Access-Control-Allow-Origin: ' . $origin)
            ->set_header('Access-Control-Allow-Methods: ' . $methods)
            ->set_header('Access-Control-Allow-Headers: ' . $headers)
            ->set_status_header(200)
            ->set_output('')
            ->_display();
        exit;
    }

    /**
     * Parse the request data and set up class variables
     * 
     * @access protected
     * @return void
     */
    protected function _parse_request() {
        // Parse the body data
        switch ($this->request->method) {
            case 'get':
                $this->request->body = $this->input->get();
                $this->_get_args = is_array($this->request->body) ? $this->request->body : [];
                break;
            
            case 'post':
                $this->request->body = $this->input->post() ?: $this->_parse_json_body();
                $this->_post_args = is_array($this->request->body) ? $this->request->body : [];
                break;
            
            case 'put':
            case 'delete':
            case 'patch':
                $this->request->body = $this->_parse_json_body();
                $body = is_array($this->request->body) ? $this->request->body : [];
                if ($this->request->method === 'put') {
                    $this->_put_args = $body;
                } elseif ($this->request->method === 'patch') {
                    $this->_patch_args = $body;
                } elseif ($this->request->method === 'delete') {
                    $this->_delete_args = $body;
                }
                break;
        }
    }

    /**
     * Parse the JSON request body
     * 
     * @access protected
     * @return array|null
     */
    protected function _parse_json_body() {
        $raw_input = $this->input->raw_input_stream;
        if (!empty($raw_input)) {
            $data = json_decode($raw_input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return null;
    }

    /**
     * Detect the response output format
     * 
     * @access protected
     * @return void
     */
    protected function _detect_output_format() {
        // Default to JSON
        $this->rest_format = 'json';
        
        // Check if a format is specified in the URL
        $format = $this->input->get('format');
        if ($format && in_array($format, ['json', 'xml'])) {
            $this->rest_format = $format;
        }
        
        // Check Accept header
        $accept = $this->input->server('HTTP_ACCEPT');
        if ($accept) {
            if (strpos($accept, 'application/json') !== false) {
                $this->rest_format = 'json';
            } elseif (strpos($accept, 'application/xml') !== false || strpos($accept, 'text/xml') !== false) {
                $this->rest_format = 'xml';
            }
        }
    }

    /**
     * Parse query parameters
     * 
     * @access protected
     * @return void
     */
    protected function _parse_query() {
        // Just use the CodeIgniter input library
        // No additional parsing needed for this implementation
    }

    /**
     * Get a query parameter from the request
     * 
     * @access public
     * @param  string $key The parameter key
     * @param  mixed  $default Default value if parameter is not found
     * @return mixed
     */
    public function get($key = null, $default = null) {
        if ($key === null) {
            return $this->input->get();
        }
        
        return $this->input->get($key) ?? $default;
    }

    /**
     * Get a POST parameter from the request
     * 
     * @access public
     * @param  string $key The parameter key
     * @param  mixed  $default Default value if parameter is not found
     * @return mixed
     */
    public function post($key = null, $default = null) {
        if ($key === null) {
            return $this->request->body ?? [];
        }
        
        return $this->request->body[$key] ?? $default;
    }

    /**
     * Get a PUT parameter from the request (JSON body).
     *
     * @access public
     * @param  string|null $key
     * @param  mixed       $default
     * @return mixed
     */
    public function put($key = null, $default = null) {
        $body = $this->request->body;
        if (!is_array($body)) {
            $body = [];
        }
        if ($key === null) {
            return $body;
        }
        return array_key_exists($key, $body) ? $body[$key] : $default;
    }

    /**
     * Get a PATCH parameter from the request (JSON body).
     *
     * @access public
     * @param  string|null $key
     * @param  mixed       $default
     * @return mixed
     */
    public function patch($key = null, $default = null) {
        // For this implementation, PATCH is parsed the same way as PUT.
        return $this->put($key, $default);
    }

    /**
     * Send a response to the client
     * 
     * @access public
     * @param  mixed   $data Data to send
     * @param  integer $http_code HTTP status code
     * @return void
     */
    public function response($data = null, $http_code = null) {
        // Set the status code
        $http_code = $http_code === null ? 200 : $http_code;
        $this->output->set_status_header($http_code);
        
        // Set the content type
        switch ($this->rest_format) {
            case 'json':
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode($data));
                break;
                
            case 'xml':
                // Basic XML conversion (for simple structures)
                $this->output->set_content_type('application/xml');
                $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response></response>');
                $this->_array_to_xml($data, $xml);
                $this->output->set_output($xml->asXML());
                break;
                
            default:
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode($data));
                break;
        }
        
        // Send the response
        $this->output->_display();
        exit;
    }

    /**
     * Convert an array to XML
     * 
     * @access protected
     * @param  array  $data Array to convert
     * @param  SimpleXMLElement $xml  XML element
     * @return void
     */
    protected function _array_to_xml($data, &$xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subnode = $xml->addChild($key);
                $this->_array_to_xml($value, $subnode);
            } else {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
}