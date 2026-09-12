<script>
    $(document).on("click", ".sync-standings-btn", function(e) {
        e.preventDefault();
        const button = $(this);
        const url = button.data("url");
        if (!url) return;

        const originalHtml = button.html();
        button.prop("disabled", true).html(
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Uploading...") }}'
        );

        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success || response.status === 200) {
                    iziToast.success({
                        message: response.message || "{{ __('Standings uploaded successfully.') }}",
                        position: "topRight"
                    });
                    const datatable = $(".common-datatable").DataTable();
                    if (datatable) {
                        datatable.ajax.reload(null, false);
                    }
                } else {
                    button.prop("disabled", false).html(originalHtml);
                    iziToast.error({
                        message: response.message || "{{ __('Failed to sync standings.') }}",
                        position: "topRight"
                    });
                }
            },
            error: function(xhr) {
                button.prop("disabled", false).html(originalHtml);
                iziToast.error({
                    message: xhr.responseJSON?.message || "{{ __('Failed to sync standings.') }}",
                    position: "topRight"
                });
            }
        });
    });
</script>

