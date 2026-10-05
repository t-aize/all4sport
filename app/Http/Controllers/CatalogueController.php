<?php

namespace App\Http\Controllers;

use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Catalogue public du site marchand : la liste des produits classés par rayon
 * (sport) et la fiche détaillée d'un produit. Ces pages sont accessibles sans
 * connexion.
 *
 * Pages React : resources/js/pages/catalogue/index.tsx et show.tsx.
 */
class CatalogueController extends Controller
{
    /**
     * Nombre de produits affichés par page.
     */
    private const PRODUITS_PAR_PAGE = 12;

    /**
     * Affiche les produits triés par rayon. Le paramètre d'URL `rayon`
     * (identifiant) restreint la liste à un seul rayon.
     */
    public function index(Request $request): Response
    {
        $rayonId = $request->integer('rayon') ?: null;

        $produits = Produit::query()
            ->with(['rayon', 'photos'])
            ->join('rayons', 'rayons.id', '=', 'produits.rayon_id')
            ->when($rayonId, fn ($requete) => $requete->where('produits.rayon_id', $rayonId))
            ->orderBy('rayons.nom_rayon')
            ->orderBy('produits.reference_produit')
            ->select('produits.*')
            ->paginate(self::PRODUITS_PAR_PAGE)
            ->withQueryString()
            ->through(fn (Produit $produit): array => [
                'reference' => $produit->reference_produit,
                'description' => $produit->description_produit,
                'prixVente' => $produit->prix_vente,
                'rayon' => $produit->rayon?->nom_rayon,
                'photo' => $produit->photos->first()?->url_photo,
            ]);

        return Inertia::render('catalogue/index', [
            'rayons' => Rayon::query()
                ->orderBy('nom_rayon')
                ->get(['id', 'nom_rayon'])
                ->map(fn (Rayon $rayon): array => ['id' => $rayon->id, 'nom' => $rayon->nom_rayon]),
            'rayonActif' => $rayonId,
            'produits' => $produits,
        ]);
    }

    /**
     * Affiche la fiche d'un produit : prix de vente, photos et disponibilité
     * (stock Internet et stock de chaque magasin).
     */
    public function show(Produit $produit): Response
    {
        $produit->load(['rayon', 'photos']);

        return Inertia::render('catalogue/show', [
            'produit' => [
                'reference' => $produit->reference_produit,
                'description' => $produit->description_produit,
                'prixVente' => $produit->prix_vente,
                'rayon' => $produit->rayon?->nom_rayon,
                'rayonId' => $produit->rayon_id,
                'photos' => $produit->photos->pluck('url_photo')->values(),
                'stockInternet' => $produit->quantite_stock_internet,
                'stocksMagasins' => $produit->magasins()
                    ->orderBy('ville')
                    ->get(['magasins.id', 'magasins.ville', 'magasins.adresse', 'magasin_produit.quantite_stock_magasin'])
                    ->map(fn (Magasin $magasin): array => [
                        'id' => $magasin->id,
                        'ville' => $magasin->ville,
                        'adresse' => $magasin->adresse,
                        'quantite' => (int) $magasin->getAttribute('quantite_stock_magasin'),
                    ])
                    ->values(),
            ],
        ]);
    }
}
