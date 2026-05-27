@extends('admin.layouts.app')
@section('title', __('Platforms'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Platforms') }}</h5>
                    <a class="btn add-new btn-primary" href="{{ route('platforms.create') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-plus icon-xs"></i>
                            {{ __('Add New Record') }}
                        </span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="card-datatable">
                        <table class="common-datatable table d-table" data-url="{{ route('platforms.index') }}"
                            data-columns='[
                                            { "data": "info" },
                                            { "data": "base_url" },
                                            { "data": "status" },
                                            { "data": "actions" }
                                        ]'>
                            <thead class="border-top">
                                <tr>
                                    <th>{{ __('Info') }}</th>
                                    <th>{{ __('Base URL') }}</th>
                                    <th>{{ __('Status') }}</th>
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
