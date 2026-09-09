<?php
require_once 'includes/functions.php';

$currentPage = 'matches';
$pageTitle = 'Live Matches - ' . SITE_NAME;

$db = getDB();

// Get live matches
$liveMatches = $db->query("
    SELECT m.*, c.name as category_name, c.icon as category_icon 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.status = 'live' 
    ORDER BY m.match_date DESC
")->fetchAll();

// Get upcoming matches
$upcomingMatches = $db->query("
    SELECT m.*, c.name as category_name, c.icon as category_icon 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.status = 'scheduled' AND m.match_date >= NOW()
    ORDER BY m.match_date ASC 
    LIMIT 10
")->fetchAll();

// Get recent results
$recentResults = $db->query("
    SELECT m.*, c.name as category_name, c.icon as category_icon 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.status = 'completed' 
    ORDER BY m.match_date DESC 
    LIMIT 10
")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>⚡ Live Matches</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Real-time updates and scores</p>
            </div>
        </div>
    </div>
</section>

<section style="padding: 50px 0;">
    <div class="container">
        
        <!-- Live Matches -->
        <?php if (count($liveMatches) > 0): ?>
            <div style="background:linear-gradient(135deg,#1a2a6c,#2a3f8a);padding:30px;border-radius:var(--radius);margin-bottom:40px;border:2px solid #ef4444;position:relative;overflow:hidden;">
                <div style="position:absolute;top:10px;right:20px;background:#ef4444;color:white;padding:4px 16px;border-radius:20px;font-size:0.8rem;font-weight:700;animation:pulse-live 1.5s infinite;">
                    🔴 LIVE
                </div>
                <h2 style="color:white;margin-bottom:20px;">🔥 Live Now</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
                    <?php foreach ($liveMatches as $match): ?>
                        <div style="background:rgba(255,255,255,0.1);border-radius:var(--radius);padding:20px;backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.1);">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <span style="color:rgba(255,255,255,0.7);font-size:0.85rem;"><?php echo $match['category_icon'] ?? '⚽'; ?> <?php echo $match['category_name'] ?? 'Match'; ?></span>
                                <span style="background:#ef4444;color:white;padding:2px 10px;border-radius:12px;font-size:0.7rem;font-weight:700;">LIVE</span>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:center;text-align:center;color:white;">
                                <div>
                                    <div style="font-weight:700;font-size:1.1rem;"><?php echo SITE_NAME; ?></div>
                                </div>
                                <div style="font-size:2.5rem;font-weight:900;color:#fbbf24;background:rgba(255,255,255,0.1);padding:0 20px;border-radius:12px;">
                                    <?php echo $match['home_score'] ?? '0'; ?> - <?php echo $match['away_score'] ?? '0'; ?>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:1.1rem;"><?php echo $match['opponent']; ?></div>
                                </div>
                            </div>
                            <div style="text-align:center;margin-top:12px;color:rgba(255,255,255,0.7);font-size:0.85rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $match['venue'] ?? getSettings('stadium_name'); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <style>
                @keyframes pulse-live {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.4; }
                }
            </style>
        <?php else: ?>
            <div style="background:#f3f4f6;padding:30px;border-radius:var(--radius);text-align:center;margin-bottom:40px;">
                <div style="font-size:3rem;margin-bottom:10px;">⏳</div>
                <h3 style="color:var(--gray-text);">No live matches at the moment</h3>
                <p style="color:var(--gray-text);">Check back during match days for live updates!</p>
            </div>
        <?php endif; ?>
        
        <!-- Upcoming Matches -->
        <div style="margin-bottom:40px;">
            <h2 style="color:var(--primary);margin-bottom:20px;">📅 Upcoming Fixtures</h2>
            <?php if (count($upcomingMatches) > 0): ?>
                <div class="matches-grid">
                    <?php foreach ($upcomingMatches as $match): ?>
                        <div class="match-card">
                            <div class="match-header">
                                <span class="match-competition"><?php echo $match['category_icon'] ?? '⚽'; ?> <?php echo $match['category_name'] ?? 'Match'; ?></span>
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
            <?php else: ?>
                <p style="color:var(--gray-text);">No upcoming matches scheduled.</p>
            <?php endif; ?>
        </div>
        
        <!-- Recent Results -->
        <div>
            <h2 style="color:var(--primary);margin-bottom:20px;">📊 Recent Results</h2>
            <?php if (count($recentResults) > 0): ?>
                <div class="matches-grid">
                    <?php foreach ($recentResults as $match): ?>
                        <div class="match-card">
                            <div class="match-header">
                                <span class="match-competition"><?php echo $match['category_icon'] ?? '⚽'; ?> <?php echo $match['category_name'] ?? 'Match'; ?></span>
                                <span class="match-status status-completed">Completed</span>
                            </div>
                            <div class="match-teams">
                                <div class="match-team">
                                    <div class="match-team-name" style="font-weight:700;"><?php echo SITE_NAME; ?></div>
                                </div>
                                <div class="match-score">
                                    <span style="font-size:1.8rem;font-weight:900;color:var(--primary);">
                                        <?php echo $match['home_score'] ?? '0'; ?> - <?php echo $match['away_score'] ?? '0'; ?>
                                    </span>
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
            <?php else: ?>
                <p style="color:var(--gray-text);">No recent results available.</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align:center;margin-top:30px;">
            <a href="matches.php" class="btn btn-primary"><i class="fas fa-list"></i> View All Matches</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
