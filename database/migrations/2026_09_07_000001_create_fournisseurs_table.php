<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `fournisseurs` : les entreprises qui livrent les produits à ALL4SPORT.
 *
 * Une migration décrit une modification de la structure de la base de données
 * (créer une table, ajouter une colonne…). Elle est versionnée avec le code :
 * `php artisan migrate` exécute `up()`, `php artisan migrate:rollback` exécute
 * `down()`. Les migrations sont jouées dans l'ordre alphabétique de leur nom,
 * d'où le préfixe daté et numéroté.
 */
return new class extends Migration
{
    /**
     * Crée la table.
     */
    public function up(): void
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            // Clé primaire auto-incrémentée (identifiant technique).
            $table->id();
            $table->string('nom_fournisseur', 40);
            $table->char('code_fournisseur', 3)->unique();
            // Ajoute les colonnes created_at et updated_at, remplies par Eloquent.
            $table->timestamps();
        });
    }

    /**
     * Annule la migration : supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};
