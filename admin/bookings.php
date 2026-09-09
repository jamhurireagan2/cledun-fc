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
$pageTitle = 'Bookings Management';

// Update booking status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = sanitize($_GET['status']);
    $allowed = ['confirmed', 'cancelled'];
    
    if (in_array($status, $allowed)) {
        $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        setFlash('success', 'Booking ' . $status . ' successfully');
        header('Location: bookings.php');
        exit();
    }
}

// Delete booking
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'Booking deleted successfully');
    header('Location: bookings.php');
    exit();
}

// Get bookings
$bookings = $db->query("
    SELECT b.*, t.section, t.price, m.opponent, m.match_date, c.name as category_name 
    FROM bookings b 
    LEFT JOIN tickets t ON b.ticket_id = t.id 
    LEFT JOIN matches m ON t.match_id = m.id 
    LEFT JOIN categories c ON m.category_id = c.id 
    ORDER BY b.created_at DESC
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">🛒 Bookings</h2>
        <span style="background:var(--admin-secondary);color:var(--admin-dark);padding:4px 16px;border-radius:20px;font-weight:600;">
            <?php 
                $pending = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch()['count'];
                echo $pending . ' pending';
            ?>
        </span>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Customer</th>
                    <th>Match</th>
                    <th>Section</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr style="<?php echo $booking['status'] === 'pending' ? 'background:#fef3c7;' : ''; ?>">
                            <td><?php echo $booking['id']; ?></td>
                            <td><code style="background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:0.8rem;"><?php echo $booking['booking_reference']; ?></code></td>
                            <td>
                                <strong><?php echo $booking['customer_name']; ?></strong><br>
                                <small style="color:var(--admin-gray);"><?php echo $booking['customer_email']; ?></small>
                            </td>
                            <td>
                                <?php echo $booking['opponent']; ?><br>
                                <small style="color:var(--admin-gray);"><?php echo $booking['category_name'] ?? ''; ?></small>
                            </td>
                            <td><?php echo $booking['section']; ?></td>
                            <td><?php echo $booking['quantity']; ?></td>
                            <td><strong>KES <?php echo number_format($booking['total_price'], 2); ?></strong></td>
                            <td><?php echo formatDate($booking['created_at']); ?></td>
                            <td>
                                <span class="status-badge" style="background:<?php 
                                    echo $booking['status'] === 'confirmed' ? '#d1fae5' : ($booking['status'] === 'cancelled' ? '#fce4ec' : '#fef3c7'); 
                                ?>;color:<?php 
                                    echo $booking['status'] === 'confirmed' ? '#065f46' : ($booking['status'] === 'cancelled' ? '#9a3412' : '#92400e'); 
                                ?>;">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a href="bookings.php?status=confirmed&id=<?php echo $booking['id']; ?>" class="btn-action view"><i class="fas fa-check"></i> Confirm</a>
                                    <a href="bookings.php?status=cancelled&id=<?php echo $booking['id']; ?>" class="btn-action delete"><i class="fas fa-times"></i> Cancel</a>
                                <?php endif; ?>
                                <a href="bookings.php?delete=<?php echo $booking['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">🛒</div>
                            No bookings found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>