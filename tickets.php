<?php
require_once 'includes/functions.php';

$currentPage = 'tickets';
$pageTitle = 'Tickets - ' . SITE_NAME;

$db = getDB();

// Get upcoming matches with tickets available
$matches = $db->query("
    SELECT m.*, c.name as category_name, c.icon as category_icon,
           (SELECT COUNT(*) FROM tickets WHERE match_id = m.id AND available_quantity > 0) as has_tickets
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.match_date >= NOW() AND m.status != 'cancelled' 
    ORDER BY m.match_date ASC 
    LIMIT 10
")->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>🎟️ Tickets</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Get your tickets for upcoming CLEDUN FC matches</p>
            </div>
        </div>
    </div>
</section>

<!-- Tickets Content -->
<section style="padding: 50px 0;">
    <div class="container">
        
        <?php if (count($matches) > 0): ?>
            <div class="matches-grid">
                <?php foreach ($matches as $match): ?>
                    <div class="match-card" style="position:relative;">
                        <?php if ($match['has_tickets'] > 0): ?>
                            <div style="position:absolute;top:-8px;right:-8px;background:#10b981;color:white;padding:4px 12px;border-radius:20px;font-size:0.7rem;font-weight:700;">
                                ✅ Tickets Available
                            </div>
                        <?php else: ?>
                            <div style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:white;padding:4px 12px;border-radius:20px;font-size:0.7rem;font-weight:700;">
                                Sold Out
                            </div>
                        <?php endif; ?>
                        
                        <div class="match-header">
                            <span class="match-competition">
                                <?php echo $match['category_icon'] ?? '⚽'; ?> 
                                <?php echo $match['category_name'] ?? 'Match'; ?>
                            </span>
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
                            <br>
                            <i class="fas fa-tag"></i> <?php echo $match['match_type'] === 'home' ? '🏠 Home Match' : '✈️ Away Match'; ?>
                        </div>
                        
                        <?php if ($match['has_tickets'] > 0): ?>
                            <a href="<?php echo SITE_URL; ?>booking.php?match=<?php echo $match['id']; ?>" 
                               class="btn btn-primary" style="width:100%;margin-top:15px;text-align:center;">
                                🎫 Book Tickets
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline" style="width:100%;margin-top:15px;text-align:center;cursor:not-allowed;opacity:0.5;" disabled>
                                Sold Out
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;">
                <div style="font-size:4rem;margin-bottom:20px;">🎟️</div>
                <h3 style="color:var(--gray-text);">No tickets available at the moment</h3>
                <p style="color:var(--gray-text);">Check back later for upcoming matches.</p>
                <a href="<?php echo SITE_URL; ?>matches.php" class="btn btn-primary" style="margin-top:20px;">
                    View Fixtures
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>