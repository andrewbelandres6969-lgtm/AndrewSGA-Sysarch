<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $minutes = intval($_POST['sitin_time_limit_minutes']);

    if ($minutes <= 0) {
        header("Location: admin_dashboard.php?error=Invalid time limit");
        exit();
    }

    $stmt = $conn->prepare("UPDATE settings SET sitin_time_limit_minutes=? WHERE id=1");
    $stmt->bind_param("i", $minutes);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=Sit-in time limit updated");
    } else {
        header("Location: admin_dashboard.php?error=Failed to update time limit");
    }
    exit();
}
?>