<?php

namespace App\Http\Controllers;

use App\Models\LignePanier;
use App\Models\Produit;
use App\Services\ServicePanier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Panier du site marchand, accessible aux visiteurs comme aux clients
 * connectés. Le produit est identifié dans l'URL par sa référence.
 *
 * Page React : resources/js/pages/panier/index.tsx.
 */
class PanierController extends Controller
{
    public function __construct(private ServicePanier $servicePanier) {}

    /**
     * Affiche le contenu du panier courant.
     */
    public function index(): Response
    {
        $panier = $this->servicePanier->courant();

        return Inertia::render('panier/index', [
            'panier' => [
                'lignes' => $panier->lignes
                    ->sortBy('created_at')
                    ->values()
                    ->map(fn (LignePanier $ligne): array => [
                        'reference' => $ligne->reference_produit,
                        'description' => $ligne->produit->description_produit,
                        'rayon' => $ligne->produit->rayon?->nom_rayon,
                        'photo' => $ligne->produit->photos->first()?->url_photo,
                        'quantite' => $ligne->quantite,
                        'prixUnitaire' => $ligne->prix_unitaire,
                        'sousTotal' => number_format($ligne->sous_total, 2, '.', ''),
                        'stockDisponible' => $ligne->produit->stockDisponibleMaximum(),
                    ]),
                'total' => number_format($panier->total, 2, '.', ''),
                'nombreArticles' => $panier->nombre_articles,
            ],
        ]);
    }

    /**
     * Ajoute un produit au panier (1 par défaut).
     */
    public function ajouter(Request $request, Produit $produit): RedirectResponse
    {
        $donnees = $request->validate([
            'quantite' => ['nullable', 'integer', 'min:1', 'max:'.ServicePanier::QUANTITE_MAXIMALE],
        ]);

        $this->servicePanier->ajouter($produit, (int) ($donnees['quantite'] ?? 1));

        return back()->with('status', 'Produit ajouté au panier.');
    }

    /**
     * Modifie la quantité d'un produit (0 le retire du panier).
     */
    public function modifier(Request $request, Produit $produit): RedirectResponse
    {
        $donnees = $request->validate([
            'quantite' => ['required', 'integer', 'min:0', 'max:'.ServicePanier::QUANTITE_MAXIMALE],
        ]);

        $this->servicePanier->modifier($produit, (int) $donnees['quantite']);

        return back()->with('status', 'Panier mis à jour.');
    }

    /**
     * Retire un produit du panier.
     */
    public function retirer(Produit $produit): RedirectResponse
    {
        $this->servicePanier->retirer($produit);

        return back()->with('status', 'Produit retiré du panier.');
    }

    /**
     * Vide le panier.
     */
    public function vider(): RedirectResponse
    {
        $this->servicePanier->vider();

        return back()->with('status', 'Panier vidé.');
    }
}
