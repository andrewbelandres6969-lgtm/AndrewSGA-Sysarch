<?php
require_once "../includes/app.php";

require_role('student');

$user_id = $_SESSION['user_id'];
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$allowed_categories = ['General', 'Bug Report', 'Feature Request', 'Complaint', 'Other'];

if ($category === '' || !in_array($category, $allowed_categories, true)) {
    redirect_with_message('student/student_dashboard.php', 'error', 'Please select a valid feedback category.');
}

if ($message === '') {
    redirect_with_message('student/student_dashboard.php', 'error', 'Please enter your feedback message.');
}

if (strlen($message) > 2000) {
    redirect_with_message('student/student_dashboard.php', 'error', 'Feedback message is too long (max 2000 characters).');
}

$stmt = $conn->prepare("INSERT INTO feedback (user_id, category, message) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $category, $message);

if ($stmt->execute()) {
    redirect_with_message('student/student_dashboard.php', 'success', 'Thank you! Your feedback has been submitted.');
} else {
    redirect_with_message('student/student_dashboard.php', 'error', 'Something went wrong. Please try again later.');
}

