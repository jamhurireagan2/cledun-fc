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
$pageTitle = 'News Management';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    setFlash('success', 'News article deleted successfully');
    header('Location: news.php');
    exit();
}

// Get news
$news = $db->query("
    SELECT n.*, u.full_name as author_name 
    FROM news n 
    LEFT JOIN users u ON n.author_id = u.id 
    ORDER BY n.created_at DESC
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">📰 News</h2>
        <a href="news-add.php" class="btn-primary">
            <i class="fas fa-plus"></i> Add News Article
        </a>
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
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($news) > 0): ?>
                    <?php foreach ($news as $article): ?>
                        <tr>
                            <td><?php echo $article['id']; ?></td>
                            <td><strong><?php echo $article['title']; ?></strong></td>
                            <td><span style="background:var(--admin-bg);padding:2px 10px;border-radius:12px;font-size:0.75rem;"><?php echo str_replace('-', ' ', ucfirst($article['category'])); ?></span></td>
                            <td><?php echo $article['author_name'] ?? 'Unknown'; ?></td>
                            <td><?php echo formatDate($article['created_at']); ?></td>
                            <td><?php echo $article['view_count']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $article['is_published'] ? 'published' : 'draft'; ?>">
                                    <?php echo $article['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                                <?php if ($article['is_featured']): ?>
                                    <span class="status-badge" style="background:var(--admin-secondary);color:var(--admin-dark);">⭐ Featured</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="news-edit.php?id=<?php echo $article['id']; ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="news.php?delete=<?php echo $article['id']; ?>" class="btn-action delete delete-confirm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px 0;color:var(--admin-gray);">
                            <div style="font-size:2rem;margin-bottom:8px;">📰</div>
                            No news articles found. <a href="news-add.php" style="color:var(--admin-secondary);">Write your first article</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>