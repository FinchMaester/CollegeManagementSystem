-- Per-fee-type discount usage (run after fees_discount_feetypes table exists)
CREATE TABLE IF NOT EXISTS `student_fees_discount_usages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_fees_discount_id` int NOT NULL,
  `feetype_id` int NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sfd_feetype` (`student_fees_discount_id`,`feetype_id`),
  KEY `student_fees_discount_id` (`student_fees_discount_id`),
  KEY `feetype_id` (`feetype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
