-- Traffic Tracker Database Initialization Script
-- This script creates the complete database schema with 3 tables: users, pages, visits

-- Set character set and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Use the traffic_tracker database
USE traffic_tracker;

-- ============================================================
-- Table: users
-- Description: Stores user accounts for dashboard authentication
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique username for login',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'User email address',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password (bcrypt)',
    roles JSON NOT NULL COMMENT 'User roles array (e.g., ["ROLE_USER", "ROLE_ADMIN"])',
    created_at DATETIME NOT NULL COMMENT 'Account creation timestamp',
    is_active TINYINT(1) NOT NULL COMMENT 'Account active status',
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User accounts for authentication and authorization';

-- ============================================================
-- Table: pages
-- Description: Stores unique pages being tracked
-- ============================================================
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(500) NOT NULL UNIQUE COMMENT 'Full URL of the tracked page',
    title VARCHAR(255) DEFAULT NULL COMMENT 'Page title from tracker',
    created_at DATETIME NOT NULL COMMENT 'First time page was tracked',
    updated_at DATETIME NOT NULL COMMENT 'Last time page was visited',
    INDEX idx_url (url(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracked web pages';

-- ============================================================
-- Table: visits
-- Description: Stores individual visit records with geolocation and bot detection
-- ============================================================
CREATE TABLE IF NOT EXISTS visits (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_id INT NOT NULL COMMENT 'Foreign key to pages table',
    visitor_fingerprint VARCHAR(64) NOT NULL COMMENT 'Unique visitor identifier hash',
    ip_address_hash VARCHAR(64) DEFAULT NULL COMMENT 'Hashed IP address for privacy (GDPR compliant)',
    ip_country_code VARCHAR(2) DEFAULT NULL COMMENT 'ISO 3166-1 alpha-2 country code (e.g., US, GB)',
    ip_country_name VARCHAR(100) DEFAULT NULL COMMENT 'Full country name (e.g., United States)',
    user_agent LONGTEXT DEFAULT NULL COMMENT 'Browser user agent string',
    referrer VARCHAR(500) DEFAULT NULL COMMENT 'Referring URL',
    screen_resolution VARCHAR(20) DEFAULT NULL COMMENT 'Screen resolution (e.g., 1920x1080)',
    visited_at DATETIME NOT NULL COMMENT 'Visit timestamp',
    session_id VARCHAR(64) DEFAULT NULL COMMENT 'Session identifier for grouping visits',
    is_bot TINYINT(1) NOT NULL COMMENT 'Flag indicating if visitor is a bot',
    is_unique TINYINT(1) NOT NULL COMMENT 'Flag indicating if this is a unique visit',
    device_type VARCHAR(20) DEFAULT NULL COMMENT 'Device type: mobile, tablet, desktop',
    browser VARCHAR(50) DEFAULT NULL COMMENT 'Browser name: Chrome, Firefox, Safari, etc.',
    
    -- Foreign key constraint
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_page_visited (page_id, visited_at) COMMENT 'Primary query index for page stats by date',
    INDEX idx_fingerprint (visitor_fingerprint) COMMENT 'Index for checking duplicate visits',
    INDEX idx_visited_at (visited_at) COMMENT 'Index for date range queries',
    INDEX idx_country (ip_country_code) COMMENT 'Index for country-based analytics',
    INDEX idx_bot (is_bot) COMMENT 'Index for filtering bot traffic',
    INDEX idx_session (session_id) COMMENT 'Index for session-based queries',
    INDEX idx_unique (is_unique) COMMENT 'Index for unique visitor queries',
    INDEX idx_device (device_type),
    INDEX idx_browser (browser)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual visit records with geolocation and bot detection';

-- ============================================================
-- Seed default admin user
-- Username: admin
-- Password: admin123 (hashed with bcrypt)
-- ============================================================
INSERT INTO users (username, email, password, roles, created_at, is_active) 
VALUES (
    'admin',
    'admin@traffic-tracker.local',
    '$2y$13$QqKt7f5pN7XCwDLGZVZqm.3BmY2dV5x0gJ3bMZDQqXKm0cQqKWvZi',
    '["ROLE_USER", "ROLE_ADMIN"]',
    NOW(),
    TRUE
) ON DUPLICATE KEY UPDATE username=username;

-- ============================================================
-- Display success message
-- ============================================================
SELECT 'Database schema created successfully!' AS message;
SELECT 'Default admin user created: admin / admin123' AS credentials;
