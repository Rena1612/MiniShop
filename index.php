<?php
/*
 * Homepage - index.php
 * Displays hero section and featured products from database
 */

// Start session for cart functionality
session_start();

// Include database connection
include 'config/db.php';

// Include header
include 'partials/header.php';

// Query to get 6 featured products from database
// We'll prioritize products marked as featured, then get recent products
$sql = "SELECT * FROM products WHERE featured = 1 ORDER BY id DESC LIMIT 6";
$result = $conn->query($sql);

// If we don't have 6 featured products, get any 6 products
if ($result->num_rows < 6) {
    $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 6";
    $result = $conn->query($sql);
}
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-content">
    <span class="hero-tag">Welcome to MiniShop</span>
    <h1 class="hero-title">Everything You Need.<strong> Discover More</strong></h1>
    <p class="hero-subtitle">A Modern MarketPlace Built for a better Price, better Value, And a better Experience</p>
    <p class="hero-description">Ship faster with and more reliable.</p>
    <div class="hero-buttons">
      <button class="hero-btn-primary"><a href="/minishop/shop.php">Get Started <span class="hero-btn-arrow">→</span></a></button>
      <button class="hero-btn-secondary">Learn More</button>
    </div>
  </div>
  <div class="hero-scroll">
    <span>Welcome</span>
    <div class="hero-scroll-line"></div>
  </div>
</section>

<!-- Featured Products Section -->
<section class="featured-products">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Check out our handpicked selection of amazing products</p>
        </div>
        
        <!-- Product Grid -->
        <div class="product-grid">
            <?php
            // Check if products exist
            if ($result->num_rows > 0) {
                // Loop through each product and display it
                while ($product = $result->fetch_assoc()) {
                    ?>
                    <div class="product-card">
                        <!-- Product Image -->
                        <div class="product-image-container">
                            <img 
                                src="/minishop/assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="product-image"
                                onerror="this.src='/minishop/assets/images/placeholder.jpg'"
                            >
                            <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
                                <span class="badge badge-warning">Only <?php echo $product['stock']; ?> left!</span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="product-info">
                            <h3 class="product-name">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            
                            <p class="product-description">
                                <?php 
                                // Show first 80 characters of description
                                $description = htmlspecialchars($product['description']);
                                echo (strlen($description) > 80) ? substr($description, 0, 80) . '...' : $description;
                                ?>
                            </p>
                            
                            <div class="product-footer">
                                <p class="product-price">
                                    $<?php echo number_format($product['price'], 2); ?>
                                </p>
                                
                                <a 
                                    href="/minishop/product.php?id=<?php echo $product['id']; ?>" 
                                    class="btn btn-primary btn-small"
                                >
                                    View Details →
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // No products found
                ?>
                <div class="no-products">
                    <p> No products available at the moment.</p>
                    <p>Please check back later!</p>
                </div>
                <?php
            }
            ?>
        </div>
        
        <!-- View All Products Button -->
        <div class="section-footer">
            <a href="/minishop/shop.php" class="btn btn-outline btn-large">
                View All Products →
            </a>
        </div>
    </div>
</section>

<!-- Why Shop With Us Section -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Why Shop With Us?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3>Fast Shipping</h3>
                <p>Get your orders delivered quickly and safely to your doorstep.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>Secure Payment</h3>
                <p>Shop with confidence using our secure payment system.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">↩️</div>
                <h3>Easy Returns</h3>
                <p>Not satisfied? Return your purchase within 30 days hassle-free.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3>Quality Products</h3>
                <p>We only sell authentic, high-quality products you can trust.</p>
            </div>
        </div>
    </div>
</section>

<?php
// Close database connection
$conn->close();

// Include footer
include 'partials/footer.php';
?>