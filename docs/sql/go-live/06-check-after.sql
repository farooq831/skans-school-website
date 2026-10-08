-- 06  CHECK AFTER (changes nothing). Run after 02 and 03 (and after opening the new admin dashboard once).

-- Both must be listed:
SHOW TABLES LIKE 'site_settings';
SHOW TABLES LIKE 'login_attempts';

-- Must show one row each:
SHOW COLUMNS FROM news_events LIKE 'created_at';
SHOW COLUMNS FROM activities LIKE 'created_at';

-- Your settings (14 rows after 03; more after the admin saves fees, policy or principal's message):
SELECT name, LEFT(value, 60) AS value, updated_at FROM site_settings ORDER BY name;

-- Row counts: must be the SAME as in 01 (news_events smaller only if you ran 04):
SELECT 'news_events' AS tbl, COUNT(*) AS total_rows FROM news_events
UNION ALL SELECT 'activities', COUNT(*) FROM activities
UNION ALL SELECT 'announcements', COUNT(*) FROM announcements
UNION ALL SELECT 'faculty', COUNT(*) FROM faculty
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'contact_form_submissions', COUNT(*) FROM contact_form_submissions
UNION ALL SELECT 'users', COUNT(*) FROM users;

-- Admin login still there (shows usernames only, no passwords):
SELECT id, username, email FROM users;
