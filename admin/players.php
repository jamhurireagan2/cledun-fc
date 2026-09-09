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
$pageTitle = 'Players Management';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM players WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Player deleted successfully');
    header('Location: players.php');
    exit();
}

// Get filter
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$query = "SELECT p.*, c.name as category_name FROM players p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($categoryFilter > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

$query .= " ORDER BY p.category_id, p.is_captain DESC, p.jersey_number ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$players = $stmt->fetchAll();

// Get categories for filter
$categories = getActiveCategories();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">👥 Players</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="players-add.php" class="btn-primary">
                <i class="fas fa-user-plus"></i> Add Player
            </a>
        </div>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <!-- Filter -->
    <div style="background:var(--admin-card);padding:15px 20px;border-radius:var(--admin-radius);margin-bottom:20px;">
        <form method="GET" style="display:flex;gap:15px;align-items:center;flex-wrap:wrap;">
            <div>
                <label style="font-weight:600;font-size:0.85rem;display:block;margin-bottom:4px;">Team</label>
                <select name="category" class="form-control" style="padding:8px 12px;min-width:150px;" onchange="this.form.submit()">
                    <option value="0">All Teams</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($categoryFilter > 0): ?>
                <div style="display:flex;gap:10px;align-items:flex-end;">
                    <a href="players.php" class="btn-secondary" style="padding:8px 16px;">Clear Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Players List -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Team</th>
                    <th>Number</th>
                    <th>Position</th>
                    <th>Goals</th>
                    <th>Assists</th>
                    <th>Captain</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($players) > 0): ?>
                    <?php foreach ($players as $player): ?>
                        <tr>
                            <td><?php echo $player['id']; ?></td>
                            <td>
                                <?php if ($player['photo']): ?>
                                    <img src="<?php echo SITE_URL; ?>uploads/players/<?php echo $player['photo']; ?>" 
                                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:50%;background:var(--admin-bg);display:flex;align-items:center;justify-content:center;color:var(--admin-gray);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $player['full_name']; ?></strong></td>
                            <td><?php echo $player['category_name'] ?? 'Uncategorized'; ?></td>
                            <td><span style="background:var(--admin-secondary);color:var(--admin-dark);padding:2px 10px;border-radius:12px;font-weight:700;"><?php echo $player['jersey_number']; ?></span></td>
                            <td><span class="player-position <?php echo getPositionBadge($player['position']); ?>" style="font-size:0.75rem;"><?php echo $player['position']; ?></span></td>
                            <td><?php echo $player['goals']; ?></td>
                            <td><?php echo $player['assists']; ?></td>
                            <td><?php echo $player['is_captain'] ? '⭐' : ''; ?></td>
                            <td>
                                <span class="status-badge <?php echo $player['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $player['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="players-edit.php?id=<?php echo $player['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="players.php?delete=<?php echo $player['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i></a>
                                <a href="<?php echo SITE_URL; ?>player.php?id=<?php echo $player['id']; ?>" target="_blank" class="btn-action view"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">👥</div>
                            No players found. <a href="players-add.php" style="color:var(--admin-secondary);">Add your first player</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>