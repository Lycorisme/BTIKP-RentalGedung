<?php
// modules/crud.php
// Universal CRUD Operations

require_once __DIR__ . '/../config/database.php';

// CREATE - Insert data
function create($table, $data) {
    try {
        $db = getDB();
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute(array_values($data))) {
            return ['success' => true, 'id' => $db->lastInsertId()];
        }
        return ['success' => false, 'message' => 'Insert failed'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// READ - Get data with optional conditions
function read($table, $where = [], $limit = 100, $offset = 0, $orderBy = 'id DESC') {
    try {
        $db = getDB();
        $sql = "SELECT * FROM $table";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
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

// UPDATE - Update data
function update($table, $id, $data) {
    try {
        $db = getDB();
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute($params)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Update failed'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// DELETE - Delete data
function delete($table, $id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM $table WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Delete failed'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// COUNT - Count records
function count_records($table, $where = []) {
    try {
        $db = getDB();
        $sql = "SELECT COUNT(*) as total FROM $table";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
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
function search($table, $column, $keyword, $limit = 50) {
    try {
        $db = getDB();
        $sql = "SELECT * FROM $table WHERE $column LIKE ? LIMIT $limit";
        $stmt = $db->prepare($sql);
        $stmt->execute(["%$keyword%"]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Pagination helper
function paginate($table, $where = [], $page = 1, $perPage = 10) {
    $offset = ($page - 1) * $perPage;
    $data = read($table, $where, $perPage, $offset);
    $total = count_records($table, $where);
    $totalPages = ceil($total / $perPage);
    
    return [
        'data' => $data,
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages
    ];
}
?>