@extends('web.layouts.app')
@section('content')
    <!-- MAIN CONTESTS CONTENT HUB -->
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Action Row -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav class="breadcrumb-list mb-1" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <span class="current">Global Contests Hub</span>
                </nav>
                <h1 class="h3 fw-extrabold text-primary-emphasis mb-0 tracking-tight">
                    Competitive Programming Contests
                </h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5"
                    data-bs-toggle="modal" data-bs-target="#exportCalendarModal">
                    <i class="fa-regular fa-calendar-plus text-primary"></i>
                    Sync Calendar (.ics)
                </button>
                <button class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#contestReminderModal">
                    <i class="fa-regular fa-bell"></i> Set Contest Alert
                </button>
            </div>
        </div>

        <!-- Key Metrics Summary Row -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Live Contests Running -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Live Contests</span>
                        <span class="badge-live-pulse rounded-pill px-2 py-0-5 extra-small">
                            <span class="pulse-dot"></span> LIVE
                        </span>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        2 Running
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Codeforces & LeetCode active
                    </div>
                </div>
            </div>

            <!-- Card 2: Starting in 24 Hours -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Next 24 Hours</span>
                        <i class="fa-regular fa-clock text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        5 Upcoming
                    </div>
                    <div class="extra-small text-muted mt-1">
                        AtCoder, CodeChef & more
                    </div>
                </div>
            </div>

            <!-- Card 3: Tracked Platforms -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Tracked Judges</span>
                        <i class="fa-solid fa-globe text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        100+ Judges
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Auto-synced every 15 mins
                    </div>
                </div>
            </div>

            <!-- Card 4: Bookmarked Contests -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">My Reminders</span>
                        <i class="fa-solid fa-star text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0" id="bookmarked-count-num">
                        3 Saved
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Instant browser alerts set
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Live Now Banner (Featured) -->
        <div class="card panel border-0 p-4 mb-4"
            style="
                    border-radius: 18px;
                    background: linear-gradient(
                        135deg,
                        rgba(239, 68, 68, 0.06) 0%,
                        rgba(59, 130, 246, 0.06) 100%
                    );
                    border: 1px solid rgba(239, 68, 68, 0.2) !important;
                ">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 48px; height: 48px">
                        <i class="fa-solid fa-code fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge-live-pulse rounded-pill px-2.5 py-1 extra-small">
                                <span class="pulse-dot"></span> LIVE CONTEST
                                NOW
                            </span>
                            <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                Codeforces</span>
                            <span class="badge bg-purple-subtle text-purple extra-small">Division 2</span>
                        </div>
                        <h2 class="h5 fw-bold text-primary-emphasis mb-1">
                            Codeforces Round 955 (Div. 2)
                        </h2>
                        <div class="extra-small text-muted d-flex align-items-center gap-3 flex-wrap">
                            <span><i class="fa-regular fa-user me-1"></i>
                                18,420 Contestants</span>
                            <span><i class="fa-regular fa-clock me-1"></i>
                                Duration: 2 Hours</span>
                            <span><i class="fa-solid fa-trophy me-1 text-warning"></i>
                                Rated for Div. 2</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                    <div class="countdown-box">
                        <span class="extra-small text-muted text-uppercase me-1">Ends In</span>
                        <span class="text-danger fw-bold fs-6" id="live-contest-timer-1">01:24:10</span>
                    </div>
                    <a href="https://codeforces.com/contests" target="_blank"
                        class="btn btn-danger fw-semibold px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket"></i> Enter
                        Contest
                    </a>
                </div>
            </div>
        </div>

        <!-- Section 2: Contests Directory Toolbar (Search, Filter Tabs, Platform Pills & Sort) -->
        <div class="card panel border-0 p-3 mb-4" style="border-radius: 16px">
            <!-- Top Row: Search + Status Tabs + Sort -->
            <div
                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <!-- Search Input -->
                <div class="position-relative flex-grow-1" style="max-width: 380px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" id="contest-directory-search" class="form-control ps-5 pe-4 rounded-3"
                        placeholder="Search contest title, round, platform..." onkeyup="filterContestsDirectory()" />
                </div>

                <!-- Status Filter Nav Pills -->
                <div class="nav nav-pills saas-filter-pills gap-1 flex-wrap">
                    <button class="nav-link active platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('all', this)">
                        All Contests (12)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('live', this)">
                        <i class="fa-solid fa-circle text-danger extra-small me-1"></i>
                        Live (2)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('upcoming', this)">
                        <i class="fa-regular fa-clock text-warning me-1"></i>
                        Upcoming (7)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('bookmarked', this)">
                        <i class="fa-solid fa-star text-warning me-1"></i>
                        Bookmarked (3)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterByStatus('past', this)">
                        <i class="fa-solid fa-box-archive text-muted me-1"></i>
                        Past Contests
                    </button>
                </div>

                <!-- Sort & Timezone Dropdown -->
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm rounded-3" style="width: auto; min-width: 170px"
                        onchange="sortContestsList(this.value)">
                        <option value="soonest" selected>
                            Start Time (Soonest)
                        </option>
                        <option value="duration">
                            Duration (Shortest)
                        </option>
                        <option value="popular">
                            Popularity (Most Registered)
                        </option>
                        <option value="platform">Platform A-Z</option>
                    </select>
                </div>
            </div>

            <!-- Bottom Row: Platform Filter Searchable Select Dropdown (100+ Platforms) -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <label for="contests-platform-select"
                    class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 d-flex align-items-center gap-1">
                    <i class="fa-solid fa-filter text-primary"></i> Filter
                    Platform:
                </label>
                <div style="min-width: 260px; max-width: 340px" class="flex-grow-1 flex-md-grow-0">
                    <select class="form-select form-select-sm rounded-3" id="contests-platform-select"
                        onchange="filterByPlatformDropdown(this.value)">
                        <option value="all" selected>
                            All Platforms (100+ Supported)
                        </option>
                        <option value="Codeforces">Codeforces (CF)</option>
                        <option value="LeetCode">LeetCode (LC)</option>
                        <option value="AtCoder">AtCoder (AC)</option>
                        <option value="CodeChef">CodeChef (CC)</option>
                        <option value="HackerRank">HackerRank (HR)</option>
                        <option value="Kaggle">Kaggle (KG)</option>
                        <option value="SPOJ">SPOJ</option>
                        <option value="CSES">CSES Problemset</option>
                        <option value="Kattis">Kattis</option>
                        <option value="LightOJ">LightOJ</option>
                        <option value="HackerEarth">HackerEarth</option>
                        <option value="VJudge">VJudge</option>
                        <option value="TopCoder">TopCoder</option>
                        <option value="DMOJ">DMOJ</option>
                        <option value="Project Euler">Project Euler</option>
                    </select>
                </div>
                <span class="text-muted extra-small ms-auto d-none d-md-inline-block"><i
                        class="fa-solid fa-circle-info me-1 text-primary"></i>
                    Select any platform from 100+ online judges.</span>
            </div>
        </div>

        <!-- Section 3: Contests Directory Table (Fixed Card without hover lift) -->
        <div class="card panel border-0 p-4 mb-4 fixed-card" style="border-radius: 16px">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="contests-directory-table">
                    <thead class="table-light extra-small text-uppercase font-monospace text-muted">
                        <tr>
                            <th scope="col" style="min-width: 170px">
                                Platform
                            </th>
                            <th scope="col" style="min-width: 260px">
                                Contest Title & Round
                            </th>
                            <th scope="col" style="min-width: 170px">
                                Start Date & Time
                            </th>
                            <th scope="col" style="min-width: 110px">
                                Duration
                            </th>
                            <th scope="col" style="min-width: 160px">
                                Countdown / Phase
                            </th>
                            <th scope="col" class="text-end" style="min-width: 140px">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="small" id="contests-table-tbody">
                        <!-- Contest Row 1: Codeforces Live -->
                        <tr data-status="live" data-platform="Codeforces" data-bookmarked="true">
                            <td>
                                <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                    Codeforces</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-2">
                                    Codeforces Round 955 (Div. 2)
                                    <span class="badge-live-pulse rounded-pill px-2 py-0-5 extra-small"><span
                                            class="pulse-dot"></span>
                                        LIVE</span>
                                </div>
                                <div class="extra-small text-muted">
                                    Rated for Div. 2 (Rating &lt; 1900)
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Today, 08:00 PM
                                </div>
                                <div class="extra-small text-muted font-monospace">
                                    UTC+06:00 (Dhaka)
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium font-monospace">2h 00m</span>
                            </td>
                            <td>
                                <span class="text-danger fw-bold font-monospace extra-small">Ends in 01h 24m</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn"
                                        title="Remove Bookmark" onclick="toggleBookmark(this)">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <a href="https://codeforces.com/contests" target="_blank"
                                        class="btn btn-sm btn-danger px-3 py-1-5 fw-semibold rounded-2">
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        Enter
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Contest Row 2: LeetCode Live -->
                        <tr data-status="live" data-platform="LeetCode" data-bookmarked="false">
                            <td>
                                <span class="platform-tag lc"><i class="fa-solid fa-terminal"></i>
                                    LeetCode</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-2">
                                    LeetCode Weekly Contest 405
                                    <span class="badge-live-pulse rounded-pill px-2 py-0-5 extra-small"><span
                                            class="pulse-dot"></span>
                                        LIVE</span>
                                </div>
                                <div class="extra-small text-muted">
                                    4 Algorithmic Problems
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Today, 08:30 AM
                                </div>
                                <div class="extra-small text-muted font-monospace">
                                    UTC+06:00 (Dhaka)
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium font-monospace">1h 30m</span>
                            </td>
                            <td>
                                <span class="text-danger fw-bold font-monospace extra-small">Ends in 00h 42m</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        title="Bookmark Contest" onclick="toggleBookmark(this)">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <a href="https://leetcode.com/contest" target="_blank"
                                        class="btn btn-sm btn-warning text-dark px-3 py-1-5 fw-semibold rounded-2">
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        Enter
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Contest Row 3: AtCoder Upcoming -->
                        <tr data-status="upcoming" data-platform="AtCoder" data-bookmarked="true">
                            <td>
                                <span class="platform-tag ac"><i class="fa-solid fa-bolt"></i>
                                    AtCoder</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    AtCoder Beginner Contest 361 (ABC 361)
                                </div>
                                <div class="extra-small text-muted">
                                    Rated for 0 - 1999 (Cyan)
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Tomorrow, 06:00 PM
                                </div>
                                <div class="extra-small text-muted font-monospace">
                                    UTC+06:00 (Dhaka)
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium font-monospace">1h 40m</span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold font-monospace extra-small">Starts in 21h 30m</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn"
                                        title="Remove Bookmark" onclick="toggleBookmark(this)">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Add to Calendar" onclick="openCalendarFor('ABC 361')">
                                        <i class="fa-regular fa-calendar-plus"></i>
                                    </button>
                                    <a href="https://atcoder.jp/contests" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Register
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Contest Row 4: CodeChef Upcoming -->
                        <tr data-status="upcoming" data-platform="CodeChef" data-bookmarked="false">
                            <td>
                                <span class="platform-tag cc"><i class="fa-solid fa-utensils"></i>
                                    CodeChef</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    CodeChef Starters 142
                                </div>
                                <div class="extra-small text-muted">
                                    Divisions 1, 2, 3 & 4 Rated
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Wed, Jul 26, 08:30 PM
                                </div>
                                <div class="extra-small text-muted font-monospace">
                                    UTC+06:00 (Dhaka)
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium font-monospace">2h 00m</span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold font-monospace extra-small">Starts in 2d 00h</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        title="Bookmark Contest" onclick="toggleBookmark(this)">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Add to Calendar"
                                        onclick="
                                                openCalendarFor(
                                                    'CodeChef Starters 142',
                                                )
                                            ">
                                        <i class="fa-regular fa-calendar-plus"></i>
                                    </button>
                                    <a href="https://codechef.com/contests" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Register
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Contest Row 5: HackerRank Upcoming -->
                        <tr data-status="upcoming" data-platform="HackerRank" data-bookmarked="true">
                            <td>
                                <span class="platform-tag hr"><i class="fa-solid fa-h"></i>
                                    HackerRank</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    University CodeSprint 2026
                                </div>
                                <div class="extra-small text-muted">
                                    Global Academic Championship
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Fri, Jul 28, 10:00 PM
                                </div>
                                <div class="extra-small text-muted font-monospace">
                                    UTC+06:00 (Dhaka)
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium font-monospace">24h 00m</span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold font-monospace extra-small">Starts in 4d 02h</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-warning text-dark rounded-2 bookmark-btn"
                                        title="Remove Bookmark" onclick="toggleBookmark(this)">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Add to Calendar"
                                        onclick="
                                                openCalendarFor(
                                                    'HackerRank CodeSprint',
                                                )
                                            ">
                                        <i class="fa-regular fa-calendar-plus"></i>
                                    </button>
                                    <a href="https://hackerrank.com/contests" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        Register
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Contest Row 6: Kaggle Upcoming -->
                        <tr data-status="upcoming" data-platform="Kaggle" data-bookmarked="false">
                            <td>
                                <span class="platform-tag kg"><i class="fa-solid fa-k"></i>
                                    Kaggle</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary-emphasis">
                                    Kaggle Grand Prix 2026
                                </div>
                                <div class="extra-small text-muted">
                                    Machine Learning & Optimization
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    Aug 01, 12:00 AM
                                </div>
                                <div class="extra-small text-muted font-monospace">
                                    UTC+06:00 (Dhaka)
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium font-monospace">7 Days</span>
                            </td>
                            <td>
                                <span class="text-secondary fw-bold font-monospace extra-small">Starts in 7d 04h</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2 bookmark-btn"
                                        title="Bookmark Contest" onclick="toggleBookmark(this)">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                    <button class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Add to Calendar"
                                        onclick="
                                                openCalendarFor(
                                                    'Kaggle Grand Prix',
                                                )
                                            ">
                                        <i class="fa-regular fa-calendar-plus"></i>
                                    </button>
                                    <a href="https://kaggle.com/competitions" target="_blank"
                                        class="btn btn-sm btn-outline-primary px-3 py-1-5 fw-semibold rounded-2">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modern SaaS Pagination Footer Bar -->
            <div
                class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 pt-3 mt-3 border-top">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted extra-small font-monospace">Showing
                        <span class="fw-bold text-primary-emphasis">1</span>
                        to
                        <span class="fw-bold text-primary-emphasis">6</span>
                        of
                        <span class="fw-bold text-primary-emphasis">124</span>
                        Contests</span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted extra-small">Per page:</span>
                        <select class="form-select form-select-sm rounded-2 py-0 px-2 extra-small"
                            style="width: auto; height: 28px">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <nav aria-label="Contests directory pagination">
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item disabled">
                            <a class="page-link rounded-2 px-2-5" href="#" aria-label="Previous">
                                <i class="fa-solid fa-chevron-left extra-small"></i>
                            </a>
                        </li>
                        <li class="page-item active">
                            <a class="page-link rounded-2 fw-semibold px-3" href="#">1</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link rounded-2 fw-medium px-3" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link rounded-2 fw-medium px-3" href="#">3</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link border-0 bg-transparent px-1">...</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link rounded-2 fw-medium px-3" href="#">13</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link rounded-2 px-2-5" href="#" aria-label="Next">
                                <i class="fa-solid fa-chevron-right extra-small"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <!-- MODAL: Contest Reminder Setup -->
    <div class="modal fade" id="contestReminderModal" tabindex="-1" aria-labelledby="contestReminderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: var(--surface)">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-primary-emphasis d-flex align-items-center gap-2"
                            id="contestReminderModalLabel">
                            <i class="fa-regular fa-bell text-primary"></i>
                            Contest Notification Alert
                        </h5>
                        <p class="text-muted extra-small mb-0 mt-1">
                            Configure automated reminders for upcoming
                            competitive programming rounds.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="reminder-config-form" onsubmit="submitReminderConfig(event)">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Reminder Timing</label>
                            <select class="form-select form-select-sm">
                                <option value="15m" selected>
                                    15 minutes before start
                                </option>
                                <option value="30m">
                                    30 minutes before start
                                </option>
                                <option value="1h">
                                    1 hour before start
                                </option>
                                <option value="1d">
                                    1 day before start
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-primary-emphasis mb-1">Notification
                                Channels</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notify_browser" checked />
                                    <label class="form-check-label small" for="notify_browser">Browser Push
                                        Notifications</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notify_email" checked />
                                    <label class="form-check-label small" for="notify_email">Email Digest
                                        (topusers@judgearena.com)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold shadow-sm">
                                Save Preference
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Export Calendar / iCal -->
    <div class="modal fade" id="exportCalendarModal" tabindex="-1" aria-labelledby="exportCalendarModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: var(--surface)">
                <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-primary-emphasis d-flex align-items-center gap-2"
                            id="exportCalendarModalLabel">
                            <i class="fa-regular fa-calendar-plus text-primary"></i>
                            Calendar Feed Subscription
                        </h5>
                        <p class="text-muted extra-small mb-0 mt-1">
                            Sync all CP contests automatically to Google
                            Calendar, Apple Calendar or Outlook.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label font-monospace extra-small text-muted uppercase fw-semibold mb-1">iCal
                            Calendar Feed URL</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace text-primary-emphasis"
                                id="ical-feed-url" value="https://judgearena.com/contests.ics" readonly />
                            <button class="btn btn-primary fw-semibold px-3" onclick="copyIcalFeedUrl()">
                                <i class="fa-regular fa-copy me-1"></i> Copy
                                URL
                            </button>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="https://calendar.google.com" target="_blank"
                            class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-between p-2-5 rounded-3">
                            <span><i class="fa-brands fa-google text-danger me-2"></i>
                                Add to Google Calendar</span>
                            <i class="fa-solid fa-arrow-up-right-from-square extra-small text-muted"></i>
                        </a>
                        <a href="#"
                            class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-between p-2-5 rounded-3"
                            onclick="
                                    alert(
                                        'Downloading judgearena-contests.ics...',
                                    )
                                ">
                            <span><i class="fa-solid fa-download text-primary me-2"></i>
                                Download .ICS File</span>
                            <i class="fa-solid fa-file-arrow-down extra-small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
