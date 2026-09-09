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
$pageTitle = 'Add Staff';
$error = '';

// Get categories for dropdown
$categories = getActiveCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $full_name = sanitize($_POST['full_name']);
    $role = sanitize($_POST['role']);
    $department = sanitize($_POST['department']);
    $bio = sanitize($_POST['bio']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle photo upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = '../uploads/staff/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . createSlug($full_name) . '.' . $fileExt;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $photo = $fileName;
        }
    }
    
    // Validate
    if (empty($full_name) || empty($role) || empty($department)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $db->prepare("INSERT INTO staff (category_id, full_name, role, department, bio, photo, email, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $full_name, $role, $department, $bio, $photo, $email, $phone, $is_active]);
        
        setFlash('success', 'Staff member added successfully!');
        header('Location: staff.php');
        exit();
    }
}

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);"><i class="fas fa-user-plus"></i> Add Staff Member</h2>
        <a href="staff.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back to Staff</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div style="background:var(--admin-card);padding:25px;border-radius:var(--admin-radius);box-shadow:var(--admin-shadow);">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <input type="text" name="role" class="form-control" placeholder="e.g., Head Coach, Assistant Coach" required>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department" class="form-control" required>
                        <option value="">Select Department</option>
                        <option value="coaching">Coaching</option>
                        <option value="medical">Medical</option>
                        <option value="management">Management</option>
                        <option value="board">Board</option>
                        <option value="academy">Academy</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Team (Optional)</label>
                    <select name="category_id" class="form-control">
                        <option value="">All Teams</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--admin-gray);">Leave blank for staff that works with all teams</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Staff Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Upload a staff photo (JPG, PNG, GIF)</small>
            </div>
            
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Staff background, qualifications, experience, etc."></textarea>
            </div>
            
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Staff</button>
                <a href="staff.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>