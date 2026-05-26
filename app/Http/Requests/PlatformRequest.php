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

    public function rules(): array
    {
        $id = $this->route('platform')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('platforms', 'name')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:255', Rule::unique('platforms', 'base_url')->ignore($id)],
            'status' => ['required', 'in:Active,Inactive'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the platform name.',
            'name.unique' => 'This platform name is already in use.',
            'name.required' => 'Please enter the display name.',
            'base_url.required' => 'Please enter the base URL.',
            'base_url.url' => 'Please enter a valid URL.',
            'base_url.unique' => 'This base URL is already in use.',
            'status.required' => 'Please select the platform status.',
            'status.in' => 'Selected status is invalid.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
