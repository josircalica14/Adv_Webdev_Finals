<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name'         => ['required', 'string', 'max:255'],
            'bio'               => ['nullable', 'string', 'max:2000'],
            'program'           => ['required', 'in:BSIT,CSE'],
            'contact_info'      => ['nullable', 'array'],
            'contact_info.*'    => ['nullable', 'url', 'max:500'],
        ];
    }
}
