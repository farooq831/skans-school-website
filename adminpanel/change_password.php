<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
// Change the password of the admin who is logged in (user menu > Change password).
// Needs the current password. Only the encrypted form (password_hash) is stored.

const PW_MIN = 8;
$errors = [];
$done = isset($_GET['done']);
$uid = (int) ($_SESSION['user_id'] ?? 0);

$stmt = $conn->prepare('SELECT id, username, password FROM users WHERE id = ?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    $current = (string) ($_POST['current'] ?? '');
    $new = (string) ($_POST['new'] ?? '');
    $again = (string) ($_POST['again'] ?? '');

    if (!password_verify($current, $user['password'])) {
        $errors['current'] = 'The current password is not correct.';
    }
    if (mb_strlen($new) < PW_MIN) {
        $errors['new'] = 'Use at least ' . PW_MIN . ' characters.';
    } elseif (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
        $errors['new'] = 'Use letters and at least one number.';
    } elseif (strcasecmp($new, $user['username']) === 0 || stripos($new, $user['username']) !== false) {
        $errors['new'] = 'The password must not contain the username.';
    } elseif ($new === $current) {
        $errors['new'] = 'Choose a password different from the current one.';
    }
    if (!isset($errors['new']) && $new !== $again) {
        $errors['again'] = 'The two new passwords are not the same.';
    }

    if (!$errors) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $up = $conn->prepare('UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?');
        $up->bind_param('si', $hash, $uid);
        $up->execute();
        $up->close();
        $clear = $conn->prepare('DELETE FROM login_attempts WHERE username = ?');
        $clear->bind_param('s', $user['username']);
        $clear->execute();
        $clear->close();
        session_regenerate_id(true);   // new session after a password change
        header('Location: change_password.php?done=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change password | SKANS Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .pw-wrap { max-width: 560px; }
        .pw-field { position: relative; }
        .pw-field .form-control { padding-right: 2.75rem; }
        .pw-eye { position: absolute; right: .35rem; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: #5A6478; padding: .35rem .5rem; }
        .pw-meter { height: 6px; border-radius: 3px; background: #E7E1D6; margin-top: .5rem; overflow: hidden; }
        .pw-meter span { display: block; height: 100%; width: 0; transition: width .2s ease, background .2s ease; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container adm-page pw-wrap">
    <div class="adm-title">
        <div>
            <h1>Change password</h1>
            <p class="adm-sub">For the admin account <strong><?= h($user['username']) ?></strong>. You stay logged in after the change.</p>
        </div>
    </div>

<?php if ($done): ?>
    <div class="alert alert-success"><i class="fas fa-check"></i> Password changed. Use the new password next time you log in.</div>
<?php endif; ?>
<?php if ($errors): ?>
    <div class="alert alert-danger">Please check the fields marked in red.</div>
<?php endif; ?>

    <form method="POST" class="adm-card adm-form" novalidate autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
        <input type="text" name="username" value="<?= h($user['username']) ?>" autocomplete="username" hidden>

        <div class="form-group">
            <label for="current">Current password</label>
            <div class="pw-field">
                <input class="form-control<?= isset($errors['current']) ? ' is-invalid' : '' ?>" type="password" id="current" name="current" autocomplete="current-password" required>
                <button class="pw-eye" type="button" data-show="current" aria-label="Show password"><i class="fas fa-eye"></i></button>
            </div>
<?php if (isset($errors['current'])): ?><div class="invalid-feedback d-block"><?= h($errors['current']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="new">New password</label>
            <div class="pw-field">
                <input class="form-control<?= isset($errors['new']) ? ' is-invalid' : '' ?>" type="password" id="new" name="new" minlength="<?= PW_MIN ?>" autocomplete="new-password" required>
                <button class="pw-eye" type="button" data-show="new" aria-label="Show password"><i class="fas fa-eye"></i></button>
            </div>
            <div class="pw-meter" aria-hidden="true"><span id="meter"></span></div>
            <small class="form-text adm-help" id="meter-text">At least <?= PW_MIN ?> characters, with letters and a number. Longer is safer.</small>
<?php if (isset($errors['new'])): ?><div class="invalid-feedback d-block"><?= h($errors['new']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="again">New password again</label>
            <div class="pw-field">
                <input class="form-control<?= isset($errors['again']) ? ' is-invalid' : '' ?>" type="password" id="again" name="again" autocomplete="new-password" required>
                <button class="pw-eye" type="button" data-show="again" aria-label="Show password"><i class="fas fa-eye"></i></button>
            </div>
<?php if (isset($errors['again'])): ?><div class="invalid-feedback d-block"><?= h($errors['again']) ?></div><?php endif; ?>
        </div>

        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-key"></i> Change password</button>
            <a href="admin_panel.php" class="btn btn-link">Cancel</a>
        </div>
    </form>
    <p class="adm-help"><i class="fas fa-circle-info"></i> Forgot the current password? Use “Forgot Password?” on the login page, or ask the IT person to reset it in phpMyAdmin.</p>
</div>
<script>
document.querySelectorAll('[data-show]').forEach(function (b) {
    b.addEventListener('click', function () {
        var i = document.getElementById(b.getAttribute('data-show'));
        var show = i.type === 'password'; i.type = show ? 'text' : 'password';
        b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        b.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
    });
});
(function () {
    var i = document.getElementById('new'), m = document.getElementById('meter'), t = document.getElementById('meter-text');
    var base = t.textContent;
    i.addEventListener('input', function () {
        var v = i.value, s = 0;
        if (v.length >= 8) s++; if (v.length >= 12) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++; if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
        var levels = [['0%', '#E7E1D6', base], ['25%', '#A12A2A', 'Weak'], ['50%', '#C77A1A', 'Fair'], ['75%', '#BD852C', 'Good'], ['100%', '#1F6B3A', 'Strong']];
        var l = v ? levels[Math.max(1, Math.min(4, s - 1))] : levels[0];
        m.style.width = l[0]; m.style.background = l[1]; t.textContent = v ? 'Strength: ' + l[2] : base;
    });
})();
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
