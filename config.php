<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "sit_in_system_clean";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
