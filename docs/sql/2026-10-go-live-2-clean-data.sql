-- =====================================================================================
-- GO-LIVE STEP 2 (optional, needs your sir's approval): the same data clean-up we did locally.
-- BEFORE: Export a backup of the live database. Keep it.
-- HOW: phpMyAdmin -> LIVE database -> SQL -> paste ONE part at a time -> Go.
-- =====================================================================================

-- PART A: see how many news posts are older than 1 July 2026 (changes nothing).
SELECT COUNT(*) AS old_news FROM news_events WHERE `date` < '2026-07-01';

-- PART B: keep a copy of those posts inside the database, then delete them from the website.
--         (The copy table news_events_before_2026_07 can be deleted after a few months.)
CREATE TABLE IF NOT EXISTS news_events_before_2026_07 AS SELECT * FROM news_events WHERE `date` < '2026-07-01';
DELETE FROM news_events WHERE `date` < '2026-07-01';

-- PART C: tidy text that older admin pages saved as "&amp;" etc.
--         Run docs/sql/2026-10-fix-encoded-text.sql (instructions inside it), twice.
