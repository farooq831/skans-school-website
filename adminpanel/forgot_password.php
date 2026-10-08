<?php
require __DIR__ . '/session.php';
include 'db.php'; // Database connection
require __DIR__ . '/../vendor/autoload.php'; // Load Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format!'); window.location.href='forgot_password.php';</script>";
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Generate a secure reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Store token in DB
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE username = ?");
        $stmt->bind_param("sss", $token, $expiry, $username);
        $stmt->execute();

        // Send reset email using PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings: email login of this campus (campus-data/<campus>/config.php 'mail'), not in the code.
            $mc = campus_config()['mail'] ?? [];
            $mail->isSMTP();
            $mail->Host       = $mc['host'] ?? 'localhost'; // SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = $mc['user'] ?? '';
            $mail->Password   = $mc['pass'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) ($mc['port'] ?? 587);

            // Email settings
            $mail->setFrom($mc['from'] ?? 'noreply@skansschool.edu.pk', $mc['from_name'] ?? 'SKANS');
            $mail->addAddress($email);
            if (!empty($mc['copy_to'])) $mail->addAddress($mc['copy_to']);
            $mail->Subject = 'Password Reset Request';
            // Link on this website's own address (works for every campus).
            $reset_link = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
                        . rtrim(str_replace(chr(92), '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/reset_password.php?token=' . $token;
            $mail->Body = "Hello " . htmlspecialchars($username) . ",\n\nClick the link below to reset your password:\n\n" .
                          $reset_link . "\n\nThis link will expire in 1 hour.";

            $mail->send();

            // Redirect after success
            echo "<script>alert('If the username and email match an admin account, a reset link has been sent to that email.'); window.location.href='login.php';</script>";
        } catch (Exception $e) {
            error_log('Password reset email failed: ' . $mail->ErrorInfo);
            echo "<script>alert('Sorry, the email could not be sent. Please contact the school office.');</script>";
        }
    } else {
        // Same message whether or not the account exists, so usernames cannot be guessed.
        echo "<script>alert('If the username and email match an admin account, a reset link has been sent to that email.'); window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKANS</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-lg" style="width: 400px;">
        <h4 class="text-center">Reset Password</h4>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter Username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter Registered Email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form> <br><p class="text-center">OR</p> 
        <a href="login.php" class="btn btn-success">Go Back to Login Page</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
