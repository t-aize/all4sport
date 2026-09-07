<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Enfant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Enfant.
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
