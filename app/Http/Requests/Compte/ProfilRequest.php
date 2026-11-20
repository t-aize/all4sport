<?php

namespace App\Http\Requests\Compte;

use App\Models\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation du formulaire "Mon compte".
 */
class ProfilRequest extends FormRequest
{
    /**
     * Seul le client connecté modifie son propre profil.
     */
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    /**
     * Les règles de validation.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:30'],
            'prenom' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:80', Rule::unique(Client::class)->ignore($this->user('web')?->getAuthIdentifier())],
            'telephone' => ['nullable', 'string', 'max:15', 'regex:/^[0-9 +().-]*$/'],
            'adresse' => ['required', 'string', 'max:50'],
            'code_postal' => ['required', 'string', 'regex:/^\d{5}$/'],
            'ville' => ['required', 'string', 'max:30'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'magasin_id' => ['nullable', 'integer', Rule::exists('magasins', 'id')],
            'rayons' => ['nullable', 'array'],
            'rayons.*' => ['integer', 'distinct', Rule::exists('rayons', 'id')],
        ];
    }

    /**
     * Les messages d'erreur en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.email' => "L'adresse email n'est pas valide.",
            'email.unique' => 'Un compte existe déjà avec cette adresse email.',
            'telephone.regex' => "Le numéro de téléphone n'est pas valide.",
            'adresse.required' => "L'adresse est obligatoire.",
            'code_postal.required' => 'Le code postal est obligatoire.',
            'code_postal.regex' => 'Le code postal doit comporter 5 chiffres.',
            'ville.required' => 'La ville est obligatoire.',
            'date_naissance.before' => 'La date de naissance doit être dans le passé.',
            'magasin_id.exists' => "Ce magasin n'existe pas.",
            'rayons.*.exists' => "Ce sport n'existe pas.",
        ];
    }
}
