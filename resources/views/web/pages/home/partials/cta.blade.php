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
                                @auth
                                    <a href="{{ route('user.show', Auth::user()->username ?? 'user') }}"
                                        class="btn-hero-primary">
                                        <span>Go to My Profile</span>
                                        <i class="fa-solid fa-arrow-right fs-6"></i>
                                    </a>
                                    <a href="{{ route('platforms.index') }}" class="btn-hero-secondary">
                                        <i class="fa-solid fa-plus text-primary"></i>
                                        <span>Connect a Platform</span>
                                    </a>
                                @else
                                    <a href="{{ route('register') }}" class="btn-hero-primary">
                                        <span>Create Free Account</span>
                                        <i class="fa-solid fa-arrow-right fs-6"></i>
                                    </a>
                                    <a href="{{ route('platforms.index') }}" class="btn-hero-secondary">
                                        <i class="fa-solid fa-plus text-primary"></i>
                                        <span>Connect Your First Platform</span>
                                    </a>
                                @endauth
                            </div>

                            <div class="hero-trust-list justify-content-center">
                                <span class="hero-trust-item"><i
                                        class="fa-solid fa-shield-halved text-success me-1"></i>
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
