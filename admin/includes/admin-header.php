<?php
// Load required files first
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$db = getDB();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --admin-primary: #1a2a6c;
            --admin-secondary: #fbbf24;
            --admin-dark: #0d1b3e;
            --admin-bg: #f3f4f6;
            --admin-card: #ffffff;
            --admin-shadow: 0 4px 20px rgba(0,0,0,0.08);
            --admin-radius: 12px;
            --admin-sidebar: #0d1b3e;
            --admin-text: #111827;
            --admin-gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--admin-bg);
            color: var(--admin-text);
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 260px;
            background: var(--admin-sidebar);
            color: #fff;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .admin-sidebar .brand {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .admin-sidebar .brand h2 {
            font-size: 1.3rem;
            font-weight: 800;
        }
        
        .admin-sidebar .brand h2 span {
            color: var(--admin-secondary);
        }
        
        .admin-sidebar .brand small {
            display: block;
            opacity: 0.6;
            font-size: 0.75rem;
            margin-top: 4px;
        }
        
        .admin-sidebar .user-info {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .admin-sidebar .user-info .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--admin-secondary);
            color: var(--admin-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 8px;
        }
        
        .admin-sidebar .user-info .name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .admin-sidebar .user-info .role {
            font-size: 0.75rem;
            opacity: 0.6;
        }
        
        .admin-sidebar .menu {
            list-style: none;
            padding: 0 10px;
        }
        
        .admin-sidebar .menu li {
            margin-bottom: 4px;
        }
        
        .admin-sidebar .menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .admin-sidebar .menu li a:hover,
        .admin-sidebar .menu li a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .admin-sidebar .menu li a.active {
            background: var(--admin-secondary);
            color: var(--admin-dark);
            font-weight: 600;
        }
        
        .admin-sidebar .menu li a i {
            width: 20px;
            text-align: center;
        }
        
        .admin-main {
            margin-left: 260px;
            flex: 1;
            padding: 20px 30px 30px;
            min-height: 100vh;
        }
        
        .admin-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .admin-topbar h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--admin-dark);
        }
        
        .admin-topbar .admin-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .admin-topbar .admin-actions a {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .admin-topbar .admin-actions .btn-logout {
            background: #ef4444;
            color: #fff;
        }
        
        .admin-topbar .admin-actions .btn-logout:hover {
            background: #dc2626;
        }
        
        .admin-topbar .admin-actions .btn-website {
            background: var(--admin-secondary);
            color: var(--admin-dark);
        }
        
        .admin-topbar .admin-actions .btn-website:hover {
            background: #fcd34d;
        }
        
        .admin-card {
            background: var(--admin-card);
            border-radius: var(--admin-radius);
            padding: 25px;
            box-shadow: var(--admin-shadow);
            margin-bottom: 25px;
        }
        
        .admin-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--admin-dark);
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: var(--admin-card);
            border-radius: var(--admin-radius);
            padding: 20px;
            box-shadow: var(--admin-shadow);
            border-left: 4px solid var(--admin-secondary);
        }
        
        .stat-card .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--admin-dark);
        }
        
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: var(--admin-gray);
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        .admin-table th {
            background: var(--admin-bg);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--admin-gray);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .admin-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .admin-table tr:hover td {
            background: #f9fafb;
        }
        
        .admin-table .status-badge {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .admin-table .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .admin-table .status-badge.inactive {
            background: #fce4ec;
            color: #9a3412;
        }
        
        .admin-table .status-badge.published {
            background: #d1fae5;
            color: #065f46;
        }
        
        .admin-table .status-badge.draft {
            background: #fef3c7;
            color: #92400e;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 10px 24px;
            background: var(--admin-secondary);
            color: var(--admin-dark);
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #fcd34d;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3);
        }
        
        .btn-secondary {
            display: inline-block;
            padding: 10px 24px;
            background: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        .btn-danger {
            display: inline-block;
            padding: 10px 24px;
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-sm {
            padding: 6px 14px;
            font-size: 0.8rem;
        }
        
        .btn-action {
            padding: 4px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-action.edit {
            color: #2563eb;
        }
        
        .btn-action.edit:hover {
            background: #dbeafe;
        }
        
        .btn-action.delete {
            color: #dc2626;
        }
        
        .btn-action.delete:hover {
            background: #fce4ec;
        }
        
        .btn-action.view {
            color: #059669;
        }
        
        .btn-action.view:hover {
            background: #d1fae5;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--admin-secondary);
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.1);
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            border-left-color: #10b981;
            background: #ecfdf5;
            color: #065f46;
        }
        
        .alert-error {
            border-left-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }
        
        .alert-warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
            color: #92400e;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .admin-sidebar.open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
                padding: 15px;
            }
            
            .hamburger {
                display: flex !important;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .hamburger {
            display: none;
            background: none;
            border: none;
            flex-direction: column;
            gap: 5px;
            padding: 5px;
            cursor: pointer;
        }
        
        .hamburger span {
            width: 28px;
            height: 3px;
            background: var(--admin-dark);
            border-radius: 3px;
            transition: 0.3s;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand">
            <h2>CLEDUN <span>FC</span></h2>
            <small>Admin Panel</small>
        </div>
        
        <div class="user-info">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)); ?></div>
            <div class="name"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></div>
            <div class="role"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></div>
        </div>
        
        <ul class="menu">
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a></li>
            <li><a href="players.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'players') !== false ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Players
            </a></li>
            <li><a href="staff.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'staff') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> Staff
            </a></li>
            <li><a href="matches.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'matches') !== false ? 'active' : ''; ?>">
                <i class="fas fa-futbol"></i> Matches
            </a></li>
            <li><a href="news.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'news') !== false ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i> News
            </a></li>
            <li><a href="categories.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'categories') !== false ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categories
            </a></li>
            <!-- ========================================= -->
            <!-- GALLERY TAB - ADDED HERE -->
            <!-- ========================================= -->
            <li><a href="gallery.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'gallery') !== false ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Gallery
            </a></li>
            <!-- ========================================= -->
             <li><a href="videos.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'videos') !== false ? 'active' : ''; ?>">
                 <i class="fas fa-video"></i> Videos
            </a></li>
            <li><a href="tickets.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'tickets') !== false ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> Tickets
            </a></li>
            <li><a href="bookings.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'bookings') !== false ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Bookings
            </a></li>
            <li><a href="messages.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'messages') !== false ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Messages
            </a></li>
            <li><a href="settings.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a></li>
            <li><a href="change-password.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'change-password') !== false ? 'active' : ''; ?>">
                <i class="fas fa-key"></i> Change Password
            </a></li>

            <li><a href="backup-database.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'backup-database') !== false ? 'active' : ''; ?>">
                <i class="fas fa-database"></i> Backup
            </a></li>
            <li><a href="cleanup.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'cleanup') !== false ? 'active' : ''; ?>">
                <i class="fas fa-broom"></i> Cleanup
            </a></li>
            <li><a href="add-players-batch.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'add-players-batch') !== false ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Batch Players
            </a></li>
            <li><a href="add-staff-batch.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'add-staff-batch') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> Batch Staff
            </a></li>
            <li><a href="add-matches-batch.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'add-matches-batch') !== false ? 'active' : ''; ?>">
                 <i class="fas fa-futbol"></i> Batch Matches
            </a></li>

            <li><a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:15px;">
                <button class="hamburger" id="hamburger" aria-label="Toggle sidebar">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
            </div>
            <div class="admin-actions">
                <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn-website">
                    <i class="fas fa-globe"></i> View Site
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <script>
            document.getElementById('hamburger').addEventListener('click', function() {
                document.getElementById('adminSidebar').classList.toggle('open');
                document.getElementById('sidebarOverlay').classList.toggle('show');
            });
            
            document.getElementById('sidebarOverlay').addEventListener('click', function() {
                document.getElementById('adminSidebar').classList.remove('open');
                document.getElementById('sidebarOverlay').classList.remove('show');
            });
        </script>