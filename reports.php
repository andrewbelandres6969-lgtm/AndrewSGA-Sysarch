<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Unauthorized");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php?error=Missing student id");
    exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='student'");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin_dashboard.php?success=Student deleted successfully");
} else {
    header("Location: admin_dashboard.php?error=Failed to delete student");
}
exit();
?>