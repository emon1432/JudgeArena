@extends('admin.layouts.app')
@section('title', __('Create New Admin'))
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('Create New Admin') }}</h5>
                    <a class="btn add-new btn-primary" href="{{ route('admin.admins.index') }}">
                        <span class="d-flex align-items-center gap-2 text-white">
                            <i class="icon-base ti tabler-arrow-back-up icon-xs"></i>
                            {{ __('Back to Admin List') }}
                        </span>
                    </a>
                </div>
                <div class="card-body">
                    <form class="row g-6 common-form" action="{{ route('admin.admins.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="col-12">
                            <h6>{{ __('Admin Information') }}</h6>
                            <hr class="mt-0" />
                        </div>
                        <div class="col-md-12 form-control-validation">
                            <label class="form-label" for="name">{{ __('Full Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="{{ __('Enter name') }}" required />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="username">{{ __('Username') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="{{ __('Enter username') }}" required />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="email">{{ __('Email') }}<span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="{{ __('Enter email') }}" required />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="phone">{{ __('Phone') }}</label>
                            <input type="text" name="phone" id="phone" class="form-control"
                                placeholder="{{ __('Enter phone number') }}" />
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="role">{{ __('Role') }}<span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="role" required>
                                <option value="admin">{{ __('Admin') }}</option>
                            </select>
                        </div>
                        <div class="col-md-5 form-control-validation align-self-center">
                            <label class="form-label" for="image">{{ __('Profile Image') }}</label>
                            <input type="file" name="image" id="image" class="form-control"
                                placeholder="{{ __('Upload profile image') }}" accept="image/*"
                                onchange="document.getElementById('image_preview').src = window.URL.createObjectURL(this.files[0])" />
                        </div>
                        <div class="col-md-1 form-control-validation">
                            <label class="form-label" for="image_preview">{{ __('Image Preview') }}</label>
                            <div class="image-preview">
                                <img id="image_preview" src="{{ asset('uploads/default.jpg') }}" class="img-fluid rounded"
                                    alt="{{ __('Image Preview') }}" />
                            </div>
                        </div>
                        <div class="col-md-6 form-control-validation">
                            <label class="form-label" for="address">{{ __('Address') }}</label>
                            <textarea name="address" id="address" class="form-control" rows="3" placeholder="{{ __('Enter address') }}"></textarea>
                        </div>
                        <div class="col-12">
                            <h6>{{ __('Password') }}</h6>
                            <hr class="mt-0" />
                        </div>
                        <div class="col-md-6 form-password-toggle form-control-validation">
                            <label class="form-label" for="password">{{ __('Password') }}</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password"
                                    placeholder="{{ __('Enter password') }}" name="password" required
                                    autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer">
                                    <i class="icon-base ti tabler-eye-off"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 form-password-toggle form-control-validation">
                            <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}<span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="{{ __('Confirm password') }}" required
                                    autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer">
                                    <i class="icon-base ti tabler-eye-off"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-12 form-control-validation">
                            <x-form-action-button :resource="'admins'" :action="'create'" :type="'page'" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
