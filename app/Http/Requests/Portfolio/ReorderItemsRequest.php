<?php

namespace App\Http\Requests\Portfolio;

use Illuminate\Foundation\Http\FormRequest;

class ReorderItemsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'item_ids'   => ['required', 'array'],
            'item_ids.*' => ['integer', 'exists:portfolio_items,id'],
        ];
    }
}
