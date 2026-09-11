<?php
require_once 'includes/functions.php';

$db = getDB();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: news.php');
    exit();
}

// Get news article
$stmt = $db->prepare("
    SELECT n.*, u.full_name as author_name 
    FROM news n 
    LEFT JOIN users u ON n.author_id = u.id 
    WHERE n.id = ? AND n.is_published = 1
");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: news.php');
    exit();
}

// Update view count
$stmt = $db->prepare("UPDATE news SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$id]);

// Get related news (same category, excluding current)
$stmt = $db->prepare("
    SELECT * FROM news 
    WHERE is_published = 1 AND category = ? AND id != ? 
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->execute([$article['category'], $id]);
$relatedNews = $stmt->fetchAll();

$currentPage = 'news';
$pageTitle = $article['title'] . ' - ' . SITE_NAME;
$seo_description = $article['excerpt'] ?: substr(strip_tags($article['content']), 0, 160);

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <span style="display:inline-block;background:#fbbf24;color:#1a2a6c;padding:4px 14px;border-radius:20px;font-size:0.75rem;font-weight:700;text-transform:uppercase;margin-bottom:10px;">
                    <?php echo str_replace('-', ' ', ucfirst($article['category'])); ?>
                </span>
                <h1 style="font-size:2rem;"><?php echo $article['title']; ?></h1>
                <div style="display:flex;gap:20px;flex-wrap:wrap;opacity:0.9;font-size:0.9rem;margin-top:12px;">
                    <span><i class="far fa-calendar-alt"></i> <?php echo formatDate($article['created_at'], 'F j, Y'); ?></span>
                    <?php if ($article['author_name']): ?>
                        <span><i class="far fa-user"></i> <?php echo $article['author_name']; ?></span>
                    <?php endif; ?>
                    <span><i class="far fa-eye"></i> <?php echo $article['view_count']; ?> views</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Article Content -->
<section style="padding: 50px 0;">
    <div class="container">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:40px;">
            
            <!-- Main Article -->
            <article style="background:var(--white);border-radius:var(--radius);padding:35px;box-shadow:var(--shadow);">
                <?php if ($article['featured_image']): ?>
                    <div style="margin-bottom:25px;border-radius:var(--radius);overflow:hidden;">
                        <img src="<?php echo SITE_URL; ?>uploads/news/<?php echo $article['featured_image']; ?>" 
                             alt="<?php echo $article['title']; ?>" 
                             style="width:100%;height:auto;display:block;">
                    </div>
                <?php endif; ?>
                
                <?php if ($article['excerpt']): ?>
                    <div style="background:var(--light-bg);padding:20px;border-radius:var(--radius);border-left:4px solid var(--secondary);margin-bottom:25px;font-size:1.05rem;font-style:italic;color:var(--gray-text);">
                        <?php echo $article['excerpt']; ?>
                    </div>
                <?php endif; ?>
                
                <div style="color:var(--dark-text);line-height:1.9;font-size:1.05rem;">
                    <?php echo nl2br($article['content']); ?>
                </div>
                
                <!-- Share Buttons -->
                <div style="margin-top:35px;padding-top:25px;border-top:1px solid #e5e7eb;">
                    <h4 style="margin-bottom:12px;color:var(--primary);">Share this article:</h4>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . 'news-detail.php?id=' . $article['id']); ?>" 
                           target="_blank" 
                           style="background:#1877f2;color:white;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . 'news-detail.php?id=' . $article['id']); ?>&text=<?php echo urlencode($article['title']); ?>" 
                           target="_blank" 
                           style="background:#1da1f2;color:white;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' - ' . SITE_URL . 'news-detail.php?id=' . $article['id']); ?>" 
                           target="_blank" 
                           style="background:#25D366;color:white;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.9rem;">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
                
                <!-- Back Button -->
                <div style="margin-top:25px;">
                    <a href="news.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to News</a>
                </div>
            </article>
            
            <!-- Sidebar -->
            <aside>
                <!-- Related News -->
                <?php if (count($relatedNews) > 0): ?>
                    <div style="background:var(--white);border-radius:var(--radius);padding:25px;box-shadow:var(--shadow);margin-bottom:25px;">
                        <h3 style="color:var(--primary);margin-bottom:15px;font-size:1.1rem;">📰 Related News</h3>
                        <?php foreach ($relatedNews as $related): ?>
                            <a href="news-detail.php?id=<?php echo $related['id']; ?>" style="display:block;padding:12px 0;border-bottom:1px solid #e5e7eb;text-decoration:none;">
                                <h4 style="font-size:0.95rem;color:var(--primary);margin-bottom:4px;"><?php echo $related['title']; ?></h4>
                                <span style="font-size:0.75rem;color:var(--gray-text);">
                                    <i class="far fa-calendar-alt"></i> <?php echo formatDate($related['created_at']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Links -->
                <div style="background:var(--white);border-radius:var(--radius);padding:25px;box-shadow:var(--shadow);">
                    <h3 style="color:var(--primary);margin-bottom:15px;font-size:1.1rem;">⚡ Quick Links</h3>
                    <ul style="list-style:none;padding:0;">
                        <li style="margin-bottom:10px;">
                            <a href="news.php" style="color:var(--primary);text-decoration:none;">📰 All News</a>
                        </li>
                        <li style="margin-bottom:10px;">
                            <a href="matches.php" style="color:var(--primary);text-decoration:none;">⚽ Matches</a>
                        </li>
                        <li style="margin-bottom:10px;">
                            <a href="gallery.php" style="color:var(--primary);text-decoration:none;">📸 Gallery</a>
                        </li>
                        <li>
                            <a href="tickets.php" style="color:var(--primary);text-decoration:none;">🎟️ Get Tickets</a>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
    @media (max-width: 768px) {
        section > .container > div[style*="grid-template-columns:2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>