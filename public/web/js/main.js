/**
 * JudgeArena Main Application Script
 * ----------------------------------
 * Handles core UI interactivity:
 * 1. Theme Controller (Light/Dark Mode Toggle with Storage Persistence)
 * 2. Tab Navigation System
 */

/* ==========================================================================
   1. THEME CONTROLLER
   ========================================================================== */
const STORAGE_KEY = "judgearena-theme";
const root = document.documentElement;

/**
 * Retrieves initial theme selection from localStorage, defaulting to 'light'.
 * @returns {string} 'light' or 'dark'
 */
function getInitialTheme() {
    try {
        const savedTheme = localStorage.getItem(STORAGE_KEY);
        if (savedTheme === "dark" || savedTheme === "light") {
            return savedTheme;
        }
    } catch (error) {
        console.warn("Storage not accessible:", error);
    }
    return "light"; // Standard default theme mode
}

/**
 * Applies the specified theme attribute to the root document element
 * and updates the toggle button icon.
 * @param {string} theme - Theme name ('light' | 'dark')
 * @param {boolean} savePreference - Whether to persist choice to localStorage
 */
function applyTheme(theme, savePreference = true) {
    root.setAttribute("data-theme", theme);
    root.setAttribute("data-bs-theme", theme);

    const themeIcon = document.getElementById("theme-toggle-icon");
    if (themeIcon) {
        themeIcon.className =
            theme === "dark" ? "fa-solid fa-moon" : "fa-solid fa-sun";
    }

    const themeSwitch = document.getElementById("theme-switch-checkbox");
    if (themeSwitch) {
        themeSwitch.checked = theme === "dark";
    }

    if (savePreference) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (error) {
            console.warn("Storage not accessible:", error);
        }
    }
}

// Immediate theme execution on script load to prevent visual flashing
let currentTheme = getInitialTheme();
applyTheme(currentTheme, false);

document.addEventListener("DOMContentLoaded", () => {
    // Initialize Select2 on all select elements if the plugin is available
    if (typeof $ !== "undefined" && $.fn.select2) {
        $("select").select2({
            width: "100%",
            minimumResultsForSearch: 4,
        });
    }

    // Synchronize icon state on DOM load
    applyTheme(currentTheme, false);

    // Attach Theme Toggle Button Event Listener
    const themeToggleBtn = document.getElementById("theme-toggle-btn");
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", () => {
            const activeTheme = root.getAttribute("data-theme") || currentTheme;
            currentTheme = activeTheme === "dark" ? "light" : "dark";
            applyTheme(currentTheme, true);
        });
    }
});

/* ==========================================================================
   2. TAB NAVIGATION SYSTEM
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            // Deactivate all tab buttons
            tabButtons.forEach((b) => b.classList.remove("active"));

            // Activate clicked tab button
            btn.classList.add("active");

            // Hide all tab content panes
            tabContents.forEach((content) => content.classList.add("d-none"));

            // Show target tab content pane
            const targetPaneId = "tab-content-" + btn.dataset.tab;
            const targetPane = document.getElementById(targetPaneId);
            if (targetPane) {
                targetPane.classList.remove("d-none");
            }
        });
    });
});

/* ==========================================================================
   X. SCROLL TO TOP BUTTON
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const scrollToTopBtn = document.getElementById("scrollToTopBtn");

    if (scrollToTopBtn) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 300) {
                scrollToTopBtn.style.opacity = "1";
                scrollToTopBtn.style.visibility = "visible";
                scrollToTopBtn.style.transform = "translateY(0)";
            } else {
                scrollToTopBtn.style.opacity = "0";
                scrollToTopBtn.style.visibility = "hidden";
                scrollToTopBtn.style.transform = "translateY(20px)";
            }
        });

        scrollToTopBtn.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });
        });
    }
});

/* ==========================================================================
   3. COMPETITIVE PROGRAMMING DASHBOARD CHARTS
   ========================================================================== */
let ratingChartInstance = null;
let platformChartInstance = null;
let verdictChartInstance = null;
let difficultyChartInstance = null;

function getThemeColors() {
    const isDark = root.getAttribute("data-theme") === "dark";
    return {
        textColor: isDark ? "#e9edf5" : "#12172a",
        mutedColor: isDark ? "#616b80" : "#8a91a3",
        gridColor: isDark
            ? "rgba(255, 255, 255, 0.07)"
            : "rgba(15, 23, 42, 0.08)",
        blue: "#3b82f6",
        purple: "#8b5cf6",
        green: "#22c55e",
        red: "#ef4444",
        yellow: "#f5a623",
        orange: "#f59e0b",
    };
}

function initCpCharts() {
    if (typeof Chart === "undefined") return;

    const colors = getThemeColors();

    // 1. Multi-Platform Rating History Line Chart
    const ratingCtx = document.getElementById("ratingChart");
    if (ratingCtx) {
        if (ratingChartInstance) ratingChartInstance.destroy();

        ratingChartInstance = new Chart(ratingCtx, {
            type: "line",
            data: {
                labels: [
                    "Jan 23",
                    "Mar 23",
                    "Jun 23",
                    "Sep 23",
                    "Dec 23",
                    "Feb 24",
                    "May 24",
                    "Aug 24",
                    "Nov 24",
                    "Jan 25",
                    "May 25",
                    "Jul 26",
                ],
                datasets: [
                    {
                        label: "Codeforces",
                        data: [
                            1200, 1340, 1420, 1390, 1560, 1680, 1620, 1790,
                            1850, 1910, 1880, 1964,
                        ],
                        borderColor: colors.blue,
                        backgroundColor: "rgba(59, 130, 246, 0.05)",
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: colors.blue,
                        borderWidth: 2,
                    },
                    {
                        label: "LeetCode",
                        data: [
                            1400, 1510, 1630, 1720, 1810, 1890, 1940, 2010,
                            2080, 2120, 2100, 2140,
                        ],
                        borderColor: colors.orange,
                        backgroundColor: "rgba(245, 158, 11, 0.05)",
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: colors.orange,
                        borderWidth: 2,
                    },
                    {
                        label: "AtCoder",
                        data: [
                            980, 1050, 1140, 1220, 1310, 1380, 1450, 1490, 1510,
                            1530, 1520, 1542,
                        ],
                        borderColor: colors.cyan || "#06b6d4",
                        backgroundColor: "transparent",
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: colors.cyan || "#06b6d4",
                        borderWidth: 2,
                    },
                    {
                        label: "JudgeArena Index",
                        data: [
                            1190, 1300, 1390, 1440, 1560, 1650, 1670, 1760,
                            1810, 1850, 1830, 1882,
                        ],
                        borderColor: colors.purple,
                        backgroundColor: "transparent",
                        borderDash: [5, 5],
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: colors.purple,
                        borderWidth: 2.5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                        align: "end",
                        labels: {
                            color: colors.textColor,
                            font: { family: "Inter", size: 10, weight: "500" },
                            boxWidth: 10,
                            usePointStyle: true,
                            pointStyle: "circle",
                        },
                    },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        mode: "index",
                        intersect: false,
                        callbacks: {
                            label: (context) =>
                                ` ${context.dataset.label}: ${context.parsed.y}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { color: colors.gridColor },
                        ticks: {
                            color: colors.mutedColor,
                            font: { family: "Inter", size: 11 },
                        },
                    },
                    y: {
                        grid: { color: colors.gridColor },
                        ticks: {
                            color: colors.mutedColor,
                            font: { family: "Inter", size: 11 },
                        },
                        min: 900,
                        max: 2300,
                    },
                },
            },
        });
    }

    // 2. Verdict Distribution Donut Chart
    // 2. Platform Solved Distribution Donut Chart
    const platformCtx = document.getElementById("platformChart");
    if (platformCtx) {
        if (platformChartInstance) platformChartInstance.destroy();

        const platformData = [1482, 840, 320, 198];
        const totalPlatform = platformData.reduce((a, b) => a + b, 0);

        platformChartInstance = new Chart(platformCtx, {
            type: "doughnut",
            data: {
                labels: ["Codeforces", "LeetCode", "AtCoder", "CodeChef"],
                datasets: [
                    {
                        data: platformData,
                        backgroundColor: [
                            colors.blue,
                            colors.orange,
                            colors.cyan || "#06b6d4",
                            colors.purple,
                        ],
                        borderWidth: 0,
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            color: colors.textColor,
                            font: { family: "Inter", size: 10, weight: "500" },
                            padding: 8,
                            usePointStyle: true,
                            pointStyle: "circle",
                        },
                    },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: (context) => {
                                const val = context.parsed;
                                const pct = (
                                    (val / totalPlatform) *
                                    100
                                ).toFixed(1);
                                return ` ${val} solved (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    // 3. Verdict Breakdown Donut Chart
    const verdictCtx = document.getElementById("verdictChart");
    if (verdictCtx) {
        if (verdictChartInstance) verdictChartInstance.destroy();

        verdictChartInstance = new Chart(verdictCtx, {
            type: "doughnut",
            data: {
                labels: [
                    "Accepted",
                    "Wrong Answer",
                    "Time Limit Exceeded",
                    "Runtime Error",
                ],
                datasets: [
                    {
                        data: [84, 10, 4, 2],
                        backgroundColor: [
                            colors.green,
                            colors.red,
                            colors.yellow,
                            colors.purple,
                        ],
                        borderWidth: 0,
                        hoverOffset: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            color: colors.textColor,
                            font: { family: "Inter", size: 10, weight: "500" },
                            padding: 8,
                            usePointStyle: true,
                            pointStyle: "circle",
                        },
                    },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: (context) =>
                                ` ${context.parsed}% of submissions`,
                        },
                    },
                },
            },
        });
    }

    // 3. Difficulty Tier Bar Chart (Enhanced Professional Design)
    const diffCtx = document.getElementById("difficultyChart");
    if (diffCtx) {
        if (difficultyChartInstance) difficultyChartInstance.destroy();

        const ctx = diffCtx.getContext("2d");

        // Create vibrant linear gradients for each bar tier
        const createGrad = (color1, color2) => {
            const grad = ctx.createLinearGradient(0, 0, 0, 240);
            grad.addColorStop(0, color1);
            grad.addColorStop(1, color2);
            return grad;
        };

        const gGreen = createGrad(
            "rgba(34, 197, 94, 0.95)",
            "rgba(34, 197, 94, 0.35)",
        );
        const gCyan = createGrad(
            "rgba(6, 182, 212, 0.95)",
            "rgba(6, 182, 212, 0.35)",
        );
        const gBlue = createGrad(
            "rgba(59, 130, 246, 0.95)",
            "rgba(59, 130, 246, 0.35)",
        );
        const gPurple = createGrad(
            "rgba(139, 92, 246, 0.95)",
            "rgba(139, 92, 246, 0.35)",
        );
        const gOrange = createGrad(
            "rgba(249, 115, 22, 0.95)",
            "rgba(249, 115, 22, 0.35)",
        );
        const gRed = createGrad(
            "rgba(239, 68, 68, 0.95)",
            "rgba(239, 68, 68, 0.35)",
        );

        const difficultyRanks = [
            "800-1100 (Pupil)",
            "1200-1400 (Specialist)",
            "1500-1700 (Expert)",
            "1800-2000 (Cand. Master)",
            "2100-2400 (Master)",
            "2500+ (Grandmaster)",
        ];

        const dataValues = [420, 380, 310, 210, 122, 40];
        const totalSolved = dataValues.reduce((a, b) => a + b, 0);

        // Custom Chart.js Plugin to draw value text on top of bars
        const barValuePlugin = {
            id: "barValuePlugin",
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        ctx.save();
                        ctx.fillStyle = colors.textColor;
                        ctx.font = "600 11px Inter, sans-serif";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "bottom";
                        ctx.fillText(value, bar.x, bar.y - 5);
                        ctx.restore();
                    });
                });
            },
        };

        difficultyChartInstance = new Chart(diffCtx, {
            type: "bar",
            data: {
                labels: [
                    "800-1100",
                    "1200-1400",
                    "1500-1700",
                    "1800-2000",
                    "2100-2400",
                    "2500+",
                ],
                datasets: [
                    {
                        label: "Problems Solved",
                        data: dataValues,
                        backgroundColor: [
                            gGreen,
                            gCyan,
                            gBlue,
                            gPurple,
                            gOrange,
                            gRed,
                        ],
                        borderColor: [
                            "#22c55e",
                            "#06b6d4",
                            "#3b82f6",
                            "#8b5cf6",
                            "#f97316",
                            "#ef4444",
                        ],
                        borderWidth: 1.5,
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.65,
                        categoryPercentage: 0.8,
                    },
                ],
            },
            plugins: [barValuePlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 20, bottom: 0 },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            title: (items) =>
                                difficultyRanks[items[0].dataIndex],
                            label: (context) => {
                                const val = context.parsed.y;
                                const pct = ((val / totalSolved) * 100).toFixed(
                                    1,
                                );
                                return ` ${val} Problems (${pct}% of total)`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: colors.mutedColor,
                            font: { family: "Inter", size: 11, weight: "600" },
                        },
                    },
                    y: {
                        grid: { color: colors.gridColor },
                        ticks: {
                            color: colors.mutedColor,
                            font: { family: "Inter", size: 11 },
                        },
                        suggestedMax: 470,
                    },
                },
            },
        });
    }
}

// Re-render chart colors when theme toggles
const prevApplyTheme = applyTheme;
applyTheme = function (theme, savePreference = true) {
    prevApplyTheme(theme, savePreference);
    setTimeout(initCpCharts, 50);
};

/* ==========================================================================
   4. GITHUB-STYLE PROFILE HEATMAP WIDGET GENERATOR
   ========================================================================== */
function initGithubHeatmap() {
    const gridContainer = document.getElementById("github-heatmap-grid");
    if (!gridContainer) return;

    gridContainer.innerHTML = "";

    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];
    const startDate = new Date(2026, 0, 1);

    // Floating tooltip element
    let tooltip = document.getElementById("heatmap-tooltip");
    if (!tooltip) {
        tooltip = document.createElement("div");
        tooltip.id = "heatmap-tooltip";
        tooltip.className = "heatmap-tooltip";
        document.body.appendChild(tooltip);
    }

    for (let week = 0; week < 52; week++) {
        for (let day = 0; day < 7; day++) {
            const cellDate = new Date(startDate);
            cellDate.setDate(startDate.getDate() + week * 7 + day);

            const monthName = months[cellDate.getMonth()];
            const dayNum = cellDate.getDate();
            const year = cellDate.getFullYear();
            const formattedDate = `${monthName} ${dayNum}, ${year}`;

            // Deterministic activity pattern
            const daySeed = (week * 7 + day + 11) % 19;
            let count = 0;
            let level = 0;

            if (daySeed > 5) {
                count = ((daySeed * 4 + day) % 15) + 1;
                if (count >= 10) level = 4;
                else if (count >= 7) level = 3;
                else if (count >= 4) level = 2;
                else level = 1;
            }

            const cell = document.createElement("div");
            cell.className = `hm-cell level-${level}`;

            const tooltipContent =
                count > 0
                    ? `<strong>${count} submission${count > 1 ? "s" : ""}</strong> on ${formattedDate}`
                    : `No submissions on ${formattedDate}`;

            cell.addEventListener("mouseenter", (e) => {
                tooltip.innerHTML = tooltipContent;
                tooltip.style.display = "block";
                const rect = cell.getBoundingClientRect();
                tooltip.style.left = `${rect.left + window.scrollX + rect.width / 2}px`;
                tooltip.style.top = `${rect.top + window.scrollY - 38}px`;
            });

            cell.addEventListener("mouseleave", () => {
                tooltip.style.display = "none";
            });

            gridContainer.appendChild(cell);
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
        initCpCharts();
        initGithubHeatmap();
        initPlatformTableControls();
    }, 100);
});

/* ---------- Platform Directory Search & Filter Controls ---------- */
function initPlatformTableControls() {
    const searchInput = document.getElementById("platform-search-input");
    const filterPills = document.querySelectorAll(".platform-filter-pill");
    const sortOptions = document.querySelectorAll(".sort-option");
    const sortLabel = document.getElementById("current-sort-label");
    const tableRows = document.querySelectorAll(
        ".saas-directory-table tbody tr",
    );

    // 1. Keyboard Shortcut (⌘K or Ctrl+K)
    document.addEventListener("keydown", (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === "k") {
            e.preventDefault();
            if (searchInput) searchInput.focus();
        }
    });

    // 2. Real-time Search Filtering
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase().trim();
            tableRows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        });
    }

    // 3. Filter Pills Switch
    filterPills.forEach((pill) => {
        pill.addEventListener("click", () => {
            filterPills.forEach((p) => p.classList.remove("active"));
            pill.classList.add("active");
            const filterType = pill.getAttribute("data-filter");

            tableRows.forEach((row) => {
                if (filterType === "all") {
                    row.style.display = "";
                } else if (filterType === "active") {
                    row.style.display =
                        row.textContent.includes("Synced") ||
                        row.textContent.includes("ago")
                            ? ""
                            : "none";
                } else if (filterType === "top-rated") {
                    const ratingText = row.children[2]?.textContent || "";
                    const rating = parseInt(ratingText.replace(/,/g, ""), 10);
                    row.style.display =
                        !isNaN(rating) && rating >= 2000 ? "" : "none";
                } else if (filterType === "needs-sync") {
                    row.style.display =
                        row.textContent.includes("Pending") ||
                        row.textContent.includes("Never")
                            ? ""
                            : "none";
                }
            });
        });
    });

    // 4. Sort Dropdown Options
    sortOptions.forEach((opt) => {
        opt.addEventListener("click", (e) => {
            e.preventDefault();
            sortOptions.forEach((o) => o.classList.remove("active"));
            opt.classList.add("active");
            if (sortLabel) sortLabel.textContent = `Sort: ${opt.textContent}`;

            const sortType = opt.getAttribute("data-sort");
            const tbody = document.querySelector(".saas-directory-table tbody");
            if (!tbody) return;

            const rowsArray = Array.from(tbody.querySelectorAll("tr"));
            rowsArray.sort((a, b) => {
                if (sortType === "name") {
                    const nameA = a.children[0].textContent
                        .trim()
                        .toLowerCase();
                    const nameB = b.children[0].textContent
                        .trim()
                        .toLowerCase();
                    return nameA.localeCompare(nameB);
                } else if (sortType === "rating") {
                    const ratA =
                        parseInt(
                            a.children[2].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    const ratB =
                        parseInt(
                            b.children[2].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    return ratB - ratA;
                } else if (sortType === "solved") {
                    const solA =
                        parseInt(
                            a.children[4].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    const solB =
                        parseInt(
                            b.children[4].textContent.replace(/,/g, ""),
                            10,
                        ) || 0;
                    return solB - solA;
                } else {
                    return 0;
                }
            });

            rowsArray.forEach((r) => tbody.appendChild(r));
        });
    });
}

/* ---------- Share Profile Helper Functions ---------- */
function copyProfileUrl() {
    const urlInput = document.getElementById("share-profile-url-input");
    if (urlInput) {
        navigator.clipboard.writeText(urlInput.value);
        const toast = document.getElementById("copy-success-toast");
        const icon = document.getElementById("copy-btn-icon");
        const btnText = document.getElementById("copy-btn-text");
        if (toast) toast.classList.remove("d-none");
        if (icon) icon.className = "fa-solid fa-check";
        if (btnText) btnText.textContent = "Copied!";
        setTimeout(() => {
            if (toast) toast.classList.add("d-none");
            if (icon) icon.className = "fa-regular fa-copy";
            if (btnText) btnText.textContent = "Copy Link";
        }, 2500);
    }
}

function copyMarkdownBadge() {
    const badgeInput = document.getElementById("share-badge-markdown-input");
    if (badgeInput) {
        navigator.clipboard.writeText(badgeInput.value);
        alert("GitHub README Markdown badge copied to clipboard!");
    }
}

/* ==========================================================================
   4. UNIFIED PROBLEMS ARCHIVE INTERACTIVITY & FILTERING ENGINE
   ========================================================================== */

/**
 * Global state variables for Problems Archive filtering, sorting & pagination.
 */
let currentProblemsStatusFilter = "all";
let currentProblemsSortOrder = "solved-date";
let currentProblemsPage = 1;
let problemsPageSize = 10;

/**
 * Initializes the Problems Archive features when DOM is ready.
 * Sets up Select2 tags dropdown, attaches event handlers, and performs initial filtering.
 */
document.addEventListener("DOMContentLoaded", () => {
    const problemsTable = document.getElementById("problems-directory-table");
    if (!problemsTable) return; // Exit if not on problems.html page

    // Initialize Select2 multi-select for Category Tags
    if (typeof $ !== "undefined" && $.fn && $.fn.select2) {
        $("#problems-tags-select")
            .select2({
                placeholder: "Filter by categories...",
                allowClear: true,
                width: "100%",
            })
            .on("change", function () {
                applyFilters(true);
            });
    }

    // Initial filtering to synchronize counts, pills & pagination
    applyFilters(true);
});

/**
 * Filters the static HTML problem table rows based on user selections:
 * - Search query (title, code, or tags)
 * - Status tab (all, solved, attempted, unsolved, bookmarked)
 * - Platform dropdown (Codeforces, LeetCode, AtCoder, CodeChef, etc.)
 * - Difficulty dropdown (Easy, Medium, Hard)
 * - Category tags (graphs, dynamic-programming, math, etc.)
 *
 * @param {boolean} resetPage - Reset to page 1 if true
 */
function applyFilters(resetPage = true) {
    const tableBody = document.getElementById("problems-table-tbody");
    if (!tableBody) return;

    if (resetPage) {
        currentProblemsPage = 1;
    }

    const rows = Array.from(tableBody.querySelectorAll("tr"));
    const searchQuery = (
        document.getElementById("problems-directory-search")?.value || ""
    )
        .toLowerCase()
        .trim();
    const selectedPlatform =
        document.getElementById("problems-platform-select")?.value || "all";
    const selectedDifficulty =
        document.getElementById("problems-difficulty-select")?.value || "all";

    // Get selected category tags from Select2
    let selectedTags = [];
    if (typeof $ !== "undefined" && $.fn && $.fn.select2) {
        selectedTags = $("#problems-tags-select").val() || [];
    } else {
        const tagsSelect = document.getElementById("problems-tags-select");
        if (tagsSelect) {
            selectedTags = Array.from(tagsSelect.selectedOptions).map(
                (opt) => opt.value,
            );
        }
    }

    // 1. Filter rows
    const matchingRows = [];
    rows.forEach((row) => {
        const pStatus = row.getAttribute("data-status") || "";
        const pPlatform = row.getAttribute("data-platform") || "";
        const pDifficulty = row.getAttribute("data-difficulty") || "";
        const pTagsStr = row.getAttribute("data-tags") || "";
        const pTags = pTagsStr.split(",").map((t) => t.trim().toLowerCase());
        const pBookmarked = row.getAttribute("data-bookmarked") === "true";
        const pTitle = (row.getAttribute("data-title") || "").toLowerCase();
        const pCode = (row.getAttribute("data-code") || "").toLowerCase();
        const rowText = row.textContent.toLowerCase();

        // Search filter match
        const matchSearch =
            searchQuery === "" ||
            pTitle.includes(searchQuery) ||
            pCode.includes(searchQuery) ||
            pTagsStr.includes(searchQuery) ||
            rowText.includes(searchQuery);

        // Platform filter match
        const matchPlatform =
            selectedPlatform === "all" || pPlatform === selectedPlatform;

        // Difficulty filter match
        const matchDifficulty =
            selectedDifficulty === "all" || pDifficulty === selectedDifficulty;

        // Tags filter match (must contain all selected tags)
        const matchTags =
            selectedTags.length === 0 ||
            selectedTags.every((tag) => pTags.includes(tag.toLowerCase()));

        // Status filter match
        let matchStatus = true;
        if (currentProblemsStatusFilter === "solved") {
            matchStatus = pStatus === "solved";
        } else if (currentProblemsStatusFilter === "attempted") {
            matchStatus = pStatus === "attempted";
        } else if (currentProblemsStatusFilter === "unsolved") {
            matchStatus = pStatus === "unsolved";
        } else if (currentProblemsStatusFilter === "bookmarked") {
            matchStatus = pBookmarked === true;
        }

        if (
            matchSearch &&
            matchPlatform &&
            matchDifficulty &&
            matchTags &&
            matchStatus
        ) {
            matchingRows.push(row);
        }
    });

    // 2. Sort matching rows
    sortRowsArray(matchingRows, currentProblemsSortOrder);

    // Hide non-matching rows and append sorted matching rows to table
    rows.forEach((row) => {
        row.style.display = "none";
    });

    // 3. Paginate matching rows
    const totalItems = matchingRows.length;
    const totalPages = Math.ceil(totalItems / problemsPageSize) || 1;
    if (currentProblemsPage > totalPages) currentProblemsPage = totalPages;

    const startIndex = (currentProblemsPage - 1) * problemsPageSize;
    const endIndex = Math.min(startIndex + problemsPageSize, totalItems);

    matchingRows.forEach((row, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
        tableBody.appendChild(row);
    });

    // Update status pills and KPI metric cards
    updateProblemsPillsAndMetrics(rows);

    // Update pagination info text and page controls
    updateProblemsPaginationControls(
        startIndex,
        endIndex,
        totalItems,
        totalPages,
    );
}

/**
 * Sorts array of problem row DOM elements in-memory.
 */
function sortRowsArray(rowsArray, sortVal) {
    rowsArray.sort((a, b) => {
        const ratingA = parseInt(a.getAttribute("data-rating") || "0", 10);
        const ratingB = parseInt(b.getAttribute("data-rating") || "0", 10);
        const titleA = (a.getAttribute("data-title") || "").toLowerCase();
        const titleB = (b.getAttribute("data-title") || "").toLowerCase();
        const statusA = a.getAttribute("data-status") || "";
        const statusB = b.getAttribute("data-status") || "";
        const dateA = new Date(
            a.getAttribute("data-solved-date") || "1970-01-01",
        ).getTime();
        const dateB = new Date(
            b.getAttribute("data-solved-date") || "1970-01-01",
        ).getTime();

        if (sortVal === "solved-date") {
            if (statusA === "solved" && statusB === "solved") {
                return dateB - dateA;
            }
            if (statusA === "solved") return -1;
            if (statusB === "solved") return 1;
            return ratingB - ratingA;
        } else if (sortVal === "diff-asc") {
            return ratingA - ratingB;
        } else if (sortVal === "diff-desc") {
            return ratingB - ratingA;
        } else if (sortVal === "name-asc") {
            return titleA.localeCompare(titleB);
        }
        return 0;
    });
}

/**
 * Triggers sorting for Problems Archive.
 * @param {string} sortVal - 'solved-date' | 'diff-asc' | 'diff-desc' | 'name-asc'
 */
function sortProblemsList(sortVal) {
    currentProblemsSortOrder = sortVal;
    applyFilters(false);
}

/**
 * Filters the problems list by status tab.
 * @param {string} status - 'all' | 'solved' | 'attempted' | 'unsolved' | 'bookmarked'
 * @param {HTMLElement} btn - The clicked tab button element
 */
function filterByStatus(status, btn) {
    currentProblemsStatusFilter = status;

    // Update active pill styling
    const pills = document.querySelectorAll("#status-filter-pills .nav-link");
    pills.forEach((p) => p.classList.remove("active"));
    if (btn) btn.classList.add("active");

    applyFilters(true);
}

/**
 * Toggles the bookmark status for a static problem row.
 * @param {HTMLElement} btn - The bookmark button clicked
 */
function toggleProblemBookmark(btn) {
    const row = btn.closest("tr");
    if (!row) return;

    const isBookmarked = row.getAttribute("data-bookmarked") === "true";

    if (isBookmarked) {
        row.setAttribute("data-bookmarked", "false");
        btn.className =
            "btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn";
        btn.title = "Bookmark Problem";
        btn.innerHTML = '<i class="fa-regular fa-star"></i>';
    } else {
        row.setAttribute("data-bookmarked", "true");
        btn.className =
            "btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn";
        btn.title = "Remove Bookmark";
        btn.innerHTML = '<i class="fa-solid fa-star"></i>';
    }

    applyFilters(false);
}

/**
 * Updates status pills text counts and metric cards.
 * @param {Array<HTMLElement>} allRows - Array of all tr elements in tbody
 */
function updateProblemsPillsAndMetrics(allRows) {
    const total = allRows.length;
    const solved = allRows.filter(
        (r) => r.getAttribute("data-status") === "solved",
    ).length;
    const attempted = allRows.filter(
        (r) => r.getAttribute("data-status") === "attempted",
    ).length;
    const unsolved = allRows.filter(
        (r) => r.getAttribute("data-status") === "unsolved",
    ).length;
    const bookmarked = allRows.filter(
        (r) => r.getAttribute("data-bookmarked") === "true",
    ).length;

    const pillsContainer = document.getElementById("status-filter-pills");
    if (pillsContainer) {
        const buttons = pillsContainer.querySelectorAll(".nav-link");
        if (buttons.length >= 5) {
            buttons[0].innerHTML = `All (${total})`;
            buttons[1].innerHTML = `<i class="fa-solid fa-circle-check text-success extra-small me-1"></i> Solved (${solved})`;
            buttons[2].innerHTML = `<i class="fa-solid fa-circle-exclamation text-warning extra-small me-1"></i> Attempted (${attempted})`;
            buttons[3].innerHTML = `<i class="fa-regular fa-circle text-muted extra-small me-1"></i> Unsolved (${unsolved})`;
            buttons[4].innerHTML = `<i class="fa-solid fa-star text-warning extra-small me-1"></i> Bookmarked (${bookmarked})`;
        }
    }

    const bookmarkedMetric = document.getElementById("bookmarked-count-num");
    if (bookmarkedMetric) {
        bookmarkedMetric.textContent = `${bookmarked} Saved`;
    }
}

/**
 * Renders pagination info text and page numbers dynamically.
 */
function updateProblemsPaginationControls(
    startIndex,
    endIndex,
    totalItems,
    totalPages,
) {
    const info = document.getElementById("pagination-info");
    if (info) {
        if (totalItems === 0) {
            info.textContent = "Showing 0 of 0 problems";
        } else {
            info.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalItems} problems`;
        }
    }

    const controls = document.getElementById("pagination-controls");
    if (!controls) return;
    controls.innerHTML = "";

    if (totalPages <= 1) return;

    // Previous button
    const prevLi = document.createElement("li");
    prevLi.className = `page-item ${currentProblemsPage === 1 ? "disabled" : ""}`;
    prevLi.innerHTML = `
    <button class="page-link py-1 px-2 rounded-2" onclick="changeProblemsPage(${currentProblemsPage - 1})" aria-label="Previous">
      <span aria-hidden="true">&laquo;</span>
    </button>
  `;
    controls.appendChild(prevLi);

    // Page Numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${currentProblemsPage === i ? "active" : ""}`;
        li.innerHTML = `
      <button class="page-link py-1 px-2.5 rounded-2 fw-semibold" onclick="changeProblemsPage(${i})">${i}</button>
    `;
        controls.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement("li");
    nextLi.className = `page-item ${currentProblemsPage === totalPages ? "disabled" : ""}`;
    nextLi.innerHTML = `
    <button class="page-link py-1 px-2 rounded-2" onclick="changeProblemsPage(${currentProblemsPage + 1})" aria-label="Next">
      <span aria-hidden="true">&raquo;</span>
    </button>
  `;
    controls.appendChild(nextLi);
}

/**
 * Changes current pagination page.
 * @param {number} page - Page number to switch to
 */
function changeProblemsPage(page) {
    currentProblemsPage = page;
    applyFilters(false);
}

/**
 * Changes page size for Problems Archive.
 * @param {string|number} size - Items per page
 */
function changePageSize(size) {
    problemsPageSize = parseInt(size, 10) || 10;
    applyFilters(true);
}

/**
 * Simulates submission synchronization across connected online judges.
 */
function syncSubmissions() {
    const btn = document.getElementById("sync-btn");
    const icon = document.getElementById("sync-icon");
    const text = document.getElementById("sync-text");

    if (!btn) return;

    btn.disabled = true;
    if (icon) icon.classList.add("fa-spin");
    if (text) text.textContent = "Syncing Submissions...";

    setTimeout(() => {
        btn.disabled = false;
        if (icon) icon.classList.remove("fa-spin");
        if (text) text.textContent = "Sync Submissions";

        alert(
            "Synchronization completed! All connected handles (Codeforces, LeetCode, AtCoder, CodeChef) are up-to-date.",
        );
    }, 1200);
}

/* ==========================================================================
   6. AUTHENTICATION & PASSWORD UTILITIES ENGINE
   ========================================================================== */

/**
 * Toggles visibility of password input fields.
 * @param {string} inputId - ID of password input element
 * @param {HTMLElement} btn - Toggle button element
 */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";

    const icon = btn.querySelector("i");
    if (icon) {
        icon.className = isPassword
            ? "fa-regular fa-eye-slash"
            : "fa-regular fa-eye";
    }
}

/**
 * Evaluates password strength and updates visual progress bar.
 * @param {string} password - Raw password input text
 */
function checkPasswordStrength(password) {
    const bar = document.getElementById("password-strength-bar");
    const text = document.getElementById("password-strength-text");

    if (!bar || !text) return;

    let score = 0;
    if (!password) {
        bar.style.width = "0%";
        bar.className = "progress-bar bg-secondary";
        text.textContent = "Too short";
        text.className = "text-secondary";
        return;
    }

    if (password.length >= 8) score += 25;
    if (/[A-Z]/.test(password)) score += 25;
    if (/[0-9]/.test(password)) score += 25;
    if (/[^A-Za-z0-9]/.test(password)) score += 25;

    bar.style.width = `${score}%`;

    if (score <= 25) {
        bar.className = "progress-bar bg-danger";
        text.textContent = "Weak";
        text.className = "text-danger fw-semibold";
    } else if (score <= 50) {
        bar.className = "progress-bar bg-warning";
        text.textContent = "Medium";
        text.className = "text-warning fw-semibold";
    } else if (score <= 75) {
        bar.className = "progress-bar bg-info";
        text.textContent = "Good";
        text.className = "text-info fw-semibold";
    } else {
        bar.className = "progress-bar bg-success";
        text.textContent = "Strong";
        text.className = "text-success fw-semibold";
    }
}

/**
 * Simulates password reset request and redirects to OTP verification page.
 * @param {Event} e - Form submit event
 */
function handlePasswordResetRequest(e) {
    e.preventDefault();

    const alertBox = document.getElementById("reset-alert-success");
    const btn = document.getElementById("reset-submit-btn");

    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending Code...';
    }

    setTimeout(() => {
        if (alertBox) alertBox.classList.remove("d-none");
        setTimeout(() => {
            window.location.href = "verify-otp.html";
        }, 800);
    }, 800);
}

/**
 * Auto-focus navigation for 6-digit OTP code inputs.
 */
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("otp-inputs-container");
    if (!container) return;

    const inputs = Array.from(container.querySelectorAll(".otp-field"));

    inputs.forEach((input, index) => {
        input.addEventListener("input", (e) => {
            const value = e.target.value;
            if (value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Start OTP Resend Countdown Timer
    startOTPCountdown();
});

/**
 * Manages OTP resend countdown timer.
 */
let otpTimerInterval = null;
function startOTPCountdown() {
    let seconds = 45;
    const timerSpan = document.getElementById("resend-timer-count");
    const resendBtn = document.getElementById("resend-otp-btn");

    if (!timerSpan || !resendBtn) return;

    resendBtn.disabled = true;

    if (otpTimerInterval) clearInterval(otpTimerInterval);

    otpTimerInterval = setInterval(() => {
        seconds--;
        if (timerSpan) timerSpan.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(otpTimerInterval);
            if (resendBtn) {
                resendBtn.disabled = false;
                resendBtn.innerHTML =
                    '<i class="fa-solid fa-rotate-right me-1"></i> Resend Code';
            }
        }
    }, 1000);
}

/**
 * Resends OTP verification code.
 */
function resendOTPCode() {
    const resendBtn = document.getElementById("resend-otp-btn");
    if (resendBtn) {
        resendBtn.disabled = true;
        resendBtn.innerHTML =
            'Resend Code (<span id="resend-timer-count">45</span>s)';
    }
    alert(
        "A new 6-digit verification code has been sent to your email address!",
    );
    startOTPCountdown();
}

/**
 * Simulates OTP verification and redirects to reset-password.html.
 * @param {Event} e - Form submit event
 */
function handleVerifyOTP(e) {
    e.preventDefault();

    const btn = document.getElementById("verify-otp-btn");
    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying...';
    }

    setTimeout(() => {
        window.location.href = "reset-password.html";
    }, 900);
}

/**
 * Simulates setting a new password and redirects to login.html.
 * @param {Event} e - Form submit event
 */
function handleSetNewPassword(e) {
    e.preventDefault();

    const pass = document.getElementById("new-pass-input")?.value || "";
    const confirmPass =
        document.getElementById("confirm-new-pass-input")?.value || "";
    const errorAlert = document.getElementById("new-password-alert-error");
    const successAlert = document.getElementById("new-password-alert-success");
    const btn = document.getElementById("save-new-pass-btn");

    if (pass !== confirmPass) {
        if (errorAlert) errorAlert.classList.remove("d-none");
        return;
    }

    if (errorAlert) errorAlert.classList.add("d-none");

    if (btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating Password...';
    }

    setTimeout(() => {
        if (successAlert) successAlert.classList.remove("d-none");
        setTimeout(() => {
            window.location.href = "login.html";
        }, 1200);
    }, 900);
}

/**
 * Simulates OAuth login provider authentication.
 * @param {string} provider - 'GitHub' | 'Google'
 */
function simulateOAuthLogin(provider) {
    alert(`Connecting to ${provider} OAuth 2.0 service...`);
    setTimeout(() => {
        window.location.href = "index.html";
    }, 800);
}

/* ==========================================================================
   HOMEPAGE INTERACTIVITY SYSTEM
   ========================================================================== */
document.addEventListener("DOMContentLoaded", () => {
    // Analytics Tab Buttons
    const analyticsTabs = document.querySelectorAll(".analytics-tab-btn");
    if (analyticsTabs.length > 0) {
        analyticsTabs.forEach((btn) => {
            btn.addEventListener("click", () => {
                analyticsTabs.forEach((b) => b.classList.remove("active"));
                btn.classList.add("active");
            });
        });
    }

    // Stat Counter Number Intersection Observer Animation
    const statNumbers = document.querySelectorAll(
        ".stat-counter-number[data-target]",
    );
    if (statNumbers.length > 0 && "IntersectionObserver" in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const targetEl = entry.target;
                        const targetVal = parseInt(
                            targetEl.getAttribute("data-target"),
                            10,
                        );
                        if (targetVal) {
                            let currentVal = 0;
                            const increment = Math.ceil(targetVal / 25);
                            const timer = setInterval(() => {
                                currentVal += increment;
                                if (currentVal >= targetVal) {
                                    currentVal = targetVal;
                                    clearInterval(timer);
                                }
                                const originalText = targetEl.textContent;
                                const suffix = originalText.includes("+")
                                    ? "+"
                                    : "";
                                const unit = originalText.includes("M")
                                    ? "M"
                                    : originalText.includes("K")
                                      ? "K"
                                      : "";
                                targetEl.textContent =
                                    currentVal + unit + suffix;
                            }, 40);
                        }
                        observer.unobserve(targetEl);
                    }
                });
            },
            { threshold: 0.3 },
        );

        statNumbers.forEach((num) => observer.observe(num));
    }

    // Footer Newsletter Form Submission Handler
    const newsletterForms = document.querySelectorAll(
        ".footer-newsletter-form",
    );
    newsletterForms.forEach((form) => {
        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const input = form.querySelector("input[type='email']");
            const btn = form.querySelector("button[type='submit']");
            if (input && input.value.trim() && btn) {
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML =
                    '<i class="fa-solid fa-check me-1"></i> Subscribed!';
                btn.classList.remove("btn-subscribe");
                btn.classList.add("btn-success");
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    btn.classList.remove("btn-success");
                    btn.classList.add("btn-subscribe");
                    input.value = "";
                }, 3000);
            }
        });
    });
});

/* ==========================================================================
   5. UNIVERSAL INFINITE SCROLLED LOADING ENGINE
   ========================================================================== */
const UniversalInfiniteScroller = {
    observer: null,
    scrollThrottleTimer: null,

    init() {
        if (!("IntersectionObserver" in window)) return;

        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const loader = entry.target;
                        if (loader.getAttribute("data-loading") === "false") {
                            this.fetchNextPage(loader);
                        }
                    }
                });
            },
            {
                root: null,
                rootMargin: "0px 0px 450px 0px",
                threshold: 0.05,
            },
        );

        this.observeElements(document);

        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (
                            node.classList &&
                            node.classList.contains("universal-infinite-loader")
                        ) {
                            this.observer.observe(node);
                            this.checkAndFetchIfVisible(node);
                        } else if (node.querySelectorAll) {
                            node.querySelectorAll(
                                ".universal-infinite-loader",
                            ).forEach((elem) => {
                                this.observer.observe(elem);
                                this.checkAndFetchIfVisible(elem);
                            });
                        }
                    }
                });
            });
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true,
        });

        window.addEventListener(
            "scroll",
            () => {
                if (!this.scrollThrottleTimer) {
                    this.scrollThrottleTimer = setTimeout(() => {
                        this.scrollThrottleTimer = null;
                        document
                            .querySelectorAll(
                                ".universal-infinite-loader[data-loading='false']",
                            )
                            .forEach((loader) => {
                                this.checkAndFetchIfVisible(loader);
                            });
                    }, 180);
                }
            },
            { passive: true },
        );
    },

    observeElements(container) {
        if (!this.observer) return;
        container
            .querySelectorAll(".universal-infinite-loader")
            .forEach((loader) => {
                this.observer.observe(loader);
                this.checkAndFetchIfVisible(loader);
            });
    },

    checkAndFetchIfVisible(loader) {
        if (
            !loader ||
            !document.body.contains(loader) ||
            loader.getAttribute("data-loading") !== "false"
        ) {
            return;
        }
        const rect = loader.getBoundingClientRect();
        const windowHeight =
            window.innerHeight || document.documentElement.clientHeight;
        if (rect.top <= windowHeight + 450 && rect.bottom >= -100) {
            this.fetchNextPage(loader);
        }
    },

    fetchNextPage(loader) {
        const nextUrl = loader.getAttribute("data-next-url");
        if (!nextUrl) return;

        loader.setAttribute("data-loading", "true");

        fetch(nextUrl, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-Infinite-Scroll": "true",
            },
        })
            .then((response) => response.text())
            .then((html) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");

                let targetSelector = loader.getAttribute(
                    "data-target-container",
                );
                let currentContainer = null;
                let newContainer = null;

                if (targetSelector && targetSelector.trim() !== "") {
                    currentContainer = document.querySelector(targetSelector);
                    newContainer = doc.querySelector(targetSelector);
                } else {
                    const parentCard =
                        loader.closest(".card") || loader.parentElement;
                    currentContainer = parentCard
                        ? parentCard.querySelector("tbody") ||
                          parentCard.querySelector(".row") ||
                          parentCard.querySelector("ul")
                        : null;
                    if (currentContainer && currentContainer.id) {
                        newContainer = doc.getElementById(currentContainer.id);
                    } else if (
                        currentContainer &&
                        currentContainer.tagName === "TBODY"
                    ) {
                        const tableId = currentContainer.closest("table")?.id;
                        newContainer = tableId
                            ? doc.querySelector(`#${tableId} tbody`)
                            : doc.querySelector("tbody");
                    }
                }

                if (currentContainer && newContainer) {
                    const fragment = document.createDocumentFragment();
                    Array.from(newContainer.children).forEach((child) => {
                        child.style.opacity = "0";
                        child.style.transition = "opacity 0.35s ease-in";
                        fragment.appendChild(child);
                    });

                    currentContainer.appendChild(fragment);

                    requestAnimationFrame(() => {
                        Array.from(currentContainer.children).forEach(
                            (child) => {
                                if (child.style.opacity === "0") {
                                    child.style.opacity = "1";
                                }
                            },
                        );
                    });
                }

                const newLoader = doc.querySelector(
                    ".universal-infinite-loader",
                );
                const newEndMessage = doc.querySelector(
                    ".universal-infinite-end",
                );

                if (newLoader && newLoader.getAttribute("data-next-url")) {
                    loader.setAttribute(
                        "data-next-url",
                        newLoader.getAttribute("data-next-url"),
                    );
                    loader.setAttribute("data-loading", "false");

                    if (this.observer) {
                        this.observer.unobserve(loader);
                        this.observer.observe(loader);
                    }
                    setTimeout(() => this.checkAndFetchIfVisible(loader), 120);
                } else if (newEndMessage) {
                    loader.insertAdjacentHTML(
                        "afterend",
                        newEndMessage.outerHTML,
                    );
                    if (this.observer) this.observer.unobserve(loader);
                    loader.remove();
                } else {
                    if (this.observer) this.observer.unobserve(loader);
                    loader.remove();
                }
            })
            .catch((err) => {
                console.error("Infinite scroll fetch failed:", err);
                loader.setAttribute("data-loading", "false");
                setTimeout(() => this.checkAndFetchIfVisible(loader), 1500);
            });
    },
};

document.addEventListener("DOMContentLoaded", () => {
    UniversalInfiniteScroller.init();
});

