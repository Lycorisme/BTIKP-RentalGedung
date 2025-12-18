<?php
// C:\laragon\www\situs-rental-gedung\includes\logger.php

/**
 * Fungsi untuk mencatat aktivitas ke database
 */
function logActivity($conn, $action, $module, $record_id, $description, $old_value = null, $new_value = null) {
    try {
        // Ambil ID User dari Session
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Ambil IP & User Agent
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Encode JSON (Pastikan tidak error jika null)
        $old_json = $old_value ? json_encode($old_value, JSON_UNESCAPED_UNICODE) : null;
        $new_json = $new_value ? json_encode($new_value, JSON_UNESCAPED_UNICODE) : null;

        $sql = "INSERT INTO activity_logs (user_id, action, module, record_id, description, old_value, new_value, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $user_id, 
            $action, 
            $module, 
            $record_id, 
            $description, 
            $old_json, 
            $new_json, 
            $ip_address, 
            $user_agent
        ]);
        
        return true;
    } catch (Exception $e) {
        // Silent fail: Jangan biarkan error log menghentikan aplikasi utama
        error_log("Gagal mencatat log: " . $e->getMessage());
        return false;
    }
}
?>