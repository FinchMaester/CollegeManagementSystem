-- Proper Library Settings RBAC
-- 1) Adds permission_category: library_settings (Library group)
-- 2) Grants Super Admin (7) + Librarian (4)
-- 3) Enables Issue Return edit flags + grants Librarian can_edit (needed for Renew)
-- 4) Adds Library sidebar submenu for Settings
-- Idempotent / safe to re-run.
--
-- Run against: a_final_college

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1) permission_category: library_settings
--    View + Edit only (like other System Settings permissions)
-- ---------------------------------------------------------------------------
INSERT INTO `permission_category`
    (`name`, `short_code`, `perm_group_id`, `enable_view`, `enable_add`, `enable_edit`, `enable_delete`)
SELECT
    'Library Settings',
    'library_settings',
    pg.id,
    1, 0, 1, 0
FROM `permission_group` pg
WHERE pg.short_code = 'library'
  AND NOT EXISTS (
      SELECT 1 FROM `permission_category` WHERE `short_code` = 'library_settings' LIMIT 1
  )
LIMIT 1;

-- Ensure flags are correct if row already existed
UPDATE `permission_category`
SET
    `name` = 'Library Settings',
    `enable_view` = 1,
    `enable_add` = 0,
    `enable_edit` = 1,
    `enable_delete` = 0,
    `perm_group_id` = (SELECT id FROM `permission_group` WHERE short_code = 'library' LIMIT 1)
WHERE `short_code` = 'library_settings';

-- ---------------------------------------------------------------------------
-- 2) Grant library_settings to Super Admin (7) and Librarian (4)
-- ---------------------------------------------------------------------------
-- Update existing
UPDATE `roles_permissions` rp
INNER JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
SET
    rp.can_view   = 1,
    rp.can_add    = 0,
    rp.can_edit   = 1,
    rp.can_delete = 0
WHERE rp.role_id IN (4, 7)
  AND pc.short_code = 'library_settings';

-- Insert missing
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT roles.role_id, pc.id, 1, 0, 1, 0
FROM `permission_category` pc
CROSS JOIN (
    SELECT 4 AS role_id UNION ALL SELECT 7 AS role_id
) roles
WHERE pc.short_code = 'library_settings'
  AND NOT EXISTS (
      SELECT 1 FROM `roles_permissions` rp
      WHERE rp.role_id = roles.role_id AND rp.perm_cat_id = pc.id
  );

-- ---------------------------------------------------------------------------
-- 3) Renew still uses issue_return can_edit — unlock category + Librarian edit
-- ---------------------------------------------------------------------------
UPDATE `permission_category`
SET `enable_view` = 1, `enable_add` = 1, `enable_edit` = 1, `enable_delete` = 1
WHERE `short_code` = 'issue_return';

UPDATE `roles_permissions` rp
INNER JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
SET
    rp.can_view   = 1,
    rp.can_add    = 1,
    rp.can_edit   = 1,
    rp.can_delete = 1
WHERE rp.role_id = 4
  AND pc.short_code = 'issue_return';

INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 4, pc.id, 1, 1, 1, 1
FROM `permission_category` pc
WHERE pc.short_code = 'issue_return'
  AND NOT EXISTS (
      SELECT 1 FROM `roles_permissions` rp
      WHERE rp.role_id = 4 AND rp.perm_cat_id = pc.id
  );

-- ---------------------------------------------------------------------------
-- 4) Sidebar: Library → Library Settings
-- ---------------------------------------------------------------------------
INSERT INTO `sidebar_sub_menus`
    (`sidebar_menu_id`, `menu`, `key`, `lang_key`, `url`, `level`, `access_permissions`, `permission_group_id`, `activate_controller`, `activate_methods`, `addon_permission`, `is_active`, `created_at`)
SELECT
    sm.id,
    'library_settings',
    NULL,
    'library_settings',
    'admin/librarysettings',
    1,
    '(\'library_settings\', \'can_view\')',
    pg.id,
    'librarysettings',
    'index',
    NULL,
    1,
    NOW()
FROM `sidebar_menus` sm
JOIN `permission_group` pg ON pg.short_code = 'library'
WHERE sm.id = 19
  AND NOT EXISTS (
      SELECT 1 FROM `sidebar_sub_menus`
      WHERE `sidebar_menu_id` = 19 AND `url` = 'admin/librarysettings'
      LIMIT 1
  )
LIMIT 1;

COMMIT;

-- ---------------------------------------------------------------------------
-- Verify
-- ---------------------------------------------------------------------------
SELECT pc.id, pc.short_code, pc.name, pc.enable_view, pc.enable_add, pc.enable_edit, pc.enable_delete
FROM permission_category pc
WHERE pc.short_code IN ('library_settings', 'issue_return');

SELECT r.id AS role_id, r.name AS role_name, pc.short_code,
       rp.can_view, rp.can_add, rp.can_edit, rp.can_delete
FROM roles_permissions rp
JOIN roles r ON r.id = rp.role_id
JOIN permission_category pc ON pc.id = rp.perm_cat_id
WHERE pc.short_code IN ('library_settings', 'issue_return')
  AND rp.role_id IN (4, 7)
ORDER BY pc.short_code, r.id;

SELECT id, menu, url, access_permissions, is_active
FROM sidebar_sub_menus
WHERE sidebar_menu_id = 19 AND url = 'admin/librarysettings';
