@extends('web.layouts.app')
@section('content')
    <!-- ================= MAIN PAGE CONTAINER ================= -->
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Action Row -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav class="breadcrumb-list mb-1" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <span class="current">Platforms Directory</span>
                </nav>
                <h1 class="h3 fw-extrabold text-primary-emphasis mb-0 tracking-tight">
                    Supported Platforms Directory
                </h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button
                    class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                    id="sync-all-btn" onclick="syncAllPlatforms()">
                    <i class="fa-solid fa-rotate text-primary" id="sync-all-icon"></i>
                    <span id="sync-all-text">Sync All Handles</span>
                </button>
            </div>
        </div>

        <!-- Key Metrics Summary Row (KPI Cards) -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Supported Judges -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Supported Judges</span>
                        <i class="fa-solid fa-cubes text-primary"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        100+
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Global online judges integrated
                    </div>
                </div>
            </div>

            <!-- Card 2: Synchronized Submissions -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Tracked Submissions</span>
                        <i class="fa-solid fa-database text-success"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        2,450,000+
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Real-time submission log
                    </div>
                </div>
            </div>

            <!-- Card 3: Sync Engine Uptime -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Engine Uptime</span>
                        <i class="fa-solid fa-bolt text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        99.9%
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Live Webhook & API sync
                    </div>
                </div>
            </div>

            <!-- Card 4: Connected Handles -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Connected Handles</span>
                        <i class="fa-solid fa-users text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        85,400+
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Active user handles connected
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Platforms Directory Toolbar (Search, Category Pills, Custom Dropdowns) -->
        <div class="card panel border-0 p-3 mb-4" style="border-radius: 16px">
            <!-- Top Row: Search + Category Pills + Sort -->
            <div
                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <!-- Search Input -->
                <div class="position-relative flex-grow-1" style="max-width: 380px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" id="platforms-directory-search" class="form-control ps-5 pe-4 rounded-3"
                        placeholder="Search platform name, code, domain..." onkeyup="applyPlatformsFilters(true)" />
                </div>

                <!-- Category Nav Pills -->
                <div class="nav nav-pills saas-filter-pills gap-1 flex-wrap" id="platform-category-pills">
                    <button class="nav-link active platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterPlatformsByCategory('all', this)">
                        All (24)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterPlatformsByCategory('global', this)">
                        <i class="fa-solid fa-globe text-primary me-1 extra-small"></i>
                        Global Judges (8)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="
                                filterPlatformsByCategory('olympiad', this)
                            ">
                        <i class="fa-solid fa-graduation-cap text-warning me-1 extra-small"></i>
                        Olympiad & Regional (8)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="
                                filterPlatformsByCategory('specialized', this)
                            ">
                        <i class="fa-solid fa-robot text-success me-1 extra-small"></i>
                        Specialized & AI (8)
                    </button>
                </div>

                <!-- Sort Dropdown -->
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm rounded-3" style="width: auto; min-width: 180px"
                        onchange="sortPlatformsList(this.value)">
                        <option value="popular" selected>
                            Most Popular (Connected)
                        </option>
                        <option value="name-asc">
                            Platform Name (A-Z)
                        </option>
                        <option value="users-desc">
                            Active Users (High to Low)
                        </option>
                    </select>
                </div>
            </div>

            <!-- Bottom Row: Sync Capability Filter & Connection Status Dropdown -->
            <div class="row g-3 align-items-center">
                <!-- Sync Capability Dropdown -->
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <label for="platforms-sync-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            <i class="fa-solid fa-filter text-primary me-0.5"></i>
                            Capability:
                        </label>
                        <select class="form-select form-select-sm rounded-3" id="platforms-sync-select"
                            onchange="applyPlatformsFilters(true)">
                            <option value="all" selected>
                                All Sync Capabilities
                            </option>
                            <option value="full">
                                Full OAuth Sync (Rating + Solved)
                            </option>
                            <option value="rating">
                                Rating & Contest Performance
                            </option>
                            <option value="solved">
                                Solved Archive & Badges
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Connection Status Filter -->
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <label for="platforms-status-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            Status:
                        </label>
                        <select class="form-select form-select-sm rounded-3" id="platforms-status-select"
                            onchange="applyPlatformsFilters(true)">
                            <option value="all" selected>
                                All Connection Statuses
                            </option>
                            <option value="connected">
                                Connected Handles
                            </option>
                            <option value="available">
                                Available to Link
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Platforms SaaS Table Card (Fixed Table Card - Layout identical to Contests & Problems) -->
        <div class="card panel border-0 p-0 mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0 text-nowrap" id="platforms-directory-table">
                    <thead class="table-light extra-small uppercase font-monospace border-bottom">
                        <tr>
                            <th class="ps-4" style="width: 260px">
                                Platform & Domain
                            </th>
                            <th>Category</th>
                            <th>Sync Engine</th>
                            <th>Features Tracked</th>
                            <th>Connected Users</th>
                            <th>Connection Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small" id="platforms-table-tbody">
                        <!-- Row 1: Codeforces -->
                        <tr data-id="codeforces" data-name="Codeforces" data-code="cf" data-category="global"
                            data-sync="full" data-status="connected" data-users="42500">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box cf p-2 rounded-2 border">
                                        <i class="fa-solid fa-code text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="{{ route('platforms.show', 'codeforces') }}"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                Codeforces
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            codeforces.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small">Global
                                    Judge</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-circle-check me-1"></i>
                                    Full OAuth Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Ratings, Contests,
                                    Submissions</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">42,500</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-link me-1"></i>
                                    Connected (@tourist)</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('platforms.show', 'codeforces') }}"
                                    class="btn btn-xs btn-primary fw-semibold rounded-2 px-3 me-1">
                                    <i class="fa-solid fa-chart-pie me-1"></i>
                                    Platform Hub
                                </a>
                                <button class="btn btn-xs btn-outline-secondary fw-semibold rounded-2 px-2"
                                    onclick="
                                            openPlatformDetailModal(
                                                'codeforces',
                                            )
                                        "
                                    title="Manage Link">
                                    <i class="fa-solid fa-gear"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2: LeetCode -->
                        <tr data-id="leetcode" data-name="LeetCode" data-code="lc" data-category="global"
                            data-sync="full" data-status="connected" data-users="38200">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box lc p-2 rounded-2 border">
                                        <i class="fa-solid fa-terminal text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://leetcode.com" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                LeetCode
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            leetcode.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small">Global
                                    Judge</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-bolt me-1"></i>
                                    Live GraphQL API</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Submissions, Streaks, Badges</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">38,200</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-link me-1"></i>
                                    Connected (@tourist)</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('leetcode')
                                        ">
                                    Manage Link
                                </button>
                            </td>
                        </tr>

                        <!-- Row 3: AtCoder -->
                        <tr data-id="atcoder" data-name="AtCoder" data-code="ac" data-category="global"
                            data-sync="full" data-status="connected" data-users="28900">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box ac p-2 rounded-2 border">
                                        <i class="fa-solid fa-bolt text-info fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://atcoder.jp" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                AtCoder
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            atcoder.jp
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small">Global
                                    Judge</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-circle-check me-1"></i>
                                    Full Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">ABC/ARC/AGC Performance</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">28,900</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-link me-1"></i>
                                    Connected (@tourist)</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('atcoder')
                                        ">
                                    Manage Link
                                </button>
                            </td>
                        </tr>

                        <!-- Row 4: CodeChef -->
                        <tr data-id="codechef" data-name="CodeChef" data-code="cc" data-category="global"
                            data-sync="full" data-status="connected" data-users="24100">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box cc p-2 rounded-2 border">
                                        <i class="fa-solid fa-utensils text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://codechef.com" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                CodeChef
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            codechef.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small">Global
                                    Judge</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-circle-check me-1"></i>
                                    Rating Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Starters & Stars Rating</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">24,100</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-link me-1"></i>
                                    Connected (@tourist)</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('codechef')
                                        ">
                                    Manage Link
                                </button>
                            </td>
                        </tr>

                        <!-- Row 5: HackerRank -->
                        <tr data-id="hackerrank" data-name="HackerRank" data-code="hr" data-category="global"
                            data-sync="solved" data-status="available" data-users="19400">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box hr p-2 rounded-2 border">
                                        <i class="fa-brands fa-hackerrank text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://hackerrank.com" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                HackerRank
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            hackerrank.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small">Global
                                    Judge</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-info-subtle text-info border border-info-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-certificate me-1"></i>
                                    Badge Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Domain Stars & Certificates</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">19,400</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal(
                                                'hackerrank',
                                            )
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 6: CSES Problem Set -->
                        <tr data-id="cses" data-name="CSES Problem Set" data-code="cses" data-category="olympiad"
                            data-sync="solved" data-status="available" data-users="18200">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box cses p-2 rounded-2 border">
                                        <i class="fa-solid fa-graduation-cap text-purple fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://cses.fi" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                CSES Problem Set
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            cses.fi
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-2 extra-small">Olympiad
                                    & Regional</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-check-double me-1"></i>
                                    Solved Counter</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">300 Classic Olympiad Tasks</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">18,200</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('cses')
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 7: SPOJ -->
                        <tr data-id="spoj" data-name="SPOJ" data-code="spoj" data-category="olympiad"
                            data-sync="solved" data-status="available" data-users="15600">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box spoj p-2 rounded-2 border">
                                        <i class="fa-solid fa-globe text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://spoj.com" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                SPOJ (Sphere Online Judge)
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            spoj.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-2 extra-small">Olympiad
                                    & Regional</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-hashtag me-1"></i>
                                    Score Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">20k Problem Archive & Points</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">15,600</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('spoj')
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 8: Project Euler -->
                        <tr data-id="pe" data-name="Project Euler" data-code="pe" data-category="specialized"
                            data-sync="solved" data-status="available" data-users="12800">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box pe p-2 rounded-2 border">
                                        <i class="fa-solid fa-calculator text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://projecteuler.net" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                Project Euler
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            projecteuler.net
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-2 extra-small">Specialized
                                    & AI</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-layer-group me-1"></i>
                                    Level Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Mathematical Algorithm Tasks</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">12,800</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="openPlatformDetailModal('pe')">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 9: USACO -->
                        <tr data-id="usaco" data-name="USACO" data-code="usaco" data-category="olympiad"
                            data-sync="rating" data-status="available" data-users="11400">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box usaco p-2 rounded-2 border">
                                        <i class="fa-solid fa-flag-usa text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://usaco.org" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                USACO
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            usaco.org
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-2 extra-small">Olympiad
                                    & Regional</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-shield-cat me-1"></i>
                                    Division Sync</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Bronze, Silver, Gold,
                                    Platinum</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">11,400</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('usaco')
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 10: Kattis -->
                        <tr data-id="kattis" data-name="Kattis" data-code="kattis" data-category="olympiad"
                            data-sync="solved" data-status="available" data-users="9800">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box kattis p-2 rounded-2 border">
                                        <i class="fa-solid fa-cat text-info fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://open.kattis.com" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                Kattis
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            open.kattis.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-2 extra-small">Olympiad
                                    & Regional</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-trophy me-1"></i>
                                    Score & Rank</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">ICPC & University Contests</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">9,800</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('kattis')
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 11: TopCoder -->
                        <tr data-id="topcoder" data-name="TopCoder" data-code="tc" data-category="global"
                            data-sync="rating" data-status="available" data-users="8700">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box tc p-2 rounded-2 border">
                                        <i class="fa-solid fa-trophy text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://topcoder.com" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                TopCoder
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            topcoder.com
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small">Global
                                    Judge</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-chart-line me-1"></i>
                                    SRM Rating</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">SRM Contests & Rating
                                    History</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">8,700</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('topcoder')
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>

                        <!-- Row 12: Beecrowd -->
                        <tr data-id="beecrowd" data-name="Beecrowd (URI)" data-code="beecrowd" data-category="olympiad"
                            data-sync="solved" data-status="available" data-users="8100">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="platform-avatar-box beecrowd p-2 rounded-2 border">
                                        <i class="fa-solid fa-bug text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            <a href="https://beecrowd.io" target="_blank"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">
                                                Beecrowd (URI)
                                            </a>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            beecrowd.io
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-2 extra-small">Olympiad
                                    & Regional</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-check me-1"></i>
                                    Solved Counter</span>
                            </td>
                            <td>
                                <span class="text-secondary extra-small font-monospace">Latin American Judge &
                                    Levels</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">8,100</span>
                            </td>
                            <td>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                        class="fa-solid fa-plus me-1"></i>
                                    Not Linked</span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3"
                                    onclick="
                                            openPlatformDetailModal('beecrowd')
                                        ">
                                    Connect Handle
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Footer -->
            <div
                class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 border-top bg-body-tertiary">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted extra-small" id="platforms-pagination-info">
                        Showing 1-10 of 12 platforms
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted extra-small">Per page:</span>
                        <select class="form-select form-select-sm rounded-2 py-0 px-2 extra-small"
                            style="width: auto; height: 28px" onchange="changePlatformsPageSize(this.value)">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <nav aria-label="Platforms table navigation">
                    <ul class="pagination pagination-sm mb-0 gap-1" id="platforms-pagination-controls">
                        <!-- controls rendered dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <!-- ================= INTERACTIVE CONNECT HANDLE MODAL ================= -->
    <div class="modal fade" id="platformDetailModal" tabindex="-1" aria-labelledby="platformDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px">
                <div class="modal-header border-bottom p-3.5">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="platform-avatar-box p-2 rounded-2 border" id="modal-platform-avatar">
                            <i class="fa-solid fa-code text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-primary-emphasis mb-0" id="modal-platform-name">
                                Connect Codeforces
                            </h5>
                            <div class="extra-small text-muted font-monospace" id="modal-platform-domain">
                                codeforces.com
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="connect-platform-form"
                        onsubmit="
                                event.preventDefault();
                                savePlatformHandle();
                            ">
                        <div class="mb-3">
                            <label for="platform-handle-input"
                                class="form-label text-primary-emphasis fw-semibold extra-small font-monospace uppercase">
                                Platform Username / Handle:
                            </label>
                            <div class="input-group">
                                <span class="input-group-text font-monospace extra-small text-muted">@</span>
                                <input type="text" class="form-control rounded-end-3" id="platform-handle-input"
                                    placeholder="e.g. tourist" required />
                            </div>
                            <div class="form-text extra-small text-muted mt-1">
                                Enter your public handle on this platform.
                                We'll automatically verify and synchronize
                                ratings & submissions.
                            </div>
                        </div>

                        <div class="card panel border p-3 rounded-3 mb-3" style="background: var(--surface-2)">
                            <div class="extra-small font-monospace text-muted uppercase fw-semibold mb-2">
                                Supported Sync Features:
                            </div>
                            <div class="row g-2 extra-small">
                                <div class="col-6 text-success">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Contest Rating Chart
                                </div>
                                <div class="col-6 text-success">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Solved Problems
                                </div>
                                <div class="col-6 text-success">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Rank & Badges
                                </div>
                                <div class="col-6 text-success">
                                    <i class="fa-solid fa-check me-1"></i>
                                    Submission Log
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 pt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary rounded-3 px-4 fw-semibold"
                                id="modal-save-btn">
                                <i class="fa-solid fa-link me-1"></i> Link
                                Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
