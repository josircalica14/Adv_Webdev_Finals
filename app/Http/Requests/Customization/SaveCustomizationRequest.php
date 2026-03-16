<?php

namespace App\Http\Requests\Customization;

use Illuminate\Foundation\Http\FormRequest;

class SaveCustomizationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $fonts = ['Roboto','Open Sans','Lato','Montserrat','Poppins','Raleway','Ubuntu','Nunito','Playfair Display','Merriweather'];

        return [
            'theme'         => ['required', 'in:default,dark,light,professional,creative'],
            'layout'        => ['required', 'in:grid,list,timeline'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'  => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'heading_font'  => ['required', 'in:' . implode(',', $fonts)],
            'body_font'     => ['required', 'in:' . implode(',', $fonts)],
        ];
    }
}
