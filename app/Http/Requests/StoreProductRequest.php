<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'            => ['required', 'string', 'max:64', 'unique:products,sku'],
            'name'           => ['required', 'string', 'max:191'],
            'slug'           => ['required', 'string', 'max:191', 'unique:products,slug'],
            'description'    => ['nullable', 'string'],
            'base_price_net' => ['required', 'numeric', 'min:0'],
            'stock_qty'      => ['required', 'integer', 'min:0'],
            'is_active'      => ['boolean'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
