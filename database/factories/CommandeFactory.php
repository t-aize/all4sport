<?php

namespace Database\Factories;

use App\Enums\ModeLivraison;
use App\Models\Client;
use App\Models\Commande;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Commande.
 *
 * @extends Factory<Commande>
 */
class CommandeFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $modeLivraison = fake()->randomElement(ModeLivraison::cases());

        return [
            'date_commande' => fake()->dateTimeBetween('-1 year'),
            'mode_livraison' => $modeLivraison,
            'adresse_livraison' => $modeLivraison === ModeLivraison::LivraisonDomicile
                ? fake('fr_FR')->streetAddress()
                : null,
            'frais_livraison' => $modeLivraison === ModeLivraison::LivraisonDomicile ? 10 : 0,
            'montant_total' => fake()->randomFloat(2, 20, 500),
            'client_id' => Client::factory(),
            'employe_id' => null,
            'magasin_id' => null,
            'entrepot_id' => null,
        ];
    }

    /**
     * Indique que la commande est retirée en magasin.
     */
    public function retraitMagasin(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_livraison' => ModeLivraison::RetraitMagasin,
            'adresse_livraison' => null,
            'frais_livraison' => 0,
            'entrepot_id' => null,
        ]);
    }

    /**
     * Indique que la commande est livrée à domicile.
     */
    public function livraisonDomicile(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode_livraison' => ModeLivraison::LivraisonDomicile,
            'adresse_livraison' => fake('fr_FR')->streetAddress(),
            'frais_livraison' => 10,
            'magasin_id' => null,
        ]);
    }
}
