<?php
function uploadImage($file) {
    $target_dir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return null;
    }
    
    // Validate file size (5MB max)
    if ($file["size"] > 5000000) {
        return null;
    }
    
    // Generate unique filename
    $filename = time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function validateVITEmail($email) {
    return preg_match('/@vitstudent\.ac\.in$/', $email);
}

?>