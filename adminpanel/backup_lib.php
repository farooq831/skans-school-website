<?php
// adminpanel/backup_lib.php : automatic database backups.
//
// - A backup is a compressed SQL file (.sql.gz) of the WHOLE database: every table, structure + data.
// - Made automatically once a day (the first time an admin opens the dashboard that day) and by the
//   optional server cron job (adminpanel/cron_backup.php). The admin can also click "Back up now".
// - Kept in the folder  backups/  (next to the website files; blocked from the web by .htaccess).
// - The newest BACKUP_KEEP backups are kept; older ones are deleted automatically.
// - Restore: phpMyAdmin → database → Import → choose the .sql.gz file (see the Backups page).
// Written in plain PHP (no mysqldump), so it works on shared hosting where programs cannot be run.

date_default_timezone_set('Asia/Karachi'); // backup times in Pakistan time (school's local time)

const BACKUP_KEEP = 30;          // number of backups to keep
const BACKUP_PREFIX = 'db-backup-';

function backup_dir(): string
{
    $dir = dirname(__DIR__) . '/backups';
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    // Make sure nobody can download backups from the web.
    if (!is_file($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess', "# Database backups: never served to the web.\nRequire all denied\n");
    }
    if (!is_file($dir . '/index.html')) {
        @file_put_contents($dir . '/index.html', '');
    }
    return $dir;
}

/** Backups, newest first: [['file' => name, 'path' => full path, 'size' => bytes, 'time' => timestamp], ...] */
function backup_list(): array
{
    $files = glob(backup_dir() . '/' . BACKUP_PREFIX . '*.sql.gz') ?: [];
    $list = [];
    foreach ($files as $path) {
        $list[] = ['file' => basename($path), 'path' => $path, 'size' => filesize($path), 'time' => filemtime($path)];
    }
    usort($list, function ($a, $b) { return $b['time'] <=> $a['time']; });
    return $list;
}

function backup_last(): ?array
{
    return backup_list()[0] ?? null;
}

/** Delete all but the newest BACKUP_KEEP backups. */
function backup_prune(): int
{
    $removed = 0;
    foreach (array_slice(backup_list(), BACKUP_KEEP) as $old) {
        if (@unlink($old['path'])) {
            $removed++;
        }
    }
    return $removed;
}

/**
 * Write a full backup of the database. Returns ['ok' => bool, 'file' => name, 'error' => message].
 * $how is written into the file header and the file name (auto / manual / cron).
 */
function backup_create(mysqli $conn, string $how = 'manual'): array
{
    @set_time_limit(300);
    $dir = backup_dir();
    if (!is_writable($dir)) {
        return ['ok' => false, 'file' => null, 'error' => 'The backups folder is not writable on this server.'];
    }

    // Only one backup at a time.
    $lock = fopen($dir . '/.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
        return ['ok' => false, 'file' => null, 'error' => 'A backup is already running. Please try again in a minute.'];
    }

    $how = preg_replace('/[^a-z]/', '', $how) ?: 'manual';
    $name = BACKUP_PREFIX . date('Y-m-d_His') . '-' . $how . '.sql.gz';
    $tmp = $dir . '/' . $name . '.part';
    $gz = gzopen($tmp, 'wb6');
    if (!$gz) {
        flock($lock, LOCK_UN);
        return ['ok' => false, 'file' => null, 'error' => 'Could not create the backup file.'];
    }

    $db = $conn->query('SELECT DATABASE() AS d')->fetch_assoc()['d'];
    $conn->set_charset('utf8mb4');
    gzwrite($gz, "-- SKANS website database backup\n-- Database: {$db}\n-- Made: " . date('Y-m-d H:i:s') . " ({$how})\n"
        . "-- Restore: phpMyAdmin -> select this database -> Import -> choose this file -> Go.\n\n"
        . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

    $tables = [];
    $r = $conn->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    while ($row = $r->fetch_row()) {
        $tables[] = $row[0];
    }

    $rows_total = 0;
    try {
        foreach ($tables as $table) {
            $t = '`' . str_replace('`', '``', $table) . '`';
            $create = $conn->query("SHOW CREATE TABLE $t")->fetch_row()[1];
            gzwrite($gz, "-- Table $t\nDROP TABLE IF EXISTS $t;\n$create;\n\n");

            // Stream the rows (unbuffered, so large tables do not use much memory), 200 rows per INSERT.
            $res = $conn->query("SELECT * FROM $t", MYSQLI_USE_RESULT);
            $batch = [];
            $cols = null;
            while ($row = $res->fetch_assoc()) {
                if ($cols === null) {
                    $cols = '(`' . implode('`, `', array_map(function ($c) { return str_replace('`', '``', $c); }, array_keys($row))) . '`)';
                }
                $vals = [];
                foreach ($row as $v) {
                    $vals[] = $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
                }
                $batch[] = '(' . implode(', ', $vals) . ')';
                $rows_total++;
                if (count($batch) === 200) {
                    gzwrite($gz, "INSERT INTO $t $cols VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch) {
                gzwrite($gz, "INSERT INTO $t $cols VALUES\n" . implode(",\n", $batch) . ";\n");
            }
            $res->free();
            gzwrite($gz, "\n");
        }
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS = 1;\n-- End of backup: " . count($tables) . " tables, {$rows_total} rows.\n");
        gzclose($gz);
        rename($tmp, $dir . '/' . $name);
    } catch (Throwable $ex) {
        @gzclose($gz);
        @unlink($tmp);
        flock($lock, LOCK_UN);
        error_log('Backup failed: ' . $ex->getMessage());
        return ['ok' => false, 'file' => null, 'error' => 'The backup failed. Details were written to the server error log.'];
    }

    flock($lock, LOCK_UN);
    fclose($lock);
    backup_prune();
    return ['ok' => true, 'file' => $name, 'error' => null, 'tables' => count($tables), 'rows' => $rows_total];
}

/** Make today's backup if there is none yet (called when an admin opens the dashboard). */
function backup_auto_daily(mysqli $conn): ?array
{
    $last = backup_last();
    if ($last && date('Y-m-d', $last['time']) === date('Y-m-d')) {
        return null; // already backed up today
    }
    return backup_create($conn, 'auto');
}

function backup_size_label(int $bytes): string
{
    return $bytes >= 1048576 ? number_format($bytes / 1048576, 2) . ' MB' : max(1, round($bytes / 1024)) . ' KB';
}
