<?php

namespace Database\Factories;

use App\Enums\RoleEmploye;
use App\Models\Employe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Employe.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Employe::factory()->create()` l'enregistre en
 * base, `Employe::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Employe>
 */
class EmployeFactory extends Factory
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
            'identifiant_connexion' => Str::limit(fake()->unique()->userName(), 20, ''),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement(RoleEmploye::cases()),
            'remember_token' => Str::random(10),
        ];
    }
}
