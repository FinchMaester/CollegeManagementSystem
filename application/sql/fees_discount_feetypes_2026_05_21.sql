-- Fee discount ↔ fee type applicability (run if migrations are not used)
CREATE TABLE IF NOT EXISTS `fees_discount_feetypes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fees_discount_id` int NOT NULL,
  `feetype_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_discount_feetype` (`fees_discount_id`,`feetype_id`),
  KEY `fees_discount_id` (`fees_discount_id`),
  KEY `feetype_id` (`feetype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
