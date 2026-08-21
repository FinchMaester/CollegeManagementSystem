<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| JWT Configuration
|--------------------------------------------------------------------------
|
| Set JWT_SECRET in .env (see .env.example). Do not commit real secrets.
|
*/

$config['jwt_secret'] = function_exists('app_env') ? app_env('JWT_SECRET', '') : '';
$config['token_expire_time'] = 86400;
