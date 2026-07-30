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
                            The Home for Competitive Programmers is<br />
                            <span class="hero-gradient-text">Coming Soon.</span>
                        </h1>

                        <p class="hero-subtitle text-balance mx-auto mb-4 max-w-2xl">
                            We're designing a world-class community space
                            for 450K+ developers to publish contest
                            editorials, vote on difficulty polls, test daily
                            algorithmic MCQs, and debug complex edge cases.
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
                        What to Expect in Community Hub
                    </h2>
                    <p class="section-subtitle">
                        Here is a sneak peek of the powerful collaborative
                        tools currently in development for JudgeArena users.
                    </p>
                </div>

                <div class="row g-4 text-start">
                    <!-- Feature 1 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative">
                            <span
                                class="badge bg-primary-subtle text-primary border rounded-pill position-absolute top-0 end-0 m-3 fs-8">In
                                Dev</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary p-2 rounded-2"><i
                                        class="fa-solid fa-newspaper fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Editorials & Walkthroughs
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Peer-reviewed contest editorials, intuition
                                breakdowns, and code snippets across
                                Codeforces & AtCoder rounds.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative">
                            <span
                                class="badge bg-warning-subtle text-warning border rounded-pill position-absolute top-0 end-0 m-3 fs-8">Planned</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning-subtle text-warning p-2 rounded-2"><i
                                        class="fa-solid fa-square-poll-vertical fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Difficulty & Quality Polls
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Vote on contest problem ratings, difficulty
                                spikes, and nominate the best problem set of
                                the month.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative">
                            <span
                                class="badge bg-success-subtle text-success border rounded-pill position-absolute top-0 end-0 m-3 fs-8">In
                                Dev</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-success-subtle text-success p-2 rounded-2"><i
                                        class="fa-solid fa-circle-question fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Algorithmic Concept MCQs
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Test your algorithmic intuition on graph
                                algorithms, memory limits, time
                                complexities, and bitwise tricks.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative">
                            <span
                                class="badge bg-info-subtle text-info border rounded-pill position-absolute top-0 end-0 m-3 fs-8">In
                                Dev</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-info-subtle text-info p-2 rounded-2"><i
                                        class="fa-solid fa-bug fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Q&A & Edge-Case Debugging
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Post TLE/WA stack traces, ask algorithm
                                design questions, and get peer assistance
                                from grandmasters.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative">
                            <span
                                class="badge bg-danger-subtle text-danger border rounded-pill position-absolute top-0 end-0 m-3 fs-8">Planned</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-danger-subtle text-danger p-2 rounded-2"><i
                                        class="fa-solid fa-fire fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    Weekly Sprint Badges
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                Compete in topic sprint challenges like
                                "Solve 5 Tree Problems Above 1800 Rating" to
                                unlock profile badges.
                            </p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="col-md-6 col-lg-4">
                        <div class="community-module-card position-relative">
                            <span
                                class="badge bg-purple-subtle text-purple border rounded-pill position-absolute top-0 end-0 m-3 fs-8"
                                style="
                                        background: rgba(139, 92, 246, 0.12);
                                        color: #8b5cf6;
                                    ">Planned</span>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-purple-subtle text-purple p-2 rounded-2"
                                    style="
                                            background: rgba(
                                                139,
                                                92,
                                                246,
                                                0.12
                                            );
                                            color: #8b5cf6;
                                        "><i
                                        class="fa-solid fa-users-line fs-6"></i></span>
                                <h5 class="fw-bold mb-0 text-primary fs-6">
                                    ICPC & Campus Leaderboards
                                </h5>
                            </div>
                            <p class="text-secondary small mb-3">
                                University team discussions, ICPC regional
                                preparation threads, and team recruitment
                                boards.
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
                                Be the first to join exclusive contest
                                discussions and editorial reviews.
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
                            <a href="index.html" class="btn-hero-secondary">
                                <i class="fa-solid fa-house text-primary me-1"></i>
                                Back to Home
                            </a>
                            <a href="platforms.html" class="btn-hero-secondary">
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
