<?php

namespace App\Http\Requests\BackOffice;

use App\Models\Fournisseur;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation du formulaire de création et de modification d'un fournisseur.
 *
 * Le code fournisseur correspond aux 3 premières lettres du nom (dossier de
 * contexte, identification produit) : il est proposé automatiquement mais
 * reste modifiable, et doit être unique. Il est figé dès que des produits y
 * sont rattachés, car il entre dans leur référence.
 */
class FournisseurRequest extends FormRequest
{
    /**
     * L'accès est déjà réservé aux rôles autorisés par le middleware "role".
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Met le code en majuscules et le déduit du nom s'il est vide.
     */
    protected function prepareForValidation(): void
    {
        $code = trim((string) $this->input('code_fournisseur'));

        if ($code === '') {
            $code = Fournisseur::codeDepuisNom((string) $this->input('nom_fournisseur'));
        }

        $this->merge(['code_fournisseur' => mb_strtoupper($code)]);
    }

    /**
     * Les règles de validation.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Fournisseur|null $fournisseur */
        $fournisseur = $this->route('fournisseur');

        return [
            'nom_fournisseur' => ['required', 'string', 'min:3', 'max:40'],
            'code_fournisseur' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
                Rule::unique('fournisseurs', 'code_fournisseur')->ignore($fournisseur?->id),
                function (string $attribut, mixed $valeur, \Closure $echec) use ($fournisseur): void {
                    if ($fournisseur !== null && $fournisseur->code_fournisseur !== $valeur && $fournisseur->produits()->exists()) {
                        $echec('Le code ne peut plus changer : il figure dans la référence de produits existants.');
                    }
                },
            ],
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
            'nom_fournisseur.required' => 'Le nom du fournisseur est obligatoire.',
            'nom_fournisseur.min' => 'Le nom doit comporter au moins :min caractères.',
            'nom_fournisseur.max' => 'Le nom ne doit pas dépasser :max caractères.',
            'code_fournisseur.size' => 'Le code doit comporter exactement 3 lettres.',
            'code_fournisseur.regex' => 'Le code doit comporter exactement 3 lettres (A à Z).',
            'code_fournisseur.unique' => 'Ce code est déjà utilisé par un autre fournisseur.',
        ];
    }
}
