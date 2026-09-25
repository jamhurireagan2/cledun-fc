<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$id = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT r.*, c.name AS category_name
    FROM player_registrations r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE r.id = ? AND r.status = 'pending'
");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) { header('Location: registrations.php'); exit(); }

try {
    $db->beginTransaction();

    // Add to players
    $stmt = $db->prepare("
        INSERT INTO players (registration_id, category_id, full_name, position, nationality, date_of_birth, bio, is_active)
        VALUES (?, ?, ?, 'CM', ?, ?, 'Registered via online application.', 1)
    ");
    $stmt->execute([
        $r['id'],
        $r['category_id'],
        $r['first_name'] . ' ' . $r['last_name'],
        $r['nationality'],
        $r['birth_date']
    ]);

    // Update registration status
    $stmt = $db->prepare("UPDATE player_registrations SET status='approved', approved_at=NOW(), approved_by=? WHERE id=?");
    $stmt->execute([$_SESSION['user_id'], $id]);

    $db->commit();

    // Send email notification
    $result = notifyRegistrationApproved($r, $r['category_name']);

    $msg = '✅ Player approved and added to the squad!';
    if ($result['email']) $msg .= ' 📧 Email sent to player.';
    else                 $msg .= ' ⚠️ Email could not be sent.';

    setFlash('success', $msg);

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Failed to approve: ' . $e->getMessage());
}

header('Location: registrations.php?status=approved');
exit();