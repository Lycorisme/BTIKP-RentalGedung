<?php
// C:\laragon\www\situs-rental-gedung\config\database.php
// Database Configuration & Connection Handler with Auto-Logging (SECURE VERSION)

if (session_status() === PHP_SESSION_NONE) session_start();

// --- 1. Class Pembungkus PDO (CCTV Otomatis) ---

class AutoLogPDO extends PDO {
    #[\ReturnTypeWillChange]
    public function prepare($query, $options = []) {
        return new AutoLogStatement(parent::prepare($query, $options), $query, $this);
    }
}

class AutoLogStatement {
    private $stmt;
    private $queryString;
    private $pdo;

    public function __construct($stmt, $queryString, $pdo) {
        $this->stmt = $stmt;
        $this->queryString = $queryString;
        $this->pdo = $pdo;
    }

    public function __call($method, $args) {
        return call_user_func_array([$this->stmt, $method], $args);
    }

    #[\ReturnTypeWillChange]
    public function execute($params = null) {
        try {
            // 1. Jalankan query utama (Booking/Gedung/dll)
            // Jika ini gagal, aplikasi akan throw error seperti biasa (Normal Behavior)
            $result = $this->stmt->execute($params);
        } catch (Exception $e) {
            throw $e; 
        }

        // 2. Jika sukses, jalankan pencatatan log
        // Kita bungkus try-catch terpisah agar jika LOG gagal, User TIDAK TAHU (Aplikasi tetap jalan)
        if ($result) {
            try {
                $this->logActivity($params);
            } catch (Exception $logError) {
                // Opsional: Catat ke file error_log server jika database log gagal
                // error_log("Activity Log Failed: " . $logError->getMessage());
            }
        }
        
        return $result;
    }

    private function logActivity($params) {
        $sql = trim($this->queryString);
        $parts = explode(' ', $sql);
        $firstWord = isset($parts[0]) ? strtoupper($parts[0]) : '';
        
        // Hanya catat perubahan data
        if (!in_array($firstWord, ['INSERT', 'UPDATE', 'DELETE'])) return;

        // Deteksi nama tabel
        preg_match('/(FROM|INTO|UPDATE)\s+`?([a-zA-Z0-9_]+)`?/', $sql, $matches);
        $table = $matches[2] ?? 'Unknown';

        // PENTING: Cegah Infinite Loop
        if ($table === 'activity_logs') return;

        // --- FILTER DATA SENSITIF (KEAMANAN) ---
        // Kita tidak boleh menyimpan password atau data sensitif lain di log text
        $filteredParams = $params;
        if (is_array($filteredParams)) {
            foreach ($filteredParams as $key => $value) {
                // Jika key mengandung kata password, token, atau secret
                // Atau jika ini array indeks tapi kita tahu urutannya (sulit dideteksi otomatis, 
                // tapi biasanya aman jika menggunakan named parameters atau framework).
                // Di native PHP PDO, kita sensor jika value terlihat seperti hash bcrypt
                
                // Sensor berdasarkan nama key (jika pakai named params :password)
                if (is_string($key) && (strpos(strtolower($key), 'password') !== false || strpos(strtolower($key), 'token') !== false)) {
                    $filteredParams[$key] = '***REDACTED***';
                }
                
                // Sensor jika value terlihat seperti hash password (bcryipt starts with $2y$)
                if (is_string($value) && strpos($value, '$2y$') === 0) {
                     $filteredParams[$key] = '***PASSWORD_HASH***';
                }
            }
        }

        $userId = $_SESSION['user_id'] ?? null;
        $action = $firstWord;
        $module = ucfirst($table);
        $desc = "$action data pada modul $module";
        
        // Simpan params yang sudah disensor
        $newValue = $filteredParams ? json_encode($filteredParams, JSON_UNESCAPED_UNICODE) : null;
        
        $recordId = null;
        if ($action === 'INSERT') {
            try { $recordId = $this->pdo->lastInsertId(); } catch(Exception $e) {}
        } elseif (is_array($params)) {
            $recordId = end($params); // Asumsi ID ada di akhir
        }

        // Insert Log (Bypass Wrapper logic dengan akses tabel langsung)
        $logSql = "INSERT INTO activity_logs (user_id, action, module, record_id, description, new_value, ip_address, user_agent) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Gunakan parent::prepare atau akses pdo langsung. 
        // Karena kita di dalam AutoLogStatement, kita pakai $this->pdo->prepare.
        // AutoLogPDO::prepare akan dipanggil lagi, TAPI akan langsung return karena cek tabel 'activity_logs'.
        $stmt = $this->pdo->prepare($logSql); 
        
        $stmt->execute([
            $userId,
            $action,
            $module,
            $recordId,
            $desc,
            $newValue,
            $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'System'
        ]);
    }
}


// --- 2. Class Database Utama ---

class Database {
    private static $instance = null;
    private $conn;
    
    private $host = 'localhost';
    private $db = 'rental_gedung';
    private $user = 'root';
    private $pass = ''; // Isi password DB hosting Anda nanti
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->conn = new AutoLogPDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            // Di Hosting, jangan echo error langsung agar path tidak bocor
            // die("Connection failed: " . $e->getMessage()); 
            error_log("DB Connection Error: " . $e->getMessage());
            die("Maaf, terjadi kesalahan koneksi database. Silakan coba beberapa saat lagi.");
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
    
    // --- Helper Methods ---
    
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
    
    public function get_settings_group($group) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM settings WHERE `group` = ? ORDER BY `order` ASC");
            $stmt->execute([$group]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
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
    
    public function update_setting($key, $value) {
        try {
            $stmt = $this->conn->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?");
            return $stmt->execute([$value, $key]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// --- Global Helper Functions ---

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