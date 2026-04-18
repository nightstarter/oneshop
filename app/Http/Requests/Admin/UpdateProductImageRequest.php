<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alt' => ['nullable', 'string', 'max:191'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }
}
