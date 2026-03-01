<div class="hero-section">

    <!-- Hero Content -->
    <div class="container position-relative" style="padding-top: 120px; padding-bottom: 80px;">
        <div class="row align-items-center">
            <div class="col-lg-6 text-white mb-5 mb-lg-0">
                <h1 class="display-3 fw-bold mb-4">
                    Track Your <span style="color: #ffd700;">Problem-Solving</span> Journey
                </h1>
                <p class="lead mb-4" style="font-size: 1.25rem; opacity: 0.95;">
                    Monitor your problem counts, ratings, contest performance, and rankings across all major
                    competitive programming platforms in one unified dashboard.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-5">
                    @auth
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('dashboard') }}" class="btn btn-primary-gradient btn-lg">
                                <i class="bi bi-speedometer2"></i> Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('user.profile.show', ['username' => auth()->user()->username]) }}"
                                class="btn btn-primary-gradient btn-lg">
                                <i class="bi bi-person-circle"></i>
                                View Profile
                            </a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary-gradient btn-lg">
                            <i class="bi bi-rocket-takeoff"></i> Start Your Journey
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light-custom btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Sign In
                        </a>
                    @endauth
                </div>

                <!-- Quick Stats -->
                <div class="row g-3">
                    @foreach ($heroStats ?? [] as $stat)
                        <div class="col-6 col-md-6">
                            <div class="stats-card">
                                <div class="stat-label">{{ $stat['label'] }}</div>
                                <div class="stat-value">{{ $stat['value'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Dashboard Preview -->
            <div class="col-lg-6">
                <div class="stats-window floating-animation">
                    <!-- User Profile Card -->
                    <div
                        style="background: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            @php
                                $featuredName = $featuredUser?->name ?? 'Community Coder';
                                $featuredUsername = $featuredUser?->username;
                                $featuredRankLabel = $featuredUserRank ? '#' . number_format($featuredUserRank) : 'N/A';
                                $featuredInitial = strtoupper(substr($featuredName, 0, 1));
                            @endphp
                            <div
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                {{ $featuredInitial }}
                            </div>
                            <div>
                                <div style="color: white; font-weight: 700;">{{ $featuredName }}</div>
                                <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.85rem;">Rank:
                                    {{ $featuredRankLabel }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Overview -->
                    <div style="margin-bottom: 15px;">
                        <div style="color: rgba(255, 255, 255, 0.8); font-weight: 600; margin-bottom: 10px;">Overall
                            Statistics</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div class="stat-item"
                                style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                <div class="stat-label">Total Problems</div>
                                <div class="stat-value" style="font-size: 1.5rem;">
                                    {{ number_format((int) ($featuredUser?->total_solved ?? 0)) }}</div>
                            </div>
                            <div class="stat-item"
                                style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                <div class="stat-label">Current Rating</div>
                                <div class="stat-value" style="font-size: 1.5rem;">
                                    {{ number_format((int) ($featuredUser?->total_rating ?? 0)) }}</div>
                            </div>
                            <div class="stat-item"
                                style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                <div class="stat-label">Best Rating</div>
                                <div class="stat-value" style="font-size: 1.5rem;">
                                    {{ number_format((int) ($featuredUser?->best_rating ?? 0)) }}</div>
                            </div>
                            <div class="stat-item"
                                style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                <div class="stat-label">Platforms</div>
                                <div class="stat-value" style="font-size: 1.5rem;">
                                    {{ number_format((int) ($featuredUser?->platform_count ?? 0)) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Breakdown -->
                    <div>
                        <div style="color: rgba(255, 255, 255, 0.8); font-weight: 600; margin-bottom: 10px;">
                            Platform Statistics</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse (($featuredPlatformStats ?? collect()) as $key => $platformProfile)
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                                    @php
                                        $colors = ['#1f77d2', '#ffc116', '#5cb85c'];
                                        $color = $colors[$key % count($colors)];
                                    @endphp
                                    <span class="platform-badge" style="margin: 0;">
                                        <i class="bi bi-circle-fill" style="color: {{ $color }};"></i>
                                        {{ $platformProfile->platform?->display_name ?? ($platformProfile->platform?->name ?? 'Platform') }}</span>
                                    <span style="color: white; font-weight: 600;">
                                        {{ number_format((int) $platformProfile->total_solved) }} problems</span>
                                </div>
                            @empty
                                <div
                                    style="padding: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 6px; color: rgba(255, 255, 255, 0.75);">
                                    Platform data will appear after profile sync.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
