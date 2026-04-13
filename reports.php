<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?show=login&error=Please log in");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$user_stmt = $conn->prepare("SELECT first_name, last_name, role FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: index.php?show=login&error=Please log in");
    exit();
}

if ($role === 'student') {
    $records_stmt = $conn->prepare(
        "SELECT s.id, s.lab_id, s.computer_number, s.purpose, s.status, s.time_in, s.time_out, s.session_end, l.lab_name
         FROM sitin_records s
         LEFT JOIN labs l ON s.lab_id = l.id
         WHERE s.user_id = ?
         ORDER BY s.id DESC"
    );
    $records_stmt->bind_param("i", $user_id);
    $records_stmt->execute();
    $records = $records_stmt->get_result();
} else {
    $records_stmt = $conn->query(
        "SELECT s.id, u.student_id, u.first_name, u.last_name, s.lab_id, s.computer_number, s.purpose, s.status, s.time_in, s.time_out, s.session_end, l.lab_name
         FROM sitin_records s
         LEFT JOIN users u ON s.user_id = u.id
         LEFT JOIN labs l ON s.lab_id = l.id
         ORDER BY s.id DESC"
    );
    $records = $records_stmt;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="logo.png" class="sidebar-logo" alt="Logo" onerror="this.style.display='none';">
                <h3><?php echo $role === 'admin' ? 'Admin Panel' : 'Student Panel'; ?></h3>
            </div>
            <?php if ($role === 'admin'): ?>
                <a href="admin_dashboard.php" class="side-link">Dashboard</a>
                <a href="reports.php" class="side-link active">Reports</a>
                <a href="logout.php" class="side-link">Logout</a>
            <?php else: ?>
                <a href="student_dashboard.php" class="side-link">Dashboard</a>
                <a href="edit_profile.php" class="side-link">Edit Profile</a>
                <a href="reports.php" class="side-link active">Reports</a>
                <a href="logout.php" class="side-link">Logout</a>
            <?php endif; ?>
        </aside>
        <main class="main-content">
            <div class="topbar glass-card">
                <h2>Reports</h2>
                <p>Logged in as <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
            </div>

            <div class="glass-card">
                <?php if ($role === 'student'): ?>
                    <h3>My Sit-In Records</h3>
                <?php else: ?>
                    <h3>All Sit-In Records</h3>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php if ($role === 'admin'): ?><th>Student ID</th><th>Name</th><?php endif; ?>
                            <th>Lab</th>
                            <th>Computer</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Ends At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $records->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <?php if ($role === 'admin'): ?>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($row['lab_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['computer_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                                <td><?php echo htmlspecialchars($row['time_out'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['session_end'] ?? '-'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
