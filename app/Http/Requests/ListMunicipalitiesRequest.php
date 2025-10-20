<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListMunicipalitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'O campo per_page deve ser um número inteiro.',
            'per_page.min' => 'O campo per_page deve ser no mínimo 1.',
            'per_page.max' => 'O campo per_page deve ser no máximo 100.',
        ];
    }
}
