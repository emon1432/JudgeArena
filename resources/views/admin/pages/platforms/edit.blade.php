@extends('admin.layouts.app')
@section('title', __('Edit Platform'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Edit Platform') }}</h5>
                    <a class="btn add-new btn-primary" href="{{ route('admin.platforms.index') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-arrow-back-up icon-xs"></i>
                            {{ __('Back to Platform List') }}
                        </span>
                    </a>
                </div>
                <div class="card-body">
                    <form class="row g-6 common-form" action="{{ route('admin.platforms.update', $platform->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <h6>{{ __('Platform Information') }}</h6>
                            <hr class="mt-0" />
                        </div>
                        <div class="col-md-8 form-control-validation">
                            <label class="form-label" for="name">{{ __('Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="{{ __('Enter name') }}" value="{{ old('name', $platform->name) }}" required />
                        </div>
                        <div class="col-md-4 form-control-validation">
                            <label class="form-label" for="short_name">{{ __('Short Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="short_name" id="short_name" class="form-control"
                                placeholder="{{ __('Enter short name') }}"
                                value="{{ old('short_name', $platform->short_name) }}" required />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="base_url">{{ __('Base URL') }}<span
                                    class="text-danger">*</span></label>
                            <input type="url" name="base_url" id="base_url" class="form-control"
                                placeholder="{{ __('Enter base URL') }}"
                                value="{{ old('base_url', $platform->base_url) }}" required />
                        </div>

                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="profile_url">{{ __('Profile URL') }}</label>
                            <input type="url" name="profile_url" id="profile_url" class="form-control"
                                placeholder="{{ __('Enter profile URL') }}"
                                value="{{ old('profile_url', $platform->profile_url) }}" />
                        </div>
                        <div class="col-md-5 form-control-validation align-self-center">
                            <label class="form-label" for="icon">{{ __('Icon') }}</label>
                            <input type="file" name="icon" id="icon" class="form-control"
                                placeholder="{{ __('Upload icon') }}" accept="image/*"
                                onchange="document.getElementById('icon_preview').src = window.URL.createObjectURL(this.files[0])" />
                        </div>
                        <div class="col-md-1 form-control-validation">
                            <label class="form-label" for="icon_preview">{{ __('Icon Preview') }}</label>
                            <div class="icon-preview">
                                <img id="icon_preview" src="{{ imageShow($platform->icon) }}" class="img-fluid rounded"
                                    alt="{{ __('Icon Preview') }}" />
                            </div>
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="status">{{ __('Status') }}<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="Active"
                                    {{ old('status', $platform->status) === 'Active' ? 'selected' : '' }}>
                                    {{ __('Active') }}</option>
                                <option value="Inactive"
                                    {{ old('status', $platform->status) === 'Inactive' ? 'selected' : '' }}>
                                    {{ __('Inactive') }}</option>
                                <option value="Maintenance"
                                    {{ old('status', $platform->status) === 'Maintenance' ? 'selected' : '' }}>
                                    {{ __('Maintenance') }}</option>
                                <option value="Coming Soon"
                                    {{ old('status', $platform->status) === 'Coming Soon' ? 'selected' : '' }}>
                                    {{ __('Coming Soon') }}</option>
                            </select>
                        </div>
                        <div class="col-12 form-control-validation">
                            <label class="form-label" for="description">{{ __('Description') }}</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                placeholder="{{ __('Enter description') }}">{{ old('description', $platform->description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>{{ __('Credentials') }}</h6>

                                <button type="button" class="btn btn-sm btn-primary" id="add-credential">
                                    {{ __('Add Credential') }}
                                </button>
                            </div>
                            <div id="credentials-wrapper">
                                @php
                                    $credentials = old('credential_keys')
                                        ? array_map(null, old('credential_keys', []), old('credential_values', []))
                                        : [];
                                    if (empty($credentials)) {
                                        $stored = $platform->credentials ?? [];
                                        foreach ($stored as $k => $v) {
                                            $credentials[] = [$k, $v];
                                        }
                                    }
                                @endphp
                                @if (count($credentials) > 0)
                                    @foreach ($credentials as $item)
                                        <div class="row g-2 credential-item mb-2">
                                            <div class="col-md-4">
                                                <input type="text" name="credential_keys[]" class="form-control"
                                                    value="{{ $item[0] ?? '' }}" placeholder="Key (example: api_key)">
                                            </div>

                                            <div class="col-md-6">
                                                <input type="text" name="credential_values[]" class="form-control"
                                                    value="{{ $item[1] ?? '' }}" placeholder="Value">
                                            </div>

                                            <div class="col-md-2">
                                                <button type="button"
                                                    class="btn btn-danger remove-credential w-100">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row g-2 credential-item mb-2">
                                        <div class="col-md-4">
                                            <input type="text" name="credential_keys[]" class="form-control"
                                                placeholder="Key (example: api_key)">
                                        </div>

                                        <div class="col-md-6">
                                            <input type="text" name="credential_values[]" class="form-control"
                                                placeholder="Value">
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button"
                                                class="btn btn-danger remove-credential w-100">Remove</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 form-control-validation">
                            <x-form-action-button :resource="'platforms'" :action="'edit'" :type="'page'" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#add-credential').click(function() {
                $('#credentials-wrapper').append(`
                    <div class="row g-2 credential-item mb-2">
                        <div class="col-md-4">
                            <input type="text" name="credential_keys[]" class="form-control" placeholder="Key (example: api_key)">
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="credential_values[]" class="form-control" placeholder="Value">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-credential w-100">Remove</button>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.remove-credential', function() {
                $(this).closest('.credential-item').remove();
            });
        });
    </script>
@endpush
