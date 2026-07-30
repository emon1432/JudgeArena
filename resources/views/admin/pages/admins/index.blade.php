@extends('admin.layouts.app')
@section('title', __('Admins List'))
@section('content')
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Admins') }}</h5>
            <a class="btn add-new btn-primary" href="{{ route('admin.admins.create') }}">
                <span class="d-flex align-items-center gap-2 text-white">
                    <i class="icon-base ti tabler-plus icon-xs"></i>
                    {{ __('Add New Record') }}
                </span>
            </a>
        </div>
        <div class="card-body">
            <div class="card-datatable">
                <table class="common-datatable table d-table" data-url="{{ route('admin.admins.index') }}"
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
