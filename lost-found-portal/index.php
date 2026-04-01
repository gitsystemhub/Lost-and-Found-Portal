<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIT Lost & Found Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>VIT Lost & Found</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Search Items</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Find Your Lost Belongings</h2>
            <p>Report lost items or help return found items to their owners</p>
            <div class="cta-buttons">
                <a href="report-lost.php" class="btn btn-primary">Report Lost Item</a>
                <a href="report-found.php" class="btn btn-secondary">Report Found Item</a>
            </div>
        </section>

        <section class="recent-items">
            <h3>Recently Reported Items</h3>
            <div class="items-grid">
                <!-- PHP code to display recent approved items -->
                <?php
// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'includes/config.php';
include 'includes/functions.php';
// ... rest of your code
                include 'includes/config.php';
                $stmt = $pdo->prepare("SELECT * FROM items WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
                $stmt->execute();
                $items = $stmt->fetchAll();
                
                foreach($items as $item) {
                    echo "<div class='item-card'>";
                    if ($item['image_path']) {
                        echo "<img src='{$item['image_path']}' alt='{$item['item_name']}'>";
                    } else {
                        echo "<div class='no-image'>No Image</div>";
                    }
                    echo "<h4>{$item['item_name']}</h4>";
                    echo "<p><strong>Type:</strong> " . ucfirst($item['item_type']) . "</p>";
                    echo "<p><strong>Location:</strong> {$item['location']}</p>";
                    echo "<p><strong>Date:</strong> {$item['date_lost_found']}</p>";
                    // Display contact information if available
                    if (!empty($item['contact_info'])) {
                        echo "<p><strong>Contact:</strong> {$item['contact_info']}</p>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 VIT University Lost & Found Portal</p>
    </footer>
</body>
</html>