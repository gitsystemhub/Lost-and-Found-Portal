<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h3>Debug Login Process</h3>";

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=vit_lost_found", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful<br>";
} catch(PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit();
}

// Test if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "✅ Form submitted via POST<br>";
    
    $email = $_POST['email'] ?? 'Not set';
    $password = $_POST['password'] ?? 'Not set';
    
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Password length: " . strlen($password) . "<br>";
    
    // Check VIT email validation
    if (preg_match('/@vit\.ac\.in$/', $email)) {
        echo "✅ VIT email validation passed<br>";
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE vit_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ User found in database<br>";
            echo "User ID: " . $user['id'] . "<br>";
            echo "Stored password hash: " . substr($user['password'], 0, 20) . "...<br>";
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                echo "✅ Password verification successful<br>";
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['vit_email'];
                $_SESSION['user_name'] = $user['full_name'];
                
                echo "Session variables set:<br>";
                echo "- user_id: " . $_SESSION['user_id'] . "<br>";
                echo "- user_email: " . $_SESSION['user_email'] . "<br>";
                echo "- user_name: " . $_SESSION['user_name'] . "<br>";
                
                // Test redirect
                echo "✅ Ready to redirect to dashboard.php<br>";
                header("Location: dashboard.php");
                exit();
            } else {
                echo "❌ Password verification failed<br>";
            }
        } else {
            echo "❌ User not found in database<br>";
        }
    } else {
        echo "❌ VIT email validation failed<br>";
    }
} else {
    echo "Form not submitted via POST<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Login</title>
</head>
<body>
    <h3>Login Form</h3>
    <form method="POST" action="">
        <div>
            <label>Email:</label>
            <input type="email" name="email" required value="test@vit.ac.in">
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" required value="password123">
        </div>
        <button type="submit">Login with Debug</button>
    </form>
</body>
</html>