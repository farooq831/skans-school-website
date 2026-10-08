-- GO-LIVE STEP 3: settings saved in the admin panel on the LOCAL copy (campus details, phones,
-- social links, monthly post limit). Copies them to the LIVE database so they need not be typed again.
-- Run AFTER step 1 (it needs the site_settings table). Safe to run more than once.
-- NOT included: the fee table (local copy had test values: session 2026-2029, a blank Playgroup
-- registration fee) and fees_history. The live site shows the original 2026-2027 fees from
-- data/fees.php until the admin changes them in Settings -> Fee structure.

SET NAMES utf8mb4;

INSERT INTO site_settings (name, value) VALUES ('address', '\"337 Street 17, Sector 1, Airport Employees CHS, Rawalpindi, Punjab 46000\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('campus_name', '\"AECHS Campus\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('classes_line', '\"Playgroup – Matric\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('email', '\"aechs.campus@skansschool.edu.pk\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('facebook', '\"https:\\/\\/www.facebook.com\\/p\\/SKANS-School-System-AECHS-Rawalpindi-100076253467677\\/\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('instagram', '\"https:\\/\\/www.instagram.com\\/skans_school_aechs\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('map_url', '\"https:\\/\\/maps.app.goo.gl\\/xgLhyvi7ufDhE6yb7\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('phone_mobile', '\"+92 333 5352965\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('phone_office', '\"+92 51 8468475\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('post_limit', '8')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('school_name', '\"SKANS School System\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('tiktok', '\"\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('whatsapp', '\"923335352965\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
INSERT INTO site_settings (name, value) VALUES ('youtube', '\"\"')
  ON DUPLICATE KEY UPDATE value = VALUES(value);
