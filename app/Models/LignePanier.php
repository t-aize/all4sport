<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LignePanier extends Model
{
    protected $table = 'lignes_panier';

    protected $fillable = ['panier_id', 'reference_produit', 'quantite', 'prix_unitaire'];

    protected $casts = [
        'quantite'      => 'integer',
        'prix_unitaire' => 'decimal:2',
    ];

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    /** Le produit est rattaché par sa référence métier (clé primaire textuelle). */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'reference_produit', 'reference_produit');
    }

    /** Sous-total de la ligne : $ligne->sous_total */
    public function getSousTotalAttribute(): float
    {
        return (float) $this->prix_unitaire * $this->quantite;
    }
}