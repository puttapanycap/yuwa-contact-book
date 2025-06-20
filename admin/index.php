<?php
// Session security settings - ต้องตั้งค่าก่อนเริ่ม session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_set_cookie_params([
        'lifetime' => 7200, // 2 hours
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

// Get statistics
$totalEmployees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$totalBuildings = $pdo->query("SELECT COUNT(DISTINCT building) FROM employees")->fetchColumn();
$totalDepartments = $pdo->query("SELECT COUNT(DISTINCT department) FROM employees")->fetchColumn();

// Get recent employees
$recentEmployees = $pdo->query("SELECT * FROM employees ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการระบบ - สมุดโทรศัพท์ภายในองค์กร</title>
    
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
                        <li><h6 class="dropdown-header">ผู้ใช้: <?= htmlspecialchars($_SESSION['admin_username']) ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-1"></i> โปรไฟล์
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cog me-1"></i> ตั้งค่า
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> ออกจากระบบ
                        </a></li>
                    </ul>
                </div>
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i>
                    กลับหน้าแรก
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($totalEmployees) ?></h4>
                                <p class="card-text">พนักงานทั้งหมด</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($totalBuildings) ?></h4>
                                <p class="card-text">ตึกทั้งหมด</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?= number_format($totalDepartments) ?></h4>
                                <p class="card-text">หน่วยงานทั้งหมด</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-sitemap fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">การจัดการข้อมูล</h5>
                        <div class="btn-group" role="group">
                            <a href="add.php" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>
                                เพิ่มพนักงานใหม่
                            </a>
                            <a href="manage.php" class="btn btn-primary">
                                <i class="fas fa-list me-1"></i>
                                จัดการข้อมูลพนักงาน
                            </a>
                            <a href="export.php" class="btn btn-info">
                                <i class="fas fa-download me-1"></i>
                                ส่งออกข้อมูล
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Employees -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>
                            พนักงานที่เพิ่มล่าสุด
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEmployees)): ?>
                            <p class="text-muted text-center">ยังไม่มีข้อมูลพนักงาน</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ชื่อ-นามสกุล</th>
                                            <th>ตำแหน่ง</th>
                                            <th>หน่วยงาน</th>
                                            <th>เบอร์โทรภายใน</th>
                                            <th>วันที่เพิ่ม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentEmployees as $employee): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($employee['name']) ?></td>
                                                <td><?= htmlspecialchars($employee['position']) ?></td>
                                                <td><?= htmlspecialchars($employee['department']) ?></td>
                                                <td><?= htmlspecialchars($employee['internal_phone']) ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($employee['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
