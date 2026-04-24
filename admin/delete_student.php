<?php
require_once "../includes/app.php";

require_role('admin');

if (!isset($_GET['id'])) {
    redirect_with_message('admin/admin_dashboard.php', 'error', 'Missing student id');
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='student'");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    redirect_with_message('admin/admin_dashboard.php', 'success', 'Student deleted successfully');
}

redirect_with_message('admin/admin_dashboard.php', 'error', 'Failed to delete student');
?>
