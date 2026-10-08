<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
// Bulk delete for News & Events posts and co-curricular activities.
// Called by the "Delete selected" button on manage_news_events.php and manage_activities.php.
// Picture files are kept on the server (they may be used elsewhere); only the records are deleted.

$types = [
    'news'       => ['table' => 'news_events', 'back' => 'manage_news_events.php', 'label' => 'news/event post'],
    'activities' => ['table' => 'activities',  'back' => 'manage_activities.php',  'label' => 'activity'],
];
$type = $_POST['type'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($types[$type])) {
    header('Location: admin_panel.php');
    exit;
}
admin_require_csrf();
$t = $types[$type];

$ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])), function ($id) { return $id > 0; })));
if (!$ids) {
    header('Location: ' . $t['back'] . '?msg=' . urlencode('Nothing selected.'));
    exit;
}
if (count($ids) > 500) {
    $ids = array_slice($ids, 0, 500);
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("DELETE FROM {$t['table']} WHERE id IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$n = $stmt->affected_rows;
$stmt->close();

$msg = $n . ' ' . $t['label'] . ($n === 1 ? '' : 's') . ' deleted.';
header('Location: ' . $t['back'] . '?msg=' . urlencode($msg));
exit;
