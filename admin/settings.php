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
    'map_latitude' => sanitize($_POST['map_latitude'] ?? '-1.2921'),
    'map_longitude' => sanitize($_POST['map_longitude'] ?? '36.8219'),
    'map_zoom' => intval($_POST['map_zoom'] ?? 15),
    'contact_email' => sanitize($_POST['contact_email']),
    'contact_phone' => sanitize($_POST['contact_phone'])
];

// === Handle Hero Banner Upload ===
if (isset($_FILES['hero_banner']) && $_FILES['hero_banner']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/banner/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $fileExt = strtolower(pathinfo($_FILES['hero_banner']['name'], PATHINFO_EXTENSION));
    $fileSize = $_FILES['hero_banner']['size'];
    
    if (in_array($fileExt, $allowed) && $fileSize <= 5 * 1024 * 1024) {
        $fileName = 'hero_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['hero_banner']['tmp_name'], $targetPath)) {
            // Delete old banner if exists
            $oldBanner = getSettings('hero_banner');
            if ($oldBanner && file_exists($uploadDir . $oldBanner)) {
                unlink($uploadDir . $oldBanner);
            }
            $settings['hero_banner'] = $fileName;
        }
    }
}
    
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

<div class="form-row">
    <div class="form-group">
        <label>Latitude</label>
        <input type="text" name="map_latitude" id="map_latitude" class="form-control" value="<?php echo $settings['map_latitude'] ?? '-1.2921'; ?>" placeholder="-1.2921">
        <small style="color:var(--admin-gray);">e.g., -1.2921</small>
    </div>
    <div class="form-group">
        <label>Longitude</label>
        <input type="text" name="map_longitude" id="map_longitude" class="form-control" value="<?php echo $settings['map_longitude'] ?? '36.8219'; ?>" placeholder="36.8219">
        <small style="color:var(--admin-gray);">e.g., 36.8219</small>
    </div>
    <div class="form-group">
        <label>Zoom Level (10-20)</label>
        <input type="number" name="map_zoom" id="map_zoom" class="form-control" min="10" max="20" value="<?php echo $settings['map_zoom'] ?? '15'; ?>">
        <small style="color:var(--admin-gray);">15 = street level</small>
    </div>
</div>

<!-- Live Map Preview -->
<div style="margin-top:15px;padding:15px;background:#f3f4f6;border-radius:10px;">
    <h4 style="margin-bottom:10px;color:var(--admin-dark);">🗺️ Live Map Preview</h4>
    <iframe 
        id="mapPreview"
        width="100%" 
        height="300" 
        style="border:0;border-radius:8px;"
        src="https://www.google.com/maps?q=<?php echo $settings['map_latitude'] ?? '-1.2921'; ?>,<?php echo $settings['map_longitude'] ?? '36.8219'; ?>&z=<?php echo $settings['map_zoom'] ?? '15'; ?>&output=embed"
        allowfullscreen>
    </iframe>
</div>

<!-- Get Coordinates Button -->
<div style="margin-top:10px;">
    <button type="button" onclick="getLocation()" class="btn-secondary btn-sm">
        📍 Use My Current Location
    </button>
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

<script>
// Live map preview on input change
document.getElementById('map_latitude').addEventListener('input', updateMap);
document.getElementById('map_longitude').addEventListener('input', updateMap);
document.getElementById('map_zoom').addEventListener('input', updateMap);

function updateMap() {
    const lat = document.getElementById('map_latitude').value || '-1.2921';
    const lng = document.getElementById('map_longitude').value || '36.8219';
    const zoom = document.getElementById('map_zoom').value || '15';
    
    document.getElementById('mapPreview').src = 
        `https://www.google.com/maps?q=${lat},${lng}&z=${zoom}&output=embed`;
}

// Get user's current location
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('map_latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('map_longitude').value = position.coords.longitude.toFixed(6);
            updateMap();
        }, function(error) {
            alert('Unable to get your location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>