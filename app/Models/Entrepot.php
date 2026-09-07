<?php

namespace App\Models;

use Database\Factories\EntrepotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Un entrepôt dessert un ou plusieurs magasins (association DESSERVIR) et expédie
 * les commandes livrées à domicile (association EXPEDIER).
 *
 * @property int $id Identifiant technique de l'entrepôt.
 * @property string $ville Ville d'implantation de l'entrepôt.
 * @property string|null $zone_expedition Zone géographique desservie par l'entrepôt.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['ville', 'zone_expedition'])]
class Entrepot extends Model
{
    /** @use HasFactory<EntrepotFactory> */
    use HasFactory;

    /**
     * Les magasins desservis par cet entrepôt (association DESSERVIR).
     *
     * @return HasMany<Magasin, $this>
     */
    public function magasins(): HasMany
    {
        return $this->hasMany(Magasin::class);
    }

    /**
     * Les commandes expédiées depuis cet entrepôt (association EXPEDIER).
     *
     * @return HasMany<Commande, $this>
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
