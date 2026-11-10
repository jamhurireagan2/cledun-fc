<?php
require_once 'includes/functions.php';

$currentPage = 'home';
$pageTitle = 'Home - ' . SITE_NAME;

$db = getDB();

// Get all categories
$stmt = $db->query("SELECT * FROM categories WHERE is_active = 1");
$categories = $stmt->fetchAll();

// Custom sort to extract the number from the name (e.g., '15' becomes 15) and sort numerically
usort($categories, function($a, $b) {
    // 1. Force Senior to always be at the bottom
    $isSeniorA = (stripos($a['name'], 'senior') !== false);
    $isSeniorB = (stripos($b['name'], 'senior') !== false);
    
    if ($isSeniorA && !$isSeniorB) return 1;
    if (!$isSeniorA && $isSeniorB) return -1;
    if ($isSeniorA && $isSeniorB) return 0;
    
    // 2. Extract the first number found in the name (e.g., "U15" or "15" becomes 15)
    preg_match('/\d+/', $a['name'], $matchesA);
    preg_match('/\d+/', $b['name'], $matchesB);
    
    $numA = isset($matchesA[0]) ? intval($matchesA[0]) : 999;
    $numB = isset($matchesB[0]) ? intval($matchesB[0]) : 999;
    
    // 3. Sort by the extracted number
    if ($numA == $numB) {
        return strcmp($a['name'], $b['name']); // If numbers are same, sort alphabetically
    }
    
    return ($numA < $numB) ? -1 : 1;
});

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
<!-- HERO BANNER SLIDESHOW WITH FADING TEXT         -->
<!-- ═══════════════════════════════════════════════ -->
<?php 
// 1. Get the fallback image (the one uploaded in settings)
$heroBanner = getSettings('hero_banner');
$fallbackUrl = $heroBanner && file_exists(__DIR__ . '/uploads/banner/' . $heroBanner) ? SITE_URL . 'uploads/banner/' . $heroBanner : SITE_URL . 'assets/images/badge.png';

// 2. Get images from Gallery for the slideshow
$stmt = $db->prepare("SELECT image_path FROM gallery WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 5");
$stmt->execute();
$slideshowImages = $stmt->fetchAll();
?>

<section class="hero-slideshow" style="position: relative; min-height: 600px; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 20px; overflow: hidden;">
    
    <!-- Slideshow Background Images -->
    <div class="slide-container" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;">
        <?php if (count($slideshowImages) > 0): ?>
            <?php foreach ($slideshowImages as $index => $img): ?>
                <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('<?php echo SITE_URL; ?>uploads/gallery/<?php echo $img['image_path']; ?>') center/cover no-repeat; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; transition: opacity 1.5s ease-in-out;"></div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback if gallery is empty -->
            <div class="hero-slide active" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('<?php echo $fallbackUrl; ?>') center/cover no-repeat; opacity: 1;"></div>
        <?php endif; ?>
        
        <!-- Dark Overlay so text is readable -->
        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(13,27,62,0.75); z-index: 2;"></div>
    </div>

    <!-- Content -->
    <div class="container" style="position: relative; z-index: 3; max-width: 900px;">
        
        <!-- Small Tag -->
        <div style="display: inline-block; background: rgba(251, 191, 36, 0.15); border: 1px solid rgba(251, 191, 36, 0.4); color: #fbbf24; padding: 8px 20px; border-radius: 30px; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 25px;">
            Youth Football Academy 📍 Kabete
        </div>

                <!-- Main Title -->
        <h1 style="font-size: 4rem; font-weight: 900; color: white; line-height: 1.1; margin-bottom: 20px; text-shadow: 0 4px 20px rgba(0,0,0,0.5);">
            CLEDUN <span class="hovering-text" style="color: #fbbf24; display: inline-block;">FOOTBALL</span> ACADEMY
        </h1>

        <!-- Fading Text Carousel -->
        <div style="margin-bottom: 40px; min-height: 80px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            
                       <div style="position: relative; height: 40px; width: 100%; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                <span id="typewriter-text" style="font-size: 1.3rem; color: #e5e7eb; font-weight: 500; text-shadow: 0 2px 10px rgba(0,0,0,0.5); margin: 0;"></span>
                <span class="typewriter-cursor" style="display: inline-block; width: 3px; height: 1.3rem; background-color: #fbbf24; margin-left: 5px; animation: blink 1s step-end infinite;"></span>
            </div>

                        <!-- Dotted Indicators (Generated by JS) -->
            <div id="typewriter-dots" style="display: flex; gap: 8px; justify-content: center; z-index: 10; position: relative; height: 4px;">
                <!-- The dots will be injected here by JavaScript -->
            </div>
            
        </div>

        <!-- Buttons -->
        <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
            <a href="<?php echo SITE_URL; ?>register-player.php" class="btn" style="background: #fbbf24; color: #1a2a6c; padding: 15px 35px; border-radius: 8px; font-weight: 800; font-size: 1rem; text-decoration: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(251, 191, 36, 0.4);" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                Register Player
            </a>
            
            <a href="<?php echo SITE_URL; ?>news.php" class="btn" style="background: rgba(255,255,255,0.1); border: 2px solid white; color: white; padding: 15px 35px; border-radius: 8px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='white'; this.style.color='#1a2a6c';" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.color='white';">
                Latest News
            </a>
            
            <a href="<?php echo SITE_URL; ?>contact.php" class="btn" style="background: rgba(255,255,255,0.1); border: 2px solid white; color: white; padding: 15px 35px; border-radius: 8px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='white'; this.style.color='#1a2a6c';" onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.color='white';">
                Contact Us
            </a>
        </div>

    </div>
</section>

<!-- Slideshow & Typewriter Script/CSS -->
<style>
             /* Shimmering Light Animation (Right to Left) */
    @keyframes shimmer {
        0% { background-position: 200% center; }
        100% { background-position: -200% center; }
    }

    .hovering-text {
        /* A base gold color with a white/yellow highlight that moves */
        background: linear-gradient(to right, #fbbf24 20%, #ffffff 50%, #fbbf24 80%);
        background-size: 200% auto;
        color: transparent;
        -webkit-background-clip: text;
        background-clip: text;
        animation: shimmer 3s linear infinite;
        display: inline-block;
        /* Keeps the text looking sharp */
        -webkit-text-fill-color: transparent; 
    }

    /* Cursor Blinking Animation */
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }

    /* Dot Styles */
    .type-dot {
        width: 25px;
        height: 4px;
        background: rgba(255,255,255,0.3); /* Gray by default */
        border-radius: 2px;
        cursor: pointer;
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .type-dot.active {
        background: #fbbf24; /* Yellow when active */
        transform: scaleY(1.5); /* Slight pop */
    }

    /* Mobile Adjustments */
    @media (max-width: 768px) {
        section h1 {
            font-size: 2.5rem !important;
        }
        #typewriter-text {
            font-size: 1rem !important;
        }
        .typewriter-cursor {
            height: 1rem !important;
        }
        .btn {
            width: 100%;
            display: block;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // ==========================================
        // 1. Background Image Slideshow
        // ==========================================
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;

        if (slides.length > 1) {
            setInterval(() => {
                slides[currentSlide].style.opacity = '0';
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].style.opacity = '1';
            }, 5000); // Changes background every 5 seconds
        }

        // ==========================================
        // 2. Typewriter Effect with Dynamic Dots
        // ==========================================
        const textElement = document.getElementById('typewriter-text');
        const dotsContainer = document.getElementById('typewriter-dots');
        
        // 👇 ADD YOUR SENTENCES HERE 👇
        const phrases = [
            "Building Champions Since 2025",
            "Talent. Discipline. Growth.",
            "From Kabete to the World",
            "Developing Young Talent for the Global Stage" // Added your new sentence!
        ];
        
        let phraseIndex = 0;
        let charIndex = 0;
        let isDeleting = false;
        let typingSpeed = 100; // Speed of typing in ms

        // Build the dots dynamically based on how many phrases you have
        phrases.forEach((phrase, index) => {
            const dot = document.createElement('div');
            dot.className = 'type-dot';
            if (index === 0) dot.classList.add('active'); // First dot starts yellow
            dot.dataset.index = index;
            
            // Allow clicking a dot to jump to that sentence
            dot.addEventListener('click', () => {
                // Only allow jumping if we aren't already transitioning
                if (phraseIndex !== index) {
                    textElement.textContent = ''; // Clear current text
                    phraseIndex = index;
                    charIndex = 0;
                    isDeleting = false;
                    updateDots();
                    type();
                }
            });
            
            dotsContainer.appendChild(dot);
        });

        const dots = document.querySelectorAll('.type-dot');

        // Function to update the yellow active dot
        function updateDots() {
            dots.forEach((dot, index) => {
                if (index === phraseIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }

        function type() {
            const currentPhrase = phrases[phraseIndex];
            
            if (isDeleting) {
                // Deleting text
                textElement.textContent = currentPhrase.substring(0, charIndex - 1);
                charIndex--;
                typingSpeed = 30; // Delete faster
            } else {
                // Typing text
                textElement.textContent = currentPhrase.substring(0, charIndex + 1);
                charIndex++;
                typingSpeed = 80; // Normal typing speed
            }

            // Logic for switching between phrases
            if (!isDeleting && charIndex === currentPhrase.length) {
                // Finished typing, wait before deleting
                typingSpeed = 2500; // Pause at the end so people can read it
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                // Finished deleting, move to next phrase
                isDeleting = false;
                phraseIndex = (phraseIndex + 1) % phrases.length;
                typingSpeed = 500; // Pause before typing next
                
                // Update the dots to show the new yellow one
                updateDots();
            }

            setTimeout(type, typingSpeed);
        }

        // Start the typewriter effect
        setTimeout(type, 1000);

    });
</script>
<!-- ═══════════════════════════════════════════════ -->

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
                    Welcome to <span style="color:#fbbf24;">CLEDUN FA</span>
                </h1>
                
                <p style="font-size:1.15rem;opacity:0.95;margin-bottom:30px;max-width:550px;line-height:1.7;">
                    Founded in <?php echo getSettings('club_established') ?: '2026'; ?>, We develop young footballers through quality coaching, teamwork, discipline, character, and competitive football. We help players learn, grow and reach their full potential from grassroots to senior level, with the ambition to compete on the global stage.
                </p>
                
                <div style="display:flex;gap:15px;flex-wrap:wrap;">
                    
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
                        <div style="font-size:0.85rem;opacity:0.8;">Categories</div>
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
<!-- ═══════════════════════════════════════════════ -->

<!-- ═══════════════════════════════════════════════ -->
<!-- HORIZONTAL CARD GALLERY (Lions Academy Style)  -->
<!-- ═══════════════════════════════════════════════ -->
<section style="padding: 60px 0; background: var(--light-bg); overflow: hidden;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px;">
            <div>
                <h2 style="color: var(--primary); font-size: 2rem; font-weight: 800; margin-bottom: 5px;">📸 CLEDUN FA Gallery</h2>
                <p style="color: var(--gray-text);">Moments captured from our matches, training, and events</p>
            </div>
        </div>

        <?php
        // Get active gallery images (Limit to latest 10 for the homepage)
        $galleryImages = $db->query("SELECT * FROM gallery WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT 10")->fetchAll();
        ?>

        <?php if (count($galleryImages) > 0): ?>
            
            <!-- Scroll Container -->
            <div style="display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; scroll-snap-type: x mandatory; scrollbar-width: thin; scrollbar-color: #fbbf24 #e5e7eb;">
                
                <?php foreach ($galleryImages as $image): ?>
                    <div style="flex: 0 0 auto; width: 320px; scroll-snap-align: start; position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; background: #000;" 
                         onclick="openLightbox('<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>', '<?php echo addslashes($image['title'] ?? 'Gallery Image'); ?>')">
                        
                        <!-- Image -->
                        <img src="<?php echo SITE_URL; ?>uploads/gallery/<?php echo $image['image_path']; ?>" 
                             alt="<?php echo $image['title'] ?? 'Gallery Image'; ?>"
                             style="width: 100%; height: 400px; object-fit: cover; transition: transform 0.5s ease, opacity 0.5s ease; opacity: 0.9;"
                             onmouseover="this.style.transform='scale(1.05)'; this.style.opacity='1';"
                             onmouseout="this.style.transform='scale(1)'; this.style.opacity='0.9';">
                        
                        <!-- Text Overlay -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; pointer-events: none;">
                            <h4 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 2px;">
                                <?php echo $image['title'] ?: 'CLEDUN FC'; ?>
                            </h4>
                            <p style="font-size: 0.85rem; opacity: 0.8;">
                                <?php echo ucfirst(str_replace('-', ' ', $image['category'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>

            <!-- Full Gallery Button -->
            <div style="text-align: center; margin-top: 15px;">
                <a href="<?php echo SITE_URL; ?>gallery.php" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 30px; border: 2px solid var(--primary); color: var(--primary); font-weight: 700; border-radius: 8px; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background='var(--primary)'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='var(--primary)';">
                    View Full Gallery <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 40px; background: var(--white); border-radius: 12px;">
                <p style="color: var(--gray-text);">No images in the gallery yet. Check back soon!</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Custom Scrollbar Styling for the Carousel -->
<style>
    /* Style the scrollbar */
    div::-webkit-scrollbar {
        height: 8px;
    }
    div::-webkit-scrollbar-track {
        background: #e5e7eb;
        border-radius: 4px;
    }
    div::-webkit-scrollbar-thumb {
        background: #fbbf24;
        border-radius: 4px;
    }
    div::-webkit-scrollbar-thumb:hover {
        background: #d97706;
    }
</style>

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
                   style="background: #1a2a6c; border-radius: var(--radius); padding: 30px 20px; text-align: center; transition: all 0.3s ease; border-bottom: 4px solid var(--secondary); text-decoration: none; display: block; box-shadow: 0 8px 20px rgba(13, 27, 62, 0.25);"
                   onmouseover="this.style.transform='translateY(-8px)'; this.style.background='#0d1b3e'; this.style.boxShadow='0 15px 30px rgba(13, 27, 62, 0.4)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.background='#1a2a6c'; this.style.boxShadow='0 8px 20px rgba(13, 27, 62, 0.25)';">
                    <div style="font-size: 2.5rem; margin-bottom: 12px;"><?php echo $icon; ?></div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; color: #fbbf24; margin-bottom: 4px;"><?php echo $cat['name']; ?></h3>
                    <p style="color: #e5e7eb; font-size: 0.85rem; opacity: 0.8; margin: 0;"><?php echo $cat['age_group']; ?></p>
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
            <p>Stay updated with the latest from CLEDUN FA</p>
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
            <p>Don't miss the next CLEDUN FA match</p>
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
            <h2>About CLEDUN FA</h2>
            <p>Our vision and mission drive everything we do</p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;">
            <div style="background:var(--white);padding:35px;border-radius:var(--radius);border-left:5px solid var(--secondary);box-shadow:var(--shadow);">
                <h3 style="color:var(--primary);font-size:1.6rem;margin-bottom:12px;">👁️ Our Vision</h3>
                <p style="color:var(--gray-text);line-height:1.8;">
                    To participate in world youth football tournaments globally, both as a team and through individual player representation. 
                    We aim to put CLEDUN FA on the international stage.
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
            <p>Watch the best moments from CLEDUN FA</p>
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