<?php
// adminpanel/cron_backup.php : daily database backup run by the server (cPanel → Cron Jobs).
// Command:  php /full/path/to/adminpanel/cron_backup.php
// Only works from the command line; opening it in a browser does nothing.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
chdir(__DIR__);
include __DIR__ . '/db.php';
require __DIR__ . '/backup_lib.php';

if (!isset($conn) || $conn->connect_error) {
    fwrite(STDERR, "Backup failed: no database connection.\n");
    exit(1);
}
$result = backup_create($conn, 'cron');
if ($result['ok']) {
    echo 'Backup created: ' . $result['file'] . ' (' . $result['tables'] . ' tables, ' . $result['rows'] . " rows)\n";
    exit(0);
}
fwrite(STDERR, 'Backup failed: ' . $result['error'] . "\n");
exit(1);
