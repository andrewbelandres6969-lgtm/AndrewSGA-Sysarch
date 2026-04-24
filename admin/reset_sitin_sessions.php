<?php
require_once "../includes/app.php";

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('reports.php', 'error', 'Invalid request.');
}

$student_user_id = (int) ($_POST['student_user_id'] ?? 0);
$return_url = trim($_POST['return_url'] ?? app_url('reports.php'));

if ($student_user_id <= 0) {
    header('Location: ' . $return_url);
    exit();
}

$default_sessions = default_sitin_sessions();
$stmt = $conn->prepare("UPDATE users SET sitin_remaining = ? WHERE id = ? AND role = 'student'");
$stmt->bind_param("ii", $default_sessions, $student_user_id);
$stmt->execute();

$separator = strpos($return_url, '?') === false ? '?' : '&';
$message = $stmt->affected_rows > 0 ? 'Student sessions reset successfully.' : 'Unable to reset student sessions.';
$type = $stmt->affected_rows > 0 ? 'success' : 'error';

header('Location: ' . $return_url . $separator . $type . '=' . urlencode($message));
exit();
?>
