<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$db = getDB();
$id = intval($_GET['id'] ?? 0);

if ($id) {
    $stmt = $db->prepare("DELETE FROM player_registrations WHERE id=?");
    $stmt->execute([$id]);
    setFlash('success', 'Registration deleted.');
}

header('Location: registrations.php');
exit();