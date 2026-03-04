@extends('admin.layouts.app')
@section('title', __('Problems'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Problems') }}</h5>
        </div>
        <div class="card-datatable">
            <table class="common-datatable table d-table" data-url="{{ route('all-problems.index') }}"
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
@endsection
