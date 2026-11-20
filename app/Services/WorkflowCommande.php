<?php

namespace App\Services;

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;
use App\Enums\RoleEmploye;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\HistoriqueEtat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Workflow de traitement d'une commande (besoins du lot A, module b) :
 * transmise → validée → en préparation → expédiée → livrée ou retirée.
 *
 * - « transmise » est enregistrée automatiquement quand le client valide et
 *   paie sa commande ;
 * - chaque état suivant est indiqué manuellement par le collaborateur dont le
 *   rôle en est responsable (voir RoleEmploye::etatsGerables) ;
 * - les états ne peuvent pas être sautés ni revenir en arrière ;
 * - « livrée » n'existe que pour une livraison à domicile, « retirée » que
 *   pour un retrait en magasin.
 *
 * Chaque changement est tracé dans `historique_etats` et recopié dans la
 * colonne `commandes.etat` (état courant).
 */
class WorkflowCommande
{
    /**
     * Enregistre l'état « transmise » d'une commande qui vient d'être payée.
     */
    public function transmettre(Commande $commande): HistoriqueEtat
    {
        return $this->tracer($commande, EtatCommande::Transmise, null);
    }

    /**
     * L'état suivant possible pour la commande, ou null si elle est terminée.
     */
    public function etatSuivant(Commande $commande): ?EtatCommande
    {
        return $commande->etat->suivant($commande->mode_livraison);
    }

    /**
     * Indique si le collaborateur peut faire passer la commande à l'état suivant.
     */
    public function peutAvancer(Employe $employe, Commande $commande): bool
    {
        $suivant = $this->etatSuivant($commande);

        return $suivant !== null && in_array($suivant, $employe->role->etatsGerables(), true);
    }

    /**
     * Fait passer la commande à l'état demandé, après avoir vérifié que c'est
     * bien l'état suivant et que le rôle du collaborateur l'y autorise.
     * `$employe` vaut null pour un changement venant d'un système externe
     * (API du lot B Logistique).
     *
     * @throws ValidationException Si la transition est interdite.
     */
    public function enregistrer(Commande $commande, EtatCommande $etat, ?Employe $employe): HistoriqueEtat
    {
        $suivant = $this->etatSuivant($commande);

        if ($suivant !== $etat) {
            throw ValidationException::withMessages([
                'etat' => $suivant === null
                    ? 'Cette commande est déjà terminée.'
                    : "La commande est « {$commande->etat->libelle()} » : l'état suivant est « {$suivant->libelle()} ».",
            ]);
        }

        if ($employe !== null && ! in_array($etat, $employe->role->etatsGerables(), true)) {
            $responsable = RoleEmploye::responsableDe($etat);

            throw ValidationException::withMessages([
                'etat' => "L'état « {$etat->libelle()} » est enregistré par le rôle « ".($responsable?->libelle() ?? 'système').' ».',
            ]);
        }

        return $this->tracer($commande, $etat, $employe);
    }

    /**
     * Restreint une requête aux commandes qu'un rôle doit traiter : celles dont
     * l'état suivant fait partie des états gérés par ce rôle.
     *
     * Exemple : le livreur voit les commandes « expédiées » à livrer à domicile,
     * le vendeur les commandes « expédiées » à retirer en magasin.
     *
     * @param  Builder<Commande>  $requete
     * @return Builder<Commande>
     */
    public function filtrerATraiter(Builder $requete, RoleEmploye $role): Builder
    {
        $couples = [];

        foreach (EtatCommande::cases() as $etat) {
            foreach (ModeLivraison::cases() as $mode) {
                $suivant = $etat->suivant($mode);

                if ($suivant !== null && in_array($suivant, $role->etatsGerables(), true)) {
                    $couples[] = [$etat, $mode];
                }
            }
        }

        return $requete->where(function (Builder $sousRequete) use ($couples): void {
            foreach ($couples as [$etat, $mode]) {
                $sousRequete->orWhere(fn (Builder $condition) => $condition
                    ->where('etat', $etat->value)
                    ->where('mode_livraison', $mode->value));
            }

            if ($couples === []) {
                $sousRequete->whereRaw('1 = 0');
            }
        });
    }

    /**
     * Ajoute la ligne d'historique et met à jour l'état courant.
     */
    private function tracer(Commande $commande, EtatCommande $etat, ?Employe $employe): HistoriqueEtat
    {
        return DB::transaction(function () use ($commande, $etat, $employe): HistoriqueEtat {
            $historique = $commande->historiqueEtats()->create([
                'etat' => $etat,
                'date_etat' => now(),
                'employe_id' => $employe?->id,
            ]);

            $commande->forceFill([
                'etat' => $etat,
                'employe_id' => $employe->id ?? $commande->employe_id,
            ])->save();

            return $historique;
        });
    }
}
