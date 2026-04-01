<?php
include 'includes/config.php';

$search_query = "";
$category_filter = "";
$location_filter = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $search_query = $_GET['search'] ?? '';
    $category_filter = $_GET['category'] ?? '';
    $location_filter = $_GET['location'] ?? '';
    
    $sql = "SELECT * FROM items WHERE status = 'approved'";
    $params = [];
    
    if (!empty($search_query)) {
        $sql .= " AND (item_name LIKE ? OR description LIKE ?)";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
    }
    
    if (!empty($category_filter)) {
        $sql .= " AND category = ?";
        $params[] = $category_filter;
    }
    
    if (!empty($location_filter)) {
        $sql .= " AND location LIKE ?";
        $params[] = "%$location_filter%";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} else {
    // Show all approved items by default
    $stmt = $pdo->prepare("SELECT * FROM items WHERE status = 'approved' ORDER BY created_at DESC");
    $stmt->execute();
    $items = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Items - VIT Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h2>Search Lost & Found Items</h2>
        
        <form method="GET" action="" class="search-form">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search by item name or description" value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            
            <div class="form-group">
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="electronics" <?php echo $category_filter == 'electronics' ? 'selected' : ''; ?>>Electronics</option>
                    <option value="books" <?php echo $category_filter == 'books' ? 'selected' : ''; ?>>Books</option>
                    <option value="clothing" <?php echo $category_filter == 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                    <option value="accessories" <?php echo $category_filter == 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                    <option value="documents" <?php echo $category_filter == 'documents' ? 'selected' : ''; ?>>Documents</option>
                </select>
            </div>
            
            <div class="form-group">
                <input type="text" name="location" placeholder="Filter by location" value="<?php echo htmlspecialchars($location_filter); ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        
        <div class="items-grid">
            <?php if (count($items) > 0): ?>
                <?php foreach($items as $item): ?>
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
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if (!empty($item['contact_info'])): ?>
                            <p><strong>Contact:</strong> <?php echo htmlspecialchars($item['contact_info']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No items found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>