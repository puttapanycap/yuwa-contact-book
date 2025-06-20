<?php
// Session security settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_set_cookie_params([
        'lifetime' => 7200,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// DataTables server-side processing
$request = $_POST;

// Base query
$baseQuery = "FROM employees WHERE 1=1";
$params = [];

// Search functionality
$searchValue = $request['search']['value'] ?? '';
if (!empty($searchValue)) {
    $baseQuery .= " AND (name LIKE ? OR position LIKE ? OR department LIKE ? OR internal_phone LIKE ? OR email LIKE ?)";
    $searchTerm = "%$searchValue%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Apply filters
if (!empty($request['building'])) {
    $baseQuery .= " AND building = ?";
    $params[] = $request['building'];
}

if (!empty($request['department'])) {
    $baseQuery .= " AND department = ?";
    $params[] = $request['department'];
}

if (!empty($request['floor'])) {
    $baseQuery .= " AND floor = ?";
    $params[] = $request['floor'];
}

if (!empty($request['position'])) {
    $baseQuery .= " AND position = ?";
    $params[] = $request['position'];
}

// Count total records
$totalQuery = "SELECT COUNT(*) as count " . $baseQuery;
$stmt = $pdo->prepare($totalQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch()['count'];

// Count filtered records (same as total if no search)
$filteredRecords = $totalRecords;

// Ordering
$columns = ['name', 'position', 'department', 'internal_phone', 'email', 'building', 'id'];
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
        'id' => $row['id'],
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
