<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    admin_require_csrf();
    $id = (int) $_POST['id'];
    $remarks = $_POST['remarks'];
    $responded_at = date('Y-m-d H:i:s'); // Current Date-Time

    $sql = "UPDATE contact_form_submissions SET remarks = ?, responded_at = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $remarks, $responded_at, $id);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error";
    }
    
    $stmt->close();
    $conn->close();
}
?>
