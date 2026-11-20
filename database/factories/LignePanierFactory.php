<?php

namespace Database\Factories;

use App\Models\LignePanier;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de lignes de panier de test.
 *
 * @extends Factory<LignePanier>
 */
class LignePanierFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'panier_id' => Panier::factory(),
            'reference_produit' => Produit::factory(),
            'quantite' => fake()->numberBetween(1, 3),
            'prix_unitaire' => fake()->randomFloat(2, 5, 200),
        ];
    }
}
