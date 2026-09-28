<?php

namespace Database\Factories;

use App\Models\Rayon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Rayon.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Rayon::factory()->create()` l'enregistre en
 * base, `Rayon::factory()->make()` crée seulement l'objet en mémoire.
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
        $nom = fake()->unique()->randomElement([
            'Football', 'Basketball', 'Tennis', 'Natation', 'Running',
            'Randonnée', 'Cyclisme', 'Musculation', 'Ski', 'Golf',
            'Rugby', 'Boxe', 'Yoga', 'Escalade', 'Surf',
        ]);

        return [
            'nom_rayon' => $nom,
            'code_rayon' => Str::upper(Str::substr($nom, 0, 3)),
        ];
    }
}