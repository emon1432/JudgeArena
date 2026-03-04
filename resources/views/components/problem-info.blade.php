<div class="d-flex flex-column">
    <div class="d-flex align-items-center flex-wrap gap-2">
        <h6 class="text-nowrap mb-0">
            @if (!empty($url))
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-heading">
                    {{ !empty($code) ? $code . ' . ' . $name : $name }}
                </a>
            @else
                {{ !empty($code) ? $code . ' . ' . $name : $name }}
            @endif
        </h6>
        @if (!empty($isPremium) && $isPremium)
            <span class="badge bg-label-warning">{{ __('Premium') }}</span>
        @endif
    </div>

    <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
        @foreach ($tags as $tag)
            <span class="badge bg-label-info">{{ $tag }}</span>
        @endforeach
    </div>
</div>
