<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // DataTables parameters
    $draw = intval($_POST['draw']);
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $searchValue = $_POST['search']['value'];
    
    // Filter parameters
    $building = $_POST['building'] ?? '';
    $department = $_POST['department'] ?? '';
    
    // Base query
    $baseQuery = "FROM employees WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($building)) {
        $baseQuery .= " AND building = ?";
        $params[] = $building;
    }
    
    if (!empty($department)) {
        $baseQuery .= " AND department = ?";
        $params[] = $department;
    }
    
    // Apply search
    if (!empty($searchValue)) {
        $baseQuery .= " AND (department LIKE ? OR internal_phone LIKE ? OR building LIKE ?)";
        $searchParam = "%{$searchValue}%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    }
    
    // Get total records
    $totalQuery = "SELECT COUNT(*) " . $baseQuery;
    $stmt = $pdo->prepare($totalQuery);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
    
    // Get filtered records
    $filteredRecords = $totalRecords;
    
    // Apply ordering
    $orderColumn = $_POST['order'][0]['column'] ?? 0;
    $orderDir = $_POST['order'][0]['dir'] ?? 'asc';
    
    $columns = ['department', 'internal_phone', 'building'];
    $orderBy = $columns[$orderColumn] ?? 'department';
    
    $dataQuery = "SELECT * " . $baseQuery . " ORDER BY {$orderBy} {$orderDir}";
    
    // Apply pagination
    if ($length != -1) {
        $dataQuery .= " LIMIT {$length} OFFSET {$start}";
    }
    
    $stmt = $pdo->prepare($dataQuery);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $formattedData = [];
    foreach ($data as $row) {
        $formattedData[] = [
            'id' => $row['id'],
            'department' => $row['department'],
            'internal_phone' => $row['internal_phone'],
            'building' => $row['building'],
            'floor' => $row['floor'],
            'room_number' => $row['room_number'] ?: ''
        ];
    }
    
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $formattedData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'draw' => 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
?>
