<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';

// Manage announcements. Rebuilt Oct 2026 (old version: _old/adminpanel/oct3/).
// The website shows the 6 newest from the last 30 days (data/site.php 'announcement_days');
// the newest is featured. Older ones leave the website automatically but stay saved here.
$site = require __DIR__ . '/../data/site.php';
$days = max(1, (int) ($site['announcement_days'] ?? 30));
$cutoff = strtotime("-$days days");
// Which announcements are on the website right now: the 6 newest from the last $days days.
$since = date('Y-m-d H:i:s', $cutoff);
$live_ids = array_map('intval', array_column($conn->query("SELECT id FROM announcements WHERE created_at >= '$since' ORDER BY created_at DESC, id DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC), 'id'));
$featured_id = $live_ids[0] ?? 0;

// Search, filter and pages (list_tools.php)
require_once __DIR__ . '/list_tools.php';
$p = lt_params(['status' => ['', 'live', 'off']], ['newest', 'oldest']);
$where = ['1=1']; $types = ''; $params = [];
if ($p['q'] !== '') { $where[] = '(heading LIKE ? OR announcement_text LIKE ?)'; $types .= 'ss'; $params[] = lt_like($p['q']); $params[] = lt_like($p['q']); }
if ($p['status'] !== '') { $where[] = ($p['status'] === 'live' ? 'id IN (' : 'id NOT IN (') . ($live_ids ? implode(',', $live_ids) : '0') . ')'; }
$order = $p['sort'] === 'oldest' ? 'created_at ASC, id ASC' : 'created_at DESC, id DESC';
[$rows, $info] = lt_query($conn, 'announcements WHERE ' . implode(' AND ', $where), $types, $params, $order, $p, 'id, image, heading, announcement_text, created_at');
$messages = [
    'added'    => ['success', 'The announcement has been published.'],
    'updated'  => ['success', 'Your changes have been saved.'],
    'deleted'  => ['success', 'The announcement has been deleted.'],
    'notfound' => ['warning', 'That announcement no longer exists.'],
];
$msg = $messages[$_GET['msg'] ?? ''] ?? null;
$token = urlencode(admin_csrf_token());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Announcements | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f0f4f8; }
        .ann-head { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; }
        .ann-head h1 { font-size: 1.6rem; color: #192A4A; margin: 0; }
        .ann-item { display: grid; grid-template-columns: 96px 1fr auto; gap: 16px; align-items: center; background: #fff; border-radius: 10px; padding: 14px; box-shadow: 0 1px 8px rgba(25,42,74,.06); }
        .ann-item + .ann-item { margin-top: 12px; }
        .ann-thumb { width: 96px; height: 96px; border-radius: 8px; background: #EEF1F6; display: grid; place-items: center; overflow: hidden; }
        .ann-thumb img { width: 100%; height: 100%; object-fit: contain; }
        .ann-thumb span { font-size: .75rem; color: #5B6475; }
        .ann-title { font-weight: 700; color: #192A4A; margin: 0 0 2px; overflow-wrap: anywhere; }
        .ann-text { color: #5B6475; font-size: .9rem; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ann-meta { font-size: .8rem; color: #5B6475; margin-top: 4px; }
        .ann-actions { display: flex; gap: 8px; }
        @media (max-width: 575px) {
            .ann-item { grid-template-columns: 72px 1fr; }
            .ann-thumb { width: 72px; height: 72px; }
            .ann-actions { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container my-4" style="max-width: 1000px">
    <div class="ann-head mb-3">
        <h1>Announcements</h1>
        <a href="add_announcement.php" class="btn btn-primary" style="background:#192A4A;border-color:#192A4A"><i class="fas fa-plus"></i> Add announcement</a>
    </div>
    <p class="text-muted">Announcements stay on the website for <strong><?= $days ?> days</strong>, then leave it automatically (they stay saved here, so you can still edit or delete them). At most the <strong>6 newest</strong> are shown; the newest is <strong>featured</strong> at the top of News &amp; Events.</p>

<?php if ($msg): ?>
    <div class="alert alert-<?= $msg[0] ?>" role="status"><?= h($msg[1]) ?></div>
<?php endif; ?>

<?= lt_search_bar($p, 'Search announcements…',
    [['name' => 'status', 'label' => 'Status', 'options' => ['' => 'All announcements', 'live' => 'On the website', 'off' => 'Not on the website']]],
    ['newest' => 'Newest first', 'oldest' => 'Oldest first']) ?>
<?php if (!$rows): ?>
    <div class="alert alert-info">No announcements yet. <a href="add_announcement.php">Add the first one</a>.</div>
<?php endif; ?>

<?php
foreach ($rows as $i => $r):
    $added = strtotime($r['created_at']);
    $live = in_array((int) $r['id'], $live_ids, true);
    $status = !$live ? 'expired' : ((int) $r['id'] === $featured_id ? 'featured' : 'live');
    $img_src = upload_url($r['image']); $has_img = $img_src !== ''; ?>
    <div class="ann-item">
        <div class="ann-thumb">
<?php if ($has_img): ?>
            <img src="<?= h($img_src) ?>" alt="" loading="lazy">
<?php else: ?>
            <span>No picture</span>
<?php endif; ?>
        </div>
        <div>
            <p class="ann-title"><?= h($r['heading']) ?>
<?php if ($status === 'featured'): ?> <span class="badge badge-warning">Featured</span><?php elseif ($status === 'live'): ?> <span class="badge badge-success">On website</span><?php else: ?> <span class="badge badge-secondary">Not on website</span><?php endif; ?>
            </p>
            <p class="ann-text"><?= h($r['announcement_text']) ?></p>
            <div class="ann-meta">Added <?= h(date('j M Y, g:i A', $added)) ?> ·
                <?php if ($status === 'expired' && $added < $cutoff): ?>left the website on <?= h(date('j M Y', $added + $days * 86400)) ?>
                <?php elseif ($status === 'expired'): ?>waiting: only the 6 newest are shown
                <?php else: ?>on the website until <?= h(date('j M Y', $added + $days * 86400)) ?><?php endif; ?></div>
        </div>
        <div class="ann-actions">
            <a href="edit_announcement.php?id=<?= (int) $r['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-pen"></i> Edit</a>
            <a href="delete_announcement.php?id=<?= (int) $r['id'] ?>&token=<?= $token ?>" class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Delete this announcement? This cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
        </div>
    </div>
<?php endforeach; ?>
<?= lt_pagination($p, $info, 'announcements') ?>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
