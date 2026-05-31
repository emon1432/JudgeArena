<h4 class="mb-4">
    <i class="bi bi-diagram-3-fill"></i> Platform Statistics
</h4>
@if ($user->platformProfiles->count() > 0)
    <div class="card overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table mb-0 platform-table">
                <thead class="text-center">
                    <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th class="fw-600 text-white ps-4"><i class="bi bi-trophy me-2"></i>Platform</th>
                        <th class="fw-600 text-white"><i class="bi bi-person me-2"></i>Handle</th>
                        <th class="fw-600 text-white text-center"><i class="bi bi-gear me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($platforms as $platform)
                        @php
                            $profile = $platform->platformProfiles->where('user_id', $user->id)->first();
                        @endphp
                        <tr class="platform-row text-center"
                            style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s ease; {{ $profile ? '' : 'background: #fff0f0;' }}">
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="platform-icon-bg"
                                        style="width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); font-size: 1.5rem;">
                                        @if ($platform->icon && imageExists($platform->icon))
                                            <img src="{{ imageShow($platform->icon) }}"
                                                alt="{{ $platform->name }} Logo"
                                                style="width: 30px; height: 30px;">
                                        @else
                                            {{ '🏆' }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-600">{{ $platform->name }}</p>
                                    </div>
                                </div>
                            </td>
                            @if ($profile)
                                <td class="py-4">
                                    <span class="badge bg-light text-dark"
                                        style="font-size: 0.85rem; padding: 8px 12px; border-radius: 8px;">
                                        {{ $profile->handle }}
                                    </span>
                                </td>
                            @else
                                <td class="py-4" colspan="4">
                                    <span class="text-muted gap-1">
                                        <i class="bi bi-x-circle"></i> Not Connected
                                    </span>
                                </td>
                            @endif
                            <td class="py-4">
                                @if (auth()->id() === $user->id)
                                    <a href="{{ route('user.profile.edit', ['username' => $user->username]) }}#profile-platform"
                                        title="Edit Platform Profiles" class="me-2 text-decoration-none">
                                        <i class="bi bi-pencil me-1"></i>
                                    </a>
                                @endif
                                @if ($profile)
                                    <a href="{{ $profile->profile_url }}" target="_blank" title="View Profile"
                                        class="text-decoration-none">
                                        <i class="bi bi-arrow-up-right me-1"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card text-center py-5 mb-5">
        <div class="card-body">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e0;"></i>
            <p class="text-muted mt-3">No connected platforms yet</p>
            @if (auth()->check() && auth()->user()->username === $user->username)
                <a href="{{ route('user.profile.edit', ['username' => $user->username]) }}#profile-platform"
                    class="btn btn-outline-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i> Connect Platform
                </a>
            @endif
        </div>
    </div>
@endif
