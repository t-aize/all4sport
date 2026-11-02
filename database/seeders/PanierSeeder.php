<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Database\Seeder;

class PanierSeeder extends Seeder
{
    public function run(): void
    {
        // Produits de démonstration avec du stock
        $produits = Produit::factory()->count(10)->create(['quantite_stock_internet' => 20]);

        // Un client de test (réutilise celui qui existe déjà)
        $client = Client::first() ?? Client::factory()->create();

        // Un panier rempli avec 3 produits
        $panier = Panier::firstOrCreate(['client_id' => $client->id]);

        foreach ($produits->random(3) as $produit) {
            $panier->lignes()->updateOrCreate(
                ['reference_produit' => $produit->reference_produit],
                ['quantite' => random_int(1, 3), 'prix_unitaire' => $produit->prix_vente]
            );
        }
    }
}