-- FM Checks Database Schema
-- Facilities Management Periodic Check System

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'manager', 'staff', 'viewer') NOT NULL DEFAULT 'staff',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Venues table
CREATE TABLE IF NOT EXISTS venues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    notes TEXT,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_name (name),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check Types table
CREATE TABLE IF NOT EXISTS check_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    colour VARCHAR(7) NOT NULL DEFAULT '#2563eb',
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Areas table (CAD plans)
CREATE TABLE IF NOT EXISTS areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venue_id INT UNSIGNED NOT NULL,
    area_name VARCHAR(100) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    cal_x1 DECIMAL(10, 8),
    cal_y1 DECIMAL(10, 8),
    cal_x2 DECIMAL(10, 8),
    cal_y2 DECIMAL(10, 8),
    cal_distance_m DECIMAL(10, 2),
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_venue_id (venue_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check Points table
CREATE TABLE IF NOT EXISTS check_points (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area_id INT UNSIGNED NOT NULL,
    reference VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    check_type_id INT UNSIGNED NOT NULL,
    x_coord DECIMAL(10, 8) NOT NULL,
    y_coord DECIMAL(10, 8) NOT NULL,
    periodicity ENUM('daily', 'weekly', 'monthly', 'quarterly', 'annually') NOT NULL DEFAULT 'monthly',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    FOREIGN KEY (check_type_id) REFERENCES check_types(id) ON DELETE RESTRICT,
    INDEX idx_area_id (area_id),
    INDEX idx_reference (reference),
    INDEX idx_periodicity (periodicity),
    UNIQUE KEY unique_area_reference (area_id, reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check Logs table
CREATE TABLE IF NOT EXISTS check_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_point_id INT UNSIGNED NOT NULL,
    performed_by INT UNSIGNED NOT NULL,
    status ENUM('pass', 'fail') NOT NULL,
    notes TEXT,
    photo_path VARCHAR(255),
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (check_point_id) REFERENCES check_points(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_check_point_id (check_point_id),
    INDEX idx_performed_by (performed_by),
    INDEX idx_status (status),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Venue Plans table (plan library per venue)
CREATE TABLE IF NOT EXISTS venue_plans (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venue_id    INT UNSIGNED NOT NULL,
    name        VARCHAR(255) NOT NULL,
    pdf_path    VARCHAR(500) NOT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id)    REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_venue_id (venue_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_log_id INT UNSIGNED,
    check_point_id INT UNSIGNED,
    venue_id INT UNSIGNED NOT NULL,
    area_id INT UNSIGNED,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    assigned_to INT UNSIGNED,
    resolved_at TIMESTAMP NULL,
    resolved_by INT UNSIGNED,
    resolution_notes TEXT,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (check_log_id) REFERENCES check_logs(id) ON DELETE SET NULL,
    FOREIGN KEY (check_point_id) REFERENCES check_points(id) ON DELETE SET NULL,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_venue_id (venue_id),
    INDEX idx_area_id (area_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role, is_active) VALUES
('admin', 'admin@fmchecks.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', TRUE);

-- Insert default check types
INSERT INTO check_types (name, colour, icon) VALUES
('Electrical', '#f59e0b', '⚡'),
('Fire Safety', '#dc2626', '🔥'),
('Plumbing', '#3b82f6', '💧'),
('HVAC', '#8b5cf6', '🌡️'),
('Structural', '#6b7280', '🏗️'),
('Security', '#ef4444', '🔒'),
('Emergency Lighting', '#eab308', '💡'),
('PAT Testing', '#10b981', '🔌');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'FM Checks', 'Application name'),
('site_timezone', 'UTC', 'Default timezone'),
('check_reminder_hours', '24', 'Hours before check due to show amber warning'),
('allow_registration', '0', 'Allow public user registration (0=no, 1=yes)');
