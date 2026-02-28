@extends('web.layouts.app')

@section('title', 'Leaderboard - VertiCode')

@push('styles')
    <style>
        .leaderboard-hero {
            background: var(--primary-gradient);
            padding: 120px 0 60px;
            position: relative;
            overflow: hidden;
        }

        .leaderboard-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.15);
        }

        .leaderboard-hero .container {
            position: relative;
            z-index: 2;
        }

        .leaderboard-filter-card,
        .leaderboard-table-card,
        .leaderboard-top-card {
            border: 0;
            border-radius: 14px;
        }

        .leaderboard-top-user {
            border-radius: 12px;
            color: white;
        }

        .leaderboard-rank-badge {
            min-width: 44px;
            border-radius: 999px;
            font-weight: 700;
        }

        .leaderboard-table thead th {
            white-space: nowrap;
            font-weight: 700;
            color: var(--bs-secondary-color);
            border-bottom-width: 1px;
        }

        .leaderboard-table tbody td {
            vertical-align: middle;
        }

        .leaderboard-score {
            font-weight: 700;
        }

        .leaderboard-profile-count {
            border-radius: 999px;
            padding: 4px 10px;
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            font-weight: 600;
            font-size: 0.82rem;
        }
    </style>
@endpush

@section('content')
    <section class="leaderboard-hero text-white">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">Global Leaderboard</h1>
                    <p class="lead mb-0 opacity-75">
                        Ranking is based on the sum of all platform ratings.
                        Discover top performers with advanced filtering.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card text-start">
                        <div class="small text-uppercase opacity-75">Current Results</div>
                        <div class="d-flex align-items-end justify-content-between mt-2">
                            <div>
                                <div class="h2 mb-0">{{ number_format($users->total()) }}</div>
                                <div class="small opacity-75">ranked users</div>
                            </div>
                            <i class="bi bi-trophy-fill fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            @include('web.pages.sections.filter')
            @include('web.pages.sections.performer-card')
            @include('web.pages.sections.leaderboard-table')
        </div>
    </section>
@endsection
