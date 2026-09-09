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
$pageTitle = 'Edit News';
$error = '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: news.php');
    exit();
}

// Get news data
$stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: news.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = createSlug($title);
    $content = $_POST['content'];
    $excerpt = sanitize($_POST['excerpt']);
    $category = sanitize($_POST['category']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    // Handle image upload
    $featured_image = $article['featured_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
        $uploadDir = '../uploads/news/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Delete old image if exists
        if ($featured_image && file_exists($uploadDir . $featured_image)) {
            unlink($uploadDir . $featured_image);
        }
        
        $fileExt = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . createSlug($title) . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath)) {
            $featured_image = $fileName;
        }
    }
    
    // Check if slug exists and is not this article
    $stmt = $db->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $id]);
    if ($stmt->fetch()) {
        $slug = $slug . '-' . time();
    }
    
    // Validate
    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $db->prepare("UPDATE news SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, category = ?, is_featured = ?, is_published = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $category, $is_featured, $is_published, $id]);
        
        setFlash('success', 'News article updated successfully!');
        header('Location: news.php');
        exit();
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);"><i class="fas fa-edit"></i> Edit Article: <?php echo $article['title']; ?></h2>
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
                    <input type="text" name="title" class="form-control" value="<?php echo $article['title']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="match-report" <?php echo $article['category'] == 'match-report' ? 'selected' : ''; ?>>Match Report</option>
                        <option value="transfer" <?php echo $article['category'] == 'transfer' ? 'selected' : ''; ?>>Transfer</option>
                        <option value="academy" <?php echo $article['category'] == 'academy' ? 'selected' : ''; ?>>Academy</option>
                        <option value="club-announcement" <?php echo $article['category'] == 'club-announcement' ? 'selected' : ''; ?>>Club Announcement</option>
                        <option value="community" <?php echo $article['category'] == 'community' ? 'selected' : ''; ?>>Community</option>
                        <option value="general" <?php echo $article['category'] == 'general' ? 'selected' : ''; ?>>General</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Excerpt</label>
                <input type="text" name="excerpt" class="form-control" value="<?php echo $article['excerpt']; ?>" placeholder="Short summary of the article">
            </div>
            
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" class="form-control" rows="10" placeholder="Full article content..." required><?php echo $article['content']; ?></textarea>
                <small style="color:var(--admin-gray);">You can use HTML for formatting</small>
            </div>
            
            <div class="form-group">
                <label>Featured Image</label>
                <?php if ($article['featured_image']): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?php echo SITE_URL; ?>uploads/news/<?php echo $article['featured_image']; ?>" style="max-width:200px;border-radius:8px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="featured_image" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Upload a new image to replace the current one (JPG, PNG, GIF)</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_featured" value="1" <?php echo $article['is_featured'] ? 'checked' : ''; ?>> Featured Article
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_published" value="1" <?php echo $article['is_published'] ? 'checked' : ''; ?>> Published
                    </label>
                </div>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Article</button>
                <a href="news.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>