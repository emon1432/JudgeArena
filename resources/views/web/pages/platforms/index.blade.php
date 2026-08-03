@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">

        <x-breadcrumb title="Platforms Directory" :breadcrumbs="['Platforms' => null]"></x-breadcrumb>

        <div class="card panel border-0 p-3 mb-4" style="border-radius: 16px">
            <form method="GET" action="{{ route('platforms.index') }}"
                onsubmit="event.preventDefault(); handleLiveSearch(document.getElementById('platforms-search-input'), true);"
                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 m-0">
                <div class="position-relative flex-grow-1" style="max-width: 380px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" id="platforms-search-input" name="search" value="{{ request('search') }}"
                        class="form-control ps-5 pe-5 rounded-3" placeholder="Search platform name, code, domain..."
                        oninput="handleLiveSearch(this)" autocomplete="off" />
                    <div id="platforms-search-spinner"
                        class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none"
                        style="width: 14px; height: 14px;" role="status"></div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}" />
                    <select name="sort" class="form-select form-select-sm rounded-3"
                        style="width: auto; min-width: 180px"
                        onchange="handleLiveSearch(document.getElementById('platforms-search-input'), true)">
                        <option value="popular" {{ request('sort', 'popular') === 'popular' ? 'selected' : '' }}>
                            Most Popular (Connected)
                        </option>
                        <option value="name-asc" {{ request('sort') === 'name-asc' ? 'selected' : '' }}>
                            Platform Name (A-Z)
                        </option>
                        <option value="name-desc" {{ request('sort') === 'name-desc' ? 'selected' : '' }}>
                            Platform Name (Z-A)
                        </option>
                        <option value="contests-desc" {{ request('sort') === 'contests-desc' ? 'selected' : '' }}>
                            Contests (High to Low)
                        </option>
                        <option value="problems-desc" {{ request('sort') === 'problems-desc' ? 'selected' : '' }}>
                            Problems (High to Low)
                        </option>
                    </select>
                </div>
            </form>
        </div>

        <div id="platforms-table-card" class="card panel border-0 p-0 mb-4 shadow-sm"
            style="border-radius: 16px; overflow: hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0 text-nowrap" id="platforms-directory-table">
                    <thead class="table-light extra-small uppercase font-monospace border-bottom">
                        <tr class="text-center">
                            <th class="ps-4" style="width: 260px">
                                Platform & Domain
                            </th>
                            <th>Contests</th>
                            <th>Problems</th>
                            <th>Connected Users</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($platforms as $platform)
                            <tr class="text-center">
                                <td class="ps-4 text-start">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div
                                            class="platform-avatar-box {{ strtolower($platform->short_name) }} p-2 rounded-2 border">
                                            @if ($platform->icon)
                                                <img src="{{ imageShow($platform->icon) }}" alt="{{ $platform->name }}"
                                                    class="rounded-2"
                                                    style="width: 30px; height: 30px; object-fit: contain;">
                                            @else
                                                <i class="fa-solid fa-code text-primary fs-5"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary-emphasis">
                                                <a href="{{ route('platforms.show', $platform->slug) }}"
                                                    class="problem-title-link text-primary-emphasis text-decoration-none">
                                                    {{ $platform->name }}
                                                </a>
                                            </div>
                                            <div class="extra-small text-muted font-monospace">
                                                <a href="{{ $platform->base_url }}" target="_blank"
                                                    class="text-decoration-none text-muted">
                                                    {{ parse_url($platform->base_url, PHP_URL_HOST) ?? $platform->base_url }}<i
                                                        class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary extra-small font-monospace">
                                        {{ number_format($platform->contests_count ?? 0) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary extra-small font-monospace">
                                        {{ number_format($platform->problems_count ?? 0) }}
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="text-secondary extra-small font-monospace">{{ number_format($platform->platform_profiles_count ?? 0) }}</span>
                                </td>
                                <td>
                                    @php
                                        $class = match ($platform->status) {
                                            'Active' => [
                                                'badge' => 'bg-success-subtle text-success border-success-subtle',
                                                'icon' => 'fa-solid fa-link',
                                            ],
                                            'Coming Soon' => [
                                                'badge' => 'bg-info-subtle text-info border-info-subtle',
                                                'icon' => 'fa-solid fa-clock',
                                            ],
                                            'Maintenance' => [
                                                'badge' => 'bg-warning-subtle text-warning border-warning-subtle',
                                                'icon' => 'fa-solid fa-screwdriver-wrench',
                                            ],
                                            'Inactive' => [
                                                'badge' => 'bg-danger-subtle text-danger border-danger-subtle',
                                                'icon' => 'fa-solid fa-ban',
                                            ],
                                            default => [
                                                'badge' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                                'icon' => 'fa-solid fa-circle-info',
                                            ],
                                        };
                                    @endphp

                                    <span class="badge {{ $class['badge'] }} border rounded-pill extra-small">
                                        <i class="{{ $class['icon'] }} me-1"></i> {{ $platform->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('platforms.show', $platform->slug) }}"
                                        class="btn btn-xs btn-primary fw-semibold rounded-2 px-3 me-1"
                                        title="View Platform Hub">
                                        <i class="fa-solid fa-chart-pie me-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-cubes fs-2 mb-2 d-block text-secondary"></i>
                                    No platforms found in directory.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-infinite-scroll :paginator="$platforms" target="#platforms-directory-table tbody" />
        </div>
    </main>
@endsection
@push('scripts')
    @include('web.pages.platforms.scripts')
@endpush
