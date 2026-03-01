@extends('web.layouts.app')

@section('title', 'Contact Us | JudgeArena')
@section('description', 'Get in touch with the JudgeArena team for support, feedback, partnerships, or platform-related questions.')
@section('keywords', 'contact JudgeArena, JudgeArena support, coding platform support, partnership inquiry')
@section('canonical', route('contact.us'))

@push('styles')
    <style>
        .contact-hero {
            background: var(--primary-gradient);
            padding-top: 130px;
            padding-bottom: 70px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.15), transparent 40%),
                radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.15), transparent 45%);
            pointer-events: none;
        }

        .contact-wrapper {
            margin-top: -35px;
            margin-bottom: 80px;
            position: relative;
            z-index: 2;
        }

        .contact-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.14);
            overflow: hidden;
        }

        .contact-info-box {
            border: 1px solid #eef1ff;
            border-radius: 12px;
            padding: 16px;
            height: 100%;
            background: #fff;
        }

        .contact-info-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(102, 126, 234, 0.12);
            color: #667eea;
            font-size: 1.15rem;
            margin-bottom: 10px;
        }

        .contact-form .form-control {
            border-radius: 10px;
            min-height: 46px;
            border: 1px solid #d8ddf0;
            box-shadow: none;
        }

        .contact-form .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }

        .contact-form textarea.form-control {
            min-height: 140px;
            resize: vertical;
        }
    </style>
@endpush

@section('content')
    <section class="contact-hero">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                    <p class="lead mb-0" style="opacity: 0.92;">
                        Have a question, feedback, or partnership idea? Send us a message and we’ll get back to you.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-wrapper">
        <div class="container">
            <div class="card contact-card">
                <div class="card-body p-4 p-md-5">
                    @if (session('success'))
                        <div class="alert alert-success border-0" style="background: #e9f9ef; color: #0f5132;">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-lg-5">
                            <h3 class="fw-bold mb-3">Let’s Talk</h3>
                            <p class="text-muted mb-4">We usually reply within 24 hours on business days.</p>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="contact-info-box">
                                        <div class="contact-info-icon"><i class="bi bi-envelope"></i></div>
                                        <h6 class="fw-bold mb-1">Email</h6>
                                        <p class="mb-0 text-muted">{{ config('mail.from.address', 'support@judgearena.com') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-12">
                                    <div class="contact-info-box">
                                        <div class="contact-info-icon"><i class="bi bi-clock-history"></i></div>
                                        <h6 class="fw-bold mb-1">Response Time</h6>
                                        <p class="mb-0 text-muted">Within 1 business day</p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-12">
                                    <div class="contact-info-box">
                                        <div class="contact-info-icon"><i class="bi bi-shield-check"></i></div>
                                        <h6 class="fw-bold mb-1">Privacy First</h6>
                                        <p class="mb-0 text-muted">Your contact details stay secure with us.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <form action="{{ route('contact.submit') }}" method="POST" class="contact-form" id="contactUsForm">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Full Name</label>
                                        <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                                            class="form-control @error('name') is-invalid @enderror" placeholder="Enter your name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                                            class="form-control @error('email') is-invalid @enderror"
                                            placeholder="Enter your email">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Subject</label>
                                        <input type="text" name="subject" value="{{ old('subject') }}"
                                            class="form-control @error('subject') is-invalid @enderror"
                                            placeholder="What is this regarding?">
                                        @error('subject')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Message</label>
                                        <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                            placeholder="Write your message here...">{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary-gradient" id="contactSubmitBtn">
                                            <span class="contact-submit-default"><i class="bi bi-send me-2"></i>Send Message</span>
                                            <span class="contact-submit-loading d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Message...</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('contactUsForm');
                const button = document.getElementById('contactSubmitBtn');

                if (!form || !button) {
                    return;
                }

                form.addEventListener('submit', function() {
                    button.disabled = true;
                    button.classList.add('disabled');
                    button.querySelector('.contact-submit-default')?.classList.add('d-none');
                    button.querySelector('.contact-submit-loading')?.classList.remove('d-none');
                });
            });
        </script>
    @endpush
@endsection
