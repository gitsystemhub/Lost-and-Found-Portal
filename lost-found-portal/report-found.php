<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'includes/config.php';
include 'includes/functions.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($_POST['item_name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $location = trim($_POST['location']);
    $date_found = $_POST['date_found'];
    $contact_info = trim($_POST['contact_info']);
    
    // Basic validation
    if (empty($item_name) || empty($description) || empty($location) || empty($date_found)) {
        $error = "Please fill in all required fields";
    } else {
        // Handle image upload
        $image_path = null;
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
            $image_path = uploadImage($_FILES['item_image']);
            if (!$image_path) {
                $error = "Failed to upload image. Please try again.";
            }
        }
        
        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO items (user_id, item_type, item_name, description, category, location, date_lost_found, image_path, contact_info) VALUES (?, 'found', ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $item_name, $description, $category, $location, $date_found, $image_path, $contact_info])) {
                    $success = "Found item reported successfully! It will be visible after admin approval.";
                    
                    // Clear form fields
                    $item_name = $description = $location = $date_found = $contact_info = '';
                } else {
                    $error = "Failed to report item. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Found Item - VIT Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-help {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-top: 0.25rem;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 0.5rem;
            display: none;
            border: 2px dashed #3498db;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="form-container">
        <h2>Report Found Item</h2>
        <p class="form-help">Help return lost items to their owners by reporting items you've found on campus.</p>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" id="foundItemForm">
            <div class="form-group">
                <label class="required">Item Name:</label>
                <input type="text" name="item_name" value="<?php echo htmlspecialchars($item_name ?? ''); ?>" required 
                       placeholder="e.g., Black Wallet, iPhone 13, Water Bottle">
                <div class="form-help">Be specific about the item name</div>
            </div>
            
            <div class="form-group">
                <label class="required">Description:</label>
                <textarea name="description" rows="4" required placeholder="Describe the item in detail including color, brand, distinguishing features, contents (if any)..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                <div class="form-help">Include details like color, brand, size, and any unique features</div>
            </div>
            
            <div class="form-group">
                <label class="required">Category:</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="electronics" <?php echo (isset($category) && $category == 'electronics') ? 'selected' : ''; ?>>Electronics</option>
                    <option value="books" <?php echo (isset($category) && $category == 'books') ? 'selected' : ''; ?>>Books & Notebooks</option>
                    <option value="clothing" <?php echo (isset($category) && $category == 'clothing') ? 'selected' : ''; ?>>Clothing</option>
                    <option value="accessories" <?php echo (isset($category) && $category == 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                    <option value="documents" <?php echo (isset($category) && $category == 'documents') ? 'selected' : ''; ?>>Documents & IDs</option>
                    <option value="bags" <?php echo (isset($category) && $category == 'bags') ? 'selected' : ''; ?>>Bags & Backpacks</option>
                    <option value="stationery" <?php echo (isset($category) && $category == 'stationery') ? 'selected' : ''; ?>>Stationery</option>
                    <option value="other" <?php echo (isset($category) && $category == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="required">Location Found:</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($location ?? ''); ?>" required 
                       placeholder="e.g., Main Library, Canteen 2, AB1 201 Classroom">
                <div class="form-help">Be specific about where you found the item</div>
            </div>
            
            <div class="form-group">
                <label class="required">Date Found:</label>
                <input type="date" name="date_found" value="<?php echo $date_found ?? ''; ?>" required max="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>Contact Information:</label>
                <input type="text" name="contact_info" value="<?php echo htmlspecialchars($contact_info ?? ''); ?>" 
                       placeholder="e.g., Room No, Phone Number (optional)">
                <div class="form-help">How the owner can contact you to claim the item</div>
            </div>
            
            <div class="form-group">
                <label>Item Image:</label>
                <input type="file" name="item_image" id="item_image" accept="image/*">
                <div class="form-help">Upload a clear photo of the item (max 5MB, JPG/PNG/JPEG)</div>
                <img id="image_preview" class="image-preview" src="#" alt="Image Preview">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Report Found Item</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('item_image').addEventListener('change', function(e) {
            const preview = document.getElementById('image_preview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Form validation
        document.getElementById('foundItemForm').addEventListener('submit', function(e) {
            const dateFound = document.querySelector('input[name="date_found"]');
            const today = new Date().toISOString().split('T')[0];
            
            if (dateFound.value > today) {
                e.preventDefault();
                alert('Date found cannot be in the future');
                dateFound.focus();
            }
        });
    </script>
</body>
</html>