<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductCompositionComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'component_stock_item_id' => ['required', 'integer', 'exists:stock_items,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }
}
