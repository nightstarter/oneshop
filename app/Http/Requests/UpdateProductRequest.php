<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id ?? $this->route('product');

        return [
            'sku'             => ['sometimes', 'string', 'max:64', "unique:products,sku,{$id}"],
            'name'            => ['sometimes', 'string', 'max:191'],
            'slug'            => ['sometimes', 'string', 'max:191', "unique:products,slug,{$id}"],
            'description'     => ['nullable', 'string'],
            'price'           => ['sometimes', 'numeric', 'min:0'],
            'active'          => ['boolean'],
            'stock_item_id'   => ['sometimes', 'nullable', 'integer', 'exists:stock_items,id'],
            'stock_quantity'  => ['sometimes', 'nullable', 'integer', 'min:0'],
            'visibility'      => ['sometimes', 'string', 'max:32'],
            'category_ids'    => ['nullable', 'array'],
            'category_ids.*'  => ['integer', 'exists:categories,id'],
        ];
    }
}
