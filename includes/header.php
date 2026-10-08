<?php
// includes/header.php : <head>, top bar and main menu for every page.
// Before including, a page may set: $page_title, $page_description, $legacy_page (old Webflow page).

require_once __DIR__ . '/bootstrap.php';

// Titles for older pages that have not been rebuilt yet.
$legacy_titles = [
    'index.php'        => ['', 'SKANS School System, AECHS Campus Rawalpindi: Playgroup to Matric. Admissions, programs, facilities, news and contact details.'],
    'about.php'        => ['About Us', 'Vision, mission, values and the Principal’s message at SKANS School System, AECHS Campus, Rawalpindi.'],
    'services.php'     => ['Facilities', 'Facilities and co-curricular activities at SKANS School System, AECHS Campus, Rawalpindi.'],
    'faculty.php'      => ['Faculty', 'Leadership and teaching faculty of SKANS School System, AECHS Campus, Rawalpindi.'],
    'news-events.php'  => ['News & Events', 'Latest news, events and announcements from SKANS School System, AECHS Campus, Rawalpindi.'],
    'contact-us.php'   => ['Contact Us', 'Address, phone numbers, email and map for SKANS School System, AECHS Campus, Rawalpindi.'],
];
$this_page = current_page();
if (!isset($page_title) && isset($legacy_titles[$this_page])) {
    [$page_title, $page_description] = $legacy_titles[$this_page];
}
$page_title = $page_title ?? '';
$page_description = campus_text($page_description ?? $legacy_titles['index.php'][1]); // this campus's name and city
$full_title = $page_title !== '' ? $page_title . ' | ' . $site['full_name'] : $site['full_name'] . ', ' . $site['city'];
$legacy_page = !empty($legacy_page);

// Main menu. One list drives both the desktop and the phone menu.
$menu = [
    ['label' => 'About',      'children' => [
        ['label' => 'About SKANS',    'href' => 'about.php'],
        ['label' => 'SKANS Policies', 'href' => 'policies.php'],
    ]],
    ['label' => 'Facilities', 'href' => 'services.php'],
    ['label' => 'Academics',  'children' => [
        ['label' => 'Programs Offered', 'href' => 'departments.php'],
        ['label' => 'Special Programs', 'href' => 'advanced-placement-program.php'],
        ['label' => 'Faculty',          'href' => 'faculty.php'],
    ]],
    ['label' => 'Admissions', 'children' => [
        ['label' => 'Admission Process', 'href' => 'admissions.php'],
        ['label' => 'Fee Structure',     'href' => 'fee-structure.php'],
        ['label' => 'Admission Policy',  'href' => 'admission-policy.php'],
    ]],
    ['label' => 'News & Events',  'href' => 'news-events.php'],
    ['label' => 'Contact Us',     'href' => 'contact-us.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($full_title) ?></title>
    <meta name="description" content="<?= e($page_description) ?>">
    <meta property="og:title" content="<?= e($full_title) ?>">
    <meta property="og:description" content="<?= e($page_description) ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="assets/img/skans-logo-640.png">
    <meta name="theme-color" content="#192A4A">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32.png">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
<?php if ($legacy_page): ?>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/mycss.css">
<?php endif; ?>
    <?php // "?v=" changes whenever the file changes, so browsers never use an old cached copy. ?>
    <link rel="stylesheet" href="assets/css/site.css?v=<?= filemtime(SK_ROOT . '/assets/css/site.css') ?>">
    <script src="assets/js/site.js?v=<?= filemtime(SK_ROOT . '/assets/js/site.js') ?>" defer></script>
</head>
<body class="<?= $legacy_page ? 'sk-legacy' : 'sk-page' ?>">
<a class="sk-skip" href="#main">Skip to content</a>

<header class="sk-header">
    <div class="sk-container sk-header__bar">
        <a class="sk-brand" href="index.php" aria-label="<?= e($site['full_name']) ?> – home">
            <img class="sk-brand__logo" src="assets/img/skans-logo-76.png"
                 srcset="assets/img/skans-logo-50.png 105w, assets/img/skans-logo-60.png 126w, assets/img/skans-logo-76.png 160w, assets/img/skans-logo-100.png 210w, assets/img/skans-logo-120.png 252w, assets/img/skans-logo-152.png 319w, assets/img/skans-logo-228.png 479w"
                 sizes="(max-width: 479px) 105px, (max-width: 1099px) 126px, 160px"
                 width="160" height="76" alt="SKANS School System – Education for life">
            <span class="sk-brand__campus">
                <span class="sk-brand__name"><?= e($site['campus']) ?></span>
                <span class="sk-brand__sub"><?= e($site['classes']) ?></span>
            </span>
        </a>

        <div class="sk-header__actions">
            <a class="sk-btn sk-btn--enroll sk-btn--small sk-header__apply" href="admissions.php">Enroll Now</a>
            <button class="sk-menu-toggle" type="button" aria-expanded="false" aria-controls="sk-nav">
                <span class="sk-menu-toggle__bars" aria-hidden="true"></span>
                <span class="sk-menu-toggle__label">Menu</span>
            </button>
        </div>

        <nav class="sk-nav" id="sk-nav" aria-label="Main menu">
            <ul class="sk-nav__list">
<?php foreach ($menu as $i => $item): ?>
<?php if (isset($item['children'])):
        $hrefs = array_column($item['children'], 'href');
        $is_active = in_array($this_page, $hrefs, true); ?>
                <li class="sk-nav__item sk-nav__item--has-sub<?= $is_active ? ' is-active' : '' ?>">
                    <button class="sk-nav__link sk-nav__toggle" type="button" aria-expanded="false" aria-controls="sk-sub-<?= $i ?>">
                        <?= e($item['label']) ?> <span class="sk-nav__caret" aria-hidden="true"></span>
                    </button>
                    <ul class="sk-nav__sub" id="sk-sub-<?= $i ?>">
<?php foreach ($item['children'] as $child): ?>
                        <li><a class="sk-nav__sublink" href="<?= e($child['href']) ?>"<?= $child['href'] === $this_page ? ' aria-current="page"' : '' ?>><?= e($child['label']) ?></a></li>
<?php endforeach; ?>
                    </ul>
                </li>
<?php else: ?>
                <li class="sk-nav__item"><a class="sk-nav__link" href="<?= e($item['href']) ?>"<?= $item['href'] === $this_page ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
                <li class="sk-nav__item sk-nav__item--apply"><a class="sk-btn sk-btn--enroll" href="admissions.php">Enroll Now</a></li>
            </ul>
        </nav>
    </div>
</header>
<?= $legacy_page ? '<div id="main">' : '<main id="main">' ?>
