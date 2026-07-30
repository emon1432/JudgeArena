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
                        <a href="{{ route('login') }}">Sign In</a>
                        <span class="sep">/</span>
                        <span class="current">Set New Password</span>
                    </nav>

                    <span
                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 extra-small font-monospace uppercase fw-semibold mb-3">
                        <i class="fa-solid fa-lock-hashtag me-1"></i> Final
                        Step: Set New Password
                    </span>

                    <h1 class="display-6 fw-extrabold text-primary-emphasis tracking-tight mb-3">
                        Create a
                        <span class="text-primary">Strong Password</span>.
                    </h1>

                    <p class="text-secondary lead fs-6 mb-4">
                        Ensure your account credentials are unique and
                        strong to protect your connected online judge
                        credentials.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-success text-white rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-shield fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    Password Best Practices
                                </div>
                                <div class="extra-small text-muted">
                                    Use a combination of uppercase letters,
                                    numbers, and special symbols for maximum
                                    security.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 extra-small text-muted font-monospace">
                        <span><i class="fa-solid fa-check-double text-success me-1"></i>
                            Instant Password Update</span>
                    </div>
                </div>
            </div>

            <!-- Right Side Professional Reset Password Form Card -->
            <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                <!-- Mobile Breadcrumb -->
                <nav class="breadcrumb-list mb-3 text-center d-lg-none" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <a href="{{ route('login') }}">Sign In</a>
                    <span class="sep">/</span>
                    <span class="current">Set New Password</span>
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
                            <i class="fa-solid fa-lock-keyhole fs-3"></i>
                        </div>
                        <h2 class="h4 fw-extrabold text-primary-emphasis tracking-tight mb-1">
                            Set New Password
                        </h2>
                        <p class="text-muted extra-small mb-0">
                            Please enter your new account password below
                        </p>
                    </div>

                    <!-- Alert Container -->

                    @if (session('status'))
                        <div class="alert alert-success extra-small mb-3" role="alert">
                            <i class="fa-solid fa-circle-check me-1.5"></i>
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Reset Password Form -->
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="new-pass-input"
                                class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                New Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <input type="password" class="form-control ps-2 border-end-0" name="password_confirmation"
                                    id="new-pass-input" name="password" required autocomplete="new-password"
                                    placeholder="••••••••••••">
                                <button type="button"
                                    class="btn btn-outline-secondary border-start-0 rounded-end-3 text-muted px-3"
                                    onclick="
                                            togglePasswordVisibility(
                                                'new-pass-input',
                                                this,
                                            )
                                        "
                                    title="Toggle password visibility">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-3">
                            <label for="confirm-new-pass-input"
                                class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                Confirm New Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                                <input type="password" class="form-control ps-2 border-end-0" id="confirm-new-pass-input"
                                    name="password_confirmation" required autocomplete="new-password"
                                    placeholder="••••••••••••">
                                <button type="button"
                                    class="btn btn-outline-secondary border-start-0 rounded-end-3 text-muted px-3"
                                    onclick="
                                            togglePasswordVisibility(
                                                'confirm-new-pass-input',
                                                this,
                                            )
                                        "
                                    title="Toggle password visibility">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Password Strength Bar -->
                        <div class="mb-4">
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

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm"
                            id="save-new-pass-btn">
                            <i class="fa-solid fa-check me-1"></i> Update
                            Password & Sign In
                        </button>
                    </form>

                    <!-- Card Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="{{ route('login') }}"
                            class="extra-small fw-semibold text-primary text-decoration-none hover-underline">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                            to Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Scripts -->
@endsection
