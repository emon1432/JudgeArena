@extends('web.layouts.app')
@section('content')
    <main class="container-fluid px-3 px-md-4 py-4 max-w-7xl">
        <!-- Top Breadcrumb & Action Row -->
        <x-breadcrumb title="Problems Archive" :breadcrumbs="['Problems' => null]"></x-breadcrumb>

        <!-- Key Metrics Summary Row -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Total Indexed Problems -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Total Indexed</span>
                        <i class="fa-solid fa-layer-group text-primary"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ number_format($totalProblems ?? 0) }}
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Curated competitive problems
                    </div>
                </div>
            </div>

            <!-- Card 2: Supported Platforms -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Active Judges</span>
                        <i class="fa-solid fa-server text-success"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ $platformsCount ?? 0 }} Platforms
                    </div>
                    <div class="extra-small text-muted mt-1">
                        {{ $platformShortNames ?: 'Multiple platforms' }} sync active
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Categories -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">Categories</span>
                        <i class="fa-solid fa-tags text-info"></i>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        {{ $totalTags ?? '50+' }} Tags
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Popular Tags
                    </div>
                </div>
            </div>

            <!-- Card 4: System Status -->
            <div class="col-6 col-md-3">
                <div class="card panel border-0 p-3 shadow-sm h-100" style="border-radius: 14px">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted extra-small uppercase font-monospace fw-semibold">System Status</span>
                        <span class="badge bg-success-subtle text-success extra-small rounded-pill px-2">Live</span>
                    </div>
                    <div class="h3 fw-bold text-primary-emphasis mb-0">
                        Syncing
                    </div>
                    <div class="extra-small text-muted mt-1">
                        Problems auto-updating hourly
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Problems Hub Directory Toolbar -->
        <div class="card panel border-0 p-3 mb-4" style="border-radius: 16px">
            <form action="{{ route('problems.index') }}" method="GET"
                onsubmit="event.preventDefault(); applyFilters(true);"
                class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 m-0">

                <div class="position-relative flex-grow-1" style="max-width: 380px">
                    <i
                        class="fa-solid fa-magnifying-glass text-muted position-absolute start-0 top-50 translate-middle-y ms-3 extra-small"></i>
                    <input type="text" name="search" id="problems-directory-search"
                        class="form-control ps-5 pe-5 rounded-3" placeholder="Search problem name, code, tags..."
                        oninput="applyFilters(false)" autocomplete="off" value="{{ request('search') }}" />
                    <div id="problems-search-spinner"
                        class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none"
                        style="width: 14px; height: 14px;" role="status"></div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap flex-lg-nowrap">
                    <!-- Platform Filter -->
                    <select class="form-select form-select-sm rounded-3" name="platform" id="problems-platform-select"
                        onchange="applyFilters(true)" style="width: auto; min-width: 130px;">
                        <option value="all" {{ request('platform', 'all') == 'all' ? 'selected' : '' }}>All Judges
                        </option>
                        @foreach ($activePlatforms as $plat)
                            <option value="{{ $plat->slug }}" {{ request('platform') == $plat->slug ? 'selected' : '' }}>
                                {{ $plat->name }}</option>
                        @endforeach
                    </select>

                    <!-- Difficulty Filter -->
                    <select class="form-select form-select-sm rounded-3" name="difficulty" id="problems-difficulty-select"
                        onchange="applyFilters(true)" style="width: auto; min-width: 120px;">
                        <option value="all" {{ request('difficulty', 'all') == 'all' ? 'selected' : '' }}>All Levels
                        </option>
                        <option value="Easy" {{ request('difficulty') == 'Easy' ? 'selected' : '' }}>Easy</option>
                        <option value="Medium" {{ request('difficulty') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Hard" {{ request('difficulty') == 'Hard' ? 'selected' : '' }}>Hard</option>
                    </select>

                    <!--   -->
                    <div class="tags-filter-wrapper" style="min-width: 180px; max-width: 280px;">
                        @php $selectedTags = request('tags') ? explode(',', request('tags')) : []; @endphp
                        <select class="form-select form-select-sm rounded-3" name="tags[]" id="problems-tags-select"
                            multiple="multiple" style="width: 100%;">
                            @foreach ($availableTags as $tag)
                                <option value="{{ $tag }}" {{ in_array($tag, $selectedTags) ? 'selected' : '' }}>
                                    {{ Str::title(str_replace('-', ' ', $tag)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Dropdown -->
                    <select class="form-select form-select-sm rounded-3" name="sort" id="problems-sort-select"
                        style="width: auto; min-width: 140px;" onchange="applyFilters(true)">
                        <option value="name-asc" {{ request('sort', 'name-asc') == 'name-asc' ? 'selected' : '' }}>Name
                            (A-Z)</option>
                        <option value="diff-asc" {{ request('sort') == 'diff-asc' ? 'selected' : '' }}>Diff (Low-High)
                        </option>
                        <option value="diff-desc" {{ request('sort') == 'diff-desc' ? 'selected' : '' }}>Diff (High-Low)
                        </option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Recently Added</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Section 3: Problems Directory Table Card -->
        <div id="problems-table-card" class="card panel border-0 p-0 mb-4 shadow-sm fixed-card"
            style="border-radius: 16px; overflow: hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap" id="problems-directory-table">
                    <thead class="table-light extra-small uppercase font-monospace border-bottom">
                        <tr class="text-center">
                            <th scope="col" class="text-start" style="min-width: 250px">Problem Name & Code</th>
                            <th scope="col" style="width: 140px">Platform</th>
                            <th scope="col" style="width: 150px">Difficulty</th>
                            <th scope="col" style="min-width: 220px">Category Tags</th>
                            <th scope="col" class="text-end" style="width: 100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small text-center" id="problems-table-tbody">
                        @forelse ($problems as $problem)
                            @php
                                $diffClass = '';
                                $diffText = '';
                                if ($problem->rating !== null) {
                                    if ($problem->rating < 1200) {
                                        $diffClass = 'easy';
                                        $diffText = 'Easy';
                                    } elseif ($problem->rating <= 1900) {
                                        $diffClass = 'medium';
                                        $diffText = 'Medium';
                                    } else {
                                        $diffClass = 'hard';
                                        $diffText = 'Hard';
                                    }
                                } else {
                                    $diffClass = 'secondary';
                                    $diffText = 'N/A';
                                }

                                $platformClass = '';
                                $platformIcon = 'fa-solid fa-code';
                                if ($problem->platform) {
                                    $slug = strtolower($problem->platform->slug);
                                    if (str_contains($slug, 'codeforces')) {
                                        $platformClass = 'cf';
                                    } elseif (str_contains($slug, 'leetcode')) {
                                        $platformClass = 'lc';
                                        $platformIcon = 'fa-solid fa-terminal';
                                    } elseif (str_contains($slug, 'atcoder')) {
                                        $platformClass = 'ac';
                                        $platformIcon = 'fa-solid fa-a';
                                    }
                                }
                            @endphp
                            <tr data-id="{{ $problem->id }}"
                                data-platform="{{ $problem->platform->name ?? 'Unknown' }}">
                                <td class="text-start">
                                    <div class="fw-bold text-primary-emphasis">
                                        <a href="{{ $problem->url }}" target="_blank"
                                            class="problem-title-link text-primary-emphasis text-decoration-none">
                                            @if ($problem->code)
                                                {{ $problem->code }} -
                                            @endif{{ $problem->name }}
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span class="platform-tag {{ $platformClass }}"><i class="{{ $platformIcon }}"></i>
                                        {{ $problem->platform->name ?? 'Unknown' }}</span>
                                </td>
                                <td>
                                    @if ($problem->rating !== null)
                                        <span class="badge-diff {{ $diffClass }}">{{ $diffText }}
                                            ({{ $problem->rating }})
                                        </span>
                                    @else
                                        <span class="badge-diff {{ $diffClass }}">{{ $diffText }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-center">
                                        @if ($problem->tags && is_array($problem->tags))
                                            @foreach (array_slice($problem->tags, 0, 4) as $tag)
                                                <span
                                                    class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block">{{ $tag }}</span>
                                            @endforeach
                                            @if (count($problem->tags) > 4)
                                                <span class="tag-chip font-monospace extra-small me-1 mb-1 d-inline-block"
                                                    title="{{ implode(', ', array_slice($problem->tags, 4)) }}">+{{ count($problem->tags) - 4 }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ $problem->url }}" target="_blank"
                                        class="btn btn-icon btn-xs btn-outline-secondary rounded-2"
                                        title="Solve on {{ $problem->platform->name ?? 'Native Judge' }}">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-layer-group fs-2 mb-2 d-block text-secondary"></i>
                                    No problems found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-infinite-scroll :paginator="$problems" target="#problems-table-tbody" />
        </div>
    </main>
@endsection

@push('scripts')
    @include('web.pages.problems.scripts')
@endpush
