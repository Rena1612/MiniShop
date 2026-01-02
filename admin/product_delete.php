<?php
/*
 * Delete Product
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    // Get product info first
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Delete product
        $delete_sql = "DELETE FROM products WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $product_id);
        
        if ($delete_stmt->execute()) {
            // Delete image file if not placeholder
            if ($product['image'] != 'placeholder.jpg' && file_exists('../assets/images/' . $product['image'])) {
                unlink('../assets/images/' . $product['image']);
            }
            
            header('Location: products.php?success=Product deleted successfully!');
            exit;
        } else {
            header('Location: products.php?error=Failed to delete product');
            exit;
        }
        
        $delete_stmt->close();
    } else {
        header('Location: products.php?error=Product not found');
        exit;
    }
    
    $stmt->close();
} else {
    header('Location: products.php?error=Invalid product ID');
    exit;
}

$conn->close();
?>