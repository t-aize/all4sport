<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de test pour le modèle Photo.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Photo::factory()->create()` l'enregistre en
 * base, `Photo::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url_photo' => fake()->imageUrl(640, 480, 'sports'),
            'reference_produit' => Produit::factory(),
        ];
    }
}
