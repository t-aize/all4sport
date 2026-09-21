<?php

namespace App\Models;

use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Une photographie illustrant un produit (association ILLUSTRER). Un produit
 * peut avoir de 1 à 5 photos.
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 *
 * @property int $id Identifiant technique de la photo.
 * @property string $url_photo Chemin/URL de la photographie du produit.
 * @property string $reference_produit Produit illustré par cette photo.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['url_photo', 'reference_produit'])]
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    /**
     * Le produit illustré par cette photo (association ILLUSTRER).
     *
     * @return BelongsTo<Produit, $this>
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'reference_produit', 'reference_produit');
    }
}
