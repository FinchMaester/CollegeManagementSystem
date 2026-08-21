<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['pre_system'][] = array(
    'class'    => 'Maintenance_hook',
    'function' => 'offline_check',
    'filename' => 'Maintenance_hook.php',
    'filepath' => 'hooks'
);

// Serve public CMS on the main domain and the HEMIS ERP on the hemis.* subdomain
// from one codebase. Configured per deployment via .env (APP_SITE_MODE etc.).
// Disabled automatically when APP_SITE_MODE is empty (legacy behavior).
$hook['pre_system'][] = array(
    'class'    => 'Host_gate_hook',
    'function' => 'enforce',
    'filename' => 'Host_gate_hook.php',
    'filepath' => 'hooks'
);