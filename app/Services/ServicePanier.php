<?php

namespace App\Services;

use App\Models\LignePanier;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicePanier
{
    /**
     * Récupère (ou crée) le panier courant :
     * lié au client s'il est connecté, sinon à la session.
     */
    public function courant(): Panier
    {
        if (Auth::check()) {
            return Panier::firstOrCreate(['client_id' => Auth::id()])->load('lignes.produit');
        }

        return Panier::firstOrCreate(
            ['session_id' => session()->getId(), 'client_id' => null]
        )->load('lignes.produit');
    }

    /** Ajoute un produit (ou augmente sa quantité s'il est déjà présent). */
    public function ajouter(Produit $produit, int $quantite = 1): LignePanier
    {
        $quantite = max(1, $quantite);

        return DB::transaction(function () use ($produit, $quantite) {
            $panier = $this->courant();

            $ligne = $panier->lignes()->where('reference_produit', $produit->reference_produit)->lockForUpdate()->first();
            $nouvelleQuantite = ($ligne?->quantite ?? 0) + $quantite;

            $this->verifierStock($produit, $nouvelleQuantite);

            if ($ligne) {
                $ligne->update(['quantite' => $nouvelleQuantite]);

                return $ligne;
            }

            return $panier->lignes()->create([
                'reference_produit' => $produit->reference_produit,
                'quantite'          => $quantite,
                'prix_unitaire'     => $produit->prix_vente,
            ]);
        });
    }

    /** Modifie la quantité d'une ligne. Une quantité <= 0 supprime la ligne. */
    public function modifier(Produit $produit, int $quantite): ?LignePanier
    {
        if ($quantite <= 0) {
            $this->retirer($produit);

            return null;
        }

        return DB::transaction(function () use ($produit, $quantite) {
            $ligne = $this->courant()->lignes()
                ->where('reference_produit', $produit->reference_produit)
                ->lockForUpdate()
                ->firstOrFail();

            $this->verifierStock($produit, $quantite);

            $ligne->update(['quantite' => $quantite]);

            return $ligne;
        });
    }

    /** Supprime une ligne du panier. */
    public function retirer(Produit $produit): void
    {
        $this->courant()->lignes()->where('reference_produit', $produit->reference_produit)->delete();
    }

    /** Vide complètement le panier. */
    public function vider(): void
    {
        $this->courant()->lignes()->delete();
    }

    /**
     * Fusionne le panier visiteur dans celui du client après connexion.
     * À appeler depuis un listener sur l'événement Login, avec l'ancien ID de session
     * (Laravel le régénère à la connexion, il faut donc le récupérer avant).
     */
    public function fusionnerPanierInvite(string $ancienSessionId, int $clientId): void
    {
        $panierInvite = Panier::where('session_id', $ancienSessionId)->whereNull('client_id')->first();

        if (! $panierInvite) {
            return;
        }

        DB::transaction(function () use ($panierInvite, $clientId) {
            $panierClient = Panier::firstOrCreate(['client_id' => $clientId]);

            foreach ($panierInvite->lignes as $ligneInvite) {
                $existante = $panierClient->lignes()
                    ->where('reference_produit', $ligneInvite->reference_produit)
                    ->first();

                if ($existante) {
                    $existante->increment('quantite', $ligneInvite->quantite);
                } else {
                    $panierClient->lignes()->create(
                        $ligneInvite->only(['reference_produit', 'quantite', 'prix_unitaire'])
                    );
                }
            }

            $panierInvite->delete();
        });
    }

    /** Vérifie que la quantité demandée ne dépasse pas le stock internet du produit. */
    private function verifierStock(Produit $produit, int $quantite): void
    {
        $stock = (int) $produit->quantite_stock_internet;

        if ($quantite > $stock) {
            throw ValidationException::withMessages([
                'quantite' => "Stock insuffisant : seulement {$stock} disponible(s).",
            ]);
        }
    }
}