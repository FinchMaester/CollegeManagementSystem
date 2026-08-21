-- Revert Librarian (role_id = 4) Library permissions to pre-grant state.
-- Undoes: application/sql/librarian_full_library_permissions.sql
-- Safe to re-run.

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1) Restore permission_category enable flags (view-only where they were)
--    library_card / books already allowed full flags before the grant — leave them.
-- ---------------------------------------------------------------------------
UPDATE `permission_category`
SET `enable_view` = 1, `enable_add` = 0, `enable_edit` = 0, `enable_delete` = 0
WHERE `short_code` IN (
    'issue_return',
    'add_staff_member',
    'add_student',
    'import_book'
);

-- ---------------------------------------------------------------------------
-- 2) Restore Librarian roles_permissions to previous values
--    Previous state:
--      books                         → 1/1/1/1  (unchanged)
--      issue_return                  → 1/0/0/0
--      add_staff_member              → 1/0/0/0
--      add_student                   → 1/0/0/0
--      import_book                   → 1/0/0/0
--      book_issue_report             → 1/0/0/0
--      book_due_report               → 1/0/0/0
--      book_inventory_report         → 1/0/0/0
--      book_issue_return_report      → 1/0/0/0
--      book_overview_widegts         → 1/0/0/0
--      library_card                  → not assigned (delete row)
-- ---------------------------------------------------------------------------

UPDATE `roles_permissions` rp
INNER JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
SET
    rp.can_view   = 1,
    rp.can_add    = 0,
    rp.can_edit   = 0,
    rp.can_delete = 0
WHERE rp.role_id = 4
  AND pc.short_code IN (
      'issue_return',
      'add_staff_member',
      'add_student',
      'import_book',
      'book_issue_report',
      'book_due_report',
      'book_inventory_report',
      'book_issue_return_report',
      'book_overview_widegts'
  );

-- books stays full CRUD (was already 1/1/1/1 before)
UPDATE `roles_permissions` rp
INNER JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
SET
    rp.can_view   = 1,
    rp.can_add    = 1,
    rp.can_edit   = 1,
    rp.can_delete = 1
WHERE rp.role_id = 4
  AND pc.short_code = 'books';

-- library_card was not assigned to Librarian before — remove it
DELETE rp
FROM `roles_permissions` rp
INNER JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
WHERE rp.role_id = 4
  AND pc.short_code = 'library_card';

COMMIT;

-- ---------------------------------------------------------------------------
-- Verify
-- ---------------------------------------------------------------------------
SELECT
    pc.short_code,
    pc.name,
    rp.can_view,
    rp.can_add,
    rp.can_edit,
    rp.can_delete
FROM roles_permissions rp
JOIN permission_category pc ON pc.id = rp.perm_cat_id
WHERE rp.role_id = 4
  AND pc.short_code IN (
      'books',
      'issue_return',
      'add_staff_member',
      'add_student',
      'import_book',
      'library_card',
      'book_issue_report',
      'book_due_report',
      'book_inventory_report',
      'book_issue_return_report',
      'book_overview_widegts'
  )
ORDER BY pc.short_code;
