<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'buildings':
            $stmt = $pdo->query("SELECT DISTINCT building FROM employees ORDER BY building");
            $data = $stmt->fetchAll();
            break;
            
        case 'floors':
            $stmt = $pdo->query("SELECT DISTINCT floor FROM employees ORDER BY floor");
            $data = $stmt->fetchAll();
            break;
            
        case 'departments':
            $stmt = $pdo->query("SELECT DISTINCT department FROM employees ORDER BY department");
            $data = $stmt->fetchAll();
            break;
            
        case 'positions':
            $stmt = $pdo->query("SELECT DISTINCT position FROM employees ORDER BY position");
            $data = $stmt->fetchAll();
            break;
            
        default:
            throw new Exception('Invalid type parameter');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
