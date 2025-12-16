<?php
// modules/rate_limit.php
// Rate Limiting System

function check_rate_limit() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $now = time();
    
    // Initialize session rate limit
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [
            'requests' => 0,
            'start_time' => $now,
            'blocked_until' => 0
        ];
    }
    
    $rl = &$_SESSION['rate_limit'];
    
    // Check if blocked
    if ($rl['blocked_until'] > $now) {
        $wait = $rl['blocked_until'] - $now;
        http_response_code(429);
        die(json_encode([
            'error' => 'Too many requests',
            'message' => "Please wait {$wait} seconds before trying again",
            'retry_after' => $wait
        ]));
    }
    
    // Reset counter if minute passed
    if ($now - $rl['start_time'] >= 60) {
        $rl['requests'] = 0;
        $rl['start_time'] = $now;
    }
    
    // Increment request counter
    $rl['requests']++;
    
    // Determine limit based on role
    $limit = 10; // Public
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'superadmin') {
            return true; // Unlimited
        } elseif ($_SESSION['role'] === 'admin') {
            $limit = 30;
        } elseif ($_SESSION['role'] === 'penyewa') {
            $limit = 20;
        }
    }
    
    // Check if exceeded
    if ($rl['requests'] > $limit) {
        $rl['blocked_until'] = $now + 900; // Block 15 minutes
        http_response_code(429);
        die(json_encode([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Blocked for 15 minutes.',
            'retry_after' => 900
        ]));
    }
    
    return true;
}

// Log rate limit violations (optional)
function log_rate_limit_violation() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_id = $_SESSION['user_id'] ?? 'guest';
    $timestamp = date('Y-m-d H:i:s');
    
    // Log to file
    $log = "[{$timestamp}] Rate limit exceeded - IP: {$ip}, User: {$user_id}\n";
    file_put_contents(__DIR__ . '/../logs/rate_limit.log', $log, FILE_APPEND);
}
?>