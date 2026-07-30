@extends('web.layouts.app')
@section('content')
    <!-- ================= MAIN PAGE CONTAINER ================= -->
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Action Row -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav class="breadcrumb-list mb-1" aria-label="Breadcrumb navigation">
                    <a href="index.html">Home</a>
                    <span class="sep">/</span>
                    <a href="platforms.html">Platforms</a>
                    <span class="sep">/</span>
                    <span class="current">Codeforces</span>
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <h1 class="h3 fw-extrabold text-primary-emphasis mb-0 tracking-tight">
                        Codeforces Overview & Analytics
                    </h1>
                    <span
                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 extra-small fw-semibold">
                        <i class="fa-solid fa-circle text-success extra-small me-1"></i>
                        Live Adapter (Sync 30s)
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="https://calendar.google.com" target="_blank" rel="noopener"
                    class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5"
                    title="Export Contests to Google Calendar">
                    <i class="fa-regular fa-calendar-plus text-primary"></i>
                    Google Calendar
                </a>
                <button class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5"
                    data-bs-toggle="modal" data-bs-target="#sharePlatformModal">
                    <i class="fa-solid fa-share-nodes text-primary"></i>
                    Share Platform
                </button>
                <a href="https://codeforces.com" target="_blank" rel="noopener"
                    class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                    Visit Official Site
                </a>
                <button class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#connectPlatformModal">
                    <i class="fa-solid fa-plug"></i> Connect My Account
                </button>
            </div>
        </div>

        <!-- ================= 1. PLATFORM HERO BANNER CARD ================= -->
        <div class="card panel border-0 p-4 mb-4" style="border-radius: 18px">
            <div class="row align-items-center g-4">
                <!-- Left: Platform Branding & Bio -->
                <div class="col-lg-7">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                            style="
                                    width: 72px;
                                    height: 72px;
                                    background: var(--surface-tertiary);
                                    border: 1px solid var(--border-strong);
                                ">
                            <i class="fa-solid fa-code fs-1" style="color: var(--primary)"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <h2 class="h4 fw-bold text-primary-emphasis mb-0">
                                    Codeforces
                                </h2>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 extra-small fw-semibold">
                                    Rank #1 Judge
                                </span>
                                <span class="badge text-bg-secondary extra-small">Public API v2</span>
                            </div>
                            <p class="text-secondary small mb-3 text-balance">
                                Codeforces is the premier competitive
                                programming platform hosting weekly rated
                                rounds (Div. 1–4), an archive of 9,400+
                                algorithmic problems, and an ELO rating
                                scale (800 to 3800+).
                            </p>

                            <!-- Resource Feature Pills (Inspired by Clist Capabilities) -->
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle extra-small fw-semibold">
                                    <i class="fa-solid fa-rotate me-1"></i>
                                    Auto Updating
                                </span>
                                <span
                                    class="badge bg-info-subtle text-info border border-info-subtle extra-small fw-semibold">
                                    <i class="fa-solid fa-chart-line me-1"></i>
                                    Account Rating Tracked
                                </span>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle extra-small fw-semibold">
                                    <i class="fa-solid fa-layer-group me-1"></i>
                                    Problem Rating Index
                                </span>
                                <span
                                    class="badge bg-purple-subtle text-purple border border-purple-subtle extra-small fw-semibold">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i>
                                    Upsolving Enabled
                                </span>
                            </div>

                            <div class="extra-small text-muted d-flex align-items-center gap-3 flex-wrap">
                                <span><i class="fa-solid fa-building me-1 text-primary"></i>
                                    ITMO University</span>
                                <span><i class="fa-solid fa-calendar me-1 text-info"></i>
                                    Founded 2010</span>
                                <span><i class="fa-solid fa-user-check me-1 text-success"></i>
                                    Public Handle Sync</span>
                                <span><i class="fa-solid fa-chart-simple me-1 text-warning"></i>
                                    Rating Range: 800 – 3800</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Quick Connect Box -->
                <div class="col-lg-5">
                    <div class="p-3.5 rounded-3 border bg-body-tertiary shadow-sm text-start">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-primary-emphasis small">Connect Codeforces to JudgeArena</span>
                            <span class="badge bg-success-subtle text-success extra-small fw-semibold">30s Auto-Sync</span>
                        </div>
                        <p class="extra-small text-muted mb-3">
                            Synchronize your Codeforces rating history,
                            contest ranks, and 9,400+ solved problems into
                            your master JudgeArena profile.
                        </p>
                        <form
                            onsubmit="
                                    event.preventDefault();
                                    triggerCodeforcesConnect();
                                ">
                            <div class="input-group input-group-sm mb-2">
                                <span
                                    class="input-group-text bg-body text-muted border-end-0">codeforces.com/profile/</span>
                                <input type="text" class="form-control border-start-0" id="codeforces-handle-input"
                                    placeholder="e.g. tourist" required />
                                <button class="btn btn-primary fw-semibold" type="submit">
                                    <i class="fa-solid fa-plug me-1"></i>
                                    Connect
                                </button>
                            </div>
                            <div class="extra-small text-muted">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i>
                                No password required. Public handle
                                verification.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 2. KEY METRICS SUMMARY ROW (4 KPI CARDS) ================= -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Contests -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Hosted Contests</span>
                        <i class="fa-solid fa-trophy text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        2,138 Contests
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Div 1, Div 2, Div 3, Div 4, Edu
                    </div>
                </div>
            </div>

            <!-- Card 2: Registered Accounts Index -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Total Accounts</span>
                        <i class="fa-solid fa-users text-primary"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        1,459,210
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Global registered handles
                    </div>
                </div>
            </div>

            <!-- Card 3: Problem Archive Index -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Problem Directory</span>
                        <i class="fa-solid fa-layer-group text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        9,480 Problems
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Ratings: 800 to 3500+
                    </div>
                </div>
            </div>

            <!-- Card 4: Adapter Sync Performance -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Sync Engine</span>
                        <i class="fa-solid fa-bolt text-success"></i>
                    </div>
                    <div class="h3 fw-bold text-success mb-0">
                        99.9% Uptime
                    </div>
                    <div class="extra-small text-muted mt-1">
                        30s Auto Polling Interval
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 3. NEW WIDGET ROW: RATING TIERS & SCALE GUIDE + COUNTRY DEMOGRAPHICS ================= -->
        <div class="row g-3 mb-4">
            <!-- Widget 1: Official Codeforces Rating System Tiers Scale -->
            <div class="col-lg-7">
                <div class="card panel h-100 border-0 p-3.5" style="border-radius: 16px">
                    <div class="panel-head flex-wrap gap-2 mb-3">
                        <div class="panel-title">
                            <i class="fa-solid fa-ranking-star text-warning me-2"></i>
                            Official Codeforces Rating System Tiers Scale
                        </div>
                        <span class="badge bg-secondary extra-small font-monospace">ELO Standard</span>
                    </div>

                    <div class="row g-2">
                        <!-- Tier 1: Newbie -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-secondary mb-1">
                                    Newbie
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace extra-small">&lt;
                                    1200</span>
                                <div class="extra-small text-muted mt-1">
                                    Gray Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 2: Pupil -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-success mb-1">
                                    Pupil
                                </div>
                                <span class="badge bg-success-subtle text-success font-monospace extra-small">1200 -
                                    1399</span>
                                <div class="extra-small text-muted mt-1">
                                    Green Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 3: Specialist -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-info mb-1">
                                    Specialist
                                </div>
                                <span class="badge bg-info-subtle text-info font-monospace extra-small">1400 - 1599</span>
                                <div class="extra-small text-muted mt-1">
                                    Cyan Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 4: Expert -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-primary mb-1">
                                    Expert
                                </div>
                                <span class="badge bg-primary-subtle text-primary font-monospace extra-small">1600 -
                                    1899</span>
                                <div class="extra-small text-muted mt-1">
                                    Blue Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 5: Candidate Master -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-purple mb-1">
                                    Candidate Master
                                </div>
                                <span class="badge bg-purple-subtle text-purple font-monospace extra-small">1900 -
                                    2099</span>
                                <div class="extra-small text-muted mt-1">
                                    Purple Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 6: Master -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-warning mb-1">
                                    Master
                                </div>
                                <span class="badge bg-warning-subtle text-warning font-monospace extra-small">2100 -
                                    2299</span>
                                <div class="extra-small text-muted mt-1">
                                    Orange Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 7: Grandmaster -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-danger mb-1">
                                    Grandmaster
                                </div>
                                <span class="badge bg-danger-subtle text-danger font-monospace extra-small">2300 -
                                    2999</span>
                                <div class="extra-small text-muted mt-1">
                                    Red Rank
                                </div>
                            </div>
                        </div>

                        <!-- Tier 8: LGM -->
                        <div class="col-6 col-md-3">
                            <div class="p-2.5 rounded-3 border bg-body-tertiary text-center">
                                <div class="fw-bold extra-small text-danger mb-1">
                                    Legendary GM
                                </div>
                                <span class="badge bg-danger text-white font-monospace extra-small">3000+</span>
                                <div class="extra-small text-muted mt-1">
                                    Nutella Red
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Country Demographics Breakdown -->
            <div class="col-lg-5">
                <div class="card panel h-100 border-0 p-3.5" style="border-radius: 16px">
                    <div class="panel-head flex-wrap gap-2 mb-3">
                        <div class="panel-title">
                            <i class="fa-solid fa-globe text-primary me-2"></i>
                            Top Countries Participation Demographics
                        </div>
                        <span class="badge bg-primary-subtle text-primary extra-small fw-semibold">Global
                            Distribution</span>
                    </div>

                    <div class="space-y-2.5">
                        <!-- Country 1: China -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-6">🇨🇳</span>
                                <span class="fw-bold text-primary-emphasis small">China</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-primary font-monospace extra-small">184,200 Coders</span>
                                <span class="text-muted extra-small d-block font-monospace">Avg Rating: 1540</span>
                            </div>
                        </div>

                        <!-- Country 2: India -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-6">🇮🇳</span>
                                <span class="fw-bold text-primary-emphasis small">India</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-primary font-monospace extra-small">240,500 Coders</span>
                                <span class="text-muted extra-small d-block font-monospace">Avg Rating: 1380</span>
                            </div>
                        </div>

                        <!-- Country 3: Bangladesh -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-6">🇧🇩</span>
                                <span class="fw-bold text-primary-emphasis small">Bangladesh</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-primary font-monospace extra-small">48,900 Coders</span>
                                <span class="text-muted extra-small d-block font-monospace">Avg Rating: 1410</span>
                            </div>
                        </div>

                        <!-- Country 4: United States -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-6">🇺🇸</span>
                                <span class="fw-bold text-primary-emphasis small">United States</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-primary font-monospace extra-small">52,100 Coders</span>
                                <span class="text-muted extra-small d-block font-monospace">Avg Rating: 1510</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 4. ANALYTICS CHARTS ROW ================= -->
        <div class="row g-3 mb-4">
            <!-- Rating Tier Distribution Chart -->
            <div class="col-lg-6">
                <div class="card panel h-100">
                    <div class="panel-head flex-wrap gap-2">
                        <div class="panel-title">
                            <i class="fa-solid fa-chart-column text-primary me-2"></i>
                            Codeforces User Rating Tier Distribution
                        </div>
                        <span
                            class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 extra-small fw-semibold">
                            42,850 Tracked Users
                        </span>
                    </div>
                    <div
                        style="
                                position: relative;
                                height: 260px;
                                width: 100%;
                            ">
                        <canvas id="cfRatingDistChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Solved Problems by Difficulty Curve Chart -->
            <div class="col-lg-6">
                <div class="card panel h-100">
                    <div class="panel-head flex-wrap gap-2">
                        <div class="panel-title">
                            <i class="fa-solid fa-chart-area text-success me-2"></i>
                            Solved Problems by Difficulty Curve
                        </div>
                        <span
                            class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 extra-small fw-semibold">
                            9,480 Problems Indexed
                        </span>
                    </div>
                    <div
                        style="
                                position: relative;
                                height: 260px;
                                width: 100%;
                            ">
                        <canvas id="cfDifficultyDistChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 5. NEW WIDGET ROW: CONTEST DIVISIONS MATRIX & TOP PROBLEM SETTERS ================= -->
        <div class="row g-3 mb-4">
            <!-- Widget 3: Contest Divisions Breakdown Matrix -->
            <div class="col-lg-6">
                <div class="card panel h-100 border-0 p-3.5" style="border-radius: 16px">
                    <div class="panel-head flex-wrap gap-2 mb-3">
                        <div class="panel-title">
                            <i class="fa-solid fa-cubes-stacked text-purple me-2" style="color: var(--purple)"></i>
                            Contest Division Formats & Frequencies
                        </div>
                        <span class="badge bg-purple-subtle text-purple extra-small fw-semibold">Format Breakdown</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0 text-nowrap extra-small">
                            <thead class="table-light text-muted uppercase font-monospace">
                                <tr>
                                    <th>Division</th>
                                    <th>Target Rating</th>
                                    <th>Frequency</th>
                                    <th>Avg Duration</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger">Div. 1</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-primary-emphasis">Rating &ge; 1900</span>
                                    </td>
                                    <td>Bi-weekly</td>
                                    <td>
                                        <span class="font-monospace">2h 15m</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-purple-subtle text-purple">Div. 2</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-primary-emphasis">Rating &lt; 2100</span>
                                    </td>
                                    <td>Weekly</td>
                                    <td>
                                        <span class="font-monospace">2h 00m</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">Div. 3</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-primary-emphasis">Rating &lt; 1600</span>
                                    </td>
                                    <td>Bi-weekly</td>
                                    <td>
                                        <span class="font-monospace">2h 15m</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">Div. 4</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-primary-emphasis">Rating &lt; 1400</span>
                                    </td>
                                    <td>Monthly</td>
                                    <td>
                                        <span class="font-monospace">2h 15m</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning">Educational</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-primary-emphasis">Rating &lt; 2100</span>
                                    </td>
                                    <td>Bi-weekly</td>
                                    <td>
                                        <span class="font-monospace">2h 00m</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Widget 4: Top Problem Setters & Authors -->
            <div class="col-lg-6">
                <div class="card panel h-100 border-0 p-3.5" style="border-radius: 16px">
                    <div class="panel-head flex-wrap gap-2 mb-3">
                        <div class="panel-title">
                            <i class="fa-solid fa-pen-nib text-primary me-2"></i>
                            Featured Problem Setters & Authors
                        </div>
                        <span class="badge bg-primary-subtle text-primary extra-small fw-semibold">Problem Creators</span>
                    </div>

                    <div class="space-y-2.5">
                        <!-- Setter 1: Mike Mirzayanov -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold extra-small"
                                    style="width: 32px; height: 32px">
                                    MM
                                </div>
                                <div>
                                    <div class="fw-bold text-primary-emphasis small">
                                        Mike Mirzayanov
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        @MikeMirzayanov · Founder
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-secondary extra-small font-monospace">480+ Problems</span>
                        </div>

                        <!-- Setter 2: Errichto -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold extra-small"
                                    style="width: 32px; height: 32px">
                                    ER
                                </div>
                                <div>
                                    <div class="fw-bold text-primary-emphasis small">
                                        Kamil Dębowski
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        @Errichto · Setter
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-secondary extra-small font-monospace">120+ Problems</span>
                        </div>

                        <!-- Setter 3: Um_nik -->
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center fw-bold extra-small"
                                    style="width: 32px; height: 32px">
                                    UM
                                </div>
                                <div>
                                    <div class="fw-bold text-primary-emphasis small">
                                        Alexey Danilyuk
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        @Um_nik · Setter
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-secondary extra-small font-monospace">95+ Problems</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 6. TABBED CONTENT SECTION (SAAS TAB SYSTEM) ================= -->
        <div class="card panel border-0 p-4 mb-4" style="border-radius: 18px">
            <!-- Tab Navigation Bar -->
            <nav class="tab-nav mb-4" id="platform-tab-nav" aria-label="Platform Details Tabs">
                <button class="tab-button active" data-tab="pf-contests">
                    <i class="fa-solid fa-trophy me-1.5"></i> Contests
                    Directory (2,138)
                </button>
                <button class="tab-button" data-tab="pf-problems">
                    <i class="fa-solid fa-layer-group me-1.5"></i> Problem
                    Directory (9,480)
                </button>
                <button class="tab-button" data-tab="pf-leaderboard">
                    <i class="fa-solid fa-award me-1.5"></i> Top Leaderboard
                    Users
                </button>
                <button class="tab-button" data-tab="pf-blogs">
                    <i class="fa-solid fa-newspaper me-1.5"></i> Official
                    Feed & Blogs
                </button>
                <button class="tab-button" data-tab="pf-adapter">
                    <i class="fa-solid fa-microchip me-1.5"></i> Adapter
                    Specifications
                </button>
            </nav>

            <!-- TAB 1: CONTESTS DIRECTORY -->
            <section class="tab-content" id="tab-content-pf-contests">
                <div
                    class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                    <div class="position-relative flex-grow-1" style="max-width: 380px">
                        <i
                            class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                        <input type="text" class="form-control ps-5 pe-4 rounded-3"
                            placeholder="Search Codeforces contests..." id="pf-contest-search"
                            onkeyup="filterPlatformContests()" />
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted extra-small font-monospace uppercase fw-semibold">Filter Phase:</span>
                        <select class="form-select form-select-sm rounded-3" style="width: 150px"
                            onchange="
                                    filterPlatformContestStatus(this.value)
                                ">
                            <option value="all" selected>All Phases</option>
                            <option value="live">Live Now</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="past">Past Rounds</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="pf-contests-table">
                        <thead class="table-light extra-small text-uppercase font-monospace text-muted">
                            <tr>
                                <th scope="col" style="min-width: 280px">
                                    Contest Name & Round
                                </th>
                                <th scope="col" style="min-width: 140px">
                                    Division
                                </th>
                                <th scope="col" style="min-width: 170px">
                                    Start Date & Time
                                </th>
                                <th scope="col" style="min-width: 110px">
                                    Duration
                                </th>
                                <th scope="col" style="min-width: 160px">
                                    Participants
                                </th>
                                <th scope="col" class="text-end" style="min-width: 140px">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <!-- Contest 1 -->
                            <tr data-status="live">
                                <td>
                                    <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-2">
                                        Codeforces Round 955 (Div. 2)
                                        <span class="badge-live-pulse rounded-pill px-2 py-0-5 extra-small"><span
                                                class="pulse-dot"></span>
                                            LIVE</span>
                                    </div>
                                    <div class="extra-small text-muted">
                                        Official Rated Round for Div. 2
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-purple-subtle text-purple extra-small">Div. 2</span>
                                </td>
                                <td>
                                    <div class="fw-medium text-primary-emphasis">
                                        Today, 08:00 PM
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        UTC+06:00
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium font-monospace">2h 00m</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary">18,420 Contestants</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/contests" target="_blank"
                                        class="btn btn-sm btn-danger px-3 py-1-5 fw-semibold rounded-2">
                                        <i class="fa-solid fa-right-to-bracket me-1"></i>
                                        Enter
                                    </a>
                                </td>
                            </tr>

                            <!-- Contest 2 -->
                            <tr data-status="upcoming">
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Codeforces Round 956 (Div. 1 + Div.
                                        2)
                                    </div>
                                    <div class="extra-small text-muted">
                                        Combined Global Rated Contest
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary extra-small">Div. 1 + Div. 2</span>
                                </td>
                                <td>
                                    <div class="fw-medium text-primary-emphasis">
                                        Jul 28, 08:00 PM
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        UTC+06:00
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium font-monospace">2h 15m</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-muted">24,150 Registered</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/contests" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Register
                                    </a>
                                </td>
                            </tr>

                            <!-- Contest 3 -->
                            <tr data-status="upcoming">
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Educational Codeforces Round 168
                                    </div>
                                    <div class="extra-small text-muted">
                                        Rated for Div. 2 · Educational
                                        Archive
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info extra-small">Educational</span>
                                </td>
                                <td>
                                    <div class="fw-medium text-primary-emphasis">
                                        Aug 02, 08:00 PM
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        UTC+06:00
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium font-monospace">2h 00m</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-muted">15,890 Registered</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/contests" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Register
                                    </a>
                                </td>
                            </tr>

                            <!-- Contest 4 -->
                            <tr data-status="past">
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Codeforces Round 954 (Div. 3)
                                    </div>
                                    <div class="extra-small text-muted">
                                        Rated for Div. 3 (Rating &lt; 1600)
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success extra-small">Div. 3</span>
                                </td>
                                <td>
                                    <div class="fw-medium text-primary-emphasis">
                                        Jul 20, 08:00 PM
                                    </div>
                                    <div class="extra-small text-muted font-monospace">
                                        UTC+06:00
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium font-monospace">2h 15m</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-muted">28,400 Solved</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/contest/1986" target="_blank"
                                        class="btn btn-sm btn-outline-secondary px-3 py-1-5 fw-semibold rounded-2">
                                        View Archive
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- TAB 2: PROBLEM DIRECTORY -->
            <section class="tab-content d-none" id="tab-content-pf-problems">
                <div
                    class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                    <div class="position-relative flex-grow-1" style="max-width: 380px">
                        <i
                            class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                        <input type="text" class="form-control ps-5 pe-4 rounded-3"
                            placeholder="Search problem ID, title, or tag..." id="pf-problem-search"
                            onkeyup="filterPlatformProblems()" />
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <select class="form-select form-select-sm rounded-3" style="width: 150px">
                            <option value="all" selected>
                                All Difficulties
                            </option>
                            <option value="800-1200">
                                800 - 1200 (Easy)
                            </option>
                            <option value="1300-1800">
                                1300 - 1800 (Medium)
                            </option>
                            <option value="1900-2400">
                                1900 - 2400 (Hard)
                            </option>
                            <option value="2500+">2500+ (Master)</option>
                        </select>
                        <select class="form-select form-select-sm rounded-3" style="width: 160px">
                            <option value="all" selected>
                                All Problem Tags
                            </option>
                            <option value="dp">Dynamic Programming</option>
                            <option value="graphs">Graph Theory</option>
                            <option value="math">Mathematics</option>
                            <option value="greedy">Greedy</option>
                            <option value="data-structures">
                                Data Structures
                            </option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="pf-problems-table">
                        <thead class="table-light extra-small text-uppercase font-monospace text-muted">
                            <tr>
                                <th scope="col" style="min-width: 120px">
                                    Code
                                </th>
                                <th scope="col" style="min-width: 260px">
                                    Problem Title
                                </th>
                                <th scope="col" style="min-width: 130px">
                                    Rating
                                </th>
                                <th scope="col" style="min-width: 200px">
                                    Primary Tags
                                </th>
                                <th scope="col" style="min-width: 160px">
                                    JudgeArena Solved
                                </th>
                                <th scope="col" class="text-end" style="min-width: 120px">
                                    Solve Link
                                </th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">1931F</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Programmable Robot
                                    </div>
                                    <div class="extra-small text-muted">
                                        Codeforces Round 928 (Div. 2)
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold font-monospace">1600</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-secondary extra-small">dp</span>
                                    <span class="badge text-bg-secondary extra-small">graphs</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">14,280 Users</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/problemset/problem/1931/F" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Solve
                                        <i class="fa-solid fa-arrow-up-right-from-square extra-small ms-1"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">1986E</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Beautiful Array
                                    </div>
                                    <div class="extra-small text-muted">
                                        Codeforces Round 954 (Div. 3)
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-info-subtle text-info border border-info-subtle fw-bold font-monospace">1400</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-secondary extra-small">greedy</span>
                                    <span class="badge text-bg-secondary extra-small">math</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">18,940 Users</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/problemset/problem/1986/E" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Solve
                                        <i class="fa-solid fa-arrow-up-right-from-square extra-small ms-1"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">1985H2</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Maximize the Largest Component
                                    </div>
                                    <div class="extra-small text-muted">
                                        Codeforces Round 952 (Div. 4)
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold font-monospace">2100</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-secondary extra-small">dsu</span>
                                    <span class="badge text-bg-secondary extra-small">grid</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">4,120 Users</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/problemset/problem/1985/H2" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Solve
                                        <i class="fa-solid fa-arrow-up-right-from-square extra-small ms-1"></i>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">4A</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary-emphasis">
                                        Watermelon
                                    </div>
                                    <div class="extra-small text-muted">
                                        Codeforces Beta Round 4 (Div. 2)
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle fw-bold font-monospace">800</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-secondary extra-small">brute force</span>
                                    <span class="badge text-bg-secondary extra-small">math</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">382,400 Users</span>
                                </td>
                                <td class="text-end">
                                    <a href="https://codeforces.com/problemset/problem/4/A" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Solve
                                        <i class="fa-solid fa-arrow-up-right-from-square extra-small ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- TAB 3: TOP LEADERBOARD USERS -->
            <section class="tab-content d-none" id="tab-content-pf-leaderboard">
                <div
                    class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                    <div>
                        <h3 class="h6 fw-bold mb-0 text-primary-emphasis">
                            Top Codeforces Programmers on JudgeArena
                        </h3>
                        <p class="extra-small text-muted mb-0">
                            Rankings automatically updated from verified
                            Codeforces ratings.
                        </p>
                    </div>
                    <div class="position-relative" style="max-width: 320px">
                        <i
                            class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                        <input type="text" class="form-control form-control-sm ps-5 pe-3 rounded-3"
                            placeholder="Search user handle..." />
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light extra-small text-uppercase font-monospace text-muted">
                            <tr>
                                <th scope="col" style="width: 70px">
                                    Rank
                                </th>
                                <th scope="col" style="min-width: 240px">
                                    User & Country
                                </th>
                                <th scope="col" style="min-width: 160px">
                                    Title Tier
                                </th>
                                <th scope="col" style="min-width: 140px">
                                    Codeforces Rating
                                </th>
                                <th scope="col" style="min-width: 140px">
                                    Codeforces Solved
                                </th>
                                <th scope="col" class="text-end" style="min-width: 140px">
                                    Profile
                                </th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <!-- User 1 -->
                            <tr>
                                <td>
                                    <span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-1">#1</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <img src="img/khairul-islam-emon.jpg" alt="tourist avatar"
                                            class="rounded-circle border" width="36" height="36" />
                                        <div>
                                            <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-1.5">
                                                🇧🇾 Gennady Korotkevich
                                                <i class="fa-solid fa-circle-check verified-badge"
                                                    style="
                                                            font-size: 0.75rem;
                                                        "></i>
                                            </div>
                                            <div class="extra-small text-secondary font-monospace">
                                                @tourist
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-legendary-lgm">Legendary Grandmaster</span>
                                </td>
                                <td>
                                    <span class="fw-extrabold text-danger font-monospace fs-6">3782</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary font-monospace">1,840 Problems</span>
                                </td>
                                <td class="text-end">
                                    <a href="profile.html"
                                        class="btn btn-sm btn-outline-secondary px-3 py-1-5 fw-semibold rounded-2">
                                        View Profile
                                    </a>
                                </td>
                            </tr>

                            <!-- User 2 -->
                            <tr>
                                <td>
                                    <span class="badge bg-secondary text-white font-monospace fw-bold px-2 py-1">#2</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="bg-body-tertiary rounded-circle d-flex align-items-center justify-content-center border font-monospace fw-bold text-muted"
                                            style="
                                                    width: 36px;
                                                    height: 36px;
                                                ">
                                            BQ
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-1.5">
                                                🇺🇸 Benjamin Qi
                                            </div>
                                            <div class="extra-small text-secondary font-monospace">
                                                @Benq
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-legendary-lgm">Legendary Grandmaster</span>
                                </td>
                                <td>
                                    <span class="fw-extrabold text-danger font-monospace fs-6">3690</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary font-monospace">2,110 Problems</span>
                                </td>
                                <td class="text-end">
                                    <a href="profile.html"
                                        class="btn btn-sm btn-outline-secondary px-3 py-1-5 fw-semibold rounded-2">
                                        View Profile
                                    </a>
                                </td>
                            </tr>

                            <!-- User 3 -->
                            <tr>
                                <td>
                                    <span class="badge bg-secondary text-white font-monospace fw-bold px-2 py-1">#3</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="bg-body-tertiary rounded-circle d-flex align-items-center justify-content-center border font-monospace fw-bold text-muted"
                                            style="
                                                    width: 36px;
                                                    height: 36px;
                                                ">
                                            EZ
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-1.5">
                                                🇯🇵 Ezaki Dai
                                            </div>
                                            <div class="extra-small text-secondary font-monospace">
                                                @EvenImage
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-legendary-lgm">Legendary Grandmaster</span>
                                </td>
                                <td>
                                    <span class="fw-extrabold text-danger font-monospace fs-6">3682</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary font-monospace">1,960 Problems</span>
                                </td>
                                <td class="text-end">
                                    <a href="profile.html"
                                        class="btn btn-sm btn-outline-secondary px-3 py-1-5 fw-semibold rounded-2">
                                        View Profile
                                    </a>
                                </td>
                            </tr>

                            <!-- User 4 -->
                            <tr>
                                <td>
                                    <span
                                        class="badge bg-body-tertiary text-muted font-monospace fw-bold px-2 py-1 border">#4</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="bg-body-tertiary rounded-circle d-flex align-items-center justify-content-center border font-monospace fw-bold text-muted"
                                            style="
                                                    width: 36px;
                                                    height: 36px;
                                                ">
                                            ZK
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-1.5">
                                                🇨🇳 Kangyang Zhou
                                            </div>
                                            <div class="extra-small text-secondary font-monospace">
                                                @zhoukangyang
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-legendary-lgm">Legendary Grandmaster</span>
                                </td>
                                <td>
                                    <span class="fw-extrabold text-danger font-monospace fs-6">3670</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary font-monospace">1,540 Problems</span>
                                </td>
                                <td class="text-end">
                                    <a href="profile.html"
                                        class="btn btn-sm btn-outline-secondary px-3 py-1-5 fw-semibold rounded-2">
                                        View Profile
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- TAB 4: OFFICIAL FEED & BLOGS -->
            <section class="tab-content d-none" id="tab-content-pf-blogs">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                    <h3 class="h6 fw-bold mb-0 text-primary-emphasis">
                        <i class="fa-solid fa-newspaper text-primary me-2"></i>
                        Official Codeforces Announcements & Editorials Feed
                    </h3>
                    <a href="https://codeforces.com/blogs" target="_blank"
                        class="extra-small fw-semibold text-primary text-decoration-none">
                        View on Codeforces
                        <i class="fa-solid fa-arrow-up-right-from-square extra-small"></i>
                    </a>
                </div>

                <div class="space-y-3">
                    <!-- Blog 1 -->
                    <div class="p-3 rounded-3 border bg-body-tertiary">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-primary-subtle text-primary extra-small fw-semibold">Contest
                                Announcement</span>
                            <span class="extra-small text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>3
                                hours ago</span>
                        </div>
                        <h4 class="h6 fw-bold text-primary-emphasis mb-1">
                            Codeforces Round 956 (Div. 1 + Div. 2)
                            Announcement
                        </h4>
                        <p class="extra-small text-muted mb-2">
                            Hello Codeforces! We invite you to participate
                            in Codeforces Round 956 (Div. 1 + Div. 2), which
                            will take place on Jul 28, 2026. The contest
                            will be rated for both divisions.
                        </p>
                        <div class="d-flex align-items-center gap-3 extra-small">
                            <span class="text-secondary"><i class="fa-solid fa-user me-1 text-primary"></i>
                                Author: @zhoukangyang</span>
                            <span class="text-secondary"><i class="fa-regular fa-thumbs-up me-1 text-success"></i>
                                +1,420 votes</span>
                            <span class="text-secondary"><i class="fa-regular fa-comment me-1 text-info"></i>
                                340 comments</span>
                        </div>
                    </div>

                    <!-- Blog 2 -->
                    <div class="p-3 rounded-3 border bg-body-tertiary">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-success-subtle text-success extra-small fw-semibold">Editorial</span>
                            <span class="extra-small text-muted font-monospace"><i
                                    class="fa-regular fa-clock me-1"></i>Yesterday</span>
                        </div>
                        <h4 class="h6 fw-bold text-primary-emphasis mb-1">
                            Editorial for Educational Codeforces Round 167
                            (Div. 2)
                        </h4>
                        <p class="extra-small text-muted mb-2">
                            Detailed editorial solutions and tutorial notes
                            for problems A through F of Educational
                            Codeforces Round 167 are now live.
                        </p>
                        <div class="d-flex align-items-center gap-3 extra-small">
                            <span class="text-secondary"><i class="fa-solid fa-user me-1 text-primary"></i>
                                Author: @Beker</span>
                            <span class="text-secondary"><i class="fa-regular fa-thumbs-up me-1 text-success"></i>
                                +890 votes</span>
                            <span class="text-secondary"><i class="fa-regular fa-comment me-1 text-info"></i>
                                180 comments</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 5: ADAPTER SPECIFICATIONS -->
            <section class="tab-content d-none" id="tab-content-pf-adapter">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <h3 class="h6 fw-bold text-primary-emphasis mb-3">
                            <i class="fa-solid fa-microchip text-primary me-2"></i>
                            JudgeArena Codeforces Adapter Architecture
                        </h3>
                        <p class="text-secondary small mb-4">
                            JudgeArena connects to Codeforces via official
                            API v2 endpoints. Data synchronization occurs
                            automatically in non-blocking background
                            workers.
                        </p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-body-tertiary h-100">
                                    <div class="fw-bold text-primary-emphasis small mb-1">
                                        <i class="fa-solid fa-network-wired text-info me-1.5"></i>
                                        API Endpoint
                                    </div>
                                    <div class="font-monospace extra-small text-muted">
                                        codeforces.com/api/user.info
                                    </div>
                                    <div class="font-monospace extra-small text-muted">
                                        codeforces.com/api/user.status
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-body-tertiary h-100">
                                    <div class="fw-bold text-primary-emphasis small mb-1">
                                        <i class="fa-solid fa-clock-rotate-left text-warning me-1.5"></i>
                                        Polling Interval
                                    </div>
                                    <div class="extra-small text-secondary fw-semibold">
                                        30 Seconds for Live Contests
                                    </div>
                                    <div class="extra-small text-muted">
                                        15 Minutes for Idle Profiles
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-body-tertiary h-100">
                                    <div class="fw-bold text-primary-emphasis small mb-1">
                                        <i class="fa-solid fa-shield-check text-success me-1.5"></i>
                                        Verification Protocol
                                    </div>
                                    <div class="extra-small text-secondary fw-semibold">
                                        Public Handle Verification
                                    </div>
                                    <div class="extra-small text-muted">
                                        Optional Bio Token for Ownership
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 rounded-3 border bg-body-tertiary h-100">
                                    <div class="fw-bold text-primary-emphasis small mb-1">
                                        <i class="fa-solid fa-database text-purple me-1.5"
                                            style="color: var(--purple)"></i>
                                        Data Scope
                                    </div>
                                    <div class="extra-small text-secondary fw-semibold">
                                        Ratings, Submissions, Verdicts
                                    </div>
                                    <div class="extra-small text-muted">
                                        Tags, Solved Timestamps, Ranks
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="p-4 rounded-3 border bg-body-tertiary">
                            <h4 class="h6 fw-bold text-primary-emphasis mb-3">
                                Adapter Engine Status
                            </h4>
                            <ul class="list-unstyled extra-small text-secondary mb-3 space-y-2">
                                <li class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="text-muted">Adapter Status</span>
                                    <span class="text-success fw-bold">● Operational</span>
                                </li>
                                <li class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="text-muted">Current Engine Latency</span>
                                    <span class="font-monospace fw-semibold">18 ms</span>
                                </li>
                                <li class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="text-muted">Daily Processed Submissions</span>
                                    <span class="font-monospace fw-semibold">480,200 / day</span>
                                </li>
                                <li class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="text-muted">Adapter Version</span>
                                    <span class="font-monospace fw-semibold">v3.8.2-stable</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span class="text-muted">Rate Limit Protocol</span>
                                    <span class="font-monospace fw-semibold">5 req / sec (Compliant)</span>
                                </li>
                            </ul>
                            <button class="btn btn-sm btn-outline-secondary w-100 fw-semibold"
                                onclick="
                                        alert(
                                            'Codeforces adapter health check: 100% operational.',
                                        )
                                    ">
                                <i class="fa-solid fa-heart-pulse text-danger me-1"></i>
                                Run Adapter Diagnostic
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- ================= MODALS ================= -->
    <!-- Modal 1: Connect Platform Handle -->
    <div class="modal fade" id="connectPlatformModal" tabindex="-1" aria-labelledby="connectPlatformModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-primary-emphasis" id="connectPlatformModalLabel">
                        <i class="fa-solid fa-plug text-primary me-2"></i>
                        Connect Codeforces Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Enter your official Codeforces handle. JudgeArena
                        will verify your public profile and aggregate your
                        submission history.
                    </p>
                    <form
                        onsubmit="
                                event.preventDefault();
                                triggerCodeforcesConnect();
                            ">
                        <div class="mb-3">
                            <label for="modal-cf-handle"
                                class="form-label small fw-semibold text-primary-emphasis">Codeforces Handle</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary text-muted">codeforces.com/profile/</span>
                                <input type="text" class="form-control" id="modal-cf-handle"
                                    placeholder="e.g. tourist" required />
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="syncSubmissionsCheck"
                                checked />
                            <label class="form-check-label extra-small text-muted" for="syncSubmissionsCheck">
                                Sync past solved problems & rating
                                trajectory automatically
                            </label>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold shadow-sm">
                                Verify & Sync Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Share Platform Hub -->
    <div class="modal fade" id="sharePlatformModal" tabindex="-1" aria-labelledby="sharePlatformModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-primary-emphasis" id="sharePlatformModalLabel">
                        <i class="fa-solid fa-share-nodes text-primary me-2"></i>
                        Share Codeforces Hub
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        Share Codeforces analytics and contest index with
                        competitive programmers.
                    </p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control small font-monospace"
                            value="https://judgearena.com/platforms/codeforces" id="share-platform-url-input" readonly />
                        <button class="btn btn-outline-secondary fw-semibold" type="button"
                            onclick="
                                    navigator.clipboard.writeText(
                                        document.getElementById(
                                            'share-platform-url-input',
                                        ).value,
                                    );
                                    alert('Copied URL to clipboard!');
                                ">
                            <i class="fa-regular fa-copy me-1"></i> Copy
                            Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
