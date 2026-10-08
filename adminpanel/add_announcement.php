<?php
require __DIR__ . '/auth.php'; // login check
require __DIR__ . '/image_upload.php';
include 'db.php';

// Add a new announcement. Rebuilt Oct 2026 (old version: _old/adminpanel/oct3/).
$form_values = ['heading' => '', 'announcement_text' => '', 'image' => ''];
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
        $upload = handle_image_upload('image');
        if ($upload['error']) {
            $form_errors[] = $upload['error'];
        } else {
            $image = $upload['file']; // may be null: announcements without a picture are allowed
            $stmt = $conn->prepare('INSERT INTO announcements (image, heading, announcement_text) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $image, $form_values['heading'], $form_values['announcement_text']);
            if ($stmt->execute()) {
                header('Location: view_announcements.php?msg=added');
                exit;
            }
            error_log('Add announcement failed: ' . $stmt->error);
            $form_errors[] = 'The announcement could not be saved. Please try again.';
        }
    }
}

$form_title = 'Add New Announcement';
$submit_label = 'Publish announcement';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Add Announcement | SKANS Admin</title>
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
