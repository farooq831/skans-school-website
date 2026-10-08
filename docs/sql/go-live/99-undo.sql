-- 99  UNDO (only if you go back to the OLD website for good and want the database exactly as before).
-- The old website works fine WITHOUT running this: the additions do not disturb it.
-- Best undo of all: Import the backup .sql you exported before going live.

-- Put back news removed by 04 (only if you ran 04):
-- INSERT INTO news_events SELECT * FROM news_events_before_2026_07
--   WHERE id NOT IN (SELECT id FROM (SELECT id FROM news_events) AS n);

-- Remove the additions (this also deletes all settings saved in the new admin panel):
-- DROP TABLE IF EXISTS site_settings;
-- DROP TABLE IF EXISTS login_attempts;
-- ALTER TABLE news_events DROP COLUMN created_at;
