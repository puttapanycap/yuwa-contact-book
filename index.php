<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมุดโทรศัพท์ภายในองค์กร</title>
    
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
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-address-book me-2"></i>
                สมุดโทรศัพท์ภายในองค์กร
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin/login.php">
                    <i class="fas fa-cog me-1"></i>
                    จัดการระบบ
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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
                                <input type="text" class="form-control input-filter" id="roomNameFilter" placeholder="กรอกชื่อห้อง/จุดบริการ...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ตึก</label>
                                <select class="form-select select2-filter" id="buildingFilter" data-placeholder="เลือกตึก...">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ชั้น</label>
                                <select class="form-select select2-filter" id="floorFilter" data-placeholder="เลือกชั้น...">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">หน่วยงาน</label>
                                <select class="form-select select2-filter" id="departmentFilter" data-placeholder="เลือกหน่วยงาน...">
                                    <option value="">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label">การดำเนินการ</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFilters">
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

        <!-- Main Content -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-phone-alt me-2"></i>
                    รายการเบอร์โทรภายใน
                    <span class="badge bg-light text-dark ms-2" id="resultCount">0</span>
                </h4>
                <div id="tableButtons"></div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employeesTable" class="table table-hover mb-0" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อห้อง/จุดบริการ</th>
                                <th>หน่วยงาน</th>
                                <th>เบอร์โทรภายใน</th>
                                <th>ที่ตั้ง</th>
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
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
