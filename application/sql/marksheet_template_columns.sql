-- Optional: run if auto-migration from Marksheet_model does not run (e.g. restricted DB user).
-- Adds dynamic marksheet layout fields to template_marksheets.

ALTER TABLE `template_marksheets`
  ADD COLUMN `column_layout_json` LONGTEXT NULL AFTER `content_footer`;

ALTER TABLE `template_marksheets`
  ADD COLUMN `marksheet_display_mode` VARCHAR(32) NOT NULL DEFAULT 'legacy' AFTER `column_layout_json`;

ALTER TABLE `template_marksheets`
  ADD COLUMN `grading_system_mode` VARCHAR(32) NOT NULL DEFAULT 'inherit' AFTER `marksheet_display_mode`;

ALTER TABLE `template_marksheets`
  ADD COLUMN `student_detail_layout_json` LONGTEXT NULL AFTER `grading_system_mode`;

ALTER TABLE `template_marksheets`
  ADD COLUMN `marksheet_footer_json` LONGTEXT NULL AFTER `student_detail_layout_json`;
