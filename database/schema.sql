-- FTI Fest | Jalankan pada database MySQL 8+ atau MariaDB 10.4+.
-- Buat database dan user terlebih dahulu dari cPanel.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  nim VARCHAR(40) DEFAULT NULL,
  class_name VARCHAR(20) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','committee','lecturer','admin') NOT NULL DEFAULT 'student',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  owner_id BIGINT UNSIGNED NOT NULL,
  academic_year VARCHAR(15) NOT NULL DEFAULT '2026/2027',
  class_name VARCHAR(20) NOT NULL,
  group_code VARCHAR(35) NOT NULL,
  group_name VARCHAR(100) NOT NULL,
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(210) NOT NULL UNIQUE,
  category ENUM('campus','umkm','agritech','tourism','environment','community','other') NOT NULL DEFAULT 'other',
  short_description VARCHAR(350) NOT NULL DEFAULT '',
  problem TEXT NOT NULL,
  solution TEXT NOT NULL,
  target_users VARCHAR(350) NOT NULL DEFAULT '',
  key_features TEXT NOT NULL,
  tech_stack VARCHAR(500) NOT NULL DEFAULT '',
  github_url VARCHAR(500) DEFAULT NULL,
  figma_url VARCHAR(500) DEFAULT NULL,
  live_url VARCHAR(500) DEFAULT NULL,
  video_url VARCHAR(500) DEFAULT NULL,
  poster_file VARCHAR(120) DEFAULT NULL,
  cover_file VARCHAR(120) DEFAULT NULL,
  report_file VARCHAR(120) DEFAULT NULL,
  status ENUM('draft','submitted','revision','approved') NOT NULL DEFAULT 'draft',
  review_note TEXT DEFAULT NULL,
  reviewed_by BIGINT UNSIGNED DEFAULT NULL,
  reviewed_at DATETIME DEFAULT NULL,
  submitted_at DATETIME DEFAULT NULL,
  published TINYINT(1) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  event_format ENUM('undecided','seminar','exhibition','both') NOT NULL DEFAULT 'undecided',
  booth_number VARCHAR(40) DEFAULT NULL,
  session_label VARCHAR(100) DEFAULT NULL,
  scheduled_at DATETIME DEFAULT NULL,
  venue VARCHAR(150) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_project_group (academic_year, class_name, group_code),
  INDEX idx_public (status, published, featured),
  INDEX idx_owner (owner_id),
  INDEX idx_category (category),
  CONSTRAINT fk_projects_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_projects_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_members (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  project_id BIGINT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  nim VARCHAR(40) NOT NULL,
  contribution VARCHAR(130) DEFAULT NULL,
  ordering SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_members_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX idx_members_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(70) NOT NULL PRIMARY KEY,
  setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  fingerprint CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_login_fingerprint (fingerprint, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  actor_id BIGINT UNSIGNED DEFAULT NULL,
  project_id BIGINT UNSIGNED DEFAULT NULL,
  action VARCHAR(65) NOT NULL,
  details TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_project (project_id, created_at),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_audit_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (setting_key, setting_value) VALUES
 ('event_name', 'FTI FEST 2027'),
 ('academic_year', '2026/2027'),
 ('event_month', 'Januari 2027'),
 ('event_date', ''),
 ('event_location', 'Lokasi akan diumumkan'),
 ('event_description', 'Showcase hasil Proyek Kolaboratif Semester V Program Studi Informatika, FTI Universitas Sebelas April.'),
 ('submissions_open', '1')
ON DUPLICATE KEY UPDATE setting_key=setting_key;
