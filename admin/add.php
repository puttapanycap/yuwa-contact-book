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

$message = '';
$messageType = '';

if ($_POST) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO employees (name, position, department, internal_phone, email, building, floor, room_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['position'],
            $_POST['department'],
            $_POST['internal_phone'],
            $_POST['email'],
            $_POST['building'],
            $_POST['floor'],
            $_POST['room_number']
        ]);
        
        $message = 'เพิ่มข้อมูลพนักงานเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มพนักงานใหม่ - สมุดโทรศัพท์ภายในองค์กร</title>
    
    <!-- Google Fonts - Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
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
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            เพิ่มพนักงานใหม่
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="addEmployeeForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="position" class="form-label">ตำแหน่ง <span class="text-danger">*</span></label>
                                    <select class="form-select select2-input" id="position" name="position" required data-placeholder="เลือกหรือพิมพ์ตำแหน่ง...">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="department" class="form-label">หน่วยงาน <span class="text-danger">*</span></label>
                                    <select class="form-select select2-input" id="department" name="department" required data-placeholder="เลือกหรือพิมพ์หน่วยงาน...">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="internal_phone" class="form-label">เบอร์โทรภายใน <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="internal_phone" name="internal_phone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">อีเมล</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="building" class="form-label">ตึก <span class="text-danger">*</span></label>
                                    <select class="form-select select2-input" id="building" name="building" required data-placeholder="เลือกหรือพิมพ์ชื่อตึก...">
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="floor" class="form-label">ชั้น <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="floor" name="floor" required min="1" max="50">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="room_number" class="form-label">หมายเลขห้อง</label>
                                    <input type="text" class="form-control" id="room_number" name="room_number">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i>
                                    ยกเลิก
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    บันทึก
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Load existing data for Select2
        loadSelectOptions();
        
        // Initialize Select2
        $('.select2-input').select2({
            theme: 'bootstrap-5',
            width: '100%',
            tags: true,
            tokenSeparators: [','],
            placeholder: function() {
                return $(this).data('placeholder');
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
        });

        function loadSelectOptions() {
            // Load positions
            $.get('../api/filter-options.php?type=positions', function(data) {
                if (data.success) {
                    data.data.forEach(function(item) {
                        $('#position').append(`<option value="${item.position}">${item.position}</option>`);
                    });
                }
            });

            // Load departments
            $.get('../api/filter-options.php?type=departments', function(data) {
                if (data.success) {
                    data.data.forEach(function(item) {
                        $('#department').append(`<option value="${item.department}">${item.department}</option>`);
                    });
                }
            });

            // Load buildings
            $.get('../api/filter-options.php?type=buildings', function(data) {
                if (data.success) {
                    data.data.forEach(function(item) {
                        $('#building').append(`<option value="${item.building}">${item.building}</option>`);
                    });
                }
            });
        }
    });
    </script>
</body>
</html>
