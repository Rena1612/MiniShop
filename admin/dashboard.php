<?php
/*
 * Admin Dashboard
 * Main admin page with statistics and overview
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

// Get dashboard statistics
$stats = array();

// Total products
$result = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $result->fetch_assoc()['total'];

// Total categories
$result = $conn->query("SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $result->fetch_assoc()['total'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Today's orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['today_orders'] = $result->fetch_assoc()['total'];

// Low stock products (less than 10)
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
$stats['low_stock'] = $result->fetch_assoc()['total'];

// Recent orders (last 10)
$recent_orders_sql = "SELECT o.*, 
                      (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                      FROM orders o 
                      ORDER BY o.created_at DESC 
                      LIMIT 10";
$recent_orders = $conn->query($recent_orders_sql);

// Top selling products
$top_products_sql = "SELECT p.name, p.price, p.stock, SUM(oi.quantity) as total_sold
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     GROUP BY oi.product_id
                     ORDER BY total_sold DESC
                     LIMIT 5";
$top_products = $conn->query($top_products_sql);

// Low stock products
$low_stock_sql = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.stock < 10 
                  ORDER BY p.stock ASC 
                  LIMIT 5";
$low_stock_products = $conn->query($low_stock_sql);

include 'includes/header.php';
?>

<div class="page-header">
    <h1>📊 Dashboard</h1>
    <p>Welcome back, <?php echo htmlspecialchars($admin['full_name']); ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">📦</div>
        <div class="stat-details">
            <h3><?php echo $stats['total_products']; ?></h3>
            <p>Total Products</p>
            <a href="products.php" class="stat-link">View Products →</a>
        </div>
    </div>
    
    <div class="stat-card stat-success">
        <div class="stat-icon">🛒</div>
        <div class="stat-details">
            <h3><?php echo $stats['total_orders']; ?></h3>
            <p>Total Orders</p>
            <a href="orders.php" class="stat-link">View Orders →</a>
        </div>
    </div>
    
    <div class="stat-card stat-info">
        <div class="stat-icon">💰</div>
        <div class="stat-details">
            <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    
    <div class="stat-card stat-warning">
        <div class="stat-icon">📂</div>
        <div class="stat-details">
            <h3><?php echo $stats['total_categories']; ?></h3>
            <p>Categories</p>
            <a href="categories.php" class="stat-link">Manage →</a>
        </div>
    </div>
    
    <div class="stat-card stat-success">
        <div class="stat-icon">📅</div>
        <div class="stat-details">
            <h3><?php echo $stats['today_orders']; ?></h3>
            <p>Today's Orders</p>
        </div>
    </div>
    
    <div class="stat-card stat-danger">
        <div class="stat-icon">⚠️</div>
        <div class="stat-details">
            <h3><?php echo $stats['low_stock']; ?></h3>
            <p>Low Stock Items</p>
        </div>
    </div>
</div>

<!-- Dashboard Grid -->
<div class="dashboard-grid">
    
    <!-- Recent Orders -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <h2>📋 Recent Orders</h2>
            <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Selling Products -->
    <div class="dashboard-panel">
        <div class="panel-header">
            <h2>🏆 Top Selling Products</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($top_products->num_rows > 0): ?>
                        <?php while ($product = $top_products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><strong class="text-success"><?php echo $product['total_sold']; ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No sales data yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="dashboard-panel full-width">
        <div class="panel-header">
            <h2>⚠️ Low Stock Alert</h2>
            <a href="products.php" class="btn btn-sm btn-warning">Restock</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($low_stock_products->num_rows > 0): ?>
                        <?php while ($product = $low_stock_products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>
                                <span class="badge badge-danger"><?php echo $product['stock']; ?> units</span>
                            </td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                    Edit Stock
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">All products are well stocked!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>