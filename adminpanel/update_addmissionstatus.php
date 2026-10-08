<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    admin_require_csrf();
    $allowed_status = ['Pending', 'Visited, Not Enrolled', 'Not Yet Visited', 'Visited and Enrolled', 'Not Interested'];
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    $remarks = trim((string) ($_POST['remarks'] ?? ''));

    if (!in_array($status, $allowed_status, true)) {
        http_response_code(400);
        exit('error: unknown status');
    }

    // Prepared statement: the values cannot change the SQL
    $stmt = $conn->prepare("UPDATE admission_form SET status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $remarks, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
    $stmt->close();
}

$conn->close();
?>
