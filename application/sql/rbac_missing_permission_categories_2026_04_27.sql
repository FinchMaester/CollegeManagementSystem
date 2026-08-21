-- RBAC fix: seed missing permission_category rows used in code
-- Date: 2026-04-27
-- App: A Final College System (CodeIgniter RBAC)
--
-- What this does:
-- 1) Inserts missing `permission_category` short_codes (idempotent)
-- 2) Optionally grants role_id=7 (Super Admin) full rights on those categories (idempotent)
--
-- How to run:
-- - phpMyAdmin: Import this file
-- - MySQL CLI:   mysql -u root -p a_final_college < rbac_missing_permission_categories_2026_04_27.sql

START TRANSACTION;

-- Library group id was observed as perm_group_id=9 on this install.
-- If your `permission_group` IDs differ, update the perm_group_id values below.

-- =========================================================
-- 1) Insert missing permission categories (idempotent)
-- =========================================================

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Batch','batch',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='batch' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Marks Register','marks_register',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='marks_register' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Assign Subject','assign_subject',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='assign_subject' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Class Time Table','class_time_table',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='class_time_table' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Student Registration Report','student_registration_report',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='student_registration_report' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Graduation Report','graduation_report',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='graduation_report' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Dropout Report','dropout_report',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='dropout_report' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Student Counts','student_counts',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='student_counts' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Staff Count Widget','staff_count_widget',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='staff_count_widget' LIMIT 1);

-- The one you specifically needed for allocation
INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Library Card','library_card',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='library_card' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Behaviour Records Assign Incident','behaviour_records_assign_incident',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='behaviour_records_assign_incident' LIMIT 1);

INSERT INTO `permission_category` (`name`,`short_code`,`perm_group_id`,`enable_view`,`enable_add`,`enable_edit`,`enable_delete`)
SELECT 'Accountants','accountants',9,1,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM `permission_category` WHERE `short_code`='accountants' LIMIT 1);

-- =========================================================
-- 2) Grant Super Admin (role_id=7) full rights (idempotent)
-- =========================================================

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='batch'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='marks_register'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='assign_subject'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='class_time_table'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='student_registration_report'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='graduation_report'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='dropout_report'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='student_counts'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='staff_count_widget'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='library_card'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='behaviour_records_assign_incident'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

INSERT INTO `roles_permissions` (`role_id`,`perm_cat_id`,`can_view`,`can_add`,`can_edit`,`can_delete`)
SELECT 7, pc.id, 1, 1, 1, 1 FROM `permission_category` pc
WHERE pc.`short_code`='accountants'
  AND NOT EXISTS (SELECT 1 FROM `roles_permissions` rp WHERE rp.`role_id`=7 AND rp.`perm_cat_id`=pc.id LIMIT 1);

COMMIT;

-- After running:
-- - Refresh `/admin/roles/permission/{roleId}` to allocate these permissions to other roles.
-- - If changes don't appear immediately, log out/in once (RBAC is cached in session).

