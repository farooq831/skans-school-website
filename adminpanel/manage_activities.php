<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}

include 'db.php';  // Include database connection

// Search, filter and pages (list_tools.php)
require_once __DIR__ . '/list_tools.php';
$p = lt_params(['period' => ['', 'month', 'year']], ['newest', 'oldest', 'az']);
$where = ['1=1']; $types = ''; $params = [];
if ($p['q'] !== '') { $where[] = '(title LIKE ? OR description LIKE ?)'; $types .= 'ss'; $params[] = lt_like($p['q']); $params[] = lt_like($p['q']); }
if ($p['period'] === 'month') { $where[] = "created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"; }
if ($p['period'] === 'year')  { $where[] = "created_at >= DATE_FORMAT(NOW(), '%Y-01-01')"; }
$order = ['newest' => 'id DESC', 'oldest' => 'id ASC', 'az' => 'title ASC'][$p['sort']];
[$activities, $info] = lt_query($conn, 'activities WHERE ' . implode(' AND ', $where), $types, $params, $order, $p);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKANS</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
    <div class="container mt-4">
        <div class="adm-title">
            <h1>Manage Activities</h1>
            <a href="add_activity.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add activity</a>
        </div>
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-info"><?= h($_GET['msg']) ?></div><?php endif; ?>
        <?= lt_search_bar($p, 'Search activities by title or description…',
                [['name' => 'period', 'label' => 'Added', 'options' => ['' => 'Any time', 'month' => 'Added this month', 'year' => 'Added this year']]],
                ['newest' => 'Newest first', 'oldest' => 'Oldest first', 'az' => 'Title A–Z']) ?>
        <?php $bulk_type = 'activities'; include __DIR__ . '/bulk_bar.php'; ?>
        <?php if (!$activities): ?><div class="alert alert-info">No activities found<?= $p['q'] !== '' ? ' for “' . h($p['q']) . '”' : '' ?>.</div><?php endif; ?>
            <ul class="list-group">
                <?php foreach ($activities as $activity) { ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center" style="gap:.75rem">
                        <label class="mb-0 d-flex align-items-center flex-grow-1" style="gap:.75rem; cursor:pointer">
                            <input type="checkbox" class="adm-check" name="ids[]" value="<?= (int) $activity['id'] ?>" form="bulk-form">
                            <?php if ($thumb = upload_url($activity['image'] ?? '')): ?><img class="adm-thumb" src="<?= h($thumb) ?>" alt="" loading="lazy"><?php endif; ?>
                            <span><?php echo h($activity['title']); ?><?php if (!empty($activity['created_at'])): ?><small class="d-block adm-help">Added <?= h(date('j M Y', strtotime($activity['created_at']))) ?></small><?php endif; ?></span>
                        </label>
                        <span>
                            <a href="edit_activity.php?id=<?php echo h($activity['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_activity.php?id=<?php echo h($activity['id']); ?>" class="btn btn-danger btn-sm">Delete</a>
                        </span>
                    </li>
                <?php } ?>
            </ul>
        <?= lt_pagination($p, $info, 'activities') ?>
    </div>

 <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
