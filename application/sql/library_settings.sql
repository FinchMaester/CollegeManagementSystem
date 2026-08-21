-- Library renewal & fine settings (admin UI: Library → Settings)
CREATE TABLE IF NOT EXISTS `library_settings` (
  `id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `renewal_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `renewal_days` INT NOT NULL DEFAULT 14,
  `max_renewals` INT NOT NULL DEFAULT 2,
  `fine_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `fine_per_day` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
  `fine_grace_days` INT NOT NULL DEFAULT 0,
  `fine_max` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `library_settings` (`id`, `renewal_enabled`, `renewal_days`, `max_renewals`, `fine_enabled`, `fine_per_day`, `fine_grace_days`, `fine_max`, `updated_at`)
SELECT 1, 1, 14, 2, 1, 10.00, 0, 500.00, NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `library_settings` WHERE `id` = 1);
