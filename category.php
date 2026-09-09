<?php
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
if (empty($slug)) {
    header('Location: index.php');
    exit();
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit();
}

$currentPage = 'squad';
$pageTitle = $category['name'] . ' - ' . SITE_NAME;

// Get players for this category
$stmt = $db->prepare("SELECT * FROM players WHERE category_id = ? AND is_active = 1 ORDER BY is_captain DESC, jersey_number ASC");
$stmt->execute([$category['id']]);
$players = $stmt->fetchAll();

// Get staff for this category
$stmt = $db->prepare("SELECT * FROM staff WHERE category_id = ? AND is_active = 1");
$stmt->execute([$category['id']]);
$staff = $stmt->fetchAll();

// Get matches for this category
$stmt = $db->prepare("SELECT * FROM matches WHERE category_id = ? ORDER BY match_date DESC LIMIT 5");
$stmt->execute([$category['id']]);
$matches = $stmt->fetchAll();

// Get achievements for this category
$stmt = $db->prepare("SELECT * FROM achievements WHERE category_id = ? ORDER BY year DESC");
$stmt->execute([$category['id']]);
$achievements = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Category Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <div style="font-size: 3rem; margin-bottom: 10px;"><?php echo $category['icon'] ?? '⚽'; ?></div>
                <h1><?php echo $category['name']; ?></h1>
                <p style="opacity: 0.8; font-size: 1.1rem;"><?php echo $category['description']; ?></p>
                <span style="display: inline-block; background: var(--secondary); color: var(--primary); padding: 4px 16px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; margin-top: 8px;">
                    Age Group: <?php echo $category['age_group']; ?>
                </span>
                <span style="display: inline-block; background: rgba(255,255,255,0.1); color: var(--white); padding: 4px 16px; border-radius: 20px; font-size: 0.85rem; margin-top: 8px; margin-left: 8px;">
                    👥 <?php echo count($players); ?> Players
                </span>
            </div>
        </div>
    </div>
</section>

<!-- Category Content -->
<section class="category-content" style="padding: 50px 0;">
    <div class="container">
        
        <!-- Players Section -->
        <div class="section-title">
            <h2>👕 Squad</h2>
            <p>Players representing <?php echo $category['name']; ?></p>
        </div>
        
        <?php if (count($players) > 0): ?>
            <div class="players-grid">
                <?php foreach ($players as $player): ?>
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
                                <span><strong><?php echo $player['appearances']; ?></strong> Apps</span>
                            </div>
                            <a href="<?php echo SITE_URL; ?>player.php?id=<?php echo $player['id']; ?>" class="btn btn-primary" style="font-size:0.8rem;padding:8px 20px;margin-top:12px;">View Profile</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center" style="color: var(--gray-text); padding: 40px 0;">No players added to this team yet.</p>
        <?php endif; ?>

        <!-- Staff Section -->
        <div style="margin-top: 60px;">
            <div class="section-title">
                <h2>👨‍🏫 Staff</h2>
                <p>Coaching and support staff for <?php echo $category['name']; ?></p>
            </div>
            
            <?php if (count($staff) > 0): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:25px;">
                    <?php foreach ($staff as $member): ?>
                        <div style="background:var(--white);border-radius:var(--radius);padding:20px;text-align:center;box-shadow:var(--shadow);transition:var(--transition);">
                            <div style="width:80px;height:80px;border-radius:50%;background:var(--light-bg);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:2.5rem;border:3px solid var(--secondary);">
                                <?php if ($member['photo']): ?>
                                    <img src="<?php echo SITE_URL; ?>uploads/staff/<?php echo $member['photo']; ?>" alt="<?php echo $member['full_name']; ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                                <?php else: ?>
                                    <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <h4 style="color:var(--primary);"><?php echo $member['full_name']; ?></h4>
                            <p style="color:var(--gray-text);font-size:0.85rem;"><?php echo $member['role']; ?></p>
                            <?php if ($member['email']): ?>
                                <p style="color:var(--gray-text);font-size:0.75rem;margin-top:4px;"><i class="fas fa-envelope"></i> <?php echo $member['email']; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: var(--gray-text); padding: 20px 0;">No staff added to this team yet.</p>
            <?php endif; ?>
        </div>

        <!-- Matches Section -->
        <div style="margin-top: 60px;">
            <div class="section-title">
                <h2>📅 Fixtures & Results</h2>
                <p>Recent and upcoming matches for <?php echo $category['name']; ?></p>
            </div>
            
            <?php if (count($matches) > 0): ?>
                <div class="matches-grid">
                    <?php foreach ($matches as $match): ?>
                        <div class="match-card">
                            <div class="match-header">
                                <span class="match-competition"><?php echo $match['match_type'] === 'home' ? '🏠 Home' : '✈️ Away'; ?></span>
                                <span class="match-status status-<?php echo $match['status']; ?>">
                                    <?php echo ucfirst($match['status']); ?>
                                </span>
                            </div>
                            <div class="match-teams">
                                <div class="match-team">
                                    <div class="match-team-name" style="font-weight:700;"><?php echo SITE_NAME; ?></div>
                                </div>
                                <div class="match-score">
                                    <?php if ($match['status'] === 'completed'): ?>
                                        <?php echo $match['home_score'] ?? '0'; ?> - <?php echo $match['away_score'] ?? '0'; ?>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: var(--gray-text); padding: 20px 0;">No matches scheduled for this team yet.</p>
            <?php endif; ?>
        </div>

        <!-- Achievements Section -->
        <div style="margin-top: 60px;">
            <div class="section-title">
                <h2>🏆 Achievements</h2>
                <p>Honors and milestones for <?php echo $category['name']; ?></p>
            </div>
            
            <?php if (count($achievements) > 0): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
                    <?php foreach ($achievements as $achievement): ?>
                        <div style="background:var(--white);padding:20px;border-radius:var(--radius);box-shadow:var(--shadow);border-left:4px solid var(--secondary);">
                            <div style="font-weight:700;color:var(--primary);font-size:1.1rem;"><?php echo $achievement['title']; ?></div>
                            <div style="font-size:0.85rem;color:var(--gray-text);margin-top:4px;">
                                <?php echo $achievement['year']; ?> · <?php echo $achievement['description']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: var(--gray-text); padding: 20px 0;">No achievements recorded for this team yet.</p>
            <?php endif; ?>
        </div>
        
        <!-- Back Button -->
        <div style="margin-top: 40px; text-align: center;">
            <a href="<?php echo SITE_URL; ?>index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <a href="<?php echo SITE_URL; ?>squad.php" class="btn btn-primary" style="margin-left: 10px;">
                View All Squads
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>