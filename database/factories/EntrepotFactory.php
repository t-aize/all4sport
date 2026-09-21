<?php

namespace Database\Factories;

use App\Models\Entrepot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Entrepot.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Entrepot::factory()->create()` l'enregistre en
 * base, `Entrepot::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Entrepot>
 */
class EntrepotFactory extends Factory
{
    /**
     * Les régions françaises utilisées comme zones d'expédition.
     *
     * @var array<int, string>
     */
    private const REGIONS = [
        'Île-de-France', 'Auvergne-Rhône-Alpes', 'Nouvelle-Aquitaine',
        'Occitanie', 'Hauts-de-France', 'Grand Est', 'Provence-Alpes-Côte d\'Azur',
        'Bretagne', 'Normandie', 'Pays de la Loire', 'Bourgogne-Franche-Comté', 'Centre-Val de Loire',
    ];

    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ville' => Str::limit(fake('fr_FR')->city(), 30, ''),
            'zone_expedition' => fake()->randomElement(self::REGIONS),
        ];
    }
}
