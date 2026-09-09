<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = getDB();
$pageTitle = 'Video Management';
$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get video file to delete
    $stmt = $db->prepare("SELECT video_file, thumbnail FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch();
    
    if ($video) {
        // Delete video file
        if ($video['video_file']) {
            $filePath = '../uploads/videos/' . $video['video_file'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        // Delete thumbnail
        if ($video['thumbnail']) {
            $thumbPath = '../uploads/videos/' . $video['thumbnail'];
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
        }
    }
    
    $stmt = $db->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Video deleted successfully');
    header('Location: videos.php');
    exit();
}

// Handle toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $db->prepare("UPDATE videos SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: videos.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $video_url = sanitize($_POST['video_url']);
    $category = sanitize($_POST['category']);
    $display_order = intval($_POST['display_order']);
    $uploaded_by = $_SESSION['user_id'];
    $video_file = '';
    
    // Handle video file upload
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
        $uploadDir = '../uploads/videos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv'];
        
        if (in_array($fileExt, $allowed)) {
            // Max file size: 100MB
            if ($_FILES['video_file']['size'] <= 100 * 1024 * 1024) {
                $fileName = time() . '_' . createSlug($title) . '.' . $fileExt;
                $uploadPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $uploadPath)) {
                    $video_file = $fileName;
                } else {
                    $error = 'Failed to upload video file.';
                }
            } else {
                $error = 'Video file is too large. Maximum size is 100MB.';
            }
        } else {
            $error = 'Invalid video format. Allowed: MP4, WEBM, OGG, MOV, AVI, MKV, FLV, WMV';
        }
    }
    
    // Handle thumbnail upload
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $uploadDir = '../uploads/videos/';
        $fileExt = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowed)) {
            $fileName = time() . '_thumb_' . createSlug($title) . '.' . $fileExt;
            $uploadPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploadPath)) {
                $thumbnail = $fileName;
            }
        }
    }
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = intval($_POST['id']);
        
        // Get existing video to delete old file if needed
        $stmt = $db->prepare("SELECT video_file, thumbnail FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        
        // Delete old video file if new one uploaded
        if (!empty($video_file) && $existing && $existing['video_file']) {
            $oldPath = '../uploads/videos/' . $existing['video_file'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        
        // Delete old thumbnail if new one uploaded
        if (!empty($thumbnail) && $existing && $existing['thumbnail']) {
            $oldThumb = '../uploads/videos/' . $existing['thumbnail'];
            if (file_exists($oldThumb)) {
                unlink($oldThumb);
            }
        }
        
        // Build update query
        if (!empty($video_file) && !empty($thumbnail)) {
            $stmt = $db->prepare("UPDATE videos SET title = ?, description = ?, video_url = ?, category = ?, thumbnail = ?, display_order = ?, video_file = ? WHERE id = ?");
            $stmt->execute([$title, $description, $video_url, $category, $thumbnail, $display_order, $video_file, $id]);
        } elseif (!empty($video_file)) {
            $stmt = $db->prepare("UPDATE videos SET title = ?, description = ?, video_url = ?, category = ?, display_order = ?, video_file = ? WHERE id = ?");
            $stmt->execute([$title, $description, $video_url, $category, $display_order, $video_file, $id]);
        } elseif (!empty($thumbnail)) {
            $stmt = $db->prepare("UPDATE videos SET title = ?, description = ?, video_url = ?, category = ?, thumbnail = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$title, $description, $video_url, $category, $thumbnail, $display_order, $id]);
        } else {
            $stmt = $db->prepare("UPDATE videos SET title = ?, description = ?, video_url = ?, category = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$title, $description, $video_url, $category, $display_order, $id]);
        }
        setFlash('success', 'Video updated successfully');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO videos (title, description, video_url, category, thumbnail, display_order, uploaded_by, video_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $video_url, $category, $thumbnail, $display_order, $uploaded_by, $video_file]);
        setFlash('success', 'Video added successfully');
    }
    header('Location: videos.php');
    exit();
}

// Get all videos
$videos = $db->query("
    SELECT v.*, u.full_name as uploader_name 
    FROM videos v 
    LEFT JOIN users u ON v.uploaded_by = u.id 
    ORDER BY v.display_order ASC, v.created_at DESC
")->fetchAll();

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h2 style="color:var(--admin-dark);">🎬 Video Highlights</h2>
        <button class="btn-primary" onclick="document.getElementById('addForm').style.display='block'">
            <i class="fas fa-plus"></i> Add Video
        </button>
    </div>

    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
    <?php endif; ?>

    <!-- Add Form -->
    <div id="addForm" style="display:none;background:var(--admin-card);padding:25px;border-radius:15px;margin-bottom:20px;box-shadow:var(--admin-shadow);">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Add New Video</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" class="form-control" required>
                        <option value="general">General</option>
                        <option value="match-highlights">Match Highlights</option>
                        <option value="training">Training</option>
                        <option value="interviews">Interviews</option>
                        <option value="skills">Skills</option>
                        <option value="events">Events</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="background:#fef3c7;padding:15px;border-radius:8px;margin:15px 0;border-left:4px solid #f59e0b;">
                <strong style="color:#92400e;">📤 Upload Options:</strong>
                <p style="color:#92400e;margin-top:5px;font-size:0.9rem;">
                    You can either upload a video file OR provide a YouTube URL. If you upload a file, the URL will be ignored.
                </p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Upload Video File</label>
                    <input type="file" name="video_file" class="form-control" accept="video/*">
                    <small style="color:var(--admin-gray);">MP4, WEBM, OGG, MOV, AVI, MKV (Max 100MB)</small>
                </div>
                <div class="form-group">
                    <label>OR YouTube URL</label>
                    <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                    <small style="color:var(--admin-gray);">Use embed URL: https://www.youtube.com/embed/VIDEO_ID</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>Thumbnail Image</label>
                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Upload a thumbnail (JPG, PNG, GIF) - Optional</small>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Video</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('addForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Videos Grid -->
    <?php if (count($videos) > 0): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
            <?php foreach ($videos as $video): ?>
                <div style="background:var(--admin-card);border-radius:var(--admin-radius);overflow:hidden;box-shadow:var(--admin-shadow);position:relative;">
                    <div style="position:relative;height:180px;overflow:hidden;background:#1a2a6c;">
                        <?php if ($video['thumbnail']): ?>
                            <img src="<?php echo SITE_URL; ?>uploads/videos/<?php echo $video['thumbnail']; ?>" 
                                 alt="<?php echo $video['title']; ?>"
                                 style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:white;font-size:3rem;background:linear-gradient(135deg,#1a2a6c,#2a3f8a);">
                                <i class="fas fa-video"></i>
                            </div>
                        <?php endif; ?>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:3rem;color:white;text-shadow:0 2px 10px rgba(0,0,0,0.5);">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <?php if (!$video['is_active']): ?>
                            <div style="position:absolute;top:10px;right:10px;background:#ef4444;color:white;padding:2px 12px;border-radius:12px;font-size:0.7rem;font-weight:600;">
                                Hidden
                            </div>
                        <?php endif; ?>
                        <?php if ($video['video_file']): ?>
                            <div style="position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,0.7);color:white;padding:2px 10px;border-radius:12px;font-size:0.6rem;">
                                📁 Uploaded
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="padding:15px;">
                        <h4 style="font-size:0.95rem;margin-bottom:4px;"><?php echo $video['title']; ?></h4>
                        <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:var(--admin-gray);">
                            <span><?php echo ucfirst(str_replace('-', ' ', $video['category'])); ?></span>
                            <span>Order: <?php echo $video['display_order']; ?></span>
                        </div>
                        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="videos.php?toggle=<?php echo $video['id']; ?>" 
                               class="btn-action <?php echo $video['is_active'] ? 'view' : 'edit'; ?>"
                               title="<?php echo $video['is_active'] ? 'Hide' : 'Show'; ?>">
                                <i class="fas <?php echo $video['is_active'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                            </a>
                            <a href="#" class="btn-action edit" onclick="editVideo(<?php echo htmlspecialchars(json_encode($video)); ?>)">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="videos.php?delete=<?php echo $video['id']; ?>" class="btn-action delete delete-confirm">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php if ($video['video_file']): ?>
                                <a href="<?php echo SITE_URL; ?>uploads/videos/<?php echo $video['video_file']; ?>" target="_blank" class="btn-action view">
                                    <i class="fas fa-download"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo $video['video_url']; ?>" target="_blank" class="btn-action view">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:60px 0;background:var(--admin-card);border-radius:var(--admin-radius);">
            <div style="font-size:4rem;margin-bottom:20px;">🎬</div>
            <h3 style="color:var(--admin-gray);">No videos in highlights</h3>
            <p style="color:var(--admin-gray);">Upload your first video highlight!</p>
            <button class="btn-primary" style="margin-top:15px;" onclick="document.getElementById('addForm').style.display='block'">
                <i class="fas fa-plus"></i> Add Video
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:var(--admin-card);padding:30px;border-radius:15px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;">
        <h3 style="margin-bottom:15px;color:var(--admin-dark);">Edit Video</h3>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="id" id="edit_id">
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" id="edit_category" class="form-control" required>
                        <option value="general">General</option>
                        <option value="match-highlights">Match Highlights</option>
                        <option value="training">Training</option>
                        <option value="interviews">Interviews</option>
                        <option value="skills">Skills</option>
                        <option value="events">Events</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" id="edit_display_order" class="form-control" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="background:#fef3c7;padding:15px;border-radius:8px;margin:15px 0;border-left:4px solid #f59e0b;">
                <strong style="color:#92400e;">📤 Update Options:</strong>
                <p style="color:#92400e;margin-top:5px;font-size:0.9rem;">
                    Upload a new video to replace the current one, or update the URL.
                </p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Upload New Video (optional)</label>
                    <input type="file" name="video_file" class="form-control" accept="video/*">
                    <small style="color:var(--admin-gray);">MP4, WEBM, OGG, MOV, AVI, MKV (Max 100MB)</small>
                </div>
                <div class="form-group">
                    <label>OR YouTube URL</label>
                    <input type="url" name="video_url" id="edit_video_url" class="form-control" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                </div>
            </div>
            
            <div class="form-group">
                <label>New Thumbnail (optional)</label>
                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                <small style="color:var(--admin-gray);">Upload a new thumbnail to replace the current one</small>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn-primary">Update Video</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editVideo(video) {
    document.getElementById('edit_id').value = video.id;
    document.getElementById('edit_title').value = video.title;
    document.getElementById('edit_category').value = video.category;
    document.getElementById('edit_video_url').value = video.video_url || '';
    document.getElementById('edit_display_order').value = video.display_order;
    document.getElementById('edit_description').value = video.description || '';
    document.getElementById('editModal').style.display = 'flex';
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});
</script>

<?php require_once 'includes/admin-footer.php'; ?>