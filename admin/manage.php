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

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'ลบข้อมูลเรียบร้อยแล้ว';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get total employees count
$totalEmployees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลพนักงาน - สมุดโทรศัพท์ภายในองค์กร</title>
    
    <!-- Google Fonts - Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
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
                <a class="nav-link" href="add.php">
                    <i class="fas fa-plus me-1"></i>
                    เพิ่มพนักงาน
                </a>
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i>
                    กลับหน้าแรก
                </a>
            </div>
        </div>
    </nav>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="fas fa-filter me-2"></i>
                        กรองข้อมูล
                    </h6>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">ตึก</label>
                            <select class="form-select select2-admin-filter" id="adminBuildingFilter" data-placeholder="เลือกตึก...">
                                <option value="">ทั้งหมด</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">หน่วยงาน</label>
                            <select class="form-select select2-admin-filter" id="adminDepartmentFilter" data-placeholder="เลือกหน่วยงาน...">
                                <option value="">ทั้งหมด</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">การดำเนินการ</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAdminFilters">
                                    <i class="fas fa-times me-1"></i>
                                    ล้างตัวกรอง
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        จัดการข้อมูลพนักงาน
                        <span class="badge bg-light text-dark ms-2"><?= number_format($totalEmployees) ?> คน</span>
                    </h4>
                </div>
                <div id="tableButtons"></div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employeesTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ตำแหน่ง</th>
                                <th>หน่วยงาน</th>
                                <th>เบอร์โทรภายใน</th>
                                <th>อีเมล</th>
                                <th>ที่ตั้ง</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ยืนยันการลบข้อมูล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>คุณต้องการลบข้อมูลของ <strong id="employeeName"></strong> หรือไม่?</p>
                    <p class="text-danger"><small>การดำเนินการนี้ไม่สามารถยกเลิกได้</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">ลบข้อมูล</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Load filter options
            loadFilterOptions();
            
            // Initialize Select2 for admin filters
            $('.select2-admin-filter').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: function() {
                    return $(this).data('placeholder');
                }
            });

            // Initialize DataTable
            const table = $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '../api/admin-datatable.php',
                    type: 'POST',
                    data: function(d) {
                        d.building = $('#adminBuildingFilter').val();
                        d.department = $('#adminDepartmentFilter').val();
                    }
                },
                columns: [
                    {
                        data: 'name',
                        render: function(data, type, row) {
                            let html = `<strong>${data}</strong>`;
                            if (row.email) {
                                html += `<br><small class="text-muted">${row.email}</small>`;
                            }
                            return html;
                        }
                    },
                    { data: 'position' },
                    {
                        data: 'department',
                        render: function(data, type, row) {
                            return `<span class="badge bg-light text-dark">${data}</span>`;
                        }
                    },
                    {
                        data: 'internal_phone',
                        render: function(data, type, row) {
                            return `<a href="tel:${data}" class="text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>
                                        ${data}
                                    </a>`;
                        }
                    },
                    {
                        data: 'email',
                        render: function(data, type, row) {
                            if (data) {
                                return `<a href="mailto:${data}" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i>
                                            ${data}
                                        </a>`;
                            }
                            return '<span class="text-muted">-</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let location = `${row.building} ชั้น ${row.floor}`;
                            if (row.room_number) {
                                location += ` ห้อง ${row.room_number}`;
                            }
                            return `<small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        ${location}
                                    </small>`;
                        },
                        orderable: false
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="btn-group btn-group-sm" role="group">
                                        <a href="edit.php?id=${row.id}" class="btn btn-outline-primary" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" title="ลบ" 
                                                onclick="confirmDelete(${row.id}, '${row.name}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>`;
                        },
                        orderable: false
                    }
                ],
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json'
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
                order: [[0, 'asc']],
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i> พิมพ์',
                        className: 'btn btn-info btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5]
                        }
                    }
                ]
            });

            // Add buttons to card header
            table.buttons().container().appendTo('#tableButtons');

            // Custom search styling
            $('.dataTables_filter input').addClass('form-control').attr('placeholder', 'ค้นหา...');
            
            // Custom length menu styling
            $('.dataTables_length select').addClass('form-select');

            // Filter change events
            $('.select2-admin-filter').on('change', function() {
                table.ajax.reload();
            });

            // Clear filters button
            $('#clearAdminFilters').on('click', function() {
                $('.select2-admin-filter').val(null).trigger('change');
                table.ajax.reload();
            });

            // Load filter options function
            function loadFilterOptions() {
                // Load buildings
                $.get('../api/filter-options.php?type=buildings', function(data) {
                    if (data.success) {
                        $('#adminBuildingFilter').empty().append('<option value="">ทั้งหมด</option>');
                        data.data.forEach(function(item) {
                            $('#adminBuildingFilter').append(`<option value="${item.building}">${item.building}</option>`);
                        });
                    }
                });

                // Load departments
                $.get('../api/filter-options.php?type=departments', function(data) {
                    if (data.success) {
                        $('#adminDepartmentFilter').empty().append('<option value="">ทั้งหมด</option>');
                        data.data.forEach(function(item) {
                            $('#adminDepartmentFilter').append(`<option value="${item.department}">${item.department}</option>`);
                        });
                    }
                });
            }
        });

        function confirmDelete(id, name) {
            $('#employeeName').text(name);
            $('#confirmDeleteBtn').attr('href', '?delete=' + id);
            $('#deleteModal').modal('show');
        }
    </script>
</body>
</html>
