<?php
require_once 'includes/functions.php';

$currentPage = 'tickets';
$pageTitle = 'Book Tickets - ' . SITE_NAME;

$db = getDB();

$match_id = isset($_GET['match']) ? intval($_GET['match']) : 0;
if ($match_id <= 0) {
    header('Location: tickets.php');
    exit();
}

// Get match details
$stmt = $db->prepare("
    SELECT m.*, c.name as category_name 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.id = ?
");
$stmt->execute([$match_id]);
$match = $stmt->fetch();

if (!$match) {
    header('Location: tickets.php');
    exit();
}

// Get available tickets for this match
$stmt = $db->prepare("SELECT * FROM tickets WHERE match_id = ? AND available_quantity > 0 ORDER BY price ASC");
$stmt->execute([$match_id]);
$tickets = $stmt->fetchAll();

$error = '';
$success = '';

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    $customer_name = sanitize($_POST['customer_name']);
    $customer_email = sanitize($_POST['customer_email']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $quantity = intval($_POST['quantity']);
    
    // Validate
    if (empty($customer_name) || empty($customer_email) || $quantity <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Get ticket details
        $stmt = $db->prepare("SELECT * FROM tickets WHERE id = ? AND available_quantity >= ?");
        $stmt->execute([$ticket_id, $quantity]);
        $ticket = $stmt->fetch();
        
        if (!$ticket) {
            $error = 'Not enough tickets available.';
        } else {
            $total_price = $ticket['price'] * $quantity;
            $booking_reference = 'CLEDUN-' . strtoupper(uniqid());
            
            // Insert booking
            $stmt = $db->prepare("INSERT INTO bookings (ticket_id, customer_name, customer_email, customer_phone, quantity, total_price, booking_reference, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$ticket_id, $customer_name, $customer_email, $customer_phone, $quantity, $total_price, $booking_reference]);
            
            // Update available quantity
            $stmt = $db->prepare("UPDATE tickets SET available_quantity = available_quantity - ? WHERE id = ?");
            $stmt->execute([$quantity, $ticket_id]);
            
            $success = "Booking successful! Your reference: <strong>$booking_reference</strong>";
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>🎟️ Book Tickets</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">
                    <?php echo SITE_NAME; ?> vs <?php echo $match['opponent']; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<section style="padding: 50px 0;">
    <div class="container">
        
        <!-- Match Info -->
        <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:30px;border-left:4px solid var(--secondary);">
            <h2 style="color:var(--primary);margin-bottom:15px;">Match Details</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <p><strong>Opponent:</strong> <?php echo $match['opponent']; ?></p>
                    <p><strong>Date:</strong> <?php echo formatDate($match['match_date'], 'F j, Y g:i A'); ?></p>
                    <p><strong>Venue:</strong> <?php echo $match['venue'] ?? getSettings('stadium_name'); ?></p>
                </div>
                <div>
                    <p><strong>Type:</strong> <?php echo $match['match_type'] === 'home' ? '🏠 Home' : '✈️ Away'; ?></p>
                    <p><strong>Team:</strong> <?php echo $match['category_name'] ?? 'CLEDUN FC'; ?></p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" style="background:#d1fae5;color:#065f46;padding:15px;border-radius:8px;margin-bottom:20px;border-left:4px solid #065f46;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error" style="background:#fce4ec;color:#9a3412;padding:15px;border-radius:8px;margin-bottom:20px;border-left:4px solid #9a3412;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (count($tickets) > 0): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;">
                
                <!-- Booking Form -->
                <div style="background:var(--white);padding:30px;border-radius:var(--radius);box-shadow:var(--shadow);">
                    <h2 style="color:var(--primary);margin-bottom:20px;">Book Your Tickets</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Select Ticket Section *</label>
                            <select name="ticket_id" class="form-control" required>
                                <option value="">Choose a section</option>
                                <?php foreach ($tickets as $ticket): ?>
                                    <option value="<?php echo $ticket['id']; ?>">
                                        <?php echo $ticket['section']; ?> - KES <?php echo number_format($ticket['price'], 2); ?> 
                                        (<?php echo $ticket['available_quantity']; ?> available)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="customer_email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="customer_phone" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label>Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            <i class="fas fa-ticket-alt"></i> Book Now
                        </button>
                    </form>
                </div>
                
                <!-- Ticket Info -->
                <div style="background:var(--white);padding:30px;border-radius:var(--radius);box-shadow:var(--shadow);">
                    <h2 style="color:var(--primary);margin-bottom:20px;">Available Sections</h2>
                    
                    <?php foreach ($tickets as $ticket): ?>
                        <div style="background:var(--light-bg);padding:15px;border-radius:8px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong><?php echo $ticket['section']; ?></strong><br>
                                <small style="color:var(--gray-text);"><?php echo $ticket['available_quantity']; ?> tickets left</small>
                            </div>
                            <div style="font-size:1.2rem;font-weight:700;color:var(--primary);">
                                KES <?php echo number_format($ticket['price'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top:20px;padding:15px;background:#dbeafe;border-radius:8px;font-size:0.9rem;">
                        <strong>ℹ️ How it works:</strong>
                        <ul style="margin-top:8px;padding-left:20px;">
                            <li>Select your section and quantity</li>
                            <li>Fill in your details</li>
                            <li>Receive a booking reference</li>
                            <li>Pay at the stadium entrance</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;background:var(--white);border-radius:var(--radius);">
                <div style="font-size:4rem;margin-bottom:20px;">🎟️</div>
                <h3 style="color:var(--gray-text);">No tickets available for this match</h3>
                <p style="color:var(--gray-text);">Check back later or contact us for more information.</p>
                <a href="tickets.php" class="btn btn-primary" style="margin-top:20px;">View All Matches</a>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>