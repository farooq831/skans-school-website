<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/backup_lib.php';
// Backups page: list of database backups, "Back up now", download, how to restore.

$notice = null;

// Download one backup (streamed through PHP; the folder itself is blocked from the web).
if (isset($_GET['download'])) {
    admin_require_csrf();
    $file = basename((string) $_GET['download']);
    $path = backup_dir() . '/' . $file;
    if (strpos($file, BACKUP_PREFIX) !== 0 || substr($file, -7) !== '.sql.gz' || !is_file($path)) {
        http_response_code(404);
        exit('Backup not found.');
    }
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup_now'])) {
    admin_require_csrf();
    $result = backup_create($conn, 'manual');
    $notice = $result['ok']
        ? ['success', 'Backup created: ' . $result['file'] . ' (' . $result['tables'] . ' tables, ' . number_format($result['rows']) . ' rows).']
        : ['danger', $result['error']];
}

$list = backup_list();
$last = $list[0] ?? null;
$token = urlencode(admin_csrf_token());
$cron_path = str_replace('\\', '/', __DIR__) . '/cron_backup.php';
$hours = $last ? (time() - $last['time']) / 3600 : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1>Database backups</h1>
            <p class="adm-sub">A full copy of the website database (news, announcements, faculty, messages, settings…) is made <strong>automatically every day</strong>. The newest <?= BACKUP_KEEP ?> backups are kept.</p>
        </div>
        <form method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=&quot;fas fa-spinner fa-spin&quot;></i> Backing up…';">
            <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
            <button type="submit" name="backup_now" value="1" class="btn btn-primary"><i class="fas fa-database"></i> Back up now</button>
        </form>
    </div>

<?php if ($notice): ?>
    <div class="alert alert-<?= $notice[0] ?>"><?= h($notice[1]) ?></div>
<?php endif; ?>

    <div class="adm-stats">
        <div class="adm-stat<?= ($hours === null || $hours > 48) ? ' adm-stat--warn' : '' ?>">
            <div class="adm-stat__label">Last backup</div>
            <div class="adm-stat__value" style="font-size:1.35rem; padding:.3rem 0"><?= $last ? h(date('j M Y, g:i A', $last['time'])) : 'None yet' ?></div>
            <div class="adm-stat__hint"><?= $last ? ($hours < 1 ? 'less than an hour ago' : (int) floor($hours) . ' hours ago') : 'Click "Back up now"' ?></div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat__label">Backups kept</div>
            <div class="adm-stat__value"><?= count($list) ?> / <?= BACKUP_KEEP ?></div>
            <div class="adm-stat__hint"><?= h(backup_size_label((int) array_sum(array_column($list, 'size')))) ?> in total</div>
        </div>
    </div>

    <div class="adm-card">
        <h2><i class="fas fa-box-archive"></i> Saved backups</h2>
<?php if (!$list): ?>
        <p class="adm-help mb-0">No backups yet.</p>
<?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Made by</th><th>Size</th><th></th></tr></thead>
                <tbody>
<?php foreach ($list as $b):
        $how = preg_match('/-(auto|manual|cron)\.sql\.gz$/', $b['file'], $m) ? $m[1] : '';
        $how_label = ['auto' => 'Automatic (daily)', 'manual' => 'Back up now', 'cron' => 'Server schedule'][$how] ?? '–'; ?>
                    <tr>
                        <td><?= h(date('D j M Y, g:i A', $b['time'])) ?></td>
                        <td><?= h($how_label) ?></td>
                        <td><?= h(backup_size_label($b['size'])) ?></td>
                        <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="backups.php?download=<?= urlencode($b['file']) ?>&token=<?= $token ?>"><i class="fas fa-download"></i> Download</a></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="adm-card">
                <h2><i class="fas fa-rotate-left"></i> How to restore a backup</h2>
                <ol class="mb-2">
                    <li>Download the backup you need (above).</li>
                    <li>Open <strong>cPanel → phpMyAdmin</strong> and click the website's database.</li>
                    <li>Click <strong>Import</strong>, choose the downloaded <code>.sql.gz</code> file, click <strong>Go</strong>.</li>
                </ol>
                <p class="adm-help mb-0"><strong>Careful:</strong> restoring replaces the current content with the content of that day. Make a "Back up now" first.</p>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="adm-card">
                <h2><i class="fas fa-clock"></i> Extra safety: server schedule (optional)</h2>
                <p class="mb-2">Daily backups are made automatically when an admin opens the dashboard. To back up even on days nobody logs in, add a <strong>Cron Job</strong> in cPanel (once a day, e.g. 2:00 AM) with this command:</p>
                <pre class="mb-2" style="white-space:pre-wrap; background:#F4F1EA; padding:.6rem; border-radius:8px; font-size:.82rem">php <?= h($cron_path) ?></pre>
                <p class="adm-help mb-0">Also download a backup to a computer or USB drive once a month, in case the whole server is lost.</p>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
