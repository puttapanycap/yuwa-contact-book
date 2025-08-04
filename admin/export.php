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

$format = $_GET['format'] ?? '';

// Get all employees
$stmt = $pdo->query("SELECT * FROM employees ORDER BY department, internal_phone");
$employees = $stmt->fetchAll();

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="phonebook_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV Headers
    fputcsv($output, [
        'หน่วยงาน',
        'เบอร์โทรภายใน',
        'ตึก',
        'ชั้น',
        'ชื่อห้อง/จุดบริการ',
        'วันที่สร้าง'
    ]);
    
    // CSV Data
    foreach ($employees as $employee) {
        fputcsv($output, [
            $employee['department'],
            $employee['internal_phone'],
            $employee['building'],
            $employee['floor'],
            $employee['room_name'],
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
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.bundle.css" rel="stylesheet">
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

    <link href="../assets/css/fonts.css" rel="stylesheet">
    <link href="../assets/css/fontawesome.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    
    <style>
        .export-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .export-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .export-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
    </style>
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
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h4 class="card-title text-inverse-info mb-0">
                            <i class="fas fa-download me-2"></i>
                            ส่งออกข้อมูลสมุดโทรศัพท์
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>ข้อมูลที่จะส่งออก:</strong> <?= count($employees) ?> รายการ
                            <br>
                            <small>ข้อมูลจะรวม: หน่วยงาน, เบอร์โทรภายใน, ที่ตั้ง, วันที่สร้าง</small>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card export-card border-success h-100" onclick="exportData('csv')">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-csv export-icon text-success"></i>
                                        <h5 class="card-title">ส่งออกเป็น CSV</h5>
                                        <p class="card-text text-muted">
                                            เหมาะสำหรับ Microsoft Excel<br>
                                            และโปรแกรมสเปรดชีต
                                        </p>
                                        <div class="mt-3">
                                            <span class="badge bg-success">แนะนำ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card export-card border-primary h-100" onclick="printData()">
                                    <div class="card-body text-center">
                                        <i class="fas fa-print export-icon text-primary"></i>
                                        <h5 class="card-title">พิมพ์รายการ</h5>
                                        <p class="card-text text-muted">
                                            พิมพ์รายการโทรศัพท์<br>
                                            สำหรับติดบอร์ด
                                        </p>
                                        <div class="mt-3">
                                            <span class="badge bg-primary">สะดวก</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <i class="fas fa-eye me-2"></i>
                                    ตัวอย่างข้อมูล
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>หน่วยงาน</th>
                                                <th>เบอร์โทรภายใน</th>
                                                <th>ที่ตั้ง</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($employees, 0, 5) as $employee): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($employee['department']) ?></td>
                                                    <td><?= htmlspecialchars($employee['internal_phone']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($employee['building']) ?> ชั้น <?= $employee['floor'] ?>
                                                        <?php if ($employee['room_name']): ?>
                                                            ห้อง <?= htmlspecialchars($employee['room_name']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (count($employees) > 5): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">
                                                        ... และอีก <?= count($employees) - 5 ?> รายการ
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div class="modal fade" id="printModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-print me-2"></i>
                        รายการโทรศัพท์ภายในองค์กร
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="printContent">
                    <div class="text-center mb-4">
                        <h4>สมุดโทรศัพท์ภายในองค์กร</h4>
                        <p class="text-muted">อัปเดตล่าสุด: <?= date('d/m/Y H:i') ?></p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>หน่วยงาน</th>
                                    <th>เบอร์โทรภายใน</th>
                                    <th>ที่ตั้ง</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($employee['department']) ?></td>
                                        <td><strong><?= htmlspecialchars($employee['internal_phone']) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($employee['building']) ?> ชั้น <?= $employee['floor'] ?>
                                            <?php if ($employee['room_name']): ?>
                                                ห้อง <?= htmlspecialchars($employee['room_name']) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        พิมพ์
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/scripts.bundle.js"></script>
    <script src="../assets/plugins/global/plugins.bundle.js"></script>
    <script src="../assets/plugins/custom/datatables/datatables.bundle.js"></script>

    <script>
        function exportData(format) {
            if (format === 'csv') {
                window.location.href = 'export.php?format=csv';
            }
        }

        function printData() {
            const printModal = new bootstrap.Modal(document.getElementById('printModal'));
            printModal.show();
        }

        // Add hover effects
        document.querySelectorAll('.export-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.borderWidth = '2px';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.borderWidth = '1px';
            });
        });
    </script>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printContent, #printContent * {
                visibility: visible;
            }
            #printContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .modal-header, .modal-footer {
                display: none !important;
            }
        }
    </style>
</body>
</html>
