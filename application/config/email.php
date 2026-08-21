<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email Configuration
|--------------------------------------------------------------------------
|
| Here you can set your email configuration settings.
|
*/

$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.gmail.com'; 
$config['smtp_port'] = 587;
$config['smtp_user'] = 'rubikhanal8@gmail.com'; 
$config['smtp_pass'] = 'fntxemcolkmagbqo'; 
$config['smtp_crypto'] = 'tls';
$config['smtp_timeout'] = 30;
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
$config['mailtype'] = 'html';
$config['validation'] = TRUE;

// Email settings
$config['from_email'] = 'rubikhanal8@gmail.com';
$config['from_name'] = 'College System'; 

