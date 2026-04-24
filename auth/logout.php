<?php
session_start();
session_destroy();
require_once "../includes/app.php";
header("Location: " . app_url('index.php') . "?success=" . urlencode('Logged out successfully'));
exit();
?>
