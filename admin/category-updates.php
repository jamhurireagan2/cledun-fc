<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$pageTitle = 'Team Updates';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Get image to delete
    $stmt = $db->prepare("SELECT image FROM category_updates WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['image']) {
        $file = __DIR__ . '/../uploads/category-updates/' . $row['image'];
        if (file_exists($file)) @unlink($file);
    }
    $stmt = $db->prepare("DELETE FROM category_updates WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Update deleted successfully');
    header('Location: category-updates.php');
    exit();
}

// Handle publish toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $stmt = $db->prepare("UPDATE category_updates SET is_published = NOT is_published WHERE id = ?");
    $stmt->execute([intval($_GET['toggle'])]);
    header('Location: category-updates.php');
    exit();
}

// Filter by category
$filterCat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

$query = "
    SELECT cu.*, c.name AS category_name, c.icon AS category_icon
    FROM category_updates cu
    LEFT JOIN categories c ON cu.category_id = c.id
";
$params = [];
if ($filterCat > 0) {
    $query .= " WHERE cu.category_id = ?";
    $params[] = $filterCat;
}
$query .= " ORDER BY cu.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$updates = $stmt->fetchAll();

// Get all categories for filter
$categories = getActiveCategories();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">📝 Team Updates</h2>
        <a href="category-updates-add.php" class="btn-primary">
            <i class="fas fa-plus"></i> Add New Update
        </a>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <!-- Filter -->
    <div style="background:var(--admin-card);padding:15px 20px;border-radius:var(--admin-radius);margin-bottom:20px;">
        <form method="GET" style="display:flex;gap:15px;align-items:center;flex-wrap:wrap;">
            <div>
                <label style="font-weight:600;font-size:0.85rem;display:block;margin-bottom:4px;">Filter by Team</label>
                <select name="cat" class="form-control" style="padding:8px 12px;min-width:200px;" onchange="this.form.submit()">
                    <option value="0">All Teams</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filterCat == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($filterCat > 0): ?>
                <div style="display:flex;gap:10px;align-items:flex-end;">
                    <a href="category-updates.php" class="btn-secondary" style="padding:8px 16px;">Clear Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Updates List -->
    <?php if (count($updates) > 0): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;">
            <?php foreach ($updates as $u): ?>
                <div style="background:var(--admin-card);border-radius:var(--admin-radius);overflow:hidden;box-shadow:var(--admin-shadow);position:relative;">
                    
                    <?php if ($u['image']): ?>
                        <div style="height:180px;overflow:hidden;background:#f3f4f6;">
                            <img src="<?php echo SITE_URL; ?>uploads/category-updates/<?php echo $u['image']; ?>" 
                                 style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    <?php endif; ?>

                    <div style="padding:18px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:0.75rem;">
                            <span style="background:#f3f4f6;padding:2px 10px;border-radius:12px;">
                                <?php echo $u['category_icon'] ?? '⚽'; ?> <?php echo $u['category_name']; ?>
                            </span>
                            <?php if (!$u['is_published']): ?>
                                <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:12px;font-weight:600;">Draft</span>
                            <?php endif; ?>
                        </div>

                        <h4 style="font-size:1rem;color:var(--admin-dark);margin-bottom:8px;"><?php echo $u['title']; ?></h4>
                        <p style="color:var(--admin-gray);font-size:0.85rem;line-height:1.5;margin-bottom:12px;">
                            <?php echo substr(strip_tags($u['content']), 0, 100); ?>...
                        </p>
                        <p style="font-size:0.75rem;color:var(--admin-gray);margin-bottom:12px;">
                            📅 <?php echo formatDate($u['created_at']); ?>
                        </p>

                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="category-updates-edit.php?id=<?php echo $u['id']; ?>" class="btn-action edit" title="Edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="category-updates.php?toggle=<?php echo $u['id']; ?>" class="btn-action view" title="Toggle Publish">
                                <i class="fas <?php echo $u['is_published'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                            </a>
                            <a href="category-updates.php?delete=<?php echo $u['id']; ?>" class="btn-action delete delete-confirm" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:60px 0;background:var(--admin-card);border-radius:var(--admin-radius);">
            <div style="font-size:4rem;margin-bottom:15px;">📝</div>
            <h3 style="color:var(--admin-gray);">No team updates yet</h3>
            <p style="color:var(--admin-gray);margin-bottom:15px;">Add your first update to share news with your teams.</p>
            <a href="category-updates-add.php" class="btn-primary"><i class="fas fa-plus"></i> Add Update</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin-footer.php'; ?>