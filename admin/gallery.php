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
    
    // Get image path to delete file
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
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $display_order = intval($_POST['display_order']);
    $uploaded_by = $_SESSION['user_id'];
    
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
                $stmt = $db->prepare("INSERT INTO gallery (title, image_path, category, display_order, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $fileName, $category, $display_order, $uploaded_by]);
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

// Get all gallery images
$images = $db->query("
    SELECT g.*, u.full_name as uploader_name 
    FROM gallery g 
    LEFT JOIN users u ON g.uploaded_by = u.id 
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
                    </div>
                    <div style="padding:15px;">
                        <h4 style="font-size:0.9rem;margin-bottom:4px;"><?php echo $image['title'] ?? 'Untitled'; ?></h4>
                        <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--admin-gray);">
                            <span><?php echo ucfirst($image['category']); ?></span>
                            <span>Order: <?php echo $image['display_order']; ?></span>
                        </div>
                        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
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

<script>
// Auto-hide upload form after upload
<?php if ($success): ?>
    document.getElementById('uploadForm').style.display = 'none';
<?php endif; ?>
</script>

<?php require_once 'includes/admin-footer.php'; ?>