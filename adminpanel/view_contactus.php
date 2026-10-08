<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';
date_default_timezone_set("Asia/karachi");

// Search, filter and pages (list_tools.php); newest first
require_once __DIR__ . '/list_tools.php';
$p = lt_params(['status' => ['', 'new', 'reviewed']], ['newest', 'oldest']);
$where = ['1=1']; $types = ''; $params = [];
if ($p['q'] !== '') { $where[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)'; $types .= 'ssss'; array_push($params, lt_like($p['q']), lt_like($p['q']), lt_like($p['q']), lt_like($p['q'])); }
if ($p['status'] === 'new') { $where[] = "(remarks IS NULL OR remarks = '')"; }
if ($p['status'] === 'reviewed') { $where[] = "(remarks IS NOT NULL AND remarks <> '')"; }
$order = $p['sort'] === 'oldest' ? 'submitted_at ASC, id ASC' : 'submitted_at DESC, id DESC';
[$rows, $info] = lt_query($conn, 'contact_form_submissions WHERE ' . implode(' AND ', $where), $types, $params, $order, $p);
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
    <h2 class="mb-4">Submitted Contact Us Forms</h2>
    <?= lt_search_bar($p, 'Search by name, email, phone or message…',
        [['name' => 'status', 'label' => 'Status', 'options' => ['' => 'All messages', 'new' => 'New (not answered)', 'reviewed' => 'Reviewed']]],
        ['newest' => 'Newest first', 'oldest' => 'Oldest first']) ?>
    
    <table class="table table-bordered table-striped">
    <thead class="thead-dark">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Submitted At</th>
            <th>Action</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($rows) {
        foreach ($rows as $row) {
            $id = $row['id'];
            $id = (int) $row['id'];
            $full_name = h($row['full_name']);
            $email = h($row['email']);
            $phone = h($row['phone']);
            $message = h($row['message']);
            $submitted_at = h($row['submitted_at']);
            $remarks = h($row['remarks'] ?? '');
            $responded_at = $row['responded_at'] ? $row['responded_at'] : "Not Responded";

            // Calculate Response Time Duration
            $duration = "N/A";
            if ($row['responded_at']) {
                $start = new DateTime($submitted_at);
                $end = new DateTime($responded_at);
                $diff = $start->diff($end);
                $duration = "{$diff->days}d {$diff->h}h {$diff->i}m";
            }

            // Determine Status
            $status = empty($remarks) ? "<span class='badge badge-warning'>Pending</span>" : "<span class='badge badge-success'>Reviewed</span>";

            echo "<tr>";
            echo "<td>$full_name</td>";
            echo "<td>$email</td>";
            echo "<td>$phone</td>";
            echo "<td>$message</td>";
            echo "<td>$submitted_at</td>";  
            echo "<td>
                    <button class='btn btn-primary' data-id='$id' data-email='$email' data-remarks='$remarks' onclick='openModal(this)'>Respond</button>
                  </td>";
            echo "<td>$status
                  <button class='btn btn-info' data-remarks='$remarks' data-responded='" . h($responded_at) . "' data-duration='" . h($duration) . "' onclick='openDetailsModal(this)'>Details</button>
                </td>";  
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No messages found.</td></tr>";
    }
    ?>
</tbody>

    </table>
    <?= lt_pagination($p, $info, 'messages') ?>
</div>

<!-- Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="responseModalLabel">Respond to Query</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalQueryId">
        <label>Email Address:</label>
        <input type="text" class="form-control" id="modalEmail" readonly>
        <button class="btn btn-secondary mt-2" onclick="copyEmail()">Copy Email Address</button><br>
        
        <label class="mt-3">Remarks:</label>
        <textarea class="form-control" id="modalRemarks" rows="3"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="saveRemarks()">Save Remarks</button>
        <a href="#" id="replyButton" class="btn btn-primary">Reply</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Submission Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Response Date:</strong> <span id="detailsResponseDate"></span></p>
        <p><strong>Response Time:</strong> <span id="detailsResponseTime"></span></p>
        <p><strong>Remarks:</strong></p>
        <textarea class="form-control" id="detailsRemarks" rows="3" readonly></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
function openDetailsModal(button) {
    var remarks = button.dataset.remarks, responseDate = button.dataset.responded, responseTime = button.dataset.duration;
    document.getElementById('detailsResponseDate').textContent = responseDate !== "Not Responded" ? responseDate : "N/A";
    document.getElementById('detailsResponseTime').textContent = responseTime !== "N/A" ? responseTime : "N/A";
    document.getElementById('detailsRemarks').value = remarks !== "" ? remarks : "No remarks provided.";

    $('#detailsModal').modal('show');
}
</script>


<script>
function openModal(button) {
    // Values come from data- attributes, so text typed by visitors can never run as code.
    var id = button.dataset.id, email = button.dataset.email, remarks = button.dataset.remarks;
    document.getElementById('modalQueryId').value = id;
    document.getElementById('modalEmail').value = email;
    document.getElementById('modalRemarks').value = remarks;
    document.getElementById('replyButton').href = 'mailto:' + encodeURIComponent(email) + '?subject=Reply to Your Contact Form Submission';
    
    $('#responseModal').modal('show');
}

function saveRemarks() {
    let id = document.getElementById('modalQueryId').value;
    let remarks = document.getElementById('modalRemarks').value;

    fetch('update_remarks.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id) + '&remarks=' + encodeURIComponent(remarks) + '&csrf_token=' + encodeURIComponent(<?= json_encode(admin_csrf_token()) ?>)
    }).then(response => response.text())
      .then(data => {
          console.log(data);
          alert('Remarks updated successfully!');
          $('#responseModal').modal('hide');
          location.reload();
      });
}

function copyEmail() {
    let emailField = document.getElementById('modalEmail');
    emailField.select();
    emailField.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Email copied: " + emailField.value);
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
