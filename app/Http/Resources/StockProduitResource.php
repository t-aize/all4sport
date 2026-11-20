<?php

namespace App\Http\Resources;

use App\Models\Magasin;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Représentation JSON du stock d'un produit pour l'API Logistique.
 *
 * @mixin Produit
 */
class StockProduitResource extends JsonResource
{
    /**
     * Transforme le produit en tableau.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference_produit' => $this->reference_produit,
            'description' => $this->description_produit,
            'fournisseur' => $this->fournisseur?->nom_fournisseur,
            'rayon' => $this->rayon?->nom_rayon,
            'lieu_stockage' => $this->lieu_stockage,
            'quantite_stock_internet' => (int) $this->quantite_stock_internet,
            'stocks_magasins' => $this->magasins->map(fn (Magasin $magasin): array => [
                'magasin_id' => $magasin->id,
                'ville' => $magasin->ville,
                'quantite_stock_magasin' => (int) $magasin->getRelation('pivot')->getAttribute('quantite_stock_magasin'),
            ])->values(),
            'mis_a_jour_le' => $this->updated_at?->toIso8601String(),
        ];
    }
}
