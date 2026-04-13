<?php
session_start();
include "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($student_id === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ? AND email = ?");
        $stmt->bind_param("ss", $student_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE student_id = ? AND email = ?");
            $update->bind_param("sss", $hashed_password, $student_id, $email);
            $update->execute();

            if ($update->affected_rows > 0) {
                header("Location: index.php?show=login&success=Password reset successful. Please log in.");
                exit();
            } else {
                $error = "Unable to update password. Please try again.";
            }
        } else {
            $error = "No account was found with that ID and email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password | CCS Sit-In Monitoring</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <section class="login-wrapper">
        <div class="login-card">
            <span class="back-btn"><a href="index.php" onclick="pageTransition('index.php'); return false;">← Back to Home</a></span>
            <div class="login-content">
                <div class="login-text-side">
                    <h2>Forgot Password</h2>
                    <p>Enter your student ID, registered email, and a new password.</p>

                    <?php if ($error): ?>
                        <div class="message error-msg"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form action="forgot_password.php" method="POST">
                        <input type="text" name="student_id" placeholder="ID Number" inputmode="numeric" required>
                        <input type="email" name="email" placeholder="Registered Email" required>
                        <input type="password" name="password" placeholder="New Password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                        <button type="submit" class="btn-primary login-submit">Reset Password</button>
                    </form>
                </div>

                <div class="login-gif-side">
                    <img src="Luffy1.gif" alt="Forgot Password GIF">
                </div>
            </div>
        </div>
    </section>
    <script src="script.js"></script>
</body>
</html>
