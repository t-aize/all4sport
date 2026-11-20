<?php

namespace App\Http\Controllers\BackOffice;

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;
use App\Enums\RoleEmploye;
use App\Http\Controllers\Compte\CommandeController as CompteCommandeController;
use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\HistoriqueEtat;
use App\Services\WorkflowCommande;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des commandes dans le back-office, selon le rôle du collaborateur :
 * - service client : valide les commandes transmises ;
 * - opérateur : prépare puis expédie les commandes validées ;
 * - livreur : confirme la livraison à domicile des commandes expédiées ;
 * - vendeur : confirme le retrait en magasin des commandes expédiées.
 *
 * Tous les collaborateurs consultent toutes les commandes ; chacun ne peut
 * enregistrer que les états dont son rôle est responsable.
 *
 * Pages React : resources/js/pages/backoffice/commandes/index.tsx et show.tsx.
 */
class CommandeController extends Controller
{
    /**
     * Nombre de commandes par page.
     */
    private const COMMANDES_PAR_PAGE = 15;

    public function __construct(private WorkflowCommande $workflow) {}

    /**
     * Liste les commandes. Par défaut, seules celles que le rôle du
     * collaborateur doit traiter sont affichées (filtre « à traiter »).
     */
    public function index(Request $request): Response
    {
        /** @var Employe $employe */
        $employe = $request->user('employe');

        $filtres = $request->validate([
            'filtre' => ['nullable', Rule::in(['a_traiter', 'toutes', ...array_column(EtatCommande::cases(), 'value')])],
            'mode' => ['nullable', Rule::enum(ModeLivraison::class)],
            'recherche' => ['nullable', 'string', 'max:50'],
        ]);

        $filtre = $filtres['filtre'] ?? 'a_traiter';
        $recherche = trim((string) ($filtres['recherche'] ?? ''));

        $commandes = Commande::query()
            ->with(['client', 'magasin', 'entrepot'])
            ->withSum('produits as nombre_articles', 'commande_produit.quantite_commandee')
            ->when($filtre === 'a_traiter', fn (Builder $requete) => $this->workflow->filtrerATraiter($requete, $employe->role))
            ->when(! in_array($filtre, ['a_traiter', 'toutes'], true), fn (Builder $requete) => $requete->where('etat', $filtre))
            ->when($filtres['mode'] ?? null, fn (Builder $requete, string $mode) => $requete->where('mode_livraison', $mode))
            ->when($recherche !== '', fn (Builder $requete) => $requete->where(function (Builder $condition) use ($recherche): void {
                $numero = (int) preg_replace('/\D/', '', $recherche);

                $condition->whereHas('client', fn (Builder $client) => $client
                    ->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('prenom', 'like', "%{$recherche}%")
                    ->orWhere('code_cli', 'like', "%{$recherche}%")
                    ->orWhere('email', 'like', "%{$recherche}%"));

                if ($numero > 0) {
                    $condition->orWhere('id', $numero);
                }
            }))
            ->latest('date_commande')
            ->latest('id')
            ->paginate(self::COMMANDES_PAR_PAGE)
            ->withQueryString()
            ->through(fn (Commande $commande): array => [
                'id' => $commande->id,
                'numero' => $commande->numero(),
                'date' => $commande->date_commande->toDateString(),
                'client' => "{$commande->client->prenom} {$commande->client->nom}",
                'codeCli' => $commande->client->code_cli,
                'modeLivraison' => $commande->mode_livraison->value,
                'modeLivraisonLibelle' => $commande->mode_livraison->libelle(),
                'lieuExpedition' => $commande->lieuExpedition(),
                'etat' => $commande->etat->value,
                'etatLibelle' => $commande->etat->libelle(),
                'montantTotal' => $commande->montant_total,
                'nombreArticles' => (int) $commande->getAttribute('nombre_articles'),
                'actionPossible' => $this->workflow->peutAvancer($employe, $commande),
            ]);

        return Inertia::render('backoffice/commandes/index', [
            'commandes' => $commandes,
            'filtres' => [
                'filtre' => $filtre,
                'mode' => $filtres['mode'] ?? null,
                'recherche' => $recherche,
            ],
            'compteurs' => [
                'a_traiter' => $this->workflow->filtrerATraiter(Commande::query(), $employe->role)->count(),
                'toutes' => Commande::query()->count(),
                ...Commande::query()->selectRaw('etat, count(*) as total')->groupBy('etat')->pluck('total', 'etat')->map(fn ($total): int => (int) $total)->all(),
            ],
            'etats' => EtatCommande::options(),
            'modes' => array_map(fn (ModeLivraison $mode): array => ['value' => $mode->value, 'label' => $mode->libelle()], ModeLivraison::cases()),
        ]);
    }

    /**
     * Affiche le détail d'une commande, son historique et l'action que le
     * collaborateur peut effectuer.
     */
    public function show(Request $request, Commande $commande): Response
    {
        /** @var Employe $employe */
        $employe = $request->user('employe');
        $commande->load(['client', 'produits.photos', 'magasin', 'entrepot', 'historiqueEtats.employe']);
        $suivant = $this->workflow->etatSuivant($commande);
        $responsable = $suivant ? RoleEmploye::responsableDe($suivant) : null;

        return Inertia::render('backoffice/commandes/show', [
            'commande' => [
                ...CompteCommandeController::detail($commande),
                'client' => [
                    'codeCli' => $commande->client->code_cli,
                    'nom' => "{$commande->client->prenom} {$commande->client->nom}",
                    'email' => $commande->client->email,
                    'telephone' => $commande->client->telephone,
                    'adresse' => $commande->client->adresseComplete(),
                ],
                'historique' => $commande->historiqueEtats
                    ->sortBy([['date_etat', 'desc'], ['id', 'desc']])
                    ->values()
                    ->map(fn (HistoriqueEtat $ligne): array => [
                        'id' => $ligne->id,
                        'etat' => $ligne->etat->value,
                        'libelle' => $ligne->etat->libelle(),
                        'date' => $ligne->date_etat->toIso8601String(),
                        'employe' => $ligne->employe ? "{$ligne->employe->prenom} {$ligne->employe->nom} ({$ligne->employe->role->libelle()})" : 'Automatique',
                    ]),
            ],
            'etatSuivant' => $suivant ? ['value' => $suivant->value, 'label' => $suivant->libelle()] : null,
            'responsableSuivant' => $responsable?->libelle(),
            'peutAvancer' => $this->workflow->peutAvancer($employe, $commande),
        ]);
    }

    /**
     * Enregistre l'état suivant de la commande (saisie manuelle par le
     * collaborateur responsable).
     */
    public function changerEtat(Request $request, Commande $commande): RedirectResponse
    {
        /** @var Employe $employe */
        $employe = $request->user('employe');

        $donnees = $request->validate([
            'etat' => ['required', Rule::enum(EtatCommande::class)],
        ]);

        $etat = EtatCommande::from($donnees['etat']);
        $this->workflow->enregistrer($commande, $etat, $employe);

        return back()->with('status', "Commande {$commande->numero()} : état « {$etat->libelle()} » enregistré.");
    }
}
