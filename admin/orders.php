<?php
/*
 * Orders Management
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$success_message = '';
$error_message = '';

// Check for messages
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where_conditions = array();
$params = array();
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY o.created_at DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Calculate totals
$total_orders = $result->num_rows;
$total_revenue = 0;
$result->data_seek(0); // Reset pointer
while ($order = $result->fetch_assoc()) {
    $total_revenue += $order['total_amount'];
}
$result->data_seek(0); // Reset again for display

include 'includes/header.php';
?>

<div class="page-header">
    <h1>🛒 Orders Management</h1>
    <p>View and manage customer orders</p>
</div>

<?php if ($success_message): ?>
<div class="alert alert-success">
    ✓ <?php echo $success_message; ?>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="alert alert-error">
    ✗ <?php echo $error_message; ?>
</div>
<?php endif; ?>

<!-- Order Statistics -->
<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 30px;">
    <div class="stat-card stat-primary">
        <div class="stat-icon">📦</div>
        <div class="stat-details">
            <h3><?php echo $total_orders; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    
    <div class="stat-card stat-success">
        <div class="stat-icon">💰</div>
        <div class="stat-details">
            <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filters-panel">
    <form method="GET" action="orders.php" class="filter-form">
        <div class="filter-group">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by customer name, email, or phone..." 
                class="form-control"
                value="<?php echo htmlspecialchars($search); ?>"
            >
        </div>
        
        <div class="filter-group">
            <input 
                type="date" 
                name="date_from" 
                class="form-control"
                placeholder="From Date"
                value="<?php echo htmlspecialchars($date_from); ?>"
            >
        </div>
        
        <div class="filter-group">
            <input 
                type="date" 
                name="date_to" 
                class="form-control"
                placeholder="To Date"
                value="<?php echo htmlspecialchars($date_to); ?>"
            >
        </div>
        
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="orders.php" class="btn btn-outline">Clear</a>
    </form>
</div>

<!-- Orders Table -->
<div class="panel">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_orders > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                        <td><?php echo $order['item_count']; ?> items</td>
                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                        <td class="actions-cell">
                            <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                👁️ View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No orders found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
include 'includes/footer.php';
?>