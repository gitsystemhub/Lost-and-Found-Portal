<?php
include '../includes/config.php';

// Create admin table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create default admin user (run this once, then delete the file)
$username = 'admin';
$password = 'admin123'; // Change this!
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    echo "Admin user created successfully!<br>";
    echo "Username: $username<br>";
    echo "Password: $password<br>";
    echo "<strong>Delete this file after use for security!</strong>";
} catch (PDOException $e) {
    echo "Admin user already exists or error: " . $e->getMessage();
}
?>