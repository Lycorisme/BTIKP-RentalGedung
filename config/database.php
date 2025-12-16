<?php
// config/database.php
// Database Configuration & Connection Handler

class Database {
    private static $instance = null;
    private $conn;
    
    private $host = 'localhost';
    private $db = 'rental_gedung';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Get single setting value
    public function get_setting($key, $default = '') {
        try {
            $stmt = $this->conn->prepare("SELECT value FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            return $result ? $result['value'] : $default;
        } catch (PDOException $e) {
            return $default;
        }
    }
    
    // Get all settings by group
    public function get_settings_group($group) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM settings WHERE `group` = ? ORDER BY `order` ASC");
            $stmt->execute([$group]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Get all settings as associative array
    public function get_all_settings() {
        try {
            $stmt = $this->conn->query("SELECT `key`, `value` FROM settings");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'];
            }
            return $settings;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Update setting
    public function update_setting($key, $value) {
        try {
            $stmt = $this->conn->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?");
            return $stmt->execute([$value, $key]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Helper functions for easy access
function getDB() {
    return Database::getInstance()->getConnection();
}

function get_setting($key, $default = '') {
    return Database::getInstance()->get_setting($key, $default);
}

function get_settings_group($group) {
    return Database::getInstance()->get_settings_group($group);
}

function get_all_settings() {
    return Database::getInstance()->get_all_settings();
}

function update_setting($key, $value) {
    return Database::getInstance()->update_setting($key, $value);
}

// Upload logo helper
function upload_logo($field_name, $upload_dir = '../../../uploads/logos/') {
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    $file = $_FILES[$field_name];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        return ['success' => false, 'message' => 'File too large (max 2MB)'];
    }
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => 'uploads/logos/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}
?>