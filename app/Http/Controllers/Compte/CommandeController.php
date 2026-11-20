<?php

namespace App\Http\Controllers\Compte;

use App\Enums\EtatCommande;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Commande;
use App\Models\HistoriqueEtat;
use App\Models\Produit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Historique des commandes du client connecté, avec le détail de chacune :
 * date, produits, quantités, prix unitaire, sous-total, total, lieu
 * d'expédition, adresse de livraison et suivi des états.
 *
 * Pages React : resources/js/pages/compte/commandes/index.tsx et show.tsx.
 */
class CommandeController extends Controller
{
    /**
     * Nombre de commandes par page.
     */
    private const COMMANDES_PAR_PAGE = 10;

    /**
     * Liste les commandes du client, de la plus récente à la plus ancienne.
     */
    public function index(Request $request): Response
    {
        /** @var Client $client */
        $client = $request->user('web');

        $commandes = $client->commandes()
            ->withCount('produits')
            ->withSum('produits as nombre_articles', 'commande_produit.quantite_commandee')
            ->latest('date_commande')
            ->latest('id')
            ->paginate(self::COMMANDES_PAR_PAGE)
            ->through(fn (Commande $commande): array => [
                'id' => $commande->id,
                'numero' => $commande->numero(),
                'date' => $commande->date_commande->toDateString(),
                'modeLivraison' => $commande->mode_livraison->libelle(),
                'etat' => $commande->etat->value,
                'etatLibelle' => $commande->etat->libelle(),
                'montantTotal' => $commande->montant_total,
                'nombreArticles' => (int) $commande->getAttribute('nombre_articles'),
            ]);

        return Inertia::render('compte/commandes/index', [
            'commandes' => $commandes,
        ]);
    }

    /**
     * Affiche le détail d'une commande du client. Un client ne peut pas
     * consulter la commande d'un autre (404 pour ne rien révéler).
     */
    public function show(Request $request, Commande $commande): Response
    {
        abort_unless($commande->client_id === $request->user('web')?->getAuthIdentifier(), 404);

        $commande->load(['produits.photos', 'magasin', 'entrepot', 'historiqueEtats']);

        return Inertia::render('compte/commandes/show', [
            'confirmation' => $request->boolean('confirmation'),
            'commande' => [
                ...$this->detail($commande),
            ],
        ]);
    }

    /**
     * Le détail d'une commande, partagé avec le back-office.
     *
     * @return array<string, mixed>
     */
    public static function detail(Commande $commande): array
    {
        $etatsAtteints = $commande->historiqueEtats
            ->sortBy([['date_etat', 'asc'], ['id', 'asc']])
            ->values();

        return [
            'id' => $commande->id,
            'numero' => $commande->numero(),
            'date' => $commande->date_commande->toDateString(),
            'modeLivraison' => $commande->mode_livraison->value,
            'modeLivraisonLibelle' => $commande->mode_livraison->libelle(),
            'etat' => $commande->etat->value,
            'etatLibelle' => $commande->etat->libelle(),
            'lieuExpedition' => $commande->lieuExpedition(),
            'adresseLivraison' => $commande->adresse_livraison,
            'magasin' => $commande->magasin ? [
                'ville' => $commande->magasin->ville,
                'adresse' => $commande->magasin->adresse,
            ] : null,
            'fraisLivraison' => $commande->frais_livraison,
            'sousTotal' => number_format($commande->sousTotal(), 2, '.', ''),
            'montantTotal' => $commande->montant_total,
            'lignes' => $commande->produits->map(function (Produit $produit): array {
                /** @var object{quantite_commandee: int, prix_unitaire_vente: string} $pivot */
                $pivot = $produit->getRelation('pivot');

                return [
                    'reference' => $produit->reference_produit,
                    'description' => $produit->description_produit,
                    'photo' => $produit->photos->first()?->url_photo,
                    'quantite' => (int) $pivot->quantite_commandee,
                    'prixUnitaire' => number_format((float) $pivot->prix_unitaire_vente, 2, '.', ''),
                    'sousTotal' => number_format($pivot->quantite_commandee * (float) $pivot->prix_unitaire_vente, 2, '.', ''),
                ];
            })->values(),
            'suivi' => collect(EtatCommande::parcours($commande->mode_livraison))
                ->map(function (EtatCommande $etat) use ($etatsAtteints): array {
                    /** @var HistoriqueEtat|null $historique */
                    $historique = $etatsAtteints->first(fn (HistoriqueEtat $ligne): bool => $ligne->etat === $etat);

                    return [
                        'etat' => $etat->value,
                        'libelle' => $etat->libelle(),
                        'date' => $historique?->date_etat->toIso8601String(),
                    ];
                })->values(),
        ];
    }
}
