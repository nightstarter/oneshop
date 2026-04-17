<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:191'],
            'code'       => ['required', 'string', 'max:32', 'unique:price_lists,code'],
            'currency'   => ['required', 'string', 'size:3'],
            'is_active'  => ['boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_to'   => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }
}
