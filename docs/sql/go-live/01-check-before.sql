-- 01  CHECK BEFORE (changes nothing). Run first and keep a screenshot of the results.
-- phpMyAdmin -> click the LIVE database -> tab SQL -> paste -> Go.

-- Tables that exist now (the new site adds site_settings and login_attempts if missing):
SHOW TABLES;

-- How many rows each main table has (compare with 06-check-after.sql):
SELECT 'news_events' AS tbl, COUNT(*) AS total_rows FROM news_events
UNION ALL SELECT 'activities', COUNT(*) FROM activities
UNION ALL SELECT 'announcements', COUNT(*) FROM announcements
UNION ALL SELECT 'faculty', COUNT(*) FROM faculty
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'contact_form_submissions', COUNT(*) FROM contact_form_submissions
UNION ALL SELECT 'users', COUNT(*) FROM users;

-- Columns the new site needs: news_events.created_at (added in 02) and activities.created_at (must exist).
SHOW COLUMNS FROM news_events LIKE 'created_at';
SHOW COLUMNS FROM activities LIKE 'created_at';
