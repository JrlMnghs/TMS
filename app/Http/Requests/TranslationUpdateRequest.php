<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validate payload for updating a translation key, tags, and values.
 */
class TranslationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'key_name' => ['sometimes', 'string', 'max:255', Rule::unique('translation_keys', 'key_name')->ignore($id)],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'values' => ['sometimes', 'array'],
        ];
    }
}


