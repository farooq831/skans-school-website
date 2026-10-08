<?php
require __DIR__ . '/auth.php'; // login check (added Oct 2026)
// session is started in auth.php

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login page if not logged in
    exit;
}

include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    admin_require_csrf();
    $delete_id = intval($_GET['delete_id']); // Ensure it's an integer
    $delete_sql = "DELETE FROM news_events WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        header("Location: manage_news_events.php?msg=Record deleted successfully."); // Redirect after deletion
        exit;
    } else {
        header("Location: manage_news_events.php?msg=Error deleting record: " . $stmt->error); // Redirect with error message
        exit;
    }
    $stmt->close();
}

// Search, filter and pages (list_tools.php)
require_once __DIR__ . '/list_tools.php';
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC);
$years = array_column($conn->query('SELECT DISTINCT YEAR(date) y FROM news_events WHERE date IS NOT NULL ORDER BY y DESC')->fetch_all(MYSQLI_ASSOC), 'y');
$p = lt_params(['category' => array_merge([''], array_map('strval', array_column($categories, 'id'))), 'year' => array_merge([''], array_map('strval', $years))], ['newest', 'oldest', 'az']);
$where = ['1=1']; $types = ''; $params = [];
if ($p['q'] !== '') { $where[] = '(news_events.title LIKE ? OR news_events.description LIKE ?)'; $types .= 'ss'; $params[] = lt_like($p['q']); $params[] = lt_like($p['q']); }
if ($p['category'] !== '') { $where[] = 'news_events.category_id = ?'; $types .= 'i'; $params[] = (int) $p['category']; }
if ($p['year'] !== '') { $where[] = 'YEAR(news_events.date) = ?'; $types .= 'i'; $params[] = (int) $p['year']; }
$order = ['newest' => 'news_events.date DESC, news_events.id DESC', 'oldest' => 'news_events.date ASC, news_events.id ASC', 'az' => 'news_events.title ASC'][$p['sort']];
[$rows, $info] = lt_query($conn, 'news_events JOIN categories ON news_events.category_id = categories.id WHERE ' . implode(' AND ', $where),
    $types, $params, $order, $p, 'news_events.*, categories.name AS category_name');
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
    <div class="adm-title">
        <h1>Manage News &amp; Events</h1>
        <a href="add_news.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add news / event</a>
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info">
            <?php echo h($_GET['msg']); ?>
        </div>
    <?php endif; ?>
    <?php
    $cat_options = ['' => 'All categories']; foreach ($categories as $c) $cat_options[(string) $c['id']] = $c['name'];
    $year_options = ['' => 'All years']; foreach ($years as $y) $year_options[(string) $y] = (string) $y;
    echo lt_search_bar($p, 'Search news and events by title or description…',
        [['name' => 'category', 'label' => 'Category', 'options' => $cat_options], ['name' => 'year', 'label' => 'Year', 'options' => $year_options]],
        ['newest' => 'Newest first', 'oldest' => 'Oldest first', 'az' => 'Title A–Z']);
    ?>
    <?php $bulk_type = 'news'; include __DIR__ . '/bulk_bar.php'; ?>
    <div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th style="width:42px"><span class="sr-only">Select</span></th>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Image</th>
                <th>Date</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($rows) {
                foreach ($rows as $row) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" class="adm-check" name="ids[]" value="' . (int) $row['id'] . '" form="bulk-form" aria-label="Select ' . h($row['title']) . '"></td>';
                    echo '<td>' . h($row['id']) . '</td>';
                    echo '<td>' . h($row['title']) . '</td>';
                    $desc = trim(html_entity_decode(html_entity_decode((string) $row['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    echo '<td title="' . h($desc) . '">' . h(mb_strlen($desc) > 120 ? mb_substr($desc, 0, 120) . '…' : $desc) . '</td>';
                    echo '<td>';
                    if (!empty($row['image_url'])) {
                        echo '<img src="' . h(upload_url($row['image_url'])) . '" width="100" height="60">';
                    } else {
                        echo 'No Image';
                    }
                    echo '</td>';
                    echo '<td>' . h($row['date']) . '</td>';
                    echo '<td>' . h($row['category_name']) . '</td>';
                    echo '<td>
                            <a href="edit_news_event.php?id=' . h($row['id']) . '" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_news_events.php?delete_id=' . (int) $row['id'] . '&token=' . urlencode(admin_csrf_token()) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</a>
                          </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="8" class="text-center">No news or events found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
    </div>
    <?= lt_pagination($p, $info, 'posts') ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
