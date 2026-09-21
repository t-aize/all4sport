<?php

namespace Database\Factories;

use App\Enums\EtatCommande;
use App\Models\Commande;
use App\Models\HistoriqueEtat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle HistoriqueEtat.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `HistoriqueEtat::factory()->create()` l'enregistre en
 * base, `HistoriqueEtat::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<HistoriqueEtat>
 */
class HistoriqueEtatFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'etat' => fake()->randomElement(EtatCommande::cases()),
            'date_etat' => fake()->dateTimeBetween('-1 year'),
            'commande_id' => Commande::factory(),
            'employe_id' => null,
        ];
    }
}
