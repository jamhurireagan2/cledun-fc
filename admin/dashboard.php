<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Dashboard';
$db = getDB();

// Get counts
$playerCount = $db->query("SELECT COUNT(*) as count FROM players WHERE is_active = 1")->fetch()['count'];
$staffCount = $db->query("SELECT COUNT(*) as count FROM staff WHERE is_active = 1")->fetch()['count'];
$matchCount = $db->query("SELECT COUNT(*) as count FROM matches WHERE status != 'cancelled'")->fetch()['count'];
$newsCount = $db->query("SELECT COUNT(*) as count FROM news WHERE is_published = 1")->fetch()['count'];
$messageCount = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'")->fetch()['count'];
$bookingCount = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch()['count'];

// Get recent activity
$recentPlayers = $db->query("SELECT * FROM players ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentMatches = $db->query("SELECT * FROM matches ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentMessages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Fix: Correct include path
require_once 'includes/admin-header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-number"><?php echo $playerCount; ?></div>
        <div class="stat-label">Active Players</div>
    </div>
    <div class="stat-card" style="border-left-color: #3b82f6;">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-number"><?php echo $staffCount; ?></div>
        <div class="stat-label">Staff Members</div>
    </div>
    <div class="stat-card" style="border-left-color: #10b981;">
        <div class="stat-icon">⚽</div>
        <div class="stat-number"><?php echo $matchCount; ?></div>
        <div class="stat-label">Matches</div>
    </div>
    <div class="stat-card" style="border-left-color: #8b5cf6;">
        <div class="stat-icon">📰</div>
        <div class="stat-number"><?php echo $newsCount; ?></div>
        <div class="stat-label">News Articles</div>
    </div>
    <div class="stat-card" style="border-left-color: #ef4444;">
        <div class="stat-icon">✉️</div>
        <div class="stat-number"><?php echo $messageCount; ?></div>
        <div class="stat-label">Unread Messages</div>
    </div>
    <div class="stat-card" style="border-left-color: #f59e0b;">
        <div class="stat-icon">🛒</div>
        <div class="stat-number"><?php echo $bookingCount; ?></div>
        <div class="stat-label">Pending Bookings</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
    <!-- Recent Players -->
    <div class="admin-card">
        <h3>👥 Recent Players</h3>
        <?php if (count($recentPlayers) > 0): ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($recentPlayers as $player): ?>
                    <li style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                        <span><?php echo $player['full_name']; ?></span>
                        <span style="color:var(--admin-gray);font-size:0.85rem;">#<?php echo $player['jersey_number']; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color:var(--admin-gray);">No players added yet.</p>
        <?php endif; ?>
        <div style="margin-top:12px;">
            <a href="players.php" class="btn-primary btn-sm">View All Players</a>
        </div>
    </div>
    
    <!-- Recent Messages -->
    <div class="admin-card">
        <h3>✉️ Recent Messages</h3>
        <?php if (count($recentMessages) > 0): ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($recentMessages as $msg): ?>
                    <li style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                        <span><strong><?php echo $msg['name']; ?></strong> - <?php echo substr($msg['message'], 0, 40); ?>...</span>
                        <span style="color:var(--admin-gray);font-size:0.75rem;"><?php echo formatDate($msg['created_at']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color:var(--admin-gray);">No messages yet.</p>
        <?php endif; ?>
        <div style="margin-top:12px;">
            <a href="messages.php" class="btn-primary btn-sm">View All Messages</a>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card" style="margin-top:25px;">
    <h3>⚡ Quick Actions</h3>
    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:10px;">
        <a href="players-add.php" class="btn-primary btn-sm"><i class="fas fa-user-plus"></i> Add Player</a>
        <a href="staff-add.php" class="btn-primary btn-sm"><i class="fas fa-user-plus"></i> Add Staff</a>
        <a href="matches-add.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Match</a>
        <a href="news-add.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add News</a>
        <a href="categories.php" class="btn-secondary btn-sm"><i class="fas fa-tags"></i> Manage Categories</a>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>