@extends('web.layouts.app')

@section('title', 'Leaderboard | VertiCode')
@section('description', 'Explore VertiCode global leaderboard rankings, compare coding ratings, and filter top programmers by country, institute, and platform.')
@section('keywords', 'coding leaderboard, competitive programming ranking, programmer rankings, VertiCode leaderboard')
@section('canonical', route('leaderboard'))

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('web/css/custom-select2.css') }}">
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

        .leaderboard-top-user .leaderboard-meta {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

        .leaderboard-filter-card .select2.select2-container {
            width: 100% !important;
        }

        .leaderboard-filter-card .select2-container--default .select2-selection--single {
            min-height: calc(1.5em + 0.75rem + 2px);
        }

        .leaderboard-pagination-wrap {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .leaderboard-pagination-wrap .pagination {
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .leaderboard-pagination-wrap .page-link {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            min-width: 2.25rem;
            text-align: center;
            font-weight: 600;
            color: #495057;
        }

        .leaderboard-pagination-wrap .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
            color: #fff;
        }

        .leaderboard-pagination-meta {
            font-size: 0.9rem;
            color: #6c757d;
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
            @include('web.pages.sections.performer-card')
            @include('web.pages.sections.filter')
            @include('web.pages.sections.leaderboard-table')
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('web/js/select2-options.js') }}"></script>
    <script>
        $(function() {
            $('.js-filter-select2').select2({
                width: '100%',
                allowClear: true,
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select option';
                }
            });

            initSelect2AjaxOptions('.js-filter-select2-ajax', {
                endpoint: '{{ route('select2.options') }}',
                placeholder: 'Search and select...'
            });
        });
    </script>
@endpush
