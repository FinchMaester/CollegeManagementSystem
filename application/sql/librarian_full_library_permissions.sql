-- Grant Librarian (role_id = 4) full Library permissions matching Super Admin.
-- Safe to re-run: updates existing rows, inserts missing ones.
-- DB: a_final_college

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1) Allow add/edit/delete checkboxes in Roles UI for Library categories
--    (Super Admin already has these grants in roles_permissions; category
--     flags were view-only which hides edit in the Roles screen.)
-- ---------------------------------------------------------------------------
UPDATE `permission_category`
SET `enable_view` = 1, `enable_add` = 1, `enable_edit` = 1, `enable_delete` = 1
WHERE `short_code` IN (
    'issue_return',
    'add_staff_member',
    'add_student',
    'import_book',
    'library_card',
    'books'
);

-- ---------------------------------------------------------------------------
-- 2) Upsert Librarian grants (role_id = 4) — view/add/edit/delete = 1
--    Category IDs (current DB):
--      28  books
--      29  issue_return          (needed for Settings save + renew)
--      30  add_staff_member
--     123  add_student
--     228  import_book
--     281  library_card          (was completely missing)
--     178  book_issue_report
--     179  book_due_report
--     180  book_inventory_report
--     221  book_issue_return_report
--     194  book_overview_widegts
-- ---------------------------------------------------------------------------

-- Update any existing Librarian rows for these permissions
UPDATE `roles_permissions` rp
INNER JOIN `permission_category` pc ON pc.id = rp.perm_cat_id
SET
    rp.can_view   = 1,
    rp.can_add    = 1,
    rp.can_edit   = 1,
    rp.can_delete = 1
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
  );

-- Insert any missing Librarian permission rows
INSERT INTO `roles_permissions` (`role_id`, `perm_cat_id`, `can_view`, `can_add`, `can_edit`, `can_delete`)
SELECT 4, pc.id, 1, 1, 1, 1
FROM `permission_category` pc
WHERE pc.short_code IN (
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
  AND NOT EXISTS (
      SELECT 1
      FROM `roles_permissions` rp
      WHERE rp.role_id = 4
        AND rp.perm_cat_id = pc.id
  );

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
