<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['nullable', 'integer', 'exists:users,id'],
            'price_list_id' => ['nullable', 'integer', 'exists:price_lists,id'],
            'type'          => ['sometimes', Rule::in(['retail', 'b2b'])],
            'company_name'  => ['nullable', 'string', 'max:191'],
            'company_id'    => ['nullable', 'string', 'max:32'],
            'vat_id'        => ['nullable', 'string', 'max:32'],
            'first_name'    => ['sometimes', 'string', 'max:100'],
            'last_name'     => ['sometimes', 'string', 'max:100'],
            'email'         => ['sometimes', 'email', 'max:191'],
            'phone'         => ['nullable', 'string', 'max:32'],
            'is_active'     => ['boolean'],
        ];
    }
}
