@extends('admin.layouts.app')
@section('title', __('Platform Sync Jobs'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Platform Sync Jobs') }}</h5>
                </div>
                <div class="card-body">
                    <div class="card-datatable">
                        <table class="common-datatable table d-table" data-url="{{ route('admin.platform-sync-jobs.index') }}"
                            data-columns='[
                                            { "data": "platform_info", "name": "platform_id" },
                                            { "data": "entity_formatted", "name": "entity" },
                                            { "data": "priority", "name": "priority" },
                                            { "data": "interval_minutes", "name": "interval_minutes" },
                                            { "data": "enabled_status", "name": "enabled" },
                                            { "data": "last_started", "name": "last_started_at" },
                                            { "data": "last_success", "name": "last_success_at" },
                                            { "data": "last_failed", "name": "last_failed_at" },
                                            { "data": "next_run", "name": "last_success_at", "orderable": false },
                                            { "data": "actions", "orderable": false }
                                        ]'>
                            <thead class="border-top">
                                <tr>
                                    <th>{{ __('Platform') }}</th>
                                    <th>{{ __('Entity') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Interval (Mins)') }}</th>
                                    <th>{{ __('Enabled') }}</th>
                                    <th>{{ __('Last Started') }}</th>
                                    <th>{{ __('Last Success') }}</th>
                                    <th>{{ __('Last Failed') }}</th>
                                    <th>{{ __('Next Run') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
