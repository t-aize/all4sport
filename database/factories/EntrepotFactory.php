<?php

namespace Database\Factories;

use App\Models\Entrepot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Entrepot.
 *
 * @extends Factory<Entrepot>
 */
class EntrepotFactory extends Factory
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
            'zone_expedition' => Str::limit(fake('fr_FR')->region(), 50, ''),
        ];
    }
}
