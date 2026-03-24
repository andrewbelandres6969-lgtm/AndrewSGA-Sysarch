<?php
session_start();
include "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=Please log in as admin");
    exit();
}

$conn->query("
    UPDATE sitin_records
    SET status='Expired', time_out=NOW(), remarks=IFNULL(remarks, 'Session expired')
    WHERE status='Approved' AND time_out IS NULL AND session_end IS NOT NULL AND NOW() > session_end
");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_lab = isset($_GET['lab']) ? intval($_GET['lab']) : 0;

if ($search !== '') {
    $like = "%$search%";
    $stmt = $conn->prepare("
        SELECT * FROM users
        WHERE role='student' AND (student_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR course LIKE ?)
        ORDER BY id DESC
    ");
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $students = $stmt->get_result();
} else {
    $students = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY id DESC");
}

$settings = $conn->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1")->fetch_assoc();

$records = $conn->query("
    SELECT s.*, u.student_id, u.first_name, u.last_name, l.lab_name
    FROM sitin_records s
    INNER JOIN users u ON s.user_id = u.id
    INNER JOIN labs l ON s.lab_id = l.id
    ORDER BY s.id DESC
");

$daily_logs_sql = "
    SELECT l.lab_name, DATE(s.time_in) log_date, COUNT(s.id) total_logs
    FROM sitin_records s
    INNER JOIN labs l ON s.lab_id = l.id
";
if ($selected_lab > 0) {
    $daily_logs_sql .= " WHERE l.id = {$selected_lab} ";
}
$daily_logs_sql .= " GROUP BY l.lab_name, DATE(s.time_in) ORDER BY log_date DESC, l.lab_name ASC";
$daily_logs = $conn->query($daily_logs_sql);

$stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM users WHERE role='student') total_students,
        (SELECT COUNT(*) FROM sitin_records) total_records,
        (SELECT COUNT(*) FROM sitin_records WHERE status='Pending') pending_count,
        (SELECT COUNT(*) FROM sitin_records WHERE status='Approved') approved_count,
        (SELECT COUNT(*) FROM sitin_records WHERE status='Completed') completed_count,
        (SELECT COUNT(*) FROM sitin_records WHERE status='Expired') expired_count
")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="images.png" class="sidebar-logo" alt="Logo">
            <h3>Admin Panel</h3>
        </div>

        <a href="admin_dashboard.php" class="side-link active">Dashboard</a>
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

        <div class="stats-grid">
            <div class="stat-card"><h3>Students</h3><p><?php echo (int)$stats['total_students']; ?></p></div>
            <div class="stat-card"><h3>Total Records</h3><p><?php echo (int)$stats['total_records']; ?></p></div>
            <div class="stat-card"><h3>Pending</h3><p><?php echo (int)$stats['pending_count']; ?></p></div>
            <div class="stat-card"><h3>Approved</h3><p><?php echo (int)$stats['approved_count']; ?></p></div>
            <div class="stat-card"><h3>Completed</h3><p><?php echo (int)$stats['completed_count']; ?></p></div>
            <div class="stat-card"><h3>Expired</h3><p><?php echo (int)$stats['expired_count']; ?></p></div>
        </div>

        <div class="glass-card">
            <h2>Admin Sit-In Time Limit</h2>
            <form method="POST" action="update_settings.php" class="search-form">
                <input type="number" name="sitin_time_limit_minutes" value="<?php echo (int)$settings['sitin_time_limit_minutes']; ?>" min="1" required>
                <button type="submit" class="btn-primary">Update Time Limit</button>
            </form>
        </div>

        <div class="glass-card">
            <h2>Search Students</h2>
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search by ID, name, course" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-primary">Search</button>
            </form>
        </div>

        <div class="glass-card">
            <h2>Student List / CRUD</h2>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Email</th>
                        <th>Remaining</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo (int)$student['sitin_remaining']; ?></td>
                            <td>
                                <a class="action-btn edit-btn" href="edit_student.php?id=<?php echo $student['id']; ?>">Edit</a>
                                <a class="action-btn delete-btn" href="delete_student.php?id=<?php echo $student['id']; ?>" onclick="return confirm('Delete this student?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

        <div class="glass-card">
            <h2>Manage Sit-In Requests</h2>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Lab</th>
                        <th>Purpose</th>
                        <th>PC No.</th>
                        <th>Time In</th>
                        <th>Session End</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($record = $records->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $record['id']; ?></td>
                            <td><?php echo htmlspecialchars($record['student_id'] . ' - ' . $record['first_name'] . ' ' . $record['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['lab_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($record['computer_number'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($record['session_end'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                            <td><?php echo htmlspecialchars($record['remarks'] ?: '-'); ?></td>
                            <td>
                                <?php if ($record['status'] === 'Pending'): ?>
                                    <form action="approve_reject_sitin.php" method="POST" class="inline-form">
                                        <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                                        <input type="text" name="computer_number" placeholder="PC No." required>
                                        <input type="text" name="remarks" placeholder="Admin remarks" required>
                                        <button type="submit" name="action" value="approve" class="btn-primary btn-small">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn-danger btn-small">Reject</button>
                                    </form>
                                <?php elseif ($record['status'] === 'Approved' && empty($record['time_out'])): ?>
                                    <form action="approve_reject_sitin.php" method="POST" class="inline-form">
                                        <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
                                        <input type="text" name="remarks" placeholder="Completion remarks">
                                        <button type="submit" name="action" value="complete" class="btn-primary btn-small">Complete</button>
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>

        <div class="glass-card">
            <h2>Per-Lab Daily Logs</h2>
            <form method="GET" class="search-form">
                <select name="lab">
                    <option value="0">All Labs</option>
                    <?php
                    $labs_list2 = $conn->query("SELECT * FROM labs ORDER BY lab_name ASC");
                    while ($lab = $labs_list2->fetch_assoc()):
                    ?>
                        <option value="<?php echo $lab['id']; ?>" <?php echo $selected_lab == $lab['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lab['lab_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn-primary">Filter</button>
            </form>

            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Lab</th>
                        <th>Date</th>
                        <th>Total Logs</th>
                    </tr>
                    <?php while ($log = $daily_logs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['lab_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['log_date']); ?></td>
                            <td><?php echo (int)$log['total_logs']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>