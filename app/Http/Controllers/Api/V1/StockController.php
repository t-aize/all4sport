<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockProduitResource;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Synchronisation des stocks avec le SI Logistique (Lot B) : lecture des
 * stocks Internet et magasins, et mise à jour après réception ou inventaire.
 */
class StockController extends Controller
{
    /**
     * Liste paginée des stocks. Filtres facultatifs : `rayon` (code de 3
     * lettres) et `modifie_depuis` (date ISO 8601) pour une synchronisation
     * incrémentale.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtres = $request->validate([
            'rayon' => ['nullable', 'string', 'size:3'],
            'modifie_depuis' => ['nullable', 'date'],
            'par_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $produits = Produit::query()
            ->with(['fournisseur', 'rayon', 'magasins'])
            ->when($filtres['rayon'] ?? null, fn (Builder $requete, string $rayon) => $requete
                ->whereHas('rayon', fn (Builder $condition) => $condition->where('code_rayon', strtoupper($rayon))))
            ->when($filtres['modifie_depuis'] ?? null, fn (Builder $requete, string $date) => $requete->where('updated_at', '>=', $date))
            ->orderBy('reference_produit')
            ->paginate((int) ($filtres['par_page'] ?? 50));

        return StockProduitResource::collection($produits);
    }

    /**
     * Le stock d'un produit.
     */
    public function show(Produit $produit): StockProduitResource
    {
        return new StockProduitResource($produit->load(['fournisseur', 'rayon', 'magasins']));
    }

    /**
     * Met à jour le stock Internet, le lieu de stockage et/ou les stocks
     * magasins d'un produit. Les magasins non mentionnés ne sont pas modifiés.
     */
    public function update(Request $request, Produit $produit): StockProduitResource
    {
        $donnees = $request->validate([
            'quantite_stock_internet' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'lieu_stockage' => ['nullable', 'string', 'max:15', 'regex:/^M[1-6][A-L](?:[1-9]|1[0-2])\.[1-8]$/'],
            'stocks_magasins' => ['nullable', 'array'],
            'stocks_magasins.*.magasin_id' => ['required', 'integer', 'distinct', Rule::exists('magasins', 'id')],
            'stocks_magasins.*.quantite_stock_magasin' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($produit, $donnees): void {
            $produit->fill(array_filter([
                'quantite_stock_internet' => $donnees['quantite_stock_internet'] ?? null,
                'lieu_stockage' => $donnees['lieu_stockage'] ?? null,
            ], fn (mixed $valeur): bool => $valeur !== null));
            $produit->save();
            $produit->touch();

            foreach ($donnees['stocks_magasins'] ?? [] as $stock) {
                $produit->magasins()->syncWithoutDetaching([
                    $stock['magasin_id'] => ['quantite_stock_magasin' => $stock['quantite_stock_magasin']],
                ]);
            }
        });

        return new StockProduitResource($produit->refresh()->load(['fournisseur', 'rayon', 'magasins']));
    }
}
