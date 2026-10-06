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

// === 1. Get Team Description ===
$stmt = $db->prepare("SELECT team_description FROM category_content WHERE category_id = ?");
$stmt->execute([$category['id']]);
$content = $stmt->fetch();
$teamDescription = $content['team_description'] ?? '';

// === 2. Get Team Updates (Blog posts) ===
$stmt = $db->prepare("
    SELECT * FROM category_updates 
    WHERE category_id = ? AND is_published = 1 
    ORDER BY display_order ASC, created_at DESC
");
$stmt->execute([$category['id']]);
$updates = $stmt->fetchAll();

// === 3. Get Recent Matches (completed) ===
$stmt = $db->prepare("
    SELECT * FROM matches 
    WHERE category_id = ? AND status = 'completed' 
    ORDER BY match_date DESC 
    LIMIT 5
");
$stmt->execute([$category['id']]);
$recentMatches = $stmt->fetchAll();

// === 4. Get Upcoming Fixtures ===
$stmt = $db->prepare("
    SELECT * FROM matches 
    WHERE category_id = ? AND status = 'scheduled' AND match_date >= NOW() 
    ORDER BY match_date ASC 
    LIMIT 5
");
$stmt->execute([$category['id']]);
$upcomingMatches = $stmt->fetchAll();

// === 5. Get Achievements ===
$stmt = $db->prepare("SELECT * FROM achievements WHERE category_id = ? ORDER BY year DESC");
$stmt->execute([$category['id']]);
$achievements = $stmt->fetchAll();

// === 6. Get Team Photos (filtered by category_id) ===
$stmt = $db->prepare("
    SELECT * FROM gallery 
    WHERE category_id = ? AND is_active = 1 
    ORDER BY display_order ASC, created_at DESC
");
$stmt->execute([$category['id']]);
$teamPhotos = $stmt->fetchAll();

// === 7. Get Team Roster (grouped by position) ===
$stmt = $db->prepare("
    SELECT * FROM players 
    WHERE category_id = ? AND is_active = 1 
    ORDER BY 
        CASE position
            WHEN 'GK' THEN 1
            WHEN 'RB' THEN 2
            WHEN 'CB' THEN 3
            WHEN 'LB' THEN 4
            WHEN 'CDM' THEN 5
            WHEN 'CM' THEN 6
            WHEN 'CAM' THEN 7
            WHEN 'RW' THEN 8
            WHEN 'LW' THEN 9
            WHEN 'CF' THEN 10
            WHEN 'ST' THEN 11
            ELSE 12
        END,
        jersey_number ASC
");
$stmt->execute([$category['id']]);
$allPlayers = $stmt->fetchAll();

// Group players by position
$positions = [
    'Goalkeepers' => ['GK'],
    'Defenders'   => ['RB', 'CB', 'LB'],
    'Midfielders' => ['CDM', 'CM', 'CAM'],
    'Forwards'    => ['RW', 'LW', 'CF', 'ST'],
];

$rosterByPosition = [];
foreach ($positions as $label => $codes) {
    $rosterByPosition[$label] = array_filter($allPlayers, function($p) use ($codes) {
        return in_array($p['position'], $codes);
    });
}

require_once 'includes/header.php';
?>

<!-- ═══════════════════════════════════════════════ -->
<!-- 1. HERO HEADER                                 -->
<!-- ═══════════════════════════════════════════════ -->
<section class="category-header" style="background:linear-gradient(135deg, #1a2a6c, #0d1b3e);padding:60px 0 50px;color:white;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-100px;right:-100px;width:400px;height:400px;background:rgba(251,191,36,0.06);border-radius:50%;pointer-events:none;"></div>
    
    <div class="container" style="position:relative;z-index:2;">
        <div style="display:flex;align-items:center;gap:25px;flex-wrap:wrap;">
            <div style="font-size:5rem;line-height:1;"><?php echo $category['icon'] ?? '⚽'; ?></div>
            <div>
                <h1 style="font-size:2.8rem;font-weight:900;margin-bottom:8px;">
                    <?php echo $category['name']; ?> Team
                </h1>
                <p style="font-size:1.1rem;opacity:0.85;margin-bottom:12px;">
                    <?php echo $category['description']; ?>
                </p>
                <div style="display:flex;gap:15px;flex-wrap:wrap;font-size:0.9rem;">
                    <span style="background:rgba(255,255,255,0.15);padding:4px 14px;border-radius:20px;">
                        📅 <?php echo $category['age_group']; ?>
                    </span>
                    <span style="background:rgba(255,255,255,0.15);padding:4px 14px;border-radius:20px;">
                        👥 <?php echo count($allPlayers); ?> Players
                    </span>
                    <span style="background:rgba(255,255,255,0.15);padding:4px 14px;border-radius:20px;">
                        ⚽ <?php echo count($recentMatches); ?> Matches Played
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container" style="padding:60px 20px;">

    <!-- ═══════════════════════════════════════════════ -->
    <!-- 2. TEAM DESCRIPTION                            -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if ($teamDescription): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📖</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">About This Team</h2>
        </div>
        <div style="background:var(--white);padding:30px;border-radius:var(--radius);box-shadow:var(--shadow);border-left:5px solid var(--secondary);">
            <div style="color:var(--gray-text);line-height:1.9;font-size:1.05rem;white-space:pre-wrap;"><?php echo htmlspecialchars($teamDescription); ?></div>
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════ -->
    <!-- 3. LATEST TEAM UPDATES                         -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (count($updates) > 0): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📝</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">Latest Team Updates</h2>
        </div>
        
        <div style="display:grid;gap:25px;">
            <?php foreach ($updates as $update): ?>
                <article style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);">
                    <?php if ($update['image']): ?>
                        <div style="height:280px;overflow:hidden;">
                            <img src="<?php echo SITE_URL; ?>uploads/category-updates/<?php echo $update['image']; ?>" 
                                 alt="<?php echo $update['title']; ?>"
                                 style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    <?php endif; ?>
                    <div style="padding:28px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;font-size:0.85rem;color:var(--gray-text);">
                            <span><i class="far fa-calendar-alt"></i> <?php echo formatDate($update['created_at'], 'F j, Y'); ?></span>
                            <span style="background:var(--light-bg);padding:2px 12px;border-radius:12px;color:var(--primary);font-weight:600;">
                                <?php echo $category['icon']; ?> <?php echo $category['name']; ?>
                            </span>
                        </div>
                        <h3 style="color:var(--primary);font-size:1.4rem;font-weight:700;margin-bottom:15px;">
                            <?php echo $update['title']; ?>
                        </h3>
                        <div style="color:var(--gray-text);line-height:1.9;font-size:1rem;white-space:pre-wrap;"><?php echo htmlspecialchars($update['content']); ?></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════ -->
    <!-- 4. RECENT MATCHES                              -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (count($recentMatches) > 0): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📅</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">Recent Matches</h2>
        </div>
        
        <div class="matches-grid">
            <?php foreach ($recentMatches as $match): ?>
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-competition"><?php echo $match['match_type'] === 'home' ? '🏠 Home' : '✈️ Away'; ?></span>
                        <span class="match-status status-completed">Completed</span>
                    </div>
                    <div class="match-teams">
                        <div class="match-team">
                            <div class="match-team-name" style="font-weight:700;"><?php echo SITE_NAME; ?></div>
                        </div>
                        <div class="match-score" style="font-size:2rem;font-weight:900;color:var(--primary);">
                            <?php echo $match['home_score'] ?? '0'; ?> - <?php echo $match['away_score'] ?? '0'; ?>
                        </div>
                        <div class="match-team">
                            <div class="match-team-name"><?php echo $match['opponent']; ?></div>
                        </div>
                    </div>
                    <div class="match-venue">
                        <i class="fas fa-calendar-alt"></i> <?php echo formatDate($match['match_date'], 'F j, Y'); ?>
                        <br>
                        <i class="fas fa-map-marker-alt"></i> <?php echo $match['venue'] ?? getSettings('stadium_name'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════ -->
    <!-- 5. ACHIEVEMENTS                                -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (count($achievements) > 0): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🏆</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">Achievements</h2>
        </div>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
            <?php foreach ($achievements as $ach): ?>
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);border-left:5px solid var(--secondary);">
                    <div style="font-size:2.5rem;color:var(--secondary);font-weight:800;line-height:1;"><?php echo $ach['year']; ?></div>
                    <h4 style="color:var(--primary);font-size:1.1rem;margin:10px 0 6px;"><?php echo $ach['title']; ?></h4>
                    <p style="color:var(--gray-text);font-size:0.9rem;line-height:1.6;"><?php echo $ach['description']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════ -->
    <!-- 6. UPCOMING FIXTURES                           -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (count($upcomingMatches) > 0): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📆</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">Upcoming Fixtures</h2>
        </div>
        
        <div class="matches-grid">
            <?php foreach ($upcomingMatches as $match): ?>
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-competition"><?php echo $match['match_type'] === 'home' ? '🏠 Home' : '✈️ Away'; ?></span>
                        <span class="match-status status-scheduled">Scheduled</span>
                    </div>
                    <div class="match-teams">
                        <div class="match-team">
                            <div class="match-team-name" style="font-weight:700;"><?php echo SITE_NAME; ?></div>
                        </div>
                        <div class="match-score">
                            <span class="vs">VS</span>
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
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════ -->
    <!-- 7. TEAM PHOTO GALLERY                          -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (count($teamPhotos) > 0): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📸</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">Team Photos</h2>
        </div>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
            <?php foreach ($teamPhotos as $photo): ?>
                <div style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);cursor:pointer;transition:transform 0.3s;" 
                     onclick="openLightbox('<?php echo SITE_URL; ?>uploads/gallery/<?php echo $photo['image_path']; ?>', '<?php echo addslashes($photo['title'] ?? 'Team Photo'); ?>')"
                     onmouseover="this.style.transform='translateY(-5px)'"
                     onmouseout="this.style.transform='translateY(0)'">
                    <div style="height:220px;overflow:hidden;background:#f3f4f6;">
                        <img src="<?php echo SITE_URL; ?>uploads/gallery/<?php echo $photo['image_path']; ?>" 
                             alt="<?php echo $photo['title'] ?? 'Team Photo'; ?>"
                             style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <?php if ($photo['title']): ?>
                        <div style="padding:15px;">
                            <h4 style="font-size:0.95rem;color:var(--primary);"><?php echo $photo['title']; ?></h4>
                            <span style="font-size:0.75rem;color:var(--gray-text);"><?php echo ucfirst(str_replace('-', ' ', $photo['category'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>


    <!-- ═══════════════════════════════════════════════ -->
    <!-- 8. TEAM ROSTER (Grouped by Position)           -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php if (count($allPlayers) > 0): ?>
    <section style="margin-bottom:60px;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:50px;height:50px;background:var(--secondary);color:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">👥</div>
            <h2 style="color:var(--primary);font-size:1.8rem;font-weight:800;">Team Roster</h2>
        </div>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:25px;">
            <?php foreach ($rosterByPosition as $positionLabel => $playersInGroup): ?>
                <?php if (count($playersInGroup) > 0): ?>
                    <div style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;">
                        <div style="background:var(--primary);color:white;padding:14px 20px;font-weight:700;font-size:1rem;">
                            <?php 
                            $icons = [
                                'Goalkeepers' => '🧤',
                                'Defenders'   => '🛡️',
                                'Midfielders' => '⚙️',
                                'Forwards'    => '⚡'
                            ];
                            echo ($icons[$positionLabel] ?? '⚽') . ' ' . $positionLabel;
                            ?>
                        </div>
                        <div style="padding:15px 20px;">
                            <?php foreach ($playersInGroup as $player): ?>
                                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f3f4f6;font-size:0.95rem;">
                                    <span style="background:var(--light-bg);color:var(--primary);width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;">
                                        <?php echo $player['jersey_number'] ?: '—'; ?>
                                    </span>
                                    <span style="font-weight:600;color:var(--dark-text);flex:1;"><?php echo $player['full_name']; ?></span>
                                    <?php if ($player['is_captain']): ?>
                                        <span style="font-size:0.7rem;color:var(--secondary);font-weight:700;">⭐ C</span>
                                    <?php endif; ?>
                                    <span style="font-size:0.75rem;color:var(--gray-text);"><?php echo $player['position']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- BACK BUTTON                                    -->
    <!-- ═══════════════════════════════════════════════ -->
    <div style="text-align:center;margin-top:50px;">
        <a href="<?php echo SITE_URL; ?>index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        <a href="<?php echo SITE_URL; ?>squad.php" class="btn btn-primary" style="margin-left:10px;">
            View All Teams
        </a>
    </div>
</div>


<!-- Lightbox Modal -->
<div id="lightbox" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.95);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <button onclick="closeLightbox()" style="position:absolute;top:20px;right:30px;background:none;border:none;color:white;font-size:2rem;cursor:pointer;z-index:10;">
        <i class="fas fa-times"></i>
    </button>
    <img id="lightboxImage" src="" style="max-width:95%;max-height:85vh;border-radius:8px;object-fit:contain;">
    <div id="lightboxCaption" style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);color:white;font-size:1.05rem;background:rgba(0,0,0,0.6);padding:10px 24px;border-radius:8px;"></div>
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
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
});
document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});
</script>

<?php require_once 'includes/footer.php'; ?>