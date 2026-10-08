<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
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
</head>
<body>
<?php include 'nav.php'; ?>
<section class="section">
    <div class="container">
        <div class="heading-and-button margin-bottom-56">
            <h2 class="h2">Manage Faculty</h2>
            <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><i class="fas fa-check"></i> <?= h($_GET['msg']) ?></div><?php endif; ?>
        </div>
        
        <?php
        // Database connection
        include 'db.php';

             // Fetch faculty data
        $sql = "SELECT id, name, position, image FROM faculty";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<table class="table table-bordered">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Name</th>';
            echo '<th>Position</th>';
            echo '<th>Image</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>'.$row["id"].'</td>';
                echo '<td>'.$row["name"].'</td>';
                echo '<td>'.$row["position"].'</td>';
                 echo '<td><img src="'.h(upload_url($row["image"])).'" alt="'.$row["name"].'" style="width: 100px; height: auto;"></td>';
                echo '<td>';
                echo '<a href="edit_faculty.php?id='.$row["id"].'" class="btn btn-primary btn-sm">Edit</a> ';
                echo '<a href="delete_faculty.php?id='.(int) $row["id"].'&token='.urlencode(admin_csrf_token()).'" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this faculty member?\')">Delete</a>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="alert alert-info">No faculty members found.</div>';
        }

        $conn->close();
        ?>
    </div>
</section>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>




