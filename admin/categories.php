<?php
/*
 * Categories Management
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$success_message = '';
$error_message = '';

// Check for success/error messages
if (isset($_GET['success'])) {
    $success_message = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            $success_message = "Category added successfully!";
        } else {
            $error_message = "Failed to add category.";
        }
        
        $stmt->close();
    } else {
        $error_message = "Category name is required.";
    }
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    
    if (!empty($name) && $id > 0) {
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        
        if ($stmt->execute()) {
            $success_message = "Category updated successfully!";
        } else {
            $error_message = "Failed to update category.";
        }
        
        $stmt->close();
    } else {
        $error_message = "Invalid category data.";
    }
}

// Handle Delete Category
if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
    $id = intval($_GET['delete']);
    
    // Check if category has products
    $check_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $count = $check_result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($count > 0) {
        $error_message = "Cannot delete category with existing products. Please reassign or delete products first.";
    } else {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success_message = "Category deleted successfully!";
        } else {
            $error_message = "Failed to delete category.";
        }
        
        $stmt->close();
    }
}

// Get all categories with product count
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name ASC";
$categories = $conn->query($sql);

include 'includes/header.php';
?>

<div class="page-header">
    <h1>📂 Categories Management</h1>
    <p>Organize your products into categories</p>
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

<div class="categories-layout">
    
    <!-- Add New Category Form -->
    <div class="panel">
        <h2>➕ Add New Category</h2>
        <form method="POST" action="categories.php" class="category-form">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control"
                    placeholder="Enter category name"
                    required
                >
            </div>
            <button type="submit" class="btn btn-success">
                ✓ Add Category
            </button>
        </form>
    </div>
    
    <!-- Categories List -->
    <div class="panel">
        <h2>📋 All Categories</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $category['id']; ?></strong></td>
                            <td>
                                <span id="cat-name-<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </span>
                                <form method="POST" action="categories.php" id="form-<?php echo $category['id']; ?>" style="display:none;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                    <input 
                                        type="text" 
                                        name="name" 
                                        value="<?php echo htmlspecialchars($category['name']); ?>"
                                        class="form-control"
                                        required
                                    >
                                </form>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo $category['product_count']; ?> products
                                </span>
                            </td>
                            <td class="actions-cell">
                                <button 
                                    onclick="editCategory(<?php echo $category['id']; ?>)" 
                                    class="btn btn-sm btn-primary"
                                    id="edit-btn-<?php echo $category['id']; ?>">
                                    ✏️ Edit
                                </button>
                                <button 
                                    onclick="document.getElementById('form-<?php echo $category['id']; ?>').submit();" 
                                    class="btn btn-sm btn-success"
                                    id="save-btn-<?php echo $category['id']; ?>"
                                    style="display:none;">
                                    ✓ Save
                                </button>
                                <button 
                                    onclick="cancelEdit(<?php echo $category['id']; ?>)" 
                                    class="btn btn-sm btn-outline"
                                    id="cancel-btn-<?php echo $category['id']; ?>"
                                    style="display:none;">
                                    Cancel
                                </button>
                                <a 
                                    href="categories.php?delete=<?php echo $category['id']; ?>" 
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this category?')">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No categories found. Add your first category above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<script>
function editCategory(id) {
    document.getElementById('cat-name-' + id).style.display = 'none';
    document.getElementById('form-' + id).style.display = 'block';
    document.getElementById('edit-btn-' + id).style.display = 'none';
    document.getElementById('save-btn-' + id).style.display = 'inline-block';
    document.getElementById('cancel-btn-' + id).style.display = 'inline-block';
}

function cancelEdit(id) {
    document.getElementById('cat-name-' + id).style.display = 'block';
    document.getElementById('form-' + id).style.display = 'none';
    document.getElementById('edit-btn-' + id).style.display = 'inline-block';
    document.getElementById('save-btn-' + id).style.display = 'none';
    document.getElementById('cancel-btn-' + id).style.display = 'none';
}
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>