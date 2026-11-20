<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EtatCommande;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommandeLogistiqueResource;
use App\Models\Commande;
use App\Services\WorkflowCommande;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Commandes transmises au SI Logistique (Lot B) : la logistique récupère les
 * commandes validées à préparer et signale leur préparation et leur
 * expédition. Les autres états restent saisis dans le back-office (vente).
 */
class CommandeController extends Controller
{
    /**
     * États que le SI Logistique peut enregistrer.
     *
     * @var list<EtatCommande>
     */
    private const ETATS_LOGISTIQUE = [EtatCommande::EnPreparation, EtatCommande::Expediee];

    public function __construct(private WorkflowCommande $workflow) {}

    /**
     * Liste les commandes d'un état, par défaut celles qui sont validées
     * (à préparer), de la plus ancienne à la plus récente.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtres = $request->validate([
            'etat' => ['nullable', Rule::enum(EtatCommande::class)],
            'par_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $commandes = Commande::query()
            ->with(['client', 'produits', 'magasin', 'entrepot'])
            ->where('etat', $filtres['etat'] ?? EtatCommande::Validee->value)
            ->oldest('date_commande')
            ->oldest('id')
            ->paginate((int) ($filtres['par_page'] ?? 50));

        return CommandeLogistiqueResource::collection($commandes);
    }

    /**
     * Le détail d'une commande.
     */
    public function show(Commande $commande): CommandeLogistiqueResource
    {
        return new CommandeLogistiqueResource($commande->load(['client', 'produits', 'magasin', 'entrepot']));
    }

    /**
     * Enregistre l'état "en préparation" ou "expédiée" d'une commande.
     */
    public function changerEtat(Request $request, Commande $commande): CommandeLogistiqueResource
    {
        $donnees = $request->validate([
            'etat' => ['required', Rule::in(array_map(fn (EtatCommande $etat): string => $etat->value, self::ETATS_LOGISTIQUE))],
        ]);

        $this->workflow->enregistrer($commande, EtatCommande::from($donnees['etat']), null);

        return new CommandeLogistiqueResource($commande->refresh()->load(['client', 'produits', 'magasin', 'entrepot']));
    }
}
