<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Produit.
 *
 * @extends Factory<Produit>
 */
class ProduitFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $coutUnitaireHt = fake()->randomFloat(2, 5, 300);

        return [
            'reference_produit' => fake()->unique()->regexify('[A-Z]{6}[0-9]{12}'),
            'description_produit' => fake('fr_FR')->sentence(12),
            'cout_unitaire_ht' => $coutUnitaireHt,
            'prix_vente' => round($coutUnitaireHt * fake()->randomFloat(2, 1.3, 2.5), 2),
            'quantite_stock_internet' => fake()->numberBetween(0, 500),
            'lieu_stockage' => fake()->optional()->regexify('[A-Z][0-9]D[0-9]\.[0-9]'),
            'fournisseur_id' => Fournisseur::factory(),
            'rayon_id' => Rayon::factory(),
        ];
    }
}
