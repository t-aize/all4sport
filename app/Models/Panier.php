<?php

namespace App\Models;

use Database\Factories\PanierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Le panier d'achat du site marchand. Il appartient soit à un client connecté
 * (`client_id`), soit à un visiteur identifié par sa session (`session_id`).
 * À la connexion, le panier visiteur est fusionné dans celui du client
 * (voir ServicePanier::fusionnerPanierInvite).
 *
 * @property int $id Identifiant technique du panier.
 * @property int|null $client_id Client propriétaire du panier (null pour un visiteur).
 * @property string|null $session_id Identifiant de session d'un visiteur non connecté.
 * @property-read float $total Montant total des lignes du panier.
 * @property-read int $nombre_articles Nombre total d'unités dans le panier.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['client_id', 'session_id'])]
class Panier extends Model
{
    /** @use HasFactory<PanierFactory> */
    use HasFactory;

    /**
     * Le client propriétaire du panier.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Les lignes (produit + quantité) du panier.
     *
     * @return HasMany<LignePanier, $this>
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(LignePanier::class);
    }

    /**
     * Montant total du panier : `$panier->total`.
     */
    public function getTotalAttribute(): float
    {
        return round((float) $this->lignes->sum(fn (LignePanier $ligne): float => $ligne->sous_total), 2);
    }

    /**
     * Nombre total d'unités dans le panier : `$panier->nombre_articles`.
     */
    public function getNombreArticlesAttribute(): int
    {
        return (int) $this->lignes->sum('quantite');
    }

    /**
     * Indique si le panier ne contient aucun article.
     */
    public function estVide(): bool
    {
        return $this->lignes->isEmpty();
    }
}
