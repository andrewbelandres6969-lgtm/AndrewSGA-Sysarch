<?php
require_once "../includes/app.php";

require_role('student');

$user_id = $_SESSION['user_id'];

expire_overdue_sitin_records($conn);

$user_stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$active_stmt = $conn->prepare("
    SELECT s.*, l.lab_name
    FROM sitin_records s
    INNER JOIN labs l ON s.lab_id = l.id
    WHERE s.user_id=? AND s.status='Approved' AND s.time_out IS NULL
    ORDER BY s.id DESC LIMIT 1
");
$active_stmt->bind_param("i", $user_id);
$active_stmt->execute();
$active_record = $active_stmt->get_result()->fetch_assoc();

$labs = $conn->query("
    SELECT l.*,
    (
        SELECT COUNT(*)
        FROM sitin_records s
        WHERE s.lab_id = l.id AND s.status='Approved' AND s.time_out IS NULL
    ) AS active_users
    FROM labs l
    ORDER BY l.lab_name ASC
");

$records_stmt = $conn->prepare("
    SELECT s.*, l.lab_name
    FROM sitin_records s
    INNER JOIN labs l ON s.lab_id = l.id
    WHERE s.user_id=?
    ORDER BY s.id DESC
");
$records_stmt->bind_param("i", $user_id);
$records_stmt->execute();
$records = $records_stmt->get_result();

$summary = $conn->query("
    SELECT
        COUNT(*) total_records,
        SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) pending_count,
        SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) approved_count,
        SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) completed_count,
        SUM(CASE WHEN status='Expired' THEN 1 ELSE 0 END) expired_count
    FROM sitin_records
    WHERE user_id = {$user_id}
")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="<?php echo asset_url('Styles/style.css'); ?>">
</head>
<body>

<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="<?php echo asset_url('Images/CCSlogo.png'); ?>" class="sidebar-logo" alt="Logo" onerror="this.style.display='none';">
            <h3>Student Panel</h3>
        </div>

        <a href="<?php echo app_url('student/student_dashboard.php'); ?>" class="side-link active">Dashboard</a>
        <a href="<?php echo app_url('student/edit_profile.php'); ?>" class="side-link">Edit Profile</a>
        <a href="<?php echo app_url('reports.php'); ?>" class="side-link">Reports</a>
        <button type="button" class="side-link side-link-button" onclick="document.getElementById('feedbackModal').classList.remove('hidden'); document.body.classList.add('modal-open');">Feedback</button>
        <a href="<?php echo app_url('auth/logout.php'); ?>" class="side-link">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar glass-card">
            <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?></h2>
            <p>Remaining Sit-In Sessions: <strong><?php echo (int)$user['sitin_remaining']; ?></strong> / 30</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php $latest_announcement = get_latest_announcement($conn); ?>
        <?php if ($latest_announcement): ?>
            <div class="announcement-banner">
                <div class="announcement-banner-title">Latest Announcement</div>
                <div class="announcement-banner-meta">
                    <?php echo htmlspecialchars($latest_announcement['author_name']); ?> |
                    <?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($latest_announcement['created_at']))); ?>
                </div>
                <div><?php echo nl2br(htmlspecialchars($latest_announcement['content'])); ?></div>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card"><h3>Total</h3><p><?php echo (int)$summary['total_records']; ?></p></div>
            <div class="stat-card"><h3>Pending</h3><p><?php echo (int)$summary['pending_count']; ?></p></div>
            <div class="stat-card"><h3>Approved</h3><p><?php echo (int)$summary['approved_count']; ?></p></div>
            <div class="stat-card"><h3>Completed</h3><p><?php echo (int)$summary['completed_count']; ?></p></div>
            <div class="stat-card"><h3>Expired</h3><p><?php echo (int)$summary['expired_count']; ?></p></div>
        </div>

        <div class="glass-card">
            <h2>Student Profile</h2>
            <div class="profile-layout">
                <div class="profile-photo-box">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="<?php echo htmlspecialchars(app_url($user['photo'])); ?>" class="profile-photo" alt="Profile Photo">
                    <?php else: ?>
                        <div class="profile-photo empty-photo">No Photo</div>
                    <?php endif; ?>

                    <form action="<?php echo app_url('student/upload_photo.php'); ?>" method="POST" enctype="multipart/form-data">
                        <?php if (empty($user['photo'])): ?>
                            <input type="file" name="photo" required>
                            <br><br>
                            <button type="submit" class="btn-primary">Upload Photo</button>
                        <?php else: ?>
                            <label for="changePhoto" class="btn-primary change-photo-btn">Change Photo</label>
                            <input type="file" name="photo" id="changePhoto" class="hidden-file-input" onchange="this.form.submit()">
                        <?php endif; ?>
                    </form>
                </div>

                <div class="profile-grid">
                    <p><strong>ID:</strong> <?php echo htmlspecialchars($user['student_id']); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($user['course']); ?></p>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($user['course_level']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                </div>
            </div>
        </div>

        <?php if ($active_record): ?>
            <div class="glass-card pulse-card">
                <h2>Active Session</h2>
                <p><strong>Lab:</strong> <?php echo htmlspecialchars($active_record['lab_name']); ?></p>
                <p><strong>Computer:</strong> <?php echo htmlspecialchars($active_record['computer_number']); ?></p>
                <p><strong>Time In:</strong> <?php echo htmlspecialchars($active_record['time_in']); ?></p>
                <p><strong>Ends At:</strong> <?php echo htmlspecialchars($active_record['session_end']); ?></p>
                <p><strong>Remaining Time:</strong> <span id="countdown" data-end="<?php echo htmlspecialchars($active_record['session_end']); ?>">Loading...</span></p>
                <a class="action-btn edit-btn" href="<?php echo app_url('student/timeout_sitin.php?id=' . $active_record['id']); ?>">Time Out</a>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <h2>Lab Availability</h2>
            <div class="lab-grid">
                <?php while ($lab = $labs->fetch_assoc()):
                    $available = $lab['total_computers'] - $lab['active_users'];
                    if ($available < 0) $available = 0;
                ?>
                    <div class="lab-card floating-card">
                        <h3><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                        <p>Total Computers: <?php echo (int)$lab['total_computers']; ?></p>
                        <p>Occupied: <?php echo (int)$lab['active_users']; ?></p>
                        <p>Available: <?php echo (int)$available; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="glass-card">
            <h2>Submit Sit-In Request</h2>
            <form action="<?php echo app_url('student/save_sitin.php'); ?>" method="POST">
                <select name="lab_id" required>
                    <option value="">Select Lab Room</option>
                    <?php
                    $labs2 = $conn->query("SELECT * FROM labs ORDER BY lab_name ASC");
                    while ($lab2 = $labs2->fetch_assoc()):
                    ?>
                        <option value="<?php echo $lab2['id']; ?>"><?php echo htmlspecialchars($lab2['lab_name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <input type="text" name="purpose" placeholder="Purpose of Sit-In" required>
                <button type="submit" class="btn-primary">Submit Request</button>
            </form>
        </div>

        <div class="glass-card">
            <h2>My Sit-In Records</h2>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Lab</th>
                        <th>PC No.</th>
                        <th>Purpose</th>
                        <th>Time In</th>
                        <th>Session End</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                    <?php while ($row = $records->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['lab_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['computer_number'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                            <td><?php echo htmlspecialchars($row['session_end'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['time_out'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['remarks'] ?: '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="feedbackModal" class="modal-overlay hidden" onclick="if(event.target===this){this.classList.add('hidden');document.body.classList.remove('modal-open');}">
    <div class="modal-card feedback-modal-card" onclick="event.stopPropagation();">
        <div class="modal-header">
            <div>
                <h2>Send Feedback</h2>
                <p>We value your thoughts. Share your feedback with us.</p>
            </div>
            <button type="button" class="modal-close" onclick="document.getElementById('feedbackModal').classList.add('hidden'); document.body.classList.remove('modal-open');">&times;</button>
        </div>
        <form action="<?php echo app_url('student/submit_feedback.php'); ?>" method="POST" class="modal-form">
            <label class="modal-label" for="feedbackCategory">Category</label>
            <select name="category" id="feedbackCategory" class="feedback-category-select" required>
                <option value="">Select a category</option>
                <option value="General">General</option>
                <option value="Bug Report">Bug Report</option>
                <option value="Feature Request">Feature Request</option>
                <option value="Complaint">Complaint</option>
                <option value="Other">Other</option>
            </select>

            <label class="modal-label" for="feedbackMessage">Message</label>
            <textarea name="message" id="feedbackMessage" placeholder="Write your feedback here..." required maxlength="2000"></textarea>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="document.getElementById('feedbackModal').classList.add('hidden'); document.body.classList.remove('modal-open');">Cancel</button>
                <button type="submit" class="btn-primary">Submit Feedback</button>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo asset_url('Scripts/script.js'); ?>"></script>
</body>
</html>
