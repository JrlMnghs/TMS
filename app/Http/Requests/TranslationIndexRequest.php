<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate query params for listing/searching translations.
 */
class TranslationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag' => ['sometimes', 'string'],
            'tags' => ['sometimes', 'string'],
            'key' => ['sometimes', 'string'],
            'keyword' => ['sometimes', 'string'],
            'locale' => ['sometimes', 'string', 'max:16'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}


