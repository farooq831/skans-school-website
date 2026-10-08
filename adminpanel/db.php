<?php
// adminpanel/db.php : database connection for the campus that this website address belongs to.
// No passwords here: each campus's login is in campus-data/<campus>/config.php (see includes/campus.php).
// Old version (single campus): _old/adminpanel/oct6/db.php
require_once dirname(__DIR__) . '/includes/campus.php';
$conn = campus_connect();
if ($conn->connect_error) {
    error_log('Database connection failed for campus ' . SK_CAMPUS . ': ' . $conn->connect_error);
    if (!defined('SK_DB_SOFT_FAIL')) {
        http_response_code(503);
        exit('The website is temporarily unavailable. Please try again in a few minutes.');
    }
}
