<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Client.
 *
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Le mot de passe courant utilisé par la fabrique.
     */
    protected static ?string $password;

    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake('fr_FR')->lastName(),
            'prenom' => fake('fr_FR')->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'telephone' => fake('fr_FR')->numerify('0# ## ## ## ##'),
            'adresse' => Str::limit(fake('fr_FR')->streetAddress(), 50, ''),
            'code_postal' => fake('fr_FR')->postcode(),
            'ville' => Str::limit(fake('fr_FR')->city(), 30, ''),
            'date_naissance' => fake()->dateTimeBetween('-70 years', '-18 years'),
            'date_dernier_achat' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
