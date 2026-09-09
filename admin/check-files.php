<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

echo "<h1>File Check</h1>";

$files = [
    'admin/gallery.php' => '../admin/gallery.php',
    'admin/includes/admin-header.php' => '../admin/includes/admin-header.php',
    'admin/includes/admin-footer.php' => '../admin/includes/admin-footer.php',
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<p style='color:green;'>✅ $name exists</p>";
    } else {
        echo "<p style='color:red;'>❌ $name NOT found at: $path</p>";
    }
}

echo "<h2>admin/includes/ folder contents:</h2>";
$dir = '../admin/includes/';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red;'>Folder not found!</p>";
}
?>