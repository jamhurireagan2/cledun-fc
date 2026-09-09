<?php
require_once 'includes/functions.php';

$currentPage = 'gallery';
$pageTitle = 'Gallery - ' . SITE_NAME;

$db = getDB();

// Get category filter
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';

$query = "SELECT * FROM gallery WHERE is_active = 1";
$params = [];

if ($categoryFilter !== 'all') {
    $query .= " AND category = ?";
    $params[] = $categoryFilter;
}

$query .= " ORDER BY display_order ASC, created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$images = $stmt->fetchAll();

// Get categories for filter - FIXED: Properly fetch categories
$categories = [];
try {
    $stmt = $db->query("SELECT DISTINCT category FROM gallery WHERE is_active = 1");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>📸 Gallery</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Moments captured from CLEDUN FC</p>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Content -->
<section style="padding: 50px 0;">
    <div class="container">
        
        <!-- Filter - FIXED: Properly check if category exists -->
        <?php if (count($categories) > 0): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:30px;justify-content:center;">
                <a href="gallery.php" class="btn <?php echo $categoryFilter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.85rem;padding:8px 20px;">
                    All
                </a>
                <?php foreach ($categories as $cat): ?>
                    <?php 
                    // FIXED: Check if category key exists
                    $categoryName = isset($cat['category']) ? $cat['category'] : '';
                    if (empty($categoryName)) continue;
                    ?>
                    <a href="gallery.php?category=<?php echo urlencode($categoryName); ?>" 
                       class="btn <?php echo $categoryFilter === $categoryName ? 'btn-primary' : 'btn-secondary'; ?>" 
                       style="font-size:0.85rem;padding:8px 20px;">
                        <?php echo ucfirst(str_replace('-', ' ', $categoryName)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Images Grid -->
        <?php if (count($images) > 0): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:25px;">
                <?php foreach ($images as $image): ?>
                    <?php 
                    // FIXED: Safely get category with fallback
                    $imageCategory = isset($image['category']) ? $image['category'] : 'General';
                    ?>
                    <div style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:var(--transition);cursor:pointer;position:relative;" 
                         onclick="openLightbox('<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>', '<?php echo addslashes($image['title'] ?? 'Gallery Image'); ?>')">
                        <div style="height:250px;overflow:hidden;background:#f3f4f6;">
                            <img src="<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>" 
                                 alt="<?php echo $image['title'] ?? 'Gallery Image'; ?>"
                                 style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s;">
                        </div>
                        <?php if (!empty($image['title'])): ?>
                            <div style="padding:15px;">
                                <h4 style="font-size:1rem;color:var(--primary);"><?php echo $image['title']; ?></h4>
                                <span style="font-size:0.75rem;color:var(--gray-text);background:var(--light-bg);padding:2px 12px;border-radius:12px;">
                                    <?php echo ucfirst(str_replace('-', ' ', $imageCategory)); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.5);color:white;padding:4px 12px;border-radius:20px;font-size:0.7rem;">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;">
                <div style="font-size:4rem;margin-bottom:20px;">🖼️</div>
                <h3 style="color:var(--gray-text);">No images found</h3>
                <p style="color:var(--gray-text);">Check back later for new gallery photos.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:30px;background:none;border:none;color:white;font-size:2rem;cursor:pointer;z-index:10;">
        <i class="fas fa-times"></i>
    </button>
    <img id="lightboxImage" src="" alt="" style="max-width:90%;max-height:80vh;border-radius:8px;object-fit:contain;">
    <div id="lightboxCaption" style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);color:white;font-size:1.1rem;background:rgba(0,0,0,0.5);padding:10px 24px;border-radius:8px;text-align:center;"></div>
</div>

<script>
function openLightbox(src, title) {
    document.getElementById('lightbox').style.display = 'flex';
    document.getElementById('lightboxImage').src = src;
    document.getElementById('lightboxCaption').textContent = title || 'CLEDUN FC';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close lightbox with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Close lightbox on click outside image
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLightbox();
    }
});
</script>

<style>
    .gallery-image:hover {
        transform: scale(1.05);
    }
</style>

<?php require_once 'includes/footer.php'; ?>