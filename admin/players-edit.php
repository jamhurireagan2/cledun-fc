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
$pageTitle = 'Edit Player';
$error = '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: players.php');
    exit();
}

// Get player data
$stmt = $db->prepare("SELECT * FROM players WHERE id = ?");
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) {
    header('Location: players.php');
    exit();
}

// Get categories for dropdown
$categories = getActiveCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $full_name = sanitize($_POST['full_name']);
    $jersey_number = intval($_POST['jersey_number']);
    $position = sanitize($_POST['position']);
    $nationality = sanitize($_POST['nationality']);
    $date_of_birth = sanitize($_POST['date_of_birth']);
    $height_cm = intval($_POST['height_cm']);
    $weight_kg = intval($_POST['weight_kg']);
    $bio = sanitize($_POST['bio']);
    $goals = intval($_POST['goals']);
    $assists = intval($_POST['assists']);
    $appearances = intval($_POST['appearances']);
    $yellow_cards = intval($_POST['yellow_cards']);
    $red_cards = intval($_POST['red_cards']);
    $is_captain = isset($_POST['is_captain']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle photo upload
    $photo = $player['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = '../uploads/players/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Delete old photo if exists
        if ($photo && file_exists($uploadDir . $photo)) {
            unlink($uploadDir . $photo);
        }
        
        $fileExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . createSlug($full_name) . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $photo = $fileName;
        }
    }
    
    // Validate
    if (empty($full_name) || empty($position) || $category_id <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $db->prepare("UPDATE players SET category_id = ?, full_name = ?, jersey_number = ?, position = ?, nationality = ?, date_of_birth = ?, height_cm = ?, weight_kg = ?, bio = ?, photo = ?, goals = ?, assists = ?, appearances = ?, yellow_cards = ?, red_cards = ?, is_captain = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$category_id, $full_name, $jersey_number, $position, $nationality, $date_of_birth, $height_cm, $weight_kg, $bio, $photo, $goals, $assists, $appearances, $yellow_cards, $red_cards, $is_captain, $is_active, $id]);
        
        setFlash('success', 'Player updated successfully!');
        header('Location: players.php');
        exit();
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);"><i class="fas fa-edit"></i> Edit Player: <?php echo $player['full_name']; ?></h2>
        <a href="players.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Players</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Team *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Team</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $player['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo $player['full_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Jersey Number</label>
                    <input type="number" name="jersey_number" class="form-control" value="<?php echo $player['jersey_number']; ?>">
                </div>
                <div class="form-group">
                    <label>Position *</label>
                    <select name="position" class="form-control" required>
                        <option value="">Select Position</option>
                        <option value="GK" <?php echo $player['position'] == 'GK' ? 'selected' : ''; ?>>GK - Goalkeeper</option>
                        <option value="RB" <?php echo $player['position'] == 'RB' ? 'selected' : ''; ?>>RB - Right Back</option>
                        <option value="CB" <?php echo $player['position'] == 'CB' ? 'selected' : ''; ?>>CB - Center Back</option>
                        <option value="LB" <?php echo $player['position'] == 'LB' ? 'selected' : ''; ?>>LB - Left Back</option>
                        <option value="CDM" <?php echo $player['position'] == 'CDM' ? 'selected' : ''; ?>>CDM - Defensive Midfielder</option>
                        <option value="CM" <?php echo $player['position'] == 'CM' ? 'selected' : ''; ?>>CM - Center Midfielder</option>
                        <option value="CAM" <?php echo $player['position'] == 'CAM' ? 'selected' : ''; ?>>CAM - Attacking Midfielder</option>
                        <option value="RW" <?php echo $player['position'] == 'RW' ? 'selected' : ''; ?>>RW - Right Winger</option>
                        <option value="LW" <?php echo $player['position'] == 'LW' ? 'selected' : ''; ?>>LW - Left Winger</option>
                        <option value="CF" <?php echo $player['position'] == 'CF' ? 'selected' : ''; ?>>CF - Center Forward</option>
                        <option value="ST" <?php echo $player['position'] == 'ST' ? 'selected' : ''; ?>>ST - Striker</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nationality</label>
                    <input type="text" name="nationality" class="form-control" value="<?php echo $player['nationality']; ?>" placeholder="e.g., Kenyan">
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo $player['date_of_birth']; ?>">
                </div>
                <div class="form-group">
                    <label>Height (cm)</label>
                    <input type="number" name="height_cm" class="form-control" value="<?php echo $player['height_cm']; ?>">
                </div>
                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" name="weight_kg" class="form-control" value="<?php echo $player['weight_kg']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Goals</label>
                    <input type="number" name="goals" class="form-control" value="<?php echo $player['goals']; ?>">
                </div>
                <div class="form-group">
                    <label>Assists</label>
                    <input type="number" name="assists" class="form-control" value="<?php echo $player['assists']; ?>">
                </div>
                <div class="form-group">
                    <label>Appearances</label>
                    <input type="number" name="appearances" class="form-control" value="<?php echo $player['appearances']; ?>">
                </div>
                <div class="form-group">
                    <label>Yellow Cards</label>
                    <input type="number" name="yellow_cards" class="form-control" value="<?php echo $player['yellow_cards']; ?>">
                </div>
                <div class="form-group">
                    <label>Red Cards</label>
                    <input type="number" name="red_cards" class="form-control" value="<?php echo $player['red_cards']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Player Photo</label>
                <?php if ($player['photo']): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?php echo SITE_URL; ?>uploads/players/<?php echo $player['photo']; ?>" style="max-width:100px;border-radius:8px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="photo" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Upload a new photo to replace the current one (JPG, PNG, GIF)</small>
            </div>
            
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Player background, achievements, etc."><?php echo $player['bio']; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_captain" value="1" <?php echo $player['is_captain'] ? 'checked' : ''; ?>> Is Captain
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo $player['is_active'] ? 'checked' : ''; ?>> Active
                    </label>
                </div>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Player</button>
                <a href="players.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>