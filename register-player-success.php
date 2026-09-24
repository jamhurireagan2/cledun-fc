<?php
require_once 'includes/functions.php';

if (empty($_SESSION['reg_success'])) {
    header('Location: index.php');
    exit();
}
$info = $_SESSION['reg_success'];
unset($_SESSION['reg_success']);

$currentPage = 'register';
$pageTitle = 'Registration Submitted - ' . SITE_NAME;
require_once 'includes/header.php';
?>

<section style="padding:80px 0;text-align:center;">
    <div class="container">
        <div style="max-width:600px;margin:0 auto;background:var(--white);padding:50px 30px;border-radius:var(--radius);box-shadow:var(--shadow);">
            <div style="font-size:5rem;margin-bottom:15px;">🎉</div>
            <h1 style="color:var(--primary);margin-bottom:15px;">Registration Submitted!</h1>
            <p style="color:var(--gray-text);font-size:1.05rem;line-height:1.8;margin-bottom:25px;">
                Thank you for registering <strong><?php echo $info['name']; ?></strong> with CLEDUN FC.<br>
                Your application reference is <strong>#<?php echo str_pad($info['id'], 6, '0', STR_PAD_LEFT); ?></strong>.
            </p>
            <div style="background:#dbeafe;padding:20px;border-radius:8px;text-align:left;font-size:0.95rem;margin-bottom:25px;">
                <strong>📌 What happens next?</strong>
                <ol style="margin-top:10px;padding-left:20px;line-height:1.9;">
                    <li>Our admin team will review your application.</li>
                    <li>You will be contacted via the details provided.</li>
                    <li>Once approved, the player will be officially added to the team.</li>
                </ol>
            </div>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="index.php" class="btn btn-primary">🏠 Back to Home</a>
                <a href="squad.php" class="btn btn-secondary">👥 View Squad</a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>