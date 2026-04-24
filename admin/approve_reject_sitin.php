<?php
require_once "../includes/app.php";

require_role('admin');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect_with_message('admin/admin_dashboard.php', 'error', 'Invalid request');
}

$id = intval($_POST['id']);
$action = $_POST['action'];
$remarks = trim($_POST['remarks'] ?? '');
$computer_number = trim($_POST['computer_number'] ?? '');

$record_stmt = $conn->prepare("SELECT * FROM sitin_records WHERE id=?");
$record_stmt->bind_param("i", $id);
$record_stmt->execute();
$record = $record_stmt->get_result()->fetch_assoc();

if (!$record) {
    redirect_with_message('admin/admin_dashboard.php', 'error', 'Record not found');
}

$settings = get_latest_settings($conn);
$limit = (int)$settings['sitin_time_limit_minutes'];

if ($action === 'approve') {
    if ($computer_number === '') {
        redirect_with_message('admin/admin_dashboard.php', 'error', 'Computer number is required for approval');
    }

    $user_stmt = $conn->prepare("SELECT sitin_remaining FROM users WHERE id=?");
    $user_stmt->bind_param("i", $record['user_id']);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();

    if ((int)$user['sitin_remaining'] <= 0) {
        redirect_with_message('admin/admin_dashboard.php', 'error', 'Student has no remaining sessions');
    }

    $stmt = $conn->prepare("
        UPDATE sitin_records
        SET status='Approved',
            computer_number=?,
            approved_at=NOW(),
            session_end=DATE_ADD(NOW(), INTERVAL ? MINUTE),
            remarks=?
        WHERE id=? AND status='Pending'
    ");
    $stmt->bind_param("sisi", $computer_number, $limit, $remarks, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $deduct = $conn->prepare("UPDATE users SET sitin_remaining = sitin_remaining - 1 WHERE id=? AND sitin_remaining > 0");
        $deduct->bind_param("i", $record['user_id']);
        $deduct->execute();
        redirect_with_message('admin/admin_dashboard.php', 'success', 'Request approved successfully');
    }

    redirect_with_message('admin/admin_dashboard.php', 'error', 'Unable to approve request');
}

if ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE sitin_records SET status='Rejected', remarks=? WHERE id=? AND status='Pending'");
    $stmt->bind_param("si", $remarks, $id);
    $stmt->execute();
    redirect_with_message('admin/admin_dashboard.php', 'success', 'Request rejected');
}

if ($action === 'complete') {
    $stmt = $conn->prepare("UPDATE sitin_records SET status='Completed', time_out=NOW(), remarks=? WHERE id=? AND status='Approved'");
    $stmt->bind_param("si", $remarks, $id);
    $stmt->execute();
    redirect_with_message('admin/admin_dashboard.php', 'success', 'Session completed');
}

redirect_with_message('admin/admin_dashboard.php', 'error', 'Invalid action');
?>
