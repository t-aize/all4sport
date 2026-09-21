<?php

namespace Database\Factories;

use App\Models\Entrepot;
use App\Models\Magasin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Magasin.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Magasin::factory()->create()` l'enregistre en
 * base, `Magasin::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Magasin>
 */
class MagasinFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ville' => Str::limit(fake('fr_FR')->city(), 30, ''),
            'adresse' => Str::limit(fake('fr_FR')->streetAddress(), 60, ''),
            'entrepot_id' => Entrepot::factory(),
        ];
    }
}
