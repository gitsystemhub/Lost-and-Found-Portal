<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

include '../includes/config.php';

// Get all users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

// Count items per user
$user_stats = [];
foreach ($users as $user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $user_stats[$user['id']] = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin | VIT Lost & Found</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .admin-nav { background: #2c3e50; padding: 1rem; margin-bottom: 2rem; border-radius: 8px; }
        .admin-nav a { color: white; text-decoration: none; margin-right: 1.5rem; padding: 0.5rem 1rem; border-radius: 4px; }
        .admin-nav a:hover { background: #34495e; }
        .users-table { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .table-header { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 100px; gap: 1rem; padding: 1rem; background: #34495e; color: white; font-weight: bold; }
        .table-row { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 100px; gap: 1rem; padding: 1rem; border-bottom: 1px solid #ecf0f1; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage Users</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="admin-logout.php" class="btn btn-secondary" style="margin-left: 1rem;">Logout</a>
            </div>
        </div>

        <div class="admin-nav">
            <a href="admin-dashboard.php">Dashboard</a>
            <a href="admin-items.php">Manage All Items</a>
            <a href="admin-users.php">Manage Users</a>
        </div>

        <div class="users-table">
            <div class="table-header">
                <div>Name</div>
                <div>Email</div>
                <div>Role</div>
                <div>Joined Date</div>
                <div>Items</div>
            </div>

            <?php if (count($users) > 0): ?>
                <?php foreach($users as $user): ?>
                    <div class="table-row">
                        <div><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div><?php echo htmlspecialchars($user['vit_email']); ?></div>
                        <div><?php echo ucfirst($user['role']); ?></div>
                        <div><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                        <div><?php echo $user_stats[$user['id']] ?? 0; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 2rem; text-align: center; color: #6c757d;">
                    <h3>No users found</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>