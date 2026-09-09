<?php
require_once 'includes/functions.php';

$currentPage = 'videos';
$pageTitle = 'Video Highlights - ' . SITE_NAME;

$db = getDB();

// Get category filter
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : 'all';

$query = "SELECT * FROM videos WHERE is_active = 1";
$params = [];

if ($categoryFilter !== 'all') {
    $query .= " AND category = ?";
    $params[] = $categoryFilter;
}

$query .= " ORDER BY display_order ASC, created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$videos = $stmt->fetchAll();

// Get categories for filter
$categories = [];
try {
    $stmt = $db->query("SELECT DISTINCT category FROM videos WHERE is_active = 1");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>🎬 Video Highlights</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Watch the best moments from CLEDUN FC</p>
            </div>
        </div>
    </div>
</section>

<!-- Video Content -->
<section style="padding: 50px 0;">
    <div class="container">
        
        <!-- Filter -->
        <?php if (count($categories) > 0): ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:30px;justify-content:center;">
                <a href="videos.php" class="btn <?php echo $categoryFilter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size:0.85rem;padding:8px 20px;">
                    All Videos
                </a>
                <?php foreach ($categories as $cat): ?>
                    <?php 
                    $categoryName = isset($cat['category']) ? $cat['category'] : '';
                    if (empty($categoryName)) continue;
                    ?>
                    <a href="videos.php?category=<?php echo urlencode($categoryName); ?>" 
                       class="btn <?php echo $categoryFilter === $categoryName ? 'btn-primary' : 'btn-secondary'; ?>" 
                       style="font-size:0.85rem;padding:8px 20px;">
                        <?php echo ucfirst(str_replace('-', ' ', $categoryName)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Videos Grid -->
        <?php if (count($videos) > 0): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:30px;">
                <?php foreach ($videos as $video): ?>
                    <div style="background:var(--white);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:var(--transition);">
                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;background:#0d1b3e;cursor:pointer;" 
                             onclick="openVideoModal(
                                 '<?php echo addslashes($video['video_file'] ? SITE_URL . 'uploads/videos/' . $video['video_file'] : $video['video_url']); ?>', 
                                 '<?php echo addslashes($video['title']); ?>', 
                                 '<?php echo addslashes($video['description'] ?? ''); ?>', 
                                 '<?php echo $video['video_file'] ? 'uploaded' : 'youtube'; ?>'
                             )">
                            
                            <?php if ($video['thumbnail']): ?>
                                <img src="<?php echo SITE_URL; ?>uploads/videos/<?php echo $video['thumbnail']; ?>" 
                                     alt="<?php echo $video['title']; ?>"
                                     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:white;font-size:4rem;background:linear-gradient(135deg,#1a2a6c,#2a3f8a);">
                                    <i class="fas fa-video"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Play Button Overlay -->
                            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:2;width:70px;height:70px;background:rgba(251,191,36,0.9);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all 0.3s;font-size:2rem;color:#1a2a6c;">
                                <i class="fas fa-play" style="margin-left:5px;"></i>
                            </div>
                            
                            <div style="position:absolute;bottom:0;left:0;right:0;padding:20px;background:linear-gradient(transparent,rgba(0,0,0,0.7));z-index:1;">
                                <span style="color:rgba(255,255,255,0.7);font-size:0.75rem;text-transform:uppercase;">
                                    <?php echo ucfirst(str_replace('-', ' ', $video['category'])); ?>
                                </span>
                                <?php if ($video['video_file']): ?>
                                    <span style="color:#fbbf24;font-size:0.7rem;margin-left:10px;">
                                        <i class="fas fa-cloud-upload-alt"></i> Uploaded
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="padding:20px;">
                            <h3 style="font-size:1.1rem;font-weight:700;color:var(--primary);margin-bottom:6px;"><?php echo $video['title']; ?></h3>
                            <?php if ($video['description']): ?>
                                <p style="color:var(--gray-text);font-size:0.9rem;line-height:1.6;"><?php echo substr($video['description'], 0, 100); ?><?php echo strlen($video['description']) > 100 ? '...' : ''; ?></p>
                            <?php endif; ?>
                            <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;">
                                <button onclick="openVideoModal('<?php 
                                    if ($video['video_file']) {
                                        echo SITE_URL . 'uploads/videos/' . $video['video_file'];
                                    } else {
                                        echo $video['video_url'];
                                    }
                                ?>', '<?php echo addslashes($video['title']); ?>', '<?php echo addslashes($video['description'] ?? ''); ?>', '<?php echo $video['video_file'] ? 'uploaded' : 'youtube'; ?>')" 
                                        class="btn btn-primary" style="font-size:0.8rem;padding:8px 20px;">
                                    <i class="fas fa-play"></i> Watch Now
                                </button>
                                <?php if ($video['video_file']): ?>
                                    <a href="<?php echo SITE_URL; ?>uploads/videos/<?php echo $video['video_file']; ?>" 
                                       download 
                                       class="btn btn-secondary" style="font-size:0.8rem;padding:8px 20px;">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 0;">
                <div style="font-size:4rem;margin-bottom:20px;">🎬</div>
                <h3 style="color:var(--gray-text);">No videos found</h3>
                <p style="color:var(--gray-text);">Check back later for video highlights.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Video Modal -->
<div id="videoModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <button onclick="closeVideoModal()" style="position:absolute;top:20px;right:30px;background:none;border:none;color:white;font-size:2rem;cursor:pointer;z-index:10;">
        <i class="fas fa-times"></i>
    </button>
    
    <div style="width:100%;max-width:900px;background:#0d1b3e;border-radius:12px;overflow:hidden;">
        <div style="position:relative;padding-bottom:56.25%;height:0;">
            <!-- For YouTube videos -->
            <iframe id="videoPlayer" 
                    src="" 
                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;display:none;"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
            </iframe>
            <!-- For uploaded videos -->
            <video id="uploadedVideoPlayer" 
                   controls 
                   style="position:absolute;top:0;left:0;width:100%;height:100%;display:none;background:#000;">
                <source id="videoSource" src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div style="padding:20px;background:#1a2a6c;color:white;">
            <h3 id="videoModalTitle" style="font-size:1.2rem;margin-bottom:4px;">Video Title</h3>
            <p id="videoModalDescription" style="opacity:0.8;font-size:0.95rem;">Video description</p>
        </div>
    </div>
</div>

<script>
function openVideoModal(url, title, description, type) {
    var iframe = document.getElementById('videoPlayer');
    var video = document.getElementById('uploadedVideoPlayer');
    var source = document.getElementById('videoSource');
    
    // Hide both players first
    iframe.style.display = 'none';
    iframe.src = '';
    video.style.display = 'none';
    video.pause();
    
    if (type === 'uploaded') {
        // Show uploaded video
        source.src = url;
        video.load();
        video.style.display = 'block';
        video.play();
    } else {
        // Show YouTube video
        iframe.src = url;
        iframe.style.display = 'block';
    }
    
    document.getElementById('videoModalTitle').textContent = title || 'Video Highlight';
    document.getElementById('videoModalDescription').textContent = description || 'CLEDUN FC Video';
    document.getElementById('videoModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeVideoModal() {
    var iframe = document.getElementById('videoPlayer');
    var video = document.getElementById('uploadedVideoPlayer');
    
    if (iframe) {
        iframe.src = '';
        iframe.style.display = 'none';
    }
    if (video) {
        video.pause();
        video.style.display = 'none';
    }
    
    document.getElementById('videoModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});

// Close on click outside
document.getElementById('videoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVideoModal();
    }
});
</script>

<style>
    .video-card:hover .play-button {
        transform: scale(1.1);
        background: rgba(251,191,36,1);
    }
    
    .video-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 40px rgba(0,0,0,0.15);
    }
</style>

<?php require_once 'includes/footer.php'; ?>