-- 02  DATABASE ADDITIONS (required). Same file as docs/sql/2026-10-go-live-1-schema.sql.
-- =====================================================================================
-- GO-LIVE STEP 1: database changes needed by the new website (October 2026).
-- Only ADDS things. No table, column or row is deleted or changed.
--
-- You may not even need this file: the new code makes these changes by itself the first time
-- the admin login page and the admin dashboard open. Run it by hand if you want to see exactly
-- what changes, or if the database user has no right to CREATE/ALTER from PHP.
--
-- HOW: phpMyAdmin -> click the LIVE database on the left -> tab "SQL" -> paste this file -> Go.
-- BEFORE: Export a backup (phpMyAdmin -> Export -> Quick -> SQL -> Go).
-- =====================================================================================

SET NAMES utf8mb4;

-- 1. Settings the admin can change (campus details, fees, admission policy, principal's message).
CREATE TABLE IF NOT EXISTS site_settings (
    name VARCHAR(64) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4;

-- 2. Wrong-password lock-out (5 tries in 15 minutes).
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    INDEX (username, ip_address, attempted_at)
);

-- 3. When a news post was ADDED (for the 8-posts-a-month limit). Old posts stay empty (not counted).
--    If you see "#1060 - Duplicate column name 'created_at'", it is already there: that is fine.
ALTER TABLE news_events ADD COLUMN created_at DATETIME NULL DEFAULT NULL;

-- 4. CHECK ONLY (changes nothing): activities must have a created_at column.
--    The result should show one row. If it shows NO row, run the line under it (remove the "-- ").
SHOW COLUMNS FROM activities LIKE 'created_at';
-- ALTER TABLE activities ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
