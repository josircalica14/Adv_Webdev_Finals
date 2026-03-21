<?php

namespace App\Http\Requests\Portfolio;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('tags_input') && is_string($this->tags_input)) {
            $this->merge([
                'tags' => array_filter(array_map('trim', explode(',', $this->tags_input))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'item_type'   => ['required', 'in:project,achievement,milestone,skill,experience,education'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'item_date'   => ['nullable', 'date'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['string', 'max:50'],
            'links'       => ['nullable', 'array'],
            'links.*.url' => ['required_with:links', 'url'],
            'links.*.label' => ['required_with:links', 'string', 'max:100'],
            'image'       => ['nullable', 'image', 'max:10240'],
        ];
    }
}
