<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$pageTitle = 'Team Descriptions';
$message = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $description = $_POST['team_description'] ?? '';

    if ($category_id > 0) {
        // Check if row exists
        $stmt = $db->prepare("SELECT id FROM category_content WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $db->prepare("UPDATE category_content SET team_description = ? WHERE category_id = ?");
            $stmt->execute([$description, $category_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO category_content (category_id, team_description) VALUES (?, ?)");
            $stmt->execute([$category_id, $description]);
        }

        $message = '<div class="alert alert-success">✅ Team description saved successfully!</div>';
    }
}

// Get all categories with their content
$stmt = $db->query("
    SELECT c.*, 
           COALESCE(cc.team_description, '') AS team_description
    FROM categories c
    LEFT JOIN category_content cc ON cc.category_id = c.id
    ORDER BY c.id
");
$categories = $stmt->fetchAll();

// Get selected category for editing
$editId = isset($_GET['edit']) ? intval($_GET['edit']) : ($categories[0]['id'] ?? 0);
$editCategory = null;
foreach ($categories as $c) {
    if ($c['id'] == $editId) { $editCategory = $c; break; }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">📖 Team Descriptions</h2>
        <a href="categories.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Manage Categories</a>
    </div>

    <?php echo $message; ?>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:25px;">

        <!-- Team List -->
        <div style="background:var(--admin-card);padding:20px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="color:var(--admin-dark);margin-bottom:15px;font-size:1rem;">🎯 Select Team</h3>
            <?php foreach ($categories as $cat): ?>
                <a href="?edit=<?php echo $cat['id']; ?>" 
                   style="display:block;padding:12px 16px;border-radius:10px;margin-bottom:6px;text-decoration:none;transition:all 0.3s;<?php echo $editId == $cat['id'] ? 'background:var(--admin-secondary);color:var(--admin-dark);font-weight:700;' : 'background:#f9fafb;color:var(--admin-text);'; ?>">
                    <span style="font-size:1.2rem;"><?php echo $cat['icon'] ?? '⚽'; ?></span>
                    <?php echo $cat['name']; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Editor -->
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <?php if ($editCategory): ?>
                <h3 style="color:var(--admin-dark);margin-bottom:15px;">
                    ✏️ Editing: <?php echo $editCategory['icon']; ?> <?php echo $editCategory['name']; ?>
                </h3>

                <form method="POST">
                    <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">

                    <div class="form-group">
                        <label>Team Description</label>
                        <p style="color:var(--admin-gray);font-size:0.85rem;margin-bottom:10px;">
                            This appears at the top of the <?php echo $editCategory['name']; ?> page. Write about the team — their focus, goals, training philosophy, etc.
                        </p>
                        <textarea name="team_description" class="form-control" rows="12" 
                                  style="font-family:Inter,sans-serif;font-size:0.95rem;line-height:1.7;padding:15px;"><?php echo htmlspecialchars($editCategory['team_description']); ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Description
                    </button>
                </form>
            <?php else: ?>
                <p style="color:var(--admin-gray);">Select a team from the left to edit its description.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>