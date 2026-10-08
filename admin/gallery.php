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
$pageTitle = 'Gallery Management';
$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $db->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    
    if ($image) {
        $filePath = '../uploads/gallery/' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Image deleted successfully');
    header('Location: gallery.php');
    exit();
}

// Handle toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $db->prepare("UPDATE gallery SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: gallery.php');
    exit();
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $title         = sanitize($_POST['title']);
    $category      = sanitize($_POST['category']);
    $category_id   = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $display_order = intval($_POST['display_order']);
    $uploaded_by   = $_SESSION['user_id'];
    
    $uploadDir = '../uploads/gallery/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if ($_FILES['image']['error'] === 0) {
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowed)) {
            $fileName = time() . '_' . createSlug($title) . '.' . $fileExt;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $stmt = $db->prepare("INSERT INTO gallery (category_id, title, image_path, category, display_order, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $title, $fileName, $category, $display_order, $uploaded_by]);
                $success = 'Image uploaded successfully!';
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP';
        }
    } else {
        $error = 'Please select an image to upload.';
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_gallery'])) {
    $id            = intval($_POST['id'] ?? 0);
    $title         = sanitize($_POST['edit_title'] ?? '');
    $category      = sanitize($_POST['edit_category'] ?? 'general');
    $category_id   = !empty($_POST['edit_category_id']) ? intval($_POST['edit_category_id']) : null;
    $display_order = intval($_POST['edit_display_order'] ?? 0);
    $is_active     = isset($_POST['edit_is_active']) ? 1 : 0;

    // Optional image replacement
    $newImageName = null;
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/gallery/';
        $fileExt = strtolower(pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newImageName = time() . '_' . createSlug($title) . '.' . $fileExt;

            if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $uploadDir . $newImageName)) {
                $stmt = $db->prepare("SELECT image_path FROM gallery WHERE id = ?");
                $stmt->execute([$id]);
                $old = $stmt->fetch();
                if ($old && $old['image_path'] && file_exists($uploadDir . $old['image_path'])) {
                    @unlink($uploadDir . $old['image_path']);
                }
            } else {
                $newImageName = null;
            }
        }
    }

    if ($newImageName) {
        $stmt = $db->prepare("
            UPDATE gallery 
            SET title = ?, category = ?, category_id = ?, display_order = ?, is_active = ?, image_path = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $category, $category_id, $display_order, $is_active, $newImageName, $id]);
    } else {
        $stmt = $db->prepare("
            UPDATE gallery 
            SET title = ?, category = ?, category_id = ?, display_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $category, $category_id, $display_order, $is_active, $id]);
    }

    setFlash('success', 'Image updated successfully!');
    header('Location: gallery.php');
    exit();
}

// Get all gallery images with team info
$images = $db->query("
    SELECT g.*, u.full_name as uploader_name, c.name as team_name, c.icon as team_icon
    FROM gallery g 
    LEFT JOIN users u ON g.uploaded_by = u.id 
    LEFT JOIN categories c ON g.category_id = c.id
    ORDER BY g.display_order ASC, g.created_at DESC
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">🖼️ Gallery / Slideshow</h2>
        <button class="btn-primary" onclick="document.getElementById('uploadForm').style.display='block'">
            <i class="fas fa-upload"></i> Upload Image
        </button>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div id="uploadForm" style="display:none;background:var(--admin-card);padding:25px;border-radius:15px;margin-bottom:20px;box-shadow:var(--admin-shadow);">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Upload New Image</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Image title">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <option value="general">General</option>
                        <option value="match-day">Match Day</option>
                        <option value="training">Training</option>
                        <option value="events">Events</option>
                        <option value="community">Community</option>
                        <option value="stadium">Stadium</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Team (Optional)</label>
                    <select name="category_id" class="form-control">
                        <option value="">All Teams (General)</option>
                        <?php 
                        $cats = getActiveCategories();
                        foreach ($cats as $cat): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--admin-gray);">Leave blank for general photos, or tie to a specific team</small>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                    <small style="color:var(--admin-gray);">Lower numbers appear first</small>
                </div>
                <div class="form-group">
                    <label>Image *</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <small style="color:var(--admin-gray);">JPG, PNG, GIF, WEBP (Max 5MB)</small>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Upload Image</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('uploadForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Gallery Grid -->
    <?php if (count($images) > 0): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;">
            <?php foreach ($images as $image): ?>
                <div style="background:var(--admin-card);border-radius:var(--admin-radius);overflow:hidden;box-shadow:var(--admin-shadow);position:relative;">
                    <div style="position:relative;height:180px;overflow:hidden;background:#f3f4f6;">
                        <img src="<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>" 
                             alt="<?php echo $image['title']; ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                        <?php if (!$image['is_active']): ?>
                            <div style="position:absolute;top:10px;right:10px;background:#ef4444;color:white;padding:2px 12px;border-radius:12px;font-size:0.7rem;font-weight:600;">
                                Hidden
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($image['team_name'])): ?>
                            <div style="position:absolute;bottom:10px;left:10px;background:rgba(26,42,108,0.9);color:white;padding:3px 10px;border-radius:12px;font-size:0.7rem;font-weight:600;">
                                <?php echo $image['team_icon'] ?? '⚽'; ?> <?php echo $image['team_name']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="padding:15px;">
                        <h4 style="font-size:0.9rem;margin-bottom:4px;"><?php echo $image['title'] ?? 'Untitled'; ?></h4>
                        <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--admin-gray);">
                            <span><?php echo ucfirst($image['category']); ?></span>
                            <span>Order: <?php echo $image['display_order']; ?></span>
                        </div>
                        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" 
                                    class="btn-action edit" 
                                    title="Edit"
                                    onclick='openEditModal(<?php echo htmlspecialchars(json_encode([
                                        "id" => $image["id"],
                                        "title" => $image["title"] ?? "",
                                        "category" => $image["category"] ?? "general",
                                        "category_id" => $image["category_id"] ?? "",
                                        "display_order" => $image["display_order"] ?? 0,
                                        "is_active" => $image["is_active"] ?? 1
                                    ]), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="gallery.php?toggle=<?php echo $image['id']; ?>" 
                               class="btn-action <?php echo $image['is_active'] ? 'view' : 'edit'; ?>"
                               title="<?php echo $image['is_active'] ? 'Hide' : 'Show'; ?>">
                                <i class="fas <?php echo $image['is_active'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                            </a>
                            <a href="gallery.php?delete=<?php echo $image['id']; ?>" 
                               class="btn-action delete delete-confirm"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a href="<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>" 
                               target="_blank" 
                               class="btn-action view"
                               title="View Full Size">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:60px 0;background:var(--admin-card);border-radius:var(--admin-radius);">
            <div style="font-size:4rem;margin-bottom:20px;">🖼️</div>
            <h3 style="color:var(--admin-gray);">No images in gallery</h3>
            <p style="color:var(--admin-gray);">Upload your first image to create a slideshow on the homepage.</p>
            <button class="btn-primary" style="margin-top:15px;" onclick="document.getElementById('uploadForm').style.display='block'">
                <i class="fas fa-upload"></i> Upload Image
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editGalleryModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:2000;align-items:center;justify-content:center;padding:20px;">
    <div style="background:var(--admin-card);padding:30px;border-radius:15px;max-width:550px;width:100%;max-height:90vh;overflow-y:auto;">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">✏️ Edit Gallery Image</h3>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_gallery" value="1">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="edit_title" id="edit_title" class="form-control" placeholder="Image title">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="edit_category" id="edit_category" class="form-control">
                        <option value="general">General</option>
                        <option value="match-day">Match Day</option>
                        <option value="training">Training</option>
                        <option value="events">Events</option>
                        <option value="community">Community</option>
                        <option value="stadium">Stadium</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Team (Optional)</label>
                    <select name="edit_category_id" id="edit_category_id" class="form-control">
                        <option value="">All Teams (General)</option>
                        <?php 
                        $cats = getActiveCategories();
                        foreach ($cats as $cat): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="edit_display_order" id="edit_display_order" class="form-control" value="0">
                    <small style="color:var(--admin-gray);">Lower numbers appear first</small>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:8px;">
                        <input type="checkbox" name="edit_is_active" id="edit_is_active" value="1">
                        Active (visible on site)
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Replace Image (Optional)</label>
                <input type="file" name="edit_image" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Leave empty to keep the current image</small>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Image</button>
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-hide upload form after upload
<?php if ($success): ?>
    document.getElementById('uploadForm').style.display = 'none';
<?php endif; ?>

function openEditModal(image) {
    document.getElementById('edit_id').value = image.id;
    document.getElementById('edit_title').value = image.title || '';
    document.getElementById('edit_category').value = image.category || 'general';
    document.getElementById('edit_category_id').value = image.category_id || '';
    document.getElementById('edit_display_order').value = image.display_order || 0;
    document.getElementById('edit_is_active').checked = image.is_active == 1;
    
    document.getElementById('editGalleryModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editGalleryModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.getElementById('editGalleryModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>