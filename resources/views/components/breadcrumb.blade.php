@props([
    'title' => '',
    'breadcrumbs' => [],
    'badge' => null,
])

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <nav class="breadcrumb-list mb-1" aria-label="Breadcrumb navigation">
            <a href="{{ route('home') }}">Home</a>
            @foreach ($breadcrumbs as $label => $url)
                <span class="sep">/</span>
                @if ($url && !$loop->last)
                    <a href="{{ $url }}">{{ $label }}</a>
                @else
                    <span class="current">{{ $label }}</span>
                @endif
            @endforeach
        </nav>
        <div class="d-flex align-items-center gap-3">
            <h1 class="h3 fw-extrabold text-primary-emphasis mb-0 tracking-tight">
                {{ $title }}
            </h1>
            @if ($badge)
                <span
                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 extra-small fw-semibold">
                    <i class="fa-solid fa-circle text-success extra-small me-1"></i>
                    {{ $badge }}
                </span>
            @endif
        </div>
    </div>

    @if (isset($slot) && $slot->isNotEmpty())
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{ $slot }}
        </div>
    @endif
</div>
