<script>
    let liveSearchDebounceTimer = null;

    function handleLiveSearch(inputElement, immediate = false) {
        clearTimeout(liveSearchDebounceTimer);
        const spinner = document.getElementById('platforms-search-spinner');
        if (spinner) spinner.classList.remove('d-none');

        const executeSearch = () => {
            const form = inputElement ? inputElement.form : document.querySelector('form[action*="platforms"]');
            if (!form) return;

            const url = new URL(form.action || "{{ route('platforms.index') }}");
            const formData = new FormData(form);
            formData.set('page', '1');

            const params = new URLSearchParams();
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
                    const newTableCard = doc.getElementById('platforms-table-card');
                    const currentTableCard = document.getElementById('platforms-table-card');

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
</script>
