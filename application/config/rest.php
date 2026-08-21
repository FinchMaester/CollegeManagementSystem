<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| HTTP protocol
|--------------------------------------------------------------------------
|
| Set to force the use of HTTPS for REST API calls
|
*/
$config['force_https'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Output Format
|--------------------------------------------------------------------------
|
| The default format of the response
|
| 'array':      Array data structure
| 'csv':        Comma separated file
| 'json':       Uses json_encode(). Note: If a GET query string
|               called 'callback' is passed, then jsonp will be returned
| 'html'        HTML using the table library in CodeIgniter
| 'php':        Uses var_export()
| 'serialized':  Uses serialize()
| 'xml':        Uses simplexml_load_string()
|
*/
$config['rest_default_format'] = 'json';

/*
|--------------------------------------------------------------------------
| REST Supported Output Formats
|--------------------------------------------------------------------------
|
| The following setting contains a list of the supported/allowed formats.
| You can remove those which you don't want to use.
| If the default format $config['rest_default_format'] is missing within
| $config['rest_supported_formats'], it will be added silently during
| REST_Controller initialization.
|
*/
$config['rest_supported_formats'] = [
    'json',
    'array',
    'csv',
    'html',
    'jsonp',
    'php',
    'serialized',
    'xml',
];

/*
|--------------------------------------------------------------------------
| REST Status Field Name
|--------------------------------------------------------------------------
|
| The field name for the status inside the response
|
*/
$config['rest_status_field_name'] = 'status';

/*
|--------------------------------------------------------------------------
| REST Message Field Name
|--------------------------------------------------------------------------
|
| The field name for the message inside the response
|
*/
$config['rest_message_field_name'] = 'message';

/*
|--------------------------------------------------------------------------
| Enable Emulate Request
|--------------------------------------------------------------------------
|
| Should we enable emulation of the request (e.g. used in Mootools request)
|
*/
$config['enable_emulate_request'] = TRUE;

/*
|--------------------------------------------------------------------------
| REST Realm
|--------------------------------------------------------------------------
|
| Name of the password protected REST API displayed on login dialogs
|
| e.g: My Secret REST API
|
*/
$config['rest_realm'] = 'REST API';

/*
|--------------------------------------------------------------------------
| REST Login
|--------------------------------------------------------------------------
|
| Set to specify the REST API requires to be logged in
|
| FALSE     No login required
| 'basic'   Unsecured login
| 'digest'  More secured login
| 'session' Check for a PHP session variable. See 'auth_source' to set the
|           authorization key
|
*/
$config['rest_auth'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Login Source
|--------------------------------------------------------------------------
|
| Is login required and if so, the user store to use
|
| ''        Use config based users or wildcard testing
| 'ldap'    Use LDAP authentication
| 'library' Use a authentication library
|
| Note: If 'rest_auth' is set to 'session' then change 'auth_source' to the name of the session variable
|
*/
$config['rest_auth_source'] = '';

/*
|--------------------------------------------------------------------------
| Allow Authentication and API Keys
|--------------------------------------------------------------------------
|
| Where you wish to have Basic, Digest or Session login, but also want to use API Keys (for limiting
| requests etc), set to TRUE;
|
*/
$config['allow_auth_and_keys'] = TRUE;
$config['strict_api_and_auth'] = TRUE; // force the use of both api and auth before a valid api request is made

/*
|--------------------------------------------------------------------------
| REST Login Class and Function
|--------------------------------------------------------------------------
|
| If not using basic or digest, you can specify a class and function name to do your authentication
|
| The function should accept a username and password as input and return an array of user data
|
| This array should follow the following structure:
|
|    array(
|        'id' => 123,
|        'username' => 'Some Person',
|        'email' => 'user@example.com',
|        'groups' => ['customers', 'admins']
|    );
|
*/
$config['rest_login_class'] = '';
$config['rest_login_function'] = '';

/*
|--------------------------------------------------------------------------
| REST API Keys Table Name
|--------------------------------------------------------------------------
|
| The table name in your database that stores API keys
|
*/
$config['rest_keys_table'] = 'keys';

/*
|--------------------------------------------------------------------------
| REST Enable Keys
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API will look for a column name 'key' in the
| $config['rest_keys_table'], and if present, and there is a key matching
| the 'key' GET variable, or the Authorization header, the key will be used
| to validate the request.
|
*/
$config['rest_enable_keys'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Key Length
|--------------------------------------------------------------------------
|
| How long should created keys be?
|
*/
$config['rest_key_length'] = 40;

/*
|--------------------------------------------------------------------------
| REST API Key Variable
|--------------------------------------------------------------------------
|
| Which variable will provide the API key
|
| Default variables:
| '' = none
| 'X-API-KEY' = Custom header
| 'key' = GET param
| 'api_key' = GET param
|
*/
$config['rest_key_name'] = 'api_key';

/*
|--------------------------------------------------------------------------
| REST API Logs Table Name
|--------------------------------------------------------------------------
|
| The table name in your database that stores logs for the REST API
|
*/
$config['rest_logs_table'] = 'logs';

/*
|--------------------------------------------------------------------------
| REST Enable Logging
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API will log actions based on the column names 'key', 'date',
| 'time' and 'ip_address'.
|
| Default table schema:
|   CREATE TABLE `logs` (
|       `id` INT(11) NOT NULL AUTO_INCREMENT,
|       `uri` VARCHAR(255) NOT NULL,
|       `method` VARCHAR(6) NOT NULL,
|       `params` TEXT DEFAULT NULL,
|       `api_key` VARCHAR(40) NOT NULL,
|       `ip_address` VARCHAR(45) NOT NULL,
|       `time` INT(11) NOT NULL,
|       `rtime` FLOAT DEFAULT NULL,
|       `authorized` VARCHAR(1) NOT NULL,
|       `response_code` smallint(3) DEFAULT '0',
|       PRIMARY KEY (`id`)
|   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
*/
$config['rest_enable_logging'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Enable Limits
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API will count the number of uses of each method
| by an API key each hour. This is useful if you want to limit the usage of your API.
|
| Set to TRUE to enable limits + store API keys in the database.
|
*/
$config['rest_enable_limits'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST API Limits Table Name
|--------------------------------------------------------------------------
|
| The table name in your database that stores API limits
|
*/
$config['rest_limits_table'] = 'limits';

/*
|--------------------------------------------------------------------------
| REST Ignore HTTP Accept
|--------------------------------------------------------------------------
|
| Set to TRUE to ignore the HTTP Accept and speed up each request a little.
| Only do this if you are using the $this->rest_format or /format/xml in URLs
|
*/
$config['rest_ignore_http_accept'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST AJAX Only
|--------------------------------------------------------------------------
|
| Set to TRUE to allow AJAX requests only. Set to FALSE to allow any kind of HTTP request.
|
*/
$config['rest_ajax_only'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Language File
|--------------------------------------------------------------------------
|
| Language file to load from the language directory
|
*/
$config['rest_language'] = 'english';

/*
|--------------------------------------------------------------------------
| CORS Allow Any Domain
|--------------------------------------------------------------------------
|
| Set to TRUE to enable Cross-Origin Resource Sharing (CORS) from any domain
|
*/
$config['allow_any_cors_domain'] = TRUE;

/*
|--------------------------------------------------------------------------
| CORS Allowable Domains
|--------------------------------------------------------------------------
|
| Used if $config['check_cors'] is set to TRUE and $config['allow_any_cors_domain']
| is set to FALSE. Set all the allowable domains within the array
|
| e.g. $config['allowed_cors_domains'] = ['http://example.com', 'https://spa.example.com']
|
*/
$config['allowed_cors_domains'] = [];

/*
|--------------------------------------------------------------------------
| CORS Forced Headers
|--------------------------------------------------------------------------
|
| If using CORS checks, always include the headers and values specified here
| in the OPTIONS client preflight.
|
*/
$config['forced_cors_headers'] = [
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE',
    'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization'
];
/*
|--------------------------------------------------------------------------
| CORS Allowable Headers
|--------------------------------------------------------------------------
|
| If using CORS checks, set the allowable headers here
|
*/
$config['allowed_cors_headers'] = [
    'Origin',
    'X-Requested-With',
    'Content-Type',
    'Accept',
    'Access-Control-Request-Method',
    'Authorization'
];

/*
|--------------------------------------------------------------------------
| CORS Allowable Methods
|--------------------------------------------------------------------------
|
| If using CORS checks, you can set the methods you want to be allowed
|
*/
$config['allowed_cors_methods'] = [
    'GET',
    'POST',
    'OPTIONS',
    'PUT',
    'PATCH',
    'DELETE'
];

/*
|--------------------------------------------------------------------------
| CORS Allow Any Domain
|--------------------------------------------------------------------------
|
| Set to TRUE to enable Cross-Origin Resource