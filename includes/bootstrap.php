<?php
// includes/bootstrap.php : runs at the top of every page.
// Starts the session, connects to the database once, loads helpers and school details.

if (defined('SK_BOOTSTRAPPED')) {
    return;
}
define('SK_BOOTSTRAPPED', true);
define('SK_ROOT', dirname(__DIR__));

// Visitors never see PHP error details; errors still go to the server log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// SK_QUIET (data files such as chat-fees.php): no session and no visitor count.
if (session_status() === PHP_SESSION_NONE && !defined('SK_QUIET')) {
    session_start();
}

require_once __DIR__ . '/campus.php';     // which campus this address belongs to (campus-data/)
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/settings.php';
$site = campus_site_defaults();            // data/site.php + this campus's config.php 'site'

// Database: this campus's own database (login in campus-data/<campus>/config.php).
mysqli_report(MYSQLI_REPORT_OFF);
include SK_ROOT . '/adminpanel/db.php';
$db_ok = isset($conn) && $conn instanceof mysqli && !$conn->connect_error;

// Admin settings (school/campus name, contact details, social links) override data/site.php.
$site = apply_site_settings($site, $db_ok ? settings_all($conn) : []);
$sk_classes = campus_class_list();                                                    // Settings > Classes & ages
$site['levels'] = pad_levels($site['levels'], campus_level_count($sk_classes));       // 3 cards, or up to 6
$site['levels'] = load_levels($site['levels']); // level cards: admin Academic Programs #1-#6
$site = apply_class_list($site, $sk_classes, $db_ok && !empty(settings_all($conn)['class_list']));

// Count one visit per IP address per day (same as the old header did, now with a safe query).
if ($db_ok && !defined('SK_QUIET')) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $today = date('Y-m-d');
    $check = $conn->prepare('SELECT 1 FROM visitors WHERE ip_address = ? AND visit_date = ? LIMIT 1');
    if ($check) {
        $check->bind_param('ss', $ip, $today);
        $check->execute();
        $check->store_result();
        if ($check->num_rows === 0) {
            $add = $conn->prepare('INSERT INTO visitors (ip_address, visit_date) VALUES (?, ?)');
            if ($add) {
                $add->bind_param('ss', $ip, $today);
                $add->execute();
                $add->close();
            }
        }
        $check->close();
    }
}
