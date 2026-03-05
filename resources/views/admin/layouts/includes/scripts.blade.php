<script>
    @foreach (session('toasts', collect())->toArray() as $toast)
        const toastOptions = {
            title: '{{ $toast['title'] }}',
            message: '{{ $toast['message'] }}',
            messageColor: '{{ $toast['messageColor'] }}',
            messageSize: '{{ $toast['messageSize'] }}',
            titleLineHeight: '{{ $toast['titleLineHeight'] }}',
            messageLineHeight: '{{ $toast['messageLineHeight'] }}',
            position: '{{ $toast['position'] }}',
            titleSize: '{{ $toast['titleSize'] }}',
            titleColor: '{{ $toast['titleColor'] }}',
            closeOnClick: '{{ $toast['closeOnClick'] }}',
        };

        const toastType = '{{ $toast['type'] }}';
        displayToast(toastType, toastOptions);
    @endforeach

    function displayToast(type, options) {
        switch (type) {
            case 'info':
                return iziToast.info(options);
            case 'success':
                return iziToast.success(options);
            case 'warning':
                return iziToast.warning(options);
            case 'error':
                return iziToast.error(options);
            default:
                return iziToast.show(options);
        }
    }

    {{ session()->forget('toasts') }}

    // Select2 Initialization
    function initSelect2(context = document) {
        $(context).find('.form-select').each(function() {
            let select = $(this);

            if (select.hasClass('select2-hidden-accessible')) {
                return; // already initialized
            }

            select.wrap('<div class="position-relative"></div>').select2({
                placeholder: "Select an option",
                dropdownParent: select.parent(),
                width: '100%'
            });
        });
    }
    initSelect2();

    $(document).ready(function() {
        // Sidebar Menu Activation
        const currentPath = window.location.pathname.replace(/\/$/, '');
        $('.menu-inner .menu-link').each(function() {
            const link = $(this);
            const href = link.attr('href');
            if (!href || href === 'javascript:void(0);') return;

            const linkPath = new URL(href, window.location.origin).pathname.replace(/\/$/, '');
            let isActive = false;
            if (currentPath === linkPath) {
                isActive = true;
            } else if (
                currentPath.startsWith(linkPath + '/') &&
                !linkPath.endsWith('/products')
            ) {
                isActive = true;
            }

            if (isActive) {
                const menuItem = link.closest('.menu-item');
                menuItem.addClass('active');

                // Open all parent submenus
                link.parents('.menu-sub').each(function() {
                    $(this).closest('.menu-item').addClass('active open');
                });
            }
        });

        // DataTable Initialization
        const datatableElement = document.querySelector(".common-datatable");
        if (datatableElement) {
            const datatable = new DataTable(datatableElement, {
                ajax: {
                    url: datatableElement.dataset.url,
                    method: "GET",
                    dataSrc:"data",
                },
                columns: JSON.parse(datatableElement.dataset.columns),
                processing: true,
                serverSide: true,
                deferRender: true,
                searchDelay: 350,
                ordering: true,
                order: JSON.parse(datatableElement.dataset.order || "[]"),
                layout: {
                    topStart: {
                        rowClass: "row m-1 my-0 justify-content-center",
                        features: [{
                            buttons: [{
                                extend: "collection",
                                className: "btn btn-label-secondary dropdown-toggle",
                                text: `<span class="d-flex align-items-center gap-2">
                                    <i class="icon-base ti tabler-upload icon-xs"></i>
                                    <span class="d-none d-sm-inline-block">Export</span>
                                </span>`,
                                buttons: [{
                                        extend: "print",
                                        text: `<i class="icon-base ti tabler-printer me-1"></i>Print`,
                                        className: "dropdown-item"
                                    },
                                    {
                                        extend: "csv",
                                        text: `<i class="icon-base ti tabler-file-text me-1"></i>Csv`,
                                        className: "dropdown-item"
                                    },
                                    {
                                        extend: "excel",
                                        text: `<i class="icon-base ti tabler-file-spreadsheet me-1"></i>Excel`,
                                        className: "dropdown-item"
                                    },
                                    {
                                        extend: "pdf",
                                        text: `<i class="icon-base ti tabler-file-description me-1"></i>Pdf`,
                                        className: "dropdown-item"
                                    },
                                    {
                                        extend: "copy",
                                        text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                                        className: "dropdown-item"
                                    },
                                ]
                            }]
                        }]
                    },
                    topEnd: {
                        rowClass: "row m-1 my-0 justify-content-center",
                        features: [{
                            search: {
                                placeholder: "Search",
                                text: "_INPUT_"
                            }
                        }]
                    },
                    bottomStart: {
                        rowClass: "row mx-3 justify-content-between",
                        features: ["info"]
                    },
                    bottomEnd: "paging"
                },
                responsive: {
                    details: {
                        display: DataTable.Responsive.display.modal({
                            header: () => "Details"
                        }),
                        type: "column",
                        renderer: function(api, rowIdx, columns) {
                            const rows = columns
                                .map(col => col.title ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                                    <td>${col.title}:</td><td>${col.data}</td></tr>` : "")
                                .join("");
                            if (!rows) return;

                            const wrapper = document.createElement("div");
                            wrapper.classList.add("table-responsive");

                            const table = document.createElement("table");
                            table.classList.add("table");

                            const tbody = document.createElement("tbody");
                            tbody.innerHTML = rows;

                            table.appendChild(tbody);
                            wrapper.appendChild(table);
                            return wrapper;
                        }
                    }
                },
                drawCallback: function() {
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });

            // Style Adjustments After DataTable Init
            const adjustClasses = [{
                    selector: ".dt-buttons .btn",
                    remove: "btn-secondary"
                },
                {
                    selector: ".dt-search .form-control",
                    remove: "form-control-sm"
                },
                {
                    selector: ".dt-length .form-select",
                    remove: "form-select-sm",
                    add: "ms-0"
                },
                {
                    selector: ".dt-length",
                    add: "mb-md-6 mb-0"
                },
                {
                    selector: ".dt-layout-start",
                    remove: "justify-content-between",
                    add: "d-flex gap-md-4 justify-content-md-between justify-content-center gap-2 flex-wrap w-auto pe-0"
                },
                {
                    selector: ".dt-layout-end",
                    remove: "justify-content-between",
                    add: "d-flex gap-md-4 justify-content-md-between justify-content-center gap-2 flex-wrap w-auto ps-0"
                },
                {
                    selector: ".dt-buttons",
                    remove: "mb-4",
                    add: "d-flex gap-4 mb-md-0 mb-0"
                },
                {
                    selector: ".dt-layout-table",
                    remove: "row mt-2"
                },
                {
                    selector: ".dt-layout-full",
                    remove: "col-md col-12",
                    add: "table-responsive"
                },
            ];

            setTimeout(() => {
                adjustClasses.forEach(({
                    selector,
                    remove,
                    add
                }) => {
                    document.querySelectorAll(selector).forEach(el => {
                        remove?.split(" ").forEach(cls => el.classList.remove(cls));
                        add?.split(" ").forEach(cls => el.classList.add(cls));
                    });
                });
            });
        }

        // Form Validation // .common-form
        document.querySelectorAll(".common-form").forEach(form => {
            const validationFields = {};

            form.querySelectorAll("[name]").forEach(input => {
                const name = input.name;
                const type = input.type;
                const validators = {};

                if (input.required) {
                    validators.notEmpty = {
                        message: "This field is required"
                    };
                }
                if (type === "email") {
                    validators.emailAddress = {
                        message: "Please include an '@' in the email address."
                    };
                }
                if (type === "url") {
                    validators.uri = {
                        message: "Please enter a valid URL"
                    };
                }
                if (type === "number") {
                    validators.numeric = {
                        message: "Please enter a valid number"
                    };
                }
                if (type === "tel") {
                    validators.regexp = {
                        regexp: /^[0-9+\-\s()]*$/,
                        message: "Please enter a valid phone number"
                    };
                }
                if (type === "password") {
                    validators.stringLength = {
                        min: 6,
                        message: "Password must be at least 6 characters long"
                    };
                }

                if (Object.keys(validators).length) {
                    validationFields[name] = {
                        validators
                    };
                }
            });

            const formValidation = FormValidation.formValidation(form, {
                fields: validationFields,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        eleValidClass: "",
                        rowSelector: ".form-control-validation",
                    }),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                },
                init: instance => {
                    instance.on("plugins.message.placed", function(e) {
                        const parent = e.element.parentElement;
                        if (parent.classList.contains("input-group")) {
                            parent.insertAdjacentElement("afterend", e
                                .messageElement);
                        } else if (parent.parentElement.classList.contains(
                                "custom-option")) {
                            e.element.closest(".row").insertAdjacentElement(
                                "afterend", e.messageElement);
                        }
                    });
                }
            });

            formValidation.on("core.form.valid", function() {
                const submitBtn = $(form).find("button[type=submit]");
                const submitBtnText = submitBtn.text().trim();
                const formData = new FormData(form);

                submitBtn.prop("disabled", true).html(
                    `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`
                );

                $.ajax({
                    url: form.action,
                    method: form.method,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.status !== 200) {
                            iziToast.error({
                                message: response.message ||
                                    "Something went wrong",
                                position: "topRight"
                            });
                            return;
                        }
                        iziToast.success({
                            message: response.message ||
                                "Form submitted successfully",
                            position: "topRight"
                        });
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        iziToast.error({
                            message: xhr.responseJSON?.message ||
                                "Something went wrong",
                            position: "topRight"
                        });
                    },
                    complete: function() {
                        submitBtn.prop("disabled", false).html(
                            `<i class="icon-base ti tabler-device-floppy icon-xs me-2"></i> ${submitBtnText}`
                        );
                    }
                });
            });

            // Prevent default submission for this specific form
            form.addEventListener('submit', e => e.preventDefault());
        });

        //on modal open or close, reset the form
        $(document).on('hidden.bs.modal', '.modal', function() {
            const form = $(this).find('.common-form');
            if (form.length) {
                form[0].reset();
                form.find('.form-control-validation').removeClass('is-invalid is-valid');
                form.find('.invalid-feedback').remove();
                form.find('.valid-feedback').remove();
                form.find('.form-check-input').prop('checked', false);
            }
            $(this).find('.select2-selection').removeClass('is-invalid is-valid');
            //render select2 elements again
            $(this).find('.form-select').each(function() {
                $(this).select2({
                    placeholder: "Select an option",
                    dropdownParent: $(this).parent(),
                });
            });
        });

        // Delete Record Confirmation
        $(document).on("click", ".delete-record", function(e) {
            e.preventDefault();
            const form = $(this).find("form");
            const actionUrl = form.attr("action");
            const method = form.attr("method") || "DELETE";
            Swal.fire({
                title: "Are you sure?",
                text: "This record will be moved to trash",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                customClass: {
                    confirmButton: "btn btn-primary me-2 waves-effect waves-light",
                    cancelButton: "btn btn-label-secondary waves-effect waves-light",
                },
                buttonsStyling: false,
            }).then(function(e) {
                if (e.isConfirmed) {
                    $.ajax({
                        url: actionUrl,
                        type: method,
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE"
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: "Deleting...",
                                text: "Please wait",
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted!",
                                    text: response.message,
                                    customClass: {
                                        confirmButton: "btn btn-success waves-effect waves-light",
                                    },
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error!",
                                    text: response.message,
                                    customClass: {
                                        confirmButton: "btn btn-danger waves-effect waves-light",
                                    },
                                });
                            }
                        },
                        complete: function() {
                            const datatable = $(".common-datatable").DataTable();
                            datatable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: xhr.responseJSON?.message ||
                                    "Something went wrong",
                                customClass: {
                                    confirmButton: "btn btn-danger waves-effect waves-light",
                                },
                            });
                        }
                    });
                } else if (e.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your record is safe :)",
                        icon: "error",
                        customClass: {
                            confirmButton: "btn btn-success waves-effect waves-light",
                        },
                    });
                }
            });
        });

        // Restore Record Confirmation
        $(document).on("click", ".restore-record", function(e) {
            e.preventDefault();
            const form = $(this).find("form");
            const actionUrl = form.attr("action");
            const method = form.attr("method") || "POST";

            Swal.fire({
                title: "Are you sure?",
                text: "This record will be restored",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, restore it!",
                customClass: {
                    confirmButton: "btn btn-primary me-2 waves-effect waves-light",
                    cancelButton: "btn btn-label-secondary waves-effect waves-light",
                },
                buttonsStyling: false,
            }).then(function(e) {
                if (e.isConfirmed) {
                    $.ajax({
                        url: actionUrl,
                        type: method,
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: "Restoring...",
                                text: "Please wait",
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Restored!",
                                    text: response.message,
                                    customClass: {
                                        confirmButton: "btn btn-success waves-effect waves-light",
                                    },
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error!",
                                    text: response.message,
                                    customClass: {
                                        confirmButton: "btn btn-danger waves-effect waves-light",
                                    },
                                });
                            }
                        },
                        complete: function() {
                            const datatable = $(".common-datatable").DataTable();
                            datatable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: xhr.responseJSON?.message ||
                                    "Something went wrong",
                                customClass: {
                                    confirmButton: "btn btn-danger waves-effect waves-light",
                                },
                            });
                        }
                    });
                } else if (e.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your record is safe :)",
                        icon: "error",
                        customClass: {
                            confirmButton: "btn btn-success waves-effect waves-light",
                        },
                    });
                }
            });
        });

        //Delete permanently record
        $(document).on("click", ".permanently-delete-record", function(e) {
            e.preventDefault();
            const form = $(this).find("form");
            const actionUrl = form.attr("action");
            const method = form.attr("method") || "DELETE";
            Swal.fire({
                title: "Are you sure?",
                text: "This record will be permanently deleted",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it permanently!",
                customClass: {
                    confirmButton: "btn btn-primary me-2 waves-effect waves-light",
                    cancelButton: "btn btn-label-secondary waves-effect waves-light",
                },
                buttonsStyling: false,
            }).then(function(e) {
                if (e.isConfirmed) {
                    $.ajax({
                        url: actionUrl,
                        type: method,
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE"
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: "Deleting...",
                                text: "Please wait",
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.status === 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted!",
                                    text: response.message,
                                    customClass: {
                                        confirmButton: "btn btn-success waves-effect waves-light",
                                    },
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error!",
                                    text: response.message,
                                    customClass: {
                                        confirmButton: "btn btn-danger waves-effect waves-light",
                                    },
                                });
                            }
                        },
                        complete: function() {
                            const datatable = $(".common-datatable").DataTable();
                            datatable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: xhr.responseJSON?.message ||
                                    "Something went wrong",
                                customClass: {
                                    confirmButton: "btn btn-danger waves-effect waves-light",
                                },
                            });
                        }
                    });
                } else if (e.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "Cancelled",
                        text: "Your record is safe :)",
                        icon: "error",
                        customClass: {
                            confirmButton: "btn btn-success waves-effect waves-light",
                        },
                    });
                }
            });
        });

        // tagify initialization
        $('.tagify').each(function() {
            new Tagify(this);
        });
    });
</script>
