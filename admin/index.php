<?php
/*
 * Admin Dashboard - Main Page
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

// Total orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Low stock products (less than 10)
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
$stats['low_stock'] = $result->fetch_assoc()['total'];

// Recent orders (last 5)
$recent_orders_sql = "SELECT o.*, 
                      (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                      FROM orders o 
                      ORDER BY o.created_at DESC 
                      LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql);

// Top selling products
$top_products_sql = "SELECT p.name, p.price, SUM(oi.quantity) as total_sold
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     GROUP BY oi.product_id
                     ORDER BY total_sold DESC
                     LIMIT 5";
$top_products = $conn->query($top_products_sql);

include 'includes/header.php';
?>

<div class="dashboard">
    <h1>Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">📦</div>
            <div class="stat-details">
                <h3><?php echo $stats['total_products']; ?></h3>
                <p>Total Products</p>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="stat-icon">🛒</div>
            <div class="stat-details">
                <h3><?php echo $stats['total_orders']; ?></h3>
                <p>Total Orders</p>
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
            <div class="stat-icon">⚠️</div>
            <div class="stat-details">
                <h3><?php echo $stats['low_stock']; ?></h3>
                <p>Low Stock Items</p>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders & Top Products -->
    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="dashboard-panel">
            <h2>Recent Orders</h2>
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
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="orders.php" class="btn btn-outline">View All Orders →</a>
        </div>
        
        <!-- Top Selling Products -->
        <div class="dashboard-panel">
            <h2>Top Selling Products</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($top_products->num_rows > 0): ?>
                            <?php while ($product = $top_products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><strong><?php echo $product['total_sold']; ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No sales data yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="products.php" class="btn btn-outline">Manage Products →</a>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>