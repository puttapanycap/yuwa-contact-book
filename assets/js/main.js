$(document).ready(() => {
  // Load filter options
  loadFilterOptions()

  // Initialize Select2 for filters
  $(".select2-filter").select2({
    theme: "bootstrap-5",
    width: "100%",
    allowClear: true,
    placeholder: function () {
      return $(this).data("placeholder")
    },
  })

  // Initialize DataTable
  const table = $("#employeesTable").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "api/datatable.php",
      type: "POST",
      data: (d) => {
        d.building = $("#buildingFilter").val()
        d.floor = $("#floorFilter").val()
        d.department = $("#departmentFilter").val()
      },
    },
    columns: [
      {
        data: "department",
        render: (data, type, row) => `<span class="badge bg-primary text-white fs-6">${data}</span>`,
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
          if (row.room_number) {
            location += ` ห้อง ${row.room_number}`
          }
          return `<div class="text-muted">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    ${location}
                </div>`
        },
        orderable: false,
      },
    ],
    responsive: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json",
    },
    dom:
      '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
      '<"row"<"col-sm-12"tr>>' +
      '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
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
  $(".select2-filter").on("change", () => {
    table.ajax.reload()
  })

  // Clear filters button
  $("#clearFilters").on("click", () => {
    $(".select2-filter").val(null).trigger("change")
    table.ajax.reload()
  })

  // Custom search styling
  $(".dataTables_filter input").addClass("form-control").attr("placeholder", "ค้นหา...")

  // Custom length menu styling
  $(".dataTables_length select").addClass("form-select")

  // Load filter options function
  function loadFilterOptions() {
    // Load buildings
    $.get("api/filter-options.php?type=buildings", (data) => {
      if (data.success) {
        $("#buildingFilter").empty().append('<option value="">ทั้งหมด</option>')
        data.data.forEach((item) => {
          $("#buildingFilter").append(`<option value="${item.building}">${item.building}</option>`)
        })
      }
    })

    // Load floors
    $.get("api/filter-options.php?type=floors", (data) => {
      if (data.success) {
        $("#floorFilter").empty().append('<option value="">ทั้งหมด</option>')
        data.data.forEach((item) => {
          $("#floorFilter").append(`<option value="${item.floor}">ชั้น ${item.floor}</option>`)
        })
      }
    })

    // Load departments
    $.get("api/filter-options.php?type=departments", (data) => {
      if (data.success) {
        $("#departmentFilter").empty().append('<option value="">ทั้งหมด</option>')
        data.data.forEach((item) => {
          $("#departmentFilter").append(`<option value="${item.department}">${item.department}</option>`)
        })
      }
    })
  }
})
