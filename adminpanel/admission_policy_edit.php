<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
// Edit the admission policy shown on the website (Admission Policy, Fee Structure and SKANS Policies pages).
// Saved in the database (site_settings 'admission_policy'); "Restore original" goes back to data/fees.php.

$defaults = require SK_ROOT . '/data/fees.php';
$sections = [
    'general'    => ['Admission',                      'One rule per line, e.g. "Merit is the sole criterion for admission."'],
    'documents'  => ['Required documents',             'One document per line, e.g. "Copy of father’s CNIC – mandatory"'],
    'fee_rules'  => ['Fee rules',                      'One rule per line, e.g. "Monthly tuition fee must be paid by the 10th of every month."'],
    'attendance' => ['Attendance',                     'One rule per line, e.g. "Minimum 90% attendance is required to appear in the annual exams."'],
    'leaving'    => ['Leaving the school and refunds', 'One rule per line, e.g. "School leaving certificates are issued after all dues are cleared."'],
];
$saved_policy = settings_all($conn)['admission_policy'] ?? [];
$value_of = function (string $key) use ($defaults, $saved_policy) {
    if (isset($saved_policy[$key])) return $saved_policy[$key];
    if ($key === 'fee_rules') return $defaults['fee_rules'];
    return $defaults['admission_policy'][$key];
};
$values = [];
foreach ($sections as $key => $_) {
    $values[$key] = implode("\n", (array) $value_of($key));
}
$values['declaration'] = (string) $value_of('declaration');
$errors = [];
$msg = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    if (isset($_POST['restore'])) {
        settings_delete($conn, 'admission_policy');
        header('Location: admission_policy_edit.php?msg=restored');
        exit;
    }
    $policy = [];
    foreach ($sections as $key => [$label]) {
        $values[$key] = (string) ($_POST[$key] ?? '');
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $values[$key])), 'strlen'));
        if (!$lines) $errors[$key] = 'Please add at least one line.';
        foreach ($lines as $line) {
            if (mb_strlen($line) > 600) { $errors[$key] = 'A line is too long (max 600 characters).'; break; }
        }
        $policy[$key] = $lines;
    }
    $values['declaration'] = trim((string) ($_POST['declaration'] ?? ''));
    if ($values['declaration'] === '') $errors['declaration'] = 'Please enter the declaration text.';
    $policy['declaration'] = $values['declaration'];

    if (!$errors) {
        settings_save($conn, 'admission_policy', $policy);
        header('Location: admission_policy_edit.php?msg=saved');
        exit;
    }
}
$is_custom = !empty($saved_policy);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Policy | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page">
    <div class="adm-title">
        <div>
            <h1>Admission policy</h1>
            <p class="adm-sub">Shown on the website's <strong>Admission Policy</strong>, <strong>Fee Structure</strong> and <strong>SKANS Policies</strong> pages. Write one rule per line; each line becomes one bullet point.</p>
        </div>
        <a class="btn btn-outline-primary" href="../admission-policy.php" target="_blank" rel="noopener"><i class="fas fa-eye"></i> View on website</a>
    </div>

<?php if ($msg === 'saved'): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Admission policy saved. It is now shown on the website.</div>
<?php elseif ($msg === 'restored'): ?>
    <div class="alert alert-success"><i class="fas fa-rotate-left"></i> The original policy text has been restored.</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">Please check the sections marked in red.</div>
<?php endif; ?>
    <p class="adm-help"><?= $is_custom ? '<span class="adm-badge adm-badge--new">Edited</span> The website shows your edited version.' : '<span class="adm-badge">Original</span> The website shows the original policy (from the admission form, June 2026).' ?></p>

    <form method="POST" class="adm-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
<?php foreach ($sections as $key => [$label, $placeholder]): ?>
        <div class="adm-card">
            <h2><i class="fas fa-list-check"></i> <?= h($label) ?></h2>
            <textarea class="form-control<?= isset($errors[$key]) ? ' is-invalid' : '' ?>" name="<?= $key ?>" rows="<?= max(3, substr_count($values[$key], "\n") + 2) ?>" placeholder="<?= h($placeholder) ?>"><?= h($values[$key]) ?></textarea>
<?php if (isset($errors[$key])): ?><div class="invalid-feedback"><?= h($errors[$key]) ?></div><?php endif; ?>
            <small class="form-text adm-help"><span data-lines="<?= $key ?>"></span></small>
        </div>
<?php endforeach; ?>
        <div class="adm-card">
            <h2><i class="fas fa-signature"></i> Declaration by parents</h2>
            <textarea class="form-control<?= isset($errors['declaration']) ? ' is-invalid' : '' ?>" name="declaration" rows="3" placeholder="Parents sign a declaration on the admission form confirming that…"><?= h($values['declaration']) ?></textarea>
<?php if (isset($errors['declaration'])): ?><div class="invalid-feedback"><?= h($errors['declaration']) ?></div><?php endif; ?>
        </div>
        <div class="adm-sticky d-flex flex-wrap align-items-center" style="gap:.5rem">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Save policy</button>
            <a href="admin_panel.php" class="btn btn-link">Cancel</a>
<?php if ($is_custom): ?>
            <button type="submit" name="restore" value="1" class="btn btn-outline-danger ml-auto" onclick="return confirm('Go back to the original policy text? Your edits will be removed.');"><i class="fas fa-rotate-left"></i> Restore original</button>
<?php endif; ?>
        </div>
    </form>
</div>
<script>
// Live "N points" counter under each list.
document.querySelectorAll('[data-lines]').forEach(function (el) {
    var box = document.querySelector('textarea[name="' + el.getAttribute('data-lines') + '"]');
    function upd() { var n = box.value.split(/\n/).filter(function (l) { return l.trim() !== ''; }).length; el.textContent = n + (n === 1 ? ' point' : ' points') + ' on the website'; }
    box.addEventListener('input', upd); upd();
});
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
