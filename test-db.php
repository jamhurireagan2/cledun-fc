<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

echo "<h1>Database Connection Test</h1>";

try {
    $db = getDB();
    echo "<p style='color:green;'>✅ Database connected successfully!</p>";
    
    // Check users table
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h2>Users in Database:</h2>";
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Password Hash</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td style='font-size:10px;'>" . substr($user['password_hash'], 0, 30) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>❌ No users found in database!</p>";
    }
    
    // Test password verification
    echo "<h2>Password Test:</h2>";
    $testPassword = 'admin123';
    $hashedPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    if (password_verify($testPassword, $hashedPassword)) {
        echo "<p style='color:green;'>✅ Password 'admin123' matches the hash!</p>";
    } else {
        echo "<p style='color:red;'>❌ Password 'admin123' does NOT match the hash!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>