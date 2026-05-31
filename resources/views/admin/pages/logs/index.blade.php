@extends('admin.layouts.app')
@section('title', __('Application Logs'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Application Logs') }}</h5>
        </div>
        <div class="card-body border-bottom mt-5">
            <form method="GET" action="{{ route('logs.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Message, source, category, platform, entity') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Level') }}</label>
                    <select name="level" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach (['info', 'warning', 'error', 'critical'] as $logLevel)
                            <option value="{{ $logLevel }}" @selected(request('level') === $logLevel)>{{ ucfirst($logLevel) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Category') }}</label>
                    <select name="category" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($categories as $logCategory)
                            <option value="{{ $logCategory }}" @selected(request('category') === $logCategory)>{{ ucfirst($logCategory) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Platform') }}</label>
                    <select name="platform" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($platforms as $logPlatform)
                            <option value="{{ $logPlatform }}" @selected(request('platform') === $logPlatform)>{{ ucfirst($logPlatform) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Entity Type') }}</label>
                    <select name="entity_type" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($entityTypes as $entityType)
                            <option value="{{ $entityType }}" @selected(request('entity_type') === $entityType)>{{ ucfirst($entityType) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('From') }}</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Entity') }}</label>
                    <input type="text" name="entity_id" value="{{ request('entity_id') }}" class="form-control" placeholder="{{ __('ID') }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('logs.index') }}" class="btn btn-label-secondary">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table"
                    data-url="{{ route('logs.index', request()->query()) }}"
                    data-order='[[0, "desc"]]'
                    data-columns='[
                { "data": "createdAt" },
                { "data": "level" },
                { "data": "category" },
                { "data": "platform" },
                { "data": "entity" },
                { "data": "source" },
                { "data": "message" },
                { "data": "userName" },
                { "data": "actions" }
                ]'>
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('Created At') }}</th>
                            <th>{{ __('Level') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Platform') }}</th>
                            <th>{{ __('Entity') }}</th>
                            <th>{{ __('Source') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
