<?php
include __DIR__ . '/../config/db.php';
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare('DELETE FROM expenses WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
header('Location: index.php');
exit;
?>
