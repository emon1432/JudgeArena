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
                            <a href="{{ route('rankings.index') }}"
                                class="text-primary fw-semibold small text-decoration-none">
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
                            <a href="{{ route('platforms.index') }}"
                                class="text-primary fw-semibold small text-decoration-none">
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
                            <a href="{{ route('contests.index') }}"
                                class="text-primary fw-semibold small text-decoration-none">
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
                            <a href="{{ route('problems.index') }}"
                                class="text-primary fw-semibold small text-decoration-none">
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
                            <a href="{{ route('rankings.index') }}"
                                class="text-primary fw-semibold small text-decoration-none">
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
                            <a href="{{ route('community.index') }}"
                                class="text-primary fw-semibold small text-decoration-none">
                                Join Community
                                <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
