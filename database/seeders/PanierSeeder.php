<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Database\Seeder;

/**
 * Paniers en cours de démonstration : celui du client de démonstration
 * (3 articles) et ceux d'une dizaine d'autres clients. Seuls des produits en
 * stock sur Internet sont ajoutés, au prix de vente actuel.
 */
class PanierSeeder extends Seeder
{
    /**
     * Crée les paniers.
     */
    public function run(): void
    {
        $produits = Produit::query()->where('quantite_stock_internet', '>=', 5)->get();

        if ($produits->isEmpty()) {
            return;
        }

        $demo = Client::query()->where('email', DatabaseSeeder::EMAIL_CLIENT_DEMO)->first();
        $clients = Client::query()->where('email', '!=', DatabaseSeeder::EMAIL_CLIENT_DEMO)->inRandomOrder()->limit(10)->get();

        foreach (collect([$demo])->filter()->merge($clients) as $position => $client) {
            $panier = Panier::query()->firstOrCreate(['client_id' => $client->id]);

            foreach ($produits->random(min($produits->count(), $position === 0 ? 3 : random_int(1, 4))) as $produit) {
                $panier->lignes()->updateOrCreate(
                    ['reference_produit' => $produit->reference_produit],
                    ['quantite' => random_int(1, 2), 'prix_unitaire' => $produit->prix_vente],
                );
            }
        }
    }
}
