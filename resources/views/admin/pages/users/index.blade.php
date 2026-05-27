@extends('admin.layouts.app')
@section('title', __('Users'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Users') }}</h5>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table" data-url="{{ route('users.index') }}"
                    data-columns='[
                { "data": "name" },
                { "data": "email" },
                { "data": "phone" },
                { "data": "actions" }
                ]'>
                    <thead class="border-top">
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
