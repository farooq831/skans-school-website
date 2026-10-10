<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
include 'db.php';
require_once __DIR__ . '/admin_lib.php';
$level_count = campus_level_count(campus_class_list($conn)); // entries #1..#N are the level cards

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    
    // Fetch existing data
    $sql = "SELECT * FROM academic_prog WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = $result->fetch_assoc();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'];
        $subtitle = $_POST['subtitle'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        
        // Handle image upload
        $target_file = $classes['image']; // Keep old image path as default
        
        if (!empty($_FILES["image"]["name"])) {
            // Saved in this campus's pictures folder; stored as "upload/images/<name>" like the older entries.
            // Random file name: an upload can never replace an existing file or choose its own name.
            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_name = bin2hex(random_bytes(8)) . '.' . $imageFileType;
            $new_file = "upload/images/" . $new_name;
            $uploadOk = 1;
            
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                echo '<script>alert("File is not an image.");</script>';
                $uploadOk = 0;
            }
            
                        
            if ($_FILES["image"]["size"] > IMAGE_MAX_BYTES) { // limit: 2 MB
                echo '<script>alert("The picture is too large. Maximum size is ' . IMAGE_MAX_LABEL . '.");</script>';
                $uploadOk = 0;
            }

            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                echo '<script>alert("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");</script>';
                $uploadOk = 0;
            }
            
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], upload_dir() . $new_name)) {
                    $target_file = $new_file;   // only use the new image if it was saved
                } else {
                    echo '<script>alert("Sorry, there was an error uploading your file.");</script>';
                }
            }
        }
        
       
        // Update the record
$update_sql = "UPDATE academic_prog SET heading = ?, subheading = ?, name = ?, description = ?, image = ? WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("sssssi", $title, $subtitle, $name, $description, $target_file, $id); // Corrected type definition string

        
        if ($stmt->execute()) {
            echo '<script>alert("Record updated successfully.");</script>';
            echo '<script>window.location.href="manage_classes.php";</script>';
        } else {
            echo '<script>alert("Error updating record. Please try again.");</script>';
        }
        
        $stmt->close();
    }
} else {
    echo '<script>alert("No ID provided.");</script>';
    echo '<script>window.location.href="manage_classes.php";</script>';
}

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
<?php $is_card = isset($classes['id']) && (int) $classes['id'] <= $level_count; // the level cards on the website ?>
                    <h2 class="text-center"><?= $is_card ? 'Edit Level Card #' . (int) $classes['id'] : 'Edit Academic Program' ?></h2>
<?php if ($is_card): ?>
                    <div class="alert alert-light small">This is one of the <strong>3 level cards</strong> on the website (Home, Admissions, Programs Offered). Saving updates the website straight away.</div>
<?php endif; ?>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name"><?= $is_card ? 'Card title (e.g. Early Years):' : 'Class Name:' ?></label>
                            <input type="text" id="name" name="name" class="form-control" value="<?= h($classes['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="title"><?= $is_card ? 'Classes line under the title (e.g. Playgroup to Class 2):' : 'Title:' ?></label>
                            <input type="text" id="title" name="title" class="form-control" value="<?= h($classes['heading'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="subtitle"><?= $is_card ? 'Sub title (not shown on the card):' : 'Sub Title:' ?></label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control" value="<?= h($classes['subheading'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description"><?= $is_card ? 'Key objectives: one per line (each line = one tick on the card):' : 'Description:' ?></label>
                            <textarea id="description" name="description" class="form-control" rows="<?= $is_card ? 10 : 4 ?>" required><?= h($classes['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="image"><?= $is_card ? 'Card picture (choose only to replace; landscape photo looks best):' : 'Image:' ?></label>
                            <input type="file" id="image" name="image" class="form-control-file" accept="image/*">
                            <?php if ($cur = upload_url(basename((string) ($classes['image'] ?? '')))): ?><img src="<?= h($cur) ?>" width="100" height="60" class="mt-2" style="object-fit:cover"><?php endif; ?>
                        </div>


                        <button type="submit" class="btn btn-primary btn-block">Update</button>
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
