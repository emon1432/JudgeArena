@extends('web.layouts.app')
@section('content')
    <main class="landing-main">

        <!-- ================= 1. HERO SECTION ================= -->
        <section class="hero-section">
            <div class="hero-glow-bg"></div>
            <div class="container hero-content">
                <div class="row align-items-center g-5">
                    <!-- Left Content -->
                    <div class="col-lg-6 text-center text-lg-start">
                        <div
                            class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-body-tertiary border mb-4 shadow-sm">
                            <span
                                class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1 fs-7 fw-semibold">Platform
                                v2.4</span>
                            <span class="small text-secondary fw-medium">Universal Competitive Programming
                                Analytics</span>
                            <i class="fa-solid fa-arrow-right fs-7 text-muted ms-1"></i>
                        </div>

                        <h1 class="hero-title text-balance">
                            One Profile.<br />
                            <span class="hero-gradient-text">Every Judge.</span>
                        </h1>

                        <p class="hero-subtitle text-balance">
                            Aggregate ratings, contest history, solved
                            problems, and verified achievements from 100+
                            competitive programming platforms into a single,
                            world-class developer profile.
                        </p>

                        <div
                            class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-3 mb-4">
                            <a href="{{ route('register') }}" class="btn-hero-primary">
                                <span>Get Started</span>
                                <i class="fa-solid fa-arrow-right fs-6"></i>
                            </a>
                            <a href="profile.html" class="btn-hero-secondary">
                                <i class="fa-solid fa-circle-play text-primary"></i>
                                <span>Explore Profiles</span>
                            </a>
                        </div>

                        <div class="hero-trust-list justify-content-center justify-content-lg-start">
                            <span class="hero-trust-item"><i class="fa-solid fa-shield-halved text-success"></i>
                                No password required</span>
                            <span class="hero-trust-item"><i class="fa-solid fa-bolt text-warning"></i>
                                Auto-syncs in 30s</span>
                            <span class="hero-trust-item"><i class="fa-solid fa-circle-check text-primary"></i>
                                100% Free Forever</span>
                        </div>
                    </div>

                    <!-- Right Centerpiece Dashboard Mockup -->
                    <div class="col-lg-6">
                        <div class="mockup-browser text-start shadow-lg">
                            <div class="mockup-browser-header">
                                <div class="mockup-browser-dots">
                                    <span class="dot-red"></span>
                                    <span class="dot-yellow"></span>
                                    <span class="dot-green"></span>
                                </div>
                                <div class="mockup-browser-address">
                                    <i class="fa-solid fa-lock text-success fs-7"></i>
                                    <span>judgearena.com/u/tourist</span>
                                </div>
                                <div class="d-none d-sm-flex align-items-center gap-2 text-muted fs-7">
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle fs-8">Connected
                                        (14 Judges)</span>
                                </div>
                            </div>
                            <div class="mockup-browser-body bg-body p-3 p-md-4">
                                <!-- User Summary -->
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ asset('web/img/khairul-islam-emon.jpg') }}" alt="Profile"
                                            class="rounded-circle border" width="54" height="54" />
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                                    Gennady Korotkevich
                                                </h5>
                                                <i class="fa-solid fa-circle-check text-primary fs-7" title="Verified"></i>
                                            </div>
                                            <div class="text-secondary fs-7">
                                                @tourist • International
                                                Grandmaster
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1.5 fs-7 fw-semibold">Global
                                        Rank #1</span>
                                </div>

                                <!-- Platform Summary Cards -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6 col-sm-3">
                                        <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                            <div class="text-muted fs-8">
                                                Codeforces
                                            </div>
                                            <div class="fw-bold text-primary fs-6">
                                                3782
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                            <div class="text-muted fs-8">
                                                AtCoder
                                            </div>
                                            <div class="fw-bold text-info fs-6">
                                                4221
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                            <div class="text-muted fs-8">
                                                LeetCode
                                            </div>
                                            <div class="fw-bold text-warning fs-6">
                                                3410
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <div class="p-2.5 rounded-3 bg-body-tertiary border text-center">
                                            <div class="text-muted fs-8">
                                                Total Solved
                                            </div>
                                            <div class="fw-bold text-success fs-6">
                                                4,892
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rating Progression SVG Chart -->
                                <div class="p-3 rounded-3 bg-body-tertiary border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2 fs-7">
                                        <span class="fw-bold text-primary text-uppercase fs-8"><i
                                                class="fa-solid fa-chart-line text-primary me-1"></i>
                                            Unified Rating Trajectory</span>
                                        <span class="text-success fw-semibold fs-8">+840 Solved This Year</span>
                                    </div>
                                    <div style="height: 90px">
                                        <svg class="w-100 h-100" viewBox="0 0 400 90" preserveAspectRatio="none">
                                            <defs>
                                                <linearGradient id="heroGraphGrad2" x1="0" y1="0"
                                                    x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3" />
                                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                                                </linearGradient>
                                            </defs>
                                            <line x1="0" y1="20" x2="400" y2="20"
                                                stroke="var(--border)" stroke-dasharray="3,3" />
                                            <line x1="0" y1="60" x2="400" y2="60"
                                                stroke="var(--border)" stroke-dasharray="3,3" />
                                            <polygon points="0,85 0,50 80,40 160,45 240,25 320,30 400,10 400,85"
                                                fill="url(#heroGraphGrad2)" />
                                            <path d="M0,50 L80,40 L160,45 L240,25 L320,30 L400,10" fill="none"
                                                stroke="#3b82f6" stroke-width="2.5" />
                                            <circle cx="400" cy="10" r="4" fill="#22c55e" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Heatmap Grid + Activity Snippet -->
                                <div class="row g-2">
                                    <div class="col-7">
                                        <div class="p-2.5 rounded-3 bg-body-tertiary border h-100">
                                            <div class="text-muted fs-8 fw-semibold mb-1">
                                                365-DAY ACTIVITY HEATMAP
                                            </div>
                                            <div class="heatmap-mini-grid">
                                                <div class="heatmap-cell lvl-2"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-1"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-2"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-1"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-2"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-1"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-2"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-1"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-2"></div>
                                                <div class="heatmap-cell lvl-4"></div>
                                                <div class="heatmap-cell lvl-3"></div>
                                                <div class="heatmap-cell lvl-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="p-2.5 rounded-3 bg-body-tertiary border h-100 text-start">
                                            <div class="text-muted fs-8 fw-semibold mb-1">
                                                RECENT ACTIVITY
                                            </div>
                                            <div class="fs-8 text-primary fw-semibold">
                                                <i class="fa-solid fa-circle-check text-success me-1"></i>
                                                Solved 1931F
                                            </div>
                                            <div class="text-muted fs-8">
                                                Codeforces • 2h ago
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 2. TRUSTED PLATFORMS ================= -->
        <section class="landing-section-muted landing-section-sm">
            <div class="container text-center mb-4">
                <div class="text-uppercase tracking-wider fw-bold text-muted fs-7">
                    CONNECT ALL YOUR COMPETITIVE PROGRAMMING ACCOUNTS ACROSS
                    100+ ONLINE JUDGES
                </div>
            </div>
            <div class="platform-marquee-wrapper">
                <div class="platform-marquee-track">
                    <div class="platform-card-pill">
                        <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                            Codeforces</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag ac"><i class="fa-solid fa-microchip"></i>
                            AtCoder</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag lc"><i class="fa-solid fa-cubes"></i>
                            LeetCode</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cc"><i class="fa-solid fa-utensils"></i>
                            CodeChef</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag hr"><i class="fa-solid fa-globe"></i> Toph</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag kg"><i class="fa-solid fa-cat"></i> Kattis</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag hr"><i class="fa-solid fa-terminal"></i>
                            HackerRank</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cf"><i class="fa-solid fa-laptop-code"></i>
                            HackerEarth</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag lc"><i class="fa-solid fa-bolt"></i> SPOJ</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cc"><i class="fa-solid fa-book"></i> UVa Online
                            Judge</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cf"><i class="fa-solid fa-trophy"></i>
                            TopCoder</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag ac"><i class="fa-solid fa-layer-group"></i>
                            CSES</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag hr"><i class="fa-solid fa-flag-usa"></i>
                            USACO</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag kg"><i class="fa-solid fa-calculator"></i> Project
                            Euler</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill">+90 More Platforms</span>
                    </div>

                    <!-- Duplicated track for smooth infinite loop -->
                    <div class="platform-card-pill">
                        <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                            Codeforces</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag ac"><i class="fa-solid fa-microchip"></i>
                            AtCoder</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag lc"><i class="fa-solid fa-cubes"></i>
                            LeetCode</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cc"><i class="fa-solid fa-utensils"></i>
                            CodeChef</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag hr"><i class="fa-solid fa-globe"></i> Toph</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag kg"><i class="fa-solid fa-cat"></i> Kattis</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag hr"><i class="fa-solid fa-terminal"></i>
                            HackerRank</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cf"><i class="fa-solid fa-laptop-code"></i>
                            HackerEarth</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag lc"><i class="fa-solid fa-bolt"></i> SPOJ</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cc"><i class="fa-solid fa-book"></i> UVa Online
                            Judge</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag cf"><i class="fa-solid fa-trophy"></i>
                            TopCoder</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag ac"><i class="fa-solid fa-layer-group"></i>
                            CSES</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag hr"><i class="fa-solid fa-flag-usa"></i>
                            USACO</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="platform-tag kg"><i class="fa-solid fa-calculator"></i> Project
                            Euler</span>
                    </div>
                    <div class="platform-card-pill">
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill">+90 More Platforms</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 3. WHY JUDGEARENA ================= -->
        <section class="landing-section">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-sparkles"></i> Built For
                        Competitive Programmers</span>
                    <h2 class="section-title">
                        Why Competitive Programmers Choose JudgeArena
                    </h2>
                    <p class="section-subtitle">
                        Stop managing fragmented profiles across dozens of
                        contest sites. JudgeArena provides the ultimate
                        unified identity and analytics suite.
                    </p>
                </div>

                <div class="row g-4 text-start">
                    <div class="col-md-6 col-lg-3">
                        <div class="saas-feature-card">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-globe"></i>
                            </div>
                            <h3 class="saas-feature-title">
                                Single Source of Truth
                            </h3>
                            <p class="saas-feature-desc">
                                Connect your handles from Codeforces,
                                AtCoder, LeetCode, CodeChef, HackerRank and
                                100+ judges into one authoritative, verified
                                link.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="saas-feature-card purple">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <h3 class="saas-feature-title">
                                Automated Background Sync
                            </h3>
                            <p class="saas-feature-desc">
                                Our non-invasive background workers track
                                rating updates, contest ranks, and accepted
                                submissions in real time with 99.9%
                                precision.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="saas-feature-card green">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <h3 class="saas-feature-title">
                                Cross-Judge Analytics
                            </h3>
                            <p class="saas-feature-desc">
                                Compare rating trajectories, difficulty
                                distributions (800 - 3500+), tag masteries
                                (DP, Graphs, Math), and submission verdict
                                statistics.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="saas-feature-card orange">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <h3 class="saas-feature-title">
                                Verified Portfolio
                            </h3>
                            <p class="saas-feature-desc">
                                Share a stunning competitive profile with
                                recruiters, sponsors, and peers. Embed
                                interactive SVG badges on your GitHub
                                README.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 4. UNIFIED PROFILE (VISUAL CONVERSION FLOW) ================= -->
        <section class="landing-section landing-section-muted">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-layer-group"></i> One
                        Profile. Every Judge.</span>
                    <h2 class="section-title">
                        How Multiple Judges Become One Unified Profile
                    </h2>
                    <p class="section-subtitle">
                        Fragmented accounts across isolated judges
                        automatically consolidate into a single
                        authoritative competitive programming identity.
                    </p>
                </div>

                <div class="row align-items-center g-4 justify-content-center text-start">
                    <!-- Scattered Platforms Column -->
                    <div class="col-lg-5">
                        <div class="d-flex flex-column gap-3">
                            <div class="flow-node-card">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                        Codeforces</span>
                                    <div>
                                        <div class="fw-bold text-primary fs-7">
                                            @tourist
                                        </div>
                                        <div class="text-muted fs-8">
                                            Rating: 3782 • 1,840 Solved
                                        </div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-link-slash text-muted fs-7"></i>
                            </div>

                            <div class="flow-arrow-down">
                                <i class="fa-solid fa-arrow-down"></i>
                            </div>

                            <div class="flow-node-card">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="platform-tag ac"><i class="fa-solid fa-microchip"></i>
                                        AtCoder</span>
                                    <div>
                                        <div class="fw-bold text-primary fs-7">
                                            @tourist
                                        </div>
                                        <div class="text-muted fs-8">
                                            Rating: 4221 • 980 Solved
                                        </div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-link-slash text-muted fs-7"></i>
                            </div>

                            <div class="flow-arrow-down">
                                <i class="fa-solid fa-arrow-down"></i>
                            </div>

                            <div class="flow-node-card">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="platform-tag lc"><i class="fa-solid fa-cubes"></i>
                                        LeetCode</span>
                                    <div>
                                        <div class="fw-bold text-primary fs-7">
                                            @tourist_lc
                                        </div>
                                        <div class="text-muted fs-8">
                                            Rating: 3410 • 1,072 Solved
                                        </div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-link-slash text-muted fs-7"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Arrow Connector -->
                    <div class="col-lg-1 text-center d-none d-lg-block">
                        <div class="fs-1 text-primary">
                            <i class="fa-solid fa-angles-right"></i>
                        </div>
                    </div>

                    <!-- Unified Result Card -->
                    <div class="col-lg-6">
                        <div class="unified-hero-result-card text-start">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ asset('web/img/khairul-islam-emon.jpg') }}" alt="Profile"
                                        class="rounded-circle border" width="60" height="60" />
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h4 class="fw-bold mb-0 text-primary">
                                                Gennady Korotkevich
                                            </h4>
                                            <i class="fa-solid fa-circle-check text-primary" title="Verified"></i>
                                        </div>
                                        <div class="text-secondary small">
                                            judgearena.com/u/tourist
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-primary text-white rounded-pill px-3 py-2 fs-7 fw-bold">UNIFIED
                                    PROFILE</span>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="p-3 rounded-3 bg-body border">
                                        <div class="text-muted fs-8 text-uppercase fw-semibold">
                                            Aggregated Rating
                                        </div>
                                        <div class="fs-3 fw-bold text-primary">
                                            2,485
                                        </div>
                                        <div class="text-success fs-8 fw-semibold">
                                            <i class="fa-solid fa-arrow-trend-up"></i>
                                            Top 0.1% Global
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3 bg-body border">
                                        <div class="text-muted fs-8 text-uppercase fw-semibold">
                                            Total Solved
                                        </div>
                                        <div class="fs-3 fw-bold text-success">
                                            4,892
                                        </div>
                                        <div class="text-muted fs-8">
                                            across 14 connected judges
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 bg-body border">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold text-primary fs-7">CONNECTED PLATFORMS (14)</span>
                                    <span class="badge bg-success-subtle text-success fs-8">Synced 2m ago</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="platform-tag cf"><i class="fa-solid fa-code"></i>
                                        Codeforces 3782</span>
                                    <span class="platform-tag ac"><i class="fa-solid fa-microchip"></i>
                                        AtCoder 4221</span>
                                    <span class="platform-tag lc"><i class="fa-solid fa-cubes"></i>
                                        LeetCode 3410</span>
                                    <span class="platform-tag cc"><i class="fa-solid fa-utensils"></i>
                                        CodeChef 3150</span>
                                    <span class="platform-tag hr"><i class="fa-solid fa-terminal"></i>
                                        HackerRank 2400</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 5. POWERFUL ANALYTICS ================= -->
        <section class="landing-section">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-chart-column"></i> Advanced
                        Analytics</span>
                    <h2 class="section-title">
                        Deep Cross-Platform Analytics
                    </h2>
                    <p class="section-subtitle">
                        Turn raw submission history into actionable insight.
                        Understand your weak topics, rating progress, and
                        speed trends.
                    </p>
                </div>

                <div class="row g-4 text-start">
                    <!-- Main Chart -->
                    <div class="col-lg-8">
                        <div class="p-4 bg-body-tertiary rounded-4 border h-100">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1 text-primary">
                                        Rating Drift & Performance
                                        Progression
                                    </h5>
                                    <p class="text-muted small mb-0">
                                        Superimposed ratings over time
                                        across Codeforces, AtCoder, and
                                        LeetCode
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="analytics-tab-btn active">
                                        1 Year
                                    </button>
                                    <button class="analytics-tab-btn">
                                        All Time
                                    </button>
                                </div>
                            </div>

                            <div class="position-relative py-3" style="height: 240px">
                                <svg class="w-100 h-100" viewBox="0 0 600 200" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="cfGrad2" x1="0" y1="0" x2="0"
                                            y2="1">
                                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3" />
                                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>
                                    <line x1="0" y1="40" x2="600" y2="40"
                                        stroke="var(--border)" stroke-dasharray="3,3" />
                                    <line x1="0" y1="90" x2="600" y2="90"
                                        stroke="var(--border)" stroke-dasharray="3,3" />
                                    <line x1="0" y1="140" x2="600" y2="140"
                                        stroke="var(--border)" stroke-dasharray="3,3" />
                                    <polygon points="0,180 0,140 100,120 200,130 300,80 400,90 500,45 600,30 600,180"
                                        fill="url(#cfGrad2)" />
                                    <path d="M0,140 L100,120 L200,130 L300,80 L400,90 L500,45 L600,30" fill="none"
                                        stroke="#3b82f6" stroke-width="3" />
                                    <path d="M0,160 L100,150 L200,120 L300,110 L400,70 L500,60 L600,45" fill="none"
                                        stroke="#06b6d4" stroke-width="2" stroke-dasharray="4,4" />
                                    <path d="M0,170 L100,165 L200,140 L300,130 L400,95 L500,75 L600,60" fill="none"
                                        stroke="#f59e0b" stroke-width="2" stroke-dasharray="2,2" />
                                    <circle cx="300" cy="80" r="5" fill="#3b82f6" stroke="#ffffff"
                                        stroke-width="2" />
                                    <circle cx="500" cy="45" r="5" fill="#3b82f6" stroke="#ffffff"
                                        stroke-width="2" />
                                    <circle cx="600" cy="30" r="6" fill="#22c55e" stroke="#ffffff"
                                        stroke-width="2" />
                                </svg>
                            </div>

                            <div
                                class="d-flex flex-wrap align-items-center justify-content-center gap-4 border-top pt-3 text-muted fs-7">
                                <span class="d-inline-flex align-items-center gap-2"><span
                                        style="
                                                width: 12px;
                                                height: 3px;
                                                background: #3b82f6;
                                                display: inline-block;
                                                border-radius: 2px;
                                            "></span>
                                    Codeforces Rating</span>
                                <span class="d-inline-flex align-items-center gap-2"><span
                                        style="
                                                width: 12px;
                                                height: 3px;
                                                background: #06b6d4;
                                                display: inline-block;
                                                border-radius: 2px;
                                            "></span>
                                    AtCoder Rating</span>
                                <span class="d-inline-flex align-items-center gap-2"><span
                                        style="
                                                width: 12px;
                                                height: 3px;
                                                background: #f59e0b;
                                                display: inline-block;
                                                border-radius: 2px;
                                            "></span>
                                    LeetCode Rating</span>
                            </div>
                        </div>
                    </div>

                    <!-- Side Stats Column -->
                    <div class="col-lg-4 d-flex flex-column gap-4">
                        <!-- Topic Tag Progress -->
                        <div class="p-4 bg-body-tertiary rounded-4 border">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fa-solid fa-brain text-primary me-2"></i>
                                Topic Tag Mastery
                            </h6>
                            <div class="mb-2.5">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium text-primary">Dynamic Programming</span>
                                    <span class="fw-bold text-success">92%</span>
                                </div>
                                <div class="progress" style="height: 6px">
                                    <div class="progress-bar bg-success" style="width: 92%"></div>
                                </div>
                            </div>
                            <div class="mb-2.5">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium text-primary">Graph Theory & Trees</span>
                                    <span class="fw-bold text-primary">85%</span>
                                </div>
                                <div class="progress" style="height: 6px">
                                    <div class="progress-bar bg-primary" style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="mb-2.5">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium text-primary">Math & Number Theory</span>
                                    <span class="fw-bold text-info">78%</span>
                                </div>
                                <div class="progress" style="height: 6px">
                                    <div class="progress-bar bg-info" style="width: 78%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium text-primary">Data Structures</span>
                                    <span class="fw-bold text-warning">71%</span>
                                </div>
                                <div class="progress" style="height: 6px">
                                    <div class="progress-bar bg-warning" style="width: 71%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Language & Verdict Breakdown -->
                        <div class="p-4 bg-body-tertiary rounded-4 border">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fa-solid fa-code-compare me-2"></i>
                                Language & Verdicts
                            </h6>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between fs-7 text-muted mb-1">
                                    <span>Languages Used</span>
                                    <span>C++ (78%), Python (14%), Java
                                        (5%)</span>
                                </div>
                                <div class="progress-stacked" style="height: 8px">
                                    <div class="progress" role="progressbar" style="width: 78%">
                                        <div class="progress-bar bg-primary"></div>
                                    </div>
                                    <div class="progress" role="progressbar" style="width: 14%">
                                        <div class="progress-bar bg-warning"></div>
                                    </div>
                                    <div class="progress" role="progressbar" style="width: 5%">
                                        <div class="progress-bar bg-danger"></div>
                                    </div>
                                    <div class="progress" role="progressbar" style="width: 3%">
                                        <div class="progress-bar bg-info"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-center pt-2 border-top">
                                <div>
                                    <div class="fw-bold text-success fs-6">
                                        88.4%
                                    </div>
                                    <div class="text-muted fs-8">
                                        Accepted
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-warning fs-6">
                                        6.2%
                                    </div>
                                    <div class="text-muted fs-8">TLE</div>
                                </div>
                                <div>
                                    <div class="fw-bold text-danger fs-6">
                                        4.1%
                                    </div>
                                    <div class="text-muted fs-8">WA</div>
                                </div>
                                <div>
                                    <div class="fw-bold text-secondary fs-6">
                                        1.3%
                                    </div>
                                    <div class="text-muted fs-8">RTE</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 6. COMMUNITY PREVIEW ================= -->
        <section class="landing-section landing-section-muted">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-comments"></i> Community
                        Hub</span>
                    <h2 class="section-title">
                        A Thriving Competitive Community
                    </h2>
                    <p class="section-subtitle">
                        Connect with competitive programmers, discuss
                        contest editorials, vote on polls, and solve daily
                        algorithmic concept challenges.
                    </p>
                </div>

                <div class="row g-4 text-start">
                    <!-- Discussion / Posts -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i
                                        class="fa-solid fa-comments fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Discussion & Ideas
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Engage in technical discussions about
                                competitive programming strategies,
                                optimization tricks, and contest prep.
                            </p>
                            <div class="p-2.5 rounded-3 bg-body border fs-7">
                                <div class="fw-semibold text-primary mb-1">
                                    Intuition for Segment Tree Lazy
                                    Propagation
                                </div>
                                <div class="text-muted fs-8">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    Active 10m ago • 48 Replies
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Polls -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i
                                        class="fa-solid fa-square-poll-vertical fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Community Polls
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Vote on contest difficulty ratings, problem
                                quality, and favorite algorithm techniques.
                            </p>
                            <div class="p-2.5 rounded-3 bg-body border fs-7">
                                <div class="fw-semibold text-primary mb-2">
                                    Which problem in AtCoder ABC 350 was
                                    hardest?
                                </div>
                                <div class="progress mb-1" style="height: 18px">
                                    <div class="progress-bar bg-primary text-start px-2 fs-8 fw-bold" style="width: 64%">
                                        Problem F (64%)
                                    </div>
                                </div>
                                <div class="text-muted fs-8">
                                    1,420 votes cast
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MCQs -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-success-subtle text-success p-2 rounded-2"><i
                                        class="fa-solid fa-circle-question fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Algorithmic MCQs
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Test your core knowledge on time complexity,
                                graph theory, memory bounds, and bitwise
                                tricks.
                            </p>
                            <div class="p-2.5 rounded-3 bg-body border fs-7">
                                <div class="fw-semibold text-primary mb-1">
                                    Q: What is the worst-case time of
                                    Quickselect algorithm?
                                </div>
                                <span class="badge bg-success-subtle text-success border me-1">O(N) Average</span>
                                <span class="badge bg-danger-subtle text-danger border">O(N²) Worst</span>
                            </div>
                        </div>
                    </div>

                    <!-- Editorials -->
                    <div class="col-md-6 col-lg-6">
                        <div class="community-module-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-info-subtle text-info p-2 rounded-2"><i
                                        class="fa-solid fa-newspaper fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Official & Community Editorials
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Community-written contest walk-throughs,
                                intuition explanations, and code templates.
                            </p>
                            <div class="p-2.5 rounded-3 bg-body border fs-7">
                                <div class="fw-semibold text-primary mb-1">
                                    Codeforces Round #960 Div 2 Problem E
                                    Editorial
                                </div>
                                <div class="text-muted fs-8">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    Posted by @tourist • 242 Upvotes
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Problem Discussion -->
                    <div class="col-md-6 col-lg-6">
                        <div class="community-module-card">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i
                                        class="fa-solid fa-bug fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Problem Discussion & Debugging
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Ask questions, share TLE stack traces, get
                                peer code reviews, and debug tricky edge
                                cases together.
                            </p>
                            <div class="p-2.5 rounded-3 bg-body border fs-7">
                                <div class="fw-semibold text-primary mb-1">
                                    Debugging WA on Test 47 for CSES Grid
                                    Paths
                                </div>
                                <div class="text-muted fs-8">
                                    <i class="fa-regular fa-comments me-1"></i>
                                    14 Answers • Solved
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 7. GLOBAL STATISTICS ================= -->
        <section class="landing-section">
            <div class="container">
                <div class="row g-4 text-center">
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-counter-card">
                            <div class="stat-counter-number" data-target="100">
                                100+
                            </div>
                            <p class="stat-counter-label">
                                Supported Platforms
                            </p>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-counter-card">
                            <div class="stat-counter-number" data-target="450">
                                450K+
                            </div>
                            <p class="stat-counter-label">
                                Registered Users
                            </p>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="stat-counter-card">
                            <div class="stat-counter-number" data-target="85">
                                85K+
                            </div>
                            <p class="stat-counter-label">
                                Tracked Contests
                            </p>
                        </div>
                    </div>
                    <div class="col-6 col-md-6 col-lg">
                        <div class="stat-counter-card">
                            <div class="stat-counter-number" data-target="15">
                                15M+
                            </div>
                            <p class="stat-counter-label">
                                Solved Problems
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg">
                        <div class="stat-counter-card">
                            <div class="stat-counter-number" data-target="65">
                                65M+
                            </div>
                            <p class="stat-counter-label">
                                Submissions Logged
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 8. EXPLORE JUDGEARENA ================= -->
        <section class="landing-section landing-section-muted">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-compass"></i> Explore
                        Hub</span>
                    <h2 class="section-title">
                        Discover the Competitive Ecosystem
                    </h2>
                    <p class="section-subtitle">
                        Jump directly into global rankings, upcoming contest
                        schedules, or the 100+ platform directory.
                    </p>
                </div>

                <div class="row g-4 text-start">
                    <div class="col-md-6 col-lg-4">
                        <div class="p-4 bg-body rounded-4 border h-100 shadow-sm hover-lift">
                            <div class="p-3 rounded-3 bg-primary-subtle text-primary d-inline-block mb-3">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-primary">
                                User Profiles
                            </h5>
                            <p class="text-secondary small mb-4">
                                Discover top competitive programmers,
                                grandmasters, and rising talent worldwide.
                            </p>
                            <a href="profile.html" class="text-primary fw-semibold small text-decoration-none">
                                View Profiles
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="p-4 bg-body rounded-4 border h-100 shadow-sm hover-lift">
                            <div class="p-3 rounded-3 bg-info-subtle text-info d-inline-block mb-3">
                                <i class="fa-solid fa-cubes fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-primary">
                                Platforms Directory
                            </h5>
                            <p class="text-secondary small mb-4">
                                Explore sync status, API details, and
                                integration guides for 100+ connected
                                judges.
                            </p>
                            <a href="{{ route('platforms.index') }}" class="text-primary fw-semibold small text-decoration-none">
                                Browse Platforms
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="p-4 bg-body rounded-4 border h-100 shadow-sm hover-lift">
                            <div class="p-3 rounded-3 bg-warning-subtle text-warning d-inline-block mb-3">
                                <i class="fa-solid fa-trophy fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-primary">
                                Contests Schedule
                            </h5>
                            <p class="text-secondary small mb-4">
                                Live calendar of upcoming rated rounds
                                across Codeforces, AtCoder & LeetCode.
                            </p>
                            <a href="{{ route('contests.index') }}" class="text-primary fw-semibold small text-decoration-none">
                                View Contests
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="p-4 bg-body rounded-4 border h-100 shadow-sm hover-lift">
                            <div class="p-3 rounded-3 bg-success-subtle text-success d-inline-block mb-3">
                                <i class="fa-solid fa-code fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-primary">
                                Problem Archive
                            </h5>
                            <p class="text-secondary small mb-4">
                                Search & filter 250,000+ problems by
                                normalized difficulty rating and topic tags.
                            </p>
                            <a href="{{ route('problems.index') }}" class="text-primary fw-semibold small text-decoration-none">
                                Search Problems
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="p-4 bg-body rounded-4 border h-100 shadow-sm hover-lift">
                            <div class="p-3 rounded-3 bg-danger-subtle text-danger d-inline-block mb-3">
                                <i class="fa-solid fa-ranking-star fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-primary">
                                Global Rankings
                            </h5>
                            <p class="text-secondary small mb-4">
                                Normalized cross-platform leaderboards
                                filtered by country, university, or company.
                            </p>
                            <a href="{{ route('rankings.index') }}" class="text-primary fw-semibold small text-decoration-none">
                                View Rankings
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="p-4 bg-body rounded-4 border h-100 shadow-sm hover-lift">
                            <div class="p-3 rounded-3 bg-purple-subtle text-purple d-inline-block mb-3"
                                style="
                                        background: rgba(139, 92, 246, 0.12);
                                        color: #8b5cf6;
                                    ">
                                <i class="fa-solid fa-users-line fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-2 text-primary">
                                Community Hub
                            </h5>
                            <p class="text-secondary small mb-4">
                                Engage in editorial discussions, vote on
                                polls, and practice algorithmic MCQs.
                            </p>
                            <a href="#" class="text-primary fw-semibold small text-decoration-none">
                                Join Community
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 9. WHO USES JUDGEARENA ================= -->
        <section class="landing-section">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-users-viewfinder"></i> Built
                        For Everyone</span>
                    <h2 class="section-title">Who Uses JudgeArena?</h2>
                    <p class="section-subtitle">
                        From university students and ICPC World Finalists to
                        coaches, universities, and tech recruiters.
                    </p>
                </div>

                <div class="row g-4 text-start">
                    <!-- Student -->
                    <div class="col-md-6 col-lg">
                        <div class="saas-feature-card h-100">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <h4 class="saas-feature-title fs-6">
                                Students
                            </h4>
                            <p class="saas-feature-desc fs-7 mb-0">
                                Track your growth across platforms while
                                preparing for coding interviews and
                                university contests.
                            </p>
                        </div>
                    </div>

                    <!-- Competitive Programmer -->
                    <div class="col-md-6 col-lg">
                        <div class="saas-feature-card purple h-100">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-code"></i>
                            </div>
                            <h4 class="saas-feature-title fs-6">
                                Competitive Programmers
                            </h4>
                            <p class="saas-feature-desc fs-7 mb-0">
                                Unify ratings from Codeforces, AtCoder &
                                LeetCode into one verified profile card for
                                your GitHub.
                            </p>
                        </div>
                    </div>

                    <!-- ICPC Teams -->
                    <div class="col-md-6 col-lg">
                        <div class="saas-feature-card green h-100">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                            <h4 class="saas-feature-title fs-6">
                                ICPC Teams
                            </h4>
                            <p class="saas-feature-desc fs-7 mb-0">
                                Compare team ratings, analyze topic gaps,
                                and benchmark team progress against global
                                regionals.
                            </p>
                        </div>
                    </div>

                    <!-- Universities -->
                    <div class="col-md-6 col-lg">
                        <div class="saas-feature-card orange h-100">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <h4 class="saas-feature-title fs-6">
                                Universities
                            </h4>
                            <p class="saas-feature-desc fs-7 mb-0">
                                Track student activity across all judges
                                without maintaining manual spreadsheets.
                            </p>
                        </div>
                    </div>

                    <!-- Recruiters -->
                    <div class="col-md-12 col-lg">
                        <div class="saas-feature-card h-100">
                            <div class="saas-feature-icon">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <h4 class="saas-feature-title fs-6">
                                Recruiters
                            </h4>
                            <p class="saas-feature-desc fs-7 mb-0">
                                Verify authentic algorithmic problem-solving
                                ability with cross-platform rating metrics.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= 10. CALL TO ACTION (CTA) ================= -->
        <section class="landing-section pt-0">
            <div class="container">
                <div class="cta-banner text-center">
                    <div class="row justify-content-center">
                        <div class="col-lg-9">
                            <span class="section-badge mb-3"><i class="fa-solid fa-rocket"></i> Get
                                Started Free</span>
                            <h2 class="hero-title mb-3">
                                Ready to Build Your Unified CP Portfolio?
                            </h2>
                            <p class="hero-subtitle mb-4">
                                Join over 450,000 competitive programmers
                                tracking their ratings, contests, and
                                problem solving achievements on JudgeArena.
                            </p>

                            <!-- Clear Action CTA Buttons -->
                            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 mb-4">
                                <a href="{{ route('register') }}" class="btn-hero-primary">
                                    <span>Create Free Account</span>
                                    <i class="fa-solid fa-arrow-right fs-6"></i>
                                </a>
                                <a href="{{ route('platforms.index') }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-plus text-primary"></i>
                                    <span>Connect Your First Platform</span>
                                </a>
                            </div>

                            <div class="hero-trust-list justify-content-center">
                                <span class="hero-trust-item"><i class="fa-solid fa-shield-halved text-success me-1"></i>
                                    No password required</span>
                                <span class="hero-trust-item"><i class="fa-solid fa-bolt text-warning me-1"></i>
                                    Setup in 30 seconds</span>
                                <span class="hero-trust-item"><i class="fa-solid fa-infinity text-primary me-1"></i>
                                    Free Forever</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
