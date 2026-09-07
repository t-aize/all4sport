<?php

namespace Database\Factories;

use App\Models\Entrepot;
use App\Models\Magasin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Magasin.
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
