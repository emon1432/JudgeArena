@extends('admin.layouts.guest')
@section('title', __('Login'))
@section('content')
    <div class="d-none d-xl-flex col-xl-8 p-0">
        <div class="auth-cover-bg d-flex justify-content-center align-items-center">
            <img src="{{ asset('admin') }}/img/illustrations/auth-login-illustration-light.png" alt="auth-login-cover"
                class="my-5 auth-illustration" data-app-light-img="illustrations/auth-login-illustration-light.png"
                data-app-dark-img="illustrations/auth-login-illustration-dark.png" style="visibility: visible;">
            <img src="{{ asset('admin') }}/img/illustrations/bg-shape-image-light.png" alt="auth-login-cover"
                class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
                data-app-dark-img="illustrations/bg-shape-image-dark.png" style="visibility: visible;">
        </div>
    </div>
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
        <div class="w-px-400 mx-auto mt-12 pt-5">
            <h4 class="mb-1">{{ __('Welcome to ') . settings('business_settings', 'company_name') . '! 👋' }}</h4>
            <p class="mb-6">{{ __('Please sign-in to your account and start the adventure') }}</p>

            <form id="formAuthentication" class="mb-6 fv-plugins-bootstrap5 fv-plugins-framework"
                action="{{ route('login') }}" method="POST" novalidate="novalidate">
                @csrf
                <div class="mb-6 form-control-validation fv-plugins-icon-container">
                    <label for="email" class="form-label">{{ __('Email or Username') }}</label>
                    <input type="text" class="form-control" id="email" name="email"
                        placeholder="{{ __('Enter your email or username') }}" autofocus="" value="admin@judgearena.com">
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                    </div>
                </div>
                <div class="mb-6 form-password-toggle form-control-validation fv-plugins-icon-container">
                    <label class="form-label" for="password">{{ __('Password') }}</label>
                    <div class="input-group input-group-merge has-validation">
                        <input type="password" id="password" class="form-control" name="password"
                            placeholder="{{ __('············') }}" aria-describedby="password" value="12345678">
                        <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                    </div>
                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                    </div>
                </div>
                <div class="my-8">
                    <div class="d-flex justify-content-between">
                        <div class="form-check mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember-me">
                            <label class="form-check-label" for="remember-me"> {{ __('Remember Me') }} </label>
                        </div>
                        <a href="auth-forgot-password-cover.html">
                            <p class="mb-0">{{ __('Forgot Password?') }}</p>
                        </a>
                    </div>
                </div>
                <button type="submit"
                    class="btn btn-primary d-grid w-100 waves-effect waves-light">{{ __('Sign in') }}</button>
            </form>
        </div>
    </div>
@endsection
