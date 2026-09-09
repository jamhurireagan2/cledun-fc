<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

echo "<h1>Reset Admin Password</h1>";

try {
    $db = getDB();
    
    // Generate a new password hash for 'admin123'
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p>New password hash generated: <code>" . $hash . "</code></p>";
    
    // Delete existing admin
    $stmt = $db->prepare("DELETE FROM users WHERE username = 'admin'");
    $stmt->execute();
    echo "<p>✅ Old admin user deleted</p>";
    
    // Insert new admin with correct hash
    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@cledunfc.com', $hash, 'CLEDUN FC Admin', 'admin']);
    echo "<p>✅ New admin user created with password: <strong>admin123</strong></p>";
    
    // Verify it works
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify('admin123', $user['password_hash'])) {
        echo "<p style='color:green;font-size:1.2rem;'>✅ SUCCESS! Password 'admin123' works correctly!</p>";
        echo "<p><a href='admin/login.php' style='font-size:1.1rem;'>Click here to login →</a></p>";
    } else {
        echo "<p style='color:red;'>❌ Something went wrong. Please try again.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>