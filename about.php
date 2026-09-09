<?php
require_once 'includes/functions.php';

$currentPage = 'about';
$pageTitle = 'About - ' . SITE_NAME;

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>ℹ️ About CLEDUN FC</h1>
                <p style="opacity: 0.8; font-size: 1.1rem;">Building champions since <?php echo getSettings('club_established') ?: '2026'; ?></p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section style="padding: 50px 0;">
    <div class="container">
        
        <!-- Vision & Mission -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-bottom:50px;">
            <div style="background:var(--white);padding:40px;border-radius:var(--radius);border-left:5px solid var(--secondary);box-shadow:var(--shadow);">
                <div style="font-size:3rem;margin-bottom:12px;">👁️</div>
                <h2 style="color:var(--primary);font-size:1.8rem;margin-bottom:12px;">Our Vision</h2>
                <p style="color:var(--gray-text);line-height:1.8;font-size:1.05rem;">
                    To participate in world youth football tournaments globally, both as a team and through individual player representation. 
                    We aim to put CLEDUN FC on the international stage and create opportunities for young athletes to showcase their talent worldwide.
                </p>
            </div>
            <div style="background:var(--white);padding:40px;border-radius:var(--radius);border-left:5px solid #3b82f6;box-shadow:var(--shadow);">
                <div style="font-size:3rem;margin-bottom:12px;">🎯</div>
                <h2 style="color:var(--primary);font-size:1.8rem;margin-bottom:12px;">Our Mission</h2>
                <p style="color:var(--gray-text);line-height:1.8;font-size:1.05rem;">
                    To nurture and foster young talent while building self-confidence, discipline, and character in young athletes. 
                    We develop complete players for life, both on and off the pitch.
                </p>
            </div>
        </div>
        
        <!-- Holistic Player Development -->
        <div style="background:var(--white);padding:40px;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:50px;">
            <h2 style="color:var(--primary);font-size:1.8rem;margin-bottom:20px;text-align:center;">🌱 Holistic Player Development</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
                <div style="text-align:center;padding:20px;background:var(--light-bg);border-radius:var(--radius);">
                    <div style="font-size:2rem;margin-bottom:8px;">💪</div>
                    <h4 style="color:var(--primary);">Physical Development</h4>
                </div>
                <div style="text-align:center;padding:20px;background:var(--light-bg);border-radius:var(--radius);">
                    <div style="font-size:2rem;margin-bottom:8px;">🧠</div>
                    <h4 style="color:var(--primary);">Mental Strength & Discipline</h4>
                </div>
                <div style="text-align:center;padding:20px;background:var(--light-bg);border-radius:var(--radius);">
                    <div style="font-size:2rem;margin-bottom:8px;">❤️</div>
                    <h4 style="color:var(--primary);">Emotional Well-being</h4>
                </div>
                <div style="text-align:center;padding:20px;background:var(--light-bg);border-radius:var(--radius);">
                    <div style="font-size:2rem;margin-bottom:8px;">🌟</div>
                    <h4 style="color:var(--primary);">Character Building & Values</h4>
                </div>
                <div style="text-align:center;padding:20px;background:var(--light-bg);border-radius:var(--radius);">
                    <div style="font-size:2rem;margin-bottom:8px;">🌍</div>
                    <h4 style="color:var(--primary);">Life Skills Beyond Football</h4>
                </div>
            </div>
        </div>
        
        <!-- Academy Focus -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:30px;margin-bottom:50px;">
            <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-bottom:4px solid var(--secondary);">
                <div style="font-size:2.5rem;margin-bottom:12px;">👥</div>
                <h3>Team & Individual Growth</h3>
            </div>
            <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-bottom:4px solid var(--secondary);">
                <div style="font-size:2.5rem;margin-bottom:12px;">💚</div>
                <h3>Player Well-being</h3>
            </div>
            <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-bottom:4px solid var(--secondary);">
                <div style="font-size:2.5rem;margin-bottom:12px;">📈</div>
                <h3>Long-term Athlete Development</h3>
            </div>
        </div>
        
        <!-- Reason for Establishment -->
        <div style="background:var(--white);padding:40px;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:50px;">
            <h2 style="color:var(--primary);font-size:1.8rem;margin-bottom:20px;text-align:center;">🏗️ Reason for Establishment</h2>
            <ul style="list-style:none;padding:0;display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
                <li style="background:var(--light-bg);padding:20px;border-radius:var(--radius);text-align:center;border-left:3px solid var(--secondary);">
                    <div style="font-size:1.5rem;margin-bottom:8px;">💪</div>
                    Build confidence and self-awareness in young athletes
                </li>
                <li style="background:var(--light-bg);padding:20px;border-radius:var(--radius);text-align:center;border-left:3px solid var(--secondary);">
                    <div style="font-size:1.5rem;margin-bottom:8px;">⚽</div>
                    Provide professional training at grassroots level
                </li>
                <li style="background:var(--light-bg);padding:20px;border-radius:var(--radius);text-align:center;border-left:3px solid var(--secondary);">
                    <div style="font-size:1.5rem;margin-bottom:8px;">🌟</div>
                    Create positive role models and opportunities
                </li>
            </ul>
        </div>
        
        <!-- Training Methodology -->
        <div style="margin-bottom:50px;">
            <h2 style="color:var(--primary);font-size:1.8rem;margin-bottom:20px;text-align:center;">🏋️ Training Methodology</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;">
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">⚡</div>
                    <h4 style="color:var(--primary);">Ball Mastery</h4>
                </div>
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">🏃</div>
                    <h4 style="color:var(--primary);">Speed & Agility</h4>
                </div>
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">🎯</div>
                    <h4 style="color:var(--primary);">Positional Play</h4>
                </div>
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">💪</div>
                    <h4 style="color:var(--primary);">Strength & Conditioning</h4>
                </div>
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">❤️</div>
                    <h4 style="color:var(--primary);">Cardio & Plyometrics</h4>
                </div>
                <div style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);text-align:center;">
                    <div style="font-size:2rem;margin-bottom:8px;">🤝</div>
                    <h4 style="color:var(--primary);">Mobility & Social Interaction</h4>
                </div>
            </div>
        </div>
        
        <!-- Roadmap -->
        <div style="margin-top:40px;">
            <h2 style="color:var(--primary);font-size:1.8rem;margin-bottom:20px;text-align:center;">🗺️ Our Roadmap</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px;">
                <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-left:4px solid var(--secondary);">
                    <div style="font-size:2.5rem;font-weight:800;color:var(--secondary);">2030</div>
                    <h4 style="margin:10px 0 4px;">🏆 Norway Cup</h4>
                    <p style="font-size:0.85rem;opacity:0.7;">U10 & U12 Teams</p>
                </div>
                <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-left:4px solid var(--secondary);">
                    <div style="font-size:2.5rem;font-weight:800;color:var(--secondary);">2031</div>
                    <h4 style="margin:10px 0 4px;">🏆 Gothia Cup</h4>
                    <p style="font-size:0.85rem;opacity:0.7;">U15 Team</p>
                </div>
                <div style="background:var(--primary);color:var(--white);padding:30px;border-radius:var(--radius);text-align:center;border-left:4px solid var(--secondary);">
                    <div style="font-size:2.5rem;font-weight:800;color:var(--secondary);">2035</div>
                    <h4 style="margin:10px 0 4px;">🏟️ Own Facility</h4>
                    <p style="font-size:0.85rem;opacity:0.7;">Players in National Teams</p>
                </div>
            </div>
        </div>
        
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>