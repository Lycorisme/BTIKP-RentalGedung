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
        die('
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>
        <body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen flex items-center justify-center p-4">
            <div class="text-center">
                <div class="inline-flex h-24 w-24 rounded-full bg-gradient-to-br from-red-500 to-orange-500 items-center justify-center mb-6 shadow-2xl">
                    <i class="fa-solid fa-ban text-white text-4xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-slate-800 mb-4">Access Denied</h1>
                <p class="text-slate-600 mb-8">You do not have permission to access this page.</p>
                <a href="/situs-rental-gedung/admin/login/" class="inline-flex items-center px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold shadow-lg hover:shadow-xl transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Login
                </a>
            </div>
        </body>
        </html>
        ');
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

// Get current user email
function getUserEmail() {
    return $_SESSION['email'] ?? '';
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

// Session timeout check (30 minutes)
function checkSessionTimeout($timeout = 1800) {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            logout();
        }
        $_SESSION['login_time'] = time(); // Update last activity
    }
}

// Rate limiting helper
function checkRateLimit($action = 'general', $limit = 30) {
    $key = "rate_limit_{$action}_" . ($_SESSION['user_id'] ?? 'guest');
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'start' => time()];
    }
    
    // Reset if 1 minute passed
    if (time() - $_SESSION[$key]['start'] > 60) {
        $_SESSION[$key] = ['count' => 0, 'start' => time()];
    }
    
    $_SESSION[$key]['count']++;
    
    if ($_SESSION[$key]['count'] > $limit) {
        return false;
    }
    
    return true;
}
?>