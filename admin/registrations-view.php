<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$id = intval($_GET['id'] ?? 0);
$pageTitle = 'View Registration';

$stmt = $db->prepare("
    SELECT r.*, c.name AS category_name, c.icon AS category_icon
    FROM player_registrations r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) { header('Location: registrations.php'); exit(); }

$stmt = $db->prepare("SELECT * FROM player_registration_contacts WHERE registration_id = ?");
$stmt->execute([$id]);
$contacts = $stmt->fetchAll();

$age = (new DateTime($r['birth_date']))->diff(new DateTime())->y;

require_once 'includes/admin-header.php';
?>

<div class="admin-page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="color:var(--admin-dark);">📝 Registration #<?php echo str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></h2>
        <a href="registrations.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">

        <div>
            <div class="admin-card">
                <h3>👤 General Information</h3>
                <table class="admin-table">
                    <tr><td><strong>Full Name</strong></td><td><?php echo $r['first_name'].' '.$r['last_name']; ?></td></tr>
                    <tr><td><strong>Category</strong></td><td><?php echo $r['category_icon'].' '.$r['category_name']; ?></td></tr>
                    <tr><td><strong>Gender</strong></td><td><?php echo $r['gender']; ?></td></tr>
                    <tr><td><strong>Age</strong></td><td><?php echo $age; ?> years (<?php echo $r['birth_date']; ?>)</td></tr>
                    <tr><td><strong>Nationality</strong></td><td><?php echo $r['nationality']; ?></td></tr>
                    <tr><td><strong>Language</strong></td><td><?php echo $r['language'] ?: '-'; ?></td></tr>
                    <tr><td><strong>School</strong></td><td><?php echo $r['school']; ?></td></tr>
                    <tr><td><strong>Email</strong></td><td><?php echo $r['email'] ?: '-'; ?></td></tr>
                    <tr><td><strong>Phone</strong></td><td><?php echo $r['phone']; ?></td></tr>
                </table>
            </div>

            <div class="admin-card">
                <h3>🚨 Emergency Contacts</h3>
                <?php if (count($contacts) > 0): ?>
                    <table class="admin-table">
                        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Type</th></tr></thead>
                        <tbody>
                        <?php foreach ($contacts as $c): ?>
                            <tr>
                                <td><?php echo $c['first_name'].' '.$c['last_name']; ?></td>
                                <td><?php echo $c['email']; ?></td>
                                <td><?php echo $c['phone']; ?></td>
                                <td><?php echo $c['contact_type']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No emergency contacts listed.</p>
                <?php endif; ?>
            </div>

            <div class="admin-card">
                <h3>🏥 Medical Information</h3>
                <p><strong>Allergies:</strong> <?php echo $r['allergies'] ?: '-'; ?></p>
                <p><strong>Medical Comment:</strong> <?php echo $r['medical_comment'] ?: '-'; ?></p>
            </div>

            <div class="admin-card">
                <h3>📌 Other Information</h3>
                <p><strong>Notify by:</strong> <?php echo $r['notify_by']; ?></p>
                <p><strong>Referral source:</strong> <?php echo $r['referral_source'] ?: '-'; ?></p>
                <p><strong>Signatory:</strong> <?php echo $r['signatory_name'] ?: '-'; ?></p>
                <p><strong>Agreement:</strong> <?php echo $r['agreement_accepted'] ? '✅ Accepted' : '❌ Not accepted'; ?></p>
            </div>
        </div>

        <aside>
            <div class="admin-card">
                <h3>⚡ Actions</h3>
                <p><strong>Status:</strong> 
                    <span class="status-badge <?php echo $r['status']; ?>" style="background:<?php
                        echo $r['status']==='approved'?'#d1fae5':($r['status']==='rejected'?'#fce4ec':'#fef3c7');
                    ?>;color:<?php
                        echo $r['status']==='approved'?'#065f46':($r['status']==='rejected'?'#9a3412':'#92400e');
                    ?>;padding:4px 12px;border-radius:20px;">
                        <?php echo ucfirst($r['status']); ?>
                    </span>
                </p>

                <?php if ($r['status'] === 'pending'): ?>
                    <div style="margin-top:15px;display:flex;flex-direction:column;gap:10px;">
                        <a href="registrations-approve.php?id=<?php echo $r['id']; ?>" class="btn-primary" style="text-align:center;"
                           onclick="return confirm('Approve this registration?');">
                            ✅ Approve & Add to Squad
                        </a>
                        <button type="button" class="btn-danger" onclick="document.getElementById('rejectBox').style.display='block'">
                            ❌ Reject
                        </button>
                    </div>

                    <div id="rejectBox" style="display:none;margin-top:15px;">
                        <form method="POST" action="registrations-reject.php">
                            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                            <div class="form-group">
                                <label>Rejection Reason *</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn-danger" style="width:100%;">Confirm Rejection</button>
                        </form>
                    </div>
                <?php elseif ($r['status'] === 'approved'): ?>
                    <p style="margin-top:10px;color:#065f46;">✅ Approved on <?php echo formatDate($r['approved_at'], 'M j, Y H:i'); ?></p>
                <?php else: ?>
                    <p style="margin-top:10px;color:#9a3412;">❌ Reason: <?php echo $r['rejection_reason']; ?></p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>