<?php
// Load config first
require_once 'config.php';

// Then load database
require_once 'database.php';

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function getSettings($key) {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function getPositionBadge($position) {
    $badges = [
        'GK' => 'badge-gk',
        'RB' => 'badge-defender',
        'CB' => 'badge-defender',
        'LB' => 'badge-defender',
        'CDM' => 'badge-midfielder',
        'CM' => 'badge-midfielder',
        'CAM' => 'badge-midfielder',
        'RW' => 'badge-forward',
        'LW' => 'badge-forward',
        'CF' => 'badge-forward',
        'ST' => 'badge-forward'
    ];
    return $badges[$position] ?? 'badge-default';
}

function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getActiveCategories() {
    $db = getDB();
    return $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY id")->fetchAll();
}

function getCategoryById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCategoryBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}
?>