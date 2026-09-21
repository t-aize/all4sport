<?php

namespace App\Models;

use App\Enums\EtatCommande;
use Database\Factories\HistoriqueEtatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Une ligne d'historique enregistrant un changement d'état d'une commande
 * (association SUIVRE), saisie manuellement par un collaborateur
 * (association ENREGISTRER) : transmise, validée, en préparation, expédiée,
 * livrée ou retirée.
 *
 * `#[Fillable]` liste les attributs qui peuvent être renseignés en masse
 * (`Model::create`, `fill`) : cela protège contre l'ajout de champs non prévus
 * par un formulaire.
 *
 * @property int $id Identifiant technique de la ligne d'historique.
 * @property EtatCommande $etat Libellé de l'état de la commande.
 * @property Carbon $date_etat Date d'enregistrement de l'état.
 * @property int $commande_id Commande concernée par ce changement d'état.
 * @property int|null $employe_id Collaborateur ayant enregistré l'état, le cas échéant.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['etat', 'date_etat', 'commande_id', 'employe_id'])]
class HistoriqueEtat extends Model
{
    /** @use HasFactory<HistoriqueEtatFactory> */
    use HasFactory;

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'etat' => EtatCommande::class,
            'date_etat' => 'datetime',
        ];
    }

    /**
     * La commande concernée par ce changement d'état (association SUIVRE).
     *
     * @return BelongsTo<Commande, $this>
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    /**
     * Le collaborateur ayant enregistré cet état (association ENREGISTRER).
     *
     * @return BelongsTo<Employe, $this>
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }
}
