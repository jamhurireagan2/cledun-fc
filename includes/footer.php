    <!-- Footer -->
    <footer style="background: linear-gradient(135deg, #0d1b3e, #1a2a6c); color: #e5e7eb; padding: 60px 0 20px; margin-top: 60px;">
        <div class="container" style="max-width:1200px;margin:0 auto;padding:0 20px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:40px;margin-bottom:40px;">
                
                <!-- About -->
                <div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:15px;">
                        <img src="<?php echo SITE_URL; ?>assets/images/badge.png" alt="CLEDUN FC" style="height:50px;" onerror="this.style.display='none'">
                        <h3 style="color:#fbbf24;font-size:1.5rem;font-weight:800;">CLEDUN <span style="color:#fff;">FC</span></h3>
                    </div>
                    <p style="opacity:0.8;font-size:0.9rem;line-height:1.6;">Building champions since <?php echo getSettings('club_established') ?: '2026'; ?>. Nurturing young talent and developing complete athletes.</p>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 style="color:#fbbf24;font-size:1.1rem;margin-bottom:15px;">Quick Links</h4>
                    <ul style="list-style:none;padding:0;">
                        <li style="margin-bottom:8px;"><a href="<?php echo SITE_URL; ?>squad.php" style="color:#e5e7eb;text-decoration:none;opacity:0.8;">Squad</a></li>
                        <li style="margin-bottom:8px;"><a href="<?php echo SITE_URL; ?>matches.php" style="color:#e5e7eb;text-decoration:none;opacity:0.8;">Matches</a></li>
                        <li style="margin-bottom:8px;"><a href="<?php echo SITE_URL; ?>news.php" style="color:#e5e7eb;text-decoration:none;opacity:0.8;">News</a></li>
                        <li style="margin-bottom:8px;"><a href="<?php echo SITE_URL; ?>tickets.php" style="color:#e5e7eb;text-decoration:none;opacity:0.8;">Tickets</a></li>
                    </ul>
                </div>
                
                <!-- Teams -->
                <div>
                    <h4 style="color:#fbbf24;font-size:1.1rem;margin-bottom:15px;">Our Teams</h4>
                    <ul style="list-style:none;padding:0;">
                        <?php 
                        $categories = getActiveCategories();
                        foreach ($categories as $cat): 
                        ?>
                            <li style="margin-bottom:8px;">
                                <a href="<?php echo SITE_URL; ?>category.php?slug=<?php echo $cat['slug']; ?>" style="color:#e5e7eb;text-decoration:none;opacity:0.8;">
                                    <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 style="color:#fbbf24;font-size:1.1rem;margin-bottom:15px;">Contact</h4>
                    <ul style="list-style:none;padding:0;">
                        <li style="margin-bottom:8px;opacity:0.8;">
                            <i class="fas fa-envelope"></i> <?php echo getSettings('contact_email') ?: 'info@cledunfc.com'; ?>
                        </li>
                        <li style="margin-bottom:8px;opacity:0.8;">
                            <i class="fas fa-phone"></i> <?php echo getSettings('contact_phone') ?: '+254 700 123 456'; ?>
                        </li>
                        <li style="margin-bottom:8px;opacity:0.8;">
                            <i class="fas fa-map-marker-alt"></i> <?php echo getSettings('stadium_location') ?: 'Farasi Lane Primary School'; ?>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div style="border-top:1px solid rgba(255,255,255,0.1);padding-top:20px;text-align:center;opacity:0.6;font-size:0.85rem;">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved.
            </div>
        </div>
    </footer>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>

    <!-- WhatsApp Floating Button -->
<style>
    .whatsapp-float {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #25D366;
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
        text-decoration: none;
        transition: all 0.3s ease;
        animation: pulse-whatsapp 2s infinite;
    }
    
    .whatsapp-float:hover {
        transform: scale(1.1);
        background: #20b85a;
        box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
    }
    
    .whatsapp-float i {
        font-size: 32px;
    }
    
    .whatsapp-float .tooltip {
        position: absolute;
        right: 70px;
        background: #1a2a6c;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .whatsapp-float .tooltip::after {
        content: '';
        position: absolute;
        right: -8px;
        top: 50%;
        transform: translateY(-50%);
        border-left: 8px solid #1a2a6c;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
    }
    
    .whatsapp-float:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }
    
    @keyframes pulse-whatsapp {
        0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.4); }
        70% { box-shadow: 0 0 0 20px rgba(37, 211, 102, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
    }
    
    @media (max-width: 768px) {
        .whatsapp-float {
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
        }
        .whatsapp-float i {
            font-size: 28px;
        }
        .whatsapp-float .tooltip {
            display: none;
        }
    }
</style>

<a href="https://wa.me/254710339213?text=Hello%20CLEDUN%20FC%21%20I%27d%20like%20to%20know%20more%20about%20the%20club." 
   target="_blank" 
   class="whatsapp-float" 
   aria-label="Chat on WhatsApp">
    <span class="tooltip">Chat with us on WhatsApp</span>
    <i class="fab fa-whatsapp"></i>
</a>
</body>
</html>