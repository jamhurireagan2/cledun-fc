<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Create Gallery Table';
$db = getDB();
$message = '';

// Check if table exists
$stmt = $db->query("SHOW TABLES LIKE 'gallery'");
$tableExists = $stmt->rowCount() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Drop table if exists
        $db->exec("DROP TABLE IF EXISTS gallery");
        
        // Create table
        $db->exec("
            CREATE TABLE IF NOT EXISTS gallery (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(200),
                image_path VARCHAR(255) NOT NULL,
                category ENUM('match-day', 'training', 'events', 'community', 'stadium', 'general') NOT NULL DEFAULT 'general',
                is_active BOOLEAN DEFAULT TRUE,
                display_order INT DEFAULT 0,
                uploaded_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
        
        // Create upload directory
        $uploadDir = '../uploads/gallery/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $message = '<div class="alert alert-success">✅ Gallery table created successfully! Upload directory also created.</div>';
        $tableExists = true;
    } catch (PDOException $e) {
        $message = '<div class="alert alert-error">❌ Error: ' . $e->getMessage() . '</div>';
    }
}

require_once 'includes/admin-header.php';
?>

<div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);">
    <h2 style="color:var(--admin-dark);">📊 Create Gallery Table</h2>
    
    <?php echo $message; ?>
    
    <div style="margin-top:20px;">
        <?php if ($tableExists): ?>
            <p style="color:green;">✅ Gallery table already exists!</p>
            <p><a href="gallery.php" class="btn-primary btn-sm"><i class="fas fa-images"></i> Go to Gallery</a></p>
        <?php else: ?>
            <p style="color:red;">❌ Gallery table does NOT exist.</p>
            <p style="color:orange;margin-top:10px;">Click the button below to create the gallery table and upload directory.</p>
            
            <form method="POST" style="margin-top:15px;">
                <button type="submit" class="btn-primary"><i class="fas fa-database"></i> Create Gallery Table</button>
            </form>
        <?php endif; ?>
    </div>
    
    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb;">
        <a href="test-gallery.php" class="btn-secondary btn-sm"><i class="fas fa-flask"></i> Test Gallery</a>
        <a href="dashboard.php" class="btn-secondary btn-sm" style="margin-left:10px;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>