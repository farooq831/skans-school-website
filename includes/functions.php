<?php
// includes/functions.php : small helpers used by every page.

/** Escape text before printing it in HTML. Also turns old "&amp;" saved by the admin panel back into "&" first. */
function e($text): string
{
    $text = (string) $text;
    // Older admin pages saved text already encoded (e.g. "&amp;amp;"). Decode until stable, then encode once.
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** True when a value still needs content from SKANS. */
function is_missing($value): bool
{
    return $value === null || $value === '' || $value === [] || (is_string($value) && strpos($value, 'CONTENT NEEDED') !== false);
}

/** Print a value, or a visible "content needed" marker when it is empty. */
function content($value, string $what = ''): string
{
    if (is_missing($value)) {
        $label = $what !== '' ? 'Content needed: ' . $what : 'Content needed';
        return '<span class="sk-needed">' . e($label) . '</span>';
    }
    return e($value);
}

/** Format an amount like "Rs. 20,000". null prints a dash. */
function money($amount, string $currency = 'Rs.'): string
{
    if ($amount === null) {
        return '<span aria-label="not charged">–</span>';
    }
    return e($currency . ' ' . number_format((float) $amount));
}

/** "2026-09-21" -> "21 Sep 2026" */
function nice_date($date): string
{
    $ts = strtotime((string) $date);
    return $ts ? date('j M Y', $ts) : '';
}

/** Shorten long text for cards without cutting words. */
function excerpt($text, int $length = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    $text = str_replace('*', '', $text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    $cut = mb_substr($text, 0, $length);
    $space = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $space ?: $length), " ,.;:") . '…';
}

/** Path of a page relative to the site root, used for active menu links. */
function current_page(): string
{
    return basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
}

/** Navy banner at the top of inner pages. $crumbs = [['Admissions', 'admissions.php'], ['Fee Structure', '']] */
function page_banner(string $title, string $intro = '', array $crumbs = []): void
{
    // Breadcrumb (Home / About ...) removed from every page on 7 Oct 2026; $crumbs is kept so pages need no change.
    echo '<section class="sk-banner"><div class="sk-container">';
    echo '<h1>' . e($title) . '</h1>';
    if ($intro !== '') {
        echo '<p>' . e($intro) . '</p>';
    }
    echo '</div></section>';
}

/** Tabs linking the pages of one section (Admissions, Academics). */
function sub_nav(string $label, array $items): void
{
    $here = current_page();
    echo '<nav class="sk-subnav" aria-label="' . e($label) . '"><div class="sk-container"><ul>';
    foreach ($items as [$text, $href]) {
        $current = $href === $here ? ' aria-current="page"' : '';
        echo '<li><a href="' . e($href) . '"' . $current . '>' . e($text) . '</a></li>';
    }
    echo '</ul></div></nav>';
}

function admissions_nav(): void
{
    sub_nav('Admissions pages', [
        ['Admission Process', 'admissions.php'],
        ['Fee Structure', 'fee-structure.php'],
        ['Admission Policy', 'admission-policy.php'],
    ]);
}

function academics_nav(): void
{
    sub_nav('Academics pages', [
        ['Programs Offered', 'departments.php'],
        ['Special Programs', 'advanced-placement-program.php'],
        ['Faculty', 'faculty.php'],
    ]);
}

/** Secret token for public forms (stops other websites from submitting our forms). */
function form_token(): string
{
    if (empty($_SESSION['form_token'])) {
        $_SESSION['form_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['form_token'];
}

function form_token_ok($token): bool
{
    return is_string($token) && !empty($_SESSION['form_token']) && hash_equals($_SESSION['form_token'], $token);
}

/** Small inline icon (decorative: hidden from screen readers). Colour follows the text colour. */
function icon(string $name): string
{
    $paths = [
        'pin'       => '<path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/>',
        'phone'     => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/>',
        'whatsapp'  => '<path d="M12 2.2A9.8 9.8 0 0 0 3.6 17l-1.4 4.8 4.9-1.3A9.8 9.8 0 1 0 12 2.2zm0 17.9a8.1 8.1 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8.1 8.1 0 1 1 12 20.1zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.6 6.6 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.4.8 3.2.6.5-.1 1.5-.6 1.7-1.2s.2-1.1.1-1.2l-.4-.2z"/>',
        'mail'      => '<path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 7.2L4 7.4V17h16V7.4l-8 4.8zM4.9 7l7.1 4.2L19.1 7H4.9z"/>',
        'map'       => '<path d="M15 4.2 9 2 3 4.3a1 1 0 0 0-.6.9V21a.6.6 0 0 0 .8.6L9 19.4l6 2.2 6-2.3a1 1 0 0 0 .6-.9V3a.6.6 0 0 0-.8-.6L15 4.2zM10 4.6l4 1.5v13.3l-4-1.5V4.6zM4.4 5.6 8 4.4v13.4l-3.6 1.3V5.6zm15.2 12.8L16 19.6V6.2l3.6-1.3v13.5z"/>',
        'youtube'   => '<path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12a26 26 0 0 0 .4 4.8 2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8A26 26 0 0 0 22 12a26 26 0 0 0-.4-4.8zM10 15V9l5.2 3L10 15z"/>',
        'tiktok'    => '<path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.7a5.8 5.8 0 0 0-.8-.1 5.8 5.8 0 1 0 5.8 5.8V9.1a7.4 7.4 0 0 0 4.3 1.4V7.4a4.3 4.3 0 0 1-3.3-1.6z"/>',
        'facebook'  => '<path d="M13.5 22v-8.2h2.8l.4-3.2h-3.2V8.6c0-.9.3-1.6 1.6-1.6h1.7V4.1a22 22 0 0 0-2.5-.1c-2.5 0-4.2 1.5-4.2 4.3v2.4H7.3v3.2h2.8V22h3.4z"/>',
        'instagram' => '<path d="M12 7.3a4.7 4.7 0 1 0 0 9.4 4.7 4.7 0 0 0 0-9.4zm0 7.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6-7.9a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0zM12 4c2.6 0 2.9 0 4 .1 2.6.1 3.8 1.4 3.9 3.9.1 1 .1 1.4.1 4s0 2.9-.1 4c-.1 2.5-1.3 3.8-3.9 3.9-1 .1-1.4.1-4 .1s-2.9 0-4-.1c-2.6-.1-3.8-1.4-3.9-3.9C4 14.9 4 14.6 4 12s0-2.9.1-4C4.2 5.5 5.4 4.2 8 4.1 9.1 4 9.4 4 12 4zm0-1.8c-2.7 0-3 0-4.1.1C4.4 2.4 2.4 4.4 2.3 7.9 2.2 9 2.2 9.3 2.2 12s0 3 .1 4.1c.1 3.5 2.1 5.5 5.6 5.6 1.1.1 1.4.1 4.1.1s3 0 4.1-.1c3.5-.1 5.5-2.1 5.6-5.6.1-1.1.1-1.4.1-4.1s0-3-.1-4.1c-.1-3.5-2.1-5.5-5.6-5.6C15 2.2 14.7 2.2 12 2.2z"/>',
    ];
    return '<svg class="sk-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">' . ($paths[$name] ?? '') . '</svg>';
}

/** Web path of the site folder ("/" on the live site, "/aechscampus/aechscampus/" locally). */
function site_base(): string
{
    $dir = str_replace(chr(92), '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')); // chr(92) = backslash (Windows paths)
    return rtrim($dir, '/') . '/';
}
