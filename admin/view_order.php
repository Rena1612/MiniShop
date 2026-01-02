<?php
/*
 * View Order Details
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
if ($order_id > 0) {
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header('Location: orders.php?error=Order not found');
        exit;
    }
    
    $order = $result->fetch_assoc();
    $stmt->close();
    
    // Get order items
    $items_sql = "SELECT oi.*, p.name, p.image 
                  FROM order_items oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
} else {
    header('Location: orders.php?error=Invalid order ID');
    exit;
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>📋 Order Details #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h1>
    <div>
        <a href="orders.php" class="btn btn-outline">← Back to Orders</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
    </div>
</div>

<div class="order-details-layout">
    
    <!-- Order Information -->
    <div class="panel">
        <h2>📦 Order Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Order Number:</label>
                <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
            </div>
            <div class="info-item">
                <label>Order Date:</label>
                <strong><?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?></strong>
            </div>
            <div class="info-item">
                <label>Total Amount:</label>
                <strong class="text-success">$<?php echo number_format($order['total_amount'], 2); ?></strong>
            </div>
        </div>
    </div>
    
    <!-- Customer Information -->
    <div class="panel">
        <h2>👤 Customer Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Name:</label>
                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
            </div>
            <div class="info-item">
                <label>Email:</label>
                <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong>
            </div>
            <div class="info-item">
                <label>Phone:</label>
                <strong><?php echo htmlspecialchars($order['customer_phone']); ?></strong>
            </div>
            <div class="info-item full-width">
                <label>Delivery Address:</label>
                <p><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Order Items -->
    <div class="panel full-width">
        <h2>🛍️ Order Items</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0;
                    while ($item = $items_result->fetch_assoc()): 
                        $line_total = $item['price'] * $item['quantity'];
                        $subtotal += $line_total;
                    ?>
                    <tr>
                        <td>
                            <img 
                                src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" 
                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                class="product-thumb"
                                onerror="this.src='../assets/images/placeholder.jpg'"
                            >
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><strong>$<?php echo number_format($line_total, 2); ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                        <td><strong>$<?php echo number_format($subtotal, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Shipping:</strong></td>
                        <td><strong>$<?php echo number_format($order['total_amount'] - $subtotal, 2); ?></strong></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" class="text-right"><strong>Total:</strong></td>
                        <td><strong class="text-success">$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
</div>

<?php
$items_stmt->close();
$conn->close();
include 'includes/footer.php';
?>