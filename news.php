<?php
require_once 'includes/functions.php';

$currentPage = 'news';
$pageTitle = 'News - ' . SITE_NAME;

$db = getDB();

// Get filter parameter
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Build query
$query = "SELECT * FROM news WHERE is_published = 1";
$params = [];

if ($categoryFilter !== 'all') {
    $query .= " AND category = ?";
    $params[] = $categoryFilter;
}

// Get total count
$countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
$stmt = $db->prepare($countQuery);
$stmt->execute($params);
$totalNews = $stmt->fetch()['total'];
$totalPages = ceil($totalNews / $perPage);

// Get news with pagination - ALTERNATIVE METHOD
$query .= " ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

$stmt = $db->prepare($query);
$stmt->execute($params); // $params only has category filter values
$newsList = $stmt->fetchAll();

// Get all news categories for filter
$categories = $db->query("SELECT DISTINCT category FROM news WHERE is_published = 1")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>📰 News</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Stay updated with the latest from CLEDUN FC</p>
            </div>
        </div>
    </div>
</section>

<!-- Filter -->
<section style="padding: 30px 0; background: var(--white); border-bottom: 1px solid #e5e7eb;">
    <div class="container">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:15px;align-items:center;">
            <div style="flex:1;min-width:150px;">
                <label style="font-weight:600;font-size:0.85rem;display:block;margin-bottom:4px;">Category</label>
                <select name="category" class="form-control" style="padding:8px 12px;" onchange="this.form.submit()">
                    <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category']; ?>" <?php echo $categoryFilter === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo str_replace('-', ' ', ucfirst($cat['category'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($categoryFilter !== 'all'): ?>
                <div style="display:flex;gap:10px;align-items:flex-end;">
                    <a href="<?php echo SITE_URL; ?>news.php" class="btn btn-outline" style="padding:8px 24px;">Clear Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- News Grid -->
<section style="padding: 50px 0;">
    <div class="container">
        <?php if (count($newsList) > 0): ?>
            <div class="news-grid">
                <?php foreach ($newsList as $news): ?>
                    <article class="news-card">
                        <?php if ($news['featured_image']): ?>
                            <div style="height:200px;overflow:hidden;">
                                <img src="<?php echo SITE_URL; ?>uploads/news/<?php echo $news['featured_image']; ?>" 
                                     alt="<?php echo $news['title']; ?>" 
                                     style="width:100%;height:100%;object-fit:cover;">
                            </div>
                        <?php endif; ?>
                        <div class="news-content">
                            <div class="news-meta">
                                <span><i class="far fa-calendar-alt"></i> <?php echo formatDate($news['created_at']); ?></span>
                                <span class="news-category"><?php echo str_replace('-', ' ', ucfirst($news['category'])); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo $news['title']; ?></h3>
                            <p class="news-excerpt"><?php echo $news['excerpt'] ?: substr(strip_tags($news['content']), 0, 150) . '...'; ?></p>
                            <div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center;">
                                <a href="<?php echo SITE_URL; ?>news-detail.php?id=<?php echo $news['id']; ?>" class="btn btn-primary" style="font-size:0.8rem;padding:8px 20px;">
                                    Read More
                                </a>
                                <span style="font-size:0.8rem;color:var(--gray-text);">
                                    <i class="far fa-eye"></i> <?php echo $news['view_count'] ?? 0; ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="display:flex;justify-content:center;gap:10px;margin-top:40px;flex-wrap:wrap;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&category=<?php echo $categoryFilter; ?>" class="btn btn-outline" style="padding:8px 16px;">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&category=<?php echo $categoryFilter; ?>" 
                           style="padding:8px 16px;border-radius:var(--radius);background:<?php echo $i === $page ? 'var(--secondary)' : 'var(--white)'; ?>;
                                  color:<?php echo $i === $page ? 'var(--primary)' : 'var(--gray-text)'; ?>;
                                  text-decoration:none;font-weight:<?php echo $i === $page ? '700' : '400'; ?>;
                                  border:1px solid <?php echo $i === $page ? 'var(--secondary)' : '#e5e7eb'; ?>;">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&category=<?php echo $categoryFilter; ?>" class="btn btn-outline" style="padding:8px 16px;">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;">
                <div style="font-size:4rem;margin-bottom:20px;">📭</div>
                <h3 style="color:var(--gray-text);">No news articles found</h3>
                <p style="color:var(--gray-text);">Check back later for updates.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>