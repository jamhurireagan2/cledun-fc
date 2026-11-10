<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google Analytics (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LRREL645W9"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-LRREL645W9');
    </script>
    <!-- End Google Analytics -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : SITE_NAME; ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo SITE_URL; ?>manifest.json">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#1a2a6c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CLEDUN FC">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>assets/images/icon-96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>assets/images/icon-72.png">
    
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
            position: relative;
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
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }
        
        .nav-links li a {
            color: #e5e7eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: color 0.3s;
            padding: 8px 0;
            display: block;
        }
        
        .nav-links li a:hover,
        .nav-links li a.active {
            color: #fbbf24;
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
            list-style: none;
            margin: 0;
        }
        
        .nav-links .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .nav-links .dropdown-menu li a {
            padding: 8px 20px;
            font-weight: 400;
        }
        
        .nav-links .dropdown-menu li a:hover {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
        }
        
                /* Social Icons in Nav */
        .nav-social {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: 10px; /* Adds a bit of space after the Register link */
        }
        
        /* We target the 'a' tag specifically inside .nav-social to override the default link padding */
        .nav-links .nav-social a {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 36px !important;
            height: 36px !important;
            padding: 0 !important; /* Removes the 8px padding that was squashing it */
            border-radius: 50% !important;
            color: white !important;
            text-decoration: none !important;
            transition: transform 0.3s;
        }
        
        .nav-links .nav-social a:hover {
            transform: scale(1.1);
            color: white !important;
        }
        
        .nav-links .nav-social a i {
            font-size: 1rem;
            margin: 0;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            z-index: 1000;
            position: relative;
        }
        
        .hamburger span {
            width: 28px;
            height: 3px;
            background: #fff;
            border-radius: 3px;
            transition: 0.3s;
        }
        
        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }
        
        .nav-live-badge {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.6rem;
            font-weight: 700;
            margin-left: 4px;
            display: inline-block;
            animation: pulse-live-nav 1.5s infinite;
        }
        
        @keyframes pulse-live-nav {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        
        /* Mobile Menu Overlay */
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .menu-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* MOBILE STYLES - SIDE DRAWER */
        @media (max-width: 992px) {
            .hamburger {
                display: flex;
                position: fixed;
                top: 15px;
                right: 20px;
                z-index: 1000;
            }
            
            .nav-logo {
                z-index: 1000;
            }
            
            .nav-links {
                display: flex;
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: #0d1b3e;
                flex-direction: column;
                padding: 80px 20px 30px;
                gap: 0;
                box-shadow: -10px 0 40px rgba(0,0,0,0.5);
                align-items: stretch;
                transition: right 0.3s ease;
                overflow-y: auto;
                z-index: 999;
                margin: 0;
                list-style: none;
            }
            
            .nav-links.active {
                right: 0;
            }
            
            .nav-links li {
                width: 100%;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            
            .nav-links li:last-child {
                border-bottom: none;
                margin-top: 15px;
            }
            
            .nav-links li a {
                padding: 14px 0;
                font-size: 1rem;
                width: 100%;
                color: #e5e7eb;
                display: block;
            }
            
            .nav-links .dropdown {
                position: static;
                width: 100%;
            }
            
            .nav-links .dropdown-menu {
                display: none;
                position: static;
                background: rgba(255,255,255,0.05);
                padding: 5px 0 5px 20px;
                border-radius: 8px;
                margin: 5px 0;
                box-shadow: none;
                width: 100%;
            }
            
            .nav-links .dropdown.open .dropdown-menu {
                display: block;
            }
            
            .nav-links .dropdown-menu li {
                border-bottom: none;
            }
            
            .nav-links .dropdown-menu li a {
                padding: 10px 0;
                font-size: 0.9rem;
            }
            
            .nav-links li a.btn-primary {
                display: block;
                text-align: center;
                margin: 10px 0;
            }

            /* Adjust social icons on mobile */
            .nav-social {
                margin-top: 15px;
                justify-content: center;
                border-bottom: none !important;
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
                CLEDUN <span>FOOTBALL ACADEMY</span>
            </a>
            
            <button class="hamburger" id="hamburger" aria-label="Menu" type="button">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="<?php echo SITE_URL; ?>" class="<?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">Home</a></li>
                
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle">Teams <i class="fas fa-chevron-down" style="font-size:0.7rem;margin-left:4px;"></i></a>
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
                
                
                <li><a href="<?php echo SITE_URL; ?>news.php" class="<?php echo ($currentPage ?? '') === 'news' ? 'active' : ''; ?>">News</a></li>
                <li><a href="<?php echo SITE_URL; ?>gallery.php" class="<?php echo ($currentPage ?? '') === 'gallery' ? 'active' : ''; ?>">Gallery</a></li>
                
                <li><a href="<?php echo SITE_URL; ?>about.php" class="<?php echo ($currentPage ?? '') === 'about' ? 'active' : ''; ?>">About</a></li>
                <li><a href="<?php echo SITE_URL; ?>contact.php" class="<?php echo ($currentPage ?? '') === 'contact' ? 'active' : ''; ?>">Contact</a></li>
                <li><a href="<?php echo SITE_URL; ?>register-player.php" class="<?php echo ($currentPage ?? '') === 'register' ? 'active' : ''; ?>">📝 Register</a></li>

                <li><a href="<?php echo SITE_URL; ?>merchandise.php" class="<?php echo ($currentPage ?? '') === 'merchandise' ? 'active' : ''; ?>">🛍️ Merchandise</a></li>

                <!-- Social Icons -->
                <?php 
                $fbUrl = getSettings('social_facebook');
                $igUrl = getSettings('social_instagram');
                ?>
                
                <?php if ($fbUrl || $igUrl): ?>
                <li class="nav-social" style="border-bottom:none;">
                    <?php if ($fbUrl): ?>
                    <a href="<?php echo htmlspecialchars($fbUrl); ?>" target="_blank" style="background:#1877f2;" title="Follow us on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <?php endif; ?>

                    <?php if ($igUrl): ?>
                    <a href="<?php echo htmlspecialchars($igUrl); ?>" target="_blank" style="background:linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);" title="Follow us on Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endif; ?>
                
            </ul>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var hamburger = document.getElementById('hamburger');
        var navLinks = document.getElementById('navLinks');
        var dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        // Create overlay
        var overlay = document.createElement('div');
        overlay.className = 'menu-overlay';
        document.body.appendChild(overlay);
        
        // Toggle mobile menu
        if (hamburger && navLinks) {
            hamburger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                navLinks.classList.toggle('active');
                hamburger.classList.toggle('active');
                overlay.classList.toggle('active');
            });
        }
        
        // Close menu when clicking overlay
        overlay.addEventListener('click', function() {
            navLinks.classList.remove('active');
            hamburger.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        // Toggle dropdown on click (mobile)
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    e.stopPropagation();
                    var parent = this.closest('.dropdown');
                    if (parent) {
                        parent.classList.toggle('open');
                    }
                }
            });
        });
        
        // Close menu when clicking a link (except dropdown toggle)
        var navLinksItems = document.querySelectorAll('.nav-links li a:not(.dropdown-toggle)');
        navLinksItems.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 992) {
                    navLinks.classList.remove('active');
                    hamburger.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
    });
    </script>

</body>
</html>