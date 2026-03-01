@extends('user.layouts.app')
@section('title', $user->name . ' | Profile')
@section('description', 'View ' . $user->name . '\'s profile, stats, and platform profiles on VertiCode.')
@section('keywords', $user->name . ', competitive programming profile, coding profile, VertiCode user profile')
@section('canonical', route('user.profile.show', $user->username))

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $user->name,
            'url' => route('user.profile.show', $user->username),
            'description' => 'Competitive programming profile on ' . config('app.name', 'VertiCode'),
            'sameAs' => array_values(array_filter([$user->website, $user->github, $user->linkedin, $user->twitter])),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@push('styles')
    <style>
        .sync-button .bi.spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .card.transition:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
@endpush
@section('content')
    <div class="container-lg">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @include('user.pages.profile.sections.show.profile-header')
        @include('user.pages.profile.sections.show.stats')
        @include('user.pages.profile.sections.show.platform-profiles')
        <div class="row mb-5 g-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm transition" style="transition: all 0.3s ease;">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                        <div class="text-center">
                            <h3 class="mb-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700;">
                                More features coming soon! Stay tuned. 🚀
                            </h3>
                            <p class="text-muted mb-0">We're working hard to bring you new insights and features to enhance your competitive programming journey.</p>
                        </div>
                        <div style="font-size: 3rem; color: #f5576c; animation: spin 2s linear infinite;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('user.pages.profile.script')
@endpush
