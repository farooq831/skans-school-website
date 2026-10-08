<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Update status if the form is submitted
if (isset($_POST['update_status'])) {
    admin_require_csrf();
    $allowed_status = ['Pending', 'Visited, Not Enrolled', 'Not Yet Visited', 'Visited and Enrolled', 'Not Interested'];
    $id = (int) $_POST['id'];
    $status = (string) $_POST['status'];
    if (in_array($status, $allowed_status, true)) {
        $stmt = $conn->prepare("UPDATE admission_form SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Pagination settings
$limit = 10;  // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Get total number of records
$total_sql = "SELECT COUNT(*) AS total FROM admission_form";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch paginated records
$sql = "SELECT * FROM admission_form ORDER BY submitted_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
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
        <h2 class="mb-4">Submitted Admission Forms</h2>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Student Name</th>
                    <th>Date of Birth</th>
                    <th>Picture</th>
                    <th>Class</th>
                    <th>Submitted at</th>
                    <th>Remarks</th>
                    <th>Action / Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . h($row['student_name']) . "</td>";
                        echo "<td>" . h($row['dob']) . "</td>";
                        echo "<td><img src='../uploads/" . rawurlencode(basename((string) $row['student_picture'])) . "' alt='Student Picture' style='width:100px; height:auto;'></td>";
                        echo "<td>" . h($row['class']) . "</td>";
                        echo "<td>" . h($row['submitted_at']) . "</td>";
                        echo "<td>" . h($row['remarks']) . "</td>"; // Display remarks
                        if ($row['status'] == "Pending") {
                            echo "<td><button type='button' class='btn btn-info view-btn' data-toggle='modal' data-target='#viewModal' data-id='" . (int) $row['id'] . "'>View</button></td>";
                        } else {
                            echo "<td><span class='text-success'>" . h($row['status']) . "</span></td>";
                        }    
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No submitted forms found.</td></tr>";
                }
                ?>
                </tbody>


        </table>
        <div class="pagination mt-4">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                <?php endif; ?>
        
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
        
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <!-- Modal for Viewing Detailed Information -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Admission Form Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Detailed information will be loaded here -->
                <div id="detail-content"></div>
                
               <!-- Status Update Form -->
                <form id="statusForm">
                    <input type="hidden" name="id" id="formId">
                    <div class="form-group">
                        <label for="status">Update Status:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="Visited, Not Enrolled">Visited, Not Enrolled</option>
                            <option value="Not Yet Visited">Not Yet Visited</option>
                            <option value="Visited and Enrolled">Visited and Enrolled</option>
                            <option value="Not Interested">Not Interested</option> <!-- New option added -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="remarks">Remarks:</label>
                        <textarea name="remarks" id="remarks" class="form-control" placeholder="Enter remarks..."></textarea>
                    </div>
                    <button type="button" id="updateStatusBtn" class="btn btn-primary">Update Status</button>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- JavaScript to handle modal data loading -->
    <script>
$(document).ready(function() {
    $('.view-btn').click(function() {
        var id = $(this).data('id');
        
        // Fetch details via AJAX
        $.ajax({
            url: 'fetch_admission.php',
            method: 'GET',
            data: { id: id },
            success: function(response) {
                $('#detail-content').html(response);
                $('#formId').val(id); // Set form ID for status update
            }
        });
    });

    // Update status on button click
    $(document).ready(function() {
    $('.view-btn').click(function() {
        var id = $(this).data('id');
        
        // Fetch details via AJAX
        $.ajax({
            url: 'fetch_admission.php',
            method: 'GET',
            data: { id: id },
            success: function(response) {
                $('#detail-content').html(response);
                $('#formId').val(id); // Set form ID for status update
            }
        });
    });

    // Update status and remarks on button click
    $('#updateStatusBtn').click(function() {
        var formId = $('#formId').val();
        var status = $('#status').val();
        var remarks = $('#remarks').val();
        
        $.ajax({
            url: 'update_addmissionstatus.php',
            method: 'POST',
            data: { id: formId, status: status, remarks: remarks, csrf_token: <?= json_encode(admin_csrf_token()) ?> },
            success: function(response) {
                alert('Status and remarks updated successfully!');
                $('#viewModal').modal('hide'); // Hide the modal
                location.reload(); // Reload the page to reflect changes
            },
            error: function() {
                alert('Failed to update status. Please try again.');
            }
        });
    });
});

});
</script>

</body>
</html>

<?php
$conn->close();
?>
