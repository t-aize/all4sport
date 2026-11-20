<?php

namespace App\Http\Controllers\BackOffice;

use App\Enums\EtatCommande;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Produit;
use App\Services\WorkflowCommande;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tableau de bord du back-office, affiché après la connexion d'un
 * collaborateur : son rôle, les états qu'il peut enregistrer, les commandes
 * qu'il doit traiter et les indicateurs clés de l'activité de vente.
 *
 * Page React : resources/js/pages/backoffice/dashboard.tsx.
 */
class DashboardController extends Controller
{
    /**
     * Seuil en dessous duquel le stock Internet d'un produit est jugé faible.
     */
    private const SEUIL_STOCK_FAIBLE = 5;

    /**
     * Abréviations françaises des mois, pour le graphique des ventes.
     *
     * @var list<string>
     */
    private const MOIS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

    /**
     * Affiche le tableau de bord du collaborateur connecté.
     */
    public function __invoke(Request $request, WorkflowCommande $workflow): Response
    {
        /** @var Employe $employe */
        $employe = $request->user('employe');

        $commandesParEtat = Commande::query()
            ->selectRaw('etat, count(*) as total')
            ->groupBy('etat')
            ->pluck('total', 'etat');

        return Inertia::render('backoffice/dashboard', [
            'roleLibelle' => $employe->role->libelle(),
            'etatsGerables' => array_map(
                fn (EtatCommande $etat): array => [
                    'value' => $etat->value,
                    'label' => $etat->libelle(),
                ],
                $employe->role->etatsGerables(),
            ),
            'indicateurs' => [
                'aTraiter' => $workflow->filtrerATraiter(Commande::query(), $employe->role)->count(),
                'commandes' => Commande::query()->count(),
                'chiffreAffaires' => number_format((float) Commande::query()->sum('montant_total'), 2, '.', ''),
                'panierMoyen' => number_format((float) Commande::query()->avg('montant_total'), 2, '.', ''),
                'clients' => Client::query()->count(),
                'produits' => Produit::query()->count(),
            ],
            'commandesParEtat' => array_map(fn (EtatCommande $etat): array => [
                'value' => $etat->value,
                'label' => $etat->libelle(),
                'total' => (int) ($commandesParEtat[$etat->value] ?? 0),
            ], EtatCommande::cases()),
            'ventesMensuelles' => $this->ventesMensuelles(),
            'aTraiter' => $workflow->filtrerATraiter(Commande::query()->with('client'), $employe->role)
                ->oldest('date_commande')
                ->oldest('id')
                ->limit(6)
                ->get()
                ->map(fn (Commande $commande): array => [
                    'id' => $commande->id,
                    'numero' => $commande->numero(),
                    'date' => $commande->date_commande->toDateString(),
                    'client' => "{$commande->client->prenom} {$commande->client->nom}",
                    'etat' => $commande->etat->value,
                    'etatLibelle' => $commande->etat->libelle(),
                    'modeLivraison' => $commande->mode_livraison->libelle(),
                    'montantTotal' => $commande->montant_total,
                ]),
            'stocksFaibles' => Produit::query()
                ->with('rayon')
                ->where('quantite_stock_internet', '<', self::SEUIL_STOCK_FAIBLE)
                ->orderBy('quantite_stock_internet')
                ->limit(6)
                ->get()
                ->map(fn (Produit $produit): array => [
                    'reference' => $produit->reference_produit,
                    'description' => $produit->description_produit,
                    'rayon' => $produit->rayon?->nom_rayon,
                    'stockInternet' => $produit->quantite_stock_internet,
                ]),
            'meilleursRayons' => DB::table('commande_produit')
                ->join('produits', 'produits.reference_produit', '=', 'commande_produit.reference_produit')
                ->join('rayons', 'rayons.id', '=', 'produits.rayon_id')
                ->selectRaw('rayons.nom_rayon as nom, sum(commande_produit.quantite_commandee * commande_produit.prix_unitaire_vente) as total')
                ->groupBy('rayons.nom_rayon')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn (object $ligne): array => [
                    'nom' => (string) $ligne->nom,
                    'total' => number_format((float) $ligne->total, 2, '.', ''),
                ]),
        ]);
    }

    /**
     * Le chiffre d'affaires et le nombre de commandes des 6 derniers mois.
     *
     * @return list<array{mois: string, total: string, commandes: int}>
     */
    private function ventesMensuelles(): array
    {
        $debut = Carbon::now()->startOfMonth()->subMonths(5);
        $commandes = Commande::query()
            ->where('date_commande', '>=', $debut->toDateString())
            ->get(['date_commande', 'montant_total']);

        $mois = [];

        for ($decalage = 0; $decalage < 6; $decalage++) {
            $date = $debut->copy()->addMonths($decalage);
            $duMois = $commandes->filter(fn (Commande $commande): bool => $commande->date_commande->isSameMonth($date));

            $mois[] = [
                'mois' => self::MOIS[$date->month - 1].' '.$date->year,
                'total' => number_format((float) $duMois->sum('montant_total'), 2, '.', ''),
                'commandes' => $duMois->count(),
            ];
        }

        return $mois;
    }
}
