<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
require_once __DIR__ . '/backup_lib.php';
// Automatic daily database backup: made the first time an admin opens the dashboard each day.
$auto_backup = backup_auto_daily($conn);
$last_backup = backup_last();
// Dashboard. Rebuilt Oct 2026: summary, quick actions, recent items. The section buttons,
// visitor charts and visitor table from the old dashboard are kept (old: _old/adminpanel/oct5/).

$site = admin_site($conn);
$q = post_quota($conn);
// Fee session shown on the website (Settings → Fee structure). Reminder from 1 March of the session's last year.
$fee_defaults = require SK_ROOT . '/data/fees.php';
$fee_session = settings_all($conn)['fees']['session'] ?? $fee_defaults['session'];
$fee_due = preg_match('/\d{4}\D+(\d{4})/', $fee_session, $fm) && time() >= strtotime($fm[1] . '-03-01');
$month = posts_this_month($conn);
$one = function (string $sql) use ($conn) { $r = $conn->query($sql); return $r ? (int) $r->fetch_assoc()['c'] : 0; };
$days = max(1, (int) ($site['announcement_days'] ?? 30));
$stats = [
    'messages_new'   => $one("SELECT COUNT(*) c FROM contact_form_submissions WHERE remarks IS NULL OR remarks = ''"),
    'announcements'  => $one("SELECT COUNT(*) c FROM announcements WHERE created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)"),
    'news'           => $one("SELECT COUNT(*) c FROM news_events"),
    'activities'     => $one("SELECT COUNT(*) c FROM activities"),
    'faculty'        => $one("SELECT COUNT(*) c FROM faculty"),
    'visitors_today' => $one("SELECT COUNT(*) c FROM visitors WHERE visit_date = CURDATE()"),
    'visitors_total' => $one("SELECT COUNT(*) c FROM visitors"),
];
$messages = $conn->query("SELECT id, full_name, email, submitted_at, remarks FROM contact_form_submissions ORDER BY submitted_at DESC, id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_news = $conn->query("SELECT id, title, date FROM news_events ORDER BY date DESC, id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_ann = $conn->query("SELECT id, heading, created_at FROM announcements ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

// Visitor count for the last 7 days (same data as the old dashboard)
$result = $conn->query("
    SELECT DATE(visit_date) AS visit_day, COUNT(*) AS daily_visitors,
           (SELECT COUNT(*) FROM visitors WHERE visit_date <= v.visit_date) AS total_visitors
    FROM visitors v
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY visit_day ORDER BY visit_day DESC");
$dates = $daily_visitors = $total_visitors = [];
$tableData = '';
while ($row = $result->fetch_assoc()) {
    array_unshift($dates, $row['visit_day']);
    array_unshift($daily_visitors, (int) $row['daily_visitors']);
    array_unshift($total_visitors, (int) $row['total_visitors']);
    $tableData = '<tr><td>' . h($row['visit_day']) . '</td><td>' . (int) $row['daily_visitors'] . '</td><td>' . (int) $row['total_visitors'] . '</td></tr>' . $tableData;
}
$conn->close();
$pct = min(100, (int) round($q['used'] / max(1, $q['limit']) * 100));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SKANS Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1>Welcome<?= isset($_SESSION['username']) ? ', ' . h($_SESSION['username']) : '' ?></h1>
            <p class="adm-sub"><?= h($site['full_name']) ?> · <?= h(date('l, j F Y')) ?></p>
        </div>
        <div class="adm-actions">
            <a class="btn btn-primary" href="add_news.php"><i class="fas fa-plus"></i> Add news / event</a>
            <a class="btn btn-primary" href="add_activity.php"><i class="fas fa-plus"></i> Add activity</a>
            <a class="btn btn-warning" href="add_announcement.php"><i class="fas fa-bullhorn"></i> Add announcement</a>
            <a class="btn btn-outline-primary" href="fees_edit.php"><i class="fas fa-money-bill-wave"></i> Fee structure</a>
            <a class="btn btn-outline-primary" href="principal_edit.php"><i class="fas fa-user-tie"></i> Principal's message</a>
            <a class="btn btn-outline-primary" href="settings.php"><i class="fas fa-gear"></i> Settings</a>
        </div>
    </div>

    <!-- Summary -->
    <div class="adm-stats">
        <a class="adm-stat<?= $q['full'] ? ' adm-stat--warn' : '' ?>" href="manage_news_events.php">
            <div class="adm-stat__label">Posts this month</div>
            <div class="adm-stat__value"><?= $q['used'] ?> / <?= $q['limit'] ?></div>
            <div class="adm-bar"><span style="width: <?= $pct ?>%"></span></div>
            <div class="adm-stat__hint"><?= $month['news'] ?> news · <?= $month['activities'] ?> activities · <?= $q['full'] ? 'limit reached, resets ' . h($q['resets']) : $q['left'] . ' left' ?></div>
        </a>
        <a class="adm-stat" href="view_contactus.php">
            <div class="adm-stat__label">New messages</div>
            <div class="adm-stat__value"><?= $stats['messages_new'] ?></div>
            <div class="adm-stat__hint">contact form, not yet answered</div>
        </a>
        <a class="adm-stat" href="view_announcements.php">
            <div class="adm-stat__label">Announcements live</div>
            <div class="adm-stat__value"><?= $stats['announcements'] ?></div>
            <div class="adm-stat__hint">added in the last <?= $days ?> days</div>
        </a>
        <a class="adm-stat<?= (!$last_backup || $last_backup['time'] < time() - 48 * 3600) ? ' adm-stat--warn' : '' ?>" href="backups.php">
            <div class="adm-stat__label">Last backup</div>
            <div class="adm-stat__value" style="font-size:1.25rem; padding:.35rem 0"><?= $last_backup ? h(date('j M, g:i A', $last_backup['time'])) : 'None' ?></div>
            <div class="adm-stat__hint"><?= ($auto_backup && $auto_backup['ok']) ? 'Today’s backup was just made' : (($auto_backup && !$auto_backup['ok']) ? h($auto_backup['error']) : 'Automatic, every day') ?></div>
        </a>
        <a class="adm-stat<?= $fee_due ? ' adm-stat--warn' : '' ?>" href="fees_edit.php">
            <div class="adm-stat__label">Fee structure</div>
            <div class="adm-stat__value" style="font-size:1.25rem; padding:.35rem 0">Session <?= h($fee_session) ?></div>
            <div class="adm-stat__hint"><?= $fee_due ? 'New session coming: update the fees' : 'Click to view or update the fees' ?></div>
        </a>
        <div class="adm-stat">
            <div class="adm-stat__label">Visitors today</div>
            <div class="adm-stat__value"><?= number_format($stats['visitors_today']) ?></div>
            <div class="adm-stat__hint"><?= number_format($stats['visitors_total']) ?> in total</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat__label">On the website</div>
            <div class="adm-stat__value" style="font-size:1.25rem; padding:.35rem 0"><?= $stats['news'] ?> news · <?= $stats['activities'] ?> activities</div>
            <div class="adm-stat__hint"><?= $stats['faculty'] ?> faculty members</div>
        </div>
    </div>

    <!-- Sections (same buttons as before) -->
    <div class="adm-card">
        <h2><i class="fas fa-layer-group"></i> Manage website sections</h2>
        <div class="adm-tiles">
            <a href="facilities.php" class="btn btn-success btn-large"><span><i class="fas fa-school d-block mb-2"></i>Facilities</span></a>
            <a href="events.php" class="btn btn-success btn-large"><span><i class="fas fa-newspaper d-block mb-2"></i>News and Events</span></a>
            <a href="academic_programs.php" class="btn btn-success btn-large"><span><i class="fas fa-graduation-cap d-block mb-2"></i>Academic Programs</span></a>
            <a href="staff.php" class="btn btn-success btn-large"><span><i class="fas fa-chalkboard-user d-block mb-2"></i>Faculty</span></a>
            <a href="admission_page.php" class="btn btn-success btn-large"><span><i class="fas fa-envelope-open-text d-block mb-2"></i>Admissions &amp; Contact Us</span></a>
            <a href="fees_edit.php" class="btn btn-success btn-large"><span><i class="fas fa-money-bill-wave d-block mb-2"></i>Fee Structure</span></a>
            <a href="settings.php" class="btn btn-success btn-large"><span><i class="fas fa-gear d-block mb-2"></i>Settings &amp; Admission Policy</span></a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="adm-card">
                <h2><i class="fas fa-envelope"></i> Latest contact messages</h2>
<?php if (!$messages): ?>
                <p class="adm-help">No messages yet.</p>
<?php else: ?>
                <ul class="adm-list">
<?php foreach ($messages as $m): ?>
                    <li><span><?= h($m['full_name']) ?> <?= trim((string) $m['remarks']) === '' ? '<span class="adm-badge adm-badge--new">New</span>' : '' ?><br><small class="adm-help"><?= h($m['email']) ?></small></span>
                        <span class="adm-meta"><?= h(date('j M Y', strtotime($m['submitted_at']))) ?></span></li>
<?php endforeach; ?>
                </ul>
                <a class="btn btn-sm btn-outline-primary mt-2" href="view_contactus.php">All messages</a>
<?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="adm-card">
                <h2><i class="fas fa-clock-rotate-left"></i> Recently added</h2>
                <ul class="adm-list">
<?php foreach ($recent_ann as $a): ?>
                    <li><span><span class="adm-badge adm-badge--new">Announcement</span> <?= h($a['heading']) ?></span><span class="adm-meta"><?= h(date('j M', strtotime($a['created_at']))) ?></span></li>
<?php endforeach; ?>
<?php foreach ($recent_news as $n): ?>
                    <li><span><span class="adm-badge">News</span> <?= h($n['title']) ?></span><span class="adm-meta"><?= h(date('j M', strtotime($n['date']))) ?></span></li>
<?php endforeach; ?>
                </ul>
                <div class="adm-actions mt-2">
                    <a class="btn btn-sm btn-outline-primary" href="manage_news_events.php">Manage news</a>
                    <a class="btn btn-sm btn-outline-primary" href="view_announcements.php">Manage announcements</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitors (same charts and table as before) -->
    <div class="adm-card">
        <h2><i class="fas fa-chart-line"></i> Visitors, last 7 days</h2>
        <div class="row">
            <div class="col-md-6 mb-3"><h3 class="h6">Daily Visitors</h3><div class="adm-chart"><canvas id="dailyVisitorChart"></canvas></div></div>
            <div class="col-md-6 mb-3"><h3 class="h6">Total Visitors</h3><div class="adm-chart"><canvas id="totalVisitorChart"></canvas></div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Date</th><th>Daily Visitors</th><th>Total Visitors</th></tr></thead>
                <tbody><?= $tableData ?: '<tr><td colspan="3">No visits in the last 7 days.</td></tr>' ?></tbody>
            </table>
        </div>
    </div>
</div>

<script>
const labels = <?= json_encode($dates) ?>;
const dailyData = <?= json_encode($daily_visitors) ?>;
const totalData = <?= json_encode($total_visitors) ?>;
new Chart(document.getElementById('dailyVisitorChart'), {
    type: 'bar',
    data: { labels: labels, datasets: [{ label: 'Daily Visitors', data: dailyData, backgroundColor: '#D4A65A', borderRadius: 6 }] },
    options: { animation: false, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
new Chart(document.getElementById('totalVisitorChart'), {
    type: 'line',
    data: { labels: labels, datasets: [{ label: 'Total Visitors', data: totalData, borderColor: '#192A4A', backgroundColor: 'rgba(25,42,74,.08)', fill: true, tension: .3 }] },
    options: { animation: false, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
