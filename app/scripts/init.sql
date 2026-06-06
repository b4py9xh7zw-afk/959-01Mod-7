-- Database initialization script
-- This script creates the necessary tables and seeds initial data

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create licenses table
CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(100) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    type ENUM('trial', 'paid') DEFAULT 'trial',
    tier ENUM('basic', 'standard', 'premium') DEFAULT 'basic',
    status ENUM('active', 'inactive', 'expired', 'converted') DEFAULT 'active',
    expires_at DATETIME NULL,
    trial_ends_at DATETIME NULL,
    converted_at DATETIME NULL,
    converted_to_license_id INT NULL,
    max_seats INT DEFAULT 1,
    max_projects INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_license_key (license_key),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_tier (tier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    config JSON NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    INDEX idx_license_id (license_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create plugins table
CREATE TABLE IF NOT EXISTS plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    plugin_code VARCHAR(100) NOT NULL,
    version VARCHAR(50) NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    requires_premium TINYINT(1) DEFAULT 0,
    config JSON NULL,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    INDEX idx_license_id (license_id),
    INDEX idx_plugin_code (plugin_code),
    INDEX idx_is_enabled (is_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_seats table
CREATE TABLE IF NOT EXISTS user_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    user_name VARCHAR(255) NULL,
    role ENUM('admin', 'member', 'viewer') DEFAULT 'member',
    is_active TINYINT(1) DEFAULT 1,
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    joined_at DATETIME NULL,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    UNIQUE KEY uk_license_email (license_id, user_email),
    INDEX idx_license_id (license_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create activity_logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    action_type ENUM('login', 'project_create', 'project_update', 'plugin_enable', 'plugin_disable', 'seat_add', 'seat_remove', 'api_call', 'export', 'config_change') NOT NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    INDEX idx_license_id (license_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create license_features table for capability control
CREATE TABLE IF NOT EXISTS license_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    feature_code VARCHAR(100) NOT NULL,
    feature_name VARCHAR(255) NOT NULL,
    is_allowed TINYINT(1) DEFAULT 1,
    is_advanced TINYINT(1) DEFAULT 0,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    UNIQUE KEY uk_license_feature (license_id, feature_code),
    INDEX idx_license_id (license_id),
    INDEX idx_feature_code (feature_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: User seeding is handled by PHP script (app/scripts/seed_users.php)
-- This ensures correct password hashing. Users will be created on first container startup.
-- Sample licenses will be created by app/scripts/seed_licenses.php after users are created.
