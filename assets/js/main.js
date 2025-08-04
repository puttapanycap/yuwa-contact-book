$(document).ready(() => {
  let table
  // Initialize Select2 for filters
  $(".select2-filter").select2({
    // theme: "bootstrap-5",
    width: "100%",
    allowClear: true,
    placeholder: function () {
      return $(this).data("placeholder")
    },
  })

  // Custom search styling
  $(".dataTables_filter input").addClass("form-control").attr("placeholder", "ค้นหา...")

  // Custom length menu styling
  $(".dataTables_length select").addClass("form-select")

  // Load filter options function
  function loadFilterOptions() {
    const buildingsRequest = $.get("api/filter-options.php?type=buildings")
    const floorsRequest = $.get("api/filter-options.php?type=floors")
    const departmentsRequest = $.get("api/filter-options.php?type=departments")

    $.when(buildingsRequest, floorsRequest, departmentsRequest).done(
      (buildingsResponse, floorsResponse, departmentsResponse) => {
        // Populate buildings
        const buildingsData = buildingsResponse[0]
        if (buildingsData.success) {
          $("#buildingFilter").empty().append('<option value="">ทั้งหมด</option>')
          buildingsData.data.forEach((item) => {
            $("#buildingFilter").append(`<option value="${item.building}">${item.building}</option>`)
          })
        }

        // Populate floors
        const floorsData = floorsResponse[0]
        if (floorsData.success) {
          $("#floorFilter").empty().append('<option value="">ทั้งหมด</option>')
          floorsData.data.forEach((item) => {
            $("#floorFilter").append(`<option value="${item.floor}">ชั้น ${item.floor}</option>`)
          })
        }

        // Populate departments
        const departmentsData = departmentsResponse[0]
        if (departmentsData.success) {
          $("#departmentFilter").empty().append('<option value="">ทั้งหมด</option>')
          departmentsData.data.forEach((item) => {
            $("#departmentFilter").append(`<option value="${item.department}">${item.department}</option>`)
          })
        }

        // All filters loaded, now initialize DataTable
        initializeDataTable()
      }
    )
  }

  // Initialize DataTable function
  function initializeDataTable() {
    table = $("#employeesTable").DataTable({
      processing: true,
      serverSide: true,
      searching: false,
      ajax: {
        url: "./api/datatable.php",
        type: "POST",
        data: (d) => {
          d.room_name = $("#roomNameFilter").val()
          d.building = $("#buildingFilter").val()
          d.floor = $("#floorFilter").val()
          d.department = $("#departmentFilter").val()
        },
      },
      columns: [
        {
          data: "room_name",
          render: (data, type, row) => `<span class="badge bg-primary text-white fs-6">${data}</span>`,
        },
        {
          data: "department",
          render: (data, type, row) => `<span class="badge bg-light text-dark fs-6">${data}</span>`,
        },
        {
          data: "internal_phone",
          render: (data, type, row) => `<a href="tel:${data}" class="text-decoration-none fs-5 fw-bold">
                      <i class="fas fa-phone me-2 text-success"></i>
                      ${data}
                  </a>`,
        },
        {
          data: null,
          render: (data, type, row) => {
            let location = `${row.building} ชั้น ${row.floor}`
            return `<div class="text-muted">
                      <i class="fas fa-map-marker-alt me-2"></i>
                      ${location}
                  </div>`
          },
          orderable: false,
        },
      ],
      responsive: false,
      language: {
        url: "./assets/plugins/custom/datatables/i18n/th.json",
        processing: `<img class="w-50px me-2" src="./assets/imgages/animations/loading_ellipsis_transparent.svg"> โหลดข้อมูล...`
      },
      // dom:
      //   '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
      //   '<"row"<"col-sm-12"tr>>' +
      //   '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      pageLength: 25,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "ทั้งหมด"],
      ],
      order: [[0, "asc"]],
      buttons: [
        {
          extend: "excel",
          text: '<i class="fas fa-file-excel me-1"></i> Excel',
          className: "btn btn-success btn-sm",
          exportOptions: {
            columns: [0, 1, 2],
          },
        },
        {
          extend: "pdf",
          text: '<i class="fas fa-file-pdf me-1"></i> PDF',
          className: "btn btn-danger btn-sm",
          exportOptions: {
            columns: [0, 1, 2],
          },
        },
        {
          extend: "print",
          text: '<i class="fas fa-print me-1"></i> พิมพ์',
          className: "btn btn-info btn-sm",
          exportOptions: {
            columns: [0, 1, 2],
          },
        },
      ],
    })

    // Add buttons to card header
    table.buttons().container().appendTo($("#tableButtons"))

    // Update result count
    table.on("draw", () => {
      const info = table.page.info()
      $("#resultCount").text(info.recordsDisplay.toLocaleString())
    })

    // Filter change events
    $(".input-filter").on("keyup", () => {
      table.ajax.reload()
    })

    $(".select2-filter").on("change", () => {
      table.ajax.reload()
    })

    // Clear filters button
    $("#clearFilters").on("click", () => {
      $("#roomNameFilter").val(null)
      $(".select2-filter").val(null).trigger("change")
      table.ajax.reload()
    })
  }

  // Load filter options, which will then initialize the DataTable
  loadFilterOptions()
})
