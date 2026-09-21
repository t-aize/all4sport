<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Fabrique de test pour le modèle Fournisseur.
 *
 * Une factory génère des enregistrements aux valeurs réalistes (grâce à Faker)
 * pour les tests et le seeder : `Fournisseur::factory()->create()` l'enregistre en
 * base, `Fournisseur::factory()->make()` crée seulement l'objet en mémoire.
 *
 * @extends Factory<Fournisseur>
 */
class FournisseurFactory extends Factory
{
    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom_fournisseur' => Str::limit(fake('fr_FR')->unique()->company(), 40, ''),
        ];
    }
}
