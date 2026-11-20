<?php

namespace App\Http\Requests;

use App\Enums\ModeLivraison;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation du choix de livraison au moment de passer commande :
 * - retrait en magasin (gratuit) : un magasin existant est obligatoire ;
 * - livraison à domicile (forfait de 10 €) : l'adresse complète est obligatoire.
 */
class PasserCommandeRequest extends FormRequest
{
    /**
     * Seul un client connecté peut passer commande (route protégée par "auth").
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
        $domicile = Rule::requiredIf($this->input('mode_livraison') === ModeLivraison::LivraisonDomicile->value);

        return [
            'mode_livraison' => ['required', Rule::enum(ModeLivraison::class)],
            'magasin_id' => [
                Rule::requiredIf($this->input('mode_livraison') === ModeLivraison::RetraitMagasin->value),
                'nullable',
                'integer',
                Rule::exists('magasins', 'id'),
            ],
            'adresse' => [$domicile, 'nullable', 'string', 'max:50'],
            'code_postal' => [$domicile, 'nullable', 'string', 'regex:/^\d{5}$/'],
            'ville' => [$domicile, 'nullable', 'string', 'max:30'],
            'accepte_conditions' => ['accepted'],
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
            'mode_livraison.required' => 'Choisissez un mode de livraison.',
            'mode_livraison.enum' => "Ce mode de livraison n'existe pas.",
            'magasin_id.required' => 'Choisissez le magasin de retrait.',
            'magasin_id.exists' => "Ce magasin n'existe pas.",
            'adresse.required' => "L'adresse de livraison est obligatoire.",
            'code_postal.required' => 'Le code postal est obligatoire.',
            'code_postal.regex' => 'Le code postal doit comporter 5 chiffres.',
            'ville.required' => 'La ville est obligatoire.',
            'accepte_conditions.accepted' => 'Vous devez accepter les conditions générales de vente.',
        ];
    }
}
