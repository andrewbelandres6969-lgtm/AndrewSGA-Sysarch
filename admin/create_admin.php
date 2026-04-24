<?php
require_once "../config.php";

$student_id = "admin";
$last_name = "Administrator";
$first_name = "System";
$middle_name = "";
$course_level = "N/A";
$email = "admin@ccs.com";
$course = "Admin";
$address = "School";
$role = "admin";
$hashed = password_hash("admin123", PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
$check->bind_param("s", $student_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE users SET password=?, role='admin' WHERE student_id=?");
    $stmt->bind_param("ss", $hashed, $student_id);
    $stmt->execute();
    echo "Admin updated successfully.";
} else {
    $stmt = $conn->prepare("
        INSERT INTO users (
            student_id, last_name, first_name, middle_name, course_level,
            password, email, course, address, role, sitin_remaining
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 30)
    ");
    $stmt->bind_param(
        "ssssssssss",
        $student_id,
        $last_name,
        $first_name,
        $middle_name,
        $course_level,
        $hashed,
        $email,
        $course,
        $address,
        $role
    );
    $stmt->execute();
    echo "Admin created successfully.";
}
?>
