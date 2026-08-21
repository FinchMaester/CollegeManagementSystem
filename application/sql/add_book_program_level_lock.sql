-- Add optional Faculty (Section), Program, Level (Academic Level), and Lock for Session to books table.
-- Run this once (e.g. in phpMyAdmin or mysql client) before using the new book fields.
-- If you already ran a version without section_id, run only the section_id line:
-- ALTER TABLE `books` ADD COLUMN `section_id` INT(11) NULL DEFAULT NULL AFTER `description`;

ALTER TABLE `books`
  ADD COLUMN `section_id` INT(11) NULL DEFAULT NULL AFTER `description`,
  ADD COLUMN `program_id` INT(11) NULL DEFAULT NULL AFTER `section_id`,
  ADD COLUMN `class_id` INT(11) NULL DEFAULT NULL AFTER `program_id`,
  ADD COLUMN `lock_session_id` INT(11) NULL DEFAULT NULL AFTER `class_id`;

-- Optional: add indexes if you filter books by program/class/session
-- ALTER TABLE `books` ADD INDEX `idx_books_program_id` (`program_id`);
-- ALTER TABLE `books` ADD INDEX `idx_books_class_id` (`class_id`);
-- ALTER TABLE `books` ADD INDEX `idx_books_lock_session_id` (`lock_session_id`);
