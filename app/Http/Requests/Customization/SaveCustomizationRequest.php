<?php

namespace App\Http\Requests\Customization;

use Illuminate\Foundation\Http\FormRequest;

class SaveCustomizationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // Checkboxes: if not submitted they're absent — default to false
        $this->merge([
            'show_email'    => $this->boolean('show_email'),
            'show_username' => $this->boolean('show_username'),
            'show_bio'      => $this->boolean('show_bio'),
        ]);

        // visible_sections comes as array of strings from checkboxes
        if (!$this->has('visible_sections')) {
            $this->merge(['visible_sections' => []]);
        }

        // section_order comes as comma-separated string from hidden input
        if ($this->has('section_order') && is_string($this->section_order)) {
            $this->merge(['section_order' => array_filter(explode(',', $this->section_order))]);
        }
    }

    public function rules(): array
    {
        $fonts    = ['Roboto','Open Sans','Lato','Montserrat','Poppins','Raleway','Ubuntu','Nunito','Playfair Display','Merriweather'];
        $sections = ['project','experience','education','achievement','milestone','skill'];

        return [
            'theme'              => ['required', 'in:default,dark,light,professional,creative'],
            'layout'             => ['required', 'in:grid,list,timeline,compact,sidebar,cards,magazine'],
            'primary_color'      => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'       => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'heading_font'       => ['required', 'in:' . implode(',', $fonts)],
            'body_font'          => ['required', 'in:' . implode(',', $fonts)],
            'font_size'          => ['required', 'in:small,medium,large'],
            'spacing'            => ['required', 'in:compact,normal,relaxed'],
            'header_style'       => ['required', 'in:classic,centered,minimal,banner'],
            'visible_sections'   => ['nullable', 'array'],
            'visible_sections.*' => ['in:' . implode(',', $sections)],
            'section_order'      => ['nullable', 'array'],
            'section_order.*'    => ['in:' . implode(',', $sections)],
            'show_email'         => ['boolean'],
            'show_username'      => ['boolean'],
            'show_bio'           => ['boolean'],
        ];
    }
}
