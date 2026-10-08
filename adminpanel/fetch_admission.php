<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
include 'db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('Invalid request.');
}

$stmt = $conn->prepare("SELECT * FROM admission_form WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    $labels = [
        'student_name' => 'Student Name', 'dob' => 'Date of Birth', 'class' => 'Class',
        'father_name' => 'Father Name', 'mother_name' => 'Mother Name', 'address' => 'Address',
        'mobile' => 'Mobile', 'allergy' => 'Allergy', 'health_issues' => 'Health Issues',
        'precaution' => 'Precaution', 'status' => 'Status',
    ];
    foreach ($labels as $key => $label) {
        echo '<p><strong>' . $label . ':</strong> ' . h($row[$key] ?? '') . '</p>';
    }
} else {
    echo "No details found.";
}

$conn->close();
