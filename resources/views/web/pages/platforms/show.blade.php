@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <x-breadcrumb title="{{ $platform->name }} Overview & Analytics" :breadcrumbs="[
            'Platforms' => route('platforms.index'),
            $platform->name => null,
        ]"></x-breadcrumb>

        <div class="panel border-0 px-4 py-0 mb-4" style="border-radius: 18px">
            <div class="row align-items-center g-4">
                <div class="col-lg-12">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 p-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                            style="
                                    width: 72px;
                                    height: 72px;
                                    background: var(--surface-tertiary);
                                    border: 1px solid var(--border-strong);
                                ">
                            @if ($platform->icon)
                                <img src="{{ imageShow($platform->icon) }}" alt="{{ $platform->name }}" class="rounded-2"
                                    style="width: 48px; height: 48px; object-fit: contain;">
                            @else
                                <i class="fa-solid fa-code fs-1" style="color: var(--primary)"></i>
                            @endif
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <h2 class="h4 fw-bold text-primary-emphasis mb-0">
                                    {{ $platform->name }}
                                </h2>
                                <a href="{{ $platform->base_url }}" target="_blank" rel="noopener"
                                    title="Visit {{ $platform->name }}">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                            <p class="text-secondary small mb-3 text-balance">
                                {!! $platform->description !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Hosted Contests</span>
                        <i class="fa-solid fa-trophy text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($platform->contests_count ?? 0) }}+ Contests
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Problem Directory</span>
                        <i class="fa-solid fa-layer-group text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($platform->problems_count ?? 0) }}+ Problems
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Global Community</span>
                        <i class="fa-solid fa-users text-primary"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($communityCount ?? 0) }}+ Users
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Connected Handles</span>
                        <i class="fa-solid fa-link text-success"></i>
                    </div>
                    <div class="h3 fw-bold text-success mb-0">
                        {{ number_format($platform->platform_profiles_count ?? 0) }}+
                    </div>
                </div>
            </div>
        </div>

        <div class="card panel border-0 p-4 mb-4" style="border-radius: 18px">
            <nav class="tab-nav mb-4" id="platform-tab-nav" aria-label="Platform Details Tabs">
                <button class="tab-button active" data-tab="pf-contests">
                    <i class="fa-solid fa-trophy me-1.5"></i> Contests Directory
                    ({{ number_format($platform->contests_count ?? 0) }})
                </button>
                <button class="tab-button" data-tab="pf-problems">
                    <i class="fa-solid fa-layer-group me-1.5"></i> Problem Directory
                    ({{ number_format($platform->problems_count ?? 0) }})
                </button>
                <button class="tab-button" data-tab="pf-rankings">
                    <i class="fa-solid fa-award me-1.5"></i> {{ $platform->name }} Rankings
                </button>
                <button class="tab-button" data-tab="pf-blogs">
                    <i class="fa-solid fa-newspaper me-1.5"></i> Official Feed & Blogs
                </button>
            </nav>

            <section class="tab-content" id="tab-content-pf-contests">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap" id="pf-contests-table">
                        <thead class="table-light extra-small text-uppercase font-monospace text-muted border-bottom">
                            <tr>
                                <th scope="col" class="ps-4" style="min-width: 280px">Title</th>
                                <th scope="col" style="min-width: 140px">Category</th>
                                <th scope="col" style="min-width: 170px">Start Date & Time</th>
                                <th scope="col" style="min-width: 110px">Duration</th>
                                <th scope="col" style="min-width: 160px">Participants</th>
                                <th scope="col" class="text-end pe-4" style="min-width: 140px">Action</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($recentContests as $contest)
                                @php
                                    $phaseClass = match (strtoupper((string) $contest->phase)) {
                                        'LIVE',
                                        'CODING',
                                        'RUNNING'
                                            => 'badge-live-pulse text-danger bg-danger-subtle border-danger-subtle',
                                        'BEFORE',
                                        'UPCOMING',
                                        'REGISTRATION'
                                            => 'bg-info-subtle text-info border-info-subtle',
                                        default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                    };
                                    $durationHours = floor(($contest->duration_seconds ?? 0) / 3600);
                                    $durationMins = floor((($contest->duration_seconds ?? 0) % 3600) / 60);
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary-emphasis d-flex align-items-center gap-2">
                                            <span>{{ $contest->name }}</span>
                                            @if ($contest->phase)
                                                <span
                                                    class="badge {{ $phaseClass }} rounded-pill px-2 py-0-5 extra-small border">
                                                    @if (in_array(strtoupper((string) $contest->phase), ['LIVE', 'CODING', 'UPCOMING']))
                                                        <span class="pulse-dot me-1"></span>
                                                    @endif
                                                    {{ strtoupper($contest->phase) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="extra-small text-muted">
                                            {{ $contest->type }}
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle font-monospace px-2.5 py-1 extra-small fw-semibold">
                                            {{ $contest->type ?? 'General' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-primary-emphasis">
                                            {{ $contest->start_time ? $contest->start_time->format('M d, Y · h:i A') : 'TBD' }}
                                        </div>
                                        <div class="extra-small text-muted font-monospace">
                                            UTC+06:00
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium font-monospace">
                                            @if ($contest->duration_seconds)
                                                {{ $durationHours }}h {{ sprintf('%02d', $durationMins) }}m
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">
                                            {{ number_format($contest->standings_count ?: ($contest->participant_count ?: 0)) }} Contestants
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ $contest->url ?: $platform->base_url }}" target="_blank"
                                            rel="noopener"
                                            class="btn btn-xs btn-outline-primary px-3 py-1 fw-semibold rounded-2">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
                                            View Round
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-trophy fs-2 mb-2 d-block text-secondary"></i>
                                        No recent contests synchronized for {{ $platform->name }} yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($platform->contests_count > 0)
                    <div class="p-3 text-center border-top bg-light-subtle rounded-bottom-3 mt-3">
                        <a href="{{ route('contests.index', ['platform' => $platform->slug]) }}"
                            class="btn btn-primary px-4 py-2 fw-semibold rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                            <span>View All {{ number_format($platform->contests_count) }} Contests in Directory</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </section>

            <section class="tab-content d-none" id="tab-content-pf-problems">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap" id="pf-problems-table">
                        <thead class="table-light extra-small text-uppercase font-monospace text-muted border-bottom">
                            <tr>
                                <th scope="col" class="ps-4" style="min-width: 320px">Problem & Code</th>
                                <th scope="col" style="min-width: 130px">Rating</th>
                                <th scope="col" style="min-width: 130px">Points</th>
                                <th scope="col" style="min-width: 220px">Primary Tags</th>
                                <th scope="col" style="min-width: 160px">Solved Count</th>
                                <th scope="col" class="text-end pe-4" style="min-width: 120px">Solve Link</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse ($recentProblems as $problem)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span
                                                class="fw-bold text-primary-emphasis">{{ $problem->code . ' - ' . $problem->name }}</span>
                                        </div>
                                        <div class="extra-small text-muted">
                                            {{ $problem->contest->name ?? $platform->name . ' Problem Archive' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($problem->rating)
                                            @php
                                                $ratingBadge = match (true) {
                                                    $problem->rating >= 2400
                                                        => 'bg-danger-subtle text-danger border-danger-subtle',
                                                    $problem->rating >= 1900
                                                        => 'bg-warning-subtle text-warning border-warning-subtle',
                                                    $problem->rating >= 1400
                                                        => 'bg-info-subtle text-info border-info-subtle',
                                                    default => 'bg-success-subtle text-success border-success-subtle',
                                                };
                                            @endphp
                                            <span class="badge {{ $ratingBadge }} border fw-bold font-monospace">
                                                {{ $problem->rating }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary extra-small">Unrated</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($problem->points)
                                            <span
                                                class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle fw-bold font-monospace">
                                                {{ $problem->points }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary extra-small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-wrap" style="max-width: 380px;">
                                        @if (is_array($problem->tags) && count($problem->tags) > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($problem->tags as $tag)
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle font-monospace px-2 py-1 extra-small d-inline-block">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted extra-small font-monospace">No tags</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-success">
                                            {{ number_format($problem->solved_count ?: ($problem->accepted_submissions ?: 0)) }}
                                            Users
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ $problem->url ?: $platform->base_url }}" target="_blank"
                                            rel="noopener"
                                            class="btn btn-xs btn-outline-primary px-3 py-1 fw-semibold rounded-2">
                                            Solve
                                            <i class="fa-solid fa-arrow-up-right-from-square extra-small ms-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-layer-group fs-2 mb-2 d-block text-secondary"></i>
                                        No problem archive synchronized for {{ $platform->name }} yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($platform->problems_count > 0)
                    <div class="p-3 text-center border-top bg-light-subtle rounded-bottom-3 mt-3">
                        <a href="{{ route('problems.index', ['platform' => $platform->slug]) }}"
                            class="btn btn-primary px-4 py-2 fw-semibold rounded-3 shadow-sm d-inline-flex align-items-center gap-2">
                            <span>View All {{ number_format($platform->problems_count) }} Problems in Directory</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </section>

            <section class="tab-content d-none" id="tab-content-pf-rankings">
                <div class="d-flex flex-column align-items-center justify-content-center min-vh-50 py-5">
                    <i class="fa-solid fa-trophy fs-1 text-muted mb-3"></i>
                    <h3 class="h5 fw-bold text-primary-emphasis mb-1">{{ $platform->name }} Rankings Coming Soon</h3>
                    <p class="text-muted extra-small">The global leaderboard and regional rankings tab will be available
                        soon.</p>
                </div>
            </section>

            <section class="tab-content d-none" id="tab-content-pf-blogs">
                <div class="d-flex flex-column align-items-center justify-content-center min-vh-50 py-5">
                    <i class="fa-solid fa-blog fs-1 text-muted mb-3"></i>
                    <h3 class="h5 fw-bold text-primary-emphasis mb-1">Official Blogs & News Coming Soon</h3>
                    <p class="text-muted extra-small">The real-time feed and blog sync for {{ $platform->name }} will be
                        available soon.</p>
                </div>
            </section>
        </div>
    </main>
@endsection
