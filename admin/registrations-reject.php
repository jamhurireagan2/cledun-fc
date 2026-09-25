<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/notifications.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$id = intval($_POST['id'] ?? 0);
$reason = sanitize($_POST['reason'] ?? '');

if ($id && $reason) {
    $stmt = $db->prepare("SELECT * FROM player_registrations WHERE id=? AND status='pending'");
    $stmt->execute([$id]);
    $r = $stmt->fetch();

    if ($r) {
        $stmt = $db->prepare("UPDATE player_registrations SET status='rejected', rejection_reason=? WHERE id=?");
        $stmt->execute([$reason, $id]);

        $result = notifyRegistrationRejected($r, $reason);

        $msg = '❌ Registration rejected.';
        if ($result['email']) $msg .= ' 📧 Email sent.';
        else                 $msg .= ' ⚠️ Email failed.';

        setFlash('success', $msg);
    }
}

header('Location: registrations.php');
exit();