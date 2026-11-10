<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$pageTitle = 'Merchandise Management';
$error = ''; $success = '';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("SELECT image FROM merchandise WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item && $item['image'] && file_exists('../uploads/merch/' . $item['image'])) {
        unlink('../uploads/merch/' . $item['image']);
    }
    $db->prepare("DELETE FROM merchandise WHERE id = ?")->execute([$id]);
    setFlash('success', 'Item deleted successfully');
    header('Location: merchandise.php'); exit();
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $in_stock = isset($_POST['in_stock']) ? 1 : 0;
    $display_order = intval($_POST['display_order']);
    
    // Handle Sizes
    $sizes = [];
    if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
        $sizes = implode(',', $_POST['sizes']);
    }

    // Handle Image Upload
    $image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_name = time() . '_' . createSlug($title) . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/merch/' . $new_name)) {
            if ($image_name && file_exists('../uploads/merch/' . $image_name)) unlink('../uploads/merch/' . $image_name);
            $image_name = $new_name;
        }
    }

    if (empty($title) || $price <= 0) {
        $error = 'Title and Price are required.';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE merchandise SET title=?, description=?, price=?, image=?, sizes_available=?, in_stock=?, display_order=? WHERE id=?");
            $stmt->execute([$title, $description, $price, $image_name, $sizes, $in_stock, $display_order, $id]);
            $success = 'Item updated!';
        } else {
            $stmt = $db->prepare("INSERT INTO merchandise (title, description, price, image, sizes_available, in_stock, display_order) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$title, $description, $price, $image_name, $sizes, $in_stock, $display_order]);
            $success = 'Item added!';
        }
        header('Location: merchandise.php'); exit();
    }
}

$items = $db->query("SELECT * FROM merchandise ORDER BY display_order ASC, created_at DESC")->fetchAll();
require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);">🛍️ Merchandise</h2>
        <button class="btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Item</button>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
        <?php foreach ($items as $item): ?>
            <div style="background:var(--admin-card);border-radius:var(--admin-radius);overflow:hidden;box-shadow:var(--admin-shadow);">
                <?php if ($item['image']): ?>
                    <img src="<?php echo SITE_URL; ?>uploads/merch/<?php echo $item['image']; ?>" style="width:100%;height:200px;object-fit:cover;">
                <?php else: ?>
                    <div style="width:100%;height:200px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:2rem;">🛍️</div>
                <?php endif; ?>
                <div style="padding:15px;">
                    <h4 style="font-size:1.1rem;color:var(--admin-dark);"><?php echo $item['title']; ?></h4>
                    <p style="color:var(--admin-secondary);font-weight:700;">KES <?php echo number_format($item['price']); ?></p>
                    <p style="font-size:0.8rem;color:var(--admin-gray);">Sizes: <?php echo $item['sizes_available'] ?: 'None selected'; ?></p>
                    <p style="font-size:0.8rem;color:<?php echo $item['in_stock'] ? '#10b981' : '#ef4444'; ?>;font-weight:600;">Status: <?php echo $item['in_stock'] ? 'In Stock' : 'Sold Out'; ?></p>
                    
                    <div style="margin-top:10px;display:flex;gap:10px;">
                        <button class="btn-action edit" onclick='openEditModal(<?php echo json_encode($item); ?>)'><i class="fas fa-edit"></i> Edit</button>
                        <a href="?delete=<?php echo $item['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Form (Used for Add and Edit) -->
<div id="itemModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:2000;align-items:center;justify-content:center;padding:20px;overflow-y:auto;">
    <div style="background:var(--admin-card);padding:30px;border-radius:15px;max-width:600px;width:100%;">
        <h3 id="modalTitle" style="margin-bottom:15px;color:var(--admin-dark);">Add Merchandise</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="item_id" value="0">
            <input type="hidden" name="existing_image" id="existing_image" value="">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Price (KES) *</label>
                    <input type="number" name="price" id="price" class="form-control" step="0.01" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="form-group">
                <label>Available Sizes (Tick all that apply)</label>
                <div style="display:flex;gap:15px;flex-wrap:wrap;margin-top:5px;">
                    <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size): ?>
                        <label style="display:flex;align-items:center;gap:5px;cursor:pointer;">
                            <input type="checkbox" name="sizes[]" value="<?php echo $size; ?>" class="size-checkbox"> <?php echo $size; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Item Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" id="display_order" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="in_stock" id="in_stock" value="1" checked> Item is in stock
                </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn-primary">Save Item</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('itemModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add Merchandise';
    document.getElementById('item_id').value = '0';
    document.getElementById('title').value = '';
    document.getElementById('price').value = '';
    document.getElementById('description').value = '';
    document.getElementById('existing_image').value = '';
    document.getElementById('display_order').value = '0';
    document.getElementById('in_stock').checked = true;
    document.querySelectorAll('.size-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('itemModal').style.display = 'flex';
}

function openEditModal(item) {
    document.getElementById('modalTitle').innerText = 'Edit Merchandise';
    document.getElementById('item_id').value = item.id;
    document.getElementById('title').value = item.title;
    document.getElementById('price').value = item.price;
    document.getElementById('description').value = item.description;
    document.getElementById('existing_image').value = item.image;
    document.getElementById('display_order').value = item.display_order;
    document.getElementById('in_stock').checked = (item.in_stock == 1);
    
    // Check the correct sizes
    document.querySelectorAll('.size-checkbox').forEach(cb => cb.checked = false);
    if (item.sizes_available) {
        let sizes = item.sizes_available.split(',');
        document.querySelectorAll('.size-checkbox').forEach(cb => {
            if (sizes.includes(cb.value)) cb.checked = true;
        });
    }
    
    document.getElementById('itemModal').style.display = 'flex';
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>