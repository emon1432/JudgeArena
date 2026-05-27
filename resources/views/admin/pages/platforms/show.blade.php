@extends('admin.layouts.app')
@section('title', $platform->name . ' | ' . __('Platform Details'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $platform->name }}</h5>
                    <a class="btn add-new btn-primary" href="{{ route('platforms.index') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-arrow-back-up icon-xs"></i>
                            {{ __('Back to Platform List') }}
                        </span>
                    </a>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>
@endsection
