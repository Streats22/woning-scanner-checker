<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validatie en normalisatie voor POST /api/analyze en legacy POST /analyze.
 */
class AnalyzeListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:65535'],
            'use_ai' => ['sometimes', 'boolean'],
            'locale' => ['sometimes', 'string', 'in:nl,en'],
        ];
    }
}
