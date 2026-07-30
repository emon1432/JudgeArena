@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Action Row -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <nav class="breadcrumb-list mb-1" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <span class="current">Global Rankings</span>
                </nav>
                <h1 class="h3 fw-extrabold text-primary-emphasis mb-0 tracking-tight">
                    Unified Global Programmers Rankings
                </h1>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button
                    class="btn btn-sm btn-outline-secondary fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                    id="sync-rankings-btn" onclick="syncRankingsData()">
                    <i class="fa-solid fa-rotate text-primary" id="sync-rankings-icon"></i>
                    <span id="sync-rankings-text">Sync Rankings</span>
                </button>
            </div>
        </div>

        <!-- Key Metrics Summary Row (KPI Cards) -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Global Rank #1 -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">World Rank #1</span>
                        <i class="fa-solid fa-trophy text-warning fs-5"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        tourist
                    </div>
                    <div class="extra-small text-muted mt-1">
                        3,850 Peak Rating (LGM)
                    </div>
                </div>
            </div>

            <!-- Card 2: Tracked Competitors -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Tracked Coders</span>
                        <i class="fa-solid fa-users text-primary"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        85,400+
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Active verified profiles
                    </div>
                </div>
            </div>

            <!-- Card 3: LGM & Guardian Tiers -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">LGM & Guardians</span>
                        <i class="fa-solid fa-award text-purple"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        142 Coders
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Rating &gt; 3,000 global tier
                    </div>
                </div>
            </div>

            <!-- Card 4: Most Solved Record -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Highest Solved</span>
                        <i class="fa-solid fa-fire text-danger"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        12,480 Solved
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Across 6 connected judges
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Rankings Directory Toolbar (Search, Category Pills, Custom Dropdowns) -->
        <div class="card panel border-0 p-3 mb-4" style="border-radius: 16px">
            <!-- Top Row: Search + Category Pills + Sort -->
            <div
                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <!-- Search Input -->
                <div class="position-relative flex-grow-1" style="max-width: 380px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" id="rankings-directory-search" class="form-control ps-5 pe-4 rounded-3"
                        placeholder="Search programmer name, handle, country, university..."
                        onkeyup="applyRankingsFilters(true)" />
                </div>

                <!-- Category Nav Pills -->
                <div class="nav nav-pills saas-filter-pills gap-1 flex-wrap" id="rankings-category-pills">
                    <button class="nav-link active platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterRankingsByCategory('all', this)">
                        All (12)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterRankingsByCategory('lgm', this)">
                        <i class="fa-solid fa-trophy text-warning me-1 extra-small"></i>
                        LGM & Guardians (6)
                    </button>
                    <button class="nav-link platform-filter-pill px-3 py-1-5 extra-small"
                        onclick="filterRankingsByCategory('regional', this)">
                        <i class="fa-solid fa-globe text-primary me-1 extra-small"></i>
                        Country Rankings (6)
                    </button>
                </div>

                <!-- Sort Dropdown -->
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm rounded-3" style="width: auto; min-width: 180px"
                        id="rankings-sort-select" onchange="sortRankingsList(this.value)">
                        <option value="rank-asc" selected>
                            Unified Rank (#1 to #N)
                        </option>
                        <option value="cf-desc">
                            Codeforces Rating (High to Low)
                        </option>
                        <option value="lc-desc">
                            LeetCode Rating (High to Low)
                        </option>
                        <option value="solved-desc">
                            Total Solved (High to Low)
                        </option>
                    </select>
                </div>
            </div>

            <!-- Bottom Row: Platform Filter & Country Dropdown -->
            <div class="row g-3 align-items-center">
                <!-- Platform Filter Dropdown -->
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <label for="rankings-platform-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            <i class="fa-solid fa-filter text-primary me-0.5"></i>
                            Platform:
                        </label>
                        <select class="form-select form-select-sm rounded-3" id="rankings-platform-select"
                            onchange="applyRankingsFilters(true)">
                            <option value="all" selected>
                                All Integrated Judges
                            </option>
                            <option value="cf">Codeforces Rating</option>
                            <option value="lc">LeetCode Rating</option>
                            <option value="ac">AtCoder Rating</option>
                        </select>
                    </div>
                </div>

                <!-- Country Filter Dropdown -->
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <label for="rankings-country-select"
                            class="text-muted extra-small font-monospace uppercase fw-semibold mb-0 text-nowrap">
                            Country:
                        </label>
                        <select class="form-select form-select-sm rounded-3" id="rankings-country-select"
                            onchange="applyRankingsFilters(true)">
                            <option value="all" selected>
                                All Countries & Regions
                            </option>
                            <option value="Belarus">Belarus</option>
                            <option value="China">China</option>
                            <option value="United States">
                                United States
                            </option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Japan">Japan</option>
                            <option value="India">India</option>
                            <option value="Ukraine">Ukraine</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Rankings SaaS Table Card (Fixed Table Card) -->
        <div class="card panel border-0 p-0 mb-4 shadow-sm" style="border-radius: 16px; overflow: hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0 text-nowrap" id="rankings-directory-table">
                    <thead class="table-light extra-small uppercase font-monospace border-bottom">
                        <tr>
                            <th class="ps-4" style="width: 80px">Rank</th>
                            <th style="min-width: 240px">
                                Programmer & Handle
                            </th>
                            <th style="min-width: 200px">
                                Country & Institute
                            </th>
                            <th>Unified Title & Peak</th>
                            <th>CF Rating</th>
                            <th>LC Rating</th>
                            <th>AC Rating</th>
                            <th>Total Solved</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small" id="rankings-table-tbody">
                        <!-- Rank #1: tourist -->
                        <tr data-rank="1" data-name="Gennady Korotkevich" data-handle="tourist" data-country="Belarus"
                            data-category="lgm" data-tier="lgm" data-cf="3850" data-lc="3450" data-ac="3800"
                            data-solved="12480">
                            <td class="ps-4">
                                <span class="badge bg-warning text-dark font-monospace fw-bold px-2 py-1 rounded-2">
                                    <i class="fa-solid fa-trophy me-1"></i>
                                    #1
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <img src="{{ asset('web/img/khairul-islam-emon.jpg') }}" alt="tourist avatar"
                                        class="rounded-circle border"
                                        style="
                                                width: 38px;
                                                height: 38px;
                                                object-fit: cover;
                                            " />
                                    <div>
                                        <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-1">
                                            <a href="profile.html"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">Gennady
                                                Korotkevich</a>
                                            <i class="fa-solid fa-circle-check text-primary extra-small"
                                                title="Verified Champion"></i>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @tourist
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇧🇾 Belarus
                                </div>
                                <div class="extra-small text-muted">
                                    ITMO University
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3850)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,850</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,450</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,800</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">12,480</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #2: Petr -->
                        <tr data-rank="2" data-name="Petr Mitrichev" data-handle="Petr" data-country="Switzerland"
                            data-category="lgm" data-tier="lgm" data-cf="3620" data-lc="3200" data-ac="3500"
                            data-solved="9840">
                            <td class="ps-4">
                                <span class="badge bg-secondary text-white font-monospace fw-bold px-2 py-1 rounded-2">
                                    <i class="fa-solid fa-medal me-1"></i>
                                    #2
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        PM
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Petr Mitrichev
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @Petr
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇨🇭 Switzerland
                                </div>
                                <div class="extra-small text-muted">
                                    Yandex
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3620)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,620</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,200</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,500</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">9,840</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #3: Benq -->
                        <tr data-rank="3" data-name="Benjamin Qi" data-handle="Benq" data-country="United States"
                            data-category="lgm" data-tier="lgm" data-cf="3780" data-lc="3380" data-ac="3720"
                            data-solved="8920">
                            <td class="ps-4">
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace fw-bold px-2 py-1 rounded-2">
                                    <i class="fa-solid fa-award me-1"></i>
                                    #3
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-purple-subtle text-purple fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        BQ
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Benjamin Qi
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @Benq
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇺🇸 United States
                                </div>
                                <div class="extra-small text-muted">
                                    MIT
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3780)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,780</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,380</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,720</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">8,920</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #4: jiangly -->
                        <tr data-rank="4" data-name="Lingyu Jiang" data-handle="jiangly" data-country="China"
                            data-category="lgm" data-tier="lgm" data-cf="3760" data-lc="3410" data-ac="3750"
                            data-solved="10150">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#4</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-danger-subtle text-danger fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        LJ
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Lingyu Jiang
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @jiangly
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇨🇳 China
                                </div>
                                <div class="extra-small text-muted">
                                    Tsinghua University
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3760)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,760</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,410</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,750</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">10,150</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #5: Um_nik -->
                        <tr data-rank="5" data-name="Alexey Danilyuk" data-handle="Um_nik" data-country="Cyprus"
                            data-category="lgm" data-tier="lgm" data-cf="3510" data-lc="3150" data-ac="3480"
                            data-solved="11200">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#5</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-info-subtle text-info fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        AD
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Alexey Danilyuk
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @Um_nik
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇨🇾 Cyprus
                                </div>
                                <div class="extra-small text-muted">
                                    ITMO University
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3510)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,510</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,150</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,480</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">11,200</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #6: ecnerwala -->
                        <tr data-rank="6" data-name="Eric Zhang" data-handle="ecnerwala" data-country="United States"
                            data-category="lgm" data-tier="lgm" data-cf="3640" data-lc="3320" data-ac="3610"
                            data-solved="7850">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#6</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-success-subtle text-success fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        EZ
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Eric Zhang
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @ecnerwala
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇺🇸 United States
                                </div>
                                <div class="extra-small text-muted">
                                    Harvard University
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3640)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,640</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,320</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,610</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">7,850</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #7: emonideas -->
                        <tr data-rank="7" data-name="Khairul Islam Emon" data-handle="emonideas"
                            data-country="Bangladesh" data-category="regional" data-tier="cm" data-cf="1964"
                            data-lc="2210" data-ac="1850" data-solved="2840">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#7</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <img src="{{ asset('web/img/khairul-islam-emon.jpg') }}" alt="emonideas avatar"
                                        class="rounded-circle border"
                                        style="
                                                width: 38px;
                                                height: 38px;
                                                object-fit: cover;
                                            " />
                                    <div>
                                        <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-1">
                                            <a href="profile.html"
                                                class="problem-title-link text-primary-emphasis text-decoration-none">Khairul
                                                Islam Emon</a>
                                            <i class="fa-solid fa-circle-check text-primary extra-small"></i>
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @emonideas
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇧🇩 Bangladesh
                                </div>
                                <div class="extra-small text-muted">
                                    University of Dhaka
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-2 extra-small fw-semibold">
                                    Candidate Master (1964)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-purple font-monospace">1,964</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">2,210</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">1,850</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">2,840</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #8: neal -->
                        <tr data-rank="8" data-name="Neal Wu" data-handle="neal" data-country="United States"
                            data-category="regional" data-tier="igm" data-cf="2940" data-lc="3280" data-ac="2890"
                            data-solved="6420">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#8</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-warning-subtle text-warning fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        NW
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Neal Wu
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @neal
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇺🇸 United States
                                </div>
                                <div class="extra-small text-muted">
                                    Meta
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    International Grandmaster (2940)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">2,940</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,280</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">2,890</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">6,420</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #9: ksun48 -->
                        <tr data-rank="9" data-name="Kevin Sun" data-handle="ksun48" data-country="Canada"
                            data-category="lgm" data-tier="lgm" data-cf="3480" data-lc="3190" data-ac="3420"
                            data-solved="8100">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#9</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-secondary-subtle text-secondary fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        KS
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Kevin Sun
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @ksun48
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇨🇦 Canada
                                </div>
                                <div class="extra-small text-muted">
                                    MIT
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Legendary Grandmaster (3480)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">3,480</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">3,190</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,420</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">8,100</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #10: chokudai -->
                        <tr data-rank="10" data-name="Kazuhiro Hosaka" data-handle="chokudai" data-country="Japan"
                            data-category="regional" data-tier="gm" data-cf="2890" data-lc="2750" data-ac="3100"
                            data-solved="5640">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#10</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-info-subtle text-info fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        KH
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Kazuhiro Hosaka
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @chokudai
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇯🇵 Japan
                                </div>
                                <div class="extra-small text-muted">
                                    AtCoder Inc
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-2 extra-small fw-semibold">
                                    Grandmaster (2890)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger font-monospace">2,890</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">2,750</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">3,100</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">5,640</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #11: anadi -->
                        <tr data-rank="11" data-name="Anadi Agrawal" data-handle="anadi" data-country="India"
                            data-category="regional" data-tier="im" data-cf="2420" data-lc="2680" data-ac="2310"
                            data-solved="4120">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#11</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-warning-subtle text-warning fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        AA
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Anadi Agrawal
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @anadi
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇮🇳 India
                                </div>
                                <div class="extra-small text-muted">
                                    IIT Delhi
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-2 extra-small fw-semibold">
                                    International Master (2420)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">2,420</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">2,680</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">2,310</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">4,120</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>

                        <!-- Rank #12: pastuschak -->
                        <tr data-rank="12" data-name="Bohdan Pastuschak" data-handle="pastuschak" data-country="Ukraine"
                            data-category="regional" data-tier="m" data-cf="2280" data-lc="2510" data-ac="2190"
                            data-solved="3750">
                            <td class="ps-4">
                                <span class="fw-bold text-muted font-monospace">#12</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center border"
                                        style="width: 38px; height: 38px">
                                        BP
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary-emphasis">
                                            Bohdan Pastuschak
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            @pastuschak
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-primary-emphasis">
                                    🇺🇦 Ukraine
                                </div>
                                <div class="extra-small text-muted">
                                    Lviv University
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 extra-small fw-semibold">
                                    Master (2280)
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary font-monospace">2,280</span>
                            </td>
                            <td>
                                <span class="fw-bold text-warning font-monospace">2,510</span>
                            </td>
                            <td>
                                <span class="fw-bold text-info font-monospace">2,190</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary-emphasis font-monospace">3,750</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="profile.html" class="btn btn-xs btn-outline-primary fw-semibold rounded-2 px-3">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Footer -->
            <div
                class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 border-top bg-body-tertiary">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted extra-small" id="rankings-pagination-info">
                        Showing 1-10 of 12 programmers
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted extra-small">Per page:</span>
                        <select class="form-select form-select-sm rounded-2 extra-small" id="rankings-per-page-select"
                            style="width: 75px" onchange="changeRankingsPageSize(this.value)">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <nav aria-label="Rankings table navigation">
                    <ul class="pagination pagination-sm mb-0 gap-1" id="rankings-pagination-controls">
                        <!-- controls rendered dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </main>
@endsection
