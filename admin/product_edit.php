<?php
/*
 * Edit Product
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$errors = array();
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product data
if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header('Location: products.php?error=Product not found');
        exit;
    }
    
    $product = $result->fetch_assoc();
    $stmt->close();
} else {
    header('Location: products.php?error=Invalid product ID');
    exit;
}

// Get all categories
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $stock = intval($_POST['stock']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $image = $product['image']; // Keep existing image
    
    // Validation
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    if ($category_id == 0) {
        $errors[] = "Please select a category.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0.";
    }
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative.";
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'product_' . time() . '.' . $ext;
            $upload_path = '../assets/images/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if not placeholder
                if ($product['image'] != 'placeholder.jpg' && file_exists('../assets/images/' . $product['image'])) {
                    unlink('../assets/images/' . $product['image']);
                }
                $image = $new_filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid image format. Allowed: JPG, PNG, GIF.";
        }
    }
    
    // Update if no errors
    if (empty($errors)) {
        $sql = "UPDATE products 
                SET category_id = ?, name = ?, price = ?, description = ?, image = ?, stock = ?, featured = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idsssiii", $category_id, $name, $price, $description, $image, $stock, $featured, $product_id);
        
        if ($stmt->execute()) {
            header('Location: products.php?success=Product updated successfully!');
            exit;
        } else {
            $errors[] = "Failed to update product. Please try again.";
        }
        
        $stmt->close();
    } else {
        // Refresh product data with submitted values
        $product['name'] = $name;
        $product['category_id'] = $category_id;
        $product['price'] = $price;
        $product['description'] = $description;
        $product['stock'] = $stock;
        $product['featured'] = $featured;
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>✏️ Edit Product</h1>
    <a href="products.php" class="btn btn-outline">← Back to Products</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <strong>Please correct the following errors:</strong>
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="form-panel">
    <form action="product_edit.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" class="product-form">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Product Name <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control"
                    value="<?php echo htmlspecialchars($product['name']); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="category_id">Category <span class="required">*</span></label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="0">
                    Select Category</option>
                    <?php
                    $categories_result->data_seek(0); // Reset pointer
                    while ($category = $categories_result->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $category['id']; ?>" 
                        <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price ($) <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="price" 
                    name="price" 
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="<?php echo $product['price']; ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="stock">Stock Quantity <span class="required">*</span></label>
                <input 
                    type="number" 
                    id="stock" 
                    name="stock" 
                    class="form-control"
                    min="0"
                    value="<?php echo $product['stock']; ?>"
                    required
                >
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-control"
                rows="5"
                placeholder="Enter product description..."
            ><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="image">Product Image</label>
            <div class="current-image">
                <img 
                    src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                    alt="Current product image"
                    style="max-width: 200px; border-radius: 8px; margin-bottom: 10px;"
                    onerror="this.src='../assets/images/placeholder.jpg'"
                >
                <p class="text-muted">Current: <?php echo htmlspecialchars($product['image']); ?></p>
            </div>
            <input 
                type="file" 
                id="image" 
                name="image" 
                class="form-control"
                accept="image/*"
            >
            <small class="form-help">Leave empty to keep current image. Allowed formats: JPG, PNG, GIF. Max size: 5MB</small>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input 
                    type="checkbox" 
                    name="featured" 
                    value="1"
                    <?php echo ($product['featured'] == 1) ? 'checked' : ''; ?>
                >
                <span>⭐ Feature this product on homepage</span>
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success btn-large">
                ✓ Update Product
            </button>
            <a href="products.php" class="btn btn-outline btn-large">
                Cancel
            </a>
        </div>
        
    </form>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
                        