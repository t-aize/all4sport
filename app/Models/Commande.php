<?php

namespace App\Models;

use App\Enums\ModeLivraison;
use Database\Factories\CommandeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Une commande passée par un client (association CHOISIR), éventuellement
 * traitée par un collaborateur (association PASSER), expédiée depuis un
 * entrepôt (association EXPEDIER) ou retirée dans un magasin (association
 * RETIRER) selon le mode de livraison choisi.
 *
 * @property int $id Identifiant technique de la commande.
 * @property Carbon $date_commande Date de passation de la commande.
 * @property ModeLivraison $mode_livraison Mode de livraison choisi (retrait en magasin / livraison à domicile).
 * @property string|null $adresse_livraison Adresse de livraison à domicile de la commande.
 * @property string $frais_livraison Frais de livraison facturés (0 € ou 10 €).
 * @property string $montant_total Montant total TTC de la commande.
 * @property int $client_id Client ayant passé la commande.
 * @property int|null $employe_id Collaborateur ayant traité la commande, le cas échéant.
 * @property int|null $magasin_id Magasin de retrait, si la commande est retirée en magasin.
 * @property int|null $entrepot_id Entrepôt d'expédition, si la commande est livrée à domicile.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['date_commande', 'mode_livraison', 'adresse_livraison', 'frais_livraison', 'montant_total', 'client_id', 'employe_id', 'magasin_id', 'entrepot_id'])]
class Commande extends Model
{
    /** @use HasFactory<CommandeFactory> */
    use HasFactory;

    /**
     * Les attributs à transtyper (cast).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_commande' => 'date',
            'mode_livraison' => ModeLivraison::class,
            'frais_livraison' => 'decimal:2',
            'montant_total' => 'decimal:2',
        ];
    }

    /**
     * Le client ayant passé cette commande (association CHOISIR).
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Le collaborateur ayant traité cette commande (association PASSER).
     *
     * @return BelongsTo<Employe, $this>
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    /**
     * Le magasin de retrait de cette commande (association RETIRER).
     *
     * @return BelongsTo<Magasin, $this>
     */
    public function magasin(): BelongsTo
    {
        return $this->belongsTo(Magasin::class);
    }

    /**
     * L'entrepôt d'expédition de cette commande (association EXPEDIER).
     *
     * @return BelongsTo<Entrepot, $this>
     */
    public function entrepot(): BelongsTo
    {
        return $this->belongsTo(Entrepot::class);
    }

    /**
     * Les produits contenus dans cette commande, avec la quantité commandée et
     * le prix unitaire de vente au moment de la commande (association CONTENIR,
     * table pivot commande_produit).
     *
     * @return BelongsToMany<Produit, $this>
     */
    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'commande_produit', 'commande_id', 'reference_produit', 'id', 'reference_produit')
            ->withPivot(['quantite_commandee', 'prix_unitaire_vente'])
            ->withTimestamps();
    }

    /**
     * L'historique des états successifs de cette commande (association SUIVRE).
     *
     * @return HasMany<HistoriqueEtat, $this>
     */
    public function historiqueEtats(): HasMany
    {
        return $this->hasMany(HistoriqueEtat::class);
    }
}
