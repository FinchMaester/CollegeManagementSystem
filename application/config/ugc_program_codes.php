<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Official program codes from UGC ProgramMgmt for this campus (short codes as stored in programs.code).
 * Used by cron hemis_go_live readiness checks. Leave empty to skip strict validation.
 *
 * Example:
 * $config['ugc_official_program_codes'] = array('BCA', 'BBA', 'BBS');
 */
$config['ugc_official_program_codes'] = array();
