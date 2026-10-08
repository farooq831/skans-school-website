<?php
// adminpanel/session.php : starts the admin session with safe cookie settings.
// Used by auth.php (every admin page), login.php and logout.php.

ini_set('display_errors', '0');   // never show PHP errors to visitors
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,       // JavaScript cannot read the session cookie
        'samesite' => 'Lax',      // cookie is not sent with requests from other websites
    ]);
    session_start();
}

/** Largest picture the admin may upload (all image uploads), decided Oct 2026. */
const IMAGE_MAX_BYTES = 2 * 1024 * 1024; // 2 MB
const IMAGE_MAX_LABEL = '2 MB';

/** Longest allowed description for news, events, activities and announcements (decided Oct 2026). */
const DESCRIPTION_MAX = 500;

/** Secret token for this login session; added to forms and delete links. */
function admin_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** True if the token sent with a request matches this session's token. */
function admin_csrf_ok($token): bool
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/** Stop the request unless it carries the right token (use before any delete or update). */
function admin_require_csrf(): void
{
    $token = $_POST['csrf_token'] ?? $_GET['token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!admin_csrf_ok($token)) {
        http_response_code(403);
        exit('This link has expired. Please go back, refresh the page and try again.');
    }
}

/** Escape text for HTML output (also fixes old "&amp;amp;" values saved by earlier versions). */
function h($text): string
{
    $text = (string) $text;
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
