@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <div class="row align-items-center justify-content-center g-4 lg-g-5">
            <!-- Left Side Feature Presentation Panel (Desktop) -->
            <div class="col-12 col-lg-6 col-xl-5 d-none d-lg-block pe-lg-4">
                <div class="pe-xl-3">
                    <nav class="breadcrumb-list mb-3" aria-label="Breadcrumb navigation">
                        <a href="{{ route('home') }}">Home</a>
                        <span class="sep">/</span>
                        <span class="current">Sign In</span>
                    </nav>

                    <span
                        class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 extra-small font-monospace uppercase fw-semibold mb-3">
                        <i class="fa-solid fa-sparkles me-1"></i>
                        Competitive Programming Portfolio
                    </span>

                    <h1 class="display-6 fw-extrabold text-primary-emphasis tracking-tight mb-3">
                        One Profile for
                        <span class="text-primary">100+ Online Judges</span>.
                    </h1>

                    <p class="text-secondary lead fs-6 mb-4">
                        Connect Codeforces, LeetCode, AtCoder, CodeChef, and
                        SPOJ to track ratings, contest history, and solved
                        problems in one place.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-primary text-white rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-chart-line fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    Unified Rating Progression
                                </div>
                                <div class="extra-small text-muted">
                                    Compare your rating trajectories across
                                    Codeforces, AtCoder, and LeetCode.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-success text-white rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-bell fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    Automated Contest Reminders
                                </div>
                                <div class="extra-small text-muted">
                                    Set instant browser, email, and Google
                                    Calendar alerts for upcoming rounds.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 extra-small text-muted font-monospace">
                        <span><i class="fa-solid fa-shield-halved text-success me-1"></i>
                            256-bit OAuth Sync</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-bolt text-warning me-1"></i>
                            99.9% Uptime</span>
                    </div>
                </div>
            </div>

            <!-- Right Side Professional Auth Card -->
            <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                <!-- Mobile Breadcrumb -->
                <nav class="breadcrumb-list mb-3 text-center d-lg-none" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <span class="current">Sign In</span>
                </nav>

                <!-- Premium Auth Card Panel -->
                <div class="card panel border-0 p-4 p-md-4-5 shadow-sm"
                    style="
                        border-radius: 18px;
                        border: 1px solid var(--border-strong) !important;
                    ">
                    <!-- Card Header -->
                    <div class="text-center mb-4">
                        <div
                            class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 p-3 mb-3">
                            <i class="fa-solid fa-right-to-bracket fs-3"></i>
                        </div>
                        <h2 class="h4 fw-extrabold text-primary-emphasis tracking-tight mb-1">
                            Welcome Back
                        </h2>
                        <p class="text-muted extra-small mb-0">
                            Sign in to your JudgeArena portfolio account
                        </p>
                    </div>

                    <!-- Alert Container for Errors & Status -->
                    @if ($errors->any())
                        <div class="alert alert-danger extra-small mb-3" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Sign In Form -->
                    <form id="login-form" method="POST" action="{{ route('login') }}" onsubmit="handleUserSignIn(event)">
                        @csrf
                        <div class="mb-3">
                            <label for="login-email"
                                class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                Email Address or Handle
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>
                                <input type="text" name="email" value="{{ old('email') }}"
                                    class="form-control rounded-end-3 ps-2" id="login-email"
                                    placeholder="tourist or email@example.com" required autofocus />
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="login-password"
                                    class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis mb-0">
                                    Password
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                        class="extra-small text-decoration-none text-primary hover-underline">
                                        Forgot Password?
                                    </a>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control ps-2 border-end-0"
                                    id="login-password" placeholder="••••••••••••" required />
                                <button type="button"
                                    class="btn btn-outline-secondary border-start-0 rounded-end-3 text-muted px-3"
                                    onclick="togglePasswordVisibility('login-password', this)"
                                    title="Toggle password visibility">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember-me" checked />
                            <label class="form-check-label extra-small text-muted" for="remember-me">
                                Remember me on this device for 30 days
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm"
                            id="signin-submit-btn">
                            <i class="fa-solid fa-right-to-bracket me-1"></i>
                            Sign In to Account
                        </button>
                    </form>

                    <!-- Card Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="extra-small text-muted mb-0">
                            Don't have a JudgeArena account?
                            <a href="{{ route('register') }}"
                                class="fw-semibold text-primary text-decoration-none hover-underline">Create an Account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('scripts')
    <script>
        function handleUserSignIn(e) {
            const btn = document.getElementById("signin-submit-btn");
            if (btn) {
                btn.disabled = true;
                btn.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin me-1"></i> Authenticating...';
            }
        }
    </script>
@endpush
