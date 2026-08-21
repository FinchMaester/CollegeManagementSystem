-- API Bearer tokens for HEMIS-style campus endpoints (Auth_model).
-- Run on database: a_final_college (or your configured DB name).

CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `created_date` datetime NOT NULL,
  `expiry_date` datetime NOT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expired` (`expired`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
