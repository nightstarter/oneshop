<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id ?? $this->route('category');

        return [
            'parent_id'   => ['nullable', 'integer', 'exists:categories,id'],
            'name'        => ['sometimes', 'string', 'max:191'],
            'slug'        => ['sometimes', 'string', 'max:191', "unique:categories,slug,{$id}"],
            'description' => ['nullable', 'string'],
            'sort_order'  => ['integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
