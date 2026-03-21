<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:users,username'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'program'   => ['required', 'in:BSIT,CSE'],
            'password'  => ['required', 'string', 'min:8', 'confirmed',
                            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ];
    }
}
