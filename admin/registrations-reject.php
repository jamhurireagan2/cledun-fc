<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$id = intval($_POST['id'] ?? 0);
$reason = sanitize($_POST['reason'] ?? '');

if ($id && $reason) {
    $stmt = $db->prepare("UPDATE player_registrations SET status='rejected', rejection_reason=? WHERE id=?");
    $stmt->execute([$reason, $id]);
    setFlash('success', 'Registration rejected.');
}

header('Location: registrations.php');
exit();