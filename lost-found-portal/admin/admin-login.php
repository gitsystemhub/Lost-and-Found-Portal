<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-dashboard.php");
    exit();
}

include '../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VIT Lost & Found</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .admin-header {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        
        .admin-note {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1><a href="../index.php" style="color: white; text-decoration: none;">VIT Lost & Found</a></h1>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../search.php">Search Items</a></li>
                <li><a href="admin-login.php">Admin Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="admin-login-container">
        <div class="admin-header">
            <h2>Admin Login</h2>
            <p>Access the administration panel</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-note">
            <strong>Note:</strong> This area is restricted to authorized administrators only.
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login as Admin</button>
        </form>
    </div>
</body>
</html>