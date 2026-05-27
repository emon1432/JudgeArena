<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => slugify($this->name)
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('platform')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('platforms', 'name')->ignore($id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('platforms', 'slug')->ignore($id)],
            'short_name' => ['required', 'string', 'max:50'],
            'base_url' => ['required', 'url', 'max:255', Rule::unique('platforms', 'base_url')->ignore($id)],
            'status' => ['required', 'in:Active,Inactive,Maintenance,Coming Soon'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
            'credential_keys' => ['nullable', 'array',],
            'credential_keys.*' => ['nullable', 'string', 'max:255', 'distinct',],
            'credential_values' => ['nullable', 'array',],
            'credential_values.*' => ['nullable', 'string',],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the platform name.',
            'name.unique' => 'This platform name is already in use.',
            'slug.required' => 'Please enter the platform name to generate slug.',
            'slug.unique' => 'This slug is already in use.',
            'short_name.required' => 'Please enter the platform short name.',
            'base_url.required' => 'Please enter the base URL.',
            'base_url.url' => 'Please enter a valid URL.',
            'base_url.unique' => 'This base URL is already in use.',
            'status.required' => 'Please select the platform status.',
            'status.in' => 'Selected status is invalid.',
            'icon.image' => 'The uploaded file must be an image.',
            'icon.max' => 'The image size must not exceed 2MB.',
            'credential_keys.array' => 'The credential keys must be an array.',
            'credential_keys.*.string' => 'Each credential key must be a string.',
            'credential_keys.*.max' => 'Each credential key must not exceed 255 characters.',
            'credential_values.array' => 'The credential values must be an array.',
            'credential_values.*.string' => 'Each credential value must be a string.',
            'credential_values.*.max' => 'Each credential value must not exceed 255 characters.',
        ];
    }
}
