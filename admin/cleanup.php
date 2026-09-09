<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Only admin can cleanup
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'Cleanup';
$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete sample gallery images
        $db->exec("DELETE FROM gallery WHERE title LIKE 'sample%' OR image_path LIKE 'sample%'");
        
        // Delete sample news
        $db->exec("DELETE FROM news WHERE title LIKE 'Sample%' OR title LIKE 'Test%'");
        
        // Delete sample players
        $db->exec("DELETE FROM players WHERE full_name LIKE 'Sample%' OR full_name LIKE 'Test%'");
        
        $message = '<div class="alert alert-success">✅ Sample data cleaned up successfully!</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-error">❌ Cleanup failed: ' . $e->getMessage() . '</div>';
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="max-width:600px;margin:0 auto;">
        <h2 style="color:var(--admin-dark);margin-bottom:20px;">🧹 Cleanup Sample Data</h2>
        
        <?php echo $message; ?>
        
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <div style="background:#fef3c7;padding:15px;border-radius:8px;margin-bottom:20px;border-left:4px solid #f59e0b;">
                <strong>⚠️ Warning:</strong>
                <p style="color:#92400e;margin-top:5px;">This will delete all sample/test data including:
                <br>- Sample gallery images
                <br>- Sample news articles
                <br>- Sample players</p>
            </div>
            
            <form method="POST">
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to delete all sample data? This cannot be undone!')">
                        <i class="fas fa-trash"></i> Cleanup Sample Data
                    </button>
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>