<?php
include __DIR__ . '/../config/db.php';

// For this simple app, we'll hardcode the user ID to 1
$user_id = 1;

$stmt = $conn->prepare('DELETE FROM expenses WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();

header('Location: index.php');
exit;