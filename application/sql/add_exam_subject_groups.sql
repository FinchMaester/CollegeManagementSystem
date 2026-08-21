-- Per-exam subject group assignment (so each exam can have its own subject groups;
-- "Exam subjects" are then filtered by this exam's subject groups, not the global exam group).
-- Run this once.

CREATE TABLE IF NOT EXISTS `exam_subject_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_group_class_batch_exam_id` int(11) NOT NULL,
  `subject_group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_subject_groups_unique` (`exam_group_class_batch_exam_id`,`subject_group_id`),
  KEY `exam_subject_groups_exam` (`exam_group_class_batch_exam_id`),
  KEY `exam_subject_groups_subject_group` (`subject_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
