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
$pageTitle = 'Settings';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'club_name' => sanitize($_POST['club_name']),
        'club_established' => sanitize($_POST['club_established']),
        'club_motto' => sanitize($_POST['club_motto']),
        'stadium_name' => sanitize($_POST['stadium_name']),
        'stadium_location' => sanitize($_POST['stadium_location']),
        'contact_email' => sanitize($_POST['contact_email']),
        'contact_phone' => sanitize($_POST['contact_phone'])
    ];
    
    try {
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        $success = 'Settings updated successfully!';
    } catch (Exception $e) {
        $error = 'Failed to update settings: ' . $e->getMessage();
    }
}

// Get current settings
$settings = [];
$stmt = $db->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);">⚙️ Site Settings</h2>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <form method="POST">
            <h3 style="color:var(--admin-dark);margin-bottom:15px;">🏫 Club Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Club Name</label>
                    <input type="text" name="club_name" class="form-control" value="<?php echo $settings['club_name'] ?? 'CLEDUN FC'; ?>">
                </div>
                <div class="form-group">
                    <label>Established Year</label>
                    <input type="text" name="club_established" class="form-control" value="<?php echo $settings['club_established'] ?? '2026'; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Club Motto</label>
                <input type="text" name="club_motto" class="form-control" value="<?php echo $settings['club_motto'] ?? 'Building Champions Since 2026'; ?>">
            </div>
            
            <hr style="margin:25px 0;border-color:#e5e7eb;">
            
            <h3 style="color:var(--admin-dark);margin-bottom:15px;">🏟️ Stadium Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Stadium Name</label>
                    <input type="text" name="stadium_name" class="form-control" value="<?php echo $settings['stadium_name'] ?? 'Farasi Lane'; ?>">
                </div>
                <div class="form-group">
                    <label>Stadium Location</label>
                    <input type="text" name="stadium_location" class="form-control" value="<?php echo $settings['stadium_location'] ?? 'Farasi Lane Primary School'; ?>">
                </div>
            </div>
            
            <hr style="margin:25px 0;border-color:#e5e7eb;">
            
            <h3 style="color:var(--admin-dark);margin-bottom:15px;">📞 Contact Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" value="<?php echo $settings['contact_email'] ?? 'info@cledunfc.com'; ?>">
                </div>
                <div class="form-group">
                    <label>Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control" value="<?php echo $settings['contact_phone'] ?? '+254 700 123 456'; ?>">
                </div>
            </div>
            
            <div style="margin-top:25px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </form>
    </div>
    
    <div style="margin-top:25px;background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <h3 style="color:var(--admin-dark);margin-bottom:15px;">ℹ️ System Information</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
            <div><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
            <div><strong>Database:</strong> <?php 
                $stmt = $db->query("SELECT VERSION() as version");
                echo $stmt->fetch()['version'];
            ?></div>
            <div><strong>Site URL:</strong> <?php echo SITE_URL; ?></div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>