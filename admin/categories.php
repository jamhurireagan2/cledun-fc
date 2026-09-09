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
$pageTitle = 'Categories Management';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Category deleted successfully');
    header('Location: categories.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = createSlug($name);
    $age_group = sanitize($_POST['age_group']);
    $description = sanitize($_POST['description']);
    $icon = sanitize($_POST['icon']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = intval($_POST['id']);
        $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, age_group = ?, description = ?, icon = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $age_group, $description, $icon, $is_active, $id]);
        setFlash('success', 'Category updated successfully');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO categories (name, slug, age_group, description, icon, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $age_group, $description, $icon, $is_active]);
        setFlash('success', 'Category added successfully');
    }
    header('Location: categories.php');
    exit();
}

$categories = $db->query("SELECT * FROM categories ORDER BY id")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">Team Categories</h2>
        <button class="btn-primary" onclick="document.getElementById('addForm').style.display='block'">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <!-- Add Form -->
    <div id="addForm" style="display:none;background:var(--admin-card);padding:25px;border-radius:15px;margin-bottom:20px;box-shadow:var(--admin-shadow);">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Add New Category</h3>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Age Group</label>
                    <input type="text" name="age_group" class="form-control" placeholder="e.g., Under 8">
                </div>
                <div class="form-group">
                    <label>Icon (emoji)</label>
                    <input type="text" name="icon" class="form-control" placeholder="e.g., ⚽ or 🌟">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary">Save Category</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Categories List -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Age Group</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td style="font-size:1.5rem;"><?php echo $cat['icon'] ?? '⚽'; ?></td>
                        <td><strong><?php echo $cat['name']; ?></strong></td>
                        <td><?php echo $cat['age_group']; ?></td>
                        <td><code style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:0.8rem;"><?php echo $cat['slug']; ?></code></td>
                        <td>
                            <span class="status-badge <?php echo $cat['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="#" class="btn-action edit" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn-action delete delete-confirm">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:var(--admin-card);padding:30px;border-radius:15px;max-width:500px;width:90%;max-height:90vh;overflow-y:auto;">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Edit Category</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-row">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Age Group</label>
                    <input type="text" name="age_group" id="edit_age_group" class="form-control">
                </div>
                <div class="form-group">
                    <label>Icon (emoji)</label>
                    <input type="text" name="icon" id="edit_icon" class="form-control">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" id="edit_is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary">Update Category</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(cat) {
    document.getElementById('edit_id').value = cat.id;
    document.getElementById('edit_name').value = cat.name;
    document.getElementById('edit_age_group').value = cat.age_group || '';
    document.getElementById('edit_icon').value = cat.icon || '';
    document.getElementById('edit_description').value = cat.description || '';
    document.getElementById('edit_is_active').value = cat.is_active;
    document.getElementById('editModal').style.display = 'flex';
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>