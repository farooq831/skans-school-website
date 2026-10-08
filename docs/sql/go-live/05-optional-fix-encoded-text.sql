-- 05  OPTIONAL: tidy text saved as &amp; by the old admin pages. Run it TWICE.
-- One-time clean-up of text that older admin pages saved already HTML-encoded
-- (e.g. "Sports &amp;amp; Spirit"). The website already DISPLAYS these correctly
-- since October 2026, so this only tidies the stored data and the admin edit forms.
--
-- 1. BACK UP THE DATABASE FIRST (phpMyAdmin -> Export -> SQL).
-- 2. Run this whole file in phpMyAdmin -> SQL.
-- 3. Run it a second time: some rows were encoded twice. When "0 rows affected"
--    appears for every statement, it is finished.

UPDATE news_events SET
  title       = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title,       '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description, '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&')
WHERE title LIKE '%&%;%' OR description LIKE '%&%;%';

UPDATE announcements SET
  heading           = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(heading,           '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  announcement_text = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(announcement_text, '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&')
WHERE heading LIKE '%&%;%' OR announcement_text LIKE '%&%;%';

UPDATE faculty SET
  name           = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,           '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  position       = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(position,       '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  experience     = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(experience,     '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  qualifications = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(qualifications, '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&')
WHERE CONCAT_WS(' ', name, position, experience, qualifications) LIKE '%&%;%';

UPDATE services SET
  title       = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title,       '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description, '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&')
WHERE title LIKE '%&%;%' OR description LIKE '%&%;%';

UPDATE activities SET
  title       = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title,       '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description, '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&')
WHERE title LIKE '%&%;%' OR description LIKE '%&%;%';

UPDATE academic_prog SET
  heading     = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(heading,     '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  subheading  = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(subheading,  '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  name        = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,        '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description, '&quot;','"'),'&#039;',''''),'&lt;','<'),'&gt;','>'),'&amp;','&')
WHERE CONCAT_WS(' ', heading, subheading, name, description) LIKE '%&%;%';
