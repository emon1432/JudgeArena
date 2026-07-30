@extends('admin.layouts.app')
@section('title', __('Contact Messages'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Contact Messages') }}</h5>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table" data-url="{{ route('admin.contact-messages.index') }}"
                    data-order='[[4, "desc"]]'
                    data-columns='[
                { "data": "name" },
                { "data": "email" },
                { "data": "subject" },
                { "data": "status" },
                { "data": "received_at" },
                { "data": "actions" }
                ]'>
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Received At') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
