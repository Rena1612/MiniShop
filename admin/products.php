<?php
/*
 * Products Management Page
 * Display all products with edit/delete options
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$success_message = '';
$error_message = '';

// Check for success/error messages from redirect
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query
$where_conditions = array();
$params = array();
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY p.id DESC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Get all categories for filter
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>📦 Products Management</h1>
        <p>Manage your product inventory</p>
    </div>
    <a href="product_add.php" class="btn btn-success">
        ➕ Add New Product
    </a>
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

<!-- Filters -->
<div class="filters-panel">
    <form method="GET" action="products.php" class="filter-form">
        <div class="filter-group">
            <input 
                type="text" 
                name="search" 
                placeholder="Search products..." 
                class="form-control"
                value="<?php echo htmlspecialchars($search); ?>"
            >
        </div>
        
        <div class="filter-group">
            <select name="category" class="form-control">
                <option value="0">All Categories</option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">🔍 Filter</button>
        <a href="products.php" class="btn btn-outline">Clear</a>
    </form>
</div>

<!-- Products Table -->
<div class="panel">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($product = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo $product['id']; ?></strong></td>
                        <td>
                            <img 
                                src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="product-thumb"
                                onerror="this.src='../assets/images/placeholder.jpg'"
                            >
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php echo substr(htmlspecialchars($product['description']), 0, 50); ?>...
                            </small>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><strong>$<?php echo number_format($product['price'], 2); ?></strong></td>
                        <td>
                            <?php if ($product['stock'] < 10): ?>
                                <span class="badge badge-warning"><?php echo $product['stock']; ?></span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?php echo $product['stock']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($product['featured'] == 1): ?>
                                <span class="badge badge-info">⭐ Yes</span>
                            <?php else: ?>
                                <span class="text-muted">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="product_edit.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-primary" 
                               title="Edit">
                                ✏️ Edit
                            </a>
                            <a href="product_delete.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               title="Delete"
                               onclick="return confirm('Are you sure you want to delete this product?')">
                                🗑️ Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No products found. <a href="product_add.php">Add your product</a>
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