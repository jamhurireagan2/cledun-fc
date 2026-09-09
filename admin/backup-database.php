<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Database Backup';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$db = getDB();
$message = '';

// Handle backup
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    try {
        // Get all tables
        $tables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $output = "-- CLEDUN FC Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            // Get create table syntax
            $stmt = $db->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $row[1] . ";\n\n";
            
            // Get data
            $stmt = $db->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
                $output .= "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = "(" . implode(", ", $rowValues) . ")";
                }
                $output .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Save backup
        $backupDir = '../uploads/backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        
        $filename = 'cledunfc_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . $filename;
        
        file_put_contents($filepath, $output);
        
        $message = '<div class="alert alert-success">✅ Backup created successfully! File: ' . $filename . '</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-error">❌ Backup failed: ' . $e->getMessage() . '</div>';
    }
}

// Handle download
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $file = '../uploads/backups/' . $_GET['download'];
    if (file_exists($file)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $_GET['download'] . '"');
        readfile($file);
        exit();
    }
}

// Get existing backups
$backups = [];
$backupDir = '../uploads/backups/';
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backupDir . $file),
                'date' => date('Y-m-d H:i:s', filemtime($backupDir . $file))
            ];
        }
    }
    rsort($backups);
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <h2 style="color:var(--admin-dark);margin-bottom:20px;">💾 Database Backup</h2>
    
    <?php echo $message; ?>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
        <!-- Create Backup -->
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">🆕 Create New Backup</h3>
            <p style="color:var(--admin-gray);margin-bottom:15px;">Create a full database backup including all tables and data.</p>
            <a href="backup-database.php?action=backup" class="btn-primary">
                <i class="fas fa-database"></i> Create Backup
            </a>
        </div>
        
        <!-- Existing Backups -->
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">📂 Existing Backups</h3>
            <?php if (count($backups) > 0): ?>
                <ul style="list-style:none;padding:0;">
                    <?php foreach ($backups as $backup): ?>
                        <li style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                            <div>
                                <strong><?php echo $backup['name']; ?></strong><br>
                                <small style="color:var(--admin-gray);">
                                    <?php echo $backup['date']; ?> · <?php echo round($backup['size'] / 1024, 2); ?> KB
                                </small>
                            </div>
                            <a href="backup-database.php?download=<?php echo urlencode($backup['name']); ?>" class="btn-primary btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color:var(--admin-gray);">No backups found. Create your first backup!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>