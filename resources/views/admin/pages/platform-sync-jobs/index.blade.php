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
                            data-group-column="0"
                            data-page-length="6"
                            data-length-menu='[6, 12, 24, 48, 96]'
                            data-columns='[
                                            { "data": "platform_info", "name": "platform_id", "visible": false },
                                            { "data": "entity_formatted", "name": "entity" },
                                            { "data": "priority", "name": "priority", "className": "text-center" },
                                            { "data": "interval_minutes", "name": "interval_minutes", "className": "text-center" },
                                            { "data": "enabled_status", "name": "enabled", "className": "text-center" },
                                            { "data": "last_started", "name": "last_started_at" },
                                            { "data": "last_success", "name": "last_success_at" },
                                            { "data": "last_failed", "name": "last_failed_at" },
                                            { "data": "next_run", "name": "last_success_at", "orderable": false },
                                            { "data": "actions", "orderable": false, "className": "text-center" }
                                        ]'>
                            <thead class="border-top">
                                <tr>
                                    <th>{{ __('Platform') }}</th>
                                    <th>{{ __('Entity') }}</th>
                                    <th class="text-center">{{ __('Priority') }}</th>
                                    <th class="text-center">{{ __('Interval (Mins)') }}</th>
                                    <th class="text-center">{{ __('Enabled') }}</th>
                                    <th>{{ __('Last Started') }}</th>
                                    <th>{{ __('Last Success') }}</th>
                                    <th>{{ __('Last Failed') }}</th>
                                    <th>{{ __('Next Run') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
