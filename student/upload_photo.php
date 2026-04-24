<?php
require_once "../includes/app.php";

require_role('student');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['photo'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['photo'];

    if ($file['error'] !== 0) {
        redirect_with_message('student/student_dashboard.php', 'error', 'Upload failed.');
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        redirect_with_message('student/student_dashboard.php', 'error', 'Invalid image type.');
    }

    $upload_dir = dirname(__DIR__) . '/' . get_photo_upload_directory();

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $relative_filename = get_photo_upload_directory() . "/student_" . $user_id . "_" . time() . "." . $ext;
    $full_filename = dirname(__DIR__) . '/' . $relative_filename;

    if (move_uploaded_file($file['tmp_name'], $full_filename)) {
        $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
        $stmt->bind_param("si", $relative_filename, $user_id);
        $stmt->execute();

        redirect_with_message('student/student_dashboard.php', 'success', 'Photo uploaded successfully.');
    }

    redirect_with_message('student/student_dashboard.php', 'error', 'Could not save image.');
}

redirect_with_message('student/student_dashboard.php', 'error', 'No photo uploaded.');
?>
