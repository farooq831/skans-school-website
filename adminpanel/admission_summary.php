<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}
include 'db.php';

// Calculate total number of admission forms received
$total_forms_sql = "SELECT COUNT(*) AS total_forms FROM admission_form";
$total_forms_result = $conn->query($total_forms_sql);
$total_forms = $total_forms_result->fetch_assoc()['total_forms'];

// Calculate total number of enrolled students
$total_enrolled_sql = "SELECT COUNT(*) AS total_enrolled FROM admission_form WHERE status = 'Visited and Enrolled'";
$total_enrolled_result = $conn->query($total_enrolled_sql);
$total_enrolled = $total_enrolled_result->fetch_assoc()['total_enrolled'];

// Calculate total number of visited but not enrolled
$total_visited_not_enrolled_sql = "SELECT COUNT(*) AS total_visited_not_enrolled FROM admission_form WHERE status = 'Visited, Not Enrolled'";
$total_visited_not_enrolled_result = $conn->query($total_visited_not_enrolled_sql);
$total_visited_not_enrolled = $total_visited_not_enrolled_result->fetch_assoc()['total_visited_not_enrolled'];

// Calculate total number of not yet visited
$total_not_visited_sql = "SELECT COUNT(*) AS total_not_visited FROM admission_form WHERE status = 'Not Yet Visited'";
$total_not_visited_result = $conn->query($total_not_visited_sql);
$total_not_visited = $total_not_visited_result->fetch_assoc()['total_not_visited'];

// Get total number of unique visitors
$total_visitors_sql = "SELECT COUNT(DISTINCT ip_address) AS total_visitors FROM visitors";
$total_visitors_result = $conn->query($total_visitors_sql);
$total_visitors = $total_visitors_result->fetch_assoc()['total_visitors'];

$conn->close();
?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKANS</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Admission Summary</h2>
        <div class="row">
             <!-- Add this code in your Admission Summary Page -->
            <div class="col-md-6">
                <div class="card bg-primary text-white mb-3">
                    <div class="card-header">Total Website Visitors</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_visitors; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light mb-3">
                    <div class="card-header">Total Forms Received</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_forms; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white mb-3">
                    <div class="card-header">Total Enrolled</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_enrolled; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white mb-3">
                    <div class="card-header">Visited but Not Enrolled</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_visited_not_enrolled; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white mb-3">
                    <div class="card-header">Not Yet Visited</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_not_visited; ?></h5>
                    </div>
                </div>
            </div>
           

        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
