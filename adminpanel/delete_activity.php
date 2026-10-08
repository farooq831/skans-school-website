<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php'; // Include database connection

// Check if ID is set and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger' role='alert'>Invalid service ID.</div>";
    exit;
}

$activity_id = intval($_GET['id']);

// Fetch existing service data
$stmt = $conn->prepare("SELECT * FROM activities WHERE id = ?");
$stmt->bind_param("i", $activity_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger' role='alert'>Activity  not found.</div>";
    exit;
}

// Delete the service if confirmed
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    admin_require_csrf();
    // Delete the service record from the database
    $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();

    // Optionally, delete the associated image file from the server
    $service = $result->fetch_assoc();
    $image_path = upload_dir() . basename((string) $service['image']);
    if (file_exists($image_path)) {
        unlink($image_path);
    }

    header("Location: manage_activities.php");
    exit;
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
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this service? This action cannot be undone.');
        }
    </script>
</head>
<body>
<?php include 'nav.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Delete Activity</h2>
        <form method="POST" action="" onsubmit="return confirmDelete();">
            <input type="hidden" name="csrf_token" value="<?php echo h(admin_csrf_token()); ?>">
            <div class="alert alert-warning" role="alert">
                You are about to delete the activity "<strong><?php echo h($result->fetch_assoc()['title']); ?></strong>". This action cannot be undone.
            </div>
            <button type="submit" class="btn btn-danger">Delete Activity</button>
            <a href="admin_panel.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
