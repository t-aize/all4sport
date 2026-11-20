<?php

namespace App\Models;

use Database\Factories\LignePanierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Une ligne de panier : un produit, sa quantité et son prix figé au moment de
 * l'ajout. Le produit est rattaché par sa référence métier.
 *
 * @property int $id
 * @property int $panier_id Panier contenant la ligne.
 * @property string $reference_produit Référence du produit ajouté.
 * @property int $quantite Quantité souhaitée.
 * @property string $prix_unitaire Prix unitaire figé à l'ajout.
 * @property-read float $sous_total Prix unitaire × quantité.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['panier_id', 'reference_produit', 'quantite', 'prix_unitaire'])]
class LignePanier extends Model
{
    /** @use HasFactory<LignePanierFactory> */
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'lignes_panier';

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'prix_unitaire' => 'decimal:2',
        ];
    }

    /**
     * Le panier contenant cette ligne.
     *
     * @return BelongsTo<Panier, $this>
     */
    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    /**
     * Le produit ajouté, rattaché par sa référence (clé primaire textuelle).
     *
     * @return BelongsTo<Produit, $this>
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'reference_produit', 'reference_produit');
    }

    /**
     * Sous-total de la ligne : `$ligne->sous_total`.
     */
    public function getSousTotalAttribute(): float
    {
        return round((float) $this->prix_unitaire * $this->quantite, 2);
    }
}
