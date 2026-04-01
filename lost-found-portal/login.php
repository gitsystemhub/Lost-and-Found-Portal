<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'includes/config.php';
include 'includes/functions.php';

// Debug: Check if files are included
if (!function_exists('validateVITEmail')) {
    die("❌ functions.php not loaded properly");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    echo "Debug: Processing login for: " . htmlspecialchars($email) . "<br>";
    
    if (validateVITEmail($email)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE vit_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "Debug: User found, verifying password...<br>";
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['vit_email'];
                $_SESSION['user_name'] = $user['full_name'];
                
                echo "Debug: Login successful, redirecting...<br>";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
                echo "Debug: Password verification failed<br>";
            }
        } else {
            $error = "User not found";
            echo "Debug: User not found in database<br>";
        }
    } else {
        $error = "Please use a valid VIT email address";
        echo "Debug: Invalid VIT email<br>";
    }
    
    // If we reach here, there was an error
    echo "Debug: Login failed - " . $error . "<br>";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login - VIT Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h2>Login to Your Account</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>VIT Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>