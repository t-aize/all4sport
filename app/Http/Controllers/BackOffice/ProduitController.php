<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackOffice\ProduitRequest;
use App\Models\Employe;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Photo;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Gestion des produits du back-office : liste, création, modification et
 * suppression, avec le stock Internet, le stock de chaque magasin et les
 * photos (de 1 à 5 par produit).
 *
 * L'accès est réservé aux opérateurs et aux vendeurs (voir routes/backoffice.php).
 * Les photos téléversées sont enregistrées sur le disque "public" (dossier
 * produits/) ; la table `photos` en garde l'URL.
 *
 * Pages React : resources/js/pages/backoffice/produits/.
 */
class ProduitController extends Controller
{
    /**
     * Nombre de produits affichés par page.
     */
    private const PRODUITS_PAR_PAGE = 15;

    /**
     * Liste les produits, avec une recherche par référence ou description.
     */
    public function index(Request $request): Response
    {
        $recherche = trim((string) $request->query('recherche', ''));

        $produits = Produit::query()
            ->with(['rayon', 'fournisseur'])
            ->withCount('photos')
            ->when($recherche !== '', fn ($requete) => $requete->where(function ($requete) use ($recherche): void {
                $requete->where('reference_produit', 'like', "%{$recherche}%")
                    ->orWhere('description_produit', 'like', "%{$recherche}%");
            }))
            ->orderBy('reference_produit')
            ->paginate(self::PRODUITS_PAR_PAGE)
            ->withQueryString()
            ->through(fn (Produit $produit): array => [
                'reference' => $produit->reference_produit,
                'description' => $produit->description_produit,
                'rayon' => $produit->rayon?->nom_rayon,
                'fournisseur' => $produit->fournisseur?->nom_fournisseur,
                'prixVente' => $produit->prix_vente,
                'stockInternet' => $produit->quantite_stock_internet,
                'nombrePhotos' => $produit->photos_count,
            ]);

        return Inertia::render('backoffice/produits/index', [
            'roleLibelle' => $this->roleLibelle($request),
            'recherche' => $recherche,
            'produits' => $produits,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un produit.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('backoffice/produits/create', [
            'roleLibelle' => $this->roleLibelle($request),
            ...$this->listesDuFormulaire(),
        ]);
    }

    /**
     * Enregistre un nouveau produit : la référence est générée par le modèle.
     */
    public function store(ProduitRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $produit = Produit::create($request->safe()->only([
                'description_produit',
                'cout_unitaire_ht',
                'prix_vente',
                'quantite_stock_internet',
                'lieu_stockage',
                'fournisseur_id',
                'rayon_id',
            ]));

            $this->synchroniserStocks($produit, $request);
            $this->ajouterPhotos($produit, $request);
        });

        return to_route('backoffice.produits.index')->with('status', 'Produit créé.');
    }

    /**
     * Affiche le formulaire de modification d'un produit.
     */
    public function edit(Request $request, Produit $produit): Response
    {
        $produit->load(['photos', 'magasins']);

        return Inertia::render('backoffice/produits/edit', [
            'roleLibelle' => $this->roleLibelle($request),
            ...$this->listesDuFormulaire(),
            'produit' => [
                'reference' => $produit->reference_produit,
                'description_produit' => $produit->description_produit,
                'cout_unitaire_ht' => $produit->cout_unitaire_ht,
                'prix_vente' => $produit->prix_vente,
                'quantite_stock_internet' => $produit->quantite_stock_internet,
                'lieu_stockage' => $produit->lieu_stockage,
                'fournisseur_id' => $produit->fournisseur_id,
                'rayon_id' => $produit->rayon_id,
                'photos' => $produit->photos->map(fn (Photo $photo): array => [
                    'id' => $photo->id,
                    'url' => $photo->url_photo,
                ])->values(),
                'stocks' => $produit->magasins->mapWithKeys(fn (Magasin $magasin): array => [
                    $magasin->id => $magasin->getRelation('pivot')->getAttribute('quantite_stock_magasin'),
                ]),
            ],
        ]);
    }

    /**
     * Met à jour un produit (sa référence ne change jamais).
     */
    public function update(ProduitRequest $request, Produit $produit): RedirectResponse
    {
        DB::transaction(function () use ($request, $produit): void {
            $produit->update($request->safe()->only([
                'description_produit',
                'cout_unitaire_ht',
                'prix_vente',
                'quantite_stock_internet',
                'lieu_stockage',
                'fournisseur_id',
                'rayon_id',
            ]));

            $this->synchroniserStocks($produit, $request);
            $this->supprimerPhotos($produit->photos()->whereIn('id', $request->photosASupprimer())->get());
            $this->ajouterPhotos($produit, $request);
        });

        return to_route('backoffice.produits.index')->with('status', 'Produit modifié.');
    }

    /**
     * Supprime un produit, ses photos et ses stocks en magasin. Un produit déjà
     * commandé ne peut pas être supprimé : il apparaît dans l'historique des
     * commandes des clients.
     */
    public function destroy(Produit $produit): RedirectResponse
    {
        if ($produit->commandes()->exists()) {
            return back()->withErrors([
                'produit' => 'Ce produit a déjà été commandé : il ne peut pas être supprimé.',
            ]);
        }

        $photos = $produit->photos()->get();

        DB::transaction(fn () => $produit->delete());
        $this->supprimerPhotos($photos, false);

        return to_route('backoffice.produits.index')->with('status', 'Produit supprimé.');
    }

    /**
     * Le libellé du rôle du collaborateur connecté, affiché dans le bandeau.
     */
    private function roleLibelle(Request $request): string
    {
        /** @var Employe $employe */
        $employe = $request->user('employe');

        return $employe->role->libelle();
    }

    /**
     * Les listes nécessaires au formulaire : fournisseurs, rayons et magasins.
     *
     * @return array<string, mixed>
     */
    private function listesDuFormulaire(): array
    {
        return [
            'fournisseurs' => Fournisseur::query()->orderBy('nom_fournisseur')->get(['id', 'nom_fournisseur'])
                ->map(fn (Fournisseur $fournisseur): array => ['id' => $fournisseur->id, 'nom' => $fournisseur->nom_fournisseur]),
            'rayons' => Rayon::query()->orderBy('nom_rayon')->get(['id', 'nom_rayon'])
                ->map(fn (Rayon $rayon): array => ['id' => $rayon->id, 'nom' => $rayon->nom_rayon]),
            'magasins' => Magasin::query()->orderBy('ville')->get(['id', 'ville'])
                ->map(fn (Magasin $magasin): array => ['id' => $magasin->id, 'ville' => $magasin->ville]),
        ];
    }

    /**
     * Enregistre le stock de chaque magasin. Les magasins dont le champ est
     * vide (ou inconnus) ne stockent plus le produit.
     */
    private function synchroniserStocks(Produit $produit, ProduitRequest $request): void
    {
        $magasinsExistants = Magasin::query()->pluck('id')->all();

        $stocks = collect($request->stocksMagasins())
            ->only($magasinsExistants)
            ->map(fn (int $quantite): array => ['quantite_stock_magasin' => $quantite])
            ->all();

        $produit->magasins()->sync($stocks);
    }

    /**
     * Enregistre les photos téléversées et les rattache au produit.
     */
    private function ajouterPhotos(Produit $produit, ProduitRequest $request): void
    {
        foreach ($request->nouvellesPhotos() as $fichier) {
            $chemin = $fichier->store('produits', 'public');

            if ($chemin === false) {
                throw new RuntimeException("Impossible d'enregistrer la photo {$fichier->getClientOriginalName()}.");
            }

            $produit->photos()->create([
                'url_photo' => Storage::disk('public')->url($chemin),
            ]);
        }
    }

    /**
     * Supprime des photos en base et, pour celles que l'application a
     * téléversées, leur fichier sur le disque.
     *
     * @param  iterable<Photo>  $photos
     * @param  bool  $supprimerEnBase  false quand la base les a déjà supprimées (suppression du produit).
     */
    private function supprimerPhotos(iterable $photos, bool $supprimerEnBase = true): void
    {
        $prefixe = Storage::disk('public')->url('');

        foreach ($photos as $photo) {
            if (str_starts_with($photo->url_photo, $prefixe)) {
                Storage::disk('public')->delete(substr($photo->url_photo, strlen($prefixe)));
            }

            if ($supprimerEnBase) {
                $photo->delete();
            }
        }
    }
}
