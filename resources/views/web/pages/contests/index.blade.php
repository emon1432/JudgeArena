@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <x-breadcrumb title="Competitive Programming Contests" :breadcrumbs="['Contests' => null]"></x-breadcrumb>

        <!-- Key Metrics Summary Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Live Contests</span>
                        <span class="badge-live-pulse rounded-pill px-2 py-0-5 extra-small">
                            <span class="pulse-dot"></span> LIVE
                        </span>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($liveCount ?? 0) }} Running
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Currently active contests
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Upcoming Contests</span>
                        <i class="fa-regular fa-clock text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($upcomingCount ?? 0) }} Upcoming
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Scheduled in the future
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Total Contests</span>
                        <i class="fa-solid fa-star text-warning"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0" id="bookmarked-count-num">
                        {{ number_format($totalContests ?? 0) }} Contests
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Tracked on JudgeArena
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Tracked Platforms</span>
                        <i class="fa-solid fa-globe text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($platformsCount ?? 0) }} Platforms
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Supported Online Judges
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Live Now Banner (Featured) -->
        @if ($featuredContests->isNotEmpty())
            <div class="row justify-content-center align-items-stretch g-3 mb-4">
                @foreach ($featuredContests as $featured)
                    <div class="col-md-6">
                        <div class="card panel border-0 p-4 h-100"
                            style="
                            border-radius: 18px;
                            background: linear-gradient(
                                135deg,
                                rgba(239, 68, 68, 0.06) 0%,
                                rgba(59, 130, 246, 0.06) 100%
                            );
                            border: 1px solid rgba(239, 68, 68, 0.2) !important;
                        ">
                            <div
                                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 p-2 border shadow-sm"
                                        style="width: 48px; height: 48px;">
                                        @if ($featured->platform && $featured->platform->icon)
                                            <img src="{{ imageShow($featured->platform->icon) }}"
                                                alt="{{ $featured->platform->name }}"
                                                style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fa-solid fa-code fs-4 text-muted"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                            @php
                                                $isFeaturedLive =
                                                    $featured->phase === 'CODING' ||
                                                    ($featured->start_time &&
                                                        $featured->end_time &&
                                                        $featured->start_time <= now() &&
                                                        $featured->end_time >= now());
                                            @endphp
                                            @if ($isFeaturedLive)
                                                <span class="badge-live-pulse rounded-pill px-2.5 py-1 extra-small">
                                                    <span class="pulse-dot"></span> LIVE CONTEST
                                                </span>
                                            @else
                                                <span
                                                    class="badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1 extra-small border border-warning-subtle">
                                                    <i class="fa-regular fa-clock me-1"></i> UPCOMING
                                                </span>
                                            @endif
                                            @if ($featured->platform)
                                                <span
                                                    class="platform-tag {{ strtolower($featured->platform->short_name) }}">
                                                    {{ $featured->platform->name }}
                                                </span>
                                            @endif
                                            @if ($featured->type)
                                                <span
                                                    class="badge bg-purple-subtle text-purple extra-small">{{ $featured->type }}</span>
                                            @endif
                                        </div>
                                        <h2 class="h6 fw-bold text-primary-emphasis mb-1">
                                            {{ $featured->name }}
                                        </h2>
                                        <div class="extra-small text-muted d-flex align-items-center gap-3 flex-wrap">
                                            @if ($featured->participant_count > 0)
                                                <span><i class="fa-regular fa-user me-1"></i>
                                                    {{ number_format($featured->participant_count) }} Contestants</span>
                                            @endif
                                            <span><i class="fa-regular fa-clock me-1"></i>
                                                {{ floor($featured->duration_seconds / 3600) }}h
                                                {{ floor(($featured->duration_seconds % 3600) / 60) }}m</span>
                                            @if ($featured->is_rated)
                                                <span><i class="fa-solid fa-trophy me-1 text-warning"></i> Rated</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3 flex-shrink-0 mt-3 mt-lg-0">
                                    <div class="countdown-box text-center text-lg-end">
                                        @if ($isFeaturedLive)
                                            <span class="extra-small text-muted text-uppercase d-block mb-1">Ends In</span>
                                            <span class="text-danger fw-bold fs-6 js-countdown"
                                                data-target-date="{{ $featured->end_time ? $featured->end_time->toISOString() : '' }}">
                                                00:00:00
                                            </span>
                                        @else
                                            <span class="extra-small text-muted text-uppercase d-block mb-1">Starts
                                                In</span>
                                            <span class="text-primary fw-bold fs-6 js-countdown"
                                                data-target-date="{{ $featured->start_time ? $featured->start_time->toISOString() : '' }}">
                                                00:00:00
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ $featured->url ?? '#' }}" target="_blank"
                                        class="btn {{ $isFeaturedLive ? 'btn-danger' : 'btn-primary' }} fw-semibold px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                                        @if ($isFeaturedLive)
                                            <i class="fa-solid fa-right-to-bracket"></i> Compete Now
                                        @else
                                            <i class="fa-solid fa-user-plus"></i> Register
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Section 2: Contests Directory Toolbar -->
        <div class="card panel border-0 p-3 mb-4 shadow-sm" style="border-radius: 16px">
            <form method="GET" action="{{ route('contests.index') }}"
                onsubmit="event.preventDefault(); handleLiveSearch(document.getElementById('contest-directory-search'), true);"
                class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 m-0">

                <!-- 1. Search Bar -->
                <div class="position-relative flex-grow-1" style="min-width: 220px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" id="contest-directory-search" name="search" value="{{ request('search') }}"
                        class="form-control ps-5 pe-5 rounded-3" placeholder="Search contest title, platform..."
                        oninput="handleLiveSearch(this)" autocomplete="off" />
                    <div id="contests-search-spinner"
                        class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none"
                        style="width: 14px; height: 14px;" role="status"></div>
                </div>

                <!-- 2. Date Filter -->
                <div class="flex-shrink-0" style="width: 100%; max-width: 160px;">
                    <input type="date" name="date" class="form-control rounded-3 text-muted"
                        value="{{ request('date') }}"
                        onchange="handleLiveSearch(document.getElementById('contest-directory-search'), true)"
                        title="Filter by Date" />
                </div>

                <!-- 2. Platform Filter -->
                <div class="flex-shrink-0" style="width: 100%; max-width: 300px;">
                    <select name="platform" class="form-select rounded-3" id="contests-platform-select"
                        onchange="handleLiveSearch(document.getElementById('contest-directory-search'), true)"
                        data-placeholder="Filter Platform">
                        <option value="all" {{ request('platform', 'all') === 'all' ? 'selected' : '' }}>All Platforms
                        </option>
                        @foreach ($platforms as $p)
                            <option value="{{ $p->slug }}" {{ request('platform') === $p->slug ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Status Filter -->
                <div class="flex-shrink-0" style="width: 100%; max-width: 220px;">
                    <select name="status" class="form-select rounded-3"
                        onchange="handleLiveSearch(document.getElementById('contest-directory-search'), true)">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Contests
                        </option>
                        <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>🔴 Live Now</option>
                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>🕒 Upcoming
                        </option>
                        <option value="past" {{ request('status') === 'past' ? 'selected' : '' }}>📦 Past / Ended
                        </option>
                    </select>
                </div>

                <!-- Hidden inputs to retain logic -->
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}" />
                <input type="hidden" name="sort" value="{{ request('sort', 'soonest') }}" />

            </form>
        </div>

        <!-- Section 3: Contests Directory Table -->
        <div id="contests-table-card" class="card panel border-0 p-0 mb-4 fixed-card shadow-sm"
            style="border-radius: 16px; overflow: hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap" id="contests-directory-table">
                    <thead class="table-light extra-small uppercase font-monospace border-bottom">
                        <tr class="text-center">
                            <th class="ps-4" style="width: 260px">
                                Platform & Contest
                            </th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small" id="contests-table-tbody">
                        @forelse($contests as $contest)
                            @php
                                $isLive =
                                    $contest->phase === 'CODING' ||
                                    ($contest->start_time &&
                                        $contest->end_time &&
                                        $contest->start_time <= now() &&
                                        $contest->end_time >= now());
                                $isUpcoming = $contest->start_time && $contest->start_time > now();
                                $isPast = $contest->end_time && $contest->end_time < now();
                            @endphp
                            <tr class="text-center">
                                <td class="ps-4 text-start">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div
                                            class="platform-avatar-box {{ $contest->platform ? strtolower($contest->platform->short_name) : 'unknown' }} p-2 rounded-2 border">
                                            @if ($contest->platform && $contest->platform->icon)
                                                <img src="{{ imageShow($contest->platform->icon) }}"
                                                    alt="{{ $contest->platform->name }}" class="rounded-2"
                                                    style="width: 30px; height: 30px; object-fit: contain;">
                                            @else
                                                <i class="fa-solid fa-code text-primary fs-5"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div
                                                class="fw-bold text-primary-emphasis d-flex align-items-center gap-2 text-wrap">
                                                <a href="{{ $contest->url ?? '#' }}" target="_blank"
                                                    class="text-primary-emphasis text-decoration-none">
                                                    {{ $contest->name }}
                                                </a>
                                                @if ($isLive)
                                                    <span class="badge-live-pulse rounded-pill px-2 py-0-5 extra-small">
                                                        <span class="pulse-dot"></span> LIVE
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="extra-small text-muted mt-1">
                                                @if ($contest->platform)
                                                    <span
                                                        class="text-muted fw-semibold me-2">{{ $contest->platform->name }}</span>
                                                @endif
                                                @if ($contest->type)
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary me-1 border">{{ $contest->type }}</span>
                                                @endif
                                                @if ($contest->is_rated)
                                                    <span class="text-warning"><i class="fa-solid fa-trophy"></i>
                                                        Rated</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-primary-emphasis">
                                        {{ $contest->start_time ? $contest->start_time->format('d M Y') : 'TBA' }}
                                    </div>
                                    <div class="font-monospace text-secondary extra-small mt-0-5">
                                        {{ $contest->start_time ? $contest->start_time->format('H:i A (T)') : '--' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary extra-small font-monospace">
                                        {{ floor($contest->duration_seconds / 3600) }}h
                                        {{ floor(($contest->duration_seconds % 3600) / 60) }}m
                                    </span>
                                </td>
                                <td>
                                    @if ($isLive)
                                        <span class="text-danger fw-bold font-monospace extra-small d-block mb-1">Ends
                                            in</span>
                                        <span class="text-danger fw-bold font-monospace small js-countdown"
                                            data-target-date="{{ $contest->end_time ? $contest->end_time->toISOString() : '' }}">
                                            00:00:00
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="text-primary fw-bold font-monospace extra-small d-block mb-1">Starts
                                            in</span>
                                        <span class="text-primary fw-bold font-monospace small js-countdown"
                                            data-target-date="{{ $contest->start_time ? $contest->start_time->toISOString() : '' }}">
                                            00:00:00
                                        </span>
                                    @else
                                        <span
                                            class="text-secondary fw-bold font-monospace extra-small d-block mb-1">Ended</span>
                                        <span class="text-muted font-monospace small">
                                            {{ $contest->end_time ? $contest->end_time->diffForHumans() : '--' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ $contest->url ?? '#' }}" target="_blank"
                                        class="btn btn-xs {{ $isLive ? 'btn-danger' : 'btn-primary' }} fw-semibold rounded-2 px-3 me-1"
                                        title="{{ $isLive ? 'Compete Now' : ($isUpcoming ? 'Register' : 'View') }}">
                                        @if ($isLive)
                                            <i class="fa-solid fa-right-to-bracket me-1"></i> Compete
                                        @elseif($isUpcoming)
                                            <i class="fa-solid fa-user-plus me-1"></i> Register
                                        @else
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-calendar-xmark fs-2 mb-2 d-block text-secondary"></i>
                                    No contests found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-infinite-scroll :paginator="$contests" target="#contests-directory-table tbody" />
        </div>
    </main>
@endsection
@push('scripts')
    @include('web.pages.contests.scripts')
@endpush
