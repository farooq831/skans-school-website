<?php
require __DIR__ . '/auth.php'; // login check
require __DIR__ . '/image_upload.php';
include 'db.php';

// Edit an announcement. Rebuilt Oct 2026 (old version: _old/adminpanel/oct3/).
$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM announcements WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$row) {
    header('Location: view_announcements.php?msg=notfound');
    exit;
}

$form_values = [
    'heading' => $row['heading'] ?? '',
    'announcement_text' => $row['announcement_text'] ?? '',
    'image' => ($row['image'] && is_file(UPLOAD_DIR . $row['image'])) ? $row['image'] : '',
];
// Older entries were saved HTML-encoded ("&amp;"); show plain text in the form.
foreach (['heading', 'announcement_text'] as $k) {
    for ($i = 0; $i < 3; $i++) {
        $decoded = html_entity_decode($form_values[$k], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $form_values[$k]) break;
        $form_values[$k] = $decoded;
    }
}
$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    $form_values['heading'] = trim((string) ($_POST['heading'] ?? ''));
    $form_values['announcement_text'] = trim((string) ($_POST['announcement_text'] ?? ''));

    if ($form_values['heading'] === '' || mb_strlen($form_values['heading']) > 150) {
        $form_errors[] = 'Please enter a title (up to 150 characters).';
    }
    if ($form_values['announcement_text'] === '' || mb_strlen($form_values['announcement_text']) > DESCRIPTION_MAX) {
        $form_errors[] = 'Please enter the announcement text (up to ' . DESCRIPTION_MAX . ' characters; it has ' . mb_strlen($form_values['announcement_text']) . ').';
    }

    if (!$form_errors) {
        $image = $row['image'];
        $upload = handle_image_upload('image');
        if ($upload['error']) {
            $form_errors[] = $upload['error'];
        } elseif ($upload['file']) {
            $image = $upload['file'];          // new picture replaces the old one
        } elseif (!empty($_POST['remove_image'])) {
            $image = null;                     // picture removed
        }

        if (!$form_errors) {
            $save = $conn->prepare('UPDATE announcements SET heading = ?, announcement_text = ?, image = ? WHERE id = ?');
            $save->bind_param('sssi', $form_values['heading'], $form_values['announcement_text'], $image, $id);
            if ($save->execute()) {
                header('Location: view_announcements.php?msg=updated');
                exit;
            }
            error_log('Update announcement failed: ' . $save->error);
            $form_errors[] = 'The changes could not be saved. Please try again.';
        }
    }
}

$form_title = 'Edit Announcement';
$submit_label = 'Save changes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Edit Announcement | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>body { background-color: #f0f4f8; }</style>
</head>
<body>
<?php include 'nav.php'; ?>
<?php include __DIR__ . '/announcement_form.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
