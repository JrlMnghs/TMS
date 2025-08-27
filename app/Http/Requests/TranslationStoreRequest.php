<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate payload for creating a translation key with values and tags.
 */
class TranslationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key_name' => ['required', 'string', 'max:255', 'unique:translation_keys,key_name'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'values' => ['required', 'array', 'min:1'],
        ];
    }
}


