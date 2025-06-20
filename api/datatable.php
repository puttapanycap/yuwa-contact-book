<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// DataTables server-side processing
$request = $_POST;

// Base query
$baseQuery = "FROM employees WHERE 1=1";
$params = [];

// Apply filters
if (!empty($request['building'])) {
    $baseQuery .= " AND building = ?";
    $params[] = $request['building'];
}

if (!empty($request['floor'])) {
    $baseQuery .= " AND floor = ?";
    $params[] = $request['floor'];
}

if (!empty($request['department'])) {
    $baseQuery .= " AND department = ?";
    $params[] = $request['department'];
}

// Search functionality
$searchValue = $request['search']['value'] ?? '';
if (!empty($searchValue)) {
    $baseQuery .= " AND (name LIKE ? OR position LIKE ? OR department LIKE ? OR internal_phone LIKE ? OR email LIKE ?)";
    $searchTerm = "%$searchValue%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Count total records
$totalQuery = "SELECT COUNT(*) as count " . $baseQuery;
$stmt = $pdo->prepare($totalQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch()['count'];

// Count filtered records (same as total if no search)
$filteredRecords = $totalRecords;

// Ordering
$columns = ['name', 'position', 'department', 'internal_phone', 'email', 'building'];
$orderColumn = $columns[$request['order'][0]['column']] ?? 'name';
$orderDir = $request['order'][0]['dir'] ?? 'asc';

// Pagination
$start = $request['start'] ?? 0;
$length = $request['length'] ?? 25;

// Final query
$dataQuery = "SELECT * " . $baseQuery . " ORDER BY $orderColumn $orderDir";
if ($length != -1) {
    $dataQuery .= " LIMIT $length OFFSET $start";
}

$stmt = $pdo->prepare($dataQuery);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Format data for DataTables
$formattedData = [];
foreach ($data as $row) {
    $formattedData[] = [
        'name' => $row['name'],
        'position' => $row['position'],
        'department' => $row['department'],
        'internal_phone' => $row['internal_phone'],
        'email' => $row['email'] ?? '',
        'building' => $row['building'],
        'floor' => $row['floor'],
        'room_number' => $row['room_number'] ?? ''
    ];
}

// Response
$response = [
    'draw' => intval($request['draw']),
    'recordsTotal' => intval($totalRecords),
    'recordsFiltered' => intval($filteredRecords),
    'data' => $formattedData
];

echo json_encode($response);
?>
