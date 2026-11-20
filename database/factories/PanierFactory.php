<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Panier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrique de paniers de test. Par défaut, le panier appartient à un client ;
 * l'état `invite()` crée un panier de visiteur rattaché à une session.
 *
 * @extends Factory<Panier>
 */
class PanierFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'session_id' => null,
        ];
    }

    /**
     * Panier d'un visiteur non connecté.
     */
    public function invite(?string $sessionId = null): static
    {
        return $this->state(fn (): array => [
            'client_id' => null,
            'session_id' => $sessionId ?? fake()->uuid(),
        ]);
    }
}
