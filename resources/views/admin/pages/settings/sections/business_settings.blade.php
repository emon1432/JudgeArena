<div class="content-header mb-4">
    <h6 class="mb-0">{{ __(ucwords(str_replace('_', ' ', $setting->key))) }}</h6>
    <small>{{ __('Update your company details here.') }}</small>
</div>
<div class="row g-6">
    <div class="col-12">
        <form class="row g-6 common-form" action="{{ route('admin.settings.update', $setting->key) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @php
                $values = json_decode($setting->value, true);
            @endphp

            <div class="col-md-12 form-control-validation">
                <label class="form-label" for="company_name">{{ __('Company Name') }}<span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="{{ __('Enter Company Name') }}"
                    name="company_name" id="company_name"
                    value="{{ old('company_name', $values['company_name'] ?? '') }}" required />
            </div>

            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="email">{{ __('Email') }}<span class="text-danger">*</span></label>
                <input type="email" class="form-control" placeholder="{{ __('Enter email') }}" name="email"
                    id="email" value="{{ old('email', $values['email'] ?? '') }}" required />
            </div>

            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="phone">{{ __('Phone') }}<span class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="{{ __('Enter phone') }}" name="phone"
                    id="phone" value="{{ old('phone', $values['phone'] ?? '') }}" required />
            </div>

            <div class="col-md-12 form-control-validation">
                <label class="form-label" for="address">{{ __('Address') }}</label>
                <textarea class="form-control" placeholder="{{ __('Enter address') }}" name="address" id="address" rows="2">{{ old('address', $values['address'] ?? '') }}</textarea>
            </div>
            <div class="col-md-5 form-control-validation align-self-center">
                <label class="form-label" for="logo">{{ __('Logo') }}</label>
                <input type="file" name="logo" id="logo" class="form-control" accept="image/*"
                    onchange="document.getElementById('logo_preview').src = window.URL.createObjectURL(this.files[0])" />
            </div>
            <div class="col-md-1 form-control-validation">
                <label class="form-label" for="logo_preview">{{ __('Logo Preview') }}</label>
                <div class="image-preview">
                    <img id="logo_preview" src="{{ imageShow($values['logo'] ?? null) }}" class="img-fluid rounded"
                        alt="{{ __('Logo Preview') }}" />
                </div>
            </div>

            <div class="col-md-5 form-control-validation align-self-center">
                <label class="form-label" for="favicon">{{ __('Favicon') }}</label>
                <input type="file" name="favicon" id="favicon" class="form-control"
                    placeholder="{{ __('Upload favicon') }}" accept="image/*"
                    onchange="document.getElementById('favicon_preview').src = window.URL.createObjectURL(this.files[0])" />
            </div>
            <div class="col-md-1 form-control-validation">
                <label class="form-label" for="favicon_preview">{{ __('Favicon Preview') }}</label>
                <div class="image-preview">
                    <img id="favicon_preview" src="{{ imageShow($values['favicon'] ?? null) }}"
                        class="img-fluid rounded" alt="{{ __('Favicon Preview') }}" />
                </div>
            </div>

            <div class="col-12 form-control-validation d-flex justify-content-end gap-2">
                <x-form-action-button :resource="'settings'" :action="'edit'" :type="'page'" />
            </div>
        </form>
    </div>
</div>
