<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LignePanier;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logique métier du panier du site marchand : ajout, modification, retrait
 * d'articles et fusion du panier visiteur dans le panier client à la connexion.
 *
 * Un visiteur non connecté a un panier rattaché à sa session ; un client
 * connecté a un panier rattaché à son compte. Conformément au processus de
 * vente en ligne, un visiteur peut remplir son panier mais doit se connecter
 * pour le valider.
 */
class ServicePanier
{
    /**
     * Quantité maximale d'un même produit dans un panier.
     */
    public const QUANTITE_MAXIMALE = 99;

    /**
     * Récupère (ou crée) le panier courant : celui du client connecté, sinon
     * celui de la session du visiteur.
     */
    public function courant(): Panier
    {
        $client = Auth::guard('web')->user();

        $panier = $client instanceof Client
            ? Panier::query()->firstOrCreate(['client_id' => $client->id])
            : Panier::query()->firstOrCreate(['session_id' => session()->getId(), 'client_id' => null]);

        return $panier->load('lignes.produit.photos', 'lignes.produit.rayon');
    }

    /**
     * Le panier courant s'il existe déjà, sans en créer un nouveau (utilisé
     * pour afficher le compteur d'articles sur toutes les pages).
     */
    public function existant(): ?Panier
    {
        $client = Auth::guard('web')->user();

        $requete = $client instanceof Client
            ? Panier::query()->where('client_id', $client->id)
            : Panier::query()->whereNull('client_id')->where('session_id', session()->getId());

        return $requete->with('lignes')->first();
    }

    /**
     * Ajoute un produit au panier, ou augmente sa quantité s'il y est déjà.
     *
     * @throws ValidationException Si le stock disponible est insuffisant.
     */
    public function ajouter(Produit $produit, int $quantite = 1): LignePanier
    {
        $quantite = max(1, $quantite);

        return DB::transaction(function () use ($produit, $quantite): LignePanier {
            $panier = $this->courant();

            /** @var LignePanier|null $ligne */
            $ligne = $panier->lignes()->where('reference_produit', $produit->reference_produit)->lockForUpdate()->first();
            $nouvelleQuantite = min(self::QUANTITE_MAXIMALE, ($ligne->quantite ?? 0) + $quantite);

            $this->verifierStock($produit, $nouvelleQuantite);

            if ($ligne !== null) {
                $ligne->update(['quantite' => $nouvelleQuantite]);

                return $ligne;
            }

            return $panier->lignes()->create([
                'reference_produit' => $produit->reference_produit,
                'quantite' => $nouvelleQuantite,
                'prix_unitaire' => $produit->prix_vente,
            ]);
        });
    }

    /**
     * Modifie la quantité d'une ligne. Une quantité nulle ou négative supprime
     * la ligne.
     *
     * @throws ValidationException Si le stock disponible est insuffisant.
     */
    public function modifier(Produit $produit, int $quantite): ?LignePanier
    {
        if ($quantite <= 0) {
            $this->retirer($produit);

            return null;
        }

        return DB::transaction(function () use ($produit, $quantite): LignePanier {
            /** @var LignePanier $ligne */
            $ligne = $this->courant()->lignes()
                ->where('reference_produit', $produit->reference_produit)
                ->lockForUpdate()
                ->firstOrFail();

            $this->verifierStock($produit, $quantite);

            $ligne->update(['quantite' => $quantite]);

            return $ligne;
        });
    }

    /**
     * Supprime un produit du panier.
     */
    public function retirer(Produit $produit): void
    {
        $this->courant()->lignes()->where('reference_produit', $produit->reference_produit)->delete();
    }

    /**
     * Vide complètement le panier courant.
     */
    public function vider(): void
    {
        $this->courant()->lignes()->delete();
    }

    /**
     * Fusionne le panier visiteur dans celui du client après sa connexion.
     *
     * Laravel régénère l'identifiant de session à la connexion : il faut donc
     * passer l'ANCIEN identifiant, lu avant la régénération (voir les
     * contrôleurs de connexion et d'inscription). Les quantités d'un même
     * produit s'additionnent, dans la limite de QUANTITE_MAXIMALE.
     */
    public function fusionnerPanierInvite(string $ancienSessionId, int $clientId): void
    {
        $panierInvite = Panier::query()
            ->where('session_id', $ancienSessionId)
            ->whereNull('client_id')
            ->with('lignes')
            ->first();

        if ($panierInvite === null) {
            return;
        }

        DB::transaction(function () use ($panierInvite, $clientId): void {
            $panierClient = Panier::query()->firstOrCreate(['client_id' => $clientId]);

            foreach ($panierInvite->lignes as $ligneInvite) {
                /** @var LignePanier|null $existante */
                $existante = $panierClient->lignes()
                    ->where('reference_produit', $ligneInvite->reference_produit)
                    ->first();

                if ($existante !== null) {
                    $existante->update([
                        'quantite' => min(self::QUANTITE_MAXIMALE, $existante->quantite + $ligneInvite->quantite),
                    ]);
                } else {
                    $panierClient->lignes()->create(
                        $ligneInvite->only(['reference_produit', 'quantite', 'prix_unitaire'])
                    );
                }
            }

            $panierInvite->delete();
        });
    }

    /**
     * Vérifie que la quantité demandée ne dépasse pas le meilleur stock
     * disponible (Internet ou un magasin). La vérification définitive, selon
     * le mode de livraison choisi, a lieu à la validation de la commande.
     *
     * @throws ValidationException
     */
    private function verifierStock(Produit $produit, int $quantite): void
    {
        $stock = $produit->stockDisponibleMaximum();

        if ($quantite > $stock) {
            throw ValidationException::withMessages([
                'quantite' => $stock === 0
                    ? 'Ce produit est en rupture de stock.'
                    : "Stock insuffisant : seulement {$stock} disponible(s).",
            ]);
        }
    }
}
