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
$employee = null;

// Get employee ID from URL
$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($employeeId <= 0) {
    header('Location: manage.php');
    exit();
}

// Get employee data
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$employeeId]);
    $employee = $stmt->fetch();
    
    if (!$employee) {
        header('Location: manage.php');
        exit();
    }
} catch (PDOException $e) {
    $message = 'เกิดข้อผิดพลาดในการโหลดข้อมูล: ' . $e->getMessage();
    $messageType = 'danger';
}

// Handle form submission
if ($_POST && $employee) {
    try {
        $stmt = $pdo->prepare("
            UPDATE employees SET 
            name = ?, position = ?, department = ?, 
            internal_phone = ?, email = ?, 
            building = ?, floor = ?, room_number = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['position'],
            $_POST['department'],
            $_POST['internal_phone'],
            $_POST['email'],
            $_POST['building'],
            $_POST['floor'],
            $_POST['room_number'],
            $employeeId
        ]);
        
        // Log the activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, 'update', 'employees', ?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_id'],
            $employeeId,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        $message = 'แก้ไขข้อมูลพนักงานเรียบร้อยแล้ว';
        $messageType = 'success';
        
        // Refresh employee data
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->execute([$employeeId]);
        $employee = $stmt->fetch();
        
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
    <title>แก้ไขข้อมูลพนักงาน - สมุดโทรศัพท์ภายในองค์กร</title>
    
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
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
                <a class="nav-link" href="manage.php">
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
                    <div class="card-header bg-warning text-dark">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-user-edit me-2"></i>
                            แก้ไขข้อมูลพนักงาน
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($employee): ?>
                            <form method="POST" id="editEmployeeForm">
                                <div class="mb-3">
                                    <label for="name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($employee['name']) ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="position" class="form-label">ตำแหน่ง <span class="text-danger">*</span></label>
                                        <select class="form-select select2-input" id="position" name="position" required data-placeholder="เลือกหรือพิมพ์ตำแหน่ง...">
                                            <option value="<?= htmlspecialchars($employee['position']) ?>" selected><?= htmlspecialchars($employee['position']) ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">หน่วยงาน <span class="text-danger">*</span></label>
                                        <select class="form-select select2-input" id="department" name="department" required data-placeholder="เลือกหรือพิมพ์หน่วยงาน...">
                                            <option value="<?= htmlspecialchars($employee['department']) ?>" selected><?= htmlspecialchars($employee['department']) ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="internal_phone" class="form-label">เบอร์โทรภายใน <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="internal_phone" name="internal_phone" 
                                               value="<?= htmlspecialchars($employee['internal_phone']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">อีเมล</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($employee['email']) ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="building" class="form-label">ตึก <span class="text-danger">*</span></label>
                                        <select class="form-select select2-input" id="building" name="building" required data-placeholder="เลือกหรือพิมพ์ชื่อตึก...">
                                            <option value="<?= htmlspecialchars($employee['building']) ?>" selected><?= htmlspecialchars($employee['building']) ?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="floor" class="form-label">ชั้น <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="floor" name="floor" 
                                               value="<?= htmlspecialchars($employee['floor']) ?>" required min="1" max="50">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="room_number" class="form-label">หมายเลขห้อง</label>
                                        <input type="text" class="form-control" id="room_number" name="room_number" 
                                               value="<?= htmlspecialchars($employee['room_number']) ?>">
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="manage.php" class="btn btn-secondary me-md-2">
                                        <i class="fas fa-times me-1"></i>
                                        ยกเลิก
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-1"></i>
                                        บันทึกการแก้ไข
                                    </button>
                                </div>
                            </form>
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

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Form validation
            $('#editEmployeeForm').on('submit', function(e) {
                let isValid = true;

                // Check required fields
                $(this).find('[required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Validate phone numbers
                const internalPhone = $('#internal_phone').val();
                const mobilePhone = $('#mobile_phone').val();

                if (internalPhone && !/^\d+$/.test(internalPhone)) {
                    $('#internal_phone').addClass('is-invalid');
                    isValid = false;
                }

                if (mobilePhone && !/^[0-9-+\s()]+$/.test(mobilePhone)) {
                    $('#mobile_phone').addClass('is-invalid');
                    isValid = false;
                }

                // Validate email
                const email = $('#email').val();
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('#email').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('กรุณากรอกข้อมูลให้ถูกต้องและครบถ้วน');
                }
            });

            // Remove invalid class on input
            $('.form-control').on('input', function() {
                $(this).removeClass('is-invalid');
            });

            // Initialize Select2
            $('.select2-input').select2({
                theme: 'bootstrap-5',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
                tags: true,
            });

            // Fetch existing data for Select2 fields
            const employeeData = <?php echo json_encode($employee); ?>;

            // Function to fetch and set options for a Select2 field
            function setSelect2Options(selectId, dataField) {
                const selectElement = $('#' + selectId);
                const initialValue = employeeData[dataField];

                // Create a new option with the existing value
                const newOption = new Option(initialValue, initialValue, true, true);
                selectElement.append(newOption).trigger('change');

                // Fetch distinct values from the database (replace with your actual AJAX call)
                $.ajax({
                    url: 'get_select_options.php', // Replace with your endpoint
                    type: 'GET',
                    data: { field: dataField },
                    dataType: 'json',
                    success: function(data) {
                        // Add fetched options to the Select2 field
                        $.each(data, function(index, value) {
                            if (value !== initialValue) {
                                const newOption = new Option(value, value, false, false);
                                selectElement.append(newOption);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching options for " + selectId + ":", error);
                    }
                });
            }

            // Set options for position, department, and building
            setSelect2Options('position', 'position');
            setSelect2Options('department', 'department');
            setSelect2Options('building', 'building');
        });
    </script>
</body>
</html>
