<?php
/*
 * Admin Settings
 * Change password and profile settings
 */

session_start();
include 'config/auth.php';
requireAdminLogin();

include '../config/db.php';

$success_message = '';
$error_message = '';
$admin = getAdminData();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current admin password
    $sql = "SELECT password FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_data = $result->fetch_assoc();
    $stmt->close();
    
    // Verify current password
    if (!password_verify($current_password, $admin_data['password'])) {
        $error_message = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE admin_users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $admin['id']);
        
        if ($update_stmt->execute()) {
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Failed to change password.";
        }
        
        $update_stmt->close();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    if (empty($full_name) || empty($email)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address.";
    } else {
        $sql = "UPDATE admin_users SET full_name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $full_name, $email, $admin['id']);
        
        if ($stmt->execute()) {
            $_SESSION['admin_full_name'] = $full_name;
            $_SESSION['admin_email'] = $email;
            $success_message = "Profile updated successfully!";
            $admin['full_name'] = $full_name;
            $admin['email'] = $email;
        } else {
            $error_message = "Failed to update profile.";
        }
        
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h1>⚙️ Settings</h1>
    <p>Manage your admin account settings</p>
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

<div class="settings-layout">
    
    <!-- Profile Settings -->
    <div class="panel">
        <h2>👤 Profile Information</h2>
        <form method="POST" action="settings.php" class="settings-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label for="username">Username (cannot be changed)</label>
                <input 
                    type="text" 
                    id="username" 
                    class="form-control"
                    value="<?php echo htmlspecialchars($admin['username']); ?>"
                    disabled
                >
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    class="form-control"
                    value="<?php echo htmlspecialchars($admin['full_name']); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control"
                    value="<?php echo htmlspecialchars($admin['email']); ?>"
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-primary">
                ✓ Update Profile
            </button>
        </form>
    </div>
    
    <!-- Change Password -->
    <div class="panel">
        <h2>🔒 Change Password</h2>
        <form method="POST" action="settings.php" class="settings-form">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    class="form-control"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    class="form-control"
                    minlength="6"
                    required
                >
                <small class="form-help">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control"
                    minlength="6"
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-success">
                ✓ Change Password
            </button>
        </form>
    </div>
    
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>