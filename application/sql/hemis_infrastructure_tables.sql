-- HEMIS-shaped local persistence (PDF V1.3 §5.1, §5.6) — run once on campus DB.

CREATE TABLE IF NOT EXISTS api_buildings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  house_name VARCHAR(255) NOT NULL DEFAULT '',
  area_covered_by_building DECIMAL(14,2) NOT NULL DEFAULT 0,
  no_of_classrooms INT NOT NULL DEFAULT 0,
  area_covered_by_all_rooms DECIMAL(14,2) NOT NULL DEFAULT 0,
  ownership_of_building TINYINT(1) NOT NULL DEFAULT 1,
  has_internet_connection TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT,
  created_at DATETIME DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_furniture_equipment_vehicle (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_type VARCHAR(128) NOT NULL DEFAULT '',
  item_name VARCHAR(255) NOT NULL DEFAULT '',
  no_of_qty INT NOT NULL DEFAULT 0,
  item_description TEXT,
  remarks TEXT,
  created_at DATETIME DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_hostels (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  hostel_type VARCHAR(128) NOT NULL DEFAULT '',
  no_of_rooms_in_hostel INT NOT NULL DEFAULT 0,
  no_of_seats INT NOT NULL DEFAULT 0,
  area_covered_by_hostel DECIMAL(14,2) NOT NULL DEFAULT 0,
  building_id INT NOT NULL DEFAULT 0,
  has_playground TINYINT(1) NOT NULL DEFAULT 0,
  has_internet TINYINT(1) NOT NULL DEFAULT 0,
  has_drinking_water TINYINT(1) NOT NULL DEFAULT 0,
  has_toilet TINYINT(1) NOT NULL DEFAULT 0,
  remarks TEXT,
  created_at DATETIME DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_lands (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  total_area DECIMAL(14,2) NOT NULL DEFAULT 0,
  unit VARCHAR(32) NOT NULL DEFAULT '',
  sheet_no VARCHAR(64) NOT NULL DEFAULT '',
  kitta_no VARCHAR(64) NOT NULL DEFAULT '',
  owner_ship TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT,
  created_at DATETIME DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_management_departments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  department_name VARCHAR(255) NOT NULL DEFAULT '',
  status TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT,
  created_at DATETIME DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_management_sections (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_name VARCHAR(255) NOT NULL DEFAULT '',
  status TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT,
  created_at DATETIME DEFAULT NULL,
  updated_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
