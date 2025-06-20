<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? '';
    
    switch ($type) {
        case 'buildings':
            $stmt = $pdo->query("SELECT DISTINCT building FROM employees WHERE building IS NOT NULL AND building != '' ORDER BY building");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        
        case 'departments':
            $stmt = $pdo->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != '' ORDER BY department");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        
        case 'floors':
            $stmt = $pdo->query("SELECT DISTINCT floor FROM employees WHERE floor IS NOT NULL ORDER BY floor");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        'message' => $e->getMessage()
    ]);
}
?>
