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
$pageTitle = 'Edit Match';
$error = '';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: matches.php');
    exit();
}

// Get match data
$stmt = $db->prepare("SELECT * FROM matches WHERE id = ?");
$stmt->execute([$id]);
$match = $stmt->fetch();

if (!$match) {
    header('Location: matches.php');
    exit();
}

// Get categories for dropdown
$categories = getActiveCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = intval($_POST['category_id']);
    $opponent = sanitize($_POST['opponent']);
    $match_type = sanitize($_POST['match_type']);
    $venue = sanitize($_POST['venue']);
    $match_date = sanitize($_POST['match_date']);
    $status = sanitize($_POST['status']);
    $home_score = !empty($_POST['home_score']) ? intval($_POST['home_score']) : null;
    $away_score = !empty($_POST['away_score']) ? intval($_POST['away_score']) : null;
    $report = sanitize($_POST['report']);
    
    // Validate
    if (empty($opponent) || empty($match_date) || $category_id <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $db->prepare("UPDATE matches SET category_id = ?, opponent = ?, match_type = ?, venue = ?, match_date = ?, status = ?, home_score = ?, away_score = ?, report = ? WHERE id = ?");
        $stmt->execute([$category_id, $opponent, $match_type, $venue, $match_date, $status, $home_score, $away_score, $report, $id]);
        
        setFlash('success', 'Match updated successfully!');
        header('Location: matches.php');
        exit();
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);"><i class="fas fa-edit"></i> Edit Match: vs <?php echo $match['opponent']; ?></h2>
        <a href="matches.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Matches</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Team *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select Team</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $match['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Opponent *</label>
                    <input type="text" name="opponent" class="form-control" value="<?php echo $match['opponent']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Match Type *</label>
                    <select name="match_type" class="form-control" required>
                        <option value="home" <?php echo $match['match_type'] == 'home' ? 'selected' : ''; ?>>🏠 Home</option>
                        <option value="away" <?php echo $match['match_type'] == 'away' ? 'selected' : ''; ?>>✈️ Away</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" class="form-control" value="<?php echo $match['venue']; ?>" placeholder="Stadium name or location">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Match Date & Time *</label>
                    <input type="datetime-local" name="match_date" class="form-control" value="<?php echo date('Y-m-d\TH:i', strtotime($match['match_date'])); ?>" required>
                </div>
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="scheduled" <?php echo $match['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="live" <?php echo $match['status'] == 'live' ? 'selected' : ''; ?>>Live</option>
                        <option value="completed" <?php echo $match['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $match['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Home Score</label>
                    <input type="number" name="home_score" class="form-control" min="0" value="<?php echo $match['home_score']; ?>">
                </div>
                <div class="form-group">
                    <label>Away Score</label>
                    <input type="number" name="away_score" class="form-control" min="0" value="<?php echo $match['away_score']; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Match Report</label>
                <textarea name="report" class="form-control" rows="4" placeholder="Match summary, key events, goalscorers, etc."><?php echo $match['report']; ?></textarea>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Match</button>
                <a href="matches.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>