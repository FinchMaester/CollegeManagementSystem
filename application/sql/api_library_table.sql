-- Campus library records for HEMIS-shaped API (run once if table missing).
CREATE TABLE IF NOT EXISTS `api_library` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `library_name` varchar(255) NOT NULL,
  `building_id` int(11) NOT NULL,
  `campus_id` int(11) NOT NULL,
  `no_library_rooms` int(11) NOT NULL DEFAULT 0,
  `total_area_library_room` decimal(12,2) NOT NULL DEFAULT 0.00,
  `no_reading_halls` int(11) NOT NULL DEFAULT 0,
  `area_reading_hall` decimal(12,2) NOT NULL DEFAULT 0.00,
  `no_books` int(11) NOT NULL DEFAULT 0,
  `no_daily_journals` int(11) NOT NULL DEFAULT 0,
  `no_weekly_journals` int(11) NOT NULL DEFAULT 0,
  `no_monthly_journals` int(11) NOT NULL DEFAULT 0,
  `no_annual_journals` int(11) NOT NULL DEFAULT 0,
  `has_e_library_facility` tinyint(1) NOT NULL DEFAULT 0,
  `remarks` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campus_id` (`campus_id`),
  KEY `building_id` (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
