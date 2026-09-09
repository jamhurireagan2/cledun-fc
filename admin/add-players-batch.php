<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Batch Add Players';
$db = getDB();
$error = '';
$success = '';

// Get categories
$categories = getActiveCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $players = [];
    $category_id = intval($_POST['category_id']);
    
    // Get player data from form
    $names = explode("\n", trim($_POST['player_names']));
    $positions = explode("\n", trim($_POST['player_positions']));
    $numbers = explode("\n", trim($_POST['player_numbers']));
    
    // Validate
    if ($category_id <= 0) {
        $error = 'Please select a team.';
    } elseif (empty($names) || empty($names[0])) {
        $error = 'Please enter at least one player name.';
    } else {
        $added = 0;
        foreach ($names as $i => $name) {
            $name = trim($name);
            if (empty($name)) continue;
            
            $position = isset($positions[$i]) ? trim($positions[$i]) : 'CM';
            $number = isset($numbers[$i]) ? intval(trim($numbers[$i])) : 0;
            
            $stmt = $db->prepare("INSERT INTO players (category_id, full_name, position, jersey_number, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$category_id, $name, $position, $number]);
            $added++;
        }
        $success = "✅ $added players added successfully!";
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <h2 style="color:var(--admin-dark);margin-bottom:20px;">⚽ Batch Add Players</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
        <!-- Batch Add Form -->
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">📝 Add Multiple Players</h3>
            <p style="color:var(--admin-gray);margin-bottom:15px;">Enter one player per line. Position and number are optional.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label>Team *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Team</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Player Names (one per line) *</label>
                    <textarea name="player_names" class="form-control" rows="8" placeholder="John Doe&#10;Jane Smith&#10;Mike Johnson" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Positions (one per line)</label>
                        <textarea name="player_positions" class="form-control" rows="8" placeholder="CM&#10;ST&#10;GK"></textarea>
                        <small style="color:var(--admin-gray);">GK, RB, CB, LB, CDM, CM, CAM, RW, LW, CF, ST</small>
                    </div>
                    <div class="form-group">
                        <label>Jersey Numbers (one per line)</label>
                        <textarea name="player_numbers" class="form-control" rows="8" placeholder="10&#10;9&#10;1"></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Add Players</button>
            </form>
        </div>
        
        <!-- Quick Tips -->
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">💡 Quick Tips</h3>
            
            <h4 style="margin-top:15px;">Position Codes:</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;font-size:0.85rem;">
                <div><strong>GK</strong> - Goalkeeper</div>
                <div><strong>RB</strong> - Right Back</div>
                <div><strong>CB</strong> - Center Back</div>
                <div><strong>LB</strong> - Left Back</div>
                <div><strong>CDM</strong> - Defensive Midfielder</div>
                <div><strong>CM</strong> - Center Midfielder</div>
                <div><strong>CAM</strong> - Attacking Midfielder</div>
                <div><strong>RW</strong> - Right Winger</div>
                <div><strong>LW</strong> - Left Winger</div>
                <div><strong>CF</strong> - Center Forward</div>
                <div><strong>ST</strong> - Striker</div>
            </div>
            
            <h4 style="margin-top:15px;">Example:</h4>
            <div style="background:#f3f4f6;padding:10px;border-radius:8px;font-size:0.85rem;">
                <strong>Names:</strong><br>
                John Doe<br>
                Jane Smith<br>
                Mike Johnson
                <br><br>
                <strong>Positions:</strong><br>
                CM<br>
                ST<br>
                GK
                <br><br>
                <strong>Numbers:</strong><br>
                10<br>
                9<br>
                1
            </div>
            
            <div style="margin-top:15px;">
                <a href="players.php" class="btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Players</a>
                <a href="players-add.php" class="btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-user-plus"></i> Add Single Player</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>