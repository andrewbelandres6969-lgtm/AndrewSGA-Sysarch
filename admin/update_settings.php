<?php
require_once "../includes/app.php";

require_role('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $minutes = intval($_POST['sitin_time_limit_minutes']);

    if ($minutes <= 0) {
        redirect_with_message('admin/admin_dashboard.php', 'error', 'Invalid time limit');
    }

    $stmt = $conn->prepare("UPDATE settings SET sitin_time_limit_minutes=? WHERE id=1");
    $stmt->bind_param("i", $minutes);

    if ($stmt->execute()) {
        redirect_with_message('admin/admin_dashboard.php', 'success', 'Sit-in time limit updated');
    }

    redirect_with_message('admin/admin_dashboard.php', 'error', 'Failed to update time limit');
}
?>
