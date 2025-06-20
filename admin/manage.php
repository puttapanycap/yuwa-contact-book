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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("
                    INSERT INTO employees (department, internal_phone, building, floor, room_name) 
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $_POST['department'],
                    $_POST['internal_phone'],
                    $_POST['building'],
                    $_POST['floor'],
                    $_POST['room_name']
                ]);

                echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว']);
                break;

            case 'edit':
                $stmt = $pdo->prepare("
                    UPDATE employees SET 
                    department = ?, internal_phone = ?, 
                    building = ?, floor = ?, room_name = ?, updated_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([
                    $_POST['department'],
                    $_POST['internal_phone'],
                    $_POST['building'],
                    $_POST['floor'],
                    $_POST['room_name'],
                    $_POST['id']
                ]);

                echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
                break;

            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
                $stmt->execute([$_POST['id']]);

                echo json_encode(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
                break;

            case 'get':
                $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $employee = $stmt->fetch();

                if ($employee) {
                    echo json_encode(['success' => true, 'data' => $employee]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน']);
                }
                break;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
    exit();
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

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

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
                <button type="button" class="btn btn-success nav-link" id="addEmployeeBtn">
                    <i class="fas fa-plus me-1"></i>
                    เพิ่มห้อง/จุดบริการ
                </button>
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-home me-1"></i>
                    กลับหน้าแรก
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
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
                            <div class="col-md-3">
                                <label class="form-label">ชื่อห้อง/จุดบริการ</label>
                                <input type="text" class="form-control form-input-filter" id="adminRoomNameFilter" placeholder="กรอกชื่อห้อง/จุดบริการ..." />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">หน่วยงาน</label>
                                <select class="form-select select2-admin-filter" id="adminDepartmentFilter" data-placeholder="เลือกหน่วยงาน...">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ตึก</label>
                                <select class="form-select select2-admin-filter" id="adminBuildingFilter" data-placeholder="เลือกตึก...">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ชั้น</label>
                                <select class="form-select select2-admin-filter" id="adminFloorFilter" data-placeholder="เลือกชั้น...">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-auto">
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
                                <th>ชื่อห้อง/จุดบริการ</th>
                                <th>หน่วยงาน</th>
                                <th>เบอร์โทรภายใน</th>
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

    <!-- Employee Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        เพิ่มห้อง/จุดบริการใหม่
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="employeeForm">
                        <input type="hidden" id="employeeId" name="id">

                        <div class="mb-3">
                            <label for="room_name" class="form-label">ชื่อห้อง/จุดบริการ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="employeeRoom" name="room_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="employeePhone" class="form-label">เบอร์โทรภายใน <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="employeePhone" name="internal_phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="employeeDepartment" class="form-label">หน่วยงาน <span class="text-danger">*</span></label>
                            <select class="form-select select2-modal" id="employeeDepartment" name="department" required data-placeholder="เลือกหรือพิมพ์หน่วยงาน...">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employeeBuilding" class="form-label">ตึก <span class="text-danger">*</span></label>
                                <select class="form-select select2-modal" id="employeeBuilding" name="building" required data-placeholder="เลือกหรือพิมพ์ชื่อตึก...">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="employeeFloor" class="form-label">ชั้น <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="employeeFloor" name="floor" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        ยกเลิก
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEmployeeBtn">
                        <i class="fas fa-save me-1"></i>
                        บันทึก
                    </button>
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let table;
            let isEditMode = false;

            // Load filter options
            loadFilterOptions();
            loadSelectOptions();

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
            table = $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '../api/admin-datatable.php',
                    type: 'POST',
                    data: function(d) {
                        d.room_name = $('#adminRoomNameFilter').val();
                        d.building = $('#adminBuildingFilter').val();
                        d.department = $('#adminDepartmentFilter').val();
                        d.floor = $('#adminFloorFilter').val();
                    }
                },
                columns: [{
                        data: 'room_name',
                        render: function(data, type, row) {
                            return `<span class="badge bg-primary text-white fs-6">${data}</span>`;
                        }
                    },{
                        data: 'department',
                        render: function(data, type, row) {
                            return `<span class="badge bg-light text-dark fs-6">${data}</span>`;
                        }
                    },
                    {
                        data: 'internal_phone',
                        render: function(data, type, row) {
                            return `<a href="tel:${data}" class="text-decoration-none fs-5 fw-bold">
                                        <i class="fas fa-phone me-2 text-success"></i>
                                        ${data}
                                    </a>`;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let location = `${row.building} ชั้น ${row.floor}`;
                            // if (row.room_name) {
                            //     location += ` ห้อง ${row.room_name}`;
                            // }
                            return `<div class="text-muted">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        ${location}
                                    </div>`;
                        },
                        orderable: false
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `<div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary edit-btn" data-id="${row.id}" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger delete-btn" data-id="${row.id}" data-name="${row.department}" title="ลบ">
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
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "ทั้งหมด"]
                ],
                order: [
                    [0, 'asc']
                ],
                buttons: [{
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
            
            $('.form-input-filter').on('keyup', function() {
                table.ajax.reload();
            });

            // Clear filters button
            $('#clearAdminFilters').on('click', function() {
                $('.form-input-filter').val(null);
                $('.select2-admin-filter').val(null).trigger('change');
                table.ajax.reload();
            });

            // Add employee button
            $('#addEmployeeBtn').on('click', function() {
                isEditMode = false;

                loadSelectOptions();

                $('#employeeModalLabel').html('<i class="fas fa-user-plus me-2"></i>เพิ่มห้อง/จุดบริการใหม่');
                $('#saveEmployeeBtn').html('<i class="fas fa-save me-1"></i>บันทึก');
                $('#employeeForm')[0].reset();
                $('#employeeId').val('');
                $('.select2-modal').val(null).trigger('change');
                $('#employeeModal').modal('show');
            });

            // Edit employee button
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                isEditMode = true;
                $('#employeeModalLabel').html('<i class="fas fa-user-edit me-2"></i>แก้ไขข้อมูลพนักงาน');
                $('#saveEmployeeBtn').html('<i class="fas fa-save me-1"></i>บันทึกการแก้ไข');

                // Get employee data
                $.post('manage.php', {
                    action: 'get',
                    id: id
                }, function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#employeeId').val(data.id);
                        $('#employeePhone').val(data.internal_phone);
                        $('#employeeFloor').val(data.floor);
                        $('#employeeRoom').val(data.room_name);

                        // Set Select2 values
                        // setSelect2Value('#employeeDepartment', data.department);
                        // setSelect2Value('#employeeBuilding', data.building);

                        loadSelectOptions('edit', {
                            department: data.department,
                            building: data.building
                        });

                        $('#employeeModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.message
                        });
                    }
                }, 'json');
            });

            // Delete employee button
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                Swal.fire({
                    title: 'ยืนยันการลบข้อมูล',
                    text: `คุณต้องการลบข้อมูลของ "${name}" หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('manage.php', {
                            action: 'delete',
                            id: id
                        }, function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'สำเร็จ',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: response.message
                                });
                            }
                        }, 'json');
                    }
                });
            });

            // Save employee button
            $('#saveEmployeeBtn').on('click', function() {
                if (validateForm()) {
                    const formData = $('#employeeForm').serialize();
                    const action = isEditMode ? 'edit' : 'add';

                    $.post('manage.php', formData + '&action=' + action, function(response) {
                        if (response.success) {
                            $('#employeeModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadFilterOptions();
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message
                            });
                        }
                    }, 'json');
                }
            });

            // Initialize Select2 for modal
            $('#employeeModal').on('shown.bs.modal', function() {
                $('.select2-modal').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    tags: true,
                    tokenSeparators: [','],
                    dropdownParent: $('#employeeModal'),
                    placeholder: function() {
                        return $(this).data('placeholder');
                    },
                    createTag: function(params) {
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
                
                // Load Floor
                $.get('../api/filter-options.php?type=floors', function(data) {
                    if (data.success) {
                        $('#adminFloorFilter').empty().append('<option value="">ทั้งหมด</option>');
                        data.data.forEach(function(item) {
                            $('#adminFloorFilter').append(`<option value="${item.floor}">${item.floor}</option>`);
                        });
                    }
                });
            }

            // Load select options for modal
            function loadSelectOptions(action = 'add', ids = {}) {

                // Load departments
                $.get('../api/filter-options.php?type=departments', function(data) {
                    $('#employeeDepartment').empty().append('<option value="">เลือกหรือพิมพ์หน่วยงาน...</option>');
                    if (data.success) {
                        data.data.forEach(function(item) {
                            $('#employeeDepartment').append(`<option value="${item.department}">${item.department}</option>`);
                        });
                        if (action === 'edit') {
                            // setSelect2Value('#employeeDepartment', ids.department);
                            $('#employeeDepartment').val(ids.department).trigger('change');
                        }
                    }
                });

                // Load buildings
                $.get('../api/filter-options.php?type=buildings', function(data) {
                    $('#employeeBuilding').empty().append('<option value="">เลือกหรือพิมพ์ชื่อตึก...</option>');
                    if (data.success) {
                        data.data.forEach(function(item) {
                            $('#employeeBuilding').append(`<option value="${item.building}">${item.building}</option>`);
                        });
                        if (action === 'edit') {
                            // setSelect2Value('#employeeBuilding', ids.building);
                            $('#employeeBuilding').val(ids.building).trigger('change');
                        }
                    }
                });
            }

            // Set Select2 value
            // function setSelect2Value(selector, value) {
            //     if (value) {
            //         const option = new Option(value, value, true, true);
            //         $(selector).append(option).trigger('change');
            //     }
            // }

            // Form validation
            function validateForm() {
                let isValid = true;

                // Remove previous validation classes
                $('.form-control, .form-select').removeClass('is-invalid');

                // Check required fields
                $('#employeeForm [required]').each(function() {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    }
                });

                // Validate phone number
                const phone = $('#employeePhone').val();
                if (phone && !/^\d+$/.test(phone)) {
                    $('#employeePhone').addClass('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ข้อมูลไม่ครบถ้วน',
                        text: 'กรุณากรอกข้อมูลให้ถูกต้องและครบถ้วน'
                    });
                }

                return isValid;
            }

            // Remove invalid class on input
            $(document).on('input change', '.form-control, .form-select', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
</body>

</html>