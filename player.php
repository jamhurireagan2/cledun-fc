<?php
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: squad.php');
    exit();
}

$db = getDB();

// Get player details with category info
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon 
    FROM players p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) {
    header('Location: squad.php');
    exit();
}

$currentPage = 'squad';
$pageTitle = $player['full_name'] . ' - ' . SITE_NAME;

// Get player stats from matches (simplified - in production you'd have a stats table)
// For now, we use the values from the players table

require_once 'includes/header.php';
?>

<!-- Player Profile -->
<section style="padding: 50px 0;">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:40px;">
            
            <!-- Player Photo & Basic Info -->
            <div>
                <div style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);">
                    <?php if ($player['photo']): ?>
                        <img src="<?php echo SITE_URL; ?>uploads/players/<?php echo $player['photo']; ?>" 
                             alt="<?php echo $player['full_name']; ?>" 
                             style="width:100%;height:auto;">
                    <?php else: ?>
                        <div style="width:100%;height:300px;display:flex;align-items:center;justify-content:center;color:white;font-size:6rem;background:linear-gradient(135deg,var(--primary),var(--primary-light));">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div style="padding:24px;">
                        <div style="display:flex;justify-content:space-between;align-items:start;">
                            <div>
                                <h1 style="font-size:1.5rem;color:var(--primary);"><?php echo $player['full_name']; ?></h1>
                                <span class="player-position <?php echo getPositionBadge($player['position']); ?>">
                                    <?php echo $player['position']; ?>
                                </span>
                            </div>
                            <div style="background:var(--secondary);color:var(--primary);padding:8px 16px;border-radius:50%;font-size:1.5rem;font-weight:900;width:60px;height:60px;display:flex;align-items:center;justify-content:center;">
                                <?php echo $player['jersey_number']; ?>
                            </div>
                        </div>
                        
                        <?php if ($player['is_captain']): ?>
                            <div style="margin-top:10px;background:var(--secondary);color:var(--primary);padding:4px 16px;border-radius:20px;font-weight:700;font-size:0.85rem;display:inline-block;">
                                ⭐ Captain
                            </div>
                        <?php endif; ?>
                        
                        <div style="margin-top:16px;border-top:1px solid #e5e7eb;padding-top:16px;">
                            <p><strong>Team:</strong> <?php echo $player['category_name'] ?? 'Uncategorized'; ?></p>
                            <?php if ($player['nationality']): ?>
                                <p><strong>Nationality:</strong> <?php echo $player['nationality']; ?></p>
                            <?php endif; ?>
                            <?php if ($player['date_of_birth']): ?>
                                <p><strong>Date of Birth:</strong> <?php echo formatDate($player['date_of_birth']); ?></p>
                            <?php endif; ?>
                            <?php if ($player['height_cm']): ?>
                                <p><strong>Height:</strong> <?php echo $player['height_cm']; ?> cm</p>
                            <?php endif; ?>
                            <?php if ($player['weight_kg']): ?>
                                <p><strong>Weight:</strong> <?php echo $player['weight_kg']; ?> kg</p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($player['category_slug']): ?>
                            <a href="<?php echo SITE_URL; ?>category.php?slug=<?php echo $player['category_slug']; ?>" 
                               class="btn btn-outline" style="width:100%;margin-top:16px;text-align:center;">
                                ← Back to <?php echo $player['category_name']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Player Stats & Bio -->
            <div>
                <!-- Stats -->
                <div style="background:var(--white);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow);margin-bottom:24px;">
                    <h2 style="color:var(--primary);font-size:1.3rem;margin-bottom:16px;">📊 Statistics</h2>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                        <div style="background:var(--light-bg);padding:16px;border-radius:var(--radius);text-align:center;">
                            <div style="font-size:2rem;font-weight:900;color:var(--primary);"><?php echo $player['appearances'] ?? 0; ?></div>
                            <div style="font-size:0.85rem;color:var(--gray-text);">Appearances</div>
                        </div>
                        <div style="background:var(--light-bg);padding:16px;border-radius:var(--radius);text-align:center;">
                            <div style="font-size:2rem;font-weight:900;color:var(--secondary);"><?php echo $player['goals'] ?? 0; ?></div>
                            <div style="font-size:0.85rem;color:var(--gray-text);">Goals</div>
                        </div>
                        <div style="background:var(--light-bg);padding:16px;border-radius:var(--radius);text-align:center;">
                            <div style="font-size:2rem;font-weight:900;color:#3b82f6;"><?php echo $player['assists'] ?? 0; ?></div>
                            <div style="font-size:0.85rem;color:var(--gray-text);">Assists</div>
                        </div>
                    </div>
                    
                    <?php if (($player['yellow_cards'] ?? 0) > 0 || ($player['red_cards'] ?? 0) > 0): ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
                            <div style="background:#fef3c7;padding:12px;border-radius:var(--radius);text-align:center;">
                                <div style="font-size:1.2rem;font-weight:700;color:#92400e;">🟨 <?php echo $player['yellow_cards'] ?? 0; ?></div>
                                <div style="font-size:0.8rem;color:var(--gray-text);">Yellow Cards</div>
                            </div>
                            <div style="background:#fce4ec;padding:12px;border-radius:var(--radius);text-align:center;">
                                <div style="font-size:1.2rem;font-weight:700;color:#9a3412;">🟥 <?php echo $player['red_cards'] ?? 0; ?></div>
                                <div style="font-size:0.8rem;color:var(--gray-text);">Red Cards</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Bio -->
                <?php if ($player['bio']): ?>
                    <div style="background:var(--white);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow);">
                        <h2 style="color:var(--primary);font-size:1.3rem;margin-bottom:12px;">📝 About</h2>
                        <p style="color:var(--gray-text);line-height:1.8;"><?php echo nl2br($player['bio']); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Back to Squad -->
                <div style="margin-top:24px;">
                    <a href="<?php echo SITE_URL; ?>squad.php" class="btn btn-outline" style="width:100%;text-align:center;">
                        ← Back to Squad
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>