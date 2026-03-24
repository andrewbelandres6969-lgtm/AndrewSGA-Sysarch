<?php
include "config.php";

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
        header("Location: index.php?show=register&error=Passwords do not match");
        exit();
    }

    $check = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: index.php?show=register&error=Student ID already exists");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (student_id, last_name, first_name, middle_name, course_level, password, email, course, address, role, sitin_remaining)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 30)");
    $stmt->bind_param("sssssssss", $student_id, $last_name, $first_name, $middle_name, $course_level, $hashed_password, $email, $course, $address);

    if ($stmt->execute()) {
        header("Location: index.php?show=login&success=Registration successful. Please log in.");
    } else {
        header("Location: index.php?show=register&error=Registration failed.");
    }
    exit();
}
?>