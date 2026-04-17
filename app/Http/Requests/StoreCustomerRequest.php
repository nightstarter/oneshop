<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
            'type'          => ['required', Rule::in(['retail', 'b2b'])],
            'company_name'  => ['nullable', 'string', 'max:191'],
            'company_id'    => ['nullable', 'string', 'max:32'],
            'vat_id'        => ['nullable', 'string', 'max:32'],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:191'],
            'phone'         => ['nullable', 'string', 'max:32'],
            'is_active'     => ['boolean'],
        ];
    }
}
