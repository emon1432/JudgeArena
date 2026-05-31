<div class="tab-pane fade" id="profile-platform">
    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="mb-0">
                <i class="bi bi-laptop"></i> Platform Preferences
            </h5>
        </div>
        <div class="card-body">
            <form
                action="{{ route('user.profile.update', ['username' => auth()->user()->username, 'section' => 'profile-platform']) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @forelse ($platforms as $key => $platform)
                    <div class="mb-4">
                        <label for="{{ $platform->name }}" class="form-label fw-600">
                            <img src="{{ imageShow($platform->icon) }}" alt="{{ $platform->name }}"
                                width="24" height="24" class="me-2">
                            {{ $platform->name }}
                            @if ($platform->name === 'uva')
                                <span data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                    title="<img src='{{ asset('web/img/uva-user-id.png') }}' alt='{{ $platform->name }}' width='500'>">
                                    (<p class="d-inline-block ms-1 mb-0 text-muted" style="font-size: 0.9em;">
                                        See example
                                    </p>
                                    <i class="bi bi-info-circle"></i>)
                                </span>
                            @endif
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">{{ $platform->profile_url }}</span>
                            <input type="text" id="{{ $platform->name }}" name="platforms[{{ $platform->id }}]"
                                value="{{ old('platforms.' . $platform->id, $platform->platformProfiles->firstWhere('user_id', auth()->id())->handle ?? '') }}"
                                class="form-control @error('platforms.' . $platform->id) is-invalid @enderror"
                                placeholder="{{ $platform->name }}_handle">
                        </div>
                        @error($platform->name)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @empty
                    <p class="text-muted">No platforms available. Please contact support.</p>
                @endforelse
                <div class="d-flex gap-2 pt-3">
                    <a href="{{ route('user.profile.show', auth()->user()->username) }}"
                        class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
