<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Batch Add Matches';
$db = getDB();
$error = '';
$success = '';

// Get categories
$categories = getActiveCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $match_type = sanitize($_POST['match_type']);
    $status = sanitize($_POST['status']);
    $venue = sanitize($_POST['venue']);
    
    $opponents = explode("\n", trim($_POST['opponents']));
    $dates = explode("\n", trim($_POST['match_dates']));
    
    if ($category_id <= 0) {
        $error = 'Please select a team.';
    } elseif (empty($opponents) || empty($opponents[0])) {
        $error = 'Please enter at least one opponent.';
    } else {
        $added = 0;
        foreach ($opponents as $i => $opponent) {
            $opponent = trim($opponent);
            if (empty($opponent)) continue;
            
            $match_date = isset($dates[$i]) ? trim($dates[$i]) : date('Y-m-d H:i:s');
            
            $stmt = $db->prepare("INSERT INTO matches (category_id, opponent, match_type, venue, match_date, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $opponent, $match_type, $venue, $match_date, $status]);
            $added++;
        }
        $success = "✅ $added matches added successfully!";
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <h2 style="color:var(--admin-dark);margin-bottom:20px;">⚽ Batch Add Matches</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">📝 Add Multiple Matches</h3>
            
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
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Match Type *</label>
                        <select name="match_type" class="form-control" required>
                            <option value="home">🏠 Home</option>
                            <option value="away">✈️ Away</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" class="form-control" placeholder="e.g., Farasi Lane Stadium">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Opponents (one per line) *</label>
                        <textarea name="opponents" class="form-control" rows="6" placeholder="Team A&#10;Team B" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Match Dates (one per line)</label>
                        <textarea name="match_dates" class="form-control" rows="6" placeholder="2024-12-01 15:00:00&#10;2024-12-08 16:00:00"></textarea>
                        <small style="color:var(--admin-gray);">Format: YYYY-MM-DD HH:MM:SS</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Add Matches</button>
            </form>
        </div>
        
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">💡 Quick Tips</h3>
            
            <h4 style="margin-top:15px;">Example:</h4>
            <div style="background:#f3f4f6;padding:10px;border-radius:8px;font-size:0.85rem;">
                <strong>Opponents:</strong><br>
                Nairobi City Stars<br>
                KCB FC<br>
                AFC Leopards
                <br><br>
                <strong>Dates:</strong><br>
                2024-12-01 15:00:00<br>
                2024-12-08 16:00:00<br>
                2024-12-15 14:00:00
            </div>
            
            <div style="margin-top:15px;">
                <a href="matches.php" class="btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Matches</a>
                <a href="matches-add.php" class="btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-plus"></i> Add Single Match</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>