<?php
require_once "../includes/app.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $course_level = trim($_POST['course_level']);
    $password = $_POST['password'];
    $repeat_password = $_POST['repeat_password'];
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $address = trim($_POST['address']);

    if ($password !== $repeat_password) {
        redirect_with_message_preserving_query('index.php', 'error', 'Passwords do not match', ['show' => 'register']);
    }

    $check = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        redirect_with_message_preserving_query('index.php', 'error', 'Student ID already exists', ['show' => 'register']);
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (student_id, last_name, first_name, middle_name, course_level, password, email, course, address, role, sitin_remaining)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 30)");
    $stmt->bind_param("sssssssss", $student_id, $last_name, $first_name, $middle_name, $course_level, $hashed_password, $email, $course, $address);

    if ($stmt->execute()) {
        redirect_with_message_preserving_query('index.php', 'success', 'Registration successful. Please log in.', ['show' => 'login']);
    }

    redirect_with_message_preserving_query('index.php', 'error', 'Registration failed.', ['show' => 'register']);
}
?>
