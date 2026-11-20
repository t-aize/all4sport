<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackOffice\FournisseurRequest;
use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des fournisseurs du back-office (liste, création, modification,
 * suppression), réservée aux opérateurs et aux vendeurs.
 *
 * Un fournisseur qui livre encore des produits ne peut pas être supprimé
 * (contrainte `restrictOnDelete` de la table produits).
 *
 * Pages React : resources/js/pages/backoffice/fournisseurs/.
 */
class FournisseurController extends Controller
{
    /**
     * Nombre de fournisseurs affichés par page.
     */
    private const FOURNISSEURS_PAR_PAGE = 15;

    /**
     * Liste les fournisseurs avec leur nombre de produits.
     */
    public function index(Request $request): Response
    {
        $recherche = trim((string) $request->query('recherche', ''));

        $fournisseurs = Fournisseur::query()
            ->withCount('produits')
            ->when($recherche !== '', fn ($requete) => $requete->where(fn ($condition) => $condition
                ->where('nom_fournisseur', 'like', "%{$recherche}%")
                ->orWhere('code_fournisseur', 'like', "%{$recherche}%")))
            ->orderBy('nom_fournisseur')
            ->paginate(self::FOURNISSEURS_PAR_PAGE)
            ->withQueryString()
            ->through(fn (Fournisseur $fournisseur): array => [
                'id' => $fournisseur->id,
                'nom' => $fournisseur->nom_fournisseur,
                'code' => $fournisseur->code_fournisseur,
                'nombreProduits' => $fournisseur->produits_count,
                'creeLe' => $fournisseur->created_at?->toDateString(),
            ]);

        return Inertia::render('backoffice/fournisseurs/index', [
            'recherche' => $recherche,
            'fournisseurs' => $fournisseurs,
        ]);
    }

    /**
     * Affiche le formulaire de création.
     */
    public function create(): Response
    {
        return Inertia::render('backoffice/fournisseurs/create');
    }

    /**
     * Enregistre un nouveau fournisseur.
     */
    public function store(FournisseurRequest $request): RedirectResponse
    {
        Fournisseur::query()->create($request->validated());

        return to_route('backoffice.fournisseurs.index')->with('status', 'Fournisseur créé.');
    }

    /**
     * Affiche le formulaire de modification.
     */
    public function edit(Fournisseur $fournisseur): Response
    {
        return Inertia::render('backoffice/fournisseurs/edit', [
            'fournisseur' => [
                'id' => $fournisseur->id,
                'nom' => $fournisseur->nom_fournisseur,
                'code' => $fournisseur->code_fournisseur,
                'nombreProduits' => $fournisseur->produits()->count(),
            ],
        ]);
    }

    /**
     * Enregistre les modifications d'un fournisseur.
     */
    public function update(FournisseurRequest $request, Fournisseur $fournisseur): RedirectResponse
    {
        $fournisseur->update($request->validated());

        return to_route('backoffice.fournisseurs.index')->with('status', 'Fournisseur modifié.');
    }

    /**
     * Supprime un fournisseur sans produit.
     */
    public function destroy(Fournisseur $fournisseur): RedirectResponse
    {
        if ($fournisseur->produits()->exists()) {
            return back()->withErrors([
                'fournisseur' => "Impossible de supprimer « {$fournisseur->nom_fournisseur} » : des produits y sont rattachés.",
            ]);
        }

        $fournisseur->delete();

        return to_route('backoffice.fournisseurs.index')->with('status', 'Fournisseur supprimé.');
    }
}
