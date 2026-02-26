<div class="card mb-5 overflow-hidden profile-header">
    <div class="profile-cover">
        <p class="text-center h-100 d-flex flex-column justify-content-center align-items-center">
            <span class="mt-2 text-white" style="font-size: 1.25rem;">{{ $user->fav_quote }}</span>
        </p>
        <div class="profile-cover-pattern"></div>
    </div>
    <div class="card-body pt-0">
        <div class="row g-4 align-items-start">
            <div class="col-auto">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar rounded-circle border-4 border-white overflow-hidden">
                        @if ($user->image && imageExists($user->image))
                            <img src="{{ imageShow($user->image) }}" alt="{{ $user->name }}"
                                class="w-100 h-100 object-fit-cover">
                        @elseif($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                class="w-100 h-100 object-fit-cover">
                        @else
                            <div
                                class="w-100 h-100 d-flex align-items-center justify-content-center profile-avatar-fallback">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-4">
                    @auth
                        @if (auth()->user()->id === $user->id)
                            <a href="{{ route('user.profile.edit', $user->username) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="col-auto flex-grow-1">
                <div class="mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2 justify-content-between">
                        <div class="d-flex flex-column">
                            <h2 class="mb-0">
                                {{ $user->name }}
                                <span class="text-muted" style="font-size: 0.75em;">({{ $user->username }})</span>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    @if ($user->country)
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-geo-alt"></i>
                            <span><img src="https://flagcdn.com/16x12/{{ strtolower($user->country->code) }}.png"
                                    alt="{{ $user->country->name }} flag"> {{ $user->country->name }}
                                ({{ $user->country->code }})</span>
                        </div>
                    @endif
                    @if ($user->institute)
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-building"></i>
                            <span>{{ $user->institute->name }}</span>
                        </div>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @if ($user->email)
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-envelope"></i>
                            <span><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></span>
                        </div>
                    @endif
                    @if ($user->phone)
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-telephone"></i>
                            <span><a href="tel:{{ $user->phone }}">{{ $user->phone }}</a></span>
                        </div>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @if ($user->date_of_birth)
                        <div class="d-flex align-items-center gap-1">
                            <i class="bi bi-cake"></i>
                            <span>{{ Carbon\Carbon::parse($user->date_of_birth)->format('F j, Y') }}
                                ({{ Carbon\Carbon::parse($user->date_of_birth)->age }} years old)</span>
                        </div>
                    @endif
                    @if ($user->gender)
                        <div class="d-flex align-items-center gap-1">
                            <i
                                class="bi bi-gender-{{ strtolower($user->gender) == 'other' ? 'genderless' : strtolower($user->gender) }}"></i>
                            <span>{{ $user->gender }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-auto flex-grow-1">
                <div class="d-flex gap-2 flex-wrap justify-content-end m-3">
                    @if ($user->website)
                        <a href="https://{{ $user->website }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle" title="Website">
                            <i class="bi bi-globe"></i>
                        </a>
                    @endif
                    @if ($user->facebook)
                        <a href="https://facebook.com/{{ $user->facebook }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle" title="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                    @endif
                    @if ($user->instagram)
                        <a href="https://instagram.com/{{ $user->instagram }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle" title="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif
                    @if ($user->twitter)
                        <a href="https://twitter.com/{{ $user->twitter }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle" title="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                    @endif
                    @if ($user->github)
                        <a href="https://github.com/{{ $user->github }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle" title="GitHub">
                            <i class="bi bi-github"></i>
                        </a>
                    @endif
                    @if ($user->linkedin)
                        <a href="https://linkedin.com/in/{{ $user->linkedin }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary rounded-circle" title="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    @endif
                </div>
                <div class="mt-4 d-flex gap-2 flex-wrap justify-content-end">
                    @auth
                        @if (auth()->user()->id === $user->id)
                            @php
                                $hasActiveProfiles = $user->platformProfiles->where('status', 'Active')->isNotEmpty();
                                $cooldownMinutes = (int) config('platforms.sync_cooldown_minutes', 120);
                                $canSync = true;
                                $remainingSeconds = 0;

                                if ($user->last_synced_at) {
                                    $nextAvailableAt = $user->last_synced_at->copy()->addMinutes($cooldownMinutes);
                                    if (now()->lt($nextAvailableAt)) {
                                        $canSync = false;
                                        $remainingSeconds = now()->diffInSeconds($nextAvailableAt, false);
                                    }
                                }

                                $syncDisabled = !$hasActiveProfiles || !$canSync;
                            @endphp
                            <form method="POST" action="{{ route('user.sync') }}" class="d-inline" id="syncForm">
                                @csrf
                                <button type="submit" class="btn btn-primary sync-button" id="syncButton"
                                    data-can-sync="{{ $canSync ? 'true' : 'false' }}"
                                    data-has-active-profiles="{{ $hasActiveProfiles ? 'true' : 'false' }}"
                                    data-remaining-seconds="{{ $remainingSeconds }}"
                                    data-cooldown-minutes="{{ $cooldownMinutes }}"
                                    data-sync-status-url="{{ route('user.sync.status') }}"
                                    {{ $syncDisabled ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-repeat"></i>
                                    <span id="syncButtonText">
                                        @if ($canSync && $hasActiveProfiles)
                                            Sync Now
                                        @else
                                            Loading...
                                        @endif
                                    </span>
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
