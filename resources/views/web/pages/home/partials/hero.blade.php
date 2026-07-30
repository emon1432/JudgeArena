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
                            @guest
                                {{-- Guest: Register or Login --}}
                                <a href="{{ route('register') }}" class="btn-hero-primary">
                                    <span>Get Started Free</span>
                                    <i class="fa-solid fa-arrow-right fs-6"></i>
                                </a>
                                <a href="{{ route('rankings.index') }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play text-primary"></i>
                                    <span>Explore Rankings</span>
                                </a>
                            @elseif (Auth::user()->role === 'admin')
                                {{-- Admin: Dashboard + Explore --}}
                                <a href="{{ route('admin.dashboard') }}" class="btn-hero-primary">
                                    <span>Go to Dashboard</span>
                                    <i class="fa-solid fa-gauge-high fs-6"></i>
                                </a>
                                <a href="{{ route('rankings.index') }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play text-primary"></i>
                                    <span>Explore Rankings</span>
                                </a>
                            @else
                                {{-- Regular User: My Profile + Explore --}}
                                <a href="{{ route('user.show', Auth::user()->username ?? 'user') }}"
                                    class="btn-hero-primary">
                                    <span>My Profile</span>
                                    <i class="fa-solid fa-arrow-right fs-6"></i>
                                </a>
                                <a href="{{ route('rankings.index') }}" class="btn-hero-secondary">
                                    <i class="fa-solid fa-circle-play text-primary"></i>
                                    <span>Explore Rankings</span>
                                </a>
                            @endguest
                        </div>

                        <div class="hero-trust-list justify-content-center justify-content-lg-start">
                            @guest
                                <span class="hero-trust-item"><i class="fa-solid fa-shield-halved text-success"></i>
                                    No password required</span>
                                <span class="hero-trust-item"><i class="fa-solid fa-bolt text-warning"></i>
                                    Auto-syncs in 30s</span>
                                <span class="hero-trust-item"><i class="fa-solid fa-circle-check text-primary"></i>
                                    100% Free Forever</span>
                            @else
                                <span class="hero-trust-item"><i class="fa-solid fa-circle-check text-success"></i>
                                    Welcome back, {{ Auth::user()->name }}</span>
                                <span class="hero-trust-item"><i class="fa-solid fa-arrows-rotate text-primary"></i>
                                    Data auto-syncing</span>
                                <span class="hero-trust-item"><i class="fa-solid fa-bolt text-warning"></i>
                                    All platforms live</span>
                            @endguest
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
                                    <span>{{ settings('system_settings', 'app_url') }}/user/tourist</span>
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
                                                <i class="fa-solid fa-circle-check text-primary fs-7"
                                                    title="Verified"></i>
                                            </div>
                                            <div class="text-secondary fs-7">
                                                @tourist â€¢ International
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
                                                Codeforces â€¢ 2h ago
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
