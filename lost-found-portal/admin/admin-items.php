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

// Handle item actions (approve, reject, delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE items SET status = 'approved' WHERE id = ?");
        $stmt->execute([$item_id]);
        $_SESSION['admin_message'] = "Item approved successfully!";
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ?");
        $stmt->execute([$item_id]);
        $_SESSION['admin_message'] = "Item rejected!";
    } elseif ($action == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $_SESSION['admin_message'] = "Item deleted successfully!";
    }
    
    header("Location: admin-items.php");
    exit();
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Build the query with filters
$sql = "SELECT i.*, u.full_name, u.vit_email FROM items i JOIN users u ON i.user_id = u.id";
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(i.item_name LIKE ? OR i.description LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $conditions[] = "i.status = ?";
    $params[] = $status_filter;
}

if (!empty($type_filter)) {
    $conditions[] = "i.item_type = ?";
    $params[] = $type_filter;
}

// Add WHERE clause if there are conditions
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY i.created_at DESC";

// Execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage All Items - Admin | VIT Lost & Found</title>
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
        
        .items-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            display: grid;
            grid-template-columns: 100px 1fr 120px 120px 150px 120px 150px;
            gap: 1rem;
            padding: 1rem;
            background: #34495e;
            color: white;
            font-weight: bold;
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 100px 1fr 120px 120px 150px 120px 150px;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #ecf0f1;
            align-items: start;
        }
        
        .table-row:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; padding: 0.25rem 0.5rem; border-radius: 4px; }
        .status-approved { background: #d4edda; color: #155724; padding: 0.25rem 0.5rem; border-radius: 4px; }
        .status-resolved { background: #f8d7da; color: #721c24; padding: 0.25rem 0.5rem; border-radius: 4px; }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; color: white; }
        
        .search-filter {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .clear-filters {
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Manage All Items</h1>
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

        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="success"><?php echo $_SESSION['admin_message']; unset($_SESSION['admin_message']); ?></div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="search-filter">
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label>Search:</label>
                    <input type="text" name="search" placeholder="Item name, description, or user" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Type:</label>
                    <select name="type">
                        <option value="">All Types</option>
                        <option value="lost" <?php echo $type_filter == 'lost' ? 'selected' : ''; ?>>Lost</option>
                        <option value="found" <?php echo $type_filter == 'found' ? 'selected' : ''; ?>>Found</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
            
            <?php if (!empty($search) || !empty($status_filter) || !empty($type_filter)): ?>
                <div class="clear-filters">
                    <a href="admin-items.php" class="btn btn-secondary">Clear All Filters</a>
                    <small style="margin-left: 1rem; color: #666;">
                        Showing <?php echo count($items); ?> item(s)
                        <?php if (!empty($search)) echo " for '$search'"; ?>
                        <?php if (!empty($status_filter)) echo " with status '$status_filter'"; ?>
                        <?php if (!empty($type_filter)) echo " of type '$type_filter'"; ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>

        <!-- Items Table -->
        <div class="items-table">
            <div class="table-header">
                <div>Image</div>
                <div>Item Details</div>
                <div>Type</div>
                <div>Category</div>
                <div>Reported By</div>
                <div>Status</div>
                <div>Actions</div>
            </div>

            <?php if (count($items) > 0): ?>
                <?php foreach($items as $item): ?>
                    <div class="table-row">
                        <div>
                            <?php if ($item['image_path']): ?>
                                <img src="../<?php echo $item['image_path']; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" class="item-image">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #6c757d; border-radius: 4px;">No Image</div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                            <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($item['description']); ?></p>
                            <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.9rem;">
                                <strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?><br>
                                <strong>Date:</strong> <?php echo $item['date_lost_found']; ?><br>
                                <?php if (!empty($item['contact_info'])): ?>
                                    <strong>Contact:</strong> <?php echo htmlspecialchars($item['contact_info']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div><?php echo ucfirst($item['item_type']); ?></div>
                        <div><?php echo ucfirst($item['category']); ?></div>
                        <div>
                            <?php echo htmlspecialchars($item['full_name']); ?><br>
                            <small><?php echo htmlspecialchars($item['vit_email']); ?></small>
                        </div>
                        <div>
                            <span class="status-<?php echo $item['status']; ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </div>
                        <div class="action-buttons">
                            <?php if ($item['status'] == 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-small btn-approve" onclick="return confirm('Approve this item?')">Approve</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-small btn-reject" onclick="return confirm('Reject this item?')">Reject</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-small btn-delete" onclick="return confirm('Delete this item permanently?')">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 2rem; text-align: center; color: #6c757d;">
                    <h3>No items found</h3>
                    <p>
                        <?php if (!empty($search) || !empty($status_filter) || !empty($type_filter)): ?>
                            No items match your filter criteria. <a href="admin-items.php">Clear filters</a> to see all items.
                        <?php else: ?>
                            There are no items in the system yet.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>