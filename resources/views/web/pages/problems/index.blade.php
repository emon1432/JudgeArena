@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Action Row -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav class="breadcrumb-list mb-1" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <span class="current">Problems Hub</span>
                </nav>
                <h1 class="h3 fw-extrabold text-primary-emphasis mb-0 tracking-tight">
                    Unified Problems Hub
                </h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button
                    class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                    id="sync-btn" onclick="syncSubmissions()">
                    <i class="fa-solid fa-rotate text-primary" id="sync-icon"></i>
                    <span id="sync-text">Sync Submissions</span>
                </button>
            </div>
        </div>

        <!-- Key Metrics Summary Row -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Solved Problems -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Total Solved</span>
                        <i class="fa-solid fa-circle-check text-success"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        2,840
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Across all connected accounts
                    </div>
                </div>
            </div>

            <!-- Card 2: Streak Progress -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Daily Solved</span>
                        <span class="badge bg-success-subtle text-success extra-small rounded-pill px-2">Streak: 12d</span>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        5 Solved
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Daily goal met (quota: 3)
                    </div>
                </div>
            </div>

            <!-- Card 3: Connected Judges -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Connected Judges</span>
                        <i class="fa-solid fa-link text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        4 Active
                    </div>
                    <div class="extra-small text-muted mt-1">
                        CF, LC, AC, CC sync active
                    </div>
                </div>
            </div>

            <!-- Card 4: Bookmarks / Saved List -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Bookmarks</span>
                        <i class="fa-solid fa-star text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0" id="bookmarked-count-num">
                        3 Saved
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Saved for targeted revision
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Problems Hub Directory Toolbar (Search, Filter Tabs, Custom Dropdowns) -->
        <div class="card panel border-0 p-3 mb-4" style="border-radius: 16px">
            <!-- Top Row: Search + Status Tabs + Sort -->
            <div
                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <!-- Search Input -->
                <div class="position-relative flex-grow-1" style="max-width: 380px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" id="problems-directory-search" class="form-control ps-5 pe-4 rounded-3"
                        placeholder="Search problem name, code, tags..." onkeyup="applyFilters(true)" />
                </div>

                <!-- Status Filter Nav Pills -->
                <div class="nav nav-pills saas-filter-pills gap-1 flex-wrap" id="status-filter-pills">
                    <button class="nav-link active platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('all', this)">
                        All (12)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('solved', this)">
                        <i class="fa-solid fa-circle-check text-success extra-small me-1"></i>
                        Solved (8)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('attempted', this)">
                        <i class="fa-solid fa-circle-exclamation text-warning extra-small me-1"></i>
                        Attempted (2)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('unsolved', this)">
                        <i class="fa-regular fa-circle text-muted extra-small me-1"></i>
                        Unsolved (2)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('bookmarked', this)">
                        <i class="fa-solid fa-star text-warning extra-small me-1"></i>
                        Bookmarked (3)
                    </button>
                </div>

                <!-- Sort Dropdown -->
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm rounded-3" style="width: auto; min-width: 170px"
                        onchange="sortProblemsList(this.value)">
                        <option value="solved-date" selected>
                            Solved Date (Newest)
                        </option>
                        <option value="diff-asc">
                            Difficulty (Low to High)
                        </option>
                        <option value="diff-desc">
                            Difficulty (High to Low)
                        </option>
                        <option value="name-asc">Problem Name (A-Z)</option>
                    </select>
                </div>
            </div>

            <!-- Bottom Row: Platform dropdown, Difficulty dropdown, Select2 Tags dropdown -->
            <div class="row g-3 align-items-center">
                <!-- Platform Filter -->
                <div class="col-12 col-md-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="problems-platform-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            <i class="fa-solid fa-filter text-primary me-0.5"></i>
                            Platform:
                        </label>
                        <select class="form-select form-select-sm rounded-3" id="problems-platform-select"
                            onchange="applyFilters(true)">
                            <option value="all" selected>All Judges</option>
                            <option value="Codeforces">Codeforces</option>
                            <option value="LeetCode">LeetCode</option>
                            <option value="AtCoder">AtCoder</option>
                            <option value="CodeChef">CodeChef</option>
                        </select>
                    </div>
                </div>

                <!-- Difficulty Filter -->
                <div class="col-12 col-md-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="problems-difficulty-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            Difficulty:
                        </label>
                        <select class="form-select form-select-sm rounded-3" id="problems-difficulty-select"
                            onchange="applyFilters(true)">
                            <option value="all" selected>All Levels</option>
                            <option value="Easy">
                                Easy (&lt; 1200 / LC Easy)
                            </option>
                            <option value="Medium">
                                Medium (1200-1900 / LC Medium)
                            </option>
                            <option value="Hard">
                                Hard (1900+ / LC Hard)
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Category Tags Multi-select -->
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <label for="problems-tags-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            Tags:
                        </label>
                        <div class="flex-grow-1">
                            <select class="form-select form-select-sm" id="problems-tags-select" multiple="multiple">
                                <option value="graphs">Graphs</option>
                                <option value="dynamic-programming">
                                    Dynamic Programming
                                </option>
                                <option value="greedy">Greedy</option>
                                <option value="math">Math</option>
                                <option value="binary-search">
                                    Binary Search
                                </option>
                                <option value="strings">Strings</option>
                                <option value="trees">Trees</option>
                                <option value="two-pointers">
                                    Two Pointers
                                </option>
                                <option value="arrays">Arrays</option>
                                <option value="sorting">Sorting</option>
                                <option value="data-structures">
                                    Data Structures
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Problems Directory Table Card -->
        <div class="card panel border-0 p-4 mb-4 fixed-card" style="border-radius: 16px">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="problems-directory-table">
                    <thead class="table-light extra-small text-uppercase font-monospace text-muted">
                        <tr>
                            <th scope="col" style="width: 100px">Status</th>
                            <th scope="col" style="min-width: 250px">
                                Problem Name & Code
                            </th>
                            <th scope="col" style="width: 140px">
                                Platform
                            </th>
                            <th scope="col" style="width: 150px">
                                Difficulty
                            </th>
                            <th scope="col" style="min-width: 220px">
                                Category Tags
                            </th>
                            <th scope="col" style="width: 190px">
                                Last Activity
                            </th>
                            <th scope="col" class="text-end" style="width: 140px">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="small" id="problems-table-tbody">
                        <!-- Row 1: Codeforces 1931F -->
                        <tr data-id="cf-1931f" data-status="solved" data-platform="Codeforces" data-difficulty="Medium"
                            data-rating="1600" data-tags="graphs,dfs-and-bfs,sorting" data-bookmarked="false"
                            data-solved-date="2026-07-25T11:45:00Z" data-title="Programmable Robot" data-code="1931F">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://codeforces.com/problemset/problem/1931/F" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        1931F - Programmable Robot
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                    Codeforces</span>
                            </td>
                            <td>
                                <span class="badge-diff medium">Medium (1600)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">graphs</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">dfs-and-bfs</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">sorting</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Jul 25, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://codeforces.com/problemset/problem/1931/F" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 2: LeetCode 42 -->
                        <tr data-id="lc-42" data-status="solved" data-platform="LeetCode" data-difficulty="Hard"
                            data-rating="2100" data-tags="two-pointers,dynamic-programming,data-structures"
                            data-bookmarked="true" data-solved-date="2026-07-23T15:30:00Z"
                            data-title="Trapping Rain Water" data-code="42">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://leetcode.com/problems/trapping-rain-water/" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        42 - Trapping Rain Water
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag lc"><i class="fa-solid fa-terminal"></i>
                                    LeetCode</span>
                            </td>
                            <td>
                                <span class="badge-diff hard">Hard (2100)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">two-pointers</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">dynamic-programming</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">data-structures</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Jul 23, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Remove Bookmark">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <a href="https://leetcode.com/problems/trapping-rain-water/" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 3: AtCoder abc350_g -->
                        <tr data-id="ac-abc350_g" data-status="attempted" data-platform="AtCoder" data-difficulty="Hard"
                            data-rating="1950" data-tags="graphs,trees,data-structures" data-bookmarked="false"
                            data-solved-date="" data-title="Mediator" data-code="abc350_g">
                            <td>
                                <span class="badge-verdict wa"><i class="fa-solid fa-circle-xmark"></i>
                                    Attempted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://atcoder.jp/contests/abc350/tasks/abc350_g" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        abc350_g - Mediator
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag ac"><i class="fa-solid fa-bolt"></i>
                                    AtCoder</span>
                            </td>
                            <td>
                                <span class="badge-diff hard">Hard (1950)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">graphs</span>
                                    <span class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">trees</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">data-structures</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-secondary">
                                    Attempted
                                </div>
                                <div class="extra-small text-muted">
                                    3 hours ago
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://atcoder.jp/contests/abc350/tasks/abc350_g" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 4: Codeforces 189A -->
                        <tr data-id="cf-189a" data-status="solved" data-platform="Codeforces" data-difficulty="Easy"
                            data-rating="1200" data-tags="dynamic-programming,greedy" data-bookmarked="false"
                            data-solved-date="2026-06-15T09:00:00Z" data-title="Cut Ribbon" data-code="189A">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://codeforces.com/problemset/problem/189/A" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        189A - Cut Ribbon
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                    Codeforces</span>
                            </td>
                            <td>
                                <span class="badge-diff easy">Easy (1200)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">dynamic-programming</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">greedy</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Jun 15, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://codeforces.com/problemset/problem/189/A" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 5: LeetCode 1 -->
                        <tr data-id="lc-1" data-status="solved" data-platform="LeetCode" data-difficulty="Easy"
                            data-rating="900" data-tags="arrays,data-structures" data-bookmarked="false"
                            data-solved-date="2026-04-10T14:20:00Z" data-title="Two Sum" data-code="1">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://leetcode.com/problems/two-sum/" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        1 - Two Sum
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag lc"><i class="fa-solid fa-terminal"></i>
                                    LeetCode</span>
                            </td>
                            <td>
                                <span class="badge-diff easy">Easy (900)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">arrays</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">data-structures</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Apr 10, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://leetcode.com/problems/two-sum/" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 6: AtCoder abc300_a -->
                        <tr data-id="ac-abc300_a" data-status="solved" data-platform="AtCoder" data-difficulty="Easy"
                            data-rating="100" data-tags="math,sorting" data-bookmarked="false"
                            data-solved-date="2026-07-18T10:00:00Z" data-title="N-choice question" data-code="abc300_a">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://atcoder.jp/contests/abc300/tasks/abc300_a" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        abc300_a - N-choice question
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag ac"><i class="fa-solid fa-bolt"></i>
                                    AtCoder</span>
                            </td>
                            <td>
                                <span class="badge-diff easy">Easy (100)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">math</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">sorting</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Jul 18, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://atcoder.jp/contests/abc300/tasks/abc300_a" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 7: CodeChef FLOW001 -->
                        <tr data-id="cc-flow001" data-status="solved" data-platform="CodeChef" data-difficulty="Easy"
                            data-rating="250" data-tags="math" data-bookmarked="false"
                            data-solved-date="2026-01-20T08:00:00Z" data-title="Add Two Numbers" data-code="FLOW001">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://www.codechef.com/problems/FLOW001" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        FLOW001 - Add Two Numbers
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag cc"><i class="fa-solid fa-utensils"></i>
                                    CodeChef</span>
                            </td>
                            <td>
                                <span class="badge-diff easy">Easy (250)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">math</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Jan 20, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://www.codechef.com/problems/FLOW001" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 8: CodeChef SUBSET -->
                        <tr data-id="cc-subset" data-status="attempted" data-platform="CodeChef"
                            data-difficulty="Medium" data-rating="1800" data-tags="dynamic-programming,data-structures"
                            data-bookmarked="true" data-solved-date="" data-title="Subset Sum" data-code="SUBSET">
                            <td>
                                <span class="badge-verdict wa"><i class="fa-solid fa-circle-xmark"></i>
                                    Attempted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://www.codechef.com/problems/SUBSET" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        SUBSET - Subset Sum
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag cc"><i class="fa-solid fa-utensils"></i>
                                    CodeChef</span>
                            </td>
                            <td>
                                <span class="badge-diff medium">Medium (1800)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">dynamic-programming</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">data-structures</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-secondary">
                                    Attempted
                                </div>
                                <div class="extra-small text-muted">
                                    Yesterday
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Remove Bookmark">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <a href="https://www.codechef.com/problems/SUBSET" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 9: LeetCode 15 -->
                        <tr data-id="lc-15" data-status="solved" data-platform="LeetCode" data-difficulty="Medium"
                            data-rating="1500" data-tags="two-pointers,arrays,sorting" data-bookmarked="false"
                            data-solved-date="2026-07-21T16:00:00Z" data-title="3Sum" data-code="15">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://leetcode.com/problems/3sum/" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        15 - 3Sum
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag lc"><i class="fa-solid fa-terminal"></i>
                                    LeetCode</span>
                            </td>
                            <td>
                                <span class="badge-diff medium">Medium (1500)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">two-pointers</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">arrays</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">sorting</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Jul 21, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://leetcode.com/problems/3sum/" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 10: Codeforces 158A -->
                        <tr data-id="cf-158a" data-status="solved" data-platform="Codeforces" data-difficulty="Easy"
                            data-rating="800" data-tags="greedy,sorting" data-bookmarked="false"
                            data-solved-date="2026-02-14T11:00:00Z" data-title="Next Round" data-code="158A">
                            <td>
                                <span class="badge-verdict ac"><i class="fa-solid fa-circle-check"></i>
                                    Accepted</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://codeforces.com/problemset/problem/158/A" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        158A - Next Round
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                    Codeforces</span>
                            </td>
                            <td>
                                <span class="badge-diff easy">Easy (800)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">greedy</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">sorting</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Solved
                                </div>
                                <div class="extra-small text-muted">
                                    Feb 14, 2026
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://codeforces.com/problemset/problem/158/A" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 11: AtCoder arc150_b -->
                        <tr data-id="ac-arc150_b" data-status="unsolved" data-platform="AtCoder"
                            data-difficulty="Medium" data-rating="1350" data-tags="math,binary-search"
                            data-bookmarked="false" data-solved-date="" data-title="Make Them Equal"
                            data-code="arc150_b">
                            <td>
                                <span class="badge-verdict text-muted border"
                                    style="
                                            background: var(--surface-2);
                                            font-weight: 500;
                                        "><i
                                        class="fa-regular fa-circle"></i>
                                    Unsolved</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://atcoder.jp/contests/arc150/tasks/arc150_b" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        arc150_b - Make Them Equal
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag ac"><i class="fa-solid fa-bolt"></i>
                                    AtCoder</span>
                            </td>
                            <td>
                                <span class="badge-diff medium">Medium (1350)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">math</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">binary-search</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-secondary">
                                    Unsolved
                                </div>
                                <div class="extra-small text-muted">
                                    Never
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Bookmark Problem">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://atcoder.jp/contests/arc150/tasks/arc150_b" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Row 12: Codeforces 2000H -->
                        <tr data-id="cf-2000h" data-status="unsolved" data-platform="Codeforces" data-difficulty="Hard"
                            data-rating="2200" data-tags="data-structures,binary-search" data-bookmarked="true"
                            data-solved-date="" data-title="K-th Exclude" data-code="2000H">
                            <td>
                                <span class="badge-verdict text-muted border"
                                    style="
                                            background: var(--surface-2);
                                            font-weight: 500;
                                        "><i
                                        class="fa-regular fa-circle"></i>
                                    Unsolved</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    <a href="https://codeforces.com/problemset/problem/2000/H" target="_blank"
                                        class="problem-title-link text-primary-emphasis text-decoration-none">
                                        2000H - K-th Exclude
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                    Codeforces</span>
                            </td>
                            <td>
                                <span class="badge-diff hard">Hard (2200)</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">data-structures</span>
                                    <span
                                        class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">binary-search</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-secondary">
                                    Unsolved
                                </div>
                                <div class="extra-small text-muted">
                                    Never
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn"
                                        onclick="
                                                toggleProblemBookmark(this)
                                            "
                                        title="Remove Bookmark">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <a href="https://codeforces.com/problemset/problem/2000/H" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on Native Judge">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Footer -->
            <div
                class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted extra-small" id="pagination-info">
                        Showing 1-10 of 12 problems
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted extra-small">Per page:</span>
                        <select class="form-select form-select-sm rounded-2 py-0 px-2 extra-small"
                            style="width: auto; height: 28px" onchange="changePageSize(this.value)">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <nav aria-label="Problems table navigation">
                    <ul class="pagination pagination-sm mb-0 gap-1" id="pagination-controls">
                        <!-- controls rendered dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </main>
@endsection
