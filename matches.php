<?php
require_once 'includes/functions.php';

$currentPage = 'matches';
$pageTitle = 'Matches - ' . SITE_NAME;

$db = getDB();

// Get filter parameters
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';

// Build query
$query = "
    SELECT m.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE 1=1
";

$params = [];

if ($categoryFilter > 0) {
    $query .= " AND m.category_id = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter !== 'all') {
    $query .= " AND m.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY m.match_date DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$matches = $stmt->fetchAll();

// Get all categories for filter
$categories = getActiveCategories();

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>📅 Fixtures & Results</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">All matches across all CLEDUN FC teams</p>
            </div>
        </div>
    </div>
</section>

<!-- Filters -->
<section style="padding: 30px 0; background: var(--white); border-bottom: 1px solid #e5e7eb;">
    <div class="container">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:15px;align-items:center;">
            <div style="flex:1;min-width:150px;">
                <label style="font-weight:600;font-size:0.85rem;display:block;margin-bottom:4px;">Team</label>
                <select name="category" class="form-control" style="padding:8px 12px;">
                    <option value="0">All Teams</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex:1;min-width:150px;">
                <label style="font-weight:600;font-size:0.85rem;display:block;margin-bottom:4px;">Status</label>
                <select name="status" class="form-control" style="padding:8px 12px;">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="live" <?php echo $statusFilter === 'live' ? 'selected' : ''; ?>>Live</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-end;">
                <button type="submit" class="btn btn-primary" style="padding:8px 24px;">Filter</button>
                <a href="<?php echo SITE_URL; ?>matches.php" class="btn btn-outline" style="padding:8px 24px;">Reset</a>
            </div>
        </form>
    </div>
</section>

<!-- Matches List -->
<section style="padding: 50px 0;">
    <div class="container">
        <?php if (count($matches) > 0): ?>
            <div class="matches-grid">
                <?php foreach ($matches as $match): ?>
                    <div class="match-card">
                        <div class="match-header">
                            <span class="match-competition">
                                <?php echo $match['category_icon'] ?? '⚽'; ?> 
                                <?php echo $match['category_name'] ?? 'Match'; ?>
                            </span>
                            <span class="match-status status-<?php echo $match['status']; ?>">
                                <?php echo ucfirst($match['status']); ?>
                            </span>
                        </div>
                        <div class="match-teams">
                            <div class="match-team">
                                <div class="match-team-name" style="font-weight:700;"><?php echo SITE_NAME; ?></div>
                                <div style="font-size:0.8rem;color:var(--gray-text);">
                                    <?php echo $match['match_type'] === 'home' ? '🏠 Home' : '✈️ Away'; ?>
                                </div>
                            </div>
                            <div class="match-score">
                                <?php if ($match['status'] === 'completed'): ?>
                                    <div style="font-size:1.8rem;font-weight:900;color:var(--primary);">
                                        <?php echo $match['home_score'] ?? '0'; ?> - <?php echo $match['away_score'] ?? '0'; ?>
                                    </div>
                                    <?php if ($match['possession_home']): ?>
                                        <div style="font-size:0.7rem;color:var(--gray-text);">
                                            Possession: <?php echo $match['possession_home']; ?>% - <?php echo $match['possession_away']; ?>%
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="vs">VS</span>
                                <?php endif; ?>
                            </div>
                            <div class="match-team">
                                <div class="match-team-name"><?php echo $match['opponent']; ?></div>
                            </div>
                        </div>
                        <div class="match-venue">
                            <i class="fas fa-calendar-alt"></i> <?php echo formatDate($match['match_date'], 'F j, Y g:i A'); ?>
                            <br>
                            <i class="fas fa-map-marker-alt"></i> <?php echo $match['venue'] ?? getSettings('stadium_name'); ?>
                        </div>
                        <?php if ($match['status'] === 'completed' && $match['report']): ?>
                            <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb;font-size:0.85rem;color:var(--gray-text);">
                                <i class="fas fa-file-alt"></i> <?php echo substr($match['report'], 0, 100) . '...'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;">
                <div style="font-size:4rem;margin-bottom:20px;">⚽</div>
                <h3 style="color:var(--gray-text);">No matches found</h3>
                <p style="color:var(--gray-text);">Try adjusting your filters or check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>