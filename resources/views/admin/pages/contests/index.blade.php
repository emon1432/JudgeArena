@extends('admin.layouts.app')
@section('title', __('Contests'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Contests') }}</h5>
        </div>
        <div class="card-datatable">
            <table class="common-datatable table d-table" data-url="{{ route('all-contests.index') }}"
                data-columns='[
                { "data": "name" },
                { "data": "platformName" },
                { "data": "phase" },
                { "data": "startAt" },
                { "data": "status" },
                { "data": "actions" }
                ]'>
                <thead class="border-top">
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Platform') }}</th>
                        <th>{{ __('Phase') }}</th>
                        <th>{{ __('Start At') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
