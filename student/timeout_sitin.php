<?php
require_once "../includes/app.php";

require_role('student');

$id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    UPDATE sitin_records
    SET time_out=NOW(), status='Completed', remarks=IFNULL(remarks, 'Timed out by student')
    WHERE id=? AND user_id=? AND status='Approved' AND time_out IS NULL
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    redirect_with_message('student/student_dashboard.php', 'success', 'Time out successful');
}

redirect_with_message('student/student_dashboard.php', 'error', 'Unable to time out');
?>
