-- 07  USEFUL QUERIES for later (run only the one you need).

-- Admin is locked out after 5 wrong passwords (15 minutes)? Clear the lock-out now:
DELETE FROM login_attempts;

-- See the last 20 admin logins:
SELECT username, login_date, login_time FROM login_history ORDER BY id DESC LIMIT 20;

-- Contact messages not answered yet:
SELECT id, full_name, phone, email, submitted_at FROM contact_form_submissions
WHERE remarks IS NULL OR remarks = '' ORDER BY submitted_at DESC;

-- News and activities added this month (the monthly limit counts these):
SELECT 'news' AS kind, COUNT(*) FROM news_events WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
UNION ALL SELECT 'activities', COUNT(*) FROM activities WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01');

-- Fee table saved by the admin goes wrong? Go back to the original fees (data/fees.php):
-- DELETE FROM site_settings WHERE name = 'fees';

-- Principal's message: go back to the original text and photo:
-- DELETE FROM site_settings WHERE name = 'principal';

-- Visitors per day, last 14 days:
SELECT visit_date, COUNT(*) AS visitors FROM visitors GROUP BY visit_date ORDER BY visit_date DESC LIMIT 14;
