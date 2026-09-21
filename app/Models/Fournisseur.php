<?php

namespace App\Models;

use Database\Factories\FournisseurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Un fournisseur livre un ou plusieurs produits à ALL4SPORT (association FOURNIR).
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 *
 * @property int $id Identifiant technique du fournisseur.
 * @property string $nom_fournisseur Raison sociale du fournisseur.
 * @property string $code_fournisseur Code unique du fournisseur (3 caractères).
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'nom_fournisseur',
    'code_fournisseur',
])]
class Fournisseur extends Model
{
    /** @use HasFactory<FournisseurFactory> */
    use HasFactory;

    /**
     * Les produits livrés par ce fournisseur.
     *
     * @return HasMany<Produit, $this>
     */
    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }
}