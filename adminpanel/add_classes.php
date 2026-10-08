<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $classname = trim($_POST['classname']);
    $description = trim($_POST['description']);

    // Handle image upload
    $target_dir = "upload/images/";
    $file_name = basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = uniqid() . '.' . $imageFileType; // Generate a unique name
    $target_file = $target_dir . $new_file_name;
    $uploadOk = 1;

    // Check for file upload errors
    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        echo '<script>alert("An error occurred during file upload.");</script>';
        $uploadOk = 0;
    }

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo '<script>alert("File is not an image.");</script>';
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_file_types = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($imageFileType, $allowed_file_types)) {
        echo '<script>alert("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");</script>';
        $uploadOk = 0;
    }

    // Check file size (limit: IMAGE_MAX_BYTES = 2 MB)
    if ($_FILES["image"]["size"] > IMAGE_MAX_BYTES) {
        echo '<script>alert("The picture is too large. Maximum size is ' . IMAGE_MAX_LABEL . '.");</script>';
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo '<script>alert("Sorry, your file was not uploaded.");</script>';
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Insert data into the database
            $sql = "INSERT INTO academic_prog (heading, subheading, name, description, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $title, $subtitle, $classname, $description,  $target_file);

            if ($stmt->execute()) {
                // Use JavaScript to redirect after a successful operation
                echo '<script>alert("New record created successfully."); window.location.href = "manage_classes.php";</script>';
                exit;
            } else {
                echo '<script>alert("Error: ' . $stmt->error . '");</script>';
            }

            $stmt->close();
        } else {
            echo '<script>alert("Sorry, there was an error uploading your file.");</script>';
        }
    }

    $conn->close();
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
        .form-container {
            background-color: #007bff; /* Bootstrap primary blue */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            color: white; /* White text for contrast */
        }
        .form-container h2 {
            color: #ffffff; /* White color for heading */
        }
        .form-group label {
            color: #ffffff; /* White color for labels */
        }
        .form-control, .form-control-file, .form-control:focus, .custom-select {
            background-color: #f7f9fc; /* Light background for inputs */
            border: none;
            border-radius: 4px;
            box-shadow: none;
        }
        .btn-primary {
            background-color: #0056b3; /* Darker blue for submit button */
            border-color: #004085;
        }
        .btn-primary:hover {
            background-color: #004085; /* Even darker on hover */
            border-color: #003366;
        }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 class="text-center">Add Academic Programs</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Sub Title:</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Class Name:</label>
                            <input type="text" id="subtitle" name="classname" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Image:</label>
                            <input type="file" id="image" name="image" class="form-control-file" accept="image/*" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
