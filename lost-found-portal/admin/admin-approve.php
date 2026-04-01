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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $stmt = $pdo->prepare("UPDATE items SET status = 'approved' WHERE id = ?");
        $stmt->execute([$item_id]);
        $_SESSION['admin_message'] = "Item approved successfully!";
    } elseif ($action == 'reject') {
        $stmt = $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ?");
        $stmt->execute([$item_id]);
        $_SESSION['admin_message'] = "Item rejected and marked as resolved!";
    }
}

header("Location: admin-dashboard.php");
exit();
?>