-- Run once on your campus database (MySQL/MariaDB) to store HEMIS central student id and sync metadata.
-- After this, set application/config/hemis.php mock_mode as needed for testing.

ALTER TABLE `students`
  ADD COLUMN `hemis_student_id` INT NULL DEFAULT NULL COMMENT 'UGC HEMIS student id' AFTER `id`,
  ADD COLUMN `hemis_sync_at` DATETIME NULL DEFAULT NULL AFTER `hemis_student_id`,
  ADD COLUMN `hemis_sync_error` VARCHAR(500) NULL DEFAULT NULL AFTER `hemis_sync_at`;
