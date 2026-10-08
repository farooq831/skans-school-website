<?php
// adminpanel/admin_lib.php : shared helpers for the admin panel (settings, monthly post limit).
// Include after db.php:  require_once __DIR__ . '/admin_lib.php';
if (!defined('SK_ROOT')) {
    define('SK_ROOT', dirname(__DIR__));
}
require_once SK_ROOT . '/includes/campus.php';
require_once SK_ROOT . '/includes/settings.php';
settings_ensure_schema($conn);

/** School details with the admin's saved settings applied. */
function admin_site(mysqli $conn): array
{
    return apply_site_settings(campus_site_defaults(), settings_all($conn));
}

/** Monthly limit for News/Events posts + co-curricular activities together (Settings, default 8). */
function post_limit(mysqli $conn): int
{
    $s = settings_all($conn);
    return isset($s['post_limit']) ? max(1, (int) $s['post_limit']) : 8;
}

/** How many News/Events posts and activities were ADDED this calendar month. */
function posts_this_month(mysqli $conn): array
{
    $start = date('Y-m-01 00:00:00');
    $news = (int) $conn->query("SELECT COUNT(*) c FROM news_events WHERE created_at >= '$start'")->fetch_assoc()['c'];
    $acts = (int) $conn->query("SELECT COUNT(*) c FROM activities WHERE created_at >= '$start'")->fetch_assoc()['c'];
    return ['news' => $news, 'activities' => $acts, 'total' => $news + $acts];
}

/** ['used' => 3, 'limit' => 8, 'left' => 5, 'full' => false, 'resets' => '1 Nov 2026'] */
function post_quota(mysqli $conn): array
{
    $used = posts_this_month($conn)['total'];
    $limit = post_limit($conn);
    return [
        'used'   => $used,
        'limit'  => $limit,
        'left'   => max(0, $limit - $used),
        'full'   => $used >= $limit,
        'resets' => date('j M Y', strtotime('first day of next month')),
    ];
}

/** Banner shown on the "add" pages. */
function post_quota_banner(array $q): string
{
    if ($q['full']) {
        return '<div class="alert alert-danger adm-quota"><strong>Monthly limit reached.</strong> '
            . $q['used'] . ' of ' . $q['limit'] . ' posts (news, events and activities) have been added this month. '
            . 'You can add new posts again from <strong>' . h($q['resets']) . '</strong>. Editing and deleting still work.</div>';
    }
    return '<div class="alert alert-info adm-quota">This month: <strong>' . $q['used'] . ' of ' . $q['limit'] . '</strong> posts used '
        . '(news, events and activities together). <strong>' . $q['left'] . '</strong> left until ' . h($q['resets']) . '.</div>';
}
