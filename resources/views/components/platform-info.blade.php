<div class="d-flex justify-content-start align-items-center item-info">
    <div class="avatar-wrapper">
        <div class="avatar avatar me-2 me-sm-4 rounded-2 bg-label-secondary">
            @if (!empty($icon) && file_exists(public_path($icon)))
                <img src="{{ $initials }}" alt="{{ $name }}" class="rounded">
            @elseif (!empty($initials))
                @php
                    $colors = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                    $color = $colors[array_rand($colors)];
                @endphp
                <span class="avatar-initial rounded-2 bg-label-{{ $color }}">
                    {{ $initials }}
                </span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-column">
        <h6 class="text-nowrap mb-0">{{ $name }} {{ $shortName ? '(' . $shortName . ')' : '' }}</h6>
        <small class="text-truncate d-none d-sm-block">{{ '@' . $slug }}</small>
    </div>
</div>
