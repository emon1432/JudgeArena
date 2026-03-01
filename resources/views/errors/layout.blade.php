@extends('web.layouts.app')

@section('title', trim($__env->yieldContent('error_code', 'Error')) . ' - ' . trim($__env->yieldContent('error_title', 'Something went wrong')) . ' | JudgeArena')
@section('description', trim($__env->yieldContent('error_message', 'An unexpected error occurred on JudgeArena.')))

@push('styles')
    <style>
        .error-hero {
            min-height: calc(100vh - 140px);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .error-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 55%);
            pointer-events: none;
        }

        .error-card {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(14px);
            border-radius: 24px;
            padding: 2.2rem;
        }

        .error-code {
            color: #fff;
            font-weight: 800;
            line-height: 1;
            font-size: clamp(3rem, 9vw, 6.2rem);
            margin-bottom: .5rem;
        }

        .error-title {
            color: #fff;
            font-weight: 700;
            line-height: 1.2;
            font-size: clamp(1.4rem, 3vw, 2.2rem);
            margin-bottom: 1rem;
        }

        .error-text {
            color: rgba(255, 255, 255, 0.92);
            font-size: 1.05rem;
            max-width: 720px;
        }
    </style>
@endpush

@section('content')
    <section class="hero-section error-hero">
        <div class="container position-relative" style="z-index: 2;">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-9">
                    <div class="error-card text-center">
                        <div class="platform-badge mb-3">
                            <i class="bi bi-exclamation-triangle"></i> Error
                        </div>

                        <h1 class="error-code">@yield('error_code', 'Error')</h1>
                        <h2 class="error-title">@yield('error_title', 'Something went wrong')</h2>

                        <p class="error-text mx-auto mb-4">
                            @yield('error_message', 'An unexpected error occurred. Please try again in a moment.')
                        </p>

                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('home') }}" class="btn btn-light btn-lg"
                                style="border-radius: 50px; padding: 12px 30px; font-weight: 700;">
                                <i class="bi bi-house-door"></i> Back to Home
                            </a>
                            <a href="{{ route('contact.us') }}" class="btn btn-outline-light-custom btn-lg">
                                <i class="bi bi-chat-dots"></i> Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
