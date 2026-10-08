# Replacing the old AECHS website with the new one

Follow the steps in order. Total time: about 2 hours the first time (most of it is testing).
Nothing is deleted from the database, and the old website can be put back within a minute (step 9).

---

## 0. What you need (from your sir)

| Item | Where you will use it |
|---|---|
| cPanel login | Everything below |
| Folder of the current site (e.g. `public_html/aechscampus`) and its web address | Steps 1, 4, 8 |
| Live database name, user and password (cPanel → MySQL Databases) | Step 4 (`config.php`) |
| Email login of `noreply@skansschool.edu.pk` (cPanel → Email Accounts) | Step 4 (`config.php`) |
| PHP version 8.0 or newer (cPanel → Select PHP Version / MultiPHP Manager) | Step 2 |
| Permission for a test subdomain, e.g. `new-aechs.skansschool.edu.pk` | Step 2 |
| Approval to go live, and the date | Step 8 |

## The files you take with you (from this computer)

| File | What it is |
|---|---|
| `_release/skans-website-v2.0.0-<date>.zip` | The new website code. Make a fresh one with `powershell -ExecutionPolicy Bypass -File docs\build-release.ps1` |
| `docs/deploy/sites.php.example` | Template: which web address shows which campus |
| `docs/deploy/config.php.example` | Template: database and email login, campus details |
| `docs/sql/2026-10-go-live-1-schema.sql` | Database additions (2 new tables, 1 new column) |
| `docs/sql/2026-10-go-live-2-clean-data.sql` | Optional: remove news from before July 2026 (keeps a copy) |
| `docs/sql/2026-10-fix-encoded-text.sql` | Optional: tidy text saved as `&amp;` by the old admin pages |
| `docs/sql/2026-10-go-live-3-settings.sql` | Campus details, phones, social links and post limit you set in the admin panel |

**What is NOT in the zip, and why:** `campus-data/` (passwords: you create it on the server), the pictures
`adminpanel/upload/images/` and student photos `uploads/` (they are already on the server), `_old/`, `docs/`, backups.

---

## 1. Back up the live site (never skip)

1. cPanel → **File Manager** → the current site folder → select all → **Compress** → Zip → download the zip.
2. cPanel → **phpMyAdmin** → click the live database → **Export** → Quick → SQL → **Go** → save the `.sql` file.
3. Keep both on your computer **and** in Google Drive. Name them with the date, e.g. `aechs-site-2026-10-20.zip`.

## 2. Make the test subdomain

1. cPanel → **Domains** (or Subdomains) → Create `new-aechs.skansschool.edu.pk`, document root e.g. `public_html/new-aechs`.
2. cPanel → **Select PHP Version** / **MultiPHP Manager** → set PHP **8.1 or 8.2** for it (and later for the main domain).
   Extensions that must be ticked: `mysqli`, `fileinfo`, `mbstring`, `zlib` (normally on), `gd` (recommended: shrinks big photos).
3. cPanel → **SSL/TLS Status** → select the subdomain → **Run AutoSSL**.

## 3. Upload the new code

1. File Manager → open `public_html/new-aechs` → **Upload** the zip → back → select it → **Extract**.
2. Delete the zip after extracting.
3. Copy the pictures from the old site into the new folder (File Manager → select → **Copy**):
   - old `adminpanel/upload/images/` → new `adminpanel/upload/images/`
   - old `uploads/` (student photos) → new `uploads/`

## 4. Create the campus settings (2 files, on the server only)

In File Manager, inside `public_html/new-aechs/campus-data/` (already there from the zip, with its `.htaccess`):

1. **New File** `sites.php` → paste `docs/deploy/sites.php.example` → change the addresses → Save.
   For the test, the line that matters is `'new-aechs.skansschool.edu.pk' => 'aechs'`.
2. **New Folder** `aechs` → inside it **New File** `config.php` → paste `docs/deploy/config.php.example` →
   fill in the database name, user, password and the email password → Save.
3. Check: open `https://new-aechs.skansschool.edu.pk/campus-data/aechs/config.php` in the browser.
   It must show **403 Forbidden**. If it shows anything else, stop and check `campus-data/.htaccess`.

### Which database?
- **Safest for testing:** a copy. cPanel → MySQL Databases → create `…_aechs_test`, add the user with ALL PRIVILEGES →
  phpMyAdmin → select it → **Import** the `.sql` from step 1. Put this name in `config.php` while testing,
  and the real database name when going live (step 8).
- The real database also works: the new code only adds to it. But test messages and posts would then be real.

## 5. Database changes (phpMyAdmin → the database → tab **SQL**)

Run in this order, one file at a time (paste → **Go**):

| # | File | Expect |
|---|---|---|
| 1 | `2026-10-go-live-1-schema.sql` | Green messages. If you see `#1060 Duplicate column name 'created_at'`, the column is already there: fine. The last line shows one row for `activities.created_at`. If it shows none, run the commented line under it. |
| 2 | `2026-10-go-live-3-settings.sql` | "14 rows affected" (or "28" when replacing). |
| 3 | *(optional, with approval)* `2026-10-go-live-2-clean-data.sql` | Part A shows how many old posts. Part B keeps a copy in `news_events_before_2026_07` and removes them from the site. |
| 4 | *(optional)* `2026-10-fix-encoded-text.sql` | Run twice, until every line says 0 rows. |

You can also skip #1: the new code creates the same tables/column itself the first time the admin login page
and dashboard open. Running it by hand just lets you see it happen.

## 6. Test on the subdomain (phone + computer)

**Website**
- [ ] Home: video plays, pop-up announcement shows and closes, facility icons, news cards, promo cards
- [ ] About (principal's message), Facilities ("Show more activities"), Faculty (cards open), News & Events (filters, "Show more")
- [ ] Contact: send the form → it appears in admin → Contact messages
- [ ] Admissions, Fee structure (session 2026–2027), Admission policy, Programs, Special programs, Policies (harassment PDF opens)
- [ ] Phone width: menu opens, nothing runs off the side

**Admin panel** (`/adminpanel/`, same username and password as before)
- [ ] Log in; dashboard shows numbers and charts
- [ ] Add a news post with a picture → shows on the site → delete it
- [ ] Edit an activity, a faculty member, an announcement
- [ ] Settings: campus details correct; Fee structure: check every amount, set the correct session; Principal's message
- [ ] Backups → **Back up now** → a file appears → Download works
- [ ] Log out → "Forgot password" → the email arrives and the link opens the reset page

Then send the test link to your sir and wait for his **OK**.

## 7. Before going live: last backup

Repeat step 1's database export (messages may have arrived since). If you tested on a copy, nothing else to do.

## 8. Go live

**Way A, recommended (switch the folder the domain points to):**
1. In `campus-data/aechs/config.php`: put the **real** database name (if you tested on a copy).
2. In `campus-data/sites.php`: make sure `aechscampus.skansschool.edu.pk` (and `www.` if used) → `'aechs'`.
3. Copy pictures again from the old folder to the new (only new files; any added since step 3).
4. cPanel → **Domains** → the main AECHS domain → **Manage** → change the document root to `public_html/new-aechs`.

**Way B (swap the folders):** rename the old folder to `aechscampus-old-2026-10`, rename `new-aechs` to the old name.

Then open the real address: Home, one news post, Contact form, admin login. Run AutoSSL if the padlock is missing.

## 9. If something goes wrong: undo

- Way A: point the domain back to the old folder. Way B: rename the folders back.
- The old site works with the database as it is now (only things were added).
- Worst case: phpMyAdmin → Import the backup `.sql` from step 1/7.

## 10. After going live

1. **Daily backup:** cPanel → **Cron Jobs** → Once Per Day → command (change the path):
   `/usr/local/bin/php /home/CPANELUSER/public_html/new-aechs/adminpanel/cron_backup.php --campus=aechs`
   (the dashboard also makes one automatically each day; this is the safety net)
2. Remove the test address line from `campus-data/sites.php` and delete the test subdomain if not needed.
3. Make sure no `db.php` with a password is left in the root of the old or new folder.
4. Keep the old folder 2–4 weeks, then download it once more and delete it.
5. Give the admin the PDF guide (`docs/SKANS-AECHS-Website-and-Admin-Guide.pdf`).

## Updating later (new versions)

1. On this computer: raise `SK_VERSION` in `includes/campus.php`, run `docs\build-release.ps1`.
2. Server: back up (step 1) → upload the zip into the site folder → **Extract** → let it overwrite.
   Never delete the site folder first: `campus-data/`, pictures and backups are not in the zip and must stay.
3. Open the admin dashboard once (applies any database additions), check the site.
