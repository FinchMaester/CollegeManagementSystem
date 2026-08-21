-- HEMIS / UGC — one-time production checklist (run manually after review)
-- Companion: GET /cron/hemis_go_live/{cron_secret}?mode=report|fix_errors|fix_gender|fix_all
--
-- 1) Gender: UGC expects numeric 1=Male, 2=Female, 3=Other for many payloads.
--    This app may still store "Male"/"Female" in students.gender for UI; the readiness
--    report counts rows that cannot be mapped. Optional normalization (BACKUP FIRST):
--
-- UPDATE students SET gender = '1' WHERE LOWER(TRIM(gender)) IN ('male','m');
-- UPDATE students SET gender = '2' WHERE LOWER(TRIM(gender)) IN ('female','f');
-- UPDATE students SET gender = '3' WHERE LOWER(TRIM(gender)) IN ('other','o');
--
-- 2) Program codes: fill application/config/ugc_program_codes.php with official codes,
--    then use cron hemis_go_live?mode=report to list programs.code values not in that list.
--
-- 3) Dev sync errors: optional cleanup of leftover test strings (cron mode=fix_errors is safer).

SELECT 1;
