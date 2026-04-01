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

// Get statistics
$pending_count = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'pending'")->fetchColumn();
$approved_count = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'approved'")->fetchColumn();
$resolved_count = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'resolved'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get pending items for approval
$pending_items = $pdo->query("
    SELECT i.*, u.full_name, u.vit_email 
    FROM items i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.status = 'pending' 
    ORDER BY i.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VIT Lost & Found</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .pending-items {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .item-card-admin {
            padding: 1.5rem;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .item-card-admin:last-child {
            border-bottom: none;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .item-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 4px;
            margin-right: 1rem;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-approve {
            background: #27ae60;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-reject {
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .no-pending {
            padding: 2rem;
            text-align: center;
            color: #7f8c8d;
        }
        
        .admin-nav {
            background: #2c3e50;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            margin-right: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .admin-nav a:hover {
            background: #34495e;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
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

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $approved_count; ?></div>
                <div class="stat-label">Approved Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $resolved_count; ?></div>
                <div class="stat-label">Resolved Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <!-- Pending Items for Approval -->
        <h2>Pending Items for Approval</h2>
        <div class="pending-items">
            <?php if (count($pending_items) > 0): ?>
                <?php foreach($pending_items as $item): ?>
                    <div class="item-card-admin">
                        <div class="item-header">
                            <?php if ($item['image_path']): ?>
                                <img src="../<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="item-image">
                            <?php endif; ?>
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                                <p><strong>Type:</strong> <?php echo ucfirst($item['item_type']); ?></p>
                                <p><strong>Category:</strong> <?php echo ucfirst($item['category']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                                <p><strong>Date:</strong> <?php echo $item['date_lost_found']; ?></p>
                                <p><strong>Reported by:</strong> <?php echo htmlspecialchars($item['full_name']); ?> (<?php echo htmlspecialchars($item['vit_email']); ?>)</p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                                <?php if ($item['contact_info']): ?>
                                    <p><strong>Contact Info:</strong> <?php echo htmlspecialchars($item['contact_info']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="item-actions">
                            <form method="POST" action="admin-approve.php" style="display: inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn-approve" onclick="return confirm('Approve this item?')">Approve</button>
                            </form>
                            
                            <form method="POST" action="admin-approve.php" style="display: inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn-reject" onclick="return confirm('Reject this item?')">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-pending">
                    <h3>No pending items for approval</h3>
                    <p>All items have been reviewed and processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>