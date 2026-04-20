<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:191'],
            'slug'             => ['required', 'string', 'max:191', 'unique:pages,slug'],
            'content'          => ['required', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_published'     => ['boolean'],
        ];
    }
}
