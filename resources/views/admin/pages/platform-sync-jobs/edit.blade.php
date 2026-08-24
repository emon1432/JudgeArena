@extends('admin.layouts.app')
@section('title', __('Edit Platform Sync Job'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Edit Platform Sync Job') }}</h5>
                    <a class="btn add-new btn-primary" href="{{ route('admin.platform-sync-jobs.index') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-arrow-back-up icon-xs"></i>
                            {{ __('Back to Sync Jobs') }}
                        </span>
                    </a>
                </div>
                <div class="card-body">
                    <form class="row g-6 common-form" action="{{ route('admin.platform-sync-jobs.update', $platformSyncJob->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <h6>{{ __('Job Information') }} ({{ $platformSyncJob->platform->name }} - {{ ucfirst(str_replace('_', ' ', $platformSyncJob->entity->value)) }})</h6>
                            <hr class="mt-0" />
                        </div>
                        
                        <div class="col-md-4 form-control-validation">
                            <label class="form-label" for="priority">{{ __('Priority') }}<span class="text-danger">*</span></label>
                            <input type="number" name="priority" id="priority" class="form-control"
                                placeholder="{{ __('Enter priority (e.g. 100)') }}" value="{{ old('priority', $platformSyncJob->priority) }}" required min="0" max="255" />
                        </div>
                        
                        <div class="col-md-4 form-control-validation">
                            <label class="form-label" for="interval_minutes">{{ __('Interval (Minutes)') }}<span class="text-danger">*</span></label>
                            <input type="number" name="interval_minutes" id="interval_minutes" class="form-control"
                                placeholder="{{ __('Enter interval in minutes') }}" value="{{ old('interval_minutes', $platformSyncJob->interval_minutes) }}" required min="1" />
                        </div>
                        
                        <div class="col-md-4 form-control-validation">
                            <label class="form-label" for="enabled">{{ __('Enabled') }}<span class="text-danger">*</span></label>
                            <select class="form-select" name="enabled" id="enabled" required>
                                <option value="1" {{ old('enabled', $platformSyncJob->enabled) == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                <option value="0" {{ old('enabled', $platformSyncJob->enabled) == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                            </select>
                        </div>

                        <div class="col-12 form-control-validation">
                            <x-form-action-button :resource="'platform-sync-jobs'" :action="'edit'" :type="'page'" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
