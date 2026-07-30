<div class="d-flex justify-content-start align-items-center user-name">
    <div class="avatar-wrapper">
        <div class="avatar avatar-sm me-4">
            @if (strlen($initials) > 2)
                <img src="{{ $initials }}" alt="{{ $user->name }}" class="rounded-circle">
            @else
                @php
                    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
                @endphp
                <span
                    class="avatar-initial rounded-circle bg-label-{{ $colors[array_rand($colors)] }}">{{ $initials }}</span>
            @endif
        </div>
    </div>
    <div class="d-flex flex-column">
        <a href="{{ route('admin.users.show', $user->id) }}" class="text-heading text-truncate">
            <span class="fw-medium">{{ $user->name }}</span>
        </a>
        <small>
            {{ '@' . $user->username }}
        </small>
    </div>
</div>
