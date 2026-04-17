<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'   => ['nullable', 'integer', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:191'],
            'slug'        => ['required', 'string', 'max:191', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
