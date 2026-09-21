<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Enfant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Enfant.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Enfant::factory()->create()` l'enregistre en
 * base, `Enfant::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Enfant>
 */
class EnfantFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date_naissance' => fake()->dateTimeBetween('-17 years', '-1 year'),
            'client_id' => Client::factory(),
        ];
    }
}
