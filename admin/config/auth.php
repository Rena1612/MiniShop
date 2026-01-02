<?php
/*
 * Admin Authentication Configuration
 * Handles session management and login checks
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Get logged in admin data
function getAdminData() {
    if (isAdminLoggedIn()) {
        return array(
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'full_name' => $_SESSION['admin_full_name']
        );
    }
    return null;
}

// Require admin login (redirect if not logged in)
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Admin logout
function adminLogout() {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_full_name']);
    session_destroy();
}
?>