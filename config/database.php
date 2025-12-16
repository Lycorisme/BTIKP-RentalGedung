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
}

// Helper function for easy access
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

// Test connection (comment out in production)
// echo "Database connected successfully!";
?>