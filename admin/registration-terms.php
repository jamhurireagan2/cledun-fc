<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$pageTitle = 'Registration Terms';
$message = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $terms = $_POST['terms'] ?? '';
    
    // Check if setting exists
    $stmt = $db->prepare("SELECT * FROM settings WHERE setting_key = 'registration_terms'");
    $stmt->execute();
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'registration_terms'");
        $stmt->execute([$terms]);
    } else {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('registration_terms', ?)");
        $stmt->execute([$terms]);
    }
    
    $message = '<div class="alert alert-success">✅ Terms updated successfully!</div>';
}

// Get current terms
$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'registration_terms'");
$stmt->execute();
$row = $stmt->fetch();
$currentTerms = $row ? $row['setting_value'] : '';

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);">📜 Registration Terms & Conditions</h2>
        <a href="registrations.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Registrations</a>
    </div>

    <?php echo $message; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--radius);box-shadow:var(--admin-shadow);">
        <form method="POST">
            <div class="form-group">
                <label style="font-size:1.1rem;font-weight:700;color:var(--admin-dark);">Terms & Conditions Text</label>
                <p style="color:var(--admin-gray);font-size:0.9rem;margin-bottom:10px;">
                    This text will be shown to users on the registration form. They must agree to it before submitting.
                </p>
                <textarea name="terms" class="form-control" rows="18" style="font-family:Inter,sans-serif;font-size:0.95rem;line-height:1.7;padding:15px;"><?php echo htmlspecialchars($currentTerms); ?></textarea>
            </div>

            <div style="display:flex;gap:10px;margin-top:15px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Terms</button>
                <a href="registrations.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>