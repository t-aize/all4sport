<?php

namespace App\Models;

use Database\Factories\RayonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Un rayon représente une discipline sportive utilisée pour classer les produits
 * (association APPARTENIR) et pour repérer les sports pratiqués par les clients
 * (association PRATIQUER).
 *
 * @property int $id Identifiant technique du rayon.
 * @property string $nom_rayon Libellé du rayon / de la discipline sportive.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nom_rayon'])]
class Rayon extends Model
{
    /** @use HasFactory<RayonFactory> */
    use HasFactory;

    /**
     * Les produits classés dans ce rayon.
     *
     * @return HasMany<Produit, $this>
     */
    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * Les clients qui pratiquent ce sport (association PRATIQUER).
     *
     * @return BelongsToMany<Client, $this>
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_rayon')->withTimestamps();
    }
}
