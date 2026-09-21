<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Produit.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Produit::factory()->create()` l'enregistre en
 * base, `Produit::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Produit>
 */
class ProduitFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * La référence n'est pas définie ici : elle est générée par Produit::booted().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $coutUnitaireHt = fake()->randomFloat(2, 5, 300);

        return [
            'description_produit' => fake('fr_FR')->sentence(12),
            'cout_unitaire_ht' => $coutUnitaireHt,
            'prix_vente' => round($coutUnitaireHt * fake()->randomFloat(2, 1.3, 2.5), 2),
            'quantite_stock_internet' => fake()->numberBetween(0, 500),
            'lieu_stockage' => fake()->optional()->regexify('[A-Z][0-9]D[0-9]\.[0-9]'),
            'fournisseur_id' => Fournisseur::query()->inRandomOrder()->value('id')
                ?? Fournisseur::factory(),
            'rayon_id' => Rayon::query()->inRandomOrder()->value('id')
                ?? Rayon::factory(),
        ];
    }
}