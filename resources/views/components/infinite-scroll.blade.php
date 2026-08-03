@props(['paginator', 'target' => ''])

@if ($paginator && $paginator->hasMorePages())
    <div class="universal-infinite-loader d-flex flex-column align-items-center justify-content-center py-4 my-2 w-100" 
         data-next-url="{{ $paginator->nextPageUrl() }}" 
         data-target-container="{{ $target }}" 
         data-loading="false">
        <div class="infinite-spinner-wrapper d-flex align-items-center gap-2 px-4 py-2 rounded-pill bg-body-tertiary border shadow-sm transition-all" style="backdrop-filter: blur(8px);">
            <div class="spinner-border spinner-border-sm text-primary" style="width: 1rem; height: 1rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="small fw-semibold text-muted font-monospace">Loading more items...</span>
        </div>
    </div>
@elseif ($paginator && ($paginator->count() > 0))
    <div class="universal-infinite-end text-center py-4 text-muted extra-small font-monospace border-top w-100">
        <i class="fa-solid fa-flag-checkered text-secondary me-1.5"></i> All available records loaded successfully.
    </div>
@endif
