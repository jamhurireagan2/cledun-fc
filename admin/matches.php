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
$pageTitle = 'Matches Management';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Match deleted successfully');
    header('Location: matches.php');
    exit();
}

// Get matches with category info
$matches = $db->query("
    SELECT m.*, c.name as category_name, c.icon as category_icon 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    ORDER BY m.match_date DESC
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">⚽ Matches</h2>
        <a href="matches-add.php" class="btn-primary">
            <i class="fas fa-plus"></i> Add Match
        </a>
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
                    <th>Team</th>
                    <th>Opponent</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($matches) > 0): ?>
                    <?php foreach ($matches as $match): ?>
                        <tr>
                            <td><?php echo $match['id']; ?></td>
                            <td><?php echo $match['category_icon'] ?? '⚽'; ?> <?php echo $match['category_name'] ?? 'Match'; ?></td>
                            <td><strong><?php echo $match['opponent']; ?></strong></td>
                            <td>
                                <span style="background:<?php echo $match['match_type'] === 'home' ? 'var(--admin-secondary)' : '#e5e7eb'; ?>;padding:2px 10px;border-radius:12px;font-size:0.75rem;">
                                    <?php echo $match['match_type'] === 'home' ? '🏠 Home' : '✈️ Away'; ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($match['match_date'], 'M j, Y g:i A'); ?></td>
                            <td>
                                <?php if ($match['status'] === 'completed'): ?>
                                    <strong><?php echo $match['home_score'] ?? '0'; ?> - <?php echo $match['away_score'] ?? '0'; ?></strong>
                                <?php else: ?>
                                    <span style="color:var(--admin-gray);">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $match['status']; ?>">
                                    <?php echo ucfirst($match['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="matches-edit.php?id=<?php echo $match['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="matches.php?delete=<?php echo $match['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">⚽</div>
                            No matches found. <a href="matches-add.php" style="color:var(--admin-secondary);">Add your first match</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>