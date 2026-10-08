<?php
// adminpanel/auth.php : the login check. The first line of every admin page is:
//     require __DIR__ . '/auth.php';
// Not logged in -> sent to the login page. Idle for 60 minutes -> logged out.

require_once __DIR__ . '/session.php';

const ADMIN_IDLE_LIMIT = 3600; // seconds

if (!empty($_SESSION['user_id']) && isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > ADMIN_IDLE_LIMIT) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

if (empty($_SESSION['user_id'])) {
    $is_ajax = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' || !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
    if ($is_ajax) {
        http_response_code(401);
        exit('Not logged in.');
    }
    header('Location: login.php');
    exit;
}

$_SESSION['last_seen'] = time();
