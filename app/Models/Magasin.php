<?php

namespace App\Models;

use Database\Factories\MagasinFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Un magasin physique stocke des produits (association STOCKER) et sert de point
 * de retrait pour les commandes (association RETIRER). Il est rattaché à un
 * entrepôt qui le dessert (association DESSERVIR).
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 *
 * @property int $id Identifiant technique du magasin.
 * @property string $ville Ville d'implantation du magasin.
 * @property string $adresse Adresse postale du magasin.
 * @property int $entrepot_id Entrepôt qui dessert ce magasin.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['ville', 'adresse', 'entrepot_id'])]
class Magasin extends Model
{
    /** @use HasFactory<MagasinFactory> */
    use HasFactory;

    /**
     * L'entrepôt qui dessert ce magasin (association DESSERVIR).
     *
     * @return BelongsTo<Entrepot, $this>
     */
    public function entrepot(): BelongsTo
    {
        return $this->belongsTo(Entrepot::class);
    }

    /**
     * Les produits stockés dans ce magasin, avec la quantité en stock
     * (association STOCKER, table pivot magasin_produit).
     *
     * @return BelongsToMany<Produit, $this>
     */
    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'magasin_produit', 'magasin_id', 'reference_produit', 'id', 'reference_produit')
            ->withPivot('quantite_stock_magasin')
            ->withTimestamps();
    }

    /**
     * Les commandes retirées dans ce magasin (association RETIRER).
     *
     * @return HasMany<Commande, $this>
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
