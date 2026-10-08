-- 08  ACADEMIC PROGRAMS #1-#3 = the 3 level cards on the website (Early Years, Juniors, Seniors).
-- From October 2026 the website reads these cards from Admin > Academic Programs (entries #1, #2, #3).
-- This puts the CURRENT website text into those 3 entries once, so the cards look the same as now
-- and the admin edits the real text. Run ONCE. A copy of the old 3 entries is kept first.
-- Skip it if the admin has ALREADY rewritten entries #1-#3 on the live site.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS academic_prog_before_2026_10 AS SELECT * FROM academic_prog WHERE id BETWEEN 1 AND 3;

UPDATE academic_prog SET name = 'Early Years', heading = 'Playgroup to Class 2', subheading = 'Key objectives',
  description = 'Enhancing motor skills and sensory development through hands-on activities\nPromoting social interaction and effective communication in group settings\nIntroducing fundamental concepts like letters, numbers and basic math\nEncouraging creativity and emotional expression through art and play\nSupporting holistic cognitive, emotional and social growth of learners\nTeaching personal care habits like hygiene, dressing and self-care\nEnsuring safety awareness and creating a secure learning environment\nFostering strong parental involvement to support learning at home'
WHERE id = 1;

UPDATE academic_prog SET name = 'Juniors', heading = 'Class 3 to Class 5', subheading = 'Key objectives',
  description = 'Developing literacy and numeracy skills through interactive lessons and activities\nPromoting teamwork, collaboration and communication in group projects\nEncouraging problem-solving and analytical thinking across subjects\nFostering creativity and self-expression through writing, art and presentations\nSupporting emotional and social growth by building empathy and resilience\nInstilling healthy habits like organization, personal responsibility and time management\nEncouraging awareness of safety, respect and community in school settings'
WHERE id = 2;

UPDATE academic_prog SET name = 'Seniors', heading = 'Class 6 to Class 10 (Matric)', subheading = 'Key objectives',
  description = 'Enhancing subject mastery in core areas like mathematics, science and language arts\nEncouraging independent thinking, research and problem-solving across disciplines\nDeveloping effective communication and collaboration through debates, group work and presentations\nNurturing creativity and critical analysis in both academic and creative projects\nSupporting emotional and social maturity by promoting leadership, empathy and self-awareness\nTeaching effective study habits, time management and personal organization for academic success\nBuilding awareness of safety, ethics and responsible behavior in both school and society\nEncouraging active parental engagement to support academic goals and personal growth'
WHERE id = 3;

-- Undo: UPDATE academic_prog a JOIN academic_prog_before_2026_10 b ON a.id = b.id
--   SET a.name = b.name, a.heading = b.heading, a.subheading = b.subheading, a.description = b.description, a.image = b.image;
