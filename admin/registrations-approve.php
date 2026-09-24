<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$id = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM player_registrations WHERE id = ? AND status = 'pending'");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) { header('Location: registrations.php'); exit(); }

try {
    $db->beginTransaction();

    // Add to players table
    $stmt = $db->prepare("
        INSERT INTO players (registration_id, category_id, full_name, position, nationality, date_of_birth, bio, is_active)
        VALUES (?, ?, ?, 'CM', ?, ?, 'Registered via online application.', 1)
    ");
    $stmt->execute([
        $r['id'],
        $r['category_id'],
        $r['first_name'].' '.$r['last_name'],
        $r['nationality'],
        $r['birth_date']
    ]);

    // Update registration
    $stmt = $db->prepare("UPDATE player_registrations SET status='approved', approved_at=NOW(), approved_by=? WHERE id=?");
    $stmt->execute([$_SESSION['user_id'], $id]);

    $db->commit();
    setFlash('success', 'Player approved and added to the squad!');
} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Failed to approve: ' . $e->getMessage());
}

header('Location: registrations.php?status=approved');
exit();