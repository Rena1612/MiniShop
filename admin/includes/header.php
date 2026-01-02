<?php
// Make sure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$admin = getAdminData();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MiniShop</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="admin-navbar">
        <div class="navbar-brand">
            <h2>🛒 MiniShop Admin</h2>
        </div>
        <div class="navbar-menu">
            <div class="navbar-user">
                <span>Welcome, <strong><?php echo htmlspecialchars($admin['full_name']); ?></strong></span>
                <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar Navigation -->
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="products.php" class="<?php echo ($current_page == 'products.php' || $current_page == 'add_product.php' || $current_page == 'edit_product.php') ? 'active' : ''; ?>">
                        📦 Products
                    </a>
                </li>
                <li>
                    <a href="categories.php" class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>">
                        📂 Categories
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="<?php echo ($current_page == 'orders.php' || $current_page == 'view_order.php') ? 'active' : ''; ?>">
                        🛒 Orders
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                        ⚙️ Settings
                    </a>
                </li>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="../index.php" target="_blank">
                        🏪 View Store
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content Area -->
        <main class="admin-content">