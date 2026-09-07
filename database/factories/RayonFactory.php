<?php

namespace Database\Factories;

use App\Models\Rayon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Rayon.
 *
 * @extends Factory<Rayon>
 */
class RayonFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom_rayon' => fake()->unique()->randomElement([
                'Football', 'Basketball', 'Tennis', 'Natation', 'Running',
                'Randonnée', 'Cyclisme', 'Musculation', 'Ski', 'Golf',
                'Rugby', 'Boxe', 'Yoga', 'Escalade', 'Surf',
            ]),
        ];
    }
}
