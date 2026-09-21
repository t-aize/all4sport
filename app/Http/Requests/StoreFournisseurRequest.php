<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFournisseurRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code_fournisseur' => strtoupper((string) $this->input('code_fournisseur')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nom_fournisseur' => ['required', 'string', 'max:40'],
            'code_fournisseur' => ['required', 'regex:/^[A-Z]{3}$/', 'unique:fournisseurs,code_fournisseur'],
        ];
    }
}
