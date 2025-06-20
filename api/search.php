<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$search = $_GET['search'] ?? '';
$building = $_GET['building'] ?? '';
$floor = $_GET['floor'] ?? '';
$department = $_GET['department'] ?? '';

// อัปเดต API search ให้ใช้ฟิลด์ name แทน first_name และ last_name
$sql = "SELECT * FROM employees WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR position LIKE ? OR internal_phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($building)) {
    $sql .= " AND building = ?";
    $params[] = $building;
}

if (!empty($floor)) {
    $sql .= " AND floor = ?";
    $params[] = $floor;
}

if (!empty($department)) {
    $sql .= " AND department = ?";
    $params[] = $department;
}

$sql .= " ORDER BY name";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $employees = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $employees,
        'count' => count($employees)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
