<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php?error=Unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $course = trim($_POST['course']);
    $course_level = trim($_POST['course_level']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, middle_name=?, course=?, course_level=?, email=?, address=? WHERE id=?");
    $stmt->bind_param("sssssssi", $first_name, $last_name, $middle_name, $course, $course_level, $email, $address, $user_id);

    if ($stmt->execute()) {
        header("Location: edit_profile.php?success=Profile updated successfully");
    } else {
        header("Location: edit_profile.php?error=Failed to update profile");
    }
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="CCSlogo.png" class="sidebar-logo" alt="Logo" onerror="this.style.display='none';">
            <h3>Student Panel</h3>
        </div>
        <a href="student_dashboard.php" class="side-link">Dashboard</a>
        <a href="edit_profile.php" class="side-link active">Edit Profile</a>
        <a href="reports.php" class="side-link">Reports</a>
        <a href="logout.php" class="side-link">Logout</a>
    </aside>

    <main class="main-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="message success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="message error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="glass-card form-card">
            <h2>Edit My Profile</h2>
            <div class="edit-profile-grid">
                <form method="POST" class="edit-profile-form">
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    <input type="text" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                    <input type="text" name="course" value="<?php echo htmlspecialchars($user['course']); ?>">
                    <select name="course_level" required>
                        <option value="">Select Course Level</option>
                        <option value="1" <?php echo ($user['course_level'] === '1') ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($user['course_level'] === '2') ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($user['course_level'] === '3') ? 'selected' : ''; ?>>3</option>
                        <option value="4" <?php echo ($user['course_level'] === '4') ? 'selected' : ''; ?>>4</option>
                    </select>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                    <button type="submit" class="btn-primary">Save Changes</button>
                </form>

                <div class="edit-profile-image">
                    <img src="Luffy.png" alt="Profile Image">
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>