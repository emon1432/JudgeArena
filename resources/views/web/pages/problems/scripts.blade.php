<script>
    let liveSearchDebounceTimer = null;

    function applyFilters(immediate = false) {
        handleLiveSearch(null, immediate);
    }

    function sortProblemsList(sortValue) {
        handleLiveSearch(null, true);
    }

    function handleLiveSearch(inputElement, immediate = false) {
        clearTimeout(liveSearchDebounceTimer);
        const spinner = document.getElementById('problems-search-spinner');
        if (spinner) spinner.classList.remove('d-none');

        const executeSearch = () => {
            const form = inputElement ? inputElement.form : document.querySelector('form[action*="problems"]');
            if (!form) return;

            const url = new URL(form.action || "{{ route('problems.index') }}");
            const formData = new FormData(form);
            formData.set('page', '1');

            const params = new URLSearchParams();
            
            const tagsSelect = $('#problems-tags-select');
            let tagsVal = tagsSelect.val();
            if (tagsSelect.length && tagsVal && tagsVal.length > 0) {
                if (!Array.isArray(tagsVal)) {
                    tagsVal = [tagsVal];
                }
                formData.delete('tags[]');
                formData.set('tags', tagsVal.join(','));
            }

            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            url.search = params.toString();

            fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableCard = doc.getElementById('problems-table-card');
                    const currentTableCard = document.getElementById('problems-table-card');

                    if (newTableCard && currentTableCard) {
                        currentTableCard.innerHTML = newTableCard.innerHTML;
                    }

                    window.history.replaceState({}, '', url.toString());
                })
                .catch(err => console.error('Live search error:', err))
                .finally(() => {
                    if (spinner) spinner.classList.add('d-none');
                });
        };

        if (immediate) {
            executeSearch();
        } else {
            liveSearchDebounceTimer = setTimeout(executeSearch, 300);
        }
    }

    $(document).ready(function() {
        function initTagsSelect() {
            if ($.fn.select2) {
                $('#problems-tags-select').select2({
                    multiple: true,
                    placeholder: "Tags...",
                    allowClear: true,
                    width: '100%'
                }).on('change', function() {
                    applyFilters(true);
                });
            } else {
                setTimeout(initTagsSelect, 100);
            }
        }
        initTagsSelect();
    });
</script>
<style>
    /* Styling to make multiple select2 look like a small input and fit in single row */
    .tags-filter-wrapper .select2-selection {
        min-height: 31px !important; /* Match form-select-sm */
        padding-top: 1px !important;
        padding-bottom: 1px !important;
        display: flex !important;
        align-items: center !important;
        border-radius: 0.375rem !important; /* rounded-3 */
        border: var(--bs-border-width) solid var(--bs-border-color) !important;
    }
    .tags-filter-wrapper .select2-selection__rendered {
        display: flex !important;
        flex-wrap: nowrap !important;
        overflow: hidden !important;
        gap: 4px;
        margin-bottom: 0 !important;
    }
    .tags-filter-wrapper .select2-selection__choice {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding: 0 4px !important;
        font-size: 0.75rem !important; /* extra-small */
        white-space: nowrap;
    }
    .tags-filter-wrapper .select2-search--inline .select2-search__field {
        margin-top: 0 !important;
        font-size: 0.75rem !important;
    }
    .tags-filter-wrapper .select2-selection__clear {
        margin-top: 0 !important;
    }
</style>
