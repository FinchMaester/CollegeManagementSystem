<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Campus REST API CORS — override via environment variable API_CORS_ALLOW_ORIGIN
 * (e.g. https://app.yourcollege.edu.np). Empty or unset defaults to * (open).
 */
$origin = getenv('API_CORS_ALLOW_ORIGIN');
$config['allow_origin'] = ($origin !== false && $origin !== '') ? $origin : '*';
$config['allow_methods'] = 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
$config['allow_headers'] = 'Content-Type, Authorization, X-Requested-With';
