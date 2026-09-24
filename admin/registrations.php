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
$pageTitle = 'Player Registrations';

$filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'pending';

$query = "SELECT r.*, c.name AS category_name, c.icon AS category_icon
          FROM player_registrations r
          LEFT JOIN categories c ON r.category_id = c.id";
$params = [];

if (in_array($filter, ['pending', 'approved', 'rejected'])) {
    $query .= " WHERE r.status = ?";
    $params[] = $filter;
}

$query .= " ORDER BY r.submitted_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">📝 Player Registrations</h2>
        <div style="display:flex;gap:8px;">
            <a href="?status=pending" class="btn-<?php echo $filter==='pending'?'primary':'secondary'; ?> btn-sm">Pending</a>
            <a href="?status=approved" class="btn-<?php echo $filter==='approved'?'primary':'secondary'; ?> btn-sm">Approved</a>
            <a href="?status=rejected" class="btn-<?php echo $filter==='rejected'?'primary':'secondary'; ?> btn-sm">Rejected</a>
        </div>
    </div>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Player</th>
                    <th>Category</th>
                    <th>Age</th>
                    <th>Contact</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($registrations) > 0): ?>
                    <?php foreach ($registrations as $r):
                        $age = (new DateTime($r['birth_date']))->diff(new DateTime())->y;
                    ?>
                        <tr>
                            <td><?php echo $r['id']; ?></td>
                            <td><strong><?php echo $r['first_name'] . ' ' . $r['last_name']; ?></strong></td>
                            <td><?php echo $r['category_icon'] ?? '⚽'; ?> <?php echo $r['category_name']; ?></td>
                            <td><?php echo $age; ?> yrs</td>
                            <td>
                                <?php echo $r['email'] ?: '-'; ?><br>
                                <small style="color:var(--admin-gray);"><?php echo $r['phone']; ?></small>
                            </td>
                            <td><?php echo formatDate($r['submitted_at'], 'M j, Y H:i'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $r['status']; ?>" style="background:<?php
                                    echo $r['status']==='approved'?'#d1fae5':($r['status']==='rejected'?'#fce4ec':'#fef3c7');
                                ?>;color:<?php
                                    echo $r['status']==='approved'?'#065f46':($r['status']==='rejected'?'#9a3412':'#92400e');
                                ?>;">
                                    <?php echo ucfirst($r['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="registrations-view.php?id=<?php echo $r['id']; ?>" class="btn-action view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="registrations-delete.php?id=<?php echo $r['id']; ?>" class="btn-action delete delete-confirm" title="Delete"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--admin-gray);">
                        <div style="font-size:3rem;margin-bottom:10px;">📭</div>
                        No <?php echo $filter; ?> registrations.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>