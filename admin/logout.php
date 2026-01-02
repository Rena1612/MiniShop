<?php
/*
 * Admin Logout
 */

session_start();
include 'config/auth.php';

// Logout admin
adminLogout();

// Redirect to login page
header('Location: login.php');
exit;
?>