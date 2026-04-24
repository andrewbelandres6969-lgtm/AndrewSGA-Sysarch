<?php
require_once "../includes/app.php";

require_role('admin');

if (!isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('admin/admin_dashboard.php', 'error', 'Missing student id');
}

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['id']);
    $student_id_value = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $course = trim($_POST['course']);
    $course_level = intval($_POST['course_level']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $sitin_remaining = intval($_POST['sitin_remaining']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($student_id_value === '' || $first_name === '' || $last_name === '' || $course === '' || $email === '') {
        $error = 'Student ID, First Name, Last Name, Course and Email are required.';
    } elseif ($password !== '' && $password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        if ($password !== '') {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET student_id=?, first_name=?, last_name=?, middle_name=?, course=?, course_level=?, email=?, address=?, sitin_remaining=?, password=? WHERE id=? AND role='student'");
            $stmt->bind_param("ssssssisisi", $student_id_value, $first_name, $last_name, $middle_name, $course, $course_level, $email, $address, $sitin_remaining, $hashed_password, $student_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET student_id=?, first_name=?, last_name=?, middle_name=?, course=?, course_level=?, email=?, address=?, sitin_remaining=? WHERE id=? AND role='student'");
            $stmt->bind_param("ssssssisii", $student_id_value, $first_name, $last_name, $middle_name, $course, $course_level, $email, $address, $sitin_remaining, $student_id);
        }

        if ($stmt->execute()) {
            $success = 'Student updated successfully.';
        } else {
            $error = 'Failed to update student.';
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND role='student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    redirect_with_message('admin/admin_dashboard.php', 'error', 'Student not found');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="<?php echo asset_url('Styles/style.css'); ?>">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="<?php echo asset_url('Images/CCSlogo.png'); ?>" class="sidebar-logo" alt="Logo" onerror="this.style.display='none';">
                <h3>Admin Panel</h3>
            </div>
            <a href="<?php echo app_url('admin/admin_dashboard.php'); ?>" class="side-link">Dashboard</a>
            <a href="<?php echo app_url('admin/announcements.php'); ?>" class="side-link">Announcements</a>
            <a href="<?php echo app_url('reports.php'); ?>" class="side-link">Reports</a>
            <a href="<?php echo app_url('auth/logout.php'); ?>" class="side-link">Logout</a>
        </aside>
        <main class="main-content">
            <?php if ($success): ?>
                <div class="message success-msg"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="glass-card">
                <h2>Edit Student</h2>
                <form method="POST" class="search-form">
                    <input type="hidden" name="id" value="<?php echo (int)$student['id']; ?>">
                    <input type="text" name="student_id" placeholder="Student ID" value="<?php echo htmlspecialchars($student['student_id']); ?>" required>
                    <input type="text" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                    <input type="text" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                    <input type="text" name="middle_name" placeholder="Middle Name" value="<?php echo htmlspecialchars($student['middle_name']); ?>">
                    <input type="text" name="course" placeholder="Course" value="<?php echo htmlspecialchars($student['course']); ?>" required>
                    <input type="number" name="course_level" placeholder="Course Level" value="<?php echo (int)$student['course_level']; ?>" min="1" max="4" required>
                    <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    <input type="text" name="address" placeholder="Address" value="<?php echo htmlspecialchars($student['address']); ?>">
                    <input type="number" name="sitin_remaining" placeholder="Remaining Sessions" value="<?php echo (int)$student['sitin_remaining']; ?>" min="0" required>
                    <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
                    <input type="password" name="confirm_password" placeholder="Confirm Password">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="<?php echo app_url('admin/admin_dashboard.php'); ?>" class="btn-secondary">Back to Dashboard</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
