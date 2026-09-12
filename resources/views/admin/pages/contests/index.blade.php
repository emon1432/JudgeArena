@extends('admin.layouts.app')
@section('title', __('Contests'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Contests') }}</h5>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table" data-url="{{ route('admin.all-contests.index') }}"
                    data-columns='[
                { "data": "name" },
                { "data": "platformName", "className": "text-center" },
                { "data": "phase", "className": "text-center" },
                { "data": "startAt", "className": "text-center" },
                { "data": "standingsCache", "orderable": false, "className": "text-center" },
                { "data": "status", "className": "text-center" },
                { "data": "actions", "orderable": false, "className": "text-center" }
                ]'>
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th class="text-center">{{ __('Platform') }}</th>
                            <th class="text-center">{{ __('Phase') }}</th>
                            <th class="text-center">{{ __('Start At') }}</th>
                            <th class="text-center">{{ __('Standings') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('admin.pages.contests.scripts')
@endpush
