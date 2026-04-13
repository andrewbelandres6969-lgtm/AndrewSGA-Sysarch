<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in as admin");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php?error=Invalid request");
    exit();
}

$announcement = trim($_POST['announcement'] ?? '');
$filename = 'announcements.txt';

if ($announcement === '') {
    if (file_exists($filename)) {
        unlink($filename);
    }
    header("Location: admin_dashboard.php?success=Announcement cleared.");
    exit();
}

if (file_put_contents($filename, $announcement) !== false) {
    header("Location: admin_dashboard.php?success=Announcement saved.");
    exit();
}

header("Location: admin_dashboard.php?error=Unable to save announcement.");
exit();
