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
     * Les ordres de tri proposés (par défaut : par rayon).
     *
     * @var list<string>
     */
    private const TRIS = ['rayon', 'prix_croissant', 'prix_decroissant', 'nouveautes'];

    /**
     * Affiche les produits triés par rayon. Le paramètre d'URL `rayon`
     * (identifiant) restreint la liste à un seul rayon.
     */
    public function index(Request $request): Response
    {
        $rayonId = $request->integer('rayon') ?: null;
        $recherche = mb_substr(trim((string) $request->query('recherche', '')), 0, 60);
        $tri = in_array($request->query('tri'), self::TRIS, true) ? (string) $request->query('tri') : 'rayon';

        $produits = Produit::query()
            ->with(['rayon', 'photos'])
            ->join('rayons', 'rayons.id', '=', 'produits.rayon_id')
            ->when($rayonId, fn ($requete) => $requete->where('produits.rayon_id', $rayonId))
            ->when($recherche !== '', fn ($requete) => $requete->where(fn ($condition) => $condition
                ->where('produits.description_produit', 'like', "%{$recherche}%")
                ->orWhere('produits.reference_produit', 'like', "%{$recherche}%")
                ->orWhere('rayons.nom_rayon', 'like', "%{$recherche}%")))
            ->when($tri === 'prix_croissant', fn ($requete) => $requete->orderBy('produits.prix_vente'))
            ->when($tri === 'prix_decroissant', fn ($requete) => $requete->orderByDesc('produits.prix_vente'))
            ->when($tri === 'nouveautes', fn ($requete) => $requete->orderByDesc('produits.created_at'))
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
                'stockInternet' => $produit->quantite_stock_internet,
            ]);

        return Inertia::render('catalogue/index', [
            'rayons' => Rayon::query()
                ->orderBy('nom_rayon')
                ->get(['id', 'nom_rayon'])
                ->map(fn (Rayon $rayon): array => ['id' => $rayon->id, 'nom' => $rayon->nom_rayon]),
            'rayonActif' => $rayonId,
            'recherche' => $recherche,
            'tri' => $tri,
            'produits' => $produits,
        ]);
    }

    /**
     * Affiche la fiche d'un produit : prix de vente, photos et disponibilité
     * (stock Internet et stock de chaque magasin).
     */
    public function show(Produit $produit): Response
    {
        $produit->load(['rayon', 'photos', 'fournisseur']);

        return Inertia::render('catalogue/show', [
            'produit' => [
                'reference' => $produit->reference_produit,
                'description' => $produit->description_produit,
                'prixVente' => $produit->prix_vente,
                'rayon' => $produit->rayon?->nom_rayon,
                'rayonId' => $produit->rayon_id,
                'photos' => $produit->photos->pluck('url_photo')->values(),
                'stockInternet' => $produit->quantite_stock_internet,
                'fournisseur' => $produit->fournisseur?->nom_fournisseur,
                'stockTotal' => $produit->stockTotal(),
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
            // Produits du même rayon, pour la section "Vous aimerez aussi".
            'similaires' => Produit::query()
                ->with(['rayon', 'photos'])
                ->where('rayon_id', $produit->rayon_id)
                ->whereKeyNot($produit->reference_produit)
                ->inRandomOrder()
                ->limit(4)
                ->get()
                ->map(fn (Produit $similaire): array => [
                    'reference' => $similaire->reference_produit,
                    'description' => $similaire->description_produit,
                    'prixVente' => $similaire->prix_vente,
                    'rayon' => $similaire->rayon?->nom_rayon,
                    'photo' => $similaire->photos->first()?->url_photo,
                    'stockInternet' => $similaire->quantite_stock_internet,
                ]),
        ]);
    }
}
