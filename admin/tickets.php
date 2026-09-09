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
$pageTitle = 'Ticket Management';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM tickets WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Ticket deleted successfully');
    header('Location: tickets.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $match_id = intval($_POST['match_id']);
    $section = sanitize($_POST['section']);
    $price = floatval($_POST['price']);
    $total_quantity = intval($_POST['total_quantity']);
    $available_quantity = intval($_POST['available_quantity']);
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = intval($_POST['id']);
        $stmt = $db->prepare("UPDATE tickets SET match_id = ?, section = ?, price = ?, total_quantity = ?, available_quantity = ? WHERE id = ?");
        $stmt->execute([$match_id, $section, $price, $total_quantity, $available_quantity, $id]);
        setFlash('success', 'Ticket updated successfully');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO tickets (match_id, section, price, total_quantity, available_quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$match_id, $section, $price, $total_quantity, $available_quantity]);
        setFlash('success', 'Ticket added successfully');
    }
    header('Location: tickets.php');
    exit();
}

// Get tickets with match info
$tickets = $db->query("
    SELECT t.*, m.opponent, m.match_date, c.name as category_name 
    FROM tickets t 
    LEFT JOIN matches m ON t.match_id = m.id 
    LEFT JOIN categories c ON m.category_id = c.id 
    ORDER BY m.match_date DESC
")->fetchAll();

// Get matches for dropdown
$matches = $db->query("SELECT id, opponent, match_date FROM matches WHERE match_date >= NOW() AND status != 'cancelled' ORDER BY match_date ASC")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">🎟️ Tickets</h2>
        <button class="btn-primary" onclick="document.getElementById('addForm').style.display='block'">
            <i class="fas fa-plus"></i> Add Ticket
        </button>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <!-- Add Form -->
    <div id="addForm" style="display:none;background:var(--admin-card);padding:25px;border-radius:15px;margin-bottom:20px;box-shadow:var(--admin-shadow);">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Add New Ticket</h3>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Match *</label>
                    <select name="match_id" class="form-control" required>
                        <option value="">Select Match</option>
                        <?php foreach ($matches as $match): ?>
                            <option value="<?php echo $match['id']; ?>">
                                <?php echo $match['opponent']; ?> - <?php echo formatDate($match['match_date']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section *</label>
                    <input type="text" name="section" class="form-control" placeholder="e.g., VIP, Regular" required>
                </div>
                <div class="form-group">
                    <label>Price (KES) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Total Quantity *</label>
                    <input type="number" name="total_quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Available Quantity *</label>
                    <input type="number" name="available_quantity" class="form-control" required>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary">Save Ticket</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Tickets List -->
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Match</th>
                    <th>Team</th>
                    <th>Section</th>
                    <th>Price</th>
                    <th>Available</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($tickets) > 0): ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo $ticket['id']; ?></td>
                            <td><strong><?php echo $ticket['opponent']; ?></strong><br><small><?php echo formatDate($ticket['match_date']); ?></small></td>
                            <td><?php echo $ticket['category_name'] ?? 'N/A'; ?></td>
                            <td><?php echo $ticket['section']; ?></td>
                            <td>KES <?php echo number_format($ticket['price'], 2); ?></td>
                            <td>
                                <span style="background:<?php echo $ticket['available_quantity'] > 0 ? '#d1fae5' : '#fce4ec'; ?>;padding:2px 10px;border-radius:12px;font-size:0.75rem;">
                                    <?php echo $ticket['available_quantity']; ?>
                                </span>
                            </td>
                            <td><?php echo $ticket['total_quantity']; ?></td>
                            <td>
                                <a href="#" class="btn-action edit" onclick="editTicket(<?php echo htmlspecialchars(json_encode($ticket)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="tickets.php?delete=<?php echo $ticket['id']; ?>" class="btn-action delete delete-confirm">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">🎟️</div>
                            No tickets found. <a href="#" style="color:var(--admin-secondary);" onclick="document.getElementById('addForm').style.display='block'">Add your first ticket</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:var(--admin-card);padding:30px;border-radius:15px;max-width:500px;width:90%;max-height:90vh;overflow-y:auto;">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Edit Ticket</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-row">
                <div class="form-group">
                    <label>Match *</label>
                    <select name="match_id" id="edit_match_id" class="form-control" required>
                        <option value="">Select Match</option>
                        <?php foreach ($matches as $match): ?>
                            <option value="<?php echo $match['id']; ?>">
                                <?php echo $match['opponent']; ?> - <?php echo formatDate($match['match_date']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Section *</label>
                    <input type="text" name="section" id="edit_section" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Price (KES) *</label>
                    <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Total Quantity *</label>
                    <input type="number" name="total_quantity" id="edit_total_quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Available Quantity *</label>
                    <input type="number" name="available_quantity" id="edit_available_quantity" class="form-control" required>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary">Update Ticket</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTicket(ticket) {
    document.getElementById('edit_id').value = ticket.id;
    document.getElementById('edit_match_id').value = ticket.match_id;
    document.getElementById('edit_section').value = ticket.section;
    document.getElementById('edit_price').value = ticket.price;
    document.getElementById('edit_total_quantity').value = ticket.total_quantity;
    document.getElementById('edit_available_quantity').value = ticket.available_quantity;
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