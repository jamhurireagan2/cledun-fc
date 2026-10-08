<?php
require_once 'includes/functions.php';

$currentPage = 'home';
$pageTitle = 'Home - ' . SITE_NAME;

$db = getDB();

// Get all categories
$categories = getActiveCategories();

// Get latest news
$latestNews = $db->query("SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();

// Get upcoming matches (all categories)
$upcomingMatches = $db->query("
    SELECT m.*, c.name as category_name, c.icon as category_icon 
    FROM matches m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE m.match_date >= NOW() AND m.status != 'cancelled' 
    ORDER BY m.match_date ASC 
    LIMIT 3
")->fetchAll();

// Get categories with player counts for each
$categoryCounts = [];
foreach ($categories as $cat) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM players WHERE category_id = ? AND is_active = 1");
    $stmt->execute([$cat['id']]);
    $categoryCounts[$cat['id']] = $stmt->fetch()['count'];
}

require_once 'includes/header.php';
?>

<!-- ═══════════════════════════════════════════════ -->
<!-- PART 1: BANNER IMAGE (Just the picture, no text) -->
<!-- ═══════════════════════════════════════════════ -->
<?php 
$heroBanner = getSettings('hero_banner');
$bannerExists = $heroBanner && file_exists(__DIR__ . '/uploads/banner/' . $heroBanner);
?>

<?php if ($bannerExists): ?>
<!-- Banner Block - Full Width, Sharp Corners -->
<section style="width:100%;margin:0;padding:0;line-height:0;font-size:0;">
    <img src="<?php echo SITE_URL; ?>uploads/banner/<?php echo $heroBanner; ?>" 
         alt="CLEDUN FC Banner"
         style="width:100%;height:auto;max-height:600px;object-fit:cover;display:block;margin:0;padding:0;vertical-align:bottom;">
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════ -->
<!-- PART 2: WELCOME SECTION (Text below the banner) -->
<!-- ═══════════════════════════════════════════════ -->
<section style="background:linear-gradient(135deg, #1a2a6c 0%, #0d1b3e 100%);color:white;padding:80px 0;position:relative;overflow:hidden;margin-top:30px;">    

    <!-- Decorative circles -->
    <div style="position:absolute;top:-100px;right:-100px;width:400px;height:400px;background:rgba(251,191,36,0.06);border-radius:50%;pointer-events:none;"></div>
    <div style="position:absolute;bottom:-150px;left:-100px;width:500px;height:500px;background:rgba(251,191,36,0.04);border-radius:50%;pointer-events:none;"></div>
    
    <div class="container" style="position:relative;z-index:2;">
        <div class="hero-content" style="display:grid;grid-template-columns:1.5fr 1fr;gap:50px;align-items:center;">
            
            <div class="hero-text">
                <div style="display:inline-block;background:rgba(251,191,36,0.15);border:1px solid rgba(251,191,36,0.3);color:#fbbf24;padding:6px 16px;border-radius:30px;font-size:0.85rem;font-weight:600;margin-bottom:20px;">
                    ⚽ Building Champions Since <?php echo getSettings('club_established') ?: '2026'; ?>
                </div>
                
                <h1 style="font-size:3rem;font-weight:900;line-height:1.1;margin-bottom:20px;">
                    Welcome to <span style="color:#fbbf24;">CLEDUN FC</span>
                </h1>
                
                <p style="font-size:1.15rem;opacity:0.95;margin-bottom:30px;max-width:550px;line-height:1.7;">
                    Founded in <?php echo getSettings('club_established') ?: '2026'; ?>, we nurture young talent and build champions. 
                    Join us on our journey to compete globally!
                </p>
                
                <div style="display:flex;gap:15px;flex-wrap:wrap;">
                    <a href="<?php echo SITE_URL; ?>tickets.php" class="btn btn-primary" style="font-size:1rem;padding:14px 28px;">
                        🎟️ Get Tickets
                    </a>
        
                    <a href="<?php echo SITE_URL; ?>register-player.php" class="btn btn-primary" style="background:#10b981;color:#fff;font-size:1rem;padding:14px 28px;">
                        📝 Register Player
                    </a>
                </div>
                
                <!-- Quick Stats -->
                <div style="display:flex;gap:35px;margin-top:40px;padding-top:30px;border-top:1px solid rgba(255,255,255,0.15);">
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#fbbf24;">
                            <?php echo count($categories); ?>
                        </div>
                        <div style="font-size:0.85rem;opacity:0.8;">Teams</div>
                    </div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#fbbf24;">
                            <?php 
                            $totalPlayers = $db->query("SELECT COUNT(*) as c FROM players WHERE is_active = 1")->fetch()['c'];
                            echo $totalPlayers;
                            ?>
                        </div>
                        <div style="font-size:0.85rem;opacity:0.8;">Players</div>
                    </div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#fbbf24;">
                            <?php echo getSettings('club_established') ?: '2026'; ?>
                        </div>
                        <div style="font-size:0.85rem;opacity:0.8;">Established</div>
                    </div>
                </div>
            </div>
            
            <!-- Badge -->
            <div class="hero-badge" style="text-align:center;">
                <img src="<?php echo SITE_URL; ?>assets/images/badge.png" 
                     alt="CLEDUN FC Badge" 
                     style="max-width:320px;width:100%;filter:drop-shadow(0 20px 40px rgba(0,0,0,0.4));"
                     onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>

<style>
    @media (max-width: 992px) {
        section[style*="padding:70px 0"] .hero-content {
            grid-template-columns: 1fr !important;
            text-align: center;
        }
        section[style*="padding:70px 0"] .hero-content > div:first-child {
            order: 2;
        }
        section[style*="padding:70px 0"] .hero-badge {
            order: 1;
        }
        section[style*="padding:70px 0"] h1 {
            font-size: 2.2rem !important;
        }
        section[style*="padding:70px 0"] .hero-badge img {
            max-width: 200px !important;
        }
        section[style*="padding:70px 0"] .hero-text > div:last-child {
            justify-content: center;
        }
    }
</style>
<!-- Slideshow / Gallery Section -->
<section style="padding: 60px 0; background: var(--light-bg);">
    <div class="container">
        <div class="section-title">
            <h2>📸 CLEDUN FC Gallery</h2>
            <p>Moments captured from our matches, training, and events</p>
        </div>
        
        <?php
        // Get active gallery images
        $galleryImages = $db->query("SELECT * FROM gallery WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 10")->fetchAll();
        ?>
        
        <?php if (count($galleryImages) > 0): ?>
            <div class="slideshow-container" style="position:relative;max-width:100%;margin:0 auto;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-hover);">
                <?php foreach ($galleryImages as $index => $image): ?>
                    <div class="slide" style="display:<?php echo $index === 0 ? 'block' : 'none'; ?>;position:relative;">
                        <img src="<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>" 
                             alt="<?php echo $image['title'] ?? 'Gallery Image'; ?>"
                             style="width:100%;height:500px;object-fit:cover;">
                        <?php if ($image['title']): ?>
                            <div style="position:absolute;bottom:0;left:0;right:0;padding:20px;background:linear-gradient(transparent,rgba(0,0,0,0.7));color:white;">
                                <h3 style="font-size:1.3rem;"><?php echo $image['title']; ?></h3>
                                <p style="opacity:0.8;font-size:0.9rem;"><?php echo ucfirst($image['category']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <!-- Navigation Arrows -->
                <button class="slide-prev" style="position:absolute;top:50%;left:15px;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;padding:15px 20px;border-radius:50%;cursor:pointer;font-size:1.2rem;transition:all 0.3s;z-index:10;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slide-next" style="position:absolute;top:50%;right:15px;transform:translateY(-50%);background:rgba(0,0,0,0.5);color:white;border:none;padding:15px 20px;border-radius:50%;cursor:pointer;font-size:1.2rem;transition:all 0.3s;z-index:10;">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Dots -->
                <div style="position:absolute;bottom:70px;left:50%;transform:translateX(-50%);display:flex;gap:10px;z-index:10;">
                    <?php foreach ($galleryImages as $index => $image): ?>
                        <span class="slide-dot" data-index="<?php echo $index; ?>" style="width:12px;height:12px;border-radius:50%;background:<?php echo $index === 0 ? '#fbbf24' : 'rgba(255,255,255,0.5)'; ?>;cursor:pointer;transition:all 0.3s;"></span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <style>
                .slide-prev:hover, .slide-next:hover {
                    background: rgba(251, 191, 36, 0.8) !important;
                    color: #1a2a6c !important;
                }
                .slide-dot:hover {
                    transform: scale(1.2);
                }
            </style>
            
            <script>
                // Slideshow functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const slides = document.querySelectorAll('.slide');
                    const dots = document.querySelectorAll('.slide-dot');
                    const prevBtn = document.querySelector('.slide-prev');
                    const nextBtn = document.querySelector('.slide-next');
                    let currentSlide = 0;
                    let slideInterval;
                    
                    function showSlide(index) {
                        slides.forEach((slide, i) => {
                            slide.style.display = i === index ? 'block' : 'none';
                        });
                        dots.forEach((dot, i) => {
                            dot.style.background = i === index ? '#fbbf24' : 'rgba(255,255,255,0.5)';
                        });
                        currentSlide = index;
                    }
                    
                    function nextSlide() {
                        const next = (currentSlide + 1) % slides.length;
                        showSlide(next);
                    }
                    
                    function prevSlide() {
                        const prev = (currentSlide - 1 + slides.length) % slides.length;
                        showSlide(prev);
                    }
                    
                    // Start auto-slideshow
                    function startSlideshow() {
                        if (slideInterval) clearInterval(slideInterval);
                        slideInterval = setInterval(nextSlide, 4000);
                    }
                    
                    // Event listeners
                    if (nextBtn) nextBtn.addEventListener('click', function() {
                        nextSlide();
                        startSlideshow();
                    });
                    
                    if (prevBtn) prevBtn.addEventListener('click', function() {
                        prevSlide();
                        startSlideshow();
                    });
                    
                    dots.forEach((dot, index) => {
                        dot.addEventListener('click', function() {
                            showSlide(index);
                            startSlideshow();
                        });
                    });
                    
                    // Pause on hover
                    const container = document.querySelector('.slideshow-container');
                    if (container) {
                        container.addEventListener('mouseenter', function() {
                            if (slideInterval) clearInterval(slideInterval);
                        });
                        container.addEventListener('mouseleave', function() {
                            startSlideshow();
                        });
                    }
                    
                    // Start the slideshow
                    startSlideshow();
                });
            </script>
        <?php else: ?>
            <div style="text-align:center;padding:40px;background:var(--white);border-radius:var(--radius);">
                <p style="color:var(--gray-text);">No images in the gallery yet. Check back soon!</p>
            </div>
        <?php endif; ?>
        
        <!-- View All Gallery Link -->
        <?php if (count($galleryImages) > 0): ?>
            <div style="text-align:center;margin-top:20px;">
                <a href="<?php echo SITE_URL; ?>gallery.php" class="btn btn-primary">
                    <i class="fas fa-images"></i> View Full Gallery
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Categories Section -->
<section style="padding: 60px 0; background: var(--white);">
    <div class="container">
        <div class="section-title">
            <h2>Our Teams</h2>
            <p>Developing talent at every age group, from grassroots to senior level</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:24px;">
            <?php 
            $icons = ['🌟', '⭐', '🔥', '💪', '🏆'];
            $i = 0;
            foreach ($categories as $cat): 
                $icon = $cat['icon'] ?? ($icons[$i % count($icons)] ?? '⚽');
                $i++;
                $playerCount = $categoryCounts[$cat['id']] ?? 0;
            ?>
                <a href="<?php echo SITE_URL; ?>category.php?slug=<?php echo $cat['slug']; ?>" 
                   style="background:var(--light-bg);border-radius:var(--radius);padding:30px 20px;text-align:center;transition:var(--transition);border-bottom:4px solid var(--secondary);text-decoration:none;display:block;">
                    <div style="font-size:2.5rem;margin-bottom:12px;"><?php echo $icon; ?></div>
                    <h3 style="font-size:1.3rem;font-weight:700;color:var(--primary);"><?php echo $cat['name']; ?></h3>
                    <p style="color:var(--gray-text);font-size:0.85rem;margin-top:4px;"><?php echo $cat['age_group']; ?></p>
                    <p style="color:var(--gray-text);font-size:0.75rem;margin-top:8px;">👥 <?php echo $playerCount; ?> players</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Latest News -->
<section style="padding: 60px 0;">
    <div class="container">
        <div class="section-title">
            <h2>Latest News</h2>
            <p>Stay updated with the latest from CLEDUN FC</p>
        </div>
        <div class="news-grid">
            <?php if (count($latestNews) > 0): ?>
                <?php foreach ($latestNews as $news): ?>
                    <article class="news-card">
    <?php if ($news['featured_image']): ?>
        <div style="height:180px;overflow:hidden;">
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
        <a href="<?php echo SITE_URL; ?>news-detail.php?id=<?php echo $news['id']; ?>" class="btn btn-primary" style="font-size:0.8rem;padding:8px 20px;margin-top:12px;">
            Read More
        </a>
    </div>
</article>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;color:var(--gray-text);grid-column:1/-1;padding:40px 0;">No news available yet. Check back soon!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Upcoming Matches -->
<section style="padding: 60px 0; background: var(--white);">
    <div class="container">
        <div class="section-title">
            <h2>Upcoming Fixtures</h2>
            <p>Don't miss the next CLEDUN FC match</p>
        </div>
        <div class="matches-grid">
            <?php if (count($upcomingMatches) > 0): ?>
                <?php foreach ($upcomingMatches as $match): ?>
                    <div class="match-card">
                        <div class="match-header">
                            <span class="match-competition">
                                <?php echo $match['category_icon'] ?? '⚽'; ?> <?php echo $match['category_name'] ?? 'Match'; ?>
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
                            <?php if ($match['match_type'] === 'home'): ?>
                                <span style="background:var(--secondary);color:var(--primary);padding:2px 10px;border-radius:12px;font-size:0.75rem;font-weight:600;display:inline-block;margin-top:4px;">🏠 Home</span>
                            <?php else: ?>
                                <span style="background:#e5e7eb;color:#4b5563;padding:2px 10px;border-radius:12px;font-size:0.75rem;display:inline-block;margin-top:4px;">✈️ Away</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;color:var(--gray-text);grid-column:1/-1;padding:40px 0;">No upcoming matches scheduled.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section style="padding: 60px 0;">
    <div class="container">
        <div class="section-title">
            <h2>About CLEDUN FC</h2>
            <p>Our vision and mission drive everything we do</p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;">
            <div style="background:var(--white);padding:35px;border-radius:var(--radius);border-left:5px solid var(--secondary);box-shadow:var(--shadow);">
                <h3 style="color:var(--primary);font-size:1.6rem;margin-bottom:12px;">👁️ Our Vision</h3>
                <p style="color:var(--gray-text);line-height:1.8;">
                    To participate in world youth football tournaments globally, both as a team and through individual player representation. 
                    We aim to put CLEDUN FC on the international stage.
                </p>
            </div>
            <div style="background:var(--white);padding:35px;border-radius:var(--radius);border-left:5px solid #3b82f6;box-shadow:var(--shadow);">
                <h3 style="color:var(--primary);font-size:1.6rem;margin-bottom:12px;">🎯 Our Mission</h3>
                <p style="color:var(--gray-text);line-height:1.8;">
                    To nurture and foster young talent while building self-confidence, discipline, and character in young athletes. 
                    We develop complete players for life.
                </p>
            </div>
        </div>
        
        <!-- Roadmap -->
        <div style="margin-top:40px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px;">
            <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-left:4px solid var(--secondary);">
                <div style="font-size:2.5rem;font-weight:800;color:var(--secondary);">2028</div>
                <h4 style="margin:10px 0 4px;">Norway Cup</h4>
                <p style="font-size:0.85rem;opacity:0.7;">U10 & U12 Teams</p>
            </div>
            <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-left:4px solid var(--secondary);">
                <div style="font-size:2.5rem;font-weight:800;color:var(--secondary);">2029</div>
                <h4 style="margin:10px 0 4px;">Gothia Cup</h4>
                <p style="font-size:0.85rem;opacity:0.7;">U15 Team</p>
            </div>
            <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-left:4px solid var(--secondary);">
                <div style="font-size:2.5rem;font-weight:800;color:var(--secondary);">2030</div>
                <h4 style="margin:10px 0 4px;">Own Facility</h4>
                <p style="font-size:0.85rem;opacity:0.7;">Players in National Teams</p>
            </div>
        </div>
    </div>
</section>

<!-- Location Map Section -->
<section style="padding: 60px 0; background: var(--light-bg);">
    <div class="container">
        <div class="section-title">
            <h2>📍 Find Us Here</h2>
            <p>Visit us at <?php echo getSettings('stadium_location') ?: 'Farasi Lane Primary School'; ?></p>
        </div>
        
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:30px;align-items:center;">
            
            <!-- Info Card -->
            <div style="background:var(--white);padding:35px;border-radius:var(--radius);box-shadow:var(--shadow);border-left:5px solid var(--secondary);">
                <div style="font-size:3rem;margin-bottom:15px;">🏟️</div>
                <h3 style="color:var(--primary);font-size:1.4rem;margin-bottom:10px;">
                    <?php echo getSettings('stadium_name') ?: 'Farasi Lane'; ?>
                </h3>
                <p style="color:var(--gray-text);font-size:1rem;line-height:1.8;margin-bottom:20px;">
                    <?php echo getSettings('stadium_location') ?: 'Farasi Lane Primary School'; ?>
                </p>
                
                <div style="border-top:1px solid #e5e7eb;padding-top:20px;">
                    <p style="margin-bottom:10px;"><strong>📧 Email:</strong><br>
                        <a href="mailto:<?php echo getSettings('contact_email') ?: 'cledunsports@gmail.com'; ?>" style="color:var(--primary);">
                            <?php echo getSettings('contact_email') ?: 'cledunsports@gmail.com'; ?>
                        </a>
                    </p>
                    <p style="margin-bottom:10px;"><strong>📱 Phone:</strong><br>
                        <?php echo getSettings('contact_phone') ?: '0710339213'; ?>
                    </p>
                </div>
                
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode((getSettings('stadium_name') ?: 'Farasi Lane') . ' ' . (getSettings('stadium_location') ?: 'Primary School') . ' Nairobi'); ?>" 
                   target="_blank" 
                   class="btn btn-primary" 
                   style="margin-top:20px;width:100%;text-align:center;">
                    🧭 Get Directions
                </a>
            </div>
            
            <!-- Map Embed (with place name for working marker) -->
            <div style="background:var(--white);padding:15px;border-radius:var(--radius);box-shadow:var(--shadow);">
                <?php 
                $mapQuery = urlencode((getSettings('stadium_name') ?: 'Farasi Lane') . ' ' . (getSettings('stadium_location') ?: 'Primary School') . ' Nairobi Kenya');
                ?>
                <iframe 
                    width="100%" 
                    height="450" 
                    style="border:0;border-radius:8px;"
                    src="https://www.google.com/maps?q=<?php echo $mapQuery; ?>&z=<?php echo getSettings('map_zoom') ?? '16'; ?>&output=embed"
                    allowfullscreen
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- Video Highlights Section -->
<section style="padding: 60px 0; background: var(--white);">
    <div class="container">
        <div class="section-title">
            <h2>🎬 Video Highlights</h2>
            <p>Watch the best moments from CLEDUN FC</p>
        </div>
        
        <?php
        $videos = $db->query("SELECT * FROM videos WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 3")->fetchAll();
        ?>
        
        <?php if (count($videos) > 0): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:25px;">
                <?php foreach ($videos as $video): 
                    $videoSrc = $video['video_file'] 
                        ? SITE_URL . 'uploads/videos/' . $video['video_file'] 
                        : $video['video_url'];
                    $videoType = $video['video_file'] ? 'uploaded' : 'youtube';
                ?>
                    <div style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:var(--transition);cursor:pointer;" 
                         data-src="<?php echo htmlspecialchars($videoSrc, ENT_QUOTES); ?>"
                         data-title="<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>"
                         data-desc="<?php echo htmlspecialchars($video['description'] ?? '', ENT_QUOTES); ?>"
                         data-type="<?php echo $videoType; ?>"
                         onclick="openVideoModalFromCard(this)">
                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#0d1b3e;">
                            <?php if ($video['thumbnail']): ?>
                                <img src="<?php echo SITE_URL; ?>uploads/videos/<?php echo $video['thumbnail']; ?>" 
                                     alt="<?php echo $video['title']; ?>"
                                     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:white;font-size:3rem;background:linear-gradient(135deg,#1a2a6c,#2a3f8a);">
                                    <i class="fas fa-video"></i>
                                </div>
                            <?php endif; ?>
                            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:2;width:60px;height:60px;background:rgba(251,191,36,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#1a2a6c;">
                                <i class="fas fa-play" style="margin-left:4px;"></i>
                            </div>
                            <?php if ($video['video_file']): ?>
                                <div style="position:absolute;bottom:10px;right:10px;background:rgba(0,0,0,0.7);color:white;padding:2px 10px;border-radius:12px;font-size:0.6rem;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="padding:15px;">
                            <h4 style="font-size:1rem;font-weight:700;color:var(--primary);"><?php echo $video['title']; ?></h4>
                            <span style="font-size:0.75rem;color:var(--gray-text);"><?php echo ucfirst(str_replace('-', ' ', $video['category'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align:center;margin-top:30px;">
                <a href="videos.php" class="btn btn-primary"><i class="fas fa-play"></i> View All Videos</a>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:40px;background:var(--light-bg);border-radius:var(--radius);">
                <p style="color:var(--gray-text);">No videos available yet. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Video Modal Script -->
<script>
function openVideoModalFromCard(el) {
    openVideoModal(
        el.dataset.src,
        el.dataset.title,
        el.dataset.desc,
        el.dataset.type
    );
}

function openVideoModal(url, title, description, type) {
    let modal = document.getElementById('videoModal');
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'videoModal';
        modal.style.cssText = 'display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;padding:20px;';
        modal.innerHTML = `
            <button onclick="closeVideoModal()" style="position:absolute;top:20px;right:30px;background:none;border:none;color:white;font-size:2rem;cursor:pointer;z-index:10;">
                <i class="fas fa-times"></i>
            </button>
            <div style="width:100%;max-width:900px;background:#0d1b3e;border-radius:12px;overflow:hidden;">
                <div style="position:relative;padding-bottom:56.25%;height:0;">
                    <iframe id="videoPlayer" src="" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;display:none;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <video id="uploadedVideoPlayer" controls style="position:absolute;top:0;left:0;width:100%;height:100%;display:none;background:#000;">
                        <source id="videoSource" src="" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div style="padding:20px;background:#1a2a6c;color:white;">
                    <h3 id="videoModalTitle" style="font-size:1.2rem;margin-bottom:4px;">Video Title</h3>
                    <p id="videoModalDescription" style="opacity:0.8;font-size:0.95rem;">Video description</p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeVideoModal();
            }
        });
    }
    
    const iframe = document.getElementById('videoPlayer');
    const video = document.getElementById('uploadedVideoPlayer');
    const source = document.getElementById('videoSource');
    
    iframe.style.display = 'none';
    iframe.src = '';
    video.style.display = 'none';
    video.pause();
    
    if (type === 'uploaded') {
        source.src = url;
        video.load();
        video.style.display = 'block';
        video.play();
    } else {
        iframe.src = url;
        iframe.style.display = 'block';
    }
    
    document.getElementById('videoModalTitle').textContent = title || 'Video Highlight';
    document.getElementById('videoModalDescription').textContent = description || 'CLEDUN FC Video';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    if (modal) {
        const iframe = document.getElementById('videoPlayer');
        const video = document.getElementById('uploadedVideoPlayer');
        if (iframe) {
            iframe.src = '';
            iframe.style.display = 'none';
        }
        if (video) {
            video.pause();
            video.style.display = 'none';
        }
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>