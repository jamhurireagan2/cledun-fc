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
$pageTitle = 'Staff Management';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Staff member deleted successfully');
    header('Location: staff.php');
    exit();
}

// Get staff with category info
$staff = $db->query("
    SELECT s.*, c.name as category_name 
    FROM staff s 
    LEFT JOIN categories c ON s.category_id = c.id 
    ORDER BY s.department, s.full_name
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">👨‍🏫 Staff</h2>
        <a href="staff-add.php" class="btn-primary">
            <i class="fas fa-user-plus"></i> Add Staff Member
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
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Team</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($staff) > 0): ?>
                    <?php foreach ($staff as $member): ?>
                        <tr>
                            <td><?php echo $member['id']; ?></td>
                            <td>
                                <?php if ($member['photo']): ?>
                                    <img src="<?php echo SITE_URL; ?>uploads/staff/<?php echo $member['photo']; ?>" 
                                         style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:50%;background:var(--admin-bg);display:flex;align-items:center;justify-content:center;color:var(--admin-gray);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $member['full_name']; ?></strong></td>
                            <td><?php echo $member['role']; ?></td>
                            <td><span style="background:var(--admin-bg);padding:2px 10px;border-radius:12px;font-size:0.75rem;"><?php echo ucfirst($member['department']); ?></span></td>
                            <td><?php echo $member['category_name'] ?? 'All Teams'; ?></td>
                            <td><?php echo $member['email'] ?? '-'; ?></td>
                            <td>
                                <span class="status-badge <?php echo $member['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="staff-edit.php?id=<?php echo $member['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="staff.php?delete=<?php echo $member['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">👨‍🏫</div>
                            No staff members found. <a href="staff-add.php" style="color:var(--admin-secondary);">Add your first staff member</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>