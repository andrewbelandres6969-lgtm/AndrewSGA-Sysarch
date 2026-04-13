<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php?error=Unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['photo'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['photo'];

    if ($file['error'] !== 0) {
        header("Location: student_dashboard.php?error=Upload failed.");
        exit();
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        header("Location: student_dashboard.php?error=Invalid image type.");
        exit();
    }

    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "uploads/student_" . $user_id . "_" . time() . "." . $ext;

    if (move_uploaded_file($file['tmp_name'], $filename)) {
        $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
        $stmt->bind_param("si", $filename, $user_id);
        $stmt->execute();

        header("Location: student_dashboard.php?success=Photo uploaded successfully.");
        exit();
    } else {
        header("Location: student_dashboard.php?error=Could not save image.");
        exit();
    }
}

header("Location: student_dashboard.php?error=No photo uploaded.");
exit();
?>