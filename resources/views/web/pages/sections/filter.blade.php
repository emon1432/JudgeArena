<h4 class="mb-4">
    <i class="bi bi-funnel-fill"></i> Filter &amp; Sort
</h4>
<div class="card leaderboard-filter-card shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('leaderboard') }}" class="row g-3">
            <div class="col-lg-3 col-md-6">
                <label for="search" class="form-label fw-semibold">Search</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}"
                    placeholder="Name or username">
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="country_id" class="form-label fw-semibold">Country</label>
                <select name="country_id" id="country_id" class="form-select">
                    <option value="">All countries</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="institute_id" class="form-label fw-semibold">Institute</label>
                <select name="institute_id" id="institute_id" class="form-select">
                    <option value="">All institutes</option>
                    @foreach ($institutes as $institute)
                        <option value="{{ $institute->id }}" @selected((string) request('institute_id') === (string) $institute->id)>
                            {{ $institute->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="platform_id" class="form-label fw-semibold">Platform</label>
                <select name="platform_id" id="platform_id" class="form-select">
                    <option value="">All platforms</option>
                    @foreach ($platforms as $platform)
                        <option value="{{ $platform->id }}" @selected((string) request('platform_id') === (string) $platform->id)>
                            {{ $platform->display_name ?: ucfirst($platform->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label for="sort" class="form-label fw-semibold">Sort by</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="rating_desc" @selected($sort === 'rating_desc')>Rating high to low</option>
                    <option value="rating_asc" @selected($sort === 'rating_asc')>Rating low to high</option>
                </select>
            </div>

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary-gradient">
                    <i class="bi bi-funnel-fill"></i> Apply filters
                </button>
                <a href="{{ route('leaderboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>
<hr>
