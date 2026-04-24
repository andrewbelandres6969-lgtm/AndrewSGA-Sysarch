<?php
require_once "../includes/app.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, student_id, password, role, first_name, last_name FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            if ($user['role'] === 'admin') {
                header("Location: " . app_url('admin/admin_dashboard.php'));
            } else {
                header("Location: " . app_url('student/student_dashboard.php'));
            }
            exit();
        }

        redirect_with_message_preserving_query('index.php', 'error', 'Invalid password', ['show' => 'login']);
    }

    redirect_with_message_preserving_query('index.php', 'error', 'Student ID not found', ['show' => 'login']);
}
?>
