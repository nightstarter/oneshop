<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:191'],
            'slug'             => ['required', 'string', 'max:191', Rule::unique('pages', 'slug')->ignore($this->route('page'))],
            'content'          => ['required', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:191'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_published'     => ['boolean'],
        ];
    }
}
