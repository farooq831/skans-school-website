# Website Audit: SKANS School System, AECHS Campus (Rawalpindi)

Audit date: 3 October 2026 · Scope: everything in `aechscampus/` (public site + `adminpanel/`) · Method: read-only code review.
Files with credentials (`db.php`, `adminpanel/db.php`, `email.php`) were **not opened**. Secrets are reported by file and line only.
Anything not found in the code is marked **[CONTENT NEEDED]**.

> Keep this file **out of the web folder** on the live server. It describes security holes that are not yet fixed.

---

## 1. Summary

1. The site is a re-skinned **Webflow template** (pastel colours, Crimson Pro font) with PHP + MySQL added. It does not use the SKANS navy/gold brand at all.
2. There **is** a real home page (`index.php`). Locally the site sits one folder too deep (`htdocs/aechscampus/aechscampus/`), so `localhost/aechscampus/` shows a folder list, not the site.
3. 11 public pages are linked from the menu; 5 more PHP pages are reachable by URL but linked from nowhere (old home, two online admission forms, contact handler, password reset).
4. **Security is the biggest problem.** Some admin files have no login check. One of them shows children's admission records (address, phone, allergies, health) to anyone who guesses a number in the URL.
5. Two public admission forms accept **any file type** as the "student picture". An attacker could upload a PHP program and take over the server.
6. Anyone who opens `adminpanel/hashpas.php` scrambles every admin password, which locks all admins out. `adminpanel/info.php` shows the server's full setup to the public.
7. The `&amp;` in titles happens because the admin panel turns `&` into `&amp;` **before saving**, then the page (or the next edit) does it again.
8. Content is uneven. Facilities, Programs and Special Programs have good copy. Policies (1 PDF), Vision/Mission (1 line each) and the Principal's message (no name) are thin. There is no Fee/Calendar/FAQ/Careers/Gallery page.
9. Contact details mostly match, but the "Email Us" link sends to a **misspelled address**. The mobile menu has a broken "Faculty" link and different labels from the desktop menu.
10. SEO basics are missing. Every page has the same title and description (which says "Junior Campus"), 4 pages have no `<h1>`, and there is no `sitemap.xml`, no `robots.txt` and no proper favicon. The site downloads about 14 MB of video on the home page.

---

## 2. Where the site lives (root URL)

| Location | What you get |
|---|---|
| `http://localhost/aechscampus/` | No `index.php` in this folder, so XAMPP will most likely show a **folder listing** with one item, `aechscampus/`. |
| `http://localhost/aechscampus/aechscampus/` | The real site. `index.php` is a **real home page**, not a redirect. |
| Live server | The live address appears to be `aechscampus.skansschool.edu.pk` (the password-reset email links there, `forgot_password.php:56`). The server error log mentions `public_html`, so `index.php` is served at the domain root. |
| `index-old.php` | An older copy of the home page. It is still public and linked from nowhere. |

---

## 3. All pages

Content level: **thin** = little real information · **ok** = usable but generic · **good** = specific and useful.

### Public pages (in the menu)

| Page | Purpose | Content level | Main problems |
|---|---|---|---|
| `index.php` (Home) | Hero video, admissions banner, 8 facility cards, academic programs (DB), latest 3 news (DB), 3 promo banners, 3 random faculty (DB) | ok | No `<h1>`. 13.8 MB video. The "Interactive Multimedia" card text is about **climate control** (line 404). News titles not escaped (515). Broken fallback image `default-image.jpg` (506). Splits programs by `*`, while the Programs page splits by `,`. |
| `about.php` | Vision, mission, core values, Principal's message, general copy, photo grid, 4 "how we meet needs" blocks | ok | Vision is one line ("Everyone deserves a GOOD life.", line 17). Core values have headings only, their text is commented out. Principal is unnamed ("Regards Principal", line 83). No `<h1>` (the Principal heading is an `<h4>` styled as h1). Missing closing `</div>` tags. First photo has no alt text. |
| `services.php` ("Facilities") | Facilities intro + services and co-curricular activities from DB | ok/good | All images have `alt="Service Image"` / `"Activity Image"`. DB text printed without escaping. Hover-only text cannot be read on phones (no hover on touch screens). |
| `admissions.php` | Subjects, age limits per class, fee list, 4-step procedure, printable form PDF | good | Fee list has **no session year, no "per month/once" label, no currency** (lines 104–108). The "Submit Online" button is commented out and an orphan "OR" is left behind (136). Cards are `<a>` links that go nowhere. Broken `<ul>` markup. |
| `departments.php` ("Academic Programs") | Programs from DB + 4 "responsible citizen" blocks | ok | Hover overlay is the only place program details appear (invisible on phones). Commented-out text mentions **"SKANS International Islamic School"**, Easter, Christmas and Native Studies (copied template). |
| `advanced-placement-program.php` ("Special Programs") | Intro + 8 program cards | good | **Robotics card repeats the Competitions text** word for word (line 95+). Intro says programs are for "Playgroup, Nursery, and Kindergarten" only, but the campus runs to Matric. Typo "future.and" (line 25). Unclosed `<p>`/`<div>` tags. Hero image has no alt text. |
| `faculty.php` | Leadership + teaching staff from DB | ok | No `<h1>`. Experience/education only show on hover. DB text not escaped. |
| `news-events.php` | Featured announcement, scrolling announcements (last 30 days), all news/events (DB) | ok | Shows **`&amp;`** in titles (see P0-7). Unclosed `<h2>` at line 59. Uses obsolete `<marquee>`. Fallback image `css/25.jpg` does not exist. Shows "No announcements in the last 30 days" whenever none were posted recently. |
| `contact-us.php` | Address + map, email, phones, Facebook/Instagram | good | **Email link goes to `aechcampus@…`** but the page shows `aechs.campus@…` (line 59). Instagram uses the Facebook icon (120). No embedded map. The contact form is commented out but its handler is still live. |
| `policies.php` ("SKANS Policies") | One card linking to `icons/hr.pdf` | thin | Only policy is the **staff** Harassment policy, which is not useful to parents. No `<h1>`. Six `<br>` used for spacing. Unclosed `<div>`. |

### Public pages (not in the menu, but reachable by URL)

| Page | Purpose | Content level | Main problems |
|---|---|---|---|
| `index-old.php` | Old home page | n/a | Duplicate content. Should be removed. |
| `onlineform.php` | Online admission form (sends email) | n/a | **Unsafe upload** (keeps the uploader's file extension). Error display on. Emails go to a hardcoded personal staff address. A commented-out password sits at line 63. |
| `onlineadmission.php` | Older online admission form | n/a | Includes `cpenal/db.php`, which **does not exist** (line 2). **Unsafe upload** using the original file name, which runs before the DB error. Error display on. |
| `contactus_form.php` | Saves contact messages to DB | n/a | Its form is commented out, but the handler still accepts posts. No validation, no spam protection. |
| `forgot_password.php`, `reset_password.php` | Admin password reset | n/a | Live in the **public** root, not in `adminpanel/`. "Back to login" points to non-existent `cpenal/login.php` (line 96). **Hardcoded mail password at line 46.** |

### Shared parts

| File | Role |
|---|---|
| `header.php` | DB include, visitor counter, `<head>` (same title/description for every page), desktop + mobile menu |
| `footer.php` | Footer links, email/phones, year, WhatsApp button + "typing" banner |
| `email.php` | Mail helper (not opened; secret at **line 20**) |
| `db.php` **and** `adminpanel/db.php` | Two DB connection files (not opened; credentials at **lines 6 and 10** in each). Pages use the admin one. |
| `vendor/` | Composer, with **PHPMailer** as the only library |

### Admin panel (`adminpanel/`)

| Area | Files |
|---|---|
| Login / logout / history | `login.php`, `logout.php`, `login_history.php`, `index.php` (redirect) |
| Dashboard + visitor stats | `admin_panel.php`, `visitors.php`, `admin_panel2.php` |
| News & Events | `events.php`, `add_news.php`, `edit_news_event.php`, `manage_news_events.php` |
| Announcements | `add_announcement.php`, `process_announcement.php`, `view_announcements.php`, `edit_announcement.php`, `update_announcement.php`, `delete_announcement.php` |
| Faculty | `staff.php`, `add_faculty.php`, `edit_faculty.php`, `manage_faculty.php`, `delete_faculty.php` |
| Facilities (services) + activities | `facilities.php`, `add_service.php`, `edit_service.php`, `Manage_services.php`, `delete_service.php`, `add_activity.php`, `edit_activity.php`, `manage_activities.php`, `delete_activity.php` |
| Academic programs | `academic_programs.php`, `add_classes.php`, `edit_classes.php`, `manage_classes.php` |
| Admissions + contact messages | `admission_page.php`, `admissionform.php`, `view_addmissions.php`, `fetch_admission.php`, `update_addmissionstatus.php`, `admission_summary.php`, `view_contactus.php`, `mark_as_replied.php`, `update_remarks.php` |
| Leftover tools (remove) | `hashpas.php`, `info.php`, `error_log` |

---

## 4. Prioritized problem list

### P0: broken or insecure (fix first)

| # | Problem (in plain words) | Where |
|---|---|---|
| P0-1 | **Children's private data is public.** `fetch_admission.php` has no login check, so anyone can open `…/adminpanel/fetch_admission.php?id=1`, `?id=2`… and read name, date of birth, parents, address, mobile, allergies and health issues. It also builds SQL straight from the URL (SQL injection). | `adminpanel/fetch_admission.php:1-7` |
| P0-2 | **File uploads can take over the server.** Both public admission forms save the "student picture" without checking it is an image, keeping the uploader's extension. A file named `x.php` would be saved in `uploads/` and could then be run as a program. `onlineadmission.php` even uses the original file name. | `onlineform.php:29-35`, `onlineadmission.php:25-46` |
| P0-3 | **One visit locks out all admins.** `hashpas.php` has no login check and re-hashes every stored password each time it is opened, so nobody can log in afterwards. | `adminpanel/hashpas.php` |
| P0-4 | **Server details exposed.** `info.php` runs `phpinfo()` publicly (PHP version, paths, settings). `adminpanel/error_log` is also downloadable and leaks server paths. | `adminpanel/info.php`, `adminpanel/error_log` |
| P0-5 | **Admin actions without login.** These admin files skip the login check: `edit_classes.php` (anyone can change Academic Programs and upload files), `update_addmissionstatus.php`, `update_remarks.php`, `mark_as_replied.php`, `add_announcement.php` (page only; its save step does check). | `adminpanel/` |
| P0-6 | **SQL built from user input** (someone could change the database through the URL or form). | `adminpanel/delete_faculty.php:14`, `edit_announcement.php:16`, `view_addmissions.php:15`, `update_addmissionstatus.php:16`, `fetch_admission.php:6` |
| P0-7 | **`&amp;` in titles.** Admin "add" and "edit" pages run `htmlspecialchars()` on input **before saving** (`add_news.php:17`, `edit_news_event.php:28`, `process_announcement.php:12`, `update_announcement.php:18`, also faculty, services, activities and classes). So "News & Events" is stored as `News &amp; Events`. **Announcements** are escaped again on output (`news-events.php:100`), so they show `&amp;` at once. **News** titles are printed raw (`news-events.php:180`, `index.php:515`), so they look fine until someone edits them: the edit form escapes the stored value again (`edit_news_event.php:142`), so it becomes `&amp;amp;` and shows as `&amp;`. **Fix:** save raw text, escape only when printing, and clean existing rows once. | see lines |
| P0-8 | **Hardcoded secrets in code.** Rotate these passwords and move them out of code. | `forgot_password.php:46`, `email.php:20`, `onlineform.php:63` (commented), `db.php:6,10`, `adminpanel/db.php:6,10` |
| P0-9 | **Error messages shown to visitors** (`display_errors` on), which reveals paths and SQL errors. Pages also `die("Connection failed: …")` with the DB error text. | `onlineform.php:10`, `onlineadmission.php:5`, `email.php:8`, `index.php:425` etc. |
| P0-10 | **Weak admin login.** No limit on password attempts, no CAPTCHA, the session ID is not renewed after login, and different messages for "Invalid username" / "Invalid password" let attackers learn real usernames. | `adminpanel/login.php:39-42` |
| P0-11 | **Stored XSS in the admin panel.** Text typed by the public (admission and contact forms) is shown to admins unescaped. A visitor could plant a script that runs when staff open the list. | `adminpanel/view_addmissions.php:69-74`, `fetch_admission.php:11-21` |
| P0-12 | **Broken links:** mobile menu "Faculty" → `safe-schools-program.php` (does not exist); "Email Us" → wrong address; "Back to login" → `cpenal/login.php`. | `header.php:126`, `contact-us.php:59`, `forgot_password.php:96` |
| P0-13 | **Deletes happen by plain link** (GET request) with no confirmation token (CSRF). A logged-in admin who clicks a crafted link could delete records without meaning to. | `manage_news_events.php`, `delete_*.php` |

### P1: missing content or structure

| # | Problem | Where |
|---|---|---|
| P1-1 | No brand: SKANS navy `#192A4A` and gold `#BD852C` appear **nowhere**. The palette is the template's pastels (`--accent: #73400d`, blue `#1a086d` wordmark, bright blue `rgba(4,0,255,.7)` overlays). Fonts are Crimson Pro / Almarai, not Times New Roman. There is no logo image in the header, only text. | `css/main.css:226-245`, `css/mycss.css:15,32,89` |
| P1-2 | Every page has the same `<title>SKANS SCHOOL SYSTEM</title>` and a description saying "Junior Campus". | `header.php:33-36` |
| P1-3 | Missing `<h1>` on Home, About, Faculty, Policies. News page has an unclosed `<h2>`. | see table |
| P1-4 | No `sitemap.xml`, no `robots.txt`, no `.htaccess` (so no custom 404 page, and the upload folders may be listable). | site root |
| P1-5 | Thin pages: Policies (1 staff PDF), Vision/Mission, Core Values (headings only), unnamed Principal. | `policies.php`, `about.php` |
| P1-6 | Missing pages parents expect: Fee Structure (dated), Admission Policy, Academic Calendar, FAQs, Careers, Gallery, 404. | n/a |
| P1-7 | Desktop and mobile menus differ: "Facilities" vs "Services", "Academic Programs" vs "Departments". Mobile has no "SKANS Policies" and marks "Services" as the current page on every page. The mobile "Academics" dropdown link is `#`. | `header.php:68-130` |
| P1-8 | Orphan and duplicate pages: `index-old.php`, `onlineadmission.php`, `onlineform.php`, `contactus_form.php`, and two `db.php` files. | root |
| P1-9 | Home page loads a **13.8 MB video**. Unused images in `css/` alone total about 15 MB (e.g. `cmff.jpg` 6.4 MB, `cmp.jpg` 3.1 MB, `rrr.jpg` 2.2 MB). Used images are often 1–2.5 MB. The favicon is a **1 MB PNG** labelled as an `.ico`. `adminpanel/upload/images/` holds 650 files (161 MB). | `video/`, `css/`, `icons/` |
| P1-10 | JavaScript error on every page: `WebFont.load(...)` runs, but the WebFont script is commented out. | `header.php:44-45` |
| P1-11 | "Read more" details for programs, faculty and news descriptions only appear on **mouse hover**, so phone users never see them. | `css/mycss.css:18-20,49-51,102-104` |
| P1-12 | `icons/About Page Content.pdf` is public and contains text about **"SKANS International Islamic School, Early Years, Westridge Campus"**, which is another campus. A CSS comment also mentions "Westridge EYE Campus". | `icons/`, `css/main.css:2274` |
| P1-13 | Contact form has no validation (no email format check, no length limit) and **no spam protection** (no CAPTCHA, honeypot or rate limit). Errors are stored in the session "for debugging". | `contactus_form.php` |
| P1-14 | Admin uploads are capped at 500 KB for news but 5 MB for everything else. Nothing resizes images, which is why huge photos reach the public pages. | `adminpanel/add_*.php` |

### P2: polish

| # | Problem | Where |
|---|---|---|
| P2-1 | **WhatsApp button and bubble.** The "Hi, We are here to assist." bubble is fixed `90px` from the right with `white-space: nowrap` and no max width. On phones about 360 px wide or narrower it runs off the left edge and gets **cut off**. The round button holds an inline image, so the line-height gap makes it slightly taller than wide (not a perfect circle), and the green "wave" shadow extends past it. The bubble also overlaps footer text. (Likely causes: please confirm on a real phone.) | `footer.php:207-242` |
| P2-2 | Phone layout risks: `banner-heading` is 43 px even on small phones, so long words overflow. Sticky banners at `top:158px` stack and overlap while scrolling. Fixed heights (`images-grid-image-1` 560 px, `large-parallax-image` 800 px, internal hero 720 px on tablets) crop photos badly. `.hero-section-content-block.home` has `min-width:560px` on desktop. | `css/main.css:2383-2387, 2716-2720, 2736-2742, 3148-3153, 3318-3322, 3719-3726, 4114` |
| P2-3 | Images without useful alt text: about 8 on Home, 7 on About, 6 on Contact, 2 on Programs, 2 on Special Programs (many are icons with `alt=""`, which is fine for pure decoration). | per page |
| P2-4 | Stale text: mobile menu footer "© SKANS 2024" (`header.php:134`). The main footer year is automatic. Commented-out Feb 2025 job advert for a different SKANS entity is left in `index.php:162-223`. | |
| P2-5 | Mixed name spellings (see section 5). | |
| P2-6 | Inline styles everywhere (news page, WhatsApp, admissions cards). The CSS file is 4,368 lines of Webflow output, mostly unused. `bg-light-yellow` is defined twice, and the second one turns it into lime. | `css/main.css:3227-3232` |
| P2-7 | Obsolete or odd markup: `<marquee>`, `<h7>`, `<p>` wrapping `<ul>`/`<div>`, many `<br>` for spacing. | |
| P2-8 | Admin panel pages all have `<title>SKANS</title>`, load Chart.js on every page, use the retired StackPath/MaxCDN Bootstrap links, and are not marked `noindex`. | `adminpanel/*.php` |
| P2-9 | The DB connection is opened up to 4 times per page (`index.php` includes `db.php` in `header.php` plus three more times). | `index.php:421,489,589` |

---

## 5. Content inconsistencies

| Item | Variants found | Notes |
|---|---|---|
| School name | "SKANS School System", "SKANS SCHOOL SYSTEM", "Skans school system Junior Campus" (meta), "SKANS School", "Skans Policies", "SKANS AECHS Campus", "SKANS School System - AECHS Campus", "SKANS International Islamic School" (comments + PDF), "Westridge" (PDF + CSS comment) | Pick one official form. |
| Email | Displayed: `aechs.campus@skansschool.edu.pk` (footer, contact). Link: `aechcampus@skansschool.edu.pk` (contact page mailto) | The link is wrong. |
| Phones | `+9251-8468475`, `+92-333-5352965` (consistent). WhatsApp `923335352965` matches the mobile. | Format `+92 51 8468475` is clearer. The "Call Now" link only dials the mobile. |
| Address | Only on the contact page: "337 Street 17, Sector 1, Airport Employees CHS, Rawalpindi, Punjab 46000". The PDF says "heart of Rawalpindi Cantt" (other campus). | Add the address to the footer. |
| Classes offered | Header: "Playgroup – Matric". Admissions: Playgroup → Class 10. Special Programs: "Playgroup, Nursery, and Kindergarten". Meta: "Junior Campus". | Confirm the real range. |
| Menu labels | Facilities/Services, Academic Programs/Departments, Special Programs | Keep one set. |
| Program details | Home splits DB text by `*`, Programs page by `,` | The same record shows differently on the two pages. |

---

## 6. Keep list (worth reusing)

- **Contact details:** address, 2 phones, `aechs.campus@` email, Google Maps link, Facebook and Instagram pages, WhatsApp number.
- **Nav labels** (desktop set): About, Facilities, Admissions, Academics (Academic Programs, Special Programs, Faculty), News & Events, Contact Us, SKANS Policies.
- **Good copy:** the 8 facility cards (Home), "A Well-Rounded Education / Academics and Extra-Curricular / Success For All / Individual Attention" (About), the 7 genuine Special Programs (STEM, Montessori, EPL, Leadership, Phonics for Mothers, Competitions, Math Olympiad), the "Responsible and Active Citizen" blocks, and the admission procedure steps.
- **Admissions data:** subjects list, age limits per class, fee items (need a date), printable `icons/form.pdf`.
- **Database content:** news/events with categories, announcements, faculty (name, position, experience, education, photo), services, activities, academic programs.
- **Photos:** campus photos in `css/` (`cmf.jpg`, `cmfff.jpg`, `and.jpeg`, `df.jpeg`, `spl.jpeg`), the home hero video and its poster `video/firstpage.jpg`, and about 650 event photos in `adminpanel/upload/images/` (resize them first).
- **Policy:** `icons/hr.pdf` (Harassment Committee policy, about 1,600 words; staff-facing).
- **Logo:** `icons/logo.png` (currently used only as the favicon; 1 MB, needs a small version).
- **Admin features:** manage news/events, announcements, faculty, facilities (services), co-curricular activities, academic programs; review admission forms with status and remarks; contact messages with "replied" and remarks; daily/total visitor counter with a 10-day chart; login history; password reset by email.

---

## 7. Proposed sitemap

| Current page | Proposed page | Notes |
|---|---|---|
| `index.php` | **Home** | Real home page: hero, short welcome, key numbers [CONTENT NEEDED], admissions call to action, latest news, quick links. Add `<h1>`. Replace the video with an image or a much smaller video. |
| `about.php` | **About** (Vision & Mission, Principal's Message, Core Values) | Fill thin sections. |
| `departments.php` | **Programs** (Pre-school, Primary, Middle, Matric) | One clear section per stage, visible without hover. |
| `advanced-placement-program.php` | **Special Programs** | Fix the Robotics text and the intro. |
| `services.php` | **Facilities** | Rename the file to match the label. |
| `faculty.php` | **Faculty** | Add `<h1>`, show details without hover. |
| `admissions.php` | **Admissions** (procedure, age limits, form) | Link out to the new pages below. |
| n/a | **Fee Structure** (new) | Dated, with frequency and currency. |
| n/a | **Admission Policy** (new) | Eligibility, documents, test, sibling and transfer rules. |
| `news-events.php` | **News & Events** | Fix `&amp;`, remove `<marquee>`. |
| n/a | **Academic Calendar** (new) | Terms, holidays, exams, PTMs. |
| n/a | **Gallery** (new) | Reuse resized event photos. |
| n/a | **FAQs** (new) | Timings, transport, uniform, fees, etc. |
| n/a | **Careers** (new) | Open posts and how to apply (replaces the old popup). |
| `policies.php` | **Policies** | Parent-facing policies + the existing HR PDF. |
| `contact-us.php` | **Contact Us** | Fix the email link, add an embedded map, re-enable a safe form. |
| n/a | **404 page** (new) | Friendly "page not found". |
| `index-old.php`, `onlineadmission.php` | **Remove** | Old or broken. |
| `onlineform.php` | **Online Admission** (only after fixing P0-2) | Or keep the PDF only. |
| `forgot_password.php`, `reset_password.php` | Move into `adminpanel/` | Not public pages. |
| n/a | `sitemap.xml`, `robots.txt`, favicon set | SEO basics. |

---

## 8. Content gaps

| Page | What is missing | Who provides it |
|---|---|---|
| Home | Key facts (founding year, students, teachers, results) [CONTENT NEEDED]; one welcome paragraph; fix the "Interactive Multimedia" card text | SKANS (facts), developer (layout) |
| About | Full vision and mission; text for the 2 core values (or the full list); Principal's name, photo and signed message; campus history [CONTENT NEEDED] | SKANS |
| Programs | Curriculum/board per stage (e.g. which Matric board) [CONTENT NEEDED]; one paragraph per stage | SKANS |
| Special Programs | Real Robotics description; corrected intro (which classes) | SKANS (facts), developer (edit) |
| Faculty | Up-to-date staff list and photos; permission to publish | SKANS |
| Fee Structure | Session year, frequency (monthly/annual/one-time), currency, sibling/other discounts, payment method, whether "Online Fee" (commented link to skans.pk) should return [CONTENT NEEDED] | SKANS |
| Admission Policy | Eligibility, required documents, test details, admission dates, withdrawal/refund rules [CONTENT NEEDED] | SKANS |
| Academic Calendar | Session dates, holidays, exam weeks, PTMs [CONTENT NEEDED] | SKANS |
| FAQs | Common parent questions and answers (timings, transport, uniform, books, canteen) [CONTENT NEEDED] | SKANS |
| Careers | Current vacancies, how to apply, HR email [CONTENT NEEDED] | SKANS |
| Gallery | Selection of approved photos with captions | SKANS (choose), developer (resize, build) |
| Policies | Parent-facing policies (attendance, discipline, fee, anti-bullying, privacy) [CONTENT NEEDED] | SKANS |
| Contact | Office hours [CONTENT NEEDED]; confirm the correct email | SKANS |
| Brand | Logo files (SVG/PNG, small sizes), confirmation of navy/gold/Times New Roman rules | SKANS |
| All pages | Unique page titles and descriptions, alt text, `<h1>` | Developer |
| Security fixes (P0) | All items in section 4 P0 | Developer |

---

## 9. Questions for you

1. Which site is live right now: the files in this folder, or a newer copy on the server? (Some uploads are dated after this code.)
2. Is `aechscampus.skansschool.edu.pk` the correct live address?
3. Which email is correct: `aechs.campus@` or `aechcampus@`? Who should receive form emails? (A personal staff address is hardcoded now.)
4. Does the campus really teach up to Matric/Class 10? Which board (Federal / Punjab / other)?
5. Are the fees on the Admissions page current? For which session, and are they monthly or one-time?
6. Do you want online admission back on the website, or only the printable PDF?
7. Should the contact form come back (with spam protection), or are phone and WhatsApp enough?
8. What is the Principal's name, and can we publish a photo?
9. What are the full vision, mission and core values (with text)?
10. Is "Eyeris Room" the correct name? What should the "Interactive Multimedia" card say?
11. Which policies can be published for parents? Should the staff Harassment policy stay public?
12. Can I delete `index-old.php`, `onlineadmission.php`, `hashpas.php`, `info.php` and `About Page Content.pdf` (Westridge text) when we move to fixing?
13. Who has admin access today, and have the admin and email passwords been shared outside the team? (They should be changed either way.)
14. Should the "Online Fee" link to skans.pk (commented out in the menu) return?
15. Do you have official logo files and brand guidelines (navy `#192A4A`, gold `#BD852C`, Times New Roman wordmark) to work from?
16. Is the home-page video required, or can we use a still photo to make the page load faster?
