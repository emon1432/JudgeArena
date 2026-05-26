<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('web/js/select2-options.js') }}"></script>
<script>
    function showProfileToast(message, type = 'success') {
        const existingContainer = document.getElementById('profile-toast-container');
        const container = existingContainer || (() => {
            const newContainer = document.createElement('div');
            newContainer.id = 'profile-toast-container';
            newContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            newContainer.style.zIndex = '1080';
            document.body.appendChild(newContainer);
            return newContainer;
        })();

        const isSuccess = type === 'success';
        const icon = isSuccess ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        const borderClass = isSuccess ? 'border-success' : 'border-danger';
        const textClass = isSuccess ? 'text-success' : 'text-danger';
        const title = isSuccess ? 'Success' : 'Error';

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center border ${borderClass}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon} ${textClass}"></i>
                    <div>
                        <div class="fw-semibold">${title}</div>
                        <div>${message}</div>
                    </div>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, {
            delay: 3500
        });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    $(document).ready(function() {
        activateTabFromHash();

        $('.nav-link[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            const hash = e.target.getAttribute('href');
            history.replaceState(null, '', hash);
        });

        $(window).on('hashchange', function() {
            activateTabFromHash();
        });

        $('.select2').select2();

        initSelect2AjaxOptions('.select2-ajax', {
            endpoint: '{{ route('select2.options') }}',
            placeholder: 'Search and select...'
        });

        initSelect2AjaxOptions('#institute_country', {
            endpoint: '{{ route('select2.options') }}',
            placeholder: 'Search and select...',
            dropdownParent: $('#addInstituteModal')
        });

        $('#addInstituteForm').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $submitBtn = $('#submitBtn');
            const originalButtonHtml = $submitBtn.html();

            $submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Request...'
            );

            const formData = {
                name: $('#institute_name').val(),
                country_id: $('#institute_country').val(),
                website: $('#institute_website').val(),
                _token: $('input[name="_token"]').val()
            };

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Reset form and close modal
                    $('#addInstituteForm')[0].reset();
                    $('#institute_country').val(null).trigger('change');
                    $('#addInstituteModal').modal('hide');

                    // Clear error messages
                    $('.invalid-feedback').html('').hide();
                    $('.form-control, .form-select').removeClass('is-invalid');

                    showProfileToast(response.message ||
                        'Institute request sent successfully.', 'success');
                },
                error: function(xhr) {
                    // Handle validation errors
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;

                        // Clear previous errors
                        $('.invalid-feedback').html('').hide();
                        $('.form-control, .form-select').removeClass('is-invalid');

                        // Display new errors
                        $.each(errors, function(field, messages) {
                            const errorEl = $('#' + field + '-error');
                            if (errorEl.length) {
                                errorEl.html(messages[0]).show();
                                $('[name="' + field + '"]').addClass('is-invalid');
                            }
                        });
                    } else {
                        const errorMsg = xhr.responseJSON?.message ||
                            'Failed to send institute request. Please try again.';
                        showProfileToast(errorMsg, 'error');
                    }
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html(originalButtonHtml);
                }
            });
        });

        $('#toggleCurrentPassword, #toggleNewPassword, #toggleConfirmPassword').on('click', function() {
            const input = $(this).closest('.input-group').find('input');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            $(this).toggleClass('bi-eye-slash bi-eye');
        });

        @if (session('sub-section'))
            const collapseId = "{{ session('sub-section') }}";
            const collapseEl = document.getElementById(collapseId);

            if (collapseEl && typeof bootstrap !== 'undefined') {
                const collapse = new bootstrap.Collapse(collapseEl, {
                    toggle: true
                });
            }
        @endif
    });

    function previewProfileImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        const allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (PNG, JPG, JPEG, GIF)');
            event.target.value = '';
            return;
        }
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 2MB. Your file is ' + (file.size / (1024 * 1024)).toFixed(
                2) + 'MB');
            event.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = document.querySelector('.rounded-circle.overflow-hidden');
            if (avatarContainer) {
                avatarContainer.innerHTML = '';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-100 h-100';
                img.style.objectFit = 'cover';
                avatarContainer.appendChild(img);
                avatarContainer.style.animation = 'fadeIn 0.3s ease-in';
            }
        };
        reader.readAsDataURL(file);
    }

    function updateCharacterCount(event) {
        const characterCountSpan = document.getElementById('fav-quote-character-count');
        if (characterCountSpan) {
            characterCountSpan.textContent = event.target.value.length;
        }
    }

    function activateTabFromHash() {
        const hash = window.location.hash;
        if (!hash) return;

        const $trigger = $(`.nav-link[data-bs-toggle="pill"][href="${hash}"]`);

        if ($trigger.length && typeof bootstrap !== 'undefined') {
            const tab = new bootstrap.Tab($trigger[0]);
            tab.show();
        }
    }

    function logoutSession(sessionId) {
        const sessionIdInput = document.getElementById('logout_session_id');
        const logoutMessage = document.getElementById('logoutMessage');
        const logoutPasswordInput = document.getElementById('logout_password');

        if (sessionIdInput && logoutMessage && logoutPasswordInput) {
            sessionIdInput.value = sessionId;
            logoutMessage.textContent = 'Please enter your password to logout from this session.';
            logoutPasswordInput.value = '';
            new bootstrap.Modal(document.getElementById('logoutSessionModal')).show();
        } else {
            console.error('Modal elements not found');
        }
    }

    function logoutAllSessions() {
        const sessionIdInput = document.getElementById('logout_session_id');
        const logoutMessage = document.getElementById('logoutMessage');
        const logoutPasswordInput = document.getElementById('logout_password');

        if (sessionIdInput && logoutMessage && logoutPasswordInput) {
            sessionIdInput.value = '';
            logoutMessage.textContent = 'Please enter your password to logout from all other sessions.';
            logoutPasswordInput.value = '';
            new bootstrap.Modal(document.getElementById('logoutSessionModal')).show();
        } else {
            console.error('Modal elements not found');
        }
    }

    // Clean up intervals on page unload
    $(window).on('beforeunload', function() {
        if (syncCountdownInterval) {
            clearInterval(syncCountdownInterval);
        }
        if (syncStatusCheckInterval) {
            clearInterval(syncStatusCheckInterval);
        }
    });
</script>
