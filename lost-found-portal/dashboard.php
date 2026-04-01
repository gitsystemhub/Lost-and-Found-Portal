<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/config.php';

// Handle item resolution
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resolve_item'])) {
    $item_id = $_POST['item_id'];
    
    $stmt = $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$item_id, $_SESSION['user_id']])) {
        $_SESSION['success_message'] = "Item marked as resolved!";
        header("Location: dashboard.php");
        exit();
    }
}

// Get user's reported items
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_items = $stmt->fetchAll();

// Count user's items by status - FIXED VERSION
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$pending_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ? AND status = 'approved'");
$stmt->execute([$_SESSION['user_id']]);
$approved_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ? AND status = 'resolved'");
$stmt->execute([$_SESSION['user_id']]);
$resolved_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VIT Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .resolve-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .resolve-btn:hover {
            background: #218838;
        }
        
        .resolved-badge {
            background: #6c757d;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: inline-block;
        }
    </style>
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
                <li><a href="report-lost.php">Report Lost</a></li>
                <li><a href="report-found.php">Report Found</a></li>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
            <p>Manage your lost and found items</p>
            <div class="cta-buttons">
                <a href="report-lost.php" class="btn btn-primary">Report Lost Item</a>
                <a href="report-found.php" class="btn btn-secondary">Report Found Item</a>
            </div>
        </section>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success" style="max-width: 1200px; margin: 1rem auto; padding: 1rem;">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <section class="recent-items">
            <h3>Your Item Statistics</h3>
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #2c3e50; margin-bottom: 0.5rem;"><?php echo $pending_count; ?></div>
                    <div class="stat-label" style="color: #7f8c8d;">Pending Approval</div>
                </div>
                <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #2c3e50; margin-bottom: 0.5rem;"><?php echo $approved_count; ?></div>
                    <div class="stat-label" style="color: #7f8c8d;">Active Items</div>
                </div>
                <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
                    <div class="stat-number" style="font-size: 2rem; font-weight: bold; color: #2c3e50; margin-bottom: 0.5rem;"><?php echo $resolved_count; ?></div>
                    <div class="stat-label" style="color: #7f8c8d;">Resolved Cases</div>
                </div>
            </div>
        </section>

        <section class="recent-items">
            <h3>Your Reported Items</h3>
            <div class="items-grid">
                <?php if (count($user_items) > 0): ?>
                    <?php foreach($user_items as $item): ?>
                        <div class="item-card">
                            <?php if ($item['image_path']): ?>
                                <img src="<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                            
                            <h4><?php echo htmlspecialchars($item['item_name']); ?></h4>
                            <p><strong>Type:</strong> <?php echo ucfirst($item['item_type']); ?></p>
                            <p><strong>Category:</strong> <?php echo ucfirst($item['category']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                            <p><strong>Date:</strong> <?php echo $item['date_lost_found']; ?></p>
                            <p><strong>Status:</strong> 
                                <span style="
                                    padding: 0.25rem 0.5rem;
                                    border-radius: 4px;
                                    font-weight: bold;
                                    <?php 
                                        if ($item['status'] == 'approved') echo 'background: #d4edda; color: #155724;';
                                        elseif ($item['status'] == 'pending') echo 'background: #fff3cd; color: #856404;';
                                        else echo 'background: #6c757d; color: white;';
                                    ?>
                                ">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </p>
                            <?php if (!empty($item['contact_info'])): ?>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($item['contact_info']); ?></p>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            
                            <!-- Resolve Button - Only show for approved items -->
                            <?php if ($item['status'] == 'approved'): ?>
                                <form method="POST" action="" onsubmit="return confirm('Mark this item as resolved? This will remove it from search results.');">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="resolve_item" class="resolve-btn">
                                        ✅ Mark as Resolved
                                    </button>
                                </form>
                            <?php elseif ($item['status'] == 'resolved'): ?>
                                <div class="resolved-badge">✅ This case has been resolved</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                        <p>You haven't reported any items yet.</p>
                        <a href="report-lost.php" class="btn btn-primary">Report Your First Item</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 VIT University Lost & Found Portal</p>
    </footer>
</body>
</html>