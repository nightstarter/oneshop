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
            'sku'            => ['sometimes', 'string', 'max:64', "unique:products,sku,{$id}"],
            'name'           => ['sometimes', 'string', 'max:191'],
            'slug'           => ['sometimes', 'string', 'max:191', "unique:products,slug,{$id}"],
            'description'    => ['nullable', 'string'],
            'base_price_net' => ['sometimes', 'numeric', 'min:0'],
            'stock_qty'      => ['sometimes', 'integer', 'min:0'],
            'is_active'      => ['boolean'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
