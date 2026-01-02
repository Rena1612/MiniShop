<?php
/*
 * Checkout Page - checkout.php (WITH TELEGRAM NOTIFICATIONS)
 * Collects customer information and processes orders
 * Sends Telegram notification on successful order
 */

// Start session
session_start();

// Include database connection
include 'config/db.php';

// Include Telegram configuration
include 'config/telegram.php';

// Initialize variables
$order_placed = false;
$order_id = 0;
$telegram_sent = false;
$errors = [];
$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

// ============================================
// CHECK IF CART IS EMPTY
// ============================================
$cart_is_empty = !isset($_SESSION['cart']) || empty($_SESSION['cart']);

// ============================================
// FETCH CART ITEMS FROM DATABASE
// ============================================
$cart_items = [];
$cart_total = 0;
$total_items = 0;

if (!$cart_is_empty) {
    // Get product IDs from cart
    $product_ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $product_ids));
    
    // Fetch product details
    $sql = "SELECT id, name, price, stock FROM products WHERE id IN ($ids_string)";
    $result = $conn->query($sql);
    
    while ($product = $result->fetch_assoc()) {
        $product_id = $product['id'];
        $quantity = $_SESSION['cart'][$product_id];
        
        // Validate stock availability
        if ($quantity > $product['stock']) {
            $quantity = $product['stock'];
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        $subtotal = $product['price'] * $quantity;
        
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        
        $cart_total += $subtotal;
        $total_items += $quantity;
    }
    
    // Calculate shipping
    $shipping_cost = ($cart_total >= 50) ? 0 : 5.99;
    $grand_total = $cart_total + $shipping_cost;
}

// ============================================
// PROCESS CHECKOUT FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$cart_is_empty) {
    
    // Get form data and trim whitespace
    $form_data['name'] = trim($_POST['customer_name'] ?? '');
    $form_data['email'] = trim($_POST['customer_email'] ?? '');
    $form_data['phone'] = trim($_POST['customer_phone'] ?? '');
    $form_data['address'] = trim($_POST['customer_address'] ?? '');
    
    // ============================================
    // VALIDATE FORM FIELDS
    // ============================================
    
    // Validate Name
    if (empty($form_data['name'])) {
        $errors[] = "Full name is required.";
    } elseif (strlen($form_data['name']) < 3) {
        $errors[] = "Name must be at least 3 characters long.";
    }
    
    // Validate Email
    if (empty($form_data['email'])) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Validate Phone
    if (empty($form_data['phone'])) {
        $errors[] = "Phone number is required.";
    } elseif (strlen($form_data['phone']) < 10) {
        $errors[] = "Please enter a valid phone number.";
    }
    
    // Validate Address
    if (empty($form_data['address'])) {
        $errors[] = "Delivery address is required.";
    } elseif (strlen($form_data['address']) < 10) {
        $errors[] = "Please enter a complete delivery address.";
    }
    
    // ============================================
    // PROCESS ORDER IF NO ERRORS
    // ============================================
    if (empty($errors)) {
        
        // Start transaction for data consistency
        $conn->begin_transaction();
        
        try {
            // Insert into orders table
            $insert_order_sql = "INSERT INTO orders (customer_name, customer_email, customer_phone, customer_address, total_amount, created_at) 
                                 VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($insert_order_sql);
            $stmt->bind_param("ssssd", 
                $form_data['name'], 
                $form_data['email'], 
                $form_data['phone'], 
                $form_data['address'], 
                $grand_total
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order.");
            }
            
            // Get the inserted order ID
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Insert each cart item into order_items table
            $insert_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES (?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_item_sql);
            
            foreach ($cart_items as $item) {
                $stmt->bind_param("iiid", 
                    $order_id, 
                    $item['id'], 
                    $item['quantity'], 
                    $item['price']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to add order items.");
                }
            }
            
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // ============================================
            // SEND TELEGRAM NOTIFICATION
            // ============================================
            $telegram_message = formatOrderMessage($order_id, $form_data, $cart_items, $grand_total);
            $telegram_sent = sendTelegramNotification($telegram_message);
            
            // Log notification status (optional)
            if ($telegram_sent) {
                error_log("Telegram notification sent successfully for Order #{$order_id}");
            } else {
                error_log("Failed to send Telegram notification for Order #{$order_id}");
            }
            
            // Clear the cart session
            $_SESSION['cart'] = [];
            
            // Mark order as placed
            $order_placed = true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Order processing failed. Please try again.";
            $errors[] = $e->getMessage();
        }
    }
}

// Include header
include 'partials/header.php';
?>

<!-- Checkout Page -->
<section class="checkout-section">
    <div class="container">
        
        <?php if ($order_placed): ?>
        
        <!-- ORDER SUCCESS MESSAGE -->
        <div class="order-success">
            <div class="success-icon">✓</div>
            <h1 class="success-title">Thank You for Your Order!</h1>
            <p class="success-message">Your order has been placed successfully.</p>
            
            <!-- Telegram Notification Status -->
            <?php if ($telegram_sent): ?>
            <div class="telegram-status telegram-success">
                ✅ Telegram notification sent! We've been notified of your order.
            </div>
            <?php else: ?>
            <div class="telegram-status telegram-info">
                ℹ️ Order saved successfully. Notification will be processed shortly.
            </div>
            <?php endif; ?>
            
            <div class="order-details-box">
                <h3>Order Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($form_data['name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($form_data['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value total-amount">$<?php echo number_format($grand_total, 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y, g:i a'); ?></span>
                </div>
            </div>
            
            <div class="order-items-summary">
                <h3>Items Ordered</h3>
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td><strong>$<?php echo number_format($cart_total, 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Shipping:</strong></td>
                            <td><strong><?php echo ($shipping_cost == 0) ? 'FREE' : '$' . number_format($shipping_cost, 2); ?></strong></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($grand_total, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="success-message-box">
                <p>📧 A confirmation email has been sent to <strong><?php echo htmlspecialchars($form_data['email']); ?></strong></p>
                <p>📦 Your order will be processed and shipped within 2-3 business days.</p>
                <p>💳 You will receive payment instructions via email shortly.</p>
            </div>
            
            <div class="success-actions">
                <a href="/minishop/index.php" class="btn btn-primary btn-large">
                    ← Back to Home
                </a>
                <a href="/minishop/shop.php" class="btn btn-outline btn-large">
                    Continue Shopping
                </a>
            </div>
        </div>
        
        <?php elseif ($cart_is_empty): ?>
        
        <!-- EMPTY CART MESSAGE -->
        <div class="empty-cart-checkout">
            <div class="empty-icon">🛒</div>
            <h2>Your Cart is Empty</h2>
            <p>You need to add items to your cart before proceeding to checkout.</p>
            <div class="empty-actions">
                <a href="/minishop/shop.php" class="btn btn-primary btn-large">
                    Browse Products
                </a>
                <a href="/minishop/index.php" class="btn btn-outline btn-large">
                    Go to Homepage
                </a>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- CHECKOUT FORM -->
        <h1 class="page-title">Checkout</h1>
        
        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>⚠ Please correct the following errors:</strong>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="checkout-layout">
            
            <!-- Billing Information Form -->
            <div class="checkout-form-section">
                <h2 class="section-title">Billing Information</h2>
                
                <form action="/minishop/checkout.php" method="POST" class="checkout-form">
                    
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="customer_name" class="form-label">
                            Full Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="customer_name" 
                            name="customer_name" 
                            class="form-input"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($form_data['name']); ?>"
                            required
                        >
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="customer_email" class="form-label">
                            Email Address <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="customer_email" 
                            name="customer_email" 
                            class="form-input"
                            placeholder="your.email@example.com"
                            value="<?php echo htmlspecialchars($form_data['email']); ?>"
                            required
                        >
                        <small class="form-help">Order confirmation will be sent to this email</small>
                    </div>
                    
                    <!-- Phone -->
                    <div class="form-group">
                        <label for="customer_phone" class="form-label">
                            Phone Number <span class="required">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="customer_phone" 
                            name="customer_phone" 
                            class="form-input"
                            placeholder="(123) 456-7890"
                            value="<?php echo htmlspecialchars($form_data['phone']); ?>"
                            required
                        >
                        <small class="form-help">For delivery contact purposes</small>
                    </div>
                    
                    <!-- Address -->
                    <div class="form-group">
                        <label for="customer_address" class="form-label">
                            Delivery Address <span class="required">*</span>
                        </label>
                        <textarea 
                            id="customer_address" 
                            name="customer_address" 
                            class="form-textarea"
                            rows="4"
                            placeholder="Enter your complete delivery address including street, city, state, and ZIP code"
                            required
                        ><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                        <small class="form-help">Please provide a complete address for accurate delivery</small>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success btn-large btn-block">
                        🛒 Place Order ($<?php echo number_format($grand_total, 2); ?>)
                    </button>
                    
                    <div class="form-note">
                        <p>By placing your order, you agree to our terms and conditions.</p>
                        <p>📱 You will receive a Telegram notification when your order is confirmed.</p>
                    </div>
                    
                </form>
            </div>
            
            <!-- Order Summary Sidebar -->
            <aside class="order-summary-sidebar">
                <div class="order-summary-box">
                    <h3 class="summary-title">Order Summary</h3>
                    
                    <!-- Cart Items List -->
                    <div class="summary-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span class="item-quantity">× <?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="item-price">$<?php echo number_format($item['subtotal'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <!-- Totals -->
                    <div class="summary-row">
                        <span>Subtotal (<?php echo $total_items; ?> items):</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>
                            <?php if ($shipping_cost == 0): ?>
                                <span class="free-shipping">FREE</span>
                            <?php else: ?>
                                $<?php echo number_format($shipping_cost, 2); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-total">
                        <span>Total:</span>
                        <span class="total-amount">$<?php echo number_format($grand_total, 2); ?></span>
                    </div>
                    
                    <!-- Edit Cart Link -->
                    <a href="/minishop/cart.php" class="btn btn-outline btn-block">
                        ← Edit Cart
                    </a>
                </div>
                
                <!-- Security Info -->
                <div class="security-box">
                    <h4>🔒 Secure Checkout</h4>
                    <ul>
                        <li>✓ Safe & secure payment</li>
                        <li>✓ Your data is protected</li>
                        <li>✓ 30-day money-back guarantee</li>
                        <li>✓ Free returns</li>
                        <li>✓ Instant Telegram notifications</li>
                    </ul>
                </div>
            </aside>
            
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