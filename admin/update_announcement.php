<?php
require_once "../includes/app.php";

require_role('admin');
ensure_announcements_table($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('admin/announcements.php', 'error', 'Invalid request');
}

$action = $_POST['action'] ?? 'save';
$announcement_id = intval($_POST['announcement_id'] ?? 0);
$announcement = trim($_POST['announcement'] ?? '');
$author_name = trim($_SESSION['name'] ?? 'CCS Admin');

if ($action === 'delete') {
    if ($announcement_id <= 0) {
        redirect_with_message('admin/announcements.php', 'error', 'Announcement not found.');
    }

    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();

    redirect_with_message('admin/announcements.php', 'success', 'Announcement deleted.');
}

if ($announcement === '') {
    redirect_with_message('admin/announcements.php', 'error', 'Announcement message is required.');
}

if ($announcement_id > 0) {
    $stmt = $conn->prepare("UPDATE announcements SET content = ?, author_name = ? WHERE id = ?");
    $stmt->bind_param("ssi", $announcement, $author_name, $announcement_id);
    $stmt->execute();
    redirect_with_message('admin/announcements.php', 'success', 'Announcement updated.');
}

$stmt = $conn->prepare("INSERT INTO announcements (title, content, author_name) VALUES ('Announcements', ?, ?)");
$stmt->bind_param("ss", $announcement, $author_name);
$stmt->execute();

redirect_with_message('admin/announcements.php', 'success', 'Announcement posted.');
?>
