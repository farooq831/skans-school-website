<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php'; // Include database connection

if (isset($_GET['id'])) {
    admin_require_csrf();
    $id = (int) $_GET['id'];

    // Delete the faculty member (prepared statement: the id cannot change the SQL)
    $stmt = $conn->prepare("DELETE FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo '<script>alert("Faculty member deleted successfully"); window.location.href="manage_faculty.php";</script>';
    } else {
        echo "Error deleting record. Please try again.";
    }
}

$conn->close();
?>
