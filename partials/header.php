<?php
/*
 * Header Partial - Reusable Header Component
 * Included at the top of every page
 * Features: Logo, Navigation, Search Bar, Cart Count
 */

// Start session if not already started (for cart tracking)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate total cart items
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>minishop - Your Online Store</title>
    
    <!-- Link to our CSS stylesheet -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/minishop/assets/css/style.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo/Brand Name -->
                <div class="logo">
                    <a href="/minishop/index.php">
                        <span class="logo-icon">🛒</span>
                        minishop
                    </a>
                </div>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="/minishop/shop.php" method="GET" class="search-form">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search products..." 
                            class="search-input"
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        >
                        <button type="submit" class="search-button">
                            🔍
                        </button>
                    </form>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="main-nav">
                    <ul>
                        <li>
                            <a href="/minishop/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="/minishop/shop.php" class="<?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>">
                                Shop
                            </a>
                        </li>
                        <li>
                            <a href="/minishop/cart.php" class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
                                 Cart
                                <?php if($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="/minishop/admin/login.php" class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">Admin</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <!-- Main Content Area Starts Here -->
    <main class="main-content">