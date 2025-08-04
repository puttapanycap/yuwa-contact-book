<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมุดโทรศัพท์ภายในองค์กร</title>

    <!-- Custom CSS -->
    <link href="./assets/css/style.bundle.css" rel="stylesheet">
    <!-- <link href="./assets/plugins/custom/datatables_org/datatables.css" rel="stylesheet" type="text/css"/> -->
    <link href="./assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="./assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>

    <link href="./assets/css/fonts.css" rel="stylesheet">
    <link href="./assets/css/fontawesome.css" rel="stylesheet">
    <link href="./assets/css/custom.css" rel="stylesheet">

    <style>
        .dt-processing {
            position: absolute;
            top: 50% !important;
            /* Move to center vertically */
            left: 50% !important;
            /* Move to center horizontally */
            transform: translate(-50%, -50%);
            /* Center precisely */
            background-color: rgba(255, 255, 255, 0.9);
            /* Add a semi-transparent background */
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default flex-column flex-row-fluid gap-4">

    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        document.documentElement.setAttribute("data-bs-theme", defaultThemeMode);
    </script>
    <!--end::Theme mode setup on page load-->

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

    <div class="container flex-column-fluid">
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
                                    <button type="button" class="btn btn-outline btn-outline-secondary btn-sm" id="clearFilters">
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
        <div class="card min-h-500px shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title text-white mb-0">
                    <i class="fas fa-phone-alt me-2"></i>
                    รายการเบอร์โทรภายใน
                    <span class="badge bg-light text-dark ms-2" id="resultCount">0</span>
                </h4>
                <div id="tableButtons"></div>
            </div>
            <div class="card-body">
                <table id="employeesTable" class="table table-hover gs-4 gy-4 gx-4 mb-0">
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

    <footer class="footer bg-dark text-white text-center mh-100px p-4">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    จัดทำโดย งานเทคโนโลยีสารสนเทศ (IT) โทร. 72152,72143
                </div>
            </div>
        </div>
    </footer>

    <!-- DataTables JS -->
    <!-- <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script> -->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> -->

    <!-- <script src="./assets/plugins/custom/datatables_org/datatables.min.js"></script> -->

    <script src="./assets/js/scripts.bundle.js"></script>
    <script src="./assets/plugins/global/plugins.bundle.js"></script>
    <script src="./assets/plugins/custom/datatables/datatables.bundle.js"></script>

    <script src="./assets/js/main.js"></script>

</body>

</html>