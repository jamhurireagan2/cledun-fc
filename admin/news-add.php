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
$pageTitle = 'Add News';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = createSlug($title);
    $content = $_POST['content'];
    $excerpt = sanitize($_POST['excerpt']);
    $category = sanitize($_POST['category']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $author_id = $_SESSION['user_id'];
    
    // Handle image upload
    $featured_image = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $uploadDir = '../uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . createSlug($title) . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath)) {
            $featured_image = $fileName;
        }
    }
    
    // Check if slug exists
    $stmt = $db->prepare("SELECT id FROM news WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        $slug = $slug . '-' . time();
    }
    
    // Validate
    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $db->prepare("INSERT INTO news (title, slug, content, excerpt, featured_image, author_id, category, is_featured, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $author_id, $category, $is_featured, $is_published]);
        
        setFlash('success', 'News article added successfully!');
        header('Location: news.php');
        exit();
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);"><i class="fas fa-plus"></i> Add News Article</h2>
        <a href="news.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to News</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="match-report">Match Report</option>
                        <option value="transfer">Transfer</option>
                        <option value="academy">Academy</option>
                        <option value="club-announcement">Club Announcement</option>
                        <option value="community">Community</option>
                        <option value="general">General</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Excerpt</label>
                <input type="text" name="excerpt" class="form-control" placeholder="Short summary of the article">
            </div>
            
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" class="form-control" rows="10" placeholder="Full article content..." required></textarea>
                <small style="color:var(--admin-gray);">You can use HTML for formatting</small>
            </div>
            
            <div class="form-group">
                <label>Featured Image</label>
                <input type="file" name="featured_image" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Upload a featured image (JPG, PNG, GIF)</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1"> Featured Article
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_published" value="1" checked> Publish
                    </label>
                </div>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Article</button>
                <a href="news.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>