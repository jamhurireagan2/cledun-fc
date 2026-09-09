<?php
require_once 'includes/functions.php';

$currentPage = 'contact';
$pageTitle = 'Contact - ' . SITE_NAME;

$db = getDB();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $messageText = sanitize($_POST['message']);
    
    // Validate
    if (empty($name) || empty($email) || empty($subject) || empty($messageText)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Save to database
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $messageText]);
        $message = 'Thank you for your message! We will get back to you soon.';
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>📧 Contact Us</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Get in touch with CLEDUN FC</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section style="padding: 50px 0;">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:50px;">
            
            <!-- Contact Form -->
            <div style="background:var(--white);padding:40px;border-radius:var(--radius);box-shadow:var(--shadow);">
                <h2 style="color:var(--primary);font-size:1.5rem;margin-bottom:20px;">Send Us a Message</h2>
                
                <?php if ($message): ?>
                    <div style="background:#d1fae5;color:#065f46;padding:15px;border-radius:var(--radius);margin-bottom:20px;border-left:4px solid #065f46;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div style="background:#fce4ec;color:#9a3412;padding:15px;border-radius:var(--radius);margin-bottom:20px;border-left:4px solid #9a3412;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h2 style="color:var(--primary);font-size:1.5rem;margin-bottom:20px;">Get In Touch</h2>
                
                <div style="background:var(--white);padding:30px;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:20px;">
                    <div style="display:flex;gap:15px;margin-bottom:20px;align-items:flex-start;">
                        <div style="background:var(--secondary);color:var(--primary);width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 style="font-weight:600;margin-bottom:4px;">Email</h4>
                            <p style="color:var(--gray-text);"><?php echo getSettings('contact_email') ?: 'info@cledunfc.com'; ?></p>
                        </div>
                    </div>
                    
                    <div style="display:flex;gap:15px;margin-bottom:20px;align-items:flex-start;">
                        <div style="background:var(--secondary);color:var(--primary);width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h4 style="font-weight:600;margin-bottom:4px;">Phone</h4>
                            <p style="color:var(--gray-text);"><?php echo getSettings('contact_phone') ?: '+254 700 123 456'; ?></p>
                        </div>
                    </div>
                    
                    <div style="display:flex;gap:15px;align-items:flex-start;">
                        <div style="background:var(--secondary);color:var(--primary);width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4 style="font-weight:600;margin-bottom:4px;">Location</h4>
                            <p style="color:var(--gray-text);"><?php echo getSettings('stadium_location') ?: 'Farasi Lane Primary School'; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div style="background:var(--white);padding:30px;border-radius:var(--radius);box-shadow:var(--shadow);">
                    <h3 style="color:var(--primary);margin-bottom:15px;">Quick Links</h3>
                    <ul style="list-style:none;padding:0;">
                        <li style="margin-bottom:10px;">
                            <a href="<?php echo SITE_URL; ?>tickets.php" style="color:var(--primary);text-decoration:none;">
                                🎟️ Buy Tickets
                            </a>
                        </li>
                        <li style="margin-bottom:10px;">
                            <a href="<?php echo SITE_URL; ?>squad.php" style="color:var(--primary);text-decoration:none;">
                                👥 Meet the Squad
                            </a>
                        </li>
                        <li style="margin-bottom:10px;">
                            <a href="<?php echo SITE_URL; ?>about.php" style="color:var(--primary);text-decoration:none;">
                                ℹ️ About CLEDUN FC
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>