<?php
// modules/settings.php
// Settings Management Helper

require_once __DIR__ . '/../config/database.php';

// Update setting value
function update_setting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE settings SET value = ?, updated_at = NOW() WHERE `key` = ?");
        return $stmt->execute([$value, $key]);
    } catch (PDOException $e) {
        return false;
    }
}

// Upload file (logo, favicon, ttd)
function upload_file($field, $upload_dir = '../../../uploads/logos/') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    $file = $_FILES[$field];
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'ico'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, ICO allowed.'];
    }
    
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        return ['success' => false, 'message' => 'File too large. Max 2MB.'];
    }
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Get settings with cache
function get_settings_cached() {
    if (!isset($_SESSION['settings_cache']) || (time() - $_SESSION['settings_cache_time']) > 300) {
        $_SESSION['settings_cache'] = get_all_settings();
        $_SESSION['settings_cache_time'] = time();
    }
    return $_SESSION['settings_cache'];
}

// Clear settings cache
function clear_settings_cache() {
    unset($_SESSION['settings_cache']);
    unset($_SESSION['settings_cache_time']);
}
?>