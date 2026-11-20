<?php

namespace App\Http\Controllers;

use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page d'accueil du site marchand www.all4sport.local : présentation de
 * l'enseigne, rayons (sports), nouveautés et réseau de magasins.
 *
 * Page React : resources/js/pages/welcome.tsx.
 */
class AccueilController extends Controller
{
    /**
     * Nombre de nouveautés mises en avant sur l'accueil.
     */
    private const NOMBRE_NOUVEAUTES = 8;

    /**
     * Affiche la page d'accueil.
     */
    public function __invoke(): Response
    {
        return Inertia::render('welcome', [
            'rayons' => Rayon::query()
                ->withCount('produits')
                ->orderBy('nom_rayon')
                ->get()
                ->map(fn (Rayon $rayon): array => [
                    'id' => $rayon->id,
                    'nom' => $rayon->nom_rayon,
                    'code' => $rayon->code_rayon,
                    'nombreProduits' => $rayon->produits_count,
                ]),
            'nouveautes' => Produit::query()
                ->with(['rayon', 'photos'])
                ->latest()
                ->orderByDesc('reference_produit')
                ->limit(self::NOMBRE_NOUVEAUTES)
                ->get()
                ->map(fn (Produit $produit): array => [
                    'reference' => $produit->reference_produit,
                    'description' => $produit->description_produit,
                    'prixVente' => $produit->prix_vente,
                    'rayon' => $produit->rayon?->nom_rayon,
                    'photo' => $produit->photos->first()?->url_photo,
                    'stockInternet' => $produit->quantite_stock_internet,
                ]),
            'magasins' => Magasin::query()
                ->with('entrepot')
                ->orderBy('ville')
                ->get()
                ->map(fn (Magasin $magasin): array => [
                    'id' => $magasin->id,
                    'ville' => $magasin->ville,
                    'adresse' => $magasin->adresse,
                    'entrepot' => $magasin->entrepot?->ville,
                ]),
            'chiffres' => [
                'produits' => Produit::query()->count(),
                'rayons' => Rayon::query()->count(),
                'magasins' => Magasin::query()->count(),
            ],
        ]);
    }
}
