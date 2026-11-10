<?php
require_once 'includes/functions.php';
$currentPage = 'merchandise';
$pageTitle = 'Merchandise - ' . SITE_NAME;
$db = getDB();

$items = $db->query("SELECT * FROM merchandise ORDER BY display_order ASC, created_at DESC")->fetchAll();
$mpesa = getSettings('mpesa_till') ?: '0710339213';

require_once 'includes/header.php';
?>

<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>🛍️ Official Merchandise</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Support the Tiki-Taka Kings! Get your official gear today.</p>
            </div>
        </div>
    </div>
</section>

<section style="padding: 50px 0; background: var(--light-bg);">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:30px;">
            
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <div style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div style="height: 280px; background: #f3f4f6; position: relative;">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo SITE_URL; ?>uploads/merch/<?php echo $item['image']; ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:4rem;">🛍️</div>
                            <?php endif; ?>
                            
                            <?php if (!$item['in_stock']): ?>
                                <div style="position:absolute;top:10px;right:10px;background:#ef4444;color:white;padding:4px 12px;border-radius:20px;font-size:0.8rem;font-weight:700;">SOLD OUT</div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="padding: 25px;">
                            <h3 style="color:var(--primary);font-size:1.3rem;margin-bottom:5px;"><?php echo $item['title']; ?></h3>
                            <p style="color:var(--gray-text);font-size:0.9rem;margin-bottom:15px;line-height:1.5;"><?php echo $item['description']; ?></p>
                            <div style="font-size:1.5rem;font-weight:800;color:var(--secondary);margin-bottom:15px;">KES <?php echo number_format($item['price'], 2); ?></div>
                            
                            <div style="margin-bottom:20px;">
                                <strong style="display:block;font-size:0.85rem;color:var(--gray-text);margin-bottom:5px;">Available Sizes:</strong>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <?php 
                                    $sizes = explode(',', $item['sizes_available']);
                                    foreach ($sizes as $size) {
                                        if (trim($size) !== '') {
                                            echo '<span style="background:var(--light-bg);padding:4px 12px;border-radius:4px;font-size:0.8rem;font-weight:600;border:1px solid #e5e7eb;">'.trim($size).'</span>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <?php if ($item['in_stock']): ?>
                                <button onclick="buyItem('<?php echo addslashes($item['title']); ?>', '<?php echo number_format($item['price']); ?>', '<?php echo $mpesa; ?>')" 
                                        class="btn btn-primary" style="width:100%;text-align:center;">
                                    🛒 Buy Now
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" style="width:100%;text-align:center;opacity:0.5;cursor:not-allowed;" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align:center; padding: 60px 0; background:var(--white); border-radius:var(--radius);">
                    <div style="font-size:4rem;margin-bottom:20px;">🛍️</div>
                    <h3 style="color:var(--gray-text);">No merchandise available yet.</h3>
                    <p style="color:var(--gray-text);">Check back soon for official CLEDUN FC gear!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- WhatsApp Redirect Script -->
<script>
function buyItem(title, price, mpesa) {
    // Ask the user for their size
    let size = prompt("Please enter your preferred size (S, M, L, XL, XXL):");
    
    if (size === null || size.trim() === '') {
        return; // User cancelled or didn't enter a size
    }
    
    size = size.toUpperCase();
    
    // Construct the message
    let message = `Hello CLEDUN FC!%0A%0AI would like to order:%0A*Item:* ${title}%0A*Price:* KES ${price}%0A*Size:* ${size}%0A%0AHow can I pay via M-Pesa?`;
    
    // Your WhatsApp number (use the one from settings or hardcoded)
    let phoneNumber = "254710339213"; // Replace with your actual WhatsApp number
    
    // Open WhatsApp
    window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
}
</script>

<?php require_once 'includes/footer.php'; ?>