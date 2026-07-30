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
                        <span class="current">Create Account</span>
                    </nav>

                    <span
                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 extra-small font-monospace uppercase fw-semibold mb-3">
                        <i class="fa-solid fa-shield me-1"></i> Free
                        Forever Account
                    </span>

                    <h1 class="display-6 fw-extrabold text-primary-emphasis tracking-tight mb-3">
                        Elevate Your
                        <span class="text-primary">Coding Portfolio</span>
                        Today.
                    </h1>

                    <p class="text-secondary lead fs-6 mb-4">
                        Join 85,000+ competitive programmers showcasing
                        their contest ranks, ratings, and problem archives
                        across 100+ judges.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-primary text-white rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-cubes fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    100+ Platform Connections
                                </div>
                                <div class="extra-small text-muted">
                                    Auto-sync ratings from Codeforces,
                                    LeetCode, AtCoder, CSES & SPOJ.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-success text-white rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-share-nodes fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    Shareable SaaS Profile URL
                                </div>
                                <div class="extra-small text-muted">
                                    Get your custom handle link
                                    judgearena.com/u/yourname for resumes.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 extra-small text-muted font-monospace">
                        <span><i class="fa-solid fa-lock text-success me-1"></i>
                            No Credit Card Required</span>
                        <span>•</span>
                        <span><i class="fa-solid fa-bolt text-warning me-1"></i>
                            Instant Handle Verification</span>
                    </div>
                </div>
            </div>

            <!-- Right Side Professional Registration Card -->
            <div class="col-12 col-md-9 col-lg-6 col-xl-5">
                <!-- Mobile Breadcrumb -->
                <nav class="breadcrumb-list mb-3 text-center d-lg-none" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <span class="current">Create Account</span>
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
                            class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-3 p-3 mb-3">
                            <i class="fa-solid fa-user-plus fs-3"></i>
                        </div>
                        <h2 class="h4 fw-extrabold text-primary-emphasis tracking-tight mb-1">
                            Create Your Free Account
                        </h2>
                        <p class="text-muted extra-small mb-0">
                            Start building your unified competitive
                            programming profile
                        </p>
                    </div>

                    <!-- Alert Container for Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger extra-small mb-3" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif


                    <!-- Registration Form -->
                    <form id="register-form" method="POST" action="{{ route('register') }}"
                        onsubmit="handleUserSignUp(event)">
                        @csrf
                        <div class="row g-3 mb-3">
                            <!-- Full Name -->
                            <div class="col-12 col-sm-6">
                                <label for="reg-fullname"
                                    class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                    Full Name
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                        <i class="fa-regular fa-user"></i>
                                    </span>
                                    <input type="text" name="name" value="{{ old('name') }}"
                                        class="form-control rounded-end-3 ps-2" id="reg-fullname"
                                        placeholder="Gennady Korotkevich" required />
                                </div>
                            </div>

                            <!-- Username / Handle -->
                            <div class="col-12 col-sm-6">
                                <label for="reg-handle"
                                    class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                    Username / Handle
                                </label>
                                <div class="input-group">
                                    <span
                                        class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">@</span>
                                    <input type="text" name="username" value="{{ old('username') }}"
                                        class="form-control rounded-end-3 ps-2" id="reg-handle" placeholder="tourist"
                                        required />
                                </div>
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="reg-email"
                                class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                Email Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="form-control rounded-end-3 ps-2" id="reg-email" placeholder="gennady@example.com"
                                    required />
                            </div>
                        </div>

                        <!-- Password Fields -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-6">
                                <label for="reg-password"
                                    class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                    Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control ps-2 border-end-0"
                                        id="reg-password" placeholder="••••••••••••"
                                        onkeyup="checkPasswordStrength(this.value)" required />
                                    <button type="button"
                                        class="btn btn-outline-secondary border-start-0 rounded-end-3 text-muted px-2.5"
                                        onclick="togglePasswordVisibility('reg-password', this)"
                                        title="Toggle password visibility">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="reg-confirm-password"
                                    class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                    Confirm Password
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </span>
                                    <input type="password" name="password_confirmation"
                                        class="form-control ps-2 border-end-0" id="reg-confirm-password"
                                        placeholder="••••••••••••" required />
                                    <button type="button"
                                        class="btn btn-outline-secondary border-start-0 rounded-end-3 text-muted px-2.5"
                                        onclick="togglePasswordVisibility('reg-confirm-password', this)"
                                        title="Toggle password visibility">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Password Strength Bar -->
                        <div class="mb-3">
                            <div class="progress" style="height: 5px" id="password-strength-bar-container">
                                <div class="progress-bar bg-secondary" id="password-strength-bar" role="progressbar"
                                    style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="extra-small text-muted mt-1 d-flex justify-content-between">
                                <span>Strength:
                                    <strong id="password-strength-text" class="text-secondary">Too short</strong></span>
                                <span>At least 8 characters</span>
                            </div>
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms-agree" required />
                            <label class="form-check-label extra-small text-muted" for="terms-agree">
                                I agree to JudgeArena's
                                <a href="#" class="text-primary text-decoration-none">Terms of Service</a>
                                and
                                <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>.
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm"
                            id="signup-submit-btn">
                            <i class="fa-solid fa-user-plus me-1"></i>
                            Create Free Account
                        </button>
                    </form>

                    <!-- Card Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="extra-small text-muted mb-0">
                            Already have a JudgeArena account?
                            <a href="{{ route('login') }}"
                                class="fw-semibold text-primary text-decoration-none hover-underline">Sign In Instead</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('scripts')
    <script>
        function handleUserSignUp(e) {

            const btn = document.getElementById("signup-submit-btn");
            if (btn) {
                btn.disabled = true;
                btn.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin me-1"></i> Creating Account...';
            }
        }
    </script>
@endpush
