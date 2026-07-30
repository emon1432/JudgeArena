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
                        <a href="{{ route('password.request') }}">Forgot Password</a>
                        <span class="sep">/</span>
                        <span class="current">Verify OTP</span>
                    </nav>

                    <span
                        class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 extra-small font-monospace uppercase fw-semibold mb-3">
                        <i class="fa-solid fa-shield me-1"></i>
                        Two-Factor Account Verification
                    </span>

                    <h1 class="display-6 fw-extrabold text-primary-emphasis tracking-tight mb-3">
                        Enter
                        <span class="text-primary">Verification Code</span>.
                    </h1>

                    <p class="text-secondary lead fs-6 mb-4">
                        We sent a 6-digit security verification code to your
                        email address. Enter the code below to reset your
                        password.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-start gap-3 p-3 rounded-3 panel border-0 shadow-xs"
                            style="background: var(--surface-2)">
                            <div class="bg-primary text-white rounded-2 p-2 flex-shrink-0">
                                <i class="fa-solid fa-hourglass-half fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-primary-emphasis small">
                                    10-Minute Code Expiration
                                </div>
                                <div class="extra-small text-muted">
                                    Verification codes expire automatically
                                    for security protection.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 extra-small text-muted font-monospace">
                        <span><i class="fa-solid fa-lock text-success me-1"></i>
                            256-bit Encrypted Token</span>
                    </div>
                </div>
            </div>

            <!-- Right Side Professional Verify OTP Card -->
            <div class="col-12 col-md-8 col-lg-6 col-xl-4">
                <!-- Mobile Breadcrumb -->
                <nav class="breadcrumb-list mb-3 text-center d-lg-none" aria-label="Breadcrumb navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">/</span>
                    <a href="{{ route('password.request') }}">Forgot Password</a>
                    <span class="sep">/</span>
                    <span class="current">Verify OTP</span>
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
                            <i class="fa-solid fa-shield-halved fs-3"></i>
                        </div>
                        <h2 class="h4 fw-extrabold text-primary-emphasis tracking-tight mb-1">
                            Enter Verification Code
                        </h2>
                        <p class="text-muted extra-small mb-0">
                            Check your inbox for a 6-digit code sent to
                            <br /><strong class="text-primary-emphasis" id="otp-user-email">tourist@example.com</strong>
                        </p>
                    </div>

                    <!-- Alert Container -->
                    <div class="alert alert-danger extra-small d-none mb-3" id="otp-alert-error" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1.5"></i>
                        Invalid code entered. Please check and try again.
                    </div>

                    <!-- OTP Form -->
                    <form id="verify-otp-form" onsubmit="handleVerifyOTP(event)">
                        <!-- 6-Digit OTP Box Inputs -->
                        <div class="d-flex justify-content-between gap-2 mb-4" id="otp-inputs-container">
                            <input type="text"
                                class="form-control text-center font-monospace fw-bold fs-4 p-2 rounded-3 otp-field"
                                maxlength="1" pattern="[0-9]" required autofocus />
                            <input type="text"
                                class="form-control text-center font-monospace fw-bold fs-4 p-2 rounded-3 otp-field"
                                maxlength="1" pattern="[0-9]" required />
                            <input type="text"
                                class="form-control text-center font-monospace fw-bold fs-4 p-2 rounded-3 otp-field"
                                maxlength="1" pattern="[0-9]" required />
                            <input type="text"
                                class="form-control text-center font-monospace fw-bold fs-4 p-2 rounded-3 otp-field"
                                maxlength="1" pattern="[0-9]" required />
                            <input type="text"
                                class="form-control text-center font-monospace fw-bold fs-4 p-2 rounded-3 otp-field"
                                maxlength="1" pattern="[0-9]" required />
                            <input type="text"
                                class="form-control text-center font-monospace fw-bold fs-4 p-2 rounded-3 otp-field"
                                maxlength="1" pattern="[0-9]" required />
                        </div>

                        <!-- Resend Code Timer -->
                        <div class="text-center mb-4 extra-small text-muted">
                            <span>Didn't receive the code? </span>
                            <button type="button"
                                class="btn btn-link btn-sm p-0 extra-small fw-semibold text-decoration-none text-primary"
                                id="resend-otp-btn" onclick="resendOTPCode()" disabled>
                                Resend Code (<span id="resend-timer-count">45</span>s)
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm"
                            id="verify-otp-btn">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            Verify & Continue
                        </button>
                    </form>

                    <!-- Card Footer -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="{{ route('password.request') }}"
                            class="extra-small fw-semibold text-primary text-decoration-none hover-underline">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Change Email Address
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Scripts -->
@endsection
