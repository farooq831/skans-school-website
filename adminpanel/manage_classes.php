<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}

include 'db.php';  // Include database connection

// Handle delete request
if (isset($_GET['delete_id'])) {
    admin_require_csrf();
    $delete_id = (int) $_GET['delete_id'];
    $delete_sql = "DELETE FROM academic_prog WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        echo '<script>alert("Record deleted successfully.");</script>';
    } else {
        echo '<script>alert("Error deleting record: ' . $stmt->error . '");</script>';
    }
    $stmt->close();
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
    <style>
        body {
            background-color: #f0f4f8; /* Light background for contrast */
        }
        .container {
            margin-top: 50px;
        }
        .table {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container">
    <h2 class="text-center">Academic Programs</h2>
    <?php // Database connection
        include 'db.php';

        // Fetch faculty data
        $sql = "SELECT * FROM academic_prog";
        $result = $conn->query($sql);
        ?>

    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Sub Title</th>
                <th>Clsss Name</th>
                <th>Description</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['heading'] . '</td>';
                    echo '<td>' . $row['subheading'] . '</td>';
                    echo '<td>' . $row['name'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td><img src="' . h(upload_url(basename((string) $row['image']))) . '" width="100" height="60" style="object-fit:cover"></td>';
                    echo '<td>
                            <a href="edit_classes.php?id=' . $row['id'] . '" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_classes.php?delete_id=' . (int) $row['id'] . '&token=' . urlencode(admin_csrf_token()) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</a>
                          </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="7" class="text-center">No records found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
