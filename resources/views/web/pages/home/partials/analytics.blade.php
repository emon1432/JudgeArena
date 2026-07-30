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
