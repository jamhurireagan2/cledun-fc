<?php
require_once 'includes/functions.php';

$currentPage = 'squad';
$pageTitle = 'Squad - ' . SITE_NAME;

$db = getDB();

// Get all categories with player counts
$categories = getActiveCategories();

// Get all active players with category info
$players = $db->query("
    SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
    FROM players p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY c.id, p.is_captain DESC, p.jersey_number ASC
")->fetchAll();

// Group players by category
$playersByCategory = [];
foreach ($players as $player) {
    $catId = $player['category_id'] ?? 0;
    if (!isset($playersByCategory[$catId])) {
        $playersByCategory[$catId] = [
            'category_name' => $player['category_name'] ?? 'Uncategorized',
            'category_slug' => $player['category_slug'] ?? '',
            'category_icon' => $player['category_icon'] ?? '⚽',
            'players' => []
        ];
    }
    $playersByCategory[$catId]['players'][] = $player;
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>👥 Full Squad</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">All players across all CLEDUN FC teams</p>
                <span style="display: inline-block; background: var(--secondary); color: var(--primary); padding: 4px 16px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; margin-top: 8px;">
                    Total Players: <?php echo count($players); ?>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Squad Content -->
<section style="padding: 50px 0;">
    <div class="container">
        
        <?php if (count($playersByCategory) > 0): ?>
            <?php foreach ($playersByCategory as $catId => $catData): ?>
                <?php if (count($catData['players']) > 0): ?>
                    <div style="margin-bottom: 50px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;border-bottom:3px solid var(--secondary);padding-bottom:10px;">
                            <span style="font-size:2rem;"><?php echo $catData['category_icon']; ?></span>
                            <h2 style="color:var(--primary);font-size:1.8rem;"><?php echo $catData['category_name']; ?></h2>
                            <span style="background:var(--light-bg);padding:2px 14px;border-radius:20px;font-size:0.8rem;color:var(--gray-text);">
                                <?php echo count($catData['players']); ?> players
                            </span>
                            <?php if ($catData['category_slug']): ?>
                                <a href="<?php echo SITE_URL; ?>category.php?slug=<?php echo $catData['category_slug']; ?>" style="font-size:0.8rem;margin-left:auto;color:var(--secondary);text-decoration:none;font-weight:600;">
                                    View Team <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="players-grid">
                            <?php foreach ($catData['players'] as $player): ?>
                                <div class="player-card">
                                    <div class="player-image-wrapper">
                                        <?php if ($player['photo']): ?>
                                            <img src="<?php echo SITE_URL; ?>uploads/players/<?php echo $player['photo']; ?>" alt="<?php echo $player['full_name']; ?>" class="player-image">
                                        <?php else: ?>
                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:white;font-size:4rem;background:linear-gradient(135deg,var(--primary),var(--primary-light));">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="player-number"><?php echo $player['jersey_number']; ?></div>
                                        <?php if ($player['is_captain']): ?>
                                            <div style="position:absolute;bottom:10px;left:10px;background:var(--secondary);color:var(--primary);padding:2px 12px;border-radius:20px;font-size:0.7rem;font-weight:700;">⭐ CAPTAIN</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="player-info">
                                        <h3><?php echo $player['full_name']; ?></h3>
                                        <span class="player-position <?php echo getPositionBadge($player['position']); ?>"><?php echo $player['position']; ?></span>
                                        <div class="player-stats">
                                            <span><strong><?php echo $player['goals']; ?></strong> Goals</span>
                                            <span><strong><?php echo $player['assists']; ?></strong> Assists</span>
                                        </div>
                                        <a href="<?php echo SITE_URL; ?>player.php?id=<?php echo $player['id']; ?>" class="btn btn-primary" style="font-size:0.8rem;padding:8px 20px;margin-top:12px;">View Profile</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center" style="color: var(--gray-text); padding: 60px 0;">No players have been added yet.</p>
        <?php endif; ?>
        
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>