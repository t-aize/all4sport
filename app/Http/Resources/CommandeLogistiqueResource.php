<?php

namespace App\Http\Resources;

use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Représentation JSON d'une commande pour l'API Logistique : uniquement ce
 * dont la logistique a besoin pour préparer et expédier (pas de donnée
 * personnelle superflue : ni email, ni téléphone, ni date de naissance).
 *
 * @mixin Commande
 */
class CommandeLogistiqueResource extends JsonResource
{
    /**
     * Transforme la commande en tableau.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero(),
            'date_commande' => $this->date_commande->toDateString(),
            'etat' => $this->etat->value,
            'mode_livraison' => $this->mode_livraison->value,
            'code_client' => $this->client->code_cli,
            'destinataire' => "{$this->client->prenom} {$this->client->nom}",
            'adresse_livraison' => $this->adresse_livraison,
            'magasin_retrait' => $this->magasin ? ['id' => $this->magasin->id, 'ville' => $this->magasin->ville] : null,
            'entrepot_expedition' => $this->entrepot ? ['id' => $this->entrepot->id, 'ville' => $this->entrepot->ville] : null,
            'lignes' => $this->produits->map(fn (Produit $produit): array => [
                'reference_produit' => $produit->reference_produit,
                'description' => $produit->description_produit,
                'lieu_stockage' => $produit->lieu_stockage,
                'quantite' => (int) $produit->getRelation('pivot')->getAttribute('quantite_commandee'),
            ])->values(),
        ];
    }
}
