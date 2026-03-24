<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php?error=Unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT sitin_remaining FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if ((int)$user['sitin_remaining'] <= 0) {
    header("Location: student_dashboard.php?error=No sit-in sessions remaining.");
    exit();
}

$active = $conn->prepare("SELECT id FROM sitin_records WHERE user_id=? AND status IN ('Pending','Approved') AND time_out IS NULL");
$active->bind_param("i", $user_id);
$active->execute();
$active->store_result();

if ($active->num_rows > 0) {
    header("Location: student_dashboard.php?error=You already have an active or pending request.");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lab_id = intval($_POST['lab_id']);
    $purpose = trim($_POST['purpose']);

    $stmt = $conn->prepare("INSERT INTO sitin_records (user_id, lab_id, purpose, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("iis", $user_id, $lab_id, $purpose);

    if ($stmt->execute()) {
        header("Location: student_dashboard.php?success=Sit-in request submitted.");
    } else {
        header("Location: student_dashboard.php?error=Failed to submit request.");
    }
    exit();
}
?>