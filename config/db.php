<?php
// Database configuration (update with your own credentials)
$DB_HOST = 'localhost';
$DB_USER = 'YOUR_DB_USERNAME';
$DB_PASS = 'YOUR_DB_PASSWORD';
$DB_NAME = 'expense_manager';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
// set charset
$conn->set_charset('utf8mb4');
?>
