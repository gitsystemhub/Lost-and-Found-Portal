<?php
// includes/header.php
?>
<header>

    <nav>
        
        <div class="logo">
            <h1><a href="index.php" style="color: white; text-decoration: none;">VIT Lost & Found</a></h1>
        </div>
        <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="search.php">Search Items</a></li>
    <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="report-lost.php">Report Lost</a></li>
        <li><a href="report-found.php">Report Found</a></li>
        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
    <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
    <!--admin login link -->
    <li><a href="admin/admin-login.php">Admin</a></li>
</ul>
    </nav>
</header>