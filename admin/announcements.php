<?php
require_once "../includes/app.php";

require_role('admin');
expire_overdue_sitin_records($conn);

$announcements = get_announcements($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
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
        <a href="<?php echo app_url('admin/announcements.php'); ?>" class="side-link active">Announcements</a>
        <a href="<?php echo app_url('reports.php'); ?>" class="side-link">Reports</a>
        <a href="<?php echo app_url('auth/logout.php'); ?>" class="side-link">Logout</a>
    </aside>

    <main class="main-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="message success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error-msg"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="announcement-modal-card announcement-page-card">
            <div class="announcement-page-hero">
                <span class="announcement-page-kicker">Admin Communication</span>
                <h1>Announcements</h1>
                <p>Create, edit, and publish updates that students can immediately see on their dashboard.</p>
            </div>

            <form method="POST" action="<?php echo app_url('admin/update_announcement.php'); ?>" class="modal-form announcement-compose-form" id="announcementForm">
                <input type="hidden" name="announcement_id" id="announcementId" value="">
                <input type="hidden" name="action" id="announcementAction" value="save">
                <label class="announcement-compose-label" for="announcementText">New Announcement</label>
                <textarea id="announcementText" name="announcement" rows="4" placeholder="Write an announcement for students..." required></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary hidden" id="announcementCancelEdit">Cancel Edit</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>

            <div class="announcement-list">
                <div class="announcement-list-header">
                    <h2>Posted Announcements</h2>
                    <span><?php echo count($announcements); ?> total</span>
                </div>
                <?php if ($announcements): ?>
                    <?php foreach ($announcements as $item): ?>
                        <div class="announcement-item">
                            <div class="announcement-item-meta">
                                <?php echo htmlspecialchars($item['author_name']); ?> |
                                <?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($item['created_at']))); ?>
                            </div>
                            <div class="announcement-item-actions">
                                <button
                                    type="button"
                                    class="action-btn edit-btn announcement-edit-btn"
                                    data-announcement-id="<?php echo (int) $item['id']; ?>"
                                    data-announcement-content="<?php echo htmlspecialchars($item['content'], ENT_QUOTES); ?>"
                                >
                                    Edit
                                </button>
                                <form method="POST" action="<?php echo app_url('admin/update_announcement.php'); ?>" class="announcement-delete-form">
                                    <input type="hidden" name="announcement_id" value="<?php echo (int) $item['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="action-btn delete-btn">Delete</button>
                                </form>
                            </div>
                            <div class="announcement-item-content"><?php echo nl2br(htmlspecialchars($item['content'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="announcement-empty-state">No announcements yet. Write the first one above.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="<?php echo asset_url('Scripts/script.js'); ?>"></script>
</body>
</html>
