<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

echo "<h1>Menu Debug</h1>";
echo "<p>Current file: " . __FILE__ . "</p>";
echo "<p>Looking for Gallery link in admin-header.php...</p>";

$filePath = __DIR__ . '/includes/admin-header.php';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    if (strpos($content, 'gallery.php') !== false) {
        echo "<p style='color:green;'>✅ Gallery link FOUND in admin-header.php!</p>";
        echo "<p>Line found: <code>" . htmlspecialchars(substr($content, strpos($content, 'gallery.php'), 100)) . "</code></p>";
    } else {
        echo "<p style='color:red;'>❌ Gallery link NOT found in admin-header.php!</p>";
        echo "<p>You need to add: <code>&lt;li&gt;&lt;a href=\"gallery.php\"&gt;&lt;i class=\"fas fa-images\"&gt;&lt;/i&gt; Gallery&lt;/a&gt;&lt;/li&gt;</code></p>";
    }
} else {
    echo "<p style='color:red;'>❌ admin-header.php not found at: $filePath</p>";
}

echo "<h2>Full Menu from admin-header.php:</h2>";
if (file_exists($filePath)) {
    // Extract the menu section
    $content = file_get_contents($filePath);
    preg_match('/<ul class="menu">(.*?)<\/ul>/s', $content, $matches);
    if (isset($matches[1])) {
        echo "<pre style='background:#f3f4f6;padding:15px;border-radius:8px;overflow:auto;max-height:400px;'>";
        echo htmlspecialchars($matches[1]);
        echo "</pre>";
    } else {
        echo "<p>Could not find menu section</p>";
    }
}
?>