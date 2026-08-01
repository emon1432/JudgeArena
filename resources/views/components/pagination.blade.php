@props(['paginator', 'limits' => [10, 25, 50, 100]])

@if ($paginator && ($paginator->hasPages() || $paginator->total() > 0))
    <div
        class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 border-top bg-body-tertiary">
        <div class="d-flex align-items-center gap-3">
            <div class="text-muted extra-small">
                @if ($paginator->total() > 0)
                    Showing <span
                        class="fw-semibold text-primary-emphasis">{{ number_format($paginator->firstItem()) }}</span> -
                    <span class="fw-semibold text-primary-emphasis">{{ number_format($paginator->lastItem()) }}</span> of
                    <span class="fw-semibold text-primary-emphasis">{{ number_format($paginator->total()) }}</span> items
                @else
                    No records found
                @endif
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="text-muted extra-small">Limit:</span>
                <select class="form-select form-select-sm rounded-2 py-0 px-2 extra-small"
                    style="width: auto; height: 28px"
                    onchange="let u = new URL(window.location.href); u.searchParams.set('per_page', this.value); u.searchParams.set('page', 1); window.location.href = u.toString();">
                    @foreach ($limits as $limit)
                        <option value="{{ $limit }}"
                            {{ (int) request('per_page', 10) === (int) $limit ? 'selected' : '' }}>
                            {{ $limit }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($paginator->hasPages())
            <nav aria-label="Table navigation">
                <ul class="pagination pagination-sm mb-0 gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="btn btn-xs btn-outline-secondary disabled rounded-2 px-2"><i
                                    class="fa-solid fa-chevron-left"></i></span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="btn btn-xs btn-outline-secondary rounded-2 px-2"
                                href="{{ $paginator->previousPageUrl() }}" rel="prev"><i
                                    class="fa-solid fa-chevron-left"></i></a>
                        </li>
                    @endif

                    {{-- Page Links --}}
                    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span
                                    class="btn btn-xs btn-primary fw-semibold rounded-2 px-2.5">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="btn btn-xs btn-outline-secondary rounded-2 px-2.5"
                                    href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="btn btn-xs btn-outline-secondary rounded-2 px-2"
                                href="{{ $paginator->nextPageUrl() }}" rel="next"><i
                                    class="fa-solid fa-chevron-right"></i></a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="btn btn-xs btn-outline-secondary disabled rounded-2 px-2"><i
                                    class="fa-solid fa-chevron-right"></i></span>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>
@endif
