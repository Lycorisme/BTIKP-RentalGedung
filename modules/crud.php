<?php
// modules/crud.php
// Universal CRUD Operations with Soft Deletes & Audit Logs

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// --- HELPER: AUDIT LOGGING ---
function log_activity($action, $module, $record_id = null, $description = '') {
    try {
        $db = getDB();
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Dapatkan info Client
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $sql = "INSERT INTO `activity_logs` (`user_id`, `action`, `module`, `record_id`, `description`, `ip_address`, `user_agent`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id, $action, $module, $record_id, $description, $ip_address, $user_agent]);
        
    } catch (PDOException $e) {
        // Silent fail: Jangan sampai logging error menghentikan aplikasi utama
        error_log("Audit Log Error: " . $e->getMessage());
    }
}

// --- CREATE ---
function create($table, $data) {
    try {
        $db = getDB();
        $columns = implode(', ', array_map(function($col) { return "`$col`"; }, array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute(array_values($data))) {
            $id = $db->lastInsertId();
            
            // Log Activity
            log_activity('CREATE', $table, $id, "Menambahkan data baru ke tabel $table");
            
            return ['success' => true, 'id' => $id, 'message' => 'Data berhasil ditambahkan'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// --- READ (Support Soft Deletes) ---
// Default: Hanya menampilkan data yang belum dihapus (deleted_at IS NULL)
function read($table, $where = [], $limit = 100, $offset = 0, $orderBy = 'id DESC') {
    try {
        $db = getDB();
        $params = [];
        $conditions = [];

        // Cek apakah tabel mendukung Soft Deletes (punya kolom deleted_at)
        // Kita asumsikan tabel utama (gedung, users, booking, pelanggan, promos) punya kolom ini.
        // Untuk tabel lain (seperti settings), logic ini aman karena kita bisa override lewat $where.
        
        // Jika user tidak secara spesifik meminta data terhapus, filter deleted_at IS NULL
        $useSoftDelete = true;
        
        // Logic untuk mendeteksi apakah tabel punya kolom deleted_at (Cacheable ideally, but simple check here)
        // Disini kita defaultkan filter deleted_at IS NULL kecuali di-request khusus
        if (!isset($where['deleted_at'])) {
             // Cek kolom tabel manual atau assume standar. 
             // Agar performa cepat, kita tambahkan kondisi ini:
             $conditions[] = "`deleted_at` IS NULL";
        }

        if (!empty($where)) {
            foreach ($where as $key => $value) {
                // Support operator khusus, misal 'harga >' => 1000
                if (strpos($key, ' ') !== false) {
                    $conditions[] = "$key ?"; // Contoh: "created_at > ?"
                } else {
                    $conditions[] = "`$key` = ?";
                }
                $params[] = $value;
            }
        }
        
        $sql = "SELECT * FROM `$table`";
        
        // Cek jika tabel punya kolom deleted_at sebelum menambah WHERE (opsional, untuk safety tabel lama)
        // Namun karena kita sudah upgrade DB, kita asumsikan tabel utama punya.
        // Untuk tabel log/settings yang tidak punya deleted_at, query akan error jika kolom tidak ada.
        // SOLUSI: Gunakan try-catch ini. Jika error "column not found", kita ulangi query tanpa deleted_at.
        
        $whereClause = !empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : "";
        $finalSql = $sql . $whereClause . " ORDER BY $orderBy LIMIT $limit OFFSET $offset";

        try {
            $stmt = $db->prepare($finalSql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $ex) {
            // Fallback: Jika error karena kolom deleted_at tidak ada (misal tabel settings)
            if (strpos($ex->getMessage(), "Unknown column 'deleted_at'") !== false) {
                // Re-build conditions tanpa deleted_at
                $conditions = array_filter($conditions, function($c) { return strpos($c, 'deleted_at') === false; });
                $whereClause = !empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : "";
                $finalSql = $sql . $whereClause . " ORDER BY $orderBy LIMIT $limit OFFSET $offset";
                
                $stmt = $db->prepare($finalSql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            }
            throw $ex; // Lempar error lain
        }

    } catch (PDOException $e) {
        // error_log($e->getMessage());
        return [];
    }
}

// --- READ ONE ---
function read_one($table, $where = []) {
    $result = read($table, $where, 1);
    return !empty($result) ? $result[0] : null;
}

// --- READ BY ID ---
function read_by_id($table, $id) {
    return read_one($table, ['id' => $id]);
}

// --- UPDATE ---
function update($table, $id, $data) {
    try {
        $db = getDB();
        
        // Ambil data lama untuk audit log (Optional: deep diff)
        // $oldData = read_by_id($table, $id);

        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = ?";
            $params[] = $value;
        }
        
        // Tambahkan updated_at jika ada kolomnya (otomatis di DB biasanya, tapi bisa dipaksa)
        // $sets[] = "`updated_at` = NOW()";

        $params[] = $id;
        
        $sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute($params)) {
            // Log Activity
            log_activity('UPDATE', $table, $id, "Mengubah data pada tabel $table. Fields: " . implode(', ', array_keys($data)));
            return ['success' => true, 'message' => 'Data berhasil diperbarui'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// --- DELETE (SOFT DELETE) ---
function delete($table, $id) {
    try {
        $db = getDB();
        
        // Cek apakah tabel mendukung soft delete (punya kolom deleted_at)
        // Cara check: Coba update deleted_at, jika gagal berarti tabel fisik harus didelete row-nya
        
        $sqlSoft = "UPDATE `$table` SET `deleted_at` = NOW() WHERE id = ?";
        
        try {
            $stmt = $db->prepare($sqlSoft);
            if ($stmt->execute([$id])) {
                // Cek apakah ada baris yang terpengaruh
                if ($stmt->rowCount() > 0) {
                    log_activity('DELETE (SOFT)', $table, $id, "Data diarsipkan (Soft Delete)");
                    return ['success' => true, 'message' => 'Data berhasil dihapus (Arsip)'];
                } else {
                    // ID tidak ditemukan
                    return ['success' => false, 'message' => 'Data tidak ditemukan'];
                }
            }
        } catch (PDOException $ex) {
            // Fallback: Jika tabel tidak punya kolom deleted_at, lakukan Hard Delete
            if (strpos($ex->getMessage(), "Unknown column") !== false) {
                return force_delete($table, $id);
            }
            throw $ex;
        }
        
        return ['success' => false, 'message' => 'Gagal menghapus data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// --- FORCE DELETE (HARD DELETE) ---
function force_delete($table, $id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM `$table` WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            log_activity('DELETE (PERMANENT)', $table, $id, "Data dihapus permanen dari database");
            return ['success' => true, 'message' => 'Data berhasil dihapus permanen'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// --- RESTORE (UNDO DELETE) ---
function restore($table, $id) {
    try {
        $db = getDB();
        $sql = "UPDATE `$table` SET `deleted_at` = NULL WHERE id = ?";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute([$id])) {
            log_activity('RESTORE', $table, $id, "Data dikembalikan dari arsip");
            return ['success' => true, 'message' => 'Data berhasil dipulihkan'];
        }
        return ['success' => false, 'message' => 'Gagal memulihkan data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// --- COUNT RECORDS ---
function count_records($table, $where = []) {
    try {
        $db = getDB();
        $sql = "SELECT COUNT(*) as total FROM `$table`";
        $params = [];
        $conditions = [];

        // Default soft delete check
        if (!isset($where['deleted_at'])) {
             $conditions[] = "`deleted_at` IS NULL";
        }

        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
        }
        
        $whereClause = !empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : "";
        $finalSql = $sql . $whereClause;

        try {
            $stmt = $db->prepare($finalSql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'];
        } catch (PDOException $ex) {
            // Fallback jika tabel tidak punya deleted_at
            if (strpos($ex->getMessage(), "Unknown column 'deleted_at'") !== false) {
                // Re-build query tanpa deleted_at
                $conditions = array_filter($conditions, function($c) { return strpos($c, 'deleted_at') === false; });
                $whereClause = !empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : "";
                $stmt = $db->prepare($sql . $whereClause);
                $stmt->execute($params);
                $result = $stmt->fetch();
                return $result['total'];
            }
            throw $ex;
        }
    } catch (PDOException $e) {
        return 0;
    }
}

// --- SEARCH ---
function search($table, $column, $keyword, $limit = 50, $orderBy = 'id DESC') {
    try {
        $db = getDB();
        // Cek deleted_at dulu
        try {
            $sql = "SELECT * FROM `$table` WHERE `$column` LIKE ? AND `deleted_at` IS NULL ORDER BY $orderBy LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute(["%$keyword%"]);
        } catch (PDOException $ex) {
            // Fallback
            $sql = "SELECT * FROM `$table` WHERE `$column` LIKE ? ORDER BY $orderBy LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute(["%$keyword%"]);
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// --- HELPER LAINNYA (Bawaan Lama) ---

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function paginate($table, $where = [], $page = 1, $perPage = 10, $orderBy = 'id DESC') {
    $page = max(1, (int)$page);
    $offset = ($page - 1) * $perPage;
    $data = read($table, $where, $perPage, $offset, $orderBy);
    $total = count_records($table, $where);
    $totalPages = ceil($total / $perPage);
    
    return [
        'data' => $data,
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

function query($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function execute($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}

function upload_file($field_name, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    $file = $_FILES[$field_name];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true, 
            'filename' => $filename, 
            'filepath' => $filepath,
            'path' => str_replace('../../', '', $filepath) // Clean path for DB
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move file'];
}
?>