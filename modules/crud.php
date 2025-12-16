<?php
// modules/crud.php
// Universal CRUD Operations

require_once __DIR__ . '/../config/database.php';

// CREATE - Insert data
function create($table, $data) {
    try {
        $db = getDB();
        $columns = implode(', ', array_map(function($col) { return "`$col`"; }, array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute(array_values($data))) {
            return ['success' => true, 'id' => $db->lastInsertId(), 'message' => 'Data berhasil ditambahkan'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// READ - Get data with optional conditions
function read($table, $where = [], $limit = 100, $offset = 0, $orderBy = 'id DESC') {
    try {
        $db = getDB();
        $sql = "SELECT * FROM `$table`";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY $orderBy LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// READ ONE - Get single record
function read_one($table, $where = []) {
    $result = read($table, $where, 1);
    return !empty($result) ? $result[0] : null;
}

// READ BY ID - Get single record by ID
function read_by_id($table, $id) {
    return read_one($table, ['id' => $id]);
}

// UPDATE - Update data
function update($table, $id, $data) {
    try {
        $db = getDB();
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute($params)) {
            return ['success' => true, 'message' => 'Data berhasil diupdate'];
        }
        return ['success' => false, 'message' => 'Gagal mengupdate data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// DELETE - Delete data
function delete($table, $id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM `$table` WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            return ['success' => true, 'message' => 'Data berhasil dihapus'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus data'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// COUNT - Count records
function count_records($table, $where = []) {
    try {
        $db = getDB();
        $sql = "SELECT COUNT(*) as total FROM `$table`";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

// SEARCH - Search with LIKE
function search($table, $column, $keyword, $limit = 50, $orderBy = 'id DESC') {
    try {
        $db = getDB();
        $sql = "SELECT * FROM `$table` WHERE `$column` LIKE ? ORDER BY $orderBy LIMIT $limit";
        $stmt = $db->prepare($sql);
        $stmt->execute(["%$keyword%"]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// MULTI SEARCH - Search multiple columns
function multi_search($table, $columns, $keyword, $limit = 50, $orderBy = 'id DESC') {
    try {
        $db = getDB();
        $conditions = [];
        foreach ($columns as $column) {
            $conditions[] = "`$column` LIKE ?";
        }
        $sql = "SELECT * FROM `$table` WHERE " . implode(' OR ', $conditions) . " ORDER BY $orderBy LIMIT $limit";
        $params = array_fill(0, count($columns), "%$keyword%");
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Pagination helper
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

// Custom Query Helper
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

// Execute Query (for INSERT/UPDATE/DELETE without return)
function execute($sql, $params = []) {
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}

// Get Last Insert ID
function last_insert_id() {
    try {
        $db = getDB();
        return $db->lastInsertId();
    } catch (PDOException $e) {
        return 0;
    }
}

// Check if record exists
function exists($table, $where) {
    return count_records($table, $where) > 0;
}

// Get distinct values
function get_distinct($table, $column, $where = []) {
    try {
        $db = getDB();
        $sql = "SELECT DISTINCT `$column` FROM `$table`";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "`$key` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY `$column` ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

// Batch insert
function batch_insert($table, $data_array) {
    try {
        $db = getDB();
        $db->beginTransaction();
        
        $success_count = 0;
        foreach ($data_array as $data) {
            $result = create($table, $data);
            if ($result['success']) {
                $success_count++;
            }
        }
        
        $db->commit();
        return [
            'success' => true,
            'count' => $success_count,
            'message' => "$success_count data berhasil ditambahkan"
        ];
    } catch (PDOException $e) {
        $db->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Get table info
function get_table_columns($table) {
    try {
        $db = getDB();
        $stmt = $db->query("SHOW COLUMNS FROM `$table`");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Format date for database
function format_date_db($date) {
    return date('Y-m-d', strtotime($date));
}

// Format datetime for database
function format_datetime_db($datetime) {
    return date('Y-m-d H:i:s', strtotime($datetime));
}

// Generate unique code
function generate_code($prefix = '', $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = $prefix;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Upload file helper
function upload_file($field_name, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    $file = $_FILES[$field_name];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_types)];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Max: ' . ($max_size / 1024 / 1024) . 'MB'];
    }
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'relative_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $filepath)
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Delete file helper
function delete_file($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}
?>