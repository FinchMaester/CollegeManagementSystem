-- HEMIS / UGC integration — run once on your application database (e.g. a_final_college).
-- Order: labs → API tokens table → optional UGC login columns on hemis_integration_settings.
-- If a section errors because the object already exists, skip that section.

-- ---------------------------------------------------------------------------
-- 1) Labs (HEMIS §5.1.4) — requires `api_buildings` for building_id references.
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- 2) Campus API bearer tokens (Auth_model).
-- ---------------------------------------------------------------------------
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

-- ---------------------------------------------------------------------------
-- 3) Optional UGC /api/User/Login fields (auto-fetch bearer when token empty).
-- If any column already exists, run hemis_integration_ugc_login.sql manually
-- or add only the missing columns.
-- ---------------------------------------------------------------------------
ALTER TABLE `hemis_integration_settings`
  ADD COLUMN `remote_login_email` varchar(255) NOT NULL DEFAULT '' AFTER `remote_bearer_token`,
  ADD COLUMN `remote_login_password` varchar(512) NOT NULL DEFAULT '' AFTER `remote_login_email`,
  ADD COLUMN `remote_college_id` int NOT NULL DEFAULT 0 AFTER `remote_login_password`,
  ADD COLUMN `remote_uni_id` int NOT NULL DEFAULT 0 AFTER `remote_college_id`,
  ADD COLUMN `remote_is_ugc` tinyint(1) NOT NULL DEFAULT 0 AFTER `remote_uni_id`,
  ADD COLUMN `ugc_token_fetched_at` datetime DEFAULT NULL AFTER `remote_is_ugc`;
