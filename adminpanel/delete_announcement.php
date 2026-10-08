<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';

// Delete one announcement (link from view_announcements.php carries a security token).
// The picture file is kept on disk. Old version: _old/adminpanel/oct3/.
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: view_announcements.php?msg=notfound');
    exit;
}
admin_require_csrf();

$stmt = $conn->prepare('DELETE FROM announcements WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();

header('Location: view_announcements.php?msg=' . ($deleted ? 'deleted' : 'notfound'));
exit;
