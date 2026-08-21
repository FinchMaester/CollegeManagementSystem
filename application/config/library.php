<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Who appears on Library → Members (admin/member)?
 *
 * registered_only (recommended)
 *   Only students who have a row in libarary_members (added via Library → Member → Student)
 *   and staff added as teacher members. Issue/return always uses a valid library member id.
 *
 * all_session_students
 *   Every student in the current session is listed (even without library registration).
 *   Unregistered students show member id 0 and book issue will fail until they are added
 *   as library members — use only if you need a full roster view.
 */
$config['library_student_member_list'] = 'registered_only';

/**
 * Book renewal (extend due date on outstanding issues).
 */
$config['library_renewal_enabled']   = true;
$config['library_renewal_days']      = 14;
$config['library_max_renewals']      = 2;

/**
 * Overdue fines (calculated when due date is before return/today).
 */
$config['library_fine_enabled']      = true;
$config['library_fine_per_day']      = 10;
$config['library_fine_grace_days']   = 0;
$config['library_fine_max']          = 500;
