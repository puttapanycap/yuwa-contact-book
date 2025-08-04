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

// Get department statistics
$departmentStats = $pdo->query("
    SELECT department, COUNT(*) as count 
    FROM employees 
    GROUP BY department 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll();

// Get building statistics
$buildingStats = $pdo->query("
    SELECT building, COUNT(*) as count 
    FROM employees 
    GROUP BY building 
    ORDER BY count DESC
")->fetchAll();

// Get recent employees
$recentEmployees = $pdo->query("
    SELECT * FROM employees 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการระบบ - สมุดโทรศัพท์ภายในองค์กร</title>
    
    <!-- Custom CSS -->
    <link href="../assets/css/style.bundle.css" rel="stylesheet">
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>

    <link href="../assets/css/fonts.css" rel="stylesheet">
    <link href="../assets/css/fontawesome.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    
    <style>
        .stats-card {
            transition: transform 0.2s ease-in-out;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .recent-item {
            border-left: 4px solid #0d6efd;
            transition: all 0.2s ease-in-out;
        }
        .recent-item:hover {
            background-color: #f8f9fa;
            border-left-color: #198754;
        }
        .action-card {
            border: none;
            transition: all 0.3s ease;
        }
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
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

    <div class="container-fluid mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-primary border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-shield fa-2x me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-1">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h5>
                            <p class="mb-0">จัดการข้อมูลสมุดโทรศัพท์ภายในองค์กร</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card bg-primary text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="card-title mb-1 text-inverse-primary"><?= number_format($totalEmployees) ?></h2>
                                <p class="card-text mb-0">รายการทั้งหมด</p>
                                <small class="opacity-75">เบอร์โทรภายใน</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-phone fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-success text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="card-title mb-1 text-inverse-success"><?= number_format($totalBuildings) ?></h2>
                                <p class="card-text mb-0">ตึกทั้งหมด</p>
                                <small class="opacity-75">อาคารในองค์กร</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-building fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-info text-white shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="card-title mb-1 text-inverse-info"><?= number_format($totalDepartments) ?></h2>
                                <p class="card-text mb-0">หน่วยงานทั้งหมด</p>
                                <small class="opacity-75">แผนกในองค์กร</small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-sitemap fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3">
                    <i class="fas fa-tools me-2"></i>
                    การจัดการข้อมูล
                </h5>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card action-card h-100 border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                        <h6 class="card-title">เพิ่มข้อมูลใหม่</h6>
                        <p class="card-text text-muted">เพิ่มเบอร์โทรภายในใหม่</p>
                        <a href="manage.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            เพิ่มข้อมูล
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card action-card h-100 border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-edit fa-3x text-success mb-3"></i>
                        <h6 class="card-title">จัดการข้อมูล</h6>
                        <p class="card-text text-muted">แก้ไข ลบ ข้อมูลที่มีอยู่</p>
                        <a href="manage.php" class="btn btn-success">
                            <i class="fas fa-cog me-1"></i>
                            จัดการ
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card action-card h-100 border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-download fa-3x text-info mb-3"></i>
                        <h6 class="card-title">ส่งออกข้อมูล</h6>
                        <p class="card-text text-muted">ดาวน์โหลดข้อมูลเป็นไฟล์</p>
                        <a href="export.php" class="btn btn-info">
                            <i class="fas fa-file-export me-1"></i>
                            ส่งออก
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card action-card h-100 border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                        <h6 class="card-title">รายงาน</h6>
                        <p class="card-text text-muted">ดูสถิติและรายงาน</p>
                        <button class="btn btn-warning" onclick="scrollToCharts()">
                            <i class="fas fa-chart-line me-1"></i>
                            ดูรายงาน
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4" id="chartsSection">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0 text-inverse-primary">
                            <i class="fas fa-chart-pie me-2"></i>
                            สถิติตามหน่วยงาน
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0 text-inverse-success">
                            <i class="fas fa-chart-bar me-2"></i>
                            สถิติตามอาคาร
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="buildingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Entries -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>
                            รายการล่าสุด
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentEmployees)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">ยังไม่มีข้อมูล</p>
                                <a href="manage.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    เพิ่มข้อมูลแรก
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($recentEmployees as $employee): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card recent-item h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-1">
                                                            <span class="badge badge-lg badge-primary"><?= htmlspecialchars($employee['department']) ?></span>
                                                        </h6>
                                                        <p class="card-text mb-2">
                                                            <i class="fas fa-phone text-success me-2"></i>
                                                            <strong><?= htmlspecialchars($employee['internal_phone']) ?></strong>
                                                        </p>
                                                        <p class="card-text text-muted mb-0">
                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                            <?= htmlspecialchars($employee['building']) ?> ชั้น <?= $employee['floor'] ?>
                                                            <?php if ($employee['room_name']): ?>
                                                                ห้อง <?= htmlspecialchars($employee['room_name']) ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($employee['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="manage.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>
                                    ดูทั้งหมด
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/scripts.bundle.js"></script>
    <script src="../assets/plugins/global/plugins.bundle.js"></script>
    <script src="../assets/plugins/custom/datatables/datatables.bundle.js"></script>

    <script>
        // Department Chart
        const departmentData = <?= json_encode($departmentStats) ?>;
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: departmentData.map(item => item.department),
                datasets: [{
                    data: departmentData.map(item => item.count),
                    backgroundColor: [
                        '#0d6efd',
                        '#198754',
                        '#dc3545',
                        '#ffc107',
                        '#6f42c1'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'LINESeedSansTH'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Building Chart
        const buildingData = <?= json_encode($buildingStats) ?>;
        const buildingCtx = document.getElementById('buildingChart').getContext('2d');
        new Chart(buildingCtx, {
            type: 'bar',
            data: {
                labels: buildingData.map(item => item.building),
                datasets: [{
                    label: 'จำนวนเบอร์โทร',
                    data: buildingData.map(item => item.count),
                    backgroundColor: '#198754',
                    borderColor: '#146c43',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            font: {
                                family: 'LINESeedSansTH'
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Scroll to charts function
        function scrollToCharts() {
            document.getElementById('chartsSection').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Auto refresh stats every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000);

        // Add some animations
        $(document).ready(() => {
            $('.stats-card').each(function(index) {
                $(this).delay(index * 100).fadeIn(500);
            });
            
            $('.action-card').each(function(index) {
                $(this).delay((index + 3) * 100).fadeIn(500);
            });
        });
    </script>
</body>
</html>
