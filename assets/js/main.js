$(document).ready(() => {
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
        data: "name",
        render: (data, type, row) => `<strong>${data}</strong>`,
      },
      { data: "position" },
      {
        data: "department",
        render: (data, type, row) => `<span class="badge bg-light text-dark">${data}</span>`,
      },
      {
        data: "internal_phone",
        render: (data, type, row) => `<a href="tel:${data}" class="text-decoration-none">
                        <i class="fas fa-phone me-1"></i>
                        ${data}
                    </a>`,
      },
      {
        data: "email",
        render: (data, type, row) => {
          if (data) {
            return `<a href="mailto:${data}" class="text-decoration-none">
                            <i class="fas fa-envelope me-1"></i>
                            ${data}
                        </a>`
          }
          return '<span class="text-muted">-</span>'
        },
      },
      {
        data: null,
        render: (data, type, row) => {
          let location = `${row.building} ชั้น ${row.floor}`
          if (row.room_number) {
            location += ` ห้อง ${row.room_number}`
          }
          return `<small class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        ${location}
                    </small>`
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
          columns: [0, 1, 2, 3, 4, 5],
        },
      },
      {
        extend: "pdf",
        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
        className: "btn btn-danger btn-sm",
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5],
        },
      },
      {
        extend: "print",
        text: '<i class="fas fa-print me-1"></i> พิมพ์',
        className: "btn btn-info btn-sm",
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5],
        },
      },
    ],
  })

  // Add buttons to card header
  table.buttons().container().appendTo($(".card-header")).addClass("float-end")

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

  // Form validation for add employee form
  if ($("#addEmployeeForm").length) {
    $("#addEmployeeForm").on("submit", function (e) {
      let isValid = true

      // Check required fields
      $(this)
        .find("[required]")
        .each(function () {
          if (!$(this).val().trim()) {
            $(this).addClass("is-invalid")
            isValid = false
          } else {
            $(this).removeClass("is-invalid")
          }
        })

      // Validate phone numbers
      const internalPhone = $("#internal_phone").val()
      const mobilePhone = $("#mobile_phone").val()

      if (internalPhone && !/^\d+$/.test(internalPhone)) {
        $("#internal_phone").addClass("is-invalid")
        isValid = false
      }

      if (mobilePhone && !/^[0-9-+\s()]+$/.test(mobilePhone)) {
        $("#mobile_phone").addClass("is-invalid")
        isValid = false
      }

      // Validate email
      const email = $("#email").val()
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        $("#email").addClass("is-invalid")
        isValid = false
      }

      if (!isValid) {
        e.preventDefault()
        alert("กรุณากรอกข้อมูลให้ถูกต้องและครบถ้วน")
      }
    })

    // Remove invalid class on input
    $(".form-control").on("input", function () {
      $(this).removeClass("is-invalid")
    })
  }
})
