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
            'sku'             => ['required', 'string', 'max:64', 'unique:products,sku'],
            'name'            => ['required', 'string', 'max:191'],
            'slug'            => ['required', 'string', 'max:191', 'unique:products,slug'],
            'description'     => ['nullable', 'string'],
            'price'           => ['required', 'numeric', 'min:0'],
            'active'          => ['boolean'],
            'stock_item_id'   => ['nullable', 'integer', 'exists:stock_items,id'],
            'stock_quantity'  => ['required_without:stock_item_id', 'nullable', 'integer', 'min:0'],
            'visibility'      => ['sometimes', 'string', 'max:32'],
            'category_ids'    => ['nullable', 'array'],
            'category_ids.*'  => ['integer', 'exists:categories,id'],
        ];
    }
}
