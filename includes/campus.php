<?php
// includes/campus.php : ONE installation of the website serves ALL SKANS campuses.
//
// The website address (domain) decides which campus is shown:
//   campus-data/sites.php            domain -> campus id      e.g. 'aechs.skansschool.edu.pk' => 'aechs'
//   campus-data/<id>/config.php      that campus's database login + its own school details
//   campus-data/<id>/uploads/        that campus's pictures (unless config.php says otherwise)
//   campus-data/<id>/backups/        that campus's database backups
// campus-data/ is NEVER part of the update zip, so updating the code never touches a campus's
// settings, pictures or backups. Each campus keeps its own database.
// Setup and update steps: docs/multi-campus.md

if (defined('SK_CAMPUS')) {
    return;
}
if (!defined('SK_ROOT')) {
    define('SK_ROOT', dirname(__DIR__));
}
const SK_VERSION = '2.1.1';                 // raise with every update zip (shown in the admin panel)
const SK_CAMPUS_DIR = SK_ROOT . '/campus-data';

/** Work out the campus for this request. Unknown address -> plain "not found" page. */
function campus_resolve(): array
{
    $map_file = SK_CAMPUS_DIR . '/sites.php';
    if (!is_file($map_file)) {
        campus_fail('Setup needed: campus-data/sites.php is missing (see docs/multi-campus.md).');
    }
    $map = campus_load_php($map_file);

    if (PHP_SAPI === 'cli') {
        // Command line (cron backups): php script.php --campus=aechs   or env SK_CAMPUS=aechs
        $id = getenv('SK_CAMPUS') ?: '';
        foreach ($GLOBALS['argv'] ?? [] as $arg) {
            if (strpos($arg, '--campus=') === 0) $id = substr($arg, 9);
        }
        if ($id === '') {
            campus_fail('Which campus? Add --campus=<folder name in campus-data>, e.g. --campus=aechs');
        }
    } else {
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $id = $map[$host] ?? $map[preg_replace('/^www\./', '', $host)] ?? '';
    }
    if (!is_string($id) || !preg_match('/^[a-z0-9-]{2,40}$/', $id) || !is_file(SK_CAMPUS_DIR . '/' . $id . '/config.php')) {
        // The exact reason goes to the server error log (cPanel: the "error_log" file in the site folder), never to visitors.
        if (!is_array($map)) {
            $why = 'campus-data/sites.php does not "return [ ... ];" an address list';
        } elseif (!is_string($id) || $id === '') {
            $why = 'address "' . ($host ?? '') . '" is not listed in campus-data/sites.php (listed: ' . implode(', ', array_keys($map)) . ')';
        } elseif (!preg_match('/^[a-z0-9-]{2,40}$/', $id)) {
            $why = 'campus id "' . $id . '" in campus-data/sites.php must be lowercase letters, numbers or dashes';
        } else {
            $why = 'file missing: ' . SK_CAMPUS_DIR . '/' . $id . '/config.php';
        }
        error_log('SKANS website not set up: ' . $why);
        campus_fail('This website address is not set up.', 404);
    }
    $config = campus_load_php(SK_CAMPUS_DIR . '/' . $id . '/config.php');
    return ['id' => $id, 'config' => is_array($config) ? $config : []];
}

/**
 * Load a settings file (sites.php / config.php) safely. A file that does not start with "<?php"
 * would be PRINTED on the page by PHP (showing passwords), so it is refused instead.
 */
function campus_load_php(string $file)
{
    $head = (string) @file_get_contents($file, false, null, 0, 16);
    if (strncmp($head, "\xEF\xBB\xBF", 3) === 0) {
        $head = substr($head, 3);                                 // ignore a UTF-8 "BOM" (some editors add it)
    }
    $head = ltrim($head);                                         // and blank lines before <?php
    if (stripos($head, '<?php') !== 0) {
        error_log('SKANS website not set up: ' . $file . ' must start with <?php on the first line');
        campus_fail('Setup needed: a settings file in campus-data/ must start with <?php on its first line.');
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($file, true);                         // always read the newest version after an edit
    }
    return require $file;
}

function campus_fail(string $message, int $code = 503): void
{
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    exit($message);
}

$GLOBALS['sk_campus'] = campus_resolve();
define('SK_CAMPUS', $GLOBALS['sk_campus']['id']);

function campus_config(): array
{
    return $GLOBALS['sk_campus']['config'];
}

/** New database connection for this campus (or a mysqli with connect_error set). */
function campus_connect(): mysqli
{
    $db = campus_config()['db'] ?? [];
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($db['host'] ?? 'localhost', $db['user'] ?? '', $db['pass'] ?? '', $db['name'] ?? '');
    if (!$conn->connect_error) {
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

/** School details for this campus: data/site.php (shared defaults) + config.php 'site' (campus defaults). */
function campus_site_defaults(): array
{
    $site = require SK_ROOT . '/data/site.php';
    return array_replace($site, campus_config()['site'] ?? []);
}

/** Pictures folder, relative to the website root, e.g. 'campus-data/aechs/uploads'. */
function upload_rel(): string
{
    $rel = campus_config()['uploads'] ?? ('campus-data/' . SK_CAMPUS . '/uploads');
    return trim(str_replace(chr(92), '/', $rel), '/');
}

/** Full server path of the pictures folder, with a trailing slash. */
function upload_dir(): string
{
    static $dir = null;
    if ($dir !== null) {
        return $dir;
    }
    $dir = SK_ROOT . '/' . upload_rel() . '/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    // Inside campus-data/ (closed to the web): open this folder again, for pictures only.
    if (strpos(upload_rel(), 'campus-data/') === 0 && !is_file($dir . '.htaccess')) {
        @file_put_contents($dir . '.htaccess', "# Pictures of this campus: images can be viewed, nothing else (no scripts).
"
            . "Require all denied
<FilesMatch \"(?i)\.(jpe?g|png|gif|webp)$\">
    Require all granted
</FilesMatch>
");
    }
    return $dir;
}

/** Web address of an uploaded picture ('' if the file is missing). Works from the website and the admin panel. */
function upload_url(?string $file): string
{
    $file = basename((string) $file);
    if ($file === '' || !is_file(upload_dir() . $file)) {
        return '';
    }
    return sk_base_url() . upload_rel() . '/' . rawurlencode($file);
}

/** Address of the website root, e.g. '/' or '/aechscampus/aechscampus/'. */
function sk_base_url(): string
{
    $dir = str_replace(chr(92), '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (basename($dir) === 'adminpanel') {
        $dir = dirname($dir);
    }
    return rtrim(str_replace(chr(92), '/', $dir), '/') . '/';
}

/** This campus's backups folder (no trailing slash). */
function campus_backup_dir(): string
{
    return SK_CAMPUS_DIR . '/' . SK_CAMPUS . '/backups';
}
