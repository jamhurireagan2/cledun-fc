<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Test Gallery';
$db = getDB();

// Check if gallery table exists
$tableExists = false;
try {
    $stmt = $db->query("SHOW TABLES LIKE 'gallery'");
    $tableExists = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    $error = $e->getMessage();
}

// Get images if table exists
$images = [];
if ($tableExists) {
    $images = $db->query("SELECT * FROM gallery ORDER BY display_order ASC, created_at DESC")->fetchAll();
}

require_once 'includes/admin-header.php';
?>

<div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);margin-bottom:20px;">
    <h2 style="color:var(--admin-dark);">🔍 Gallery Debug Test</h2>
    
    <div style="margin-top:20px;">
        <h3>Database Check:</h3>
        <?php if ($tableExists): ?>
            <p style="color:green;">✅ Gallery table exists!</p>
        <?php else: ?>
            <p style="color:red;">❌ Gallery table does NOT exist! Run the SQL to create it.</p>
        <?php endif; ?>
    </div>
    
    <div style="margin-top:15px;">
        <h3>Images Found:</h3>
        <?php if (count($images) > 0): ?>
            <p style="color:green;">✅ Found <?php echo count($images); ?> images in gallery.</p>
            <ul>
                <?php foreach ($images as $img): ?>
                    <li><?php echo $img['title'] ?? 'Untitled'; ?> - <?php echo $img['image_path']; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color:orange;">⚠️ No images found in gallery. Upload some images!</p>
        <?php endif; ?>
    </div>
    
    <div style="margin-top:15px;">
        <h3>File Structure Check:</h3>
        <?php 
        $uploadDir = '../uploads/gallery/';
        if (is_dir($uploadDir)) {
            echo '<p style="color:green;">✅ Uploads/gallery folder exists!</p>';
            $files = scandir($uploadDir);
            $images = array_filter($files, function($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            });
            if (count($images) > 0) {
                echo '<p>📁 Found ' . count($images) . ' image files in folder:</p><ul>';
                foreach ($images as $file) {
                    echo '<li>' . $file . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p style="color:orange;">⚠️ No image files found in uploads/gallery/ folder.</p>';
            }
        } else {
            echo '<p style="color:red;">❌ Uploads/gallery folder does NOT exist! Create it: uploads/gallery/</p>';
        }
        ?>
    </div>
    
    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb;">
        <h3>Quick Actions:</h3>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;">
            <a href="gallery.php" class="btn-primary btn-sm"><i class="fas fa-images"></i> Go to Gallery</a>
            <a href="gallery.php?action=create_table" class="btn-secondary btn-sm"><i class="fas fa-database"></i> Create Table</a>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>