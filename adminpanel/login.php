<?php
require __DIR__ . '/session.php';
include 'db.php';  // Include the database connection file
date_default_timezone_set('Asia/Karachi'); // Set timezone to Pakistan Standard Time (PST)

// Lock-out after too many wrong passwords: 5 tries per username + IP address in 15 minutes.
const LOGIN_MAX_TRIES = 5;
const LOGIN_WINDOW_MINUTES = 15;
$conn->query("CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    INDEX (username, ip_address, attempted_at)
)");

if (!empty($_GET['expired'])) {
    $error_message = 'You were logged out after 60 minutes without activity. Please log in again.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);

    $count = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempted_at > ?");
    $count->bind_param("sss", $username, $ip, $since);
    $count->execute();
    $count->bind_result($recent_failures);
    $count->fetch();
    $count->close();

    if ($recent_failures >= LOGIN_MAX_TRIES) {
        $error_message = 'Too many failed attempts. Please wait ' . LOGIN_WINDOW_MINUTES . ' minutes and try again.';
    } else {
        // Query to fetch user by username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;
        $stmt->close();

        // Check if the password matches (using password_verify)
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);   // new session ID after login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_seen'] = time();

            $clear = $conn->prepare("DELETE FROM login_attempts WHERE username = ? AND ip_address = ?");
            $clear->bind_param("ss", $username, $ip);
            $clear->execute();
            $clear->close();

            // Get current date, time, and day
            $login_date = date("Y-m-d");
            $login_time = date("H:i:s");
            $login_day = date("l");  // Full day name (e.g., Monday)

            // Insert login history
            $history_stmt = $conn->prepare("INSERT INTO login_history (user_id, username, login_date, login_time, login_day) VALUES (?, ?, ?, ?, ?)");
            $history_stmt->bind_param("issss", $user['id'], $user['username'], $login_date, $login_time, $login_day);
            $history_stmt->execute();
            $history_stmt->close();

            header("Location: admin_panel.php");
            exit;
        }

        // Same message for a wrong username or a wrong password, so usernames cannot be guessed.
        $now = date('Y-m-d H:i:s');
        $log = $conn->prepare("INSERT INTO login_attempts (username, ip_address, attempted_at) VALUES (?, ?, ?)");
        $log->bind_param("sss", $username, $ip, $now);
        $log->execute();
        $log->close();
        $error_message = "Incorrect username or password.";
    }
}


// School details for the login page (from Settings, falling back to data/site.php).
require_once __DIR__ . '/admin_lib.php';
$school = admin_site($conn);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Admin login | <?= h($school['full_name']) ?></title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?: 1 ?>">
</head>
<body class="adm-login-page">
<!-- Login page redesigned Oct 2026 (old version: _old/adminpanel/oct5/login-before-redesign.php). -->
<main class="adm-login">
    <section class="adm-login__brand" aria-label="About the school">
        <div class="adm-login__brand-inner">
            <div class="adm-login__logo">
                <img src="../assets/img/skans-logo-120.png" srcset="../assets/img/skans-logo-228.png 2x" width="252" height="120" alt="<?= h($school['name']) ?> – <?= h($school['tagline'] ?? 'Education for life') ?>">
            </div>
            <p class="adm-login__eyebrow">Admin panel</p>
            <h1 class="adm-login__school"><?= h($school['name']) ?></h1>
            <p class="adm-login__campus"><?= h($school['campus']) ?> · <?= h($school['classes']) ?></p>
            <p class="adm-login__tagline">“<?= h($school['tagline'] ?? 'Education for life') ?>”</p>
            <ul class="adm-login__info">
                <li><i class="fas fa-location-dot" aria-hidden="true"></i><span><?= h($school['address']) ?></span></li>
<?php foreach ($school['phones'] as $phone): ?>
                <li><i class="fas fa-phone" aria-hidden="true"></i><span><?= h(trim(str_replace('/ WhatsApp', '', $phone['label']))) ?>: <?= h($phone['display']) ?></span></li>
<?php endforeach; ?>
                <li><i class="fas fa-envelope" aria-hidden="true"></i><span><?= h($school['email']) ?></span></li>
            </ul>
        </div>
    </section>

    <section class="adm-login__panel" aria-labelledby="login-title">
        <div class="adm-login__card">
            <img class="adm-login__card-logo" src="../assets/img/skans-logo-60.png" srcset="../assets/img/skans-logo-120.png 2x" width="126" height="60" alt="">
            <h2 class="adm-login__title" id="login-title">Welcome back</h2>
            <p class="adm-login__sub">Sign in to manage the <?= h($school['campus']) ?> website.</p>
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger" role="alert"><i class="fas fa-circle-exclamation"></i> <?php echo h($error_message); ?></div>
            <?php } ?>
            <form method="POST" action="login.php" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="adm-login__field">
                        <i class="fas fa-user" aria-hidden="true"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" autocomplete="username" required autofocus value="<?= h($_POST['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="adm-login__field">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="adm-login__eye" id="toggle-password" aria-label="Show password" aria-pressed="false"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg adm-login__submit"><i class="fas fa-right-to-bracket"></i> Login</button>
            </form>
            <div class="adm-login__links">
                <a href="forgot_password.php"><i class="fas fa-key"></i> Forgot Password?</a>
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to website</a>
            </div>
            <p class="adm-login__note"><i class="fas fa-shield-halved"></i> For school staff only. After 5 wrong tries, login is paused for 15 minutes.</p>
        </div>
        <p class="adm-login__copy">© <?= date('Y') ?> <?= h($school['name']) ?></p>
    </section>
</main>
<script>
// Show / hide password
document.getElementById('toggle-password').addEventListener('click', function () {
    var input = document.getElementById('password'), show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    this.setAttribute('aria-pressed', show ? 'true' : 'false');
    this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    this.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
});
</script>
</body>
</html>
