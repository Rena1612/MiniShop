<?php
/*
 * Shop Page - shop.php
 * Displays all products with search and category filtering
 */

// Start session for cart functionality
session_start();

// Include database connection
include 'config/db.php';

// Include header
include 'partials/header.php';

// Initialize variables for filtering
$search_query = '';
$category_id = '';
$where_conditions = [];
$params = [];
$types = '';

// Check if search parameter exists
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $where_conditions[] = "(p.name LIKE ? OR description LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Check if category filter exists
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $where_conditions[] = "category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

// Build the SQL query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";

// Add WHERE clause if there are conditions
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY p.id DESC";

// Prepare and execute the statement
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Get all categories for filter dropdown
$categories_sql = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_sql);

// Count total products found
$total_products = $result->num_rows;
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">
            <?php 
            if (!empty($search_query)) {
                echo 'Search Results for "' . htmlspecialchars($search_query) . '"';
            } elseif (!empty($category_id)) {
                echo 'Shop by Category';
            } else {
                echo 'Shop All Products';
            }
            ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $total_products; ?> product<?php echo ($total_products != 1) ? 's' : ''; ?> found
        </p>
    </div>
</section>

<!-- Shop Section -->
<section class="shop-section">
    <div class="container">
        <div class="shop-layout">
            
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar">
                <div class="sidebar-widget">
                    <h3 class="widget-title">🔍 Search Products</h3>
                    <form action="/minishop/shop.php" method="GET" class="sidebar-search-form">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search products..." 
                            class="sidebar-search-input"
                            value="<?php echo htmlspecialchars($search_query); ?>"
                        >
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </form>
                </div>

                <div class="sidebar-widget">
                    <h3 class="widget-title">📂 Categories</h3>
                    <ul class="category-list">
                        <li>
                            <a href="/minishop/shop.php" class="category-link <?php echo empty($category_id) ? 'active' : ''; ?>">
                                All Products
                            </a>
                        </li>
                        <?php while($category = $categories_result->fetch_assoc()): ?>
                        <li>
                            <a 
                                href="/minishop/shop.php?category=<?php echo $category['id']; ?>" 
                                class="category-link <?php echo ($category_id == $category['id']) ? 'active' : ''; ?>"
                            >
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3 class="widget-title">ℹ️ Shopping Info</h3>
                    <ul class="info-list">
                        <li>✓ Free shipping on orders over $50</li>
                        <li>✓ 30-day return policy</li>
                        <li>✓ Secure checkout</li>
                        <li>✓ 24/7 customer support</li>
                    </ul>
                </div>

                <?php if (!empty($search_query) || !empty($category_id)): ?>
                <div class="sidebar-widget">
                    <a href="/minishop/shop.php" class="btn btn-outline btn-block">
                        Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </aside>

            <!-- Products Grid -->
            <div class="shop-content">
                <?php if ($total_products > 0): ?>
                
                <div class="product-grid">
                    <?php while($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <!-- Product Image -->
                        <div class="product-image-container">
                            <a href="/minishop/product.php?id=<?php echo $product['id']; ?>">
                                <img 
                                    src="/minishop/assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="product-image"
                                    onerror="this.src='/minishop/assets/images/placeholder.jpg'"
                                >
                            </a>
                            
                            <!-- Category Badge -->
                            <span class="badge badge-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                            
                            <!-- Stock Badge -->
                            <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
                                <span class="badge badge-warning">Only <?php echo $product['stock']; ?> left!</span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="/minishop/product.php?id=<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <p class="product-description">
                                <?php 
                                // Show first 100 characters of description
                                $description = htmlspecialchars($product['description']);
                                echo (strlen($description) > 100) ? substr($description, 0, 100) . '...' : $description;
                                ?>
                            </p>
                            
                            <div class="product-footer">
                                <p class="product-price">
                                    $<?php echo number_format($product['price'], 2); ?>
                                </p>
                                
                                <div class="product-actions">
                                    <a 
                                        href="/minishop/product.php?id=<?php echo $product['id']; ?>" 
                                        class="btn btn-outline btn-small"
                                        title="View product details"
                                    >
                                        View Details
                                    </a>
                                    
                                    <?php if ($product['stock'] > 0): ?>
                                    <a 
                                        href="/minishop/cart.php?action=add&id=<?php echo $product['id']; ?>" 
                                        class="btn btn-primary btn-small"
                                        title="Add to cart"
                                    >
                                        🛒 Add to Cart
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-disabled btn-small" disabled>
                                        Out of Stock
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <?php else: ?>
                
                <!-- No Products Found -->
                <div class="no-products-found">
                    <div class="no-products-icon">🔍</div>
                    <h2>No Products Found</h2>
                    <p>
                        <?php if (!empty($search_query)): ?>
                            We couldn't find any products matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>".
                        <?php else: ?>
                            No products available in this category at the moment.
                        <?php endif; ?>
                    </p>
                    <p>Try searching for something else or browse all categories.</p>
                    <div style="margin-top: 30px;">
                        <a href="/minishop/shop.php" class="btn btn-primary">View All Products</a>
                    </div>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Close database connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();

// Include footer
include 'partials/footer.php';
?>