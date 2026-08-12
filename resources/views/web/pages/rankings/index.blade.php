@extends('web.layouts.app')
@section('content')
    <main class="landing-main">
        <!-- Coming Soon Hero Section -->
        <section class="hero-section text-center py-5 position-relative overflow-hidden">
            <div class="hero-glow-bg"></div>
            <div class="container hero-content py-4">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Feature Status Pill -->
                        <div
                            class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-body-tertiary border mb-4 shadow-sm">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fs-7 fw-semibold">
                                <i class="fa-solid fa-sparkles me-1"></i>
                                Coming Soon • Q4 2026
                            </span>
                            <span class="small text-secondary fw-medium">Under Active Development</span>
                        </div>

                        <h1 class="hero-title text-balance mb-3">
                            The Ultimate Global Leaderboard is<br />
                            <span class="hero-gradient-text">Coming Soon.</span>
                        </h1>

                        <p class="hero-subtitle text-balance mx-auto mb-4 max-w-2xl">
                            We're building a unified ranking system to track, aggregate,
                            and compare competitive programmers across all major platforms
                            including Codeforces, LeetCode, and AtCoder.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Planned Modules Grid -->
        <section class="landing-section landing-section-muted border-top">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="section-badge"><i class="fa-solid fa-layer-group"></i> Preview
                        Features</span>
                    <h2 class="section-title">
                        What to Expect in Global Rankings
                    </h2>
                    <p class="section-subtitle">
                        Here is a sneak peek of the powerful ranking metrics
                        currently in development for JudgeArena users.
                    </p>
                </div>

                <div class="row g-4 text-start justify-content-center">
                    <!-- Feature 1 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative h-100">
                            <span
                                class="badge bg-primary-subtle text-primary border rounded-pill position-absolute top-0 end-0 m-3 fs-8">In
                                Dev</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i
                                        class="fa-solid fa-globe fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Unified Global Rank
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                An aggregated Elo rating algorithm combining your performance
                                across multiple integrated online judges.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative h-100">
                            <span
                                class="badge bg-warning-subtle text-warning border rounded-pill position-absolute top-0 end-0 m-3 fs-8">Planned</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i
                                        class="fa-solid fa-flag fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Country Leaderboards
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Compare your rankings with peers in your country
                                and represent your nation on the global stage.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative h-100">
                            <span
                                class="badge bg-success-subtle text-success border rounded-pill position-absolute top-0 end-0 m-3 fs-8">In
                                Dev</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-success-subtle text-success p-2 rounded-2"><i
                                        class="fa-solid fa-building-columns fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    University & Institute Ranks
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Form teams, connect with alumni, and battle for
                                the top institute position worldwide.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="hero-section text-center py-5 position-relative overflow-hidden">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Early Access Email Form -->
                        <div
                            class="cta-banner p-4 rounded-4 border bg-body-tertiary mb-5 text-center shadow-sm max-w-xl mx-auto">
                            <h5 class="fw-bold mb-2 text-primary">
                                Get Early Beta Access
                            </h5>
                            <p class="text-secondary small mb-3">
                                Be the first to claim your unified global rank.
                            </p>
                            <form onsubmit="handleEarlyAccess(event)" class="row g-2 justify-content-center">
                                <div class="col-sm-8">
                                    <input type="email" class="form-control py-2.5 rounded-3 border"
                                        placeholder="Enter your email address" required />
                                </div>
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold">
                                        <span>Notify Me</span>
                                        <i class="fa-solid fa-bell ms-1"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="extra-small text-muted mt-2">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i>
                                No spam. Unsubscribe anytime.
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                            <a href="{{ route('home') }}" class="btn-hero-secondary">
                                <i class="fa-solid fa-house text-primary me-1"></i>
                                Back to Home
                            </a>
                            <a href="{{ route('platforms.index') }}" class="btn-hero-secondary">
                                <i class="fa-solid fa-cubes text-info me-1"></i>
                                Explore Platforms
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
