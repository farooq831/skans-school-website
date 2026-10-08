<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}

include 'db.php';  // Include database connection

// Fetch services and activities
$services = [];
if ($result = $conn->query("SELECT * FROM services")) {
    $services = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "<div class='alert alert-danger'>Error fetching services: " . h($conn->error) . "</div>";
}
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
    <div class="container mt-4">
        <div class="adm-title">
            <h1>Manage Facilities &amp; Services</h1>
            <a href="add_service.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add facility</a>
        </div>
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><i class="fas fa-check"></i> <?= h($_GET['msg']) ?></div><?php endif; ?>
        <ul class="list-group mb-4">
            <?php foreach ($services as $service) { ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo h($service['title']); ?>
                    <span>
                        <a href="edit_service.php?id=<?php echo h($service['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_service.php?id=<?php echo h($service['id']); ?>" class="btn btn-danger btn-sm">Delete</a>
                    </span>
                </li>
            <?php } ?>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
