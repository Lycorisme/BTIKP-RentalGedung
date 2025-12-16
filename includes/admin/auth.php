<?php
// includes/admin/auth.php
// Authentication & Authorization System

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /situs-rental-gedung/admin/login/');
        exit;
    }
}

// Check if user has required role
function requireRole($roles) {
    requireLogin();
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    if (!in_array($_SESSION['role'], $roles)) {
        http_response_code(403);
        die('Access Denied: Insufficient permissions');
    }
}

// Get current user role
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user name
function getUserName() {
    return $_SESSION['nama_lengkap'] ?? 'User';
}

// Check if user is admin or superadmin
function isAdmin() {
    return in_array(getUserRole(), ['admin', 'superadmin']);
}

// Check if user is superadmin
function isSuperAdmin() {
    return getUserRole() === 'superadmin';
}

// Login user
function login($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
    $_SESSION['login_time'] = time();
}

// Logout user
function logout() {
    session_unset();
    session_destroy();
    header('Location: /situs-rental-gedung/admin/login/');
    exit;
}

// Handle logout request
if (isset($_GET['logout'])) {
    logout();
}
?>