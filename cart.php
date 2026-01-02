<?php
/*
 * Shopping Cart - cart.php
 * Complete cart management with sessions: add, update, remove items
 * Displays all cart items with product details from database
 */

// Start session to access cart data
session_start();

// Include database connection
include 'config/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize messages
$success_message = '';
$error_message = '';

// ============================================
// HANDLE CART ACTIONS
// ============================================

// ACTION: Add product to cart (GET - from shop/product page)
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Get product details from database to validate
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check if product is in stock
        if ($product['stock'] > 0) {
            // Check if product already in cart
            if (isset($_SESSION['cart'][$product_id])) {
                // Increase quantity if not exceeding stock
                if ($_SESSION['cart'][$product_id] < $product['stock']) {
                    $_SESSION['cart'][$product_id]++;
                    $success_message = "Product quantity updated in cart!";
                } else {
                    $error_message = "Cannot add more. Maximum stock limit (" . $product['stock'] . ") reached.";
                }
            } else {
                // Add new product to cart
                $_SESSION['cart'][$product_id] = 1;
                $success_message = "\"" . $product['name'] . "\" added to cart successfully!";
            }
        } else {
            $error_message = "Sorry, this product is out of stock.";
        }
    } else {
        $error_message = "Product not found.";
    }
    $stmt->close();
}

// ACTION: Add product to cart (POST - from product details page with quantity)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Get product details from database
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check stock availability
        if ($product['stock'] >= $quantity) {
            // Check if product already in cart
            if (isset($_SESSION['cart'][$product_id])) {
                $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
                
                // Check if new quantity exceeds stock
                if ($new_quantity <= $product['stock']) {
                    $_SESSION['cart'][$product_id] = $new_quantity;
                    $success_message = "Cart updated! \"" . $product['name'] . "\" quantity: " . $new_quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $product['stock'];
                    $error_message = "Added maximum available stock (" . $product['stock'] . " items).";
                }
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
                $success_message = $quantity . " × \"" . $product['name'] . "\" added to cart!";
            }
        } else {
            $error_message = "Sorry, only " . $product['stock'] . " items available in stock.";
        }
    } else {
        $error_message = "Product not found.";
    }
    $stmt->close();
}

// ACTION: Update cart quantities (POST)
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        $updates_made = 0;
        
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = intval($quantity);
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                unset($_SESSION['cart'][$product_id]);
                $updates_made++;
            } else {
                // Get product stock to validate
                $sql = "SELECT stock, name FROM products WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    
                    // Update quantity but don't exceed stock
                    if ($quantity <= $product['stock']) {
                        $_SESSION['cart'][$product_id] = $quantity;
                        $updates_made++;
                    } else {
                        $_SESSION['cart'][$product_id] = $product['stock'];
                        $error_message = "Some quantities adjusted to available stock.";
                        $updates_made++;
                    }
                }
                $stmt->close();
            }
        }
        
        if ($updates_made > 0) {
            $success_message = "Cart updated successfully!";
        }
    }
}

// ACTION: Remove single item from cart (GET)
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $success_message = "Product removed from cart.";
    }
}

// ACTION: Clear entire cart (GET)
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['cart'] = [];
    $success_message = "Cart cleared successfully!";
}

// ============================================
// FETCH CART ITEMS WITH PRODUCT DETAILS FROM DATABASE
// ============================================

$cart_items = [];
$cart_total = 0;
$total_items = 0;

if (!empty($_SESSION['cart'])) {
    // Get all product IDs from cart
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $product_ids));
    
    // Fetch product details for all cart items from database
    $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($ids_string)";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($product = $result->fetch_assoc()) {
            $product_id = $product['id'];
            $quantity = $_SESSION['cart'][$product_id];
            
            // Calculate line total (subtotal) for this product
            $line_total = $product['price'] * $quantity;
            
            // Build cart item array with all product info
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'stock' => $product['stock'],
                'quantity' => $quantity,
                'line_total' => $line_total
            ];
            
            // Add to cart total
            $cart_total += $line_total;
            $total_items += $quantity;
        }
    }
}

// Calculate shipping
$shipping_cost = ($cart_total >= 50) ? 0 : 5.99;
$grand_total = $cart_total + $shipping_cost;

// Include header
include 'partials/header.php';
?>

<!-- Shopping Cart Page -->
<section class="cart-section">
    <div class="container">
        <!-- Page Title -->
        <h1 class="page-title">🛒 Shopping Cart</h1>
        
        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
        <div class="alert alert-success">
            ✓ <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="alert alert-error">
            ⚠ <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
        
        <!-- Cart Has Items -->
        <form action="/minishop/cart.php" method="POST" class="cart-form">
            <input type="hidden" name="action" value="update">
            
            <div class="cart-layout">
                <!-- Cart Items Section -->
                <div class="cart-items-section">
                    <h2 class="section-heading">Cart Items (<?php echo $total_items; ?>)</h2>
                    
                    <div class="cart-items-list">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item-card">
                            <!-- Product Image -->
                            <div class="cart-item-image">
                                <a href="/minishop/product.php?id=<?php echo $item['id']; ?>">
                                    <img 
                                        src="/minishop/assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        onerror="this.src='/minishop/assets/images/placeholder.jpg'"
                                    >
                                </a>
                            </div>
                            
                            <!-- Product Details -->
                            <div class="cart-item-details">
                                <h3 class="cart-item-name">
                                    <a href="/minishop/product.php?id=<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h3>
                                
                                <p class="cart-item-price">
                                    Price: <strong>$<?php echo number_format($item['price'], 2); ?></strong>
                                </p>
                                
                                <p class="cart-item-stock">
                                    <?php if ($item['stock'] < 10 && $item['stock'] > 0): ?>
                                        <span class="stock-warning">⚠ Only <?php echo $item['stock']; ?> left in stock</span>
                                    <?php elseif ($item['stock'] > 0): ?>
                                        <span class="stock-ok">✓ In stock</span>
                                    <?php else: ?>
                                        <span class="stock-out">✗ Out of stock</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <!-- Quantity Control -->
                            <div class="cart-item-quantity">
                                <label>Quantity:</label>
                                <div class="quantity-control-inline">
                                    <input 
                                        type="number" 
                                        name="quantities[<?php echo $item['id']; ?>]" 
                                        value="<?php echo $item['quantity']; ?>" 
                                        min="1" 
                                        max="<?php echo $item['stock']; ?>"
                                        class="quantity-input-cart"
                                    >
                                    <span class="stock-max">Max: <?php echo $item['stock']; ?></span>
                                </div>
                            </div>
                            
                            <!-- Line Total -->
                            <div class="cart-item-total">
                                <label>Subtotal:</label>
                                <p class="line-total-amount">
                                    $<?php echo number_format($item['line_total'], 2); ?>
                                </p>
                                <p class="line-calculation">
                                    (<?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?>)
                                </p>
                            </div>
                            
                            <!-- Remove Button -->
                            <div class="cart-item-remove">
                                <a 
                                    href="/minishop/cart.php?action=remove&id=<?php echo $item['id']; ?>" 
                                    class="btn-remove-item"
                                    onclick="return confirm('Remove \"<?php echo htmlspecialchars($item['name']); ?>\" from cart?')"
                                    title="Remove item"
                                >
                                    🗑️ Remove
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Cart Action Buttons -->
                    <div class="cart-actions-bar">
                        <button type="submit" class="btn btn-primary">
                            💾 Update Cart
                        </button>
                        <a href="/minishop/shop.php" class="btn btn-outline">
                            ← Continue Shopping
                        </a>
                        <a 
                            href="/minishop/cart.php?action=clear" 
                            class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to clear your entire cart?')"
                        >
                            🗑️ Clear Cart
                        </a>
                    </div>
                </div>

                <!-- Cart Summary Sidebar -->
                <aside class="cart-summary-sidebar">
                    <div class="cart-summary-box">
                        <h3 class="summary-title">Order Summary</h3>
                        
                        <!-- Items Count -->
                        <div class="summary-row">
                            <span class="summary-label">Items in Cart:</span>
                            <span class="summary-value"><?php echo $total_items; ?></span>
                        </div>
                        
                        <!-- Subtotal -->
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value">$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        
                        <!-- Shipping -->
                        <div class="summary-row">
                            <span class="summary-label">Shipping:</span>
                            <span class="summary-value">
                                <?php if ($shipping_cost == 0): ?>
                                    <span class="free-shipping">FREE</span>
                                <?php else: ?>
                                    $<?php echo number_format($shipping_cost, 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <!-- Free Shipping Progress -->
                        <?php if ($cart_total < 50): ?>
                        <div class="shipping-progress">
                            <p class="shipping-message">
                                💡 Add <strong>$<?php echo number_format(50 - $cart_total, 2); ?></strong> more for FREE shipping!
                            </p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo min(($cart_total / 50) * 100, 100); ?>%"></div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="shipping-progress">
                            <p class="shipping-achieved">
                                ✓ You've qualified for FREE shipping!
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-divider"></div>
                        
                        <!-- Grand Total -->
                        <div class="summary-row summary-total-row">
                            <span class="summary-label-total">Total:</span>
                            <span class="summary-value-total">$<?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        
                        <!-- Checkout Button -->
                        <a href="/minishop/checkout.php" class="btn btn-checkout">
                            Proceed to Checkout →
                        </a>
                        
                        <!-- Security Badges -->
                        <div class="security-info">
                            <p>🔒 Secure Checkout</p>
                            <p>✓ Safe Payment Processing</p>
                            <p>↩️ Easy Returns</p>
                        </div>
                    </div>
                    
                    <!-- Promo Info Box -->
                    <div class="promo-box">
                        <h4>💰 Special Offers</h4>
                        <ul>
                            <li>Free shipping on orders over $50</li>
                            <li>30-day money-back guarantee</li>
                            <li>24/7 customer support</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </form>

        <?php else: ?>
        
        <!-- Empty Cart Message -->
        <div class="empty-cart-container">
            <div class="empty-cart-icon">🛒</div>
            <h2 class="empty-cart-title">Your Cart is Empty</h2>
            <p class="empty-cart-text">Looks like you haven't added any products to your cart yet.</p>
            <p class="empty-cart-text">Start shopping and discover amazing products!</p>
            
            <div class="empty-cart-actions">
                <a href="/minishop/shop.php" class="btn btn-primary btn-large">
                    🛍️ Start Shopping
                </a>
                <a href="/minishop/index.php" class="btn btn-outline btn-large">
                    ← Back to Home
                </a>
            </div>
            
            <!-- Featured Categories -->
            <div class="quick-links">
                <h3>Shop by Category</h3>
                <div class="category-quick-links">
                    <a href="/minishop/shop.php?category=1" class="category-badge">💻 Electronics</a>
                    <a href="/minishop/shop.php?category=2" class="category-badge">👕 Clothing</a>
                    <a href="/minishop/shop.php?category=3" class="category-badge">📚 Books</a>
                    <a href="/minishop/shop.php?category=4" class="category-badge">🏡 Home & Garden</a>
                    <a href="/minishop/shop.php?category=5" class="category-badge">⚽ Sports</a>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>
</section>

<?php
// Close database connection
$conn->close();

// Include footer
include 'partials/footer.php';
?>