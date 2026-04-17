<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('price_list')?->id ?? $this->route('price_list');

        return [
            'name'       => ['sometimes', 'string', 'max:191'],
            'code'       => ['sometimes', 'string', 'max:32', "unique:price_lists,code,{$id}"],
            'currency'   => ['sometimes', 'string', 'size:3'],
            'is_active'  => ['boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_to'   => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }
}
