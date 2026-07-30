<div class="content-header mb-4">
    <h6 class="mb-0">{{ __(ucwords(str_replace('_', ' ', $setting->key))) }}</h6>
    <small>{{ __('Configure core application behavior.') }}</small>
</div>

<div class="row g-6">
    <div class="col-12">
        <form class="row g-6 common-form" action="{{ route('admin.settings.update', $setting->key) }}" method="POST">
            @csrf
            @method('PUT')
            @php
                $values = json_decode($setting->value, true);
            @endphp

            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="app_name">{{ __('App Name') }}<span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="app_name" id="app_name"
                    value="{{ old('app_name', $values['app_name'] ?? '') }}" required>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="app_url">{{ __('App URL') }}<span class="text-danger">*</span></label>
                <input type="url" class="form-control" name="app_url" id="app_url"
                    value="{{ old('app_url', $values['app_url'] ?? '') }}" required>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="app_locale">{{ __('App Locale') }}<span
                        class="text-danger">*</span></label>
                <select name="app_locale" id="app_locale" class="form-select" required>
                    <option value="en" {{ ($values['app_locale'] ?? '') === 'en' ? 'selected' : '' }}>English
                    </option>
                    <option value="bn" {{ ($values['app_locale'] ?? '') === 'bn' ? 'selected' : '' }}>Bangla
                    </option>
                </select>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="app_timezone">{{ __('Timezone') }}<span
                        class="text-danger">*</span></label>
                <select name="app_timezone" id="app_timezone" class="form-select" required>
                    @foreach (timezone_identifiers_list() as $timezone)
                        <option value="{{ $timezone }}"
                            {{ ($values['app_timezone'] ?? '') === $timezone ? 'selected' : '' }}>
                            {{ $timezone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="date_format">{{ __('Date Format') }}<span
                        class="text-danger">*</span></label>
                <select name="date_format" id="date_format" class="form-select" required>
                    <option value="Y-m-d" {{ ($values['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>Y-m-d
                        ({{ date('Y-m-d') }})</option>
                    <option value="d-m-Y" {{ ($values['date_format'] ?? '') === 'd-m-Y' ? 'selected' : '' }}>d-m-Y
                        ({{ date('d-m-Y') }})</option>
                    <option value="m-d-Y" {{ ($values['date_format'] ?? '') === 'm-d-Y' ? 'selected' : '' }}>m-d-Y
                        ({{ date('m-d-Y') }})</option>
                    <option value="m/d/Y" {{ ($values['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' }}>m/d/Y
                        ({{ date('m/d/Y') }})</option>
                    <option value="d/m/Y" {{ ($values['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' }}>d/m/Y
                        ({{ date('d/m/Y') }})</option>
                    <option value="Y/m/d" {{ ($values['date_format'] ?? '') === 'Y/m/d' ? 'selected' : '' }}>Y/m/d
                        ({{ date('Y/m/d') }})</option>
                    <option value="d.M.Y" {{ ($values['date_format'] ?? '') === 'd.M.Y' ? 'selected' : '' }}>d.M.Y
                        ({{ date('d.M.Y') }})</option>
                    <option value="M d, Y" {{ ($values['date_format'] ?? '') === 'M d, Y' ? 'selected' : '' }}>M d, Y
                        ({{ date('M d, Y') }})</option>
                    <option value="d M Y" {{ ($values['date_format'] ?? '') === 'd M Y' ? 'selected' : '' }}>d M Y
                        ({{ date('d M Y') }})</option>
                    <option value="D, d M Y" {{ ($values['date_format'] ?? '') === 'D, d M Y' ? 'selected' : '' }}>D, d
                        M Y ({{ date('D, d M Y') }})</option>
                </select>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="time_format">{{ __('Time Format') }}<span
                        class="text-danger">*</span></label>
                <select name="time_format" id="time_format" class="form-select" required>
                    <option value="H:i" {{ ($values['time_format'] ?? '') === 'H:i' ? 'selected' : '' }}>24 Hour ({{ date('H:i') }})
                    </option>
                    <option value="h:i A" {{ ($values['time_format'] ?? '') === 'h:i A' ? 'selected' : '' }}>12 Hour ({{ date('h:i A') }})
                    </option>
                </select>
            </div>
            <div class="col-md-4 form-control-validation">
                <label class="form-label" for="decimal_separator">{{ __('Decimal Separator') }}</label>
                <select name="decimal_separator" id="decimal_separator" class="form-select">
                    <option value="." {{ ($values['decimal_separator'] ?? '') === '.' ? 'selected' : '' }}>Dot (.)
                    </option>
                    <option value="," {{ ($values['decimal_separator'] ?? '') === ',' ? 'selected' : '' }}>Comma
                        (,)</option>
                    <option value=" " {{ ($values['decimal_separator'] ?? '') === ' ' ? 'selected' : '' }}>Space
                        ( )</option>
                </select>
            </div>
            <div class="col-md-4 form-control-validation">
                <label class="form-label" for="thousand_separator">{{ __('Thousand Separator') }}</label>
                <select name="thousand_separator" id="thousand_separator" class="form-select">
                    <option value="." {{ ($values['thousand_separator'] ?? '') === '.' ? 'selected' : '' }}>Dot
                        (.)
                    </option>
                    <option value="," {{ ($values['thousand_separator'] ?? '') === ',' ? 'selected' : '' }}>Comma
                        (,)</option>
                    <option value=" " {{ ($values['thousand_separator'] ?? '') === ' ' ? 'selected' : '' }}>Space
                        ( )</option>
                </select>
            </div>
            <div class="col-md-4 form-control-validation">
                <label class="form-label" for="decimal_precision">{{ __('Decimal Precision') }}</label>
                <select name="decimal_precision" id="decimal_precision" class="form-select">
                    <option value="2" {{ ($values['decimal_precision'] ?? '') == 2 ? 'selected' : '' }}>2
                        {{ __('Digits') }}</option>
                    <option value="3" {{ ($values['decimal_precision'] ?? '') == 3 ? 'selected' : '' }}>3
                        {{ __('Digits') }}</option>
                </select>
            </div>
            <div class="col-md-12 form-control-validation">
                <label class="form-label" for="footer_text">{{ __('Footer Text') }}</label>
                <input type="text" class="form-control" name="footer_text" id="footer_text"
                    value="{{ old('footer_text', $values['footer_text'] ?? '') }}">
            </div>
            <div class="col-md-12 form-control-validation">
                <label class="form-label" for="copyright">{{ __('Copyright') }}</label>
                <input type="text" class="form-control" name="copyright" id="copyright"
                    value="{{ old('copyright', $values['copyright'] ?? '') }}">
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <x-form-action-button :resource="'settings'" :action="'edit'" :type="'page'" />
            </div>
        </form>
    </div>
</div>
