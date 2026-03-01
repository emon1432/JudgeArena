@extends('web.layouts.app')

@section('title', 'Login | VertiCode')
@section('description', 'Sign in to your VertiCode account and continue your coding journey.')

@push('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .auth-page {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1100px;
            margin: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -50px;
            right: -50px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -30px;
            left: -30px;
        }

        .login-right {
            padding: 60px 50px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .brand-logo-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .brand-logo-text {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .feature-item {
            display: flex;
            align-items: start;
            gap: 15px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .form-control {
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1.05rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .register-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e0e0e0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            cursor: pointer;
        }

        .input-group .form-control {
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="auth-page">
        <div class="login-container">
            <div class="login-card">
                <div class="row g-0">
                    <!-- Left Side - Branding -->
                    <div class="col-lg-5 d-none d-lg-block">
                        <div class="login-left">
                            <div class="brand-logo">
                                <div class="brand-logo-icon">
                                    <i class="bi bi-code-slash"></i>
                                </div>
                                <div class="brand-logo-text">
                                    <a href="{{ route('home') }}" class="text-white text-decoration-none">
                                        VertiCode
                                    </a>
                                </div>
                            </div>

                            <h2 class="mb-4" style="font-weight: 700;">Welcome Back! 👋</h2>
                            <p class="mb-4" style="opacity: 0.95;">Sign in to your account and continue your coding
                                journey across multiple competitive programming platforms.</p>

                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Real-time Sync</h5>
                                    <p class="mb-0" style="opacity: 0.9; font-size: 0.95rem;">Automatically sync profiles
                                        from all major platforms</p>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Track Progress</h5>
                                    <p class="mb-0" style="opacity: 0.9; font-size: 0.95rem;">Visualize your growth with
                                        detailed analytics</p>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-trophy-fill"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Performance Analytics</h5>
                                    <p class="mb-0" style="opacity: 0.9; font-size: 0.95rem;">Deep insights into your
                                        problem-solving patterns</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Login Form -->
                    <div class="col-lg-7">
                        <div class="login-right">
                            <div class="text-center d-lg-none mb-4">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div
                                        style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="bi bi-code-slash"></i>
                                    </div>
                                    <span
                                        style="font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">VertiCode</span>
                                </div>
                            </div>

                            <h1 class="page-title">Sign In</h1>
                            <p class="page-subtitle">Welcome back! Please enter your details</p>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-person-fill text-muted"></i> Email or Username
                                    </label>
                                    <input type="text" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}"
                                        placeholder="Enter your email or username" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock-fill text-muted"></i> Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password" placeholder="Enter your password" required>
                                        <span class="input-group-text" onclick="togglePassword('password', this)">
                                            <i class="bi bi-eye"></i>
                                        </span>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="forgot-password">
                                            Forgot Password?
                                        </a>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-login">
                                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                                </button>
                            </form>

                            <div class="register-link">
                                Don't have an account? <a href="{{ route('register') }}">Create one now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            const iconElement = icon.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                iconElement.classList.remove('bi-eye');
                iconElement.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                iconElement.classList.remove('bi-eye-slash');
                iconElement.classList.add('bi-eye');
            }
        }
    </script>
@endpush
