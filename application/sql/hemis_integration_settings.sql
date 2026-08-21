-- Central / UGC / Ministry API integration settings (single row, overridden via admin UI).
-- Run once, then open System Settings → Central API (HEMIS) to edit.

CREATE TABLE IF NOT EXISTS `hemis_integration_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sync_student_update` tinyint(1) NOT NULL DEFAULT 1,
  `sync_student_upgrade` tinyint(1) NOT NULL DEFAULT 1,
  `sync_graduation` tinyint(1) NOT NULL DEFAULT 1,
  `sync_dropout` tinyint(1) NOT NULL DEFAULT 1,
  `sync_library` tinyint(1) NOT NULL DEFAULT 1,
  `sync_employee` tinyint(1) NOT NULL DEFAULT 1,
  `sync_labs` tinyint(1) NOT NULL DEFAULT 1,
  `sync_buildings` tinyint(1) NOT NULL DEFAULT 1,
  `sync_fev` tinyint(1) NOT NULL DEFAULT 1,
  `sync_hostels` tinyint(1) NOT NULL DEFAULT 1,
  `sync_lands` tinyint(1) NOT NULL DEFAULT 1,
  `sync_management` tinyint(1) NOT NULL DEFAULT 1,
  `pull_batch` tinyint(1) NOT NULL DEFAULT 0,
  `pull_fiscal_year` tinyint(1) NOT NULL DEFAULT 0,
  `pull_address` tinyint(1) NOT NULL DEFAULT 0,
  `pull_employee_positions` tinyint(1) NOT NULL DEFAULT 0,
  `pull_programmgmt` tinyint(1) NOT NULL DEFAULT 0,
  `pull_management` tinyint(1) NOT NULL DEFAULT 0,
  `mock_mode` tinyint(1) NOT NULL DEFAULT 1,
  `mock_http_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `local_mock_base_url` varchar(512) NOT NULL DEFAULT '',
  `remote_base_url` varchar(512) NOT NULL DEFAULT 'https://hemisapi.ugcnepal.edu.np',
  `remote_bearer_token` text,
  `remote_timeout` int(11) NOT NULL DEFAULT 30,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
