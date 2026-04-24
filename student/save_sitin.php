<?php
require_once "../includes/app.php";

require_role('student');

$user_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT sitin_remaining FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if ((int)$user['sitin_remaining'] <= 0) {
    redirect_with_message('student/student_dashboard.php', 'error', 'No sit-in sessions remaining.');
}

$active = $conn->prepare("SELECT id FROM sitin_records WHERE user_id=? AND status IN ('Pending','Approved') AND time_out IS NULL");
$active->bind_param("i", $user_id);
$active->execute();
$active->store_result();

if ($active->num_rows > 0) {
    redirect_with_message('student/student_dashboard.php', 'error', 'You already have an active or pending request.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lab_id = intval($_POST['lab_id']);
    $purpose = trim($_POST['purpose']);

    $stmt = $conn->prepare("INSERT INTO sitin_records (user_id, lab_id, purpose, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("iis", $user_id, $lab_id, $purpose);

    if ($stmt->execute()) {
        redirect_with_message('student/student_dashboard.php', 'success', 'Sit-in request submitted.');
    }

    redirect_with_message('student/student_dashboard.php', 'error', 'Failed to submit request.');
}
?>
