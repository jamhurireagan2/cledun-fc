<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/responsive.css">
    
    <style>
        /* Navigation Styles */
        .navbar {
            background: linear-gradient(135deg, #1a2a6c, #0d1b3e);
            padding: 0 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
        }
        
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.5rem;
        }
        
        .nav-logo img {
            height: 50px;
            width: auto;
        }
        
        .nav-logo span {
            color: #fbbf24;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-links li a {
            color: #e5e7eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s;
            position: relative;
            padding: 8px 0;
        }
        
        .nav-links li a:hover {
            color: #fbbf24;
        }
        
        .nav-links li a.active {
            color: #fbbf24;
        }
        
        .nav-links li a.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #fbbf24;
            border-radius: 3px;
        }
        
        .nav-links .dropdown {
            position: relative;
        }
        
        .nav-links .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #1a2a6c;
            min-width: 200px;
            border-radius: 8px;
            padding: 10px 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .nav-links .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .nav-links .dropdown-menu li {
            padding: 0;
        }
        
        .nav-links .dropdown-menu li a {
            display: block;
            padding: 8px 20px;
            color: #e5e7eb;
            font-weight: 400;
        }
        
        .nav-links .dropdown-menu li a:hover {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
        }
        
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
        
        .hamburger span {
            width: 28px;
            height: 3px;
            background: #fff;
            border-radius: 3px;
            transition: 0.3s;
        }
        
        /* Live badge in nav */
        .nav-live-badge {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.6rem;
            font-weight: 700;
            margin-left: 4px;
            animation: pulse-live-nav 1.5s infinite;
            display: inline-block;
        }
        
        @keyframes pulse-live-nav {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }
            
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #0d1b3e;
                padding: 20px;
                gap: 15px;
                border-top: 2px solid #fbbf24;
            }
            
            .nav-links.open {
                display: flex;
            }
            
            .nav-links .dropdown-menu {
                position: static;
                background: rgba(255,255,255,0.05);
                padding-left: 20px;
            }
            
            .nav-links li a {
                padding: 10px 0;
            }
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo SITE_URL; ?>" class="nav-logo">
                <img src="<?php echo SITE_URL; ?>assets/images/badge.png" alt="CLEDUN FC" onerror="this.style.display='none'">
                CLEDUN <span>FC</span>
            </a>
            
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="<?php echo SITE_URL; ?>" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>squad.php" class="<?php echo ($currentPage ?? '') === 'squad' ? 'active' : ''; ?>">Squad</a></li>
                
                <li class="dropdown">
                    <a href="#">Teams <i class="fas fa-chevron-down" style="font-size:0.7rem;margin-left:4px;"></i></a>
                    <ul class="dropdown-menu">
                        <?php 
                        $categories = getActiveCategories();
                        foreach ($categories as $cat): 
                        ?>
                            <li><a href="<?php echo SITE_URL; ?>category.php?slug=<?php echo $cat['slug']; ?>">
                                <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                
                <!-- NEW: Live Matches Link -->
                <li><a href="<?php echo SITE_URL; ?>live-matches.php" class="<?php echo ($currentPage ?? '') === 'live-matches' ? 'active' : ''; ?>">
                    ⚡ Live <span class="nav-live-badge">LIVE</span>
                </a></li>
                
                <li><a href="<?php echo SITE_URL; ?>matches.php" class="<?php echo ($currentPage ?? '') === 'matches' ? 'active' : ''; ?>">Matches</a></li>
                <li><a href="<?php echo SITE_URL; ?>news.php" class="<?php echo ($currentPage ?? '') === 'news' ? 'active' : ''; ?>">News</a></li>
                <li><a href="<?php echo SITE_URL; ?>gallery.php" class="<?php echo ($currentPage ?? '') === 'gallery' ? 'active' : ''; ?>">Gallery</a></li>
                <li><a href="<?php echo SITE_URL; ?>videos.php" class="<?php echo ($currentPage ?? '') === 'videos' ? 'active' : ''; ?>">
                🎬 Videos
                </a></li>
                <li><a href="<?php echo SITE_URL; ?>about.php" class="<?php echo ($currentPage ?? '') === 'about' ? 'active' : ''; ?>">About</a></li>
                <li><a href="<?php echo SITE_URL; ?>contact.php" class="<?php echo ($currentPage ?? '') === 'contact' ? 'active' : ''; ?>">Contact</a></li>
                <li><a href="<?php echo SITE_URL; ?>tickets.php" class="btn btn-primary" style="background:#fbbf24;color:#1a2a6c;padding:8px 20px;border-radius:8px;">🎟️ Tickets</a></li>
            </ul>
        </div>
    </nav>

    <script>
        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('open');
        });
    </script>

    <!-- Chatbot Script -->
<script src="<?php echo SITE_URL; ?>assets/js/chatbot.js"></script>
