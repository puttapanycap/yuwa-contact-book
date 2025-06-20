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
    header('Location: login.php');
    exit();
}

$format = $_GET['format'] ?? 'csv';

// Get all employees
$stmt = $pdo->query("SELECT * FROM employees ORDER BY name");
$employees = $stmt->fetchAll();

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="employees_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV Headers
    fputcsv($output, [
        'ชื่อ-นามสกุล',
        'ตำแหน่ง',
        'หน่วยงาน',
        'เบอร์โทรภายใน',
        'อีเมล',
        'ตึก',
        'ชั้น',
        'หมายเลขห้อง',
        'วันที่สร้าง'
    ]);
    
    // CSV Data
    foreach ($employees as $employee) {
        fputcsv($output, [
            $employee['name'],
            $employee['position'],
            $employee['department'],
            $employee['internal_phone'],
            $employee['email'],
            $employee['building'],
            $employee['floor'],
            $employee['room_number'],
            $employee['created_at']
        ]);
    }
    
    fclose($output);
    exit();
}

// If not CSV, show export options page
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งออกข้อมูล - สมุดโทรศัพท์ภายในองค์กร</title>
    
    <!-- Google Fonts - Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i>
                จัดการระบบ
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['admin_name']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> ออกจากระบบ
                        </a></li>
                    </ul>
                </div>
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left me-1"></i>
                    กลับ
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-download me-2"></i>
                            ส่งออกข้อมูล
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">เลือกรูปแบบไฟล์ที่ต้องการส่งออก</p>
                        
                        <div class="d-grid gap-3">
                            <a href="export.php?format=csv" class="btn btn-success btn-lg">
                                <i class="fas fa-file-csv fa-2x mb-2"></i>
                                <br>
                                ส่งออกเป็น CSV
                                <small class="d-block">เหมาะสำหรับ Excel</small>
                            </a>
                            
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    ข้อมูลจะถูกส่งออกทั้งหมด <?= count($employees) ?> รายการ
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
