<?php
/*
 * Product Details Page - product.php
 * Displays detailed information about a single product
 */

// Start session for cart functionality
session_start();

// Include database connection
include 'config/db.php';

// Include header
include 'partials/header.php';

// Initialize variables
$product = null;
$error_message = '';
$related_products = [];

// Check if product ID is provided and valid
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = intval($_GET['id']); // Convert to integer for security
    
    // Prepare SQL statement to get product details
    $sql = "SELECT p.*, c.name as category_name, c.id as category_id 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if product exists
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Get related products from the same category (excluding current product)
        $related_sql = "SELECT * FROM products 
                        WHERE category_id = ? AND id != ? 
                        ORDER BY RAND() 
                        LIMIT 4";
        $related_stmt = $conn->prepare($related_sql);
        $related_stmt->bind_param("ii", $product['category_id'], $product_id);
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        
        while ($related = $related_result->fetch_assoc()) {
            $related_products[] = $related;
        }
        $related_stmt->close();
    } else {
        $error_message = "Product not found. The product may have been removed or the link is invalid.";
    }
    
    $stmt->close();
} else {
    $error_message = "No product ID provided. Please select a product from the shop.";
}
?>

<?php if ($error_message): ?>
    <!-- Error Message -->
    <section class="error-section">
        <div class="container">
            <div class="error-box">
                <div class="error-icon">❌</div>
                <h2>Product Not Found</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
                <div class="error-actions">
                    <a href="/minishop/shop.php" class="btn btn-primary">Browse All Products</a>
                    <a href="/minishop/index.php" class="btn btn-outline">Go to Homepage</a>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>
    <!-- Product Details Section -->
    <section class="product-details-section">
        <div class="container">
            <!-- Breadcrumb Navigation -->
            <nav class="breadcrumb">
                <a href="/minishop/index.php">Home</a>
                <span class="separator">›</span>
                <a href="/minishop/shop.php">Shop</a>
                <span class="separator">›</span>
                <a href="/minishop/shop.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
                <span class="separator">›</span>
                <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>

            <!-- Product Details Grid -->
            <div class="product-details-grid">
                
                <!-- Product Image -->
                <div class="product-image-section">
                    <div class="product-main-image">
                        <img 
                            src="/minishop/assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            onerror="this.src='/minishop/assets/images/placeholder.jpg'"
                        >
                        <?php if ($product['stock'] == 0): ?>
                            <div class="out-of-stock-overlay">
                                <span>OUT OF STOCK</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="product-info-section">
                    <!-- Category Badge -->
                    <div class="product-category">
                        <a href="/minishop/shop.php?category=<?php echo $product['category_id']; ?>">
                            📂 <?php echo htmlspecialchars($product['category_name']); ?>
                        </a>
                    </div>

                    <!-- Product Name -->
                    <h1 class="product-detail-name">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <!-- Product Price -->
                    <div class="product-price-section">
                        <p class="product-detail-price">
                            $<?php echo number_format($product['price'], 2); ?>
                        </p>
                    </div>

                    <!-- Stock Status -->
                    <div class="stock-status">
                        <?php if ($product['stock'] > 10): ?>
                            <span class="stock-badge in-stock">
                                ✓ In Stock (<?php echo $product['stock']; ?> available)
                            </span>
                        <?php elseif ($product['stock'] > 0): ?>
                            <span class="stock-badge low-stock">
                                ⚠ Only <?php echo $product['stock']; ?> left in stock!
                            </span>
                        <?php else: ?>
                            <span class="stock-badge out-of-stock">
                                ✗ Out of Stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Product Description -->
                    <div class="product-description-section">
                        <h3>Product Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <!-- Add to Cart Form -->
                    <?php if ($product['stock'] > 0): ?>
                    <form action="/minishop/cart.php" method="POST" class="add-to-cart-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div class="quantity-section">
                            <label for="quantity" class="quantity-label">Quantity:</label>
                            <div class="quantity-control">
                                <button type="button" class="qty-btn qty-minus" onclick="decreaseQuantity()">−</button>
                                <input 
                                    type="number" 
                                    id="quantity" 
                                    name="quantity" 
                                    value="1" 
                                    min="1" 
                                    max="<?php echo $product['stock']; ?>"
                                    class="quantity-input"
                                    required
                                >
                                <button type="button" class="qty-btn qty-plus" onclick="increaseQuantity()">+</button>
                            </div>
                            <span class="quantity-hint">Maximum: <?php echo $product['stock']; ?></span>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary btn-large btn-add-to-cart">
                                🛒 Add to Cart
                            </button>
                            <a href="/myshop/shop.php" class="btn btn-outline btn-large">
                                ← Continue Shopping
                            </a>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="out-of-stock-message">
                        <p>😔 This product is currently out of stock.</p>
                        <a href="/myshop/shop.php" class="btn btn-outline btn-large">
                            Browse Other Products
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Product Features -->
                    <div class="product-features">
                        <h3>Why Buy From Us?</h3>
                        <ul class="features-list">
                            <li>✓ Free shipping on orders over $50</li>
                            <li>✓ 30-day money-back guarantee</li>
                            <li>✓ Secure payment processing</li>
                            <li>✓ 24/7 customer support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products Section -->
    <?php if (!empty($related_products)): ?>
    <section class="related-products-section">
        <div class="container">
            <h2 class="section-title">You May Also Like</h2>
            <div class="product-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <a href="/minishop/product.php?id=<?php echo $related['id']; ?>">
                            <img 
                                src="/minishop/assets/images/<?php echo htmlspecialchars($related['image']); ?>" 
                                alt="<?php echo htmlspecialchars($related['name']); ?>"
                                class="product-image"
                                onerror="this.src='/minishop/assets/images/placeholder.jpg'"
                            >
                        </a>
                        <?php if ($related['stock'] < 10 && $related['stock'] > 0): ?>
                            <span class="badge badge-warning">Only <?php echo $related['stock']; ?> left!</span>
                        <?php elseif ($related['stock'] == 0): ?>
                            <span class="badge badge-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="/minishop/product.php?id=<?php echo $related['id']; ?>">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="product-footer">
                            <p class="product-price">
                                $<?php echo number_format($related['price'], 2); ?>
                            </p>
                            
                            <a 
                                href="/minishop/product.php?id=<?php echo $related['id']; ?>" 
                                class="btn btn-primary btn-small"
                            >
                                View Details →
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php endif; ?>

<!-- JavaScript for Quantity Controls -->
<script>
function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const minValue = parseInt(input.min);
    
    if (currentValue > minValue) {
        input.value = currentValue - 1;
    }
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    
    if (currentValue < maxValue) {
        input.value = currentValue + 1;
    }
}

// Prevent manual input outside min/max range
document.getElementById('quantity')?.addEventListener('change', function() {
    const min = parseInt(this.min);
    const max = parseInt(this.max);
    let value = parseInt(this.value);
    
    if (value < min) this.value = min;
    if (value > max) this.value = max;
});
</script>

<?php
// Close database connection
$conn->close();

// Include footer
include 'partials/footer.php';
?>