<div class="d-flex flex-column">
    <div class="d-flex align-items-center flex-wrap gap-2">
        @php
            $title = !empty($code) ? $code . ' . ' . $name : $name;
            $mainTitle = $title;
            $parentheticalTitle = null;

            if (preg_match('/^(.*?)(\s*\(.*\))$/u', $title, $matches)) {
                $mainTitle = trim($matches[1]);
                $parentheticalTitle = trim($matches[2]);
            }
        @endphp
        <h6 class="mb-0 lh-sm">
            @if (!empty($url))
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-heading d-inline-block">
                    <span class="d-block">{{ $mainTitle }}</span>
                    @if (!empty($parentheticalTitle))
                        <small class="text-muted d-block">{{ $parentheticalTitle }}</small>
                    @endif
                </a>
            @else
                <span class="d-block">{{ $mainTitle }}</span>
                @if (!empty($parentheticalTitle))
                    <small class="text-muted d-block">{{ $parentheticalTitle }}</small>
                @endif
            @endif
        </h6>
        @if (!empty($isRated) && $isRated)
            <span class="badge bg-label-success">{{ __('Rated') }}</span>
        @endif
    </div>

    <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
        @foreach ($tags as $tag)
            <span class="badge bg-label-info">{{ $tag }}</span>
        @endforeach
    </div>
</div>
