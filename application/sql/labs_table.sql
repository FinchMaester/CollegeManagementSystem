-- Local labs table for HEMIS §5.1.4 — links to api_buildings (building_id).
-- Run once if `labs` is missing.

CREATE TABLE IF NOT EXISTS `labs` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_name` varchar(255) NOT NULL DEFAULT '',
  `building_id` int UNSIGNED NOT NULL DEFAULT 0,
  `area_covered` decimal(14,2) NOT NULL DEFAULT 0.00,
  `lab_type` varchar(128) NOT NULL DEFAULT '',
  `adequency_of_equipment` tinyint(1) NOT NULL DEFAULT 0,
  `has_internet` tinyint(1) NOT NULL DEFAULT 0,
  `equipment` text,
  `remarks` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_labs_building` (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
