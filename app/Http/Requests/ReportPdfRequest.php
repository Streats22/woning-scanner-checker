<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query-validatie voor PDF-rapport (theme/locale allowlist, voorkomt onverwachte invoer).
 */
class ReportPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme' => ['sometimes', 'string', 'in:light,dark'],
            'locale' => ['sometimes', 'string', 'in:nl,en'],
        ];
    }
}
