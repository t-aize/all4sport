<?php

namespace App\Http\Requests\BackOffice;

use App\Models\Produit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

/**
 * Validation du formulaire de création et de modification d'un produit du
 * back-office.
 *
 * La même requête sert aux deux cas : à la modification, la route contient le
 * produit (`$this->route('produit')`) et les photos peuvent ne pas être
 * renvoyées. Un produit doit toujours avoir entre 1 et 5 photos.
 *
 * Utilisée par BackOffice\ProduitController::store et ::update.
 */
class ProduitRequest extends FormRequest
{
    /**
     * Nombre minimal et maximal de photos d'un produit.
     */
    public const PHOTOS_MIN = 1;

    public const PHOTOS_MAX = 5;

    /**
     * L'accès est déjà réservé aux rôles autorisés par le middleware "role".
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Les règles de validation appliquées aux champs du formulaire.
     *
     * Le lieu de stockage suit l'identification des cellules d'entrepôt :
     * module M1 à M6, rangée A à L, section 1 à 12, étagère 1 à 8 (ex : M2D3.5).
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'description_produit' => ['required', 'string', 'max:500'],
            'cout_unitaire_ht' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'prix_vente' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'quantite_stock_internet' => ['required', 'integer', 'min:0'],
            'lieu_stockage' => ['nullable', 'string', 'max:15', 'regex:/^M[1-6][A-L](?:[1-9]|1[0-2])\.[1-8]$/'],
            'fournisseur_id' => ['required', 'integer', 'exists:fournisseurs,id'],
            'rayon_id' => ['required', 'integer', 'exists:rayons,id'],
            'stocks' => ['nullable', 'array'],
            'stocks.*' => ['nullable', 'integer', 'min:0'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'photos_supprimees' => ['nullable', 'array'],
            'photos_supprimees.*' => ['integer'],
        ];
    }

    /**
     * Les messages d'erreur affichés dans le formulaire.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lieu_stockage.regex' => 'Le lieu de stockage doit ressembler à M2D3.5 (module M1-M6, rangée A-L, section 1-12, étagère 1-8).',
            'photos.*.image' => 'Chaque fichier doit être une image.',
            'photos.*.mimes' => 'Les photos doivent être au format JPG, PNG ou WebP.',
            'photos.*.max' => 'Chaque photo doit peser 4 Mo au maximum.',
        ];
    }

    /**
     * Vérifie, une fois les champs valides, que le produit aura entre 1 et 5
     * photos après l'enregistrement (photos conservées + nouvelles photos).
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $total = $this->nombrePhotosConservees() + count($this->nouvellesPhotos());

                if ($total < self::PHOTOS_MIN || $total > self::PHOTOS_MAX) {
                    $validator->errors()->add(
                        'photos',
                        sprintf('Un produit doit avoir entre %d et %d photos (vous en avez %d).', self::PHOTOS_MIN, self::PHOTOS_MAX, $total),
                    );
                }
            },
        ];
    }

    /**
     * Les photos téléversées dans le formulaire.
     *
     * @return list<UploadedFile>
     */
    public function nouvellesPhotos(): array
    {
        return array_values(array_filter((array) $this->file('photos')));
    }

    /**
     * Les identifiants des photos existantes que le formulaire retire.
     *
     * @return array<int, int>
     */
    public function photosASupprimer(): array
    {
        $produit = $this->route('produit');

        if (! $produit instanceof Produit) {
            return [];
        }

        return $produit->photos()
            ->whereIn('id', array_map('intval', (array) $this->input('photos_supprimees', [])))
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Les quantités en stock par magasin renseignées dans le formulaire, sous
     * la forme [identifiant du magasin => quantité]. Les champs vides sont
     * ignorés : le magasin ne stocke alors pas ce produit.
     *
     * @return array<int, int>
     */
    public function stocksMagasins(): array
    {
        $stocks = [];

        foreach ((array) $this->input('stocks', []) as $magasinId => $quantite) {
            if ($quantite !== null && $quantite !== '') {
                $stocks[(int) $magasinId] = (int) $quantite;
            }
        }

        return $stocks;
    }

    /**
     * Le nombre de photos que le produit garde après la modification.
     */
    private function nombrePhotosConservees(): int
    {
        $produit = $this->route('produit');

        if (! $produit instanceof Produit) {
            return 0;
        }

        return $produit->photos()->count() - count($this->photosASupprimer());
    }
}
