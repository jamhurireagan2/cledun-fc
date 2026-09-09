<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = getDB();
$pageTitle = 'Messages';

// Mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->execute([$_GET['read']]);
    header('Location: messages.php');
    exit();
}

// Delete message
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Message deleted successfully');
    header('Location: messages.php');
    exit();
}

// Get all messages
$messages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);">✉️ Contact Messages</h2>
        <span style="background:var(--admin-secondary);color:var(--admin-dark);padding:4px 16px;border-radius:20px;font-weight:600;">
            <?php 
                $unread = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'")->fetch()['count'];
                echo $unread . ' unread';
            ?>
        </span>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr style="<?php echo $msg['status'] === 'unread' ? 'background:#fef3c7;' : ''; ?>">
                            <td><?php echo $msg['id']; ?></td>
                            <td><strong><?php echo $msg['name']; ?></strong></td>
                            <td><?php echo $msg['email']; ?></td>
                            <td><?php echo $msg['subject']; ?></td>
                            <td><?php echo substr($msg['message'], 0, 60); ?>...</td>
                            <td><?php echo formatDate($msg['created_at']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $msg['status']; ?>" style="background:<?php echo $msg['status'] === 'unread' ? '#fef3c7' : '#d1fae5'; ?>;color:<?php echo $msg['status'] === 'unread' ? '#92400e' : '#065f46'; ?>;">
                                    <?php echo ucfirst($msg['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($msg['status'] === 'unread'): ?>
                                    <a href="messages.php?read=<?php echo $msg['id']; ?>" class="btn-action view"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                                <a href="mailto:<?php echo $msg['email']; ?>" class="btn-action edit"><i class="fas fa-reply"></i></a>
                                <a href="messages.php?delete=<?php echo $msg['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">✉️</div>
                            No messages received yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>