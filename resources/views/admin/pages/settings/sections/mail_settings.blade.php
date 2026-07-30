<div class="content-header mb-4">
    <h6 class="mb-0">{{ __(ucwords(str_replace('_', ' ', $setting->key))) }}</h6>
    <small>{{ __('Configure your mail settings here.') }}</small>
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
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_driver">{{ __('Mail Driver') }}<span
                        class="text-danger">*</span></label>
                <select class="form-select" name="mail_driver" id="mail_driver" required>
                    <option value="smtp"
                        {{ old('mail_driver', $values['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>
                        SMTP</option>
                    <option value="sendmail"
                        {{ old('mail_driver', $values['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>
                        Sendmail</option>
                    <option value="mailgun"
                        {{ old('mail_driver', $values['mail_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>
                        Mailgun</option>
                    <option value="ses"
                        {{ old('mail_driver', $values['mail_driver'] ?? '') == 'ses' ? 'selected' : '' }}>
                        SES</option>
                    <option value="postmark"
                        {{ old('mail_driver', $values['mail_driver'] ?? '') == 'postmark' ? 'selected' : '' }}>
                        Postmark</option>
                    <option value="log"
                        {{ old('mail_driver', $values['mail_driver'] ?? '') == 'log' ? 'selected' : '' }}>
                        Log</option>
                </select>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_host">{{ __('Mail Host') }}<span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="{{ __('Enter mail host') }}" name="mail_host"
                    id="mail_host" value="{{ old('mail_host', $values['mail_host'] ?? '') }}" required />
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_port">{{ __('Mail Port') }}<span
                        class="text-danger">*</span></label>
                <input type="number" class="form-control" placeholder="{{ __('Enter mail port') }}" name="mail_port"
                    id="mail_port" value="{{ old('mail_port', $values['mail_port'] ?? '') }}" required />
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_username">{{ __('Mail Username') }}<span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="{{ __('Enter mail username') }}"
                    name="mail_username" id="mail_username"
                    value="{{ old('mail_username', $values['mail_username'] ?? '') }}" required />
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_password">{{ __('Mail Password') }}<span
                        class="text-danger">*</span></label>
                <input type="password" class="form-control" placeholder="{{ __('Enter mail password') }}"
                    name="mail_password" id="mail_password"
                    value="{{ old('mail_password', $values['mail_password'] ?? '') }}" required />
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_encryption">{{ __('Mail Encryption') }}<span
                        class="text-danger">*</span></label>
                <select class="form-select" name="mail_encryption" id="mail_encryption" required>
                    <option value=""
                        {{ old('mail_encryption', $values['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>
                        None</option>
                    <option value="tls"
                        {{ old('mail_encryption', $values['mail_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>
                        TLS</option>
                    <option value="ssl"
                        {{ old('mail_encryption', $values['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>
                        SSL</option>
                </select>
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_from_address">{{ __('Mail From Address') }}<span
                        class="text-danger">*</span></label>
                <input type="email" class="form-control" placeholder="{{ __('Enter mail from address') }}"
                    name="mail_from_address" id="mail_from_address"
                    value="{{ old('mail_from_address', $values['mail_from_address'] ?? '') }}" required />
            </div>
            <div class="col-md-6 form-control-validation">
                <label class="form-label" for="mail_from_name">{{ __('Mail From Name') }}<span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="{{ __('Enter mail from name') }}"
                    name="mail_from_name" id="mail_from_name"
                    value="{{ old('mail_from_name', $values['mail_from_name'] ?? '') }}" required />
            </div>
            <div class="col-12 form-control-validation d-flex justify-content-end gap-2">
                <x-form-action-button :resource="'settings'" :action="'edit'" :type="'page'" />
            </div>
        </form>
    </div>
</div>
<div class="content-header mb-4">
    <h6 class="mb-0">{{ __('Send Test Mail') }}</h6>
    <small>{{ __('Send a test email to verify your mail settings.') }}</small>
</div>
<div class="row g-6">
    <div class="col-12">
        <form class="row g-6 common-form" action="{{ route('test.mail') }}" method="POST">
            @csrf
            <div class="col-md-12 form-control-validation">
                <label class="form-label" for="test_email">{{ __('Test Email Address') }}<span
                        class="text-danger">*</span></label>
                <input type="email" class="form-control" placeholder="{{ __('Enter test email address') }}"
                    name="test_email" id="test_email" required />
            </div>
            <div class="col-12 form-control-validation d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="icon-base ti tabler-mail icon-xs me-2"></i>
                    {{ __('Send Test Mail') }}
                </button>
            </div>
        </form>
    </div>
</div>
