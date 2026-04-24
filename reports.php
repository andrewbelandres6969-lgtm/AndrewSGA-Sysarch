<?php
require_once "includes/app.php";

if (!isset($_SESSION['user_id'])) {
    redirect_with_message_preserving_query('index.php', 'error', 'Please log in', ['show' => 'login']);
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$user_stmt = $conn->prepare("SELECT first_name, last_name, role FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    redirect_with_message_preserving_query('index.php', 'error', 'Please log in', ['show' => 'login']);
}

function format_report_duration($row)
{
    $start = $row['time_in'] ?? '';
    $end = $row['time_out'] ?: ($row['session_end'] ?: '');

    if ($start === '' || $end === '') {
        return '-';
    }

    $seconds = max(0, strtotime($end) - strtotime($start));
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    }

    return $minutes . 'm';
}

function format_report_laboratory($row)
{
    $lab_name = trim((string) ($row['lab_name'] ?? ''));

    if ($lab_name === '') {
        return 'N/A';
    }

    if (preg_match('/(\d+)/', $lab_name, $matches)) {
        return $matches[1];
    }

    return $lab_name;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="<?php echo asset_url('Styles/style.css'); ?>">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="<?php echo asset_url('Images/CCSlogo.png'); ?>" class="sidebar-logo" alt="Logo" onerror="this.style.display='none';">
                <h3><?php echo $role === 'admin' ? 'Admin Panel' : 'Student Panel'; ?></h3>
            </div>
            <?php if ($role === 'admin'): ?>
                <a href="<?php echo app_url('admin/admin_dashboard.php'); ?>" class="side-link">Dashboard</a>
                <a href="<?php echo app_url('admin/announcements.php'); ?>" class="side-link">Announcements</a>
                <a href="<?php echo app_url('reports.php'); ?>" class="side-link active">Reports</a>
                <a href="<?php echo app_url('auth/logout.php'); ?>" class="side-link">Logout</a>
            <?php else: ?>
                <a href="<?php echo app_url('student/student_dashboard.php'); ?>" class="side-link">Dashboard</a>
                <a href="<?php echo app_url('student/edit_profile.php'); ?>" class="side-link">Edit Profile</a>
                <a href="<?php echo app_url('reports.php'); ?>" class="side-link active">Reports</a>
                <a href="<?php echo app_url('auth/logout.php'); ?>" class="side-link">Logout</a>
            <?php endif; ?>
        </aside>

        <main class="main-content">
            <?php if ($role === 'admin'): ?>
                <?php
                expire_overdue_sitin_records($conn);
                $filters = get_sitin_report_filters($_GET);
                $labs = fetch_lab_options($conn);
                $report_rows = fetch_admin_sitin_report_rows($conn, $filters);
                $summary = build_admin_sitin_report_summary($report_rows);
                $students_for_reset = fetch_students_for_session_reset($conn, $filters['search']);
                $export_query = http_build_query(array_filter($filters, function ($value) {
                    return $value !== '' && $value !== 0;
                }));
                $return_url = app_url('reports.php' . ($export_query !== '' ? '?' . $export_query : ''));
                ?>

                <div class="topbar glass-card">
                    <h2>Generate Reports</h2>
                    <p>Generate and export sit-in reports by date and filter.</p>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="message success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="message error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <div class="stats-grid report-summary-grid">
                    <div class="stat-card">
                        <h3>Total Logs</h3>
                        <p><?php echo (int) $summary['total_logs']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Students</h3>
                        <p><?php echo (int) $summary['unique_students']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Approved</h3>
                        <p><?php echo (int) $summary['approved_count']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Completed</h3>
                        <p><?php echo (int) $summary['completed_count']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Active Now</h3>
                        <p><?php echo (int) $summary['active_count']; ?></p>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="report-shell-header">
                        <div>
                            <h3>Sit-in Reports</h3>
                        </div>

                        <form method="GET" class="report-toolbar">
                            <div class="report-toolbar-top">
                                <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from']); ?>" class="report-date-input">
                                <button type="submit" class="btn-primary">Search</button>
                                <a href="<?php echo app_url('reports.php'); ?>" class="btn-danger">Reset</a>
                            </div>
                            <div class="report-toolbar-bottom">
                                <label for="reportFilter">Filter</label>
                                <input id="reportFilter" type="text" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" placeholder="Search student or purpose">
                                <select name="status">
                                    <option value="">All Statuses</option>
                                    <?php foreach (['Pending', 'Approved', 'Rejected', 'Completed', 'Expired'] as $status_option): ?>
                                        <option value="<?php echo $status_option; ?>" <?php echo $filters['status'] === $status_option ? 'selected' : ''; ?>>
                                            <?php echo $status_option; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="lab_id">
                                    <option value="0">All Labs</option>
                                    <?php foreach ($labs as $lab): ?>
                                        <option value="<?php echo (int) $lab['id']; ?>" <?php echo $filters['lab_id'] === (int) $lab['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($lab['lab_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="report-export-row">
                        <a href="<?php echo app_url('admin/export_sitin_reports.php?format=excel' . ($export_query !== '' ? '&' . $export_query : '')); ?>" class="report-pill-btn">Excel</a>
                        <a href="<?php echo app_url('admin/export_sitin_reports.php?format=pdf' . ($export_query !== '' ? '&' . $export_query : '')); ?>" class="report-pill-btn">PDF</a>
                        <button type="button" class="report-pill-btn" onclick="window.print()">Print</button>
                    </div>

                    <div class="table-wrapper report-table-wrap">
                        <table class="report-table-clean">
                            <thead>
                                <tr>
                                    <th>ID Number</th>
                                    <th>Name</th>
                                    <th>Purpose</th>
                                    <th>Laboratory</th>
                                    <th>Login</th>
                                    <th>Logout</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($report_rows): ?>
                                    <?php foreach ($report_rows as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                            <td><?php echo htmlspecialchars(format_report_laboratory($row)); ?></td>
                                            <td><?php echo htmlspecialchars(date('h:i:s A', strtotime($row['time_in']))); ?></td>
                                            <td><?php echo htmlspecialchars($row['time_out'] ? date('h:i:s A', strtotime($row['time_out'])) : '--'); ?></td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['time_in']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">No sit-in report records matched the selected filters.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="report-card-header">
                        <div>
                            <h3>Search Students and Reset Session</h3>
                            <p>Reset a student's remaining sit-in sessions back to the default allowance.</p>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Remaining Sessions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($students_for_reset): ?>
                                    <?php foreach ($students_for_reset as $student_row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student_row['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student_row['first_name'] . ' ' . $student_row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student_row['course']); ?></td>
                                            <td><?php echo (int) $student_row['sitin_remaining']; ?> / <?php echo default_sitin_sessions(); ?></td>
                                            <td>
                                                <form method="POST" action="<?php echo app_url('admin/reset_sitin_sessions.php'); ?>" class="report-inline-form">
                                                    <input type="hidden" name="student_user_id" value="<?php echo (int) $student_row['id']; ?>">
                                                    <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">
                                                    <button type="submit" class="btn-danger btn-small" onclick="return confirm('Reset this student\\'s sit-in sessions back to <?php echo default_sitin_sessions(); ?>?')">Reset Session</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No students matched your search.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <?php
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
                ?>

                <div class="topbar glass-card">
                    <h2>Reports</h2>
                    <p>Logged in as <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                </div>

                <div class="glass-card">
                    <h3>My Sit-In Records</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
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
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
