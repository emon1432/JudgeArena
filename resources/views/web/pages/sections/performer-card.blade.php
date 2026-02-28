<h4 class="mb-4">
    <i class="bi bi-award-fill"></i> Top Performers
</h4>
<div class="row g-4 mb-4">
    @forelse ($topUsers as $index => $topUser)
        @php
            $position = $index + 1;
            $background =
                $position === 1
                    ? 'var(--primary-gradient)'
                    : ($position === 2
                        ? 'var(--secondary-gradient)'
                        : 'var(--dark-gradient)');
            $image =
                $topUser->image && imageExists($topUser->image)
                    ? imageShow($topUser->image)
                    : $topUser->profile_photo_url;
        @endphp
        <div class="col-lg-4 col-md-6">
            <div class="leaderboard-top-user p-4 shadow-sm" style="background: {{ $background }};">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-light text-dark leaderboard-rank-badge">#{{ $position }}</span>
                    <span class="small opacity-75">
                        @if ($position === 1)
                            🥇 Top Performer
                        @elseif ($position === 2)
                            🥈 Runner-Up
                        @elseif ($position === 3)
                            🥉 Third Place
                        @else
                            Top {{ $position }}
                        @endif
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('user.profile.show', $topUser->username) }}"
                        class="d-flex align-items-center gap-3 text-decoration-none text-dark">
                        <img src="{{ $image }}" alt="{{ $topUser->name }}" class="rounded-circle" width="56"
                            height="56">
                    </a>
                    <div>
                        <div class="fw-bold h5 mb-1">
                            <a href="{{ route('user.profile.show', $topUser->username) }}"
                                class="text-decoration-none text-light">
                                {{ $topUser->name }}
                            </a>
                            @if ($topUser->country)
                                <span title="{{ $topUser->country->name . ' (' . $topUser->country->code . ')' }}">
                                    {{ $topUser->country->flag }}</span>
                            @endif
                        </div>
                        <a href="{{ route('user.profile.show', $topUser->username) }}"
                            class="small opacity-75 text-decoration-none text-light">
                            &#64;{{ $topUser->username }}
                        </a>
                        <div class="small opacity-75"><i class="bi bi-building"></i>
                            {{ $topUser->institute ? $topUser->institute->name : 'No institute' }}
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-top border-light border-opacity-25 d-flex justify-content-between">
                    <div>
                        <div class="small opacity-75">Total rating</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format((int) $topUser->total_rating) }}</div>
                    </div>
                    <div class="text-end">
                        <div class="small opacity-75">Total solved</div>
                        <div class="h5 mb-0 fw-bold">{{ number_format((int) $topUser->total_solved) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">No top users found for the current filter set.</div>
        </div>
    @endforelse
</div>
<hr>
