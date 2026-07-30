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
                        <span class="current">Reset Password</span>
                    </nav>

                    <span
                        class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1.5 extra-small font-monospace uppercase fw-semibold mb-3">
                        <i class="fa-solid fa-key me-1"></i> Account
                        Security & Recovery
                    </span>

                    <h1 class="display-6 fw-extrabold text-primary-emphasis tracking-tight mb-3">
                        Secure Account
                        <span class="text-primary">Password Reset</span>.
                    </h1>

                    <p class="text-secondary lead fs-6 mb-4">
                        Regain instant access to your JudgeArena competitive
                        programming analytics and contest notifications.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-warning text-dark rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-envelope-circle-check fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    Instant Reset Verification Link
                                </div>
                                <div class="extra-small text-muted">
                                    A single-use 15-minute expiration
                                    recovery token will be sent to your
                                    inbox.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 extra-small text-muted font-monospace">
                        <span><i class="fa-solid fa-shield text-success me-1"></i>
                            End-to-End Encrypted Link</span>
                    </div>
                </div>
            </div>

            <!-- Right Side Professional Reset Password Card -->
            <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                <!-- Mobile Breadcrumb -->
                <nav class="breadcrumb-list mb-3 text-center d-lg-none" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <a href="{{ route('login') }}">Sign In</a>
                    <span class="sep">/</span>
                    <span class="current">Reset Password</span>
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
                            class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-3 p-3 mb-3">
                            <i class="fa-solid fa-key fs-3"></i>
                        </div>
                        <h2 class="h4 fw-extrabold text-primary-emphasis tracking-tight mb-1">
                            Reset Your Password
                        </h2>
                        <p class="text-muted extra-small mb-0">
                            Enter your account email to receive recovery
                            instructions
                        </p>
                    </div>

                    <!-- Alert Container for Email Sent Feedback -->
                    @if (session('status'))
                        <div class="alert alert-success extra-small mb-3" role="alert">
                            <i class="fa-solid fa-circle-check me-1.5"></i>
                            {{ session('status') }}
                        </div>
                    @endif
                    {{-- <x-validation-errors class="mb-3" /> --}}
                    @if ($errors->any())
                        <div class="alert alert-danger extra-small mb-3" role="alert">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Reset Password Form -->
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="reset-email"
                                class="form-label extra-small font-monospace uppercase fw-semibold text-primary-emphasis">
                                Account Email Address
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary border-end-0 text-muted extra-small">
                                    <i class="fa-regular fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control rounded-end-3 ps-2" id="reset-email"
                                    name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                    placeholder="Enter Your Email">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm"
                            id="reset-submit-btn">
                            <i class="fa-solid fa-paper-plane me-1"></i>
                            Send Reset Link
                        </button>
                    </form>

                    <!-- Card Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="{{ route('login') }}"
                            class="extra-small fw-semibold text-primary text-decoration-none hover-underline">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Return to Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Scripts -->
@endsection
