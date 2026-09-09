<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'User not found. Please check your username.';
            } else {
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    
                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid password. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* RESET - Full page centering */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0d1b3e 0%, #1a2a6c 50%, #2a3f8a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        /* Login Card - Perfectly Centered */
        .login-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 45px 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 30px 80px rgba(0,0,0,0.5);
            position: relative;
            margin: 0 auto;
        }
        
        /* Login Header */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header .logo {
            font-size: 3.5rem;
            margin-bottom: 8px;
            display: block;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a2a6c;
            letter-spacing: -0.5px;
        }
        
        .login-header h1 span {
            color: #fbbf24;
        }
        
        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        
        /* Form Fields */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .form-group label i {
            color: #1a2a6c;
            width: 20px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #f9fafb;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #fbbf24;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.15);
        }
        
        .form-group input::placeholder {
            color: #9ca3af;
        }
        
        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #fbbf24;
            color: #1a2a6c;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            margin-top: 5px;
        }
        
        .btn-login:hover {
            background: #fcd34d;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(251, 191, 36, 0.35);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        /* Error Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border-left: 4px solid #ef4444;
            background: #fef2f2;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert i {
            font-size: 1.1rem;
        }
        
        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #6b7280;
            font-size: 0.85rem;
        }
        
        .login-footer a {
            color: #1a2a6c;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .login-footer a:hover {
            color: #fbbf24;
        }
        
        .credentials-box {
            background: #f3f4f6;
            padding: 14px 18px;
            border-radius: 12px;
            margin-top: 12px;
            font-size: 0.85rem;
            line-height: 1.8;
            border: 1px solid #e5e7eb;
        }
        
        .credentials-box strong {
            color: #1a2a6c;
        }
        
        .credentials-box code {
            background: #ffffff;
            padding: 2px 10px;
            border-radius: 6px;
            font-weight: 700;
            color: #1a2a6c;
            border: 1px solid #e5e7eb;
            font-size: 0.8rem;
        }
        
        .credentials-box .small-text {
            color: #6b7280;
            font-size: 0.75rem;
            display: block;
            margin-top: 4px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .login-header .logo {
                font-size: 2.8rem;
            }
            
            .credentials-box {
                font-size: 0.75rem;
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <span class="logo">🏆</span>
            <h1>CLEDUN <span>FC</span></h1>
            <p>Admin Panel Login</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="on">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username or Email
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Enter username or email" 
                    required 
                    autofocus
                    autocomplete="username"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password" 
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="login-footer">
            <div class="credentials-box">
                <strong>🔑 Login Credentials</strong><br>
                Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code>
                <span class="small-text">📧 Email: admin@cledunfc.com</span>
            </div>
            <p style="margin-top:14px;">
                <a href="<?php echo SITE_URL; ?>">
                    <i class="fas fa-arrow-left"></i> Back to Website
                </a>
            </p>
        </div>
    </div>
</body>
</html>