<script>
    let liveSearchDebounceTimer = null;

    function handleLiveSearch(inputElement, immediate = false) {
        clearTimeout(liveSearchDebounceTimer);
        const spinner = document.getElementById('contests-search-spinner');
        if (spinner) spinner.classList.remove('d-none');

        const executeSearch = () => {
            const form = inputElement ? inputElement.form : document.querySelector('form[action*="contests"]');
            if (!form) return;

            const url = new URL(form.action || "{{ route('contests.index') }}");
            const formData = new FormData(form);
            formData.set('page', '1');

            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }

            // Also append the active status tab if it exists
            const activeStatus = document.querySelector('.platform-filter-pill.active');
            if (activeStatus && activeStatus.dataset.status) {
                params.set('status', activeStatus.dataset.status);
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
                    const newTableCard = doc.getElementById('contests-table-card');
                    const currentTableCard = document.getElementById('contests-table-card');

                    if (newTableCard && currentTableCard) {
                        currentTableCard.innerHTML = newTableCard.innerHTML;
                        initCountdowns(); // Re-initialize countdowns for new HTML
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

    function filterByStatus(status, btnElement) {
        // Update active tab
        document.querySelectorAll('.platform-filter-pill').forEach(el => el.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');

        // Trigger search
        handleLiveSearch(document.getElementById('contest-directory-search'), true);
    }

    // Countdown Timer Logic
    function initCountdowns() {
        const timerElements = document.querySelectorAll('.js-countdown');

        timerElements.forEach(el => {
            const targetDateStr = el.dataset.targetDate; // e.g. ISO string
            if (!targetDateStr) return;

            const targetDate = new Date(targetDateStr).getTime();

            const updateTimer = () => {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    el.innerHTML = "00:00:00";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (days > 0) {
                    el.innerHTML = `${days}d ${hours.toString().padStart(2, '0')}h`;
                } else {
                    el.innerHTML =
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            };

            updateTimer(); // Initial call

            // Only set interval if we haven't already
            if (!el.dataset.intervalId) {
                const intervalId = setInterval(updateTimer, 1000);
                el.dataset.intervalId = intervalId;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCountdowns();
    });
</script>
