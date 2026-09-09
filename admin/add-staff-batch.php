<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Batch Add Staff';
$db = getDB();
$error = '';
$success = '';

// Get categories
$categories = getActiveCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_data = [];
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    
    $names = explode("\n", trim($_POST['staff_names']));
    $roles = explode("\n", trim($_POST['staff_roles']));
    $departments = explode("\n", trim($_POST['staff_departments']));
    
    if (empty($names) || empty($names[0])) {
        $error = 'Please enter at least one staff name.';
    } else {
        $added = 0;
        foreach ($names as $i => $name) {
            $name = trim($name);
            if (empty($name)) continue;
            
            $role = isset($roles[$i]) ? trim($roles[$i]) : 'Staff';
            $department = isset($departments[$i]) ? trim($departments[$i]) : 'other';
            
            $stmt = $db->prepare("INSERT INTO staff (category_id, full_name, role, department, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$category_id, $name, $role, $department]);
            $added++;
        }
        $success = "✅ $added staff members added successfully!";
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <h2 style="color:var(--admin-dark);margin-bottom:20px;">👨‍🏫 Batch Add Staff</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">📝 Add Multiple Staff</h3>
            
            <form method="POST">
                <div class="form-group">
                    <label>Team (Optional - leave blank for all teams)</label>
                    <select name="category_id" class="form-control">
                        <option value="">All Teams</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Staff Names (one per line) *</label>
                    <textarea name="staff_names" class="form-control" rows="6" placeholder="John Coach&#10;Jane Trainer" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Roles (one per line)</label>
                        <textarea name="staff_roles" class="form-control" rows="6" placeholder="Head Coach&#10;Assistant Coach"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Departments (one per line)</label>
                        <textarea name="staff_departments" class="form-control" rows="6" placeholder="coaching&#10;coaching"></textarea>
                        <small style="color:var(--admin-gray);">coaching, medical, management, board, academy, other</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary"><i class="fas fa-user-plus"></i> Add Staff</button>
            </form>
        </div>
        
        <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
            <h3 style="margin-bottom:15px;">💡 Quick Tips</h3>
            
            <h4 style="margin-top:15px;">Department Options:</h4>
            <ul style="padding-left:20px;color:var(--admin-gray);">
                <li><strong>coaching</strong> - Coaches and trainers</li>
                <li><strong>medical</strong> - Physios and medical staff</li>
                <li><strong>management</strong> - Club management</li>
                <li><strong>board</strong> - Board members</li>
                <li><strong>academy</strong> - Academy staff</li>
                <li><strong>other</strong> - Other staff</li>
            </ul>
            
            <h4 style="margin-top:15px;">Example:</h4>
            <div style="background:#f3f4f6;padding:10px;border-radius:8px;font-size:0.85rem;">
                <strong>Names:</strong><br>
                John Coach<br>
                Jane Trainer
                <br><br>
                <strong>Roles:</strong><br>
                Head Coach<br>
                Assistant Coach
                <br><br>
                <strong>Departments:</strong><br>
                coaching<br>
                coaching
            </div>
            
            <div style="margin-top:15px;">
                <a href="staff.php" class="btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Staff</a>
                <a href="staff-add.php" class="btn-primary btn-sm" style="margin-left:10px;"><i class="fas fa-user-plus"></i> Add Single Staff</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>