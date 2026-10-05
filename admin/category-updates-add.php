<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$pageTitle = 'Add Team Update';
$error = '';

$categories = getActiveCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id'] ?? 0);
    $title       = sanitize($_POST['title'] ?? '');
    $content     = $_POST['content'] ?? '';
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    // Handle image upload
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/category-updates/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $imageName = 'update_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $error = 'Invalid image. Allowed: JPG, PNG, WEBP · Max 5MB.';
        }
    }

    if (!$error) {
        if ($category_id <= 0 || empty($title) || empty($content)) {
            $error = 'Please fill in all required fields.';
        } else {
            $stmt = $db->prepare("
                INSERT INTO category_updates (category_id, title, content, image, is_published)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$category_id, $title, $content, $imageName, $is_published]);

            setFlash('success', 'Team update added successfully!');
            header('Location: category-updates.php');
            exit();
        }
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);">📝 Add Team Update</h2>
        <a href="category-updates.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Team / Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Team</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Publish Status</label>
                    <select name="is_published" class="form-control">
                        <option value="1">Publish Now</option>
                        <option value="0">Save as Draft</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Update Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Match Report: Senior Team vs Diamond FC" required>
            </div>

            <div class="form-group">
                <label>Featured Image (Optional)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">JPG, PNG, WEBP · Max 5MB</small>
            </div>

            <div class="form-group">
                <label>Content *</label>
                <p style="color:var(--admin-gray);font-size:0.85rem;margin-bottom:8px;">
                    Write about the match, training session, or team news. Use line breaks for paragraphs.
                </p>
                <textarea name="content" class="form-control" rows="14" 
                          style="font-family:Inter,sans-serif;font-size:0.95rem;line-height:1.7;padding:15px;" required></textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:15px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Update</button>
                <a href="category-updates.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>