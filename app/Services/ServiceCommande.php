<?php

namespace App\Services;

use App\Enums\ModeLivraison;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Entrepot;
use App\Models\LignePanier;
use App\Models\Magasin;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Passage d'une commande à partir du panier, selon le processus de vente en
 * ligne : validation du panier → choix du mode de livraison → vérification des
 * stocks → ajout de la commande au compte client → gestion de la livraison.
 *
 * Le stock contrôlé (puis décrémenté) dépend du mode de livraison :
 * - retrait en magasin : le stock du magasin choisi ;
 * - livraison à domicile : le stock Internet, expédié par l'entrepôt qui
 *   dessert le magasin par défaut du client.
 */
class ServiceCommande
{
    public function __construct(private WorkflowCommande $workflow) {}

    /**
     * Crée la commande, décrémente les stocks, vide le panier et enregistre
     * l'état « transmise ». Tout se fait dans une transaction : en cas de stock
     * insuffisant, rien n'est enregistré.
     *
     * @param  array{adresse?: string|null, code_postal?: string|null, ville?: string|null}  $adresse
     *
     * @throws ValidationException Si le panier est vide ou un stock insuffisant.
     */
    public function passer(Client $client, Panier $panier, ModeLivraison $mode, ?Magasin $magasin, array $adresse = []): Commande
    {
        $panier->loadMissing('lignes.produit');

        if ($panier->estVide()) {
            throw ValidationException::withMessages(['panier' => 'Votre panier est vide.']);
        }

        return DB::transaction(function () use ($client, $panier, $mode, $magasin, $adresse): Commande {
            $this->verifierStocks($panier, $mode, $magasin);

            $frais = $mode->frais();
            $sousTotal = round($panier->lignes->sum(fn (LignePanier $ligne): float => $ligne->sous_total), 2);

            $commande = Commande::query()->create([
                'date_commande' => now(),
                'mode_livraison' => $mode,
                'adresse_livraison' => $mode === ModeLivraison::LivraisonDomicile
                    ? mb_substr(trim(($adresse['adresse'] ?? '').', '.($adresse['code_postal'] ?? '').' '.($adresse['ville'] ?? '')), 0, 100)
                    : null,
                'frais_livraison' => $frais,
                'montant_total' => $sousTotal + $frais,
                'client_id' => $client->id,
                'magasin_id' => $mode === ModeLivraison::RetraitMagasin ? $magasin?->id : null,
                'entrepot_id' => $mode === ModeLivraison::LivraisonDomicile ? $this->entrepotExpediteur($client)?->id : null,
            ]);

            foreach ($panier->lignes as $ligne) {
                $commande->produits()->attach($ligne->reference_produit, [
                    'quantite_commandee' => $ligne->quantite,
                    'prix_unitaire_vente' => $ligne->prix_unitaire,
                ]);

                $this->decrementerStock($ligne->produit, $ligne->quantite, $mode, $magasin);
            }

            $client->forceFill(['date_dernier_achat' => now()])->save();
            $panier->lignes()->delete();

            $this->workflow->transmettre($commande);

            return $commande;
        });
    }

    /**
     * Vérifie la disponibilité de chaque ligne du panier dans le lieu de stock
     * correspondant au mode de livraison.
     *
     * @throws ValidationException Liste les produits indisponibles.
     */
    public function verifierStocks(Panier $panier, ModeLivraison $mode, ?Magasin $magasin): void
    {
        $erreurs = [];

        foreach ($panier->lignes as $ligne) {
            /** @var Produit $produit */
            $produit = Produit::query()->lockForUpdate()->findOrFail($ligne->reference_produit);
            $disponible = $this->stockDisponible($produit, $mode, $magasin);

            if ($ligne->quantite > $disponible) {
                $lieu = $mode === ModeLivraison::RetraitMagasin ? "au magasin de {$magasin?->ville}" : 'sur Internet';
                $erreurs[] = "« {$produit->description_produit} » : {$disponible} disponible(s) {$lieu}, {$ligne->quantite} demandé(s).";
            }
        }

        if ($erreurs !== []) {
            throw ValidationException::withMessages(['stock' => $erreurs]);
        }
    }

    /**
     * Le stock disponible d'un produit pour le mode de livraison choisi.
     */
    public function stockDisponible(Produit $produit, ModeLivraison $mode, ?Magasin $magasin): int
    {
        return $mode === ModeLivraison::RetraitMagasin
            ? ($magasin ? $produit->stockMagasin($magasin->id) : 0)
            : (int) $produit->quantite_stock_internet;
    }

    /**
     * L'entrepôt qui expédie une livraison à domicile : celui qui dessert le
     * magasin par défaut du client (zone d'expédition), sinon le premier.
     */
    public function entrepotExpediteur(Client $client): ?Entrepot
    {
        return $client->magasin->entrepot ?? Entrepot::query()->orderBy('id')->first();
    }

    /**
     * Retire la quantité commandée du stock concerné.
     */
    private function decrementerStock(Produit $produit, int $quantite, ModeLivraison $mode, ?Magasin $magasin): void
    {
        if ($mode === ModeLivraison::RetraitMagasin && $magasin !== null) {
            $produit->magasins()->updateExistingPivot($magasin->id, [
                'quantite_stock_magasin' => max(0, $produit->stockMagasin($magasin->id) - $quantite),
            ]);

            return;
        }

        $produit->forceFill([
            'quantite_stock_internet' => max(0, (int) $produit->quantite_stock_internet - $quantite),
        ])->save();
    }
}
