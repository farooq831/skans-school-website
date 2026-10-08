<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}

include 'db.php';  // Include database connection

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
        .btn-large {
    width: 4in;       /* Width of 4 inches */
    height: 2in;      /* Height of 4 inches */
    font-size: 1.5rem; /* Larger font size for better visibility */
    padding: 1rem;    /* Padding inside the button */
    display: flex;    /* Center text vertically */
    align-items: center; /* Center text vertically */
    justify-content: center; /* Center text horizontally */
}

    </style>
</head>
<body>
<?php include 'nav.php'; ?>

    <div class="container mt-4">
    <div class="row">

        <div class="col-md-3 mb-4 d-flex justify-content-center">
            <a href="add_classes.php" class="btn btn-success btn-large">Add New Academic Program</a>
        </div>
        <div class="col-md-3 mb-4 d-flex justify-content-center">
            <a href="manage_classes.php" class="btn btn-info btn-large">Manage Academic Program</a>
        </div>
 
        <div class="col-12"><div class="alert alert-info">
            <strong>Entries #1, #2 and #3 are the three level cards on the website</strong> (Early Years, Juniors, Seniors on the Home, Admissions and Programs Offered pages).
            Open <em>Manage Academic Program</em> and click <em>Edit</em> on #1, #2 or #3 to change a card: the change shows on the website straight away.
            Please do not delete entries #1&ndash;#3.
        </div></div>
    </div>
</div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
