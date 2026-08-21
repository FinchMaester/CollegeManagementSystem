-- Optional: timestamp for stale HEMIS error cleanup (see Auth_model::prune_stale_hemis_sync_errors).
-- Run once if not already applied.

ALTER TABLE `students`
  ADD COLUMN `hemis_sync_error_at` DATETIME NULL DEFAULT NULL AFTER `hemis_sync_error`;
