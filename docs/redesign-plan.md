# Redesign Plan: SKANS School System, AECHS Campus

Date: 3 October 2026 · Based on: `docs/audit.md` (same folder) · Status: **plan only, no site file has been changed yet.**

**Goal:** a professional, elegant, trustworthy school website that looks the same on phones, tablets and desktops, with nothing overlapping or cut off.

### Decisions already made (3 Oct 2026)

| Topic | Decision |
|---|---|
| Logo | ~~Ask SKANS for official logo files~~. **Done:** SKANS supplied `icons/aechs_logo.png` (navy + gold, transparent background) on 3 Oct 2026. |
| Replaced files | Move to `_old/` inside the site folder. `_old/` gets an `.htaccess` "deny all" so nothing in it can be opened from a browser, and it is **never uploaded** to the live server. |
| "Book a visit" | A short visit-request form saved to the database and listed in the admin panel, with spam protection. |
| "Apply Now" | Goes to the Admissions page (steps, age limits, printable form PDF). Both online admission forms are retired to `_old/` for now. |

### Progress log

**3 Oct 2026, built:**

- **Design base:** `assets/css/site.css` (all classes start with `sk-` so they cannot clash with the old `css/main.css` that un-rebuilt pages still load), `assets/js/site.js`, `includes/` (bootstrap, helpers, header, footer), `data/site.php`, `data/fees.php`.
- **New logo** `icons/aechs_logo.png`, resized to `assets/img/skans-logo.png` (25 KB). New favicons.
- **Pages:**
  - Admissions: Admission Process (`admissions.php`), Fee Structure (`fee-structure.php`), Admission Policy (`admission-policy.php`), Admission Form (`admission-form.php`).
  - Academics: Programs Offered (`departments.php`), Special Programs (`advanced-placement-program.php`).
  - SKANS Policies (`policies.php`).
- **Downloads:** `downloads/` holds the Playgroup–Class 7 form as PDF + Word (from `docs/Admission Form Pg-G-7 …June 2026.docx`).
- **Old pages** (Home, About, Facilities, Faculty, News, Contact) now use the new header and footer; their content is not rebuilt yet.
- **All P0 security fixes** are done (see section 6). One-time data clean-up script: `docs/sql/2026-10-fix-encoded-text.sql` (to be run by you after a backup).

**Decisions made on 3 Oct 2026:**

| Topic | Decision |
|---|---|
| Fees | Taken from the SKANS fee table. Tuition Rs. 16,000/month for all classes. Lab charges Rs. 700/month for Class 9–10. Session year still **[CONTENT NEEDED]**. |
| Levels | Early Years (PG–KG), Juniors (Grade 1–5), Middle (Grade 6–7, objectives **[CONTENT NEEDED]**), Seniors (Pre 9–Class 10). |
| Activity posts | Activity posts in the "Academic Programs" admin table show as "Recent class activities" on Programs Offered. |
| Pre 9–Class 10 form | SKANS will upload it later (marker shown). |
| Build order | Pages first, then security (both done). |
| Subjects | From the two SKANS course sheets in `docs/` (GXS Course .xlsx). Playgroup to Class 10 only; FA/FSc/ICS, IGCSE and A Levels left out. Shown as "Subjects by class" on Programs Offered (3 Oct 2026). |
| Session, board, Robotics | Session 2026–2027; Matric board FBISE; Robotics text supplied by SKANS (same wording as the Competitions card). |
| Middle objectives | Written by the developer from the Juniors/Seniors objectives and the Class 6–7 subjects, at SKANS's request (3 Oct 2026). |
| Logo | Header uses `icons/aechs_logo_white.jpg` (white background), pre-sized to 50/60/76 px tall plus 2x/3x copies in `assets/img/skans-logo-*.png`. |
| Contact page | Rebuilt with a contact form (saved to `contact_form_submissions`, shown in admin). Spam protection: hidden field, 3-second minimum, form token, 3 per visitor per hour, 2 per email per 10 minutes (3 Oct 2026). |
| Levels (changed) | 3 levels: Early Years (Playgroup–Class 2), Juniors (Class 3–5), Seniors (Class 6–10). Middle level removed; old version in `_old/data-site-4-levels.php`. Heading "Academic Programs", cards side by side. |
| Re-admission | Not shown on the website (data kept in `data/fees.php`, switch `readmission_public`). |
| Admission form | Not offered online for now: menu, tabs, footer and buttons removed, `admission-form.php` redirects to Admissions, `downloads/` blocked. To restore: `forms_public => true` in `data/fees.php` and delete `downloads/.htaccess`. |
| About page | Rebuilt: Principal's message (text unchanged) → Core Values → Vision & Mission → rest of the old page. Old page: `_old/about-legacy.php`. |
| Spacing | Section padding reduced to 32 px (phone) / 48 px (larger screens). |
| Programs page | Level cards on a navy band, always side by side (one row of three from 768px; swipe row on phones). "Subjects by class" section removed from the page; subject data kept in `data/site.php`. |
| Home page | "Join Our Welcoming Community" banner: Admission + Fee Structure buttons. "Academic Programs" replaced by the same section as the Programs page (`includes/levels-section.php`, shared by both). Old home: `_old/index-before-levels.php`. |
| Header button | "Apply Now" renamed "Enroll Now": navy background (logo colour) with gold text (old button colour), contrast 6.4:1. |
| Class activities | "Recent class activities" removed from Programs Offered (activity posts stay in the database). |
| News clean-up | 325 news/events dated before 1 Jul 2026 deleted from the LOCAL database; 33 kept (25 Aug–21 Sep 2026). Restore file: `docs/sql/backup-news-events-before-2026-07-01.sql`. Photo files left in `adminpanel/upload/images/`. Live database not changed yet. |
| Announcements | News & Events page rebuilt (old: `_old/news-events-legacy.php`). Website shows the 6 newest announcements; newest featured. Every picture shown whole (object-fit: contain on a blurred copy) - never cropped or stretched. Admin: new Add/Edit pages with live preview and real file-type check (`adminpanel/announcement_form.php`, `image_upload.php`); list with thumbnails and Featured/On website labels. `process_announcement.php` and `update_announcement.php` retired to `_old/adminpanel/oct3/`. |
| Announcement expiry | Each announcement leaves the website automatically 30 days after it was added (`announcement_days` in `data/site.php`); it stays saved in the admin panel, labelled "Not on website". "Ask a question" button removed from the featured announcement. |
| Announcement pop-up | Home page shows the latest current announcement in a pop-up shortly after loading (`includes/announcement-popup.php`); closes with ✕, Esc, Close button or a click outside; shown once per visit per announcement; nothing shown when no announcement is from the last 30 days. Picture always shown whole (object-fit: contain on a blurred copy). Shared lookup: `includes/announcements.php` (also used by News & Events). |
| Faculty | Faculty page rebuilt (old: `_old/faculty-legacy.php`). Hover overlay replaced by a round arrow button on each photo that opens a details card over the photo (experience, education); works by click, tap and keyboard, Escape closes. Same card on the Home page "Our Faculty" (3 random). Shared code: `includes/faculty-card.php`. |
| Facilities | Facilities page rebuilt (old: `_old/services-legacy.php`): intro with smaller heading, text beside photo; facilities and co-curricular activities use the same arrow cards as faculty (`includes/photo-card.php`). Portrait/square photos fill the card; wide photos shown whole on a blur. Activities show 12 at a time ("Show more"). |
| Description limit | Descriptions of news/events, activities and announcements limited to 500 characters (`DESCRIPTION_MAX` in `adminpanel/session.php`): text box limit, live counter (`adminpanel/char_limit.php`) and server check. Existing longer entries must be shortened when next edited. |
| Browser cache | CSS/JS links carry `?v=<file time>` so browsers always load the latest version. |

### Still open (I will not assume these. See section 9.)

Program level grouping · whether current fee figures stay visible · hero photo vs video · contact form · visit-request email recipient · visiting hours. Each one is marked **[CONFIRM]** where it appears.

### Markers used in this plan

- **[CONTENT NEEDED]**: SKANS must supply the text or number. The page shows a visible marker until it arrives.
- **[CONFIRM]**: you need to approve the choice before it is built.

---

## 1. Design system

### 1.1 Colours (CSS variables)

The logo is **indigo (about `#3B3186`)**, not navy. The plan uses the brand navy you specified. When SKANS sends official files, check that the logo colour matches; if not, we keep navy for the site and place the logo on a white background.

| Token | Value | Use |
|---|---|---|
| `--navy` | `#192A4A` | Header text, headings, primary buttons, footer background |
| `--navy-700` | `#22365C` | Hover on navy |
| `--gold` | `#BD852C` | **Decoration only:** rules under headings, borders, icons, large text on navy |
| `--gold-light` | `#D4A65A` | "Apply Now" button background (navy text), small gold text on navy |
| `--gold-dark` | `#8A5F1C` | Small gold text (eyebrows, links) on light backgrounds |
| `--bg` | `#FAF7F2` | Page background (warm off-white) |
| `--surface` | `#FFFFFF` | Cards, tables, forms |
| `--sand` | `#F3EBDD` | Alternate section background (replaces the pastel blocks) |
| `--text` | `#192A4A` | Body text |
| `--muted` | `#5B6475` | Secondary text (dates, captions) |
| `--border` | `#E4DCCD` | Card and table borders |
| `--error` / `--success` | `#A12A2A` / `#1F6B3A` | Form messages (check contrast when built) |

**Contrast check (WCAG AA: 4.5:1 for normal text, 3:1 for large text):**

| Pair | Ratio | Allowed use |
|---|---|---|
| Navy on off-white | 13.4 | All text ✅ |
| White on navy | 14.3 | All text ✅ |
| Navy on light gold `#D4A65A` | 6.4 | Buttons, all text ✅ |
| Light gold on navy | 6.4 | Small text in footer/header ✅ |
| Dark gold `#8A5F1C` on off-white | 5.3 | Small gold text ✅ |
| Muted `#5B6475` on off-white | 5.6 | Captions ✅ |
| **Brand gold `#BD852C` on off-white** | **3.0** | ❌ not for text. Lines and icons only |
| **White on brand gold** | **3.2** | ❌ not for button text |
| **Brand gold on navy** | **4.46** | Only large text (24 px+, or 19 px bold) |

### 1.2 Typography

| Role | Font | Size |
|---|---|---|
| Headings | `"Times New Roman", Times, Georgia, serif`, which matches the logo wordmark. It is built into devices, so nothing extra downloads. | h1 `clamp(2rem, 5vw, 3.25rem)`, h2 `clamp(1.6rem, 3.5vw, 2.25rem)`, h3 `1.375rem` |
| Body | System sans-serif: `system-ui, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif` (no download, very readable) | **17 px** (`1.0625rem`), line-height 1.6, never below 16 px |
| Eyebrow / labels | Body font, uppercase, letter-spacing 0.08em, `--gold-dark` | 0.875rem (labels only, not paragraphs) |

Crimson Pro, Almarai and the WebFont script are removed. This also fixes the JavaScript error on every page (audit P1-10).

### 1.3 One CSS file: `assets/css/site.css`

It replaces `css/main.css` (4,368 lines of Webflow output) and `css/mycss.css`. Target: **under 800 lines**, organised in this order:

1. **Tokens:** the `:root` variables above, plus spacing scale `--space-1…8` (4, 8, 12, 16, 24, 32, 48, 72 px), `--radius: 10px`, `--shadow`.
2. **Base:** box-sizing, body, links, `img { max-width:100%; height:auto; display:block }`, focus outline (gold-light, 3 px), `prefers-reduced-motion`.
3. **Layout:** `.container` (max-width 1200 px, side padding 16 px on phones / 24 px from 768 px), `.section` (vertical padding from the spacing scale), `.grid` (CSS grid, `repeat(auto-fit, minmax(260px, 1fr))`), `.stack` (flex column with gap).
4. **Components (one style each):**
   - **Buttons:** `.btn` base, then `.btn--primary` (navy, white text), `.btn--accent` (light gold, navy text, used for "Apply Now"), `.btn--outline` (navy border). Minimum tap size 44 × 44 px.
   - **Cards:** `.card` (white, border, radius, padding; image on top with `aspect-ratio: 3/2; object-fit: cover`).
   - **Tables:** `.table` inside `.table-wrap { overflow-x: auto }` so wide tables scroll inside their box, never the page.
   - **Forms:** `.field`, `label`, input/select/textarea (16 px+ text so iPhones don't zoom), `.field-error`, `.form-message`.
   - **Section headers:** `.section-header` = eyebrow + serif h2 + short gold rule + optional intro paragraph.
   - **Small extras:** `.fact` (quick-fact tile), `.steps` (numbered list), `.badge-needed` (visible `[CONTENT NEEDED]` marker, dashed border).
5. **Header and nav, footer, WhatsApp button.**
6. **Utilities:** a few only (`.visually-hidden`, `.text-center`).

### 1.4 Layout rules (apply to every page)

- **No fixed heights** on content boxes. Use content height, padding, or `aspect-ratio` for images.
- **No `position: absolute`** for content. It is allowed only for decorative overlays, such as the dark gradient over the hero photo.
- **No hover-only information.** Everything currently in hover overlays (programs, faculty, news descriptions) becomes visible text.
- **No negative margins, no `100vw` widths, no `white-space: nowrap`** on text that can be long.
- **Phone-first CSS:** write phone styles first, then `@media (min-width: 768px)` and `@media (min-width: 1024px)`.
- **Test at 360, 768 and 1280 px** (plus 320 px as a stress test).
- **Real HTML text only.** No text baked into images.
- `z-index` uses three levels only: header 100, mobile menu 200, WhatsApp button 300.

### 1.5 Header and navigation

```
[logo icon] SKANS School System        About ▾  Academics ▾  Admissions ▾  News & Events ▾  Contact Us   [ Apply Now ]
            AECHS Campus · Playgroup – Matric
```

| Menu | Items |
|---|---|
| About ▾ | About SKANS · Principal's Message (`about.php#principal`) · SKANS Policies · Careers |
| Academics ▾ | Academic Programs · Special Programs · Faculty · Facilities · Academic Calendar |
| Admissions ▾ | Admissions · Fee Structure · Admission Policy · FAQs · Book a Visit |
| News & Events ▾ | News & Events · Gallery |
| Contact Us | (direct link) |
| **Apply Now** | `.btn--accent` → `admissions.php` |

- Existing labels are kept ("Academic Programs", "Special Programs", "Facilities", "SKANS Policies", "News & Events", "Contact Us"). **Desktop and mobile menus come from the same list**, so they can't drift apart again (audit P1-7).
- Dropdowns open on **click/tap and keyboard** (a `<button aria-expanded>`), not hover.
- Phones (below 1024 px): logo + "Apply Now" + a menu button. The menu opens as a full-height panel and the dropdowns become expandable sections. The current page is highlighted with `aria-current="page"`.
- The header is sticky with a solid off-white background, so it never covers content.

### 1.6 Footer

Footer has a navy background with 4 columns on desktop, 2 on tablet and 1 on phone:

| Column | Content |
|---|---|
| Campus | Name, address, map link |
| Contact | Phones, correct email, WhatsApp |
| Quick links | Main pages |
| Follow us | Facebook, Instagram (each with its own icon) |

The © line uses the automatic year. All contact details come from `data/site.php` (section 3.1), so they are typed **once**.

### 1.7 WhatsApp button (fixes audit P2-1)

- A 56 px circle with the icon as `display: block` (perfect circle, no line-height gap). It sits at `right: 16px; bottom: calc(16px + env(safe-area-inset-bottom))`.
- The typing bubble that repeats every 5 seconds is **removed**. Optionally, show one small label once, with `max-width: calc(100vw - 104px)` and text allowed to wrap, so it cannot be cut off.
- It has `aria-label="Chat with us on WhatsApp"`. Pages get bottom padding so the button never covers the footer text.
- The pulse animation is switched off for users who set "reduce motion".

---

## 2. The new Home page (`index.php`)

Section order, top to bottom. "Source" says where each piece comes from. Nothing is invented.

| # | Section | Content | Source |
|---|---|---|---|
| 1 | **Header** | As in 1.5 | `includes/header.php` |
| 2 | **Hero** | **h1:** "SKANS School System, AECHS Campus". **Tagline:** "Empowering Minds, Inspiring Futures" (existing). **Line:** "Playgroup to Matric · Airport Employees CHS, Rawalpindi". **Buttons:** **Apply Now** (accent) → `admissions.php`, **Book a Visit** (outline, white) → `book-a-visit.php`. Background: campus photo with a navy gradient for readable text. Height comes from content and padding, not a fixed height. | Photo: `video/firstpage.jpg` (424 KB) resized to 1600 px and about 250 KB. **[CONFIRM]** photo, or keep the video (see 9) |
| 3 | **Quick facts** | 4 tiles: Classes "Playgroup – Matric" · Location "AECHS, Rawalpindi" · Students **[CONTENT NEEDED]** · Established **[CONTENT NEEDED]** (or Teachers / Pass rate, whichever SKANS supplies) | `data/site.php` → `facts` |
| 4 | **Programs by level** | 4 cards, each with level name, classes, age guide (from the current Admissions page) and one line of description, linking to `departments.php#level`. **Early Years:** Playgroup (2.5+), Nursery (3.5+), Kindergarten (4.5+). **Primary:** Grade 1–5 (5.5+ to 9.5+). **Middle:** Grade 6–7, Pre-9 (10.5+ to 12.5+). **Secondary (Matric):** Class 9–10 (13.5+, 14.5+). Descriptions **[CONTENT NEEDED]**. | `data/site.php` → `levels`. **[CONFIRM]** this grouping and the board name |
| 5 | **Admission steps (teaser)** | 4 numbered steps, shortened from the existing procedure: 1) Meet the Principal 2) Register at the admin & accounts office 3) Complete the admission form 4) Principal signs the form. Button: "Full admission details" → `admissions.php`. | Existing text in `admissions.php` |
| 6 | **Fee link band** | Sand-coloured strip: "Fee structure for [session]" + button → `fee-structure.php`. The session comes from `data/fees.php`; it shows **[CONTENT NEEDED]** until supplied. | `data/fees.php` |
| 7 | **Latest news** | The **3 newest** items from `news_events` (same query as today): image (`alt` = title, `aspect-ratio: 3/2`, lazy), category, date "3 Oct 2026", title, first 120 characters of description, link to `news-events.php#news-<id>`. All text escaped. Without an image it shows a navy placeholder block, not the missing `default-image.jpg`. Button "All news & events". | Database (no change to tables) |
| 8 | **Principal's message** | Short excerpt of the existing message (about.php), photo **[CONTENT NEEDED]**, name **[CONTENT NEEDED]**, link "Read the full message" → `about.php#principal` | Existing copy |
| 9 | **Location & contact** | Left: address, phones (tap to call), email (correct `aechs.campus@…`), WhatsApp, office hours **[CONTENT NEEDED]**. Right: Google Map (lazy-loaded `iframe` with a "View on Google Maps" link using the existing map link). Buttons: **Book a Visit**, **Call us**. | `data/site.php` |
| 10 | **Footer** | As in 1.6 | `includes/footer.php` |

**Removed from Home** (nothing is lost):

- The 8 facility cards move to `services.php` (Facilities), their copy kept. The "Interactive Multimedia" card text is corrected there; see the content gaps.
- The 3 random faculty move to `faculty.php` only.
- The 3 big promo banners are replaced by sections 4–6.
- The commented-out 2025 job popup is deleted. It goes to `_old/` with the old `index.php`.

**Speed target for Home:** total download **under 1.5 MB** on first visit (today it is over 14 MB with the video).

---

## 3. File structure and shared parts

### 3.1 New structure

```
aechscampus/                     (site folder = web root)
├── index.php, about.php, …      pages (existing file names kept, so old links keep working)
├── includes/
│   ├── bootstrap.php            starts session, connects DB (one place), loads helpers + data/site.php
│   ├── functions.php            e() escape, content() marker helper, csrf_token()/csrf_check(), date format
│   ├── header.php               <head> with per-page title/description + nav
│   ├── footer.php               footer + WhatsApp button + site.js
│   └── .htaccess                deny all (cannot be opened directly)
├── data/
│   ├── site.php                 name, address, phones, email, socials, facts, levels (single source of truth)
│   ├── fees.php                 fee structure + admission policy (section 5.1)
│   ├── faqs.php, calendar.php, careers.php
│   └── .htaccess                deny all
├── assets/
│   ├── css/site.css             the only stylesheet
│   ├── js/site.js               menu, dropdowns, nothing else
│   └── img/                     resized copies of kept photos + logo icon + favicons
├── uploads/.htaccess            PHP cannot run here
├── adminpanel/upload/images/.htaccess   PHP cannot run here
├── _old/                        replaced files + .htaccess deny all (never uploaded)
├── .htaccess                    no folder listings, custom 404, block error_log / .md / .sql files
├── 404.php, robots.txt, sitemap.xml
└── docs/                        audit.md, redesign-plan.md (never uploaded)
```

### 3.2 How every page starts (simple pattern)

```php
<?php
$page_title       = 'Fee Structure';                     // becomes "Fee Structure | SKANS School System, AECHS Campus"
$page_description = 'Fees for the [session] academic year at SKANS AECHS Campus, Rawalpindi.';
$active_nav       = 'admissions';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/header.php';
?>
  … page HTML …
<?php require __DIR__ . '/includes/footer.php'; ?>
```

- **Database:** `bootstrap.php` includes the existing `adminpanel/db.php` **once**. I will not open that file. Moving the credentials out of it is a step **you** do (P0-8), and only `bootstrap.php` needs updating afterwards.
- **`e($text)`** escapes anything printed from the database or a form. **`content($value)`** prints the value, or the `[CONTENT NEEDED]` badge when the value is empty or still the marker.
- **Before go-live:** a search for `CONTENT NEEDED` across the site must return nothing, or only items you've accepted.

### 3.3 Files moved to `_old/` (each copied there before it is replaced)

| File | Why |
|---|---|
| `header.php`, `footer.php` | Replaced by `includes/header.php`, `includes/footer.php` |
| `css/main.css`, `css/mycss.css` | Replaced by `assets/css/site.css` |
| Each page file **before** it is rebuilt (e.g. `_old/index.php`) | Keeps the original copy |
| `index-old.php` | Duplicate home |
| `onlineform.php`, `onlineadmission.php` | Unsafe uploads (P0-2); "Apply Now" now goes to Admissions |
| `contactus_form.php` | Replaced by the visit form (and a contact form only if you want one, see 9) |
| `forgot_password.php`, `reset_password.php` | Rebuilt inside `adminpanel/` |
| `adminpanel/hashpas.php`, `adminpanel/info.php` | Dangerous leftovers (P0-3, P0-4) |
| Each admin file **before** it is fixed | Keeps the original copy |
| `icons/About Page Content.pdf` | Text belongs to the Westridge campus |
| Unused big images (`css/cmff.jpg`, `cmp.jpg`, `rrr.jpg`, `Highly Qualified….png`, `defence day.png`, `icons/res.png`) | Not used anywhere, about 15 MB |

The original photos stay. **Resized copies** go in `assets/img/`.

---

## 4. Page-by-page rebuild order

Each phase ends with the test checklist (section 7) for the pages it touched. **Phase 0 and 1 come first because the security holes are live today.**

### Phase 0: Safety net (before any change)

| Step | Who | Why |
|---|---|---|
| Full **backup** of the live site files and a **database export** (phpMyAdmin → Export → SQL) | You | Lets us undo anything |
| Copy the live files to local XAMPP and test there first | You / developer | We never edit the live server directly |
| **Check the live `uploads/` and `adminpanel/upload/images/` for any file that is not an image** (e.g. `.php`, `.phtml`). If one exists, the server may already be compromised. Stop and tell me. | You | P0-2 has been open for a long time |
| Optional: `git init` in the site folder for full change history | You decide | Easier than `_old/` alone |
| Create `_old/` + its `.htaccess` deny file | Developer | Agreed decision |

### Phase 1: Security fixes on the current site (section 6)

All P0 items. It touches `adminpanel/*`, root `.htaccess`, the upload folders' `.htaccess`, and retires dangerous files. **The site looks the same after this phase. It is just safe.**

### Phase 2: Foundation

| Files | Why |
|---|---|
| `assets/css/site.css`, `assets/js/site.js` | One design system (section 1) |
| `includes/bootstrap.php`, `functions.php`, `header.php`, `footer.php` | One header/footer, escaping, CSRF, per-page titles |
| `data/site.php` | Contact details typed once (fixes the wrong email link and mixed names) |
| `assets/img/` | Logo icon (from `icons/logo.png`, resized to 192/96/48 px), favicon set (32 px PNG, 180 px Apple icon), resized photos |
| `.htaccess`, `404.php` | No folder listings, friendly 404 |

### Phase 3: Home

`index.php`, as in section 2.

### Phase 4: Admissions pages (most important for parents)

| File | Change | Why |
|---|---|---|
| `admissions.php` | Rebuild: intro, steps, age-limit table, subjects list, "Download admission form (PDF)", links to Fee Structure / Admission Policy / FAQs / Book a Visit. Fee card replaced by a link (see 9 for whether figures stay). | Clear path to apply. Fixes broken markup and dead card links. |
| `fee-structure.php` (new) | Reads `data/fees.php` | Section 5.1 |
| `admission-policy.php` (new) | Reads `data/fees.php` (`admission_policy` part) | Section 5.1 |
| `book-a-visit.php` (new) + DB table `visit_requests` + `adminpanel/visit_requests.php` (new) | Visit form | Section 5.2 |

### Phase 5: About and Academics

| File | Change | Why |
|---|---|---|
| `about.php` | h1 "About SKANS AECHS Campus". Vision & Mission, Core Values (headings kept, text **[CONTENT NEEDED]**), Principal's message with `id="principal"` (name/photo **[CONTENT NEEDED]**), the 4 "how we meet needs" blocks (kept), photo grid with alt text. | Missing h1, thin content, broken `<div>`s |
| `departments.php` (Academic Programs) | Sections per level (same grouping as Home) + the DB programs shown as visible cards (no hover). The "Responsible and Active Citizen" blocks are kept. | Hover-only content; `,`/`*` split mismatch (use one rule) |
| `advanced-placement-program.php` (Special Programs) | Keep the 7 real program texts. Robotics text **[CONTENT NEEDED]**. Intro corrected to cover all levels **[CONFIRM wording]**. | Duplicate text, wrong intro |
| `faculty.php` | h1, Leadership / Teaching sections, cards show experience and education as visible lists | No h1, hover-only |
| `services.php` (Facilities) | h1, the 8 facility cards moved from Home + DB services + activities as cards, real alt text | Combine facility content in one place |

### Phase 6: News and school life

| File | Change | Why |
|---|---|---|
| `news-events.php` | Featured announcement (no `<marquee>`), recent announcements as a plain list (no auto-scroll, or optional with pause), news grid with All / News / Events filter buttons (`<button>`s), each item with an `id` for links from Home, all text escaped | `&amp;`, obsolete markup, accessibility |
| `gallery.php` (new) | Section 5.3 | |
| `calendar.php` (new) | Section 5.3 | |

### Phase 7: Contact and information

| File | Change | Why |
|---|---|---|
| `contact-us.php` | h1, contact cards (from `data/site.php`), map, office hours **[CONTENT NEEDED]**, Book a Visit button, social links with correct icons | Wrong email link, wrong Instagram icon |
| `policies.php` (SKANS Policies) | h1, list of policies as cards. Harassment policy PDF kept. Parent policies **[CONTENT NEEDED]**. | Thin page |
| `faqs.php`, `careers.php` (new) | Section 5.3 | |

### Phase 8: SEO, speed, final checks

`sitemap.xml`, `robots.txt` (blocks `/adminpanel/`, `/_old/`, `/docs/`), unique titles and descriptions, Open Graph image, image sizes, run the full section 7 checklist, then upload (**excluding `_old/` and `docs/`**).

---

## 5. New pages and their structure

### 5.1 Fee Structure and Admission Policy, both from `data/fees.php`

Both pages read the **same** file, so SKANS updates only one place. **No numbers are filled in.** Every amount starts as `null`, which shows **[CONTENT NEEDED]**.

```php
<?php
// data/fees.php : edit this file only. Amounts in whole rupees, no commas (e.g. 5000).
// null or '[CONTENT NEEDED]' shows a visible "content needed" marker on the site.
return [
  'session'      => '[CONTENT NEEDED]',          // e.g. "2026–27"
  'currency'     => '[CONTENT NEEDED]',          // e.g. "PKR"
  'last_updated' => '[CONTENT NEEDED]',          // date SKANS approved these fees
  'one_time' => [                              // paid once at admission
    ['label' => 'Registration Fee',        'amount' => null, 'note' => ''],
    ['label' => 'Admission Fee',           'amount' => null, 'note' => ''],
    ['label' => 'Security Fee',            'amount' => null, 'note' => '[CONTENT NEEDED] refundable?'],
  ],
  'recurring' => [                             // per class group, per period
    ['level' => 'Early Years', 'classes' => 'Playgroup – KG', 'tuition' => null, 'period' => '[CONTENT NEEDED]'],
    // … one row per level [CONFIRM grouping]
  ],
  'annual' => [
    ['label' => 'Annual Resource Charges', 'amount' => null, 'note' => ''],
  ],
  'notes'           => ['[CONTENT NEEDED] discounts, payment dates, late fee, payment methods'],
  'admission_policy' => [
    'eligibility'   => '[CONTENT NEEDED]',
    'age_limits'    => 'from data/site.php levels',   // reuses existing age table, no duplication
    'documents'     => ['[CONTENT NEEDED]'],           // e.g. B-form, photos, previous school report
    'test'          => '[CONTENT NEEDED]',            // the procedure mentions an admission test
    'dates'         => '[CONTENT NEEDED]',
    'siblings'      => '[CONTENT NEEDED]',
    'withdrawal'    => '[CONTENT NEEDED]',
    'refunds'       => '[CONTENT NEEDED]',
  ],
];
```

The **labels** above (Registration, Admission, Security, Tuition, Annual Resource Charges) are the ones already on the site. The **amounts** currently published (`admissions.php:104-108`) are **not** copied in, because they have no session or period. SKANS must re-confirm them.

**`fee-structure.php` layout:**

1. h1 "Fee Structure [session]"
2. "Last updated" line
3. Table "Paid once at admission"
4. Table "Tuition by level" (inside `.table-wrap`, which scrolls sideways on phones and never overflows the page)
5. Table "Annual charges"
6. Notes
7. Buttons: "Admission Policy" · "Book a Visit" · "Call the office"

**`admission-policy.php` layout:**

1. h1 "Admission Policy"
2. Short sections with h2s: Eligibility & age limits (reuses the existing age table), Required documents, Admission test, Important dates, Siblings, Withdrawal & refunds
3. Download admission form (PDF)
4. Link to Fee Structure

### 5.2 Book a Visit (`book-a-visit.php`)

| Part | Detail |
|---|---|
| Fields | Parent/guardian name (required) · Mobile number (required, Pakistani format check) · Email (optional) · Class of interest (select from the levels list) · Preferred date (date, not in the past) · Preferred time (morning/afternoon) · Message (optional, max 1,000 characters) |
| Visiting hours | **[CONFIRM]** shown next to the date field |
| Spam protection | Hidden "honeypot" field + reject forms sent in under 3 seconds + max 5 requests per IP per hour + CSRF token. No Google reCAPTCHA, so no third-party keys or tracking (it can be added later). |
| Saving | Prepared statement into a new table `visit_requests` (id, name, phone, email, class, preferred_date, preferred_time, message, status, created_at, ip). I will give you the exact SQL to run in phpMyAdmin. |
| After submit | Same page shows "Thank you, we will call you to confirm" (no redirect to a blank page). Errors show next to each field, and filled-in values are kept. |
| Admin | New `adminpanel/visit_requests.php` (login required): list, newest first, status New / Contacted / Visited, all text escaped, status change by POST + CSRF. A link is added to `adminpanel/nav.php`. |
| Email alert | Optional, through PHPMailer once credentials are in a safe config. **[CONFIRM]** recipient address. |
| Privacy line | "We use these details only to arrange your visit." |

### 5.3 Other new pages

| Page | Structure | Data source |
|---|---|---|
| `faqs.php` | h1, questions grouped (Admissions, Fees, Daily life, Transport), each a `<details><summary>` (works without JavaScript, keyboard-friendly) | `data/faqs.php`, all answers **[CONTENT NEEDED]**. Questions drafted by developer, approved by SKANS. |
| `careers.php` | h1, short "Work at SKANS" intro **[CONTENT NEEDED]**, open positions (title, qualification, experience, posts, deadline), how to apply (email **[CONTENT NEEDED]**). If there are no positions: "No vacancies at the moment." | `data/careers.php` (could become an admin feature later) |
| `gallery.php` | h1, grid of photos taken from `news_events` images (already in DB), newest first, captions = event title + date, filter by year. A click opens the full image (plain link, no heavy lightbox). Thumbnails need resizing (see 6, P1). | Database, no new admin work |
| `calendar.php` | h1, session title, table of terms, holidays, exams and PTMs (month, date, event), "Download calendar (PDF)" if SKANS has one | `data/calendar.php`, all **[CONTENT NEEDED]** |
| `404.php` | h1 "Page not found", short apology, search-free links to Home, Admissions, Contact. Same header/footer. Sends a real 404 status. | n/a |

---

## 6. P0 fixes (safest approach for each)

General rules for this phase:

- Copy each file to `_old/` before changing it.
- Change one file at a time and test the admin feature it belongs to.
- Never print database or form text without escaping.

| Audit # | Fix | Safest approach |
|---|---|---|
| **P0-1** `fetch_admission.php` leaks children's data | Add the login check, read `id` as a number, use a prepared statement, escape every field | Create **`adminpanel/auth.php`** (the login check in one file) and `require` it on line 1. Test: logged out → redirected to login; logged in → details still show in the admin modal. |
| **P0-2** unsafe uploads | Retire `onlineform.php` and `onlineadmission.php` to `_old/`. Add `.htaccess` in `uploads/` and `adminpanel/upload/images/` that stops PHP from running and only serves images. Admin uploads: check real image type (`finfo`), allow jpg/png/webp only, always rename to a random name, max 2 MB. | Retiring is safer than patching. The `.htaccess` is a second lock even if a bad file slipped in before. **Also check the live folders for non-image files (Phase 0).** |
| **P0-3** `hashpas.php` | Move to `_old/` (blocked) | Not needed any more. Passwords are already hashed by `login.php` (`password_verify`). |
| **P0-4** `info.php`, public `error_log` | Move `info.php` to `_old/`. Delete `error_log` from the server. Root `.htaccess` blocks any file named `error_log`, `*.log`, `*.md`, `*.sql`. | Nothing to break |
| **P0-5** admin files without login | `require 'auth.php';` at the top of **every** admin file except `login.php` (and the moved reset pages) | One shared file means it cannot be forgotten again. Test each admin page logged out. |
| **P0-6** SQL from user input | Rewrite the 5 queries as prepared statements with `(int)` IDs. Status values checked against a fixed list (Pending/Approved/Rejected…). | Small, local changes; same results |
| **P0-7** `&amp;` | **(a)** Stop encoding on save: remove `htmlspecialchars()` from the `$_POST` lines in the 14 add/edit files. **(b)** Escape only when printing, on public pages and in admin forms. **(c)** One-time clean-up of existing rows: after a DB backup, run `REPLACE()` updates on the text columns of `news_events`, `announcements`, `faculty`, `services`, `activities`, `academic_prog`, turning `&amp;` → `&`, `&quot;` → `"`, `&#039;` → `'`, `&lt;` → `<`, `&gt;` → `>`. Repeat until no `&amp;` is left (some rows are double-encoded). | Do (a) and (b) together, then (c). Test by adding and editing a title with `&` and `'`, then checking the public page and the admin form after **two** edits. I will give you the exact SQL; you run it after the backup. |
| **P0-8** hardcoded secrets | **You** change the DB password and the mail password in cPanel. **You** put the new values in a config file **outside** `public_html` (e.g. `/home2/…/skans-config.php`), and the code reads them from there. Delete the commented password line in `onlineform.php` (the file is retired anyway). | I don't open or copy these files. I'll give you a template config file and the one line that loads it. |
| **P0-9** errors shown to visitors | `display_errors` off, `log_errors` on (to a file outside the web root), set once in `bootstrap.php` / `auth.php`. Replace `die("Connection failed: …")` with a polite "Sorry, please try again later" page. | Errors still go to a log you can read in cPanel |
| **P0-10** weak login | Same message for wrong username or password. `session_regenerate_id(true)` after login. Lock out after 5 failed tries per username+IP for 15 minutes (small new table `login_attempts`). Session cookie set to `HttpOnly`, `Secure`, `SameSite=Lax`. Log out after 60 minutes idle. Reset pages moved into `adminpanel/`. Reset email says the same thing whether or not the account exists, and the token is valid for 1 hour (already). | No change to the existing users or passwords |
| **P0-11** stored XSS in admin | Escape every field in `view_addmissions.php`, `fetch_admission.php`, `view_contactus.php`, `admissionform.php`, `admission_summary.php` | Visible text stays the same |
| **P0-12** broken links | Fixed by the new header/footer and `data/site.php`. Mobile "Faculty" → `faculty.php`, email link → `aechs.campus@…`, reset "back to login" → `adminpanel/login.php`. | Link check in section 7 |
| **P0-13** delete by link (CSRF) | Delete and status actions become small POST forms with a CSRF token + "Are you sure?" confirm. GET requests can no longer delete. | Test each delete button |

---

## 7. Test checklist

### 7.1 Every page (run each time a page is rebuilt)

**Screen sizes.** Chrome DevTools → device toolbar, check at **360 × 800**, **768 × 1024**, **1280 × 800**, and also **320 px**:
- [ ] No sideways scrolling of the page (only tables may scroll inside their box)
- [ ] Nothing overlaps or is cut off: header, buttons, cards, WhatsApp button, footer
- [ ] Text is at least 16 px. Headings wrap nicely with no word breaking out of its box.
- [ ] Images keep their shape (no stretching) and have alt text (decorative icons: `alt=""`)
- [ ] Mobile menu opens and closes, dropdowns work by tap, the current page is highlighted
- [ ] The WhatsApp button doesn't cover any link or text, including the last footer line
- [ ] Rotate the phone to landscape: still fine

**Content and code:**
- [ ] Exactly **one `<h1>`**. Headings in order (h2 → h3).
- [ ] Unique `<title>` and meta description
- [ ] No `[CONTENT NEEDED]` left (or the remaining ones are accepted by you)
- [ ] No `&amp;`, `&#039;` or other code visible in text
- [ ] W3C HTML validator: no errors (validator.w3.org)

**Accessibility:**
- [ ] Use the page with the keyboard only (Tab / Enter / Esc). Focus is always visible.
- [ ] axe DevTools or WAVE browser extension: no contrast or label errors
- [ ] Lighthouse (mobile) Accessibility **≥ 95**

**Links:**
- [ ] Every link and button goes to a real page (no 404). Phone links dial, the email link opens with the correct address, social links open the right page.

**Speed.** Lighthouse mobile, and the Network tab with "Slow 4G":
- [ ] Performance **≥ 85**. Largest image under 300 KB. Page total under 1.5 MB (Home) / 1 MB (others).
- [ ] Images below the first screen use `loading="lazy"`

**Errors:**
- [ ] Browser console: no red errors
- [ ] With the database stopped, the page shows a polite message, not PHP errors

### 7.2 Page-specific checks

| Page | Extra checks |
|---|---|
| Home | Apply Now → Admissions; Book a Visit → form. Latest news shows the 3 newest items and each link opens the right item. Quick facts and principal markers are filled. The map loads lazily and the "View on Google Maps" link works. The hero text stays readable over the photo at all 4 widths. |
| Admissions | The PDF downloads. The age table matches `data/site.php`. All 4 links to sub-pages work. |
| Fee Structure | Every amount comes from `data/fees.php` (change one number in the file → the page changes). An empty value shows the marker. The tables scroll inside their box at 320 px. Print preview is readable. |
| Admission Policy | Each section shows the data file text or the marker. The age table is the same as on Admissions. |
| Book a Visit | **Valid** form → saved and listed in the admin panel. **Empty** required fields → messages next to the fields, values kept. Bad phone number → error. Past date → error. Honeypot filled → silently rejected. Sent in under 3 seconds → rejected. 6th request in an hour → "try later". Missing CSRF token → rejected. `<script>` typed in the message → shows as plain text in the admin panel. Works with the keyboard only. |
| About | `#principal` link from Home scrolls to the message. Photos have alt text. |
| Academic Programs / Special Programs / Faculty / Facilities | All details visible without hover (test on a real phone). DB records display; an empty table shows a friendly message, not "No faculty members found" on the wrong page. |
| News & Events | Filter buttons work and show the active state. Text with `&` displays correctly after adding **and** after editing twice in the admin panel. Items without an image show a placeholder. |
| Gallery | Thumbnails are small files (under 80 KB each). Clicking opens the full image. The year filter works. |
| Calendar / FAQs / Careers | Data file edits appear on the page. FAQ items open with keyboard and screen reader. The "No vacancies" state works. |
| Contact | Correct email in the link and the text. The map works. The phone link dials. |
| SKANS Policies | The PDF opens. Each card is a real link. |
| 404 | A wrong URL shows the 404 page with a real **404 status** (check in the Network tab). |
| Admin panel | Every admin page while **logged out** → redirect to login. Delete needs POST + confirm. 5 wrong passwords → lock-out message. Password reset works end to end. `fetch_admission.php?id=1` logged out → no data. Uploading a `.php` file renamed `.jpg` → rejected. |
| Server | `/_old/`, `/docs/`, `/includes/`, `/data/`, `/adminpanel/error_log`, `/uploads/` (listing) → all blocked (403/404). `robots.txt` and `sitemap.xml` open. |

---

## 8. What is kept (from audit section 6)

- Logo (icon now, official files later)
- Campus photos, resized
- Hero poster photo
- All real copy: facilities, the 4 About blocks, the 7 Special Programs, citizen blocks, admission steps, subjects, age limits
- Contact details, map link, social links, WhatsApp number
- All database content: news, events, announcements, faculty, services, activities, programs, admissions, contact messages
- Harassment policy PDF and the printable admission form PDF
- Every admin feature, made secure, plus the new Visit Requests page

---

## 9. Questions before building (your answers change the plan)

1. **Program levels:** is this grouping right? Early Years (PG–KG) · Primary (1–5) · Middle (6–7, Pre-9) · Secondary/Matric (9–10). Which exam board?
2. **Fee figures:** until SKANS confirms new fees, should the site **(a)** show no amounts (only `[CONTENT NEEDED]` / "Contact the office"), or **(b)** keep showing the current figures labelled "as currently published"?
3. **Hero:** campus photo (fast, recommended) or keep the 13.8 MB video? If you choose the video, I'll compress it to under 3 MB, play it only on desktop, and show the photo on phones.
4. **Contact form:** keep only Book a Visit + phone/WhatsApp/email, or also add a general contact form (same spam protection)?
5. **Visit requests:** who should get an email alert (if anyone)? What are the visiting days and hours?
6. **Quick facts:** which 2 facts can SKANS supply (founding year, students, teachers, results)?
7. **History:** do you want to run `git init` so every change is tracked, in addition to `_old/`?


### Admin panel update (5 Oct 2026)

| Item | What was done |
|---|---|
| Look | One admin style (`adminpanel/admin.css`, loaded by `nav.php`): white top menu with logo, navy/gold buttons, tidy tables, forms and section tiles. All existing buttons and links kept. Login page restyled (illustration kept). |
| Dashboard | `admin_panel.php` rebuilt: summary cards (posts this month vs limit, new messages, live announcements, visitors), quick-add buttons, the 5 section buttons + Settings, latest messages, recently added, visitor charts and table (kept). |
| Settings | New `adminpanel/settings.php`: school name, campus name, classes line, address, Google Maps link, email, office/mobile phones, WhatsApp, Facebook, Instagram, YouTube, TikTok, monthly post limit. Placeholders in every field; validation. Saved in DB table `site_settings`; website reads it on every page (`includes/settings.php`). Empty required fields fall back to `data/site.php`. |
| Admission policy | New `adminpanel/admission_policy_edit.php`: edit Admission, Documents, Fee rules, Attendance, Leaving, Declaration (one line = one bullet). Shown on Admission Policy, Fee Structure and Policies pages. "Restore original" returns to `data/fees.php`. |
| Post limit | News/events + activities together: max 8 per calendar month (changeable in Settings). Add pages show usage and are locked at the limit; server also refuses. `news_events.created_at` column added automatically (old posts NULL, not counted). |
| Bulk delete | Select-all + "Delete selected" on Manage News & Events and Manage Activities (`bulk_bar.php`, `bulk_delete.php`, token-protected, confirm). Picture files kept. |
| Live server | `site_settings` table and `news_events.created_at` column are created automatically the first time an admin opens the dashboard. Backups: `_old/adminpanel/oct5/`. |

### Backups and list tools (6 Oct 2026)

| Item | What was done |
|---|---|
| Automatic backups | `adminpanel/backup_lib.php`: full database backup as `backups/db-backup-<date>-<auto|manual|cron>.sql.gz` (plain PHP, no mysqldump). Made automatically once a day when an admin opens the dashboard; optional cPanel cron `php …/adminpanel/cron_backup.php`; "Back up now" on Settings → Backups (`backups.php`, download with token). Newest 30 kept. `backups/` blocked from the web (.htaccess). Restore tested: all tables and row counts identical. Do NOT upload the local `backups/` folder to the live server. |
| Search, filters, pages | `adminpanel/list_tools.php` on Manage Activities (search, added this month/year, sort, thumbnails), Manage News & Events (search, category, year, sort, short descriptions), Manage Announcements (search, on/not on website), Contact messages (search, new/reviewed). 10/20/50/100 per page; bulk delete works per page. |

### Fee editor and modern forms (6 Oct 2026)

| Item | What was done |
|---|---|
| Fee Structure editor | `adminpanel/fees_edit.php` (Settings → Fee structure): session year + table of all classes × 6 fees, "Set all" per column, "Prepare next session" (next year's name + optional % increase, rounded to 100), history of previous sessions (last 10), "Restore original". Saved in `site_settings` ('fees', 'fees_history'); website reads it via `load_fees()`. |
| Modern forms | `adminpanel/item_editor.php` drives Add/Edit for News & Events, Activities, Facilities, Faculty (8 pages, old versions in `_old/adminpanel/oct6/`): messages on the page, values kept after errors, live preview, character counters, 2 MB picture check with size/pixels, safe upload (real file type, random name), monthly post limit for news + activities. Same database columns as before. |
