-- Diagnostic: Exam subject groups (per-exam vs global)
-- Run these in your DB to see why "Add Exam Subject" shows which subjects.

-- 1) Ensure per-exam table exists (if missing, run add_exam_subject_groups.sql first)
SHOW TABLES LIKE 'exam_subject_groups';

-- 2) Per-exam assignment: which exam has which subject groups?
--    If this returns rows for an exam, that exam's dropdown uses ONLY these groups' subjects.
--    If this returns no rows for an exam, that exam uses the exam group (global) list below.
SELECT esg.id, esg.exam_group_class_batch_exam_id AS exam_id, esg.subject_group_id, sg.name AS subject_group_name
FROM exam_subject_groups esg
LEFT JOIN subject_groups sg ON sg.id = esg.subject_group_id
ORDER BY esg.exam_group_class_batch_exam_id, esg.subject_group_id;

-- 3) Global (exam group) assignment: which subject groups are assigned to each exam group?
--    These subjects are used for an exam when that exam has NO rows in exam_subject_groups.
SELECT egsg.exam_group_id, egsg.subject_group_id, sg.name AS subject_group_name
FROM exam_group_subject_groups egsg
LEFT JOIN subject_groups sg ON sg.id = egsg.subject_group_id
ORDER BY egsg.exam_group_id, egsg.subject_group_id;

-- 4) To make "Add Exam Subject" use only per-exam groups for an exam:
--    Assign subject groups to that exam via the UI "Assign Subject Group" button.
-- 5) To make all exams use the global list again (like before):
--    Delete per-exam assignments (optional; then every exam uses exam group list):
-- DELETE FROM exam_subject_groups;
