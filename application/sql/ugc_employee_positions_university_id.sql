-- Optional manual run if migrations are not used.
-- Adds UGC universityId storage for cached employee positions.

ALTER TABLE `ugc_employee_positions`
  ADD COLUMN `university_id` INT NULL AFTER `idx`;
