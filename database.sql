CREATE DATABASE IF NOT EXISTS egg_incubator_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE egg_incubator_db;

CREATE TABLE IF NOT EXISTS readings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  temperature FLOAT NOT NULL,
  humidity FLOAT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  min_temp FLOAT DEFAULT 35,
  max_temp FLOAT DEFAULT 39,
  min_humidity FLOAT DEFAULT 50,
  max_humidity FLOAT DEFAULT 65,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO settings (id, min_temp, max_temp, min_humidity, max_humidity)
VALUES (1, 35, 39, 50, 65);

CREATE TABLE IF NOT EXISTS egg_batches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_name VARCHAR(100) NOT NULL,
  tray_number INT NOT NULL,
  egg_type VARCHAR(50),
  quantity INT NOT NULL,
  start_date DATE NOT NULL,
  incubation_days INT NOT NULL,
  expected_hatch_date DATE NOT NULL,
  status VARCHAR(30) DEFAULT 'Incubating',
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tray_status (tray_number, status),
  INDEX idx_expected_hatch_date (expected_hatch_date)
) ENGINE=InnoDB;
