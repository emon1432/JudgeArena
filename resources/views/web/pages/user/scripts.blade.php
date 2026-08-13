<script>
    let ratingChartInstance = null;
    let platformChartInstance = null;
    let verdictChartInstance = null;
    let difficultyChartInstance = null;
    
    function getThemeColors() {
        const root = document.documentElement;
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
            cyan: "#06b6d4"
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
    
        // 2. Platform Solved Distribution Donut Chart
        const platformCtx = document.getElementById("platformChart");
        if (platformCtx) {
            if (platformChartInstance) platformChartInstance.destroy();
    
            const platformLabels = {!! json_encode($platformCounts->keys()) !!};
            const platformData = {!! json_encode($platformCounts->values()) !!};
            const totalPlatform = platformData.reduce((a, b) => a + b, 0);
    
            platformChartInstance = new Chart(platformCtx, {
                type: "doughnut",
                data: {
                    labels: platformLabels.length > 0 ? platformLabels : ["None"],
                    datasets: [
                        {
                            data: platformData.length > 0 ? platformData : [1],
                            backgroundColor: [
                                colors.blue,
                                colors.orange,
                                colors.cyan,
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
                                    const pct = totalPlatform > 0 ? ((val / totalPlatform) * 100).toFixed(1) : 0;
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
    
            const verdictLabels = {!! json_encode($verdictCounts->keys()) !!};
            const verdictData = {!! json_encode($verdictCounts->values()) !!};
            
            verdictChartInstance = new Chart(verdictCtx, {
                type: "doughnut",
                data: {
                    labels: verdictLabels.length > 0 ? verdictLabels : ["None"],
                    datasets: [
                        {
                            data: verdictData.length > 0 ? verdictData : [1],
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
                                label: (context) => {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.parsed;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${val} (${pct}%)`;
                                }
                            },
                        },
                    },
                },
            });
        }
    
        // 4. Difficulty Tier Bar Chart (Enhanced Professional Design)
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
    
            const gGreen = createGrad("rgba(34, 197, 94, 0.95)", "rgba(34, 197, 94, 0.35)");
            const gCyan = createGrad("rgba(6, 182, 212, 0.95)", "rgba(6, 182, 212, 0.35)");
            const gBlue = createGrad("rgba(59, 130, 246, 0.95)", "rgba(59, 130, 246, 0.35)");
            const gPurple = createGrad("rgba(139, 92, 246, 0.95)", "rgba(139, 92, 246, 0.35)");
            const gOrange = createGrad("rgba(249, 115, 22, 0.95)", "rgba(249, 115, 22, 0.35)");
            const gRed = createGrad("rgba(239, 68, 68, 0.95)", "rgba(239, 68, 68, 0.35)");
    
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
                    labels: ["800-1100", "1200-1400", "1500-1700", "1800-2000", "2100-2400", "2500+"],
                    datasets: [
                        {
                            label: "Problems Solved",
                            data: dataValues,
                            backgroundColor: [gGreen, gCyan, gBlue, gPurple, gOrange, gRed],
                            borderColor: ["#22c55e", "#06b6d4", "#3b82f6", "#8b5cf6", "#f97316", "#ef4444"],
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
                    layout: { padding: { top: 20, bottom: 0 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                title: (items) => difficultyRanks[items[0].dataIndex],
                                label: (context) => {
                                    const val = context.parsed.y;
                                    const pct = ((val / totalSolved) * 100).toFixed(1);
                                    return ` ${val} Problems (${pct}% of total)`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: colors.mutedColor, font: { family: "Inter", size: 11, weight: "600" } },
                        },
                        y: {
                            grid: { color: colors.gridColor },
                            ticks: { color: colors.mutedColor, font: { family: "Inter", size: 11 } },
                            suggestedMax: 470,
                        },
                    },
                },
            });
        }
    }
    
    // Re-render chart colors when theme toggles
    if (typeof applyTheme !== 'undefined') {
        const prevApplyTheme = applyTheme;
        applyTheme = function (theme, savePreference = true) {
            prevApplyTheme(theme, savePreference);
            setTimeout(initCpCharts, 50);
        };
    }
    
    function initGithubHeatmap() {
        const gridContainer = document.getElementById("github-heatmap-grid");
        if (!gridContainer) return;
    
        gridContainer.innerHTML = "";
    
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const startDate = new Date(2026, 0, 1);
    
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
    
                const tooltipContent = count > 0
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
        }, 100);
    });
</script>
