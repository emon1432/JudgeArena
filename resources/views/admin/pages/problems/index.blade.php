@extends('admin.layouts.app')
@section('title', __('Problems'))
@section('content')
    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="icon-base ti tabler-filter icon-sm text-primary"></i>
                <h6 class="card-title mb-0 fw-semibold">{{ __('Filter Problems') }}</h6>
            </div>
            <button type="button" class="btn btn-sm btn-label-secondary dt-filter-reset">
                <i class="icon-base ti tabler-rotate-clockwise icon-xs me-1"></i>
                {{ __('Reset Filters') }}
            </button>
        </div>
        <div class="card-body pt-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label fw-medium">{{ __('Platform') }}</label>
                    <select class="form-select" data-dt-filter="platform">
                        <option value="">{{ __('All Platforms') }}</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label fw-medium">{{ __('Rating Range') }}</label>
                    <select class="form-select" data-dt-filter="rating_range">
                        <option value="">{{ __('All Ratings') }}</option>
                        <option value="800-1199">800 - 1199 (Beginner)</option>
                        <option value="1200-1599">1200 - 1599 (Intermediate)</option>
                        <option value="1600-1999">1600 - 1999 (Advanced)</option>
                        <option value="2000-2399">2000 - 2399 (Expert)</option>
                        <option value="2400+">2400+ (Master)</option>
                        <option value="unrated">{{ __('Unrated') }}</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label fw-medium">{{ __('Status') }}</label>
                    <select class="form-select" data-dt-filter="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Datatable Card --}}
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Problems') }}</h5>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table" data-url="{{ route('admin.all-problems.index') }}"
                    data-columns='[
                { "data": "name" },
                { "data": "platformName" },
                { "data": "difficultyRating" },
                { "data": "contestName" },
                { "data": "actions" }
                ]'>
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Platform') }}</th>
                            <th>{{ __('Difficulty / Rating') }}</th>
                            <th>{{ __('Contest') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
