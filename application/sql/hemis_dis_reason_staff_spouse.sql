-- UGC dropout reasons are text; staff HR fields for HEMIS spouse/dependents.
-- Run once: mysql ... < application/sql/hemis_dis_reason_staff_spouse.sql

ALTER TABLE `students`
  MODIFY `dis_reason` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `staff`
  ADD COLUMN `spouse_name` VARCHAR(255) NULL DEFAULT NULL AFTER `marital_status`,
  ADD COLUMN `dependent_info` TEXT NULL AFTER `spouse_name`;
