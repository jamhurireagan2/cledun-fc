<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Sidebar Check';

require_once 'includes/admin-header.php';
?>

<div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);">
    <h2 style="color:var(--admin-dark);">📋 Sidebar Menu Debug</h2>
    <p>This page shows the current sidebar menu items from admin-header.php</p>
    
    <div style="margin-top:20px;background:#f3f4f6;padding:20px;border-radius:8px;">
        <h3>Expected Menu Items:</h3>
        <ol style="padding-left:20px;line-height:2;">
            <li>Dashboard</li>
            <li>Players</li>
            <li>Staff</li>
            <li>Matches</li>
            <li>News</li>
            <li>Categories</li>
            <li style="color:green;font-weight:bold;">✅ Gallery (Should be here!)</li>
            <li>Tickets</li>
            <li>Bookings</li>
            <li>Messages</li>
            <li>Settings</li>
            <li>Logout</li>
        </ol>
    </div>
    
    <div style="margin-top:20px;">
        <h3>Check your admin-header.php file:</h3>
        <p>Make sure this line exists in the menu section:</p>
        <code style="display:block;background:#1a2a6c;color:#fbbf24;padding:12px;border-radius:8px;margin-top:8px;">
            &lt;li&gt;&lt;a href="gallery.php" class="&lt;?php echo strpos($_SERVER['PHP_SELF'], 'gallery') !== false ? 'active' : ''; ?&gt;"&gt;<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&lt;i class="fas fa-images"&gt;&lt;/i&gt; Gallery<br>
            &lt;/a&gt;&lt;/li&gt;
        </code>
    </div>
    
    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb;">
        <a href="test-gallery.php" class="btn-primary btn-sm"><i class="fas fa-flask"></i> Test Gallery</a>
        <a href="dashboard.php" class="btn-secondary btn-sm" style="margin-left:10px;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>